<?php
/**
 * Admin Products API
 * Endpoint: /api/v1/admin/products.php
 * Handles CRUD operations for products
 * 
 * Methods:
 * - GET: List products with filters
 * - POST: Create new product
 * - PUT: Update product
 * - DELETE: Delete product
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

/**
 * Generate slug from string
 */
function generateSlug($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Upload product images
 */
function uploadProductImages($files, $productId) {
    $uploadDir = __DIR__ . '/../../../uploads/products/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $uploadedImages = [];
    
    foreach ($files as $file) {
        // Validate file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            continue; // Skip invalid files
        }
        
        if ($file['size'] > $maxSize) {
            continue; // Skip files that are too large
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'product_' . $productId . '_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $relativePath = '/uploads/products/' . $filename;
            $uploadedImages[] = $relativePath;
        }
    }
    
    return $uploadedImages;
}

/**
 * Handle base64 image uploads (from form)
 */
function saveBase64Images($base64Images, $productId) {
    $uploadDir = __DIR__ . '/../../../uploads/products/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $savedImages = [];
    
    foreach ($base64Images as $index => $base64Data) {
        // Check if it's a valid base64 image
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
            $imageType = $matches[1];
            $data = base64_decode(substr($base64Data, strpos($base64Data, ',') + 1));
            
            // Generate unique filename
            $filename = 'product_' . $productId . '_' . time() . '_' . $index . '.' . $imageType;
            $filepath = $uploadDir . $filename;
            
            if (file_put_contents($filepath, $data)) {
                $relativePath = '/uploads/products/' . $filename;
                $savedImages[] = $relativePath;
            }
        }
    }
    
    return $savedImages;
}

/**
 * Save product specifications
 */
