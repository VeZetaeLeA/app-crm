<?php
namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Models\User;
use Core\Session;
use Core\Auth;
use Core\Mail;
use App\Services\AIService;
use App\Services\RealTimeService;
use PDO;
use App\Repositories\TicketRepository;

class TicketController extends Controller
{
    private $ticketRepo;

    public function __construct()
    {
        $this->ticketRepo = new TicketRepository(Database::getInstance()->getConnection());
        $this->middleware('auth', [], [], ['request', 'submit', 'received']);
        $this->middleware('2fa', [], [], ['request', 'submit', 'received']);
    }

    /**
     * List Tickets (Admin/Staff sees all, Client sees own)
     */
    public function index()
    {
        $filters = [];
        if (Auth::isClient()) {
            $filters['client_id'] = Auth::user()['id'];
        }

        $tickets = $this->ticketRepo->getAll($filters);
        $isClient = Auth::isClient();

        // 🧠 Intelligence Services Integration
        $intelligence = new \App\Services\CRM\IntelligenceService();
        $leadService = new \App\Services\CRM\LeadService();

        foreach ($tickets as &$t) {
            // Predictivve Delay Risk
            $riskData = $intelligence->calculateDelayRisk($t);
            $t['is_at_risk'] = $riskData['is_at_risk'];
            $t['risk_reason'] = $riskData['risk_reason'];

            // Lead Intelligence Score (Only for Admin/Staff)
            if (!$isClient) {
                $clientId = $t['client_id'] ?? null;
                $t['lead_score'] = $clientId ? $leadService->calculateScore($clientId) : 0;
            }
        }

        $view = Auth::role() . '/tickets/index';
        $this->viewLayout($view, Auth::role(), [
            'title' => 'Gestión de Tickets | ' . \Core\Config::get('business.company_name'),
            'tickets' => $tickets
        ]);
    }

    /**
     * Kanban View (SPRINT 2.3)
     */
    public function kanban()
    {
        if (Auth::isClient())
            $this->redirect('/ticket');

        $tickets = $this->ticketRepo->getAll();

        $intelligence = new \App\Services\CRM\IntelligenceService();
        $leadService  = new \App\Services\CRM\LeadService();

        foreach ($tickets as &$t) {
            $riskData      = $intelligence->calculateDelayRisk($t);
            $t['is_at_risk']  = $riskData['is_at_risk'];
            $t['risk_reason'] = $riskData['risk_reason'];
            $t['lead_score']  = $leadService->calculateScore($t['client_id'] ?? 0);
        }

        $this->viewLayout(Auth::role() . '/tickets/kanban', Auth::role(), [
            'title'   => 'Kanban de Tickets | ' . \Core\Config::get('business.company_name'),
            'tickets' => $tickets
        ]);
    }

    /**
     * Public Service Request Form (Guided Flow)
     */
    public function request()
    {
        $db = Database::getInstance()->getConnection();
        
        // Step 1 needs categories to start the guided flow
        $stmt = $db->query("SELECT * FROM service_categories WHERE is_active = 1 ORDER BY order_position ASC");
        $categories = $stmt->fetchAll();

        $this->viewLayout('public/tickets/request', 'public', [
            'title' => 'Solicitud de Servicio | ' . \Core\Config::get('business.company_name'),
            'categories' => $categories
        ]);
    }

