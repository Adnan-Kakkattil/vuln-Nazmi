<?php
/**
 * Roles Management API
 * Endpoint: /api/v1/admin/roles.php
 * Methods: GET (list/single), POST (create), PUT (update), DELETE
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
            'role',
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
$roleId = $_GET['id'] ?? null;

// Route requests
switch ($method) {
    case 'GET':
        if ($roleId) {
            handleGetRole($pdo, $roleId);
        } else {
            handleListRoles($pdo);
        }
        break;
        
    case 'POST':
        handleCreateRole($pdo, $currentAdmin);
        break;
        
    case 'PUT':
        if (!$roleId) {
            sendResponse(['success' => false, 'message' => 'Role ID is required'], 400);
        }
        handleUpdateRole($pdo, $roleId, $currentAdmin);
        break;
        
    case 'DELETE':
        if (!$roleId) {
            sendResponse(['success' => false, 'message' => 'Role ID is required'], 400);
        }
        handleDeleteRole($pdo, $roleId, $currentAdmin);
        break;
        
    default:
        sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

/**
 * List all roles
 */
function handleListRoles($pdo) {
    try {
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        
        // Build query
        $where = ['1=1'];
        $params = [];
        
        if ($search) {
            $where[] = "(r.name LIKE ? OR r.description LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($status !== '') {
            $where[] = "r.is_active = ?";
            $params[] = $status;
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get roles with permission count and user count
        $stmt = $pdo->prepare("
            SELECT 
                r.*,
                COUNT(DISTINCT rp.permission_id) as permission_count,
                COUNT(DISTINCT au.id) as user_count
            FROM roles r
            LEFT JOIN role_permissions rp ON r.id = rp.role_id
            LEFT JOIN users au ON r.id = au.role_id
            WHERE {$whereClause}
            GROUP BY r.id
            ORDER BY r.created_at DESC
        ");
        $stmt->execute($params);
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        sendResponse([
            'success' => true,
            'data' => $roles
        ]);
        
    } catch (PDOException $e) {
        error_log("List Roles Error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Failed to fetch roles'
        ], 500);
    }
}

/**
 * Get single role with permissions
 */
function handleGetRole($pdo, $roleId) {
    try {
        // Get role
        $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
        $stmt->execute([$roleId]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$role) {
            sendResponse([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }
        
        // Get permissions for this role
        $stmt = $pdo->prepare("
            SELECT p.*
            FROM permissions p
            INNER JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = ?
            ORDER BY p.module, p.name
        ");
        $stmt->execute([$roleId]);
        $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get user count for this role
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role_id = ?");
        $stmt->execute([$roleId]);
        $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $role['permissions'] = $permissions;
        $role['user_count'] = intval($userCount);
        
        sendResponse([
            'success' => true,
            'data' => $role
        ]);
        
    } catch (PDOException $e) {
        error_log("Get Role Error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Failed to fetch role'
        ], 500);
    }
}

/**
 * Create new role
 */
function handleCreateRole($pdo, $currentAdmin) {
    // Check permission - Super Admin (role_id=1) has all permissions, or check manage_users permission
    // Also check role_name as fallback since hasPermission checks for 'Super Admin' role_name
    $isSuperAdmin = ($currentAdmin['role_id'] == 1) || 
                    (isset($currentAdmin['role_name']) && $currentAdmin['role_name'] === 'Super Admin') ||
                    (isset($currentAdmin['role_name']) && $currentAdmin['role_name'] === 'Admin');
    
    if (!$isSuperAdmin && !hasPermission('manage_users')) {
        sendResponse([
            'success' => false,
            'message' => 'Insufficient permissions. Only Super Admin or users with manage_users permission can create roles.'
        ], 403);
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $name = trim($input['name'] ?? '');
    $description = trim($input['description'] ?? '');
    $isActive = isset($input['is_active']) ? intval($input['is_active']) : 1;
    $permissions = $input['permissions'] ?? []; // Array of permission IDs
    
    // Validation
    if (empty($name)) {
        sendResponse([
            'success' => false,
            'message' => 'Role name is required'
        ], 400);
    }
    
    try {
        // Check if role name exists
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            sendResponse([
                'success' => false,
                'message' => 'Role name already exists'
            ], 409);
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Create role
        $stmt = $pdo->prepare("
            INSERT INTO roles (name, description, is_active)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$name, $description, $isActive]);
        $newRoleId = $pdo->lastInsertId();
        
        // Assign permissions
        if (!empty($permissions) && is_array($permissions)) {
            $stmt = $pdo->prepare("
                INSERT INTO role_permissions (role_id, permission_id)
                VALUES (?, ?)
            ");
            foreach ($permissions as $permissionId) {
                $stmt->execute([$newRoleId, intval($permissionId)]);
            }
        }
        
        $pdo->commit();
        
        // Get created role
        $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
        $stmt->execute([$newRoleId]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Log activity
        logActivity($pdo, $currentAdmin['id'], 'create', 'roles', $newRoleId, [
            'name' => $name,
            'permissions' => $permissions
        ]);
        
        sendResponse([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => $role
        ], 201);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Create Role Error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Failed to create role'
        ], 500);
    }
}

/**
 * Update role
 */
function handleUpdateRole($pdo, $roleId, $currentAdmin) {
    // Check permission - Super Admin (role_id=1) has all permissions, or check manage_users permission
    // Also check role_name as fallback since hasPermission checks for 'Super Admin' role_name
    $isSuperAdmin = ($currentAdmin['role_id'] == 1) || 
                    (isset($currentAdmin['role_name']) && $currentAdmin['role_name'] === 'Super Admin') ||
                    (isset($currentAdmin['role_name']) && $currentAdmin['role_name'] === 'Admin');
    
    if (!$isSuperAdmin && !hasPermission('manage_users')) {
        sendResponse([
            'success' => false,
            'message' => 'Insufficient permissions. Only Super Admin or users with manage_users permission can update roles.'
        ], 403);
    }
    
    // Prevent editing Super Admin role
    if ($roleId == 1) {
        sendResponse([
            'success' => false,
            'message' => 'Cannot edit Super Admin role'
        ], 400);
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    // Get current role
    $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
    $stmt->execute([$roleId]);
    $currentRole = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$currentRole) {
        sendResponse([
            'success' => false,
            'message' => 'Role not found'
        ], 404);
    }
    
    // Update fields
    $name = isset($input['name']) ? trim($input['name']) : $currentRole['name'];
    $description = isset($input['description']) ? trim($input['description']) : $currentRole['description'];
    $isActive = isset($input['is_active']) ? intval($input['is_active']) : $currentRole['is_active'];
    $permissions = $input['permissions'] ?? null; // Array of permission IDs
    
    // Validation
    if (empty($name)) {
        sendResponse([
            'success' => false,
            'message' => 'Role name is required'
        ], 400);
    }
    
    try {
        // Check if role name exists (excluding current role)
        if ($name != $currentRole['name']) {
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ? AND id != ?");
            $stmt->execute([$name, $roleId]);
            if ($stmt->fetch()) {
                sendResponse([
                    'success' => false,
                    'message' => 'Role name already exists'
                ], 409);
            }
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        $changes = [];
        
        // Update role
        $stmt = $pdo->prepare("
            UPDATE roles 
            SET name = ?, description = ?, is_active = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $description, $isActive, $roleId]);
        
        // Track changes
        if ($name != $currentRole['name']) $changes['name'] = [$currentRole['name'], $name];
        if ($description != $currentRole['description']) $changes['description'] = [$currentRole['description'], $description];
        if ($isActive != $currentRole['is_active']) $changes['is_active'] = [$currentRole['is_active'], $isActive];
        
        // Update permissions if provided
        if ($permissions !== null && is_array($permissions)) {
            // Get current permissions
            $stmt = $pdo->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
            $stmt->execute([$roleId]);
            $currentPermissions = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'permission_id');
            
            $newPermissions = array_map('intval', $permissions);
            
            // Remove permissions not in new list
            $toRemove = array_diff($currentPermissions, $newPermissions);
            if (!empty($toRemove)) {
                $placeholders = implode(',', array_fill(0, count($toRemove), '?'));
                $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ? AND permission_id IN ($placeholders)");
                $stmt->execute(array_merge([$roleId], $toRemove));
            }
            
            // Add new permissions
            $toAdd = array_diff($newPermissions, $currentPermissions);
            if (!empty($toAdd)) {
                $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                foreach ($toAdd as $permissionId) {
                    $stmt->execute([$roleId, $permissionId]);
                }
            }
            
            $changes['permissions'] = [$currentPermissions, $newPermissions];
        }
        
        $pdo->commit();
        
        // Get updated role
        $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
        $stmt->execute([$roleId]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Log activity
        logActivity($pdo, $currentAdmin['id'], 'update', 'roles', $roleId, $changes);
        
        sendResponse([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => $role
        ]);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Update Role Error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Failed to update role'
        ], 500);
    }
}

/**
 * Delete role
 */
function handleDeleteRole($pdo, $roleId, $currentAdmin) {
    // Check permission - Super Admin (role_id=1) has all permissions, or check manage_users permission
    // Also check role_name as fallback since hasPermission checks for 'Super Admin' role_name
    $isSuperAdmin = ($currentAdmin['role_id'] == 1) || 
                    (isset($currentAdmin['role_name']) && $currentAdmin['role_name'] === 'Super Admin') ||
                    (isset($currentAdmin['role_name']) && $currentAdmin['role_name'] === 'Admin');
    
    if (!$isSuperAdmin && !hasPermission('manage_users')) {
        sendResponse([
            'success' => false,
            'message' => 'Insufficient permissions. Only Super Admin or users with manage_users permission can delete roles.'
        ], 403);
    }
    
    // Prevent deleting Super Admin role
    if ($roleId == 1) {
        sendResponse([
            'success' => false,
            'message' => 'Cannot delete Super Admin role'
        ], 400);
    }
    
    try {
        // Check if role exists
        $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
        $stmt->execute([$roleId]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$role) {
            sendResponse([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }
        
        // Check if role is assigned to any users
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role_id = ?");
        $stmt->execute([$roleId]);
        $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($userCount > 0) {
            sendResponse([
                'success' => false,
                'message' => "Cannot delete role. It is assigned to {$userCount} user(s)."
            ], 400);
        }
        
        // Delete role (CASCADE will handle role_permissions)
        $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
        $stmt->execute([$roleId]);
        
        // Log activity
        logActivity($pdo, $currentAdmin['id'], 'delete', 'roles', $roleId, [
            'name' => $role['name']
        ]);
        
        sendResponse([
            'success' => true,
            'message' => 'Role deleted successfully'
        ]);
        
    } catch (PDOException $e) {
        error_log("Delete Role Error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Failed to delete role'
        ], 500);
    }
}