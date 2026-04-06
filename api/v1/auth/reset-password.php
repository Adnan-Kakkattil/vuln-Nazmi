<?php
/**
 * Reset Password API
 * Endpoint: /api/v1/auth/reset-password.php
 * Method: POST
 * Security: Rate limiting, token validation, IP logging
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/email/EmailService.php';

/**
 * Send JSON response
 */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

/**
 * @return array<string,mixed>|null
 */
function fetchUserForPasswordReset(PDO $pdo, string $email, string $token): ?array {
    if ($email !== '') {
        $stmt = $pdo->prepare("
            SELECT id, email, first_name, last_name, password_reset_token, password_reset_expires
            FROM users 
            WHERE LOWER(email) = LOWER(?) AND password_reset_token = ?
        ");
        $stmt->execute([$email, $token]);
    } else {
        $stmt = $pdo->prepare("
            SELECT id, email, first_name, last_name, password_reset_token, password_reset_expires
            FROM users 
            WHERE password_reset_token = ?
        ");
        $stmt->execute([$token]);
    }
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/**
 * Get client IP address
 */
function getClientIp() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Rate limiting check
 */
function checkRateLimit($identifier, $maxAttempts = 5, $windowSeconds = 300) {
    $storageDir = __DIR__ . '/../../../storage/';
    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0755, true);
    }
    
    $rateLimitFile = $storageDir . 'ratelimit_' . md5($identifier) . '.json';
    $now = time();
    
    if (file_exists($rateLimitFile)) {
        $data = json_decode(file_get_contents($rateLimitFile), true);
        if ($data && isset($data['attempts']) && isset($data['window_start'])) {
            // Reset window if expired
            if ($now - $data['window_start'] > $windowSeconds) {
                $data = ['attempts' => 0, 'window_start' => $now];
            } else {
                // Check if limit exceeded
                if ($data['attempts'] >= $maxAttempts) {
                    $remaining = $windowSeconds - ($now - $data['window_start']);
                    return [
                        'allowed' => false,
                        'remaining' => $remaining
                    ];
                }
                $data['attempts']++;
            }
        } else {
            $data = ['attempts' => 1, 'window_start' => $now];
        }
    } else {
        $data = ['attempts' => 1, 'window_start' => $now];
    }
    
    file_put_contents($rateLimitFile, json_encode($data));
    return ['allowed' => true, 'remaining' => $maxAttempts - $data['attempts']];
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

$token = trim($input['token'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$confirmPassword = $input['confirm_password'] ?? '';
$validateOnly = isset($input['validate_only']) && $input['validate_only'] === true;

// Get client IP for rate limiting and logging
$clientIp = getClientIp();

// Rate limiting: Only apply to actual password reset attempts, not validation
if (!$validateOnly) {
    $rateLimit = checkRateLimit('reset_password_' . $clientIp, 200, 300);
    if (!$rateLimit['allowed']) {
        error_log("Reset password rate limit exceeded for IP: $clientIp");
        sendResponse([
            'success' => false,
            'message' => 'Too many reset attempts. Please try again in ' . ceil($rateLimit['remaining'] / 60) . ' minutes.'
        ], 429);
    }
}

// Security: Validate token format (64 hex characters)
if (empty($token) || !preg_match('/^[a-f0-9]{64}$/i', $token)) {
    error_log("Invalid reset token format from IP: $clientIp");
    sendResponse([
        'success' => false,
        'valid' => false,
        'message' => 'Invalid reset token format'
    ], 400);
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error_log("Invalid email format in reset password request from IP: $clientIp");
    sendResponse([
        'success' => false,
        'valid' => false,
        'message' => 'Invalid email address'
    ], 400);
}

if ($email !== '') {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
}

// If validate_only flag is set, just check token validity without resetting password
if ($validateOnly) {
    try {
        $pdo = getDbConnection();
        $user = fetchUserForPasswordReset($pdo, $email, $token);
        
        if (!$user) {
            sendResponse([
                'success' => false,
                'valid' => false,
                'message' => 'Invalid or expired reset token'
            ], 200);
        }
        
        // Check if token has expired
        if (empty($user['password_reset_expires']) || strtotime($user['password_reset_expires']) < time()) {
            sendResponse([
                'success' => false,
                'valid' => false,
                'message' => 'Reset token has expired. Please request a new one.'
            ], 200);
        }
        
        sendResponse([
            'success' => true,
            'valid' => true,
            'message' => 'Token is valid'
        ], 200);
        
    } catch (Exception $e) {
        error_log("Token validation error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'valid' => false,
            'message' => 'Error validating token'
        ], 500);
    }
}

if (empty($password)) {
    sendResponse([
        'success' => false,
        'message' => 'Password is required'
    ], 400);
}

if ($password !== $confirmPassword) {
    sendResponse([
        'success' => false,
        'message' => 'Passwords do not match'
    ], 400);
}

// Password strength validation
if (strlen($password) < 8) {
    sendResponse([
        'success' => false,
        'message' => 'Password must be at least 8 characters long'
    ], 400);
}

if (!preg_match('/[A-Z]/', $password)) {
    sendResponse([
        'success' => false,
        'message' => 'Password must contain at least one uppercase letter'
    ], 400);
}

if (!preg_match('/[a-z]/', $password)) {
    sendResponse([
        'success' => false,
        'message' => 'Password must contain at least one lowercase letter'
    ], 400);
}

if (!preg_match('/[0-9]/', $password)) {
    sendResponse([
        'success' => false,
        'message' => 'Password must contain at least one number'
    ], 400);
}

try {
    $pdo = getDbConnection();
    $user = fetchUserForPasswordReset($pdo, $email, $token);

    if (!$user) {
        error_log("Invalid reset token attempt for email: $email from IP: $clientIp");
        sendResponse([
            'success' => false,
            'message' => 'Invalid or expired reset token'
        ], 400);
    }
    
    // Security: Check if token has expired
    if (empty($user['password_reset_expires']) || strtotime($user['password_reset_expires']) < time()) {
        error_log("Expired reset token attempt for email: $email from IP: $clientIp");
        
        // Clear expired token
        $stmt = $pdo->prepare("
            UPDATE users 
            SET password_reset_token = NULL, 
                password_reset_expires = NULL
            WHERE id = ?
        ");
        $stmt->execute([$user['id']]);
        
        sendResponse([
            'success' => false,
            'message' => 'Reset token has expired. Please request a new one.'
        ], 400);
    }

    $resolvedEmail = $user['email'];
    error_log("Password reset attempt for user ID: {$user['id']}, email: $resolvedEmail from IP: $clientIp");
    
    // Security: Hash new password with strong algorithm
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    if ($passwordHash === false) {
        error_log("Password hashing failed for user ID: {$user['id']}");
        sendResponse([
            'success' => false,
            'message' => 'An error occurred. Please try again later.'
        ], 500);
    }
    
    $stmt = $pdo->prepare("
        UPDATE users 
        SET password_hash = ?,
            updated_at = NOW()
        WHERE id = ? AND password_reset_token = ?
    ");
    $result = $stmt->execute([$passwordHash, $user['id'], $token]);
    
    // Security: Verify update was successful (token might have been used concurrently)
    if (!$result || $stmt->rowCount() === 0) {
        error_log("Password reset failed - token may have been used concurrently for user ID: {$user['id']}");
        sendResponse([
            'success' => false,
            'message' => 'This reset link has already been used or is invalid. Please request a new one.'
        ], 400);
    }
    
    // Security: Log successful password reset
    error_log("Password successfully reset for user ID: {$user['id']}, email: $resolvedEmail from IP: $clientIp");
    
    // Send confirmation email
    try {
        if (class_exists('EmailService')) {
            $emailService = new EmailService();
            $userName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            $emailSent = $emailService->sendPasswordResetConfirmationEmail($resolvedEmail, $userName);
            
            if ($emailSent) {
                error_log("Password reset confirmation email sent successfully to: $resolvedEmail (User ID: {$user['id']})");
            } else {
                error_log("Failed to send password reset confirmation email to: $resolvedEmail (User ID: {$user['id']})");
            }
        } else {
            error_log("EmailService class not found - Confirmation email not sent to: $resolvedEmail");
        }
    } catch (Exception $e) {
        error_log("Email Service Error for confirmation email to $resolvedEmail: " . $e->getMessage());
        // Don't fail the reset if email fails - password was already changed
    } catch (Error $e) {
        error_log("Email Service Fatal Error for confirmation email to $resolvedEmail: " . $e->getMessage());
        // Don't fail the reset if email fails
    }
    
    sendResponse([
        'success' => true,
        'message' => 'Password has been reset successfully. You can now log in with your new password.'
    ]);
    
} catch (PDOException $e) {
    error_log("Reset Password Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'An error occurred. Please try again later.'
    ], 500);
} catch (Exception $e) {
    error_log("Reset Password Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'An error occurred. Please try again later.'
    ], 500);
}
