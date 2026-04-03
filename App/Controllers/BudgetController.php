<?php
namespace App\Controllers;

use Core\Controller;
use Core\Database;
use Core\Auth;
use Core\Session;
use Core\Mail;
use PDO;

class BudgetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('2fa');
    }

    /**
     * Show Budget Creation Form (from Ticket)
     */
    public function create($ticket_id)
    {
        if (Auth::isClient())
            $this->redirect('/dashboard');

        $db = Database::getInstance()->getConnection();

        // Get ticket info
        $stmt = $db->prepare("SELECT t.*, u.name as client_name, sp.name as plan_name, sp.price as plan_price, s.name as service_name, sc.name as category_name
                             FROM tickets t 
                             JOIN users u ON t.client_id = u.id 
                             JOIN service_plans sp ON t.service_plan_id = sp.id 
                             JOIN services s ON sp.service_id = s.id
                             JOIN service_categories sc ON s.category_id = sc.id
                             WHERE t.id = ?");
        $stmt->execute([$ticket_id]);
        $ticket = $stmt->fetch();

        if (!$ticket)
            $this->redirect('/dashboard');

        $this->viewLayout('staff/budgets/create', 'staff', [
            'title' => 'Generar Presupuesto | ' . \Core\Config::get('business.company_name'),
            'ticket' => $ticket
        ]);
    }

    /**
     * Edit Budget (Create new version)
     */
    public function edit($id)
    {
        if (Auth::isClient())
            $this->redirect('/dashboard');

        $db = Database::getInstance()->getConnection();

        $sql = "SELECT b.*, t.ticket_number, u.name as client_name, s.name as service_name
                FROM budgets b 
                JOIN tickets t ON b.ticket_id = t.id 
                JOIN users u ON t.client_id = u.id 
                JOIN service_plans sp ON t.service_plan_id = sp.id 
                JOIN services s ON sp.service_id = s.id
                WHERE b.id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $budget = $stmt->fetch();

        if (!$budget)
            $this->redirect('/dashboard');

        $stmt = $db->prepare("SELECT * FROM budget_items WHERE budget_id = ?");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll();

        $this->viewLayout('staff/budgets/edit', 'staff', [
            'title' => 'Editar Presupuesto v' . ($budget['version'] + 1),
            'budget' => $budget,
            'items' => $items
        ]);
    }

    /**
     * Save new version of budget
     */
    public function updateVersion()
    {
        if (Auth::isClient())
            $this->redirect('/dashboard');

        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();

        try {
            $old_budget_id = $_POST['parent_id'];
            $ticket_id = $_POST['ticket_id'];
            $title = $_POST['title'];
            $service_reference = $_POST['service_reference'] ?? '';
            $scope = $_POST['scope'];
            $timeline = $_POST['timeline_weeks'];
            $items = $_POST['items'];

            // Get parent info
            $stmt = $db->prepare("SELECT version, parent_budget_id FROM budgets WHERE id = ?");
            $stmt->execute([$old_budget_id]);
            $parent = $stmt->fetch();

            $root_id = $parent['parent_budget_id'] ?: $old_budget_id;
            $new_version = $parent['version'] + 1;

            $subtotal = 0;
            foreach ($items as $item) {
                $qty   = is_numeric($item['quantity'] ?? 0) ? (float)$item['quantity'] : 0;
                $price = is_numeric($item['unit_price'] ?? 0) ? (float)$item['unit_price'] : 0;
                $subtotal += ($qty * $price);
            }

            // C-01 FIX: Usar clave correcta del árbol de configuración (business.tax_rate desde .env)
            $tax_rate   = (float) \Core\Config::get('business.tax_rate', 0);
            $tax_amount = $subtotal * ($tax_rate / 100);
            $total      = $subtotal + $tax_amount;
            $budget_number = 'VZL-B' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(2)));

            // 1. Insert New Version
            $sql = "INSERT INTO budgets (budget_number, parent_budget_id, ticket_id, version, title, service_reference, scope, timeline_weeks, subtotal, tax_rate, tax_amount, total, created_by, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'sent')";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $budget_number,
                $root_id,
                $ticket_id,
                $new_version,
                $title,
                $service_reference,
                $scope,
                $timeline,
                $subtotal,
                $tax_rate,
                $tax_amount,
                $total,
                Auth::user()['id']
            ]);
            $new_budget_id = $db->lastInsertId();

            // 2. Insert Items
            $sqlItem = "INSERT INTO budget_items (budget_id, description, quantity, unit_price, total) VALUES (?, ?, ?, ?, ?)";
            $stmtItem = $db->prepare($sqlItem);
            foreach ($items as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $stmtItem->execute([$new_budget_id, $item['description'], $item['quantity'], $item['unit_price'], $itemTotal]);
            }

            // 3. Mark old budget as "rejected" or "overwritten"? 
            // In many CRMs, old versions are just kept as history.
            // Let's mark it as rejected to clarify it's no longer the active one.
            $db->prepare("UPDATE budgets SET status = 'rejected' WHERE id = ? AND status = 'sent'")->execute([$old_budget_id]);

            // 4. Notification to Client
            $stmt = $db->prepare("SELECT u.id, u.email, u.name FROM tickets t JOIN users u ON t.client_id = u.id WHERE t.id = ?");
            $stmt->execute([$ticket_id]);
            $client = $stmt->fetch();

            if ($client) {
                Mail::sendBudgetAvailable($client['email'], $client['name'], $budget_number, $new_budget_id);
                \App\Models\Notification::send($client['id'], 'budget_received', 'Nueva Versión de Presupuesto', "Se ha generado una nueva versión ($new_version) para el presupuesto de '$title'.", '/budget/show/' . $new_budget_id);
            }

            $db->commit();
            Session::flash('success', "Nueva versión (v$new_version) generada con éxito.");
            $this->redirect('/budget/show/' . $new_budget_id);

        } catch (\Exception $e) {
            $db->rollBack();
            error_log('[BudgetController::updateVersion] ' . $e->getMessage());
            Session::flash('error', 'Error al generar la nueva versión del presupuesto. Contacta a soporte.');
            $this->redirect('/dashboard');
            return;
        }
    }

    /**
     * Save Budget
     */
    public function store()
    {
        if (Auth::isClient())
            $this->redirect('/dashboard');

        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();

        try {
            $ticket_id = $_POST['ticket_id'];
            $title = $_POST['title'];
            $service_reference = $_POST['service_reference'] ?? '';
            $scope = $_POST['scope'];
            $timeline = $_POST['timeline_weeks'];
            $items = $_POST['items']; // Array of items

            $subtotal = 0;
            foreach ($items as $item) {
                $qty   = is_numeric($item['quantity'] ?? 0) ? (float)$item['quantity'] : 0;
                $price = is_numeric($item['unit_price'] ?? 0) ? (float)$item['unit_price'] : 0;
                $subtotal += ($qty * $price);
            }

            // C-01 FIX: Usar clave correcta del árbol de configuración (business.tax_rate desde .env)
            $tax_rate   = (float) \Core\Config::get('business.tax_rate', 0);
            $tax_amount = $subtotal * ($tax_rate / 100);
            $total      = $subtotal + $tax_amount;
            $budget_number = 'VZL-B' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(2)));

            // 1. Insert Budget
            $sql = "INSERT INTO budgets (budget_number, ticket_id, title, service_reference, scope, timeline_weeks, subtotal, tax_rate, tax_amount, total, created_by, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'sent')";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $budget_number,
                $ticket_id,
                $title,
                $service_reference,
                $scope,
                $timeline,
                $subtotal,
                $tax_rate,
                $tax_amount,
                $total,
                Auth::user()['id']
            ]);
            $budget_id = $db->lastInsertId();

            // 2. Insert Items
            $sqlItem = "INSERT INTO budget_items (budget_id, description, quantity, unit_price, total) VALUES (?, ?, ?, ?, ?)";
            $stmtItem = $db->prepare($sqlItem);
            foreach ($items as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $stmtItem->execute([$budget_id, $item['description'], $item['quantity'], $item['unit_price'], $itemTotal]);
            }

            // 3. Update Ticket Status
            $db->prepare("UPDATE tickets SET status = 'budget_sent' WHERE id = ?")->execute([$ticket_id]);

            // 4. Notification to Client
            $stmt = $db->prepare("SELECT u.id, u.email, u.name FROM tickets t JOIN users u ON t.client_id = u.id WHERE t.id = ?");
            $stmt->execute([$ticket_id]);
            $client = $stmt->fetch();

            if ($client) {
                Mail::sendBudgetAvailable($client['email'], $client['name'], $budget_number, $budget_id);
                \App\Models\Notification::send($client['id'], 'budget_received', 'Nuevo Presupuesto', "Se ha generado el presupuesto $budget_number para tu solicitud '$title'.", '/budget/show/' . $budget_id);
            }

            $db->commit();
            Session::flash('success', 'Presupuesto generado y enviado al cliente.');
            $this->redirect('/ticket/detail/' . $ticket_id);

        } catch (\Exception $e) {
            $db->rollBack();
            error_log('[BudgetController::store] ' . $e->getMessage());
            Session::flash('error', 'Error al guardar el presupuesto. Contacta a soporte.');
            $this->redirect('/dashboard');
            return;
        }
    }

    /**
     * View Budget (Client or Staff)
     */
    public function show($id)
    {
        $db = Database::getInstance()->getConnection();

        $sql = "SELECT b.*, t.ticket_number, t.client_id, u.name as client_name, u.email as client_email, u.company as client_company 
                FROM budgets b 
                JOIN tickets t ON b.ticket_id = t.id 
                JOIN users u ON t.client_id = u.id 
                WHERE b.id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $budget = $stmt->fetch();

        if (!$budget)
            $this->redirect('/dashboard');

        // Security: Clients can only see their own budgets
        if (Auth::isClient() && $budget['client_id'] != Auth::user()['id']) {
            Session::flash('error', 'No tienes permiso para ver este presupuesto.');
            $this->redirect('/dashboard');
        }

        $stmt = $db->prepare("SELECT * FROM budget_items WHERE budget_id = ?");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll();

        // Get invoice id if exists
        $stmt = $db->prepare("SELECT id FROM invoices WHERE budget_id = ?");
        $stmt->execute([$id]);
        $invoice_id = $stmt->fetchColumn();

        // Get version history
        $rootId = $budget['parent_budget_id'] ?: $budget['id'];
        $stmt = $db->prepare("SELECT id, budget_number, version, status, created_at 
                             FROM budgets 
                             WHERE id = ? OR parent_budget_id = ? 
                             ORDER BY version DESC");
        $stmt->execute([$rootId, $rootId]);
        $history = $stmt->fetchAll();

        $layout = Auth::role();
        $this->viewLayout($layout . '/budgets/view', $layout, [
            'title' => 'Presupuesto: ' . $budget['budget_number'],
            'budget' => $budget,
            'items' => $items,
            'invoice_id' => $invoice_id,
            'history' => $history
        ]);
    }

    /**
     * Client Decision (Approve/Reject)
     */
    public function decision()
    {
        if (!Auth::isClient())
            $this->redirect('/dashboard');

        $budget_id = $_POST['budget_id'];
        $decision = $_POST['decision']; // 'approved' or 'rejected'

        $db = Database::getInstance()->getConnection();

        $status = ($decision == 'approved') ? 'approved' : 'rejected';
        $ticket_status = ($decision == 'approved') ? 'budget_approved' : 'budget_rejected';

        $db->beginTransaction();
        try {
            $stmt = $db->prepare("UPDATE budgets SET status = ?, approved_at = " . ($decision == 'approved' ? "NOW()" : "NULL") . " WHERE id = ?");
            $stmt->execute([$status, $budget_id]);

            // Get ticket_id
            $stmt = $db->prepare("SELECT ticket_id FROM budgets WHERE id = ?");
            $stmt->execute([$budget_id]);
            $ticket_id = $stmt->fetchColumn();

            $db->prepare("UPDATE tickets SET status = ? WHERE id = ?")->execute([$ticket_status, $ticket_id]);

            // AUTOMATION: If approved, generate invoice immediately
            if ($decision == 'approved') {
                $invoiceService = new \App\Services\InvoiceService();
                $result = $invoiceService->createFromBudget($budget_id, Auth::user()['id']);

                if (!$result['success']) {
                    // Log error but don't break the whole flow if invoice fails? 
                    // Actually, for consistency, we might want to know.
                    \App\Services\AuditService::log('auto_invoice_failed', ['budget_id' => $budget_id, 'error' => $result['error']], 'ERROR');
                }
            }

            // Notify Staff/Admin
            $stmt = $db->query("SELECT id FROM users WHERE role IN ('admin', 'staff')");
            $staff = $stmt->fetchAll();
            foreach ($staff as $s) {
                \App\Models\Notification::send($s['id'], 'budget_decision', 'Decisión de Presupuesto', "El cliente ha " . ($decision == 'approved' ? 'aprobado' : 'rechazado') . " el presupuesto.", '/budget/show/' . $budget_id);
            }

            $db->commit();
            $this->redirect('/budget/show/' . $budget_id);
        } catch (\Exception $e) {
            $db->rollBack();
            error_log('[BudgetController::decision] ' . $e->getMessage());
            Session::flash('error', 'Error al procesar la decisión. Por favor intenta nuevamente.');
            $this->redirect('/budget/show/' . $budget_id);
            return;
        }
    }

    /**
     * Export Budget to PDF
     */
    public function exportPdf($id)
    {
        $db = Database::getInstance()->getConnection();

        $sql = "SELECT b.*, t.ticket_number, t.client_id, u.name as client_name, u.email as client_email, u.company as client_company 
                FROM budgets b 
                JOIN tickets t ON b.ticket_id = t.id 
                JOIN users u ON t.client_id = u.id 
                WHERE b.id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $budget = $stmt->fetch();

        if (!$budget)
            $this->redirect('/dashboard');

        // Security: Clients can only see their own budgets
        if (Auth::isClient() && $budget['client_id'] != Auth::user()['id']) {
            Session::flash('error', 'No tienes permiso para descargar este presupuesto.');
            $this->redirect('/dashboard');
        }

        $stmt = $db->prepare("SELECT * FROM budget_items WHERE budget_id = ?");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll();

        // Convert logo to base64 for PDF
        $logoPath = BASE_PATH . '/public/assets/images/logo.png';
        $logoData = "";
        if (file_exists($logoPath)) {
            $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
            $logoContent = file_get_contents($logoPath);
            $logoData = 'data:image/' . $logoType . ';base64,' . base64_encode($logoContent);
        }

        $html = \Core\View::renderToString('pdf/budget', [
            'budget' => $budget,
            'items' => $items,
            'logo_base64' => $logoData
        ]);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("Presupuesto-{$budget['budget_number']}.pdf", ["Attachment" => true]);
        exit;
    }
}
