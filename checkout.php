<?php
/**
 * Checkout Page
 * Requires user to be logged in to checkout (supports both regular users and admins)
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load config for database access
require_once __DIR__ . '/includes/config.php';

// Check if user is logged in (regular user OR admin)
$isRegularUser = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$isAdmin = isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
$isLoggedIn = $isRegularUser || $isAdmin;

// Get user info based on login type
if ($isAdmin) {
    $userId = $_SESSION['admin_id'];
    $userEmail = $_SESSION['admin_email'] ?? null;
    $userName = $_SESSION['admin_name'] ?? null;
} else {
    $userId = $_SESSION['user_id'] ?? null;
    $userEmail = $_SESSION['user_email'] ?? null;
    $userName = $_SESSION['user_name'] ?? null;
}

// ALWAYS require login for checkout
if (!$isLoggedIn) {
    // Store guest session ID in cookie so we can migrate cart after login
    // Cookie expires in 1 hour (enough time to complete login)
    $guestSessionId = session_id();
    setcookie('guest_session_id', $guestSessionId, time() + 3600, '/', '', false, true);
    
    $returnUrl = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?return=" . $returnUrl . "&message=login_required");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <title>Checkout | BLine Boutique</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="shortcut icon" type="image/png" href="logo.png">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Razorpay SDK -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f9fafb; /* gray-50 */
            color: #1a1a1a;
            -webkit-font-smoothing: antialiased;
        }
        
        .font-serif {
            font-family: 'Playfair Display', serif;
        }
        
        :root {
            --brand-color: #14b8a6;
        }
        
        .bg-brand {
            background-color: var(--brand-color);
        }
        
        .text-brand {
            color: var(--brand-color);
        }
        
        .border-brand {
            border-color: var(--brand-color);
        }
        
        .ring-brand {
            --tw-ring-color: var(--brand-color);
        }

        /* Mobile-first responsive improvements */
        @media (max-width: 640px) {
            * {
                max-width: 100%;
            }
            
            h1 {
                font-size: 1.75rem !important;
                line-height: 1.2 !important;
            }
            
            h2 {
                font-size: 1.5rem !important;
            }
            
            button, a[role="button"] {
                min-height: 44px;
                min-width: 44px;
            }

            /* Better spacing on mobile */
            .step-content {
                padding: 1rem !important;
            }

            /* Stack form fields on mobile */
            .grid {
                grid-template-columns: 1fr !important;
            }

            /* Modal responsive - removed duplicate, see below */

            /* Progress steps on mobile */
            nav[aria-label="Progress"] {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            nav[aria-label="Progress"] ol {
                min-width: max-content;
            }
        }
        
        /* Tablet responsive */
        @media (min-width: 641px) and (max-width: 1024px) {
            .container {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }
        
        /* Smooth transitions for all interactive elements */
        .transition-all-200 {
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 200ms;
        }

        /* Glassmorphism for Navbar */
        .glass-nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        /* Step transition animation */
        .step-content {
            transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
        }
        
        .step-inactive {
            display: none !important;
            opacity: 0;
            transform: translateY(10px);
        }
        
        .step-active {
            display: block !important;
            opacity: 1;
            transform: translateY(0);
        }

        /* Cart and Wishlist Counters */
        #cart-count, #wishlist-count, #cart-count-mobile, #wishlist-count-mobile {
            display: none;
        }

        #cart-count.show, #wishlist-count.show, #cart-count-mobile.show, #wishlist-count-mobile.show {
            display: flex;
        }

        /* Hide scrollbar for number inputs */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }

        /* Validation Styles */
        .input-error {
            border-color: var(--brand-color) !important;
            border-width: 2px !important;
            background-color: #f0fdfa !important;
        }

        .input-valid {
            border-color: #22c55e !important;
            border-width: 2px !important;
        }

        .error-message {
            display: none;
            color: var(--brand-color);
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        .error-message.show {
            display: block;
        }
        
        /* Custom Radio Styling helper */
        .radio-circle {
            transition: all 0.2s;
        }
        input:checked + div .radio-circle {
            border-color: var(--brand-color);
            border-width: 5px;
        }

        /* Custom Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9998;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            backdrop-filter: blur(4px);
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .modal-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.9);
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            z-index: 9999;
            max-width: 90%;
            width: 100%;
            max-width: 420px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.show .modal-container {
            opacity: 1;
            visibility: visible;
            transform: translate(-50%, -50%) scale(1);
        }

        /* Mobile modal improvements - Full Bottom Sheet Style */
        @media (max-width: 640px) {
            .modal-overlay {
                backdrop-filter: blur(8px);
            }

            .modal-container {
                top: auto !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                transform: translateY(100%) !important;
                max-width: 100% !important;
                width: 100% !important;
                border-radius: 1.5rem 1.5rem 0 0 !important;
                max-height: 85vh !important;
                box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15) !important;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }

            .modal-overlay.show .modal-container {
                transform: translateY(0) !important;
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }

            .modal-header {
                padding: 1.25rem 1.5rem !important;
                position: sticky;
                top: 0;
                background: white;
                z-index: 1;
                border-bottom: 1px solid #e5e7eb;
                min-height: 60px;
            }

            .modal-title {
                font-size: 1.125rem !important;
                font-weight: 700 !important;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .modal-close {
                min-width: 44px;
                min-height: 44px;
                padding: 0.75rem !important;
            }

            .modal-body {
                padding: 1.5rem 1.5rem !important;
                max-height: calc(85vh - 140px);
                overflow-y: auto;
            }

            .modal-footer {
                padding: 1.25rem 1.5rem !important;
                padding-bottom: max(1.25rem, env(safe-area-inset-bottom)) !important;
                position: sticky;
                bottom: 0;
                background: white;
                border-top: 1px solid #e5e7eb;
                flex-direction: column;
                gap: 0.75rem;
            }

            .modal-btn {
                width: 100% !important;
                padding: 0.875rem 1.5rem !important;
                min-height: 52px !important;
                font-size: 1rem !important;
                font-weight: 600 !important;
            }

            /* Success Modal Specific Mobile Styles */
            .modal-success-content {
                padding: 0.5rem 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .modal-success-icon {
                width: 80px !important;
                height: 80px !important;
                margin: 0 auto 1.5rem !important;
            }

            .modal-success-icon i {
                width: 36px !important;
                height: 36px !important;
            }

            .modal-message {
                font-size: 0.9375rem !important;
                line-height: 1.6 !important;
                word-wrap: break-word;
                overflow-wrap: break-word;
                padding: 0 0.5rem;
            }

            .modal-message.font-semibold {
                font-size: 1.125rem !important;
                margin-bottom: 1rem !important;
                padding: 0;
            }

            /* Ensure long order IDs wrap properly */
            .modal-message.break-words {
                word-break: break-all;
                hyphens: auto;
            }

            /* Ensure modal content is scrollable on very small screens */
            @media (max-height: 600px) {
                .modal-container {
                    max-height: 95vh !important;
                }

                .modal-body {
                    max-height: calc(95vh - 140px) !important;
                    padding: 1rem 1.5rem !important;
                }
            }
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .modal-close {
            background: none;
            border: none;
            cursor: pointer;
            color: #6b7280;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            background: #f3f4f6;
            color: #111827;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-message {
            color: #374151;
            font-size: 0.9375rem;
            line-height: 1.5;
        }

        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .modal-btn {
            padding: 0.625rem 1.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: 0.75rem;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }

        .modal-btn-primary {
            background: var(--brand-color);
            color: white;
        }

        .modal-btn-primary:hover {
            background: #0d9488;
        }

        .modal-btn-secondary {
            background: white;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .modal-btn-secondary:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        .modal-icon {
            width: 1.5rem;
            height: 1.5rem;
        }

        .modal-icon.error {
            color: var(--brand-color);
        }

        .modal-icon.success {
            color: #10b981;
        }

        .modal-icon.info {
            color: #3b82f6;
        }

        /* Loading Spinner */
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Success Modal Specific Styles */
        .modal-success-content {
            text-align: center;
            padding: 1rem 0;
            width: 100%;
        }

        .modal-success-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
            flex-shrink: 0;
        }

        /* Break words for long order IDs */
        .break-words {
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out forwards;
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
            }
            50% {
                box-shadow: 0 0 0 20px rgba(16, 185, 129, 0);
            }
        }

        /* Responsive improvements for checkout form */
        @media (max-width: 640px) {
            /* Order summary sticky positioning on mobile */
            .lg\:sticky {
                position: relative !important;
                top: auto !important;
            }

            /* Better button spacing on mobile */
            .flex.flex-col.sm\:flex-row {
                flex-direction: column;
                gap: 0.75rem;
            }

            /* Progress steps mobile optimization */
            nav[aria-label="Progress"] ol li {
                flex-shrink: 0;
            }

            /* Step text hidden on very small screens */
            #step-text-1, #step-text-2, #step-text-3 {
                display: none !important;
            }

            /* Form inputs full width on mobile */
            input, select, textarea {
                width: 100%;
            }
        }

        /* ========================================
           SIDEBAR STYLES (Cart/Wishlist)
           ======================================== */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
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
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 999;
            transform: translateX(100%);
            transition: transform 0.3s ease-out;
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
            background: #f9fafb;
        }

        .sidebar-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
        }

        .sidebar-close {
            background: none;
            border: none;
            cursor: pointer;
            color: #6b7280;
            padding: 8px;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .sidebar-close:hover {
            background: #e5e7eb;
            color: #111827;
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 24px;
            -webkit-overflow-scrolling: touch;
            min-height: 0;
        }

        .sidebar-empty {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .sidebar-empty i {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            color: #d1d5db;
        }

        .cart-item, .wishlist-item {
            display: flex;
            gap: 16px;
            padding: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 16px;
            background: white;
            transition: box-shadow 0.2s;
        }

        .cart-item:hover, .wishlist-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .cart-item-image, .wishlist-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            background: #f3f4f6;
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
        }

        .cart-item-price, .wishlist-item-price {
            color: var(--brand-color);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 8px;
        }

        .quantity-btn {
            width: 32px;
            height: 32px;
            border: 1px solid #e5e7eb;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .quantity-btn:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
        }

        .quantity-value {
            font-weight: 600;
            min-width: 24px;
            text-align: center;
        }

        .cart-item-remove, .wishlist-item-remove {
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: color 0.2s;
        }

        .cart-item-remove:hover, .wishlist-item-remove:hover {
            color: var(--brand-color);
        }

        .sidebar-footer {
            padding: 20px 24px;
            padding-bottom: max(20px, env(safe-area-inset-bottom));
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
            flex-shrink: 0;
            position: relative;
            z-index: 10;
        }

        .sidebar-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 2px solid #e5e7eb;
        }

        .total-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 1rem;
        }

        .total-value {
            font-weight: 700;
            color: var(--brand-color);
            font-size: 1.5rem;
        }

        .sidebar-checkout {
            width: 100%;
            padding: 16px 20px;
            min-height: 52px;
            background: var(--brand-color);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            -webkit-tap-highlight-color: transparent;
        }

        .sidebar-checkout:hover {
            background: #0d9488;
        }

        .sidebar-checkout:disabled {
            background: #d1d5db;
            cursor: not-allowed;
        }

        .wishlist-add-to-cart {
            margin-top: 8px;
            padding: 8px 16px;
            background: var(--brand-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: background 0.2s;
        }

        .wishlist-add-to-cart:hover {
            background: #0d9488;
        }

        /* Saved Address Card Styles */
        .saved-address-card {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .saved-address-card:hover {
            border-color: #5eead4;
            background: #f0fdfa;
        }

        .saved-address-card.selected {
            border-color: var(--brand-color);
            background: #f0fdfa;
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
        }

        .saved-address-card .default-badge {
            position: absolute;
            top: -8px;
            right: 12px;
            background: var(--brand-color);
            color: white;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .saved-address-card .address-type {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .saved-address-card .address-text {
            font-size: 14px;
            color: #374151;
            line-height: 1.5;
        }

        .saved-address-card .check-icon {
            position: absolute;
            top: 12px;
            left: 12px;
            width: 20px;
            height: 20px;
            border: 2px solid #e5e7eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .saved-address-card.selected .check-icon {
            background: var(--brand-color);
            border-color: var(--brand-color);
        }

        .saved-address-card.selected .check-icon svg {
            display: block;
        }

        .saved-address-card .check-icon svg {
            display: none;
            color: white;
            width: 12px;
            height: 12px;
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Navigation Header (matching index.php) -->
    <?php include 'header.php'; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 sm:pt-8 pb-8 lg:pb-12">
        <div class="lg:grid lg:grid-cols-12 lg:gap-12 lg:items-start">
            
            <!-- LEFT COLUMN: Checkout Form -->
            <div class="lg:col-span-7 xl:col-span-8">
                
                <!-- Progress Steps -->
                <nav aria-label="Progress" class="mb-8">
                    <ol role="list" class="flex items-center space-x-4">
                        <!-- Step 1 Indicator -->
                        <li class="flex items-center">
                            <span id="step-icon-1" class="flex items-center justify-center w-8 h-8 bg-brand rounded-full ring-4 ring-teal-100 transition-all-200">
                                <i data-lucide="user" class="w-4 h-4 text-white"></i>
                            </span>
                            <span id="step-text-1" class="ml-3 text-sm font-semibold text-brand hidden sm:block">Information</span>
                        </li>
                        
                        <!-- Connector -->
                        <li class="flex-1 h-0.5 bg-gray-200 relative rounded-full overflow-hidden">
                            <div id="progress-bar-1" class="absolute left-0 top-0 h-full bg-brand w-0 transition-all duration-500 ease-out"></div>
                        </li>
                        
                        <!-- Step 2 Indicator -->
                        <li class="flex items-center">
                            <span id="step-icon-2" class="flex items-center justify-center w-8 h-8 bg-white border-2 border-gray-300 rounded-full transition-all-200">
                                <i data-lucide="truck" class="w-4 h-4 text-gray-400"></i>
                            </span>
                            <span id="step-text-2" class="ml-3 text-sm font-medium text-gray-500 hidden sm:block">Shipping</span>
                        </li>
                        
                        <!-- Connector -->
                        <li class="flex-1 h-0.5 bg-gray-200 relative rounded-full overflow-hidden">
                            <div id="progress-bar-2" class="absolute left-0 top-0 h-full bg-brand w-0 transition-all duration-500 ease-out"></div>
                        </li>
                        
                        <!-- Step 3 Indicator -->
                        <li class="flex items-center">
                            <span id="step-icon-3" class="flex items-center justify-center w-8 h-8 bg-white border-2 border-gray-300 rounded-full transition-all-200">
                                <i data-lucide="credit-card" class="w-4 h-4 text-gray-400"></i>
                            </span>
                            <span id="step-text-3" class="ml-3 text-sm font-medium text-gray-500 hidden sm:block">Payment</span>
                        </li>
                    </ol>
                </nav>

                <form id="checkout-form" onsubmit="event.preventDefault();">
                    
                    <!-- STEP 1: Contact & Information -->
                    <div id="step-1" class="step-content step-active bg-white p-4 sm:p-6 md:p-8 rounded-xl sm:rounded-2xl shadow-sm border border-gray-200">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4 sm:mb-6">
                            <h2 class="text-lg sm:text-xl font-bold text-gray-900">Contact Information</h2>
                            <?php if ($isLoggedIn): ?>
                                <span class="text-xs sm:text-sm text-green-600 font-medium flex items-center gap-1">
                                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                                    Logged in as <?php echo htmlspecialchars($userEmail); ?>
                                </span>
                            <?php else: ?>
                                <a href="login.php?return=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="text-xs sm:text-sm text-brand hover:text-teal-700 font-medium">Already have an account? Log in</a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="space-y-4 sm:space-y-5">
                            <div>
                                <label for="email" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                                <input type="email" id="email" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-gray-900 placeholder-gray-400 focus:border-brand focus:ring-1 focus:ring-brand transition-all-200 outline-none <?php echo $isLoggedIn ? 'bg-gray-50' : ''; ?>" placeholder="you@example.com" required onblur="validateEmail(this)" oninput="clearError(this)" value="<?php echo htmlspecialchars($userEmail ?? ''); ?>" <?php echo $isLoggedIn ? 'readonly' : ''; ?>>
                                <span class="error-message" id="email-error"></span>
                            </div>

                            <div class="flex items-center mb-4">
                                <input id="newsletter" type="checkbox" class="h-4 w-4 text-brand focus:ring-brand border-gray-300 rounded cursor-pointer" checked>
                                <label for="newsletter" class="ml-2 block text-sm text-gray-600 cursor-pointer select-none">
                                    Email me with news and exclusive offers
                                </label>
                            </div>

                            <h2 class="text-xl font-bold text-gray-900 pt-6 mb-2">Shipping Address</h2>

                            <?php
                            // Split user name for pre-filling
                            $nameParts = $userName ? explode(' ', $userName, 2) : ['', ''];
                            $userFirstName = $nameParts[0] ?? '';
                            $userLastName = $nameParts[1] ?? '';
                            ?>

                            <!-- Saved Addresses Section -->
                            <div id="saved-addresses-section" class="mb-6 hidden">
                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-sm font-medium text-gray-700">Select a saved address or enter a new one</p>
                                </div>
                                <div id="saved-addresses-list" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                                    <!-- Saved addresses will be loaded here -->
                                </div>
                                <button type="button" id="use-new-address-btn" onclick="useNewAddress()" class="text-sm text-brand font-medium flex items-center gap-1 hover:text-teal-700">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                    Enter a different address
                                </button>
                            </div>

                            <!-- Address Form (shows when entering new address or no saved addresses) -->
                            <div id="address-form-section">
                                <div id="back-to-saved-btn" class="hidden mb-4">
                                    <button type="button" onclick="showSavedAddresses()" class="text-sm text-gray-600 font-medium flex items-center gap-1 hover:text-brand">
                                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                                        Back to saved addresses
                                    </button>
                                </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label for="first-name" class="block text-sm font-medium text-gray-700 mb-1.5">First Name</label>
                                    <input type="text" id="first-name" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-brand focus:ring-1 focus:ring-brand transition-all-200 outline-none" required value="<?php echo htmlspecialchars($userFirstName); ?>" oninput="clearError(this)">
                                    <span class="error-message" id="first-name-error"></span>
                                </div>
                                <div>
                                    <label for="last-name" class="block text-sm font-medium text-gray-700 mb-1.5">Last Name</label>
                                    <input type="text" id="last-name" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-brand focus:ring-1 focus:ring-brand transition-all-200 outline-none" required value="<?php echo htmlspecialchars($userLastName); ?>" oninput="clearError(this)">
                                    <span class="error-message" id="last-name-error"></span>
                                </div>
                            </div>

                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
                                <input type="text" id="address" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-brand focus:ring-1 focus:ring-brand transition-all-200 outline-none" placeholder="Apartment, suite, etc." required oninput="clearError(this)">
                                <span class="error-message" id="address-error"></span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                                <div class="sm:col-span-1">
                                    <label for="zip" class="block text-sm font-medium text-gray-700 mb-1.5">PIN Code</label>
                                    <input type="text" id="zip" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-brand focus:ring-1 focus:ring-brand transition-all-200 outline-none" placeholder="6 digits" maxlength="6" required onblur="validatePincode(this)" oninput="validatePincodeInput(this)">
                                    <span class="error-message" id="zip-error"></span>
                                </div>
                                <div class="sm:col-span-1">
                                    <label for="city" class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                                    <input type="text" id="city" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-brand focus:ring-1 focus:ring-brand transition-all-200 outline-none" required oninput="clearError(this)">
                                    <span class="error-message" id="city-error"></span>
                                </div>
                                <div class="sm:col-span-1">
                                    <label for="state" class="block text-sm font-medium text-gray-700 mb-1.5">State</label>
                                    <select id="state" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-brand focus:ring-1 focus:ring-brand transition-all-200 outline-none bg-white" onchange="clearError(this)">
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
                                    <span class="error-message" id="state-error"></span>
                                </div>
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-sm border-r border-gray-300 pr-2 my-2 select-none">+91</span>
                                    <input type="tel" id="phone" class="w-full pl-14 px-4 py-3 rounded-lg border border-gray-300 focus:border-brand focus:ring-1 focus:ring-brand transition-all-200 outline-none" placeholder="9876543210" maxlength="10" required onblur="validatePhone(this)" oninput="validatePhoneInput(this)">
                                </div>
                                <span class="error-message" id="phone-error"></span>
                            </div>
                            </div><!-- End of address-form-section -->
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button type="button" onclick="goToStep(2)" class="w-full sm:w-auto px-8 py-3.5 bg-brand text-white font-semibold rounded-lg hover:bg-teal-600 transition-all shadow-lg shadow-teal-200 flex items-center justify-center gap-2 transform active:scale-[0.98]">
                                Continue to Shipping <i data-lucide="arrow-right" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2: Shipping Method -->
                    <div id="step-2" class="step-content step-inactive bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold text-gray-900">Shipping Method</h2>
                        </div>

                        <!-- Summary of Info (Read Only) -->
                        <div class="bg-gray-50 rounded-xl p-5 border border-gray-200 mb-8 text-sm">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-gray-200 pb-3 mb-3 gap-2">
                                <div class="flex flex-col sm:flex-row sm:gap-4">
                                    <span class="text-gray-500 min-w-[60px]">Contact</span>
                                    <span class="text-gray-900 font-medium" id="summary-email">user@example.com</span>
                                </div>
                                <button type="button" onclick="goToStep(1)" class="text-brand font-medium text-xs hover:text-teal-800 text-left sm:text-right">Change</button>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="flex flex-col sm:flex-row sm:gap-4">
                                    <span class="text-gray-500 min-w-[60px]">Ship to</span>
                                    <span class="text-gray-900 font-medium" id="summary-address">123 Street, Kerala</span>
                                </div>
                                <button type="button" onclick="goToStep(1)" class="text-brand font-medium text-xs hover:text-teal-800 text-left sm:text-right">Change</button>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- Standard Shipping -->
                            <label class="group relative block cursor-pointer">
                                <input type="radio" name="shipping-method" value="standard" class="peer sr-only" checked onchange="updateTotal()">
                                <div class="flex items-center p-4 border rounded-xl transition-all-200 border-gray-200 bg-white hover:border-teal-300 peer-checked:border-brand peer-checked:bg-teal-50 peer-checked:ring-1 peer-checked:ring-brand">
                                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full radio-circle mr-4 flex-shrink-0 bg-white"></div>
                                    <div class="flex-1">
                                        <div class="flex justify-between">
                                            <span class="block text-sm font-semibold text-gray-900">Standard Delivery</span>
                                            <span class="block text-sm font-bold text-gray-900">Free</span>
                                        </div>
                                        <span class="block text-sm text-gray-500 mt-0.5">Estimated 3-5 business days</span>
                                    </div>
                                </div>
                            </label>

                            <!-- Express Shipping -->
                            <label class="group relative block cursor-pointer">
                                <input type="radio" name="shipping-method" value="express" class="peer sr-only" onchange="updateTotal()">
                                <div class="flex items-center p-4 border rounded-xl transition-all-200 border-gray-200 bg-white hover:border-teal-300 peer-checked:border-brand peer-checked:bg-teal-50 peer-checked:ring-1 peer-checked:ring-brand">
                                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full radio-circle mr-4 flex-shrink-0 bg-white"></div>
                                    <div class="flex-1">
                                        <div class="flex justify-between">
                                            <span class="block text-sm font-semibold text-gray-900">Express Delivery</span>
                                            <span class="block text-sm font-bold text-gray-900">₹450</span>
                                        </div>
                                        <span class="block text-sm text-gray-500 mt-0.5">1-2 business days (Fastest)</span>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-between items-center">
                            <button type="button" onclick="goToStep(1)" class="text-gray-600 hover:text-black text-sm font-medium flex items-center gap-2 transition-colors py-2">
                                <i data-lucide="arrow-left" class="w-4 h-4"></i> Return to Information
                            </button>
                            <button type="button" onclick="goToStep(3)" class="w-full sm:w-auto px-8 py-3.5 bg-brand text-white font-semibold rounded-lg hover:bg-teal-600 transition-all shadow-lg shadow-teal-200 flex items-center justify-center gap-2 transform active:scale-[0.98]">
                                Continue to Payment <i data-lucide="arrow-right" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 3: Payment -->
                    <div id="step-3" class="step-content step-inactive bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold text-gray-900">Payment</h2>
                            <span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-1 rounded border border-green-100 flex items-center gap-1">
                                <i data-lucide="shield-check" class="w-3 h-3"></i> 256-bit SSL Encrypted
                            </span>
                        </div>

                        <div class="space-y-4">
                            <!-- Razorpay / UPI -->
                            <label class="group relative block cursor-pointer">
                                <input type="radio" name="payment-method" id="payment-online" class="peer sr-only" checked onchange="updatePaymentButton()">
                                <div class="border rounded-xl transition-all-200 border-gray-200 bg-white hover:border-teal-300 peer-checked:border-brand peer-checked:bg-teal-50 peer-checked:ring-1 peer-checked:ring-brand overflow-hidden">
                                    <div class="p-4 flex items-center">
                                        <div class="w-5 h-5 border-2 border-gray-300 rounded-full radio-circle mr-4 flex-shrink-0 bg-white"></div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <span class="block text-sm font-semibold text-gray-900">Razorpay Secure</span>
                                                <div class="flex gap-2 opacity-80">
                                                    <!-- Simulating payment icons -->
                                                    <div class="h-5 w-8 bg-gray-200 rounded border border-gray-300"></div>
                                                    <div class="h-5 w-8 bg-gray-200 rounded border border-gray-300"></div>
                                                    <div class="h-5 w-8 bg-gray-200 rounded border border-gray-300"></div>
                                                </div>
                                            </div>
                                            <span class="block text-xs text-gray-500 mt-1">UPI, Credit/Debit Cards, NetBanking</span>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 p-4 border-t border-gray-100 text-center peer-checked:bg-teal-50/50 peer-checked:border-teal-100">
                                        <i data-lucide="arrow-up-right" class="w-8 h-8 text-gray-400 mx-auto mb-2"></i>
                                        <p class="text-gray-600 text-sm">After clicking "Pay Now", you will be redirected to Razorpay to complete your purchase securely.</p>
                                    </div>
                                </div>
                            </label>

                            <!-- COD -->
                            <label class="group relative block cursor-pointer">
                                <input type="radio" name="payment-method" id="payment-cod" class="peer sr-only" onchange="updatePaymentButton()">
                                <div class="flex items-center p-4 border rounded-xl transition-all-200 border-gray-200 bg-white hover:border-teal-300 peer-checked:border-brand peer-checked:bg-teal-50 peer-checked:ring-1 peer-checked:ring-brand">
                                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full radio-circle mr-4 flex-shrink-0 bg-white"></div>
                                    <span class="block text-sm font-semibold text-gray-900">Cash on Delivery (COD)</span>
                                </div>
                            </label>
                        </div>

                        <div class="mt-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Billing Address</h3>
                            <div class="space-y-3">
                                <label class="group relative block cursor-pointer">
                                    <input type="radio" name="billing" id="billing-same" class="peer sr-only" checked onchange="toggleBillingForm()">
                                    <div class="flex items-center p-4 border rounded-xl transition-all-200 border-gray-200 bg-white hover:border-teal-300 peer-checked:border-brand peer-checked:bg-teal-50 peer-checked:ring-1 peer-checked:ring-brand">
                                        <div class="w-5 h-5 border-2 border-gray-300 rounded-full radio-circle mr-4 flex-shrink-0 bg-white"></div>
                                        <span class="block text-sm font-medium text-gray-900">Same as shipping address</span>
                                    </div>
                                </label>
                                <label class="group relative block cursor-pointer">
                                    <input type="radio" name="billing" id="billing-different" class="peer sr-only" onchange="toggleBillingForm()">
                                    <div class="flex items-center p-4 border rounded-xl transition-all-200 border-gray-200 bg-white hover:border-teal-300 peer-checked:border-brand peer-checked:bg-teal-50 peer-checked:ring-1 peer-checked:ring-brand">
                                        <div class="w-5 h-5 border-2 border-gray-300 rounded-full radio-circle mr-4 flex-shrink-0 bg-white"></div>
                                        <span class="block text-sm font-medium text-gray-900">Use a different billing address</span>
                                    </div>
                                </label>
                            </div>
                            
                            <!-- Different Billing Address Form -->
                            <div id="billing-form" class="hidden mt-4 p-5 bg-gray-50 rounded-xl border border-gray-200 space-y-4 animate-fadeIn">
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label for="billing-address1" class="block text-sm font-medium text-gray-700 mb-1.5">Address Line 1 <span class="text-red-500">*</span></label>
                                        <input type="text" id="billing-address1" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-brand focus:ring-1 focus:ring-brand transition-all-200 outline-none" placeholder="Street address, P.O. box">
                                    </div>
                                    <div>
                                        <label for="billing-address2" class="block text-sm font-medium text-gray-700 mb-1.5">Address Line 2</label>
                                        <input type="text" id="billing-address2" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-brand focus:ring-1 focus:ring-brand transition-all-200 outline-none" placeholder="Apartment, suite, unit, building, floor, etc.">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="billing-city" class="block text-sm font-medium text-gray-700 mb-1.5">City <span class="text-brand">*</span></label>
                                        <input type="text" id="billing-city" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-brand focus:ring-1 focus:ring-brand transition-all-200 outline-none" placeholder="City">
                                    </div>
                                    <div>
                                        <label for="billing-state" class="block text-sm font-medium text-gray-700 mb-1.5">State <span class="text-brand">*</span></label>
                                        <select id="billing-state" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-brand focus:ring-1 focus:ring-brand transition-all-200 outline-none bg-white">
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
                                            <option value="Jammu and Kashmir">Jammu and Kashmir</option>
                                            <option value="Ladakh">Ladakh</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="billing-pincode" class="block text-sm font-medium text-gray-700 mb-1.5">PIN Code <span class="text-red-500">*</span></label>
                                        <input type="text" id="billing-pincode" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-brand focus:ring-1 focus:ring-brand transition-all-200 outline-none" placeholder="6 digits" maxlength="6">
                                    </div>
                                    <div>
                                        <label for="billing-country" class="block text-sm font-medium text-gray-700 mb-1.5">Country</label>
                                        <input type="text" id="billing-country" value="India" class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-100 text-gray-600 outline-none cursor-not-allowed" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-between items-center">
                            <button type="button" onclick="goToStep(2)" class="text-gray-600 hover:text-black text-sm font-medium flex items-center gap-2 transition-colors py-2">
                                <i data-lucide="arrow-left" class="w-4 h-4"></i> Return to Shipping
                            </button>
                            <button type="submit" onclick="handlePlaceOrder(event)" class="w-full sm:w-auto px-8 py-3.5 bg-brand text-white font-bold rounded-lg hover:bg-teal-600 transition-all shadow-lg shadow-teal-200 flex items-center justify-center gap-2 transform active:scale-[0.98]">
                                <span id="place-order-text">Pay Now</span>
                                <span id="place-order-loading" class="hidden">
                                    <span class="loading-spinner inline-block mr-2"></span>
                                    Processing...
                                </span>
                                <span id="final-btn-amount">₹18,198.88</span>
                            </button>
                        </div>
                    </div>
                </form>
                
            </div>

            <!-- RIGHT COLUMN: Order Summary (Sticky) -->
            <div class="mt-8 lg:mt-0 lg:col-span-5 xl:col-span-4 relative order-1 lg:order-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-6 lg:p-8 lg:sticky lg:top-24">
                    <div class="flex items-center justify-between mb-6">
                         <h2 class="text-lg font-bold text-gray-900">Order Summary</h2>
                         <span id="items-count-badge" class="text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-1 rounded-full">0 Items</span>
                    </div>

                    <!-- Loading State -->
                    <div id="cart-loading" class="py-8 text-center">
                        <div class="loading-spinner mx-auto mb-3" style="border: 2px solid #e5e7eb; border-top-color: #dc2626;"></div>
                        <p class="text-sm text-gray-500">Loading cart...</p>
                    </div>

                    <!-- Empty Cart State -->
                    <div id="cart-empty" class="hidden py-8 text-center">
                        <i data-lucide="shopping-cart" class="w-12 h-12 mx-auto mb-3 text-gray-300"></i>
                        <p class="text-gray-500 mb-4">Your cart is empty</p>
                        <a href="shop.php" class="inline-block px-6 py-2 bg-brand text-white rounded-lg font-medium hover:bg-teal-600 transition-colors">Continue Shopping</a>
                    </div>

                    <!-- Product List -->
                    <div id="cart-items-container" class="hidden">
                        <div class="flow-root">
                            <ul id="cart-items-list" role="list" class="-my-4 divide-y divide-gray-100">
                                <!-- Cart items will be rendered here -->
                            </ul>
                        </div>

                        <!-- Discount Code -->
                        <div class="mt-8">
                            <div id="discount-section" class="flex gap-2">
                                <div class="relative flex-1">
                                    <input type="text" id="discount-code" placeholder="Enter discount code" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:border-brand focus:ring-1 focus:ring-brand outline-none transition-all-200" 
                                           onkeypress="if(event.key === 'Enter') applyDiscountCode()">
                                    <div id="discount-error" class="hidden mt-1 text-xs text-red-500"></div>
                                </div>
                                <button id="apply-discount-btn" onclick="applyDiscountCode()" class="bg-gray-900 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors shadow-md flex items-center gap-2">
                                    <span>Apply</span>
                                </button>
                            </div>
                            <div id="applied-coupon" class="hidden mt-3 p-3 bg-teal-50 border border-teal-200 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="check-circle" class="w-5 h-5 text-teal-600"></i>
                                        <div>
                                            <p class="text-sm font-semibold text-teal-900" id="applied-coupon-code"></p>
                                            <p class="text-xs text-teal-700" id="applied-coupon-desc"></p>
                                        </div>
                                    </div>
                                    <button onclick="removeDiscountCode()" class="text-teal-600 hover:text-teal-800 transition-colors">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mt-3 pt-3 border-t border-dashed border-gray-200">
                                <p class="text-xs text-gray-500 mb-2">Promo lookup (tests the same server check as Apply):</p>
                                <div class="flex gap-2 flex-wrap">
                                    <input type="text" id="coupon-explore-q" placeholder="Code or search" class="flex-1 min-w-[140px] px-3 py-2 border border-gray-200 rounded-lg text-xs">
                                    <button type="button" onclick="searchCouponsExplore()" class="px-3 py-2 bg-gray-100 text-gray-800 rounded-lg text-xs font-medium">Lookup</button>
                                </div>
                                <pre id="coupon-explore-result" class="hidden mt-2 text-[10px] bg-slate-900 text-slate-100 p-2 rounded-lg overflow-x-auto max-h-48"></pre>
                            </div>
                        </div>

                        <!-- Cost Breakdown -->
                        <div class="mt-8 border-t border-gray-100 pt-6 space-y-4">
                            <div class="flex items-center justify-between text-sm text-gray-600">
                                <p>Subtotal</p>
                                <p id="summary-subtotal" class="font-medium text-gray-900">₹0.00</p>
                            </div>
                            <div class="flex items-center justify-between text-sm text-gray-600">
                                <p>Shipping</p>
                                <p id="summary-shipping" class="text-gray-500 text-xs font-medium uppercase tracking-wide">Calculated next step</p>
                            </div>
                            <div id="tax-row" class="flex items-center justify-between text-sm text-gray-600">
                                <p class="flex items-center gap-1"><span id="tax-label">GST (18%)</span> <i data-lucide="info" class="w-3.5 h-3.5 text-gray-400 cursor-help" title="Government mandated tax"></i></p>
                                <p id="summary-tax" class="font-medium text-gray-900">₹0.00</p>
                            </div>
                            <div id="discount-row" class="hidden flex items-center justify-between text-sm text-teal-600">
                                <p class="flex items-center gap-1">
                                    <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                                    <span id="discount-label">Discount</span>
                                </p>
                                <p id="summary-discount" class="font-medium text-teal-600">-₹0.00</p>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="mt-6 border-t border-dashed border-gray-300 pt-6">
                            <div class="flex items-center justify-between">
                                <p class="text-base font-medium text-gray-900">Total</p>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-gray-500 text-sm font-normal">INR</span>
                                    <p class="text-2xl font-bold text-brand tracking-tight" id="summary-total">₹0.00</p>
                                </div>
                            </div>
                            <p id="summary-tax-note" class="mt-2 text-xs text-gray-400 text-right">Including ₹0.00 in taxes</p>
                        </div>
                    </div>
                </div>
                
                <!-- Trust Badges -->
                <div class="mt-6 flex justify-center gap-3 opacity-50 grayscale hover:grayscale-0 transition-all duration-500">
                    <div class="h-6 w-10 bg-gray-300 rounded"></div> <!-- Visa -->
                    <div class="h-6 w-10 bg-gray-300 rounded"></div> <!-- Mastercard -->
                    <div class="h-6 w-10 bg-gray-300 rounded"></div> <!-- RuPay -->
                    <div class="h-6 w-10 bg-gray-300 rounded"></div> <!-- UPI -->
                </div>
            </div>
        </div>
    </main>

    <!-- Custom Modal -->
    <div id="modal-overlay" class="modal-overlay" onclick="closeModal()" ontouchstart="event.preventDefault()">
        <div class="modal-container" onclick="event.stopPropagation()" ontouchstart="event.stopPropagation()">
            <div class="modal-header">
                <div class="modal-title">
                    <i data-lucide="alert-circle" class="modal-icon error" id="modal-icon"></i>
                    <span id="modal-title">Error</span>
                </div>
                <button class="modal-close" onclick="closeModal()">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="modal-content">
                    <p class="modal-message" id="modal-message">Please fill all required fields before continuing.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-primary" onclick="closeModal()" id="modal-ok-btn">OK</button>
            </div>
        </div>
    </div>

    <script>
        // State for checkout
        const state = {
            cartItems: [],
            subtotal: 0,
            taxRate: 0.18,
            shippingCost: 0,
            discountAmount: 0,
            appliedCoupon: null,
            isLoading: true,
            // Settings from API
            settings: {
                tax: { enabled: true, rate: 18, inclusive: false },
                payment: { online_enabled: true, cod_enabled: true, razorpay_key: '' },
                auth: { guest_checkout_enabled: true },
                general: { currency_symbol: '₹' }
            }
        };

        // API Base URL
        const API_BASE = 'api/v1';

        // =====================
        // SAVED ADDRESSES
        // =====================
        let savedAddresses = [];
        let selectedAddressId = null;
        let usingNewAddress = false;

        /**
         * Load saved addresses from API
         */
        async function loadSavedAddresses() {
            try {
                const response = await fetch(`${API_BASE}/addresses.php`, {
                    credentials: 'include'
                });
                const result = await response.json();
                
                if (result.success && result.data && result.data.length > 0) {
                    savedAddresses = result.data.filter(addr => addr.id); // Only saved addresses, not from orders
                    renderSavedAddresses();
                    
                    // Auto-select default address
                    const defaultAddr = savedAddresses.find(a => a.is_default == 1);
                    if (defaultAddr) {
                        selectSavedAddress(defaultAddr.id);
                    } else if (savedAddresses.length > 0) {
                        selectSavedAddress(savedAddresses[0].id);
                    }
                    
                    // Show saved addresses section
                    document.getElementById('saved-addresses-section')?.classList.remove('hidden');
                    document.getElementById('address-form-section')?.classList.add('hidden');
                    document.getElementById('back-to-saved-btn')?.classList.add('hidden');
                } else {
                    // No saved addresses, show form directly
                    document.getElementById('saved-addresses-section')?.classList.add('hidden');
                    document.getElementById('address-form-section')?.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error loading addresses:', error);
                // Show form on error
                document.getElementById('saved-addresses-section')?.classList.add('hidden');
                document.getElementById('address-form-section')?.classList.remove('hidden');
            }
        }

        /**
         * Render saved addresses
         */
        function renderSavedAddresses() {
            const container = document.getElementById('saved-addresses-list');
            if (!container) return;
            
            container.innerHTML = savedAddresses.map(addr => {
                const typeIcon = addr.address_type === 'home' ? '🏠' : addr.address_type === 'work' ? '🏢' : '📍';
                const typeLabel = addr.address_type ? addr.address_type.charAt(0).toUpperCase() + addr.address_type.slice(1) : 'Address';
                const isDefault = addr.is_default == 1;
                const isSelected = selectedAddressId === addr.id;
                const displayName = addr.full_name || addr.name || '';
                const displayPhone = addr.phone ? `+91 ${addr.phone}` : '';
                
                return `
                    <div class="saved-address-card ${isSelected ? 'selected' : ''}" 
                         onclick="selectSavedAddress(${addr.id})" 
                         data-address-id="${addr.id}">
                        ${isDefault ? '<span class="default-badge">Default</span>' : ''}
                        <div class="check-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        <div class="pl-8">
                            <div class="address-type">${typeIcon} ${typeLabel}</div>
                            ${displayName ? `<div class="text-sm font-medium text-gray-900 mb-1">${escapeHtml(displayName)}</div>` : ''}
                            <div class="address-text">
                                ${escapeHtml(addr.address_line1)}${addr.address_line2 ? ', ' + escapeHtml(addr.address_line2) : ''}<br>
                                ${escapeHtml(addr.city)}, ${escapeHtml(addr.state)} - ${escapeHtml(addr.pincode)}
                                ${displayPhone ? `<br><span class="text-gray-500">${displayPhone}</span>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        /**
         * Escape HTML for XSS prevention
         */
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        /**
         * Select a saved address
         */
        function selectSavedAddress(addressId) {
            selectedAddressId = addressId;
            usingNewAddress = false;
            
            // Update UI
            document.querySelectorAll('.saved-address-card').forEach(card => {
                card.classList.remove('selected');
                if (parseInt(card.dataset.addressId) === addressId) {
                    card.classList.add('selected');
                }
            });
            
            // Find the address and populate form fields (hidden but needed for validation)
            const addr = savedAddresses.find(a => a.id === addressId);
            if (addr) {
                // Populate form fields with selected address
                document.getElementById('address').value = addr.address_line1 + (addr.address_line2 ? ', ' + addr.address_line2 : '');
                document.getElementById('city').value = addr.city || '';
                document.getElementById('state').value = addr.state || '';
                document.getElementById('zip').value = addr.pincode || '';
            }
        }

        /**
         * Use new address (show form)
         */
        function useNewAddress() {
            usingNewAddress = true;
            selectedAddressId = null;
            
            // Clear form fields
            document.getElementById('address').value = '';
            document.getElementById('city').value = '';
            document.getElementById('state').value = '';
            document.getElementById('zip').value = '';
            
            // Show form, hide saved addresses
            document.getElementById('saved-addresses-section')?.classList.add('hidden');
            document.getElementById('address-form-section')?.classList.remove('hidden');
            document.getElementById('back-to-saved-btn')?.classList.remove('hidden');
        }

        /**
         * Show saved addresses (go back from form)
         */
        function showSavedAddresses() {
            if (savedAddresses.length > 0) {
                usingNewAddress = false;
                document.getElementById('saved-addresses-section')?.classList.remove('hidden');
                document.getElementById('address-form-section')?.classList.add('hidden');
                
                // Re-select previous address if any
                if (selectedAddressId) {
                    selectSavedAddress(selectedAddressId);
                }
            }
        }

        /**
         * Get shipping address data (from saved or form)
         */
        function getShippingAddressData() {
            // If using new address, ALWAYS use form data regardless of selectedAddressId
            if (usingNewAddress) {
                const addressInput = document.getElementById('address');
                const cityInput = document.getElementById('city');
                const stateInput = document.getElementById('state');
                const zipInput = document.getElementById('zip');
                const phoneInput = document.getElementById('phone');
                
                const formData = {
                    fullName: '',
                    phone: phoneInput?.value.trim() || '',
                    addressLine1: addressInput?.value.trim() || '',
                    addressLine2: '',
                    city: cityInput?.value.trim() || '',
                    state: stateInput?.value || '',
                    pincode: zipInput?.value.trim() || '',
                    country: 'India',
                    savedAddressId: null
                };
                
                console.log('Using NEW address from form:', formData);
                return formData;
            }
            
            // If a saved address is selected, use that
            if (selectedAddressId) {
                const addr = savedAddresses.find(a => a.id === selectedAddressId);
                if (addr) {
                    const savedData = {
                        fullName: addr.full_name || '',
                        phone: addr.phone || '',
                        addressLine1: addr.address_line1,
                        addressLine2: addr.address_line2 || '',
                        city: addr.city,
                        state: addr.state,
                        pincode: addr.pincode,
                        country: addr.country || 'India',
                        savedAddressId: addr.id
                    };
                    console.log('Using SAVED address:', savedData);
                    return savedData;
                }
            }
            
            // Fallback: use form data (for cases with no saved addresses)
            const addressInput = document.getElementById('address');
            const cityInput = document.getElementById('city');
            const stateInput = document.getElementById('state');
            const zipInput = document.getElementById('zip');
            const phoneInput = document.getElementById('phone');
            
            return {
                fullName: '',
                phone: phoneInput?.value.trim() || '',
                addressLine1: addressInput?.value.trim() || '',
                addressLine2: '',
                city: cityInput?.value.trim() || '',
                state: stateInput?.value || '',
                pincode: zipInput?.value.trim() || '',
                country: 'India',
                savedAddressId: null
            };
        }

        /**
         * Initialize checkout page
         */
        async function initCheckout() {
            try {
                // Load settings first
                await loadSettings();
                // Then load cart
                await loadCart();
                // Load saved addresses
                await loadSavedAddresses();
                // Apply settings to UI
                applySettingsToUI();
                // Update order summary
                updateOrderSummary();
            } catch (error) {
                console.error('Failed to initialize checkout:', error);
                showEmptyCart();
            }
        }

        /**
         * Load system settings from API
         */
        async function loadSettings() {
            try {
                const response = await fetch(`${API_BASE}/settings.php`);
                const data = await response.json();
                
                if (data.success && data.data) {
                    state.settings = data.data;
                    // Update tax rate from settings
                    if (state.settings.tax && state.settings.tax.enabled) {
                        state.taxRate = parseFloat(state.settings.tax.rate) / 100;
                        console.log('Tax rate from settings:', state.settings.tax.rate, '% => state.taxRate:', state.taxRate);
                    } else {
                        state.taxRate = 0;
                        console.log('Tax is disabled or not found in settings');
                    }
                    console.log('Settings loaded:', state.settings);
                }
            } catch (error) {
                console.error('Error loading settings:', error);
                // Use defaults if settings can't be loaded
            }
        }

        /**
         * Apply settings to UI (show/hide payment methods, etc.)
         */
        function applySettingsToUI() {
            const payment = state.settings.payment || {};
            
            // Online Payment (Razorpay)
            const onlinePaymentLabel = document.querySelector('label:has(#payment-online)');
            if (onlinePaymentLabel) {
                if (!payment.online_enabled) {
                    onlinePaymentLabel.classList.add('hidden');
                    // If online is disabled and was selected, switch to COD if available
                    const onlineRadio = document.getElementById('payment-online');
                    if (onlineRadio && onlineRadio.checked && payment.cod_enabled) {
                        const codRadio = document.getElementById('payment-cod');
                        if (codRadio) codRadio.checked = true;
                    }
                } else {
                    onlinePaymentLabel.classList.remove('hidden');
                }
            }
            
            // Cash on Delivery
            const codPaymentLabel = document.querySelector('label:has(#payment-cod)');
            if (codPaymentLabel) {
                if (!payment.cod_enabled) {
                    codPaymentLabel.classList.add('hidden');
                    // If COD is disabled and was selected, switch to online if available
                    const codRadio = document.getElementById('payment-cod');
                    if (codRadio && codRadio.checked && payment.online_enabled) {
                        const onlineRadio = document.getElementById('payment-online');
                        if (onlineRadio) onlineRadio.checked = true;
                    }
                } else {
                    codPaymentLabel.classList.remove('hidden');
                }
            }
            
            // If both are disabled, show a message
            if (!payment.online_enabled && !payment.cod_enabled) {
                const paymentContainer = document.querySelector('#step-3 .space-y-4');
                if (paymentContainer) {
                    paymentContainer.innerHTML = `
                        <div class="text-center py-8 text-gray-500">
                            <i data-lucide="alert-circle" class="w-12 h-12 mx-auto mb-4 text-yellow-500"></i>
                            <p class="font-medium">Payment methods are currently unavailable.</p>
                            <p class="text-sm mt-2">Please contact support for assistance.</p>
                        </div>
                    `;
                    lucide.createIcons();
                }
            }
            
            // Update tax display based on settings
            const taxRow = document.getElementById('tax-row');
            const taxLabel = document.getElementById('tax-label');
            
            if (state.settings.tax?.enabled) {
                // Show tax row and update the label with correct percentage
                if (taxRow) taxRow.classList.remove('hidden');
                if (taxLabel) {
                    const taxPercent = state.settings.tax.rate || 18;
                    taxLabel.textContent = `GST (${taxPercent}%)`;
                }
            } else {
                // Hide tax row if tax is disabled
                if (taxRow) taxRow.classList.add('hidden');
            }
        }

        /**
         * Load cart items from API
         */
        async function loadCart() {
            try {
                const response = await fetch(`${API_BASE}/cart.php`);
                const data = await response.json();
                
                if (data.success && data.data.items.length > 0) {
                    state.cartItems = data.data.items;
                    state.subtotal = data.data.total;
                    renderCartItems();
                    // Update totals including Pay Now button amount
                    updateOrderSummary();
                    showCartItems();
                } else {
                    showEmptyCart();
                }
            } catch (error) {
                console.error('Error loading cart:', error);
                showEmptyCart();
            } finally {
                state.isLoading = false;
                document.getElementById('cart-loading').classList.add('hidden');
            }
        }

        /**
         * Render cart items in order summary
         */
        function renderCartItems() {
            const container = document.getElementById('cart-items-list');
            if (!container) return;
            
            container.innerHTML = state.cartItems.map(item => {
                const imageUrl = item.image && item.image.startsWith('/') 
                    ? item.image 
                    : (item.image || 'logo.png');
                
                return `
                    <li class="flex py-6">
                        <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-xl border border-gray-200 relative bg-gray-50">
                            <img src="${imageUrl}" alt="${item.name}" class="h-full w-full object-cover object-center p-1" onerror="this.src='logo.png'">
                            <span class="absolute -top-2 -right-2 bg-gray-600 text-white text-[11px] font-bold w-5 h-5 flex items-center justify-center rounded-full shadow-md z-10">${item.quantity}</span>
                        </div>
                        <div class="ml-4 flex flex-1 flex-col justify-center">
                            <div>
                                <div class="flex justify-between text-base font-semibold text-gray-900">
                                    <h3 class="line-clamp-2">${item.name}</h3>
                                    <p class="ml-2 whitespace-nowrap">${formatCurrency(item.total_price)}</p>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">${formatCurrency(item.current_price)} × ${item.quantity}</p>
                            </div>
                        </div>
                    </li>
                `;
            }).join('');
            
            // Update items count badge
            const totalItems = state.cartItems.reduce((sum, item) => sum + item.quantity, 0);
            const badge = document.getElementById('items-count-badge');
            if (badge) {
                badge.textContent = `${totalItems} Item${totalItems !== 1 ? 's' : ''}`;
            }
        }

        /**
         * Show cart items container
         */
        function showCartItems() {
            document.getElementById('cart-loading')?.classList.add('hidden');
            document.getElementById('cart-empty')?.classList.add('hidden');
            document.getElementById('cart-items-container')?.classList.remove('hidden');
        }

        /**
         * Show empty cart state
         */
        function showEmptyCart() {
            document.getElementById('cart-loading')?.classList.add('hidden');
            document.getElementById('cart-items-container')?.classList.add('hidden');
            document.getElementById('cart-empty')?.classList.remove('hidden');
            lucide.createIcons();
        }

        /**
         * Update order summary totals
         */
        function updateOrderSummary() {
            const tax = state.subtotal * state.taxRate;
            const total = calculateTotal();
            
            document.getElementById('summary-subtotal').textContent = formatCurrency(state.subtotal);
            document.getElementById('summary-tax').textContent = formatCurrency(tax);
            document.getElementById('summary-total').textContent = formatCurrency(total);
            document.getElementById('final-btn-amount').textContent = formatCurrency(total);
            document.getElementById('summary-tax-note').textContent = `Including ${formatCurrency(tax)} in taxes`;
            
            // Update discount display
            updateDiscountDisplay();
        }

        /**
         * Apply discount code
         */
        async function applyDiscountCode() {
            const codeInput = document.getElementById('discount-code');
            const applyBtn = document.getElementById('apply-discount-btn');
            const errorDiv = document.getElementById('discount-error');
            const code = codeInput.value.trim();
            
            if (!code) {
                errorDiv.textContent = 'Please enter a discount code';
                errorDiv.classList.remove('hidden');
                return;
            }
            
            // Show loading state
            const originalBtnText = applyBtn.innerHTML;
            applyBtn.disabled = true;
            applyBtn.innerHTML = '<span>Applying...</span>';
            errorDiv.classList.add('hidden');
            
            try {
                const response = await fetch(`${API_BASE}/coupons.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        code: code,
                        subtotal: state.subtotal
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Apply discount
                    state.discountAmount = result.discount.amount;
                    state.appliedCoupon = {
                        code: result.coupon.code,
                        name: result.coupon.name,
                        description: result.coupon.description,
                        discount: result.discount
                    };
                    
                    // Update UI
                    updateDiscountDisplay();
                    updateTotals();
                    
                    // Show success message
                    showModal('Success', `Coupon "${code}" applied successfully!`, 'success');
                } else {
                    errorDiv.textContent = result.message || 'Invalid coupon code';
                    errorDiv.classList.remove('hidden');
                    state.discountAmount = 0;
                    state.appliedCoupon = null;
                    updateDiscountDisplay();
                    updateTotals();
                }
            } catch (error) {
                console.error('Error applying coupon:', error);
                errorDiv.textContent = 'Failed to apply coupon. Please try again.';
                errorDiv.classList.remove('hidden');
            } finally {
                applyBtn.disabled = false;
                applyBtn.innerHTML = originalBtnText;
            }
        }

        async function searchCouponsExplore() {
            const q = document.getElementById('coupon-explore-q')?.value ?? '';
            const sub = state.subtotal > 0 ? state.subtotal : 100;
            const pre = document.getElementById('coupon-explore-result');
            if (!pre) return;
            pre.classList.remove('hidden');
            pre.textContent = 'Loading...';
            try {
                const response = await fetch(`${API_BASE}/coupons.php?code=${encodeURIComponent(q)}&subtotal=${sub}`, {
                    credentials: 'include'
                });
                const j = await response.json();
                pre.textContent = JSON.stringify(j, null, 2);
            } catch (e) {
                pre.textContent = String(e);
            }
        }
        
        /**
         * Remove discount code
         */
        function removeDiscountCode() {
            state.discountAmount = 0;
            state.appliedCoupon = null;
            document.getElementById('discount-code').value = '';
            updateDiscountDisplay();
            updateTotals();
        }
        
        /**
         * Update discount display
         */
        function updateDiscountDisplay() {
            const discountRow = document.getElementById('discount-row');
            const summaryDiscount = document.getElementById('summary-discount');
            const appliedCouponDiv = document.getElementById('applied-coupon');
            const appliedCouponCode = document.getElementById('applied-coupon-code');
            const appliedCouponDesc = document.getElementById('applied-coupon-desc');
            
            if (state.discountAmount > 0 && state.appliedCoupon) {
                // Show discount row
                discountRow.classList.remove('hidden');
                summaryDiscount.textContent = `-${formatCurrency(state.discountAmount)}`;
                
                // Show applied coupon info
                appliedCouponDiv.classList.remove('hidden');
                appliedCouponCode.textContent = state.appliedCoupon.code;
                appliedCouponDesc.textContent = state.appliedCoupon.description || state.appliedCoupon.name;
                
                // Update discount code input
                document.getElementById('discount-code').value = state.appliedCoupon.code;
                document.getElementById('discount-code').disabled = true;
                
                // Initialize icons
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            } else {
                // Hide discount row
                discountRow.classList.add('hidden');
                appliedCouponDiv.classList.add('hidden');
                document.getElementById('discount-code').disabled = false;
            }
        }

        function calculateTotal() {
            const tax = state.subtotal * state.taxRate;
            return state.subtotal + tax + state.shippingCost - state.discountAmount;
        }
        
        /**
         * Update totals display
         */
        function updateTotals() {
            const tax = state.subtotal * state.taxRate;
            const total = calculateTotal();
            
            document.getElementById('summary-subtotal').textContent = formatCurrency(state.subtotal);
            document.getElementById('summary-tax').textContent = formatCurrency(tax);
            document.getElementById('summary-total').textContent = formatCurrency(total);
            
            // Update Pay Now button amount
            const finalBtnAmount = document.getElementById('final-btn-amount');
            if (finalBtnAmount) {
                finalBtnAmount.textContent = formatCurrency(total);
            }
            
            // Update discount display
            updateDiscountDisplay();
            
            // Update tax note
            document.getElementById('summary-tax-note').textContent = `Including ${formatCurrency(tax)} in taxes`;
        }

        function formatCurrency(amount) {
            return '₹' + amount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Validation Functions
        function validateEmail(input) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const errorMsg = document.getElementById('email-error');
            
            if (!input.value.trim()) {
                showFieldError(input, errorMsg, 'Email address is required');
                return false;
            }
            
            if (!emailRegex.test(input.value.trim())) {
                showFieldError(input, errorMsg, 'Please enter a valid email address');
                return false;
            }
            
            showFieldSuccess(input, errorMsg);
            return true;
        }

        function validatePhone(input) {
            const phoneRegex = /^[6-9]\d{9}$/;
            const errorMsg = document.getElementById('phone-error');
            const phoneValue = input.value.replace(/\D/g, ''); // Remove non-digits
            
            if (!phoneValue) {
                showFieldError(input, errorMsg, 'Phone number is required');
                return false;
            }
            
            if (!phoneRegex.test(phoneValue)) {
                showFieldError(input, errorMsg, 'Please enter a valid 10-digit Indian mobile number');
                return false;
            }
            
            showFieldSuccess(input, errorMsg);
            return true;
        }

        function validatePhoneInput(input) {
            // Only allow digits
            input.value = input.value.replace(/\D/g, '');
        }

        function validatePincode(input) {
            const pincodeRegex = /^\d{6}$/;
            const errorMsg = document.getElementById('zip-error');
            const pincodeValue = input.value.replace(/\D/g, ''); // Remove non-digits
            
            if (!pincodeValue) {
                showFieldError(input, errorMsg, 'PIN code is required');
                return false;
            }
            
            if (!pincodeRegex.test(pincodeValue)) {
                showFieldError(input, errorMsg, 'PIN code must be exactly 6 digits');
                return false;
            }
            
            showFieldSuccess(input, errorMsg);
            return true;
        }

        function validatePincodeInput(input) {
            // Only allow digits, max 6
            input.value = input.value.replace(/\D/g, '').substring(0, 6);
        }

        function showFieldError(input, errorElement, message) {
            if (input) {
                input.classList.add('input-error');
                input.classList.remove('input-valid');
            }
            if (errorElement) {
                errorElement.textContent = message;
                errorElement.classList.add('show');
            }
        }

        function showFieldSuccess(input, errorElement) {
            if (input) {
                input.classList.remove('input-error');
                input.classList.add('input-valid');
            }
            if (errorElement) {
                errorElement.classList.remove('show');
                errorElement.textContent = '';
            }
        }

        function clearError(input) {
            if (!input) return;
            input.classList.remove('input-error');
            const errorId = input.id + '-error';
            const errorElement = document.getElementById(errorId);
            if (errorElement) {
                errorElement.classList.remove('show');
                errorElement.textContent = '';
            }
        }

        /**
         * Get user-friendly field label
         */
        function getFieldLabel(fieldId) {
            const labels = {
                'email': 'Email',
                'first-name': 'First Name',
                'last-name': 'Last Name',
                'address': 'Address',
                'zip': 'PIN Code',
                'city': 'City',
                'state': 'State',
                'phone': 'Phone Number'
            };
            return labels[fieldId] || fieldId.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        /**
         * Comprehensive validation function that collects all errors
         * Returns: { isValid: boolean, errors: Array<{field: string, message: string}> }
         */
        function validateCheckoutForm() {
            const errors = [];
            const email = document.getElementById('email');
            const firstName = document.getElementById('first-name');
            const lastName = document.getElementById('last-name');
            const address = document.getElementById('address');
            const zip = document.getElementById('zip');
            const city = document.getElementById('city');
            const state = document.getElementById('state');
            const phone = document.getElementById('phone');

            // Validate email (always required)
            if (!email || !email.value.trim()) {
                errors.push({ field: 'email', message: 'Email address is required' });
                showFieldError(email, document.getElementById('email-error'), 'Email address is required');
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
                errors.push({ field: 'email', message: 'Please enter a valid email address' });
                showFieldError(email, document.getElementById('email-error'), 'Please enter a valid email address');
            } else {
                showFieldSuccess(email, document.getElementById('email-error'));
            }

            // Validate name fields (always required)
            if (!firstName || !firstName.value.trim()) {
                errors.push({ field: 'first-name', message: 'First name is required' });
                showFieldError(firstName, document.getElementById('first-name-error'), 'First name is required');
            } else {
                showFieldSuccess(firstName, document.getElementById('first-name-error'));
            }

            if (!lastName || !lastName.value.trim()) {
                errors.push({ field: 'last-name', message: 'Last name is required' });
                showFieldError(lastName, document.getElementById('last-name-error'), 'Last name is required');
            } else {
                showFieldSuccess(lastName, document.getElementById('last-name-error'));
            }

            // Check if using saved address or new address
            // Safety check: ensure variables are defined
            const isUsingSavedAddress = (typeof usingNewAddress !== 'undefined' && !usingNewAddress) && 
                                       (typeof selectedAddressId !== 'undefined' && selectedAddressId);
            
            if (isUsingSavedAddress) {
                // SAVED ADDRESS SELECTED - skip address form validation
                // Phone is optional when using saved address
            } else {
                // NEW ADDRESS - validate all address fields
                
                // Validate phone
                if (!phone || !phone.value.trim()) {
                    errors.push({ field: 'phone', message: 'Phone number is required' });
                    showFieldError(phone, document.getElementById('phone-error'), 'Phone number is required');
                } else {
                    const phoneValue = phone.value.replace(/\D/g, '');
                    if (!/^[6-9]\d{9}$/.test(phoneValue)) {
                        errors.push({ field: 'phone', message: 'Please enter a valid 10-digit Indian mobile number' });
                        showFieldError(phone, document.getElementById('phone-error'), 'Please enter a valid 10-digit Indian mobile number');
                    } else {
                        showFieldSuccess(phone, document.getElementById('phone-error'));
                    }
                }
                
                // Validate pincode
                if (!zip || !zip.value.trim()) {
                    errors.push({ field: 'zip', message: 'PIN code is required' });
                    showFieldError(zip, document.getElementById('zip-error'), 'PIN code is required');
                } else {
                    const pincodeValue = zip.value.replace(/\D/g, '');
                    if (!/^\d{6}$/.test(pincodeValue)) {
                        errors.push({ field: 'zip', message: 'PIN code must be exactly 6 digits' });
                        showFieldError(zip, document.getElementById('zip-error'), 'PIN code must be exactly 6 digits');
                    } else {
                        showFieldSuccess(zip, document.getElementById('zip-error'));
                    }
                }
                
                // Validate address fields
                if (!address || !address.value.trim()) {
                    errors.push({ field: 'address', message: 'Address is required' });
                    showFieldError(address, document.getElementById('address-error'), 'Address is required');
                } else {
                    showFieldSuccess(address, document.getElementById('address-error'));
                }

                if (!city || !city.value.trim()) {
                    errors.push({ field: 'city', message: 'City is required' });
                    showFieldError(city, document.getElementById('city-error'), 'City is required');
                } else {
                    showFieldSuccess(city, document.getElementById('city-error'));
                }

                // Validate state dropdown
                if (!state || !state.value || state.value === '') {
                    errors.push({ field: 'state', message: 'State is required' });
                    showFieldError(state, document.getElementById('state-error'), 'State is required');
                } else {
                    showFieldSuccess(state, document.getElementById('state-error'));
                }
            }

            return {
                isValid: errors.length === 0,
                errors: errors
            };
        }

        // Navigation Logic
        function goToStep(stepNumber) {
            // Validate step 1 before proceeding to step 2
            if (stepNumber === 2) {
                // Use comprehensive validation function
                const validation = validateCheckoutForm();
                
                if (!validation.isValid) {
                    // Scroll to first error field
                    const firstErrorField = document.getElementById(validation.errors[0].field);
                    if (firstErrorField) {
                        firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstErrorField.focus();
                    }
                    
                    // Show modal with specific errors
                    showModal(
                        'Validation Error', 
                        'Please fix the errors in the form before continuing.', 
                        'error',
                        true,
                        validation.errors
                    );
                    return;
                }
            }

            // Hide all steps with force
            document.querySelectorAll('.step-content').forEach(el => {
                el.classList.remove('step-active');
                el.classList.add('step-inactive');
                el.style.display = 'none';
            });

            // Show target step with force
            const targetStep = document.getElementById(`step-${stepNumber}`);
            if (targetStep) {
                targetStep.classList.remove('step-inactive');
                targetStep.classList.add('step-active');
                targetStep.style.display = 'block';

                // Scroll to top of form
                setTimeout(() => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }, 100);

                // Update Progress Bar
                updateProgress(stepNumber);

                // If moving to step 2, update summary info
                if (stepNumber === 2) {
                    const email = document.getElementById('email').value || "user@example.com";
                    const firstName = document.getElementById('first-name').value;
                    const lastName = document.getElementById('last-name').value;
                    
                    // Get address from saved or form
                    const shippingData = getShippingAddressData();
                    const addressSummary = shippingData.city 
                        ? `${shippingData.addressLine1}, ${shippingData.city}, ${shippingData.state}`
                        : `${firstName} ${lastName}`.trim() || "Your Address";
                    
                    if (document.getElementById('summary-email')) {
                        document.getElementById('summary-email').textContent = email;
                    }
                    if (document.getElementById('summary-address')) {
                        document.getElementById('summary-address').textContent = addressSummary;
                    }
                }
            } else {
                console.error(`Step ${stepNumber} not found`);
            }
        }

        function updateProgress(step) {
            // Update Icons & Text
            const steps = [1, 2, 3];
            
            steps.forEach(s => {
                const iconContainer = document.getElementById(`step-icon-${s}`);
                const text = document.getElementById(`step-text-${s}`);
                
                // Null-safety check: prevent crashes if DOM elements don't exist yet
                if (!iconContainer || !text) return;
                
                let iconName = '';
                let iconClasses = '';
                let containerClasses = '';
                let textClasses = '';

                // Define content based on state
                if (s === 1) iconName = 'user';
                else if (s === 2) iconName = 'truck';
                else if (s === 3) iconName = 'credit-card';

                if (step === s) {
                    // Active State (Current Step)
                    containerClasses = "flex items-center justify-center w-8 h-8 bg-white border-2 border-brand rounded-full transition-all-200 ring-4 ring-teal-50";
                    iconClasses = "w-4 h-4 text-brand";
                    textClasses = "ml-3 text-sm font-bold text-brand hidden sm:block";
                } else if (step > s) {
                    // Completed State
                    containerClasses = "flex items-center justify-center w-8 h-8 bg-brand border-2 border-brand rounded-full transition-all-200";
                    iconName = 'check'; // Force checkmark
                    iconClasses = "w-4 h-4 text-white";
                    textClasses = "ml-3 text-sm font-medium text-teal-700 hidden sm:block";
                } else {
                    // Inactive State
                    containerClasses = "flex items-center justify-center w-8 h-8 bg-white border-2 border-gray-300 rounded-full transition-all-200";
                    iconClasses = "w-4 h-4 text-gray-400";
                    textClasses = "ml-3 text-sm font-medium text-gray-500 hidden sm:block";
                }

                // Apply classes to container and text
                iconContainer.className = containerClasses;
                text.className = textClasses;

                // Re-inject icon HTML (wipes container clean and creates fresh <i> tag)
                // This ensures compatibility with Lucide's DOM manipulation
                iconContainer.innerHTML = `<i data-lucide="${iconName}" class="${iconClasses}"></i>`;
            });
            
            // Re-render icons
            lucide.createIcons();
            
            // Update Bars
            const bar1 = document.getElementById('progress-bar-1');
            const bar2 = document.getElementById('progress-bar-2');

            if (step >= 2) bar1.style.width = '100%'; else bar1.style.width = '0%';
            if (step >= 3) bar2.style.width = '100%'; else bar2.style.width = '0%';
        }

        function updateTotal() {
            const shippingMethod = document.querySelector('input[name="shipping-method"]:checked')?.value || 'standard';
            const shippingDisplay = document.getElementById('summary-shipping');
            
            if (shippingMethod === 'express') {
                state.shippingCost = 450;
                shippingDisplay.textContent = '₹450.00';
                shippingDisplay.className = 'font-medium text-gray-900 text-sm';
            } else {
                state.shippingCost = 0;
                shippingDisplay.textContent = 'Free';
                shippingDisplay.className = 'font-medium text-brand text-sm';
            }
            
            // Update all totals including Pay Now button amount
            updateOrderSummary();
            updatePaymentButton();
        }

        /**
         * Update Place Order button text based on payment method
         */
        function updatePaymentButton() {
            const paymentMethod = document.querySelector('input[name="payment-method"]:checked');
            const placeOrderText = document.getElementById('place-order-text');
            
            if (!paymentMethod || !placeOrderText) return;
            
            if (paymentMethod.id === 'payment-cod') {
                placeOrderText.textContent = 'Place Order';
            } else {
                placeOrderText.textContent = 'Pay Now';
            }
        }

        /**
         * Toggle billing address form visibility
         */
        function toggleBillingForm() {
            const billingDifferent = document.getElementById('billing-different');
            const billingForm = document.getElementById('billing-form');
            
            if (!billingDifferent || !billingForm) return;
            
            if (billingDifferent.checked) {
                billingForm.classList.remove('hidden');
                // Make billing fields required
                document.getElementById('billing-address1').required = true;
                document.getElementById('billing-city').required = true;
                document.getElementById('billing-state').required = true;
                document.getElementById('billing-pincode').required = true;
            } else {
                billingForm.classList.add('hidden');
                // Remove required from billing fields
                document.getElementById('billing-address1').required = false;
                document.getElementById('billing-city').required = false;
                document.getElementById('billing-state').required = false;
                document.getElementById('billing-pincode').required = false;
            }
        }

        // Note: Cart/Wishlist counters and navbar are handled in cart-wishlist.js

        // Modal Functions
        function showModal(title, message, type = 'error', showCloseButton = true, errorList = null) {
            const overlay = document.getElementById('modal-overlay');
            const modalTitle = document.getElementById('modal-title');
            const modalContent = document.getElementById('modal-content');
            const modalIcon = document.getElementById('modal-icon');
            const modalFooter = document.querySelector('.modal-footer');
            const modalClose = document.querySelector('.modal-close');
            
            modalTitle.textContent = title;
            
            // Update icon based on type
            let iconName = 'alert-circle';
            let iconClass = 'modal-icon error';
            
            if (type === 'success') {
                iconName = 'check-circle';
                iconClass = 'modal-icon success';
                // Special success layout - Mobile responsive
                modalContent.innerHTML = `
                    <div class="modal-success-content">
                        <div class="modal-success-icon">
                            <i data-lucide="check" class="w-8 h-8 text-white"></i>
                        </div>
                        <p class="modal-message font-semibold text-lg sm:text-xl text-gray-900 mb-2 sm:mb-3">${title}</p>
                        <p class="modal-message text-gray-600 text-sm sm:text-base break-words px-2">${message}</p>
                    </div>
                `;
            } else {
                // Regular layout - support error list
                let contentHTML = `<p class="modal-message text-sm sm:text-base break-words mb-3" id="modal-message">${message}</p>`;
                
                // If error list is provided, show specific errors
                if (errorList && Array.isArray(errorList) && errorList.length > 0) {
                    contentHTML += '<div class="text-left mt-4 space-y-2 max-h-60 overflow-y-auto">';
                    contentHTML += '<p class="text-sm font-semibold text-gray-700 mb-2">Please fix the following:</p>';
                    contentHTML += '<ul class="list-disc list-inside space-y-1.5 text-sm text-gray-600">';
                    errorList.forEach(error => {
                        const fieldLabel = getFieldLabel(error.field);
                        contentHTML += `<li class="flex items-start"><span class="text-red-500 mr-2">•</span><span><strong>${fieldLabel}:</strong> ${error.message}</span></li>`;
                    });
                    contentHTML += '</ul>';
                    contentHTML += '</div>';
                }
                
                modalContent.innerHTML = contentHTML;
            }
            
            modalIcon.setAttribute('data-lucide', iconName);
            modalIcon.className = iconClass;
            
            // Show/hide close button
            if (modalClose) {
                modalClose.style.display = showCloseButton ? 'flex' : 'none';
            }
            
            // Show modal
            overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
            
            // Prevent body scroll on mobile
            if (window.innerWidth <= 640) {
                const scrollY = window.scrollY;
                document.body.style.position = 'fixed';
                document.body.style.top = `-${scrollY}px`;
                document.body.style.width = '100%';
            }
            
            // Re-render icons
            lucide.createIcons();
        }

        function closeModal() {
            const overlay = document.getElementById('modal-overlay');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
            
            // Restore body scroll on mobile
            if (window.innerWidth <= 640) {
                const scrollY = document.body.style.top;
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.width = '';
                if (scrollY) {
                    window.scrollTo(0, parseInt(scrollY || '0') * -1);
                }
            }
        }

        // Handle Place Order
        async function handlePlaceOrder(event) {
            event.preventDefault();
            
            const placeOrderBtn = event.target.closest('button[type="submit"]');
            const placeOrderText = document.getElementById('place-order-text');
            const placeOrderLoading = document.getElementById('place-order-loading');
            
            if (!placeOrderBtn) return;
            
            // Check if cart is empty
            if (state.cartItems.length === 0) {
                showModal('Empty Cart', 'Your cart is empty. Please add items before placing an order.', 'error');
                return;
            }
            
            // Validate form fields using comprehensive validation
            const validation = validateCheckoutForm();
            
            if (!validation.isValid) {
                // Scroll to first error field
                const firstErrorField = document.getElementById(validation.errors[0].field);
                if (firstErrorField) {
                    firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstErrorField.focus();
                }
                
                // Show modal with specific errors
                showModal(
                    'Validation Error', 
                    'Please fix the errors in the form before placing your order.', 
                    'error',
                    true,
                    validation.errors
                );
                resetButtonState(placeOrderBtn, placeOrderText, placeOrderLoading);
                return;
            }
            
            // Validate payment method
            const paymentMethodInput = document.querySelector('input[name="payment-method"]:checked');
            if (!paymentMethodInput) {
                showModal('Payment Required', 'Please select a payment method to continue.', 'error');
                return;
            }
            
            // Map payment method value
            let paymentMethod = 'online';
            if (paymentMethodInput.id === 'payment-cod') {
                paymentMethod = 'cod';
            } else if (paymentMethodInput.id === 'payment-online') {
                paymentMethod = 'online';
            }
            
            // Show loading state
            if (placeOrderText) placeOrderText.classList.add('hidden');
            if (placeOrderLoading) placeOrderLoading.classList.remove('hidden');
            placeOrderBtn.disabled = true;
            
            // Check if using different billing address
            const useDifferentBilling = document.getElementById('billing-different')?.checked || false;
            
            // Collect billing address data
            let billingData = null;
            if (useDifferentBilling) {
                // Validate billing fields
                const billingAddress1 = document.getElementById('billing-address1')?.value.trim();
                const billingCity = document.getElementById('billing-city')?.value.trim();
                const billingState = document.getElementById('billing-state')?.value.trim();
                const billingPincode = document.getElementById('billing-pincode')?.value.trim();
                
                if (!billingAddress1 || !billingCity || !billingState || !billingPincode) {
                    showModal('Billing Address Required', 'Please fill in all required billing address fields.', 'error');
                    resetButtonState(placeOrderBtn, placeOrderText, placeOrderLoading);
                    return;
                }
                
                if (!/^\d{6}$/.test(billingPincode)) {
                    showModal('Invalid PIN Code', 'Billing PIN code must be exactly 6 digits.', 'error');
                    resetButtonState(placeOrderBtn, placeOrderText, placeOrderLoading);
                    return;
                }
                
                billingData = {
                    addressLine1: billingAddress1,
                    addressLine2: document.getElementById('billing-address2')?.value.trim() || '',
                    city: billingCity,
                    state: billingState,
                    pincode: billingPincode,
                    country: 'India'
                };
            }
            
            // Get shipping address from saved or form
            const shippingAddressData = getShippingAddressData();
            
            // Validate that address data is complete
            if (!shippingAddressData.addressLine1 || !shippingAddressData.city || !shippingAddressData.state || !shippingAddressData.pincode) {
                showModal('Address Required', 'Please provide a complete shipping address.', 'error');
                resetButtonState(placeOrderBtn, placeOrderText, placeOrderLoading);
                return;
            }
            
            // Get phone - from saved address if available, otherwise from form
            const customerPhone = shippingAddressData.phone || document.getElementById('phone').value.trim();
            
            // Get name - from saved address if available, otherwise from form
            const customerFirstName = document.getElementById('first-name').value.trim();
            const customerLastName = document.getElementById('last-name').value.trim();
            const shippingName = shippingAddressData.fullName || `${customerFirstName} ${customerLastName}`.trim();
            
            // Collect form data for API
            const orderData = {
                customer: {
                    firstName: customerFirstName,
                    lastName: customerLastName,
                    email: document.getElementById('email').value.trim(),
                    phone: customerPhone
                },
                shipping: {
                    firstName: customerFirstName,
                    lastName: customerLastName,
                    fullName: shippingName,
                    addressLine1: shippingAddressData.addressLine1,
                    addressLine2: shippingAddressData.addressLine2,
                    city: shippingAddressData.city,
                    state: shippingAddressData.state,
                    pincode: shippingAddressData.pincode,
                    country: shippingAddressData.country,
                    phone: customerPhone,
                    method: document.querySelector('input[name="shipping-method"]:checked')?.value || 'standard',
                    savedAddressId: shippingAddressData.savedAddressId
                },
                billing: billingData, // null if same as shipping, object if different
                payment: {
                    method: paymentMethod
                },
                newsletter: document.getElementById('newsletter')?.checked || false,
                cart: state.cartItems.map(item => ({
                    id: item.product_id,
                    name: item.name,
                    price: item.current_price,
                    quantity: item.quantity,
                    sku: item.sku || null
                })),
                totals: {
                    subtotal: state.subtotal,
                    shipping: state.shippingCost,
                    tax: state.settings.tax?.enabled ? state.subtotal * state.taxRate : 0,
                    tax_rate: state.settings.tax?.enabled ? (state.settings.tax.rate || 18) : 0,
                    discount: state.discountAmount,
                    total: calculateTotal()
                },
                coupon_code: state.appliedCoupon ? state.appliedCoupon.code : null
            };
            
            // If online payment selected, initiate Razorpay
            if (paymentMethod === 'online') {
                await initiateRazorpayPayment(orderData, placeOrderBtn, placeOrderText, placeOrderLoading);
            } else {
                // COD - directly place order
                await placeOrderDirectly(orderData, placeOrderBtn, placeOrderText, placeOrderLoading);
            }
        }

        /**
         * Calculate total amount
         */
        function calculateTotal() {
            const tax = state.settings.tax?.enabled ? state.subtotal * state.taxRate : 0;
            return state.subtotal + tax + state.shippingCost - state.discountAmount;
        }

        /**
         * Initiate Razorpay Payment
         */
        async function initiateRazorpayPayment(orderData, placeOrderBtn, placeOrderText, placeOrderLoading) {
            const razorpayKey = state.settings.payment?.razorpay_key;
            
            if (!razorpayKey) {
                showModal('Configuration Error', 'Online payment is not configured. Please contact support or choose Cash on Delivery.', 'error');
                resetButtonState(placeOrderBtn, placeOrderText, placeOrderLoading);
                return;
            }
            
            try {
                // Create order on server first to get order ID for Razorpay
                const response = await fetch(`${API_BASE}/payment/create-order.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        amount: Math.round(calculateTotal() * 100), // Amount in paise
                        currency: 'INR',
                        orderData: orderData
                    })
                });
                
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error(result.message || 'Failed to create payment order');
                }
                
                // Razorpay options
                const options = {
                    key: razorpayKey,
                    amount: result.data.amount,
                    currency: result.data.currency || 'INR',
                    name: state.settings.general?.site_name || 'BLine Boutique',
                    description: `Order Payment`,
                    order_id: result.data.razorpay_order_id,
                    prefill: {
                        name: `${orderData.customer.firstName} ${orderData.customer.lastName}`,
                        email: orderData.customer.email,
                        contact: orderData.customer.phone
                    },
                    theme: {
                        color: '#14b8a6' // Teal theme matching site
                    },
                    handler: async function(response) {
                        // Payment successful - verify and complete order
                        await verifyAndCompleteOrder(response, result.data.order_id, orderData, placeOrderBtn, placeOrderText, placeOrderLoading);
                    },
                    modal: {
                        ondismiss: function() {
                            // User cancelled payment
                            showModal('Payment Cancelled', 'You cancelled the payment. Your order has not been placed.', 'error');
                            resetButtonState(placeOrderBtn, placeOrderText, placeOrderLoading);
                        }
                    }
                };
                
                // Open Razorpay checkout
                const rzp = new Razorpay(options);
                rzp.on('payment.failed', function(response) {
                    showModal('Payment Failed', response.error.description || 'Payment failed. Please try again.', 'error');
                    resetButtonState(placeOrderBtn, placeOrderText, placeOrderLoading);
                });
                rzp.open();
                
            } catch (error) {
                console.error('Razorpay init error:', error);
                showModal('Payment Error', error.message || 'Failed to initialize payment. Please try again.', 'error');
                resetButtonState(placeOrderBtn, placeOrderText, placeOrderLoading);
            }
        }

        /**
         * Verify Razorpay payment and complete order
         */
        async function verifyAndCompleteOrder(paymentResponse, orderId, orderData, placeOrderBtn, placeOrderText, placeOrderLoading) {
            try {
                const response = await fetch(`${API_BASE}/payment/verify.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        razorpay_order_id: paymentResponse.razorpay_order_id,
                        razorpay_payment_id: paymentResponse.razorpay_payment_id,
                        razorpay_signature: paymentResponse.razorpay_signature,
                        order_id: orderId
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showModal(
                        'Payment Successful!', 
                        `Your payment has been received and order confirmed.<br><br><strong>Order ID:</strong> ${result.data.order_number}<br><br>Redirecting to order details...`, 
                        'success',
                        false
                    );
                    
                    setTimeout(() => {
                        window.location.href = `order-success.php?order=${result.data.order_number}`;
                    }, 2500);
                } else {
                    throw new Error(result.message || 'Payment verification failed');
                }
            } catch (error) {
                console.error('Payment verification error:', error);
                showModal('Verification Error', 'Payment received but verification failed. Please contact support with your payment ID.', 'error');
                resetButtonState(placeOrderBtn, placeOrderText, placeOrderLoading);
            }
        }

        /**
         * Place order directly (for COD)
         */
        async function placeOrderDirectly(orderData, placeOrderBtn, placeOrderText, placeOrderLoading) {
            try {
                const response = await fetch(`${API_BASE}/orders.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(orderData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const orderNumber = result.order_number || result.data?.order_number;
                    
                    showModal(
                        'Order Placed Successfully!', 
                        `Your order has been confirmed.<br><br><strong>Order ID:</strong> ${orderNumber}<br><br>Redirecting to order details...`, 
                        'success',
                        false
                    );
                    
                    setTimeout(() => {
                        window.location.href = `order-success.php?order=${orderNumber}`;
                    }, 2500);
                } else {
                    showModal('Order Failed', result.message || 'Failed to place order. Please try again.', 'error');
                    resetButtonState(placeOrderBtn, placeOrderText, placeOrderLoading);
                }
            } catch (error) {
                console.error('Order submission error:', error);
                showModal('Error', 'An error occurred while placing your order. Please try again.', 'error');
                resetButtonState(placeOrderBtn, placeOrderText, placeOrderLoading);
            }
        }

        /**
         * Reset button state after error/cancel
         */
        function resetButtonState(placeOrderBtn, placeOrderText, placeOrderLoading) {
            if (placeOrderText) placeOrderText.classList.remove('hidden');
            if (placeOrderLoading) placeOrderLoading.classList.add('hidden');
            if (placeOrderBtn) placeOrderBtn.disabled = false;
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Initialize checkout page
        document.addEventListener('DOMContentLoaded', function() {
            initCheckout();
            updateProgress(1); // Ensure initial state is rendered correctly
            lucide.createIcons(); // Initialize icons
        });
    </script>

    <!-- SECTION 5: FOOTER & CONTACT -->
    <footer id="contact" class="bg-gray-50 pt-12 sm:pt-16 md:pt-20 border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-8 sm:gap-10 md:gap-12 mb-12 sm:mb-16">
                
                <!-- Brand Info -->
                <div>
                    <div class="mb-6">
                        <h2 class="text-2xl font-serif font-bold text-gray-900">BLine.</h2>
                    </div>
                    <p class="text-gray-500 mb-6 text-sm leading-relaxed">
                        BLine Boutique - Elegance Redefined. Discover unparalleled craftsmanship and timeless elegance in our latest boutique arrivals.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-brand transition-colors"><i data-lucide="facebook" class="w-5 h-5"></i></a>
                        <a href="#" class="text-gray-400 hover:text-brand transition-colors"><i data-lucide="instagram" class="w-5 h-5"></i></a>
                        <a href="#" class="text-gray-400 hover:text-brand transition-colors"><i data-lucide="twitter" class="w-5 h-5"></i></a>
                    </div>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="font-bold text-gray-900 mb-6">Contact Us</h4>
                    <ul class="space-y-4 text-sm text-gray-600">
                        <li class="flex items-start gap-3">
                            <i data-lucide="map-pin" class="w-5 h-5 text-brand mt-0.5"></i>
                            <span>BLine Boutique<br>Your Address Here</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i data-lucide="phone" class="w-5 h-5 text-brand"></i>
                            <span>+91 6238762189</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i data-lucide="mail" class="w-5 h-5 text-brand"></i>
                            <span>info@blineboutique.com</span>
                        </li>
                    </ul>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="font-bold text-gray-900 mb-6">Quick Links</h4>
                    <ul class="space-y-3 text-sm text-gray-600">
                        <li><a href="index.php#about" class="hover:text-brand transition-colors">About us</a></li>
                        <li><a href="index.php#contact" class="hover:text-brand transition-colors">Contact Us</a></li>
                        <li><a href="index.php#products" class="hover:text-brand transition-colors">Products</a></li>
                    </ul>
                </div>

                <!-- Information -->
                <div>
                    <h4 class="font-bold text-gray-900 mb-6">Information</h4>
                    <ul class="space-y-3 text-sm text-gray-600">
                        <li><a href="#" class="hover:text-brand transition-colors">Terms & Conditions</a></li>
                        <li><a href="#" class="hover:text-brand transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-brand transition-colors">Refund Policy</a></li>
                    </ul>
                </div>

                <!-- Your Account -->
                <div>
                    <h4 class="font-bold text-gray-900 mb-6">Your Account</h4>
                    <ul class="space-y-3 text-sm text-gray-600">
                        <li><a href="#" class="hover:text-brand transition-colors">Search</a></li>
                        <li><a href="#" class="hover:text-brand transition-colors">Profile</a></li>
                        <li><a href="#" class="hover:text-brand transition-colors">Orders</a></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="border-t border-gray-200 py-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs text-gray-500">© 2026 BLine Boutique. All rights reserved.</p>
                <div class="flex gap-4">
                     <!-- Payment Icons (Simulated with text/svg for simplicity) -->
                     <span class="text-xs text-gray-400 border border-gray-200 px-2 py-1 rounded">Visa</span>
                     <span class="text-xs text-gray-400 border border-gray-200 px-2 py-1 rounded">Mastercard</span>
                     <span class="text-xs text-gray-400 border border-gray-200 px-2 py-1 rounded">UPI</span>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>