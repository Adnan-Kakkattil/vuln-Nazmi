<?php
// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <title>Order Details | NAZMI BOUTIQUE</title>
    
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
    <script src="js/tivora-alerts.js"></script>

    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #ffffff;
            color: #1a1a1a;
            overflow-x: hidden;
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

        /* Mobile-first responsive improvements */
        @media (max-width: 640px) {
            * {
                max-width: 100%;
            }
            
            h1 {
                font-size: 1.75rem !important;
                line-height: 1.3 !important;
            }
            
            h2 {
                font-size: 1.5rem !important;
            }
            
            h3 {
                font-size: 1.25rem !important;
            }
            
            button, a[role="button"] {
                min-height: 44px;
                min-width: 44px;
            }

            /* Order overview specific */
            .detail-card {
                padding: 1rem !important;
                border-radius: 1rem !important;
            }

            .detail-grid {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }

            .order-items-list {
                gap: 1rem !important;
            }

            /* Better spacing */
            section {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            /* Form inputs */
            input, select, textarea {
                font-size: 16px !important; /* Prevents zoom on iOS */
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
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

        /* Glassmorphism for Navbar */
        .glass-nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        /* Detail Card Styles */
        .detail-card {
            background: white;
            border-radius: 1.5rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .detail-card:hover {
            box-shadow: 0 4px 12px rgba(20, 184, 166, 0.15);
        }

        @media (min-width: 640px) {
            .detail-card {
                padding: 2rem;
                border-radius: 2rem;
            }
        }
    </style>
</head>
<body class="antialiased">
    <!-- Navigation -->
    <?php include 'header.php'; ?>

    <!-- Include Sidebars -->
    <?php include 'includes/sidebars.php'; ?>

    <!-- Order Details Section -->
    <div class="pt-6 sm:pt-8 pb-12 sm:pb-16 md:pb-20 min-h-screen bg-white">
        <div class="container mx-auto px-4 lg:px-12">
            <!-- Back Button -->
            <div class="mb-8">
                <a href="orders.php" class="inline-flex items-center gap-2 text-slate-600 hover:text-brand transition-colors text-sm font-medium">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                    <span>Back to Orders</span>
                </a>
            </div>

            <!-- Page Header -->
            <div class="mb-12 md:mb-16">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-6">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="h-[2px] w-8 bg-brand"></span>
                            <span class="text-brand font-bold uppercase tracking-widest text-xs">Order Details</span>
                        </div>
                        <h1 class="text-3xl md:text-5xl font-serif font-black text-slate-900 tracking-tight">Order Information</h1>
                        <p id="order-id-header" class="text-slate-500 mt-2 font-medium">Loading order information...</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                        <button id="cancel-order-btn" class="hidden px-6 py-3 bg-white border-2 border-slate-200 text-slate-700 rounded-2xl hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900 transition-all font-black text-sm flex items-center justify-center gap-2">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            <span>Cancel Order</span>
                        </button>
                        <button id="copy-order-id-btn" class="px-6 py-3 bg-white border-2 border-slate-200 text-slate-700 rounded-2xl hover:bg-slate-50 hover:border-brand hover:text-brand transition-all font-black text-sm flex items-center justify-center gap-2">
                            <i data-lucide="copy" class="w-4 h-4"></i>
                            <span>Copy Order ID</span>
                        </button>
                        <a href="shop.php" class="px-6 py-3 bg-brand text-white rounded-2xl hover:bg-teal-600 transition-all font-black text-sm flex items-center justify-center gap-2 shadow-xl shadow-brand/20">
                            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                            <span>Continue Shopping</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Order Details Container -->
            <div id="order-details-container" class="space-y-6 md:space-y-8" style="display: block;">
                <!-- Loading state will be replaced by JavaScript -->
                <div class="text-center py-20">
                    <div class="animate-spin rounded-full h-16 w-16 border-4 border-brand border-t-transparent mx-auto mb-6"></div>
                    <p class="text-slate-500 font-medium">Loading order details...</p>
                </div>
            </div>

            <!-- Error State -->
            <div id="error-state" class="text-center py-20 md:py-32" style="display: none;">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-slate-50 rounded-full mb-8">
                    <i data-lucide="alert-triangle" class="w-12 h-12 text-slate-300"></i>
                </div>
                <h3 class="text-2xl md:text-3xl font-serif font-black text-slate-900 mb-4">Order Not Found</h3>
                <p class="text-slate-500 mb-8 max-w-md mx-auto font-medium">The order you're looking for doesn't exist or has been removed.</p>
                <a href="orders.php" class="inline-block px-8 py-4 bg-brand text-white font-black rounded-2xl hover:bg-teal-600 transition-all shadow-xl shadow-brand/20">
                    View All Orders
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white pt-24 pb-12 border-t border-slate-100">
        <div class="container mx-auto px-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-16 mb-24">
                <div class="space-y-8">
                    <h2 class="text-4xl font-serif font-black">NAZMI<span class="text-brand">.</span></h2>
                    <p class="text-slate-500 leading-relaxed font-medium">
                        Designing for the bold, the elegant, and the authentic. Nazmi Boutique is your destination for premium artisanal apparel.
                    </p>
                    <div class="flex gap-5">
                        <div class="w-12 h-12 rounded-full border border-slate-100 flex items-center justify-center hover:bg-brand hover:text-white transition-all cursor-pointer">
                            <i data-lucide="facebook" class="w-5 h-5"></i>
                        </div>
                        <div class="w-12 h-12 rounded-full border border-slate-100 flex items-center justify-center hover:bg-brand hover:text-white transition-all cursor-pointer">
                            <i data-lucide="instagram" class="w-5 h-5"></i>
                        </div>
                        <div class="w-12 h-12 rounded-full border border-slate-100 flex items-center justify-center hover:bg-brand hover:text-white transition-all cursor-pointer">
                            <i data-lucide="twitter" class="w-5 h-5"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <h6 class="font-black text-xs uppercase tracking-[0.2em] mb-10 text-slate-400">Collections</h6>
                    <ul class="space-y-5">
                        <li onclick="window.location.href='shop.php?filter=new'" class="text-sm font-bold text-slate-600 hover:text-brand transition-colors cursor-pointer">New Arrivals</li>
                        <li onclick="window.location.href='shop.php?filter=bestseller'" class="text-sm font-bold text-slate-600 hover:text-brand transition-colors cursor-pointer">Best Sellers</li>
                        <li onclick="window.location.href='shop.php?filter=ethnic'" class="text-sm font-bold text-slate-600 hover:text-brand transition-colors cursor-pointer">Ethnic Wear</li>
                        <li onclick="window.location.href='shop.php?filter=sale'" class="text-sm font-bold text-slate-600 hover:text-brand transition-colors cursor-pointer">Sale</li>
                    </ul>
                </div>

                <div>
                    <h6 class="font-black text-xs uppercase tracking-[0.2em] mb-10 text-slate-400">Assistance</h6>
                    <ul class="space-y-5">
                        <li onclick="window.location.href='orders.php'" class="text-sm font-bold text-slate-600 hover:text-brand transition-colors cursor-pointer">Track Order</li>
                        <li class="text-sm font-bold text-slate-600 hover:text-brand transition-colors cursor-pointer">Returns</li>
                        <li class="text-sm font-bold text-slate-600 hover:text-brand transition-colors cursor-pointer">Size Guide</li>
                        <li onclick="window.location.href='index.php#contact'" class="text-sm font-bold text-slate-600 hover:text-brand transition-colors cursor-pointer">Contact</li>
                    </ul>
                </div>

                <div class="space-y-6">
                    <h6 class="font-black text-xs uppercase tracking-[0.2em] mb-10 text-slate-400">Join Us</h6>
                    <div class="flex">
                        <input type="text" id="newsletterEmail" placeholder="Email Address" class="bg-slate-50 border-none rounded-l-lg px-4 py-3 text-sm focus:ring-1 focus:ring-brand w-full">
                        <button onclick="subscribeNewsletter()" class="bg-brand text-white px-6 rounded-r-lg font-bold">JOIN</button>
                    </div>
                </div>
            </div>

            <div class="pt-12 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-8 text-[10px] font-black uppercase tracking-widest text-slate-400">
                <p>© 2024 Nazmi Boutique International</p>
                <div class="flex gap-4 opacity-40">
                    <img src="https://cdn-icons-png.flaticon.com/512/349/349221.png" alt="Visa" class="h-4">
                    <img src="https://cdn-icons-png.flaticon.com/512/349/349230.png" alt="Mastercard" class="h-4">
                </div>
            </div>
        </div>
    </footer>

    <!-- Cart/Wishlist JavaScript -->
    <script src="js/cart-wishlist.js"></script>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Load Order Details from API
        async function loadOrderDetails() {
            const urlParams = new URLSearchParams(window.location.search);
            // Support both 'order' and 'orderId' parameters
            const orderNumber = urlParams.get('order') || urlParams.get('orderId') || urlParams.get('orderNumber');
            
            if (!orderNumber) {
                showError();
                return;
            }

            try {
                // Fetch order from API
                const response = await fetch(`/api/v1/orders.php?order_number=${encodeURIComponent(orderNumber)}`);
                const result = await response.json();
                
                if (!result.success || !result.data) {
                    showError();
                    return;
                }

                renderOrderDetails(result.data);
            } catch (error) {
                console.error('Error loading order:', error);
                showError();
            }
        }

        function renderOrderDetails(order) {
            if (!order) {
                showError();
                return;
            }

            try {
                // Hide error state and show container
                const errorState = document.getElementById('error-state');
                const container = document.getElementById('order-details-container');
                
                if (errorState) errorState.style.display = 'none';
                if (container) container.style.display = 'block';

                // Get order number/id
                const orderNumber = order.order_number || order.orderNumber || order.orderId;
            
            // Update header
            document.getElementById('order-id-header').textContent = `Order ID: ${orderNumber}`;
            
            // Newsletter subscription
            window.subscribeNewsletter = function() {
                const email = document.getElementById('newsletterEmail')?.value;
                if (email) {
                    alert('Thank you for subscribing!');
                    document.getElementById('newsletterEmail').value = '';
                }
            };

            // Show/Hide Cancel Button
            const cancelBtn = document.getElementById('cancel-order-btn');
            const allowCancel = ['pending', 'confirmed'];
            if (cancelBtn) {
                if (allowCancel.includes(order.status)) {
                    cancelBtn.classList.remove('hidden');
                    // Add click listener
                    cancelBtn.onclick = async () => {
                        const confirmed = await Tivora.confirm('Are you sure you want to cancel this order? This action cannot be undone.');
                        if (confirmed) {
                            try {
                                cancelBtn.disabled = true;
                                cancelBtn.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-teal-500"></div> <span>Cancelling...</span>';
                                
                                const response = await fetch('/api/v1/orders.php', {
                                    method: 'PATCH',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ order_number: orderNumber })
                                });
                                const result = await response.json();
                                
                                if (result.success) {
                                    await Tivora.alert('Order cancelled successfully.', 'success');
                                    loadOrderDetails(); // Reload page data
                                } else {
                                    await Tivora.alert(result.message || 'Failed to cancel order.', 'error');
                                }
                            } catch (error) {
                                console.error('Error cancelling order:', error);
                                await Tivora.alert('An error occurred while cancelling your order.', 'error');
                            } finally {
                                cancelBtn.disabled = false;
                                cancelBtn.innerHTML = '<i data-lucide="x-circle" class="w-4 h-4"></i> <span>Cancel Order</span>';
                                lucide.createIcons();
                            }
                        }
                    };
                } else {
                    cancelBtn.classList.add('hidden');
                }
            }

            // Format dates
            const orderDate = new Date(order.order_date || order.orderDate);
            const formattedDate = orderDate.toLocaleDateString('en-IN', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            // Calculate delivery date (7 days from order date)
            const deliveryDate = new Date(orderDate);
            deliveryDate.setDate(deliveryDate.getDate() + 7);
            const formattedDeliveryDate = deliveryDate.toLocaleDateString('en-IN', { 
                weekday: 'long',
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });

            // Status colors
            const statusColors = {
                'confirmed': { bg: 'bg-green-100', text: 'text-green-700', label: 'Confirmed' },
                'processing': { bg: 'bg-blue-100', text: 'text-blue-700', label: 'Processing' },
                'shipped': { bg: 'bg-purple-100', text: 'text-purple-700', label: 'Shipped' },
                'delivered': { bg: 'bg-gray-100', text: 'text-gray-700', label: 'Delivered' },
                'cancelled': { bg: 'bg-slate-100', text: 'text-slate-700', label: 'Cancelled' }
            };
            const status = statusColors[order.status] || statusColors['confirmed'];

            // Payment method text
            const paymentMethod = order.payment_method || order.payment?.method || 'N/A';
            let paymentMethodText = '';
            if (paymentMethod === 'upi') {
                paymentMethodText = 'UPI Payment';
            } else if (paymentMethod === 'card' || paymentMethod === 'online') {
                paymentMethodText = 'Online Payment';
            } else if (paymentMethod === 'cod') {
                paymentMethodText = 'Cash on Delivery';
            } else {
                paymentMethodText = paymentMethod.toUpperCase();
            }
            
            // Payment status
            const paymentStatus = order.payment_status || order.payment?.status || 'pending';
            const paymentStatusText = paymentStatus.charAt(0).toUpperCase() + paymentStatus.slice(1);
            const paymentStatusColor = paymentStatus === 'completed' ? 'text-green-600' : 
                                       (paymentStatus === 'failed' ? 'text-teal-600' : 'text-yellow-600');

            // Get totals - handle API format (totals nested object)
            const orderTotals = order.totals || {};
            const totals = {
                subtotal: parseFloat(orderTotals.subtotal || order.subtotal || 0),
                tax: parseFloat(orderTotals.tax || order.tax || order.tax_amount || 0),
                taxRate: parseFloat(orderTotals.tax_rate || order.tax_rate || 18), // Get tax rate from order data
                shipping: parseFloat(orderTotals.shipping || order.shipping_cost || 0),
                total: parseFloat(orderTotals.total || order.total || order.total_amount || 0)
            };
            
            // Get customer info - handle API response format
            const customer = {
                firstName: order.customer?.first_name || order.shipping_first_name || '',
                lastName: order.customer?.last_name || order.shipping_last_name || '',
                email: order.customer?.email || order.guest_email || '',
                phone: order.customer?.phone || order.shipping_phone || ''
            };
            
            // Get shipping info - handle API response format (shipping_address nested object)
            const shippingAddr = order.shipping_address || {};
            const shipping = {
                addressLine1: shippingAddr.address_line1 || order.shipping_address_line1 || '',
                addressLine2: shippingAddr.address_line2 || order.shipping_address_line2 || '',
                city: shippingAddr.city || order.shipping_city || '',
                state: shippingAddr.state || order.shipping_state || '',
                pincode: shippingAddr.pincode || order.shipping_pincode || '',
                country: shippingAddr.country || order.shipping_country || 'India'
            };
            
            // Get order items
            const items = order.items || order.cart || [];

            // Render order details - container already defined above
            container.innerHTML = `
                <!-- Order Status & Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                    <div class="detail-card">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 bg-brand/10 rounded-full flex items-center justify-center">
                                <i data-lucide="package" class="w-6 h-6 text-brand"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Order Status</h3>
                                <p class="text-xl font-serif font-black ${status.text} ${status.bg} inline-block px-4 py-2 rounded-2xl">${status.label}</p>
                            </div>
                        </div>
                        <div class="space-y-3 text-sm text-slate-600 font-medium">
                            <div class="flex justify-between">
                                <span>Order Date:</span>
                                <span class="font-black text-slate-900">${formattedDate}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Estimated Delivery:</span>
                                <span class="font-black text-slate-900">${formattedDeliveryDate}</span>
                            </div>
                        </div>
                    </div>

                    <div class="detail-card">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 bg-brand/10 rounded-full flex items-center justify-center">
                                <i data-lucide="credit-card" class="w-6 h-6 text-brand"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Payment Method</h3>
                                <p class="text-xl font-serif font-black text-slate-900">${paymentMethodText}</p>
                            </div>
                        </div>
                        <div class="space-y-3 text-sm text-slate-600 font-medium">
                            <div class="flex justify-between">
                                <span>Payment Status:</span>
                                <span class="font-black ${paymentStatusColor}">${paymentStatusText}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Total Amount:</span>
                                <span class="font-serif font-black text-brand text-xl">₹${totals.total.toLocaleString('en-IN')}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Address -->
                <div class="detail-card">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-brand/10 rounded-full flex items-center justify-center">
                            <i data-lucide="map-pin" class="w-6 h-6 text-brand"></i>
                        </div>
                        <h3 class="text-xl font-serif font-black text-slate-900">Delivery Address</h3>
                    </div>
                    <div class="text-slate-600 space-y-2 font-medium">
                        <p class="font-black text-slate-900 text-lg">${customer.firstName} ${customer.lastName}</p>
                        <p>${shipping.addressLine1}</p>
                        ${shipping.addressLine2 ? `<p>${shipping.addressLine2}</p>` : ''}
                        <p>${shipping.city}, ${shipping.state} - ${shipping.pincode}</p>
                        <p>${shipping.country}</p>
                        ${customer.phone ? `<p class="mt-3"><i data-lucide="phone" class="w-4 h-4 inline mr-2"></i> ${customer.phone}</p>` : ''}
                        ${customer.email ? `<p><i data-lucide="mail" class="w-4 h-4 inline mr-2"></i> ${customer.email}</p>` : ''}
                    </div>
                </div>

                <!-- Order Items -->
                <div class="detail-card">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-brand/10 rounded-full flex items-center justify-center">
                            <i data-lucide="shopping-bag" class="w-6 h-6 text-brand"></i>
                        </div>
                        <h3 class="text-xl font-serif font-black text-slate-900">Order Items</h3>
                    </div>
                    <div class="space-y-6">
                        ${items && items.length > 0 ? items.map(item => {
                            const itemName = item.product_name || item.name || 'Product';
                            const itemQty = item.quantity || 1;
                            const itemPrice = parseFloat(item.unit_price || item.price || 0);
                            const itemImage = item.image || '431.webp';
                            return `
                            <div class="flex gap-6 pb-6 border-b border-slate-100 last:border-0">
                                <img src="${itemImage}" alt="${itemName}" class="w-24 h-24 md:w-32 md:h-32 object-cover rounded-2xl border border-slate-100" onerror="this.src='431.webp'">
                                <div class="flex-1">
                                    <h4 class="font-black text-slate-900 mb-2 text-lg">${itemName}</h4>
                                    <p class="text-sm text-slate-500 mb-3 font-medium">Quantity: ${itemQty}</p>
                                    <p class="text-brand font-serif font-black text-xl">₹${(itemPrice * itemQty).toLocaleString('en-IN')}</p>
                                </div>
                            </div>
                        `}).join('') : '<p class="text-slate-500 text-center py-12 font-medium">No items found</p>'}
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="detail-card">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-brand/10 rounded-full flex items-center justify-center">
                            <i data-lucide="receipt" class="w-6 h-6 text-brand"></i>
                        </div>
                        <h3 class="text-xl font-serif font-black text-slate-900">Order Summary</h3>
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between text-base font-medium">
                            <span class="text-slate-600">Subtotal:</span>
                            <span class="font-black text-slate-900">₹${totals.subtotal.toLocaleString('en-IN')}</span>
                        </div>
                        <div class="flex justify-between text-base font-medium">
                            <span class="text-slate-600">Shipping:</span>
                            <span class="font-black ${totals.shipping > 0 ? 'text-slate-900' : 'text-green-600'}">${totals.shipping > 0 ? '₹' + totals.shipping.toLocaleString('en-IN') : 'Free'}</span>
                        </div>
                        <div class="flex justify-between text-base font-medium">
                            <span class="text-slate-600">Tax (GST ${totals.taxRate}%):</span>
                            <span class="font-black text-slate-900">₹${totals.tax.toLocaleString('en-IN')}</span>
                        </div>
                        <div class="flex justify-between text-xl font-serif font-black text-brand pt-4 border-t border-slate-200">
                            <span>Total:</span>
                            <span>₹${totals.total.toLocaleString('en-IN')}</span>
                        </div>
                    </div>
                </div>
            `;

                lucide.createIcons();
            } catch (renderError) {
                console.error('Error rendering order details:', renderError);
                showError();
            }
        }

        function showError() {
            document.getElementById('order-details-container').style.display = 'none';
            document.getElementById('error-state').style.display = 'block';
        }

        // Copy Order ID
        const copyOrderIdBtn = document.getElementById('copy-order-id-btn');
        if (copyOrderIdBtn) {
            copyOrderIdBtn.addEventListener('click', () => {
                const urlParams = new URLSearchParams(window.location.search);
                const orderId = urlParams.get('order') || urlParams.get('orderId') || urlParams.get('orderNumber');
                if (orderId) {
                    navigator.clipboard.writeText(orderId).then(() => {
                        const btn = document.getElementById('copy-order-id-btn');
                        if (!btn) return;
                        const originalText = btn.innerHTML;
                        btn.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> <span>Copied!</span>';
                        btn.classList.add('bg-green-50', 'border-green-600', 'text-green-600');
                        lucide.createIcons();
                        setTimeout(() => {
                            if (btn) {
                                btn.innerHTML = originalText;
                                btn.classList.remove('bg-green-50', 'border-green-600', 'text-green-600');
                                lucide.createIcons();
                            }
                        }, 2000);
                    }).catch(err => {
                        alert('Failed to copy Order ID');
                    });
                }
            });
        }

        // Initialize
        loadOrderDetails();
        lucide.createIcons();
    </script>

</body>
</html>