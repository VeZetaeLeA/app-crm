<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Database;
use Core\Auth;
use Core\Session;
use PDO;

class BlogCMSController extends Controller
{
    public function __construct()
    {
        if (!Auth::can('manage_cms')) {
            Session::flash('error', 'Acceso denegado. Se requieren permisos de Gestión de Contenido.');
            $this->redirect('/dashboard');
        }
    }

    public function index()
    {
        $db = Database::getInstance()->getConnection();

        $posts = $db->query("SELECT p.*, c.name as category_name, u.name as author_name 
                            FROM blog_posts p 
                            JOIN blog_categories c ON p.category_id = c.id 
                            JOIN users u ON p.author_id = u.id 
                            ORDER BY p.created_at DESC")->fetchAll();

        $this->viewLayout('admin/blog/index', 'admin', [
            'title' => 'Gestión de Blog | ' . \Core\Config::get('business.company_name'),
            'posts' => $posts
        ]);
    }

    public function create()
    {
        $db = Database::getInstance()->getConnection();
        $categories = $db->query("SELECT * FROM blog_categories")->fetchAll();

        $this->viewLayout('admin/blog/create', 'admin', [
            'title' => 'Nueva Publicación',
            'categories' => $categories
        ]);
    }

    public function store()
    {
        $db = Database::getInstance()->getConnection();

        $title = $_POST['title'];
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $excerpt = $_POST['excerpt'];
        $content = $_POST['content'];
        $category_id = $_POST['category_id'];
        $status = $_POST['status'];
        $author_id = Auth::user()['id'];

        // 1. Insert initial post to get the ID
        $sql = "INSERT INTO blog_posts (title, slug, excerpt, content, category_id, status, author_id, published_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, " . ($status == 'published' ? 'NOW()' : 'NULL') . ")";
        $stmt = $db->prepare($sql);
        $stmt->execute([$title, $slug, $excerpt, $content, $category_id, $status, $author_id]);
        $postId = $db->lastInsertId();

        // 2. Handle featured image if present
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
            $errors = \Core\Validator::validateFile($_FILES['featured_image'], 5242880, ['jpg', 'jpeg', 'png', 'webp']);

            if (empty($errors)) {
                $upload_dir = 'assets/images/post/';
                if (!is_dir($upload_dir))
                    mkdir($upload_dir, 0777, true);

                $file_name = \Core\Validator::generateSecureFileName($_FILES['featured_image']['name']);
                $target_file = $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
                    $featured_image = 'assets/images/post/' . $file_name;

                    // Update post with the image path
                    $db->prepare("UPDATE blog_posts SET featured_image = ? WHERE id = ?")
                        ->execute([$featured_image, $postId]);
                }
            } else {
                Session::flash('error', 'Error en imagen: ' . implode(', ', $errors));
            }
        }

        Session::flash('success', 'Publicación creada.');
        $this->redirect('/admin/blog');
    }

    public function edit($id)
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT * FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch();

        if (!$post)
            $this->redirect('/admin/blog');

        $categories = $db->query("SELECT * FROM blog_categories")->fetchAll();

        $this->viewLayout('admin/blog/edit', 'admin', [
            'title' => 'Editar: ' . $post['title'],
            'post' => $post,
            'categories' => $categories
        ]);
    }

    public function update()
    {
        $db = Database::getInstance()->getConnection();
        $id = $_POST['id'];
        $title = $_POST['title'];
        $excerpt = $_POST['excerpt'];
        $content = $_POST['content'];
        $category_id = $_POST['category_id'];
        $status = $_POST['status'];

        $stmt = $db->prepare("SELECT featured_image, slug FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch();
        $featured_image = $post['featured_image'];
        $slug = $post['slug'];

        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
            $errors = \Core\Validator::validateFile($_FILES['featured_image'], 5242880, ['jpg', 'jpeg', 'png', 'webp']);

            if (empty($errors)) {
                $upload_dir = 'assets/images/post/';
                if (!is_dir($upload_dir))
                    mkdir($upload_dir, 0777, true);

                $file_name = \Core\Validator::generateSecureFileName($_FILES['featured_image']['name']);
                $target_file = $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
                    // Borrar imagen anterior si existe
                    if ($featured_image && file_exists('assets/images/post/' . basename($featured_image))) {
                        @unlink('assets/images/post/' . basename($featured_image));
                    }
                    $featured_image = 'assets/images/post/' . $file_name;
                }
            } else {
                Session::flash('error', 'Error en imagen: ' . implode(', ', $errors));
            }
        }

        $sql = "UPDATE blog_posts SET title = ?, excerpt = ?, content = ?, category_id = ?, status = ?, featured_image = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$title, $excerpt, $content, $category_id, $status, $featured_image, $id]);

        Session::flash('success', 'Publicación actualizada.');
        $this->redirect('/admin/blog');
    }

    public function delete($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'Publicación eliminada.');
        $this->redirect('/admin/blog');
    }
}
