<?php
/**
 * Setup SMTP Settings in Database
 * This script updates the database with the provided SMTP credentials
 * Run this once to configure email settings: php setup-smtp-settings.php
 */

require_once __DIR__ . '/includes/config.php';

echo "=== Setting up SMTP Configuration ===\n\n";

try {
    $pdo = getDbConnection();
    
    // SMTP Configuration
    $smtpSettings = [
        'email_service_enabled' => '1',
        'smtp_host' => 'smtp.hostinger.com',
        'smtp_port' => '465',
        'smtp_username' => 'mail@arzhizeon.com',
        'smtp_password' => 'Adnan@66202',
        'smtp_encryption' => 'ssl', // SSL for port 465
        'email_from_address' => 'mail@archizeon.com',
        'email_from_name' => 'NAZMI BOUTIQUE',
        'email_notifications_enabled' => '1',
        'email_order_confirmation' => '1',
        'email_shipping_updates' => '1',
        'email_welcome_email' => '1'
    ];
    
    // Prepare update statement
    $stmt = $pdo->prepare("
        INSERT INTO system_settings (setting_key, setting_value, setting_type, description, category)
        VALUES (?, ?, 'string', ?, 'email')
        ON DUPLICATE KEY UPDATE 
            setting_value = VALUES(setting_value),
            updated_at = CURRENT_TIMESTAMP
    ");
    
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
    
    $updated = 0;
    foreach ($smtpSettings as $key => $value) {
        $description = $descriptions[$key] ?? ucfirst(str_replace('_', ' ', $key));
        $stmt->execute([$key, $value, $description]);
        $updated++;
        echo "✓ Updated: $key = $value\n";
    }
    
    echo "\n=== Configuration Complete ===\n";
    echo "Updated $updated settings successfully.\n";
    echo "\nNext steps:\n";
    echo "1. Test SMTP connection: php test-smtp-connection.php\n";
    echo "2. Test forgot password email: php test-forgot-password-email.php\n";
    echo "3. Or access via browser: test-forgot-password-email.php?email=your@email.com\n";
    
} catch (PDOException $e) {
    echo "\n✗ Database Error: " . $e->getMessage() . "\n";
    echo "Please check your database connection and try again.\n";
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
}