function saveProductSpecifications($pdo, $productId, $specs, $adminId) {
    // Delete existing specifications
    $stmt = $pdo->prepare("DELETE FROM product_specifications WHERE product_id = ?");
    $stmt->execute([$productId]);
    
    // Insert new specifications
    $sortOrder = 0;
    foreach ($specs as $key => $value) {
        if (!empty($value)) {
            $stmt = $pdo->prepare("
                INSERT INTO product_specifications (product_id, spec_key, spec_value, sort_order)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$productId, $key, $value, $sortOrder++]);
        }
    }
}

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // List products with filters
            $categoryId = $_GET['category_id'] ?? null;
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 50);
            $offset = ($page - 1) * $limit;
            
            // Build query - show all products (hard delete removes them completely)
            // Since we're doing hard deletes, all products that exist should be shown
            $where = [];
            $params = [];
            
            if ($categoryId) {
                $where[] = "p.category_id = ?";
                $params[] = $categoryId;
            }
            
            if ($search) {
                $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
                $searchParam = "%{$search}%";
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            if ($status && $status !== 'all') {
                if ($status === 'low') {
                    $where[] = "p.stock_quantity <= p.low_stock_threshold AND p.stock_quantity > 0";
                } elseif ($status === 'out') {
                    $where[] = "p.stock_quantity = 0";
                } elseif ($status === 'high') {
                    $where[] = "p.stock_quantity > 30";
                } elseif ($status === 'medium') {
                    $where[] = "p.stock_quantity BETWEEN 10 AND 30";
                }
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Get total count
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM products p {$whereClause}");
            $countStmt->execute($params);
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get products
            $stmt = $pdo->prepare("
                SELECT 
                    p.*,
                    c.name as category_name,
                    c.slug as category_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                {$whereClause}
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $params[] = $limit;
            $params[] = $offset;
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get images and specifications for each product
            foreach ($products as &$product) {
                // Get images
                $imgStmt = $pdo->prepare("SELECT image_url, alt_text, is_primary, sort_order FROM product_images WHERE product_id = ? ORDER BY sort_order, is_primary DESC");
                $imgStmt->execute([$product['id']]);
                $product['images'] = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get specifications
                $specStmt = $pdo->prepare("SELECT spec_key, spec_value FROM product_specifications WHERE product_id = ? ORDER BY sort_order");
                $specStmt->execute([$product['id']]);
                $specs = $specStmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Convert specs to object
                $product['specifications'] = [];
                foreach ($specs as $spec) {
                    $product['specifications'][$spec['spec_key']] = $spec['spec_value'];
                }
            }
            
            sendResponse([
                'success' => true,
                'data' => $products,
                'pagination' => [
                    'total' => intval($total),
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => ceil($total / $limit)
                ]
            ]);
            break;
            
        case 'POST':
            // Create new product
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (empty($data['name']) || empty($data['sku']) || empty($data['category_id']) || 
                !isset($data['price']) || !isset($data['stock_quantity'])) {
                sendResponse([
                    'success' => false,
                    'message' => 'Missing required fields: name, sku, category_id, price, stock_quantity'
                ], 400);
            }
            
            // Check if SKU already exists
            $checkStmt = $pdo->prepare("SELECT id FROM products WHERE sku = ?");
            $checkStmt->execute([$data['sku']]);
            if ($checkStmt->fetch()) {
                sendResponse([
                    'success' => false,
                    'message' => 'SKU already exists'
                ], 400);
            }
            
            // Generate slug
            $slug = generateSlug($data['name']);
            $slugStmt = $pdo->prepare("SELECT id FROM products WHERE slug = ?");
            $slugStmt->execute([$slug]);
            $counter = 1;
            $originalSlug = $slug;
            while ($slugStmt->fetch()) {
                $slug = $originalSlug . '-' . $counter;
                $slugStmt->execute([$slug]);
                $counter++;
            }
            
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                // Insert product
                $stmt = $pdo->prepare("
                    INSERT INTO products (
                        sku, name, slug, short_description, full_description, category_id,
                        price, original_price, cost_price, stock_quantity, low_stock_threshold,
                        status, is_featured, is_new, weight_kg, dimensions_cm
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $data['sku'],
                    $data['name'],
                    $slug,
                    $data['short_description'] ?? $data['description'] ?? null,
                    $data['full_description'] ?? $data['description'] ?? null,
                    $data['category_id'],
                    $data['price'],
                    $data['original_price'] ?? null,
                    $data['cost_price'] ?? null,
                    $data['stock_quantity'],
                    $data['low_stock_threshold'] ?? 10,
                    $data['status'] ?? 'active',
                    $data['is_featured'] ?? 0,
                    $data['is_new'] ?? 0,
                    $data['weight_kg'] ?? null,
                    $data['dimensions_cm'] ?? null
                ]);
                
                $productId = $pdo->lastInsertId();
                
                // Save images (base64 or file upload)
                $images = $data['images'] ?? [];
                $imagePaths = [];
                
                if (!empty($images) && is_array($images) && count($images) > 0) {
                    // Check if images are base64 strings
                    if (is_string($images[0]) && strpos($images[0], 'data:image') === 0) {
                        $imagePaths = saveBase64Images($images, $productId);
                    } elseif (is_string($images[0]) && (strpos($images[0], '/uploads/') === 0 || strpos($images[0], 'uploads/') === 0)) {
                        // Already file paths
                        foreach ($images as $img) {
                            if (!empty($img)) {
                                $imagePaths[] = strpos($img, '/') === 0 ? $img : '/' . $img;
                            }
                        }
                    }
                }
                
                // Save images to database
                $primarySet = false;
                foreach ($imagePaths as $index => $imagePath) {
                    $imgStmt = $pdo->prepare("
                        INSERT INTO product_images (product_id, image_url, sort_order, is_primary)
                        VALUES (?, ?, ?, ?)
                    ");
                    $isPrimary = !$primarySet ? 1 : 0;
                    $primarySet = true;
                    $imgStmt->execute([$productId, $imagePath, $index, $isPrimary]);
                }
                
                // Save specifications
                $specs = $data['specifications'] ?? [];
                if (!empty($specs)) {
                    saveProductSpecifications($pdo, $productId, $specs, $admin['id']);
                }
                
                // Log activity
                $logStmt = $pdo->prepare("
                    INSERT INTO admin_activity_log (user_id, action, module, entity_id, entity_type)
                    VALUES (?, 'create', 'products', ?, 'product')
                ");
                $logStmt->execute([$admin['id'], $productId]);
                
                $pdo->commit();
                
                // Fetch created product
                $fetchStmt = $pdo->prepare("
                    SELECT p.*, c.name as category_name, c.slug as category_slug
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.id = ?
                ");
                $fetchStmt->execute([$productId]);
                $product = $fetchStmt->fetch(PDO::FETCH_ASSOC);
                
                // Get images and specs
                $imgStmt = $pdo->prepare("SELECT image_url, alt_text, is_primary, sort_order FROM product_images WHERE product_id = ? ORDER BY sort_order");
                $imgStmt->execute([$productId]);
                $product['images'] = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
                
                $specStmt = $pdo->prepare("SELECT spec_key, spec_value FROM product_specifications WHERE product_id = ? ORDER BY sort_order");
                $specStmt->execute([$productId]);
                $specs = $specStmt->fetchAll(PDO::FETCH_ASSOC);
                $product['specifications'] = [];
                foreach ($specs as $spec) {
                    $product['specifications'][$spec['spec_key']] = $spec['spec_value'];
                }
                
                sendResponse([
                    'success' => true,
                    'message' => 'Product created successfully',
                    'data' => $product
                ], 201);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case 'PUT':
            // Update product
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                sendResponse([
                    'success' => false,
                    'message' => 'Product ID is required'
                ], 400);
            }
            
            $productId = $data['id'];
            
            // Check if product exists
            $checkStmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
            $checkStmt->execute([$productId]);
            if (!$checkStmt->fetch()) {
                sendResponse([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }
            
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                // Update product
                $updateFields = [];
                $updateParams = [];
                
                $allowedFields = ['name', 'sku', 'short_description', 'full_description', 'category_id',
                                 'price', 'original_price', 'cost_price', 'stock_quantity', 'low_stock_threshold',
                                 'status', 'is_featured', 'is_new', 'weight_kg', 'dimensions_cm'];
                
                foreach ($allowedFields as $field) {
                    if (isset($data[$field])) {
                        $updateFields[] = "{$field} = ?";
                        $updateParams[] = $data[$field];
                    }
                }
                
                // Update slug if name changed
                if (isset($data['name'])) {
                    $slug = generateSlug($data['name']);
                    $slugStmt = $pdo->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
                    $slugStmt->execute([$slug, $productId]);
                    $counter = 1;
                    $originalSlug = $slug;
                    while ($slugStmt->fetch()) {
                        $slug = $originalSlug . '-' . $counter;
                        $slugStmt->execute([$slug, $productId]);
                        $counter++;
                    }
                    $updateFields[] = "slug = ?";
                    $updateParams[] = $slug;
                }
                
                if (!empty($updateFields)) {
                    $updateParams[] = $productId;
                    $updateSql = "UPDATE products SET " . implode(', ', $updateFields) . " WHERE id = ?";
                    $updateStmt = $pdo->prepare($updateSql);
                    $updateStmt->execute($updateParams);
                }
                
                // Handle images update if provided
                if (isset($data['images']) && is_array($data['images'])) {
                    // Delete existing images
                    $delImgStmt = $pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
                    $delImgStmt->execute([$productId]);
                    
                    // Process each image individually - handle mix of base64 and file paths
                    $imagePaths = [];
                    $uploadDir = __DIR__ . '/../../../uploads/products/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    foreach ($data['images'] as $index => $imageData) {
                        if (empty($imageData)) continue;
                        
                        // Check if it's a base64 image
                        if (is_string($imageData) && preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                            // It's a base64 image - save as file
                            $imageType = $matches[1];
                            $base64Data = base64_decode(substr($imageData, strpos($imageData, ',') + 1));
                            
                            if ($base64Data !== false) {
                                $filename = 'product_' . $productId . '_' . time() . '_' . $index . '.' . $imageType;
                                $filepath = $uploadDir . $filename;
                                
                                if (file_put_contents($filepath, $base64Data)) {
                                    $imagePaths[] = '/uploads/products/' . $filename;
                                }
                            }
                        } elseif (is_string($imageData) && (strpos($imageData, '/uploads/') === 0 || strpos($imageData, 'uploads/') === 0)) {
                            // It's already a file path - keep it
                            $imagePaths[] = $imageData;
                        }
                    }
                    
                    // Save images to database
                    $primarySet = false;
                    foreach ($imagePaths as $index => $imagePath) {
                        $imgStmt = $pdo->prepare("
                            INSERT INTO product_images (product_id, image_url, sort_order, is_primary)
                            VALUES (?, ?, ?, ?)
                        ");
                        $isPrimary = !$primarySet ? 1 : 0;
                        $primarySet = true;
                        $imgStmt->execute([$productId, $imagePath, $index, $isPrimary]);
                    }
                }
                
                // Update specifications if provided
                if (isset($data['specifications']) && is_array($data['specifications'])) {
                    saveProductSpecifications($pdo, $productId, $data['specifications'], $admin['id']);
                }
                
                // Log activity
                $logStmt = $pdo->prepare("
                    INSERT INTO admin_activity_log (user_id, action, module, entity_id, entity_type)
                    VALUES (?, 'update', 'products', ?, 'product')
                ");
                $logStmt->execute([$admin['id'], $productId]);
                
                $pdo->commit();
                
                // Fetch updated product
                $fetchStmt = $pdo->prepare("
                    SELECT p.*, c.name as category_name, c.slug as category_slug
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.id = ?
                ");
                $fetchStmt->execute([$productId]);
                $product = $fetchStmt->fetch(PDO::FETCH_ASSOC);
                
                // Get images and specs
                $imgStmt = $pdo->prepare("SELECT image_url, alt_text, is_primary, sort_order FROM product_images WHERE product_id = ? ORDER BY sort_order");
                $imgStmt->execute([$productId]);
                $product['images'] = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
                
                $specStmt = $pdo->prepare("SELECT spec_key, spec_value FROM product_specifications WHERE product_id = ? ORDER BY sort_order");
                $specStmt->execute([$productId]);
                $specs = $specStmt->fetchAll(PDO::FETCH_ASSOC);
                $product['specifications'] = [];
                foreach ($specs as $spec) {
                    $product['specifications'][$spec['spec_key']] = $spec['spec_value'];
                }
                
                sendResponse([
                    'success' => true,
                    'message' => 'Product updated successfully',
                    'data' => $product
                ]);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case 'DELETE':
            // Hard delete product from database
            $productId = $_GET['id'] ?? null;
            
            if (!$productId) {
                sendResponse([
                    'success' => false,
                    'message' => 'Product ID is required'
                ], 400);
            }
            
            // Ensure productId is an integer to prevent SQL injection
            $productId = intval($productId);
            
            if ($productId <= 0) {
                sendResponse([
                    'success' => false,
                    'message' => 'Invalid product ID'
                ], 400);
            }
            
            // Check if product exists
            $checkStmt = $pdo->prepare("SELECT id, name FROM products WHERE id = ?");
            $checkStmt->execute([$productId]);
            $product = $checkStmt->fetch();
            
            if (!$product) {
                sendResponse([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }
            
            // Check if product has been ordered (order_items has no CASCADE, so we can't delete if ordered)
            $orderCheckStmt = $pdo->prepare("SELECT COUNT(*) as count FROM order_items WHERE product_id = ?");
            $orderCheckStmt->execute([$productId]);
            $orderCount = $orderCheckStmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($orderCount > 0) {
                sendResponse([
                    'success' => false,
                    'message' => 'Cannot delete product that has been ordered. Products with order history cannot be permanently deleted for record keeping purposes.'
                ], 400);
            }
            
            // Start transaction for data integrity
            $pdo->beginTransaction();
            
            try {
                // Log activity before deletion
                $logStmt = $pdo->prepare("
                    INSERT INTO admin_activity_log (user_id, action, module, entity_id, entity_type)
                    VALUES (?, 'delete', 'products', ?, 'product')
                ");
                $logStmt->execute([$admin['id'], $productId]);
                
                // Hard delete the product
                // Note: Most related tables have ON DELETE CASCADE, so they will be automatically deleted:
                // - product_images
                // - product_specifications
                // - product_features
                // - product_variants
                // - cart_items
                // - wishlist_items
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$productId]);
                
                $pdo->commit();
                
                sendResponse([
                    'success' => true,
                    'message' => 'Product permanently deleted from database'
                ]);
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                // Check if error is due to foreign key constraint
                if ($e->getCode() == '23000') {
                    sendResponse([
                        'success' => false,
                        'message' => 'Cannot delete product due to database constraints. The product may have related records that prevent deletion.'
                    ], 400);
                }
                throw $e;
            }
            break;
            
        default:
            sendResponse([
                'success' => false,
                'message' => 'Method not allowed'
            ], 405);
    }
    
} catch (PDOException $e) {
    error_log("Products API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => config('DEBUG', false) ? $e->getMessage() : 'Internal server error'
    ], 500);
} catch (Exception $e) {
    error_log("Products API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'An error occurred',
        'error' => config('DEBUG', false) ? $e->getMessage() : 'Internal server error'
    ], 500);
}
