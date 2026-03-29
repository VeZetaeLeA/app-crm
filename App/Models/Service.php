<?php
namespace App\Models;

use Core\Database;
use Core\Auth;

class Service
{
    /**
     * Get active services for a specific client
     */
    public static function getActiveByClient($client_id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT s.*, p.name as plan_name 
                             FROM active_services s 
                             JOIN service_plans p ON s.service_plan_id = p.id 
                             WHERE s.client_id = ? AND s.status = 'active'
                             ORDER BY s.start_date DESC");
        $stmt->execute([$client_id]);
        return $stmt->fetchAll();
    }

    /**
     * Get all categories with their services
     */
    public static function getCategorized()
    {
        $db = Database::getInstance()->getConnection();
        $categories = $db->query("SELECT * FROM service_categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

        foreach ($categories as &$cat) {
            $stmt = $db->prepare("SELECT * FROM services WHERE category_id = ? AND is_active = 1");
            $stmt->execute([$cat['id']]);
            $cat['services'] = $stmt->fetchAll();
        }

        return $categories;
    }
}
