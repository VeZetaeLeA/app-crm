<?php
namespace App\Policies;

use Core\Auth;

/**
 * Invoice Policy
 * Centraliza las reglas de autorización para facturas
 */
class InvoicePolicy
{
    /**
     * Verifica si el usuario puede ver la factura
     */
    public static function canView(array $user, array $invoice): bool
    {
        // Admin y Staff pueden ver todas las facturas
        if (in_array($user['role'], ['admin', 'staff'])) {
            return true;
        }

        // Clientes solo pueden ver sus propias facturas
        if ($user['role'] === 'client') {
            return $invoice['client_id'] === $user['id'];
        }

        return false;
    }

    /**
     * Verifica si el usuario puede crear facturas
     */
    public static function canCreate(array $user): bool
    {
        // Solo admin y staff pueden crear facturas
        return in_array($user['role'], ['admin', 'staff']);
    }

    /**
     * Verifica si el usuario puede editar la factura
     */
    public static function canEdit(array $user, array $invoice): bool
    {
        // Solo admin puede editar facturas
        // Staff no puede editar para mantener integridad financiera
        if ($user['role'] !== 'admin') {
            return false;
        }

        // No se pueden editar facturas pagadas
        return $invoice['status'] !== 'paid';
    }

    /**
     * Verifica si el usuario puede eliminar la factura
     */
    public static function canDelete(array $user, array $invoice): bool
    {
        // Solo admin puede eliminar
        if ($user['role'] !== 'admin') {
            return false;
        }

        // No se pueden eliminar facturas pagadas
        return $invoice['status'] !== 'paid';
    }

    /**
     * Verifica si el usuario puede cambiar el estado de la factura
     */
    public static function canChangeStatus(array $user, array $invoice, string $newStatus): bool
    {
        // Solo admin puede cambiar estados
        if ($user['role'] !== 'admin') {
            return false;
        }

        // No se puede cambiar el estado de facturas pagadas
        if ($invoice['status'] === 'paid' && $newStatus !== 'paid') {
            return false;
        }

        return true;
    }

    /**
     * Verifica si el usuario puede subir comprobante de pago
     */
    public static function canUploadPaymentReceipt(array $user, array $invoice): bool
    {
        // Admin puede subir comprobantes
        if ($user['role'] === 'admin') {
            return true;
        }

        // Cliente puede subir comprobante de su propia factura
        if ($user['role'] === 'client' && $invoice['client_id'] === $user['id']) {
            // Solo si la factura está pendiente de pago
            return in_array($invoice['status'], ['unpaid', 'processing', 'overdue']);
        }

        return false;
    }

    /**
     * Verifica si el usuario puede verificar comprobantes de pago
     */
    public static function canVerifyPayment(array $user, array $invoice): bool
    {
        // Solo admin puede verificar pagos
        return $user['role'] === 'admin';
    }

    /**
     * Verifica si el usuario puede descargar la factura en PDF
     */
    public static function canDownload(array $user, array $invoice): bool
    {
        // Todos los que pueden ver pueden descargar
        return self::canView($user, $invoice);
    }

    /**
     * Verifica si el usuario puede enviar recordatorios de pago
     */
    public static function canSendReminder(array $user, array $invoice): bool
    {
        // Solo admin y staff pueden enviar recordatorios
        return in_array($user['role'], ['admin', 'staff']);
    }

    /**
     * Verifica si el usuario puede cancelar la factura
     */
    public static function canCancel(array $user, array $invoice): bool
    {
        // Solo admin puede cancelar
        if ($user['role'] !== 'admin') {
            return false;
        }

        // No se pueden cancelar facturas pagadas
        return $invoice['status'] !== 'paid';
    }
}
