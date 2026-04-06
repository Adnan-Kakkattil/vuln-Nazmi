<?php
/**
 * Direct SMTP Connection Test Script
 * Tests SMTP connection without going through the full email service
 * Run this directly: php test-smtp-connection.php
 */

// Increase execution time for testing
set_time_limit(60);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "=== SMTP Connection Test ===\n\n";

// Load SMTP settings from database
try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        SELECT setting_key, setting_value 
        FROM system_settings 
        WHERE category = 'email'
    ");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $settings = [];
    foreach ($results as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    echo "Settings loaded from database:\n";
    echo "  SMTP Host: " . ($settings['smtp_host'] ?? 'smtp.hostinger.com') . "\n";
    echo "  SMTP Port: " . ($settings['smtp_port'] ?? '465') . "\n";
    echo "  SMTP Username: " . ($settings['smtp_username'] ?? 'mail@arzhizeon.com') . "\n";
    echo "  SMTP Encryption: " . ($settings['smtp_encryption'] ?? 'ssl') . "\n";
    echo "  Email Service Enabled: " . ($settings['email_service_enabled'] ?? '0') . "\n\n";
    
} catch (Exception $e) {
    echo "Error loading settings: " . $e->getMessage() . "\n";
    echo "Using default settings...\n\n";
    $settings = [];
}

// Create PHPMailer instance
$mail = new PHPMailer(true);

try {
    // SMTP Configuration with Hostinger defaults
    $mail->isSMTP();
    $mail->Host = $settings['smtp_host'] ?? 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = $settings['smtp_username'] ?? 'mail@archizeon.com';
    $mail->Password = $settings['smtp_password'] ?? 'Adnan@66202';
    
    // Get port and determine encryption type
    // Port 465 requires SSL (SMTPS), Port 587 uses TLS (STARTTLS)
    $port = intval($settings['smtp_port'] ?? 465);
    $encryption = strtolower($settings['smtp_encryption'] ?? '');
    
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
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL for port 465
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS for port 587
    }
    
    $mail->Port = $port;
    
    // Timeout settings
    $mail->Timeout = 10; // 10 seconds timeout
    $mail->SMTPKeepAlive = false;
    
    // SSL/TLS options
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => false
        ],
        'tls' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => false
        ]
    ];
    
    // Enable verbose debugging
    $mail->SMTPDebug = 2; // Show all debug output
    $mail->Debugoutput = function($str, $level) {
        echo "SMTP Debug [$level]: $str\n";
    };
    
    // From address - use provided email
    $mail->setFrom(
        $settings['email_from_address'] ?? 'mail@archizeon.com',
        $settings['email_from_name'] ?? 'NAZMI BOUTIQUE'
    );
    
    // Test recipient (use your email or the SMTP username)
    $testEmail = $settings['smtp_username'] ?? 'mail@archizeon.com';
    echo "Test email will be sent to: $testEmail\n";
    echo "You can change this by editing the script.\n\n";
    $mail->addAddress($testEmail);
    
    // Test email content
    $mail->isHTML(true);
    $mail->Subject = 'SMTP Connection Test - ' . date('Y-m-d H:i:s');
    $mail->Body = '<h1>SMTP Test Successful!</h1><p>If you receive this email, your SMTP configuration is working correctly.</p>';
    $mail->AltBody = 'SMTP Test Successful! If you receive this email, your SMTP configuration is working correctly.';
    
    echo "Attempting to connect to SMTP server...\n";
    echo "Host: {$mail->Host}\n";
    echo "Port: {$mail->Port}\n";
    echo "Encryption: {$mail->SMTPSecure}\n";
    echo "Username: {$mail->Username}\n\n";
    
    // Try to send
    $startTime = microtime(true);
    $result = $mail->send();
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    
    echo "\n=== RESULT ===\n";
    if ($result) {
        echo "✓ SUCCESS! Email sent successfully!\n";
        echo "  Duration: {$duration} seconds\n";
        echo "  Sent to: {$testEmail}\n";
    } else {
        echo "✗ FAILED! Could not send email.\n";
        echo "  Error: " . $mail->ErrorInfo . "\n";
    }
    
} catch (Exception $e) {
    echo "\n=== ERROR ===\n";
    echo "✗ Exception occurred: " . $e->getMessage() . "\n";
    echo "  PHPMailer Error: " . ($mail->ErrorInfo ?? 'No error info') . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Check SMTP host and port are correct\n";
    echo "2. Verify username and password\n";
    echo "3. Check firewall/network allows SMTP connections\n";
    echo "4. Verify Hostinger email account is active\n";
} catch (Error $e) {
    echo "\n=== FATAL ERROR ===\n";
    echo "✗ Fatal error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
