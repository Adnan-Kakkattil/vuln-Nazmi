<?php
/**
 * Integration Finance API
 * Endpoint: /api/v1/integration/finance.php
 * Returns financial data for POS synchronization
 * 
 * Methods:
 * - GET: Get financial transactions/summary
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
requireScope($authData, 'finance');

$apiKeyId = $authData['api_key_id'];
$apiKey = $authData['api_key'];

try {
    // Get summary endpoint
    if (isset($_GET['summary'])) {
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $groupBy = $_GET['group_by'] ?? 'day'; // day, week, month
        
        $sql = 'SELECT 
                    DATE(transaction_date) as date,
                    transaction_type,
                    SUM(amount) as total_amount,
                    COUNT(*) as transaction_count
                FROM financial_transactions
                WHERE transaction_date BETWEEN :date_from AND :date_to
                GROUP BY DATE(transaction_date), transaction_type
                ORDER BY date DESC';
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':date_from' => $dateFrom,
            ':date_to' => $dateTo . ' 23:59:59'
        ]);
        $summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Also get order-based revenue
        $orderStmt = $pdo->prepare(
            'SELECT 
                DATE(order_date) as date,
                COUNT(*) as order_count,
                SUM(total_amount) as total_revenue,
                SUM(subtotal) as total_subtotal,
                SUM(tax_amount) as total_tax,
                SUM(discount_amount) as total_discount
             FROM orders
             WHERE order_date BETWEEN :date_from AND :date_to
               AND status NOT IN ("cancelled")
             GROUP BY DATE(order_date)
             ORDER BY date DESC'
        );
        $orderStmt->execute([
            ':date_from' => $dateFrom,
            ':date_to' => $dateTo . ' 23:59:59'
        ]);
        $orderSummary = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $responseTime = (int)((microtime(true) - $startTime) * 1000);
        logApiRequest($pdo, $apiKeyId, $apiKey, 'finance', 'GET', '/api/v1/integration/finance?summary=1',
                     $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null, 200, $responseTime);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'transactions' => $summary,
                'orders' => $orderSummary,
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ]
        ]);
        exit;
    }
    
    // Get transactions
    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? null;
    $transactionType = $_GET['transaction_type'] ?? null;
    $limit = min((int)($_GET['limit'] ?? 100), 500);
    $offset = (int)($_GET['offset'] ?? 0);
    
    $sql = 'SELECT * FROM financial_transactions WHERE 1=1';
    $params = [];
    
    if ($dateFrom) {
        $sql .= ' AND transaction_date >= :date_from';
        $params[':date_from'] = $dateFrom;
    }
    
    if ($dateTo) {
        $sql .= ' AND transaction_date <= :date_to';
        $params[':date_to'] = $dateTo . ' 23:59:59';
    }
    
    if ($transactionType) {
        $sql .= ' AND transaction_type = :transaction_type';
        $params[':transaction_type'] = $transactionType;
    }
    
    $sql .= ' ORDER BY transaction_date DESC
              LIMIT :limit OFFSET :offset';
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $responseTime = (int)((microtime(true) - $startTime) * 1000);
    logApiRequest($pdo, $apiKeyId, $apiKey, 'finance', 'GET', '/api/v1/integration/finance',
                 $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null, 200, $responseTime);
    
    echo json_encode([
        'success' => true,
        'data' => $transactions,
        'count' => count($transactions),
        'limit' => $limit,
        'offset' => $offset
    ]);
    
} catch (Exception $e) {
    $responseTime = (int)((microtime(true) - $startTime) * 1000);
    logApiRequest($pdo, $apiKeyId ?? null, $apiKey ?? null, 'finance', 'GET', '/api/v1/integration/finance',
                 $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null, 500, $responseTime, $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}
