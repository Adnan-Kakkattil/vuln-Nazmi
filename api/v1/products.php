<?php
/**
 * Public Products API
 * Endpoint: /api/v1/products.php
 * Returns products for public/customer access (no authentication required)
 * 
 * Methods:
 * - GET: List products with filters (featured, category, search, etc.)
 * - GET ?id={id}: Get single product with full details (specifications, features, images)
 * - GET ?slug={slug}: Get single product by slug
 */

header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../../includes/config.php';

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

/**
 * Get single product with full details
 */
function getSingleProduct($pdo, $id = null, $slug = null) {
    // Build query based on id or slug
    $whereCondition = "";
    $bindValue = null;
    
    if ($id) {
        $whereCondition = "p.id = ?";
        $bindValue = intval($id);
    } elseif ($slug) {
        $whereCondition = "p.slug = ?";
        $bindValue = $slug;
    } else {
        return null;
    }
    
    // Query product
    $query = "
        SELECT 
            p.*,
            c.name as category_name,
            c.slug as category_slug,
            c.description as category_description
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE {$whereCondition}
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$bindValue]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        return null;
    }
    
    // Get product images
    $imgStmt = $pdo->prepare("
        SELECT id, image_url, alt_text, sort_order, is_primary 
        FROM product_images 
        WHERE product_id = ? 
        ORDER BY is_primary DESC, sort_order ASC
    ");
    $imgStmt->execute([$product['id']]);
    $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get product specifications
    $specStmt = $pdo->prepare("
        SELECT spec_key, spec_value, sort_order 
        FROM product_specifications 
        WHERE product_id = ? 
        ORDER BY sort_order ASC
    ");
    $specStmt->execute([$product['id']]);
    $specs = $specStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get product features
    $featStmt = $pdo->prepare("
        SELECT icon_name, feature_text, sort_order 
        FROM product_features 
        WHERE product_id = ? 
        ORDER BY sort_order ASC
    ");
    $featStmt->execute([$product['id']]);
    $features = $featStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get product reviews summary
    $reviewStmt = $pdo->prepare("
        SELECT 
            COUNT(*) as review_count,
            AVG(rating) as average_rating,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        FROM product_reviews 
        WHERE product_id = ? AND is_approved = 1
    ");
    $reviewStmt->execute([$product['id']]);
    $reviewSummary = $reviewStmt->fetch(PDO::FETCH_ASSOC);
    
    // Calculate discount percentage
    $discountPercentage = $product['discount_percentage'];
    if (!$discountPercentage && $product['original_price'] && $product['original_price'] > $product['price']) {
        $discountPercentage = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
    }
    
    // Transform images
    $transformedImages = [];
    foreach ($images as $img) {
        $transformedImages[] = [
            'id' => intval($img['id']),
            'url' => $img['image_url'],
            'alt' => $img['alt_text'] ?? $product['name'],
            'is_primary' => (bool)$img['is_primary']
        ];
    }
    
    // Transform specifications into object
    $specsObject = [];
    foreach ($specs as $spec) {
        $specsObject[$spec['spec_key']] = $spec['spec_value'];
    }
    
    // Transform features
    $transformedFeatures = [];
    foreach ($features as $feat) {
        $transformedFeatures[] = [
            'icon' => $feat['icon_name'] ?? 'check',
            'text' => $feat['feature_text']
        ];
    }
    
    // Generate default features from specifications if no features exist
    if (empty($transformedFeatures) && !empty($specsObject)) {
        // Create features from boutique specs
        $featureMap = [
            'Material' => 'shirt',
            'Pattern' => 'sparkles',
            'Fit Type' => 'ruler',
            'Style' => 'sparkle',
            'Season' => 'sun',
            'Occasion' => 'calendar',
            'Available Sizes' => 'ruler',
            'Available Colors' => 'palette',
            'Care Instructions' => 'droplet',
            'Brand' => 'tag'
        ];
        
        $featureCount = 0;
        foreach ($specsObject as $key => $value) {
            if ($featureCount >= 4) break; // Limit to 4 features
            if (isset($featureMap[$key]) && !empty($value)) {
                $transformedFeatures[] = [
                    'icon' => $featureMap[$key],
                    'text' => $key . ': ' . $value
                ];
                $featureCount++;
            }
        }
    }
    
    // Increment view count
    $updateViewStmt = $pdo->prepare("UPDATE products SET view_count = view_count + 1 WHERE id = ?");
    $updateViewStmt->execute([$product['id']]);
    
    return [
        'id' => intval($product['id']),
        'sku' => $product['sku'],
        'name' => $product['name'],
        'slug' => $product['slug'],
        'short_description' => $product['short_description'] ?? '',
        'full_description' => $product['full_description'] ?? $product['short_description'] ?? '',
        'price' => floatval($product['price']),
        'original_price' => $product['original_price'] ? floatval($product['original_price']) : null,
        'discount_percentage' => $discountPercentage ? intval($discountPercentage) : null,
        'cost_price' => null, // Don't expose cost price to public
        'stock_quantity' => intval($product['stock_quantity']),
        'low_stock_threshold' => intval($product['low_stock_threshold'] ?? 10),
        'in_stock' => intval($product['stock_quantity']) > 0,
        'status' => $product['status'],
        'is_featured' => (bool)$product['is_featured'],
        'is_new' => (bool)$product['is_new'],
        'warranty_months' => $product['warranty_months'] ? intval($product['warranty_months']) : 12,
        'weight_kg' => $product['weight_kg'] ? floatval($product['weight_kg']) : null,
        'dimensions_cm' => $product['dimensions_cm'],
        'category' => [
            'id' => $product['category_id'] ? intval($product['category_id']) : null,
            'name' => $product['category_name'] ?? '',
            'slug' => $product['category_slug'] ?? '',
            'description' => $product['category_description'] ?? ''
        ],
        'images' => $transformedImages,
        'primary_image' => !empty($transformedImages) ? $transformedImages[0]['url'] : null,
        'specifications' => $specsObject,
        'features' => $transformedFeatures,
        'reviews' => [
            'count' => intval($reviewSummary['review_count'] ?? 0),
            'average' => $reviewSummary['average_rating'] ? round(floatval($reviewSummary['average_rating']), 1) : 0,
            'distribution' => [
                '5' => intval($reviewSummary['five_star'] ?? 0),
                '4' => intval($reviewSummary['four_star'] ?? 0),
                '3' => intval($reviewSummary['three_star'] ?? 0),
                '2' => intval($reviewSummary['two_star'] ?? 0),
                '1' => intval($reviewSummary['one_star'] ?? 0)
            ]
        ],
        'meta' => [
            'title' => $product['meta_title'] ?? $product['name'],
            'description' => $product['meta_description'] ?? $product['short_description'],
            'keywords' => $product['meta_keywords'] ?? ''
        ],
        'stats' => [
            'views' => intval($product['view_count'] ?? 0),
            'sold' => intval($product['sold_count'] ?? 0)
        ],
        'created_at' => $product['created_at'],
        'updated_at' => $product['updated_at']
    ];
}

// Only GET method is allowed for public access
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') {
    sendResponse([
        'success' => false,
        'message' => 'Method not allowed'
    ], 405);
}

try {
    // Check if requesting single product
    $productId = isset($_GET['id']) ? intval($_GET['id']) : null;
    $productSlug = $_GET['slug'] ?? null;
    
    // Single product request
    if ($productId || $productSlug) {
        $product = getSingleProduct($pdo, $productId, $productSlug);
        
        if (!$product) {
            sendResponse([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
        
        // Check if product is active (unless admin)
        if ($product['status'] !== 'active') {
            sendResponse([
                'success' => false,
                'message' => 'Product not available'
            ], 404);
        }
        
        sendResponse([
            'success' => true,
            'data' => $product
        ]);
    }
    
    // Get query parameters for list
    $featured = isset($_GET['featured']) ? (intval($_GET['featured']) === 1) : null;
    $categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
    $categorySlug = $_GET['category_slug'] ?? $_GET['category'] ?? null; // Support both 'category' and 'category_slug'
    $search = $_GET['search'] ?? null;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : null;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $sort = $_GET['sort'] ?? 'created_at'; // created_at, price_asc, price_desc, name
    $status = $_GET['status'] ?? 'active'; // Only return active products by default
    
    // Build WHERE clause
    $whereConditions = ["p.status = 'active'"]; // Only active products
    
    // Featured filter
    if ($featured !== null) {
        $whereConditions[] = $featured ? "p.is_featured = 1" : "p.is_featured = 0";
    }
    
    // Category filter - support category name, slug, or ID
    if ($categoryId) {
        $whereConditions[] = "p.category_id = :category_id";
    } elseif ($categorySlug) {
        // Try to match by category name or slug
        $whereConditions[] = "(c.name = :category_slug OR c.slug = :category_slug)";
    }
    
    // Search filter
    if ($search) {
        $whereConditions[] = "(p.name LIKE :search OR p.short_description LIKE :search OR p.sku LIKE :search)";
    }
    
    $whereClause = "WHERE " . implode(" AND ", $whereConditions);
    
    // Build ORDER BY clause
    $orderByMap = [
        'created_at' => 'p.created_at DESC',
        'price_asc' => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'name' => 'p.name ASC',
        'featured' => 'p.is_featured DESC, p.created_at DESC'
    ];
    $orderBy = $orderByMap[$sort] ?? $orderByMap['created_at'];
    
    // Build LIMIT clause
    $limitClause = "";
    if ($limit) {
        $limitClause = "LIMIT :limit";
        if ($offset > 0) {
            $limitClause .= " OFFSET :offset";
        }
    }
    
    // Query products with images
    $query = "
        SELECT 
            p.id,
            p.sku,
            p.name,
            p.slug,
            p.short_description,
            p.price,
            p.original_price,
            p.discount_percentage,
            p.stock_quantity,
            p.status,
            p.is_featured,
            p.is_new,
            p.category_id,
            c.name as category_name,
            c.slug as category_slug,
            (
                SELECT image_url 
                FROM product_images 
                WHERE product_id = p.id 
                AND is_primary = 1 
                LIMIT 1
            ) as primary_image,
            (
                SELECT GROUP_CONCAT(image_url SEPARATOR ',')
                FROM product_images 
                WHERE product_id = p.id
            ) as all_images
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        {$whereClause}
        ORDER BY {$orderBy}
        {$limitClause}
    ";
    
    $stmt = $pdo->prepare($query);
    
    // Bind parameters
    if ($categoryId) {
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
    }
    if ($categorySlug) {
        $stmt->bindValue(':category_slug', $categorySlug, PDO::PARAM_STR);
    }
    if ($search) {
        $searchTerm = '%' . $search . '%';
        $stmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
    }
    if ($limit) {
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        if ($offset > 0) {
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
    }
    
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Transform products for frontend
    $transformedProducts = [];
    foreach ($products as $product) {
        // Parse images
        $images = [];
        if ($product['primary_image']) {
            $images[] = $product['primary_image'];
        }
        if ($product['all_images']) {
            $allImages = explode(',', $product['all_images']);
            foreach ($allImages as $img) {
                if ($img && $img !== $product['primary_image']) {
                    $images[] = $img;
                }
            }
        }
        
        // Calculate discount percentage if original_price exists
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
            'category' => [
                'id' => $product['category_id'] ? intval($product['category_id']) : null,
                'name' => $product['category_name'] ?? '',
                'slug' => $product['category_slug'] ?? ''
            ],
            'image' => $images[0] ?? null,
            'images' => $images,
            'in_stock' => intval($product['stock_quantity']) > 0
        ];
    }
    
    // Get total count for pagination
    $countQuery = "
        SELECT COUNT(*) as total
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        {$whereClause}
    ";
    
    $countStmt = $pdo->prepare($countQuery);
    if ($categoryId) {
        $countStmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
    }
    if ($categorySlug) {
        $countStmt->bindValue(':category_slug', $categorySlug, PDO::PARAM_STR);
    }
    if ($search) {
        $searchTerm = '%' . $search . '%';
        $countStmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
    }
    
    $countStmt->execute();
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    sendResponse([
        'success' => true,
        'products' => $transformedProducts, // Changed from 'data' to 'products' for frontend compatibility
        'data' => $transformedProducts, // Keep 'data' for backward compatibility
        'meta' => [
            'total' => intval($total),
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => $limit ? ($offset + $limit) < $total : false
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Products API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'Failed to fetch products',
        'error' => getenv('DEBUG') ? $e->getMessage() : 'Internal server error'
    ], 500);
}
