<?php
/**
 * Admin Coupons API
 * Endpoint: /api/v1/admin/coupons.php
 * Handles CRUD operations for coupons
 * 
 * Methods:
 * - GET: List coupons with filters
 * - POST: Create new coupon
 * - PUT: Update coupon
 * - DELETE: Delete coupon
 */

header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../../../includes/config.php';

// Database connection
$pdo = getDbConnection();

// Try to get admin (optional for now - you can enable auth later)
$admin = requireAdminAuth();
if ($admin === false) {
    // Create a default admin for testing/development
    // In production, you should uncomment the requireAdminAuthOrDie() below
    $admin = [
        'id' => 1,
        'email' => 'admin@test.com',
        'role_id' => 1,
        'role_name' => 'Super Admin'
    ];
}

// Uncomment below to enforce authentication and permissions:
// $admin = requireAdminAuthOrDie();
// requirePermission('manage_coupons');

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

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // List coupons with filters
            $search = $_GET['search'] ?? null;
            $statusFilter = $_GET['status'] ?? 'all';
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 100);
            $offset = ($page - 1) * $limit;
            
            // Build query
            $where = [];
            $params = [];
            
            if ($search) {
                $where[] = "(code LIKE ? OR name LIKE ? OR description LIKE ?)";
                $searchParam = "%{$search}%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Get total count
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM coupons {$whereClause}");
            if (!empty($params)) {
                $countStmt->execute($params);
            } else {
                $countStmt->execute();
            }
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get coupons
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
                    applicable_to,
                    applicable_category_id,
                    applicable_product_ids,
                    is_active,
                    created_by,
                    created_at,
                    updated_at
                FROM coupons
                {$whereClause}
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?
            ");
            $params[] = $limit;
            $params[] = $offset;
            $stmt->execute($params);
            $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get usage count for each coupon
            foreach ($coupons as &$coupon) {
                $usageStmt = $pdo->prepare("SELECT COUNT(*) as count FROM coupon_usage WHERE coupon_id = ?");
                $usageStmt->execute([$coupon['id']]);
                $usedCount = $usageStmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Calculate status before transformation (uses DB field names)
                $status = calculateCouponStatus($coupon, intval($usedCount));
                
                // Transform to frontend format
                $coupon['type'] = $coupon['discount_type'] === 'fixed_amount' ? 'fixed' : 'percentage';
                $coupon['value'] = floatval($coupon['discount_value']);
                $coupon['minPurchase'] = floatval($coupon['minimum_purchase_amount']);
                $coupon['usageLimit'] = $coupon['usage_limit'] ? intval($coupon['usage_limit']) : 0;
                $coupon['usedCount'] = intval($usedCount);
                $coupon['startDate'] = date('Y-m-d', strtotime($coupon['valid_from']));
                $coupon['expiryDate'] = date('Y-m-d', strtotime($coupon['valid_until']));
                $coupon['description'] = $coupon['description'];
                $coupon['active'] = (bool)$coupon['is_active'];
                $coupon['status'] = $status;
                
                // Remove DB-specific fields
                unset($coupon['discount_type'], $coupon['discount_value'], $coupon['minimum_purchase_amount'],
                      $coupon['usage_limit'], $coupon['valid_from'], $coupon['valid_until'], 
                      $coupon['is_active'], $coupon['maximum_discount_amount'],
                      $coupon['usage_limit_per_user'], $coupon['applicable_to'], 
                      $coupon['applicable_category_id'], $coupon['applicable_product_ids']);
            }
            
            // Apply status filter if not 'all'
            if ($statusFilter !== 'all') {
                $coupons = array_filter($coupons, function($coupon) use ($statusFilter) {
                    return $coupon['status'] === $statusFilter;
                });
                $coupons = array_values($coupons); // Re-index array
            }
            
            sendResponse([
                'success' => true,
                'data' => $coupons,
                'pagination' => [
                    'total' => intval($total),
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => ceil($total / $limit)
                ]
            ]);
            break;
            
        case 'POST':
            // Create new coupon
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (empty($data['code']) || empty($data['type']) || !isset($data['value']) || empty($data['expiryDate'])) {
                sendResponse([
                    'success' => false,
                    'message' => 'Missing required fields: code, type, value, expiryDate'
                ], 400);
            }
            
            // Check if coupon code already exists
            $checkStmt = $pdo->prepare("SELECT id FROM coupons WHERE code = ?");
            $checkStmt->execute([strtoupper($data['code'])]);
            if ($checkStmt->fetch()) {
                sendResponse([
                    'success' => false,
                    'message' => 'Coupon code already exists'
                ], 400);
            }
            
            // Validate discount value
            if ($data['type'] === 'percentage' && $data['value'] > 100) {
                sendResponse([
                    'success' => false,
                    'message' => 'Percentage discount cannot exceed 100%'
                ], 400);
            }
            
            // Map frontend type to database type
            $discountType = $data['type'] === 'fixed' ? 'fixed_amount' : 'percentage';
            
            // Prepare data
            $code = strtoupper(trim($data['code']));
            $name = $data['name'] ?? $code;
            $description = $data['description'] ?? null;
            $discountValue = floatval($data['value']);
            $minPurchase = floatval($data['minPurchase'] ?? 0);
            $usageLimit = !empty($data['usageLimit']) ? intval($data['usageLimit']) : null;
            $startDate = !empty($data['startDate']) ? date('Y-m-d H:i:s', strtotime($data['startDate'])) : date('Y-m-d H:i:s');
            $expiryDate = date('Y-m-d 23:59:59', strtotime($data['expiryDate']));
            $isActive = isset($data['active']) ? (int)$data['active'] : 1;
            
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                // Insert coupon
                $stmt = $pdo->prepare("
                    INSERT INTO coupons (
                        code,
                        name,
                        description,
                        discount_type,
                        discount_value,
                        minimum_purchase_amount,
                        usage_limit,
                        valid_from,
                        valid_until,
                        is_active,
                        created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $code,
                    $name,
                    $description,
                    $discountType,
                    $discountValue,
                    $minPurchase,
                    $usageLimit,
                    $startDate,
                    $expiryDate,
                    $isActive,
                    $admin['id']
                ]);
                
                $couponId = $pdo->lastInsertId();
                
                // Log activity
                $logStmt = $pdo->prepare("
                    INSERT INTO admin_activity_log (user_id, action, module, entity_id, entity_type)
                    VALUES (?, 'create', 'coupons', ?, 'coupon')
                ");
                $logStmt->execute([$admin['id'], $couponId]);
                
                $pdo->commit();
                
                // Fetch created coupon
                $fetchStmt = $pdo->prepare("
                    SELECT 
                        id,
                        code,
                        name,
                        description,
                        discount_type,
                        discount_value,
                        minimum_purchase_amount,
                        usage_limit,
                        valid_from,
                        valid_until,
                        is_active
                    FROM coupons
                    WHERE id = ?
                ");
                $fetchStmt->execute([$couponId]);
                $coupon = $fetchStmt->fetch(PDO::FETCH_ASSOC);
                
                // Transform to frontend format
                $coupon['type'] = $coupon['discount_type'] === 'fixed_amount' ? 'fixed' : 'percentage';
                $coupon['value'] = floatval($coupon['discount_value']);
                $coupon['minPurchase'] = floatval($coupon['minimum_purchase_amount']);
                $coupon['usageLimit'] = $coupon['usage_limit'] ? intval($coupon['usage_limit']) : 0;
                $coupon['usedCount'] = 0;
                $coupon['startDate'] = date('Y-m-d', strtotime($coupon['valid_from']));
                $coupon['expiryDate'] = date('Y-m-d', strtotime($coupon['valid_until']));
                $coupon['description'] = $coupon['description'];
                $coupon['active'] = (bool)$coupon['is_active'];
                $coupon['status'] = calculateCouponStatus($coupon, 0);
                
                // Remove DB-specific fields
                unset($coupon['discount_type'], $coupon['discount_value'], $coupon['minimum_purchase_amount'],
                      $coupon['usage_limit'], $coupon['valid_from'], $coupon['valid_until'], $coupon['is_active']);
                
                sendResponse([
                    'success' => true,
                    'message' => 'Coupon created successfully',
                    'data' => $coupon
                ], 201);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case 'PUT':
            // Update coupon
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                sendResponse([
                    'success' => false,
                    'message' => 'Coupon ID is required'
                ], 400);
            }
            
            $couponId = $data['id'];
            
            // Check if coupon exists
            $checkStmt = $pdo->prepare("SELECT id FROM coupons WHERE id = ?");
            $checkStmt->execute([$couponId]);
            if (!$checkStmt->fetch()) {
                sendResponse([
                    'success' => false,
                    'message' => 'Coupon not found'
                ], 404);
            }
            
            // Check if code already exists (excluding current coupon)
            if (!empty($data['code'])) {
                $codeCheckStmt = $pdo->prepare("SELECT id FROM coupons WHERE code = ? AND id != ?");
                $codeCheckStmt->execute([strtoupper($data['code']), $couponId]);
                if ($codeCheckStmt->fetch()) {
                    sendResponse([
                        'success' => false,
                        'message' => 'Coupon code already exists'
                    ], 400);
                }
            }
            
            // Validate discount value
            if (isset($data['type']) && $data['type'] === 'percentage' && isset($data['value']) && $data['value'] > 100) {
                sendResponse([
                    'success' => false,
                    'message' => 'Percentage discount cannot exceed 100%'
                ], 400);
            }
            
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                // Map frontend type to database type
                $discountType = null;
                if (isset($data['type'])) {
                    $discountType = $data['type'] === 'fixed' ? 'fixed_amount' : 'percentage';
                }
                
                // Build update query
                $updateFields = [];
                $updateParams = [];
                
                if (isset($data['code'])) {
                    $updateFields[] = "code = ?";
                    $updateParams[] = strtoupper(trim($data['code']));
                }
                
                if (isset($data['name'])) {
                    $updateFields[] = "name = ?";
                    $updateParams[] = $data['name'];
                }
                
                if (isset($data['description'])) {
                    $updateFields[] = "description = ?";
                    $updateParams[] = $data['description'];
                }
                
                if ($discountType) {
                    $updateFields[] = "discount_type = ?";
                    $updateParams[] = $discountType;
                }
                
                if (isset($data['value'])) {
                    $updateFields[] = "discount_value = ?";
                    $updateParams[] = floatval($data['value']);
                }
                
                if (isset($data['minPurchase'])) {
                    $updateFields[] = "minimum_purchase_amount = ?";
                    $updateParams[] = floatval($data['minPurchase']);
                }
                
                if (isset($data['usageLimit'])) {
                    $updateFields[] = "usage_limit = ?";
                    $updateParams[] = !empty($data['usageLimit']) ? intval($data['usageLimit']) : null;
                }
                
                if (isset($data['startDate'])) {
                    $updateFields[] = "valid_from = ?";
                    $updateParams[] = date('Y-m-d H:i:s', strtotime($data['startDate']));
                }
                
                if (isset($data['expiryDate'])) {
                    $updateFields[] = "valid_until = ?";
                    $updateParams[] = date('Y-m-d 23:59:59', strtotime($data['expiryDate']));
                }
                
                if (isset($data['active'])) {
                    $updateFields[] = "is_active = ?";
                    $updateParams[] = (int)$data['active'];
                }
                
                if (!empty($updateFields)) {
                    $updateParams[] = $couponId;
                    $updateSql = "UPDATE coupons SET " . implode(', ', $updateFields) . " WHERE id = ?";
                    $updateStmt = $pdo->prepare($updateSql);
                    $updateStmt->execute($updateParams);
                }
                
                // Log activity
                $logStmt = $pdo->prepare("
                    INSERT INTO admin_activity_log (user_id, action, module, entity_id, entity_type)
                    VALUES (?, 'update', 'coupons', ?, 'coupon')
                ");
                $logStmt->execute([$admin['id'], $couponId]);
                
                $pdo->commit();
                
                // Fetch updated coupon
                $fetchStmt = $pdo->prepare("
                    SELECT 
                        id,
                        code,
                        name,
                        description,
                        discount_type,
                        discount_value,
                        minimum_purchase_amount,
                        usage_limit,
                        valid_from,
                        valid_until,
                        is_active
                    FROM coupons
                    WHERE id = ?
                ");
                $fetchStmt->execute([$couponId]);
                $coupon = $fetchStmt->fetch(PDO::FETCH_ASSOC);
                
                // Get usage count
                $usageStmt = $pdo->prepare("SELECT COUNT(*) as count FROM coupon_usage WHERE coupon_id = ?");
                $usageStmt->execute([$couponId]);
                $usedCount = intval($usageStmt->fetch(PDO::FETCH_ASSOC)['count']);
                
                // Calculate status before transformation (uses DB field names)
                $status = calculateCouponStatus($coupon, $usedCount);
                
                // Transform to frontend format
                $coupon['type'] = $coupon['discount_type'] === 'fixed_amount' ? 'fixed' : 'percentage';
                $coupon['value'] = floatval($coupon['discount_value']);
                $coupon['minPurchase'] = floatval($coupon['minimum_purchase_amount']);
                $coupon['usageLimit'] = $coupon['usage_limit'] ? intval($coupon['usage_limit']) : 0;
                $coupon['usedCount'] = $usedCount;
                $coupon['startDate'] = date('Y-m-d', strtotime($coupon['valid_from']));
                $coupon['expiryDate'] = date('Y-m-d', strtotime($coupon['valid_until']));
                $coupon['description'] = $coupon['description'];
                $coupon['active'] = (bool)$coupon['is_active'];
                $coupon['status'] = $status;
                
                // Remove DB-specific fields
                unset($coupon['discount_type'], $coupon['discount_value'], $coupon['minimum_purchase_amount'],
                      $coupon['usage_limit'], $coupon['valid_from'], $coupon['valid_until'], $coupon['is_active']);
                
                sendResponse([
                    'success' => true,
                    'message' => 'Coupon updated successfully',
                    'data' => $coupon
                ]);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case 'DELETE':
            // Delete coupon
            $couponId = intval($_GET['id'] ?? 0);
            
            if (!$couponId) {
                sendResponse([
                    'success' => false,
                    'message' => 'Coupon ID is required'
                ], 400);
            }
            
            // Check if coupon exists
            $checkStmt = $pdo->prepare("SELECT id FROM coupons WHERE id = ?");
            $checkStmt->execute([$couponId]);
            if (!$checkStmt->fetch()) {
                sendResponse([
                    'success' => false,
                    'message' => 'Coupon not found'
                ], 404);
            }
            
            // Check if coupon has usage records
            $usageStmt = $pdo->prepare("SELECT COUNT(*) as count FROM coupon_usage WHERE coupon_id = ?");
            $usageStmt->execute([$couponId]);
            $usageCount = $usageStmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($usageCount > 0) {
                // Soft delete - set is_active to 0 instead of hard delete
                $pdo->beginTransaction();
                try {
                    $updateStmt = $pdo->prepare("UPDATE coupons SET is_active = 0 WHERE id = ?");
                    $updateStmt->execute([$couponId]);
                    
                    // Log activity
                    $logStmt = $pdo->prepare("
                        INSERT INTO admin_activity_log (user_id, action, module, entity_id, entity_type)
                        VALUES (?, 'delete', 'coupons', ?, 'coupon')
                    ");
                    $logStmt->execute([$admin['id'], $couponId]);
                    
                    $pdo->commit();
                    
                    sendResponse([
                        'success' => true,
                        'message' => 'Coupon deactivated successfully (has usage history)'
                    ]);
                } catch (Exception $e) {
                    $pdo->rollBack();
                    throw $e;
                }
            } else {
                // Hard delete - no usage history
                $pdo->beginTransaction();
                try {
                    $deleteStmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
                    $deleteStmt->execute([$couponId]);
                    
                    // Log activity
                    $logStmt = $pdo->prepare("
                        INSERT INTO admin_activity_log (user_id, action, module, entity_id, entity_type)
                        VALUES (?, 'delete', 'coupons', ?, 'coupon')
                    ");
                    $logStmt->execute([$admin['id'], $couponId]);
                    
                    $pdo->commit();
                    
                    sendResponse([
                        'success' => true,
                        'message' => 'Coupon deleted successfully'
                    ]);
                } catch (Exception $e) {
                    $pdo->rollBack();
                    throw $e;
                }
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
