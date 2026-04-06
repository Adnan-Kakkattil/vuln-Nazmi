<?php
/**
 * Reports Page Content
 * Generates various reports (sales, stock, finance, product)
 */
?>
<!-- Report Type Selection -->
<div class="stat-card mb-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Select Report Type</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="report-type-card stat-card p-4 cursor-pointer transition-all hover:border-teal-500 hover:-translate-y-0.5 hover:shadow-lg" onclick="selectReportType('sales')" id="report-sales">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-5 h-5 text-green-600"></i>
                </div>
                <h3 class="font-semibold text-gray-900">Sales Report</h3>
            </div>
            <p class="text-sm text-gray-500">Revenue, orders, and sales analytics</p>
        </div>
        <div class="report-type-card stat-card p-4 cursor-pointer transition-all hover:border-teal-500 hover:-translate-y-0.5 hover:shadow-lg" onclick="selectReportType('stock')" id="report-stock">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i data-lucide="package" class="w-5 h-5 text-blue-600"></i>
                </div>
                <h3 class="font-semibold text-gray-900">Stock Report</h3>
            </div>
            <p class="text-sm text-gray-500">Inventory levels and stock movements</p>
        </div>
        <div class="report-type-card stat-card p-4 cursor-pointer transition-all hover:border-teal-500 hover:-translate-y-0.5 hover:shadow-lg" onclick="selectReportType('finance')" id="report-finance">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                    <i data-lucide="dollar-sign" class="w-5 h-5 text-purple-600"></i>
                </div>
                <h3 class="font-semibold text-gray-900">Financial Report</h3>
            </div>
            <p class="text-sm text-gray-500">Income, expenses, and profitability</p>
        </div>
        <div class="report-type-card stat-card p-4 cursor-pointer transition-all hover:border-teal-500 hover:-translate-y-0.5 hover:shadow-lg" onclick="selectReportType('product')" id="report-product">
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
<div class="stat-card mb-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Filter Options</h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
            <input type="date" id="date-from" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
            <input type="date" id="date-to" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Quick Select</label>
            <select id="quick-date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500" onchange="applyQuickDate()">
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
    <div class="stat-card">
        <div class="py-12 text-center">
            <i data-lucide="file-text" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
            <p class="text-gray-500 text-lg font-medium mb-2">Select a report type to view</p>
            <p class="text-gray-400 text-sm">Choose from Sales, Stock, Financial, or Product reports above</p>
        </div>
    </div>
</div>

<style>
    .report-type-card.active {
        border-color: #14b8a6;
        background: #f0fdfa;
    }
</style>

