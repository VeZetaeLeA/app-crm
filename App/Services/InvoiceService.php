<?php
namespace App\Services;

use App\Domain\Invoice\InvoiceStatus;
use App\Policies\InvoicePolicy;
use App\Services\AuditService;
use App\Services\FinOpsService;
use App\Models\Notification;
use Core\Database;
use Exception;

/**
 * Invoice Service
 */
class InvoiceService
{
    private $db;
    private $invoiceRepo;

    public function __construct(\App\Repositories\InvoiceRepositoryInterface $invoiceRepo = null)
    {
        $this->db = Database::getInstance()->getConnection();
        $this->invoiceRepo = $invoiceRepo ?? new \App\Repositories\InvoiceRepository($this->db);
    }

    /**
     * Crea una factura a partir de un presupuesto aprobado.
     */
    public function createFromBudget(int $budget_id, int $created_by): array
    {
        // Obtener presupuesto
        $budget = $this->invoiceRepo->getApprovedBudget($budget_id);

        if (!$budget) {
            return ['success' => false, 'error' => 'Presupuesto no encontrado o no aprobado'];
        }

        // Verificar si ya existe factura
        if ($this->invoiceRepo->hasInvoiceForBudget($budget_id)) {
            return ['success' => false, 'error' => 'Ya existe una factura para este presupuesto.'];
        }

        $ownsTransaction = false;
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
            $ownsTransaction = true;
        }

