<?php
/**
 * Integration API Authentication Middleware
 * Validates Bearer tokens for POS integration requests
 */

// Load config and helpers
if (!function_exists('getDbConnection')) {
    require_once __DIR__ . '/../../../../includes/config.php';
    require_once __DIR__ . '/../../../../includes/api_integration_helpers.php';
}

/**
 * Validate Bearer token from Authorization header
 * @return array|false Returns API key data if valid, false otherwise
 */
function validateIntegrationToken() {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    
    // Extract token from "Bearer {token}" format
    if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        $token = $matches[1];
    } else {
        return false;
    }
    
    if (empty($token)) {
        return false;
    }
    
    try {
        // Decode token (simplified - use proper JWT library in production)
        $tokenData = json_decode(base64_decode($token), true);
        
        if (!$tokenData || !isset($tokenData['api_key'])) {
            return false;
        }
        
        // Check expiration
        if (isset($tokenData['exp']) && $tokenData['exp'] < time()) {
            return false;
        }
        
        // Get API key from database
        $pdo = getDbConnection();
        $keyData = isApiKeyValid($pdo, $tokenData['api_key']);
        
        if (!$keyData) {
            return false;
        }
        
        // Check scopes if needed
        $requestedScopes = $tokenData['scopes'] ?? [];
        
        return [
            'api_key_id' => $keyData['id'],
            'api_key' => $keyData['api_key'],
            'tenant_id' => $keyData['tenant_id'],
            'scopes' => $keyData['scopes'] ? json_decode($keyData['scopes'], true) : [],
            'token_data' => $tokenData
        ];
        
    } catch (Exception $e) {
        error_log('Token validation error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Require valid integration token - exit if invalid
 * @return array API key data
 */
function requireIntegrationAuth() {
    $authData = validateIntegrationToken();
    
    if ($authData === false) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Invalid or expired token',
            'message' => 'Authentication required'
        ]);
        exit;
    }
    
    return $authData;
}

/**
 * Check if token has required scope
 * @param array $authData Authentication data from requireIntegrationAuth()
 * @param string $requiredScope Required scope (products, orders, finance)
 * @return bool
 */
function hasScope(array $authData, string $requiredScope): bool {
    $scopes = $authData['scopes'] ?? [];
    
    // If no scopes defined, allow all (backward compatibility)
    if (empty($scopes)) {
        return true;
    }
    
    return in_array($requiredScope, $scopes, true);
}

/**
 * Require specific scope - exit if not available
 * @param array $authData Authentication data
 * @param string $requiredScope Required scope
 */
function requireScope(array $authData, string $requiredScope): void {
    if (!hasScope($authData, $requiredScope)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Insufficient permissions',
            'message' => "Scope '{$requiredScope}' is required"
        ]);
        exit;
    }
}
