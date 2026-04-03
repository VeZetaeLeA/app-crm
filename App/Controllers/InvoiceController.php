<?php
namespace App\Controllers;

use Core\Controller;
use Core\Database;
use Core\Auth;
use Core\Session;
use PDO;

use App\Repositories\InvoiceRepository;

class InvoiceController extends Controller
{
    private $invoiceRepo;

    public function __construct()
    {
        $this->invoiceRepo = new InvoiceRepository(Database::getInstance()->getConnection());
        $this->middleware('auth');
        $this->middleware('2fa');
    }

    /**
     * List Invoices (Client sees own)
     */
    public function index()
    {
        $filters = [];
        if (Auth::isClient()) {
            $filters['client_id'] = Auth::user()['id'];
        }

        $invoices = $this->invoiceRepo->getAll($filters);

        $view = Auth::isClient() ? 'client/invoices/index' : 'admin/invoices/index';
        $this->viewLayout($view, Auth::role(), [
            'title' => 'Mis Facturas | ' . \Core\Config::get('business.company_name'),
            'invoices' => $invoices
        ]);
    }

    /**
     * Create Invoice from Budget
     */
    public function createFromBudget($budget_id)
    {
        if (Auth::isClient())
            $this->redirect('/dashboard');

        $invoiceService = new \App\Services\InvoiceService();
        $result = $invoiceService->createFromBudget($budget_id, Auth::user()['id']);

        if ($result['success']) {
            Session::flash('success', 'Factura generada con éxito.');
            $this->redirect('/invoice/show/' . $result['invoice_id']);
        } else {
            Session::flash('error', $result['error']);
            $this->redirect('/budget/show/' . $budget_id);
        }
    }

    /**
     * View Invoice
     */
    public function show($id)
    {
        $invoice = $this->invoiceRepo->getById($id);

        if (!$invoice)
            $this->redirect('/dashboard');

        // Security check for clients
        if (Auth::isClient() && $invoice['client_id'] != Auth::user()['id']) {
            $this->redirect('/dashboard');
        }

        // Get payment receipt via Repo
        $receipt = $this->invoiceRepo->getReceiptByInvoice($id);

        $layout = Auth::role();
        $this->viewLayout($layout . '/invoices/view', $layout, [
            'title' => 'Factura: ' . $invoice['invoice_number'],
            'invoice' => $invoice,
            'receipt' => $receipt
        ]);
    }

