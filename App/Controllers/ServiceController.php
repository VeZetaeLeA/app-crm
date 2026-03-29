<?php
namespace App\Controllers;

use Core\Controller;
use Core\Database;
use PDO;

class ServiceController extends Controller
{
    public function category($slug)
    {
        $db = Database::getInstance()->getConnection();

        // Get category
        $stmt = $db->prepare("SELECT * FROM service_categories WHERE slug = ? AND is_active = 1");
        $stmt->execute([$slug]);
        $category = $stmt->fetch();

        if (!$category) {
            $this->redirect('/');
        }

        // Get services in this category
        $stmt = $db->prepare("SELECT * FROM services WHERE category_id = ? AND is_active = 1 ORDER BY order_position ASC");
        $stmt->execute([$category['id']]);
        $services = $stmt->fetchAll();

        $this->viewLayout('public/services/category', 'public', [
            'title' => $category['name'] . ' | ' . \Core\Config::get('business.company_name'),
            'category' => $category,
            'services' => $services
        ]);
    }

    public function detail($slug)
    {
        $db = Database::getInstance()->getConnection();

        // Get service
        $stmt = $db->prepare("SELECT s.*, c.name as category_name, c.slug as category_slug, c.image as category_image, c.icon as category_pilar_icon, c.id as category_id
                             FROM services s 
                             JOIN service_categories c ON s.category_id = c.id 
                             WHERE s.slug = ? AND s.is_active = 1");
        $stmt->execute([$slug]);
        $service = $stmt->fetch();

        if (!$service) {
            $this->redirect('/');
        }

        // Get plans for this service
        $stmt = $db->prepare("SELECT * FROM service_plans WHERE service_id = ? AND is_active = 1 ORDER BY order_position ASC, price ASC");
        $stmt->execute([$service['id']]);
        $plans = $stmt->fetchAll();

        $this->viewLayout('public/services/detail', 'public', [
            'title' => $service['name'] . ' | ' . \Core\Config::get('business.company_name'),
            'service' => $service,
            'plans' => $plans
        ]);
    }

    /**
     * AJAX: Get services by category ID
     */
    public function getByCategory($categoryId)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, name, short_description, icon FROM services WHERE category_id = ? AND is_active = 1 ORDER BY order_position ASC");
        $stmt->execute([$categoryId]);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->json($services);
    }

    /**
     * AJAX: Get plans by service ID
     */
    public function getPlans($serviceId)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, name, price, features, is_featured FROM service_plans WHERE service_id = ? AND is_active = 1 ORDER BY order_position ASC, price ASC");
        $stmt->execute([$serviceId]);
        $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decode features JSON if it exists
        foreach ($plans as &$plan) {
            if ($plan['features']) {
                $plan['features'] = json_decode($plan['features'], true);
            }
        }

        $this->json($plans);
    }
}
