<?php
/**
 * Admin Orders API
 * Endpoint: /api/v1/admin/orders.php
 * Handles order management for admin panel
 * 
 * Methods:
 * - GET: List orders with filters
 * - PUT: Update order status
 * - DELETE: Delete order (soft delete by changing status to cancelled)
 */

header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../../../includes/config.php';

// Database connection
$pdo = getDbConnection();

// Require admin authentication
$admin = requireAdminAuthOrDie();
requirePermission('view_orders');

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
    switch ($method) {
        case 'GET':
            // Check if requesting a single order
            $orderId = $_GET['id'] ?? null;
            if ($orderId) {
                // Get single order by ID or order_number
                if (is_numeric($orderId)) {
                    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
                    $stmt->execute([$orderId]);
                } else {
                    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
                    $stmt->execute([$orderId]);
                }
                $order = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$order) {
                    sendResponse([
                        'success' => false,
                        'message' => 'Order not found'
                    ], 404);
                }
                
                // Re-fetch single order with user join for consistency
                if (is_numeric($orderId)) {
                    $singleStmt = $pdo->prepare("
                        SELECT o.*, u.first_name as user_first_name, u.last_name as user_last_name, u.email as user_email
                        FROM orders o
                        LEFT JOIN users u ON o.user_id = u.id
                        WHERE o.id = ?
                    ");
                    $singleStmt->execute([$orderId]);
                } else {
                    $singleStmt = $pdo->prepare("
                        SELECT o.*, u.first_name as user_first_name, u.last_name as user_last_name, u.email as user_email
                        FROM orders o
                        LEFT JOIN users u ON o.user_id = u.id
                        WHERE o.order_number = ?
                    ");
                    $singleStmt->execute([$orderId]);
                }
                $orders = [$singleStmt->fetch(PDO::FETCH_ASSOC)];
                $total = 1;
                $page = 1;
                $limit = 1;
            } else {
                // List orders with filters
                $search = $_GET['search'] ?? null;
                $statusFilter = $_GET['status'] ?? 'all';
                $dateFilter = $_GET['date'] ?? null;
                $page = intval($_GET['page'] ?? 1);
                $limit = intval($_GET['limit'] ?? 100);
                $offset = ($page - 1) * $limit;
            
                // Build query
                $where = [];
                $params = [];
            
            if ($search) {
                $where[] = "(order_number LIKE ? OR shipping_first_name LIKE ? OR shipping_last_name LIKE ? OR guest_email LIKE ? OR guest_name LIKE ?)";
                $searchParam = "%{$search}%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            if ($statusFilter && $statusFilter !== 'all') {
                $where[] = "status = ?";
                $params[] = $statusFilter;
            }
            
            if ($dateFilter) {
                $where[] = "DATE(order_date) = ?";
                $params[] = $dateFilter;
            }
            
                $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
                
                // Get total count
                $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders {$whereClause}");
                if (!empty($params)) {
                    $countStmt->execute($params);
                } else {
                    $countStmt->execute();
                }
                $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            }
            
            // Get orders with user info (only if not single order)
            if (!$orderId) {
            $stmt = $pdo->prepare("
                SELECT 
                    o.id,
                    o.order_number,
                    o.user_id,
                    o.guest_email,
                    o.guest_name,
                    o.shipping_first_name,
                    o.shipping_last_name,
                    o.shipping_phone,
                    o.shipping_address_line1,
                    o.shipping_address_line2,
                    o.shipping_city,
                    o.shipping_state,
                    o.shipping_pincode,
                    o.shipping_country,
                    o.billing_address_line1,
                    o.billing_address_line2,
                    o.billing_city,
                    o.billing_state,
                    o.billing_pincode,
                    o.billing_country,
                    o.subtotal,
                    o.discount_amount,
                    o.tax_amount,
                    o.total_amount,
                    o.shipping_cost,
                    o.shipping_method_id,
                    o.payment_method,
                    o.payment_status,
                    o.status,
                    o.status_notes,
                    o.order_date,
                    o.confirmed_date,
                    o.processing_date,
                    o.shipped_date,
                    o.delivered_date,
                    o.cancelled_date,
                    o.cancelled_reason,
                    o.notes,
                    o.internal_notes,
                    o.created_at,
                    o.updated_at,
                    u.first_name as user_first_name,
                    u.last_name as user_last_name,
                    u.email as user_email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                {$whereClause}
                ORDER BY o.order_date DESC
                LIMIT ? OFFSET ?
            ");
            
                $queryParams = $params;
                $queryParams[] = $limit;
                $queryParams[] = $offset;
                $stmt->execute($queryParams);
                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            // Get order items and transform to frontend format
            foreach ($orders as &$order) {
                // Get order items
                $itemsStmt = $pdo->prepare("
                    SELECT 
                        id,
                        product_id,
                        product_name,
                        product_sku,
                        quantity,
                        unit_price,
                        discount_amount,
                        tax_amount,
                        total_price
                    FROM order_items
                    WHERE order_id = ?
                ");
                $itemsStmt->execute([$order['id']]);
                $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Transform items to frontend format
                $orderItems = [];
                foreach ($items as $item) {
                    $orderItems[] = [
                        'id' => $item['id'],
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product_name'],
                        'product_sku' => $item['product_sku'],
                        'quantity' => intval($item['quantity']),
                        'unit_price' => floatval($item['unit_price']),
                        'discount_amount' => floatval($item['discount_amount']),
                        'tax_amount' => floatval($item['tax_amount']),
                        'total_price' => floatval($item['total_price'])
                    ];
                }
                
                // Calculate total items count
                $itemsCount = array_sum(array_column($orderItems, 'quantity'));
                
                // Get customer info from joined user data or guest/shipping info
                $customerName = null;
                $customerEmail = null;
                
                if ($order['user_id'] && $order['user_first_name']) {
                    // Use user data from join
                    $customerName = trim(($order['user_first_name'] ?? '') . ' ' . ($order['user_last_name'] ?? ''));
                    $customerEmail = $order['user_email'] ?? null;
                } else {
                    // Fallback to guest/shipping info
                    $customerName = $order['guest_name'] ?? trim($order['shipping_first_name'] . ' ' . $order['shipping_last_name']);
                    $customerEmail = $order['guest_email'] ?? null;
                }
                
                // Transform to frontend format (keep both formats for compatibility)
                $order['orderId'] = $order['order_number'];
                $order['orderNumber'] = $order['order_number'];
                $order['orderDate'] = $order['order_date'];
                $order['order_date'] = $order['order_date']; // Keep for compatibility
                
                // Customer info (multiple formats for compatibility)
                $order['customer_name'] = $customerName;
                $order['customer_email'] = $customerEmail;
                $order['guest_name'] = $order['guest_name'] ?? null;
                $order['guest_email'] = $order['guest_email'] ?? null;
                $order['customer'] = [
                    'firstName' => $order['shipping_first_name'],
                    'lastName' => $order['shipping_last_name'],
                    'name' => $customerName,
                    'email' => $customerEmail,
                    'phone' => $order['shipping_phone'] ?? null
                ];
                
                // Items (multiple formats)
                $order['items'] = $orderItems;
                $order['cart'] = $orderItems; // For compatibility
                $order['itemsCount'] = $itemsCount;
                
                // Totals (keep both formats)
                $order['total_amount'] = floatval($order['total_amount']);
                $order['total'] = floatval($order['total_amount']);
                $order['subtotal'] = floatval($order['subtotal']);
                $order['discount_amount'] = floatval($order['discount_amount']);
                $order['tax_amount'] = floatval($order['tax_amount']);
                $order['shipping_cost'] = floatval($order['shipping_cost']);
                $order['totals'] = [
                    'subtotal' => floatval($order['subtotal']),
                    'discount' => floatval($order['discount_amount']),
                    'tax' => floatval($order['tax_amount']),
                    'shipping' => floatval($order['shipping_cost']),
                    'total' => floatval($order['total_amount'])
                ];
                
                // Payment (keep both formats)
                $order['payment_method'] = $order['payment_method'];
                $order['payment_status'] = $order['payment_status'];
                $order['payment'] = [
                    'method' => $order['payment_method'],
                    'status' => $order['payment_status']
                ];
                
                // Keep shipping details as top-level properties for detail view
                $order['shipping_phone'] = $order['shipping_phone'] ?? null;
                $order['shipping_address_line1'] = $order['shipping_address_line1'] ?? null;
                $order['shipping_address_line2'] = $order['shipping_address_line2'] ?? null;
                $order['shipping_city'] = $order['shipping_city'] ?? null;
                $order['shipping_state'] = $order['shipping_state'] ?? null;
                $order['shipping_pincode'] = $order['shipping_pincode'] ?? null;
                $order['shipping_country'] = $order['shipping_country'] ?? null;
            }
            
            if ($orderId) {
                // Single order response
                sendResponse([
                    'success' => true,
                    'data' => $orders[0] ?? null
                ]);
            } else {
                // List orders response
                sendResponse([
                    'success' => true,
                    'data' => $orders,
                    'pagination' => [
                        'total' => intval($total),
                        'page' => $page,
                        'limit' => $limit,
                        'pages' => ceil($total / $limit)
                    ]
                ]);
            }
            break;
            
        case 'PUT':
            // Update order status
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                sendResponse([
                    'success' => false,
                    'message' => 'Order ID is required'
                ], 400);
            }
            
            // Find order by order_number if id is not numeric
            $orderIdentifier = $data['id'];
            
            // Check if order exists
            if (is_numeric($orderIdentifier)) {
                $checkStmt = $pdo->prepare("SELECT id, order_number, status FROM orders WHERE id = ?");
                $checkStmt->execute([$orderIdentifier]);
            } else {
                $checkStmt = $pdo->prepare("SELECT id, order_number, status FROM orders WHERE order_number = ?");
                $checkStmt->execute([$orderIdentifier]);
            }
            
            $existingOrder = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if (!$existingOrder) {
                sendResponse([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }
            
            $orderId = $existingOrder['id'];
            $currentStatus = $existingOrder['status'];
            
            // Validate new status
            $validStatuses = ['confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
            $newStatus = $data['status'] ?? $currentStatus;
            
            if (!in_array($newStatus, $validStatuses)) {
                sendResponse([
                    'success' => false,
                    'message' => 'Invalid order status'
                ], 400);
            }
            
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                // Build update query
                $updateFields = [];
                $updateParams = [];
                
                if (isset($data['status']) && $data['status'] !== $currentStatus) {
                    $updateFields[] = "status = ?";
                    $updateParams[] = $newStatus;
                    
                    // Update status-specific date fields
                    $statusDateFields = [
                        'confirmed' => 'confirmed_date',
                        'processing' => 'processing_date',
                        'shipped' => 'shipped_date',
                        'delivered' => 'delivered_date',
                        'cancelled' => 'cancelled_date'
                    ];
                    
                    if (isset($statusDateFields[$newStatus])) {
                        $dateField = $statusDateFields[$newStatus];
                        $updateFields[] = "{$dateField} = NOW()";
                    }

                    // Auto-complete payment on delivery
                    if ($newStatus === 'delivered') {
                        $updateFields[] = "payment_status = 'completed'";
                    }
                }
                
                // Allow manual payment status update if provided
                if (isset($data['payment_status'])) {
                    $updateFields[] = "payment_status = ?";
                    $updateParams[] = $data['payment_status'];
                }
                
                if (isset($data['status_notes'])) {
                    $updateFields[] = "status_notes = ?";
                    $updateParams[] = $data['status_notes'];
                }
                
                if (isset($data['internal_notes'])) {
                    $updateFields[] = "internal_notes = ?";
                    $updateParams[] = $data['internal_notes'];
                }
                
                if (!empty($updateFields)) {
                    $updateParams[] = $orderId;
                    $updateSql = "UPDATE orders SET " . implode(', ', $updateFields) . " WHERE id = ?";
                    $updateStmt = $pdo->prepare($updateSql);
                    $updateStmt->execute($updateParams);
                    
                    // Insert status history if status changed
                    if (isset($data['status']) && $data['status'] !== $currentStatus) {
                        $historyStmt = $pdo->prepare("
                            INSERT INTO order_status_history (order_id, status, notes, changed_by)
                            VALUES (?, ?, ?, ?)
                        ");
                        $historyStmt->execute([
                            $orderId,
                            $newStatus,
                            $data['status_notes'] ?? null,
                            $admin['id']
                        ]);
                    }
                }
                
                // Log activity
                $logStmt = $pdo->prepare("
                    INSERT INTO admin_activity_log (user_id, action, module, entity_id, entity_type)
                    VALUES (?, 'update', 'orders', ?, 'order')
                ");
                $logStmt->execute([$admin['id'], $orderId]);
                
                $pdo->commit();
                
                sendResponse([
                    'success' => true,
                    'message' => 'Order status updated successfully',
                    'data' => [
                        'id' => $existingOrder['order_number'],
                        'status' => $newStatus
                    ]
                ]);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case 'DELETE':
            // Soft delete order (change status to cancelled)
            $orderIdentifier = $_GET['id'] ?? null;
            
            if (!$orderIdentifier) {
                sendResponse([
                    'success' => false,
                    'message' => 'Order ID is required'
                ], 400);
            }
            
            // Check if order exists
            if (is_numeric($orderIdentifier)) {
                $checkStmt = $pdo->prepare("SELECT id, order_number, status FROM orders WHERE id = ?");
                $checkStmt->execute([$orderIdentifier]);
            } else {
                $checkStmt = $pdo->prepare("SELECT id, order_number, status FROM orders WHERE order_number = ?");
                $checkStmt->execute([$orderIdentifier]);
            }
            
            $existingOrder = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if (!$existingOrder) {
                sendResponse([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }
            
            $orderId = $existingOrder['id'];
            
            // Don't allow deleting delivered orders
            if ($existingOrder['status'] === 'delivered') {
                sendResponse([
                    'success' => false,
                    'message' => 'Cannot delete delivered orders'
                ], 400);
            }
            
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                // Soft delete - change status to cancelled
                $updateStmt = $pdo->prepare("
                    UPDATE orders 
                    SET status = 'cancelled', cancelled_date = NOW(), cancelled_reason = 'Deleted by admin'
                    WHERE id = ?
                ");
                $updateStmt->execute([$orderId]);
                
                // Insert status history
                $historyStmt = $pdo->prepare("
                    INSERT INTO order_status_history (order_id, status, notes, changed_by)
                    VALUES (?, 'cancelled', 'Order deleted by admin', ?)
                ");
                $historyStmt->execute([$orderId, $admin['id']]);
                
                // Log activity
                $logStmt = $pdo->prepare("
                    INSERT INTO admin_activity_log (user_id, action, module, entity_id, entity_type)
                    VALUES (?, 'delete', 'orders', ?, 'order')
                ");
                $logStmt->execute([$admin['id'], $orderId]);
                
                $pdo->commit();
                
                sendResponse([
                    'success' => true,
                    'message' => 'Order cancelled/deleted successfully'
                ]);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        default:
            sendResponse([
                'success' => false,
                'message' => 'Method not allowed'
            ], 405);
            break;
    }
} catch (Exception $e) {
    sendResponse([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ], 500);
}
