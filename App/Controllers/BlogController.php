<?php
namespace App\Controllers;

use Core\Controller;
use Core\Database;
use PDO;

class BlogController extends Controller
{
    public function index()
    {
        $db = Database::getInstance()->getConnection();

        // Paginación
        $limit = 15;
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        // Total posts para paginación
        $total = $db->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'")->fetchColumn();
        $totalPages = ceil($total / $limit);

        // Get posts con límite
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, c.color as category_color, u.name as author_name 
                FROM blog_posts p 
                JOIN blog_categories c ON p.category_id = c.id 
                JOIN users u ON p.author_id = u.id 
                WHERE p.status = 'published' 
                ORDER BY p.published_at DESC
                LIMIT $limit OFFSET $offset";
        $stmt = $db->query($sql);
        $posts = $stmt->fetchAll();

        // Get categories for filtering
        $categories = $db->query("SELECT * FROM blog_categories WHERE is_active = 1")->fetchAll();

        $this->viewLayout('public/blog/index', 'public', [
            'title' => 'Blog | ' . \Core\Config::get('business.company_name'),
            'posts' => $posts,
            'categories' => $categories,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }

    public function post($slug)
    {
        $db = Database::getInstance()->getConnection();

        // Get post
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, u.name as author_name 
                FROM blog_posts p 
                JOIN blog_categories c ON p.category_id = c.id 
                JOIN users u ON p.author_id = u.id 
                WHERE p.slug = ? AND p.status = 'published'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$slug]);
        $post = $stmt->fetch();

        if (!$post) {
            $this->redirect('/blog');
        }

        // Increment views
        $db->prepare("UPDATE blog_posts SET views_count = views_count + 1 WHERE id = ?")->execute([$post['id']]);

        // Get comments (only approved by default)
        $stmt = $db->prepare("SELECT * FROM comments WHERE post_id = ? AND status = 'approved' ORDER BY created_at DESC");
        $stmt->execute([$post['id']]);
        $comments = $stmt->fetchAll();

        $this->viewLayout('public/blog/post', 'public', [
            'title' => $post['title'] . ' | ' . \Core\Config::get('business.company_name'),
            'post' => $post,
            'comments' => $comments
        ]);
    }

    public function comment()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            $this->redirect('/blog');

        $db = Database::getInstance()->getConnection();
        $postId = (int) $_POST['post_id'];
        $name = strip_tags(trim($_POST['name']));
        $email = strip_tags(trim($_POST['email']));
        $content = strip_tags(trim($_POST['content']));
        $userId = \Core\Auth::user() ? \Core\Auth::user()['id'] : null;
        $ip = $_SERVER['REMOTE_ADDR'];

        // Get post slug for redirect
        $stmt = $db->prepare("SELECT slug FROM blog_posts WHERE id = ?");
        $stmt->execute([$postId]);
        $post = $stmt->fetch();

        if (!$post)
            $this->redirect('/blog');

        $sql = "INSERT INTO comments (post_id, user_id, author_name, author_email, content, status, ip_address) 
                VALUES (?, ?, ?, ?, ?, 'approved', ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$postId, $userId, $name, $email, $content, $ip]);

        \Core\Session::flash('success', 'Comentario publicado con éxito.');
        $this->redirect('/blog/post/' . $post['slug']);
    }

    public function category($slug)
    {
        $db = Database::getInstance()->getConnection();

        // Get category
        $stmt = $db->prepare("SELECT * FROM blog_categories WHERE slug = ?");
        $stmt->execute([$slug]);
        $category = $stmt->fetch();

        if (!$category) {
            $this->redirect('/blog');
        }

        // Get posts in category
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, c.color as category_color, u.name as author_name 
                FROM blog_posts p 
                JOIN blog_categories c ON p.category_id = c.id 
                JOIN users u ON p.author_id = u.id 
                WHERE p.category_id = ? AND p.status = 'published' 
                ORDER BY p.published_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$category['id']]);
        $posts = $stmt->fetchAll();

        $categories = $db->query("SELECT * FROM blog_categories WHERE is_active = 1")->fetchAll();

        $this->viewLayout('public/blog/index', 'public', [
            'title' => 'Categoría: ' . $category['name'] . ' | ' . \Core\Config::get('business.company_name'),
            'posts' => $posts,
            'categories' => $categories,
            'categoryFilter' => $category['slug'],
            'totalPages' => 1 // Category view doesn't have pagination yet for simplicity or you can implement it similarly
        ]);
    }
}
