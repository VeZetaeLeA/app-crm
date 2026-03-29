<?php
/**
 * Overrides para entorno PRODUCTION
 */
return [
    'base_url' => getenv('APP_URL'),
    'debug' => false,
    'display_errors' => false,
    'mail_enabled' => getenv('MAIL_ENABLED') === 'true',
    'force_https' => true,
    'rate_limit' => true,
    'audit_active' => true,
    'db' => [
        'host' => getenv('DB_HOST'),
        'name' => getenv('DB_DATABASE'),
        'user' => getenv('DB_USERNAME'),
        'pass' => getenv('DB_PASSWORD'),
    ]
];
