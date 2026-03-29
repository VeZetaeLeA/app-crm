<?php
/**
 * UI Components - Alerts
 * Sistema de diseño para alertas y mensajes consistentes
 */

namespace App\UI;

class Alert
{
    /**
     * Alerta de éxito
     */
    public static function success(string $message, bool $dismissible = true): string
    {
        return self::render($message, 'success', 'check_circle', $dismissible);
    }

    /**
     * Alerta de error
     */
    public static function error(string $message, bool $dismissible = true): string
    {
        return self::render($message, 'danger', 'error', $dismissible);
    }

    /**
     * Alerta de advertencia
     */
    public static function warning(string $message, bool $dismissible = true): string
    {
        return self::render($message, 'warning', 'warning', $dismissible);
    }

    /**
     * Alerta de información
     */
    public static function info(string $message, bool $dismissible = true): string
    {
        return self::render($message, 'info', 'info', $dismissible);
    }

    /**
     * Alerta con lista de errores
     */
    public static function errors(array $errors, bool $dismissible = true): string
    {
        $message = "<strong>Se encontraron los siguientes errores:</strong><ul class='mb-0 mt-2'>";
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $message .= "<li>$error</li>";
            }
        }
        $message .= "</ul>";

        return self::render($message, 'danger', 'error', $dismissible);
    }

    /**
     * Alerta de validación
     */
    public static function validation(array $errors): string
    {
        return self::errors($errors, true);
    }

    /**
     * Renderiza una alerta
     */
    private static function render(string $message, string $type, string $icon, bool $dismissible): string
    {
        $dismissClass = $dismissible ? 'alert-dismissible fade show' : '';
        $dismissButton = $dismissible ? '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' : '';

        return "
        <div class='alert alert-$type $dismissClass' role='alert'>
            <div class='d-flex align-items-start'>
                <span class='material-symbols-outlined me-2' style='font-size: 24px;'>$icon</span>
                <div class='flex-grow-1'>
                    $message
                </div>
                $dismissButton
            </div>
        </div>";
    }

    /**
     * Alerta con título
     */
    public static function withTitle(string $title, string $message, string $type = 'info', bool $dismissible = true): string
    {
        $icons = [
            'success' => 'check_circle',
            'danger' => 'error',
            'warning' => 'warning',
            'info' => 'info'
        ];

        $icon = $icons[$type] ?? 'info';
        $dismissClass = $dismissible ? 'alert-dismissible fade show' : '';
        $dismissButton = $dismissible ? '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' : '';

        return "
        <div class='alert alert-$type $dismissClass' role='alert'>
            <div class='d-flex align-items-start'>
                <span class='material-symbols-outlined me-2' style='font-size: 24px;'>$icon</span>
                <div class='flex-grow-1'>
                    <h5 class='alert-heading mb-1'>$title</h5>
                    <p class='mb-0'>$message</p>
                </div>
                $dismissButton
            </div>
        </div>";
    }

    /**
     * Alerta con acción
     */
    public static function withAction(string $message, string $actionText, string $actionHref, string $type = 'info'): string
    {
        $icons = [
            'success' => 'check_circle',
            'danger' => 'error',
            'warning' => 'warning',
            'info' => 'info'
        ];

        $icon = $icons[$type] ?? 'info';

        return "
        <div class='alert alert-$type' role='alert'>
            <div class='d-flex align-items-center justify-content-between'>
                <div class='d-flex align-items-start flex-grow-1'>
                    <span class='material-symbols-outlined me-2' style='font-size: 24px;'>$icon</span>
                    <div>$message</div>
                </div>
                <a href='$actionHref' class='btn btn-sm btn-outline-$type ms-3'>$actionText</a>
            </div>
        </div>";
    }
}
