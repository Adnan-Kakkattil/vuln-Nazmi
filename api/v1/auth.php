<?php
/**
 * User Authentication API
 * Endpoint: /api/v1/auth.php
 * Handles user authentication for the public website
 *
 * Methods:
 * - POST: Login or Register
 * - GET: Verify session / Get user info
 * - DELETE: Logout
 */

header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../../includes/config.php';

// Database connection
$pdo = getDbConnection();

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Send JSON response
 */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

/**
 * Rate limiting for security
 */
function checkRateLimit($key, $maxAttempts = 5, $windowSeconds = 300) {
    $cacheFile = sys_get_temp_dir() . '/rate_limit_' . md5($key) . '.json';
    
    $data = [];
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true) ?? [];
    }
    
    $now = time();
    $windowStart = $now - $windowSeconds;
    
    // Filter out old attempts
    $data = array_filter($data, function($timestamp) use ($windowStart) {
        return $timestamp > $windowStart;
    });
    
    if (count($data) >= $maxAttempts) {
        return false;
    }
    
    $data[] = $now;
    file_put_contents($cacheFile, json_encode($data));
    
    return true;
}

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $action = $data['action'] ?? 'login';
            
            if ($action === 'register' || $action === 'signup') {
                handleRegister($pdo, $data);
            } else {
                handleLogin($pdo, $data);
            }
            break;
            
        case 'GET':
            handleVerifySession($pdo);
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $action = $data['action'] ?? 'update_profile';
            
            if ($action === 'change_password') {
                handleChangePassword($pdo, $data);
            } else {
                handleUpdateProfile($pdo, $data);
            }
            break;
            
        case 'DELETE':
            handleLogout();
            break;
            
        default:
            sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (PDOException $e) {
    error_log("Auth API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'Database error occurred'
    ], 500);
} catch (Exception $e) {
    error_log("Auth API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'An error occurred'
    ], 500);
}

/**
 * Handle user login (supports both admin and regular users)
 */
function handleLogin($pdo, $data) {
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    
    // Validation
    if (empty($email) || empty($password)) {
        sendResponse([
            'success' => false,
            'message' => 'Email and password are required'
        ], 400);
    }
    
    // Rate limiting
    if (!checkRateLimit('user_login_' . $email, 5, 300)) {
        sendResponse([
            'success' => false,
            'message' => 'Too many login attempts. Please try again later.'
        ], 429);
    }
    
    // Check if login is enabled
    $loginEnabled = getSystemSetting($pdo, 'auth_login_enabled');
    if ($loginEnabled === '0') {
        sendResponse([
            'success' => false,
            'message' => 'Login is currently disabled'
        ], 403);
    }
    
    $emailLower = strtolower($email);
    
    // Check users table (unified for both admin and customer)
    $stmt = $pdo->prepare("
        SELECT u.id, u.email, u.password_hash, u.first_name, u.last_name, u.phone, u.role_id, u.is_active, u.email_verified,
               r.name as role_name
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        WHERE u.email = ?
    ");
    $stmt->execute([$emailLower]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        sendResponse([
            'success' => false,
            'message' => 'Invalid email or password'
        ], 401);
    }
    
    // Check if user is active
    if (!$user['is_active']) {
        sendResponse([
            'success' => false,
            'message' => 'Your account has been deactivated. Please contact support.'
        ], 403);
    }
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        sendResponse([
            'success' => false,
            'message' => 'Invalid email or password'
        ], 401);
    }
    
    // Update last login
    $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $updateStmt->execute([$user['id']]);
    
    // Check if user is admin (role_id != 2, where 2 is Customer)
    // Any role that's not Customer is considered an admin role
    $isAdmin = ($user['role_id'] != 2);
    
    if ($isAdmin) {
        // Set admin session
        $adminName = trim($user['first_name'] . ' ' . $user['last_name']);
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['admin_name'] = $adminName;
        $_SESSION['admin_role_id'] = $user['role_id'];
        $_SESSION['admin_role_name'] = $user['role_name'] ?? 'Super Admin';
        $_SESSION['admin_role'] = $user['role_name'] ?? 'Super Admin'; // Keep for compatibility
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login_time'] = time();
        
        // Also set user session for compatibility
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $adminName;
        
        // Return admin data
        sendResponse([
            'success' => true,
            'message' => 'Login successful',
            'is_admin' => true,
            'data' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $adminName,
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'role' => $user['role_name'] ?? 'Admin'
            ]
        ]);
    } else {
        // Set customer session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
        $_SESSION['user_login_time'] = time();
        
        // Migrate guest cart to user cart
        migrateGuestCart($pdo, $user['id'], session_id());
        
        // Return user data (excluding sensitive info)
        sendResponse([
            'success' => true,
            'message' => 'Login successful',
            'is_admin' => false,
            'data' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'phone' => $user['phone'],
                'email_verified' => (bool)$user['email_verified']
            ]
        ]);
    }
}

