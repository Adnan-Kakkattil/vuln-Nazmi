<?php
/**
 * Complete Email Service Verification Script
 * Tests all aspects of the email service implementation
 * Access via browser: http://yourdomain.com/verify-email-service.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(60);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/email/EmailService.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Service Verification</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #14b8a6;
            margin-bottom: 10px;
            border-bottom: 3px solid #14b8a6;
            padding-bottom: 10px;
        }
        h2 {
            color: #333;
            margin: 30px 0 15px 0;
            font-size: 20px;
        }
        .test-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #14b8a6;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #dc3545;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 12px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #ffc107;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 12px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #17a2b8;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background: #e9ecef;
            font-weight: 600;
        }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-fail { color: #dc3545; font-weight: bold; }
        .status-warn { color: #ffc107; font-weight: bold; }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .btn {
            display: inline-block;
            background: #14b8a6;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #0d9488;
        }
        .btn-test {
            background: #17a2b8;
        }
        .btn-test:hover {
            background: #138496;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Email Service Verification</h1>
        <p>This script verifies that the email service is properly configured and ready to use.</p>
        
        <?php
        $testEmail = $_GET['email'] ?? 'mail@arzhizeon.com';
        $runTests = isset($_GET['test']);
        
        // Test 1: Check Database Connection
        echo '<div class="test-section">';
        echo '<h2>1. Database Connection</h2>';
        try {
            $pdo = getDbConnection();
            echo '<div class="success">✅ Database connection successful</div>';
            $dbOk = true;
        } catch (Exception $e) {
            echo '<div class="error">❌ Database connection failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
            $dbOk = false;
        }
        echo '</div>';
        
        if (!$dbOk) {
            echo '<div class="error"><strong>Cannot proceed without database connection.</strong></div>';
            exit;
        }
        
        // Test 2: Check SMTP Settings in Database
        echo '<div class="test-section">';
        echo '<h2>2. SMTP Settings Configuration</h2>';
        try {
            $stmt = $pdo->prepare("
                SELECT setting_key, setting_value 
                FROM system_settings 
                WHERE category = 'email'
                ORDER BY setting_key
            ");
            $stmt->execute();
            $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $settingsMap = [];
            foreach ($settings as $setting) {
                $settingsMap[$setting['setting_key']] = $setting['setting_value'];
            }
            
            $requiredSettings = [
                'email_service_enabled' => '1',
                'smtp_host' => 'smtp.hostinger.com',
                'smtp_port' => '465',
                'smtp_username' => 'mail@arzhizeon.com',
                'smtp_encryption' => 'ssl',
                'email_from_address' => 'mail@archizeon.com'
            ];
            
            echo '<table>';
            echo '<tr><th>Setting</th><th>Expected</th><th>Current</th><th>Status</th></tr>';
            
            $allSettingsOk = true;
            foreach ($requiredSettings as $key => $expected) {
                $current = $settingsMap[$key] ?? '<em>Not set</em>';
                $displayCurrent = ($key === 'smtp_password') ? '••••••••' : htmlspecialchars($current);
                $match = ($current === $expected);
                $allSettingsOk = $allSettingsOk && $match;
                
                echo '<tr>';
                echo '<td><code>' . htmlspecialchars($key) . '</code></td>';
                echo '<td>' . htmlspecialchars($expected) . '</td>';
                echo '<td>' . $displayCurrent . '</td>';
                echo '<td class="' . ($match ? 'status-ok' : 'status-fail') . '">' . ($match ? '✅' : '❌') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            
            if ($allSettingsOk) {
                echo '<div class="success">✅ All SMTP settings are correctly configured</div>';
            } else {
                echo '<div class="warning">⚠️ Some settings need to be updated. <a href="setup-smtp-web.php" class="btn">Update Settings</a></div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="error">❌ Error checking settings: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        echo '</div>';
        
        // Test 3: Check EmailService Class
        echo '<div class="test-section">';
        echo '<h2>3. EmailService Class</h2>';
        try {
            if (class_exists('EmailService')) {
                echo '<div class="success">✅ EmailService class found</div>';
                
                $emailService = new EmailService();
                echo '<div class="success">✅ EmailService instance created successfully</div>';
                
                // Check if mailer is configured
                $reflection = new ReflectionClass($emailService);
                $mailerProperty = $reflection->getProperty('mailer');
                $mailerProperty->setAccessible(true);
                $mailer = $mailerProperty->getValue($emailService);
                
                if ($mailer) {
                    echo '<div class="success">✅ PHPMailer instance initialized</div>';
                } else {
                    echo '<div class="warning">⚠️ PHPMailer instance not initialized (may be disabled)</div>';
                }
                
            } else {
                echo '<div class="error">❌ EmailService class not found</div>';
            }
        } catch (Exception $e) {
            echo '<div class="error">❌ Error initializing EmailService: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        echo '</div>';
        
        // Test 4: Check PHPMailer Installation
        echo '<div class="test-section">';
        echo '<h2>4. PHPMailer Installation</h2>';
        try {
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
                $version = $mailer::VERSION ?? 'Unknown';
                echo '<div class="success">✅ PHPMailer is installed (Version: ' . htmlspecialchars($version) . ')</div>';
            } else {
                echo '<div class="error">❌ PHPMailer not found. Run: <code>composer install</code></div>';
            }
        } catch (Exception $e) {
            echo '<div class="error">❌ Error checking PHPMailer: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        echo '</div>';
        
        // Test 5: Check API Endpoints
        echo '<div class="test-section">';
        echo '<h2>5. API Endpoints</h2>';
        $endpoints = [
            'Forgot Password' => 'api/v1/auth/forgot-password.php',
            'Reset Password' => 'api/v1/auth/reset-password.php'
        ];
        
        echo '<table>';
        echo '<tr><th>Endpoint</th><th>File</th><th>Status</th></tr>';
        foreach ($endpoints as $name => $file) {
            $exists = file_exists(__DIR__ . '/' . $file);
            echo '<tr>';
            echo '<td>' . htmlspecialchars($name) . '</td>';
            echo '<td><code>' . htmlspecialchars($file) . '</code></td>';
            echo '<td class="' . ($exists ? 'status-ok' : 'status-fail') . '">' . ($exists ? '✅ Exists' : '❌ Missing') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
        
        // Test 6: Test Email Sending (if requested)
        if ($runTests) {
            echo '<div class="test-section">';
            echo '<h2>6. Test Email Sending</h2>';
            echo '<div class="info">Attempting to send test email to: <strong>' . htmlspecialchars($testEmail) . '</strong></div>';
            
            try {
                $emailService = new EmailService();
                $testToken = bin2hex(random_bytes(32));
                
                echo '<div class="info">Generating test reset token...</div>';
                echo '<div class="info">Token: <code>' . substr($testToken, 0, 20) . '...</code></div>';
                
                $startTime = microtime(true);
                $result = $emailService->sendForgotPasswordEmail($testEmail, $testToken, 'Test User');
                $duration = round(microtime(true) - $startTime, 2);
                
                if ($result) {
                    echo '<div class="success">';
                    echo '<h3>✅ Email Sent Successfully!</h3>';
                    echo '<p>Duration: ' . $duration . ' seconds</p>';
                    echo '<p>Please check your inbox (and spam folder) for the test email.</p>';
                    echo '</div>';
                } else {
                    echo '<div class="error">';
                    echo '<h3>❌ Failed to Send Email</h3>';
                    echo '<p>Please check:</p>';
                    echo '<ul>';
                    echo '<li>SMTP credentials are correct</li>';
                    echo '<li>Port 465 is not blocked by firewall</li>';
                    echo '<li>Email account is active</li>';
                    echo '<li>Check PHP error logs for details</li>';
                    echo '</ul>';
                    echo '</div>';
                }
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<h3>❌ Exception Occurred</h3>';
                echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<div class="test-section">';
            echo '<h2>6. Test Email Sending</h2>';
            echo '<form method="GET" style="margin: 15px 0;">';
            echo '<label>Test Email Address: <input type="email" name="email" value="' . htmlspecialchars($testEmail) . '" required style="padding: 8px; margin: 5px; border: 1px solid #ddd; border-radius: 4px;"></label> ';
            echo '<input type="hidden" name="test" value="1">';
            echo '<button type="submit" class="btn btn-test">Send Test Email</button>';
            echo '</form>';
            echo '</div>';
        }
        
        // Summary
        echo '<div class="test-section">';
        echo '<h2>📋 Summary</h2>';
        echo '<div class="info">';
        echo '<h3>Quick Links:</h3>';
        echo '<ul>';
        echo '<li><a href="setup-smtp-web.php" class="btn">Configure SMTP Settings</a></li>';
        echo '<li><a href="test-smtp-connection.php" class="btn">Test SMTP Connection</a></li>';
        echo '<li><a href="test-forgot-password-email.php" class="btn">Test Forgot Password Email</a></li>';
        echo '<li><a href="forgot-password.php" class="btn">Forgot Password Page</a></li>';
        echo '</ul>';
        echo '</div>';
        echo '</div>';
        ?>
    </div>
</body>
</html>
