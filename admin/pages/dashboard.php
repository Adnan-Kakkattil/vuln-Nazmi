<?php
/**
 * Dashboard Page Content
 * Main dashboard with stats, charts, and recent orders
 */
?>
<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Revenue Card -->
    <div class="stat-card">
        <div class="flex items-center justify-between mb-4">
            <div class="stat-icon bg-teal-100">
                <i data-lucide="dollar-sign" class="w-6 h-6 text-teal-600"></i>
            </div>
            <span class="text-sm text-green-600 font-medium flex items-center gap-1">
                <i data-lucide="trending-up" class="w-4 h-4"></i>
                +12.5%
            </span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-1" id="total-revenue">₹0</h3>
        <p class="text-sm text-gray-500">Total Revenue</p>
    </div>

    <!-- Total Orders Card -->
    <div class="stat-card">
        <div class="flex items-center justify-between mb-4">
            <div class="stat-icon bg-blue-100">
                <i data-lucide="shopping-cart" class="w-6 h-6 text-blue-600"></i>
            </div>
            <span class="text-sm text-green-600 font-medium flex items-center gap-1">
                <i data-lucide="trending-up" class="w-4 h-4"></i>
                +8.2%
            </span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-1" id="total-orders">0</h3>
        <p class="text-sm text-gray-500">Total Orders</p>
    </div>

    <!-- Products in Stock Card -->
    <div class="stat-card">
        <div class="flex items-center justify-between mb-4">
            <div class="stat-icon bg-yellow-100">
                <i data-lucide="package" class="w-6 h-6 text-yellow-600"></i>
            </div>
            <span class="text-sm text-gray-500 font-medium">In Stock</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-1" id="products-in-stock">0</h3>
        <p class="text-sm text-gray-500">Products Available</p>
    </div>

    <!-- Low Stock Alert Card -->
    <div class="stat-card">
        <div class="flex items-center justify-between mb-4">
            <div class="stat-icon bg-orange-100">
                <i data-lucide="alert-circle" class="w-6 h-6 text-orange-600"></i>
            </div>
            <span class="text-sm text-teal-600 font-medium flex items-center gap-1">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                Attention
            </span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-1" id="low-stock-items">0</h3>
        <p class="text-sm text-gray-500">Low Stock Items</p>
    </div>
</div>

<!-- Charts and Activity Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Revenue Chart Card -->
    <div class="lg:col-span-2 stat-card">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Revenue Overview</h2>
            <select id="chart-period" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500" onchange="updateChart()">
                <option value="7">Last 7 days</option>
                <option value="30" selected>Last 30 days</option>
                <option value="90">Last 3 months</option>
                <option value="365">Last year</option>
            </select>
        </div>
        <div class="h-64">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Recent Activity Card -->
    <div class="stat-card">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Recent Activity</h2>
        <div id="recent-activity-list" class="space-y-4">
            <div class="text-center py-4 text-gray-500 text-sm">Loading activity...</div>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="stat-card">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Recent Orders</h2>
        <a href="dashboard.php?page=orders" class="text-sm text-teal-600 hover:text-teal-700 font-medium flex items-center gap-1">
            View All
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Order ID</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Customer</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Product</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Amount</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Date</th>
                </tr>
            </thead>
            <tbody id="recent-orders-table" class="divide-y divide-gray-200">
                <!-- Orders will be loaded dynamically from API -->
            </tbody>
        </table>
    </div>
</div>

