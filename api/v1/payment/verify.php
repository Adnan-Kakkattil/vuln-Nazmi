<?php
/**
 * Razorpay Payment Verification API
 * Endpoint: /api/v1/payment/verify.php
 * Verifies Razorpay payment signature and updates order status
 *
 * Methods:
 * - POST: Verify payment and complete order
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
    
    $razorpayOrderId = $input['razorpay_order_id'] ?? '';
    $razorpayPaymentId = $input['razorpay_payment_id'] ?? '';
    $razorpaySignature = $input['razorpay_signature'] ?? '';
    $orderId = $input['order_id'] ?? '';
    
    if (empty($razorpayOrderId) || empty($razorpayPaymentId) || empty($razorpaySignature) || empty($orderId)) {
        sendResponse(['success' => false, 'message' => 'Missing payment verification data'], 400);
    }
    
    // Get Razorpay secret from system settings
    $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'razorpay_secret'");
    $stmt->execute();
    $razorpaySecret = $stmt->fetchColumn();
    
    if (empty($razorpaySecret)) {
        sendResponse(['success' => false, 'message' => 'Payment gateway not configured'], 500);
    }
    
    // Verify signature
    $generatedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, $razorpaySecret);
    
    if (!hash_equals($generatedSignature, $razorpaySignature)) {
        // Invalid signature - possible tampering
        error_log("Invalid Razorpay signature for order {$orderId}");
        
        // Update order with failed payment
        $updateStmt = $pdo->prepare("UPDATE orders SET payment_status = 'failed', status = 'cancelled', cancelled_date = NOW(), cancelled_reason = 'Payment verification failed' WHERE id = ?");
        $updateStmt->execute([$orderId]);
        
        sendResponse(['success' => false, 'message' => 'Payment verification failed. Invalid signature.'], 400);
    }
    
    // Signature is valid - update order
    $pdo->beginTransaction();
    
    // Get order details
    $orderStmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $orderStmt->execute([$orderId]);
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        $pdo->rollBack();
        sendResponse(['success' => false, 'message' => 'Order not found'], 404);
    }
    
    // Update order status
    $updateStmt = $pdo->prepare("
        UPDATE orders 
        SET payment_status = 'completed', 
            payment_transaction_id = ?,
            status = 'confirmed',
            confirmed_date = NOW(),
            payment_gateway_response = ?
        WHERE id = ?
    ");
    $updateStmt->execute([
        $razorpayPaymentId,
        json_encode([
            'razorpay_order_id' => $razorpayOrderId,
            'razorpay_payment_id' => $razorpayPaymentId,
            'verified_at' => date('Y-m-d H:i:s')
        ]),
        $orderId
    ]);
    
    // Update stock quantities
    $itemsStmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stockUpdateStmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ?, sold_count = sold_count + ? WHERE id = ?");
    foreach ($items as $item) {
        $stockUpdateStmt->execute([$item['quantity'], $item['quantity'], $item['product_id']]);
    }
    
    // Clear cart
    $userId = $_SESSION['user_id'] ?? null;
    $sessionId = session_id();
    
    if ($userId) {
        $clearCartStmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $clearCartStmt->execute([$userId]);
    } else {
        $clearCartStmt = $pdo->prepare("DELETE FROM cart_items WHERE session_id = ?");
        $clearCartStmt->execute([$sessionId]);
    }
    
    // Record payment transaction
    $transactionStmt = $pdo->prepare("
        INSERT INTO payment_transactions (order_id, transaction_id, payment_method, amount, currency, status, gateway, gateway_response)
        VALUES (?, ?, 'online', ?, 'INR', 'success', 'razorpay', ?)
    ");
    $transactionStmt->execute([
        $orderId,
        $razorpayPaymentId,
        $order['total_amount'],
        json_encode([
            'razorpay_order_id' => $razorpayOrderId,
            'razorpay_payment_id' => $razorpayPaymentId
        ])
    ]);
    
    // Add to order status history
    $historyStmt = $pdo->prepare("
        INSERT INTO order_status_history (order_id, status, notes)
        VALUES (?, 'confirmed', 'Payment received via Razorpay')
    ");
    $historyStmt->execute([$orderId]);
    
    $pdo->commit();
    
    // Auto-sync order to POS system immediately (non-blocking)
    try {
        require_once __DIR__ . '/../../../includes/api_integration_helpers.php';
        syncOrderToPOS($pdo, $orderId, true); // true = async/non-blocking
    } catch (Exception $e) {
        error_log("Auto-sync order failed: " . $e->getMessage());
        // Don't fail payment verification if sync fails
    }
    
    // Send order confirmation emails
    try {
        require_once __DIR__ . '/../../../includes/email/EmailService.php';
        
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
        // Don't fail payment verification if email fails
    }
    
    sendResponse([
        'success' => true,
        'message' => 'Payment verified successfully',
        'data' => [
            'order_id' => $orderId,
            'order_number' => $order['order_number'],
            'payment_id' => $razorpayPaymentId
        ]
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Payment Verify Error: ' . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'Payment verification error',
        'error' => $e->getMessage()
    ], 500);
}
