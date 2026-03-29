<?php
/**
 * Configuración común para todos los entornos
 */
return [
    'name' => getenv('APP_NAME') ?: 'CRM OS',
    'app_key' => getenv('APP_KEY'),
    'timezone' => getenv('APP_TIMEZONE') ?: 'America/Argentina/Buenos_Aires',
    'charset' => getenv('APP_CHARSET') ?: 'UTF-8',
    'locale' => getenv('APP_LOCALE') ?: 'es',
    'security' => [
        'session_lifetime' => getenv('SESSION_LIFETIME') ?: 7200,
        'session_heartbeat' => getenv('SESSION_HEARTBEAT') ?: 300,
        'session_warning' => getenv('SESSION_WARNING') ?: 300,
        'csrf_token_name' => '_token',
        'auth_max_attempts' => getenv('AUTH_MAX_ATTEMPTS') ?: 5,
        'auth_rate_limit_decay' => getenv('AUTH_RATE_LIMIT_DECAY') ?: 60,
        'auth_brute_force_block' => getenv('AUTH_BRUTE_FORCE_BLOCK') ?: 1800,
        'auth_account_lock' => getenv('AUTH_ACCOUNT_LOCK') ?: 900,
        'hash_algo' => getenv('AUTH_HASH_ALGO') ?: 'argon2id',
        'jwt_secret' => getenv('JWT_SECRET'),
        'jwt_ttl' => getenv('JWT_TTL') ?: 3600,
        'global_rate_limit' => getenv('GLOBAL_RATE_LIMIT') ?: 100,
    ],
    'intelligence' => [
        'rbac_mode' => getenv('RBAC_MODE') ?: 'classic',
        'lead_scoring' => getenv('LEAD_SCORING_ENABLED') === 'true',
        'roi_metrics' => getenv('ROI_METRICS_ENABLED') === 'true',
        'internal_events' => getenv('INTERNAL_EVENTS_ENABLED') === 'true',
    ],
    'mail' => [
        'enabled' => getenv('MAIL_ENABLED') === 'true',
        'host' => getenv('MAIL_HOST'),
        'port' => getenv('MAIL_PORT'),
        'user' => getenv('MAIL_USERNAME'),
        'pass' => getenv('MAIL_PASSWORD'),
        'enc' => getenv('MAIL_ENCRYPTION'),
        'from_address' => getenv('MAIL_FROM_ADDRESS'),
        'from_name' => getenv('MAIL_FROM_NAME'),
    ],
    'business' => [
        'company_name' => getenv('COMPANY_NAME'),
        'company_slogan' => getenv('COMPANY_SLOGAN'),
        'company_mail' => getenv('COMPANY_MAIL'),
        'company_phone' => getenv('COMPANY_PHONE'),
        'company_address' => getenv('COMPANY_ADDRESS'),
        'company_est_year' => getenv('COMPANY_EST_YEAR') ?: '2017',
        'sla_response_time' => getenv('SLA_RESPONSE_TIME') ?: '24h',
        'currency_symbol' => getenv('CURRENCY_SYMBOL') ?: '$',
        'tax_rate' => getenv('TAX_RATE') ?: 0,
        'show_enterprise_profile' => getenv('SHOW_ENTERPRISE_PROFILE') === 'true',
        'years_exp' => getenv('COMPANY_YEARS_EXP') ?: 10,
        'projects_delivered' => getenv('COMPANY_PROJECTS_DELIVERED') ?: 0,
    ],
    'social' => [
        'instagram' => getenv('SOCIAL_INSTAGRAM') ?: '#',
        'linkedin' => getenv('SOCIAL_LINKEDIN') ?: '#',
        'twitter' => getenv('SOCIAL_TWITTER') ?: '#',
        'github' => getenv('SOCIAL_GITHUB') ?: '#',
        'facebook' => getenv('SOCIAL_FACEBOOK') ?: '#',
    ],
    'limits' => [
        'max_upload_size' => getenv('MAX_UPLOAD_SIZE') ?: 10485760, // 10MB
    ],
    'typography' => [
        'font_heading' => getenv('FONT_HEADING') ?: "'Space Grotesk', sans-serif",
        'font_body' => getenv('FONT_BODY') ?: "'Outfit', sans-serif",
        'scale_ratio' => getenv('TYPO_SCALE_RATIO') ?: 1.25,
    ],
    'payment' => [
        'mp_access_token' => getenv('MP_ACCESS_TOKEN') ?: '',
        'mp_public_key' => getenv('MP_PUBLIC_KEY') ?: '',
        'mp_currency_id' => getenv('MP_CURRENCY_ID') ?: 'ARS',
        'exchange_rate' => getenv('MP_EXCHANGE_RATE') ?: 1,
    ],
    'bank' => [
        'name' => getenv('BANK_NAME'),
        'account_name' => getenv('BANK_ACCOUNT_NAME'),
        'account_number' => getenv('BANK_ACCOUNT_NUMBER'),
        'cbu_alias' => getenv('BANK_CBU_ALIAS'),
    ]
];
