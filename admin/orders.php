<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management | Tivora Admin</title>
    
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

        .admin-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .admin-sidebar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .admin-sidebar::-webkit-scrollbar-thumb {
            background: #14b8a6;
            border-radius: 3px;
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
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-confirmed {
            background: #dcfce7;
            color: #166534;
        }

        .status-processing {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-shipped {
            background: #e9d5ff;
            color: #6b21a8;
        }

        .status-delivered {
            background: #f3f4f6;
            color: #374151;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal {
            background: white;
            border-radius: 16px;
            padding: 32px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-large {
            max-width: 900px;
            padding: 0;
        }

        /* Payment Badge Styles */
        .payment-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .payment-cod {
            background: #fef3c7;
            color: #92400e;
        }

        .payment-online {
            background: #dbeafe;
            color: #1e40af;
        }

        .payment-pending {
            color: #d97706;
        }

        .payment-completed {
            color: #059669;
        }

        .payment-failed {
            color: #0d9488;
        }

        /* Order Detail Modal Styles */
        .order-detail-header {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            color: white;
            padding: 24px 32px;
            border-radius: 16px 16px 0 0;
        }

        .order-detail-body {
            padding: 24px 32px;
        }

        .detail-section {
            margin-bottom: 24px;
        }

        .detail-section:last-child {
            margin-bottom: 0;
        }

        .detail-section-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-card {
            background: #f9fafb;
            border-radius: 12px;
            padding: 16px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .detail-value {
            color: #111827;
            font-weight: 500;
            font-size: 0.875rem;
            text-align: right;
        }

        .order-item {
            display: flex;
            gap: 12px;
            padding: 12px;
            background: white;
            border-radius: 8px;
            margin-bottom: 8px;
            border: 1px solid #e5e7eb;
        }

        .order-item:last-child {
            margin-bottom: 0;
        }

        .order-item-image {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .order-item-details {
            flex: 1;
            min-width: 0;
        }

        .order-item-name {
            font-weight: 500;
            color: #111827;
            font-size: 0.875rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .order-item-sku {
            color: #9ca3af;
            font-size: 0.75rem;
        }

        .order-item-qty-price {
            text-align: right;
            flex-shrink: 0;
        }

        .order-item-price {
            font-weight: 600;
            color: #111827;
        }

        .order-item-qty {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 0.875rem;
        }

        .totals-row.total {
            font-size: 1rem;
            font-weight: 700;
            color: #14b8a6;
            border-top: 2px solid #e5e7eb;
            padding-top: 12px;
            margin-top: 4px;
        }

        .timeline {
            position: relative;
            padding-left: 24px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 8px;
            bottom: 8px;
            width: 2px;
            background: #e5e7eb;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 16px;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            left: -24px;
            top: 2px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #e5e7eb;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #e5e7eb;
        }

        .timeline-dot.active {
            background: #22c55e;
            box-shadow: 0 0 0 2px #22c55e;
        }

        .timeline-dot.cancelled {
            background: #14b8a6;
            box-shadow: 0 0 0 2px #14b8a6;
        }

        .timeline-content {
            font-size: 0.875rem;
        }

        .timeline-title {
            font-weight: 500;
            color: #111827;
        }

        .timeline-date {
            color: #9ca3af;
            font-size: 0.75rem;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-sidebar.show {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0;
            }

            .mobile-menu-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }

            .mobile-menu-overlay.show {
                display: block;
            }

            .modal {
                padding: 24px;
                width: 95%;
            }
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

        ::-webkit-scrollbar-thumb:hover {
            background: #0d9488;
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
            <a href="orders.php" class="admin-sidebar-item active">
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
            <a href="api_integration.php" class="admin-sidebar-item">
                <i data-lucide="plug"></i>
                <span>API Integration</span>
            </a>
            <a href="settings.php" class="admin-sidebar-item">
                <i data-lucide="settings"></i>
                <span>Settings</span>
            </a>
        </nav>

        <!-- Bottom Section -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200">
            <a href="../index.php" class="admin-sidebar-item">
                <i data-lucide="arrow-left"></i>
                <span>Back to Website</span>
            </a>
            <a href="../login.php" class="admin-sidebar-item">
                <i data-lucide="log-out"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobile-menu-overlay" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <main class="admin-main">
        <!-- Top Header -->
        <header class="admin-header">
            <div class="flex items-center gap-4">
                <button id="sidebar-toggle" class="lg:hidden text-gray-600 hover:text-teal-600" onclick="toggleSidebar()">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h1 class="text-2xl font-bold text-gray-900">Order Management</h1>
            </div>
        </header>

        <!-- Content -->
        <div class="p-6 lg:p-8">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
                <div class="card">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <i data-lucide="shopping-bag" class="w-6 h-6 text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Orders</p>
                            <p class="text-2xl font-bold text-gray-900" id="total-orders">0</p>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                            <i data-lucide="check-circle" class="w-6 h-6 text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Confirmed</p>
                            <p class="text-2xl font-bold text-gray-900" id="confirmed-orders">0</p>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
                            <i data-lucide="clock" class="w-6 h-6 text-yellow-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Processing</p>
                            <p class="text-2xl font-bold text-gray-900" id="processing-orders">0</p>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                            <i data-lucide="truck" class="w-6 h-6 text-purple-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Shipped</p>
                            <p class="text-2xl font-bold text-gray-900" id="shipped-orders">0</p>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center">
                            <i data-lucide="package-check" class="w-6 h-6 text-gray-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Delivered</p>
                            <p class="text-2xl font-bold text-gray-900" id="delivered-orders">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Bar -->
            <div class="card mb-6">
                <div class="flex flex-col md:flex-row gap-4 items-center">
                    <div class="flex-1 w-full">
                        <div class="relative">
                            <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                            <input type="text" id="search-input" placeholder="Search orders by ID, customer name, email..." 
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                    <select id="filter-status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="all">All Status</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <input type="date" id="filter-date" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                    <button onclick="resetFilters()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="card">
                <div class="overflow-x-auto">
                    <table class="w-full" id="orders-table">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Order ID</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Customer</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Date</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Items</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Amount</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Payment</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                                <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="orders-tbody" class="divide-y divide-gray-200">
                            <!-- Orders will be dynamically loaded here -->
                        </tbody>
                    </table>
                    <div id="empty-state" class="hidden py-12 text-center">
                        <i data-lucide="shopping-bag-x" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                        <p class="text-gray-500 text-lg font-medium mb-2">No orders found</p>
                        <p class="text-gray-400 text-sm">Orders will appear here when customers place them</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Update Status Modal -->
    <div class="modal-overlay" id="status-modal">
        <div class="modal" style="max-width: 400px;">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Update Order Status</h2>
                <button onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form id="status-form" onsubmit="updateOrderStatus(event)">
                <input type="hidden" id="status-order-id">
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">Order: <span class="font-medium text-gray-900" id="status-order-number"></span></p>
                    <p class="text-sm text-gray-600">Current Status: <span class="font-medium text-gray-900" id="status-current-status"></span></p>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Status *</label>
                        <select id="new-status" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">Select Status</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                        <select id="new-payment-status" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                        <p class="text-[10px] text-gray-500 mt-1">Note: Setting order to 'Delivered' automatically marks as 'Completed'.</p>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="submit" class="flex-1 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-medium">
                        Update Status
                    </button>
                    <button type="button" onclick="closeStatusModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Order Detail Modal -->
    <div class="modal-overlay" id="order-detail-modal" onclick="closeOrderDetailModal(event)">
        <div class="modal modal-large" onclick="event.stopPropagation()">
            <!-- Header -->
            <div class="order-detail-header">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold" id="detail-order-id">Order Details</h2>
                        <p class="text-sm text-red-100 mt-1" id="detail-order-date">-</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="status-badge bg-white/20 text-white" id="detail-order-status">-</span>
                        <button onclick="closeOrderDetailModal()" class="text-white/80 hover:text-white p-1">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Body -->
            <div class="order-detail-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div>
                        <!-- Customer Info -->
                        <div class="detail-section">
                            <h3 class="detail-section-title">
                                <i data-lucide="user" class="w-4 h-4"></i>
                                Customer Information
                            </h3>
                            <div class="detail-card">
                                <div class="detail-row">
                                    <span class="detail-label">Name</span>
                                    <span class="detail-value" id="detail-customer-name">-</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Email</span>
                                    <span class="detail-value" id="detail-customer-email">-</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Phone</span>
                                    <span class="detail-value" id="detail-customer-phone">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="detail-section">
                            <h3 class="detail-section-title">
                                <i data-lucide="map-pin" class="w-4 h-4"></i>
                                Shipping Address
                            </h3>
                            <div class="detail-card">
                                <p class="text-sm text-gray-700" id="detail-shipping-address">-</p>
                            </div>
                        </div>

                        <!-- Payment Info -->
                        <div class="detail-section">
                            <h3 class="detail-section-title">
                                <i data-lucide="credit-card" class="w-4 h-4"></i>
                                Payment Information
                            </h3>
                            <div class="detail-card">
                                <div class="detail-row">
                                    <span class="detail-label">Method</span>
                                    <span class="detail-value" id="detail-payment-method">-</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Status</span>
                                    <span class="detail-value" id="detail-payment-status">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Status Timeline -->
                        <div class="detail-section">
                            <h3 class="detail-section-title">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                                Order Timeline
                            </h3>
                            <div class="detail-card">
                                <div class="timeline" id="detail-timeline">
                                    <!-- Timeline items will be inserted here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div>
                        <!-- Order Items -->
                        <div class="detail-section">
                            <h3 class="detail-section-title">
                                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                Order Items
                            </h3>
                            <div class="detail-card" id="detail-items-container">
                                <!-- Items will be inserted here -->
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="detail-section">
                            <h3 class="detail-section-title">
                                <i data-lucide="receipt" class="w-4 h-4"></i>
                                Order Summary
                            </h3>
                            <div class="detail-card">
                                <div class="totals-row">
                                    <span class="text-gray-600">Subtotal</span>
                                    <span class="font-medium" id="detail-subtotal">₹0</span>
                                </div>
                                <div class="totals-row">
                                    <span class="text-gray-600">Discount</span>
                                    <span class="font-medium text-green-600" id="detail-discount">-₹0</span>
                                </div>
                                <div class="totals-row">
                                    <span class="text-gray-600">Tax (GST)</span>
                                    <span class="font-medium" id="detail-tax">₹0</span>
                                </div>
                                <div class="totals-row">
                                    <span class="text-gray-600">Shipping</span>
                                    <span class="font-medium" id="detail-shipping">₹0</span>
                                </div>
                                <div class="totals-row total">
                                    <span>Total</span>
                                    <span id="detail-total">₹0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="detail-section" id="notes-section" style="display: none;">
                            <h3 class="detail-section-title">
                                <i data-lucide="message-square" class="w-4 h-4"></i>
                                Notes
                            </h3>
                            <div class="detail-card">
                                <p class="text-sm text-gray-700" id="detail-notes">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 mt-6 pt-6 border-t border-gray-200">
                    <button onclick="openStatusModalFromDetail()" class="flex-1 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-medium flex items-center justify-center gap-2">
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                        Update Status
                    </button>
                    <button onclick="printOrder()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium flex items-center justify-center gap-2">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        Print
                    </button>
                    <button onclick="closeOrderDetailModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Orders data loaded from API
        let orders = [];
        
        // API base URL
        const API_BASE = '/api/v1/admin';

        // Initialize Lucide Icons
        lucide.createIcons();

        // Load orders on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadOrders();
        });

        // Load orders from API
        async function loadOrders() {
            try {
                const response = await fetch(`${API_BASE}/orders.php`);
                const result = await response.json();
                
                if (result.success) {
                    orders = result.data || [];
                    renderOrders();
                    updateSummary();
                } else {
                    console.error('Failed to load orders:', result.message);
                }
            } catch (error) {
                console.error('Error loading orders:', error);
            }
        }

        // Render orders table
        function renderOrders(filteredOrders = null) {
            const tbody = document.getElementById('orders-tbody');
            const emptyState = document.getElementById('empty-state');
            const ordersToRender = filteredOrders || orders.sort((a, b) => new Date(b.orderDate) - new Date(a.orderDate));

            tbody.innerHTML = '';

            if (ordersToRender.length === 0) {
                emptyState.classList.remove('hidden');
                return;
            }

            emptyState.classList.add('hidden');

            ordersToRender.forEach(order => {
                const orderDate = new Date(order.orderDate);
                const formattedDate = orderDate.toLocaleDateString('en-IN', { 
                    day: 'numeric', 
                    month: 'short', 
                    year: 'numeric' 
                });
                
                const itemsCount = order.itemsCount || (order.cart ? order.cart.reduce((sum, item) => sum + item.quantity, 0) : 0);
                const totalAmount = order.totals?.total || 0;
                const customerName = order.customer?.name || (order.customer ? `${order.customer.firstName || ''} ${order.customer.lastName || ''}`.trim() : 'N/A');
                const customerEmail = order.customer?.email || 'N/A';
                
                const statusColors = {
                    'confirmed': 'status-confirmed',
                    'processing': 'status-processing',
                    'shipped': 'status-shipped',
                    'delivered': 'status-delivered',
                    'cancelled': 'status-cancelled'
                };
                const statusClass = statusColors[order.status] || 'status-confirmed';
                const statusText = order.status ? order.status.charAt(0).toUpperCase() + order.status.slice(1) : 'Confirmed';

                // Payment method display
                const paymentMethod = order.payment?.method || 'N/A';
                const paymentStatus = order.payment?.status || 'pending';
                const paymentBadgeClass = paymentMethod === 'cod' ? 'payment-cod' : 'payment-online';
                const paymentStatusClass = paymentStatus === 'completed' ? 'payment-completed' : (paymentStatus === 'failed' ? 'payment-failed' : 'payment-pending');
                const paymentIcon = paymentMethod === 'cod' ? 'banknote' : 'credit-card';
                const paymentLabel = paymentMethod === 'cod' ? 'COD' : (paymentMethod === 'online' ? 'Online' : paymentMethod.toUpperCase());

                const row = document.createElement('tr');
                const currentOrderId = order.orderId; // Capture in closure
                row.className = 'hover:bg-gray-50 cursor-pointer transition-colors';
                row.addEventListener('click', function(e) {
                    // Don't open modal if clicking on action buttons
                    if (e.target.closest('.action-btn')) return;
                    viewOrderDetail(currentOrderId);
                });
                row.innerHTML = `
                    <td class="py-3 px-4 text-sm font-semibold text-gray-900">${order.orderId || 'N/A'}</td>
                    <td class="py-3 px-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900">${customerName}</p>
                            <p class="text-xs text-gray-500">${customerEmail}</p>
                        </div>
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-700">${formattedDate}</td>
                    <td class="py-3 px-4 text-sm text-gray-700">${itemsCount} item${itemsCount !== 1 ? 's' : ''}</td>
                    <td class="py-3 px-4 text-sm font-semibold text-gray-900">₹${totalAmount.toLocaleString('en-IN')}</td>
                    <td class="py-3 px-4">
                        <div class="flex flex-col gap-1">
                            <span class="payment-badge ${paymentBadgeClass}">
                                <i data-lucide="${paymentIcon}" class="w-3 h-3"></i>
                                ${paymentLabel}
                            </span>
                            <span class="text-xs ${paymentStatusClass}">${paymentStatus.charAt(0).toUpperCase() + paymentStatus.slice(1)}</span>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick="event.stopPropagation(); openStatusModal('${order.orderId}')" class="action-btn p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Update Status">
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </button>
                            <button onclick="event.stopPropagation(); deleteOrder('${order.orderId}')" class="action-btn p-2 text-teal-600 hover:bg-teal-50 rounded-lg transition-colors" title="Delete">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });

            lucide.createIcons();
        }

        // Update summary cards
        function updateSummary(filteredOrders = null) {
            const ordersToUse = filteredOrders || orders;
            
            const total = ordersToUse.length;
            const confirmed = ordersToUse.filter(o => o.status === 'confirmed').length;
            const processing = ordersToUse.filter(o => o.status === 'processing').length;
            const shipped = ordersToUse.filter(o => o.status === 'shipped').length;
            const delivered = ordersToUse.filter(o => o.status === 'delivered').length;

            document.getElementById('total-orders').textContent = total;
            document.getElementById('confirmed-orders').textContent = confirmed;
            document.getElementById('processing-orders').textContent = processing;
            document.getElementById('shipped-orders').textContent = shipped;
            document.getElementById('delivered-orders').textContent = delivered;
        }

        // Search and filter
        document.getElementById('search-input').addEventListener('input', filterOrders);
        document.getElementById('filter-status').addEventListener('change', filterOrders);
        document.getElementById('filter-date').addEventListener('change', filterOrders);

        function filterOrders() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const statusFilter = document.getElementById('filter-status').value;
            const dateFilter = document.getElementById('filter-date').value;

            // Client-side filtering
            let filtered = orders.filter(order => {
                const orderDateStr = order.orderDate ? new Date(order.orderDate).toISOString().split('T')[0] : '';
                const searchMatches = 
                    (order.orderId && order.orderId.toLowerCase().includes(searchTerm)) ||
                    (order.orderNumber && order.orderNumber.toLowerCase().includes(searchTerm)) ||
                    (order.customer?.firstName && order.customer.firstName.toLowerCase().includes(searchTerm)) ||
                    (order.customer?.lastName && order.customer.lastName.toLowerCase().includes(searchTerm)) ||
                    (order.customer?.name && order.customer.name.toLowerCase().includes(searchTerm)) ||
                    (order.customer?.email && order.customer.email.toLowerCase().includes(searchTerm));
                
                const matchesStatus = statusFilter === 'all' || order.status === statusFilter;
                const matchesDate = !dateFilter || orderDateStr === dateFilter;

                return searchMatches && matchesStatus && matchesDate;
            });

            renderOrders(filtered);
            updateSummary(filtered);
        }

        // Reset filters
        function resetFilters() {
            document.getElementById('search-input').value = '';
            document.getElementById('filter-status').value = 'all';
            document.getElementById('filter-date').value = '';
            loadOrders();
        }

        // Open Status Modal
        function openStatusModal(orderId) {
            const order = orders.find(o => o.orderId === orderId || o.orderNumber === orderId);
            
            if (!order) return;

            document.getElementById('status-order-id').value = orderId;
            document.getElementById('status-order-number').textContent = order.orderId || order.orderNumber || orderId;
            document.getElementById('status-current-status').textContent = order.status ? order.status.charAt(0).toUpperCase() + order.status.slice(1) : 'Confirmed';
            document.getElementById('new-status').value = order.status || 'confirmed';
            document.getElementById('new-payment-status').value = order.payment?.status || 'pending';
            document.getElementById('status-modal').classList.add('show');
            lucide.createIcons();
        }

        // Close Status Modal
        function closeStatusModal() {
            document.getElementById('status-modal').classList.remove('show');
        }

        // Update Order Status
        async function updateOrderStatus(event) {
            event.preventDefault();

            const orderId = document.getElementById('status-order-id').value;
            const newStatus = document.getElementById('new-status').value;
            const newPaymentStatus = document.getElementById('new-payment-status').value;

            try {
                const response = await fetch(`${API_BASE}/orders.php`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: orderId,
                        status: newStatus,
                        payment_status: newPaymentStatus
                    })
                });
                const result = await response.json();

                if (result.success) {
                    await loadOrders();
                    closeStatusModal();
                    await Tivora.alert('Order status updated successfully!', 'success');
                } else {
                    await Tivora.alert('Error: ' + (result.message || 'Failed to update order status'), 'error');
                }
            } catch (error) {
                console.error('Error updating order status:', error);
                await Tivora.alert('An error occurred while updating the order status. Please try again.', 'error');
            }
        }

        // Delete Order
        async function deleteOrder(orderId) {
            if (!await Tivora.confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
                return;
            }

            try {
                const response = await fetch(`${API_BASE}/orders.php?id=${orderId}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    await loadOrders();
                    await Tivora.alert('Order deleted (cancelled) successfully!', 'success');
                } else {
                    await Tivora.alert('Error: ' + (result.message || 'Failed to delete order'), 'error');
                }
            } catch (error) {
                console.error('Error deleting order:', error);
                await Tivora.alert('An error occurred while deleting the order. Please try again.', 'error');
            }
        }

        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('mobile-menu-overlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        // Current order being viewed in detail modal
        let currentDetailOrderId = null;

        // View Order Detail
        function viewOrderDetail(orderId) {
            if (!orderId) {
                console.error('No order ID provided');
                return;
            }
            
            const order = orders.find(o => o.orderId === orderId || o.orderNumber === orderId);
            if (!order) {
                console.error('Order not found:', orderId, 'Available orders:', orders.map(o => o.orderId));
                return;
            }

            currentDetailOrderId = orderId;

            // Populate header
            document.getElementById('detail-order-id').textContent = `Order #${order.orderId || order.orderNumber}`;
            const orderDate = new Date(order.orderDate);
            document.getElementById('detail-order-date').textContent = orderDate.toLocaleDateString('en-IN', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            // Status badge
            const statusEl = document.getElementById('detail-order-status');
            const status = order.status || 'confirmed';
            statusEl.textContent = status.charAt(0).toUpperCase() + status.slice(1);
            statusEl.className = 'status-badge';
            const statusClasses = {
                'confirmed': 'status-confirmed',
                'processing': 'status-processing',
                'shipped': 'status-shipped',
                'delivered': 'status-delivered',
                'cancelled': 'status-cancelled'
            };
            statusEl.classList.add(statusClasses[status] || 'status-confirmed');

            // Customer info
            const customerName = order.customer?.name || `${order.customer?.firstName || ''} ${order.customer?.lastName || ''}`.trim() || 'N/A';
            document.getElementById('detail-customer-name').textContent = customerName;
            document.getElementById('detail-customer-email').textContent = order.customer?.email || 'N/A';
            document.getElementById('detail-customer-phone').textContent = order.shipping_phone || order.customer?.phone || 'N/A';

            // Shipping address
            const shippingParts = [];
            if (order.shipping_address_line1) shippingParts.push(order.shipping_address_line1);
            if (order.shipping_address_line2) shippingParts.push(order.shipping_address_line2);
            if (order.shipping_city) shippingParts.push(order.shipping_city);
            if (order.shipping_state) shippingParts.push(order.shipping_state);
            if (order.shipping_pincode) shippingParts.push(order.shipping_pincode);
            if (order.shipping_country) shippingParts.push(order.shipping_country);
            document.getElementById('detail-shipping-address').textContent = shippingParts.length > 0 ? shippingParts.join(', ') : 'N/A';

            // Payment info
            const paymentMethod = order.payment?.method || 'N/A';
            const paymentMethodDisplay = paymentMethod === 'cod' ? 'Cash on Delivery (COD)' : 
                                         paymentMethod === 'online' ? 'Online Payment' : 
                                         paymentMethod.toUpperCase();
            document.getElementById('detail-payment-method').innerHTML = `
                <span class="payment-badge ${paymentMethod === 'cod' ? 'payment-cod' : 'payment-online'}">
                    <i data-lucide="${paymentMethod === 'cod' ? 'banknote' : 'credit-card'}" class="w-3 h-3"></i>
                    ${paymentMethodDisplay}
                </span>
            `;

            const paymentStatus = order.payment?.status || 'pending';
            const paymentStatusClass = paymentStatus === 'completed' ? 'text-green-600' : 
                                      (paymentStatus === 'failed' ? 'text-teal-600' : 'text-yellow-600');
            document.getElementById('detail-payment-status').innerHTML = `
                <span class="${paymentStatusClass} font-medium">${paymentStatus.charAt(0).toUpperCase() + paymentStatus.slice(1)}</span>
            `;

            // Order items
            const itemsContainer = document.getElementById('detail-items-container');
            itemsContainer.innerHTML = '';
            
            if (order.cart && order.cart.length > 0) {
                order.cart.forEach(item => {
                    const itemHtml = `
                        <div class="order-item">
                            <div class="order-item-image">
                                <i data-lucide="package" class="w-6 h-6 text-gray-400"></i>
                            </div>
                            <div class="order-item-details">
                                <p class="order-item-name">${item.name || 'Product'}</p>
                                <p class="order-item-sku">SKU: ${item.sku || 'N/A'}</p>
                            </div>
                            <div class="order-item-qty-price">
                                <p class="order-item-price">₹${(item.price || 0).toLocaleString('en-IN')}</p>
                                <p class="order-item-qty">Qty: ${item.quantity || 1}</p>
                            </div>
                        </div>
                    `;
                    itemsContainer.innerHTML += itemHtml;
                });
            } else {
                itemsContainer.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">No items found</p>';
            }

            // Totals
            const totals = order.totals || {};
            document.getElementById('detail-subtotal').textContent = `₹${(totals.subtotal || 0).toLocaleString('en-IN')}`;
            document.getElementById('detail-discount').textContent = totals.discount > 0 ? `-₹${totals.discount.toLocaleString('en-IN')}` : '₹0';
            document.getElementById('detail-tax').textContent = `₹${(totals.tax || 0).toLocaleString('en-IN')}`;
            document.getElementById('detail-shipping').textContent = totals.shipping > 0 ? `₹${totals.shipping.toLocaleString('en-IN')}` : 'Free';
            document.getElementById('detail-total').textContent = `₹${(totals.total || 0).toLocaleString('en-IN')}`;

            // Timeline
            renderTimeline(order);

            // Notes
            const notesSection = document.getElementById('notes-section');
            if (order.notes || order.internal_notes) {
                notesSection.style.display = 'block';
                document.getElementById('detail-notes').textContent = order.notes || order.internal_notes;
            } else {
                notesSection.style.display = 'none';
            }

            // Show modal
            document.getElementById('order-detail-modal').classList.add('show');
            lucide.createIcons();
        }

        // Render order timeline
        function renderTimeline(order) {
            const timeline = document.getElementById('detail-timeline');
            timeline.innerHTML = '';

            const statuses = [
                { key: 'confirmed', label: 'Confirmed', dateKey: 'confirmed_date' },
                { key: 'processing', label: 'Processing', dateKey: 'processing_date' },
                { key: 'shipped', label: 'Shipped', dateKey: 'shipped_date' },
                { key: 'delivered', label: 'Delivered', dateKey: 'delivered_date' }
            ];

            const currentStatusIndex = statuses.findIndex(s => s.key === order.status);
            const isCancelled = order.status === 'cancelled';

            statuses.forEach((statusItem, index) => {
                const isActive = !isCancelled && index <= currentStatusIndex;
                const date = order[statusItem.dateKey];
                const formattedDate = date ? new Date(date).toLocaleDateString('en-IN', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }) : (isActive && index === 0 ? new Date(order.orderDate).toLocaleDateString('en-IN', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }) : '-');

                const timelineItem = document.createElement('div');
                timelineItem.className = 'timeline-item';
                timelineItem.innerHTML = `
                    <div class="timeline-dot ${isActive ? 'active' : ''} ${isCancelled && index === currentStatusIndex ? 'cancelled' : ''}"></div>
                    <div class="timeline-content">
                        <p class="timeline-title">${statusItem.label}</p>
                        <p class="timeline-date">${formattedDate}</p>
                    </div>
                `;
                timeline.appendChild(timelineItem);
            });

            // Add cancelled status if applicable
            if (isCancelled) {
                const cancelDate = order.cancelled_date ? new Date(order.cancelled_date).toLocaleDateString('en-IN', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }) : '-';

                const cancelledItem = document.createElement('div');
                cancelledItem.className = 'timeline-item';
                cancelledItem.innerHTML = `
                    <div class="timeline-dot cancelled"></div>
                    <div class="timeline-content">
                        <p class="timeline-title text-teal-600">Cancelled</p>
                        <p class="timeline-date">${cancelDate}</p>
                    </div>
                `;
                timeline.appendChild(cancelledItem);
            }
        }

        // Close Order Detail Modal
        function closeOrderDetailModal(event) {
            if (event && event.target !== event.currentTarget) return;
            document.getElementById('order-detail-modal').classList.remove('show');
            currentDetailOrderId = null;
        }

        // Open status modal from detail view
        function openStatusModalFromDetail() {
            if (currentDetailOrderId) {
                closeOrderDetailModal();
                setTimeout(() => {
                    openStatusModal(currentDetailOrderId);
                }, 200);
            }
        }

        // Print order
        function printOrder() {
            const order = orders.find(o => o.orderId === currentDetailOrderId || o.orderNumber === currentDetailOrderId);
            if (!order) return;

            const customerName = order.customer?.name || `${order.customer?.firstName || ''} ${order.customer?.lastName || ''}`.trim() || 'N/A';
            const paymentMethod = order.payment?.method === 'cod' ? 'Cash on Delivery' : 'Online Payment';
            
            let itemsHtml = '';
            if (order.cart && order.cart.length > 0) {
                order.cart.forEach(item => {
                    itemsHtml += `
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #eee;">${item.name || 'Product'}</td>
                            <td style="padding: 8px; border-bottom: 1px solid #eee;">${item.sku || '-'}</td>
                            <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: center;">${item.quantity}</td>
                            <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: right;">₹${(item.price || 0).toLocaleString('en-IN')}</td>
                            <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: right;">₹${((item.price || 0) * (item.quantity || 1)).toLocaleString('en-IN')}</td>
                        </tr>
                    `;
                });
            }

            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Order ${order.orderId || order.orderNumber}</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; color: #333; }
                        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #14b8a6; padding-bottom: 20px; }
                        .header h1 { color: #14b8a6; margin: 0; }
                        .section { margin-bottom: 20px; }
                        .section-title { font-weight: bold; color: #14b8a6; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
                        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
                        .info-item { margin-bottom: 8px; }
                        .label { color: #666; font-size: 12px; }
                        .value { font-weight: 500; }
                        table { width: 100%; border-collapse: collapse; }
                        th { background: #f5f5f5; padding: 10px; text-align: left; }
                        .totals { margin-top: 20px; text-align: right; }
                        .totals-row { padding: 5px 0; }
                        .totals-total { font-size: 18px; font-weight: bold; color: #14b8a6; border-top: 2px solid #333; padding-top: 10px; }
                        @media print { body { padding: 0; } }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>TIVORA</h1>
                        <p>Order Invoice</p>
                    </div>
                    
                    <div class="info-grid">
                        <div class="section">
                            <div class="section-title">Order Details</div>
                            <div class="info-item">
                                <div class="label">Order ID</div>
                                <div class="value">${order.orderId || order.orderNumber}</div>
                            </div>
                            <div class="info-item">
                                <div class="label">Date</div>
                                <div class="value">${new Date(order.orderDate).toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric' })}</div>
                            </div>
                            <div class="info-item">
                                <div class="label">Status</div>
                                <div class="value">${(order.status || 'Confirmed').charAt(0).toUpperCase() + (order.status || 'Confirmed').slice(1)}</div>
                            </div>
                            <div class="info-item">
                                <div class="label">Payment Method</div>
                                <div class="value">${paymentMethod}</div>
                            </div>
                        </div>
                        
                        <div class="section">
                            <div class="section-title">Customer Details</div>
                            <div class="info-item">
                                <div class="label">Name</div>
                                <div class="value">${customerName}</div>
                            </div>
                            <div class="info-item">
                                <div class="label">Email</div>
                                <div class="value">${order.customer?.email || '-'}</div>
                            </div>
                            <div class="info-item">
                                <div class="label">Phone</div>
                                <div class="value">${order.shipping_phone || '-'}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="section">
                        <div class="section-title">Order Items</div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th style="text-align: center;">Qty</th>
                                    <th style="text-align: right;">Price</th>
                                    <th style="text-align: right;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsHtml}
                            </tbody>
                        </table>
                        
                        <div class="totals">
                            <div class="totals-row">
                                <span>Subtotal:</span>
                                <strong>₹${(order.totals?.subtotal || 0).toLocaleString('en-IN')}</strong>
                            </div>
                            ${order.totals?.discount > 0 ? `
                            <div class="totals-row">
                                <span>Discount:</span>
                                <strong>-₹${(order.totals?.discount || 0).toLocaleString('en-IN')}</strong>
                            </div>` : ''}
                            <div class="totals-row">
                                <span>Tax (GST):</span>
                                <strong>₹${(order.totals?.tax || 0).toLocaleString('en-IN')}</strong>
                            </div>
                            <div class="totals-row">
                                <span>Shipping:</span>
                                <strong>${order.totals?.shipping > 0 ? '₹' + order.totals.shipping.toLocaleString('en-IN') : 'Free'}</strong>
                            </div>
                            <div class="totals-row totals-total">
                                <span>Total:</span>
                                <strong>₹${(order.totals?.total || 0).toLocaleString('en-IN')}</strong>
                            </div>
                        </div>
                    </div>
                    
                    <scr` + `ipt>
                        window.onload = function() { window.print(); }
                    </scr` + `ipt>
                </body>
                </html>
            `);
            printWindow.document.close();
        }

        // Reinitialize icons
        setInterval(() => {
            lucide.createIcons();
        }, 1000);
    </script>
</body>
</html>
