<?php
/**
 * Application Configuration
 */

define('APP_NAME', 'Hotel PMS Training Platform');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/pms-training');

define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/data/uploads');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set 1 if HTTPS
session_start();

// Error reporting (0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_LIFETIME', 7200); // 2 hours

// Pagination
define('PER_PAGE', 20);

// Date formats
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i');
