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
        'honeypot_enabled' => getenv('SECURITY_HONEYPOT_ENABLED') === 'true',
        'min_form_time' => getenv('SECURITY_MIN_FORM_TIME') ?: 3,
        'recaptcha_site_key' => getenv('RECAPTCHA_V3_SITE_KEY'),
        'recaptcha_secret_key' => getenv('RECAPTCHA_V3_SECRET_KEY'),
        'recaptcha_score' => getenv('RECAPTCHA_V3_SCORE_LMT') ?: 0.5,
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
        'font_url'    => getenv('FONT_URL') ?: 'https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=JetBrains+Mono:wght@400;500;600&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap',
        'font_heading' => getenv('FONT_HEADING') ?: "'Sora', system-ui, sans-serif",
        'font_body'   => getenv('FONT_BODY') ?: "'Inter', system-ui, sans-serif",
        'font_mono'   => getenv('FONT_MONO') ?: "'JetBrains Mono', 'Courier New', monospace",
        'scale_ratio' => getenv('TYPO_SCALE_RATIO') ?: 1.25,
    ],
    'ui' => [
        // Dark Mode Backgrounds
        'color_bg_dark'        => getenv('UI_COLOR_BG_DARK') ?: '#09090B',
        'color_surface_dark'   => getenv('UI_COLOR_SURFACE_DARK') ?: '#18181B',
        'color_surface_elev'   => getenv('UI_COLOR_SURFACE_ELEVATED') ?: '#27272A',
        'color_border_dark'    => getenv('UI_COLOR_BORDER_DARK') ?: 'rgba(255,255,255,0.06)',
        // Light Mode Backgrounds
        'color_bg_light'       => getenv('UI_COLOR_BG_LIGHT') ?: '#FAFAFA',
        'color_surface_light'  => getenv('UI_COLOR_SURFACE_LIGHT') ?: '#FFFFFF',
        'color_border_light'   => getenv('UI_COLOR_BORDER_LIGHT') ?: 'rgba(9, 9, 11, 0.08)',
        // Brand Accents
        'primary_color'        => getenv('UI_PRIMARY_COLOR') ?: '#6366F1',
        'secondary_color'      => getenv('UI_SECONDARY_COLOR') ?: '#DB2777',
        'gold_color'           => getenv('UI_GOLD_COLOR') ?: '#D4AF37',
        // Semantic / State Colors (CRM critical)
        'color_success'        => getenv('UI_COLOR_SUCCESS') ?: '#10B981',
        'color_warning'        => getenv('UI_COLOR_WARNING') ?: '#F59E0B',
        'color_danger'         => getenv('UI_COLOR_DANGER') ?: '#EF4444',
        'color_info'           => getenv('UI_COLOR_INFO') ?: '#3B82F6',
        // Border Radius
        'radius_sm'            => getenv('UI_RADIUS_SM') ?: '6px',
        'radius_md'            => getenv('UI_RADIUS_MD') ?: '12px',
        'radius_lg'            => getenv('UI_RADIUS_LG') ?: '20px',
        'radius_pill'          => getenv('UI_RADIUS_PILL') ?: '50rem',
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
