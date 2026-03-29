<?php
namespace App\Policies;

use Core\Auth;

/**
 * Project Policy
 * Centraliza las reglas de autorización para proyectos
 */
class ProjectPolicy
{
    /**
     * Verifica si el usuario puede ver el proyecto
     */
    public static function canView(array $user, array $project): bool
    {
        // Admin y Staff pueden ver todos los proyectos
        if (in_array($user['role'], ['admin', 'staff'])) {
            return true;
        }

        // Clientes solo pueden ver sus propios proyectos
        if ($user['role'] === 'client') {
            return $project['client_id'] === $user['id'];
        }

        return false;
    }

    /**
     * Verifica si el usuario puede editar el proyecto
     */
    public static function canEdit(array $user, array $project): bool
    {
        // Solo admin y staff pueden editar
        if (in_array($user['role'], ['admin', 'staff'])) {
            return true;
        }

        return false;
    }

    /**
     * Verifica si el usuario puede eliminar el proyecto
     */
    public static function canDelete(array $user, array $project): bool
    {
        // Solo admin puede eliminar
        return $user['role'] === 'admin';
    }

    /**
     * Verifica si el usuario puede cambiar el estado del proyecto
     */
    public static function canChangeStatus(array $user, array $project): bool
    {
        // Admin y staff asignado pueden cambiar estado
        if ($user['role'] === 'admin') {
            return true;
        }

        if ($user['role'] === 'staff' && isset($project['assigned_to'])) {
            return $project['assigned_to'] === $user['id'];
        }

        return false;
    }

    /**
     * Verifica si el usuario puede subir archivos al proyecto
     */
    public static function canUploadFiles(array $user, array $project): bool
    {
        // Admin y staff pueden subir archivos
        if (in_array($user['role'], ['admin', 'staff'])) {
            return true;
        }

        return false;
    }

    /**
     * Verifica si el usuario puede descargar archivos del proyecto
     */
    public static function canDownloadFiles(array $user, array $project): bool
    {
        // Todos los roles pueden descargar si tienen acceso al proyecto
        return self::canView($user, $project);
    }

    /**
     * Verifica si el usuario puede crear proyectos
     */
    public static function canCreate(array $user): bool
    {
        // Solo admin y staff pueden crear proyectos
        return in_array($user['role'], ['admin', 'staff']);
    }

    /**
     * Verifica si el usuario puede asignar staff al proyecto
     */
    public static function canAssignStaff(array $user, array $project): bool
    {
        // Solo admin puede asignar staff
        return $user['role'] === 'admin';
    }
}
