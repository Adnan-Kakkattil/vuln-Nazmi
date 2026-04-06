<?php
/**
 * Orders Page Content
 * Order management page with status updates and order details
 */
?>
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
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>
        </div>
        <select id="filter-status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
            <option value="all">All Status</option>
            <option value="confirmed">Confirmed</option>
            <option value="processing">Processing</option>
            <option value="shipped">Shipped</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
        </select>
        <input type="date" id="filter-date" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
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

<!-- Update Status Modal -->
<div class="modal-overlay" id="status-modal" onclick="closeStatusModal(event)">
    <div class="modal" style="max-width: 500px;" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center">
                    <i data-lucide="edit-3" class="w-5 h-5 text-teal-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Update Order Status</h2>
            </div>
            <button onclick="closeStatusModal()" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <form id="status-form" onsubmit="updateOrderStatus(event)">
            <input type="hidden" id="status-order-id">
            
            <!-- Order Info Card -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <i data-lucide="shopping-bag" class="w-4 h-4 text-gray-500"></i>
                        <span class="text-sm text-gray-600">Order:</span>
                        <span class="text-sm font-semibold text-gray-900" id="status-order-number">-</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-gray-500"></i>
                        <span class="text-sm text-gray-600">Current Status:</span>
                        <span class="text-sm font-semibold text-gray-900" id="status-current-status">-</span>
                    </div>
                </div>
            </div>
            
            <!-- Form Fields -->
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        New Status <span class="text-red-500">*</span>
                    </label>
                    <select id="new-status" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors bg-white">
                        <option value="">Select Status</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1.5">Select the new status for this order</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Payment Status <span class="text-red-500">*</span>
                    </label>
                    <select id="new-payment-status" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors bg-white">
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                        <option value="refunded">Refunded</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1.5">
                        <i data-lucide="info" class="w-3 h-3 inline-block mr-1"></i>
                        Setting order to 'Delivered' automatically marks payment as 'Completed'
                    </p>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="flex gap-3 mt-8 pt-6 border-t border-gray-200">
                <button type="submit" class="flex-1 px-6 py-3 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-semibold shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Update Status</span>
                </button>
                <button type="button" onclick="closeStatusModal()" class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-colors font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Enhanced Status Modal Styles */
    #status-modal .modal {
        padding: 32px;
    }
    
    #status-modal select {
        font-size: 14px;
        cursor: pointer;
    }
    
    #status-modal select:focus {
        box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
    }
    
    #status-modal label {
        user-select: none;
    }
    
    #status-modal .bg-gray-50 {
        background-color: #f9fafb;
    }
</style>

