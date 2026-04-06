<?php
/**
 * Public Orders API
 * Endpoint: /api/v1/orders.php
 * Handles order creation (checkout) and order retrieval for customers
 * 
 * Methods:
 * - GET: Get user's orders (requires login) or single order by order_number
 * - POST: Create a new order (checkout)
 */

header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../../includes/config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

/**
 * Generate unique order number
 */
function generateOrderNumber($pdo) {
    $prefix = 'ORD';
    $timestamp = date('YmdHis');
    $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    $orderNumber = "{$prefix}-{$timestamp}-{$random}";
    
    // Check if exists and regenerate if needed
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNumber]);
    if ($stmt->fetch()) {
        return generateOrderNumber($pdo); // Recursive call to generate new number
    }
    
    return $orderNumber;
}

// Get user info - support both regular users and admins
$userId = $_SESSION['user_id'] ?? null;
$adminId = $_SESSION['admin_id'] ?? null;
$sessionId = session_id();

// For viewing orders, allow both user_id and admin_id
// Admin users might also have placed orders
$effectiveUserId = $userId ?: $adminId;

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGetOrders($pdo, $effectiveUserId);
            break;
        case 'POST':
            handleCreateOrder($pdo, $userId, $sessionId);
            break;
        case 'PATCH':
            handleCancelOrder($pdo, $effectiveUserId);
            break;
        default:
            sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    error_log("Orders API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage()
    ], 500);
}

/**
 * Cancel an order
 */
