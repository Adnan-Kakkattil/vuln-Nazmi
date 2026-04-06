<?php
/**
 * API Authentication Token Endpoint
 * Handles authentication for POS system integrations
 * 
 * POST /api/v1/integration/auth/token.php
 * Body: { "api_key": "tiv_xxx", "api_secret": "xxx" }
 * Response: { "token": "jwt_token", "expires_in": 3600 }
 */

header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Include configuration and helpers
require_once __DIR__ . '/../../../../includes/config.php';
require_once __DIR__ . '/../../../../includes/api_integration_helpers.php';

$pdo = getDbConnection();
$startTime = microtime(true);

try {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $apiKey = trim($input['api_key'] ?? $input['client_id'] ?? '');
    $apiSecret = trim($input['api_secret'] ?? $input['client_secret'] ?? '');
    
    if (empty($apiKey) || empty($apiSecret)) {
        throw new Exception('API key and secret are required');
    }
    
    // Validate API key format
    if (!isValidApiKeyFormat($apiKey)) {
        logApiRequest($pdo, null, $apiKey, 'auth', 'POST', '/api/v1/integration/auth/token', 
                     $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null, 
                     401, null, 'Invalid API key format');
        http_response_code(401);
        echo json_encode(['error' => 'Invalid API key format']);
        exit;
    }
    
    // Get API key from database
    $keyData = isApiKeyValid($pdo, $apiKey);
    
    if (!$keyData) {
        logApiRequest($pdo, null, $apiKey, 'auth', 'POST', '/api/v1/integration/auth/token', 
                     $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null, 
                     401, null, 'API key not found or inactive');
        http_response_code(401);
        echo json_encode(['error' => 'Invalid API key']);
        exit;
    }
    
    // Check IP whitelist (if allowed_ips is set)
    $allowedIps = $keyData['allowed_ips'] ?? null;
    if ($allowedIps && !isIpAllowed($allowedIps, $_SERVER['REMOTE_ADDR'] ?? '')) {
        logApiRequest($pdo, $keyData['id'], $apiKey, 'auth', 'POST', '/api/v1/integration/auth/token', 
                     $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null, 
                     403, null, 'IP address not allowed');
        http_response_code(403);
        echo json_encode(['error' => 'IP address not allowed']);
        exit;
    }
    
    // Verify API secret
    $storedSecretHash = $keyData['api_secret'] ?? null;
    if (!$storedSecretHash) {
        logApiRequest($pdo, $keyData['id'], $apiKey, 'auth', 'POST', '/api/v1/integration/auth/token', 
                     $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null, 
                     401, null, 'API secret not found in database');
        http_response_code(401);
        echo json_encode(['error' => 'Invalid API configuration']);
        exit;
    }
    
    if (!verifyApiSecret($apiSecret, $storedSecretHash)) {
        logApiRequest($pdo, $keyData['id'], $apiKey, 'auth', 'POST', '/api/v1/integration/auth/token', 
                     $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null, 
                     401, null, 'Invalid API secret');
        http_response_code(401);
        echo json_encode(['error' => 'Invalid API secret']);
        exit;
    }
    
    // Generate JWT token (simplified - use proper JWT library in production)
    $scopes = [];
    if (!empty($keyData['scopes'])) {
        $decodedScopes = json_decode($keyData['scopes'], true);
        $scopes = is_array($decodedScopes) ? $decodedScopes : [];
    }
    
    $tokenPayload = [
        'api_key_id' => $keyData['id'],
        'api_key' => $apiKey,
        'tenant_id' => $keyData['tenant_id'] ?? null,
        'scopes' => $scopes,
        'iat' => time(),
        'exp' => time() + 3600 // 1 hour expiration
    ];
    
    // Simple token generation (use proper JWT in production)
    $token = base64_encode(json_encode($tokenPayload));
    
    // Update last used timestamp
    updateApiKeyLastUsed($pdo, $apiKey);
    
    // Calculate response time
    $responseTime = (int)((microtime(true) - $startTime) * 1000);
    
    // Log successful authentication
    logApiRequest($pdo, $keyData['id'], $apiKey, 'auth', 'POST', '/api/v1/integration/auth/token', 
                 $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null, 
                 200, $responseTime, null, null, 'Authentication successful');
    
    // Return token
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'token' => $token,
        'expires_in' => 3600,
        'token_type' => 'Bearer'
    ]);
    
} catch (Exception $e) {
    $responseTime = (int)((microtime(true) - $startTime) * 1000);
    
    logApiRequest($pdo, null, $apiKey ?? null, 'auth', 'POST', '/api/v1/integration/auth/token', 
                 $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null, 
                 500, $responseTime, $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}
