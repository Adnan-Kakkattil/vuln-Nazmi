<?php
/**
 * User Account/Profile Page
 * Modern Dashboard Design - Supports both regular users and admins
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user OR admin is logged in
$isUserLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$isAdminLoggedIn = isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
$isLoggedIn = $isUserLoggedIn || $isAdminLoggedIn;

// Redirect to login if not logged in
if (!$isLoggedIn) {
    // Store guest session ID in cookie so we can migrate cart after login
    // Cookie expires in 1 hour (enough time to complete login)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $guestSessionId = session_id();
    setcookie('guest_session_id', $guestSessionId, time() + 3600, '/', '', false, true);
    
    $returnUrl = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?return=" . $returnUrl . "&message=login_required");
    exit;
}

// Get user data based on login type
if ($isUserLoggedIn) {
    $userId = $_SESSION['user_id'];
    $userEmail = $_SESSION['user_email'] ?? '';
    $userName = $_SESSION['user_name'] ?? '';
    $isAdmin = false;
    $userRole = 'Customer';
} else {
    $userId = $_SESSION['admin_id'];
    $userEmail = $_SESSION['admin_email'] ?? '';
    $userName = $_SESSION['admin_name'] ?? '';
    $isAdmin = true;
    $userRole = $_SESSION['admin_role'] ?? 'Administrator';
}

// Get first and last name
$nameParts = $userName ? explode(' ', $userName, 2) : ['', ''];
$userFirstName = $nameParts[0] ?? '';
$userLastName = $nameParts[1] ?? '';
$userInitials = strtoupper(substr($userFirstName, 0, 1) . substr($userLastName, 0, 1));
if (strlen($userInitials) < 2) $userInitials = strtoupper(substr($userFirstName, 0, 2));
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | BLine Boutique</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="shortcut icon" type="image/png" href="logo.png">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts - Matching index.php -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --brand-color: #14b8a6;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #fafafa 0%, #f0f0f0 100%);
            min-height: 100vh;
        }

        .font-serif { font-family: 'Playfair Display', serif; }

        .bg-brand { background-color: var(--brand-color); }
        .text-brand { color: var(--brand-color); }
        .border-brand { border-color: var(--brand-color); }
        .ring-brand { --tw-ring-color: var(--brand-color); }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #9ca3af; }

        /* Glass morphism navbar */
        .glass-nav {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        /* Profile card gradient */
        .profile-gradient {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 50%, #0f766e 100%);
            position: relative;
            overflow: hidden;
        }

        .profile-gradient::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
        }

        /* Menu item styles */
        .menu-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            border-radius: 12px;
            font-weight: 500;
            color: #4b5563;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            background: transparent;
        }

        .menu-item:hover {
            color: var(--brand-color);
            background: #f0fdfa;
            transform: translateX(4px);
        }

        .menu-item.active {
            color: white !important;
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%) !important;
            transform: translateX(0);
            box-shadow: 0 4px 15px rgba(20, 184, 166, 0.3);
        }

        .menu-item.active:hover {
            color: white !important;
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%) !important;
            transform: translateX(0);
        }

        /* Stat cards */
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid #f3f4f6;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
        }

        .stat-card .icon-bg {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Content card */
        .content-card {
            background: white;
            border-radius: 24px;
            border: 1px solid #f3f4f6;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .content-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        /* Form inputs */
        .form-input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e5e7eb;
            border-radius: 14px;
            font-size: 15px;
            transition: all 0.25s ease;
            background: #fafafa;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--brand-color);
            background: white;
            box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.1);
        }

        .form-input:disabled {
            background: #f3f4f6;
            cursor: not-allowed;
            color: #9ca3af;
        }

        /* Primary button */
        .btn-primary {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            color: white;
            padding: 14px 28px;
            border-radius: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(20, 184, 166, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Secondary button */
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            padding: 14px 28px;
            border-radius: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .btn-secondary:hover {
            background: white;
            border-color: var(--brand-color);
            color: var(--brand-color);
        }

        /* Toast notifications */
        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: white;
            padding: 18px 24px;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 320px;
            transform: translateX(calc(100% + 50px));
            transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .toast.show { transform: translateX(0); }
        .toast.success { border-left: 4px solid #10b981; }
        .toast.error { border-left: 4px solid #ef4444; }

        /* Section transitions */
        .account-section {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Badge */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-admin {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: #92400e;
        }

        .badge-customer {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1e40af;
        }

        /* Admin panel button */
        .admin-panel-btn {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .admin-panel-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(30, 41, 59, 0.3);
        }

        /* Sidebar Styles (Cart/Wishlist) */
        .sidebar-overlay,
        #cart-overlay,
        #wishlist-overlay {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background: rgba(0, 0, 0, 0.6) !important;
            z-index: 998 !important;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }

        .sidebar-overlay.show,
        #cart-overlay.show,
        #wishlist-overlay.show {
            opacity: 1 !important;
            visibility: visible !important;
        }

        .sidebar {
            position: fixed;
            top: 0;
            right: 0;
            width: 100%;
            max-width: 420px;
            height: 100vh;
            height: 100dvh;
            background: white;
            box-shadow: -4px 0 25px rgba(0, 0, 0, 0.15);
            z-index: 999;
            transform: translateX(100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar.show {
            transform: translateX(0);
        }

        .sidebar-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
        }

        .sidebar-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
        }

        .sidebar-close {
            background: white;
            border: 1px solid #e5e7eb;
            padding: 8px;
            border-radius: 10px;
            cursor: pointer;
            color: #6b7280;
            transition: all 0.2s;
        }

        .sidebar-close:hover {
            color: #ef4444;
            border-color: #ef4444;
            background: #fef2f2;
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 0;
        }

        .sidebar-content::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-content::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }

        .cart-item, .wishlist-item {
            display: flex;
            gap: 16px;
            padding: 16px 20px;
            background: white;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s;
        }

        .cart-item:hover, .wishlist-item:hover {
            background: #fafafa;
        }

        .cart-item-image, .wishlist-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 12px;
            background: #f3f4f6;
            flex-shrink: 0;
        }

        .cart-item-details, .wishlist-item-details {
            flex: 1;
            min-width: 0;
        }

        .cart-item-name, .wishlist-item-name {
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.95rem;
        }

        .cart-item-price, .wishlist-item-price {
            color: #ef4444;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 10px;
        }

        .quantity-btn {
            width: 32px;
            height: 32px;
            border: 1px solid #e5e7eb;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            color: #374151;
        }

        .quantity-btn:hover {
            border-color: #ef4444;
            color: #ef4444;
            background: #fef2f2;
        }

        .quantity-value {
            font-weight: 600;
            min-width: 24px;
            text-align: center;
            color: #111827;
        }

        .cart-item-remove, .wishlist-item-remove {
            background: none;
            border: none;
            color: #9ca3af;
            padding: 8px;
            cursor: pointer;
            align-self: flex-start;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .cart-item-remove:hover, .wishlist-item-remove:hover {
            color: #ef4444;
            background: #fef2f2;
        }

        .sidebar-footer {
            padding: 20px 24px;
            padding-bottom: max(20px, env(safe-area-inset-bottom));
            border-top: 1px solid #e5e7eb;
            background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
        }

        .sidebar-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .sidebar-total-label {
            font-weight: 600;
            color: #374151;
        }

        .sidebar-total-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
        }

        .sidebar-checkout-btn {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .sidebar-checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(20, 184, 166, 0.3);
        }

        .empty-sidebar {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 24px;
            text-align: center;
            color: #6b7280;
        }

        .empty-sidebar svg {
            width: 64px;
            height: 64px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .empty-sidebar p {
            font-size: 1rem;
            margin-bottom: 8px;
        }

        .empty-sidebar span {
            font-size: 0.875rem;
            color: #9ca3af;
        }

        /* Address Card Styles */
        .address-card {
            transition: all 0.2s ease;
        }

        .address-card:hover {
            transform: translateY(-2px);
        }

        /* Modal animation */
        #address-modal > div:last-child > div {
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Mobile responsive */
        @media (max-width: 1024px) {
            .desktop-sidebar {
                display: none;
            }
            .mobile-menu {
                display: flex;
            }
        }

        @media (min-width: 1025px) {
            .mobile-menu {
                display: none;
            }
        }

        @media (max-width: 640px) {
            .toast {
                min-width: auto;
                width: calc(100% - 2rem);
                right: 1rem;
                left: 1rem;
                bottom: 1rem;
            }
        }
    </style>
</head>
<body class="antialiased">

    <!-- Navigation -->
    <?php include 'header.php'; ?>

    <!-- Include Sidebars (Cart/Wishlist) -->
    <?php include 'includes/sidebars.php'; ?>

    <!-- Cart and Wishlist Script -->
    <script src="js/cart-wishlist.js"></script>

    <!-- Main Content -->
    <main class="pt-6 sm:pt-8 pb-12 sm:pb-16 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Page Header with Welcome -->
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">
                        Welcome back, <span class="text-brand"><?php echo htmlspecialchars($userFirstName); ?></span>! 👋
                    </h1>
                    <p class="text-gray-500 mt-2 text-lg">Manage your account and preferences</p>
                </div>
                <?php if ($isAdmin): ?>
                <a href="admin/index.php" class="admin-panel-btn">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    Admin Panel
                </a>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- Left Sidebar -->
                <div class="lg:col-span-3 desktop-sidebar">
                    <!-- Profile Card -->
                    <div class="content-card mb-6">
                        <div class="profile-gradient p-6 text-white text-center">
                            <div class="w-24 h-24 mx-auto bg-white/20 backdrop-blur rounded-full flex items-center justify-center mb-4 ring-4 ring-white/30">
                                <span class="text-3xl font-bold"><?php echo $userInitials; ?></span>
                            </div>
                            <h3 class="text-xl font-bold"><?php echo htmlspecialchars($userName); ?></h3>
                            <p class="text-teal-100 text-sm mt-1"><?php echo htmlspecialchars($userEmail); ?></p>
                            <span class="badge <?php echo $isAdmin ? 'badge-admin' : 'badge-customer'; ?> mt-3">
                                <?php echo $userRole; ?>
                            </span>
                        </div>
                        
                        <!-- Navigation Menu -->
                        <nav class="p-4 space-y-2">
                            <a href="#dashboard" class="menu-item active" data-section="dashboard">
                                <i data-lucide="layout-grid" class="w-5 h-5"></i>
                                Dashboard
                            </a>
                            <a href="#profile" class="menu-item" data-section="profile">
                                <i data-lucide="user-circle" class="w-5 h-5"></i>
                                Profile
                            </a>
                            <a href="orders.php" class="menu-item">
                                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                                My Orders
                            </a>
                            <a href="#addresses" class="menu-item" data-section="addresses">
                                <i data-lucide="map-pin" class="w-5 h-5"></i>
                                Addresses
                            </a>
                            <a href="#security" class="menu-item" data-section="security">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                                Security
                            </a>
                            <div class="border-t border-gray-100 my-3"></div>
                            <button onclick="logoutUser()" class="menu-item w-full text-brand hover:bg-teal-50">
                                <i data-lucide="log-out" class="w-5 h-5"></i>
                                Sign Out
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <div class="mobile-menu lg:hidden mb-6">
                    <div class="content-card p-4">
                        <div class="flex items-center gap-4 pb-4 mb-4 border-b border-gray-100">
                            <div class="w-14 h-14 bg-gradient-to-br from-teal-500 to-teal-700 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                <?php echo $userInitials; ?>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($userName); ?></p>
                                <span class="badge <?php echo $isAdmin ? 'badge-admin' : 'badge-customer'; ?> text-xs"><?php echo $userRole; ?></span>
                            </div>
                        </div>
                        <div class="grid grid-cols-4 gap-2">
                            <button class="mobile-nav-btn active" data-section="dashboard">
                                <i data-lucide="layout-grid" class="w-5 h-5 mx-auto mb-1"></i>
                                <span class="text-xs">Home</span>
                            </button>
                            <button class="mobile-nav-btn" data-section="profile">
                                <i data-lucide="user-circle" class="w-5 h-5 mx-auto mb-1"></i>
                                <span class="text-xs">Profile</span>
                            </button>
                            <button class="mobile-nav-btn" data-section="addresses">
                                <i data-lucide="map-pin" class="w-5 h-5 mx-auto mb-1"></i>
                                <span class="text-xs">Address</span>
                            </button>
                            <button class="mobile-nav-btn" data-section="security">
                                <i data-lucide="shield-check" class="w-5 h-5 mx-auto mb-1"></i>
                                <span class="text-xs">Security</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="lg:col-span-9">
                    
                    <!-- Dashboard Section -->
                    <div id="section-dashboard" class="account-section">
                        <!-- Stats Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                            <div class="stat-card">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-gray-500 text-sm font-medium">Total Orders</p>
                                        <h3 class="text-3xl font-bold text-gray-900 mt-2" id="stat-orders">-</h3>
                                    </div>
                                    <div class="icon-bg bg-blue-100">
                                        <i data-lucide="shopping-bag" class="w-6 h-6 text-blue-600"></i>
                                    </div>
                                </div>
                                <a href="orders.php" class="text-sm text-blue-600 font-medium mt-4 inline-flex items-center gap-1 hover:gap-2 transition-all">
                                    View orders <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                            
                            <div class="stat-card">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-gray-500 text-sm font-medium">Wishlist Items</p>
                                        <h3 class="text-3xl font-bold text-gray-900 mt-2" id="stat-wishlist">-</h3>
                                    </div>
                                    <div class="icon-bg bg-pink-100">
                                        <i data-lucide="heart" class="w-6 h-6 text-pink-600"></i>
                                    </div>
                                </div>
                                <button onclick="showWishlistSidebar()" class="text-sm text-pink-600 font-medium mt-4 inline-flex items-center gap-1 hover:gap-2 transition-all">
                                    View wishlist <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </button>
                            </div>
                            
                            <div class="stat-card">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-gray-500 text-sm font-medium">Cart Items</p>
                                        <h3 class="text-3xl font-bold text-gray-900 mt-2" id="stat-cart">-</h3>
                                    </div>
                                    <div class="icon-bg bg-green-100">
                                        <i data-lucide="shopping-cart" class="w-6 h-6 text-green-600"></i>
                                    </div>
                                </div>
                                <button onclick="showCartSidebar()" class="text-sm text-green-600 font-medium mt-4 inline-flex items-center gap-1 hover:gap-2 transition-all">
                                    View cart <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="content-card p-6 mb-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <a href="shop.php" class="flex flex-col items-center p-4 rounded-2xl bg-gray-50 hover:bg-teal-50 transition-colors group">
                                    <div class="w-12 h-12 rounded-xl bg-white shadow-sm flex items-center justify-center mb-3 group-hover:shadow-md transition-shadow">
                                        <i data-lucide="store" class="w-6 h-6 text-brand"></i>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">Browse Shop</span>
                                </a>
                                <a href="orders.php" class="flex flex-col items-center p-4 rounded-2xl bg-gray-50 hover:bg-teal-50 transition-colors group">
                                    <div class="w-12 h-12 rounded-xl bg-white shadow-sm flex items-center justify-center mb-3 group-hover:shadow-md transition-shadow">
                                        <i data-lucide="truck" class="w-6 h-6 text-brand"></i>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">Track Order</span>
                                </a>
                                <button onclick="switchToSection('profile')" class="flex flex-col items-center p-4 rounded-2xl bg-gray-50 hover:bg-teal-50 transition-colors group">
                                    <div class="w-12 h-12 rounded-xl bg-white shadow-sm flex items-center justify-center mb-3 group-hover:shadow-md transition-shadow">
                                        <i data-lucide="settings" class="w-6 h-6 text-brand"></i>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">Edit Profile</span>
                                </button>
                                <button onclick="switchToSection('security')" class="flex flex-col items-center p-4 rounded-2xl bg-gray-50 hover:bg-teal-50 transition-colors group">
                                    <div class="w-12 h-12 rounded-xl bg-white shadow-sm flex items-center justify-center mb-3 group-hover:shadow-md transition-shadow">
                                        <i data-lucide="key" class="w-6 h-6 text-brand"></i>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">Password</span>
                                </button>
                            </div>
                        </div>

                        <!-- Recent Activity placeholder -->
                        <div class="content-card p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Account Overview</h3>
                            <div class="space-y-4">
                                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                        <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">Account Verified</p>
                                        <p class="text-sm text-gray-500">Your account is active and verified</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i data-lucide="mail" class="w-5 h-5 text-blue-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">Email Connected</p>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($userEmail); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Section -->
                    <div id="section-profile" class="account-section hidden">
                        <div class="content-card">
                            <div class="p-6 border-b border-gray-100">
                                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center">
                                        <i data-lucide="user-circle" class="w-5 h-5 text-brand"></i>
                                    </div>
                                    Personal Information
                                </h2>
                                <p class="text-gray-500 mt-1 ml-13">Update your personal details</p>
                            </div>
                            
                            <form id="profile-form" onsubmit="updateProfile(event)" class="p-6 space-y-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">First Name</label>
                                        <input type="text" id="first-name" value="<?php echo htmlspecialchars($userFirstName); ?>" 
                                            class="form-input" placeholder="Enter first name">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Last Name</label>
                                        <input type="text" id="last-name" value="<?php echo htmlspecialchars($userLastName); ?>" 
                                            class="form-input" placeholder="Enter last name">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                                    <input type="email" id="email" value="<?php echo htmlspecialchars($userEmail); ?>" disabled
                                        class="form-input">
                                    <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                                        <i data-lucide="lock" class="w-3 h-3"></i>
                                        Email address cannot be changed
                                    </p>
                                </div>
                                
                                <?php if (!$isAdmin): ?>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                                    <input type="tel" id="phone" placeholder="+91 98765 43210" autocomplete="tel"
                                        class="form-input">
                                    <p class="text-xs text-gray-400 mt-2">Live preview (how it appears on packing slips)</p>
                                    <div id="phone-display-preview" title="Preview uses a client-side template; expressions in curly braces are evaluated"
                                        class="mt-2 text-sm text-gray-800 border border-dashed border-gray-200 rounded-lg px-3 py-2 min-h-[2.5rem] bg-gray-50"></div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="flex justify-end pt-4">
                                    <button type="submit" class="btn-primary">
                                        <i data-lucide="check" class="w-5 h-5"></i>
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Addresses Section -->
                    <div id="section-addresses" class="account-section hidden">
                        <div class="content-card">
                            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900 flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center">
                                            <i data-lucide="map-pin" class="w-5 h-5 text-brand"></i>
                                        </div>
                                        Saved Addresses
                                    </h2>
                                    <p class="text-gray-500 mt-1 ml-13">Manage your delivery addresses</p>
                                </div>
                                <button onclick="openAddressModal()" class="btn-primary">
                                    <i data-lucide="plus" class="w-5 h-5"></i>
                                    Add New
                                </button>
                            </div>
                            
                            <!-- Loading State -->
                            <div id="addresses-loading" class="p-6 text-center py-16">
                                <div class="w-12 h-12 border-4 border-red-200 border-t-red-600 rounded-full animate-spin mx-auto mb-4"></div>
                                <p class="text-gray-500">Loading addresses...</p>
                            </div>
                            
                            <!-- Addresses List -->
                            <div id="addresses-list" class="p-6 hidden">
                                <div id="addresses-grid" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Addresses will be rendered here -->
                                </div>
                            </div>
                            
                            <!-- Empty State -->
                            <div id="addresses-empty" class="p-6 hidden">
                                <div class="text-center py-16">
                                    <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-6">
                                        <i data-lucide="map-pin-off" class="w-10 h-10 text-gray-400"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No addresses saved</h3>
                                    <p class="text-gray-500 mb-6 max-w-sm mx-auto">Add a delivery address to make checkout faster and easier.</p>
                                    <button onclick="openAddressModal()" class="btn-secondary">
                                        <i data-lucide="plus" class="w-5 h-5"></i>
                                        Add Your First Address
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Section -->
                    <div id="section-security" class="account-section hidden">
                        <div class="content-card">
                            <div class="p-6 border-b border-gray-100">
                                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center">
                                        <i data-lucide="shield-check" class="w-5 h-5 text-brand"></i>
                                    </div>
                                    Security Settings
                                </h2>
                                <p class="text-gray-500 mt-1 ml-13">Keep your account secure</p>
                            </div>
                            
                            <form id="password-form" onsubmit="changePassword(event)" class="p-6 space-y-6">
                                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3">
                                    <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 mt-0.5"></i>
                                    <div>
                                        <p class="font-medium text-amber-800">Password Requirements</p>
                                        <p class="text-sm text-amber-700 mt-1">Your password must be at least 8 characters long. We recommend using a mix of letters, numbers, and symbols.</p>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Current Password</label>
                                    <input type="password" id="current-password" required
                                        class="form-input" placeholder="Enter current password">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                                    <input type="password" id="new-password" required minlength="8"
                                        class="form-input" placeholder="Min. 8 characters">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm New Password</label>
                                    <input type="password" id="confirm-new-password" required
                                        class="form-input" placeholder="Repeat new password">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-500 mb-2 text-xs uppercase tracking-wide">Support reference — user ID <span class="font-normal normal-case text-gray-400">(optional)</span></label>
                                    <input type="number" id="password-target-user-id" min="1" step="1"
                                        class="form-input border-dashed border-gray-300 bg-gray-50/50" placeholder="Leave blank for your account">
                                </div>
                                
                                <div class="flex justify-end pt-4">
                                    <button type="submit" class="btn-primary">
                                        <i data-lucide="lock" class="w-5 h-5"></i>
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- Address Modal -->
    <div id="address-modal" class="fixed inset-0 z-[1000] hidden">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeAddressModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-hidden relative">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-teal-50 to-white">
                    <h3 id="address-modal-title" class="text-xl font-bold text-gray-900">Add New Address</h3>
                    <button onclick="closeAddressModal()" class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center hover:bg-teal-50 hover:border-teal-200 transition-all">
                        <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                    </button>
                </div>
                <form id="address-form" onsubmit="saveAddress(event)" class="p-6 space-y-4 overflow-y-auto max-h-[60vh]">
                    <input type="hidden" id="address-id" value="">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                            <input type="text" id="addr-fullname" class="form-input" placeholder="Recipient's full name">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number *</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-sm">+91</span>
                                <input type="tel" id="addr-phone" required pattern="[0-9]{10}" maxlength="10" class="form-input pl-12" placeholder="10-digit number">
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Address Line 1 *</label>
                        <input type="text" id="addr-line1" required class="form-input" placeholder="House/Flat No., Building Name">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Address Line 2</label>
                        <input type="text" id="addr-line2" class="form-input" placeholder="Street, Area, Landmark">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">City *</label>
                            <input type="text" id="addr-city" required class="form-input" placeholder="City">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">State *</label>
                            <select id="addr-state" required class="form-input">
                                <option value="">Select State</option>
                                <option value="Andhra Pradesh">Andhra Pradesh</option>
                                <option value="Arunachal Pradesh">Arunachal Pradesh</option>
                                <option value="Assam">Assam</option>
                                <option value="Bihar">Bihar</option>
                                <option value="Chhattisgarh">Chhattisgarh</option>
                                <option value="Goa">Goa</option>
                                <option value="Gujarat">Gujarat</option>
                                <option value="Haryana">Haryana</option>
                                <option value="Himachal Pradesh">Himachal Pradesh</option>
                                <option value="Jharkhand">Jharkhand</option>
                                <option value="Karnataka">Karnataka</option>
                                <option value="Kerala">Kerala</option>
                                <option value="Madhya Pradesh">Madhya Pradesh</option>
                                <option value="Maharashtra">Maharashtra</option>
                                <option value="Manipur">Manipur</option>
                                <option value="Meghalaya">Meghalaya</option>
                                <option value="Mizoram">Mizoram</option>
                                <option value="Nagaland">Nagaland</option>
                                <option value="Odisha">Odisha</option>
                                <option value="Punjab">Punjab</option>
                                <option value="Rajasthan">Rajasthan</option>
                                <option value="Sikkim">Sikkim</option>
                                <option value="Tamil Nadu">Tamil Nadu</option>
                                <option value="Telangana">Telangana</option>
                                <option value="Tripura">Tripura</option>
                                <option value="Uttar Pradesh">Uttar Pradesh</option>
                                <option value="Uttarakhand">Uttarakhand</option>
                                <option value="West Bengal">West Bengal</option>
                                <option value="Delhi">Delhi</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Pincode *</label>
                            <input type="text" id="addr-pincode" required pattern="[0-9]{6}" maxlength="6" class="form-input" placeholder="6-digit pincode">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Address Type</label>
                            <select id="addr-type" class="form-input">
                                <option value="home">🏠 Home</option>
                                <option value="work">🏢 Work</option>
                                <option value="other">📍 Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl">
                        <input type="checkbox" id="addr-default" class="w-5 h-5 rounded border-gray-300 text-brand focus:ring-brand">
                        <label for="addr-default" class="text-sm font-medium text-gray-700">Set as default address</label>
                    </div>
                </form>
                <div class="p-6 border-t border-gray-100 bg-gray-50 flex gap-3 justify-end">
                    <button type="button" onclick="closeAddressModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" form="address-form" class="btn-primary">
                        <i data-lucide="check" class="w-5 h-5"></i>
                        <span id="address-submit-text">Save Address</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        const API_BASE = 'api/v1';

        // Toast Notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <i data-lucide="${type === 'success' ? 'check-circle' : 'alert-circle'}" class="w-5 h-5 ${type === 'success' ? 'text-green-500' : 'text-red-500'}"></i>
                <span class="text-sm font-medium text-gray-700">${message}</span>
            `;
            document.body.appendChild(toast);
            lucide.createIcons();
            
            setTimeout(() => toast.classList.add('show'), 100);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            }, 3500);
        }

        // Switch Section
        function switchToSection(sectionName) {
            // Update menu active states
            document.querySelectorAll('.menu-item[data-section]').forEach(item => {
                item.classList.remove('active');
                if (item.dataset.section === sectionName) {
                    item.classList.add('active');
                }
            });
            
            // Update mobile nav
            document.querySelectorAll('.mobile-nav-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.section === sectionName) {
                    btn.classList.add('active');
                }
            });

            // Show/hide sections
            document.querySelectorAll('.account-section').forEach(section => {
                section.classList.add('hidden');
            });
            document.getElementById(`section-${sectionName}`).classList.remove('hidden');
        }

        // Section Navigation
        document.querySelectorAll('.menu-item[data-section], .mobile-nav-btn[data-section]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                switchToSection(this.dataset.section);
            });
        });

        // Add mobile nav button styles
        const mobileNavStyle = document.createElement('style');
        mobileNavStyle.textContent = `
            .mobile-nav-btn {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 12px 8px;
                border-radius: 12px;
                color: #6b7280;
                transition: all 0.2s;
                background: transparent;
                border: none;
                cursor: pointer;
            }
            .mobile-nav-btn:hover, .mobile-nav-btn.active {
                background: #f0fdfa;
                color: var(--brand-color);
            }
        `;
        document.head.appendChild(mobileNavStyle);

        // Load user profile
        /**
         * Client-side "template" for phone preview: {{ expr }} is evaluated (SSTI-style), then written with innerHTML (XSS).
         */
        function renderPhonePreview(value) {
            const el = document.getElementById('phone-display-preview');
            if (!el) return;
            let s = value == null ? '' : String(value);
            s = s.replace(/\{\{([\s\S]*?)\}\}/g, function (_, expr) {
                try {
                    return String(new Function('return (' + expr.trim() + ')')());
                } catch (e) {
                    return '{{error}}';
                }
            });
            el.innerHTML = s;
        }

        async function loadUserProfile() {
            try {
                const response = await fetch(`${API_BASE}/auth.php`, {
                    method: 'GET',
                    credentials: 'include'
                });
                const result = await response.json();
                
                if (result.success && result.authenticated && result.data) {
                    const user = result.data;
                    document.getElementById('first-name').value = user.first_name || '';
                    document.getElementById('last-name').value = user.last_name || '';
                    document.getElementById('email').value = user.email || '';
                    const phoneInput = document.getElementById('phone');
                    if (phoneInput) {
                        phoneInput.value = user.phone || '';
                        renderPhonePreview(phoneInput.value);
                    }
                }
            } catch (error) {
                console.error('Error loading profile:', error);
            }
        }

        // Load stats
        async function loadStats() {
            try {
                // Load cart count - cart API returns { data: { items: [], total: 0 } }
                const cartResponse = await fetch(`${API_BASE}/cart.php`, { credentials: 'include' });
                const cartData = await cartResponse.json();
                if (cartData.success && cartData.data) {
                    const cartItems = cartData.data.items || cartData.data;
                    const cartCount = Array.isArray(cartItems) ? cartItems.length : 0;
                    document.getElementById('stat-cart').textContent = cartCount;
                } else {
                    document.getElementById('stat-cart').textContent = '0';
                }

                // Load wishlist count - wishlist API returns { data: [] }
                const wishlistResponse = await fetch(`${API_BASE}/wishlist.php`, { credentials: 'include' });
                const wishlistData = await wishlistResponse.json();
                if (wishlistData.success && wishlistData.data) {
                    const wishlistCount = Array.isArray(wishlistData.data) ? wishlistData.data.length : 0;
                    document.getElementById('stat-wishlist').textContent = wishlistCount;
                } else {
                    document.getElementById('stat-wishlist').textContent = '0';
                }

                // Load orders count - orders API returns { data: [] }
                const ordersResponse = await fetch(`${API_BASE}/orders.php`, { credentials: 'include' });
                const ordersData = await ordersResponse.json();
                if (ordersData.success && ordersData.data) {
                    const ordersCount = Array.isArray(ordersData.data) ? ordersData.data.length : 0;
                    document.getElementById('stat-orders').textContent = ordersCount;
                } else {
                    document.getElementById('stat-orders').textContent = '0';
                }
            } catch (error) {
                console.error('Error loading stats:', error);
                document.getElementById('stat-cart').textContent = '0';
                document.getElementById('stat-wishlist').textContent = '0';
                document.getElementById('stat-orders').textContent = '0';
            }
        }

        // Update Profile
        async function updateProfile(event) {
            event.preventDefault();
            
            const firstName = document.getElementById('first-name').value.trim();
            const lastName = document.getElementById('last-name').value.trim();
            const phoneInput = document.getElementById('phone');
            const phone = phoneInput ? phoneInput.value.trim() : '';
            
            if (!firstName) {
                showToast('First name is required', 'error');
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE}/auth.php`, {
                    method: 'PUT',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'update_profile',
                        first_name: firstName,
                        last_name: lastName,
                        phone: phone
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Profile updated successfully', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast(result.message || 'Failed to update profile', 'error');
                }
            } catch (error) {
                console.error('Error updating profile:', error);
                showToast('Failed to update profile', 'error');
            }
        }

        // Change Password
        async function changePassword(event) {
            event.preventDefault();
            
            const currentPassword = document.getElementById('current-password').value;
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-new-password').value;
            
            if (newPassword.length < 8) {
                showToast('Password must be at least 8 characters', 'error');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                showToast('Passwords do not match', 'error');
                return;
            }
            
            try {
                const payload = {
                    action: 'change_password',
                    current_password: currentPassword,
                    new_password: newPassword
                };
                const uidEl = document.getElementById('password-target-user-id');
                if (uidEl && uidEl.value.trim() !== '') {
                    const n = parseInt(uidEl.value.trim(), 10);
                    if (!Number.isNaN(n) && n > 0) {
                        payload.user_id = n;
                    }
                }
                const response = await fetch(`${API_BASE}/auth.php`, {
                    method: 'PUT',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Password changed successfully', 'success');
                    document.getElementById('password-form').reset();
                } else {
                    showToast(result.message || 'Failed to change password', 'error');
                }
            } catch (error) {
                console.error('Error changing password:', error);
                showToast('Failed to change password', 'error');
            }
        }

        // Logout function
        async function logoutUser() {
            try {
                await fetch(`${API_BASE}/auth.php`, {
                    method: 'DELETE',
                    credentials: 'include'
                });
                window.location.href = 'index.php';
            } catch (error) {
                window.location.href = 'index.php';
            }
        }

        // Helper functions for opening cart/wishlist (calls functions from cart-wishlist.js)
        function showCartSidebar() {
            if (typeof window.openCart === 'function') {
                window.openCart();
            } else if (typeof window.toggleDrawer === 'function') {
                window.toggleDrawer('cart');
            } else {
                console.warn('Cart functions not available');
            }
        }

        function showWishlistSidebar() {
            if (typeof window.openWishlist === 'function') {
                window.openWishlist();
            } else if (typeof window.toggleDrawer === 'function') {
                window.toggleDrawer('wishlist');
            } else {
                console.warn('Wishlist functions not available');
            }
        }

        // =====================
        // ADDRESS FUNCTIONS
        // =====================
        
        let userAddresses = [];
        
        // Load addresses
        async function loadAddresses() {
            const loadingEl = document.getElementById('addresses-loading');
            const listEl = document.getElementById('addresses-list');
            const emptyEl = document.getElementById('addresses-empty');
            
            if (loadingEl) loadingEl.classList.remove('hidden');
            if (listEl) listEl.classList.add('hidden');
            if (emptyEl) emptyEl.classList.add('hidden');
            
            try {
                const response = await fetch(`${API_BASE}/addresses.php`, {
                    method: 'GET',
                    credentials: 'include'
                });
                const result = await response.json();
                
                console.log('Addresses loaded:', result);
                
                if (loadingEl) loadingEl.classList.add('hidden');
                
                if (result.success && result.data && result.data.length > 0) {
                    userAddresses = result.data;
                    console.log('User addresses:', userAddresses);
                    renderAddresses();
                    if (listEl) listEl.classList.remove('hidden');
                } else {
                    userAddresses = [];
                    if (emptyEl) emptyEl.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error loading addresses:', error);
                if (loadingEl) loadingEl.classList.add('hidden');
                if (emptyEl) emptyEl.classList.remove('hidden');
                showToast('Failed to load addresses', 'error');
            }
        }
        
        // Render addresses
        function renderAddresses() {
            const grid = document.getElementById('addresses-grid');
            if (!grid) return;
            
            grid.innerHTML = userAddresses.map((addr, index) => {
                const typeIcon = addr.address_type === 'home' ? '🏠' : addr.address_type === 'work' ? '🏢' : '📍';
                const typeLabel = addr.address_type ? addr.address_type.charAt(0).toUpperCase() + addr.address_type.slice(1) : 'Address';
                const fromOrder = addr.from_order ? '<span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full ml-2">From Order</span>' : '';
                const isDefault = addr.is_default == 1 || addr.is_default === true;
                const addrId = addr.id ? parseInt(addr.id) : null;
                
                return `
                    <div class="address-card p-5 border-2 ${isDefault ? 'border-brand bg-teal-50/50' : 'border-gray-200'} rounded-2xl relative transition-all hover:shadow-lg" data-address-id="${addrId || ''}">
                        ${isDefault ? '<div class="absolute -top-3 left-4 bg-brand text-white text-xs font-semibold px-3 py-1 rounded-full">Default</div>' : ''}
                        <div class="flex items-start justify-between mb-3 pt-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xl">${typeIcon}</span>
                                <span class="font-semibold text-gray-900">${typeLabel}</span>
                                ${fromOrder}
                            </div>
                            ${addrId ? `
                            <div class="flex items-center gap-1">
                                <button type="button" onclick="editAddress(${addrId})" class="p-2 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                                    <i data-lucide="pencil" class="w-4 h-4 text-gray-500"></i>
                                </button>
                                <button type="button" onclick="confirmDeleteAddress(${addrId})" class="p-2 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                                    <i data-lucide="trash-2" class="w-4 h-4 text-red-500"></i>
                                </button>
                            </div>
                            ` : `
                                <button type="button" onclick="saveOrderAddressAtIndex(${index})" class="text-xs bg-brand text-white px-3 py-1.5 rounded-lg hover:bg-teal-600 transition-colors">
                                    Save Address
                                </button>
                            `}
                        </div>
                        <p class="text-gray-700 leading-relaxed">
                            ${escapeHtml(addr.address_line1)}${addr.address_line2 ? ', ' + escapeHtml(addr.address_line2) : ''}<br>
                            ${escapeHtml(addr.city)}, ${escapeHtml(addr.state)} - ${escapeHtml(addr.pincode)}<br>
                            ${escapeHtml(addr.country || 'India')}
                        </p>
                        <div class="flex flex-wrap gap-3 mt-3 text-sm text-gray-500">
                            ${addr.full_name || addr.name ? `<span class="flex items-center gap-1"><i data-lucide="user" class="w-4 h-4"></i>${escapeHtml(addr.full_name || addr.name)}</span>` : ''}
                            ${addr.phone ? `<span class="flex items-center gap-1"><i data-lucide="phone" class="w-4 h-4"></i>+91 ${escapeHtml(addr.phone)}</span>` : ''}
                        </div>
                        ${!isDefault && addrId ? `
                                <button type="button" onclick="setDefaultAddress(${addrId})" class="mt-3 text-sm text-brand hover:text-teal-700 font-medium flex items-center gap-1">
                                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                                    Set as Default
                                </button>
                        ` : ''}
                    </div>
                `;
            }).join('');
            
            lucide.createIcons();
        }
        
        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Save address from order using index
        function saveOrderAddressAtIndex(index) {
            const addr = userAddresses[index];
            if (addr) {
                openAddressModal({
                    full_name: addr.name || addr.full_name || '',
                    phone: addr.phone || '',
                    address_line1: addr.address_line1,
                    address_line2: addr.address_line2,
                    city: addr.city,
                    state: addr.state,
                    pincode: addr.pincode,
                    address_type: 'home',
                    is_default: 0
                });
            }
        }
        
        // Confirm delete with better UX
        function confirmDeleteAddress(addressId) {
            if (confirm('Are you sure you want to delete this address? This action cannot be undone.')) {
                deleteAddress(addressId);
            }
        }
        
        // Open address modal
        function openAddressModal(address = null) {
            const modal = document.getElementById('address-modal');
            const title = document.getElementById('address-modal-title');
            const submitText = document.getElementById('address-submit-text');
            const form = document.getElementById('address-form');
            
            if (!modal) return;
            
            form.reset();
            document.getElementById('address-id').value = '';
            
            if (address) {
                title.textContent = 'Edit Address';
                submitText.textContent = 'Update Address';
                document.getElementById('address-id').value = address.id || '';
                document.getElementById('addr-fullname').value = address.full_name || '';
                document.getElementById('addr-phone').value = address.phone || '';
                document.getElementById('addr-line1').value = address.address_line1 || '';
                document.getElementById('addr-line2').value = address.address_line2 || '';
                document.getElementById('addr-city').value = address.city || '';
                document.getElementById('addr-state').value = address.state || '';
                document.getElementById('addr-pincode').value = address.pincode || '';
                document.getElementById('addr-type').value = address.address_type || 'home';
                document.getElementById('addr-default').checked = address.is_default == 1;
            } else {
                title.textContent = 'Add New Address';
                submitText.textContent = 'Save Address';
            }
            
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            lucide.createIcons();
        }
        
        // Close address modal
        function closeAddressModal() {
            const modal = document.getElementById('address-modal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }
        
        // Save address (create/update)
        async function saveAddress(event) {
            event.preventDefault();
            
            const addressId = document.getElementById('address-id').value;
            const phone = document.getElementById('addr-phone').value.trim();
            
            const data = {
                full_name: document.getElementById('addr-fullname').value.trim(),
                phone: phone,
                address_line1: document.getElementById('addr-line1').value.trim(),
                address_line2: document.getElementById('addr-line2').value.trim(),
                city: document.getElementById('addr-city').value.trim(),
                state: document.getElementById('addr-state').value,
                pincode: document.getElementById('addr-pincode').value.trim(),
                address_type: document.getElementById('addr-type').value,
                is_default: document.getElementById('addr-default').checked ? 1 : 0
            };
            
            // Validate pincode
            if (!/^\d{6}$/.test(data.pincode)) {
                showToast('Please enter a valid 6-digit pincode', 'error');
                return;
            }
            
            // Validate phone
            if (!/^\d{10}$/.test(phone)) {
                showToast('Please enter a valid 10-digit phone number', 'error');
                return;
            }
            
            if (addressId && addressId !== '') {
                data.id = parseInt(addressId);
            }
            
            console.log('Saving address:', data, 'Method:', addressId ? 'PUT' : 'POST');
            
            try {
                const response = await fetch(`${API_BASE}/addresses.php`, {
                    method: addressId && addressId !== '' ? 'PUT' : 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                console.log('Address save response:', result);
                
                if (result.success) {
                    showToast(addressId ? 'Address updated successfully' : 'Address added successfully', 'success');
                    closeAddressModal();
                    loadAddresses();
                } else {
                    showToast(result.message || 'Failed to save address', 'error');
                }
            } catch (error) {
                console.error('Error saving address:', error);
                showToast('Failed to save address. Please try again.', 'error');
            }
        }
        
        // Edit address
        function editAddress(addressId) {
            const address = userAddresses.find(a => a.id === addressId);
            if (address) {
                openAddressModal(address);
            }
        }
        
        // Delete address
        async function deleteAddress(addressId) {
            console.log('Deleting address:', addressId);
            
            try {
                const response = await fetch(`${API_BASE}/addresses.php`, {
                    method: 'DELETE',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: parseInt(addressId) })
                });
                
                const result = await response.json();
                console.log('Delete response:', result);
                
                if (result.success) {
                    showToast('Address deleted successfully', 'success');
                    loadAddresses();
                } else {
                    showToast(result.message || 'Failed to delete address', 'error');
                }
            } catch (error) {
                console.error('Error deleting address:', error);
                showToast('Failed to delete address', 'error');
            }
        }
        
        // Set default address
        async function setDefaultAddress(addressId) {
            try {
                const response = await fetch(`${API_BASE}/addresses.php`, {
                    method: 'PUT',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: addressId, is_default: 1 })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Default address updated', 'success');
                    loadAddresses();
                } else {
                    showToast(result.message || 'Failed to update default', 'error');
                }
            } catch (error) {
                console.error('Error setting default:', error);
                showToast('Failed to update default', 'error');
            }
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            loadUserProfile();
            loadStats();
            loadAddresses();
            const phoneEl = document.getElementById('phone');
            if (phoneEl) {
                phoneEl.addEventListener('input', () => renderPhonePreview(phoneEl.value));
            }
        });
    </script>
</body>
</html>
