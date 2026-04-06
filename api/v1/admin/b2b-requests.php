<?php
/**
 * Admin B2B Requests API
 * Endpoint: /api/v1/admin/b2b-requests.php
 * Handles B2B request management for admin panel
 * 
 * Methods:
 * - GET: List B2B requests with filters
 * - PUT: Update B2B request (status, notes)
 * - DELETE: Delete B2B request
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
// requirePermission('view_b2b');

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
            // List B2B requests with filters
            $search = $_GET['search'] ?? null;
            $statusFilter = $_GET['status'] ?? null;
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 100);
            $offset = ($page - 1) * $limit;
            
            // Build query
            $where = [];
            $params = [];
            
            if ($search) {
                $where[] = "(company_name LIKE ? OR contact_person LIKE ? OR email LIKE ? OR phone LIKE ?)";
                $searchParam = "%{$search}%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            if ($statusFilter) {
                $where[] = "status = ?";
                $params[] = $statusFilter;
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Get total count
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM b2b_requests {$whereClause}");
            if (!empty($where)) {
                $countStmt->execute($params);
            } else {
                $countStmt->execute();
            }
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get B2B requests
            $stmt = $pdo->prepare("
                SELECT 
                    id,
                    company_name,
                    contact_person,
                    email,
                    phone,
                    business_type,
                    gst_number,
                    address_line1,
                    address_line2,
                    city,
                    state,
                    pincode,
                    country,
                    monthly_volume_estimate,
                    product_categories,
                    special_requirements,
                    status,
                    notes,
                    reviewed_by,
                    reviewed_at,
                    created_at,
                    updated_at
                FROM b2b_requests
                {$whereClause}
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            // Add limit and offset to params
            $queryParams = $params;
            $queryParams[] = $limit;
            $queryParams[] = $offset;
            $stmt->execute($queryParams);
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Transform to frontend format
            foreach ($requests as &$request) {
                $request['name'] = $request['contact_person'];
                $request['company'] = $request['company_name'];
                $request['date'] = $request['created_at'];
                $request['createdAt'] = $request['created_at'];
                
                // Parse product_categories if it's JSON
                if ($request['product_categories']) {
                    $decoded = json_decode($request['product_categories'], true);
                    $request['product_categories'] = $decoded ? $decoded : $request['product_categories'];
                }
                
                // Remove DB-specific fields or keep them if needed
                // Keep all fields for now as admin may need full details
            }
            
            sendResponse([
                'success' => true,
                'data' => $requests,
                'pagination' => [
                    'total' => intval($total),
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => ceil($total / $limit)
                ]
            ]);
            break;
            
        case 'PUT':
            // Update B2B request (status, notes)
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                sendResponse([
                    'success' => false,
                    'message' => 'Request ID is required'
                ], 400);
            }
            
            $requestId = intval($data['id']);
            
            // Check if request exists
            $checkStmt = $pdo->prepare("SELECT id FROM b2b_requests WHERE id = ?");
            $checkStmt->execute([$requestId]);
            if (!$checkStmt->fetch()) {
                sendResponse([
                    'success' => false,
                    'message' => 'B2B request not found'
                ], 404);
            }
            
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                // Build update query
                $updateFields = [];
                $updateParams = [];
                
                if (isset($data['status'])) {
                    $updateFields[] = "status = ?";
                    $updateParams[] = $data['status'];
                }
                
                if (isset($data['notes'])) {
                    $updateFields[] = "notes = ?";
                    $updateParams[] = $data['notes'];
                }
                
                if (isset($data['status']) && in_array($data['status'], ['approved', 'rejected'])) {
                    // Set reviewed_by and reviewed_at
                    $updateFields[] = "reviewed_by = ?";
                    $updateParams[] = $admin['id'];
                    $updateFields[] = "reviewed_at = NOW()";
                }
                
                if (!empty($updateFields)) {
                    $updateParams[] = $requestId;
                    $updateSql = "UPDATE b2b_requests SET " . implode(', ', $updateFields) . " WHERE id = ?";
                    $updateStmt = $pdo->prepare($updateSql);
                    $updateStmt->execute($updateParams);
                }
                
                // Log activity
                $logStmt = $pdo->prepare("
                    INSERT INTO admin_activity_log (user_id, action, module, entity_id, entity_type)
                    VALUES (?, 'update', 'b2b', ?, 'b2b_request')
                ");
                $logStmt->execute([$admin['id'], $requestId]);
                
                $pdo->commit();
                
                // Fetch updated request
                $fetchStmt = $pdo->prepare("
                    SELECT 
                        id,
                        company_name,
                        contact_person,
                        email,
                        phone,
                        business_type,
                        gst_number,
                        address_line1,
                        address_line2,
                        city,
                        state,
                        pincode,
                        country,
                        monthly_volume_estimate,
                        product_categories,
                        special_requirements,
                        status,
                        notes,
                        reviewed_by,
                        reviewed_at,
                        created_at,
                        updated_at
                    FROM b2b_requests
                    WHERE id = ?
                ");
                $fetchStmt->execute([$requestId]);
                $request = $fetchStmt->fetch(PDO::FETCH_ASSOC);
                
                // Transform to frontend format
                $request['name'] = $request['contact_person'];
                $request['company'] = $request['company_name'];
                $request['date'] = $request['created_at'];
                $request['createdAt'] = $request['created_at'];
                
                sendResponse([
                    'success' => true,
                    'message' => 'B2B request updated successfully',
                    'data' => $request
                ]);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case 'DELETE':
            // Delete B2B request
            $requestId = intval($_GET['id'] ?? 0);
            
            if (!$requestId) {
                sendResponse([
                    'success' => false,
                    'message' => 'Request ID is required'
                ], 400);
            }
            
            // Check if request exists
            $checkStmt = $pdo->prepare("SELECT id FROM b2b_requests WHERE id = ?");
            $checkStmt->execute([$requestId]);
            if (!$checkStmt->fetch()) {
                sendResponse([
                    'success' => false,
                    'message' => 'B2B request not found'
                ], 404);
            }
            
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                $deleteStmt = $pdo->prepare("DELETE FROM b2b_requests WHERE id = ?");
                $deleteStmt->execute([$requestId]);
                
                // Log activity
                $logStmt = $pdo->prepare("
                    INSERT INTO admin_activity_log (user_id, action, module, entity_id, entity_type)
                    VALUES (?, 'delete', 'b2b', ?, 'b2b_request')
                ");
                $logStmt->execute([$admin['id'], $requestId]);
                
                $pdo->commit();
                
                sendResponse([
                    'success' => true,
                    'message' => 'B2B request deleted successfully'
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
