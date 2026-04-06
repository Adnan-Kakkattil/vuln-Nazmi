<?php
/**
 * Cart API
 * Endpoint: /api/v1/cart.php
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

// Get user or session identifier
$userId = $_SESSION['user_id'] ?? null;
$sessionId = session_id();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGetCart($pdo, $userId, $sessionId);
            break;
        case 'POST':
            handleAddToCart($pdo, $userId, $sessionId);
            break;
        case 'PUT':
            handleUpdateQuantity($pdo, $userId, $sessionId);
            break;
        case 'DELETE':
            handleRemoveFromCart($pdo, $userId, $sessionId);
            break;
        default:
            sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    error_log("Cart API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage()
    ], 500);
}

function getCartData($pdo, $userId, $sessionId) {
    $query = "
        SELECT 
            ci.id,
            ci.product_id,
            ci.quantity,
            ci.price as unit_price,
            p.name,
            p.price as current_price,
            (
                SELECT image_url 
                FROM product_images 
                WHERE product_id = p.id 
                AND is_primary = 1 
                LIMIT 1
            ) as image
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE " . ($userId ? "ci.user_id = :user_id" : "ci.session_id = :session_id");

    $stmt = $pdo->prepare($query);
    if ($userId) {
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    } else {
        $stmt->bindValue(':session_id', $sessionId, PDO::PARAM_STR);
    }
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = 0;
    $totalQuantity = 0;
    foreach ($items as &$item) {
        $item['id'] = (int)$item['id'];
        $item['product_id'] = (int)$item['product_id'];
        $item['quantity'] = (int)$item['quantity'];
        $item['unit_price'] = (float)$item['unit_price'];
        $item['current_price'] = (float)$item['current_price'];
        $item['total_price'] = $item['quantity'] * $item['current_price'];
        $total += $item['total_price'];
        $totalQuantity += $item['quantity'];
    }

    return [
        'items' => $items,
        'total' => $total,
        'count' => $totalQuantity  // Total quantity of items, not number of unique items
    ];
}

function handleGetCart($pdo, $userId, $sessionId) {
    sendResponse([
        'success' => true,
        'data' => getCartData($pdo, $userId, $sessionId)
    ]);
}

function handleAddToCart($pdo, $userId, $sessionId) {
    $input = json_decode(file_get_contents('php://input'), true);
    $productId = intval($input['product_id'] ?? 0);
    $quantity = intval($input['quantity'] ?? 1);

    if (!$productId) {
        sendResponse(['success' => false, 'message' => 'Product ID is required'], 400);
    }

    // Get product details
    $stmt = $pdo->prepare("SELECT price, name FROM products WHERE id = ? AND status = 'active'");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        sendResponse(['success' => false, 'message' => 'Product not found'], 404);
    }

    // Check if already in cart
    $checkQuery = "SELECT id, quantity FROM cart_items WHERE product_id = :product_id AND " . ($userId ? "user_id = :user_id" : "session_id = :session_id");
    $stmt = $pdo->prepare($checkQuery);
    $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
    if ($userId) {
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    } else {
        $stmt->bindValue(':session_id', $sessionId, PDO::PARAM_STR);
    }
    $stmt->execute();
    $existing = $stmt->fetch();

    if ($existing) {
        $newQuantity = $existing['quantity'] + $quantity;
        $updateStmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $updateStmt->execute([$newQuantity, $existing['id']]);
    } else {
        $insertQuery = "INSERT INTO cart_items (user_id, session_id, product_id, quantity, price) VALUES (:user_id, :session_id, :product_id, :quantity, :price)";
        $stmt = $pdo->prepare($insertQuery);
        $stmt->bindValue(':user_id', $userId, $userId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':session_id', $userId ? null : $sessionId, $userId ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindValue(':price', $product['price']);
        $stmt->execute();
    }

    sendResponse([
        'success' => true,
        'message' => 'Product added to cart',
        'data' => getCartData($pdo, $userId, $sessionId)
    ]);
}

function handleUpdateQuantity($pdo, $userId, $sessionId) {
    $input = json_decode(file_get_contents('php://input'), true);
    $productId = intval($input['product_id'] ?? 0);
    $quantity = intval($input['quantity'] ?? 0);

    if (!$productId || $quantity < 1) {
        sendResponse(['success' => false, 'message' => 'Invalid input'], 400);
    }

    $updateQuery = "UPDATE cart_items SET quantity = :quantity WHERE product_id = :product_id AND " . ($userId ? "user_id = :user_id" : "session_id = :session_id");
    $stmt = $pdo->prepare($updateQuery);
    $stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
    $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
    if ($userId) {
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    } else {
        $stmt->bindValue(':session_id', $sessionId, PDO::PARAM_STR);
    }
    $stmt->execute();

    sendResponse([
        'success' => true,
        'message' => 'Cart updated',
        'data' => getCartData($pdo, $userId, $sessionId)
    ]);
}

function handleRemoveFromCart($pdo, $userId, $sessionId) {
    $input = json_decode(file_get_contents('php://input'), true);
    $productId = intval($input['product_id'] ?? 0);

    if (!$productId) {
        sendResponse(['success' => false, 'message' => 'Product ID is required'], 400);
    }

    $deleteQuery = "DELETE FROM cart_items WHERE product_id = :product_id AND " . ($userId ? "user_id = :user_id" : "session_id = :session_id");
    $stmt = $pdo->prepare($deleteQuery);
    $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
    if ($userId) {
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    } else {
        $stmt->bindValue(':session_id', $sessionId, PDO::PARAM_STR);
    }
    $stmt->execute();

    sendResponse([
        'success' => true,
        'message' => 'Item removed from cart',
        'data' => getCartData($pdo, $userId, $sessionId)
    ]);
}
