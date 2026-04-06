<?php
/**
 * Razorpay Create Order API
 * Endpoint: /api/v1/payment/create-order.php
 * Creates a Razorpay order for online payment
 *
 * Methods:
 * - POST: Create a new Razorpay order
 */

header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../../../includes/config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = getDbConnection();

function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Only POST method allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['amount']) || !isset($input['orderData'])) {
        sendResponse(['success' => false, 'message' => 'Invalid request data'], 400);
    }
    
    $amount = (int) $input['amount']; // Amount in paise
    $currency = $input['currency'] ?? 'INR';
    $orderData = $input['orderData'];
    
    // Get Razorpay credentials from system settings
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('razorpay_key', 'razorpay_secret', 'payment_online_enabled')");
    $stmt->execute();
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Check if online payment is enabled
    if (empty($settings['payment_online_enabled']) || $settings['payment_online_enabled'] !== '1') {
        sendResponse(['success' => false, 'message' => 'Online payment is currently disabled'], 400);
    }
    
    $razorpayKey = $settings['razorpay_key'] ?? '';
    $razorpaySecret = $settings['razorpay_secret'] ?? '';
    
    if (empty($razorpayKey) || empty($razorpaySecret)) {
        sendResponse(['success' => false, 'message' => 'Payment gateway not configured'], 500);
    }
    
    // Create pending order in database first
    $userId = $_SESSION['user_id'] ?? null;
    $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
    
    // Get tax rate
    $taxStmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'tax_rate'");
    $taxStmt->execute();
    $taxRate = $taxStmt->fetchColumn() ?: 18;
    
    // Get shipping method ID
    $shippingMethodCode = $orderData['shipping']['method'] ?? 'standard';
    $shippingStmt = $pdo->prepare("SELECT id, cost FROM shipping_methods WHERE code = ?");
    $shippingStmt->execute([$shippingMethodCode]);
    $shippingMethod = $shippingStmt->fetch(PDO::FETCH_ASSOC);
    $shippingMethodId = $shippingMethod ? $shippingMethod['id'] : null;
    $shippingCost = $shippingMethod ? $shippingMethod['cost'] : 0;
    
    // Calculate totals
    $subtotal = $orderData['totals']['subtotal'] ?? 0;
    $taxAmount = $orderData['totals']['tax'] ?? ($subtotal * ($taxRate / 100));
    $discountAmount = $orderData['totals']['discount'] ?? 0;
    $totalAmount = $orderData['totals']['total'] ?? ($subtotal + $taxAmount + $shippingCost - $discountAmount);
    
    $pdo->beginTransaction();
    
    // Insert order with pending payment status
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            order_number, user_id, guest_email, guest_name,
            shipping_first_name, shipping_last_name, shipping_address_line1, shipping_address_line2,
            shipping_city, shipping_state, shipping_pincode, shipping_country, shipping_phone,
            shipping_method_id, shipping_cost,
            subtotal, discount_amount, tax_amount, tax_rate, total_amount,
            payment_method, payment_status, status, order_date
        ) VALUES (
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?,
            ?, ?, ?, ?, ?,
            'online', 'pending', 'pending', NOW()
        )
    ");
    
    $customerName = trim(($orderData['customer']['firstName'] ?? '') . ' ' . ($orderData['customer']['lastName'] ?? ''));
    
    $stmt->execute([
        $orderNumber,
        $userId,
        $orderData['customer']['email'],
        $customerName,
        $orderData['shipping']['firstName'] ?? $orderData['customer']['firstName'],
        $orderData['shipping']['lastName'] ?? $orderData['customer']['lastName'],
        $orderData['shipping']['addressLine1'],
        $orderData['shipping']['addressLine2'] ?? null,
        $orderData['shipping']['city'],
        $orderData['shipping']['state'],
        $orderData['shipping']['pincode'],
        $orderData['shipping']['country'] ?? 'India',
        $orderData['shipping']['phone'] ?? $orderData['customer']['phone'],
        $shippingMethodId,
        $shippingCost,
        $subtotal,
        $discountAmount,
        $taxAmount,
        $taxRate,
        $totalAmount,
    ]);
    
    $orderId = $pdo->lastInsertId();
    
    // Insert order items
    if (!empty($orderData['cart'])) {
        $itemStmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, product_name, product_sku, quantity, unit_price, total_price)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($orderData['cart'] as $item) {
            $itemStmt->execute([
                $orderId,
                $item['id'],
                $item['name'],
                $item['sku'] ?? null,
                $item['quantity'],
                $item['price'],
                $item['price'] * $item['quantity']
            ]);
        }
    }
    
    // Create Razorpay order
    $razorpayOrderData = [
        'amount' => $amount,
        'currency' => $currency,
        'receipt' => $orderNumber,
        'notes' => [
            'order_id' => $orderId,
            'order_number' => $orderNumber
        ]
    ];
    
    $ch = curl_init('https://api.razorpay.com/v1/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($razorpayOrderData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_USERPWD, $razorpayKey . ':' . $razorpaySecret);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $razorpayResponse = json_decode($response, true);
    
    if ($httpCode !== 200 || !isset($razorpayResponse['id'])) {
        $pdo->rollBack();
        error_log('Razorpay Error: ' . $response);
        sendResponse([
            'success' => false, 
            'message' => 'Failed to create payment order',
            'error' => $razorpayResponse['error']['description'] ?? 'Unknown error'
        ], 500);
    }
    
    // Update order with Razorpay order ID
    $updateStmt = $pdo->prepare("UPDATE orders SET payment_transaction_id = ? WHERE id = ?");
    $updateStmt->execute([$razorpayResponse['id'], $orderId]);
    
    $pdo->commit();
    
    sendResponse([
        'success' => true,
        'data' => [
            'razorpay_order_id' => $razorpayResponse['id'],
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'amount' => $amount,
            'currency' => $currency
        ]
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Payment Create Order Error: ' . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'Failed to process payment request',
        'error' => $e->getMessage()
    ], 500);
}
