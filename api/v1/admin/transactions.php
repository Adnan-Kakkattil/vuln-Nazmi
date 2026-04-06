<?php
/**
 * Admin Financial Transactions API
 * Endpoint: /api/v1/admin/transactions.php
 * Handles CRUD operations for financial transactions
 * 
 * Methods:
 * - GET: List transactions with filters
 * - POST: Create new transaction
 * - PUT: Update transaction
 * - DELETE: Delete transaction
 */

header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../../../includes/config.php';

// Database connection
$pdo = getDbConnection();

// Try to get admin (optional for now - you can enable auth later)
$admin = requireAdminAuth();
if ($admin === false) {
    // Create a default admin for testing/development
    // In production, you should uncomment the requireAdminAuthOrDie() below
    $admin = [
        'id' => 1,
        'email' => 'admin@test.com',
        'role_id' => 1,
        'role_name' => 'Super Admin'
    ];
}

// Uncomment below to enforce authentication:
// $admin = requireAdminAuthOrDie();
// requirePermission('manage_finance');

/**
 * Send JSON response
 */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

/**
 * Map frontend transaction type to database transaction_type
 */
function mapTransactionType($frontendType) {
    $mapping = [
        'credit' => 'sale',      // Credit = Sales
        'debit' => 'expense',    // Debit = Expenses
        'purchase' => 'purchase' // Purchase = Purchase
    ];
    return $mapping[$frontendType] ?? $frontendType;
}

/**
 * Reverse map - database transaction_type to frontend type
 */
