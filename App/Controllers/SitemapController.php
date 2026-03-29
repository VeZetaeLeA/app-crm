<?php
namespace App\Controllers;

use Core\Controller;
use Core\Database;

class SitemapController extends Controller
{
    /**
     * Generates dynamic sitemap.xml
     */
    public function index()
    {
        $db = Database::getInstance()->getConnection();
        $urls = [];
        $baseUrl = url();

        // 1. Static Pages
        $urls[] = ['loc' => $baseUrl, 'priority' => '1.0', 'changefreq' => 'daily'];
        $urls[] = ['loc' => $baseUrl . '/blog', 'priority' => '0.8', 'changefreq' => 'weekly'];

        // 2. Blog Posts
        $stmt = $db->query("SELECT slug, updated_at FROM blog_posts WHERE is_published = 1");
        while ($post = $stmt->fetch()) {
            $urls[] = [
                'loc' => $baseUrl . '/blog/view/' . $post['slug'],
                'lastmod' => date('Y-m-d', strtotime($post['updated_at'])),
                'priority' => '0.7',
                'changefreq' => 'monthly'
            ];
        }

        // 3. Service Categories
        $stmt = $db->query("SELECT slug FROM service_categories WHERE is_active = 1");
        while ($cat = $stmt->fetch()) {
            $urls[] = [
                'loc' => $baseUrl . '/service/category/' . $cat['slug'],
                'priority' => '0.9',
                'changefreq' => 'weekly'
            ];
        }

        header("Content-Type: application/xml; charset=utf-8");
        
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        foreach ($urls as $url) {
            echo '<url>';
            echo '<loc>' . htmlspecialchars($url['loc']) . '</loc>';
            if (isset($url['lastmod'])) {
                echo '<lastmod>' . $url['lastmod'] . '</lastmod>';
            }
            echo '<changefreq>' . $url['changefreq'] . '</changefreq>';
            echo '<priority>' . $url['priority'] . '</priority>';
            echo '</url>';
        }

        echo '</urlset>';
        exit;
    }
}
