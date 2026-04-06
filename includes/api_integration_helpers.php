<?php
/**
 * API Integration Helper Functions for Tivora E-commerce
 * Provides utilities for API key generation, validation, and management
 */

// Prevent direct access
if (!defined('APP_INIT')) {
    die('Direct access not allowed');
}

/**
 * Generate a unique API key (Client ID)
 * Format: naz_xxxxxxxxxxxxxxxx (20 characters)
 */
function generateApiKey(): string
{
    $prefix = 'naz_';
    $random = bin2hex(random_bytes(12)); // 24 hex characters
    return $prefix . $random;
}

/**
 * Generate a secure API secret (Client Secret)
 * Returns a random string that should be hashed before storage
 */
function generateApiSecret(): string
{
    return bin2hex(random_bytes(32)); // 64 character hex string
}

/**
 * Hash API secret for storage
 */
function hashApiSecret(string $secret): string
{
    return password_hash($secret, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify API secret
 */
function verifyApiSecret(string $secret, ?string $hash): bool
{
    if (empty($hash)) {
        return false;
    }
    return password_verify($secret, $hash);
}

/**
 * Validate API key format
 */
function isValidApiKeyFormat(string $apiKey): bool
{
    return preg_match('/^naz_[a-f0-9]{24}$/i', $apiKey) === 1;
}

/**
 * Check if API key is active and valid
 */
function isApiKeyValid(PDO $pdo, string $apiKey): ?array
{
    // Try to get all columns (supports both old and new schema)
    $stmt = $pdo->prepare(
        'SELECT 
            id,
            api_key,
            COALESCE(api_secret, api_secret_hash) as api_secret,
            COALESCE(key_name, name) as key_name,
            name,
            description,
            scopes,
            is_active,
            expires_at,
            last_used_at,
            created_by,
            created_at,
            updated_at,
            COALESCE(tenant_id, NULL) as tenant_id,
            COALESCE(tenant_name, NULL) as tenant_name,
            COALESCE(allowed_ips, NULL) as allowed_ips,
            COALESCE(notes, NULL) as notes
         FROM pos_api_keys 
         WHERE api_key = :api_key AND is_active = 1 
         AND (expires_at IS NULL OR expires_at > NOW())'
    );
    $stmt->execute([':api_key' => $apiKey]);
    $keyData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Ensure api_secret is not null
    if ($keyData && empty($keyData['api_secret'])) {
        // Try to get from api_secret_hash if api_secret is null
        $stmt2 = $pdo->prepare('SELECT api_secret_hash FROM pos_api_keys WHERE api_key = :api_key');
        $stmt2->execute([':api_key' => $apiKey]);
        $hashData = $stmt2->fetch(PDO::FETCH_ASSOC);
        if ($hashData && !empty($hashData['api_secret_hash'])) {
            $keyData['api_secret'] = $hashData['api_secret_hash'];
        }
    }
    
    return $keyData ?: null;
}

/**
 * Log API request
 */
function logApiRequest(
    PDO $pdo,
    ?int $apiKeyId,
    ?string $apiKey,
    string $requestType,
    string $requestMethod,
    string $endpoint,
    ?string $ipAddress = null,
    ?string $userAgent = null,
    ?int $statusCode = null,
    ?int $responseTimeMs = null,
    ?string $errorMessage = null,
    ?array $requestData = null,
    ?string $responseSummary = null
): void
{
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO pos_integration_logs 
             (api_key_id, api_key, request_type, request_method, endpoint, ip_address, 
              user_agent, status_code, response_time_ms, error_message, request_data, response_summary)
             VALUES 
             (:api_key_id, :api_key, :request_type, :request_method, :endpoint, :ip_address,
              :user_agent, :status_code, :response_time_ms, :error_message, :request_data, :response_summary)'
        );
        
        $stmt->execute([
            ':api_key_id' => $apiKeyId,
            ':api_key' => $apiKey,
            ':request_type' => $requestType,
            ':request_method' => $requestMethod,
            ':endpoint' => $endpoint,
            ':ip_address' => $ipAddress ?: $_SERVER['REMOTE_ADDR'] ?? null,
            ':user_agent' => $userAgent ?: $_SERVER['HTTP_USER_AGENT'] ?? null,
            ':status_code' => $statusCode,
            ':response_time_ms' => $responseTimeMs,
            ':error_message' => $errorMessage,
            ':request_data' => $requestData ? json_encode($requestData) : null,
            ':response_summary' => $responseSummary
        ]);
    } catch (Exception $e) {
        error_log('Failed to log API request: ' . $e->getMessage());
    }
}

/**
 * Update API key last used timestamp
 */
function updateApiKeyLastUsed(PDO $pdo, string $apiKey): void
{
    try {
        $pdo->prepare(
            'UPDATE pos_api_keys SET last_used_at = NOW() WHERE api_key = :api_key'
        )->execute([':api_key' => $apiKey]);
    } catch (Exception $e) {
        error_log('Failed to update API key last used: ' . $e->getMessage());
    }
}

/**
 * Check IP whitelist
 */
