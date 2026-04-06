<?php
/**
 * Admin Authentication API
 * Endpoint: /api/v1/admin/auth.php
 * Methods: POST (login), DELETE (logout), GET (verify)
 */

header('Content-Type: application/json');

// Load configuration
require_once __DIR__ . '/../../../includes/config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CORS headers
$allowedOrigins = explode(',', config('CORS_ALLOWED_ORIGINS', ''));
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}

// Handle OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    http_response_code(200);
    exit;
}

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
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Rate limiting check (simple implementation)
 */
function checkRateLimit($identifier, $maxRequests = 5, $windowSeconds = 300) {
    // Simple file-based rate limiting (in production, use Redis)
    $rateLimitFile = __DIR__ . '/../../../storage/ratelimit_' . md5($identifier) . '.json';
    $currentTime = time();
    
    if (file_exists($rateLimitFile)) {
        $data = json_decode(file_get_contents($rateLimitFile), true);
        
        // Clean old entries
        $data = array_filter($data, function($timestamp) use ($currentTime, $windowSeconds) {
            return ($currentTime - $timestamp) < $windowSeconds;
        });
        
        if (count($data) >= $maxRequests) {
            return false; // Rate limit exceeded
        }
    } else {
        $data = [];
    }
    
    // Add current request
    $data[] = $currentTime;
    file_put_contents($rateLimitFile, json_encode($data));
    
    return true;
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Route requests
switch ($method) {
    case 'POST':
        // Login
        handleLogin($pdo);
        break;
        
    case 'DELETE':
        // Logout
        handleLogout($pdo);
        break;
        
    case 'GET':
        // Verify session / Get current admin
        handleVerify($pdo);
        break;
        
    default:
        sendResponse([
            'success' => false,
            'message' => 'Method not allowed'
        ], 405);
}

/**
 * Handle login
 */
function handleLogin($pdo) {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST; // Fallback to form data
    }
    
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $remember = isset($input['remember']) && $input['remember'] === true;
    
    // Validation
    if (empty($email) || empty($password)) {
        sendResponse([
            'success' => false,
            'message' => 'Email and password are required'
        ], 400);
    }
    
    if (!validateEmail($email)) {
        sendResponse([
            'success' => false,
            'message' => 'Invalid email format'
        ], 400);
    }
    
    // Rate limiting
    if (!checkRateLimit('admin_login_' . $email, 5, 300)) {
        sendResponse([
            'success' => false,
            'message' => 'Too many login attempts. Please try again later.'
        ], 429);
    }
    
    try {
        // Get admin user from unified users table (role_id = 1)
        $stmt = $pdo->prepare("
            SELECT 
                u.*,
                r.name as role_name,
                r.description as role_description
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.email = ? AND u.role_id = 1 AND u.is_active = 1
        ");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$admin) {
            sendResponse([
                'success' => false,
                'message' => 'Invalid email or password'
            ], 401);
        }
        
        // Verify password
        if (!password_verify($password, $admin['password_hash'])) {
            sendResponse([
                'success' => false,
                'message' => 'Invalid email or password'
            ], 401);
        }
        
        // Update last login
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $updateStmt->execute([$admin['id']]);
        
        // Create session
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_name'] = trim($admin['first_name'] . ' ' . $admin['last_name']);
        $_SESSION['admin_role_id'] = $admin['role_id'];
        $_SESSION['admin_role_name'] = $admin['role_name'];
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login_time'] = time();
        
        // Also set user session for compatibility
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user_email'] = $admin['email'];
        $_SESSION['user_name'] = trim($admin['first_name'] . ' ' . $admin['last_name']);
        
        // Set session timeout
        if ($remember) {
            $_SESSION['admin_remember'] = true;
            // Extend session lifetime (e.g., 7 days)
            ini_set('session.gc_maxlifetime', 604800);
        }
        
        // Log activity
        $logStmt = $pdo->prepare("
            INSERT INTO admin_activity_log (user_id, action, module, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?)
        ");
        $logStmt->execute([
            $admin['id'],
            'login',
            'auth',
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        // Return admin data (without sensitive info)
        sendResponse([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'id' => $admin['id'],
                'email' => $admin['email'],
                'first_name' => $admin['first_name'],
                'last_name' => $admin['last_name'],
                'role' => [
                    'id' => $admin['role_id'],
                    'name' => $admin['role_name'],
                    'description' => $admin['role_description']
                ]
            ]
        ], 200);
        
    } catch (PDOException $e) {
        error_log("Admin Login Error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Login failed. Please try again.'
        ], 500);
    }
}

/**
 * Handle logout
 */
function handleLogout($pdo = null) {
    if (isset($_SESSION['admin_id'])) {
        $adminId = $_SESSION['admin_id'];
        
        // Log activity if we have DB connection
        if ($pdo) {
            try {
                $logStmt = $pdo->prepare("
                    INSERT INTO admin_activity_log (user_id, action, module, ip_address)
                    VALUES (?, ?, ?, ?)
                ");
                $logStmt->execute([
                    $adminId,
                    'logout',
                    'auth',
                    $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
            } catch (PDOException $e) {
                error_log("Logout Log Error: " . $e->getMessage());
            }
        }
    }
    
    // Destroy session
    session_unset();
    session_destroy();
    
    sendResponse([
        'success' => true,
        'message' => 'Logged out successfully'
    ], 200);
}

/**
 * Verify session / Get current admin
 */
function handleVerify($pdo) {
    if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
        sendResponse([
            'success' => false,
            'message' => 'Not authenticated',
            'authenticated' => false
        ], 401);
    }
    
    // Check session timeout
    $sessionTimeout = config('ADMIN_SESSION_TIMEOUT', 3600);
    $loginTime = $_SESSION['admin_login_time'] ?? 0;
    
    if (time() - $loginTime > $sessionTimeout) {
        session_destroy();
        sendResponse([
            'success' => false,
            'message' => 'Session expired',
            'authenticated' => false
        ], 401);
    }
    
    try {
        $adminId = $_SESSION['admin_id'];
        $stmt = $pdo->prepare("
            SELECT 
                u.*,
                r.name as role_name,
                r.description as role_description
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = ? AND u.role_id = 1 AND u.is_active = 1
        ");
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$admin) {
            session_destroy();
            sendResponse([
                'success' => false,
                'message' => 'Admin account not found or inactive',
                'authenticated' => false
            ], 401);
        }
        
        sendResponse([
            'success' => true,
            'authenticated' => true,
            'data' => [
                'id' => $admin['id'],
                'email' => $admin['email'],
                'first_name' => $admin['first_name'],
                'last_name' => $admin['last_name'],
                'role' => [
                    'id' => $admin['role_id'],
                    'name' => $admin['role_name'],
                    'description' => $admin['role_description']
                ]
            ]
        ], 200);
        
    } catch (PDOException $e) {
        error_log("Admin Verify Error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Verification failed',
            'authenticated' => false
        ], 500);
    }
}