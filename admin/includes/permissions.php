<?php
/**
 * Admin Permissions Helper Functions
 * Used to check module access and filter menu items
 */

// Load config if not already loaded
if (!function_exists('getDbConnection')) {
    require_once __DIR__ . '/../../includes/config.php';
}

/**
 * Check if current admin user has access to a specific module
 * @param string $module Module name (e.g., 'dashboard', 'orders', 'stock')
 * @return bool
 */
function hasModuleAccess($module) {
    // Start session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if admin is logged in
    if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
        return false;
    }
    
    $roleId = $_SESSION['admin_role_id'] ?? null;
    
    // If role_id is 1 (Super Admin), grant access to all modules
    if ($roleId == 1) {
        return true;
    }
    
    // If no role_id, deny access
    if (!$roleId) {
        return false;
    }
    
    try {
        $pdo = getDbConnection();
        
        // Check if role has any permission for this module
        // A user has module access if they have ANY permission for that module
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM role_permissions rp
            INNER JOIN permissions p ON rp.permission_id = p.id
            WHERE rp.role_id = ? AND p.module = ?
        ");
        $stmt->execute([$roleId, $module]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    } catch (PDOException $e) {
        error_log("Module Access Check Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all modules the current admin user has access to
 * @return array Array of module names (for internal use)
 */
function getAccessibleModules() {
    // Start session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if admin is logged in
    if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
        return [];
    }
    
    $roleId = $_SESSION['admin_role_id'] ?? null;
    
    // If role_id is 1 (Super Admin), return all modules
    if ($roleId == 1) {
        return ['dashboard', 'stock', 'finance', 'coupons', 'orders', 'b2b', 'reports', 'users', 'settings'];
    }
    
    // If no role_id, return empty array
    if (!$roleId) {
        return [];
    }
    
    try {
        $pdo = getDbConnection();
        
        // Get distinct modules that the role has permissions for
        $stmt = $pdo->prepare("
            SELECT DISTINCT p.module
            FROM role_permissions rp
            INNER JOIN permissions p ON rp.permission_id = p.id
            WHERE rp.role_id = ?
            ORDER BY p.module
        ");
        $stmt->execute([$roleId]);
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return $results ?: [];
    } catch (PDOException $e) {
        error_log("Get Accessible Modules Error: " . $e->getMessage());
        return [];
    }
}