function isIpAllowed(?string $allowedIps, string $clientIp): bool
{
    if (empty($allowedIps)) {
        return true; // No restriction
    }
    
    $allowed = array_map('trim', explode(',', $allowedIps));
    return in_array($clientIp, $allowed, true);
}

/**
 * Get API key statistics
 */
function getApiKeyStats(PDO $pdo, int $apiKeyId): array
{
    $stats = [
        'total_requests' => 0,
        'successful_requests' => 0,
        'failed_requests' => 0,
        'last_request_at' => null,
        'avg_response_time' => 0
    ];
    
    try {
        // Total requests
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) as total,
                    SUM(CASE WHEN status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END) as success,
                    SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as failed,
                    MAX(created_at) as last_request,
                    AVG(response_time_ms) as avg_time
             FROM pos_integration_logs
             WHERE api_key_id = :api_key_id'
        );
        $stmt->execute([':api_key_id' => $apiKeyId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $stats['total_requests'] = (int)($result['total'] ?? 0);
            $stats['successful_requests'] = (int)($result['success'] ?? 0);
            $stats['failed_requests'] = (int)($result['failed'] ?? 0);
            $stats['last_request_at'] = $result['last_request'];
            $stats['avg_response_time'] = (float)($result['avg_time'] ?? 0);
        }
    } catch (Exception $e) {
        error_log('Failed to get API key stats: ' . $e->getMessage());
    }
    
    return $stats;
}

/**
 * Sync order to POS system immediately (non-blocking)
 * This function is called automatically after each order is created
 */
