<?php
/**
 * API Keys Management API
 * Endpoint: /api/v1/admin/api-keys.php
 * Methods: GET (list/single), POST (create), PUT (update), DELETE
 */

header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/api_integration_helpers.php';

// Require admin authentication
$currentAdmin = requireAdminAuthOrDie();

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

// Get request method and ID
$method = $_SERVER['REQUEST_METHOD'];
$apiKeyId = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

// Route requests
switch ($method) {
    case 'GET':
        if ($action === 'regenerate_secret' && $apiKeyId) {
            handleRegenerateSecret($pdo, $apiKeyId, $currentAdmin);
        } elseif ($apiKeyId) {
            handleGetApiKey($pdo, $apiKeyId);
        } else {
            handleListApiKeys($pdo);
        }
        break;
        
    case 'POST':
        if ($action === 'regenerate_secret' && $apiKeyId) {
            handleRegenerateSecret($pdo, $apiKeyId, $currentAdmin);
        } else {
            handleCreateApiKey($pdo, $currentAdmin);
        }
        break;
        
    case 'PUT':
        if (!$apiKeyId) {
            sendResponse(['success' => false, 'message' => 'API Key ID is required'], 400);
        }
        handleUpdateApiKey($pdo, $apiKeyId, $currentAdmin);
        break;
        
    case 'DELETE':
        if (!$apiKeyId) {
            sendResponse(['success' => false, 'message' => 'API Key ID is required'], 400);
        }
        handleDeleteApiKey($pdo, $apiKeyId, $currentAdmin);
        break;
        
    default:
        sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

/**
 * List all API keys
 */
function handleListApiKeys($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                pak.*,
                pak.name as key_name,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
                (SELECT COUNT(*) FROM pos_integration_logs WHERE api_key_id = pak.id) as request_count
            FROM pos_api_keys pak
            LEFT JOIN users u ON u.id = pak.created_by
            ORDER BY pak.created_at DESC
        ");
        $stmt->execute();
        $apiKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Decode JSON fields and map columns
        foreach ($apiKeys as &$key) {
            // Map name to key_name for frontend compatibility
            $key['key_name'] = $key['name'];
            
            if ($key['scopes']) {
                $key['scopes'] = json_decode($key['scopes'], true) ?: [];
            } else {
                $key['scopes'] = [];
            }
            
            // Parse description to extract tenant info if needed
            if (!empty($key['description'])) {
                // Try to extract tenant_name and tenant_id from description
                if (preg_match('/Tenant:\s*(.+?)(?:\n|$)/i', $key['description'], $matches)) {
                    $key['tenant_name'] = trim($matches[1]);
                }
                if (preg_match('/Tenant ID:\s*(.+?)(?:\n|$)/i', $key['description'], $matches)) {
                    $key['tenant_id'] = trim($matches[1]);
                }
                if (preg_match('/Allowed IPs:\s*(.+?)(?:\n\n|$)/i', $key['description'], $matches)) {
                    $key['allowed_ips'] = trim($matches[1]);
                }
            }
            
            // Don't expose the secret hash
            unset($key['api_secret_hash']);
        }
        
        sendResponse([
            'success' => true,
            'data' => $apiKeys
        ]);
        
    } catch (PDOException $e) {
        error_log("List API Keys Error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Failed to fetch API keys'
        ], 500);
    }
}

/**
 * Get single API key
 */
function handleGetApiKey($pdo, $apiKeyId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM pos_api_keys WHERE id = ?");
        $stmt->execute([$apiKeyId]);
        $apiKey = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$apiKey) {
            sendResponse([
                'success' => false,
                'message' => 'API key not found'
            ], 404);
        }
        
        // Map name to key_name for frontend compatibility
        $apiKey['key_name'] = $apiKey['name'];
        
        // Decode JSON fields
        if ($apiKey['scopes']) {
            $apiKey['scopes'] = json_decode($apiKey['scopes'], true) ?: [];
        } else {
            $apiKey['scopes'] = [];
        }
        
        // Parse description to extract tenant info if needed
        if (!empty($apiKey['description'])) {
            if (preg_match('/Tenant:\s*(.+?)(?:\n|$)/i', $apiKey['description'], $matches)) {
                $apiKey['tenant_name'] = trim($matches[1]);
            }
            if (preg_match('/Tenant ID:\s*(.+?)(?:\n|$)/i', $apiKey['description'], $matches)) {
                $apiKey['tenant_id'] = trim($matches[1]);
            }
            if (preg_match('/Allowed IPs:\s*(.+?)(?:\n\n|$)/i', $apiKey['description'], $matches)) {
                $apiKey['allowed_ips'] = trim($matches[1]);
            }
            // Extract notes (everything after double newline)
            if (preg_match('/\n\n(.+)$/s', $apiKey['description'], $matches)) {
                $apiKey['notes'] = trim($matches[1]);
            }
        }
        
        // Don't expose the secret hash
        unset($apiKey['api_secret_hash']);
        
        sendResponse([
            'success' => true,
            'data' => $apiKey
        ]);
        
    } catch (PDOException $e) {
        error_log("Get API Key Error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Failed to fetch API key'
        ], 500);
    }
}