/**
 * Handle user registration
 */
function handleRegister($pdo, $data) {
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $firstName = trim($data['first_name'] ?? '');
    $lastName = trim($data['last_name'] ?? '');
    $phone = trim($data['phone'] ?? '');
    
    // Check if signup is enabled
    $signupEnabled = getSystemSetting($pdo, 'auth_signup_enabled');
    if ($signupEnabled === '0') {
        sendResponse([
            'success' => false,
            'message' => 'Registration is currently disabled'
        ], 403);
    }
    
    // Validation
    if (empty($email) || empty($password) || empty($firstName)) {
        sendResponse([
            'success' => false,
            'message' => 'Email, password, and first name are required'
        ], 400);
    }
    
    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse([
            'success' => false,
            'message' => 'Please enter a valid email address'
        ], 400);
    }
    
    // Password strength validation
    if (strlen($password) < 8) {
        sendResponse([
            'success' => false,
            'message' => 'Password must be at least 8 characters long'
        ], 400);
    }
    
    // Check if email already exists
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->execute([strtolower($email)]);
    if ($checkStmt->fetch()) {
        sendResponse([
            'success' => false,
            'message' => 'An account with this email already exists'
        ], 409);
    }
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Generate email verification token
    $verificationToken = bin2hex(random_bytes(32));
    
    // Insert new user
    $stmt = $pdo->prepare("
        INSERT INTO users (email, password_hash, first_name, last_name, phone, email_verification_token, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        strtolower($email),
        $passwordHash,
        $firstName,
        $lastName,
        $phone,
        $verificationToken
    ]);
    
    $userId = $pdo->lastInsertId();
    
    // Set session (auto-login after registration)
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = strtolower($email);
    $_SESSION['user_name'] = trim($firstName . ' ' . $lastName);
    $_SESSION['user_login_time'] = time();
    
    // Migrate guest cart to user cart
    migrateGuestCart($pdo, $userId, session_id());
    
    // TODO: Send verification email if email service is enabled
    
    sendResponse([
        'success' => true,
        'message' => 'Registration successful',
        'data' => [
            'id' => $userId,
            'email' => strtolower($email),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone,
            'email_verified' => false
        ]
    ], 201);
}

/**
 * Verify session and return user info (supports both admin and regular users)
 */