<script>
    // Dashboard-specific JavaScript
    (function() {
        // API Base URL - use global if available, otherwise define
        if (typeof window.API_BASE === 'undefined' && typeof API_BASE === 'undefined') {
            window.API_BASE = '/api/v1/admin';
        }
        const apiBase = (typeof API_BASE !== 'undefined') ? API_BASE : (window.API_BASE || '/api/v1/admin');
        
        // Dashboard data loaded from API
        let dashboardData = {};
        
        // Chart instance
        let revenueChart = null;

        // Load dashboard data from API
        async function loadDashboardData() {
            try {
                const period = parseInt(document.getElementById('chart-period')?.value || 30);
                const endDate = new Date();
                const startDate = new Date();
                startDate.setDate(startDate.getDate() - 30); // Last 30 days for stats
                
                const response = await fetch(`${apiBase}/dashboard.php?period=${period}&start_date=${startDate.toISOString().split('T')[0]}&end_date=${endDate.toISOString().split('T')[0]}`, {
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' }
                });
                
                if (!response.ok) {
                    throw new Error('Failed to fetch dashboard data');
                }
                
                const result = await response.json();
                
                if (result.success) {
                    dashboardData = result.data;
                    updateDashboardStats();
                    initRevenueChart();
                    updateRecentOrders();
                    updateRecentActivity();
                } else {
                    console.error('Failed to load dashboard data:', result.message);
                    showError('Failed to load dashboard data');
                }
            } catch (error) {
                console.error('Error loading dashboard data:', error);
                showError('Error loading dashboard data. Please refresh the page.');
            }
        }
        
        function showError(message) {
            const tbody = document.getElementById('recent-orders-table');
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-gray-500">${message}</td></tr>`;
            }
        }

        // Initialize Revenue Chart
        function initRevenueChart() {
            const ctx = document.getElementById('revenueChart');
            if (!ctx) return;

            const period = parseInt(document.getElementById('chart-period').value) || 30;
            const chartData = dashboardData.revenue_chart || [];

            // Group by date
            const revenueByDate = {};
            chartData.forEach(item => {
                const date = new Date(item.date);
                const label = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                revenueByDate[item.date] = parseFloat(item.revenue || 0);
            });

            // Prepare chart data
            const labels = [];
            const data = [];
            
            // Generate labels for the period
            for (let i = period - 1; i >= 0; i--) {
                const date = new Date();
                date.setDate(date.getDate() - i);
                const dateStr = date.toISOString().split('T')[0];
                const label = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                labels.push(label);
                data.push(revenueByDate[dateStr] || 0);
            }

            // Destroy existing chart if it exists
            if (revenueChart) {
                revenueChart.destroy();
            }

            // Create new chart
            revenueChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue (₹)',
                        data: data,
                        borderColor: '#14b8a6',
                        backgroundColor: 'rgba(20, 184, 166, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#14b8a6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: '#0d9488',
                        pointHoverBorderColor: '#ffffff',
                        pointHoverBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: '600'
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    return 'Revenue: ₹' + context.parsed.y.toLocaleString('en-IN');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString('en-IN');
                                },
                                font: {
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }

        // Update chart when period changes
        window.updateChart = async function() {
            await loadDashboardData();
        };

        // Update Dashboard Stats
        function updateDashboardStats() {
            const stats = dashboardData.statistics || {};
            
            const totalRevenue = stats.total_revenue || 0;
            const totalOrders = stats.total_orders || 0;
            const productsInStock = stats.products_in_stock || stats.total_products || 0;
            const lowStockItems = stats.low_stock_count || 0;

            // Format currency
            const formatCurrency = (amount) => {
                return parseFloat(amount).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            };

            // Update UI
            const revenueElement = document.getElementById('total-revenue');
            if (revenueElement) {
                revenueElement.textContent = `₹${formatCurrency(totalRevenue)}`;
            }

            const ordersElement = document.getElementById('total-orders');
            if (ordersElement) {
                ordersElement.textContent = totalOrders.toLocaleString('en-IN');
            }

            const stockElement = document.getElementById('products-in-stock');
            if (stockElement) {
                stockElement.textContent = productsInStock.toLocaleString('en-IN');
            }

            const lowStockElement = document.getElementById('low-stock-items');
            if (lowStockElement) {
                lowStockElement.textContent = lowStockItems.toLocaleString('en-IN');
            }
        }

        // Update Recent Orders Table
        function updateRecentOrders() {
            const recentOrders = dashboardData.recent_orders || [];
            const tbody = document.getElementById('recent-orders-table');
            
            if (!tbody) return;
            
            if (recentOrders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-gray-500">No recent orders</td></tr>';
                return;
            }
            
            // Clear existing rows
            tbody.innerHTML = '';
            
            // Add new order rows
            recentOrders.forEach(order => {
                const row = document.createElement('tr');
                
                const statusColors = {
                    'confirmed': 'bg-blue-100 text-blue-800',
                    'processing': 'bg-yellow-100 text-yellow-800',
                    'shipped': 'bg-purple-100 text-purple-800',
                    'delivered': 'bg-green-100 text-green-800',
                    'cancelled': 'bg-red-100 text-red-800',
                    'pending': 'bg-gray-100 text-gray-800'
                };
                const statusClass = statusColors[order.status] || 'bg-gray-100 text-gray-800';
                const statusText = order.status ? order.status.charAt(0).toUpperCase() + order.status.slice(1) : 'Pending';
                
                const orderDate = new Date(order.order_date);
                const formattedDate = orderDate.toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });

                row.className = 'hover:bg-gray-50 cursor-pointer transition-colors';
                row.onclick = () => {
                    window.location.href = `dashboard.php?page=orders&search=${order.order_number || order.id}`;
                };
                
                row.innerHTML = `
                    <td class="py-3 px-4 text-sm font-semibold text-gray-900">#${escapeHtml(order.order_number || order.id)}</td>
                    <td class="py-3 px-4 text-sm text-gray-700">${escapeHtml(order.customer_name || 'Guest')}</td>
                    <td class="py-3 px-4 text-sm text-gray-700">${escapeHtml(order.product_name || 'Multiple products')}</td>
                    <td class="py-3 px-4 text-sm font-bold text-gray-900">₹${parseFloat(order.total_amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold ${statusClass}">${statusText}</span>
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-500">${formattedDate}</td>
                `;
                tbody.appendChild(row);
            });
            
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        // Update Recent Activity
        function updateRecentActivity() {
            const recentOrders = dashboardData.recent_orders || [];
            const activityList = document.getElementById('recent-activity-list');
            
            if (!activityList) return;
            
            if (recentOrders.length === 0) {
                activityList.innerHTML = '<div class="text-center py-4 text-gray-500 text-sm">No recent activity</div>';
                return;
            }
            
            // Generate activity items from recent orders
            const activities = recentOrders.slice(0, 5).map(order => {
                const orderDate = new Date(order.order_date);
                const timeAgo = getTimeAgo(orderDate);
                
                const statusIcons = {
                    'confirmed': { icon: 'check', bg: 'bg-green-100', color: 'text-green-600' },
                    'processing': { icon: 'package', bg: 'bg-blue-100', color: 'text-blue-600' },
                    'shipped': { icon: 'truck', bg: 'bg-purple-100', color: 'text-purple-600' },
                    'delivered': { icon: 'check-circle', bg: 'bg-green-100', color: 'text-green-600' },
                    'cancelled': { icon: 'x-circle', bg: 'bg-red-100', color: 'text-red-600' }
                };
                
                const iconInfo = statusIcons[order.status] || { icon: 'shopping-cart', bg: 'bg-teal-100', color: 'text-teal-600' };
                const activityText = order.status === 'delivered' ? 'Order delivered' : 
                                   order.status === 'shipped' ? 'Order shipped' :
                                   order.status === 'processing' ? 'Order processing' :
                                   order.status === 'confirmed' ? 'New order confirmed' :
                                   'New order received';
                
                return `
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full ${iconInfo.bg} flex items-center justify-center flex-shrink-0">
                            <i data-lucide="${iconInfo.icon}" class="w-4 h-4 ${iconInfo.color}"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">${activityText}</p>
                            <p class="text-xs text-gray-500">Order #${order.order_number || order.id} - ₹${parseFloat(order.total_amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})} - ${timeAgo}</p>
                        </div>
                    </div>
                `;
            }).join('');
            
            activityList.innerHTML = activities;
            
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        function getTimeAgo(date) {
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);
            
            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins} min${diffMins > 1 ? 's' : ''} ago`;
            if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
            if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Initialize dashboard on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                loadDashboardData();
            });
        } else {
            loadDashboardData();
        }
        
        // Make updateChart globally accessible
        window.updateChart = async function() {
            await loadDashboardData();
        };
    })();
</script>
