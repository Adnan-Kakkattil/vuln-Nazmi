<?php
/**
 * Wishlist API
 * Endpoint: /api/v1/wishlist.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = getDbConnection();

function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Wishlist usually requires authentication, but we'll allow guest check for consistency if needed.
// However, the schema says user_id is NOT NULL in wishlist_items.
// Let's check schema again.
/*
CREATE TABLE wishlist_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    ...
)
*/
// So for wishlist, the user MUST be logged in. 
// If not logged in, we'll return an error or empty list.

$userId = $_SESSION['user_id'] ?? null;

if (!$userId && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    // For POST/DELETE, require login
    sendResponse([
        'success' => false,
        'message' => 'Please login to manage your wishlist',
        'requires_login' => true
    ], 401);
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGetWishlist($pdo, $userId);
            break;
        case 'POST':
            handleToggleWishlist($pdo, $userId);
            break;
        case 'DELETE':
            handleRemoveFromWishlist($pdo, $userId);
            break;
        default:
            sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    error_log("Wishlist API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage()
    ], 500);
}

function getWishlistData($pdo, $userId) {
    if (!$userId) {
        return [
            'items' => [],
            'count' => 0
        ];
    }

    $query = "
        SELECT 
            wi.id,
            wi.product_id,
            p.name,
            p.price,
            (
                SELECT image_url 
                FROM product_images 
                WHERE product_id = p.id 
                AND is_primary = 1 
                LIMIT 1
            ) as image
        FROM wishlist_items wi
        JOIN products p ON wi.product_id = p.id
        WHERE wi.user_id = :user_id";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as &$item) {
        $item['id'] = (int)$item['id'];
        $item['product_id'] = (int)$item['product_id'];
        $item['price'] = (float)$item['price'];
    }

    return [
        'items' => $items,
        'count' => count($items)
    ];
}

function handleGetWishlist($pdo, $userId) {
    sendResponse([
        'success' => true,
        'data' => getWishlistData($pdo, $userId)
    ]);
}

function handleToggleWishlist($pdo, $userId) {
    $input = json_decode(file_get_contents('php://input'), true);
    $productId = intval($input['product_id'] ?? 0);

    if (!$productId) {
        sendResponse(['success' => false, 'message' => 'Product ID is required'], 400);
    }

    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM wishlist_items WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Remove
        $stmt = $pdo->prepare("DELETE FROM wishlist_items WHERE id = ?");
        $stmt->execute([$existing['id']]);
        sendResponse([
            'success' => true,
            'message' => 'Removed from wishlist',
            'action' => 'removed',
            'data' => getWishlistData($pdo, $userId)
        ]);
    } else {
        // Add
        $stmt = $pdo->prepare("INSERT INTO wishlist_items (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$userId, $productId]);
        sendResponse([
            'success' => true,
            'message' => 'Added to wishlist',
            'action' => 'added',
            'data' => getWishlistData($pdo, $userId)
        ]);
    }
}

function handleRemoveFromWishlist($pdo, $userId) {
    $input = json_decode(file_get_contents('php://input'), true);
    $productId = intval($input['product_id'] ?? 0);

    if (!$productId) {
        sendResponse(['success' => false, 'message' => 'Product ID is required'], 400);
    }

    $stmt = $pdo->prepare("DELETE FROM wishlist_items WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);

    sendResponse([
        'success' => true,
        'message' => 'Removed from wishlist',
        'data' => getWishlistData($pdo, $userId)
    ]);
}
