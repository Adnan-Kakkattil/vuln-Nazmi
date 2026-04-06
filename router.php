<?php
/**
 * PHP Built-in Server Router
 * Use this when starting the PHP built-in server:
 * php -S localhost:8080 router.php
 */

$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove leading slash
$path = ltrim($path, '/');

// If it's a file that exists, serve it directly
if ($path && file_exists(__DIR__ . '/' . $path) && !is_dir(__DIR__ . '/' . $path)) {
    return false; // Let PHP serve the file
}

// Route API integration endpoints
if (preg_match('#^api/v1/integration/(products|orders|finance|auth|token)(?:\.php)?(?:\?.*)?$#', $path, $matches)) {
    $endpoint = $matches[1];
    
    $endpointMap = [
        'products' => __DIR__ . '/api/v1/integration/products.php',
        'orders' => __DIR__ . '/api/v1/integration/orders.php',
        'finance' => __DIR__ . '/api/v1/integration/finance.php',
        'auth' => __DIR__ . '/api/v1/integration/auth.php',
        'token' => __DIR__ . '/api/v1/integration/auth/token.php'
    ];
    
    if (isset($endpointMap[$endpoint]) && file_exists($endpointMap[$endpoint])) {
        require_once $endpointMap[$endpoint];
        exit;
    }
}

// Route auth/token specifically
if (preg_match('#^api/v1/integration/auth/token(?:\.php)?(?:\?.*)?$#', $path)) {
    $tokenFile = __DIR__ . '/api/v1/integration/auth/token.php';
    if (file_exists($tokenFile)) {
        require_once $tokenFile;
        exit;
    }
}

// For all other requests, let PHP handle normally
return false;
