<?php
/**
 * Admin Dashboard - Single Page Application Router
 * Handles routing for all admin pages via ?page= parameter
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
$isAdminLoggedIn = isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);

if (!$isAdminLoggedIn) {
    header('Location: ../login.php?message=admin_required');
    exit;
}

// Load permissions helper
require_once __DIR__ . '/includes/permissions.php';

// Get page parameter or default to 'dashboard'
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Define valid pages and their titles
$validPages = [
    'dashboard' => 'Dashboard',
    'stock' => 'Stock Management',
    'finance' => 'Finance',
    'coupons' => 'Discount Coupons',
    'orders' => 'Orders',
    'requests' => 'B2B Requests',
    'reports' => 'Reports',
    'users' => 'Users',
    'roles' => 'Roles',
    'api-integration' => 'API Integration',
    'settings' => 'Settings',
    'test-email' => 'Test Email'
];

// Map page names to module names for permission checking
$pageToModuleMap = [
    'dashboard' => 'dashboard',
    'stock' => 'stock',
    'finance' => 'finance',
    'coupons' => 'coupons',
    'orders' => 'orders',
    'requests' => 'b2b',
    'reports' => 'reports',
    'users' => 'users',
    'roles' => 'users', // Roles management is part of users module
    'api-integration' => 'settings', // API Integration is part of settings
    'settings' => 'settings'
];

// Validate page parameter
if (!isset($validPages[$page])) {
    $page = 'dashboard';
}

// Check if user has access to this page/module
$module = $pageToModuleMap[$page] ?? $page;
if (!hasModuleAccess($module)) {
    // User doesn't have access, redirect to dashboard or first accessible page
    $accessibleModules = getAccessibleModules();
    if (in_array('dashboard', $accessibleModules)) {
        header('Location: dashboard.php?page=dashboard&message=access_denied');
    } else {
        // If no dashboard access, redirect to login
        header('Location: ../login.php?message=access_denied');
    }
    exit;
}

$pageTitle = $validPages[$page];
$pageFile = 'pages/' . $page . '.php';

// Check if page file exists, otherwise use dashboard
if (!file_exists($pageFile)) {
    $page = 'dashboard';
    $pageFile = 'pages/dashboard.php';
    $pageTitle = 'Dashboard';
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | NAZMI BOUTIQUE Admin</title>
    
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
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- Common Admin Styles -->
    <?php include 'common-styles.php'; ?>
    
    <!-- Dashboard Specific Styles -->
    <style>

    </style>
</head>
<body class="antialiased">
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main class="admin-main">
        <?php 
        include 'header.php'; 
        ?>

        <!-- Page Content -->
        <div id="page-content" class="p-6 lg:p-8">
            <?php include $pageFile; ?>
        </div>
    </main>

    <!-- Global Admin Scripts -->
    <script>
        // API base URL
        const API_BASE = '/api/v1/admin';
        
        // Current page
        const currentPage = '<?php echo htmlspecialchars($page); ?>';

        // Set page-specific action buttons
        function setPageActions() {
            const pageActionsDiv = document.getElementById('page-actions');
            if (!pageActionsDiv) {
                // Retry after a short delay if element not found
                setTimeout(() => {
                    const retryDiv = document.getElementById('page-actions');
                    if (retryDiv) {
                        setPageActions();
                    }
                }, 100);
                return;
            }

            const actionButtons = {
                'stock': `
                    <button onclick="if (typeof openAddProductModal === 'function') { openAddProductModal(); } else { alert('Function not available'); }" 
                            class="flex items-center gap-2 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Add Product</span>
                    </button>
                `,
                'finance': `
                    <button onclick="if (typeof openAddTransactionModal === 'function') { openAddTransactionModal(); } else { alert('Function not available'); }" 
                            class="flex items-center gap-2 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Add Transaction</span>
                    </button>
                `,
                'coupons': `
                    <button onclick="if (typeof openAddCouponModal === 'function') { openAddCouponModal(); } else { alert('Function not available'); }" 
                            class="flex items-center gap-2 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Add Coupon</span>
                    </button>
                `,
                'users': `
                    <button onclick="if (typeof openCreateUserModal === 'function') { openCreateUserModal(); } else { alert('Function not available'); }" 
                            class="flex items-center gap-2 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <span>Add User</span>
                    </button>
                `,
                'roles': `
                    <button onclick="if (typeof openCreateRoleModal === 'function') { openCreateRoleModal(); } else { alert('Function not available'); }" 
                            class="flex items-center gap-2 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors">
                        <i data-lucide="shield-plus" class="w-4 h-4"></i>
                        <span>Create Role</span>
                    </button>
                `,
                'api-integration': `
                    <button onclick="if (typeof openCreateApiKeyModal === 'function') { openCreateApiKeyModal(); } else { alert('Function not available'); }" 
                            class="flex items-center gap-2 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Create API Key</span>
                    </button>
                `,
                'settings': `
                    <button id="save-btn" onclick="if (typeof saveAllSettings === 'function') { saveAllSettings(); } else { alert('Function not available'); }" 
                            class="flex items-center gap-2 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span id="save-btn-text">Save Changes</span>
                        <div id="save-spinner" class="hidden w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    </button>
                `,
                'reports': `
                    <button onclick="if (typeof exportReport === 'function') { exportReport(); } else { alert('Function not available'); }" 
                            class="flex items-center gap-2 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>Export Report</span>
                    </button>
                `,
                'stock': `
                    <button onclick="if (typeof openAddProductModal === 'function') { openAddProductModal(); } else { alert('Function not available'); }" 
                            class="flex items-center gap-2 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Add Product</span>
                    </button>
                `
            };

            if (actionButtons[currentPage]) {
                pageActionsDiv.innerHTML = actionButtons[currentPage];
                // Reinitialize icons after adding button
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            } else {
                pageActionsDiv.innerHTML = '';
            }
        }

        // Initialize Lucide Icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Set page actions when DOM is ready or immediately if already loaded
        function initPageActions() {
            setPageActions();
            // Reinitialize icons after setting actions
            setTimeout(() => {
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
                // Double-check settings page button is visible
                if (currentPage === 'settings') {
                    const saveBtn = document.getElementById('save-btn');
                    if (saveBtn) {
                        saveBtn.style.display = 'flex';
                    }
                }
            }, 100);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPageActions);
        } else {
            // DOM is already loaded
            initPageActions();
        }
        
        // Also try setting page actions after a delay to ensure header is loaded
        setTimeout(() => {
            if (currentPage === 'settings') {
                setPageActions();
                const saveBtn = document.getElementById('save-btn');
                if (saveBtn) {
                    saveBtn.style.display = 'flex';
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }
            }
        }, 500);

        // Reinitialize icons periodically for dynamic content
        setInterval(() => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }, 1000);
    </script>
</body>
</html>
