<?php
/**
 * Admin Reports API
 * Endpoint: /api/v1/admin/reports.php
 * Handles report generation for admin panel
 * 
 * Methods:
 * - GET: Generate reports by type (sales, stock, finance, product)
 */

header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../../../includes/config.php';

// Database connection
$pdo = getDbConnection();

// Require admin authentication
$admin = requireAdminAuthOrDie();

/**
 * Send JSON response
 */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method !== 'GET') {
        sendResponse([
            'success' => false,
            'message' => 'Method not allowed'
        ], 405);
    }
    
    // Get parameters
    $reportType = $_GET['type'] ?? null;
    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? null;
    
    if (!$reportType) {
        sendResponse([
            'success' => false,
            'message' => 'Report type is required (sales, stock, finance, product)'
        ], 400);
    }
    
    if (!$dateFrom || !$dateTo) {
        sendResponse([
            'success' => false,
            'message' => 'Date range is required (date_from and date_to)'
        ], 400);
    }
    
    // Generate report based on type
    switch ($reportType) {
        case 'sales':
            generateSalesReport($pdo, $dateFrom, $dateTo);
            break;
            
        case 'stock':
            generateStockReport($pdo);
            break;
            
        case 'finance':
            generateFinancialReport($pdo, $dateFrom, $dateTo);
            break;
            
        case 'product':
            generateProductReport($pdo);
            break;
            
        default:
            sendResponse([
                'success' => false,
                'message' => 'Invalid report type. Use: sales, stock, finance, product'
            ], 400);
    }
    
} catch (Exception $e) {
    sendResponse([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ], 500);
}

/**
 * Generate Sales Report
 */