/**
 * Create new API key
 */
function handleCreateApiKey($pdo, $currentAdmin) {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $keyName = trim($input['key_name'] ?? '');
    $tenantId = !empty($input['tenant_id']) ? trim($input['tenant_id']) : null;
    $tenantName = !empty($input['tenant_name']) ? trim($input['tenant_name']) : null;
    $expiresAt = !empty($input['expires_at']) ? $input['expires_at'] : null;
    $allowedIps = !empty($input['allowed_ips']) ? trim($input['allowed_ips']) : null;
    $scopes = $input['scopes'] ?? [];
    $notes = !empty($input['notes']) ? trim($input['notes']) : null;
    
    // Validation
    if (empty($keyName)) {
        sendResponse([
            'success' => false,
            'message' => 'Key name is required'
        ], 400);
    }
    
    try {
        // Generate API key and secret
        $apiKey = generateApiKey();
        $apiSecret = generateApiSecret();
        $hashedSecret = hashApiSecret($apiSecret);
        
        // Store in database
        // Map key_name to name, and combine tenant/notes into description
        $description = '';
        if ($tenantName) {
            $description .= "Tenant: {$tenantName}";
        }
        if ($tenantId) {
            $description .= ($description ? "\n" : '') . "Tenant ID: {$tenantId}";
        }
        if ($allowedIps) {
            $description .= ($description ? "\n" : '') . "Allowed IPs: {$allowedIps}";
        }
        if ($notes) {
            $description .= ($description ? "\n\n" : '') . $notes;
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO pos_api_keys 
            (name, api_key, api_secret_hash, description, scopes, expires_at, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $keyName,
            $apiKey,
            $hashedSecret,
            !empty($description) ? $description : null,
            !empty($scopes) ? json_encode($scopes) : null,
            $expiresAt ?: null,
            $currentAdmin['id']
        ]);
        
        $newApiKeyId = $pdo->lastInsertId();
        
        // Get created API key
        $stmt = $pdo->prepare("SELECT * FROM pos_api_keys WHERE id = ?");
        $stmt->execute([$newApiKeyId]);
        $createdKey = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendResponse([
            'success' => true,
            'message' => 'API key created successfully',
            'data' => [
                'id' => $createdKey['id'],
                'api_key' => $apiKey,
                'api_secret' => $apiSecret, // Only shown once
                'key_name' => $createdKey['name'],
                'name' => $createdKey['name']
            ]
        ], 201);
        
    } catch (PDOException $e) {
        error_log("Create API Key Error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Failed to create API key'
        ], 500);
    }
}

/**
 * Update API key
 */