    /**
     * Upload Payment Receipt (Client)
     */
    public function pay()
    {
        if (!Auth::isClient())
            $this->redirect('/dashboard');

        $invoice_id = filter_input(INPUT_POST, 'invoice_id', FILTER_VALIDATE_INT);
        if (!$invoice_id) {
            Session::flash('error', 'Factura no identificada o formato inválido.');
            $this->redirect('/invoice');
        }
        $db = Database::getInstance()->getConnection();

        // Hardened file upload logic
        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0) {
            $errors = \Core\Validator::validateFile($_FILES['receipt'], 5 * 1024 * 1024, ['jpg', 'jpeg', 'png', 'pdf']);

            if (!empty($errors)) {
                Session::flash('error', implode(' ', $errors));
                $this->redirect('/invoice/show/' . $invoice_id);
            }

            $upload_dir = BASE_PATH . '/public/uploads/receipts/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $filename = \Core\Validator::generateSecureFileName($_FILES['receipt']['name']);
            $filepath = 'uploads/receipts/' . $filename;
            move_uploaded_file($_FILES['receipt']['tmp_name'], $upload_dir . $filename);

            $db->beginTransaction();
            try {
                // 1. Insert into payment_receipts
                $sql = "INSERT INTO payment_receipts (invoice_id, uploaded_by, filename, filepath, amount, payment_date, status) 
                        VALUES (?, ?, ?, ?, ?, CURDATE(), 'pending')";
                $stmt = $db->prepare($sql);

                // Get amount from invoice and input
                $stmtAmt = $db->prepare("SELECT total, paid_amount FROM invoices WHERE id = ?");
                $stmtAmt->execute([$invoice_id]);
                $invoiceData = $stmtAmt->fetch();

                $pending = $invoiceData['total'] - $invoiceData['paid_amount'];
                $amt = isset($_POST['amount']) ? floatval($_POST['amount']) : $pending;
                if ($amt <= 0 || $amt > $pending) {
                    $amt = $pending; // fallback
                }

                $stmt->execute([$invoice_id, Auth::user()['id'], $filename, $filepath, $amt]);

                $db->prepare("UPDATE invoices SET status = 'processing' WHERE id = ?")->execute([$invoice_id]);

                \Core\SecurityLogger::log('payment_receipt_uploaded', [
                    'invoice_id' => $invoice_id,
                    'filename' => $filename
                ]);

                // Notify Staff/Admin
                $staffStmt = $db->query("SELECT id FROM users WHERE role IN ('admin', 'staff')");
                $staff = $staffStmt->fetchAll();
                foreach ($staff as $s) {
                    \App\Models\Notification::send($s['id'], 'payment_upload', 'Comprobante de Pago', "Un cliente ha subido un comprobante para la factura #" . $invoice_id, '/invoice/show/' . $invoice_id);
                }

                $db->commit();
                Session::flash('success', 'Comprobante enviado. El staff verificará tu pago en breve.');
            } catch (\Exception $e) {
                $db->rollBack();
                Session::flash('error', 'Error al registrar el pago: ' . $e->getMessage());
            }
        }