function generateSalesReport($pdo, $dateFrom, $dateTo) {
    // Get sales summary from orders
    $stmt = $pdo->prepare("
        SELECT 
            o.id,
            o.order_number,
            o.order_date,
            o.total_amount as amount,
            o.status,
            o.discount_amount,
            o.tax_amount,
            o.subtotal,
            CONCAT(o.shipping_first_name, ' ', o.shipping_last_name) as description,
            o.order_number as reference
        FROM orders o
        WHERE o.status NOT IN ('cancelled')
        AND DATE(o.order_date) >= ? AND DATE(o.order_date) <= ?
        ORDER BY o.order_date DESC
    ");
    $stmt->execute([$dateFrom, $dateTo]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Transform to frontend format
    $transactions = [];
    foreach ($orders as $order) {
        $transactions[] = [
            'id' => $order['id'],
            'date' => $order['order_date'],
            'type' => 'credit',
            'category' => 'sales',
            'description' => 'Order ' . $order['order_number'],
            'reference' => $order['order_number'],
            'amount' => floatval($order['amount'])
        ];
    }
    
    // Calculate summary
    $totalRevenue = array_sum(array_column($transactions, 'amount'));
    $totalOrders = count($transactions);
    $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
    
    sendResponse([
        'success' => true,
        'type' => 'sales',
        'data' => [
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_orders' => $totalOrders,
                'avg_order_value' => $avgOrderValue
            ],
            'transactions' => $transactions
        ]
    ]);
}

/**
 * Generate Stock Report
 */
function generateStockReport($pdo) {
    // Get all products
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.sku,
            p.name,
            p.price,
            p.stock_quantity as quantity,
            p.low_stock_threshold,
            p.status,
            c.name as category_name,
            c.slug as category_slug,
            CASE 
                WHEN p.stock_quantity = 0 THEN 'out'
                WHEN p.stock_quantity <= p.low_stock_threshold THEN 'low'
                WHEN p.stock_quantity < 30 THEN 'medium'
                ELSE 'high'
            END as stock_status
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status != 'discontinued'
        ORDER BY p.name
    ");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Transform to frontend format
    $transformedProducts = [];
    foreach ($products as $product) {
        $transformedProducts[] = [
            'id' => $product['id'],
            'sku' => $product['sku'],
            'name' => $product['name'],
            'price' => floatval($product['price']),
            'quantity' => intval($product['quantity']),
            'status' => $product['stock_status'],
            'category' => $product['category_slug'] ?? 'uncategorized'
        ];
    }
    
    // Calculate summary
    $totalProducts = count($transformedProducts);
    $inStock = count(array_filter($transformedProducts, fn($p) => $p['quantity'] > 0));
    $lowStock = count(array_filter($transformedProducts, fn($p) => $p['status'] === 'low'));
    $outOfStock = count(array_filter($transformedProducts, fn($p) => $p['status'] === 'out'));
    $totalValue = array_sum(array_map(fn($p) => $p['quantity'] * $p['price'], $transformedProducts));
    
    sendResponse([
        'success' => true,
        'type' => 'stock',
        'data' => [
            'summary' => [
                'total_products' => $totalProducts,
                'in_stock' => $inStock,
                'low_stock' => $lowStock,
                'out_of_stock' => $outOfStock,
                'total_value' => $totalValue
            ],
            'products' => $transformedProducts
        ]
    ]);
}

/**
 * Generate Financial Report
 */
function generateFinancialReport($pdo, $dateFrom, $dateTo) {
    // Get financial transactions
    $stmt = $pdo->prepare("
        SELECT 
            id,
            transaction_type,
            reference_type as category,
            amount,
            description,
            transaction_date as date,
            reference_id
        FROM financial_transactions
        WHERE DATE(transaction_date) >= ? AND DATE(transaction_date) <= ?
        ORDER BY transaction_date DESC
    ");
    $stmt->execute([$dateFrom, $dateTo]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Transform to frontend format
    $transformedTransactions = [];
    foreach ($transactions as $trans) {
        // Map transaction_type to frontend type
        $typeMap = [
            'sale' => 'credit',
            'refund' => 'debit',
            'expense' => 'debit',
            'purchase' => 'purchase'
        ];
        
        $frontendType = $typeMap[$trans['transaction_type']] ?? 'debit';
        
        $transformedTransactions[] = [
            'id' => $trans['id'],
            'date' => $trans['date'],
            'type' => $frontendType,
            'category' => $trans['category'],
            'description' => $trans['description'] ?? '',
            'reference' => $trans['reference_id'] ? 'REF-' . $trans['reference_id'] : null,
            'amount' => floatval($trans['amount'])
        ];
    }
    
    // Calculate summary
    $credits = array_sum(array_map(fn($t) => $t['type'] === 'credit' ? $t['amount'] : 0, $transformedTransactions));
    $debits = array_sum(array_map(fn($t) => $t['type'] === 'debit' ? $t['amount'] : 0, $transformedTransactions));
    $purchases = array_sum(array_map(fn($t) => $t['type'] === 'purchase' ? $t['amount'] : 0, $transformedTransactions));
    $netIncome = $credits - $debits - $purchases;
    
    sendResponse([
        'success' => true,
        'type' => 'finance',
        'data' => [
            'summary' => [
                'total_credit' => $credits,
                'total_debit' => $debits,
                'total_purchases' => $purchases,
                'net_income' => $netIncome
            ],
            'transactions' => $transformedTransactions
        ]
    ]);
}

/**
 * Generate Product Report
 */
function generateProductReport($pdo) {
    // Get all products with sales data
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.sku,
            p.name,
            p.price,
            p.stock_quantity as quantity,
            c.name as category_name,
            c.slug as category_slug,
            COALESCE(SUM(oi.quantity), 0) as total_sold,
            COALESCE(SUM(oi.total_price), 0) as total_revenue,
            COALESCE(COUNT(DISTINCT oi.order_id), 0) as times_ordered
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN order_items oi ON p.id = oi.product_id
        LEFT JOIN orders o ON oi.order_id = o.id AND o.status NOT IN ('cancelled')
        WHERE p.status != 'discontinued'
        GROUP BY p.id, p.sku, p.name, p.price, p.stock_quantity, c.name, c.slug
        ORDER BY total_revenue DESC, p.name
    ");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Transform to frontend format
    $transformedProducts = [];
    foreach ($products as $product) {
        $transformedProducts[] = [
            'id' => $product['id'],
            'sku' => $product['sku'],
            'name' => $product['name'],
            'price' => floatval($product['price']),
            'quantity' => intval($product['quantity']),
            'category' => $product['category_slug'] ?? 'uncategorized',
            'total_sold' => intval($product['total_sold']),
            'total_revenue' => floatval($product['total_revenue']),
            'times_ordered' => intval($product['times_ordered'])
        ];
    }
    
    sendResponse([
        'success' => true,
        'type' => 'product',
        'data' => [
            'summary' => [
                'total_products' => count($transformedProducts)
            ],
            'products' => $transformedProducts
        ]
    ]);
}
