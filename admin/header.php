<?php
/**
 * Admin Header Component
 * Reusable header for all admin pages
 * 
 * Usage: include 'header.php';
 * 
 * Optional parameters:
 * - $pageTitle: Page title to display (default: current page name)
 * - $showDate: Show current date (default: true)
 * - $showUser: Show user info (default: true)
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get admin info
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminEmail = $_SESSION['admin_email'] ?? '';
$adminId = $_SESSION['admin_id'] ?? null;

// Get first name for display
$adminFirstName = '';
if ($adminName) {
    $nameParts = explode(' ', $adminName);
    $adminFirstName = $nameParts[0];
}

// Default values
$pageTitle = $pageTitle ?? 'Dashboard';
$showDate = $showDate ?? true;
$showUser = $showUser ?? true;
?>
<!-- Top Header -->
<header class="admin-header">
    <div class="flex items-center gap-4">
        <button id="sidebar-toggle" class="lg:hidden text-gray-600 hover:text-brand transition-colors" onclick="toggleSidebar()">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
        <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($pageTitle); ?></h1>
    </div>
    <div class="flex items-center gap-4">
        <?php if ($showDate): ?>
        <div class="text-sm text-gray-600 hidden sm:block">
            <span id="current-date"></span>
        </div>
        <?php endif; ?>
        <!-- Page-specific action buttons -->
        <div id="page-actions"></div>
        <?php if ($showUser && $adminId): ?>
        <div class="flex items-center gap-3">
            <div class="text-right hidden md:block">
                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($adminFirstName); ?></p>
                <p class="text-xs text-gray-500 truncate max-w-[150px]"><?php echo htmlspecialchars($adminEmail); ?></p>
            </div>
            <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center cursor-pointer hover:bg-teal-200 transition-colors" 
                 onclick="window.location.href='../account.php'"
                 title="View Profile">
                <span class="text-teal-600 font-semibold text-sm">
                    <?php echo strtoupper(substr($adminFirstName, 0, 1)); ?>
                </span>
            </div>
        </div>
        <?php endif; ?>
    </div>
</header>

<script>
    // Update current date
    <?php if ($showDate): ?>
    const updateDate = () => {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const dateElement = document.getElementById('current-date');
        if (dateElement) {
            dateElement.textContent = now.toLocaleDateString('en-US', options);
        }
    };
    updateDate();
    <?php endif; ?>

    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
