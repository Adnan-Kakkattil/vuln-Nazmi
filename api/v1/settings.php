<?php
/**
 * Public Settings API
 * Endpoint: /api/v1/settings.php
 * Returns public (non-sensitive) system settings for frontend use
 *
 * Methods:
 * - GET: Retrieve public settings (filtered for security)
 */

header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../../includes/config.php';

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

// Only GET method allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse([
        'success' => false,
        'message' => 'Method not allowed'
    ], 405);
}

try {
    // Define which settings are safe to expose publicly
    // IMPORTANT: Never expose secrets, passwords, or sensitive API keys
    $publicSettings = [
        // Tax settings
        'tax_enabled',
        'tax_rate',
        'tax_inclusive',
        
        // Payment method availability (NOT the API keys!)
        'payment_online_enabled',
        'payment_cod_enabled',
        
        // Razorpay public key ONLY (not the secret)
        'razorpay_key',
        
        // Auth settings
        'auth_login_enabled',
        'auth_signup_enabled',
        'auth_forgot_password_enabled',
        'auth_guest_checkout_enabled',
        
        // General settings
        'site_name',
        'site_email',
        'site_phone',
        'currency',
        'currency_symbol'
    ];
    
    // Build the query to fetch only public settings
    $placeholders = str_repeat('?,', count($publicSettings) - 1) . '?';
    
    $stmt = $pdo->prepare("
        SELECT setting_key, setting_value, setting_type
        FROM system_settings
        WHERE setting_key IN ($placeholders)
    ");
    $stmt->execute($publicSettings);
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert values based on type and build response
    $result = [];
    foreach ($settings as $setting) {
        $value = $setting['setting_value'];
        
        // Convert based on type
        switch ($setting['setting_type']) {
            case 'boolean':
                $value = $value === '1' || $value === 'true' || $value === true;
                break;
            case 'number':
                $value = is_numeric($value) ? floatval($value) : 0;
                break;
            case 'json':
                $value = json_decode($value, true);
                break;
            // default: keep as string
        }
        
        $result[$setting['setting_key']] = $value;
    }
    
    // Add computed/derived settings for convenience
    $response = [
        'success' => true,
        'data' => [
            // Tax
            'tax' => [
                'enabled' => $result['tax_enabled'] ?? true,
                'rate' => $result['tax_rate'] ?? 18,
                'inclusive' => $result['tax_inclusive'] ?? false
            ],
            
            // Payment methods
            'payment' => [
                'online_enabled' => $result['payment_online_enabled'] ?? true,
                'cod_enabled' => $result['payment_cod_enabled'] ?? true,
                'razorpay_key' => $result['razorpay_key'] ?? '' // Public key only
            ],
            
            // Authentication
            'auth' => [
                'login_enabled' => $result['auth_login_enabled'] ?? true,
                'signup_enabled' => $result['auth_signup_enabled'] ?? true,
                'forgot_password_enabled' => $result['auth_forgot_password_enabled'] ?? true,
                'guest_checkout_enabled' => $result['auth_guest_checkout_enabled'] ?? true
            ],
            
            // General
            'general' => [
                'site_name' => $result['site_name'] ?? 'NAZMI BOUTIQUE',
                'site_email' => $result['site_email'] ?? '',
                'site_phone' => $result['site_phone'] ?? '',
                'currency' => $result['currency'] ?? 'INR',
                'currency_symbol' => $result['currency_symbol'] ?? '₹'
            ],
            
            // Raw settings for backwards compatibility
            'settings' => $result
        ]
    ];
    
    sendResponse($response);
    
} catch (PDOException $e) {
    error_log("Public Settings API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'Database error occurred'
    ], 500);
} catch (Exception $e) {
    error_log("Public Settings API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'An error occurred'
    ], 500);
}
