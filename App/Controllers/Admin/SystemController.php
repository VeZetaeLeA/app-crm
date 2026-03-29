<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Database;
use Core\Auth;
use Core\Session;

class SystemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('2fa');
        
        if (!Auth::isAdmin()) {
            $this->redirect('/dashboard');
        }
    }

    /**
     * Show System Settings
     */
    public function settings()
    {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->query("SELECT * FROM app_config ORDER BY config_group, label");
        $configs = $stmt->fetchAll();

        // Group configs by group for the UI
        $grouped = [];
        foreach ($configs as $config) {
            $grouped[$config['config_group']][] = $config;
        }

        $this->viewLayout('admin/system/settings', 'admin', [
            'title' => 'Configuración del Sistema | ' . \Core\Config::get('business.company_name'),
            'configs' => $grouped
        ]);
    }

    /**
     * Update System Settings
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/system/settings');
        }

        $db = Database::getInstance()->getConnection();
        $updates = $_POST['config'] ?? [];

        $db->beginTransaction();
        try {
            $stmt = $db->prepare("UPDATE app_config SET config_value = ? WHERE config_key = ?");
            foreach ($updates as $key => $value) {
                $stmt->execute([$value, $key]);
            }
            $db->commit();
            
            Session::flash('success', 'Configuración actualizada correctamente. Los cambios se aplicarán de inmediato.');
            
            \Core\SecurityLogger::log('system_config_updated', [
                'updated_keys' => array_keys($updates)
            ]);

        } catch (\Exception $e) {
            $db->rollBack();
            Session::flash('error', 'Error al actualizar configuración: ' . $e->getMessage());
        }

        $this->redirect('/admin/system/settings');
    }
}
