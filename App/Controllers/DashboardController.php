<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use PDO;

/**
 * Dashboard Controller
 * Serves the main landing panel after login, routing to specific views based on user role.
 */
class DashboardController extends Controller
{
    private $service;

    /**
     * Initializes the controller and redirects guests to login.
     */
    public function __construct(\App\Services\DashboardService $service)
    {
        $this->middleware('auth');
        $this->middleware('2fa');
        $this->service = $service;
    }

    /**
     * Entry point for the dashboard route. 
     * Determines user role and dispatches to the corresponding private method.
     */
    public function index()
    {
        $role = Auth::role();
        switch ($role) {
            case 'admin':
            case 'super_admin': // Unified handling, will differentiate in view if needed
                $this->admin();
                break;
            case 'staff':
                $this->staff();
                break;
            default:
                $this->client();
        }
    }

    private function client()
    {
        $data = $this->service->getClientDashboardData(Auth::user()['id']);

        $this->viewLayout('client/dashboard', 'client', [
            'title' => 'Panel de Cliente | ' . \Core\Config::get('business.company_name'),
            'services' => $data['services'],
            'tickets' => $data['tickets'],
            'unpaid_count' => $data['unpaid_count']
        ]);
    }

    private function staff()
    {
        $tickets = $this->service->getStaffDashboardData(Auth::user()['id']);

        $this->viewLayout('staff/dashboard', 'staff', [
            'title' => 'Panel Staff | ' . \Core\Config::get('business.company_name'),
            'tickets' => $tickets
        ]);
    }

    private function admin()
    {
        $stats = $this->service->getAdminStats();
        $dailyData = $this->service->getDailyPerformance();
        $monthlyData = $this->service->getMonthlyPerformance();
        $resourceStats = $this->service->getResourceDistribution();
        $widgetConfig = $this->service->getWidgetConfig(Auth::user()['id']);

        $financialStats = $this->service->getFinancials();
        $recent_tickets = $this->service->getRecentTicketsWithClients(10);

        $this->viewLayout('admin/dashboard', 'admin', [
            'title' => 'Cerebro Central Admin | ' . \Core\Config::get('business.company_name'),
            'stats' => $stats,
            'daily_perf' => $dailyData,
            'monthly_perf' => $monthlyData,
            'resource_stats' => $resourceStats,
            'financial' => $financialStats,
            'tickets' => $recent_tickets,
            'widget_config' => $widgetConfig
        ]);
    }

    /**
     * Updates only the widget order via AJAX.
     */
    public function saveOrder()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid method']);
            return;
        }

        $order = $_POST['order'] ?? []; // Array of widget keys in order
        if (empty($order)) {
            $this->json(['success' => false, 'message' => 'No order data']);
            return;
        }

        $userId = Auth::user()['id'];
        $currentConfig = $this->service->getWidgetConfig($userId);

        $payload = [];
        foreach ($order as $index => $key) {
            if (isset($currentConfig[$key])) {
                $payload[$key] = [
                    'is_visible' => $currentConfig[$key]['is_visible'],
                    'sort_order' => $index + 1
                ];
            }
        }

        $result = $this->service->updateWidgetConfig($userId, $payload);
        $this->json(['success' => $result]);
    }

    /**
     * Updates the dashboard widget configuration.
     */
    public function updateConfig()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/dashboard');
        }

        $widgets = $_POST['widgets'] ?? [];
        // Extract only the visible ones, as checkboxes only send value if checked
        $allWidgets = [
            'stats_cards',
            'performance_chart',
            'resource_dist',
            'bi_indicators',
            'recent_tickets',
            'insight_alerts',
            'financial_metrics'
        ];

        $payload = [];
        foreach ($allWidgets as $key) {
            $payload[$key] = [
                'is_visible' => isset($widgets[$key]['is_visible']) ? 1 : 0,
                'sort_order' => (int) ($widgets[$key]['sort_order'] ?? 0)
            ];
        }

        $result = $this->service->updateWidgetConfig(Auth::user()['id'], $payload);

        if ($result) {
            \Core\Session::flash('success', 'Configuración del dashboard actualizada.');
        } else {
            \Core\Session::flash('error', 'Error al actualizar la configuración.');
        }

        $this->redirect('/dashboard');
    }

    /**
     * Urgent Support Redirect for Client Dashboard
     */
    public function urgentSupport()
    {
        $user = Auth::user();

        // Use service to find latest ticket for this client
        $ticket_id = $this->service->getLatestTicketId($user['id']);

        if (!$ticket_id) {
            \Core\Session::flash('error', 'No tienes requerimientos activos para iniciar un chat de soporte.');
            $this->redirect('/ticket/request');
            return;
        }

        // Send URGENT Email using Professional Template
        $adminEmail = \Core\Config::get('mail.from_address', getenv('COMPANY_MAIL') ?: 'contacto@tuempresa.com');
        \Core\Mail::sendUrgentSupport($adminEmail, $user['name'], $user['email'], $ticket_id);

        // Insert system auto-reply in the ticket chat (only if no system msg in last 5 min)
        $db = \Core\Database::getInstance()->getConnection();
        $recentCheck = $db->prepare("SELECT COUNT(*) FROM chat_messages WHERE ticket_id = ? AND user_id IS NULL AND message_type = 'system' AND created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
        $recentCheck->execute([$ticket_id]);
        if ((int) $recentCheck->fetchColumn() === 0) {
            $firstName = explode(' ', $user['name'])[0];
            $autoMsg = "🔔 Soporte Prioritario Activado\n\n¡Hola {$firstName}! Recibimos tu señal. Nuestro equipo ya fue notificado y se conectará contigo en breve.\n\nMientras tanto, cuéntanos con un poco más de detalle:\n• ¿Qué está ocurriendo exactamente?\n• ¿Tiene impacto en producción o en una fecha crítica?\n• ¿Ya intentaste algo para resolverlo?\n\nCuanto más detalle compartas aquí, más rápido podremos actuar. ⚡\n\nTiempo de respuesta estimado: < 30 minutos en horario laboral.";
            $db->prepare("INSERT INTO chat_messages (ticket_id, user_id, message, message_type) VALUES (?, NULL, ?, 'system')")
                ->execute([$ticket_id, $autoMsg]);
        }

        \Core\Session::flash('info', 'Solicitud de soporte urgente enviada. Un especialista se conectará pronto.');
        $this->redirect('/ticket/detail/' . $ticket_id);
    }
}