function handleCancelOrder($pdo, $userId) {
    if (!$userId) {
        sendResponse(['success' => false, 'message' => 'Login required to cancel orders'], 401);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $orderNumber = $input['order_number'] ?? null;
    $reason = $input['reason'] ?? 'Cancelled by customer';

    if (!$orderNumber) {
        sendResponse(['success' => false, 'message' => 'Order number is required'], 400);
    }

    // Check if order exists and belongs to user
    $stmt = $pdo->prepare("SELECT id, status, user_id FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        sendResponse(['success' => false, 'message' => 'Order not found'], 404);
    }

    if ($order['user_id'] != $userId) {
        sendResponse(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    // Only allow cancellation for certain statuses
    $allowCancel = ['pending', 'confirmed'];
    if (!in_array($order['status'], $allowCancel)) {
        sendResponse(['success' => false, 'message' => 'Order cannot be cancelled in current status: ' . $order['status']], 400);
    }

    $pdo->beginTransaction();
    try {
        // Update order status
        $updateStmt = $pdo->prepare("
            UPDATE orders 
            SET status = 'cancelled', 
                cancelled_date = NOW(), 
                cancelled_reason = ? 
            WHERE id = ?
        ");
        $updateStmt->execute([$reason, $order['id']]);

        // Restore stock
        $itemsStmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        $itemsStmt->execute([$order['id']]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as $item) {
            // Update product stock
            $stockStmt = $pdo->prepare("
                UPDATE products 
                SET stock_quantity = stock_quantity + ?, 
                    sold_count = sold_count - ?
                WHERE id = ?
            ");
            $stockStmt->execute([$item['quantity'], $item['quantity'], $item['product_id']]);

            // Create stock movement record
            $movementStmt = $pdo->prepare("
                INSERT INTO stock_movements (
                    product_id,
                    movement_type,
                    quantity,
                    previous_quantity,
                    new_quantity,
                    reference_type,
                    reference_id,
                    notes
                ) VALUES (?, 'restock', ?, 
                    (SELECT stock_quantity - ? FROM products WHERE id = ?), 
                    (SELECT stock_quantity FROM products WHERE id = ?), 
                    'order_cancel', ?, 'Order Cancelled: " . $orderNumber . "')
            ");
            $movementStmt->execute([
                $item['product_id'],
                $item['quantity'],
                $item['quantity'],
                $item['product_id'],
                $item['product_id'],
                $order['id']
            ]);
        }

        // Insert status history
        $historyStmt = $pdo->prepare("
            INSERT INTO order_status_history (order_id, status, notes)
            VALUES (?, 'cancelled', ?)
        ");
        $historyStmt->execute([$order['id'], $reason]);

        $pdo->commit();
        sendResponse(['success' => true, 'message' => 'Order cancelled successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Get orders for logged in user or single order by order_number
 */
function handleGetOrders($pdo, $userId) {
    $orderNumber = $_GET['order_number'] ?? null;
    
    // If order_number is provided, get single order
    if ($orderNumber) {
        $stmt = $pdo->prepare("
            SELECT 
                o.*,
                sm.name as shipping_method_name,
                sm.code as shipping_method_code
            FROM orders o
            LEFT JOIN shipping_methods sm ON o.shipping_method_id = sm.id
            WHERE o.order_number = ?
        ");
        $stmt->execute([$orderNumber]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            sendResponse(['success' => false, 'message' => 'Order not found'], 404);
        }
        
        // Get order items
        $itemsStmt = $pdo->prepare("
            SELECT 
                oi.*,
                (SELECT image_url FROM product_images WHERE product_id = oi.product_id AND is_primary = 1 LIMIT 1) as image
            FROM order_items oi
            WHERE oi.order_id = ?
        ");
        $itemsStmt->execute([$order['id']]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get order status history
        $historyStmt = $pdo->prepare("
            SELECT status, notes, created_at
            FROM order_status_history
            WHERE order_id = ?
            ORDER BY created_at ASC
        ");
        $historyStmt->execute([$order['id']]);
        $history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Transform to frontend format
        $responseOrder = [
            'order_number' => $order['order_number'],
            'status' => $order['status'],
            'payment_status' => $order['payment_status'],
            'payment_method' => $order['payment_method'],
            'order_date' => $order['order_date'],
            'customer' => [
                'first_name' => $order['shipping_first_name'],
                'last_name' => $order['shipping_last_name'],
                'email' => $order['guest_email'],
                'phone' => $order['shipping_phone']
            ],
            'shipping_address' => [
                'address_line1' => $order['shipping_address_line1'],
                'address_line2' => $order['shipping_address_line2'],
                'city' => $order['shipping_city'],
                'state' => $order['shipping_state'],
                'pincode' => $order['shipping_pincode'],
                'country' => $order['shipping_country']
            ],
            'shipping_method' => [
                'name' => $order['shipping_method_name'],
                'code' => $order['shipping_method_code'],
                'cost' => floatval($order['shipping_cost'])
            ],
            'items' => array_map(function($item) {
                return [
                    'product_id' => intval($item['product_id']),
                    'name' => $item['product_name'],
                    'sku' => $item['product_sku'],
                    'quantity' => intval($item['quantity']),
                    'unit_price' => floatval($item['unit_price']),
                    'total_price' => floatval($item['total_price']),
                    'image' => $item['image']
                ];
            }, $items),
            'totals' => [
                'subtotal' => floatval($order['subtotal']),
                'discount' => floatval($order['discount_amount']),
                'tax' => floatval($order['tax_amount']),
                'tax_rate' => floatval($order['tax_rate']),
                'shipping' => floatval($order['shipping_cost']),
                'total' => floatval($order['total_amount'])
            ],
            'status_history' => $history,
            'dates' => [
                'ordered' => $order['order_date'],
                'confirmed' => $order['confirmed_date'],
                'processing' => $order['processing_date'],
                'shipped' => $order['shipped_date'],
                'delivered' => $order['delivered_date']
            ],
            'notes' => $order['notes']
        ];
        
        sendResponse([
            'success' => true,
            'data' => $responseOrder
        ]);
    }
    
    // Get all orders for logged in user
    if (!$userId) {
        sendResponse(['success' => false, 'message' => 'Login required to view orders'], 401);
    }
    
    $page = intval($_GET['page'] ?? 1);
    $limit = intval($_GET['limit'] ?? 10);
    $offset = ($page - 1) * $limit;
    
    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
    $countStmt->execute([$userId]);
    $total = $countStmt->fetch()['total'];
    
    // Get orders
    $stmt = $pdo->prepare("
        SELECT 
            o.order_number,
            o.status,
            o.payment_status,
            o.payment_method,
            o.subtotal,
            o.tax_amount,
            o.shipping_cost,
            o.total_amount,
            o.order_date,
            (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
        FROM orders o
        WHERE o.user_id = ?
        ORDER BY o.order_date DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$userId, $limit, $offset]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse([
        'success' => true,
        'data' => array_map(function($order) {
            return [
                'order_number' => $order['order_number'],
                'status' => $order['status'],
                'payment_status' => $order['payment_status'],
                'payment_method' => $order['payment_method'],
                'items_count' => intval($order['items_count']),
                'subtotal' => floatval($order['subtotal']),
                'tax' => floatval($order['tax_amount']),
                'shipping' => floatval($order['shipping_cost']),
                'total' => floatval($order['total_amount']),
                'order_date' => $order['order_date']
            ];
        }, $orders),
        'pagination' => [
            'total' => intval($total),
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

/**
 * Create a new order (checkout)
 */
function handleCreateOrder($pdo, $userId, $sessionId) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $requiredFields = ['customer', 'shipping', 'payment'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            sendResponse(['success' => false, 'message' => "Missing required field: {$field}"], 400);
        }
    }
    
    // Validate customer info
    $customer = $input['customer'];
    if (empty($customer['firstName']) || empty($customer['lastName']) || 
        empty($customer['email']) || empty($customer['phone'])) {
        sendResponse(['success' => false, 'message' => 'Missing customer information'], 400);
    }
    
    // Validate shipping info
    $shipping = $input['shipping'];
    if (empty($shipping['addressLine1']) || empty($shipping['city']) || 
        empty($shipping['state']) || empty($shipping['pincode'])) {
        sendResponse(['success' => false, 'message' => 'Missing shipping address'], 400);
    }
    
    // Validate payment method
    $payment = $input['payment'];
    $validPaymentMethods = ['online', 'cod', 'upi', 'card'];
    if (empty($payment['method']) || !in_array($payment['method'], $validPaymentMethods)) {
        sendResponse(['success' => false, 'message' => 'Invalid payment method'], 400);
    }
    
    // Get cart items from database
    // Use COALESCE to get price from cart (snapshot) or product's current price
    $cartQuery = "
        SELECT 
            ci.product_id,
            ci.quantity,
            p.name,
            p.sku,
            COALESCE(NULLIF(ci.price, 0), p.price) as price,
            p.stock_quantity
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE " . ($userId ? "ci.user_id = :user_id" : "ci.session_id = :session_id");
    
    $cartStmt = $pdo->prepare($cartQuery);
    if ($userId) {
        $cartStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    } else {
        $cartStmt->bindValue(':session_id', $sessionId, PDO::PARAM_STR);
    }
    $cartStmt->execute();
    $cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($cartItems)) {
        sendResponse(['success' => false, 'message' => 'Cart is empty'], 400);
    }
    
    // Validate stock and calculate totals
    $subtotal = 0;
    $orderItems = [];
    
    foreach ($cartItems as $item) {
        if ($item['stock_quantity'] < $item['quantity']) {
            sendResponse([
                'success' => false, 
                'message' => "Insufficient stock for: {$item['name']}. Available: {$item['stock_quantity']}"
            ], 400);
        }
        
        $itemTotal = $item['price'] * $item['quantity'];
        $subtotal += $itemTotal;
        
        $orderItems[] = [
            'product_id' => $item['product_id'],
            'name' => $item['name'],
            'sku' => $item['sku'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['price'],
            'total_price' => $itemTotal
        ];
    }
    
    // Calculate shipping cost
    $shippingMethod = $shipping['method'] ?? 'standard';
    $shippingCost = 0;
    $shippingMethodId = null;
    
    $shippingStmt = $pdo->prepare("SELECT id, cost FROM shipping_methods WHERE code = ? AND is_active = 1");
    $shippingStmt->execute([$shippingMethod]);
    $shippingMethodRow = $shippingStmt->fetch();
    
    if ($shippingMethodRow) {
        $shippingMethodId = $shippingMethodRow['id'];
        $shippingCost = floatval($shippingMethodRow['cost']);
    }
    
    // Get tax rate from request data or settings
    $taxRate = 18.00; // Default fallback
    $taxAmount = 0;
    
    // Check if tax rate is provided in request totals
    if (isset($input['totals']['tax_rate']) && $input['totals']['tax_rate'] > 0) {
        $taxRate = floatval($input['totals']['tax_rate']);
        // Calculate tax amount if not provided
        if (isset($input['totals']['tax']) && $input['totals']['tax'] > 0) {
            $taxAmount = round(floatval($input['totals']['tax']), 2);
        } else {
            $taxAmount = round($subtotal * ($taxRate / 100), 2);
        }
    } else if (isset($input['totals']['tax']) && $input['totals']['tax'] > 0) {
        // Tax amount provided but no rate - calculate rate from amount
        $taxAmount = round(floatval($input['totals']['tax']), 2);
        if ($subtotal > 0) {
            $taxRate = round(($taxAmount / $subtotal) * 100, 2);
        }
    } else {
        // Get tax rate from settings
        $settingsStmt = $pdo->prepare("SELECT value FROM settings WHERE key_name = 'tax_rate'");
        $settingsStmt->execute();
        $taxRateSetting = $settingsStmt->fetchColumn();
        
        if ($taxRateSetting !== false && $taxRateSetting > 0) {
            $taxRate = floatval($taxRateSetting);
        }
        
        // Calculate tax amount
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
    }
    
    // Apply discount if coupon provided
    $discountAmount = 0;
    $couponId = null;
    if (!empty($input['coupon_code'])) {
        $couponCode = strtoupper(trim($input['coupon_code']));
        
        // Validate coupon
        $couponStmt = $pdo->prepare("
            SELECT 
                id,
                discount_type,
                discount_value,
                minimum_purchase_amount,
                maximum_discount_amount,
                usage_limit,
                valid_from,
                valid_until,
                is_active
            FROM coupons
            WHERE code = ?
        ");
        $couponStmt->execute([$couponCode]);
        $coupon = $couponStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($coupon) {
            // Check usage count
            $usageStmt = $pdo->prepare("SELECT COUNT(*) as count FROM coupon_usage WHERE coupon_id = ?");
            $usageStmt->execute([$coupon['id']]);
            $usedCount = intval($usageStmt->fetch(PDO::FETCH_ASSOC)['count']);
            
            // Check if coupon is valid
            $today = date('Y-m-d');
            $validFrom = date('Y-m-d', strtotime($coupon['valid_from']));
            $validUntil = date('Y-m-d', strtotime($coupon['valid_until']));
            
            $isValid = $coupon['is_active'] 
                && $validUntil >= $today 
                && $validFrom <= $today
                && (!$coupon['usage_limit'] || $usedCount < $coupon['usage_limit']);
            
            if ($isValid) {
                $minPurchase = floatval($coupon['minimum_purchase_amount'] ?? 0);
                
                // Check minimum purchase requirement
                if ($minPurchase > 0 && $subtotal < $minPurchase) {
                    // Minimum purchase not met, but don't fail order - just don't apply discount
                    $discountAmount = 0;
                } else {
                    // Calculate discount
                    if ($coupon['discount_type'] === 'percentage') {
                        $discountAmount = ($subtotal * floatval($coupon['discount_value'])) / 100;
                        $maxDiscount = $coupon['maximum_discount_amount'] ? floatval($coupon['maximum_discount_amount']) : null;
                        if ($maxDiscount !== null && $discountAmount > $maxDiscount) {
                            $discountAmount = $maxDiscount;
                        }
                    } else { // fixed_amount
                        $discountAmount = floatval($coupon['discount_value']);
                        if ($discountAmount > $subtotal) {
                            $discountAmount = $subtotal;
                        }
                    }
                    
                    $discountAmount = round($discountAmount, 2);
                    $couponId = $coupon['id'];
                }
            }
        }
    }
    
    // Calculate total
    $totalAmount = $subtotal + $taxAmount + $shippingCost - $discountAmount;
    
    // Generate order number
    $orderNumber = generateOrderNumber($pdo);
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Get billing address (use shipping if not provided)
        $billing = $input['billing'] ?? null;
        $billingAddressLine1 = $billing ? ($billing['addressLine1'] ?? null) : null;
        $billingAddressLine2 = $billing ? ($billing['addressLine2'] ?? null) : null;
        $billingCity = $billing ? ($billing['city'] ?? null) : null;
        $billingState = $billing ? ($billing['state'] ?? null) : null;
        $billingPincode = $billing ? ($billing['pincode'] ?? null) : null;
        $billingCountry = $billing ? ($billing['country'] ?? 'India') : null;
        
        // Create order
        $orderStmt = $pdo->prepare("
            INSERT INTO orders (
                order_number,
                user_id,
                guest_email,
                guest_name,
                shipping_first_name,
                shipping_last_name,
                shipping_address_line1,
                shipping_address_line2,
                shipping_city,
                shipping_state,
                shipping_pincode,
                shipping_country,
                shipping_phone,
                billing_address_line1,
                billing_address_line2,
                billing_city,
                billing_state,
                billing_pincode,
                billing_country,
                shipping_method_id,
                shipping_cost,
                subtotal,
                discount_amount,
                tax_amount,
                tax_rate,
                total_amount,
                payment_method,
                payment_status,
                status,
                order_date,
                confirmed_date,
                notes
            ) VALUES (
                :order_number,
                :user_id,
                :guest_email,
                :guest_name,
                :shipping_first_name,
                :shipping_last_name,
                :shipping_address_line1,
                :shipping_address_line2,
                :shipping_city,
                :shipping_state,
                :shipping_pincode,
                :shipping_country,
                :shipping_phone,
                :billing_address_line1,
                :billing_address_line2,
                :billing_city,
                :billing_state,
                :billing_pincode,
                :billing_country,
                :shipping_method_id,
                :shipping_cost,
                :subtotal,
                :discount_amount,
                :tax_amount,
                :tax_rate,
                :total_amount,
                :payment_method,
                :payment_status,
                :status,
                NOW(),
                NOW(),
                :notes
            )
        ");
        
        $orderStmt->execute([
            ':order_number' => $orderNumber,
            ':user_id' => $userId,
            ':guest_email' => $customer['email'],
            ':guest_name' => trim($customer['firstName'] . ' ' . $customer['lastName']),
            ':shipping_first_name' => $customer['firstName'],
            ':shipping_last_name' => $customer['lastName'],
            ':shipping_address_line1' => $shipping['addressLine1'],
            ':shipping_address_line2' => $shipping['addressLine2'] ?? '',
            ':shipping_city' => $shipping['city'],
            ':shipping_state' => $shipping['state'],
            ':shipping_pincode' => $shipping['pincode'],
            ':shipping_country' => $shipping['country'] ?? 'India',
            ':shipping_phone' => $customer['phone'],
            ':billing_address_line1' => $billingAddressLine1,
            ':billing_address_line2' => $billingAddressLine2,
            ':billing_city' => $billingCity,
            ':billing_state' => $billingState,
            ':billing_pincode' => $billingPincode,
            ':billing_country' => $billingCountry,
            ':shipping_method_id' => $shippingMethodId,
            ':shipping_cost' => $shippingCost,
            ':subtotal' => $subtotal,
            ':discount_amount' => $discountAmount,
            ':tax_amount' => $taxAmount,
            ':tax_rate' => $taxRate,
            ':total_amount' => $totalAmount,
            ':payment_method' => $payment['method'],
            ':payment_status' => $payment['method'] === 'cod' ? 'pending' : 'pending',
            ':status' => 'confirmed',
            ':notes' => $input['notes'] ?? null
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // Record coupon usage if coupon was applied
        if ($couponId && $discountAmount > 0) {
            $couponUsageStmt = $pdo->prepare("
                INSERT INTO coupon_usage (coupon_id, order_id, user_id, discount_amount)
                VALUES (?, ?, ?, ?)
            ");
            $couponUsageStmt->execute([
                $couponId,
                $orderId,
                $userId,
                $discountAmount
            ]);
        }
        
        // Create order items
        $itemStmt = $pdo->prepare("
            INSERT INTO order_items (
                order_id,
                product_id,
                product_name,
                product_sku,
                quantity,
                unit_price,
                total_price
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($orderItems as $item) {
            $itemStmt->execute([
                $orderId,
                $item['product_id'],
                $item['name'],
                $item['sku'],
                $item['quantity'],
                $item['unit_price'],
                $item['total_price']
            ]);
            
            // Update product stock
            $stockStmt = $pdo->prepare("
                UPDATE products 
                SET stock_quantity = stock_quantity - ?, 
                    sold_count = sold_count + ?
                WHERE id = ?
            ");
            $stockStmt->execute([$item['quantity'], $item['quantity'], $item['product_id']]);
            
            // Create stock movement record
            $movementStmt = $pdo->prepare("
                INSERT INTO stock_movements (
                    product_id,
                    movement_type,
                    quantity,
                    previous_quantity,
                    new_quantity,
                    reference_type,
                    reference_id,
                    notes
                ) VALUES (?, 'sale', ?, 
                    (SELECT stock_quantity + ? FROM products WHERE id = ?), 
                    (SELECT stock_quantity FROM products WHERE id = ?), 
                    'order', ?, 'Order: " . $orderNumber . "')
            ");
            $movementStmt->execute([
                $item['product_id'],
                -$item['quantity'],
                $item['quantity'],
                $item['product_id'],
                $item['product_id'],
                $orderId
            ]);
        }
        
        // Insert order status history
        $historyStmt = $pdo->prepare("
            INSERT INTO order_status_history (order_id, status, notes)
            VALUES (?, 'confirmed', 'Order placed successfully')
        ");
        $historyStmt->execute([$orderId]);
        
        // Clear cart
        $clearCartQuery = "DELETE FROM cart_items WHERE " . ($userId ? "user_id = ?" : "session_id = ?");
        $clearCartStmt = $pdo->prepare($clearCartQuery);
        $clearCartStmt->execute([$userId ? $userId : $sessionId]);
        
        $pdo->commit();
        
        // Auto-sync order to POS system immediately (non-blocking)
        try {
            require_once __DIR__ . '/../../includes/api_integration_helpers.php';
            syncOrderToPOS($pdo, $orderId, true); // true = async/non-blocking
        } catch (Exception $e) {
            error_log("Auto-sync order failed: " . $e->getMessage());
            // Don't fail order creation if sync fails
        }
        
        // Send order confirmation emails
        try {
            require_once __DIR__ . '/../../includes/email/EmailService.php';
            
            // Fetch complete order data with items
            $orderStmt = $pdo->prepare("
                SELECT o.*, 
                       u.email as customer_email,
                       u.first_name as customer_first_name,
                       u.last_name as customer_last_name
                FROM orders o
                LEFT JOIN users u ON u.id = o.user_id
                WHERE o.id = ?
            ");
            $orderStmt->execute([$orderId]);
            $orderData = $orderStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($orderData) {
                // Fetch order items
                $itemsStmt = $pdo->prepare("
                    SELECT product_id, product_name, product_sku, quantity, unit_price, total_price
                    FROM order_items
                    WHERE order_id = ?
                ");
                $itemsStmt->execute([$orderId]);
                $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Send emails
                $emailService = new EmailService();
                
                // Send to customer
                $emailService->sendOrderConfirmationEmail($orderData, $orderItems);
                
                // Send to admin and specified emails
                $adminEmails = ['info@nazmiboutique.com', 'contact.adnanks@gmail.com'];
                $emailService->sendOrderNotificationToAdmin($orderData, $orderItems, $adminEmails);
            }
        } catch (Exception $e) {
            error_log("Order email sending error: " . $e->getMessage());
            // Don't fail order creation if email fails
        }
        
        // Return success response
        sendResponse([
            'success' => true,
            'message' => 'Order placed successfully',
            'data' => [
                'order_number' => $orderNumber,
                'status' => 'confirmed',
                'totals' => [
                    'subtotal' => $subtotal,
                    'tax' => $taxAmount,
                    'shipping' => $shippingCost,
                    'discount' => $discountAmount,
                    'total' => $totalAmount
                ]
            ]
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
