<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | Tivora Admin</title>
    
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

        /* Report Type Card */
        .report-type-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .report-type-card:hover {
            border-color: #14b8a6;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
        }

        .report-type-card.active {
            border-color: #14b8a6;
            background: #fef2f2;
        }

        /* Export Button */
        .export-btn {
            transition: all 0.2s ease;
        }

        .export-btn:hover {
            transform: translateY(-1px);
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
            <a href="orders.php" class="admin-sidebar-item">
                <i data-lucide="shopping-bag"></i>
                <span>Orders</span>
            </a>
            <a href="requests.php" class="admin-sidebar-item">
                <i data-lucide="inbox"></i>
                <span>B2B Requests</span>
            </a>
            <a href="report.php" class="admin-sidebar-item active">
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
                <h1 class="text-2xl font-bold text-gray-900">Reports</h1>
            </div>
            <button onclick="exportReport()" class="flex items-center gap-2 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors export-btn">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Export Report</span>
            </button>
        </header>

        <!-- Content -->
        <div class="p-6 lg:p-8">
            <!-- Report Type Selection -->
            <div class="card mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Select Report Type</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="report-type-card card p-4" onclick="selectReportType('sales')" id="report-sales">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                <i data-lucide="trending-up" class="w-5 h-5 text-green-600"></i>
                            </div>
                            <h3 class="font-semibold text-gray-900">Sales Report</h3>
                        </div>
                        <p class="text-sm text-gray-500">Revenue, orders, and sales analytics</p>
                    </div>
                    <div class="report-type-card card p-4" onclick="selectReportType('stock')" id="report-stock">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <i data-lucide="package" class="w-5 h-5 text-blue-600"></i>
                            </div>
                            <h3 class="font-semibold text-gray-900">Stock Report</h3>
                        </div>
                        <p class="text-sm text-gray-500">Inventory levels and stock movements</p>
                    </div>
                    <div class="report-type-card card p-4" onclick="selectReportType('finance')" id="report-finance">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                <i data-lucide="dollar-sign" class="w-5 h-5 text-purple-600"></i>
                            </div>
                            <h3 class="font-semibold text-gray-900">Financial Report</h3>
                        </div>
                        <p class="text-sm text-gray-500">Income, expenses, and profitability</p>
                    </div>
                    <div class="report-type-card card p-4" onclick="selectReportType('product')" id="report-product">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                                <i data-lucide="shopping-cart" class="w-5 h-5 text-yellow-600"></i>
                            </div>
                            <h3 class="font-semibold text-gray-900">Product Report</h3>
                        </div>
                        <p class="text-sm text-gray-500">Product performance and analysis</p>
                    </div>
                </div>
            </div>

            <!-- Date Range and Filters -->
            <div class="card mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Filter Options</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input type="date" id="date-from" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input type="date" id="date-to" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quick Select</label>
                        <select id="quick-date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" onchange="applyQuickDate()">
                            <option value="">Select Period</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="quarter">This Quarter</option>
                            <option value="year">This Year</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button onclick="generateReport()" class="w-full px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-medium">
                            Generate Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Report Content Area -->
            <div id="report-content">
                <!-- Default Message -->
                <div class="card">
                    <div class="py-12 text-center">
                        <i data-lucide="file-text" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                        <p class="text-gray-500 text-lg font-medium mb-2">Select a report type to view</p>
                        <p class="text-gray-400 text-sm">Choose from Sales, Stock, Financial, or Product reports above</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        let selectedReportType = '';
        let reportData = {};
        
        // API base URL
        const API_BASE = '/api/v1/admin';

        // Initialize Lucide Icons
        lucide.createIcons();

        // Set default date range (this month)
        document.addEventListener('DOMContentLoaded', () => {
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            
            document.getElementById('date-from').valueAsDate = firstDay;
            document.getElementById('date-to').valueAsDate = lastDay;
        });

        // Select Report Type
        function selectReportType(type) {
            selectedReportType = type;
            
            // Update active state
            document.querySelectorAll('.report-type-card').forEach(card => {
                card.classList.remove('active');
            });
            document.getElementById(`report-${type}`).classList.add('active');
        }

        // Apply Quick Date Selection
        function applyQuickDate() {
            const quickDate = document.getElementById('quick-date').value;
            const today = new Date();
            let fromDate, toDate;

            switch(quickDate) {
                case 'today':
                    fromDate = toDate = today;
                    break;
                case 'week':
                    const dayOfWeek = today.getDay();
                    fromDate = new Date(today);
                    fromDate.setDate(today.getDate() - dayOfWeek);
                    toDate = today;
                    break;
                case 'month':
                    fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    toDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    break;
                case 'quarter':
                    const quarter = Math.floor(today.getMonth() / 3);
                    fromDate = new Date(today.getFullYear(), quarter * 3, 1);
                    toDate = new Date(today.getFullYear(), (quarter + 1) * 3, 0);
                    break;
                case 'year':
                    fromDate = new Date(today.getFullYear(), 0, 1);
                    toDate = new Date(today.getFullYear(), 11, 31);
                    break;
                default:
                    return;
            }

            document.getElementById('date-from').valueAsDate = fromDate;
            document.getElementById('date-to').valueAsDate = toDate;
        }

        // Generate Report
        async function generateReport() {
            if (!selectedReportType) {
                await Tivora.alert('Please select a report type first', 'warning');
                return;
            }

            const dateFrom = document.getElementById('date-from').value;
            const dateTo = document.getElementById('date-to').value;

            if (!dateFrom || !dateTo) {
                await Tivora.alert('Please select a date range', 'warning');
                return;
            }

            try {
                // Fetch report data from API
                const url = `${API_BASE}/reports.php?type=${selectedReportType}&date_from=${dateFrom}&date_to=${dateTo}`;
                const response = await fetch(url);
                const result = await response.json();
                
                if (result.success) {
                    reportData = result.data;
                    
                    // Generate report based on type
                    switch(selectedReportType) {
                        case 'sales':
                            generateSalesReport(result.data);
                            break;
                        case 'stock':
                            generateStockReport(result.data);
                            break;
                        case 'finance':
                            generateFinancialReport(result.data);
                            break;
                        case 'product':
                            generateProductReport(result.data);
                            break;
                    }
                } else {
                    await Tivora.alert('Error: ' + (result.message || 'Failed to generate report'), 'error');
                }
            } catch (error) {
                console.error('Error generating report:', error);
                await Tivora.alert('An error occurred while generating the report. Please try again.', 'error');
            }
        }

        // Generate Sales Report
        function generateSalesReport(data) {
            const summary = data.summary || {};
            const transactions = data.transactions || [];
            
            const totalRevenue = summary.total_revenue || 0;
            const totalOrders = summary.total_orders || 0;
            const avgOrderValue = summary.avg_order_value || 0;

            const content = `
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="card">
                            <p class="text-sm text-gray-500 mb-2">Total Revenue</p>
                            <p class="text-3xl font-bold text-gray-900">₹${totalRevenue.toLocaleString('en-IN')}</p>
                        </div>
                        <div class="card">
                            <p class="text-sm text-gray-500 mb-2">Total Orders</p>
                            <p class="text-3xl font-bold text-gray-900">${totalOrders}</p>
                        </div>
                        <div class="card">
                            <p class="text-sm text-gray-500 mb-2">Avg Order Value</p>
                            <p class="text-3xl font-bold text-gray-900">₹${avgOrderValue.toLocaleString('en-IN')}</p>
                        </div>
                    </div>
                    <div class="card">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Sales Transactions</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Date</th>
                                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Description</th>
                                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Reference</th>
                                        <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    ${transactions.length > 0 ? transactions.map(t => `
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-3 px-4 text-sm text-gray-700">${new Date(t.date).toLocaleDateString('en-IN')}</td>
                                            <td class="py-3 px-4 text-sm text-gray-900">${t.description || 'Order'}</td>
                                            <td class="py-3 px-4 text-sm text-gray-600">${t.reference || '-'}</td>
                                            <td class="py-3 px-4 text-sm font-semibold text-green-600 text-right">₹${t.amount.toLocaleString('en-IN')}</td>
                                        </tr>
                                    `).join('') : '<tr><td colspan="4" class="py-8 text-center text-gray-500">No sales transactions found</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('report-content').innerHTML = content;
            lucide.createIcons();
        }

        // Generate Stock Report
        function generateStockReport(data) {
            const summary = data.summary || {};
            const products = data.products || [];
            
            const totalProducts = summary.total_products || 0;
            const inStock = summary.in_stock || 0;
            const lowStock = summary.low_stock || 0;
            const outOfStock = summary.out_of_stock || 0;
            const totalValue = summary.total_value || 0;

            const content = `
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="card">
                            <p class="text-sm text-gray-500 mb-2">Total Products</p>
                            <p class="text-3xl font-bold text-gray-900">${totalProducts}</p>
                        </div>
                        <div class="card">
                            <p class="text-sm text-gray-500 mb-2">In Stock</p>
                            <p class="text-3xl font-bold text-green-600">${inStock}</p>
                        </div>
                        <div class="card">
                            <p class="text-sm text-gray-500 mb-2">Low Stock</p>
                            <p class="text-3xl font-bold text-yellow-600">${lowStock}</p>
                        </div>
                        <div class="card">
                            <p class="text-sm text-gray-500 mb-2">Out of Stock</p>
                            <p class="text-3xl font-bold text-teal-600">${outOfStock}</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Inventory Value</h3>
                            <p class="text-2xl font-bold text-gray-900">₹${totalValue.toLocaleString('en-IN')}</p>
                        </div>
                    </div>
                    <div class="card">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Low Stock Items</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Product</th>
                                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">SKU</th>
                                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Stock</th>
                                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Price</th>
                                        <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Value</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    ${products.filter(p => p.status === 'low' || p.status === 'out').length > 0 ? 
                                        products.filter(p => p.status === 'low' || p.status === 'out').map(p => `
                                            <tr class="hover:bg-gray-50">
                                                <td class="py-3 px-4 text-sm text-gray-900">${p.name}</td>
                                                <td class="py-3 px-4 text-sm text-gray-600">${p.sku}</td>
                                                <td class="py-3 px-4 text-sm font-medium ${p.status === 'out' ? 'text-teal-600' : 'text-yellow-600'}">${p.quantity}</td>
                                                <td class="py-3 px-4 text-sm text-gray-700">₹${p.price.toLocaleString('en-IN')}</td>
                                                <td class="py-3 px-4 text-sm font-semibold text-gray-900 text-right">₹${(p.quantity * p.price).toLocaleString('en-IN')}</td>
                                            </tr>
                                        `).join('') : 
                                        '<tr><td colspan="5" class="py-8 text-center text-gray-500">No low stock items</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('report-content').innerHTML = content;
            lucide.createIcons();
        }

        // Generate Financial Report
        function generateFinancialReport(data) {
            const summary = data.summary || {};
            const transactions = data.transactions || [];
            
            const credits = summary.total_credit || 0;
            const debits = summary.total_debit || 0;
            const purchases = summary.total_purchases || 0;
            const netIncome = summary.net_income || 0;

            const content = `
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="card">
                            <p class="text-sm text-gray-500 mb-2">Total Credit</p>
                            <p class="text-3xl font-bold text-green-600">₹${credits.toLocaleString('en-IN')}</p>
                        </div>
                        <div class="card">
                            <p class="text-sm text-gray-500 mb-2">Total Debit</p>
                            <p class="text-3xl font-bold text-teal-600">₹${debits.toLocaleString('en-IN')}</p>
                        </div>
                        <div class="card">
                            <p class="text-sm text-gray-500 mb-2">Total Purchases</p>
                            <p class="text-3xl font-bold text-blue-600">₹${purchases.toLocaleString('en-IN')}</p>
                        </div>
                        <div class="card">
                            <p class="text-sm text-gray-500 mb-2">Net Income</p>
                            <p class="text-3xl font-bold ${netIncome >= 0 ? 'text-green-600' : 'text-teal-600'}">₹${Math.abs(netIncome).toLocaleString('en-IN')}</p>
                        </div>
                    </div>
                    <div class="card">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">All Transactions</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Date</th>
                                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Type</th>
                                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Description</th>
                                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Category</th>
                                        <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    ${transactions.length > 0 ? transactions.sort((a, b) => new Date(b.date) - new Date(a.date)).map(t => `
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-3 px-4 text-sm text-gray-700">${new Date(t.date).toLocaleDateString('en-IN')}</td>
                                            <td class="py-3 px-4"><span class="inline-flex px-2 py-1 rounded-full text-xs font-medium ${t.type === 'credit' ? 'bg-green-100 text-green-800' : t.type === 'debit' ? 'bg-teal-100 text-red-800' : 'bg-blue-100 text-blue-800'}">${t.type.charAt(0).toUpperCase() + t.type.slice(1)}</span></td>
                                            <td class="py-3 px-4 text-sm text-gray-900">${t.description}</td>
                                            <td class="py-3 px-4 text-sm text-gray-600 capitalize">${t.category || '-'}</td>
                                            <td class="py-3 px-4 text-sm font-semibold ${t.type === 'credit' ? 'text-green-600' : 'text-teal-600'} text-right">${t.type === 'credit' ? '+' : '-'}₹${t.amount.toLocaleString('en-IN')}</td>
                                        </tr>
                                    `).join('') : '<tr><td colspan="5" class="py-8 text-center text-gray-500">No transactions found</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('report-content').innerHTML = content;
            lucide.createIcons();
        }

        // Generate Product Report
        function generateProductReport(data) {
            const summary = data.summary || {};
            const products = data.products || [];
            
            const totalProducts = summary.total_products || products.length;

            const content = `
                <div class="space-y-6">
                    <div class="card">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Overview</h3>
                        <p class="text-gray-600 mb-4">Total Products: <span class="font-semibold text-gray-900">${totalProducts}</span></p>
                    </div>
                    <div class="card">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">All Products</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Product</th>
                                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">SKU</th>
                                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Category</th>
                                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Stock</th>
                                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Price</th>
                                        <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Total Value</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    ${products.length > 0 ? products.map(p => `
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-3 px-4 text-sm text-gray-900">${p.name}</td>
                                            <td class="py-3 px-4 text-sm text-gray-600">${p.sku}</td>
                                            <td class="py-3 px-4 text-sm text-gray-700 capitalize">${(p.category || 'uncategorized').replace(/-/g, ' ')}</td>
                                            <td class="py-3 px-4 text-sm font-medium text-gray-900">${p.quantity || 0}</td>
                                            <td class="py-3 px-4 text-sm text-gray-700">₹${p.price.toLocaleString('en-IN')}</td>
                                            <td class="py-3 px-4 text-sm font-semibold text-gray-900 text-right">₹${((p.quantity || 0) * p.price).toLocaleString('en-IN')}</td>
                                        </tr>
                                    `).join('') : '<tr><td colspan="6" class="py-8 text-center text-gray-500">No products found</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('report-content').innerHTML = content;
            lucide.createIcons();
        }

        // Export Report
        async function exportReport() {
            const reportContent = document.getElementById('report-content');
            if (!reportContent || !selectedReportType) {
                await Tivora.alert('Please generate a report first', 'warning');
                return;
            }

            // In a real application, this would export to PDF/Excel/CSV
            await Tivora.alert('Export functionality would be implemented here. This would generate a PDF, Excel, or CSV file based on the selected report.', 'info');
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
