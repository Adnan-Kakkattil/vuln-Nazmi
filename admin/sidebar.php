<?php
/**
 * Admin Sidebar Component
 * Reusable sidebar for all admin pages
 * 
 * Usage: include 'sidebar.php';
 * 
 * The active menu item is automatically determined based on the current page parameter
 */

// Load permissions helper
require_once __DIR__ . '/includes/permissions.php';

// Get current page parameter or default to 'dashboard'
$currentPageParam = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Get accessible modules for current user
$accessibleModules = getAccessibleModules();

// Define menu items - all routes go through dashboard.php with page parameter
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

$allMenuItems = [
    [
        'page' => 'dashboard',
        'icon' => 'layout-dashboard',
        'label' => 'Dashboard',
        'title' => 'Dashboard'
    ],
    [
        'page' => 'stock',
        'icon' => 'package',
        'label' => 'Stock Management',
        'title' => 'Stock Management'
    ],
    [
        'page' => 'finance',
        'icon' => 'dollar-sign',
        'label' => 'Finance',
        'title' => 'Finance'
    ],
    [
        'page' => 'coupons',
        'icon' => 'ticket',
        'label' => 'Discount Coupons',
        'title' => 'Discount Coupons'
    ],
    [
        'page' => 'orders',
        'icon' => 'shopping-bag',
        'label' => 'Orders',
        'title' => 'Orders'
    ],
    [
        'page' => 'requests',
        'icon' => 'inbox',
        'label' => 'B2B Requests',
        'title' => 'B2B Requests'
    ],
    [
        'page' => 'reports',
        'icon' => 'file-text',
        'label' => 'Reports',
        'title' => 'Reports'
    ],
    [
        'page' => 'users',
        'icon' => 'users',
        'label' => 'Users',
        'title' => 'Users'
    ],
    [
        'page' => 'roles',
        'icon' => 'shield',
        'label' => 'Roles',
        'title' => 'Roles'
    ],
    [
        'page' => 'api-integration',
        'icon' => 'plug',
        'label' => 'API Integration',
        'title' => 'API Integration'
    ],
    [
        'page' => 'settings',
        'icon' => 'settings',
        'label' => 'Settings',
        'title' => 'Settings'
    ]
];

// Filter menu items based on user's module access
$menuItems = [];
foreach ($allMenuItems as $item) {
    $module = $pageToModuleMap[$item['page']] ?? $item['page'];
    // Check if user has access to this module
    if (hasModuleAccess($module)) {
        $menuItems[] = $item;
    }
}

// Function to check if menu item is active
function isMenuActive($menuItem, $currentPageParam) {
    return $menuItem['page'] === $currentPageParam;
}
?>
<!-- Sidebar -->
<aside class="admin-sidebar" id="admin-sidebar">
    <!-- Logo -->
    <div class="p-6 border-b border-gray-200">
        <a href="../index.php" class="flex items-center gap-2">
            <h2 class="text-xl font-bold text-brand">NAZMI BOUTIQUE</h2>
            <span class="text-xs text-gray-500 font-medium ml-1">Admin</span>
        </a>
    </div>

    <!-- Navigation Menu -->
    <nav class="py-4 flex-1 overflow-y-auto">
        <?php foreach ($menuItems as $item): ?>
            <a href="dashboard.php?page=<?php echo htmlspecialchars($item['page']); ?>" 
               class="admin-sidebar-item <?php echo isMenuActive($item, $currentPageParam) ? 'active' : ''; ?>"
               data-page="<?php echo htmlspecialchars($item['page']); ?>">
                <i data-lucide="<?php echo htmlspecialchars($item['icon']); ?>"></i>
                <span><?php echo htmlspecialchars($item['label']); ?></span>
            </a>
        <?php endforeach; ?>

    <!-- Bottom Section -->
    <div class="p-4 border-t border-gray-200 bg-white flex-shrink-0">
        <a href="../index.php" class="admin-sidebar-item">
            <i data-lucide="arrow-left"></i>
            <span>Back to Website</span>
        </a>
        <a href="#" class="admin-sidebar-item" onclick="openLogoutModal(); return false;">
            <i data-lucide="log-out"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>

<!-- Logout Confirmation Modal -->
<div class="modal-overlay" id="logout-modal-overlay" onclick="closeLogoutModal(event)">
    <div class="modal" onclick="event.stopPropagation()" style="max-width: 450px;">
        <div class="p-6">
            <!-- Modal Header -->
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="log-out" class="w-6 h-6 text-red-600"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Confirm Logout</h2>
                    <p class="text-sm text-gray-500">Are you sure you want to logout?</p>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="mb-6">
                <p class="text-sm text-gray-600">
                    You will be redirected to the login page. Make sure you have saved any unsaved changes.
                </p>
            </div>

            <!-- Modal Actions -->
            <div class="flex flex-col sm:flex-row gap-3">
                <button
                    onclick="confirmLogout()"
                    class="flex-1 bg-red-500 text-white py-3 px-6 rounded-lg font-semibold hover:bg-red-600 transition-colors shadow-md shadow-red-100 flex items-center justify-center gap-2"
                >
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                    <span>Yes, Logout</span>
                </button>
                <button
                    onclick="closeLogoutModal()"
                    class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 hover:border-gray-400 transition-colors"
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Menu Overlay -->
<div class="mobile-menu-overlay" id="mobile-menu-overlay" onclick="toggleSidebar()"></div>

<script>
    // Toggle sidebar on mobile
    function toggleSidebar() {
        const sidebar = document.getElementById('admin-sidebar');
        const overlay = document.getElementById('mobile-menu-overlay');
        if (sidebar) {
            sidebar.classList.toggle('show');
        }
        if (overlay) {
            overlay.classList.toggle('show');
        }
    }

    // Open Logout Modal
    function openLogoutModal() {
        const overlay = document.getElementById('logout-modal-overlay');
        if (overlay) {
            overlay.classList.add('show');
            // Reinitialize icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
    }

    // Close Logout Modal
    function closeLogoutModal(event) {
        if (!event || event.target.id === 'logout-modal-overlay') {
            const overlay = document.getElementById('logout-modal-overlay');
            if (overlay) {
                overlay.classList.remove('show');
            }
        }
    }

    // Confirm Logout
    async function confirmLogout() {
        const logoutBtn = event.target.closest('button');
        if (logoutBtn) {
            logoutBtn.disabled = true;
            logoutBtn.innerHTML = '<div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div><span>Logging out...</span>';
        }
        
        try {
            const response = await fetch('/api/v1/admin/auth.php', {
                method: 'DELETE',
                credentials: 'include'
            });
            
            // Close modal
            closeLogoutModal();
            
            // Redirect to login page regardless of API response
            window.location.href = '../login.php';
        } catch (error) {
            console.error('Logout error:', error);
            // Still redirect to login page
            closeLogoutModal();
            window.location.href = '../login.php';
        }
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const overlay = document.getElementById('logout-modal-overlay');
            if (overlay && overlay.classList.contains('show')) {
                closeLogoutModal();
            }
        }
    });

    // Make functions available globally
    window.toggleSidebar = toggleSidebar;
    window.openLogoutModal = openLogoutModal;
    window.closeLogoutModal = closeLogoutModal;
    window.confirmLogout = confirmLogout;

    // Initialize Lucide icons when sidebar is loaded
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
