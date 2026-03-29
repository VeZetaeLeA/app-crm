<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Database;
use Core\Auth;
use Core\Session;
use PDO;

/**
 * Service CMS Controller
 * Handles CRUD operations for Services and Service Categories in the Admin Panel.
 */
class ServiceCMSController extends Controller
{
    /**
     * Initializes the controller, enforcing admin-only access via Middleware.
     */
    public function __construct()
    {
        if (!\Core\Auth::can('manage_services')) {
            \Core\Session::flash('error', 'Acceso denegado. Se requieren permisos de Gestión de Servicios.');
            $this->redirect('/dashboard');
        }
    }

    /**
     * Displays the main dashboard for listing categories and services.
     * 
     * @return void
     */
    public function index()
    {
        $db = Database::getInstance()->getConnection();

        $categories = $db->query("SELECT * FROM service_categories ORDER BY name ASC")->fetchAll();
        $services = $db->query("SELECT s.*, c.name as category_name FROM services s JOIN service_categories c ON s.category_id = c.id ORDER BY s.name ASC")->fetchAll();

        $this->viewLayout('admin/services/index', 'admin', [
            'title' => 'Gestión de Servicios | ' . \Core\Config::get('business.company_name'),
            'categories' => $categories,
            'services' => $services
        ]);
    }

    public function editService($id)
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $service = $stmt->fetch();

        if (!$service)
            $this->redirect('/admin/services');

        $categories = $db->query("SELECT * FROM service_categories")->fetchAll();

        // Get plans sorted by order_position
        $stmt = $db->prepare("SELECT * FROM service_plans WHERE service_id = ? ORDER BY order_position ASC, id ASC");
        $stmt->execute([$id]);
        $plans = $stmt->fetchAll();

