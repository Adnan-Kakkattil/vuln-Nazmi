<?php
/**
 * Shipping Methods API
 * Endpoint: /api/v1/shipping.php
 * Returns available shipping methods
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/config.php';

$pdo = getDbConnection();

function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            id,
            name,
            code,
            cost,
            estimated_days_min,
            estimated_days_max
        FROM shipping_methods
        WHERE is_active = 1
        ORDER BY cost ASC
    ");
    $stmt->execute();
    $methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse([
        'success' => true,
        'data' => array_map(function($method) {
            return [
                'id' => intval($method['id']),
                'name' => $method['name'],
                'code' => $method['code'],
                'cost' => floatval($method['cost']),
                'estimated_days' => [
                    'min' => intval($method['estimated_days_min']),
                    'max' => intval($method['estimated_days_max'])
                ],
                'label' => $method['cost'] > 0 
                    ? '₹' . number_format($method['cost'], 0) 
                    : 'Free'
            ];
        }, $methods)
    ]);
    
} catch (Exception $e) {
    error_log("Shipping API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'An error occurred'
    ], 500);
}
