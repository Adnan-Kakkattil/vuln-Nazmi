<?php
/**
 * Admin Categories API
 * Endpoint: /api/v1/admin/categories.php
 * Returns list of categories for dropdowns
 */

header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../../../includes/config.php';

// Database connection
$pdo = getDbConnection();

// Require admin authentication
$admin = requireAdminAuthOrDie();

/**
 * Send JSON response
 */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

/**
 * Generate slug from string
 */
function generateSlug($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

try {
    switch ($method) {
        case 'GET':
            // List categories
            $stmt = $pdo->query("
                SELECT id, name, slug, description, parent_id, is_active
                FROM categories
                WHERE is_active = 1
                ORDER BY sort_order ASC, name ASC
            ");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse([
                'success' => true,
                'data' => $categories
            ]);
            break;
            
        case 'POST':
            // Create new category
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (empty($data['name'])) {
                sendResponse([
                    'success' => false,
                    'message' => 'Category name is required'
                ], 400);
            }
            
            // Generate slug
            $slug = generateSlug($data['name']);
            
            // Check if slug already exists
            $checkStmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
            $checkStmt->execute([$slug]);
            $counter = 1;
            $originalSlug = $slug;
            while ($checkStmt->fetch()) {
                $slug = $originalSlug . '-' . $counter;
                $checkStmt->execute([$slug]);
                $counter++;
            }
            
            // Insert category
            $stmt = $pdo->prepare("
                INSERT INTO categories (name, slug, description, is_active)
                VALUES (?, ?, ?, 1)
            ");
            $stmt->execute([
                $data['name'],
                $slug,
                $data['description'] ?? null
            ]);
            
            $categoryId = $pdo->lastInsertId();
            
            // Fetch created category
            $fetchStmt = $pdo->prepare("SELECT id, name, slug, description, parent_id, is_active FROM categories WHERE id = ?");
            $fetchStmt->execute([$categoryId]);
            $category = $fetchStmt->fetch(PDO::FETCH_ASSOC);
            
            // Log activity
            $logStmt = $pdo->prepare("
                INSERT INTO admin_activity_log (user_id, action, module, entity_id, entity_type)
                VALUES (?, 'create', 'categories', ?, 'category')
            ");
            $logStmt->execute([$admin['id'], $categoryId]);
            
            sendResponse([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);
            break;
            
        default:
            sendResponse([
                'success' => false,
                'message' => 'Method not allowed'
            ], 405);
    }
    
} catch (PDOException $e) {
    error_log("Categories API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => config('DEBUG', false) ? $e->getMessage() : 'Internal server error'
    ], 500);
} catch (Exception $e) {
    error_log("Categories API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'An error occurred',
        'error' => config('DEBUG', false) ? $e->getMessage() : 'Internal server error'
    ], 500);
}