function handleVerifySession($pdo) {
    $adminId = $_SESSION['admin_id'] ?? null;
    $userId = $_SESSION['user_id'] ?? null;

    $requestedUserId = intval($_GET['user_id'] ?? 0);
    if ($requestedUserId > 0) {
        if (!$userId && !$adminId) {
            sendResponse(['success' => false, 'message' => 'Authentication required'], 401);
        }
        $stmt = $pdo->prepare("
            SELECT u.id, u.email, u.first_name, u.last_name, u.phone, u.email_verified, u.is_active, u.role_id,
                   r.name as role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = ?
        ");
        $stmt->execute([$requestedUserId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            sendResponse(['success' => false, 'message' => 'User not found'], 404);
        }
        $isAdminRole = ($row['role_id'] != 2);
        sendResponse([
            'success' => true,
            'authenticated' => true,
            'is_admin' => $isAdminRole,
            'data' => [
                'id' => $row['id'],
                'email' => $row['email'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'phone' => $row['phone'],
                'email_verified' => (bool) $row['email_verified'],
                'role' => $row['role_name'] ?? ($isAdminRole ? 'Admin' : 'Customer'),
            ],
        ]);
    }
    
    // Check admin session first (check if user_id has admin role)
    if ($adminId) {
        $loginTime = $_SESSION['admin_login_time'] ?? 0;
        if (time() - $loginTime > 86400) {
            // Session expired
            session_destroy();
            sendResponse([
                'success' => true,
                'authenticated' => false,
                'message' => 'Session expired'
            ]);
        }
        
        // Get admin data from users table (any role that's not Customer)
        $stmt = $pdo->prepare("
            SELECT u.id, u.email, u.first_name, u.last_name, u.role_id, u.is_active,
                   r.name as role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = ? AND u.role_id != 2
        ");
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$admin || !$admin['is_active']) {
            session_destroy();
            sendResponse([
                'success' => true,
                'authenticated' => false,
                'message' => 'Admin not found or inactive'
            ]);
        }
        
        $adminName = trim($admin['first_name'] . ' ' . $admin['last_name']);
        
        sendResponse([
            'success' => true,
            'authenticated' => true,
            'is_admin' => true,
            'data' => [
                'id' => $admin['id'],
                'email' => $admin['email'],
                'name' => $adminName,
                'first_name' => $admin['first_name'],
                'last_name' => $admin['last_name'],
                'role' => $admin['role_name'] ?? 'Admin'
            ]
        ]);
    }
    
    // Check regular user session
    if (!$userId) {
        sendResponse([
            'success' => true,
            'authenticated' => false,
            'message' => 'Not logged in'
        ]);
    }
    
    // Check session timeout (24 hours)
    $loginTime = $_SESSION['user_login_time'] ?? 0;
    if (time() - $loginTime > 86400) {
        // Session expired
        session_destroy();
        sendResponse([
            'success' => true,
            'authenticated' => false,
            'message' => 'Session expired'
        ]);
    }
    
    // Get user data
    $stmt = $pdo->prepare("
        SELECT u.id, u.email, u.first_name, u.last_name, u.phone, u.email_verified, u.is_active, u.role_id,
               r.name as role_name
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !$user['is_active']) {
        session_destroy();
        sendResponse([
            'success' => true,
            'authenticated' => false,
            'message' => 'User not found or inactive'
        ]);
    }
    
    // Check if user is admin (role_id != 2, where 2 is Customer)
    $isAdmin = ($user['role_id'] != 2);
    
    sendResponse([
        'success' => true,
        'authenticated' => true,
        'is_admin' => $isAdmin,
        'data' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'phone' => $user['phone'],
            'email_verified' => (bool)$user['email_verified'],
            'role' => $user['role_name'] ?? ($isAdmin ? 'Admin' : 'Customer')
        ]
    ]);
}

/**
 * Handle logout
 */
function handleLogout() {
    // Clear session
    $_SESSION = [];
    
    // Destroy session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    
    sendResponse([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
}

/**
 * Handle profile update (supports both users and admins)
 */
function handleUpdateProfile($pdo, $data) {
    $userId = $_SESSION['user_id'] ?? null;
    $adminId = $_SESSION['admin_id'] ?? null;
    $isAdmin = !empty($adminId);
    
    if (!$userId && !$adminId) {
        sendResponse([
            'success' => false,
            'message' => 'Not logged in'
        ], 401);
    }
    
    $firstName = trim($data['first_name'] ?? '');
    $lastName = trim($data['last_name'] ?? '');
    $phone = trim($data['phone'] ?? '');
    
    if (empty($firstName)) {
        sendResponse([
            'success' => false,
            'message' => 'First name is required'
        ], 400);
    }
    
    try {
        // Use unified users table for both admin and customer
        $targetId = $isAdmin ? $adminId : $userId;
        if (isset($data['user_id']) && intval($data['user_id']) > 0) {
            $targetId = intval($data['user_id']);
        }

        if ($isAdmin) {
            // Update admin user (any role that's not Customer)
            $stmt = $pdo->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, updated_at = NOW()
                WHERE id = ? AND role_id != 2
            ");
            $stmt->execute([$firstName, $lastName, $targetId]);
            
            // Update session
            $_SESSION['admin_name'] = trim($firstName . ' ' . $lastName);
            $_SESSION['user_name'] = trim($firstName . ' ' . $lastName);
        } else {
            // Update regular user
            $stmt = $pdo->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, phone = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$firstName, $lastName, $phone, $targetId]);

            if (array_key_exists('role_id', $data)) {
                $newRole = intval($data['role_id']);
                $pdo->prepare('UPDATE users SET role_id = ? WHERE id = ?')->execute([$newRole, $targetId]);
            }
            
            // Update session
            $_SESSION['user_name'] = trim($firstName . ' ' . $lastName);
        }
        
        sendResponse([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone
            ]
        ]);
    } catch (Exception $e) {
        error_log("Profile update error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Failed to update profile'
        ], 500);
    }
}

