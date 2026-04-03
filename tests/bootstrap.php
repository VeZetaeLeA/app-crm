<?php
/**
 * PHPUnit Bootstrap for VeZetaeLeA
 */

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/env.php';
\EnvLoader::load(BASE_PATH . '/.env');

require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/Core/Helpers.php';
require_once BASE_PATH . '/tests/RedirectException.php';

\Core\Config::load();
\Core\Session::start();

// Prevent real emails during testing
\Core\Config::set('mail.mail_enabled', false);

// Inform core that we are running tests
define('PHPUNIT_TESTING', true);
