<?php
namespace Core;

/**
 * Security Manager - Handles HTTP Headers and platform hardening
 */
class Security
{
    public static function setHeaders()
    {
        // 1. Prevents site from being embedded in an iframe (Anti-Clickjacking)
        header('X-Frame-Options: SAMEORIGIN');

        // 2. Prevents browser from sniffing the MIME type
        header('X-Content-Type-Options: nosniff');

        // 3. Enables XSS Filter and tells it to stop page execution if attack is detected
        header('X-XSS-Protection: 1; mode=block');

        // 4. Content Security Policy (Basic restrictive policy)
        // Allow images from current origin and data: (for logos/icons)
        // Allow scripts from current origin and common CDNs used in the project
        // Allow styles from current origin, Google Fonts, and Bootstrap CDNs
        $csp = "default-src 'self'; ";
        $csp .= "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; ";
        $csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://fonts.gstatic.com; ";
        $csp .= "font-src 'self' data: https://fonts.gstatic.com https://fonts.googleapis.com; ";
        $csp .= "img-src 'self' data: https://cdn.jsdelivr.net; ";
        $csp .= "frame-ancestors 'self'; ";
        $csp .= "base-uri 'self'; ";
        $csp .= "form-action 'self';";

        header("Content-Security-Policy: $csp");

        // 5. Strict-Transport-Security (HSTS) - Only if on HTTPS
        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }

        // 6. Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // 7. Permissions Policy (Disable dangerous features if not needed)
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    }

    private static function isHttps()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }
}
