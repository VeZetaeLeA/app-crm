<?php
/**
 * Overrides para entorno LOCAL
 */
return [
    'base_url' => getenv('APP_URL'),
    'debug' => true,
    'display_errors' => true,
    'mail_enabled' => getenv('MAIL_ENABLED') === 'true',
    'force_https' => false,
    'db' => [
        'host' => getenv('DB_HOST'),
        'name' => getenv('DB_DATABASE'),
        'user' => getenv('DB_USERNAME'),
        'pass' => getenv('DB_PASSWORD'),
    ]
];
