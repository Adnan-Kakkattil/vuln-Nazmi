<?php
/**
 * Test Email API
 * Endpoint: /api/v1/admin/test-email.php
 * Method: POST
 * Tests email configuration and sends a test email
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/email/EmailService.php';

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

$testEmail = trim($input['email'] ?? $admin['email'] ?? '');

// Validation
if (empty($testEmail)) {
    sendResponse([
        'success' => false,
        'message' => 'Email address is required'
    ], 400);
}

if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
    sendResponse([
        'success' => false,
        'message' => 'Invalid email address'
    ], 400);
}

try {
    // Test email service
    $emailService = new EmailService();
    
    // Create test email content
    $subject = 'Test Email - BLine Boutique';
    $body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); padding: 30px; text-align: center; color: white; }
            .content { padding: 30px; background: #f9fafb; }
            .success { background: #d1fae5; border-left: 4px solid #059669; padding: 15px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>BLine<span style="color: #ffffff;">.</span></h1>
            </div>
            <div class="content">
                <h2>Email Test Successful!</h2>
                <div class="success">
                    <p><strong>✓ SMTP Configuration Test Passed</strong></p>
                    <p>This is a test email from your BLine Boutique email service.</p>
                    <p>If you received this email, your SMTP settings are configured correctly.</p>
                </div>
                <p><strong>Test Details:</strong></p>
                <ul>
                    <li>Test Time: ' . date('Y-m-d H:i:s') . '</li>
                    <li>Recipient: ' . htmlspecialchars($testEmail) . '</li>
                    <li>SMTP Status: Connected Successfully</li>
                </ul>
                <p>You can now use the email service for password resets, order confirmations, and other notifications.</p>
            </div>
        </div>
    </body>
    </html>';
    
    $altBody = "Email Test Successful!\n\n";
    $altBody .= "This is a test email from your BLine Boutique email service.\n\n";
    $altBody .= "Test Time: " . date('Y-m-d H:i:s') . "\n";
    $altBody .= "Recipient: " . $testEmail . "\n";
    $altBody .= "SMTP Status: Connected Successfully\n\n";
    $altBody .= "You can now use the email service for password resets, order confirmations, and other notifications.";
    
    // Send test email
    $emailSent = $emailService->send($testEmail, $subject, $body, $altBody);
    
    if ($emailSent) {
        sendResponse([
            'success' => true,
            'message' => 'Test email sent successfully! Please check your inbox.',
            'data' => [
                'email' => $testEmail,
                'sent_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        sendResponse([
            'success' => false,
            'message' => 'Failed to send test email. Please check your SMTP settings and try again.',
            'error' => 'Email send failed'
        ], 500);
    }
    
} catch (Exception $e) {
    error_log("Test Email Error: " . $e->getMessage());
    error_log("Test Email Error Trace: " . $e->getTraceAsString());
    sendResponse([
        'success' => false,
        'message' => 'Error sending test email: ' . $e->getMessage(),
        'error' => $e->getMessage()
    ], 500);
} catch (Error $e) {
    error_log("Test Email Fatal Error: " . $e->getMessage());
    error_log("Test Email Fatal Error Trace: " . $e->getTraceAsString());
    sendResponse([
        'success' => false,
        'message' => 'Fatal error: ' . $e->getMessage(),
        'error' => $e->getMessage()
    ], 500);
}