<script>
    let selectedReportType = '';
    let reportData = {};
    
    // API base URL
    const API_BASE = '/api/v1/admin';

    // Set default date range (this month)
    document.addEventListener('DOMContentLoaded', () => {
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        
        const dateFromEl = document.getElementById('date-from');
        const dateToEl = document.getElementById('date-to');
        
        if (dateFromEl) dateFromEl.valueAsDate = firstDay;
        if (dateToEl) dateToEl.valueAsDate = lastDay;
        
        lucide.createIcons();
    });

    // Select Report Type
    function selectReportType(type) {
        selectedReportType = type;
        
        // Update active state
        document.querySelectorAll('.report-type-card').forEach(card => {
            card.classList.remove('active');
        });
        const selectedCard = document.getElementById(`report-${type}`);
        if (selectedCard) selectedCard.classList.add('active');
    }

    // Apply Quick Date Selection
    function applyQuickDate() {
        const quickDate = document.getElementById('quick-date')?.value;
        if (!quickDate) return;
        
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

        const dateFromEl = document.getElementById('date-from');
        const dateToEl = document.getElementById('date-to');
        
        if (dateFromEl) dateFromEl.valueAsDate = fromDate;
        if (dateToEl) dateToEl.valueAsDate = toDate;
    }

    // Generate Report
    async function generateReport() {
        if (!selectedReportType) {
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Please select a report type first', 'warning');
            } else {
                alert('Please select a report type first');
            }
            return;
        }

        const dateFromEl = document.getElementById('date-from');
        const dateToEl = document.getElementById('date-to');
        
        const dateFrom = dateFromEl?.value;
        const dateTo = dateToEl?.value;

        if (!dateFrom || !dateTo) {
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Please select a date range', 'warning');
            } else {
                alert('Please select a date range');
            }
            return;
        }

        try {
            // Fetch report data from API
            const url = `${API_BASE}/reports.php?type=${selectedReportType}&date_from=${dateFrom}&date_to=${dateTo}`;
            const response = await fetch(url, {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch report');
            }
            
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
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Error: ' + (result.message || 'Failed to generate report'), 'error');
                } else {
                    alert('Error: ' + (result.message || 'Failed to generate report'));
                }
            }
        } catch (error) {
            console.error('Error generating report:', error);
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('An error occurred while generating the report. Please try again.', 'error');
            } else {
                alert('An error occurred while generating the report. Please try again.');
            }
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
                    <div class="stat-card">
                        <p class="text-sm text-gray-500 mb-2">Total Revenue</p>
                        <p class="text-3xl font-bold text-gray-900">₹${totalRevenue.toLocaleString('en-IN')}</p>
                    </div>
                    <div class="stat-card">
                        <p class="text-sm text-gray-500 mb-2">Total Orders</p>
                        <p class="text-3xl font-bold text-gray-900">${totalOrders}</p>
                    </div>
                    <div class="stat-card">
                        <p class="text-sm text-gray-500 mb-2">Avg Order Value</p>
                        <p class="text-3xl font-bold text-gray-900">₹${avgOrderValue.toLocaleString('en-IN')}</p>
                    </div>
                </div>
                <div class="stat-card">
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
                                        <td class="py-3 px-4 text-sm text-gray-900">${escapeHtml(t.description || 'Order')}</td>
                                        <td class="py-3 px-4 text-sm text-gray-600">${escapeHtml(t.reference || '-')}</td>
                                        <td class="py-3 px-4 text-sm font-semibold text-green-600 text-right">₹${parseFloat(t.amount || 0).toLocaleString('en-IN')}</td>
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
                    <div class="stat-card">
                        <p class="text-sm text-gray-500 mb-2">Total Products</p>
                        <p class="text-3xl font-bold text-gray-900">${totalProducts}</p>
                    </div>
                    <div class="stat-card">
                        <p class="text-sm text-gray-500 mb-2">In Stock</p>
                        <p class="text-3xl font-bold text-green-600">${inStock}</p>
                    </div>
                    <div class="stat-card">
                        <p class="text-sm text-gray-500 mb-2">Low Stock</p>
                        <p class="text-3xl font-bold text-yellow-600">${lowStock}</p>
                    </div>
                    <div class="stat-card">
                        <p class="text-sm text-gray-500 mb-2">Out of Stock</p>
                        <p class="text-3xl font-bold text-red-600">${outOfStock}</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Inventory Value</h3>
                        <p class="text-2xl font-bold text-gray-900">₹${totalValue.toLocaleString('en-IN')}</p>
                    </div>
                </div>
                <div class="stat-card">
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
                                            <td class="py-3 px-4 text-sm text-gray-900">${escapeHtml(p.name)}</td>
                                            <td class="py-3 px-4 text-sm text-gray-600">${escapeHtml(p.sku)}</td>
                                            <td class="py-3 px-4 text-sm font-medium ${p.status === 'out' ? 'text-red-600' : 'text-yellow-600'}">${p.quantity || 0}</td>
                                            <td class="py-3 px-4 text-sm text-gray-700">₹${parseFloat(p.price || 0).toLocaleString('en-IN')}</td>
                                            <td class="py-3 px-4 text-sm font-semibold text-gray-900 text-right">₹${((p.quantity || 0) * (p.price || 0)).toLocaleString('en-IN')}</td>
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
                    <div class="stat-card">
                        <p class="text-sm text-gray-500 mb-2">Total Credit</p>
                        <p class="text-3xl font-bold text-green-600">₹${credits.toLocaleString('en-IN')}</p>
                    </div>
                    <div class="stat-card">
                        <p class="text-sm text-gray-500 mb-2">Total Debit</p>
                        <p class="text-3xl font-bold text-red-600">₹${debits.toLocaleString('en-IN')}</p>
                    </div>
                    <div class="stat-card">
                        <p class="text-sm text-gray-500 mb-2">Total Purchases</p>
                        <p class="text-3xl font-bold text-blue-600">₹${purchases.toLocaleString('en-IN')}</p>
                    </div>
                    <div class="stat-card">
                        <p class="text-sm text-gray-500 mb-2">Net Income</p>
                        <p class="text-3xl font-bold ${netIncome >= 0 ? 'text-green-600' : 'text-red-600'}">₹${Math.abs(netIncome).toLocaleString('en-IN')}</p>
                    </div>
                </div>
                <div class="stat-card">
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
                                        <td class="py-3 px-4"><span class="inline-flex px-2 py-1 rounded-full text-xs font-medium ${t.type === 'credit' ? 'bg-green-100 text-green-800' : t.type === 'debit' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'}">${(t.type || '').charAt(0).toUpperCase() + (t.type || '').slice(1)}</span></td>
                                        <td class="py-3 px-4 text-sm text-gray-900">${escapeHtml(t.description || '')}</td>
                                        <td class="py-3 px-4 text-sm text-gray-600 capitalize">${escapeHtml((t.category || '-').replace(/-/g, ' '))}</td>
                                        <td class="py-3 px-4 text-sm font-semibold ${t.type === 'credit' ? 'text-green-600' : 'text-red-600'} text-right">${t.type === 'credit' ? '+' : '-'}₹${parseFloat(t.amount || 0).toLocaleString('en-IN')}</td>
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
                <div class="stat-card">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Overview</h3>
                    <p class="text-gray-600 mb-4">Total Products: <span class="font-semibold text-gray-900">${totalProducts}</span></p>
                </div>
                <div class="stat-card">
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
                                        <td class="py-3 px-4 text-sm text-gray-900">${escapeHtml(p.name)}</td>
                                        <td class="py-3 px-4 text-sm text-gray-600">${escapeHtml(p.sku)}</td>
                                        <td class="py-3 px-4 text-sm text-gray-700 capitalize">${escapeHtml((p.category || 'uncategorized').replace(/-/g, ' '))}</td>
                                        <td class="py-3 px-4 text-sm font-medium text-gray-900">${p.quantity || 0}</td>
                                        <td class="py-3 px-4 text-sm text-gray-700">₹${parseFloat(p.price || 0).toLocaleString('en-IN')}</td>
                                        <td class="py-3 px-4 text-sm font-semibold text-gray-900 text-right">₹${((p.quantity || 0) * (p.price || 0)).toLocaleString('en-IN')}</td>
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
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Please generate a report first', 'warning');
            } else {
                alert('Please generate a report first');
            }
            return;
        }

        // In a real application, this would export to PDF/Excel/CSV
        if (typeof Tivora !== 'undefined' && Tivora.alert) {
            await Tivora.alert('Export functionality would be implemented here. This would generate a PDF, Excel, or CSV file based on the selected report.', 'info');
        } else {
            alert('Export functionality would be implemented here. This would generate a PDF, Excel, or CSV file based on the selected report.');
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Make exportReport globally accessible
    window.exportReport = exportReport;
</script>
