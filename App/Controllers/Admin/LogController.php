<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Auth;
use Core\Database;

class LogController extends Controller
{
    public function __construct()
    {
        if (!Auth::can('view_logs')) {
            \Core\Session::flash('error', 'Acceso denegado. Se requieren permisos de Auditoría.');
            $this->redirect('/dashboard');
        }
    }

    /**
     * Show Audit Logs
     */
    public function index()
    {
        $db = Database::getInstance()->getConnection();

        $level = $_GET['level'] ?? null;
        $user_id = $_GET['user_id'] ?? null;
        $years = $_GET['year'] ?? [];
        $months = $_GET['month'] ?? [];

        $sql = "SELECT * FROM audit_logs";
        $params = [];
        $where = [];

        if ($level) {
            $where[] = "level = ?";
            $params[] = $level;
        }

        if ($user_id) {
            $where[] = "user_id = ?";
            $params[] = $user_id;
        }

        if (!empty($years)) {
            $inYears = str_repeat('?,', count($years) - 1) . '?';
            $where[] = "YEAR(created_at) IN ($inYears)";
            $params = array_merge($params, $years);
        }

        if (!empty($months)) {
            $inMonths = str_repeat('?,', count($months) - 1) . '?';
            $where[] = "MONTH(created_at) IN ($inMonths)";
            $params = array_merge($params, $months);
        }

        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY created_at DESC LIMIT 500";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        // Obtener Años y Meses disponibles dinámicamente
        $stmtYears = $db->query("SELECT DISTINCT YEAR(created_at) as year FROM audit_logs ORDER BY year DESC");
        $availableYears = $stmtYears->fetchAll(\PDO::FETCH_COLUMN);

        $stmtMonths = $db->query("SELECT DISTINCT MONTH(created_at) as month FROM audit_logs ORDER BY month ASC");
        $availableMonths = $stmtMonths->fetchAll(\PDO::FETCH_COLUMN);

        $this->viewLayout('admin/logs/index', 'admin', [
            'title' => 'Logs de Auditoría | ' . \Core\Config::get('business.company_name'),
            'logs' => $logs,
            'availableYears' => $availableYears,
            'availableMonths' => $availableMonths
        ]);
    }

    public function exportCsv()
    {
        $db = Database::getInstance()->getConnection();

        $level = $_GET['level'] ?? null;
        $user_id = $_GET['user_id'] ?? null;
        $years = $_GET['year'] ?? [];
        $months = $_GET['month'] ?? [];

        $sql = "SELECT * FROM audit_logs";
        $params = [];
        $where = [];

        if ($level) {
            $where[] = "level = ?";
            $params[] = $level;
        }

        if ($user_id) {
            $where[] = "user_id = ?";
            $params[] = $user_id;
        }

        if (!empty($years)) {
            $inYears = str_repeat('?,', count($years) - 1) . '?';
            $where[] = "YEAR(created_at) IN ($inYears)";
            $params = array_merge($params, $years);
        }

        if (!empty($months)) {
            $inMonths = str_repeat('?,', count($months) - 1) . '?';
            $where[] = "MONTH(created_at) IN ($inMonths)";
            $params = array_merge($params, $months);
        }

        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY created_at DESC LIMIT 10000";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=audit_logs_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Fecha', 'Accion', 'Usuario', 'Nivel', 'IP', 'Metodo', 'Detalles', 'Firma']);

        foreach ($logs as $log) {
            fputcsv($output, [
                $log['created_at'],
                $log['action'],
                $log['user_email'],
                $log['level'],
                $log['ip_address'],
                $log['request_method'],
                $log['details'],
                $log['signature_hash']
            ]);
        }
        fclose($output);
        exit;
    }
}
