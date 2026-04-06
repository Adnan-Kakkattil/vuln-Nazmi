<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Management | Tivora Admin</title>
    
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

        /* Transaction Type Badge */
        .transaction-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .transaction-credit {
            background: #dcfce7;
            color: #166534;
        }

        .transaction-debit {
            background: #fee2e2;
            color: #991b1b;
        }

        .transaction-purchase {
            background: #dbeafe;
            color: #1e40af;
        }

        /* Stock Section Styles */
        .stock-section {
            border-top: 2px solid #e5e7eb;
            padding-top: 20px;
            margin-top: 20px;
            display: none;
        }

        .stock-section.show {
            display: block;
        }

        .stock-section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 16px;
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
            <a href="finance.php" class="admin-sidebar-item active">
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
                <h1 class="text-2xl font-bold text-gray-900">Finance Management</h1>
            </div>
            <button onclick="openAddTransactionModal()" class="flex items-center gap-2 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add Transaction</span>
            </button>
        </header>

        <!-- Content -->
        <div class="p-6 lg:p-8">
            <!-- Financial Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="card">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                            <i data-lucide="arrow-down-circle" class="w-6 h-6 text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Credit</p>
                            <p class="text-2xl font-bold text-gray-900" id="total-credit">₹0</p>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-teal-100 flex items-center justify-center">
                            <i data-lucide="arrow-up-circle" class="w-6 h-6 text-teal-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Debit</p>
                            <p class="text-2xl font-bold text-gray-900" id="total-debit">₹0</p>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <i data-lucide="shopping-cart" class="w-6 h-6 text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Purchases</p>
                            <p class="text-2xl font-bold text-gray-900" id="total-purchase">₹0</p>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                            <i data-lucide="wallet" class="w-6 h-6 text-purple-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Balance</p>
                            <p class="text-2xl font-bold text-gray-900" id="balance">₹0</p>
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
                            <input type="text" id="search-input" placeholder="Search transactions by description, reference..." 
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                    <select id="filter-type" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="all">All Types</option>
                        <option value="credit">Credit</option>
                        <option value="debit">Debit</option>
                        <option value="purchase">Purchase</option>
                    </select>
                    <input type="date" id="filter-date" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                    <button onclick="resetFilters()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="card">
                <div class="overflow-x-auto">
                    <table class="w-full" id="transactions-table">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Date</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Type</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Description</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Reference</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Amount</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Category</th>
                                <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="transactions-tbody" class="divide-y divide-gray-200">
                            <!-- Transactions will be dynamically loaded here -->
                        </tbody>
                    </table>
                    <div id="empty-state" class="hidden py-12 text-center">
                        <i data-lucide="receipt-x" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                        <p class="text-gray-500 text-lg font-medium mb-2">No transactions found</p>
                        <p class="text-gray-400 text-sm">Add your first transaction to get started</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add/Edit Transaction Modal -->
    <div class="modal-overlay" id="transaction-modal">
        <div class="modal">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900" id="modal-title">Add Transaction</h2>
                <button onclick="closeTransactionModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form id="transaction-form" onsubmit="saveTransaction(event)">
                <input type="hidden" id="transaction-id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Type *</label>
                        <select id="transaction-type" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            onchange="updateTransactionType()">
                            <option value="">Select Type</option>
                            <option value="credit">Credit (Income)</option>
                            <option value="debit">Debit (Expense)</option>
                            <option value="purchase">Purchase</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                            <input type="date" id="transaction-date" required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount (₹) *</label>
                            <input type="number" id="transaction-amount" min="0" step="0.01" required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                        <input type="text" id="transaction-description" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            placeholder="Brief description of the transaction">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select id="transaction-category" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="">Select Category</option>
                                <option value="sales">Sales</option>
                                <option value="purchase">Purchase</option>
                                <option value="rent">Rent</option>
                                <option value="salary">Salary</option>
                                <option value="utilities">Utilities</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="marketing">Marketing</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                            <input type="text" id="transaction-reference" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                placeholder="Invoice/Receipt/Check No.">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea id="transaction-notes" rows="3" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            placeholder="Additional notes (optional)"></textarea>
                    </div>

                    <!-- Add to Stock Section (shown only for Purchase type) -->
                    <div id="stock-section" class="stock-section">
                        <div class="mb-4">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="add-to-stock" 
                                    class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-red-500"
                                    onchange="toggleStockFields()">
                                <span class="text-sm font-medium text-gray-700">Add this purchase to stock inventory</span>
                            </label>
                        </div>

                        <div id="stock-fields" style="display: none;">
                            <h3 class="stock-section-title">Stock Details</h3>
                            
                            <!-- Product Selection -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Existing Product (Optional)</label>
                                <select id="stock-product-select" onchange="handleProductChange()"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <option value="">-- New Product --</option>
                                    <!-- Products will be loaded dynamically -->
                                </select>
                                <p class="text-[10px] text-gray-500 mt-1">Select a product to update its stock, or leave as "New Product" to add a new one.</p>
                            </div>

                            <!-- Basic Stock Info -->
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
                                        <input type="text" id="stock-product-name" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">SKU *</label>
                                        <input type="text" id="stock-sku" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                                        <select id="stock-category" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                        <option value="">Select Category</option>
                                        <!-- Categories will be loaded dynamically -->
                                    </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                                        <input type="number" id="stock-quantity" min="0" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Brand</label>
                                        <input type="text" id="stock-brand" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                                        <input type="text" id="stock-model" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Product Description</label>
                                    <textarea id="stock-description" rows="2" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="submit" class="flex-1 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-medium">
                        Save Transaction
                    </button>
                    <button type="button" onclick="closeTransactionModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Transactions data loaded from API
        let transactions = [];
        let categories = [];
        let products = [];
        
        // API base URL
        const API_BASE = '/api/v1/admin';

        // Initialize Lucide Icons
        lucide.createIcons();

        // Set default date to today
        document.getElementById('transaction-date').valueAsDate = new Date();

        // Load transactions and categories on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadCategories();
            loadProducts();
            loadTransactions();
        });

        // Load categories from API
        async function loadCategories() {
            try {
                const response = await fetch(`${API_BASE}/categories.php`);
                const result = await response.json();
                
                if (result.success) {
                    categories = result.data;
                    
                    // Populate stock category dropdown
                    const stockCategorySelect = document.getElementById('stock-category');
                    stockCategorySelect.innerHTML = '<option value="">Select Category</option>';
                    
                    categories.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.id;
                        option.textContent = cat.name;
                        stockCategorySelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Failed to load categories:', error);
            }
        }

        // Load products from API
        async function loadProducts() {
            try {
                const response = await fetch(`${API_BASE}/products.php?limit=1000`);
                const result = await response.json();
                
                if (result.success) {
                    products = result.data;
                    
                    // Populate stock product select
                    const productSelect = document.getElementById('stock-product-select');
                    productSelect.innerHTML = '<option value="">-- New Product --</option>';
                    
                    products.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.id;
                        option.textContent = `${product.name} (${product.sku})`;
                        productSelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Failed to load products:', error);
            }
        }

        // Handle product selection change
        function handleProductChange() {
            const productId = document.getElementById('stock-product-select').value;
            
            if (productId) {
                const product = products.find(p => p.id == productId);
                if (product) {
                    document.getElementById('stock-product-name').value = product.name;
                    document.getElementById('stock-sku').value = product.sku;
                    document.getElementById('stock-category').value = product.category_id;
                    document.getElementById('stock-brand').value = product.specifications?.Brand || '';
                    document.getElementById('stock-model').value = product.specifications?.Model || '';
                    document.getElementById('stock-description').value = product.short_description || '';
                    
                    // Set as readonly/disabled to prevent accidental changes
                    document.getElementById('stock-product-name').readOnly = true;
                    document.getElementById('stock-sku').readOnly = true;
                    document.getElementById('stock-category').disabled = true;
                    
                    // Highlight that we're updating existing
                    document.getElementById('stock-product-name').classList.add('bg-gray-50');
                    document.getElementById('stock-sku').classList.add('bg-gray-50');
                }
            } else {
                // Reset for new product
                document.getElementById('stock-product-name').value = '';
                document.getElementById('stock-sku').value = '';
                document.getElementById('stock-category').value = '';
                document.getElementById('stock-brand').value = '';
                document.getElementById('stock-model').value = '';
                document.getElementById('stock-description').value = '';
                
                document.getElementById('stock-product-name').readOnly = false;
                document.getElementById('stock-sku').readOnly = false;
                document.getElementById('stock-category').disabled = false;
                
                document.getElementById('stock-product-name').classList.remove('bg-gray-50');
                document.getElementById('stock-sku').classList.remove('bg-gray-50');
            }
        }

        // Load transactions from API
        async function loadTransactions() {
            try {
                const response = await fetch(`${API_BASE}/transactions.php`);
                const result = await response.json();
                
                if (result.success) {
                    transactions = result.data || [];
                    renderTransactions();
                    updateSummary();
                } else {
                    console.error('Failed to load transactions:', result.message);
                }
            } catch (error) {
                console.error('Error loading transactions:', error);
            }
        }

        // Render transactions table
        function renderTransactions(filteredTransactions = null) {
            const tbody = document.getElementById('transactions-tbody');
            const emptyState = document.getElementById('empty-state');
            const transactionsToRender = filteredTransactions || transactions.sort((a, b) => new Date(b.date) - new Date(a.date));

            tbody.innerHTML = '';

            if (transactionsToRender.length === 0) {
                emptyState.classList.remove('hidden');
                return;
            }

            emptyState.classList.add('hidden');

            transactionsToRender.forEach(transaction => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                
                const date = new Date(transaction.date);
                const formattedDate = date.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
                
                const amountClass = transaction.type === 'credit' ? 'text-green-600' : 'text-teal-600';
                const amountPrefix = transaction.type === 'credit' ? '+' : '-';
                
                const actions = transaction.is_system 
                    ? `<a href="orders.php?search=${transaction.reference}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors inline-block" title="View Order">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                       </a>`
                    : `<div class="flex items-center justify-end gap-2 text-right">
                            <button onclick="editTransaction(${transaction.id})" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </button>
                            <button onclick="deleteTransaction(${transaction.id})" class="p-2 text-teal-600 hover:bg-teal-50 rounded-lg transition-colors" title="Delete">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>`;

                // Clean up previous template part
                const paymentBadge = transaction.is_system 
                    ? `<div class="mt-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase ${transaction.payment_status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'}">
                            ${transaction.payment_status}
                        </span>
                       </div>`
                    : '';

                row.innerHTML = `
                    <td class="py-3 px-4 text-sm text-gray-700">${formattedDate}</td>
                    <td class="py-3 px-4">
                        <span class="transaction-badge transaction-${transaction.type}">${transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1)}</span>
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-900 font-medium">
                        ${transaction.description}
                        ${paymentBadge}
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-600">
                        ${transaction.is_system 
                            ? `<span class="bg-gray-100 px-2 py-1 rounded text-xs font-mono">${transaction.reference}</span>`
                            : (transaction.reference || '-')}
                    </td>
                    <td class="py-3 px-4 text-sm font-semibold ${amountClass}">${amountPrefix}₹${transaction.amount.toLocaleString('en-IN')}</td>
                    <td class="py-3 px-4 text-sm text-gray-700 capitalize">${transaction.category || '-'}</td>
                    <td class="py-3 px-4 text-right">
                        ${actions}
                    </td>
                `;
                tbody.appendChild(row);
            });

            lucide.createIcons();
        }

        // Update summary cards
        function updateSummary() {
            // ONLY count credits that are COMPLETED (Money in hand)
            const totalCredit = transactions
                .filter(t => t.type === 'credit' && t.payment_status === 'completed')
                .reduce((sum, t) => sum + parseFloat(t.amount || 0), 0);
            
            const totalDebit = transactions
                .filter(t => t.type === 'debit')
                .reduce((sum, t) => sum + parseFloat(t.amount || 0), 0);
            
            const totalPurchase = transactions
                .filter(t => t.type === 'purchase')
                .reduce((sum, t) => sum + parseFloat(t.amount || 0), 0);
            
            const balance = totalCredit - totalDebit - totalPurchase;

            // Format numbers properly without leading zeros
            const formatCurrency = (amount) => {
                return parseFloat(amount).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            };

            document.getElementById('total-credit').textContent = `₹${formatCurrency(totalCredit)}`;
            document.getElementById('total-debit').textContent = `₹${formatCurrency(totalDebit)}`;
            document.getElementById('total-purchase').textContent = `₹${formatCurrency(totalPurchase)}`;
            
            const balanceElement = document.getElementById('balance');
            balanceElement.textContent = `₹${formatCurrency(Math.abs(balance))}`;
            balanceElement.className = balance >= 0 ? 'text-2xl font-bold text-green-600' : 'text-2xl font-bold text-teal-600';
        }

        // Search and filter
        document.getElementById('search-input').addEventListener('input', filterTransactions);
        document.getElementById('filter-type').addEventListener('change', filterTransactions);
        document.getElementById('filter-date').addEventListener('change', filterTransactions);

        function filterTransactions() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const typeFilter = document.getElementById('filter-type').value;
            const dateFilter = document.getElementById('filter-date').value;

            // Client-side filtering (can be enhanced with server-side filtering)
            let filtered = transactions.filter(transaction => {
                const matchesSearch = transaction.description.toLowerCase().includes(searchTerm) ||
                                    (transaction.reference && transaction.reference.toLowerCase().includes(searchTerm)) ||
                                    (transaction.category && transaction.category.toLowerCase().includes(searchTerm));
                const matchesType = typeFilter === 'all' || transaction.type === typeFilter;
                const matchesDate = !dateFilter || transaction.date === dateFilter;

                return matchesSearch && matchesType && matchesDate;
            });

            renderTransactions(filtered);
        }

        // Reset filters
        function resetFilters() {
            document.getElementById('search-input').value = '';
            document.getElementById('filter-type').value = 'all';
            document.getElementById('filter-date').value = '';
            renderTransactions();
        }

        // Add Transaction Modal
        function openAddTransactionModal() {
            document.getElementById('modal-title').textContent = 'Add Transaction';
            document.getElementById('transaction-form').reset();
            document.getElementById('transaction-id').value = '';
            document.getElementById('transaction-date').valueAsDate = new Date();
            document.getElementById('stock-section').classList.remove('show');
            document.getElementById('add-to-stock').checked = false;
            toggleStockFields();
            document.getElementById('stock-product-select').value = '';
            handleProductChange();
            document.getElementById('transaction-modal').classList.add('show');
            lucide.createIcons();
        }

        // Update transaction type UI
        function updateTransactionType() {
            const type = document.getElementById('transaction-type').value;
            const stockSection = document.getElementById('stock-section');
            
            if (type === 'purchase') {
                stockSection.classList.add('show');
            } else {
                stockSection.classList.remove('show');
                document.getElementById('add-to-stock').checked = false;
                toggleStockFields();
            }
        }

        // Toggle stock fields visibility
        function toggleStockFields() {
            const addToStock = document.getElementById('add-to-stock').checked;
            const stockFields = document.getElementById('stock-fields');
            
            if (addToStock) {
                stockFields.style.display = 'block';
                // Make required fields actually required
                document.getElementById('stock-product-name').required = true;
                document.getElementById('stock-sku').required = true;
                document.getElementById('stock-category').required = true;
                document.getElementById('stock-quantity').required = true;
            } else {
                stockFields.style.display = 'none';
                // Remove required attribute
                document.getElementById('stock-product-name').required = false;
                document.getElementById('stock-sku').required = false;
                document.getElementById('stock-category').required = false;
                document.getElementById('stock-quantity').required = false;
            }
        }

        // Edit Transaction
        function editTransaction(id) {
            const transaction = transactions.find(t => t.id === id);
            if (!transaction) return;

            document.getElementById('modal-title').textContent = 'Edit Transaction';
            document.getElementById('transaction-id').value = transaction.id;
            document.getElementById('transaction-type').value = transaction.type;
            document.getElementById('transaction-date').value = transaction.date;
            document.getElementById('transaction-amount').value = transaction.amount;
            document.getElementById('transaction-description').value = transaction.description;
            document.getElementById('transaction-category').value = transaction.category || '';
            document.getElementById('transaction-reference').value = transaction.reference || '';
            document.getElementById('transaction-notes').value = transaction.notes || '';
            
            // Update stock section visibility
            updateTransactionType();
            if (transaction.addToStock) {
                document.getElementById('add-to-stock').checked = true;
                toggleStockFields();
            }
            
            document.getElementById('transaction-modal').classList.add('show');
            lucide.createIcons();
        }

        // Close Transaction Modal
        function closeTransactionModal() {
            document.getElementById('transaction-modal').classList.remove('show');
        }

        // Save Transaction
        async function saveTransaction(event) {
            event.preventDefault();

            const id = document.getElementById('transaction-id').value;
            const transactionType = document.getElementById('transaction-type').value;
            const addToStock = document.getElementById('add-to-stock').checked;

            // Validate stock fields if add to stock is checked
            if (transactionType === 'purchase' && addToStock) {
                const productName = document.getElementById('stock-product-name').value;
                const sku = document.getElementById('stock-sku').value;
                const category = document.getElementById('stock-category').value;
                const quantity = document.getElementById('stock-quantity').value;

                if (!productName || !sku || !category || !quantity) {
                    await Tivora.alert('Please fill in all required stock fields (Product Name, SKU, Category, Quantity)', 'warning');
                    return;
                }
            }

            const transaction = {
                type: transactionType,
                date: document.getElementById('transaction-date').value,
                amount: parseFloat(document.getElementById('transaction-amount').value),
                description: document.getElementById('transaction-description').value,
                category: document.getElementById('transaction-category').value || null,
                reference: document.getElementById('transaction-reference').value || null,
                notes: document.getElementById('transaction-notes').value || null
            };

            try {
                // If adding to stock, create/update product via products API
                if (transactionType === 'purchase' && addToStock) {
                    const selectedProductId = document.getElementById('stock-product-select').value;
                    const qty = parseInt(document.getElementById('stock-quantity').value) || 0;
                    const totalAmount = parseFloat(document.getElementById('transaction-amount').value) || 0;
                    
                    const stockData = {
                        name: document.getElementById('stock-product-name').value,
                        sku: document.getElementById('stock-sku').value,
                        category_id: parseInt(document.getElementById('stock-category').value),
                        stock_quantity: qty,
                        price: qty > 0 ? (totalAmount / qty) : 0,
                        short_description: document.getElementById('stock-description').value || null,
                        specifications: {}
                    };
                    
                    const brand = document.getElementById('stock-brand').value.trim();
                    const model = document.getElementById('stock-model').value.trim();
                    if (brand) stockData.specifications['Brand'] = brand;
                    if (model) stockData.specifications['Model'] = model;

                    try {
                        if (selectedProductId) {
                            // Update existing product by ID
                            const product = products.find(p => p.id == selectedProductId);
                            if (product) {
                                const updateData = {
                                    id: product.id,
                                    stock_quantity: product.stock_quantity + stockData.stock_quantity
                                };
                                await fetch(`${API_BASE}/products.php`, {
                                    method: 'PUT',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify(updateData)
                                });
                            }
                        } else {
                            // First check if SKU exists even if "New Product" was selected
                            const checkResponse = await fetch(`${API_BASE}/products.php?search=${stockData.sku}`);
                            const checkResult = await checkResponse.json();
                            const existingProduct = checkResult.success && checkResult.data 
                                ? checkResult.data.find(p => p.sku === stockData.sku) 
                                : null;

                            if (existingProduct) {
                                // SKU exists, just update quantity
                                await fetch(`${API_BASE}/products.php`, {
                                    method: 'PUT',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({
                                        id: existingProduct.id,
                                        stock_quantity: existingProduct.stock_quantity + stockData.stock_quantity
                                    })
                                });
                            } else {
                                // Truly a new product
                                await fetch(`${API_BASE}/products.php`, {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify(stockData)
                                });
                            }
                        }
                        
                        // Refresh products list after update
                        await loadProducts();
                    } catch (stockError) {
                        console.error('Error handling stock:', stockError);
                    }
                }

                // Save transaction via API
                const url = `${API_BASE}/transactions.php`;
                const method = id ? 'PUT' : 'POST';
                
                if (id) {
                    transaction.id = parseInt(id);
                }

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(transaction)
                });

                const result = await response.json();

                if (result.success) {
                    // Reload transactions
                    await loadTransactions();
                    closeTransactionModal();
                    await Tivora.alert(id ? 'Transaction updated successfully!' : 'Transaction created successfully!', 'success');
                } else {
                    await Tivora.alert('Error: ' + (result.message || 'Failed to save transaction'), 'error');
                }
            } catch (error) {
                console.error('Error saving transaction:', error);
                await Tivora.alert('An error occurred while saving the transaction. Please try again.', 'error');
            }
        }

        // Delete Transaction
        async function deleteTransaction(id) {
            if (!await Tivora.confirm('Are you sure you want to delete this transaction?')) {
                return;
            }

            try {
                const response = await fetch(`${API_BASE}/transactions.php?id=${id}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    await loadTransactions();
                    await Tivora.alert('Transaction deleted successfully!', 'success');
                } else {
                    await Tivora.alert('Error: ' + (result.message || 'Failed to delete transaction'), 'error');
                }
            } catch (error) {
                console.error('Error deleting transaction:', error);
                await Tivora.alert('An error occurred while deleting the transaction. Please try again.', 'error');
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