/**
 * Handle password change (supports both users and admins)
 */
function handleChangePassword($pdo, $data) {
    $userId = $_SESSION['user_id'] ?? null;
    $adminId = $_SESSION['admin_id'] ?? null;
    $isAdmin = !empty($adminId);
    
    if (!$userId && !$adminId) {
        sendResponse([
            'success' => false,
            'message' => 'Not logged in'
        ], 401);
    }
    
    $currentPassword = $data['current_password'] ?? '';
    $newPassword = $data['new_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword)) {
        sendResponse([
            'success' => false,
            'message' => 'Current and new password are required'
        ], 400);
    }
    
    if (strlen($newPassword) < 8) {
        sendResponse([
            'success' => false,
            'message' => 'Password must be at least 8 characters long'
        ], 400);
    }
    
    // Verify current password (unified users table)
    $targetId = $isAdmin ? $adminId : $userId;
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$targetId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
        sendResponse([
            'success' => false,
            'message' => 'Current password is incorrect'
        ], 401);
    }
    
    // Update password (unified users table)
    try {
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$newPasswordHash, $targetId]);
        
        sendResponse([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    } catch (Exception $e) {
        error_log("Password change error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Failed to change password'
        ], 500);
    }
}

/**
 * Migrate guest cart items to user cart
 * Checks both the current session ID and any stored guest session ID from cookie
 */
function migrateGuestCart($pdo, $userId, $sessionId) {
    try {
        // Get guest session ID from cookie (stored before redirect to login)
        $guestSessionId = $_COOKIE['guest_session_id'] ?? null;
        
        // Collect all session IDs to check (current session + guest session from cookie)
        $sessionIdsToCheck = [$sessionId];
        if ($guestSessionId && $guestSessionId !== $sessionId) {
            $sessionIdsToCheck[] = $guestSessionId;
        }
        
        // Get all guest cart items from any of the session IDs
        $placeholders = implode(',', array_fill(0, count($sessionIdsToCheck), '?'));
        $guestCartStmt = $pdo->prepare("
            SELECT product_id, quantity, price, session_id 
            FROM cart_items 
            WHERE session_id IN ($placeholders) AND user_id IS NULL
        ");
        $guestCartStmt->execute($sessionIdsToCheck);
        $guestItems = $guestCartStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($guestItems)) {
            // Clear the guest session cookie if no items found
            if ($guestSessionId) {
                setcookie('guest_session_id', '', time() - 3600, '/', '', false, true);
            }
            return;
        }
        
        // Migrate each item
        foreach ($guestItems as $item) {
            // Check if user already has this product in cart
            $existsStmt = $pdo->prepare("
                SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?
            ");
            $existsStmt->execute([$userId, $item['product_id']]);
            $existingItem = $existsStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingItem) {
                // Update quantity (add guest quantity to existing)
                $updateStmt = $pdo->prepare("
                    UPDATE cart_items SET quantity = quantity + ? WHERE id = ?
                ");
                $updateStmt->execute([$item['quantity'], $existingItem['id']]);
            } else {
                // Insert as user's cart item
                $insertStmt = $pdo->prepare("
                    INSERT INTO cart_items (user_id, session_id, product_id, quantity, price)
                    VALUES (?, NULL, ?, ?, ?)
                ");
                $insertStmt->execute([$userId, $item['product_id'], $item['quantity'], $item['price']]);
            }
        }
        
        // Delete all guest cart items from all checked session IDs
        $deletePlaceholders = implode(',', array_fill(0, count($sessionIdsToCheck), '?'));
        $deleteStmt = $pdo->prepare("
            DELETE FROM cart_items 
            WHERE session_id IN ($deletePlaceholders) AND user_id IS NULL
        ");
        $deleteStmt->execute($sessionIdsToCheck);
        
        // Clear the guest session cookie after successful migration
        if ($guestSessionId) {
            setcookie('guest_session_id', '', time() - 3600, '/', '', false, true);
        }
        
    } catch (Exception $e) {
        error_log("Cart migration error: " . $e->getMessage());
        // Don't throw - cart migration failure shouldn't break login
    }
}

/**
 * Get system setting value
 */
function getSystemSetting($pdo, $key) {
    $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['setting_value'] : null;
}
