<?php
/**
 * User Addresses API
 * Handles CRUD operations for user addresses
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../includes/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = getDbConnection();

function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Check if user is logged in (supports both regular users and admins)
$userId = $_SESSION['user_id'] ?? null;
$adminId = $_SESSION['admin_id'] ?? null;

if (!$userId && !$adminId) {
    sendResponse(['success' => false, 'message' => 'Authentication required'], 401);
}

// For address management, we need a valid user_id from the users table
// Admin users cannot create addresses in user_addresses table (FK constraint)
$effectiveUserId = $userId;
$isAdmin = !empty($adminId);

// If admin is trying to manage addresses, they need a user account too
if ($isAdmin && !$userId) {
    // For GET requests, we can show addresses from orders the admin might have made
    // For other operations, we need a user_id
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse([
            'success' => false, 
            'message' => 'Admin users need a customer account to save delivery addresses. Please create a customer account or use the admin panel.'
        ], 403);
    }
    $effectiveUserId = $adminId; // For viewing only
}

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            handleGetAddresses($pdo, $effectiveUserId, $isAdmin);
            break;
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            handleCreateAddress($pdo, $effectiveUserId, $input, $isAdmin);
            break;
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            handleUpdateAddress($pdo, $effectiveUserId, $input, $isAdmin);
            break;
        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            handleDeleteAddress($pdo, $effectiveUserId, $input, $isAdmin);
            break;
        default:
            sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (PDOException $e) {
    error_log("Addresses API Error: " . $e->getMessage());
    
    // Check if error is about missing columns
    if (strpos($e->getMessage(), 'Unknown column') !== false) {
        if (strpos($e->getMessage(), 'full_name') !== false || strpos($e->getMessage(), 'phone') !== false) {
            sendResponse([
                'success' => false, 
                'message' => 'Database needs to be updated. Please run: ALTER TABLE user_addresses ADD COLUMN full_name VARCHAR(200) AFTER user_id; ALTER TABLE user_addresses ADD COLUMN phone VARCHAR(20) AFTER full_name;'
            ], 500);
        }
    }
    
    sendResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    error_log("Addresses API Error: " . $e->getMessage());
    sendResponse(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
}

/**
 * Get all addresses for user
 */
