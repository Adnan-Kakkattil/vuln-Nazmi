<?php
/**
 * Test Forgot Password Email Service
 * This script tests the EmailService for forgot password functionality
 * Run this directly: php test-forgot-password-email.php
 * Or access via browser: http://yourdomain.com/test-forgot-password-email.php
 */

// Increase execution time for testing
set_time_limit(60);

// Enable error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/email/EmailService.php';

// Check if running from command line or browser
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Test Forgot Password Email</title>';
    echo '<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}';
    echo '.success{color:green;background:#d4edda;padding:10px;border-radius:5px;margin:10px 0;}';
    echo '.error{color:red;background:#f8d7da;padding:10px;border-radius:5px;margin:10px 0;}';
    echo '.info{color:blue;background:#d1ecf1;padding:10px;border-radius:5px;margin:10px 0;}';
    echo 'pre{background:#fff;padding:10px;border-radius:5px;overflow:auto;}</style></head><body>';
    echo '<h1>Test Forgot Password Email Service</h1>';
}

function output($message, $type = 'info') {
    global $isCLI;
    if ($isCLI) {
        echo $message . "\n";
    } else {
        $class = $type === 'error' ? 'error' : ($type === 'success' ? 'success' : 'info');
        echo "<div class='$class'>" . htmlspecialchars($message) . "</div>";
    }
}

output("=== Testing Forgot Password Email Service ===\n", 'info');

// Get test email from command line argument or form
$testEmail = '';
if ($isCLI) {
    $testEmail = $argv[1] ?? 'mail@arzhizeon.com';
} else {
    $testEmail = $_GET['email'] ?? 'mail@arzhizeon.com';
}

if (!$isCLI) {
    echo '<form method="GET" style="margin:20px 0;">';
    echo '<label>Test Email Address: <input type="email" name="email" value="' . htmlspecialchars($testEmail) . '" required></label> ';
    echo '<button type="submit">Send Test Email</button>';
    echo '</form>';
}

output("Test Email: $testEmail", 'info');
output("", 'info');

try {
    // Initialize EmailService
    output("Initializing EmailService...", 'info');
    $emailService = new EmailService();
    output("EmailService initialized successfully.", 'success');
    
    // Generate a test reset token
    $resetToken = bin2hex(random_bytes(32));
    $userName = 'Test User';
    
    output("Generated reset token: " . substr($resetToken, 0, 20) . "...", 'info');
    output("", 'info');
    
    // Send forgot password email
    output("Sending forgot password email...", 'info');
    $startTime = microtime(true);
    
    $result = $emailService->sendForgotPasswordEmail($testEmail, $resetToken, $userName);
    
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    
    if ($result) {
        output("✓ SUCCESS! Email sent successfully!", 'success');
        output("  Duration: {$duration} seconds", 'info');
        output("  Sent to: {$testEmail}", 'info');
        output("", 'info');
        output("Please check your inbox (and spam folder) for the password reset email.", 'info');
        output("The email should contain a reset link with the token.", 'info');
    } else {
        output("✗ FAILED! Could not send email.", 'error');
        output("  Please check the error logs for more details.", 'error');
        output("  Common issues:", 'error');
        output("  1. SMTP credentials are incorrect", 'error');
        output("  2. Port 465 requires SSL encryption", 'error');
        output("  3. Firewall blocking SMTP connections", 'error');
        output("  4. Email account is not active", 'error');
    }
    
} catch (Exception $e) {
    output("✗ EXCEPTION: " . $e->getMessage(), 'error');
    output("  File: " . $e->getFile() . ":" . $e->getLine(), 'error');
    if (!$isCLI) {
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
} catch (Error $e) {
    output("✗ FATAL ERROR: " . $e->getMessage(), 'error');
    output("  File: " . $e->getFile() . ":" . $e->getLine(), 'error');
    if (!$isCLI) {
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
}

output("", 'info');
output("=== Test Complete ===", 'info');

if (!$isCLI) {
    echo '</body></html>';
}
