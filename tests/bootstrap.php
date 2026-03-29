<?php
/**
 * PHPUnit Bootstrap for DataWyrd
 */

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/env.php';
\EnvLoader::load(BASE_PATH . '/.env');

require_once BASE_PATH . '/vendor/autoload.php';

\Core\Config::load();

// Prevent real emails during testing
\Core\Config::set('mail.mail_enabled', false);
