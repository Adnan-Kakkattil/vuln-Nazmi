<?php
/**
 * Integration Orders API
 * Endpoint: /api/v1/integration/orders.php
 * Returns orders for POS synchronization
 * 
 * Methods:
 * - GET: List orders with filters
 * - GET ?id={id}: Get single order with items
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
requireScope($authData, 'orders');

$apiKeyId = $authData['api_key_id'];
$apiKey = $authData['api_key'];

try {
    // Get single order by ID
    if (isset($_GET['id'])) {
        $orderId = (int)$_GET['id'];
        
        $stmt = $pdo->prepare(
            'SELECT o.*,
                    u.email AS customer_email,
                    u.first_name AS customer_first_name,
                    u.last_name AS customer_last_name,
                    u.phone AS customer_phone
             FROM orders o
             LEFT JOIN users u ON u.id = o.user_id
             WHERE o.id = :id'
        );
        $stmt->execute([':id' => $orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            logApiRequest($pdo, $apiKeyId, $apiKey, 'orders', 'GET', '/api/v1/integration/orders?id=' . $orderId,
                         $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null, 404);
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
            exit;
        }
        
        // Get order items
        $itemsStmt = $pdo->prepare(
            'SELECT oi.*, p.sku, p.name AS product_name
             FROM order_items oi
             LEFT JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = :order_id'
        );
        $itemsStmt->execute([':order_id' => $orderId]);
        $order['items'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $responseTime = (int)((microtime(true) - $startTime) * 1000);
        logApiRequest($pdo, $apiKeyId, $apiKey, 'orders', 'GET', '/api/v1/integration/orders?id=' . $orderId,
                     $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null, 200, $responseTime);
        
        echo json_encode([
            'success' => true,
            'data' => $order
        ]);
        exit;
    }
    
    // List orders
    $since = $_GET['since'] ?? null;
    $status = $_GET['status'] ?? null;
    $limit = min((int)($_GET['limit'] ?? 50), 200);
    $offset = (int)($_GET['offset'] ?? 0);
    
    $sql = 'SELECT 
                o.*,
                u.email AS customer_email,
                u.first_name AS customer_first_name,
                u.last_name AS customer_last_name,
                u.phone AS customer_phone
            FROM orders o
            LEFT JOIN users u ON u.id = o.user_id
            WHERE 1=1';
    
    $params = [];
    
    if ($since) {
        $sql .= ' AND o.updated_at > :since';
        $params[':since'] = $since;
    }
    
    if ($status) {
        $sql .= ' AND o.status = :status';
        $params[':status'] = $status;
    }
    
    $sql .= ' ORDER BY o.order_date DESC
              LIMIT :limit OFFSET :offset';
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get order items for each order
    foreach ($orders as &$order) {
        $itemsStmt = $pdo->prepare(
            'SELECT 
                oi.*,
                p.sku,
                p.name AS product_name
             FROM order_items oi
             LEFT JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = :order_id'
        );
        $itemsStmt->execute([':order_id' => $order['id']]);
        $order['items'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    $responseTime = (int)((microtime(true) - $startTime) * 1000);
    logApiRequest($pdo, $apiKeyId, $apiKey, 'orders', 'GET', '/api/v1/integration/orders',
                 $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null, 200, $responseTime);
    
    echo json_encode([
        'success' => true,
        'data' => $orders,
        'count' => count($orders),
        'limit' => $limit,
        'offset' => $offset
    ]);
    
} catch (Exception $e) {
    $responseTime = (int)((microtime(true) - $startTime) * 1000);
    logApiRequest($pdo, $apiKeyId ?? null, $apiKey ?? null, 'orders', 'GET', '/api/v1/integration/orders',
                 $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null, 500, $responseTime, $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}