<!-- Order Detail Modal -->
<div class="modal-overlay" id="order-detail-modal" onclick="closeOrderDetailModal(event)">
    <div class="modal modal-large" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="order-detail-header">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold" id="detail-order-id">Order Details</h2>
                    <p class="text-sm text-white/80 mt-1" id="detail-order-date">-</p>
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
                <button onclick="event.stopPropagation(); openStatusModalFromDetail();" class="flex-1 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-medium flex items-center justify-center gap-2">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                    Update Status
                </button>
                <button onclick="event.stopPropagation(); printOrder();" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium flex items-center justify-center gap-2">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    Print
                </button>
                <button onclick="event.stopPropagation(); closeOrderDetailModal();" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Orders page-specific JavaScript
    (function() {
        // Ensure API_BASE is defined
        if (typeof API_BASE === 'undefined') {
            window.API_BASE = '/api/v1/admin';
        }
        
        let orders = [];
        let currentDetailOrderId = null;

        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initOrdersPage);
        } else {
            initOrdersPage();
        }

        function initOrdersPage() {
            // Initialize icons first
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            
            loadOrders();
            
            // Set up event listeners
            const searchInput = document.getElementById('search-input');
            const filterStatus = document.getElementById('filter-status');
            const filterDate = document.getElementById('filter-date');
            
            if (searchInput) searchInput.addEventListener('input', filterOrders);
            if (filterStatus) filterStatus.addEventListener('change', filterOrders);
            if (filterDate) filterDate.addEventListener('change', filterOrders);
            
            // Add Escape key listener to close modals
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    const statusModal = document.getElementById('status-modal');
                    if (statusModal && statusModal.classList.contains('show')) {
                        closeStatusModal();
                    }
                    const detailModal = document.getElementById('order-detail-modal');
                    if (detailModal && detailModal.classList.contains('show')) {
                        closeOrderDetailModal();
                    }
                }
            });
            
            // Re-initialize icons periodically for dynamic content
            setInterval(() => {
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }, 1000);
        }

        // Load orders from API
        window.loadOrders = async function() {
            try {
                const apiBase = window.API_BASE || '/api/v1/admin';
                const response = await fetch(`${apiBase}/orders.php`, {
                    credentials: 'include'
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    orders = result.data || [];
                    renderOrders();
                    updateSummary();
                } else {
                    console.error('Failed to load orders:', result.message);
                    if (typeof Tivora !== 'undefined') {
                        await Tivora.alert('Failed to load orders: ' + (result.message || 'Unknown error'), 'error');
                    }
                }
            } catch (error) {
                console.error('Error loading orders:', error);
                if (typeof Tivora !== 'undefined') {
                    await Tivora.alert('Error loading orders. Please check your connection and try again.', 'error');
                }
            }
        };

        // Render orders table
        window.renderOrders = function(filteredOrders = null) {
            const tbody = document.getElementById('orders-tbody');
            const emptyState = document.getElementById('empty-state');
            const ordersToRender = filteredOrders || orders.sort((a, b) => new Date(b.orderDate || b.order_date) - new Date(a.orderDate || a.order_date));

            tbody.innerHTML = '';

            if (ordersToRender.length === 0) {
                emptyState.classList.remove('hidden');
                return;
            }

            emptyState.classList.add('hidden');

            ordersToRender.forEach(order => {
                const orderDate = new Date(order.orderDate || order.order_date);
                const formattedDate = orderDate.toLocaleDateString('en-IN', { 
                    day: 'numeric', 
                    month: 'short', 
                    year: 'numeric' 
                });
                
                const itemsCount = order.itemsCount || (order.items ? order.items.reduce((sum, item) => sum + (item.quantity || 1), 0) : 0);
                const totalAmount = order.total_amount || order.total || (order.totals ? order.totals.total : 0);
                const customerName = order.customer_name || order.guest_name || 'Guest';
                const customerEmail = order.customer_email || order.guest_email || 'N/A';
                
                const statusColors = {
                    'confirmed': 'status-confirmed',
                    'processing': 'status-processing',
                    'shipped': 'status-shipped',
                    'delivered': 'status-delivered',
                    'cancelled': 'status-cancelled'
                };
                const statusClass = statusColors[order.status] || 'status-confirmed';
                const statusText = order.status ? order.status.charAt(0).toUpperCase() + order.status.slice(1) : 'Confirmed';

                const paymentMethod = order.payment_method || 'N/A';
                const paymentStatus = order.payment_status || 'pending';
                const paymentBadgeClass = paymentMethod === 'cod' ? 'payment-cod' : 'payment-online';
                const paymentStatusClass = paymentStatus === 'completed' ? 'payment-completed' : (paymentStatus === 'failed' ? 'payment-failed' : 'payment-pending');
                const paymentIcon = paymentMethod === 'cod' ? 'banknote' : 'credit-card';
                const paymentLabel = paymentMethod === 'cod' ? 'COD' : (paymentMethod === 'online' ? 'Online' : paymentMethod.toUpperCase());

                const row = document.createElement('tr');
                const currentOrderId = order.order_number || order.orderNumber || order.id;
                row.className = 'hover:bg-gray-50 cursor-pointer transition-colors';
                row.addEventListener('click', function(e) {
                    if (e.target.closest('.action-btn')) return;
                    viewOrderDetail(currentOrderId);
                });
                row.innerHTML = `
                    <td class="py-3 px-4 text-sm font-semibold text-gray-900">${order.order_number || order.orderNumber || order.id || 'N/A'}</td>
                    <td class="py-3 px-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900">${customerName}</p>
                            <p class="text-xs text-gray-500">${customerEmail}</p>
                        </div>
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-700">${formattedDate}</td>
                    <td class="py-3 px-4 text-sm text-gray-700">${itemsCount} item${itemsCount !== 1 ? 's' : ''}</td>
                    <td class="py-3 px-4 text-sm font-semibold text-gray-900">₹${totalAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
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
                            <button onclick="event.stopPropagation(); openStatusModal('${currentOrderId}')" class="action-btn p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Update Status">
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </button>
                            <button onclick="event.stopPropagation(); deleteOrder('${currentOrderId}')" class="action-btn p-2 text-teal-600 hover:bg-teal-50 rounded-lg transition-colors" title="Delete">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });

            lucide.createIcons();
        };

        // Update summary cards
        window.updateSummary = function(filteredOrders = null) {
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
        };

        // Filter orders
        window.filterOrders = function() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const statusFilter = document.getElementById('filter-status').value;
            const dateFilter = document.getElementById('filter-date').value;

            let filtered = orders.filter(order => {
                const orderDateStr = order.orderDate || order.order_date;
                const orderDateFormatted = orderDateStr ? new Date(orderDateStr).toISOString().split('T')[0] : '';
                const searchMatches = 
                    (order.order_number && order.order_number.toLowerCase().includes(searchTerm)) ||
                    (order.id && order.id.toString().includes(searchTerm)) ||
                    (order.customer_name && order.customer_name.toLowerCase().includes(searchTerm)) ||
                    (order.guest_name && order.guest_name.toLowerCase().includes(searchTerm)) ||
                    (order.customer_email && order.customer_email.toLowerCase().includes(searchTerm)) ||
                    (order.guest_email && order.guest_email.toLowerCase().includes(searchTerm));
                
                const matchesStatus = statusFilter === 'all' || order.status === statusFilter;
                const matchesDate = !dateFilter || orderDateFormatted === dateFilter;

                return searchMatches && matchesStatus && matchesDate;
            });

            renderOrders(filtered);
            updateSummary(filtered);
        };

        // Reset filters
        window.resetFilters = function() {
            document.getElementById('search-input').value = '';
            document.getElementById('filter-status').value = 'all';
            document.getElementById('filter-date').value = '';
            loadOrders();
        };

        // Open Status Modal
        window.openStatusModal = function(orderId) {
            const order = orders.find(o => (o.order_number || o.orderNumber || o.id) == orderId);
            
            if (!order) {
                console.error('Order not found:', orderId);
                return;
            }

            document.getElementById('status-order-id').value = orderId;
            document.getElementById('status-order-number').textContent = order.order_number || order.orderNumber || order.id || orderId;
            
            // Format current status with badge styling
            const currentStatus = order.status || 'confirmed';
            const statusText = currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1);
            document.getElementById('status-current-status').textContent = statusText;
            
            document.getElementById('new-status').value = currentStatus;
            document.getElementById('new-payment-status').value = order.payment_status || 'pending';
            
            const modal = document.getElementById('status-modal');
            if (modal) {
                modal.classList.add('show');
                
                // Initialize icons after opening modal
                setTimeout(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }, 100);
            }
        };

        // Close Status Modal
        window.closeStatusModal = function(event) {
            if (event && event.target !== event.currentTarget && !event.target.closest('.modal')) {
                return;
            }
            const modal = document.getElementById('status-modal');
            if (modal) {
                modal.classList.remove('show');
                // Reset form
                const form = document.getElementById('status-form');
                if (form) {
                    form.reset();
                }
            }
        };

        // Update Order Status
        window.updateOrderStatus = async function(event) {
            event.preventDefault();

            const orderId = document.getElementById('status-order-id').value;
            const newStatus = document.getElementById('new-status').value;
            const newPaymentStatus = document.getElementById('new-payment-status').value;

            try {
                const apiBase = window.API_BASE || '/api/v1/admin';
                const response = await fetch(`${apiBase}/orders.php`, {
                    method: 'PUT',
                    credentials: 'include',
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
        };

        // Delete Order
        window.deleteOrder = async function(orderId) {
            if (!await Tivora.confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
                return;
            }

            try {
                const apiBase = window.API_BASE || '/api/v1/admin';
                const response = await fetch(`${apiBase}/orders.php?id=${orderId}`, {
                    method: 'DELETE',
                    credentials: 'include'
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
        };

        // View Order Detail
        window.viewOrderDetail = function(orderId) {
            const order = orders.find(o => (o.order_number || o.orderNumber || o.id) == orderId);
            if (!order) {
                console.error('Order not found:', orderId);
                return;
            }

            currentDetailOrderId = orderId;

            document.getElementById('detail-order-id').textContent = `Order #${order.order_number || order.orderNumber || order.id}`;
            const orderDate = new Date(order.order_date || order.orderDate);
            document.getElementById('detail-order-date').textContent = orderDate.toLocaleDateString('en-IN', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

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

            const customerName = order.customer_name || order.guest_name || 'N/A';
            document.getElementById('detail-customer-name').textContent = customerName;
            document.getElementById('detail-customer-email').textContent = order.customer_email || order.guest_email || 'N/A';
            document.getElementById('detail-customer-phone').textContent = order.shipping_phone || 'N/A';

            const shippingParts = [];
            if (order.shipping_address_line1) shippingParts.push(order.shipping_address_line1);
            if (order.shipping_address_line2) shippingParts.push(order.shipping_address_line2);
            if (order.shipping_city) shippingParts.push(order.shipping_city);
            if (order.shipping_state) shippingParts.push(order.shipping_state);
            if (order.shipping_pincode) shippingParts.push(order.shipping_pincode);
            if (order.shipping_country) shippingParts.push(order.shipping_country);
            document.getElementById('detail-shipping-address').textContent = shippingParts.length > 0 ? shippingParts.join(', ') : 'N/A';

            const paymentMethod = order.payment_method || 'N/A';
            const paymentMethodDisplay = paymentMethod === 'cod' ? 'Cash on Delivery (COD)' : 
                                         paymentMethod === 'online' ? 'Online Payment' : 
                                         paymentMethod.toUpperCase();
            document.getElementById('detail-payment-method').innerHTML = `
                <span class="payment-badge ${paymentMethod === 'cod' ? 'payment-cod' : 'payment-online'}">
                    <i data-lucide="${paymentMethod === 'cod' ? 'banknote' : 'credit-card'}" class="w-3 h-3"></i>
                    ${paymentMethodDisplay}
                </span>
            `;

            const paymentStatus = order.payment_status || 'pending';
            const paymentStatusClass = paymentStatus === 'completed' ? 'text-green-600' : 
                                      (paymentStatus === 'failed' ? 'text-teal-600' : 'text-yellow-600');
            document.getElementById('detail-payment-status').innerHTML = `
                <span class="${paymentStatusClass} font-medium">${paymentStatus.charAt(0).toUpperCase() + paymentStatus.slice(1)}</span>
            `;

            const itemsContainer = document.getElementById('detail-items-container');
            itemsContainer.innerHTML = '';
            
            if (order.items && order.items.length > 0) {
                order.items.forEach(item => {
                    const itemHtml = `
                        <div class="order-item">
                            <div class="order-item-image">
                                <i data-lucide="package" class="w-6 h-6 text-gray-400"></i>
                            </div>
                            <div class="order-item-details">
                                <p class="order-item-name">${item.product_name || 'Product'}</p>
                                <p class="order-item-sku">SKU: ${item.product_sku || 'N/A'}</p>
                            </div>
                            <div class="order-item-qty-price">
                                <p class="order-item-price">₹${(item.unit_price || item.price || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
                                <p class="order-item-qty">Qty: ${item.quantity || 1}</p>
                            </div>
                        </div>
                    `;
                    itemsContainer.innerHTML += itemHtml;
                });
            } else {
                itemsContainer.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">No items found</p>';
            }

            const subtotal = order.subtotal || (order.totals ? order.totals.subtotal : 0);
            const discount = order.discount_amount || (order.totals ? order.totals.discount : 0);
            const tax = order.tax_amount || (order.totals ? order.totals.tax : 0);
            const shipping = order.shipping_cost || (order.totals ? order.totals.shipping : 0);
            const total = order.total_amount || order.total || (order.totals ? order.totals.total : 0);
            
            document.getElementById('detail-subtotal').textContent = `₹${subtotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            document.getElementById('detail-discount').textContent = discount > 0 ? `-₹${discount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` : '₹0.00';
            document.getElementById('detail-tax').textContent = `₹${tax.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            document.getElementById('detail-shipping').textContent = shipping > 0 ? `₹${shipping.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` : 'Free';
            document.getElementById('detail-total').textContent = `₹${total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

            renderTimeline(order);

            const notesSection = document.getElementById('notes-section');
            if (order.notes || order.internal_notes) {
                notesSection.style.display = 'block';
                document.getElementById('detail-notes').textContent = order.notes || order.internal_notes;
            } else {
                notesSection.style.display = 'none';
            }

            document.getElementById('order-detail-modal').classList.add('show');
            lucide.createIcons();
        };

        // Render order timeline
        window.renderTimeline = function(order) {
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
                }) : (isActive && index === 0 ? new Date(order.order_date || order.orderDate).toLocaleDateString('en-IN', {
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
        };

        // Close Order Detail Modal
        window.closeOrderDetailModal = function(event) {
            if (event && event.target !== event.currentTarget) return;
            document.getElementById('order-detail-modal').classList.remove('show');
            currentDetailOrderId = null;
        };

        // Open status modal from detail view
        window.openStatusModalFromDetail = function() {
            if (currentDetailOrderId) {
                // Close detail modal first
                const detailModal = document.getElementById('order-detail-modal');
                if (detailModal) {
                    detailModal.classList.remove('show');
                }
                
                // Open status modal after a short delay to ensure smooth transition
                setTimeout(() => {
                    openStatusModal(currentDetailOrderId);
                }, 150);
            } else {
                console.error('No order selected for status update');
            }
        };

        // Print order
        window.printOrder = function() {
            const order = orders.find(o => (o.order_number || o.orderNumber || o.id) == currentDetailOrderId);
            if (!order) return;

            window.print();
        };
    })();
</script>
