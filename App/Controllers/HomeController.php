<?php
namespace App\Controllers;

use Core\Controller;
use Core\Database;
use PDO;

/**
 * Home Controller
 * Handles the public landing page and main user touchpoints.
 */
class HomeController extends Controller
{
    /**
     * Renders the home page with service categories and recent blog posts.
     */
    public function index()
    {
        $db = Database::getInstance()->getConnection();

        // Get service categories
        $categories = \Core\Cache::remember('home_service_categories', 3600, function() use ($db) {
            $stmt = $db->query("SELECT * FROM service_categories WHERE is_active = 1 ORDER BY order_position ASC");
            return $stmt->fetchAll();
        });

        // Get latest 3 blog posts with author and category names
        $latestPosts = \Core\Cache::remember('home_latest_posts', 1800, function() use ($db) {
            $sql = "SELECT p.*, u.name as author_name, c.name as category_name, c.color as category_color 
                    FROM blog_posts p 
                    JOIN users u ON p.author_id = u.id 
                    JOIN blog_categories c ON p.category_id = c.id 
                    WHERE p.status = 'published' 
                    ORDER BY p.published_at DESC LIMIT 3";
            $stmt = $db->query($sql);
            return $stmt->fetchAll();
        });

        // Get blog categories for filtering
        $blogCategories = \Core\Cache::remember('home_blog_categories', 3600, function() use ($db) {
            $sql = "SELECT * FROM blog_categories WHERE is_active = 1 ORDER BY name ASC";
            $stmt = $db->query($sql);
            return $stmt->fetchAll();
        });

        // Obtener datos dinámicos de la landing
        $landingData = \Core\Cache::remember('home_landing_data', 3600, function() use ($db) {
            $stmt = $db->query("SELECT * FROM landing_pillars ORDER BY order_index ASC");
            $pillars = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = [];
            foreach ($pillars as $pillar) {
                $pillarId = $pillar['id'];

                $stmtServices = $db->prepare("SELECT * FROM landing_services WHERE pillar_id = ? ORDER BY order_index ASC");
                $stmtServices->execute([$pillarId]);
                $pillar['services'] = $stmtServices->fetchAll(PDO::FETCH_ASSOC);

                $stmtPlans = $db->prepare("SELECT * FROM landing_plans WHERE pillar_id = ? ORDER BY order_index ASC");
                $stmtPlans->execute([$pillarId]);
                $pillar['plans'] = $stmtPlans->fetchAll(PDO::FETCH_ASSOC);

                $data[$pillar['slug']] = $pillar;
            }
            return $data;
        });

        $siteName = \Core\Config::get('business.company_name');
        $siteName = trim(str_replace('Soluciones Tecnológicas', '', $siteName));

        $this->viewLayout('public/home', 'public', [
            'title' => $siteName,
            'description' => 'Orquestación de negocios y soluciones tecnológicas de alta performance. Business Intelligence, Apps y Sistemas Cloud para empresas de vanguardia.',
            'landingData' => $landingData,
            'isLoggedIn' => \Core\Auth::check(),
            'categories' => $categories,
            'latestPosts' => $latestPosts,
            'blogCategories' => $blogCategories
        ]);
    }
}
