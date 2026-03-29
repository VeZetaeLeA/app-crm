<?php
namespace App\Policies;

use Core\Auth;

/**
 * Ticket Policy
 * Centraliza las reglas de autorización para tickets
 */
class TicketPolicy
{
    /**
     * Verifica si el usuario puede ver el ticket
     */
    public static function canView(array $user, array $ticket): bool
    {
        // Admin y Staff pueden ver todos los tickets
        if (in_array($user['role'], ['admin', 'staff'])) {
            return true;
        }

        // Clientes solo pueden ver sus propios tickets
        if ($user['role'] === 'client') {
            return $ticket['client_id'] === $user['id'];
        }

        return false;
    }

    /**
     * Verifica si el usuario puede editar el ticket
     */
    public static function canEdit(array $user, array $ticket): bool
    {
        // Admin puede editar todos
        if ($user['role'] === 'admin') {
            return true;
        }

        // Staff asignado puede editar
        if ($user['role'] === 'staff' && isset($ticket['assigned_to'])) {
            return $ticket['assigned_to'] === $user['id'];
        }

        return false;
    }

    /**
     * Verifica si el usuario puede eliminar el ticket
     */
    public static function canDelete(array $user, array $ticket): bool
    {
        // Solo admin puede eliminar
        return $user['role'] === 'admin';
    }

    /**
     * Verifica si el usuario puede cambiar el estado del ticket
     */
    public static function canChangeStatus(array $user, array $ticket, string $newStatus): bool
    {
        // Admin puede cambiar a cualquier estado
        if ($user['role'] === 'admin') {
            return true;
        }

        // Staff asignado puede cambiar estados operativos
        if ($user['role'] === 'staff' && isset($ticket['assigned_to']) && $ticket['assigned_to'] === $user['id']) {
            $allowedStates = ['in_analysis', 'budget_sent', 'closed'];
            return in_array($newStatus, $allowedStates);
        }

        // Cliente puede aprobar/rechazar presupuesto
        if ($user['role'] === 'client' && $ticket['client_id'] === $user['id']) {
            $allowedStates = ['budget_approved', 'budget_rejected'];
            return in_array($newStatus, $allowedStates) && $ticket['status'] === 'budget_sent';
        }

        return false;
    }

    /**
     * Verifica si el usuario puede enviar mensajes en el chat
     */
    public static function canSendMessage(array $user, array $ticket): bool
    {
        // Todos los que pueden ver el ticket pueden enviar mensajes
        return self::canView($user, $ticket);
    }

    /**
     * Verifica si el usuario puede crear presupuestos
     */
    public static function canCreateBudget(array $user, array $ticket): bool
    {
        // Solo admin y staff asignado pueden crear presupuestos
        if ($user['role'] === 'admin') {
            return true;
        }

        if ($user['role'] === 'staff' && isset($ticket['assigned_to'])) {
            return $ticket['assigned_to'] === $user['id'];
        }

        return false;
    }

    /**
     * Verifica si el usuario puede ver presupuestos
     */
    public static function canViewBudget(array $user, array $ticket): bool
    {
        // Todos los que pueden ver el ticket pueden ver presupuestos
        return self::canView($user, $ticket);
    }

    /**
     * Verifica si el usuario puede crear tickets
     */
    public static function canCreate(array $user): bool
    {
        // Todos los roles autenticados pueden crear tickets
        // Los no autenticados pueden crear desde formulario público
        return true;
    }

    /**
     * Verifica si el usuario puede asignar el ticket a staff
     */
    public static function canAssign(array $user, array $ticket): bool
    {
        // Solo admin puede asignar tickets
        return $user['role'] === 'admin';
    }

    /**
     * Verifica si el usuario puede cambiar la prioridad
     */
    public static function canChangePriority(array $user, array $ticket): bool
    {
        // Admin y staff asignado pueden cambiar prioridad
        if ($user['role'] === 'admin') {
            return true;
        }

        if ($user['role'] === 'staff' && isset($ticket['assigned_to'])) {
            return $ticket['assigned_to'] === $user['id'];
        }

        return false;
    }

    /**
     * Verifica si el usuario puede cerrar el ticket
     */
    public static function canClose(array $user, array $ticket): bool
    {
        // Admin puede cerrar cualquier ticket
        if ($user['role'] === 'admin') {
            return true;
        }

        // Staff asignado puede cerrar
        if ($user['role'] === 'staff' && isset($ticket['assigned_to'])) {
            return $ticket['assigned_to'] === $user['id'];
        }

        // Cliente puede cerrar su propio ticket si está en ciertos estados
        if ($user['role'] === 'client' && $ticket['client_id'] === $user['id']) {
            $closableStates = ['budget_rejected', 'active'];
            return in_array($ticket['status'], $closableStates);
        }

        return false;
    }
}
