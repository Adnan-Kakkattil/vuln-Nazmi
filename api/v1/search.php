<?php
/**
 * Advanced Search API
 * Endpoint: /api/v1/search.php
 * Provides Amazon/Flipkart-style search functionality with autocomplete
 * 
 * Methods:
 * - GET ?q={query}&type=autocomplete - Get search suggestions
 * - GET ?q={query} - Full search with filters and pagination
 */

header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../../includes/config.php';

// Database connection
$pdo = getDbConnection();

// Start session for rate limiting
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Send JSON response
 */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

/**
 * Rate limiting for search requests
 */
function checkSearchRateLimit($key, $maxAttempts = 30, $windowSeconds = 60) {
    $cacheFile = sys_get_temp_dir() . '/search_rate_limit_' . md5($key) . '.json';
    
    $data = [];
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true) ?? [];
    }
    
    $now = time();
    $windowStart = $now - $windowSeconds;
    
    // Filter out old attempts
    $data = array_filter($data, function($timestamp) use ($windowStart) {
        return $timestamp > $windowStart;
    });
    
    if (count($data) >= $maxAttempts) {
        return false;
    }
    
    $data[] = $now;
    file_put_contents($cacheFile, json_encode($data));
    
    return true;
}

/**
 * Sanitize search query
 */
function sanitizeSearchQuery($query) {
    // Remove dangerous characters but allow spaces and common search characters
    $query = trim($query);
    $query = preg_replace('/[<>"\']/', '', $query); // Remove HTML/script tags
    $query = preg_replace('/\s+/', ' ', $query); // Normalize whitespace
    $query = mb_substr($query, 0, 100); // Limit length
    
    return $query;
}

/**
 * Get autocomplete suggestions
 */
