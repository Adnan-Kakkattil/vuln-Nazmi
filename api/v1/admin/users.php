<?php
/**
 * Admin Users Management API
 * Endpoint: /api/v1/admin/users.php
 * Methods: GET (list/single), POST (create), PUT (update), DELETE (deactivate)
 */

header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../../../includes/config.php';

// Require admin authentication
$currentAdmin = requireAdminAuthOrDie();

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
 * Log admin activity
 */
function logActivity($pdo, $adminId, $action, $module, $entityId = null, $changes = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO admin_activity_log (user_id, action, module, entity_id, entity_type, changes, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $adminId,
            $action,
            $module,
            $entityId,
            'admin_user',
            $changes ? json_encode($changes) : null,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (PDOException $e) {
        error_log("Activity Log Error: " . $e->getMessage());
    }
}

// Get request method and ID
$method = $_SERVER['REQUEST_METHOD'];
$userId = $_GET['id'] ?? null;

// Route requests
switch ($method) {
    case 'GET':
        if ($userId) {
            handleGetUser($pdo, $userId);
        } else {
            handleListUsers($pdo);
        }
        break;
        
    case 'POST':
        handleCreateUser($pdo, $currentAdmin);
        break;
        
    case 'PUT':
        if (!$userId) {
            sendResponse(['success' => false, 'message' => 'User ID is required'], 400);
        }
        handleUpdateUser($pdo, $userId, $currentAdmin);
        break;
        
    case 'DELETE':
        if (!$userId) {
            sendResponse(['success' => false, 'message' => 'User ID is required'], 400);
        }
        handleDeleteUser($pdo, $userId, $currentAdmin);
        break;
        
    default:
        sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

/**
 * List all admin users
 */
function handleListUsers($pdo) {
    try {
        $page = intval($_GET['page'] ?? 1);
        $limit = intval($_GET['limit'] ?? 20);
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $roleId = $_GET['role_id'] ?? '';
        
        $offset = ($page - 1) * $limit;
        
        // Build query
        $where = ['1=1'];
        $params = [];
        
        if ($search) {
            $where[] = "(email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($status !== '') {
            $where[] = "is_active = ?";
            $params[] = $status;
        }
        
        if ($roleId) {
            $where[] = "role_id = ?";
            $params[] = $roleId;
        }
        
        // Filter for admin users only (role_id != 2, where 2 is Customer)
        // This allows listing all admin users regardless of their specific admin role
        $where[] = "role_id != 2";
        $whereClause = implode(' AND ', $where);
        
        // Get total count
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM users
            WHERE {$whereClause}
        ");
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get users
        $stmt = $pdo->prepare("
            SELECT 
                u.*,
                r.name as role_name,
                r.description as role_description
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE {$whereClause}
            ORDER BY u.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Remove sensitive data
        foreach ($users as &$user) {
            unset($user['password_hash']);
        }
        
        sendResponse([
            'success' => true,
            'data' => $users,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => intval($total),
                'pages' => ceil($total / $limit)
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("List Users Error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Failed to fetch users'
        ], 500);
    }
}

/**
 * Get single admin user
 */
function handleGetUser($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                u.*,
                r.name as role_name,
                r.description as role_description
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = ? AND u.role_id != 2
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            sendResponse([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        
        // Remove sensitive data
        unset($user['password_hash']);
        
        sendResponse([
            'success' => true,
            'data' => $user
        ]);
        
    } catch (PDOException $e) {
        error_log("Get User Error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Failed to fetch user'
        ], 500);
    }
}

/**
 * Create new admin user
 */
function handleCreateUser($pdo, $currentAdmin) {
    // Check permission - allow admin role (id=1) or users with manage_users permission
    if ($currentAdmin['role_id'] != 1 && !hasPermission('manage_users')) {
        sendResponse([
            'success' => false,
            'message' => 'Insufficient permissions'
        ], 403);
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $firstName = trim($input['first_name'] ?? '');
    $lastName = trim($input['last_name'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $roleId = intval($input['role_id'] ?? 0);
    $isActive = isset($input['is_active']) ? intval($input['is_active']) : 1;
    
    // Validation
    if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
        sendResponse([
            'success' => false,
            'message' => 'Email, password, first name, and last name are required'
        ], 400);
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse([
            'success' => false,
            'message' => 'Invalid email format'
        ], 400);
    }
    
    if (strlen($password) < 8) {
        sendResponse([
            'success' => false,
            'message' => 'Password must be at least 8 characters long'
        ], 400);
    }
    
    if (!$roleId) {
        sendResponse([
            'success' => false,
            'message' => 'Role is required'
        ], 400);
    }
    
    try {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            sendResponse([
                'success' => false,
                'message' => 'Email already exists'
            ], 409);
        }
        
        // Verify role exists and is not Customer role (role_id != 2)
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE id = ? AND id != 2");
        $stmt->execute([$roleId]);
        if (!$stmt->fetch()) {
            sendResponse([
                'success' => false,
                'message' => 'Invalid role. Only admin roles are allowed.'
            ], 400);
        }
        
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        // Create admin user in unified users table with selected role_id
        $stmt = $pdo->prepare("
            INSERT INTO users (email, password_hash, first_name, last_name, phone, role_id, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$email, $passwordHash, $firstName, $lastName, $phone, $roleId, $isActive]);
        $newUserId = $pdo->lastInsertId();
        
        // Get created user with role
        $stmt = $pdo->prepare("
            SELECT 
                u.*,
                r.name as role_name,
                r.description as role_description
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = ?
        ");
        $stmt->execute([$newUserId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        unset($user['password_hash']);
        
        // Log activity
        logActivity($pdo, $currentAdmin['id'], 'create', 'admin_users', $newUserId, [
            'email' => $email,
            'role_id' => $roleId
        ]);
        
        sendResponse([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
        
    } catch (PDOException $e) {
        error_log("Create User Error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Failed to create user'
        ], 500);
    }
}

/**
 * Update admin user
 */
function handleUpdateUser($pdo, $userId, $currentAdmin) {
    // Check permission - allow admin role (id=1) or users with manage_users permission
    if ($currentAdmin['role_id'] != 1 && !hasPermission('manage_users')) {
        sendResponse([
            'success' => false,
            'message' => 'Insufficient permissions'
        ], 403);
    }
    
    // Prevent self-deactivation
    if ($userId == $currentAdmin['id']) {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }
        if (isset($input['is_active']) && $input['is_active'] == 0) {
            sendResponse([
                'success' => false,
                'message' => 'You cannot deactivate your own account'
            ], 400);
        }
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    // Get current user data (admin users only, role_id != 2)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role_id != 2");
    $stmt->execute([$userId]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$currentUser) {
        sendResponse([
            'success' => false,
            'message' => 'User not found'
        ], 404);
    }
    
    // Update fields
    $firstName = isset($input['first_name']) ? trim($input['first_name']) : $currentUser['first_name'];
    $lastName = isset($input['last_name']) ? trim($input['last_name']) : $currentUser['last_name'];
    $phone = isset($input['phone']) ? trim($input['phone']) : $currentUser['phone'];
    $roleId = isset($input['role_id']) ? intval($input['role_id']) : $currentUser['role_id'];
    $isActive = isset($input['is_active']) ? intval($input['is_active']) : $currentUser['is_active'];
    $password = $input['password'] ?? null;
    
    // Validation
    if (empty($firstName) || empty($lastName)) {
        sendResponse([
            'success' => false,
            'message' => 'First name and last name are required'
        ], 400);
    }
    
    if ($password && strlen($password) < 8) {
        sendResponse([
            'success' => false,
            'message' => 'Password must be at least 8 characters long'
        ], 400);
    }
    
    // Admin users must have admin role (role_id != 2)
    if ($roleId && $roleId == 2) {
        sendResponse([
            'success' => false,
            'message' => 'Admin users cannot have Customer role'
        ], 400);
    }
    
    try {
        $changes = [];
        
        // Prepare update query (role_id always stays 1 for admin users)
        $updateFields = ['first_name = ?', 'last_name = ?', 'phone = ?', 'is_active = ?'];
        $params = [$firstName, $lastName, $phone, $isActive];
        
        // Track changes
        if ($firstName != $currentUser['first_name']) $changes['first_name'] = [$currentUser['first_name'], $firstName];
        if ($lastName != $currentUser['last_name']) $changes['last_name'] = [$currentUser['last_name'], $lastName];
        if ($phone != $currentUser['phone']) $changes['phone'] = [$currentUser['phone'], $phone];
        if ($roleId != $currentUser['role_id']) $changes['role_id'] = [$currentUser['role_id'], $roleId];
        if ($isActive != $currentUser['is_active']) $changes['is_active'] = [$currentUser['is_active'], $isActive];
        
        // Update password if provided
        if ($password) {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $updateFields[] = 'password_hash = ?';
            $params[] = $passwordHash;
            $changes['password'] = ['***', '***']; // Don't log actual password
        }
        
        $params[] = $userId;
        
        // Update role_id if provided (but not to Customer role)
        if ($roleId && $roleId != $currentUser['role_id']) {
            $updateFields[] = 'role_id = ?';
            $params[] = $roleId;
        }
        
        $params[] = $userId;
        $stmt = $pdo->prepare("
            UPDATE users 
            SET " . implode(', ', $updateFields) . "
            WHERE id = ? AND role_id != 2
        ");
        $stmt->execute($params);
        
        // Get updated user
        $stmt = $pdo->prepare("
            SELECT 
                u.*,
                r.name as role_name,
                r.description as role_description
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        unset($user['password_hash']);
        
        // Log activity
        logActivity($pdo, $currentAdmin['id'], 'update', 'admin_users', $userId, $changes);
        
        sendResponse([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
        
    } catch (PDOException $e) {
        error_log("Update User Error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Failed to update user'
        ], 500);
    }
}

/**
 * Delete/Deactivate admin user
 */
function handleDeleteUser($pdo, $userId, $currentAdmin) {
    // Check permission - allow admin role (id=1) or users with manage_users permission
    if ($currentAdmin['role_id'] != 1 && !hasPermission('manage_users')) {
        sendResponse([
            'success' => false,
            'message' => 'Insufficient permissions'
        ], 403);
    }
    
    // Prevent self-deletion
    if ($userId == $currentAdmin['id']) {
        sendResponse([
            'success' => false,
            'message' => 'You cannot delete your own account'
        ], 400);
    }
    
    try {
        // Check if user exists (admin users only, role_id != 2)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role_id != 2");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            sendResponse([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        
        // Soft delete: Set is_active to 0 instead of deleting
        $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ? AND role_id != 2");
        $stmt->execute([$userId]);
        
        // Log activity
        logActivity($pdo, $currentAdmin['id'], 'delete', 'admin_users', $userId, [
            'email' => $user['email']
        ]);
        
        sendResponse([
            'success' => true,
            'message' => 'User deactivated successfully'
        ]);
        
    } catch (PDOException $e) {
        error_log("Delete User Error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Failed to delete user'
        ], 500);
    }
}