function reverseMapTransactionType($dbType) {
    $mapping = [
        'sale' => 'credit',
        'expense' => 'debit',
        'purchase' => 'purchase',
        'refund' => 'debit'
    ];
    return $mapping[$dbType] ?? $dbType;
}

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // List transactions with filters
            $typeFilter = $_GET['type'] ?? '';
            $dateFilter = $_GET['date'] ?? '';
            $search = $_GET['search'] ?? '';
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 100);
            $offset = ($page - 1) * $limit;
            
            // Map frontend type filter to database type if needed
            $dbType = $typeFilter !== 'all' ? mapTransactionType($typeFilter) : '';

            // Build query for combined transactions (from financial_transactions and orders)
            $where = [];
            $params = [];
            
            // Base filters for the combined query
            if ($dbType) {
                $where[] = "transaction_type = ?";
                $params[] = $dbType;
            }
            
            if ($dateFilter) {
                $where[] = "transaction_date = ?";
                $params[] = $dateFilter;
            }
            
            if ($search) {
                $where[] = "(description LIKE ? OR reference_type LIKE ?)";
                $searchParam = "%{$search}%";
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Subquery for combined transactions
            $combinedQuery = "
                SELECT 
                    id, transaction_type, reference_type, reference_id, 
                    amount, currency, description, transaction_date, created_at, is_system,
                    payment_status, payment_method
                FROM (
                    SELECT 
                        id, transaction_type, reference_type, reference_id, 
                        amount, currency, description, transaction_date, created_at, 0 as is_system,
                        'completed' as payment_status, 'cash' as payment_method
                    FROM financial_transactions
                    
                    UNION ALL
                    
                    SELECT 
                        id, 'sale' as transaction_type, 'Sales|Order' as reference_type, 
                        id as reference_id, total_amount as amount, 'INR' as currency, 
                        CONCAT('Order: ', order_number) as description, 
                        DATE(order_date) as transaction_date, created_at, 1 as is_system,
                        payment_status, payment_method
                    FROM orders
                    WHERE status != 'cancelled'
                ) as combined_transactions
            ";

            // Get total count
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM ($combinedQuery) as t {$whereClause}");
            $countStmt->execute($params);
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get transactions
            $stmt = $pdo->prepare("
                SELECT * FROM ($combinedQuery) as t
                {$whereClause}
                ORDER BY transaction_date DESC, created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $queryParams = $params;
            $queryParams[] = $limit;
            $queryParams[] = $offset;
            $stmt->execute($queryParams);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Transform to frontend format
            foreach ($transactions as &$transaction) {
                $transaction['type'] = reverseMapTransactionType($transaction['transaction_type']);
                $transaction['date'] = $transaction['transaction_date'];
                $transaction['is_system'] = (bool)$transaction['is_system'];
                $transaction['payment_status'] = $transaction['payment_status'] ?? 'completed';
                $transaction['payment_method'] = $transaction['payment_method'] ?? 'manual';
                
                // Parse reference_type - format: "category|reference" or just "category" or just "reference"
                $referenceType = $transaction['reference_type'] ?? null;
                $category = null;
                $reference = null;
                
                if ($referenceType) {
                    if (strpos($referenceType, '|') !== false) {
                        // Format: "category|reference"
                        list($category, $reference) = explode('|', $referenceType, 2);
                        // If category is 'ref', it means no category was provided
                        if ($category === 'ref') {
                            $category = null;
                        }
                    } else {
                        // Just category or old format - no reference stored
                        $category = $referenceType;
                        // Try to reconstruct from reference_id if available
                        if ($transaction['reference_id']) {
                            $reference = (string)$transaction['reference_id'];
                        }
                    }
                } elseif ($transaction['reference_id']) {
                    // Only reference_id exists, no reference_type
                    $reference = (string)$transaction['reference_id'];
                }
                
                $transaction['category'] = $category;
                $transaction['reference'] = $reference;
                
                // Remove DB-specific fields
                unset($transaction['transaction_type'], $transaction['transaction_date'], $transaction['reference_type'], $transaction['reference_id']);
            }
            
            sendResponse([
                'success' => true,
                'data' => $transactions,
                'pagination' => [
                    'total' => intval($total),
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => ceil($total / $limit)
                ]
            ]);
            break;
            
        case 'POST':
            // Create new transaction
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (empty($data['type']) || empty($data['date']) || !isset($data['amount']) || empty($data['description'])) {
                sendResponse([
                    'success' => false,
                    'message' => 'Missing required fields: type, date, amount, description'
                ], 400);
            }
            
            // Map frontend type to database type
            $dbTransactionType = mapTransactionType($data['type']);
            
            // Handle reference and category
            // Store reference string in reference_type using format: "category|reference" or just "reference"
            $category = $data['category'] ?? null;
            $referenceString = !empty($data['reference']) ? trim($data['reference']) : null;
            $referenceType = null;
            $referenceId = null;
            
            if ($referenceString) {
                // Try to extract numeric ID if reference is in format "PREFIX-NUMBER"
                if (preg_match('/^([A-Z]+)-?(\d+)$/i', $referenceString, $matches)) {
                    $referenceId = intval($matches[2]);
                }
                
                // Store reference string in reference_type with category
                // Format: "category|reference" or "ref|reference" if no category
                if ($category) {
                    $referenceType = $category . '|' . $referenceString;
                } else {
                    $referenceType = 'ref|' . $referenceString;
                }
            } elseif ($category) {
                // Only category, no reference
                $referenceType = $category;
            }
            
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                // Insert transaction
                $stmt = $pdo->prepare("
                    INSERT INTO financial_transactions (
                        transaction_type,
                        reference_type,
                        reference_id,
                        amount,
                        currency,
                        description,
                        transaction_date
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $dbTransactionType,
                    $referenceType,
                    $referenceId,
                    $data['amount'],
                    $data['currency'] ?? 'INR',
                    $data['description'],
                    $data['date']
                ]);
                
                $transactionId = $pdo->lastInsertId();
                
                // If it's an expense (debit) and has category, also add to expenses table
                if ($data['type'] === 'debit' && !empty($data['category'])) {
                    // Get or create expense category
                    $catStmt = $pdo->prepare("SELECT id FROM expense_categories WHERE name = ? LIMIT 1");
                    $catStmt->execute([$data['category']]);
                    $category = $catStmt->fetch();
                    
                    $categoryId = null;
                    if ($category) {
                        $categoryId = $category['id'];
                    } else {
                        // Create category
                        $createCatStmt = $pdo->prepare("INSERT INTO expense_categories (name) VALUES (?)");
                        $createCatStmt->execute([$data['category']]);
                        $categoryId = $pdo->lastInsertId();
                    }
                    
                    // Insert expense
                    $expenseStmt = $pdo->prepare("
                        INSERT INTO expenses (
                            category_id,
                            description,
                            amount,
                            expense_date,
                            payment_method,
                            notes,
                            created_by
                        ) VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $expenseStmt->execute([
                        $categoryId,
                        $data['description'],
                        $data['amount'],
                        $data['date'],
                        $data['payment_method'] ?? null,
                        $data['notes'] ?? null,
                        $admin['id']
                    ]);
                }
                
                // Log activity
                $logStmt = $pdo->prepare("
                    INSERT INTO admin_activity_log (user_id, action, module, entity_id, entity_type)
                    VALUES (?, 'create', 'finance', ?, 'transaction')
                ");
                $logStmt->execute([$admin['id'], $transactionId]);
                
                $pdo->commit();
                
                // Auto-sync transaction to POS system immediately (non-blocking)
                try {
                    require_once __DIR__ . '/../../../includes/api_integration_helpers.php';
                    syncTransactionToPOS($pdo, $transactionId, true); // true = async/non-blocking
                } catch (Exception $e) {
                    error_log("Auto-sync transaction failed: " . $e->getMessage());
                    // Don't fail transaction creation if sync fails
                }
                
                // Fetch created transaction
                $fetchStmt = $pdo->prepare("
                    SELECT 
                        id,
                        transaction_type,
                        reference_type,
                        reference_id,
                        amount,
                        currency,
                        description,
                        transaction_date,
                        created_at
                    FROM financial_transactions
                    WHERE id = ?
                ");
                $fetchStmt->execute([$transactionId]);
                $transaction = $fetchStmt->fetch(PDO::FETCH_ASSOC);
                
                // Transform to frontend format
                $transaction['type'] = reverseMapTransactionType($transaction['transaction_type']);
                $transaction['date'] = $transaction['transaction_date'];
                
                // Parse reference_type - format: "category|reference" or just "category" or just "reference"
                $referenceType = $transaction['reference_type'] ?? null;
                $category = null;
                $reference = null;
                
                if ($referenceType) {
                    if (strpos($referenceType, '|') !== false) {
                        // Format: "category|reference"
                        list($parsedCategory, $parsedReference) = explode('|', $referenceType, 2);
                        // If category is 'ref', it means no category was provided
                        if ($parsedCategory === 'ref') {
                            $category = null;
                        } else {
                            $category = $parsedCategory;
                        }
                        $reference = $parsedReference;
                    } else {
                        // Just category or old format - no reference stored
                        $category = $referenceType;
                        // Try to reconstruct from reference_id if available
                        if ($transaction['reference_id']) {
                            $reference = (string)$transaction['reference_id'];
                        }
                    }
                } elseif ($transaction['reference_id']) {
                    // Only reference_id exists, no reference_type
                    $reference = (string)$transaction['reference_id'];
                }
                
                $transaction['category'] = $category;
                $transaction['reference'] = $reference;
                $transaction['notes'] = $data['notes'] ?? null;
                unset($transaction['transaction_type'], $transaction['transaction_date'], $transaction['reference_type'], $transaction['reference_id']);
                
                sendResponse([
                    'success' => true,
                    'message' => 'Transaction created successfully',
                    'data' => $transaction
                ], 201);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case 'PUT':
            // Update transaction
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                sendResponse([
                    'success' => false,
                    'message' => 'Transaction ID is required'
                ], 400);
            }
            
            $transactionId = $data['id'];
            
            // Check if transaction exists
            $checkStmt = $pdo->prepare("SELECT id FROM financial_transactions WHERE id = ?");
            $checkStmt->execute([$transactionId]);
            if (!$checkStmt->fetch()) {
                sendResponse([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }
            
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                // Map frontend type to database type
                $dbTransactionType = mapTransactionType($data['type'] ?? '');
                
                // Handle reference and category for update
                $category = $data['category'] ?? null;
                $referenceString = !empty($data['reference']) ? trim($data['reference']) : null;
                $referenceType = null;
                $referenceId = null;
                
                if ($referenceString) {
                    // Try to extract numeric ID if reference is in format "PREFIX-NUMBER"
                    if (preg_match('/^([A-Z]+)-?(\d+)$/i', $referenceString, $matches)) {
                        $referenceId = intval($matches[2]);
                    }
                    
                    // Store reference string in reference_type with category
                    if ($category) {
                        $referenceType = $category . '|' . $referenceString;
                    } else {
                        $referenceType = 'ref|' . $referenceString;
                    }
                } elseif ($category) {
                    // Only category, no reference
                    $referenceType = $category;
                }
                
                // Update transaction
                $updateFields = [];
                $updateParams = [];
                
                if (isset($data['type'])) {
                    $updateFields[] = "transaction_type = ?";
                    $updateParams[] = $dbTransactionType;
                }
                
                if (isset($data['amount'])) {
                    $updateFields[] = "amount = ?";
                    $updateParams[] = $data['amount'];
                }
                
                if (isset($data['description'])) {
                    $updateFields[] = "description = ?";
                    $updateParams[] = $data['description'];
                }
                
                if (isset($data['date'])) {
                    $updateFields[] = "transaction_date = ?";
                    $updateParams[] = $data['date'];
                }
                
                // Update reference fields
                $updateFields[] = "reference_type = ?";
                $updateParams[] = $referenceType;
                $updateFields[] = "reference_id = ?";
                $updateParams[] = $referenceId;
                
                if (!empty($updateFields)) {
                    $updateParams[] = $transactionId;
                    $updateSql = "UPDATE financial_transactions SET " . implode(', ', $updateFields) . " WHERE id = ?";
                    $updateStmt = $pdo->prepare($updateSql);
                    $updateStmt->execute($updateParams);
                }
                
                // Log activity
                $logStmt = $pdo->prepare("
                    INSERT INTO admin_activity_log (user_id, action, module, entity_id, entity_type)
                    VALUES (?, 'update', 'finance', ?, 'transaction')
                ");
                $logStmt->execute([$admin['id'], $transactionId]);
                
                $pdo->commit();
                
                // Fetch updated transaction
                $fetchStmt = $pdo->prepare("
                    SELECT 
                        id,
                        transaction_type,
                        reference_type,
                        reference_id,
                        amount,
                        currency,
                        description,
                        transaction_date,
                        created_at
                    FROM financial_transactions
                    WHERE id = ?
                ");
                $fetchStmt->execute([$transactionId]);
                $transaction = $fetchStmt->fetch(PDO::FETCH_ASSOC);
                
                // Transform to frontend format
                $transaction['type'] = reverseMapTransactionType($transaction['transaction_type']);
                $transaction['date'] = $transaction['transaction_date'];
                
                // Parse reference_type - format: "category|reference" or just "category" or just "reference"
                $referenceType = $transaction['reference_type'] ?? null;
                $category = null;
                $reference = null;
                
                if ($referenceType) {
                    if (strpos($referenceType, '|') !== false) {
                        // Format: "category|reference"
                        list($parsedCategory, $parsedReference) = explode('|', $referenceType, 2);
                        // If category is 'ref', it means no category was provided
                        if ($parsedCategory === 'ref') {
                            $category = null;
                        } else {
                            $category = $parsedCategory;
                        }
                        $reference = $parsedReference;
                    } else {
                        // Just category or old format - no reference stored
                        $category = $referenceType;
                        // Try to reconstruct from reference_id if available
                        if ($transaction['reference_id']) {
                            $reference = (string)$transaction['reference_id'];
                        }
                    }
                } elseif ($transaction['reference_id']) {
                    // Only reference_id exists, no reference_type
                    $reference = (string)$transaction['reference_id'];
                }
                
                $transaction['category'] = $category;
                $transaction['reference'] = $reference;
                unset($transaction['transaction_type'], $transaction['transaction_date'], $transaction['reference_type'], $transaction['reference_id']);
                
                sendResponse([
                    'success' => true,
                    'message' => 'Transaction updated successfully',
                    'data' => $transaction
                ]);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case 'DELETE':
            // Delete transaction (hard delete)
            $transactionId = $_GET['id'] ?? null;
            
            if (!$transactionId) {
                sendResponse([
                    'success' => false,
                    'message' => 'Transaction ID is required'
                ], 400);
            }
            
            // Ensure transactionId is an integer
            $transactionId = intval($transactionId);
            
            if ($transactionId <= 0) {
                sendResponse([
                    'success' => false,
                    'message' => 'Invalid transaction ID'
                ], 400);
            }
            
            // Check if transaction exists
            $checkStmt = $pdo->prepare("SELECT id FROM financial_transactions WHERE id = ?");
            $checkStmt->execute([$transactionId]);
            if (!$checkStmt->fetch()) {
                sendResponse([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }
            
            // Delete transaction
            $stmt = $pdo->prepare("DELETE FROM financial_transactions WHERE id = ?");
            $stmt->execute([$transactionId]);
            
            // Log activity
            $logStmt = $pdo->prepare("
                INSERT INTO admin_activity_log (user_id, action, module, entity_id, entity_type)
                VALUES (?, 'delete', 'finance', ?, 'transaction')
            ");
            $logStmt->execute([$admin['id'], $transactionId]);
            
            sendResponse([
                'success' => true,
                'message' => 'Transaction deleted successfully'
            ]);
            break;
            
        default:
            sendResponse([
                'success' => false,
                'message' => 'Method not allowed'
            ], 405);
    }
    
} catch (PDOException $e) {
    error_log("Transactions API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => config('DEBUG', false) ? $e->getMessage() : 'Internal server error'
    ], 500);
} catch (Exception $e) {
    error_log("Transactions API Error: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'message' => 'An error occurred',
        'error' => config('DEBUG', false) ? $e->getMessage() : 'Internal server error'
    ], 500);
}
