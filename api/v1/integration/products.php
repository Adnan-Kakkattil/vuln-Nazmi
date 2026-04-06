<?php
/**
 * Integration Products API
 * Endpoint: /api/v1/integration/products.php
 * Returns products for POS synchronization
 * 
 * Methods:
 * - GET: List products with filters
 * - GET ?id={id}: Get single product
 */

header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Load dependencies
require_once __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/api_integration_helpers.php';

$pdo = getDbConnection();
$startTime = microtime(true);

// Authenticate request
$authData = requireIntegrationAuth();
requireScope($authData, 'products');

$apiKeyId = $authData['api_key_id'];
$apiKey = $authData['api_key'];

try {
    // Get single product by ID
    if (isset($_GET['id'])) {
        $productId = (int)$_GET['id'];
        
        $stmt = $pdo->prepare(
            'SELECT 
                p.*,
                c.name AS category_name,
                c.slug AS category_slug
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.id = :id'
        );
        $stmt->execute([':id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            logApiRequest($pdo, $apiKeyId, $apiKey, 'products', 'GET', '/api/v1/integration/products?id=' . $productId,
                         $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null, 404);
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            exit;
        }
        
        // Get images
        $imgStmt = $pdo->prepare(
            'SELECT image_url, alt_text, is_primary, sort_order 
             FROM product_images 
             WHERE product_id = :id 
             ORDER BY is_primary DESC, sort_order ASC'
        );
        $imgStmt->execute([':id' => $productId]);
        $product['images'] = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get specifications
        $specStmt = $pdo->prepare(
            'SELECT spec_key, spec_value 
             FROM product_specifications 
             WHERE product_id = :id 
             ORDER BY sort_order ASC'
        );
        $specStmt->execute([':id' => $productId]);
        $product['specifications'] = $specStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $responseTime = (int)((microtime(true) - $startTime) * 1000);
        logApiRequest($pdo, $apiKeyId, $apiKey, 'products', 'GET', '/api/v1/integration/products?id=' . $productId,
                     $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null, 200, $responseTime);
        
        echo json_encode([
            'success' => true,
            'data' => $product
        ]);
        exit;
    }
    
    // List products
    $since = $_GET['since'] ?? null;
    $limit = min((int)($_GET['limit'] ?? 100), 500);
    $offset = (int)($_GET['offset'] ?? 0);
    $status = $_GET['status'] ?? null;
    
    $sql = 'SELECT 
                p.id,
                p.sku,
                p.name,
                p.slug,
                p.short_description,
                p.full_description,
                p.category_id,
                c.name AS category_name,
                c.slug AS category_slug,
                p.price,
                p.original_price,
                p.cost_price,
                p.status,
                p.stock_quantity,
                p.low_stock_threshold,
                p.is_featured,
                p.is_new,
                p.weight_kg,
                p.dimensions_cm,
                p.warranty_months,
                p.updated_at,
                GROUP_CONCAT(DISTINCT pi.image_url ORDER BY pi.is_primary DESC, pi.sort_order ASC SEPARATOR ",") AS images
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            LEFT JOIN product_images pi ON pi.product_id = p.id
            WHERE 1=1';
    
    $params = [];
    
    if ($since) {
        $sql .= ' AND p.updated_at > :since';
        $params[':since'] = $since;
    }
    
    if ($status) {
        $sql .= ' AND p.status = :status';
        $params[':status'] = $status;
    }
    
    $sql .= ' GROUP BY p.id
              ORDER BY p.updated_at DESC
              LIMIT :limit OFFSET :offset';
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format products
    foreach ($products as &$product) {
        $product['images'] = $product['images'] ? explode(',', $product['images']) : [];
        $product['id'] = (int)$product['id'];
        $product['category_id'] = $product['category_id'] ? (int)$product['category_id'] : null;
        $product['price'] = (float)$product['price'];
        $product['original_price'] = $product['original_price'] ? (float)$product['original_price'] : null;
        $product['cost_price'] = $product['cost_price'] ? (float)$product['cost_price'] : null;
        $product['stock_quantity'] = (int)$product['stock_quantity'];
        $product['low_stock_threshold'] = (int)$product['low_stock_threshold'];
    }
    
    $responseTime = (int)((microtime(true) - $startTime) * 1000);
    logApiRequest($pdo, $apiKeyId, $apiKey, 'products', 'GET', '/api/v1/integration/products',
                 $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null, 200, $responseTime);
    
    echo json_encode([
        'success' => true,
        'data' => $products,
        'count' => count($products),
        'limit' => $limit,
        'offset' => $offset
    ]);
    
} catch (Exception $e) {
    $responseTime = (int)((microtime(true) - $startTime) * 1000);
    logApiRequest($pdo, $apiKeyId ?? null, $apiKey ?? null, 'products', 'GET', '/api/v1/integration/products',
                 $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null, 500, $responseTime, $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}
