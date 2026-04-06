<?php
/**
 * Email Service using PHPMailer
 * Handles all email sending functionality
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$__blineAutoload = __DIR__ . '/../../vendor/autoload.php';
if (is_file($__blineAutoload)) {
    require_once $__blineAutoload;
}
require_once __DIR__ . '/../config.php';

class EmailService {
    private $mailer;
    private $settings;
    
    public function __construct() {
        try {
            if (!class_exists(PHPMailer::class)) {
                throw new \RuntimeException('PHPMailer not available (run composer install)');
            }
            $this->mailer = new PHPMailer(true);
            $this->loadSettings();
            $this->configureMailer();
        } catch (\Throwable $e) {
            error_log("EmailService Constructor Error: " . $e->getMessage());
            $this->settings = [];
        }
    }
    
    /**
     * Load email settings from database
     */
    private function loadSettings() {
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("
                SELECT setting_key, setting_value 
                FROM system_settings 
                WHERE category = 'email'
            ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->settings = [];
            foreach ($results as $row) {
                $this->settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (PDOException $e) {
            error_log("Email Service: Failed to load settings - " . $e->getMessage());
            $this->settings = [];
        }
    }
    
    /**
     * Configure PHPMailer with SMTP settings
     * Best practices: Port 465 uses SSL, Port 587 uses TLS
     */
    private function configureMailer() {
        try {
            // Check if email service is enabled
            if (empty($this->settings['email_service_enabled']) || $this->settings['email_service_enabled'] != '1') {
                error_log("Email Service: Service is disabled in settings");
                return; // Don't throw, just return
            }
            
            // SMTP Configuration with Hostinger defaults
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->settings['smtp_host'] ?? 'smtp.hostinger.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->settings['smtp_username'] ?? 'mail@archizeon.com';
            $this->mailer->Password = $this->settings['smtp_password'] ?? 'Adnan@66202';
            
            // Get port and determine encryption type
            // Port 465 requires SSL (SMTPS), Port 587 uses TLS (STARTTLS)
            $port = intval($this->settings['smtp_port'] ?? 465);
            $encryption = strtolower($this->settings['smtp_encryption'] ?? '');
            
            // Auto-detect encryption based on port if not explicitly set
            if (empty($encryption)) {
                if ($port == 465) {
                    $encryption = 'ssl';
                } else {
                    $encryption = 'tls';
                }
            }
            
            // Set encryption based on type
            if ($encryption === 'ssl') {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL for port 465
            } else {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS for port 587
            }
            
            $this->mailer->Port = $port;
            
            // Set timeout values to prevent hanging
            $this->mailer->Timeout = 30; // Connection timeout in seconds
            $this->mailer->SMTPKeepAlive = false; // Don't keep connection alive
            
            // Additional SMTP options for better reliability
            // Note: verify_peer set to false for compatibility, but in production consider enabling
            $this->mailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => false
                ]
            ];
            
            // From address - use provided email
            $this->mailer->setFrom(
                $this->settings['email_from_address'] ?? 'mail@archizeon.com',
                $this->settings['email_from_name'] ?? 'BLine Boutique'
            );
            
            // Character set for proper encoding
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->Encoding = 'base64';
            
            // Enable verbose debug output (disable in production)
            // Set to 0 for production, 2 for debugging
            $this->mailer->SMTPDebug = 0; // 0 = off, 2 = verbose
            $this->mailer->Debugoutput = function($str, $level) {
                error_log("PHPMailer Debug [$level]: $str");
            };
            
        } catch (Exception $e) {
            error_log("Email Service Configuration Error: " . $e->getMessage());
            // Don't throw - allow service to be created but mark as unavailable
            $this->settings = [];
        }
    }
    
    /**
     * Send email
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body HTML body
     * @param string $altBody Plain text alternative
     * @param array $attachments Optional attachments
     * @return bool Success status
     */
    public function send($to, $subject, $body, $altBody = '', $attachments = []) {
        // Check if mailer is properly configured
        if (!$this->mailer || empty($this->settings['email_service_enabled']) || $this->settings['email_service_enabled'] != '1') {
            error_log("Email Service: Cannot send email - service not configured");
            return false;
        }
        
        try {
            // Reset mailer for new email
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            $this->mailer->clearReplyTos();
            $this->mailer->clearCCs();
            $this->mailer->clearBCCs();
            
            // Set recipient
            $this->mailer->addAddress($to);
            
            // Set content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = $altBody ?: strip_tags($body);
            
            // Add attachments if any
            foreach ($attachments as $attachment) {
                if (isset($attachment['path'])) {
                    $this->mailer->addAttachment(
                        $attachment['path'],
                        $attachment['name'] ?? basename($attachment['path'])
                    );
                }
            }
            
            // Set execution time limit for email sending
            $maxExecutionTime = ini_get('max_execution_time');
            set_time_limit(30); // Set to 30 seconds max for email sending
            
            // Send email
            $result = $this->mailer->send();
            
            // Restore original execution time
            set_time_limit($maxExecutionTime);
            
            if ($result) {
                error_log("Email sent successfully to: $to");
            } else {
                error_log("Email send failed to: $to - " . $this->mailer->ErrorInfo);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Email Send Exception: " . $e->getMessage());
            error_log("PHPMailer Error Info: " . ($this->mailer->ErrorInfo ?? 'No error info'));
            return false;
        } catch (Error $e) {
            error_log("Email Send Fatal Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get base URL from current request (auto-detect production domain)
     * Always uses HTTP_HOST from request to ensure production domain is used
     * @return string Base URL
     */
    private function getBaseUrl() {
        // Always prioritize HTTP_HOST from current request (most reliable for production)
        if (!empty($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
            
            // Skip localhost, 127.0.0.1, and ::1
            if (strpos($host, 'localhost') === false && 
                strpos($host, '127.0.0.1') === false && 
                strpos($host, '::1') === false) {
                
                // Determine protocol
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                           (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
                
                // Remove standard ports
                $host = preg_replace('/:80$/', '', $host);
                $host = preg_replace('/:443$/', '', $host);
                
                return $protocol . '://' . $host;
            }
        }
        
        // Fallback: Check APP_URL if it's not localhost
        if (defined('APP_URL') && !empty(APP_URL)) {
            $appUrl = APP_URL;
            if (strpos($appUrl, 'localhost') === false && 
                strpos($appUrl, '127.0.0.1') === false && 
                strpos($appUrl, '::1') === false) {
                return rtrim($appUrl, '/');
            }
        }
        
        // Last resort: Use SERVER_NAME
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                   (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
        $host = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $host = preg_replace('/:80$/', '', $host);
        $host = preg_replace('/:443$/', '', $host);
        
        return $protocol . '://' . $host;
    }
    
    /**
     * Send forgot password email
     * @param string $to Recipient email
     * @param string $resetToken Reset token
     * @param string $userName User's name
     * @return bool Success status
     */
    public function sendForgotPasswordEmail($to, $resetToken, $userName = '') {
        // Get base URL - auto-detect from current request to ensure production domain
        $baseUrl = $this->getBaseUrl();
        
        // Ensure URL doesn't end with slash
        $baseUrl = rtrim($baseUrl, '/');
        
        $resetLink = $baseUrl . '/reset-password.php?token=' . urlencode($resetToken) . '&email=' . urlencode($to);
        $expiryTime = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $subject = 'Reset Your Password - BLine Boutique';
        
        $body = $this->getForgotPasswordTemplate($userName, $resetLink, $expiryTime);
        $altBody = "Hello " . ($userName ?: 'there') . ",\n\n";
        $altBody .= "You requested to reset your password. Click the link below to reset it:\n\n";
        $altBody .= $resetLink . "\n\n";
        $altBody .= "This link will expire in 1 hour.\n\n";
        $altBody .= "If you didn't request this, please ignore this email.\n\n";
        $altBody .= "Best regards,\nBLine Boutique";
        
        return $this->send($to, $subject, $body, $altBody);
    }
    
    /**
     * Send password reset confirmation email
     * @param string $to Recipient email
     * @param string $userName User's name
     * @return bool Success status
     */
    public function sendPasswordResetConfirmationEmail($to, $userName = '') {
        $subject = 'Password Reset Successful - BLine Boutique';
        
        $body = $this->getPasswordResetConfirmationTemplate($userName);
        $altBody = "Hello " . ($userName ?: 'there') . ",\n\n";
        $altBody .= "Your password has been successfully reset.\n\n";
        $altBody .= "If you didn't make this change, please contact us immediately.\n\n";
        $altBody .= "Best regards,\nBLine Boutique";
        
        return $this->send($to, $subject, $body, $altBody);
    }
    
    /**
     * Get forgot password email template
     */
    private function getForgotPasswordTemplate($userName, $resetLink, $expiryTime) {
        $name = $userName ?: 'there';
        
        return '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .email-header h1 {
            color: #ffffff;
            font-family: "Playfair Display", serif;
            font-size: 32px;
            font-weight: 900;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .email-body {
            padding: 40px 30px;
        }
        .email-content {
            color: #4b5563;
            font-size: 16px;
            line-height: 1.8;
        }
        .email-content h2 {
            color: #111827;
            font-family: "Playfair Display", serif;
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 20px 0;
        }
        .email-content p {
            margin: 0 0 20px 0;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .reset-button {
            display: inline-block;
            background-color: #111827;
            color: #ffffff !important;
            padding: 16px 32px;
            text-decoration: none;
            border-radius: 16px;
            font-weight: 800;
            font-size: 16px;
            letter-spacing: 0.5px;
            transition: background-color 0.3s;
        }
        .reset-button:hover {
            background-color: #14b8a6;
        }
        .reset-link {
            background-color: #f1f5f9;
            padding: 15px;
            border-radius: 8px;
            word-break: break-all;
            font-size: 14px;
            color: #64748b;
            margin: 20px 0;
        }
        .email-footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .email-footer p {
            color: #6b7280;
            font-size: 14px;
            margin: 5px 0;
        }
        .expiry-notice {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .expiry-notice p {
            margin: 0;
            color: #92400e;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>BLine<span style="color: #ffffff;">.</span></h1>
        </div>
        
        <div class="email-body">
            <div class="email-content">
                <h2>Reset Your Password</h2>
                
                <p>Hello ' . htmlspecialchars($name) . ',</p>
                
                <p>We received a request to reset your password for your BLine Boutique account. Click the button below to create a new password:</p>
                
                <div class="button-container">
                    <a href="' . htmlspecialchars($resetLink) . '" class="reset-button">RESET PASSWORD</a>
                </div>
                
                <p>Or copy and paste this link into your browser:</p>
                <div class="reset-link">' . htmlspecialchars($resetLink) . '</div>
                
                <div class="expiry-notice">
                    <p><strong>⏰ Important:</strong> This link will expire in 1 hour (by ' . htmlspecialchars($expiryTime) . ').</p>
                </div>
                
                <p>If you didn\'t request a password reset, please ignore this email. Your password will remain unchanged.</p>
                
                <p>For security reasons, if you didn\'t make this request, we recommend checking your account settings.</p>
            </div>
        </div>
        
        <div class="email-footer">
            <p><strong>BLine Boutique</strong></p>
            <p>Your trusted fashion destination</p>
            <p style="margin-top: 15px; font-size: 12px; color: #9ca3af;">
                This is an automated email. Please do not reply to this message.
            </p>
        </div>
    </div>
</body>
</html>';
    }
    
    /**
     * Get password reset confirmation email template
     */
    private function getPasswordResetConfirmationTemplate($userName) {
        $name = $userName ?: 'there';
        
        return '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Successful</title>
    <style>
        body {
            font-family: "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .email-header h1 {
            color: #ffffff;
            font-family: "Playfair Display", serif;
            font-size: 32px;
            font-weight: 900;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .email-body {
            padding: 40px 30px;
        }
        .email-content {
            color: #4b5563;
            font-size: 16px;
            line-height: 1.8;
        }
        .email-content h2 {
            color: #111827;
            font-family: "Playfair Display", serif;
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 20px 0;
        }
        .email-content p {
            margin: 0 0 20px 0;
        }
        .success-icon {
            text-align: center;
            margin: 30px 0;
        }
        .success-icon div {
            width: 80px;
            height: 80px;
            background-color: #d1fae5;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .success-icon svg {
            width: 40px;
            height: 40px;
            color: #059669;
        }
        .email-footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .email-footer p {
            color: #6b7280;
            font-size: 14px;
            margin: 5px 0;
        }
        .security-notice {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .security-notice p {
            margin: 0;
            color: #991b1b;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>BLine<span style="color: #ffffff;">.</span></h1>
        </div>
        
        <div class="email-body">
            <div class="email-content">
                <h2>Password Reset Successful</h2>
                
                <div class="success-icon">
                    <div>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                
                <p>Hello ' . htmlspecialchars($name) . ',</p>
                
                <p>Your password has been successfully reset. You can now log in to your BLine Boutique account using your new password.</p>
                
                <div class="security-notice">
                    <p><strong>🔒 Security Notice:</strong> If you didn\'t make this change, please contact us immediately to secure your account.</p>
                </div>
                
                <p>Thank you for keeping your account secure!</p>
            </div>
        </div>
        
        <div class="email-footer">
            <p><strong>BLine Boutique</strong></p>
            <p>Your trusted fashion destination</p>
            <p style="margin-top: 15px; font-size: 12px; color: #9ca3af;">
                This is an automated email. Please do not reply to this message.
            </p>
        </div>
    </div>
</body>
</html>';
    }
    
    /**
     * Send order confirmation email to customer
     * @param array $orderData Order data from database
     * @param array $orderItems Order items array
     * @return bool Success status
     */
    public function sendOrderConfirmationEmail($orderData, $orderItems = []) {
        $customerEmail = $orderData['guest_email'] ?? $orderData['email'] ?? '';
        $customerName = $orderData['guest_name'] ?? $orderData['customer_name'] ?? 'Customer';
        
        if (empty($customerEmail)) {
            error_log("Order confirmation email: No customer email found for order {$orderData['order_number']}");
            return false;
        }
        
        $subject = 'Order Confirmation - ' . $orderData['order_number'] . ' | BLine Boutique';
        $body = $this->getOrderConfirmationTemplate($orderData, $orderItems, $customerName);
        $altBody = $this->getOrderConfirmationPlainText($orderData, $orderItems, $customerName);
        
        return $this->send($customerEmail, $subject, $body, $altBody);
    }
    
    /**
     * Send order notification email to admin
     * @param array $orderData Order data from database
     * @param array $orderItems Order items array
     * @param array $recipients Array of admin email addresses
     * @return bool Success status
     */
    public function sendOrderNotificationToAdmin($orderData, $orderItems = [], $recipients = []) {
        if (empty($recipients)) {
            $recipients = ['info@blineboutique.com', 'contact.adnanks@gmail.com'];
        }
        
        $subject = 'New Order Received - ' . $orderData['order_number'] . ' | BLine Boutique';
        $body = $this->getOrderAdminNotificationTemplate($orderData, $orderItems);
        $altBody = $this->getOrderAdminNotificationPlainText($orderData, $orderItems);
        
        $allSent = true;
        foreach ($recipients as $email) {
            if (!empty($email)) {
                $sent = $this->send($email, $subject, $body, $altBody);
                if (!$sent) {
                    $allSent = false;
                    error_log("Failed to send order notification to admin: $email");
                }
            }
        }
        
        return $allSent;
    }
    
    /**
     * Send order tracking/status update email
     * @param array $orderData Order data
     * @param string $status New order status
     * @param string $trackingNumber Optional tracking number
     * @return bool Success status
     */
    public function sendOrderTrackingEmail($orderData, $status, $trackingNumber = '') {
        $customerEmail = $orderData['guest_email'] ?? $orderData['email'] ?? '';
        $customerName = $orderData['guest_name'] ?? $orderData['customer_name'] ?? 'Customer';
        
        if (empty($customerEmail)) {
            return false;
        }
        
        $subject = 'Order Update - ' . $orderData['order_number'] . ' | BLine Boutique';
        $body = $this->getOrderTrackingTemplate($orderData, $status, $trackingNumber, $customerName);
        $altBody = $this->getOrderTrackingPlainText($orderData, $status, $trackingNumber, $customerName);
        
        return $this->send($customerEmail, $subject, $body, $altBody);
    }
    
    /**
     * Send order cancellation email
     * @param array $orderData Order data
     * @param string $reason Cancellation reason
     * @return bool Success status
     */
    public function sendOrderCancellationEmail($orderData, $reason = '') {
        $customerEmail = $orderData['guest_email'] ?? $orderData['email'] ?? '';
        $customerName = $orderData['guest_name'] ?? $orderData['customer_name'] ?? 'Customer';
        
        if (empty($customerEmail)) {
            return false;
        }
        
        $subject = 'Order Cancelled - ' . $orderData['order_number'] . ' | BLine Boutique';
        $body = $this->getOrderCancellationTemplate($orderData, $reason, $customerName);
        $altBody = $this->getOrderCancellationPlainText($orderData, $reason, $customerName);
        
        return $this->send($customerEmail, $subject, $body, $altBody);
    }
    
    /**
     * Send order refund email
     * @param array $orderData Order data
     * @param float $refundAmount Refund amount
     * @param string $refundReason Refund reason
     * @return bool Success status
     */
    public function sendOrderRefundEmail($orderData, $refundAmount, $refundReason = '') {
        $customerEmail = $orderData['guest_email'] ?? $orderData['email'] ?? '';
        $customerName = $orderData['guest_name'] ?? $orderData['customer_name'] ?? 'Customer';
        
        if (empty($customerEmail)) {
            return false;
        }
        
        $subject = 'Refund Processed - ' . $orderData['order_number'] . ' | BLine Boutique';
        $body = $this->getOrderRefundTemplate($orderData, $refundAmount, $refundReason, $customerName);
        $altBody = $this->getOrderRefundPlainText($orderData, $refundAmount, $refundReason, $customerName);
        
        return $this->send($customerEmail, $subject, $body, $altBody);
    }
    
    /**
     * Send refund confirmation email
     * @param array $orderData Order data
     * @param float $refundAmount Refund amount
     * @return bool Success status
     */
    public function sendRefundConfirmationEmail($orderData, $refundAmount) {
        $customerEmail = $orderData['guest_email'] ?? $orderData['email'] ?? '';
        $customerName = $orderData['guest_name'] ?? $orderData['customer_name'] ?? 'Customer';
        
        if (empty($customerEmail)) {
            return false;
        }
        
        $subject = 'Refund Confirmed - ' . $orderData['order_number'] . ' | BLine Boutique';
        $body = $this->getRefundConfirmationTemplate($orderData, $refundAmount, $customerName);
        $altBody = $this->getRefundConfirmationPlainText($orderData, $refundAmount, $customerName);
        
        return $this->send($customerEmail, $subject, $body, $altBody);
    }
    
    /**
     * Send refund rejection email
     * @param array $orderData Order data
     * @param string $rejectionReason Rejection reason
     * @return bool Success status
     */
    public function sendRefundRejectionEmail($orderData, $rejectionReason = '') {
        $customerEmail = $orderData['guest_email'] ?? $orderData['email'] ?? '';
        $customerName = $orderData['guest_name'] ?? $orderData['customer_name'] ?? 'Customer';
        
        if (empty($customerEmail)) {
            return false;
        }
        
        $subject = 'Refund Request Update - ' . $orderData['order_number'] . ' | BLine Boutique';
        $body = $this->getRefundRejectionTemplate($orderData, $rejectionReason, $customerName);
        $altBody = $this->getRefundRejectionPlainText($orderData, $rejectionReason, $customerName);
        
        return $this->send($customerEmail, $subject, $body, $altBody);
    }
    
    /**
     * Get order confirmation email template (HTML)
     */
    private function getOrderConfirmationTemplate($orderData, $orderItems, $customerName) {
        $orderNumber = $orderData['order_number'] ?? 'N/A';
        $orderDate = isset($orderData['order_date']) ? date('F d, Y', strtotime($orderData['order_date'])) : date('F d, Y');
        $orderTime = isset($orderData['order_date']) ? date('h:i A', strtotime($orderData['order_date'])) : date('h:i A');
        
        $subtotal = number_format($orderData['subtotal'] ?? 0, 2);
        $taxAmount = number_format($orderData['tax_amount'] ?? 0, 2);
        $shippingCost = number_format($orderData['shipping_cost'] ?? 0, 2);
        $discountAmount = number_format($orderData['discount_amount'] ?? 0, 2);
        $totalAmount = number_format($orderData['total_amount'] ?? 0, 2);
        
        $paymentMethod = ucfirst($orderData['payment_method'] ?? 'N/A');
        $paymentStatus = ucfirst($orderData['payment_status'] ?? 'pending');
        
        // Shipping address
        $shippingAddress = $this->formatAddress($orderData, 'shipping');
        
        // Order items HTML
        $itemsHtml = '';
        if (!empty($orderItems)) {
            foreach ($orderItems as $item) {
                $itemName = htmlspecialchars($item['product_name'] ?? $item['name'] ?? 'Product');
                $itemSku = htmlspecialchars($item['product_sku'] ?? $item['sku'] ?? '');
                $itemQty = $item['quantity'] ?? 1;
                $itemPrice = number_format($item['unit_price'] ?? $item['price'] ?? 0, 2);
                $itemTotal = number_format(($item['unit_price'] ?? $item['price'] ?? 0) * $itemQty, 2);
                
                $itemsHtml .= '
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">
                        <div style="font-weight: 600; color: #111827;">' . $itemName . '</div>
                        ' . ($itemSku ? '<div style="font-size: 12px; color: #6b7280; margin-top: 4px;">SKU: ' . $itemSku . '</div>' : '') . '
                    </td>
                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: center; color: #4b5563;">' . $itemQty . '</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: right; color: #111827; font-weight: 600;">₹' . $itemPrice . '</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: right; color: #111827; font-weight: 600;">₹' . $itemTotal . '</td>
                </tr>';
            }
        }
        
        return '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .email-header h1 {
            color: #ffffff;
            font-family: "Playfair Display", serif;
            font-size: 32px;
            font-weight: 900;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .email-body {
            padding: 40px 30px;
        }
        .email-content {
            color: #4b5563;
            font-size: 16px;
            line-height: 1.8;
        }
        .email-content h2 {
            color: #111827;
            font-family: "Playfair Display", serif;
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 20px 0;
        }
        .order-info-box {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
        }
        .order-info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .order-info-row:last-child {
            border-bottom: none;
        }
        .order-info-label {
            color: #6b7280;
            font-weight: 600;
            font-size: 14px;
        }
        .order-info-value {
            color: #111827;
            font-weight: 600;
            font-size: 14px;
        }
        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        .order-items-table th {
            background: #f9fafb;
            padding: 12px;
            text-align: left;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }
        .order-items-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .totals-section {
            background: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }
        .total-row.final {
            border-top: 2px solid #e5e7eb;
            margin-top: 8px;
            padding-top: 12px;
            font-size: 18px;
            font-weight: 700;
            color: #14b8a6;
        }
        .address-box {
            background: #f9fafb;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        .address-box strong {
            color: #111827;
            font-size: 14px;
            display: block;
            margin-bottom: 8px;
        }
        .address-box p {
            margin: 4px 0;
            color: #4b5563;
            font-size: 14px;
            line-height: 1.6;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .email-button {
            display: inline-block;
            background-color: #111827;
            color: #ffffff !important;
            padding: 16px 32px;
            text-decoration: none;
            border-radius: 16px;
            font-weight: 800;
            font-size: 16px;
            letter-spacing: 0.5px;
            transition: background-color 0.3s;
        }
        .email-button:hover {
            background-color: #14b8a6;
        }
        .email-footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .email-footer p {
            color: #6b7280;
            font-size: 14px;
            margin: 5px 0;
        }
        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 20px 15px;
            }
            .order-items-table {
                font-size: 12px;
            }
            .order-items-table th,
            .order-items-table td {
                padding: 8px 4px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>BLine<span style="color: #ffffff;">.</span></h1>
        </div>
        
        <div class="email-body">
            <div class="email-content">
                <h2>Order Confirmed!</h2>
                
                <p>Hello ' . htmlspecialchars($customerName) . ',</p>
                
                <p>Thank you for your order! We\'re excited to prepare your items for shipment.</p>
                
                <div class="order-info-box">
                    <div class="order-info-row">
                        <span class="order-info-label">Order Number</span>
                        <span class="order-info-value">' . htmlspecialchars($orderNumber) . '</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">Order Date</span>
                        <span class="order-info-value">' . htmlspecialchars($orderDate) . ' at ' . htmlspecialchars($orderTime) . '</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">Payment Method</span>
                        <span class="order-info-value">' . htmlspecialchars($paymentMethod) . '</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">Payment Status</span>
                        <span class="order-info-value">' . htmlspecialchars($paymentStatus) . '</span>
                    </div>
                </div>
                
                <h3 style="color: #111827; font-size: 20px; font-weight: 700; margin: 30px 0 15px 0;">Order Items</h3>
                
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Price</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . $itemsHtml . '
                    </tbody>
                </table>
                
                <div class="totals-section">
                    <div class="total-row">
                        <span>Subtotal</span>
                        <span>₹' . $subtotal . '</span>
                    </div>
                    ' . ($discountAmount > 0 ? '<div class="total-row"><span>Discount</span><span>-₹' . $discountAmount . '</span></div>' : '') . '
                    <div class="total-row">
                        <span>Shipping</span>
                        <span>₹' . $shippingCost . '</span>
                    </div>
                    <div class="total-row">
                        <span>Tax (GST)</span>
                        <span>₹' . $taxAmount . '</span>
                    </div>
                    <div class="total-row final">
                        <span>Total Amount</span>
                        <span>₹' . $totalAmount . '</span>
                    </div>
                </div>
                
                <h3 style="color: #111827; font-size: 20px; font-weight: 700; margin: 30px 0 15px 0;">Shipping Address</h3>
                <div class="address-box">
                    ' . $shippingAddress . '
                </div>
                
                <div class="button-container">
                    <a href="' . $this->getBaseUrl() . '/order-overview.php?order=' . urlencode($orderNumber) . '" class="email-button">VIEW ORDER DETAILS</a>
                </div>
                
                <p style="margin-top: 30px;">We\'ll send you another email once your order ships. If you have any questions, please don\'t hesitate to contact us.</p>
            </div>
        </div>
        
        <div class="email-footer">
            <p><strong>BLine Boutique</strong></p>
            <p>Your trusted fashion destination</p>
            <p style="margin-top: 15px; font-size: 12px; color: #9ca3af;">
                This is an automated email. Please do not reply to this message.
            </p>
        </div>
    </div>
</body>
</html>';
    }
    
    /**
     * Format address for email display
     */
    private function formatAddress($orderData, $type = 'shipping') {
        $prefix = $type === 'shipping' ? 'shipping_' : 'billing_';
        $firstName = $orderData[$prefix . 'first_name'] ?? '';
        $lastName = $orderData[$prefix . 'last_name'] ?? '';
        $name = trim($firstName . ' ' . $lastName);
        $line1 = $orderData[$prefix . 'address_line1'] ?? '';
        $line2 = $orderData[$prefix . 'address_line2'] ?? '';
        $city = $orderData[$prefix . 'city'] ?? '';
        $state = $orderData[$prefix . 'state'] ?? '';
        $pincode = $orderData[$prefix . 'pincode'] ?? '';
        $country = $orderData[$prefix . 'country'] ?? 'India';
        $phone = $orderData[$prefix . 'phone'] ?? $orderData['shipping_phone'] ?? '';
        
        $address = '<strong>' . htmlspecialchars($name) . '</strong>';
        if ($line1) $address .= '<p>' . htmlspecialchars($line1) . '</p>';
        if ($line2) $address .= '<p>' . htmlspecialchars($line2) . '</p>';
        $address .= '<p>' . htmlspecialchars($city) . ', ' . htmlspecialchars($state) . ' ' . htmlspecialchars($pincode) . '</p>';
        $address .= '<p>' . htmlspecialchars($country) . '</p>';
        if ($phone) $address .= '<p style="margin-top: 8px;">Phone: ' . htmlspecialchars($phone) . '</p>';
        
        return $address;
    }
    
    /**
     * Get order confirmation plain text
     */
    private function getOrderConfirmationPlainText($orderData, $orderItems, $customerName) {
        $orderNumber = $orderData['order_number'] ?? 'N/A';
        $totalAmount = number_format($orderData['total_amount'] ?? 0, 2);
        
        $text = "Hello $customerName,\n\n";
        $text .= "Thank you for your order!\n\n";
        $text .= "Order Number: $orderNumber\n";
        $text .= "Total Amount: ₹$totalAmount\n\n";
        $text .= "We'll send you another email once your order ships.\n\n";
        $text .= "Best regards,\nBLine Boutique";
        
        return $text;
    }
    
    /**
     * Get order admin notification template
     */
    private function getOrderAdminNotificationTemplate($orderData, $orderItems) {
        $orderNumber = $orderData['order_number'] ?? 'N/A';
        $orderDate = isset($orderData['order_date']) ? date('F d, Y h:i A', strtotime($orderData['order_date'])) : date('F d, Y h:i A');
        
        $subtotal = number_format($orderData['subtotal'] ?? 0, 2);
        $taxAmount = number_format($orderData['tax_amount'] ?? 0, 2);
        $shippingCost = number_format($orderData['shipping_cost'] ?? 0, 2);
        $discountAmount = number_format($orderData['discount_amount'] ?? 0, 2);
        $totalAmount = number_format($orderData['total_amount'] ?? 0, 2);
        
        $paymentMethod = ucfirst($orderData['payment_method'] ?? 'N/A');
        $paymentStatus = ucfirst($orderData['payment_status'] ?? 'pending');
        $orderStatus = ucfirst($orderData['status'] ?? 'pending');
        
        $customerEmail = $orderData['guest_email'] ?? $orderData['customer_email'] ?? 'N/A';
        $customerName = $orderData['guest_name'] ?? 'Guest Customer';
        $customerPhone = $orderData['shipping_phone'] ?? 'N/A';
        
        // Shipping address
        $shippingAddress = $this->formatAddress($orderData, 'shipping');
        
        // Order items HTML
        $itemsHtml = '';
        if (!empty($orderItems)) {
            foreach ($orderItems as $item) {
                $itemName = htmlspecialchars($item['product_name'] ?? $item['name'] ?? 'Product');
                $itemSku = htmlspecialchars($item['product_sku'] ?? $item['sku'] ?? '');
                $itemQty = $item['quantity'] ?? 1;
                $itemPrice = number_format($item['unit_price'] ?? $item['price'] ?? 0, 2);
                $itemTotal = number_format(($item['unit_price'] ?? $item['price'] ?? 0) * $itemQty, 2);
                
                $itemsHtml .= '
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">
                        <div style="font-weight: 600; color: #111827;">' . $itemName . '</div>
                        ' . ($itemSku ? '<div style="font-size: 12px; color: #6b7280; margin-top: 4px;">SKU: ' . $itemSku . '</div>' : '') . '
                    </td>
                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: center; color: #4b5563;">' . $itemQty . '</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: right; color: #111827; font-weight: 600;">₹' . $itemPrice . '</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: right; color: #111827; font-weight: 600;">₹' . $itemTotal . '</td>
                </tr>';
            }
        }
        
        $baseUrl = $this->getBaseUrl();
        
        return '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order Notification</title>
    ' . $this->getEmailStyles() . '
</head>
<body>
    <div class="email-container">
        <div class="email-header" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
            <h1>BLine<span style="color: #ffffff;">.</span></h1>
        </div>
        
        <div class="email-body">
            <div class="email-content">
                <h2>New Order Received</h2>
                
                <p>A new order has been placed and requires your attention.</p>
                
                <div class="order-info-box" style="background: #eef2ff; border-left: 4px solid #6366f1;">
                    <div class="order-info-row">
                        <span class="order-info-label">Order Number</span>
                        <span class="order-info-value" style="color: #4f46e5; font-size: 18px;">' . htmlspecialchars($orderNumber) . '</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">Order Date</span>
                        <span class="order-info-value">' . htmlspecialchars($orderDate) . '</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">Order Status</span>
                        <span class="order-info-value">' . htmlspecialchars($orderStatus) . '</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">Payment Method</span>
                        <span class="order-info-value">' . htmlspecialchars($paymentMethod) . '</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">Payment Status</span>
                        <span class="order-info-value">' . htmlspecialchars($paymentStatus) . '</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">Total Amount</span>
                        <span class="order-info-value" style="color: #059669; font-size: 20px; font-weight: 700;">₹' . $totalAmount . '</span>
                    </div>
                </div>
                
                <h3 style="color: #111827; font-size: 20px; font-weight: 700; margin: 30px 0 15px 0;">Customer Information</h3>
                <div class="order-info-box">
                    <div class="order-info-row">
                        <span class="order-info-label">Customer Name</span>
                        <span class="order-info-value">' . htmlspecialchars($customerName) . '</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">Email</span>
                        <span class="order-info-value">' . htmlspecialchars($customerEmail) . '</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">Phone</span>
                        <span class="order-info-value">' . htmlspecialchars($customerPhone) . '</span>
                    </div>
                </div>
                
                <h3 style="color: #111827; font-size: 20px; font-weight: 700; margin: 30px 0 15px 0;">Order Items</h3>
                
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Price</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . $itemsHtml . '
                    </tbody>
                </table>
                
                <div class="totals-section">
                    <div class="total-row">
                        <span>Subtotal</span>
                        <span>₹' . $subtotal . '</span>
                    </div>
                    ' . ($discountAmount > 0 ? '<div class="total-row"><span>Discount</span><span>-₹' . $discountAmount . '</span></div>' : '') . '
                    <div class="total-row">
                        <span>Shipping</span>
                        <span>₹' . $shippingCost . '</span>
                    </div>
                    <div class="total-row">
                        <span>Tax (GST)</span>
                        <span>₹' . $taxAmount . '</span>
                    </div>
                    <div class="total-row final">
                        <span>Total Amount</span>
                        <span>₹' . $totalAmount . '</span>
                    </div>
                </div>
                
                <h3 style="color: #111827; font-size: 20px; font-weight: 700; margin: 30px 0 15px 0;">Shipping Address</h3>
                <div class="address-box">
                    ' . $shippingAddress . '
                </div>
                
                <div class="button-container">
                    <a href="' . $baseUrl . '/admin/orders.php?order=' . urlencode($orderNumber) . '" class="email-button" style="background-color: #6366f1;">VIEW ORDER IN ADMIN</a>
                </div>
            </div>
        </div>
        
        ' . $this->getEmailFooter() . '
    </div>
</body>
</html>';
    }
    
    /**
     * Get order admin notification plain text
     */
    private function getOrderAdminNotificationPlainText($orderData, $orderItems) {
        $orderNumber = $orderData['order_number'] ?? 'N/A';
        $totalAmount = number_format($orderData['total_amount'] ?? 0, 2);
        $customerEmail = $orderData['guest_email'] ?? $orderData['customer_email'] ?? 'N/A';
        
        $text = "New Order Received\n\n";
        $text .= "Order Number: $orderNumber\n";
        $text .= "Customer Email: $customerEmail\n";
        $text .= "Total Amount: ₹$totalAmount\n\n";
        $text .= "Please log in to the admin panel to process this order.\n\n";
        $text .= "Best regards,\nBLine Boutique";
        
        return $text;
    }
    
    /**
     * Get order tracking template
     */
    private function getOrderTrackingTemplate($orderData, $status, $trackingNumber, $customerName) {
        $orderNumber = $orderData['order_number'] ?? 'N/A';
        $statusLabels = [
            'confirmed' => 'Order Confirmed',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled'
        ];
        $statusLabel = $statusLabels[strtolower($status)] ?? ucfirst($status);
        
        $baseUrl = $this->getBaseUrl();
        $trackingHtml = '';
        if (!empty($trackingNumber)) {
            $trackingHtml = '<div class="order-info-box" style="background: #dbeafe; border-left: 4px solid #2563eb;">
                <p style="margin: 0; color: #1e40af; font-weight: 600;">Tracking Number: ' . htmlspecialchars($trackingNumber) . '</p>
            </div>';
        }
        
        return '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Update</title>
    ' . $this->getEmailStyles() . '
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>BLine<span style="color: #ffffff;">.</span></h1>
        </div>
        
        <div class="email-body">
            <div class="email-content">
                <h2>Order Status Update</h2>
                
                <p>Hello ' . htmlspecialchars($customerName) . ',</p>
                
                <p>Your order <strong>' . htmlspecialchars($orderNumber) . '</strong> status has been updated.</p>
                
                <div class="order-info-box">
                    <div class="order-info-row">
                        <span class="order-info-label">Order Number</span>
                        <span class="order-info-value">' . htmlspecialchars($orderNumber) . '</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">New Status</span>
                        <span class="order-info-value" style="color: #14b8a6; font-weight: 700;">' . htmlspecialchars($statusLabel) . '</span>
                    </div>
                </div>
                
                ' . $trackingHtml . '
                
                <div class="button-container">
                    <a href="' . $baseUrl . '/order-overview.php?order=' . urlencode($orderNumber) . '" class="email-button">VIEW ORDER STATUS</a>
                </div>
            </div>
        </div>
        
        ' . $this->getEmailFooter() . '
    </div>
</body>
</html>';
    }
    
    private function getOrderTrackingPlainText($orderData, $status, $trackingNumber, $customerName) {
        $orderNumber = $orderData['order_number'] ?? 'N/A';
        return "Hello $customerName,\n\nYour order $orderNumber status has been updated to: $status" . 
               ($trackingNumber ? "\nTracking Number: $trackingNumber" : '') . 
               "\n\nBest regards,\nBLine Boutique";
    }
    
    /**
     * Get order cancellation template
     */
    private function getOrderCancellationTemplate($orderData, $reason, $customerName) {
        $orderNumber = $orderData['order_number'] ?? 'N/A';
        $totalAmount = number_format($orderData['total_amount'] ?? 0, 2);
        $baseUrl = $this->getBaseUrl();
        
        $reasonHtml = '';
        if (!empty($reason)) {
            $reasonHtml = '<div class="order-info-box" style="background: #fef2f2; border-left: 4px solid #ef4444;">
                <p style="margin: 0; color: #991b1b;"><strong>Reason:</strong> ' . htmlspecialchars($reason) . '</p>
            </div>';
        }
        
        return '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Cancelled</title>
    ' . $this->getEmailStyles() . '
</head>
<body>
    <div class="email-container">
        <div class="email-header" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
            <h1>BLine<span style="color: #ffffff;">.</span></h1>
        </div>
        
        <div class="email-body">
            <div class="email-content">
                <h2>Order Cancelled</h2>
                
                <p>Hello ' . htmlspecialchars($customerName) . ',</p>
                
                <p>We regret to inform you that your order <strong>' . htmlspecialchars($orderNumber) . '</strong> has been cancelled.</p>
                
                ' . $reasonHtml . '
                
                <div class="order-info-box">
                    <div class="order-info-row">
                        <span class="order-info-label">Order Number</span>
                        <span class="order-info-value">' . htmlspecialchars($orderNumber) . '</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">Order Amount</span>
                        <span class="order-info-value">₹' . $totalAmount . '</span>
                    </div>
                </div>
                
                <p>If payment was made, refund will be processed within 5-7 business days.</p>
                
                <div class="button-container">
                    <a href="' . $baseUrl . '/order-overview.php?order=' . urlencode($orderNumber) . '" class="email-button">VIEW ORDER</a>
                </div>
            </div>
        </div>
        
        ' . $this->getEmailFooter() . '
    </div>
</body>
</html>';
    }
    
    private function getOrderCancellationPlainText($orderData, $reason, $customerName) {
        $orderNumber = $orderData['order_number'] ?? 'N/A';
        return "Hello $customerName,\n\nYour order $orderNumber has been cancelled." . 
               ($reason ? "\nReason: $reason" : '') . 
               "\n\nBest regards,\nBLine Boutique";
    }
    
    /**
     * Get order refund template
     */
    private function getOrderRefundTemplate($orderData, $refundAmount, $reason, $customerName) {
        $orderNumber = $orderData['order_number'] ?? 'N/A';
        $formattedAmount = number_format($refundAmount, 2);
        $baseUrl = $this->getBaseUrl();
        
        $reasonHtml = '';
        if (!empty($reason)) {
            $reasonHtml = '<p><strong>Reason:</strong> ' . htmlspecialchars($reason) . '</p>';
        }
        
        return '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Processed</title>
    ' . $this->getEmailStyles() . '
</head>
<body>
    <div class="email-container">
        <div class="email-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <h1>BLine<span style="color: #ffffff;">.</span></h1>
        </div>
        
        <div class="email-body">
            <div class="email-content">
                <h2>Refund Processed</h2>
                
                <p>Hello ' . htmlspecialchars($customerName) . ',</p>
                
                <p>We have processed a refund for your order <strong>' . htmlspecialchars($orderNumber) . '</strong>.</p>
                
                <div class="order-info-box" style="background: #d1fae5; border-left: 4px solid #10b981;">
                    <div class="order-info-row">
                        <span class="order-info-label">Refund Amount</span>
                        <span class="order-info-value" style="color: #059669; font-size: 20px;">₹' . $formattedAmount . '</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">Order Number</span>
                        <span class="order-info-value">' . htmlspecialchars($orderNumber) . '</span>
                    </div>
                </div>
                
                ' . $reasonHtml . '
                
                <p>The refund will be credited to your original payment method within 5-7 business days.</p>
                
                <div class="button-container">
                    <a href="' . $baseUrl . '/order-overview.php?order=' . urlencode($orderNumber) . '" class="email-button">VIEW ORDER</a>
                </div>
            </div>
        </div>
        
        ' . $this->getEmailFooter() . '
    </div>
</body>
</html>';
    }
    
    private function getOrderRefundPlainText($orderData, $refundAmount, $reason, $customerName) {
        $orderNumber = $orderData['order_number'] ?? 'N/A';
        $formattedAmount = number_format($refundAmount, 2);
        return "Hello $customerName,\n\nRefund of ₹$formattedAmount has been processed for order $orderNumber." . 
               ($reason ? "\nReason: $reason" : '') . 
               "\n\nBest regards,\nBLine Boutique";
    }
    
    /**
     * Get refund confirmation template
     */
    private function getRefundConfirmationTemplate($orderData, $refundAmount, $customerName) {
        return $this->getOrderRefundTemplate($orderData, $refundAmount, 'Refund confirmed and processed', $customerName);
    }
    
    private function getRefundConfirmationPlainText($orderData, $refundAmount, $customerName) {
        return $this->getOrderRefundPlainText($orderData, $refundAmount, 'Refund confirmed', $customerName);
    }
    
    /**
     * Get refund rejection template
     */
    private function getRefundRejectionTemplate($orderData, $reason, $customerName) {
        $orderNumber = $orderData['order_number'] ?? 'N/A';
        $baseUrl = $this->getBaseUrl();
        
        $reasonHtml = !empty($reason) ? '<p><strong>Reason:</strong> ' . htmlspecialchars($reason) . '</p>' : 
                     '<p>Please contact our support team for more information.</p>';
        
        return '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Request Update</title>
    ' . $this->getEmailStyles() . '
</head>
<body>
    <div class="email-container">
        <div class="email-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <h1>BLine<span style="color: #ffffff;">.</span></h1>
        </div>
        
        <div class="email-body">
            <div class="email-content">
                <h2>Refund Request Update</h2>
                
                <p>Hello ' . htmlspecialchars($customerName) . ',</p>
                
                <p>We have reviewed your refund request for order <strong>' . htmlspecialchars($orderNumber) . '</strong>.</p>
                
                <div class="order-info-box" style="background: #fef3c7; border-left: 4px solid #f59e0b;">
                    <p style="margin: 0; color: #92400e; font-weight: 600;">Unfortunately, your refund request could not be processed at this time.</p>
                </div>
                
                ' . $reasonHtml . '
                
                <p>If you have any questions or concerns, please contact our support team.</p>
                
                <div class="button-container">
                    <a href="' . $baseUrl . '/order-overview.php?order=' . urlencode($orderNumber) . '" class="email-button">VIEW ORDER</a>
                </div>
            </div>
        </div>
        
        ' . $this->getEmailFooter() . '
    </div>
</body>
</html>';
    }
    
    private function getRefundRejectionPlainText($orderData, $reason, $customerName) {
        $orderNumber = $orderData['order_number'] ?? 'N/A';
        return "Hello $customerName,\n\nYour refund request for order $orderNumber could not be processed." . 
               ($reason ? "\nReason: $reason" : '') . 
               "\n\nBest regards,\nBLine Boutique";
    }
    
    /**
     * Get common email styles (matching login.php theme)
     */
    private function getEmailStyles() {
        return '<style>
        body {
            font-family: "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .email-header h1 {
            color: #ffffff;
            font-family: "Playfair Display", serif;
            font-size: 32px;
            font-weight: 900;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .email-body {
            padding: 40px 30px;
        }
        .email-content {
            color: #4b5563;
            font-size: 16px;
            line-height: 1.8;
        }
        .email-content h2 {
            color: #111827;
            font-family: "Playfair Display", serif;
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 20px 0;
        }
        .order-info-box {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
        }
        .order-info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .order-info-row:last-child {
            border-bottom: none;
        }
        .order-info-label {
            color: #6b7280;
            font-weight: 600;
            font-size: 14px;
        }
        .order-info-value {
            color: #111827;
            font-weight: 600;
            font-size: 14px;
        }
        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        .order-items-table th {
            background: #f9fafb;
            padding: 12px;
            text-align: left;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }
        .order-items-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .totals-section {
            background: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }
        .total-row.final {
            border-top: 2px solid #e5e7eb;
            margin-top: 8px;
            padding-top: 12px;
            font-size: 18px;
            font-weight: 700;
            color: #14b8a6;
        }
        .address-box {
            background: #f9fafb;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        .address-box strong {
            color: #111827;
            font-size: 14px;
            display: block;
            margin-bottom: 8px;
        }
        .address-box p {
            margin: 4px 0;
            color: #4b5563;
            font-size: 14px;
            line-height: 1.6;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .email-button {
            display: inline-block;
            background-color: #111827;
            color: #ffffff !important;
            padding: 16px 32px;
            text-decoration: none;
            border-radius: 16px;
            font-weight: 800;
            font-size: 16px;
            letter-spacing: 0.5px;
            transition: background-color 0.3s;
        }
        .email-button:hover {
            background-color: #14b8a6;
        }
        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 20px 15px;
            }
            .email-header {
                padding: 30px 20px;
            }
            .order-items-table {
                font-size: 12px;
            }
            .order-items-table th,
            .order-items-table td {
                padding: 8px 4px;
            }
        }
        </style>';
    }
    
    /**
     * Get common email footer
     */
    private function getEmailFooter() {
        return '<div class="email-footer" style="background-color: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb;">
            <p style="color: #6b7280; font-size: 14px; margin: 5px 0;"><strong>BLine Boutique</strong></p>
            <p style="color: #6b7280; font-size: 14px; margin: 5px 0;">Your trusted fashion destination</p>
            <p style="margin-top: 15px; font-size: 12px; color: #9ca3af;">
                This is an automated email. Please do not reply to this message.
            </p>
        </div>';
    }
}
