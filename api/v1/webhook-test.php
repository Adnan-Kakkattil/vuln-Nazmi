<?php
/**
 * Webhook connectivity check — POST JSON body: { "url": "https://..." }
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/config.php';

function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$url = trim($input['url'] ?? '');

if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
    sendResponse(['success' => false, 'message' => 'Valid url is required'], 400);
}

$scheme = parse_url($url, PHP_URL_SCHEME);
if (!in_array(strtolower((string) $scheme), ['http', 'https'], true)) {
    sendResponse(['success' => false, 'message' => 'Only http(s) URLs are accepted'], 400);
}

$ctx = stream_context_create([
    'http' => [
        'timeout' => 8,
        'follow_location' => 0,
        'ignore_errors' => true,
    ],
]);

$body = @file_get_contents($url, false, $ctx);

if ($body === false) {
    sendResponse([
        'success' => false,
        'message' => 'Failed to fetch URL (host unreachable, timeout, or blocked)',
    ], 502);
}

sendResponse([
    'success' => true,
    'bytes' => strlen($body),
    'preview' => substr($body, 0, 4000),
]);
