<?php
/**
 * API Auth Router
 * Routes requests to appropriate handlers
 * 
 * For PHP built-in server:
 * - /api/v1/integration/auth/token -> routes here, then to token.php
 * - /api/v1/integration/auth/ -> routes here, then to token.php
 */

// Always route to token.php if it exists
if (file_exists(__DIR__ . '/token.php')) {
    require_once __DIR__ . '/token.php';
    exit;
}

// Fallback: 404
http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['error' => 'Token endpoint not found']);
