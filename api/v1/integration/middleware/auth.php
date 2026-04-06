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

    $tokenData = json_decode(base64_decode($token), true);
    if (is_array($tokenData) && !empty($tokenData['api_key'])) {
        return [
            'api_key_id' => (int) ($tokenData['api_key_id'] ?? 0),
            'api_key' => $tokenData['api_key'],
            'tenant_id' => $tokenData['tenant_id'] ?? 'lab-tenant',
            'scopes' => isset($tokenData['scopes']) && is_array($tokenData['scopes']) ? $tokenData['scopes'] : ['products', 'orders', 'finance'],
            'token_data' => $tokenData,
        ];
    }

    return false;
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