    /**
     * Submit Public Request
     */
    public function submit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/');
        }

        // Rate Limiting: Max 5 tickets per hour per IP
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!\Core\RateLimiter::attempt('ticket_submit_ip_' . $ip, 5, 3600)) {
            // M-08 FIX: Mensaje de rate limit preciso
            Session::flash('error', 'Has alcanzado el límite de 5 solicitudes por hora. Por favor, espera antes de enviar otra.');
            $this->redirect('/ticket/request');
            return;
        }

        $isHoneypotEnabled = \Core\Config::get('security.honeypot_enabled', true);
        if ($isHoneypotEnabled) {
            // Drop silencioso si el robot llenó el honeypot
            if (!empty($_POST['_vzl_security_trap'])) {
                \Core\SecurityLogger::log('bot_detected_honeypot', ['ip' => $ip, 'email' => $_POST['email'] ?? ''], 'WARN');
                Session::flash('success', '¡Solicitud recibida! Hemos enviado detalles a tu correo.');
                $this->redirect('/');
                return;
            }
            
            // Drop silencioso si el formulario fue enviado anormalmente rápido (menos de X segundos)
            $loadTime = (int)($_POST['_vzl_load_time'] ?? 0);
            $minTime = \Core\Config::get('security.min_form_time', 3);
            if (time() - $loadTime < $minTime) {
                \Core\SecurityLogger::log('bot_detected_speed', ['ip' => $ip, 'time' => time() - $loadTime], 'WARN');
                Session::flash('success', '¡Solicitud recibida! Hemos enviado detalles a tu correo.');
                $this->redirect('/');
                return;
            }
        }

        // M-06 FIX: Validación server-side de campos críticos (Reforzada con single_email)
        $validator = new \Core\Validator();
        $validator->validate($_POST, [
            'name'        => 'required|min:2|max:100',
            'email'       => 'required|single_email|email',
            'subject'     => 'required|min:5|max:200',
            'description' => 'required|min:10|max:3000',
        ]);
        if ($validator->fails()) {
            \Core\SecurityLogger::log('ticket_validation_failed', [
                'errors' => $validator->errors(),
                'ip' => $ip,
                'email' => $_POST['email'] ?? 'N/A'
            ], 'WARN');
            $firstErrors = array_map(function($e) { return $e[0]; }, $validator->errors());
            Session::flash('error', implode(' ', $firstErrors));
            $this->redirect('/ticket/request');
            return;
        }

        // --- M-07 FIX: reCAPTCHA v3 Server-Side Validation ---
        $recaptchaSecret = \Core\Config::get('security.recaptcha_secret_key');
        if (!empty($recaptchaSecret)) {
            $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

            if (empty($recaptchaResponse)) {
                \Core\SecurityLogger::log('bot_detected_recaptcha_missing', ['ip' => $ip, 'email' => $_POST['email'] ?? ''], 'WARN');
                Session::flash('error', 'Validación de seguridad requerida.');
                $this->redirect('/ticket/request');
                return;
            }

            // Verify with Google (v3) - Using cURL POST (Standard)
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $postData = [
                'secret'   => $recaptchaSecret,
                'response' => $recaptchaResponse
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Compatibility with some local setups
            $verifyResponse = curl_exec($ch);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($verifyResponse === false) {
                \Core\SecurityLogger::log('recaptcha_curl_error', ['error' => $curlError, 'ip' => $ip], 'ERROR');
                Session::flash('error', 'Error de conexión con el servicio de seguridad. Reintente.');
                $this->redirect('/ticket/request');
                return;
            }

            $responseData = json_decode($verifyResponse);
            $scoreLimit = (float)\Core\Config::get('security.recaptcha_score', 0.5);

            $success = $responseData->success ?? false;
            $score = (float)($responseData->score ?? 0);
            $action = $responseData->action ?? '';

            if (!$success || $score < $scoreLimit || (!empty($action) && $action !== 'ticket_request')) {
                \Core\SecurityLogger::log('bot_detected_recaptcha_fail', [
                    'ip' => $ip, 
                    'score' => $score, 
                    'action_expected' => 'ticket_request',
                    'action_received' => $action,
                    'success' => $success,
                    'error_codes' => $responseData->{'error-codes'} ?? [],
                    'response_raw' => $verifyResponse
                ], 'WARN');
                
                Session::flash('error', 'Nuestros sistemas detectaron actividad inusual (Bot risk: ' . $score . '). Intenta de nuevo.');
                $this->redirect('/ticket/request');
                return;
            }
        }

        $db = Database::getInstance()->getConnection();
        $userModel = new User();

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $company = $_POST['company'] ?? '';
        $subject = $_POST['subject'] ?? '';
        $service_id = $_POST['service_id'] ?? null;
        $plan_id = $_POST['service_plan_id'] ?? null;
        $description = $_POST['description'] ?? '';

        // Fallback: If service_id is provided but no specific plan_id, map to the first available plan
        if (!$plan_id && $service_id) {
            $stmt = $db->prepare("SELECT id FROM service_plans WHERE service_id = ? ORDER BY price ASC LIMIT 1");
            $stmt->execute([$service_id]);
            $plan_id = $stmt->fetchColumn() ?: null;
        }

        // Final check: service_plan_id is required by the database
        if (!$plan_id) {
            Session::flash('error', 'Debes seleccionar un servicio o plan válido.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
            return;
        }

        $user = $userModel->findByEmail($email);
        $isNewUser = false;
        if (!$user) {
            $isNewUser = true;
            $tempPass = bin2hex(random_bytes(4));
            $userModel->create([
                'name' => $name,
                'email' => $email,
                'password' => $tempPass,
                'role' => 'client',
                'company' => $company,
                'phone' => $phone
            ]);
            $user = $userModel->findByEmail($email);

            // Send Welcome Email
            Mail::sendWelcome($email, $name, $tempPass);
        }

        // SLA Calculation (Sprint 5.1)
        $slaStmt = $db->prepare("SELECT sc.sla_hours 
                                FROM service_plans sp 
                                JOIN services s ON sp.service_id = s.id 
                                JOIN service_categories sc ON s.category_id = sc.id 
                                WHERE sp.id = ?");
        $slaStmt->execute([$plan_id]);
        $slaHours = $slaStmt->fetchColumn() ?: 48;
        $slaDeadline = date('Y-m-d H:i:s', strtotime("+{$slaHours} hours"));

        $ticket_number = 'TKT-' . strtoupper(bin2hex(random_bytes(3)));
        
        $lastTicketId = $this->ticketRepo->createTicket([
            'ticket_number' => $ticket_number,
            'client_id' => $user['id'],
            'service_plan_id' => $plan_id,
            'subject' => $subject,
            'description' => $description,
            'status' => 'open',
            'sla_deadline' => $slaDeadline
        ]);

        if ($lastTicketId) {
            
            // Notification: Ticket Received (PRD v1.0 - Use professional template)
            Mail::sendRequestConfirmation($email, $name, $ticket_number, $subject);

            \Core\SecurityLogger::log('ticket_created', [
                'ticket_number' => $ticket_number,
                'email' => $email,
                'subject' => $subject
            ]);

            // Notify Staff/Admin
            $staff = \App\Models\User::getStaffAndAdmins();
            foreach ($staff as $s) {
                \App\Models\Notification::send($s['id'], 'new_ticket', 'Nueva Solicitud', "Nueva solicitud recibida: $subject de $name.", '/ticket/detail/' . $lastTicketId);
            }

            Session::flash('success', '¡Solicitud recibida! Hemos enviado detalles a tu correo.');

            // 🤖 GAI: Análisis y Action Items (SPRINT 3 - E11-007, E11-009)
            $aiService = new \App\Services\AIService();
            if ($aiService->isEnabled()) {
                // GAI-04: Sentimiento y Análisis Profundo
                $analysis = $aiService->analyzeTicketContent($description);
                if ($analysis) {
                    $this->ticketRepo->updateAiAnalysis($lastTicketId, $analysis['sentiment'] ?? 'neutral', $analysis);

                    // Si la IA sugiere prioridad diferente, actualizarla
                    if (isset($analysis['priority']) && in_array($analysis['priority'], ['low','normal','high','urgent'])) {
                        $this->ticketRepo->updatePriority($lastTicketId, $analysis['priority']);
                    }
                }

                // GAI-02: Extracción de Action Items (E11-007)
                $tasks = $aiService->extractActionItems($description);

                // IMPORTANTE: Insertamos la descripción inicial como primer mensaje del chat para contexto de IA
                $this->ticketRepo->createMessage($lastTicketId, $user['id'], $description, 'client');

                if ($tasks && is_array($tasks)) {
                    foreach ($tasks as $task) {
                        $this->ticketRepo->createTask($lastTicketId, $task);
                    }
                    
                    // Mensaje de sistema (Unificado)
                    $sysMsg = "🤖 Vezi Copilot ha analizado tu requerimiento.\nSentimiento detectado: " . ($analysis['sentiment'] ?? 'neutral') . ".\nHe sugerido " . count($tasks) . " tareas iniciales.";
                    $this->ticketRepo->createMessage($lastTicketId, null, $sysMsg, 'system');
                }
            }

            // AUTO-LOGIN: Set user in session so they can access the dashboard immediately
            if (!headers_sent()) {
                session_regenerate_id(true);
            }
            Session::set('user', $user);

            $this->redirect('/quote/received');
        } else {
            Session::flash('error', 'Ocurrió un error al procesar tu solicitud.');
            $this->redirect('/ticket/request');
        }
    }

    /**
     * Internal Ticket Detail
     */
    public function detail($id)
    {
        $db = \Core\Database::getInstance()->getConnection();
        $ticket = $this->ticketRepo->getById($id);

        if (!$ticket)
            $this->redirect('/dashboard');

        // Security: clients can only see their own tickets
        if (Auth::isClient() && $ticket['client_id'] != Auth::user()['id']) {
            $this->redirect('/dashboard');
        }

        // Get chat messages via Repo
        $messages = $this->ticketRepo->getMessages($id);

        // Get budget if exists
        $stmt = $db->prepare("SELECT * FROM budgets WHERE ticket_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$id]);
        $budget = $stmt->fetch();

        // Get invoice if budget exists
        $invoice = null;
        if ($budget) {
            $stmt = $db->prepare("SELECT * FROM invoices WHERE budget_id = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$budget['id']]);
            $invoice = $stmt->fetch();
        }

        // Get AI Action Items
        $tasks = $this->ticketRepo->getTasks($id);

        $layout = Auth::role();
        $this->viewLayout(Auth::role() . '/tickets/detail', $layout, [
            'title' => 'Detalle de Ticket: ' . $ticket['ticket_number'],
            'ticket' => $ticket,
            'messages' => $messages,
            'budget' => $budget,
            'invoice' => $invoice,
            'tasks' => $tasks
        ]);
    }

    /**
     * Update Ticket Status (AJAX or Form)
     */
    public function updateStatus()
    {
        if (Auth::isClient()) {
            $this->json(['success' => false, 'message' => 'Acción no permitida.']);
            return;
        }

        $id = $_POST['ticket_id'] ?? null;
        $status = $_POST['status'] ?? '';

        // C-03 FIX: Whitelist de estados válidos para prevenir inyección de valores arbitrarios
        $allowedStatuses = ['open', 'in_progress', 'budget_sent', 'budget_approved',
                            'budget_rejected', 'resolved', 'closed', 'cancelled'];
        if (!$id || !in_array($status, $allowedStatuses, true)) {
            $this->json(['success' => false, 'message' => 'Estado o ticket inválido.']);
            return;
        }

        // Get info via Repo
        $info = $this->ticketRepo->getById($id);
        if (!$info) return;

        // Auto-assign
        if (empty($info['assigned_to'])) {
            $this->ticketRepo->assignTicket($id, Auth::user()['id']);
        }

        $result = $this->ticketRepo->updateStatus($id, $status);

        if ($result) {
            // Send Notification (email client)
            Mail::sendTicketUpdate($info['client_email'], $info['ticket_number'], $status);

            \Core\SecurityLogger::log('ticket_status_changed', [
                'ticket_id'     => $id,
                'ticket_number' => $info['ticket_number'],
                'new_status'    => $status
            ]);

            // Notify Client (internal)
            \App\Models\Notification::send($info['client_id'], 'ticket_update', 'Actualizacion de Ticket', "Tu ticket " . $info['ticket_number'] . " ha cambiado a estado: " . translateStatus($status), '/ticket/detail/' . $id);

            // Real-Time Broadcast
            \App\Services\RealTimeService::broadcast('ticket_status_update', [
                'ticket_id'     => $id,
                'ticket_number' => $info['ticket_number'],
                'status'        => translateStatus($status)
            ]);

            // AJAX support
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'new_status' => $status]);
                exit;
            }

            $this->redirect('/ticket/detail/' . $id);
        }
    }

    /**
     * Export tickets to CSV
     */
    public function exportCsv()
    {
        $filters = [];
        if (Auth::isClient()) {
            $filters['client_id'] = Auth::user()['id'];
        }

        $tickets = $this->ticketRepo->getAll($filters);

        // Map for CSV
        $reportData = array_map(function($t) {
            $row = [
                'Ticket #' => $t['ticket_number'],
                'Asunto' => $t['subject'],
                'Estado' => translateStatus($t['status']),
                'Prioridad' => $t['priority'],
                'Fecha' => $t['created_at'],
                'Plan' => $t['plan_name']
            ];
            if (!Auth::isClient()) {
                $row = array_merge(['Cliente' => $t['client_name']], $row);
            }
            return $row;
        }, $tickets);

        $headers = array_keys($reportData[0] ?? []);
        \App\Utils\CsvExporter::export('tickets_' . date('Ymd'), $headers, $reportData);
    }
}