function handleUpdateApiKey($pdo, $apiKeyId, $currentAdmin) {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    // Get current API key
    $stmt = $pdo->prepare("SELECT * FROM pos_api_keys WHERE id = ?");
    $stmt->execute([$apiKeyId]);
    $currentKey = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$currentKey) {
        sendResponse([
            'success' => false,
            'message' => 'API key not found'
        ], 404);
    }
    
    // Update fields
    $keyName = isset($input['key_name']) ? trim($input['key_name']) : $currentKey['name'];
    $tenantId = isset($input['tenant_id']) ? (!empty($input['tenant_id']) ? trim($input['tenant_id']) : null) : null;
    $tenantName = isset($input['tenant_name']) ? (!empty($input['tenant_name']) ? trim($input['tenant_name']) : null) : null;
    $expiresAt = isset($input['expires_at']) ? ($input['expires_at'] ?: null) : $currentKey['expires_at'];
    $allowedIps = isset($input['allowed_ips']) ? (!empty($input['allowed_ips']) ? trim($input['allowed_ips']) : null) : null;
    $scopes = isset($input['scopes']) ? $input['scopes'] : json_decode($currentKey['scopes'], true);
    $notes = isset($input['notes']) ? (!empty($input['notes']) ? trim($input['notes']) : null) : null;
    $isActive = isset($input['is_active']) ? intval($input['is_active']) : $currentKey['is_active'];
    
    // Validation
    if (empty($keyName)) {
        sendResponse([
            'success' => false,
            'message' => 'Key name is required'
        ], 400);
    }
    
    // Combine tenant/notes into description
    $description = '';
    if ($tenantName) {
        $description .= "Tenant: {$tenantName}";
    }
    if ($tenantId) {
        $description .= ($description ? "\n" : '') . "Tenant ID: {$tenantId}";
    }
    if ($allowedIps) {
        $description .= ($description ? "\n" : '') . "Allowed IPs: {$allowedIps}";
    }
    if ($notes) {
        $description .= ($description ? "\n\n" : '') . $notes;
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE pos_api_keys SET
                name = ?,
                description = ?,
                scopes = ?,
                expires_at = ?,
                is_active = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $keyName,
            !empty($description) ? $description : null,
            !empty($scopes) ? json_encode($scopes) : null,
            $expiresAt,
            $isActive,
            $apiKeyId
        ]);
        
        // Get updated API key
        $stmt = $pdo->prepare("SELECT * FROM pos_api_keys WHERE id = ?");
        $stmt->execute([$apiKeyId]);
        $updatedKey = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Map name to key_name for frontend compatibility
        $updatedKey['key_name'] = $updatedKey['name'];
        
        if ($updatedKey['scopes']) {
            $updatedKey['scopes'] = json_decode($updatedKey['scopes'], true) ?: [];
        }
        
        // Parse description to extract tenant info if needed
        if (!empty($updatedKey['description'])) {
            if (preg_match('/Tenant:\s*(.+?)(?:\n|$)/i', $updatedKey['description'], $matches)) {
                $updatedKey['tenant_name'] = trim($matches[1]);
            }
            if (preg_match('/Tenant ID:\s*(.+?)(?:\n|$)/i', $updatedKey['description'], $matches)) {
                $updatedKey['tenant_id'] = trim($matches[1]);
            }
            if (preg_match('/Allowed IPs:\s*(.+?)(?:\n\n|$)/i', $updatedKey['description'], $matches)) {
                $updatedKey['allowed_ips'] = trim($matches[1]);
            }
            if (preg_match('/\n\n(.+)$/s', $updatedKey['description'], $matches)) {
                $updatedKey['notes'] = trim($matches[1]);
            }
        }
        
        unset($updatedKey['api_secret_hash']);
        
        sendResponse([
            'success' => true,
            'message' => 'API key updated successfully',
            'data' => $updatedKey
        ]);
        
    } catch (PDOException $e) {
        error_log("Update API Key Error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Failed to update API key'
        ], 500);
    }
}

/**
 * Regenerate API secret
 */
function handleRegenerateSecret($pdo, $apiKeyId, $currentAdmin) {
    try {
        // Check if API key exists
        $stmt = $pdo->prepare("SELECT api_key FROM pos_api_keys WHERE id = ?");
        $stmt->execute([$apiKeyId]);
        $apiKey = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$apiKey) {
            sendResponse([
                'success' => false,
                'message' => 'API key not found'
            ], 404);
        }
        
        // Generate new secret
        $newSecret = generateApiSecret();
        $hashedSecret = hashApiSecret($newSecret);
        
        // Update secret
        $stmt = $pdo->prepare("UPDATE pos_api_keys SET api_secret_hash = ? WHERE id = ?");
        $stmt->execute([$hashedSecret, $apiKeyId]);
        
        sendResponse([
            'success' => true,
            'message' => 'API secret regenerated successfully',
            'data' => [
                'api_key' => $apiKey['api_key'],
                'api_secret' => $newSecret // Only shown once
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("Regenerate Secret Error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Failed to regenerate secret'
        ], 500);
    }
}

/**
 * Delete API key
 */
function handleDeleteApiKey($pdo, $apiKeyId, $currentAdmin) {
    try {
        // Check if API key exists
        $stmt = $pdo->prepare("SELECT * FROM pos_api_keys WHERE id = ?");
        $stmt->execute([$apiKeyId]);
        $apiKey = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$apiKey) {
            sendResponse([
                'success' => false,
                'message' => 'API key not found'
            ], 404);
        }
        
        // Delete API key
        $stmt = $pdo->prepare("DELETE FROM pos_api_keys WHERE id = ?");
        $stmt->execute([$apiKeyId]);
        
        sendResponse([
            'success' => true,
            'message' => 'API key deleted successfully'
        ]);
        
    } catch (PDOException $e) {
        error_log("Delete API Key Error: " . $e->getMessage());
        sendResponse([
            'success' => false,
            'message' => 'Failed to delete API key'
        ], 500);
    }
}
