<?php
/**
 * UI Components - Badges
 * Sistema de diseño para etiquetas de estado consistentes
 */

namespace App\UI;

class Badge
{
    /**
     * Badge primario
     */
    public static function primary(string $text, bool $rounded = false): string
    {
        return self::render($text, 'primary', $rounded);
    }

    /**
     * Badge secundario
     */
    public static function secondary(string $text, bool $rounded = false): string
    {
        return self::render($text, 'secondary', $rounded);
    }

    /**
     * Badge de éxito
     */
    public static function success(string $text, bool $rounded = false): string
    {
        return self::render($text, 'success', $rounded);
    }

    /**
     * Badge de peligro/error
     */
    public static function danger(string $text, bool $rounded = false): string
    {
        return self::render($text, 'danger', $rounded);
    }

    /**
     * Badge de advertencia
     */
    public static function warning(string $text, bool $rounded = false): string
    {
        return self::render($text, 'warning', $rounded);
    }

    /**
     * Badge de información
     */
    public static function info(string $text, bool $rounded = false): string
    {
        return self::render($text, 'info', $rounded);
    }

    /**
     * Renderiza un badge con variante dinámica (útil para estados de dominio)
     */
    public static function status(string $text, string $variant, bool $rounded = true): string
    {
        // Limpiar "badge-" si viene incluido en el string del dominio
        $variant = str_replace('badge-', '', $variant);
        return self::render($text, $variant, $rounded);
    }

    /**
     * Renderiza el HTML del badge
     */
    private static function render(string $text, string $type, bool $rounded): string
    {
        $pillClass = $rounded ? 'rounded-pill' : '';
        return "<span class='badge bg-$type $pillClass'>$text</span>";
    }
}
