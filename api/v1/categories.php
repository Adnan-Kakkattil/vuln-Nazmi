<?php
/**
 * Public Categories API
 * Endpoint: /api/v1/categories.php
 * Returns categories for public/customer access (no authentication required)
 * 
 * Methods:
 * - GET: List categories
 */

header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../../includes/config.php';

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

// Only GET method is allowed for public access
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') {
    sendResponse([
        'success' => false,
        'message' => 'Method not allowed'
    ], 405);
}

try {
    // Get query parameters
    $activeOnly = isset($_GET['active']) ? (intval($_GET['active']) === 1) : true;
    
    // Build query
    $query = "
        SELECT 
            id,
            name,
            slug,
            description,
            parent_id,
            image,
            sort_order,
            is_active
        FROM categories
    ";
    
    if ($activeOnly) {
        $query .= " WHERE is_active = 1";
    }
    
    $query .= " ORDER BY sort_order ASC, name ASC";
    
    $stmt = $pdo->query($query);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Transform categories for frontend
    $transformedCategories = [];
    foreach ($categories as $category) {
        $transformedCategories[] = [
            'id' => intval($category['id']),
            'name' => $category['name'],
            'slug' => $category['slug'],
            'description' => $category['description'] ?? '',
            'parent_id' => $category['parent_id'] ? intval($category['parent_id']) : null,
            'image' => $category['image'] ?? null,
            'sort_order' => intval($category['sort_order']),
            'is_active' => (bool)$category['is_active']
        ];
    }
    
    sendResponse([
        'success' => true,
        'categories' => $transformedCategories, // Added for frontend compatibility
        'data' => $transformedCategories // Keep for backward compatibility
    ]);
    
} catch (Exception $e) {
    error_log("Categories API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'Failed to fetch categories',
        'error' => getenv('DEBUG') ? $e->getMessage() : 'Internal server error'
    ], 500);
}
