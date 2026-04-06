<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discount Coupons | BLine Boutique Admin</title>
    
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

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
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

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-expired {
            background: #f3f4f6;
            color: #6b7280;
        }

        /* Discount Badge */
        .discount-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            background: #fef2f2;
            color: #14b8a6;
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
                <img src="../Tivora_wordmark_red.avif" alt="BLine Boutique" class="h-8 w-auto">
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
            <a href="coupons.php" class="admin-sidebar-item active">
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
                <h1 class="text-2xl font-bold text-gray-900">Discount Coupons</h1>
            </div>
            <button onclick="openAddCouponModal()" class="flex items-center gap-2 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add Coupon</span>
            </button>
        </header>

        <!-- Content -->
        <div class="p-6 lg:p-8">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="card">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <i data-lucide="ticket" class="w-6 h-6 text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Coupons</p>
                            <p class="text-2xl font-bold text-gray-900" id="total-coupons">0</p>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                            <i data-lucide="check-circle" class="w-6 h-6 text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Active Coupons</p>
                            <p class="text-2xl font-bold text-gray-900" id="active-coupons">0</p>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-teal-100 flex items-center justify-center">
                            <i data-lucide="x-circle" class="w-6 h-6 text-teal-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Expired Coupons</p>
                            <p class="text-2xl font-bold text-gray-900" id="expired-coupons">0</p>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                            <i data-lucide="users" class="w-6 h-6 text-purple-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Usage</p>
                            <p class="text-2xl font-bold text-gray-900" id="total-usage">0</p>
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
                            <input type="text" id="search-input" placeholder="Search coupons by code or description..." 
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                    <select id="filter-status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="expired">Expired</option>
                    </select>
                    <button onclick="resetFilters()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Coupons Table -->
            <div class="card">
                <div class="overflow-x-auto">
                    <table class="w-full" id="coupons-table">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Coupon Code</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Discount</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Min. Purchase</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Usage</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Expiry Date</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                                <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="coupons-tbody" class="divide-y divide-gray-200">
                            <!-- Coupons will be dynamically loaded here -->
                        </tbody>
                    </table>
                    <div id="empty-state" class="hidden py-12 text-center">
                        <i data-lucide="ticket-x" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                        <p class="text-gray-500 text-lg font-medium mb-2">No coupons found</p>
                        <p class="text-gray-400 text-sm">Add your first coupon to get started</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add/Edit Coupon Modal -->
    <div class="modal-overlay" id="coupon-modal">
        <div class="modal">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900" id="modal-title">Add Coupon</h2>
                <button onclick="closeCouponModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form id="coupon-form" onsubmit="saveCoupon(event)">
                <input type="hidden" id="coupon-id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Coupon Code *</label>
                        <input type="text" id="coupon-code" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            placeholder="e.g., SAVE20, WELCOME10" style="text-transform: uppercase;">
                        <p class="text-xs text-gray-500 mt-1">Enter a unique coupon code (uppercase)</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Discount Type *</label>
                            <select id="discount-type" required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                onchange="updateDiscountType()">
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (₹)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Discount Value *</label>
                            <input type="number" id="discount-value" min="0" step="0.01" required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                placeholder="Enter amount or percentage">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Purchase (₹)</label>
                            <input type="number" id="min-purchase" min="0" step="0.01" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                placeholder="0 for no minimum">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Usage Limit</label>
                            <input type="number" id="usage-limit" min="0" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                placeholder="0 for unlimited">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <input type="date" id="start-date" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date *</label>
                            <input type="date" id="expiry-date" required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="coupon-description" rows="2" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            placeholder="Brief description of the coupon"></textarea>
                    </div>
                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" id="coupon-active" checked 
                                class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-red-500">
                            <span class="text-sm font-medium text-gray-700">Active</span>
                        </label>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="submit" class="flex-1 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-medium">
                        Save Coupon
                    </button>
                    <button type="button" onclick="closeCouponModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Coupons data loaded from API
        let coupons = [];
        
        // API base URL
        const API_BASE = '/api/v1/admin';

        // Initialize Lucide Icons
        lucide.createIcons();

        // Load coupons on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadCoupons();
        });

        // Load coupons from API
        async function loadCoupons() {
            try {
                const response = await fetch(`${API_BASE}/coupons.php`);
                const result = await response.json();
                
                if (result.success) {
                    coupons = result.data || [];
                    renderCoupons();
                    updateSummary();
                } else {
                    console.error('Failed to load coupons:', result.message);
                }
            } catch (error) {
                console.error('Error loading coupons:', error);
            }
        }

        // Render coupons table
        function renderCoupons(filteredCoupons = null) {
            const tbody = document.getElementById('coupons-tbody');
            const emptyState = document.getElementById('empty-state');
            const couponsToRender = filteredCoupons || coupons;

            tbody.innerHTML = '';

            if (couponsToRender.length === 0) {
                emptyState.classList.remove('hidden');
                return;
            }

            emptyState.classList.add('hidden');

            couponsToRender.forEach(coupon => {
                const status = getCouponStatus(coupon);
                const discountText = coupon.type === 'percentage' 
                    ? `${coupon.value}%` 
                    : `₹${coupon.value}`;
                
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                row.innerHTML = `
                    <td class="py-3 px-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">${coupon.code}</p>
                            <p class="text-xs text-gray-500">${coupon.description || 'No description'}</p>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="discount-badge">${discountText}</span>
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-700">
                        ${coupon.minPurchase > 0 ? `₹${coupon.minPurchase.toLocaleString('en-IN')}` : 'No minimum'}
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-700">
                        ${coupon.usedCount || 0}${coupon.usageLimit > 0 ? ` / ${coupon.usageLimit}` : ' / ∞'}
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-700">
                        ${new Date(coupon.expiryDate).toLocaleDateString('en-IN')}
                    </td>
                    <td class="py-3 px-4">
                        <span class="status-badge status-${status}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick="copyCouponCode('${coupon.code}')" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Copy Code">
                                <i data-lucide="copy" class="w-4 h-4"></i>
                            </button>
                            <button onclick="editCoupon(${coupon.id})" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </button>
                            <button onclick="deleteCoupon(${coupon.id})" class="p-2 text-teal-600 hover:bg-teal-50 rounded-lg transition-colors" title="Delete">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });

            lucide.createIcons();
        }

        // Get coupon status
        function getCouponStatus(coupon) {
            const today = new Date();
            const expiryDate = new Date(coupon.expiryDate);
            const startDate = coupon.startDate ? new Date(coupon.startDate) : null;

            if (!coupon.active) return 'inactive';
            if (expiryDate < today) return 'expired';
            if (startDate && startDate > today) return 'inactive';
            if (coupon.usageLimit > 0 && coupon.usedCount >= coupon.usageLimit) return 'expired';
            
            return 'active';
        }

        // Update summary cards
        function updateSummary() {
            const total = coupons.length;
            const active = coupons.filter(c => getCouponStatus(c) === 'active').length;
            const expired = coupons.filter(c => getCouponStatus(c) === 'expired').length;
            const totalUsage = coupons.reduce((sum, c) => sum + (c.usedCount || 0), 0);

            document.getElementById('total-coupons').textContent = total;
            document.getElementById('active-coupons').textContent = active;
            document.getElementById('expired-coupons').textContent = expired;
            document.getElementById('total-usage').textContent = totalUsage;
        }

        // Search and filter
        document.getElementById('search-input').addEventListener('input', filterCoupons);
        document.getElementById('filter-status').addEventListener('change', filterCoupons);

        function filterCoupons() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const statusFilter = document.getElementById('filter-status').value;

            let filtered = coupons.filter(coupon => {
                const matchesSearch = coupon.code.toLowerCase().includes(searchTerm) ||
                                    (coupon.description && coupon.description.toLowerCase().includes(searchTerm));
                const matchesStatus = statusFilter === 'all' || getCouponStatus(coupon) === statusFilter;

                return matchesSearch && matchesStatus;
            });

            renderCoupons(filtered);
        }

        // Reset filters
        function resetFilters() {
            document.getElementById('search-input').value = '';
            document.getElementById('filter-status').value = 'all';
            renderCoupons();
        }

        // Update discount type UI
        function updateDiscountType() {
            const type = document.getElementById('discount-type').value;
            const valueInput = document.getElementById('discount-value');
            if (type === 'percentage') {
                valueInput.setAttribute('max', '100');
                valueInput.setAttribute('placeholder', '0-100');
            } else {
                valueInput.removeAttribute('max');
                valueInput.setAttribute('placeholder', 'Enter amount in ₹');
            }
        }

        // Add Coupon Modal
        function openAddCouponModal() {
            document.getElementById('modal-title').textContent = 'Add Coupon';
            document.getElementById('coupon-form').reset();
            document.getElementById('coupon-id').value = '';
            document.getElementById('coupon-active').checked = true;
            document.getElementById('expiry-date').valueAsDate = new Date(new Date().setMonth(new Date().getMonth() + 1));
            document.getElementById('coupon-modal').classList.add('show');
            lucide.createIcons();
        }

        // Edit Coupon
        function editCoupon(id) {
            const coupon = coupons.find(c => c.id === id);
            if (!coupon) return;

            document.getElementById('modal-title').textContent = 'Edit Coupon';
            document.getElementById('coupon-id').value = coupon.id;
            document.getElementById('coupon-code').value = coupon.code;
            document.getElementById('discount-type').value = coupon.type;
            document.getElementById('discount-value').value = coupon.value;
            document.getElementById('min-purchase').value = coupon.minPurchase || '';
            document.getElementById('usage-limit').value = coupon.usageLimit || '';
            document.getElementById('start-date').value = coupon.startDate || '';
            document.getElementById('expiry-date').value = coupon.expiryDate;
            document.getElementById('coupon-description').value = coupon.description || '';
            document.getElementById('coupon-active').checked = coupon.active;
            document.getElementById('coupon-modal').classList.add('show');
            updateDiscountType();
            lucide.createIcons();
        }

        // Close Coupon Modal
        function closeCouponModal() {
            document.getElementById('coupon-modal').classList.remove('show');
        }

        // Save Coupon
        async function saveCoupon(event) {
            event.preventDefault();

            const id = document.getElementById('coupon-id').value;
            const code = document.getElementById('coupon-code').value.toUpperCase().trim();
            
            if (!code) {
                await Tivora.alert('Coupon code is required', 'warning');
                return;
            }

            const coupon = {
                code: code,
                name: code, // Use code as name if not provided
                type: document.getElementById('discount-type').value,
                value: parseFloat(document.getElementById('discount-value').value),
                minPurchase: parseFloat(document.getElementById('min-purchase').value) || 0,
                usageLimit: parseInt(document.getElementById('usage-limit').value) || 0,
                startDate: document.getElementById('start-date').value || null,
                expiryDate: document.getElementById('expiry-date').value,
                description: document.getElementById('coupon-description').value || null,
                active: document.getElementById('coupon-active').checked
            };

            // Validate discount value
            if (coupon.type === 'percentage' && coupon.value > 100) {
                await Tivora.alert('Percentage discount cannot exceed 100%', 'warning');
                return;
            }

            try {
                const url = `${API_BASE}/coupons.php`;
                const method = id ? 'PUT' : 'POST';
                
                if (id) {
                    coupon.id = parseInt(id);
                }

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(coupon)
                });

                const result = await response.json();

                if (result.success) {
                    // Reload coupons
                    await loadCoupons();
                    closeCouponModal();
                    await Tivora.alert(id ? 'Coupon updated successfully!' : 'Coupon created successfully!', 'success');
                } else {
                    await Tivora.alert('Error: ' + (result.message || 'Failed to save coupon'), 'error');
                }
            } catch (error) {
                console.error('Error saving coupon:', error);
                await Tivora.alert('An error occurred while saving the coupon. Please try again.', 'error');
            }
        }

        // Delete Coupon
        async function deleteCoupon(id) {
            if (!await Tivora.confirm('Are you sure you want to delete this coupon?')) {
                return;
            }

            try {
                const response = await fetch(`${API_BASE}/coupons.php?id=${id}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    await loadCoupons();
                    await Tivora.alert('Coupon deleted successfully!', 'success');
                } else {
                    await Tivora.alert('Error: ' + (result.message || 'Failed to delete coupon'), 'error');
                }
            } catch (error) {
                console.error('Error deleting coupon:', error);
                await Tivora.alert('An error occurred while deleting the coupon. Please try again.', 'error');
            }
        }

        // Copy coupon code to clipboard
        async function copyCouponCode(code) {
            try {
                await navigator.clipboard.writeText(code);
                await Tivora.alert(`Coupon code "${code}" copied to clipboard!`, 'success');
            } catch (err) {
                // Fallback for older browsers
                const textarea = document.createElement('textarea');
                textarea.value = code;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                await Tivora.alert(`Coupon code "${code}" copied to clipboard!`, 'success');
            }
        }

        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('mobile-menu-overlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        // Reinitialize icons
        setInterval(() => {
            lucide.createIcons();
        }, 1000);
    </script>
</body>
</html>
