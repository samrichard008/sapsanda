<?php
/**
 * SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
 * Application Configuration
 * Compatible with XAMPP, cPanel Shared Hosting & GitHub Deployment
 */

// Prevent multiple inclusions
if (defined('SAPSADA_CONFIG_LOADED')) {
    return;
}
define('SAPSADA_CONFIG_LOADED', true);

// Set default timezone to Sri Lanka
date_default_timezone_set('Asia/Colombo');

// Error reporting: E_ALL for development, easily toggled
$app_env = getenv('APP_ENV') ?: 'development';
if ($app_env === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Session security configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

// Database Credentials (Supports Environment Variables or Defaults)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'sapsada_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

// Directory Paths
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'includes');
define('CONFIG_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'config');
define('UPLOADS_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads');
define('ADMIN_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'admin');

// Dynamic Base URL calculation (works out-of-the-box on localhost/sapsanda or live domain)
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Calculate relative path from web server root to this directory
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    // If inside /admin, navigate back one level
    if (substr($scriptDir, -6) === '/admin') {
        $subDir = substr($scriptDir, 0, -6);
    } else {
        $subDir = $scriptDir;
    }
    $subDir = rtrim($subDir, '/');
    
    $baseUrl = getenv('APP_URL') ?: ($protocol . $host . $subDir);
    define('BASE_URL', rtrim($baseUrl, '/'));
    define('ADMIN_URL', BASE_URL . '/admin');
    define('ASSETS_URL', BASE_URL . '/assets');
    define('UPLOADS_URL', BASE_URL . '/uploads');
}

// Default Organization Identity
define('ORG_NAME', 'SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT');
define('ORG_REG_NO', 'G A 00282904');
define('ORG_EMAIL', 'sapsadaunit@gmail.com');
define('ORG_PHONE_1', '070 7261978');
define('ORG_PHONE_2', '072 253 2493');
define('ORG_HOTLINE', '0777 26 19 78');
define('ORG_ADDRESS', 'Jayabodhi Viharaya, Nalluruwa, Panadura, Sri Lanka');
define('ORG_WHATSAPP', '94707261978');

// Supported Languages
$supported_languages = ['en', 'si', 'ta'];
$default_language = 'en';

// Determine active language
if (isset($_GET['lang']) && in_array(strtolower($_GET['lang']), $supported_languages)) {
    $_SESSION['sapsada_lang'] = strtolower($_GET['lang']);
    setcookie('sapsada_lang', strtolower($_GET['lang']), time() + (86400 * 30), '/');
}

$current_lang = $_SESSION['sapsada_lang'] ?? ($_COOKIE['sapsada_lang'] ?? $default_language);
if (!in_array($current_lang, $supported_languages)) {
    $current_lang = $default_language;
}
define('CURRENT_LANG', $current_lang);
