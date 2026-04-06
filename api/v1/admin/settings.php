<?php
/**
 * Admin Settings API
 * Endpoint: /api/v1/admin/settings.php
 * Manages system settings for the admin panel
 *
 * Methods:
 * - GET: Retrieve all settings or settings by category
 * - PUT: Update settings
 */

header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../../../includes/config.php';

// Database connection
$pdo = getDbConnection();

// Require admin authentication
$admin = requireAdminAuthOrDie();

/**
 * Send JSON response
 */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

/**
 * Convert setting value based on type
 */
function convertSettingValue($value, $type) {
    switch ($type) {
        case 'boolean':
            return $value === '1' || $value === 'true' || $value === true;
        case 'number':
            return is_numeric($value) ? floatval($value) : 0;
        case 'json':
            return json_decode($value, true);
        default:
            return $value;
    }
}

/**
 * Prepare value for storage
 */
function prepareValueForStorage($value, $type) {
    switch ($type) {
        case 'boolean':
            return ($value === true || $value === '1' || $value === 'true') ? '1' : '0';
        case 'json':
            return is_array($value) ? json_encode($value) : $value;
        default:
            return strval($value);
    }
}

try {
    switch ($method) {
        case 'GET':
            handleGetSettings($pdo);
            break;
            
        case 'PUT':
            handleUpdateSettings($pdo, $admin);
            break;
            
        default:
            sendResponse([
                'success' => false,
                'message' => 'Method not allowed'
            ], 405);
    }
    
} catch (PDOException $e) {
    error_log("Settings API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ], 500);
} catch (Exception $e) {
    error_log("Settings API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage()
    ], 500);
}

/**
 * Get all settings or filter by category
 */
function handleGetSettings($pdo) {
    $category = $_GET['category'] ?? null;
    
    if ($category) {
        $stmt = $pdo->prepare("
            SELECT setting_key, setting_value, setting_type, description, category
            FROM system_settings
            WHERE category = ?
            ORDER BY id ASC
        ");
        $stmt->execute([$category]);
    } else {
        $stmt = $pdo->query("
            SELECT setting_key, setting_value, setting_type, description, category
            FROM system_settings
            ORDER BY category ASC, id ASC
        ");
    }
    
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group settings by category and convert values
    $grouped = [];
    $flat = [];
    
    foreach ($settings as $setting) {
        $convertedValue = convertSettingValue($setting['setting_value'], $setting['setting_type']);
        
        // Flat format for easy access
        $flat[$setting['setting_key']] = $convertedValue;
        
        // Grouped format by category
        if (!isset($grouped[$setting['category']])) {
            $grouped[$setting['category']] = [];
        }
        $grouped[$setting['category']][] = [
            'key' => $setting['setting_key'],
            'value' => $convertedValue,
            'type' => $setting['setting_type'],
            'description' => $setting['description']
        ];
    }
    
    sendResponse([
        'success' => true,
        'data' => [
            'settings' => $flat,
            'grouped' => $grouped
        ]
    ]);
}

/**
 * Update multiple settings at once
 */
function handleUpdateSettings($pdo, $admin) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data) || !is_array($data)) {
        sendResponse([
            'success' => false,
            'message' => 'No settings data provided'
        ], 400);
    }
    
    // Handle both flat settings and nested "settings" key
    $settings = isset($data['settings']) ? $data['settings'] : $data;
    
    $pdo->beginTransaction();
    
    try {
        $updateStmt = $pdo->prepare("
            UPDATE system_settings 
            SET setting_value = ?, updated_by = ?, updated_at = NOW()
            WHERE setting_key = ?
        ");
        
        // Get current setting types for proper value conversion
        $typeStmt = $pdo->prepare("SELECT setting_key, setting_type FROM system_settings WHERE setting_key = ?");
        
        $updatedCount = 0;
        $updatedKeys = [];
        
        foreach ($settings as $key => $value) {
            // Get the setting type
            $typeStmt->execute([$key]);
            $settingInfo = $typeStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($settingInfo) {
                $preparedValue = prepareValueForStorage($value, $settingInfo['setting_type']);
                $updateStmt->execute([$preparedValue, $admin['id'], $key]);
                
                if ($updateStmt->rowCount() > 0) {
                    $updatedCount++;
                    $updatedKeys[] = $key;
                }
            }
        }
        
        // Log the activity
        $logStmt = $pdo->prepare("
            INSERT INTO admin_activity_log (user_id, action, module, entity_type, changes)
            VALUES (?, 'update', 'settings', 'system_settings', ?)
        ");
        $logStmt->execute([
            $admin['id'],
            json_encode(['updated_keys' => $updatedKeys])
        ]);
        
        $pdo->commit();
        
        sendResponse([
            'success' => true,
            'message' => "Successfully updated {$updatedCount} setting(s)",
            'updated_keys' => $updatedKeys
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