        try {

            $invoice_number = 'VZL-INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));

            // Obtener client_id del ticket
            $client_id = $this->invoiceRepo->getClientIdByTicket($budget['ticket_id']);

            $invoiceId = $this->invoiceRepo->createInvoice([
                'invoice_number' => $invoice_number,
                'client_id' => $client_id,
                'budget_id' => $budget_id,
                'service_reference' => $budget['service_reference'],
                'subtotal' => $budget['subtotal'],
                'tax_amount' => $budget['tax_amount'],
                'total' => $budget['total'],
                'created_by' => $created_by
            ]);

            // 🚀 Event Sourcing (E11-012)
            $finOps = new FinOpsService();
            $finOps->recordEvent($invoiceId, 'CREATE', $budget['total'], [
                'budget_id' => $budget_id,
                'subtotal' => $budget['subtotal'],
                'tax_amount' => $budget['tax_amount']
            ], $created_by);

            // Actualizar estado del ticket a 'invoiced'
            $this->invoiceRepo->updateTicketStatus($budget['ticket_id'], 'invoiced');

            AuditService::log('invoice_generated', [
                'invoice_id' => $invoiceId,
                'budget_id' => $budget_id
            ], 'INFO');

            // Notificar al Cliente
            Notification::send($client_id, 'invoice_generated', 'Factura Generada', "Se ha generado automáticamente la factura $invoice_number para tu servicio.", "/invoice/show/" . $invoiceId);

            // Dispatch Event (Evolution 2.0)
            \Core\EventDispatcher::dispatch(new \App\Events\InvoiceIssued([
                'invoice_id' => $invoiceId,
                'client_id' => $client_id,
                'amount' => $budget['total']
            ]));

            if ($ownsTransaction) {
                $this->db->commit();
            }
            return ['success' => true, 'invoice_id' => $invoiceId];

        } catch (Exception $e) {
            if ($ownsTransaction) {
                $this->db->rollBack();
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Confirma el pago de una factura y activa el servicio.
     */
    public function confirmPayment(int $invoice_id, int $verified_by): array
    {
        $invoice = $this->findInvoiceById($invoice_id);
        if (!$invoice) {
            return ['success' => false, 'error' => 'Factura no encontrada'];
        }

        $ownsTransaction = false;
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
            $ownsTransaction = true;
        }

        try {
            // Get total pending amounts in receipts
            $amount_paid_now = $this->invoiceRepo->getPendingPaymentReceiptsSum($invoice_id);

            // 🚀 Event Sourcing (E11-013)
            $finOps = new FinOpsService();
            $finOps->recordEvent($invoice_id, 'APPLY_PAYMENT', $amount_paid_now, [
                'batch_confirmation' => true,
                'verified_by' => $verified_by
            ], $verified_by);

            // Proyectar el estado actualizado basado en eventos
            $finOps->syncInvoiceProjection($invoice_id);
            $balance = $finOps->calculateBalance($invoice_id);

            $is_fully_paid = $balance['is_fully_paid'];
            $new_status = $balance['is_void'] ? 'void' : ($is_fully_paid ? 'paid' : 'partial');

            // 2. Actualizar estado del comprobante
            $this->invoiceRepo->verifyPendingReceipts($invoice_id, $verified_by);

            // 3. Obtener detalles de la factura para crear/verificar Servicio Activo
            $inv = $this->invoiceRepo->getInvoiceWithBudgetDetails($invoice_id);
            $plan_id = $this->invoiceRepo->getServicePlanIdByTicket($inv['ticket_id']);

            // 4. Verificar si ya existe un Servicio Activo para esta factura (evitar duplicados)
            $existingService = $this->invoiceRepo->hasActiveServiceForInvoice($invoice_id);

            if (!$existingService) {
                // 5. Crear Servicio Activo (primer pago: parcial o total)
                $this->invoiceRepo->createActiveService([
                    'client_id' => $inv['client_id'],
                    'ticket_id' => $inv['ticket_id'],
                    'invoice_id' => $invoice_id,
                    'service_plan_id' => $plan_id,
                    'name' => $inv['budget_title'],
                    'activated_by' => $verified_by
                ]);

                // 6. Cerrar el Ticket de Pre-Venta al activar el servicio
                $this->invoiceRepo->updateTicketStatus($inv['ticket_id'], 'closed');

                AuditService::log('service_activated', [
                    'invoice_id' => $invoice_id,
                    'client_id' => $inv['client_id'],
                    'partial_pay' => !$is_fully_paid
                ], 'INFO');

                // Notificar al Cliente: mensaje adaptado según tipo de pago
                $payMsg = $is_fully_paid
                    ? "¡Tu servicio '{$inv['budget_title']}' ha sido activado con pago completo!"
                    : "¡Tu servicio '{$inv['budget_title']}' ha sido activado con un pago inicial. Tu Workspace ya está disponible.";
                Notification::send($inv['client_id'], 'service_activated', 'Servicio Activado', $payMsg, "/project/workspace");

                // Dispatch Event (Evolution 2.0)
                \Core\EventDispatcher::dispatch(new \App\Events\ProjectStarted([
                    'invoice_id' => $invoice_id,
                    'client_id' => $inv['client_id'],
                    'plan_id' => $plan_id
                ]));
            } else {
                // El servicio ya existe (pago parcial posterior): solo notificar el abono recibido
                Notification::send(
                    $inv['client_id'],
                    'payment_verified',
                    'Pago Recibido',
                    "Hemos verificado un pago por $" . number_format($amount_paid_now, 2) . " para la factura #" . $invoice['invoice_number'] . ".",
                    "/invoice/show/" . $invoice_id
                );
            }

            if ($is_fully_paid) {
                AuditService::log('invoice_paid', ['invoice_id' => $invoice_id], 'INFO');
            } else {
                AuditService::log('invoice_partial_payment', ['invoice_id' => $invoice_id, 'amount' => $amount_paid_now], 'INFO');
            }

            if ($ownsTransaction) {
                $this->db->commit();
            }
            return ['success' => true];

        } catch (Exception $e) {
            if ($ownsTransaction) {
                $this->db->rollBack();
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Registra un pago y actualiza el estado de la factura (Simplificado).
     */
    public function markAsPaid(int $invoiceId, array $currentUser): array
    {
        $invoice = $this->findInvoiceById($invoiceId);
        if (!$invoice)
            return ['success' => false, 'error' => 'Factura no encontrada'];

        // 1. Autorización
        if (!InvoicePolicy::canVerifyPayment($currentUser, $invoice)) {
            AuditService::log('unauthorized_payment_verification_attempt', ['invoice_id' => $invoiceId], 'WARN');
            return ['success' => false, 'error' => 'No autorizado'];
        }

        // 2. Transición
        $status = InvoiceStatus::fromString($invoice['status']);
        $newStatus = InvoiceStatus::paid();

        if (!$status->canTransitionTo($newStatus)) {
            return ['success' => false, 'error' => 'No se puede marcar como pagada desde el estado actual'];
        }

        return $this->confirmPayment($invoiceId, $currentUser['id']);
    }

    private function findInvoiceById(int $id): ?array
    {
        return $this->invoiceRepo->findInvoiceById($id);
    }
}
