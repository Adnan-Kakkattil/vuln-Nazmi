<?php
/**
 * Admin Dashboard API
 * Endpoint: /api/v1/admin/dashboard.php
 * Returns dashboard statistics and data
 */

header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../../../includes/config.php';

// Require admin authentication
$admin = requireAdminAuthOrDie();

// Database connection
$pdo = getDbConnection();

/**
 * Send JSON response
 */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Get date range (default: last 30 days)
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

try {
    // Total Revenue
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total_amount), 0) as total_revenue
        FROM orders
        WHERE status NOT IN ('cancelled') 
        AND order_date >= ? AND order_date <= ?
    ");
    $stmt->execute([$startDate, $endDate]);
    $revenue = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Total Orders
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_orders
        FROM orders
        WHERE status NOT IN ('cancelled')
        AND order_date >= ? AND order_date <= ?
    ");
    $stmt->execute([$startDate, $endDate]);
    $orders = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Total Customers
    $stmt = $pdo->query("SELECT COUNT(DISTINCT id) as total_customers FROM users WHERE is_active = 1");
    $customers = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Total Products
    $stmt = $pdo->query("SELECT COUNT(*) as total_products FROM products WHERE status = 'active'");
    $products = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Low Stock Products
    $stmt = $pdo->query("
        SELECT COUNT(*) as low_stock_count
        FROM products
        WHERE status = 'active' AND stock_quantity <= low_stock_threshold
    ");
    $lowStock = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Recent Orders (last 10) with order items
    $stmt = $pdo->prepare("
        SELECT 
            o.id,
            o.order_number,
            o.total_amount,
            o.status,
            o.order_date,
            o.user_id,
            o.guest_name,
            o.guest_email,
            CONCAT(o.shipping_first_name, ' ', o.shipping_last_name) as customer_name
        FROM orders o
        WHERE o.status NOT IN ('cancelled')
        ORDER BY o.order_date DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get first product name for each order
    foreach ($recentOrders as &$order) {
        $itemsStmt = $pdo->prepare("
            SELECT product_name 
            FROM order_items 
            WHERE order_id = ? 
            LIMIT 1
        ");
        $itemsStmt->execute([$order['id']]);
        $firstItem = $itemsStmt->fetch(PDO::FETCH_ASSOC);
        $order['product_name'] = $firstItem['product_name'] ?? 'Multiple products';
        $order['customer_name'] = $order['customer_name'] ?: $order['guest_name'] ?: 'Guest';
    }
    
    // Top Products (by sales)
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.name,
            p.sku,
            SUM(oi.quantity) as total_sold,
            SUM(oi.total_price) as revenue
        FROM products p
        INNER JOIN order_items oi ON p.id = oi.product_id
        INNER JOIN orders o ON oi.order_id = o.id
        WHERE o.status NOT IN ('cancelled')
        AND o.order_date >= ? AND o.order_date <= ?
        GROUP BY p.id, p.name, p.sku
        ORDER BY total_sold DESC
        LIMIT 10
    ");
    $stmt->execute([$startDate, $endDate]);
    $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Revenue by day for chart (last N days based on period parameter)
    $chartPeriod = intval($_GET['period'] ?? 30);
    $stmt = $pdo->prepare("
        SELECT 
            DATE(order_date) as date,
            COUNT(*) as order_count,
            COALESCE(SUM(total_amount), 0) as revenue
        FROM orders
        WHERE status NOT IN ('cancelled')
        AND order_date >= DATE_SUB(?, INTERVAL ? DAY)
        GROUP BY DATE(order_date)
        ORDER BY date ASC
    ");
    $stmt->execute([$endDate, $chartPeriod]);
    $revenueChart = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Order Status Breakdown
    $stmt = $pdo->prepare("
        SELECT 
            status,
            COUNT(*) as count
        FROM orders
        WHERE order_date >= ? AND order_date <= ?
        GROUP BY status
    ");
    $stmt->execute([$startDate, $endDate]);
    $statusBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse([
        'success' => true,
        'data' => [
            'statistics' => [
                'total_revenue' => floatval($revenue['total_revenue']),
                'total_orders' => intval($orders['total_orders']),
                'total_customers' => intval($customers['total_customers']),
                'total_products' => intval($products['total_products']),
                'products_in_stock' => intval($products['total_products']), // Active products are in stock
                'low_stock_count' => intval($lowStock['low_stock_count'])
            ],
            'recent_orders' => $recentOrders,
            'top_products' => $topProducts,
            'revenue_chart' => $revenueChart,
            'status_breakdown' => $statusBreakdown,
            'date_range' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]
    ], 200);
    
} catch (PDOException $e) {
    error_log("Dashboard API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'Failed to fetch dashboard data',
        'error' => config('DEBUG', false) ? $e->getMessage() : 'Internal server error'
    ], 500);
}