        $this->redirect('/invoice/show/' . $invoice_id);
    }

    /**
     * Confirm Payment & Activate Service (Staff/Admin)
     */
    public function confirm($id)
    {
        if (Auth::isClient())
            $this->redirect('/dashboard');

        $invoiceService = new \App\Services\InvoiceService();
        $result = $invoiceService->confirmPayment($id, Auth::user()['id']);

        if ($result['success']) {
            Session::flash('success', 'Pago verificado. El servicio ha sido activado.');
        } else {
            Session::flash('error', 'Error al confirmar pago: ' . $result['error']);
        }

        $this->redirect('/invoice/show/' . $id);
    }
    /**
     * Iniciar Checkout con MercadoPago
     */
    public function payMp()
    {
        if (!Auth::isClient()) {
            $this->redirect('/dashboard');
        }

        $invoice_id = $_POST['invoice_id'] ?? null;
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

        if (!$invoice_id || $amount <= 0) {
            Session::flash('error', 'Datos de pago inválidos.');
            $this->redirect('/invoice');
        }

        // Ensure invoice belongs to user
        $invoice = $this->invoiceRepo->getById($invoice_id);

        if (!$invoice || $invoice['client_id'] != Auth::user()['id']) {
            Session::flash('error', 'Factura no encontrada.');
            $this->redirect('/invoice');
        }

        $pending = $invoice['total'] - $invoice['paid_amount'];
        if ($amount > $pending) {
            Session::flash('error', 'El monto supera el total adeudado.');
            $this->redirect('/invoice/show/' . $invoice_id);
        }

        $mpToken = trim(\Core\Config::get('payment.mp_access_token'));
        if (empty($mpToken)) {
            Session::flash('error', 'MercadoPago no configurado.');
            $this->redirect('/invoice/show/' . $invoice_id);
        }

        // Send cURL request to MP to create preference
        $appUrl = getenv('APP_URL') ? rtrim(getenv('APP_URL'), '/') : '';

        // Multi-divisa: Conversión Transparente
        $invoiceCurrency = $invoice['currency'] ?? 'USD';
        $mpCurrency = \Core\Config::get('payment.mp_currency_id') ?: 'ARS';
        $amountToPayMP = $amount;
        $exchangeRate = 1;

        if ($invoiceCurrency !== $mpCurrency) {
            $exchangeRate = (float) \Core\Config::get('payment.exchange_rate') ?: 1;
            if ($exchangeRate <= 0)
                $exchangeRate = 1;
            $amountToPayMP = round($amount * $exchangeRate, 2);
        }

        $preferenceData = [
            'items' => [
                [
                    'title' => 'Pago Factura #' . $invoice['invoice_number'],
                    'quantity' => 1,
                    'unit_price' => (float) $amountToPayMP, // Valor convertido
                    'currency_id' => $mpCurrency    // Moneda de destino
                ]
            ],
            'external_reference' => (string) $invoice_id,
            'back_urls' => [
                'success' => $appUrl . '/invoice/show/' . $invoice_id,
                'failure' => $appUrl . '/invoice/show/' . $invoice_id,
                'pending' => $appUrl . '/invoice/show/' . $invoice_id,
            ],
            'auto_return' => 'approved',
            'notification_url' => $appUrl . '/webhook/mercadopago',
            'metadata' => [
                'invoice_id' => $invoice_id,
                'original_amount' => $amount,
                'original_currency' => $invoiceCurrency,
                'exchange_rate' => $exchangeRate ?? 1
            ]
        ];

        $ch = curl_init('https://api.mercadopago.com/checkout/preferences');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $mpToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($preferenceData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            \Core\SecurityLogger::log('mp_connection_error', ['error' => $error], 'ERROR');
            Session::flash('error', 'Error de conexión con la plataforma de pago. Intenta más tarde.');
            $this->redirect('/invoice/show/' . $invoice_id);
            return;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300 && isset($result['init_point'])) {
            header('Location: ' . $result['init_point']);
            exit;
        } else {
            \Core\SecurityLogger::log('mp_preference_error', ['response' => $result]);
            Session::flash('error', 'Error al crear solicitud en MercadoPago.');
            $this->redirect('/invoice/show/' . $invoice_id);
        }
    }

    /**
     * Export Invoice to PDF with QR
     */
    public function exportPdf($id)
    {
        $db = Database::getInstance()->getConnection();

        $sql = "SELECT i.*, u.name as client_name, u.email as client_email, u.company as client_company, u.phone as client_phone, b.budget_number 
                FROM invoices i 
                JOIN users u ON i.client_id = u.id 
                LEFT JOIN budgets b ON i.budget_id = b.id 
                WHERE i.id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $invoice = $stmt->fetch();

        if (!$invoice)
            $this->redirect('/dashboard');

        // Security: Clients can only see their own invoices
        if (Auth::isClient() && $invoice['client_id'] != Auth::user()['id']) {
            Session::flash('error', 'No tienes permiso para descargar esta factura.');
            $this->redirect('/dashboard');
        }

        // Convert logo to base64 (Sólo si GD está activo, requerido por Dompdf para PNGs)
        $logoPath = BASE_PATH . '/public/assets/images/logo.png';
        $logoData = "";
        if (file_exists($logoPath) && extension_loaded('gd')) {
            $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
            $logoContent = file_get_contents($logoPath);
            $logoData = 'data:image/' . $logoType . ';base64,' . base64_encode($logoContent);
        }

        // Generate QR Code (^6.0 Standard - Immutable - SVG Writer to skip GD)
        $validationUrl = url("/invoice/validate/" . md5($invoice['invoice_number']));
        $builder = new \Endroid\QrCode\Builder\Builder();
        $result = $builder->build(
            writer: new \Endroid\QrCode\Writer\SvgWriter(),
            data: $validationUrl,
            encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
            errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::Low,
            size: 200,
            margin: 10,
            roundBlockSizeMode: \Endroid\QrCode\RoundBlockSizeMode::Margin
        );

        $qrBase64 = $result->getDataUri();

        $html = \Core\View::renderToString('pdf/invoice', [
            'invoice' => $invoice,
            'logo_base64' => $logoData,
            'qr_base64' => $qrBase64
        ]);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("Factura-{$invoice['invoice_number']}.pdf", ["Attachment" => true]);
        exit;
    }

    /**
     * Void Invoice (Staff/Admin)
     */
    public function void($id)
    {
        if (Auth::isClient())
            $this->redirect('/dashboard');

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM invoices WHERE id = ?");
        $stmt->execute([$id]);
        $invoice = $stmt->fetch();

        if (!$invoice) {
            Session::flash('error', 'Factura no encontrada.');
            $this->redirect('/invoice');
        }

        try {
            $finOps = new \App\Services\FinOpsService();
            // Record VOID event. Amount is the negative of the total to balance out.
            $finOps->recordEvent((int)$id, 'VOID', -(float)$invoice['total'], [
                'reason' => !empty($_POST['reason']) ? trim($_POST['reason']) : \Core\Config::get('app.default_void_reason', 'Anulación administrativa'),
                'voided_by' => Auth::user()['id']
            ], Auth::user()['id']);

            $finOps->syncInvoiceProjection((int)$id);

            \App\Services\AuditService::log('invoice_voided', ['invoice_id' => $id], 'WARN');
            Session::flash('success', 'Factura anulada con éxito.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al anular factura: ' . $e->getMessage());
        }

        $this->redirect('/invoice/show/' . $id);
    }

    /**
     * Refund Invoice (Staff/Admin)
     */
    public function refund($id)
    {
        if (Auth::isClient())
            $this->redirect('/dashboard');

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM invoices WHERE id = ?");
        $stmt->execute([$id]);
        $invoice = $stmt->fetch();

        if (!$invoice) {
            Session::flash('error', 'Factura no encontrada.');
            $this->redirect('/invoice');
        }

        $refundAmount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
        if ($refundAmount <= 0 || $refundAmount > $invoice['paid_amount']) {
            Session::flash('error', 'Monto de reembolso inválido.');
            $this->redirect('/invoice/show/' . $id);
        }

        try {
            $finOps = new \App\Services\FinOpsService();
            // recordEvent for REFUND. FinOpsService::calculateBalance subtracts REFUND sum from totalPaid.
            $finOps->recordEvent((int)$id, 'REFUND', $refundAmount, [
                'reason' => !empty($_POST['reason']) ? trim($_POST['reason']) : \Core\Config::get('app.default_refund_reason', 'Reembolso al cliente'),
                'refunded_by' => Auth::user()['id']
            ], Auth::user()['id']);

            $finOps->syncInvoiceProjection((int)$id);

            \App\Services\AuditService::log('invoice_refunded', [
                'invoice_id' => $id,
                'amount' => $refundAmount
            ], 'INFO');
            
            Session::flash('success', 'Reembolso registrado correctamente.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al registrar reembolso: ' . $e->getMessage());
        }

        $this->redirect('/invoice/show/' . $id);
    }

    /**
     * Export all invoices to CSV
     */
    public function exportCsv()
    {
        if (!Auth::isAdmin()) {
            $this->redirect('/dashboard');
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT i.invoice_number, u.name as client_name, i.issue_date, i.total, i.paid_amount, i.status 
                           FROM invoices i 
                           JOIN users u ON i.client_id = u.id 
                           ORDER BY i.created_at DESC");
        $results = $stmt->fetchAll();

        $headers = ['Número de Factura', 'Cliente', 'Fecha de Emisión', 'Total', 'Monto Pagado', 'Estado'];
        $data = [];
        foreach ($results as $inv) {
            $data[] = [
                $inv['invoice_number'],
                $inv['client_name'],
                $inv['issue_date'],
                $inv['total'],
                $inv['paid_amount'],
                $inv['status']
            ];
        }

        \App\Utils\CsvExporter::export('facturas_' . date('Ymd'), $headers, $data);
    }
}
