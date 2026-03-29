<?php
namespace App\Support;

class SEOHelper
{
    /**
     * Genera etiquetas meta básicas.
     */
    public static function getMetaTags($title = '', $description = '')
    {
        $siteName = \Core\Config::get('business.company_name');

        // Clean redundant suffixes from title
        $title = str_replace(' | ' . \Core\Config::get('business.company_name'), '', $title);

        // If title already has the full site name, don't append
        if (strpos($title, $siteName) !== false) {
            $fullTitle = $title;
        } elseif ($title === \Core\Config::get('business.company_name')) {
            // Exception for Home as requested
            $fullTitle = $title;
        } else {
            $fullTitle = $title ? "$title | $siteName" : $siteName;
        }

        $desc = $description ?: 'CRM Inteligente y Orquestación de Negocio para Empresas Tecnológicas.';

        return "
    <title>$fullTitle</title>
    <meta name='description' content='$desc'>
    <meta property='og:title' content='$fullTitle'>
    <meta property='og:description' content='$desc'>
    <meta property='og:type' content='website'>
";
    }

    /**
     * Genera JSON-LD para Organización.
     */
    public static function getOrganizationSchema()
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => \Core\Config::get('business.company_name'),
            'url' => getenv('APP_URL') ?: 'http://localhost',
            'logo' => (getenv('APP_URL') ?: 'http://localhost') . '/assets/images/logo.png',
            'description' => 'Servicios de Datos, BI y Desarrollo Web de Alta Performance.'
        ];
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
    }

    /**
     * Genera JSON-LD para Servicios (Pillars).
     */
    public static function getServicesSchema($pillars)
    {
        $items = [];
        foreach ($pillars as $p) {
            $items[] = [
                '@type' => 'Service',
                'name' => $p['title'],
                'description' => $p['subtitle'],
                'provider' => [
                    '@type' => 'Organization',
                    'name' => \Core\Config::get('business.company_name')
                ]
            ];
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => $items
        ];
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
    }
}
