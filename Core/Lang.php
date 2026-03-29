<?php

namespace Core;

/**
 * Class Lang
 * 
 * Gestiona el sistema de internacionalización (i18n) cargando diccionarios JSON dinámicos.
 * Prioriza el rendimiento mediante caché en memoria durante el ciclo de vida de la petición.
 */
class Lang
{
    private static $dictionary = [];
    private static $currentLocale = 'es';

    /**
     * Inicializa el sistema de lenguaje.
     * Carga el archivo JSON correspondiente al locale configurado en el .env
     */
    public static function init()
    {
        self::$currentLocale = Config::get('app.locale', 'es');
        $path = base_path('locales/' . self::$currentLocale . '.json');

        if (file_exists($path)) {
            $content = file_get_contents($path);
            self::$dictionary = json_decode($content, true) ?: [];
        } else {
            // Fallback al español si el archivo solicitado no existe
            $fallbackPath = base_path('locales/es.json');
            if (file_exists($fallbackPath)) {
                self::$dictionary = json_decode(file_get_contents($fallbackPath), true) ?: [];
            }
        }
    }

    /**
     * Obtiene una traducción mediante una clave con notación de puntos (ej: "home.hero.title").
     * Permite pasar placeholders dinámicos para inyectar valores en la cadena.
     * 
     * @param string $key Clave de traducción.
     * @param array $placeholders Mapa de parámetros para reemplazar en la traducción (:name => "Valor").
     * @return string
     */
    public static function get(string $key, array $placeholders = []): string
    {
        $keys = explode('.', $key);
        $value = self::$dictionary;

        foreach ($keys as $segment) {
            if (isset($value[$segment])) {
                $value = $value[$segment];
            } else {
                // Si no existe la clave, devolvemos la clave original resaltada para debugging
                return "[Missing Lang: $key]";
            }
        }

        if (!is_string($value)) {
            return "[Invalid value for: $key]";
        }

        // Reemplazo de placeholders dinámicos
        foreach ($placeholders as $placeholder => $replacement) {
            $value = str_replace(':' . $placeholder, $replacement, $value);
        }

        return $value;
    }

    /**
     * Cambia el idioma en tiempo de ejecución (útil para selectores de idioma).
     */
    public static function setLocale(string $locale)
    {
        self::$currentLocale = $locale;
        self::init();
    }

    public static function current(): string
    {
        return self::$currentLocale;
    }
}
