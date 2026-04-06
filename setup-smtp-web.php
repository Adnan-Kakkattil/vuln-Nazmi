<?php
/**
 * Web-based SMTP Settings Setup
 * Access via browser: http://yourdomain.com/setup-smtp-web.php
 * This will update the database with SMTP configuration
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMTP Settings Setup</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #14b8a6;
            padding-bottom: 10px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #17a2b8;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .btn {
            background: #14b8a6;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #0d9488;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 SMTP Settings Setup</h1>
        
        <?php
        $action = $_GET['action'] ?? 'setup';
        
        if ($action === 'setup' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = 'process';
        }
        
        if ($action === 'process') {
            try {
                $pdo = getDbConnection();
                
                // SMTP Configuration
                $smtpSettings = [
                    'email_service_enabled' => '1',
                    'smtp_host' => 'smtp.hostinger.com',
                    'smtp_port' => '465',
                    'smtp_username' => 'mail@archizeon.com',
                    'smtp_password' => 'Adnan@66202',
                    'smtp_encryption' => 'ssl',
                    'email_from_address' => 'mail@archizeon.com',
                    'email_from_name' => 'BLine Boutique',
                    'email_notifications_enabled' => '1',
                    'email_order_confirmation' => '1',
                    'email_shipping_updates' => '1',
                    'email_welcome_email' => '1'
                ];
                
                $descriptions = [
                    'email_service_enabled' => 'Enable/Disable Email Service',
                    'smtp_host' => 'SMTP Host',
                    'smtp_port' => 'SMTP Port',
                    'smtp_username' => 'SMTP Username',
                    'smtp_password' => 'SMTP Password',
                    'smtp_encryption' => 'SMTP Encryption (ssl/tls)',
                    'email_from_address' => 'From Email Address',
                    'email_from_name' => 'From Name for emails',
                    'email_notifications_enabled' => 'Enable/Disable Email Notifications',
                    'email_order_confirmation' => 'Send order confirmation emails',
                    'email_shipping_updates' => 'Send shipping update emails',
                    'email_welcome_email' => 'Send welcome email on registration'
                ];
                
                $stmt = $pdo->prepare("
                    INSERT INTO system_settings (setting_key, setting_value, setting_type, description, category)
                    VALUES (?, ?, 'string', ?, 'email')
                    ON DUPLICATE KEY UPDATE 
                        setting_value = VALUES(setting_value),
                        updated_at = CURRENT_TIMESTAMP
                ");
                
                $updated = [];
                $errors = [];
                
                foreach ($smtpSettings as $key => $value) {
                    try {
                        $description = $descriptions[$key] ?? ucfirst(str_replace('_', ' ', $key));
                        $stmt->execute([$key, $value, $description]);
                        $updated[] = $key;
                    } catch (PDOException $e) {
                        $errors[] = "$key: " . $e->getMessage();
                    }
                }
                
                if (count($updated) > 0) {
                    echo '<div class="success">';
                    echo '<h3>✅ Configuration Updated Successfully!</h3>';
                    echo '<p>Updated <strong>' . count($updated) . '</strong> settings in the database.</p>';
                    echo '</div>';
                    
                    echo '<h3>Updated Settings:</h3>';
                    echo '<table>';
                    echo '<tr><th>Setting</th><th>Value</th></tr>';
                    foreach ($updated as $key) {
                        $displayValue = ($key === 'smtp_password') ? '••••••••' : htmlspecialchars($smtpSettings[$key]);
                        echo '<tr><td>' . htmlspecialchars($key) . '</td><td>' . $displayValue . '</td></tr>';
                    }
                    echo '</table>';
                    
                    if (count($errors) > 0) {
                        echo '<div class="error">';
                        echo '<h3>⚠️ Some Errors Occurred:</h3>';
                        echo '<ul>';
                        foreach ($errors as $error) {
                            echo '<li>' . htmlspecialchars($error) . '</li>';
                        }
                        echo '</ul>';
                        echo '</div>';
                    }
                    
                    echo '<div class="info">';
                    echo '<h3>📋 Next Steps:</h3>';
                    echo '<ol>';
                    echo '<li>Test SMTP connection: <a href="test-smtp-connection.php" target="_blank">test-smtp-connection.php</a></li>';
                    echo '<li>Test forgot password email: <a href="test-forgot-password-email.php" target="_blank">test-forgot-password-email.php</a></li>';
                    echo '<li>Test the forgot password flow: <a href="forgot-password.php" target="_blank">forgot-password.php</a></li>';
                    echo '</ol>';
                    echo '</div>';
                } else {
                    echo '<div class="error">';
                    echo '<h3>❌ No Settings Were Updated</h3>';
                    echo '<p>Please check the errors above and try again.</p>';
                    echo '</div>';
                }
                
            } catch (PDOException $e) {
                echo '<div class="error">';
                echo '<h3>❌ Database Error</h3>';
                echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '</div>';
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<h3>❌ Error</h3>';
                echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '</div>';
            }
        } else {
            // Show current settings and setup form
            try {
                $pdo = getDbConnection();
                $stmt = $pdo->prepare("
                    SELECT setting_key, setting_value 
                    FROM system_settings 
                    WHERE category = 'email'
                    ORDER BY setting_key
                ");
                $stmt->execute();
                $currentSettings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $settingsMap = [];
                foreach ($currentSettings as $setting) {
                    $settingsMap[$setting['setting_key']] = $setting['setting_value'];
                }
                
                echo '<div class="info">';
                echo '<h3>📧 Current SMTP Configuration</h3>';
                echo '<p>The following settings will be updated in your database:</p>';
                echo '</div>';
                
                echo '<table>';
                echo '<tr><th>Setting</th><th>Current Value</th><th>New Value</th></tr>';
                
                $newSettings = [
                    'email_service_enabled' => '1',
                    'smtp_host' => 'smtp.hostinger.com',
                    'smtp_port' => '465',
                    'smtp_username' => 'mail@archizeon.com',
                    'smtp_password' => 'Adnan@66202',
                    'smtp_encryption' => 'ssl',
                    'email_from_address' => 'mail@archizeon.com',
                    'email_from_name' => 'BLine Boutique'
                ];
                
                foreach ($newSettings as $key => $newValue) {
                    $currentValue = $settingsMap[$key] ?? '<em>Not set</em>';
                    $displayCurrent = ($key === 'smtp_password') ? '••••••••' : htmlspecialchars($currentValue);
                    $displayNew = ($key === 'smtp_password') ? '••••••••' : htmlspecialchars($newValue);
                    $changed = ($currentValue !== $newValue) ? ' style="background: #fff3cd;"' : '';
                    echo "<tr$changed>";
                    echo '<td><strong>' . htmlspecialchars($key) . '</strong></td>';
                    echo '<td>' . $displayCurrent . '</td>';
                    echo '<td>' . $displayNew . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                
                echo '<form method="POST" action="?action=process">';
                echo '<button type="submit" class="btn">🚀 Update SMTP Settings</button>';
                echo '</form>';
                
            } catch (PDOException $e) {
                echo '<div class="error">';
                echo '<h3>❌ Database Connection Error</h3>';
                echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '<p>Please check your database configuration in <code>includes/config.php</code></p>';
                echo '</div>';
            }
        }
        ?>
    </div>
</body>
</html>
