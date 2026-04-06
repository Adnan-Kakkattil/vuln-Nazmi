<?php
/**
 * Authentication Middleware
 * Use this to protect API endpoints
 */

// Load config if not already loaded
if (!function_exists('config')) {
    require_once __DIR__ . '/../../../includes/config.php';
}

/**
 * Check if admin is authenticated
 * @return bool|array Returns false if not authenticated, admin data if authenticated
 */
function requireAdminAuth() {
    // Start session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if admin is logged in - accept either admin_logged_in flag or admin_id
    // This provides backward compatibility with existing sessions
    $isLoggedIn = (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) || 
                  (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']));
    
    if (!$isLoggedIn) {
        return false;
    }
    
    // Ensure admin_id exists
    if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
        return false;
    }
    
    // Set admin_logged_in if not set (for compatibility)
    if (!isset($_SESSION['admin_logged_in'])) {
        $_SESSION['admin_logged_in'] = true;
    }
    
    // Check session timeout
    $sessionTimeout = config('ADMIN_SESSION_TIMEOUT', 3600);
    $loginTime = $_SESSION['admin_login_time'] ?? 0;
    
    // If no login time is set, set it now
    if ($loginTime == 0) {
        $_SESSION['admin_login_time'] = time();
        $loginTime = time();
    }
    
    if (time() - $loginTime > $sessionTimeout) {
        session_destroy();
        return false;
    }
    
    // Get role_id and role_name from session or database if missing
    $roleId = $_SESSION['admin_role_id'] ?? null;
    $roleName = $_SESSION['admin_role_name'] ?? null;
    
    // If role info is missing, fetch from database
    if (!$roleId || !$roleName) {
        try {
            require_once __DIR__ . '/../../../includes/config.php';
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("
                SELECT u.role_id, r.name as role_name
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.id = ? AND u.role_id != 2
            ");
            $stmt->execute([$_SESSION['admin_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $_SESSION['admin_role_id'] = $user['role_id'];
                $_SESSION['admin_role_name'] = $user['role_name'] ?? 'Super Admin';
                $roleId = $_SESSION['admin_role_id'];
                $roleName = $_SESSION['admin_role_name'];
            }
        } catch (PDOException $e) {
            error_log("Auth Middleware Error: " . $e->getMessage());
        }
    }
    
    return [
        'id' => $_SESSION['admin_id'],
        'email' => $_SESSION['admin_email'] ?? null,
        'role_id' => $roleId,
        'role_name' => $roleName
    ];
}

/**
 * Require admin authentication - exit if not authenticated
 */
function requireAdminAuthOrDie() {
    $admin = requireAdminAuth();
    
    if ($admin === false) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Authentication required',
            'authenticated' => false
        ]);
        exit;
    }
    
    return $admin;
}

/**
 * Check if admin has specific permission
 * @param string $permission Permission name
 * @return bool
 */
function hasPermission($permission) {
    $admin = requireAdminAuth();
    
    if (!$admin) {
        return false;
    }
    
    // Super Admin has all permissions
    if ($admin['role_name'] === 'Super Admin') {
        return true;
    }
    
    // Load configuration
    require_once __DIR__ . '/../../../includes/config.php';
    $pdo = getDbConnection();
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM role_permissions rp
            INNER JOIN permissions p ON rp.permission_id = p.id
            WHERE rp.role_id = ? AND p.name = ?
        ");
        $stmt->execute([$admin['role_id'], $permission]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    } catch (PDOException $e) {
        error_log("Permission Check Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Require specific permission - exit if not authorized
 * @param string $permission Permission name
 */
function requirePermission($permission) {
    requireAdminAuthOrDie();
    
    if (!hasPermission($permission)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Insufficient permissions',
            'required_permission' => $permission
        ]);
        exit;
    }
}

/**
 * Get current authenticated admin ID
 * @return int|null
 */
function getCurrentAdminId() {
    $admin = requireAdminAuth();
    return $admin ? $admin['id'] : null;
}