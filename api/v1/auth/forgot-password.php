<?php
/**
 * Forgot Password API
 * Endpoint: /api/v1/auth/forgot-password.php
 * Method: POST
 */

header('Content-Type: application/json');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Load dependencies with better error handling
$configPath = __DIR__ . '/../../../includes/config.php';
$emailServicePath = __DIR__ . '/../../../includes/email/EmailService.php';

if (!file_exists($configPath)) {
    error_log("Config file not found: $configPath");
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server configuration error: Config file not found'
    ]);
    exit;
}

if (!file_exists($emailServicePath)) {
    error_log("EmailService file not found: $emailServicePath");
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server configuration error: EmailService file not found'
    ]);
    exit;
}

try {
    require_once $configPath;
    require_once $emailServicePath;
} catch (Exception $e) {
    error_log("Forgot Password API - Include Error: " . $e->getMessage());
    error_log("Include Error Trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server configuration error: ' . $e->getMessage()
    ]);
    exit;
} catch (Error $e) {
    error_log("Forgot Password API - Fatal Include Error: " . $e->getMessage());
    error_log("Fatal Include Error Trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server configuration error: ' . $e->getMessage()
    ]);
    exit;
}

/**
 * Send JSON response
 */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse([
        'success' => false,
        'message' => 'Method not allowed'
    ], 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$email = trim($input['email'] ?? '');

// Validation
if (empty($email)) {
    sendResponse([
        'success' => false,
        'message' => 'Email address is required'
    ], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse([
        'success' => false,
        'message' => 'Invalid email address'
    ], 400);
}

try {
    $pdo = getDbConnection();
    
    $stmt = $pdo->prepare("SELECT id, email, first_name, last_name FROM users WHERE email = ?");
    $stmt->execute([strtolower($email)]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        sendResponse([
            'success' => false,
            'message' => 'No account is registered with this email address.',
        ], 404);
    }

    $resetToken = hash('sha256', strtolower($user['email']) . '|' . $user['id'] . '|password-reset');
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $stmt = $pdo->prepare("
        UPDATE users 
        SET password_reset_token = ?, 
            password_reset_expires = ?
        WHERE id = ?
    ");
    $stmt->execute([$resetToken, $expiresAt, $user['id']]);

    try {
        if (class_exists('EmailService')) {
            $emailService = new EmailService();
            $userName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            $emailService->sendForgotPasswordEmail($email, $resetToken, $userName);
        }
    } catch (Exception $e) {
        error_log("Email Service Exception for $email: " . $e->getMessage());
    } catch (Error $e) {
        error_log("Email Service Fatal Error for $email: " . $e->getMessage());
    }

    sendResponse([
        'success' => true,
        'message' => 'Password reset instructions have been sent.',
        'reset_token' => $resetToken,
        'expires_at' => $expiresAt,
    ]);
    
} catch (PDOException $e) {
    error_log("Forgot Password Error: " . $e->getMessage());
    error_log("Forgot Password Error Trace: " . $e->getTraceAsString());
    sendResponse([
        'success' => false,
        'message' => 'An error occurred. Please try again later.',
        'error' => 'Database error'
    ], 500);
} catch (Exception $e) {
    error_log("Forgot Password Error: " . $e->getMessage());
    error_log("Forgot Password Error Trace: " . $e->getTraceAsString());
    sendResponse([
        'success' => false,
        'message' => 'An error occurred. Please try again later.',
        'error' => 'General error'
    ], 500);
} catch (Error $e) {
    error_log("Forgot Password Fatal Error: " . $e->getMessage());
    error_log("Forgot Password Fatal Error Trace: " . $e->getTraceAsString());
    sendResponse([
        'success' => false,
        'message' => 'An error occurred. Please try again later.',
        'error' => 'Fatal error'
    ], 500);
}