function syncOrderToPOS(PDO $pdo, int $orderId, bool $async = true): void
{
    // Check if auto-sync is enabled
    try {
        $autoSyncEnabled = getSystemSetting($pdo, 'pos_auto_sync_enabled');
        if ($autoSyncEnabled === '0' || $autoSyncEnabled === 'false') {
            return; // Auto-sync is disabled
        }
    } catch (Exception $e) {
        // If setting doesn't exist, assume enabled
    }
    
    try {
        // Get all active API keys with 'orders' scope
        $stmt = $pdo->prepare("
            SELECT id, api_key, scopes 
            FROM pos_api_keys 
            WHERE is_active = 1 
            AND (expires_at IS NULL OR expires_at > NOW())
            AND JSON_CONTAINS(scopes, '\"orders\"', '$')
        ");
        $stmt->execute();
        $apiKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($apiKeys)) {
            return; // No active API keys with orders scope
        }
        
        // Fetch order data
        $orderStmt = $pdo->prepare("
            SELECT o.*, 
                   u.email AS customer_email,
                   u.first_name AS customer_first_name,
                   u.last_name AS customer_last_name,
                   u.phone AS customer_phone
            FROM orders o
            LEFT JOIN users u ON u.id = o.user_id
            WHERE o.id = ?
        ");
        $orderStmt->execute([$orderId]);
        $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            return; // Order not found
        }
        
        // Fetch order items
        $itemsStmt = $pdo->prepare("
            SELECT oi.*,
                   p.sku,
                   p.name AS product_name
            FROM order_items oi
            LEFT JOIN products p ON p.id = oi.product_id
            WHERE oi.order_id = ?
        ");
        $itemsStmt->execute([$orderId]);
        $order['items'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If async, use background process (non-blocking)
        if ($async) {
            // Use register_shutdown_function for async execution (runs after response is sent)
            register_shutdown_function(function() use ($pdo, $order, $apiKeys) {
                // Ensure we have a connection
                try {
                    syncOrderToPOSInternal($pdo, $order, $apiKeys);
                } catch (Exception $e) {
                    error_log("Async sync order error: " . $e->getMessage());
                }
            });
        } else {
            syncOrderToPOSInternal($pdo, $order, $apiKeys);
        }
    } catch (Exception $e) {
        error_log("Sync Order to POS Error: " . $e->getMessage());
    }
}

/**
 * Internal function to sync order to POS (called synchronously or asynchronously)
 */
function syncOrderToPOSInternal(PDO $pdo, array $order, array $apiKeys): void
{
    foreach ($apiKeys as $apiKeyData) {
        try {
            // Get POS endpoint from system settings or use default
            $posEndpoint = getSystemSetting($pdo, 'pos_sync_endpoint') ?: null;
            
            if (!$posEndpoint) {
                // If no endpoint configured, skip sync
                continue;
            }
            
            // Prepare sync data
            $syncData = [
                'order_id' => $order['id'],
                'order_number' => $order['order_number'],
                'order_date' => $order['order_date'],
                'customer' => [
                    'email' => $order['customer_email'],
                    'first_name' => $order['customer_first_name'],
                    'last_name' => $order['customer_last_name'],
                    'phone' => $order['customer_phone']
                ],
                'items' => $order['items'],
                'totals' => [
                    'subtotal' => $order['subtotal'],
                    'tax_amount' => $order['tax_amount'],
                    'discount_amount' => $order['discount_amount'],
                    'shipping_cost' => $order['shipping_cost'],
                    'total_amount' => $order['total_amount']
                ],
                'payment' => [
                    'method' => $order['payment_method'],
                    'status' => $order['payment_status'],
                    'transaction_id' => $order['payment_transaction_id']
                ],
                'status' => $order['status']
            ];
            
            // Send to POS system via webhook/API
            $ch = curl_init($posEndpoint . '/api/sync/order');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($syncData),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-API-Key: ' . $apiKeyData['api_key'],
                    'X-Sync-Source: ecommerce'
                ],
                CURLOPT_TIMEOUT => 5, // 5 second timeout
                CURLOPT_CONNECTTIMEOUT => 2
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            // Log sync attempt
            logApiRequest(
                $pdo,
                $apiKeyData['id'],
                $apiKeyData['api_key'],
                'orders',
                'POST',
                '/api/sync/order',
                $_SERVER['REMOTE_ADDR'] ?? null,
                'Auto-Sync',
                $httpCode,
                null,
                $error ?: null,
                $syncData,
                $response ? substr($response, 0, 200) : null
            );
            
        } catch (Exception $e) {
            error_log("POS Sync Error for API Key {$apiKeyData['id']}: " . $e->getMessage());
        }
    }
}

/**
 * Sync financial transaction to POS system immediately (non-blocking)
 * This function is called automatically after each financial transaction is created
 */
function syncTransactionToPOS(PDO $pdo, int $transactionId, bool $async = true): void
{
    // Check if auto-sync is enabled
    try {
        $autoSyncEnabled = getSystemSetting($pdo, 'pos_auto_sync_enabled');
        if ($autoSyncEnabled === '0' || $autoSyncEnabled === 'false') {
            return; // Auto-sync is disabled
        }
    } catch (Exception $e) {
        // If setting doesn't exist, assume enabled
    }
    
    try {
        // Get all active API keys with 'finance' scope
        $stmt = $pdo->prepare("
            SELECT id, api_key, scopes 
            FROM pos_api_keys 
            WHERE is_active = 1 
            AND (expires_at IS NULL OR expires_at > NOW())
            AND JSON_CONTAINS(scopes, '\"finance\"', '$')
        ");
        $stmt->execute();
        $apiKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($apiKeys)) {
            return; // No active API keys with finance scope
        }
        
        // Fetch transaction data
        $stmt = $pdo->prepare("
            SELECT * FROM financial_transactions WHERE id = ?
        ");
        $stmt->execute([$transactionId]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$transaction) {
            return; // Transaction not found
        }
        
        // If async, use background process (non-blocking)
        if ($async) {
            register_shutdown_function(function() use ($pdo, $transaction, $apiKeys) {
                // Ensure we have a connection
                try {
                    syncTransactionToPOSInternal($pdo, $transaction, $apiKeys);
                } catch (Exception $e) {
                    error_log("Async sync transaction error: " . $e->getMessage());
                }
            });
        } else {
            syncTransactionToPOSInternal($pdo, $transaction, $apiKeys);
        }
    } catch (Exception $e) {
        error_log("Sync Transaction to POS Error: " . $e->getMessage());
    }
}

/**
 * Internal function to sync transaction to POS (called synchronously or asynchronously)
 */
function syncTransactionToPOSInternal(PDO $pdo, array $transaction, array $apiKeys): void
{
    foreach ($apiKeys as $apiKeyData) {
        try {
            // Get POS endpoint from system settings or use default
            $posEndpoint = getSystemSetting($pdo, 'pos_sync_endpoint') ?: null;
            
            if (!$posEndpoint) {
                // If no endpoint configured, skip sync
                continue;
            }
            
            // Prepare sync data
            $syncData = [
                'transaction_id' => $transaction['id'],
                'transaction_type' => $transaction['transaction_type'],
                'reference_type' => $transaction['reference_type'],
                'reference_id' => $transaction['reference_id'],
                'amount' => $transaction['amount'],
                'currency' => $transaction['currency'],
                'description' => $transaction['description'],
                'transaction_date' => $transaction['transaction_date']
            ];
            
            // Send to POS system via webhook/API
            $ch = curl_init($posEndpoint . '/api/sync/transaction');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($syncData),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-API-Key: ' . $apiKeyData['api_key'],
                    'X-Sync-Source: ecommerce'
                ],
                CURLOPT_TIMEOUT => 5, // 5 second timeout
                CURLOPT_CONNECTTIMEOUT => 2
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            // Log sync attempt
            logApiRequest(
                $pdo,
                $apiKeyData['id'],
                $apiKeyData['api_key'],
                'finance',
                'POST',
                '/api/sync/transaction',
                $_SERVER['REMOTE_ADDR'] ?? null,
                'Auto-Sync',
                $httpCode,
                null,
                $error ?: null,
                $syncData,
                $response ? substr($response, 0, 200) : null
            );
            
        } catch (Exception $e) {
            error_log("POS Sync Error for API Key {$apiKeyData['id']}: " . $e->getMessage());
        }
    }
}

/**
 * Get system setting value (helper function)
 */
function getSystemSetting(PDO $pdo, string $key): ?string
{
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['setting_value'] : null;
    } catch (Exception $e) {
        error_log("Failed to get system setting {$key}: " . $e->getMessage());
        return null;
    }
}