function getAutocompleteSuggestions($pdo, $query, $limit = 10) {
    if (empty($query) || strlen($query) < 2) {
        return [];
    }
    
    $sanitizedQuery = sanitizeSearchQuery($query);
    $searchTerm = '%' . $sanitizedQuery . '%';
    
    // Search in product names, descriptions, SKU, and boutique specifications (most relevant)
    // Note: Using GROUP BY instead of DISTINCT to avoid SQL error with ORDER BY columns not in SELECT
    // Using different parameter names for each occurrence to avoid binding conflicts
    $exactTerm = $sanitizedQuery . '%';
    
    $stmt = $pdo->prepare("
        SELECT p.name, p.slug, p.id, c.name as category_name, p.short_description, p.view_count, p.sold_count
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'active' 
        AND (
            p.name LIKE :search1
            OR p.short_description LIKE :search2
            OR p.sku LIKE :search3
            OR c.name LIKE :search4
            OR EXISTS (
                SELECT 1 FROM product_specifications ps 
                WHERE ps.product_id = p.id 
                AND (
                    ps.spec_key LIKE :search5 
                    OR ps.spec_value LIKE :search6
                )
            )
        )
        GROUP BY p.id, p.name, p.slug, c.name, p.short_description, p.view_count, p.sold_count
        ORDER BY 
            CASE 
                WHEN p.name LIKE :exact THEN 1
                WHEN p.name LIKE :start THEN 2
                WHEN p.short_description LIKE :search_order THEN 3
                ELSE 4
            END,
            p.view_count DESC,
            p.sold_count DESC
        LIMIT :limit
    ");
    
    // Bind all search parameters
    $stmt->bindValue(':search1', $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(':search2', $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(':search3', $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(':search4', $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(':search5', $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(':search6', $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(':search_order', $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(':exact', $exactTerm, PDO::PARAM_STR);
    $stmt->bindValue(':start', $exactTerm, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $suggestions = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $suggestions[] = [
            'id' => intval($row['id']),
            'name' => $row['name'],
            'slug' => $row['slug'],
            'category' => $row['category_name'] ?? '',
            'type' => 'product'
        ];
    }
    
    // Also search in categories
    $catStmt = $pdo->prepare("
        SELECT name, slug
        FROM categories
        WHERE is_active = 1 
        AND name LIKE :search
        LIMIT 5
    ");
    $catStmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
    $catStmt->execute();
    
    while ($row = $catStmt->fetch(PDO::FETCH_ASSOC)) {
        $suggestions[] = [
            'name' => $row['name'],
            'slug' => $row['slug'],
            'type' => 'category'
        ];
    }
    
    return array_slice($suggestions, 0, $limit);
}

/**
 * Perform full search with filters
 */
function performSearch($pdo, $query, $filters = [], $page = 1, $perPage = 20) {
    $sanitizedQuery = sanitizeSearchQuery($query);
    
    if (empty($sanitizedQuery) || strlen($sanitizedQuery) < 2) {
        return [
            'products' => [],
            'total' => 0,
            'suggestions' => []
        ];
    }
    
    // Build WHERE conditions
    $whereConditions = ["p.status = 'active'"];

    // Search in multiple fields with relevance scoring
    // Search in: product name, description, SKU, category name, and boutique-specific specifications
    // Using different parameter names for each occurrence to avoid binding conflicts
    $searchTerm = '%' . $sanitizedQuery . '%';
    $exactTerm = $sanitizedQuery . '%';
    
    $whereConditions[] = "(
        p.name LIKE :search_where1 
        OR p.short_description LIKE :search_where2 
        OR p.full_description LIKE :search_where3
        OR p.sku LIKE :search_where4
        OR c.name LIKE :search_where5
        OR EXISTS (
            SELECT 1 FROM product_specifications ps 
            WHERE ps.product_id = p.id 
            AND (
                ps.spec_key LIKE :search_where6 
                OR ps.spec_value LIKE :search_where7
            )
        )
    )";
    // WHERE-only params (COUNT query must not bind ORDER BY placeholders — native PDO throws HY093)
    $whereParams = [
        ':search_where1' => $searchTerm,
        ':search_where2' => $searchTerm,
        ':search_where3' => $searchTerm,
        ':search_where4' => $searchTerm,
        ':search_where5' => $searchTerm,
        ':search_where6' => $searchTerm,
        ':search_where7' => $searchTerm,
    ];
    
    // Category filter
    if (!empty($filters['category_id'])) {
        $whereConditions[] = "p.category_id = :category_id";
        $whereParams[':category_id'] = intval($filters['category_id']);
    }
    
    // Price range filter
    if (!empty($filters['min_price'])) {
        $whereConditions[] = "p.price >= :min_price";
        $whereParams[':min_price'] = floatval($filters['min_price']);
    }
    if (!empty($filters['max_price'])) {
        $whereConditions[] = "p.price <= :max_price";
        $whereParams[':max_price'] = floatval($filters['max_price']);
    }
    
    // In stock filter
    if (isset($filters['in_stock']) && $filters['in_stock'] === true) {
        $whereConditions[] = "p.stock_quantity > 0";
    }
    
    // Featured filter
    if (isset($filters['featured']) && $filters['featured'] === true) {
        $whereConditions[] = "p.is_featured = 1";
    }
    
    $whereClause = "WHERE " . implode(" AND ", $whereConditions);
    
    // Build ORDER BY; for relevance, CASE must appear in SELECT (not only ORDER BY) — MySQL DISTINCT + ORDER BY rule (3065)
    $sort = $filters['sort'] ?? 'relevance';
    $orderByMap = [
        'relevance' => 'relevance_rank ASC, p.view_count DESC, p.sold_count DESC, p.created_at DESC',
        'price_asc' => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'newest' => 'p.created_at DESC',
        'popularity' => 'p.view_count DESC, p.sold_count DESC',
        'name' => 'p.name ASC'
    ];
    if (!array_key_exists($sort, $orderByMap)) {
        $sort = 'relevance';
    }
    $orderBy = $orderByMap[$sort];

    $relevanceSelectSql = '';
    if ($sort === 'relevance') {
        $relevanceSelectSql = ", (
            CASE 
                WHEN p.name LIKE :exact THEN 1
                WHEN p.name LIKE :start THEN 2
                WHEN p.short_description LIKE :search_order THEN 3
                ELSE 4
            END
        ) AS relevance_rank";
    }

    $orderParams = [];
    if ($sort === 'relevance') {
        $orderParams = [
            ':exact' => $exactTerm,
            ':start' => $exactTerm,
            ':search_order' => $searchTerm,
        ];
    }

    // Calculate offset
    $offset = ($page - 1) * $perPage;

    // Get total count (only WHERE placeholders)
    $countQuery = "
        SELECT COUNT(DISTINCT p.id) as total
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        {$whereClause}
    ";

    $countStmt = $pdo->prepare($countQuery);
    foreach ($whereParams as $key => $value) {
        $countStmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $countStmt->execute();
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get products
    $query = "
        SELECT DISTINCT
            p.id,
            p.sku,
            p.name,
            p.slug,
            p.short_description,
            p.price,
            p.original_price,
            p.discount_percentage,
            p.stock_quantity,
            p.is_featured,
            p.is_new,
            p.view_count,
            p.sold_count,
            p.created_at,
            p.category_id,
            c.name as category_name,
            c.slug as category_slug,
            (
                SELECT image_url 
                FROM product_images 
                WHERE product_id = p.id 
                AND is_primary = 1 
                LIMIT 1
            ) as primary_image
            {$relevanceSelectSql}
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        {$whereClause}
        ORDER BY {$orderBy}
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $pdo->prepare($query);
    foreach ($whereParams as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    foreach ($orderParams as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Transform products
    $transformedProducts = [];
    foreach ($products as $product) {
        $discountPercentage = $product['discount_percentage'];
        if (!$discountPercentage && $product['original_price'] && $product['original_price'] > $product['price']) {
            $discountPercentage = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
        }
        
        $transformedProducts[] = [
            'id' => intval($product['id']),
            'sku' => $product['sku'],
            'name' => $product['name'],
            'slug' => $product['slug'],
            'description' => $product['short_description'] ?? '',
            'price' => floatval($product['price']),
            'original_price' => $product['original_price'] ? floatval($product['original_price']) : null,
            'discount_percentage' => $discountPercentage ? intval($discountPercentage) : null,
            'stock_quantity' => intval($product['stock_quantity']),
            'is_featured' => (bool)$product['is_featured'],
            'is_new' => (bool)$product['is_new'],
            'in_stock' => intval($product['stock_quantity']) > 0,
            'category' => [
                'id' => $product['category_id'] ? intval($product['category_id']) : null,
                'name' => $product['category_name'] ?? '',
                'slug' => $product['category_slug'] ?? ''
            ],
            'image' => $product['primary_image'] ?? null,
            'stats' => [
                'views' => intval($product['view_count'] ?? 0),
                'sold' => intval($product['sold_count'] ?? 0)
            ]
        ];
    }
    
    return [
        'products' => $transformedProducts,
        'total' => intval($total),
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => ceil($total / $perPage)
    ];
}

// Only GET method allowed
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') {
    sendResponse([
        'success' => false,
        'message' => 'Method not allowed'
    ], 405);
}

try {
    // Rate limiting
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rateLimitKey = 'search_' . $clientIp;
    
    if (!checkSearchRateLimit($rateLimitKey, 30, 60)) {
        sendResponse([
            'success' => false,
            'message' => 'Too many search requests. Please wait a moment.'
        ], 429);
    }

    // Get query parameter
    $query = $_GET['q'] ?? $_GET['query'] ?? '';
    $searchType = $_GET['type'] ?? 'full'; // 'autocomplete' or 'full'
    
    // Validate query
    if (empty($query)) {
        sendResponse([
            'success' => false,
            'message' => 'Search query is required'
        ], 400);
    }
    
    // Sanitize query
    $sanitizedQuery = sanitizeSearchQuery($query);
    
    if (strlen($sanitizedQuery) < 2) {
        sendResponse([
            'success' => false,
            'message' => 'Search query must be at least 2 characters'
        ], 400);
    }
    
    // Handle autocomplete request
    if ($searchType === 'autocomplete') {
        $suggestions = getAutocompleteSuggestions($pdo, $sanitizedQuery, 10);
        
        sendResponse([
            'success' => true,
            'query' => $sanitizedQuery,
            'suggestions' => $suggestions
        ]);
    }
    
    // Handle full search request
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? min(max(1, intval($_GET['per_page'])), 100) : 20;
    
    // Parse filters
    $filters = [
        'category_id' => isset($_GET['category_id']) ? intval($_GET['category_id']) : null,
        'min_price' => isset($_GET['min_price']) ? floatval($_GET['min_price']) : null,
        'max_price' => isset($_GET['max_price']) ? floatval($_GET['max_price']) : null,
        'in_stock' => isset($_GET['in_stock']) ? filter_var($_GET['in_stock'], FILTER_VALIDATE_BOOLEAN) : null,
        'featured' => isset($_GET['featured']) ? filter_var($_GET['featured'], FILTER_VALIDATE_BOOLEAN) : null,
        'sort' => $_GET['sort'] ?? 'relevance'
    ];
    
    // Remove null filters
    $filters = array_filter($filters, function($value) {
        return $value !== null;
    });
    
    $searchResults = performSearch($pdo, $sanitizedQuery, $filters, $page, $perPage);
    
    sendResponse([
        'success' => true,
        'query' => $sanitizedQuery,
        'products' => $searchResults['products'], // Added for frontend compatibility
        'data' => $searchResults['products'], // Keep for backward compatibility
        'meta' => [
            'total' => $searchResults['total'],
            'page' => $searchResults['page'],
            'per_page' => $searchResults['per_page'],
            'total_pages' => $searchResults['total_pages'],
            'has_more' => $searchResults['page'] < $searchResults['total_pages']
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Search API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'Database error occurred'
    ], 500);
} catch (Exception $e) {
    error_log("Search API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'An error occurred'
    ], 500);
}