function handleGetAddresses($pdo, $userId, $isAdmin) {
    // For admin users, we need to check if they have addresses in user_addresses table
    // If not, we might want to show addresses from their orders
    
    $addresses = [];
    
    // Get saved addresses
    $stmt = $pdo->prepare("
        SELECT id, full_name, phone, address_line1, address_line2, city, state, pincode, country, 
               is_default, address_type, created_at
        FROM user_addresses 
        WHERE user_id = ?
        ORDER BY is_default DESC, created_at DESC
    ");
    $stmt->execute([$userId]);
    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no saved addresses, try to get from previous orders
    if (empty($addresses)) {
        $stmt = $pdo->prepare("
            SELECT DISTINCT 
                shipping_address_line1 as address_line1,
                shipping_address_line2 as address_line2,
                shipping_city as city,
                shipping_state as state,
                shipping_pincode as pincode,
                shipping_country as country,
                shipping_first_name,
                shipping_last_name,
                shipping_phone,
                order_date as created_at
            FROM orders 
            WHERE user_id = ?
            ORDER BY order_date DESC
            LIMIT 5
        ");
        $stmt->execute([$userId]);
        $orderAddresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format order addresses as suggestions
        foreach ($orderAddresses as $addr) {
            $addresses[] = [
                'id' => null,
                'address_line1' => $addr['address_line1'],
                'address_line2' => $addr['address_line2'],
                'city' => $addr['city'],
                'state' => $addr['state'],
                'pincode' => $addr['pincode'],
                'country' => $addr['country'],
                'is_default' => 0,
                'address_type' => 'shipping',
                'from_order' => true,
                'name' => trim($addr['shipping_first_name'] . ' ' . $addr['shipping_last_name']),
                'phone' => $addr['shipping_phone']
            ];
        }
    }
    
    sendResponse([
        'success' => true,
        'data' => $addresses
    ]);
}

/**
 * Create a new address
 */
function handleCreateAddress($pdo, $userId, $data, $isAdmin) {
    // Validate required fields
    $required = ['address_line1', 'city', 'state', 'pincode'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            sendResponse(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'], 400);
        }
    }
    
    $fullName = trim($data['full_name'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $addressLine1 = trim($data['address_line1']);
    $addressLine2 = trim($data['address_line2'] ?? '');
    $city = trim($data['city']);
    $state = trim($data['state']);
    $pincode = trim($data['pincode']);
    $country = trim($data['country'] ?? 'India');
    $addressType = trim($data['address_type'] ?? 'home');
    $isDefault = !empty($data['is_default']) ? 1 : 0;
    
    // Validate pincode format (6 digits for India)
    if (!preg_match('/^\d{6}$/', $pincode)) {
        sendResponse(['success' => false, 'message' => 'Invalid pincode format'], 400);
    }
    
    // Validate phone format (10 digits for India)
    if ($phone && !preg_match('/^\d{10}$/', $phone)) {
        sendResponse(['success' => false, 'message' => 'Invalid phone number format (10 digits required)'], 400);
    }
    
    // If this is set as default, unset other defaults
    if ($isDefault) {
        $stmt = $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
        $stmt->execute([$userId]);
    }
    
    // Check if this is the first address (make it default automatically)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_addresses WHERE user_id = ?");
    $stmt->execute([$userId]);
    if ($stmt->fetchColumn() == 0) {
        $isDefault = 1;
    }
    
    // Insert new address
    $stmt = $pdo->prepare("
        INSERT INTO user_addresses (user_id, full_name, phone, address_line1, address_line2, city, state, pincode, country, address_type, is_default)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$userId, $fullName, $phone, $addressLine1, $addressLine2, $city, $state, $pincode, $country, $addressType, $isDefault]);
    
    $newId = $pdo->lastInsertId();
    
    sendResponse([
        'success' => true,
        'message' => 'Address added successfully',
        'data' => [
            'id' => $newId,
            'full_name' => $fullName,
            'phone' => $phone,
            'address_line1' => $addressLine1,
            'address_line2' => $addressLine2,
            'city' => $city,
            'state' => $state,
            'pincode' => $pincode,
            'country' => $country,
            'address_type' => $addressType,
            'is_default' => $isDefault
        ]
    ]);
}

/**
 * Update an existing address
 */
function handleUpdateAddress($pdo, $userId, $data, $isAdmin) {
    $addressId = intval($data['id'] ?? 0);
    
    if (!$addressId) {
        sendResponse(['success' => false, 'message' => 'Address ID is required'], 400);
    }
    
    // Verify ownership
    $stmt = $pdo->prepare("SELECT id FROM user_addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$addressId, $userId]);
    if (!$stmt->fetch()) {
        sendResponse(['success' => false, 'message' => 'Address not found'], 404);
    }
    
    // Validate phone if provided
    if (isset($data['phone']) && $data['phone'] && !preg_match('/^\d{10}$/', $data['phone'])) {
        sendResponse(['success' => false, 'message' => 'Invalid phone number format (10 digits required)'], 400);
    }
    
    // Build update query dynamically
    $updates = [];
    $params = [];
    
    $allowedFields = ['full_name', 'phone', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country', 'address_type', 'is_default'];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = $field === 'is_default' ? (int)$data[$field] : trim($data[$field]);
        }
    }
    
    if (empty($updates)) {
        sendResponse(['success' => false, 'message' => 'No fields to update'], 400);
    }
    
    // If setting as default, unset other defaults first
    if (!empty($data['is_default'])) {
        $stmt = $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ? AND id != ?");
        $stmt->execute([$userId, $addressId]);
    }
    
    $params[] = $addressId;
    $params[] = $userId;
    
    $sql = "UPDATE user_addresses SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    sendResponse([
        'success' => true,
        'message' => 'Address updated successfully'
    ]);
}

/**
 * Delete an address
 */
function handleDeleteAddress($pdo, $userId, $data, $isAdmin) {
    $addressId = intval($data['id'] ?? 0);
    
    if (!$addressId) {
        sendResponse(['success' => false, 'message' => 'Address ID is required'], 400);
    }
    
    // Verify ownership and check if it's used in orders
    $stmt = $pdo->prepare("SELECT id, is_default FROM user_addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$addressId, $userId]);
    $address = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$address) {
        sendResponse(['success' => false, 'message' => 'Address not found'], 404);
    }
    
    // Delete the address
    $stmt = $pdo->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$addressId, $userId]);
    
    // If this was the default address, set another one as default
    if ($address['is_default']) {
        $stmt = $pdo->prepare("
            UPDATE user_addresses 
            SET is_default = 1 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$userId]);
    }
    
    sendResponse([
        'success' => true,
        'message' => 'Address deleted successfully'
    ]);
}
