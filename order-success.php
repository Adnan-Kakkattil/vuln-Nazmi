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
    <title>Order Success | BLine Boutique</title>
    
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

            /* Order success specific */
            .success-icon {
                width: 80px !important;
                height: 80px !important;
            }

            .order-card {
                padding: 1rem !important;
            }

            .order-details-grid {
                grid-template-columns: 1fr !important;
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

        /* Success Animation */
        .success-animation {
            animation: scaleIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }

        /* Card Hover Effects */
        .info-card {
            transition: all 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(20, 184, 166, 0.15);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <?php include 'header.php'; ?>

    <!-- Include Sidebars -->
    <?php include 'includes/sidebars.php'; ?>

    <!-- Success Content -->
    <section class="pt-6 sm:pt-8 pb-12 sm:pb-16 md:pb-20 lg:pb-32 min-h-screen bg-white">
        <div class="container mx-auto px-4 lg:px-12">
            <!-- Success Icon Card -->
            <div class="text-center mb-16 fade-in-up">
                <div class="inline-flex items-center justify-center w-32 h-32 bg-gradient-to-br from-brand/10 to-brand/5 rounded-full mb-8 success-animation">
                    <i data-lucide="check-circle" class="w-20 h-20 text-brand"></i>
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-serif font-black text-slate-900 mb-6">Order Placed Successfully!</h1>
                <p class="text-lg md:text-xl text-slate-600 mb-2 font-medium">Thank you for your purchase</p>
                <p class="text-sm text-slate-500">We've sent you a confirmation email with order details</p>
            </div>

            <!-- Order ID Card -->
            <div class="bg-white rounded-3xl shadow-xl border-2 border-brand/20 p-8 md:p-10 mb-8 fade-in-up delay-100">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div class="flex items-center gap-6">
                        <div class="w-16 h-16 bg-brand/10 rounded-full flex items-center justify-center flex-shrink-0">
                            <i data-lucide="receipt" class="w-8 h-8 text-brand"></i>
                        </div>
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Order ID</p>
                            <p id="order-id" class="text-2xl md:text-3xl font-serif font-black text-slate-900">ORD-1768635308085</p>
                        </div>
                    </div>
                    <button onclick="copyOrderId()" class="px-6 py-3 bg-white border-2 border-brand text-brand rounded-2xl hover:bg-brand/5 transition-colors flex items-center gap-2 font-black text-sm">
                        <i data-lucide="copy" class="w-5 h-5"></i>
                        Copy ID
                    </button>
                </div>
            </div>

            <!-- Order Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 mb-8 md:mb-12">
                <!-- Delivery Address -->
                <div class="bg-white rounded-3xl shadow-lg border border-slate-100 p-8 info-card fade-in-up delay-200">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-brand/10 rounded-full flex items-center justify-center">
                            <i data-lucide="map-pin" class="w-6 h-6 text-brand"></i>
                        </div>
                        <h3 class="text-xl font-serif font-black text-slate-900">Delivery Address</h3>
                    </div>
                    <div id="delivery-address" class="text-sm text-slate-600 space-y-2 font-medium">
                        <p class="font-black text-slate-900 text-base"></p>
                        <p></p>
                        <p></p>
                        <p></p>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="bg-white rounded-3xl shadow-lg border border-slate-100 p-8 info-card fade-in-up delay-200">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-brand/10 rounded-full flex items-center justify-center">
                            <i data-lucide="credit-card" class="w-6 h-6 text-brand"></i>
                        </div>
                        <h3 class="text-xl font-serif font-black text-slate-900">Payment Method</h3>
                    </div>
                    <div id="payment-method" class="text-sm text-slate-600">
                        <p class="font-black text-slate-900 text-base"></p>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="bg-white rounded-3xl shadow-lg border border-slate-100 p-8 info-card fade-in-up delay-300">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-brand/10 rounded-full flex items-center justify-center">
                            <i data-lucide="package" class="w-6 h-6 text-brand"></i>
                        </div>
                        <h3 class="text-xl font-serif font-black text-slate-900">Order Summary</h3>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-slate-600 font-medium">
                            <span>Items</span>
                            <span id="order-items-count" class="font-black text-slate-900">0</span>
                        </div>
                        <div class="flex justify-between text-slate-600 font-medium">
                            <span>Subtotal</span>
                            <span id="order-subtotal" class="font-black text-slate-900">₹0</span>
                        </div>
                        <div class="flex justify-between text-slate-600 font-medium">
                            <span id="order-tax-label">Tax (GST)</span>
                            <span id="order-tax" class="font-black text-slate-900">₹0</span>
                        </div>
                        <div class="pt-4 border-t border-slate-200">
                            <div class="flex justify-between">
                                <span class="font-black text-slate-900 text-lg">Total</span>
                                <span id="order-total" class="text-2xl font-serif font-black text-brand">₹0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estimated Delivery -->
                <div class="bg-white rounded-3xl shadow-lg border border-slate-100 p-8 info-card fade-in-up delay-300">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-brand/10 rounded-full flex items-center justify-center">
                            <i data-lucide="truck" class="w-6 h-6 text-brand"></i>
                        </div>
                        <h3 class="text-xl font-serif font-black text-slate-900">Estimated Delivery</h3>
                    </div>
                    <div class="text-sm text-slate-600">
                        <p class="font-black text-slate-900 text-base mb-2" id="delivery-date"></p>
                        <p class="text-slate-500 font-medium">We'll send you tracking details soon</p>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-3xl shadow-lg border border-slate-100 p-8 md:p-10 mb-12 fade-in-up delay-400">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 bg-brand/10 rounded-full flex items-center justify-center">
                        <i data-lucide="shopping-bag" class="w-6 h-6 text-brand"></i>
                    </div>
                    <h3 class="text-2xl font-serif font-black text-slate-900">Order Items</h3>
                </div>
                <div id="order-items-list" class="space-y-6">
                    <!-- Items will be populated by JavaScript -->
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center fade-in-up delay-400">
                <a href="index.php" class="px-8 py-4 bg-white border-2 border-brand text-brand font-black rounded-2xl hover:bg-brand/5 transition-all shadow-lg hover:shadow-xl text-center flex items-center justify-center gap-3">
                    <i data-lucide="home" class="w-5 h-5"></i>
                    Back to Home
                </a>
                <a href="shop.php" class="px-8 py-4 bg-brand text-white font-black rounded-2xl hover:bg-teal-600 transition-all shadow-xl shadow-brand/20 hover:shadow-2xl transform hover:scale-105 text-center flex items-center justify-center gap-3">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                    Continue Shopping
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white pt-24 pb-12 border-t border-slate-100">
        <div class="container mx-auto px-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-16 mb-24">
                <div class="space-y-8">
                    <h2 class="text-4xl font-serif font-black">BLine<span class="text-brand">.</span></h2>
                    <p class="text-slate-500 leading-relaxed font-medium">
                        Designing for the bold, the elegant, and the authentic. BLine Boutique is your destination for premium artisanal apparel.
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
                <p>© 2024 BLine Boutique International</p>
                <div class="flex gap-4 opacity-40">
                    <img src="https://cdn-icons-png.flaticon.com/512/349/349221.png" alt="Visa" class="h-4">
                    <img src="https://cdn-icons-png.flaticon.com/512/349/349230.png" alt="Mastercard" class="h-4">
                </div>
            </div>
        </div>
    </footer>

    <script>
        // API Base URL
        const API_BASE = 'api/v1';

        // Copy Order ID
        function copyOrderId() {
            const orderId = document.getElementById('order-id').textContent;
            navigator.clipboard.writeText(orderId).then(() => {
                alert('Order ID copied to clipboard!');
            });
        }

        // Format currency
        function formatCurrency(amount) {
            return '₹' + parseFloat(amount).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Load order data from API
        async function loadOrderData() {
            const urlParams = new URLSearchParams(window.location.search);
            const orderNumber = urlParams.get('order') || urlParams.get('orderId');
            
            if (!orderNumber) {
                showOrderError('No order number provided');
                return;
            }
            
            // Set Order ID immediately
            const orderIdEl = document.getElementById('order-id');
            if (orderIdEl) orderIdEl.textContent = orderNumber;

            try {
                const response = await fetch(`${API_BASE}/orders.php?order_number=${encodeURIComponent(orderNumber)}`);
                const result = await response.json();
                
                if (result.success && result.data) {
                    renderOrderDetails(result.data);
                } else {
                    showOrderError(result.message || 'Order not found');
                }
            } catch (error) {
                console.error('Error loading order:', error);
                showOrderError('Failed to load order details');
            }
        }

        // Render order details
        function renderOrderDetails(order) {
            // Delivery Address
            const deliveryAddress = document.getElementById('delivery-address');
            if (deliveryAddress && order.customer && order.shipping_address) {
                const addr = order.shipping_address;
                deliveryAddress.innerHTML = `
                    <p class="font-black text-slate-900 text-base">${order.customer.first_name || ''} ${order.customer.last_name || ''}</p>
                    <p class="font-medium">${addr.address_line1 || ''}${addr.address_line2 ? ', ' + addr.address_line2 : ''}</p>
                    <p class="font-medium">${addr.city || ''}, ${addr.state || ''} ${addr.pincode || ''}</p>
                    <p class="font-medium">${addr.country || 'India'}</p>
                `;
            }

            // Payment Method
            const paymentMethodEl = document.getElementById('payment-method');
            if (paymentMethodEl && order.payment_method) {
                const methodNames = {
                    'online': 'Online Payment (Razorpay)',
                    'upi': 'UPI Payment',
                    'card': 'Credit/Debit Card',
                    'cod': 'Cash on Delivery'
                };
                paymentMethodEl.innerHTML = `
                    <p class="font-black text-slate-900 text-base">${methodNames[order.payment_method] || order.payment_method}</p>
                    <p class="text-sm text-slate-500 mt-2 font-medium">Status: <span class="capitalize font-bold">${order.payment_status || 'pending'}</span></p>
                `;
            }

            // Order Summary
            if (order.totals) {
                const itemsCount = order.items ? order.items.reduce((sum, item) => sum + item.quantity, 0) : 0;
                const itemsCountEl = document.getElementById('order-items-count');
                const subtotalEl = document.getElementById('order-subtotal');
                const taxEl = document.getElementById('order-tax');
                const taxLabelEl = document.getElementById('order-tax-label');
                const totalEl = document.getElementById('order-total');
                
                // Get values from order totals
                const subtotal = parseFloat(order.totals.subtotal || 0);
                const taxAmount = parseFloat(order.totals.tax || order.totals.tax_amount || 0);
                const taxRate = parseFloat(order.totals.tax_rate || order.tax_rate || 18);
                const total = parseFloat(order.totals.total || order.totals.total_amount || 0);
                
                if (itemsCountEl) itemsCountEl.textContent = itemsCount;
                if (subtotalEl) subtotalEl.textContent = formatCurrency(subtotal);
                
                // Display tax with dynamic rate from order data
                if (taxLabelEl) {
                    // Show tax label with actual rate from order
                    if (taxAmount > 0 && taxRate > 0) {
                        taxLabelEl.textContent = `Tax (GST ${taxRate}%)`;
                    } else {
                        taxLabelEl.textContent = 'Tax (GST)';
                    }
                }
                
                if (taxEl) taxEl.textContent = formatCurrency(taxAmount);
                if (totalEl) totalEl.textContent = formatCurrency(total);
            }

            // Order Items
            const orderItemsList = document.getElementById('order-items-list');
            if (orderItemsList && order.items && order.items.length > 0) {
                orderItemsList.innerHTML = order.items.map(item => {
                    const imageUrl = item.image && item.image.startsWith('/') 
                        ? item.image 
                        : (item.image || 'logo.png');
                    
                    return `
                        <div class="flex gap-6 pb-6 border-b border-slate-100 last:border-0">
                            <img src="${imageUrl}" alt="${item.name}" class="w-24 h-24 md:w-32 md:h-32 object-cover rounded-2xl border border-slate-100" onerror="this.src='logo.png'">
                            <div class="flex-1">
                                <h4 class="font-black text-slate-900 mb-2 text-lg">${item.name}</h4>
                                <p class="text-sm text-slate-500 mb-3 font-medium">Quantity: ${item.quantity}</p>
                                <p class="text-brand font-serif font-black text-xl">${formatCurrency(item.total_price)}</p>
                            </div>
                        </div>
                    `;
                }).join('');
            } else if (orderItemsList) {
                orderItemsList.innerHTML = '<p class="text-slate-500 text-center py-12 font-medium">No items found</p>';
            }

            // Estimated Delivery Date
            const deliveryDateEl = document.getElementById('delivery-date');
            if (deliveryDateEl) {
                // Calculate delivery date based on shipping method
                const daysToAdd = order.shipping_method?.code === 'express' ? 2 : 5;
                const deliveryDate = new Date();
                deliveryDate.setDate(deliveryDate.getDate() + daysToAdd);
                const formattedDate = deliveryDate.toLocaleDateString('en-IN', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
                deliveryDateEl.textContent = formattedDate;
            }
            
            // Re-initialize icons
            lucide.createIcons();
        }

        // Show error state
        function showOrderError(message) {
            const orderItemsList = document.getElementById('order-items-list');
            if (orderItemsList) {
                orderItemsList.innerHTML = `
                    <div class="text-center py-12">
                        <i data-lucide="alert-circle" class="w-16 h-16 mx-auto mb-4 text-slate-300"></i>
                        <p class="text-slate-500 font-medium mb-6">${message}</p>
                        <a href="shop.php" class="inline-block px-8 py-4 bg-brand text-white rounded-2xl hover:bg-teal-600 transition-all font-black shadow-xl shadow-brand/20">
                            Continue Shopping
                        </a>
                    </div>
                `;
                lucide.createIcons();
            }
        }
        
        // Newsletter subscription
        function subscribeNewsletter() {
            const email = document.getElementById('newsletterEmail')?.value;
            if (email) {
                alert('Thank you for subscribing!');
                document.getElementById('newsletterEmail').value = '';
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadOrderData();
            lucide.createIcons();
        });
    </script>
</body>
</html>