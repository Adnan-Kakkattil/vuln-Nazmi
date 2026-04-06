<?php
/**
 * Public Coupons API
 * Endpoint: /api/v1/coupons.php
 * Handles coupon validation and application for checkout
 * 
 * Methods:
 * - POST: Validate and apply coupon code
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
 * Calculate coupon status
 */
function calculateCouponStatus($coupon, $usedCount) {
    $today = date('Y-m-d');
    $validFrom = date('Y-m-d', strtotime($coupon['valid_from']));
    $validUntil = date('Y-m-d', strtotime($coupon['valid_until']));
    
    if (!$coupon['is_active']) {
        return 'inactive';
    }
    
    if ($validUntil < $today) {
        return 'expired';
    }
    
    if ($validFrom > $today) {
        return 'inactive';
    }
    
    if ($coupon['usage_limit'] && $usedCount >= $coupon['usage_limit']) {
        return 'expired';
    }
    
    return 'active';
}

/**
 * Calculate discount amount
 */
function calculateDiscount($coupon, $subtotal) {
    $discountType = $coupon['discount_type'];
    $discountValue = floatval($coupon['discount_value']);
    $minPurchase = floatval($coupon['minimum_purchase_amount'] ?? 0);
    $maxDiscount = $coupon['maximum_discount_amount'] ? floatval($coupon['maximum_discount_amount']) : null;
    
    // Check minimum purchase requirement
    if ($minPurchase > 0 && $subtotal < $minPurchase) {
        return [
            'valid' => false,
            'message' => "Minimum purchase of ₹" . number_format($minPurchase, 2) . " required"
        ];
    }
    
    // Calculate discount
    if ($discountType === 'percentage') {
        $discountAmount = ($subtotal * $discountValue) / 100;
        // Apply maximum discount limit if set
        if ($maxDiscount !== null && $discountAmount > $maxDiscount) {
            $discountAmount = $maxDiscount;
        }
    } else { // fixed_amount
        $discountAmount = $discountValue;
        // Don't allow discount to exceed subtotal
        if ($discountAmount > $subtotal) {
            $discountAmount = $subtotal;
        }
    }
    
    return [
        'valid' => true,
        'discount_amount' => round($discountAmount, 2),
        'discount_type' => $discountType,
        'discount_value' => $discountValue,
        'code' => $coupon['code']
    ];
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            // Validate coupon code
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['code'])) {
                sendResponse([
                    'success' => false,
                    'message' => 'Coupon code is required'
                ], 400);
            }
            
            $code = strtoupper(trim($input['code']));
            $subtotal = floatval($input['subtotal'] ?? 0);
            
            if ($subtotal <= 0) {
                sendResponse([
                    'success' => false,
                    'message' => 'Invalid subtotal amount'
                ], 400);
            }
            
            // Get coupon from database
            $stmt = $pdo->prepare("
                SELECT 
                    id,
                    code,
                    name,
                    description,
                    discount_type,
                    discount_value,
                    minimum_purchase_amount,
                    maximum_discount_amount,
                    usage_limit,
                    usage_limit_per_user,
                    valid_from,
                    valid_until,
                    is_active
                FROM coupons
                WHERE code = ?
            ");
            $stmt->execute([$code]);
            $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$coupon) {
                sendResponse([
                    'success' => false,
                    'message' => 'Invalid coupon code'
                ], 404);
            }
            
            // Get usage count
            $usageStmt = $pdo->prepare("SELECT COUNT(*) as count FROM coupon_usage WHERE coupon_id = ?");
            $usageStmt->execute([$coupon['id']]);
            $usedCount = intval($usageStmt->fetch(PDO::FETCH_ASSOC)['count']);
            
            // Check coupon status
            $status = calculateCouponStatus($coupon, $usedCount);
            
            if ($status !== 'active') {
                $messages = [
                    'inactive' => 'This coupon is not currently active',
                    'expired' => 'This coupon has expired'
                ];
                sendResponse([
                    'success' => false,
                    'message' => $messages[$status] ?? 'This coupon is not valid'
                ], 400);
            }
            
            // Calculate discount
            $discountResult = calculateDiscount($coupon, $subtotal);
            
            if (!$discountResult['valid']) {
                sendResponse([
                    'success' => false,
                    'message' => $discountResult['message']
                ], 400);
            }
            
            // Return success with discount details
            sendResponse([
                'success' => true,
                'message' => 'Coupon applied successfully',
                'coupon' => [
                    'id' => $coupon['id'],
                    'code' => $coupon['code'],
                    'name' => $coupon['name'],
                    'description' => $coupon['description']
                ],
                'discount' => [
                    'amount' => $discountResult['discount_amount'],
                    'type' => $discountResult['discount_type'],
                    'value' => $discountResult['discount_value']
                ]
            ]);
            break;
            
        default:
            sendResponse([
                'success' => false,
                'message' => 'Method not allowed'
            ], 405);
    }
} catch (Exception $e) {
    error_log('Coupon API Error: ' . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'An error occurred while validating the coupon'
    ], 500);
}
