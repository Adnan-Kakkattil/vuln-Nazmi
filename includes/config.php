<?php
/**
 * Application Configuration
 * Centralized configuration loading from .env
 */

// Prevent direct access
if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

// Load .env file
$envFile = __DIR__ . '/../.env';
$config = [];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments and empty lines
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        // Parse key=value pairs
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            // Process variable substitution
            $value = preg_replace_callback('/\$\{([^}]+)\}/', function($matches) use ($config) {
                return isset($config[$matches[1]]) ? $config[$matches[1]] : '';
            }, $value);
            
            // Use environment variable if available, otherwise use .env value
            $config[$key] = getenv($key) !== false ? getenv($key) : $value;
        }
    }
}

/**
 * Get configuration value
 * @param string $key Configuration key
 * @param mixed $default Default value if key doesn't exist
 * @return mixed
 */
function config($key, $default = null) {
    global $config;
    return isset($config[$key]) ? $config[$key] : $default;
}

/**
 * Get database configuration
 */
function getDbConfig() {
    return [
        'host' => config('DB_HOST', '127.0.0.1'),
        'port' => config('DB_PORT', 3306),
        'database' => config('DB_DATABASE', ''),
        'username' => config('DB_USERNAME', ''),
        'password' => config('DB_PASSWORD', ''),
        'charset' => config('DB_CHARSET', 'utf8mb4'),
        'collation' => config('DB_COLLATION', 'utf8mb4_unicode_ci'),
    ];
}

/**
 * Create PDO database connection
 * @return PDO
 */
function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        $db = getDbConfig();
        $dsn = sprintf(
            "mysql:host=%s;port=%d;dbname=%s;charset=%s",
            $db['host'],
            $db['port'],
            $db['database'],
            $db['charset']
        );
        
        try {
            $pdo = new PDO($dsn, $db['username'], $db['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ]);
        } catch (PDOException $e) {
            if (config('DEBUG', false)) {
                die("Database Connection Error: " . $e->getMessage());
            } else {
                die("Database connection failed. Please contact administrator.");
            }
        }
    }
    
    return $pdo;
}

// Define useful constants
define('APP_NAME', config('APP_NAME', 'BLine Boutique'));
define('APP_ENV', config('APP_ENV', 'production'));

// Auto-detect APP_URL from current request if not set or set to localhost
$configUrl = config('APP_URL', null);
$detectedUrl = $configUrl;

// If URL is empty, localhost, or 127.0.0.1, auto-detect from current request
if (empty($detectedUrl) || 
    strpos($detectedUrl, 'localhost') !== false || 
    strpos($detectedUrl, '127.0.0.1') !== false ||
    strpos($detectedUrl, '::1') !== false) {
    
    // Auto-detect from current request
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
               (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
    
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    
    // Remove standard ports from URL
    $host = preg_replace('/:80$/', '', $host);
    $host = preg_replace('/:443$/', '', $host);
    
    $detectedUrl = $protocol . '://' . $host;
}

define('APP_URL', $detectedUrl);
define('APP_TIMEZONE', config('APP_TIMEZONE', 'Asia/Kolkata'));

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Error reporting based on environment
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// CORS headers for API requests
if (!headers_sent()) {
    $allowedOrigins = explode(',', config('CORS_ALLOWED_ORIGINS', ''));
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: " . config('CORS_ALLOWED_METHODS', 'GET,POST,PUT,DELETE,OPTIONS'));
        header("Access-Control-Allow-Headers: " . config('CORS_ALLOWED_HEADERS', 'Content-Type,Authorization'));
    }

    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}