        $this->viewLayout('admin/services/edit', 'admin', [
            'title' => 'Editar Servicio: ' . $service['name'],
            'service' => $service,
            'categories' => $categories,
            'plans' => $plans
        ]);
    }

    public function updateService()
    {
        $db = Database::getInstance()->getConnection();
        $id = $_POST['id'];
        $name = $_POST['name'];
        $category_id = $_POST['category_id'];
        $short_description = $_POST['short_description'];
        $full_description = $_POST['full_description'];
        $icon = $_POST['icon'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $sql = "UPDATE services SET name = ?, category_id = ?, short_description = ?, full_description = ?, icon = ?, is_active = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$name, $category_id, $short_description, $full_description, $icon, $is_active, $id]);

        if ($result) {
            Session::flash('success', 'Servicio actualizado correctamente.');
        }

        $this->redirect('/admin/services/edit/' . $id);
    }

    public function updatePlan()
    {
        $db = Database::getInstance()->getConnection();
        $id = $_POST['plan_id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $features = json_encode(explode("\n", str_replace("\r", "", $_POST['features'])));
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        $sql = "UPDATE service_plans SET name = ?, price = ?, features = ?, is_featured = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$name, $price, $features, $is_featured, $id]);

        if ($result) {
            Session::flash('success', 'Plan actualizado.');
        }

        $stmt = $db->prepare("SELECT service_id FROM service_plans WHERE id = ?");
        $stmt->execute([$id]);
        $service_id = $stmt->fetchColumn();

        $this->redirect('/admin/services/edit/' . $service_id);
    }

    public function storePlan()
    {
        $db = Database::getInstance()->getConnection();
        $service_id = $_POST['service_id'];
        $name = $_POST['name'] ?: 'Nuevo Plan';
        $price = $_POST['price'] ?: 0;
        $features = json_encode([]);

        // Get max order_position
        $maxPos = $db->query("SELECT MAX(order_position) FROM service_plans WHERE service_id = " . (int) $service_id)->fetchColumn() ?: 0;
        $order_position = $maxPos + 1;

        $sql = "INSERT INTO service_plans (service_id, name, price, features, order_position) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$service_id, $name, $price, $features, $order_position]);

        if ($result) {
            Session::flash('success', 'Nuevo plan añadido correctamente.');
        } else {
            Session::flash('error', 'Error al añadir el plan.');
        }

        $this->redirect('/admin/services/edit/' . $service_id);
    }

    public function deletePlan($id)
    {
        $db = Database::getInstance()->getConnection();

        // Get service_id before deleting
        $stmt = $db->prepare("SELECT service_id FROM service_plans WHERE id = ?");
        $stmt->execute([$id]);
        $service_id = $stmt->fetchColumn();

        if ($service_id) {
            $stmt = $db->prepare("DELETE FROM service_plans WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result) {
                Session::flash('success', 'Plan eliminado.');
            }
        }

        $this->redirect('/admin/services/edit/' . $service_id);
    }

    /**
     * Reorders a plan by swapping its order_position with its neighbor.
     * 
     * @param int $id The plan ID.
     * @param string $direction 'up' or 'down'.
     */
    public function reorderPlan($id, $direction)
    {
        $db = Database::getInstance()->getConnection();

        // Get current plan data
        $stmt = $db->prepare("SELECT id, service_id, order_position FROM service_plans WHERE id = ?");
        $stmt->execute([$id]);
        $currentPlan = $stmt->fetch();

        if (!$currentPlan) {
            $this->redirect('/admin/services');
        }

        $service_id = $currentPlan['service_id'];
        $currentPos = $currentPlan['order_position'];

        if ($direction === 'up') {
            // Find the plan just above this one
            $stmt = $db->prepare("SELECT id, order_position FROM service_plans WHERE service_id = ? AND order_position < ? ORDER BY order_position DESC LIMIT 1");
            $stmt->execute([$service_id, $currentPos]);
        } else {
            // Find the plan just below this one
            $stmt = $db->prepare("SELECT id, order_position FROM service_plans WHERE service_id = ? AND order_position > ? ORDER BY order_position ASC LIMIT 1");
            $stmt->execute([$service_id, $currentPos]);
        }

        $neighbor = $stmt->fetch();

        if ($neighbor) {
            // Swap positions
            $db->beginTransaction();
            try {
                $stmt = $db->prepare("UPDATE service_plans SET order_position = ? WHERE id = ?");
                $stmt->execute([$neighbor['order_position'], $currentPlan['id']]);
                $stmt->execute([$currentPlan['order_position'], $neighbor['id']]);
                $db->commit();
                Session::flash('success', 'Orden actualizado.');
            } catch (\Exception $e) {
                $db->rollBack();
                Session::flash('error', 'Error al actualizar el orden.');
            }
        }

        $this->redirect('/admin/services/edit/' . $service_id);
    }

    public function storeCategory()
    {
        $db = Database::getInstance()->getConnection();
        $name = $_POST['name'];
        $icon = $_POST['icon'] ?: 'folder';
        $description = $_POST['description'] ?: '';

        // Generate slug
        $slugParam = $_POST['slug'] ?? '';
        $slug = !empty($slugParam)
            ? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $slugParam)))
            : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

        $image = '';

        // Hardened Image Upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $errors = \Core\Validator::validateFile($_FILES['image'], 5 * 1024 * 1024, ['png', 'jpg', 'jpeg']);

            if (empty($errors)) {
                $uploadDir = BASE_PATH . '/public/assets/images/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileName = "pillar_" . $slug . ".png";
                $uploadFile = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                    $image = "assets/images/" . $fileName;
                }
            } else {
                Session::flash('error', 'Error en imagen: ' . implode(', ', $errors));
            }
        }

        $sql = "INSERT INTO service_categories (name, slug, icon, description, image) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$name, $slug, $icon, $description, $image]);

        if ($result) {
            Session::flash('success', 'Categoría creada correctamente.');
        }

        $this->redirect('/admin/services');
    }

    public function updateCategory()
    {
        $db = Database::getInstance()->getConnection();
        $id = $_POST['id'];
        $name = $_POST['name'];
        $icon = $_POST['icon'] ?: 'folder';
        $description = $_POST['description'] ?: '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Configurar el nuevo slug manualmente
        $slugParam = $_POST['slug'] ?? '';
        $new_slug = !empty($slugParam)
            ? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $slugParam)))
            : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

        $image = $_POST['existing_image'] ?? '';

        // Hardened Image Upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $errors = \Core\Validator::validateFile($_FILES['image'], 5 * 1024 * 1024, ['png', 'jpg', 'jpeg']);

            if (empty($errors)) {
                $uploadDir = BASE_PATH . '/public/assets/images/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileName = "pillar_" . $new_slug . ".png";
                $uploadFile = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                    $image = "assets/images/" . $fileName;
                }
            } else {
                Session::flash('error', 'Error en imagen: ' . implode(', ', $errors));
            }
        }

        $sql = "UPDATE service_categories SET name = ?, slug = ?, icon = ?, description = ?, image = ?, is_active = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$name, $new_slug, $icon, $description, $image, $is_active, $id]);

        if ($result) {
            Session::flash('success', 'Categoría actualizada correctamente.');
        }

        $this->redirect('/admin/services');
    }

    public function storeService()
    {
        $db = Database::getInstance()->getConnection();
        $name = $_POST['name'];
        $category_id = $_POST['category_id'];
        $short_description = $_POST['short_description'];
        $icon = $_POST['icon'] ?: 'bolt';

        $sql = "INSERT INTO services (name, category_id, short_description, full_description, icon, is_active) VALUES (?, ?, ?, ?, ?, 0)";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$name, $category_id, $short_description, $short_description, $icon]);

        if ($result) {
            $service_id = $db->lastInsertId();
            // Create default plans
            $plans = [
                ['name' => 'Básico', 'price' => 0],
                ['name' => 'Pro', 'price' => 0],
                ['name' => 'Enterprise', 'price' => 0]
            ];
            $i = 1;
            foreach ($plans as $p) {
                $stmt = $db->prepare("INSERT INTO service_plans (service_id, name, price, features, order_position) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$service_id, $p['name'], $p['price'], json_encode([]), $i++]);
            }
            Session::flash('success', 'Servicio creado. Ahora puedes configurar sus detalles y planes.');
            $this->redirect('/admin/services/edit/' . $service_id);
        } else {
            $this->redirect('/admin/services');
        }
    }

    public function deleteService($id)
    {
        $db = Database::getInstance()->getConnection();

        // Delete plans first
        $stmt = $db->prepare("DELETE FROM service_plans WHERE service_id = ?");
        $stmt->execute([$id]);

        // Delete service
        $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
        $result = $stmt->execute([$id]);

        if ($result) {
            Session::flash('success', 'Servicio eliminado.');
        }

        $this->redirect('/admin/services');
    }
}
