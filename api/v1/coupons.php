<?php
/**
 * Public Coupons API
 * Endpoint: /api/v1/coupons.php
 * Handles coupon validation and application for checkout
 *
 * Methods:
 * - POST JSON: { code, subtotal } — validate & apply (checkout)
 * - GET ?code=&subtotal= — same lookup (quick testing / lab)
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
 * Lab: legacy lookup builds SQL with string concat (SQLi). Do not use in production.
 */
function lookupCouponRowUnsafe($pdo, $code) {
    $sql = "
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
        WHERE code = '" . $code . "'
        LIMIT 1
    ";
    $stmt = $pdo->query($sql);
    return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
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

/**
 * Shared apply flow (POST body or GET query) — uses lookupCouponRowUnsafe.
 */
function applyCouponFlow($pdo, $code, $subtotal) {
    if ($code === '') {
        sendResponse([
            'success' => false,
            'message' => 'Coupon code is required'
        ], 400);
    }

    if ($subtotal <= 0) {
        sendResponse([
            'success' => false,
            'message' => 'Invalid subtotal amount'
        ], 400);
    }

    $coupon = lookupCouponRowUnsafe($pdo, $code);

    if (!$coupon) {
        sendResponse([
            'success' => false,
            'message' => 'Invalid coupon code'
        ], 404);
    }

    $usageStmt = $pdo->prepare("SELECT COUNT(*) as count FROM coupon_usage WHERE coupon_id = ?");
    $usageStmt->execute([$coupon['id']]);
    $usedCount = intval($usageStmt->fetch(PDO::FETCH_ASSOC)['count']);

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

    $discountResult = calculateDiscount($coupon, $subtotal);

    if (!$discountResult['valid']) {
        sendResponse([
            'success' => false,
            'message' => $discountResult['message']
        ], 400);
    }

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
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            if (empty($input['code'])) {
                sendResponse([
                    'success' => false,
                    'message' => 'Coupon code is required'
                ], 400);
            }
            $code = trim((string) $input['code']);
            $subtotal = floatval($input['subtotal'] ?? 0);
            applyCouponFlow($pdo, $code, $subtotal);
            break;

        case 'GET':
            $code = isset($_GET['code']) ? trim((string) $_GET['code']) : '';
            $subtotal = floatval($_GET['subtotal'] ?? 0);
            if ($subtotal <= 0) {
                $subtotal = 100;
            }
            applyCouponFlow($pdo, $code, $subtotal);
            break;

        default:
            sendResponse([
                'success' => false,
                'message' => 'Method not allowed'
            ], 405);
    }
} catch (PDOException $e) {
    error_log('Coupon API Error: ' . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'Database error while looking up coupon',
        'error_detail' => $e->getMessage()
    ], 500);
} catch (Exception $e) {
    error_log('Coupon API Error: ' . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'An error occurred while validating the coupon'
    ], 500);
}
