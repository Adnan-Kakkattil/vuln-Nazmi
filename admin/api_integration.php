<?php
/**
 * API Integration Management
 * Admin module for managing POS API keys and integrations
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    header('Location: ../login.php?message=admin_required');
    exit;
}

// Include configuration and helpers
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/api_integration_helpers.php';

$pdo = getDbConnection();
$adminId = (int)$_SESSION['admin_id'];

// Handle form submissions
$action = $_GET['action'] ?? 'list';
$apiKeyId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$message = null;
$error = null;
$showSecret = false;
$newApiKey = null;
$newApiSecret = null;

// Create new API key
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    try {
        $keyName = trim($_POST['key_name'] ?? '');
        $tenantId = !empty($_POST['tenant_id']) ? trim($_POST['tenant_id']) : null;
        $tenantName = !empty($_POST['tenant_name']) ? trim($_POST['tenant_name']) : null;
        $expiresAt = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
        $allowedIps = !empty($_POST['allowed_ips']) ? trim($_POST['allowed_ips']) : null;
        $scopes = !empty($_POST['scopes']) ? $_POST['scopes'] : [];
        $notes = !empty($_POST['notes']) ? trim($_POST['notes']) : null;
        
        if (empty($keyName)) {
            throw new Exception('Key name is required');
        }
        
        // Generate API key and secret
        $apiKey = generateApiKey();
        $apiSecret = generateApiSecret();
        $hashedSecret = hashApiSecret($apiSecret);
        
        // Store in database (support both old and new schema)
        // Try new schema first, fallback to old schema
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO pos_api_keys 
                 (key_name, name, api_key, api_secret, api_secret_hash, tenant_id, tenant_name, expires_at, allowed_ips, scopes, notes, created_by)
                 VALUES 
                 (:key_name, :name, :api_key, :api_secret, :api_secret_hash, :tenant_id, :tenant_name, :expires_at, :allowed_ips, :scopes, :notes, :created_by)'
            );
            
            $stmt->execute([
                ':key_name' => $keyName,
                ':name' => $keyName, // Also set name for compatibility
                ':api_key' => $apiKey,
                ':api_secret' => $hashedSecret, // For new schema
                ':api_secret_hash' => $hashedSecret, // For old schema
                ':tenant_id' => $tenantId,
                ':tenant_name' => $tenantName,
                ':expires_at' => $expiresAt ?: null,
                ':allowed_ips' => $allowedIps,
                ':scopes' => !empty($scopes) ? json_encode($scopes) : null,
                ':notes' => $notes,
                ':created_by' => $adminId
            ]);
        } catch (PDOException $e) {
            // Fallback to old schema (name and api_secret_hash only)
            $stmt = $pdo->prepare(
                'INSERT INTO pos_api_keys 
                 (name, api_key, api_secret_hash, scopes, expires_at, created_by)
                 VALUES 
                 (:name, :api_key, :api_secret_hash, :scopes, :expires_at, :created_by)'
            );
            
            $stmt->execute([
                ':name' => $keyName,
                ':api_key' => $apiKey,
                ':api_secret_hash' => $hashedSecret,
                ':scopes' => !empty($scopes) ? json_encode($scopes) : null,
                ':expires_at' => $expiresAt ?: null,
                ':created_by' => $adminId
            ]);
        }
        
        // Show the secret only once
        $newApiKey = $apiKey;
        $newApiSecret = $apiSecret;
        $showSecret = true;
        $message = 'API key created successfully! Please copy the credentials now - the secret will not be shown again.';
        $action = 'list';
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Update API key
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update' && $apiKeyId) {
    try {
        $keyName = trim($_POST['key_name'] ?? '');
        $tenantId = !empty($_POST['tenant_id']) ? trim($_POST['tenant_id']) : null;
        $tenantName = !empty($_POST['tenant_name']) ? trim($_POST['tenant_name']) : null;
        $expiresAt = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
        $allowedIps = !empty($_POST['allowed_ips']) ? trim($_POST['allowed_ips']) : null;
        $scopes = !empty($_POST['scopes']) ? $_POST['scopes'] : [];
        $notes = !empty($_POST['notes']) ? trim($_POST['notes']) : null;
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $pdo->prepare(
            'UPDATE pos_api_keys SET
                key_name = :key_name,
                tenant_id = :tenant_id,
                tenant_name = :tenant_name,
                expires_at = :expires_at,
                allowed_ips = :allowed_ips,
                scopes = :scopes,
                notes = :notes,
                is_active = :is_active
             WHERE id = :id'
        );
        
        $stmt->execute([
            ':id' => $apiKeyId,
            ':key_name' => $keyName,
            ':tenant_id' => $tenantId,
            ':tenant_name' => $tenantName,
            ':expires_at' => $expiresAt ?: null,
            ':allowed_ips' => $allowedIps,
            ':scopes' => !empty($scopes) ? json_encode($scopes) : null,
            ':notes' => $notes,
            ':is_active' => $isActive
        ]);
        
        $message = 'API key updated successfully!';
        $action = 'list';
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Delete API key
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete' && $apiKeyId) {
    try {
        $pdo->prepare('DELETE FROM pos_api_keys WHERE id = :id')->execute([':id' => $apiKeyId]);
        $message = 'API key deleted successfully!';
        $action = 'list';
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Regenerate secret
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'regenerate_secret' && $apiKeyId) {
    try {
        $newSecret = generateApiSecret();
        $hashedSecret = hashApiSecret($newSecret);
        
        $pdo->prepare(
            'UPDATE pos_api_keys SET api_secret = :secret WHERE id = :id'
        )->execute([':id' => $apiKeyId, ':secret' => $hashedSecret]);
        
        $newApiSecret = $newSecret;
        $showSecret = true;
        $message = 'API secret regenerated! Please copy it now - it will not be shown again.';
        
        // Get API key for display
        $stmt = $pdo->prepare('SELECT api_key FROM pos_api_keys WHERE id = :id');
        $stmt->execute([':id' => $apiKeyId]);
        $keyData = $stmt->fetch(PDO::FETCH_ASSOC);
        $newApiKey = $keyData['api_key'] ?? null;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch data
$apiKeys = [];
$currentApiKey = null;

try {
    // Get all API keys
    $stmt = $pdo->prepare(
        'SELECT pak.*, 
                au.first_name as created_by_name, au.last_name as created_by_lastname,
                (SELECT COUNT(*) FROM pos_integration_logs WHERE api_key_id = pak.id) as request_count
         FROM pos_api_keys pak
         LEFT JOIN users au ON au.id = pak.created_by AND au.role_id = 1
         ORDER BY pak.created_at DESC'
    );
    $stmt->execute();
    $apiKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get single API key for edit
    if ($action === 'edit' && $apiKeyId) {
        $stmt = $pdo->prepare(
            'SELECT * FROM pos_api_keys WHERE id = :id'
        );
        $stmt->execute([':id' => $apiKeyId]);
        $currentApiKey = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($currentApiKey && $currentApiKey['scopes']) {
            $currentApiKey['scopes'] = json_decode($currentApiKey['scopes'], true) ?: [];
        }
        
        if (!$currentApiKey) {
            $action = 'list';
        }
    }
} catch (Exception $e) {
    $error = 'Error loading data: ' . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Integration | Tivora Admin</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../logo.png">
    <link rel="shortcut icon" type="image/png" href="../logo.png">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="js/tivora-alerts.js"></script>

    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            color: #1a1a1a;
        }

        /* Sidebar Styles */
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 260px;
            background: white;
            border-right: 1px solid #e5e7eb;
            transition: transform 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }

        .admin-sidebar-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #6b7280;
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .admin-sidebar-item:hover {
            background: #fef2f2;
            color: #14b8a6;
        }

        .admin-sidebar-item.active {
            background: #fef2f2;
            color: #14b8a6;
            border-left-color: #14b8a6;
            font-weight: 600;
        }

        .admin-sidebar-item i {
            width: 20px;
            height: 20px;
        }

        /* Main Content Area */
        .admin-main {
            margin-left: 260px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* Top Header */
        .admin-header {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #14b8a6;
            border-radius: 4px;
        }
    </style>
</head>
<body class="antialiased">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="admin-sidebar">
        <!-- Logo -->
        <div class="p-6 border-b border-gray-200">
            <a href="../index.php" class="flex items-center gap-2">
                <img src="../Tivora_wordmark_red.avif" alt="Tivora Electronics" class="h-8 w-auto">
                <span class="text-xs text-gray-500 font-medium ml-1">Admin</span>
            </a>
        </div>

        <!-- Navigation Menu -->
        <nav class="py-4">
            <a href="dashboard.php" class="admin-sidebar-item">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>
            <a href="stock.php" class="admin-sidebar-item">
                <i data-lucide="package"></i>
                <span>Stock Management</span>
            </a>
            <a href="finance.php" class="admin-sidebar-item">
                <i data-lucide="dollar-sign"></i>
                <span>Finance</span>
            </a>
            <a href="coupons.php" class="admin-sidebar-item">
                <i data-lucide="ticket"></i>
                <span>Discount Coupons</span>
            </a>
            <a href="orders.php" class="admin-sidebar-item">
                <i data-lucide="shopping-bag"></i>
                <span>Orders</span>
            </a>
            <a href="requests.php" class="admin-sidebar-item">
                <i data-lucide="inbox"></i>
                <span>B2B Requests</span>
            </a>
            <a href="report.php" class="admin-sidebar-item">
                <i data-lucide="file-text"></i>
                <span>Reports</span>
            </a>
            <a href="users.php" class="admin-sidebar-item">
                <i data-lucide="users"></i>
                <span>Users</span>
            </a>
            <a href="roles.php" class="admin-sidebar-item">
                <i data-lucide="shield"></i>
                <span>Roles</span>
            </a>
            <a href="api_integration.php" class="admin-sidebar-item active">
                <i data-lucide="plug"></i>
                <span>API Integration</span>
            </a>
            <a href="settings.php" class="admin-sidebar-item">
                <i data-lucide="settings"></i>
                <span>Settings</span>
            </a>
        </nav>

        <!-- Bottom Section -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200 bg-white">
            <a href="../logout.php" class="admin-sidebar-item text-teal-600">
                <i data-lucide="log-out"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="admin-main">
        <!-- Header -->
        <div class="admin-header">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">API Integration</h1>
                <p class="text-sm text-gray-600 mt-1">Manage POS system API keys and credentials</p>
            </div>
            <?php if ($action === 'list'): ?>
            <button 
                onclick="window.location.href='?action=create'" 
                class="px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors flex items-center gap-2">
                <i data-lucide="plus"></i>
                Create API Key
            </button>
            <?php endif; ?>
        </div>

        <!-- Content -->
        <div class="p-8">
            <!-- Messages -->
            <?php if ($message): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700 flex items-center gap-2">
                <i data-lucide="check-circle"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="mb-6 p-4 bg-teal-50 border border-red-200 rounded-lg text-red-700 flex items-center gap-2">
                <i data-lucide="alert-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php endif; ?>

            <!-- Show New Credentials -->
            <?php if ($showSecret && $newApiKey && $newApiSecret): ?>
            <div class="mb-6 p-6 bg-yellow-50 border-2 border-yellow-400 rounded-lg">
                <h3 class="text-lg font-semibold text-yellow-900 mb-4">⚠️ Important: Save These Credentials</h3>
                <p class="text-sm text-yellow-800 mb-4">The secret will not be shown again. Copy and store it securely.</p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client ID (API Key)</label>
                        <div class="flex items-center gap-2">
                            <input type="text" id="api-key-display" value="<?php echo htmlspecialchars($newApiKey); ?>" 
                                   class="flex-1 px-4 py-2 bg-white border border-gray-300 rounded-lg font-mono text-sm" readonly>
                            <button onclick="copyToClipboard('api-key-display')" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg">
                                <i data-lucide="copy"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client Secret</label>
                        <div class="flex items-center gap-2">
                            <input type="text" id="api-secret-display" value="<?php echo htmlspecialchars($newApiSecret); ?>" 
                                   class="flex-1 px-4 py-2 bg-white border border-gray-300 rounded-lg font-mono text-sm" readonly>
                            <button onclick="copyToClipboard('api-secret-display')" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg">
                                <i data-lucide="copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
            <!-- API Keys List -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <?php if (empty($apiKeys)): ?>
                <div class="p-12 text-center">
                    <i data-lucide="plug" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">No API Keys</h3>
                    <p class="text-gray-600 mb-6">Create your first API key to enable POS integration</p>
                    <button onclick="window.location.href='?action=create'" class="px-6 py-3 bg-teal-500 text-white rounded-lg hover:bg-teal-600">
                        Create API Key
                    </button>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase">Key Name</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase">API Key</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase">Tenant</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase">Requests</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase">Last Used</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($apiKeys as $key): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900"><?php echo htmlspecialchars($key['key_name']); ?></div>
                                    <?php if ($key['notes']): ?>
                                    <div class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars(substr($key['notes'], 0, 50)); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <code class="text-sm text-gray-700 font-mono"><?php echo htmlspecialchars($key['api_key']); ?></code>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php echo $key['tenant_name'] ? htmlspecialchars($key['tenant_name']) : '—'; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($key['is_active']): ?>
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">Active</span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-full">Inactive</span>
                                    <?php endif; ?>
                                    <?php if ($key['expires_at'] && strtotime($key['expires_at']) < time()): ?>
                                    <span class="px-2 py-1 text-xs font-medium bg-teal-100 text-red-700 rounded-full ml-1">Expired</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php echo number_format($key['request_count'] ?? 0); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php 
                                    if ($key['last_used_at']) {
                                        echo date('M d, Y H:i', strtotime($key['last_used_at']));
                                    } else {
                                        echo 'Never';
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="?action=edit&id=<?php echo $key['id']; ?>" 
                                           class="px-3 py-1 text-sm text-gray-700 hover:bg-gray-100 rounded transition-colors">
                                            Edit
                                        </a>
                                        <form method="post" action="?action=delete&id=<?php echo $key['id']; ?>" 
                                              class="inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this API key?');">
                                            <button type="submit" class="px-3 py-1 text-sm text-teal-600 hover:bg-teal-50 rounded transition-colors">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($action === 'create' || $action === 'edit'): ?>
            <!-- Create/Edit Form -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 max-w-3xl">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <?php echo $action === 'create' ? 'Create New API Key' : 'Edit API Key'; ?>
                    </h2>
                    <a href="?action=list" class="text-gray-600 hover:text-gray-800">
                        <i data-lucide="x"></i>
                    </a>
                </div>

                <form method="post" action="?action=<?php echo $action; ?><?php echo $apiKeyId ? '&id=' . $apiKeyId : ''; ?>" class="space-y-6">
                    <!-- Key Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Key Name *</label>
                        <input type="text" name="key_name" required
                               value="<?php echo htmlspecialchars($currentApiKey['key_name'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="e.g., NomadsCipher POS Main">
                    </div>

                    <!-- Tenant Info -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tenant ID (Optional)</label>
                            <input type="text" name="tenant_id"
                                   value="<?php echo htmlspecialchars($currentApiKey['tenant_id'] ?? ''); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                   placeholder="POS Tenant ID">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tenant Name (Optional)</label>
                            <input type="text" name="tenant_name"
                                   value="<?php echo htmlspecialchars($currentApiKey['tenant_name'] ?? ''); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                   placeholder="e.g., Tivora Store">
                        </div>
                    </div>

                    <!-- Expiration -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expiration Date (Optional)</label>
                        <input type="datetime-local" name="expires_at"
                               value="<?php echo $currentApiKey['expires_at'] ? date('Y-m-d\TH:i', strtotime($currentApiKey['expires_at'])) : ''; ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <!-- Allowed IPs -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Allowed IP Addresses (Optional)</label>
                        <input type="text" name="allowed_ips"
                               value="<?php echo htmlspecialchars($currentApiKey['allowed_ips'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="192.168.1.1, 10.0.0.1 (comma-separated)">
                        <p class="mt-1 text-xs text-gray-500">Leave empty to allow all IPs</p>
                    </div>

                    <!-- Scopes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">API Scopes</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="scopes[]" value="products"
                                       <?php echo ($currentApiKey && in_array('products', $currentApiKey['scopes'] ?? [])) ? 'checked' : 'checked'; ?>>
                                <span>Products (Read/Write)</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="scopes[]" value="orders"
                                       <?php echo ($currentApiKey && in_array('orders', $currentApiKey['scopes'] ?? [])) ? 'checked' : 'checked'; ?>>
                                <span>Orders (Read)</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="scopes[]" value="finance"
                                       <?php echo ($currentApiKey && in_array('finance', $currentApiKey['scopes'] ?? [])) ? 'checked' : 'checked'; ?>>
                                <span>Finance (Read)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                        <textarea name="notes" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                  placeholder="Additional notes about this API key"><?php echo htmlspecialchars($currentApiKey['notes'] ?? ''); ?></textarea>
                    </div>

                    <?php if ($action === 'edit'): ?>
                    <!-- Active Toggle -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Active</label>
                            <p class="text-xs text-gray-500">Enable or disable this API key</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1"
                                   <?php echo ($currentApiKey['is_active'] ?? 0) ? 'checked' : ''; ?>
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-500"></div>
                        </label>
                    </div>

                    <!-- Regenerate Secret -->
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <h3 class="text-sm font-semibold text-yellow-900 mb-2">Regenerate Secret</h3>
                        <p class="text-xs text-yellow-800 mb-3">Generate a new client secret. The old secret will be invalidated.</p>
                        <form method="post" action="?action=regenerate_secret&id=<?php echo $apiKeyId; ?>" 
                              onsubmit="return confirm('Are you sure? The current secret will stop working immediately.');">
                            <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 text-sm">
                                Regenerate Secret
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>

                    <!-- Form Actions -->
                    <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                        <button type="submit" class="px-6 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-medium">
                            <?php echo $action === 'create' ? 'Create API Key' : 'Update API Key'; ?>
                        </button>
                        <a href="?action=list" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        lucide.createIcons();
        
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            element.select();
            element.setSelectionRange(0, 99999); // For mobile devices
            navigator.clipboard.writeText(element.value);
            
            // Show feedback
            const button = event.target.closest('button');
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i data-lucide="check"></i>';
            lucide.createIcons();
            
            setTimeout(() => {
                button.innerHTML = originalHTML;
                lucide.createIcons();
            }, 2000);
        }
    </script>
</body>
</html>
