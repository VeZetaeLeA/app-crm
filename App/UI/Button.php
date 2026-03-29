<?php
/**
 * UI Components - Buttons
 * Sistema de diseño para botones consistentes
 */

namespace App\UI;

class Button
{
    /**
     * Botón primario
     */
    public static function primary(string $text, string $href = '#', array $attributes = []): string
    {
        return self::render($text, $href, 'btn-primary', $attributes);
    }

    /**
     * Botón secundario
     */
    public static function secondary(string $text, string $href = '#', array $attributes = []): string
    {
        return self::render($text, $href, 'btn-secondary', $attributes);
    }

    /**
     * Botón de éxito
     */
    public static function success(string $text, string $href = '#', array $attributes = []): string
    {
        return self::render($text, $href, 'btn-success', $attributes);
    }

    /**
     * Botón de peligro
     */
    public static function danger(string $text, string $href = '#', array $attributes = []): string
    {
        return self::render($text, $href, 'btn-danger', $attributes);
    }

    /**
     * Botón de advertencia
     */
    public static function warning(string $text, string $href = '#', array $attributes = []): string
    {
        return self::render($text, $href, 'btn-warning', $attributes);
    }

    /**
     * Botón de información
     */
    public static function info(string $text, string $href = '#', array $attributes = []): string
    {
        return self::render($text, $href, 'btn-info', $attributes);
    }

    /**
     * Botón outline primario
     */
    public static function outlinePrimary(string $text, string $href = '#', array $attributes = []): string
    {
        return self::render($text, $href, 'btn-outline-primary', $attributes);
    }

    /**
     * Botón con icono
     */
    public static function withIcon(string $text, string $icon, string $href = '#', string $variant = 'primary', array $attributes = []): string
    {
        $iconHtml = "<span class='material-symbols-outlined' style='font-size: 18px; vertical-align: middle;'>$icon</span>";
        $textWithIcon = "$iconHtml <span style='vertical-align: middle;'>$text</span>";

        return self::render($textWithIcon, $href, "btn-$variant", $attributes);
    }

    /**
     * Botón de submit de formulario
     */
    public static function submit(string $text, array $attributes = []): string
    {
        $attributes['type'] = 'submit';
        return self::renderButton($text, 'btn-primary', $attributes);
    }

    /**
     * Botón deshabilitado
     */
    public static function disabled(string $text, string $variant = 'secondary'): string
    {
        return self::render($text, '#', "btn-$variant", ['disabled' => true]);
    }

    /**
     * Renderiza un botón como enlace
     */
    private static function render(string $text, string $href, string $class, array $attributes): string
    {
        $attrs = self::buildAttributes(array_merge($attributes, ['class' => "btn $class"]));
        return "<a href='$href' $attrs>$text</a>";
    }

    /**
     * Renderiza un botón como button element
     */
    private static function renderButton(string $text, string $class, array $attributes): string
    {
        $attrs = self::buildAttributes(array_merge($attributes, ['class' => "btn $class"]));
        return "<button $attrs>$text</button>";
    }

    /**
     * Construye string de atributos HTML
     */
    private static function buildAttributes(array $attributes): string
    {
        $attrs = [];
        foreach ($attributes as $key => $value) {
            if ($value === true) {
                $attrs[] = $key;
            } elseif ($value !== false && $value !== null) {
                $attrs[] = "$key=\"" . htmlspecialchars($value, ENT_QUOTES) . "\"";
            }
        }
        return implode(' ', $attrs);
    }
}
