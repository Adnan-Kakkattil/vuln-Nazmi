<?php
/**
 * Permissions Management API
 * Endpoint: /api/v1/admin/permissions.php
 * Methods: GET (list), GET ?role_id= (permissions for role)
 */

header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../../../includes/config.php';

// Require admin authentication
requireAdminAuthOrDie();

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

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $roleId = $_GET['role_id'] ?? null;
    $module = $_GET['module'] ?? null;
    
    try {
        if ($roleId) {
            // Get permissions for specific role
            $stmt = $pdo->prepare("
                SELECT p.*
                FROM permissions p
                INNER JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id = ?
                ORDER BY p.module, p.name
            ");
            $stmt->execute([$roleId]);
            $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse([
                'success' => true,
                'data' => $permissions
            ]);
        } else {
            // Get all permissions, optionally filtered by module
            $sql = "SELECT * FROM permissions";
            $params = [];
            
            if ($module) {
                $sql .= " WHERE module = ?";
                $params[] = $module;
            }
            
            $sql .= " ORDER BY module, name";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Group by module
            $grouped = [];
            foreach ($permissions as $permission) {
                $module = $permission['module'];
                if (!isset($grouped[$module])) {
                    $grouped[$module] = [];
                }
                $grouped[$module][] = $permission;
            }
            
            sendResponse([
                'success' => true,
                'data' => $permissions,
                'grouped' => $grouped
            ]);
        }
    } catch (PDOException $e) {
        error_log("Get Permissions Error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Failed to fetch permissions'
        ], 500);
    }
} else {
    sendResponse([
        'success' => false,
        'message' => 'Method not allowed'
    ], 405);
}