<?php
/**
 * Integration API Router
 * Routes requests to appropriate endpoint files
 * 
 * Supports:
 * - /api/v1/integration/products
 * - /api/v1/integration/orders
 * - /api/v1/integration/finance
 * - /api/v1/integration/auth
 */

// Get the request path
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

// Remove query string
$path = parse_url($requestUri, PHP_URL_PATH);

// For PHP built-in server, get the actual requested path
if (php_sapi_name() === 'cli-server') {
    // Extract endpoint from REQUEST_URI
    $pathParts = explode('/', trim($path, '/'));
    $endpoint = end($pathParts);
    
    // Remove 'index.php' or 'index' if present
    if ($endpoint === 'index.php' || $endpoint === 'index' || empty($endpoint)) {
        // Try to get from PATH_INFO or use default
        $pathInfo = $_SERVER['PATH_INFO'] ?? '';
        if ($pathInfo) {
            $pathInfoParts = explode('/', trim($pathInfo, '/'));
            $endpoint = end($pathInfoParts) ?: 'products';
        } else {
            $endpoint = 'products';
        }
    }
} else {
    // For Apache/Nginx, use standard routing
    $basePath = dirname($scriptName);
    if ($basePath !== '/' && strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
    }
    $path = trim($path, '/');
    $segments = explode('/', $path);
    $endpoint = end($segments);
    if (empty($endpoint) || $endpoint === 'index.php' || $endpoint === 'index') {
        $endpoint = 'products';
    }
}

// Map endpoints to files
$endpointMap = [
    'products' => __DIR__ . '/products.php',
    'orders' => __DIR__ . '/orders.php',
    'finance' => __DIR__ . '/finance.php',
    'auth' => __DIR__ . '/auth.php',
    'token' => __DIR__ . '/auth/token.php'
];

// Check if endpoint exists
if (isset($endpointMap[$endpoint]) && file_exists($endpointMap[$endpoint])) {
    // Include the endpoint file
    require_once $endpointMap[$endpoint];
    exit;
}

// If endpoint not found, try direct file access
$directFile = __DIR__ . '/' . $endpoint . '.php';
if (file_exists($directFile)) {
    require_once $directFile;
    exit;
}

// 404 Not Found
http_response_code(404);
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'error' => 'Endpoint not found',
    'message' => "The requested endpoint '{$endpoint}' does not exist. Available: products, orders, finance, auth"
]);
