<?php
// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in (supports both regular users and admins)
$userId = $_SESSION['user_id'] ?? null;
$adminId = $_SESSION['admin_id'] ?? null;
$isLoggedIn = !empty($userId) || !empty($adminId);

// Redirect to login if not logged in
if (!$isLoggedIn) {
    header('Location: login.php?return=' . urlencode('/orders.php') . '&message=login_required');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <title>My Orders | NAZMI BOUTIQUE</title>
    
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
            
            button, a[role="button"] {
                min-height: 44px;
                min-width: 44px;
            }

            /* Sidebar mobile optimizations */
            .sidebar {
                width: 100%;
                max-width: 100%;
            }

            .sidebar-header {
                padding: 16px 20px;
            }

            .sidebar-title {
                font-size: 1.25rem;
            }

            .sidebar-content {
                padding: 16px 20px;
            }

            .sidebar-footer {
                padding: 16px 20px;
                padding-bottom: max(16px, env(safe-area-inset-bottom));
            }

            .sidebar-checkout {
                padding: 16px;
                font-size: 0.95rem;
            }

            .cart-item {
                padding: 12px 0;
                gap: 12px;
            }

            .cart-item-image {
                width: 70px;
                height: 70px;
            }

            /* Orders page specific */
            .order-card {
                padding: 1rem !important;
            }

            .order-grid {
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

        /* Order Card Hover Effect */
        .order-card {
            transition: all 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(20, 184, 166, 0.15);
        }
    </style>
</head>
<body class="antialiased">
    <!-- Navigation -->
    <?php include 'header.php'; ?>

    <!-- Include Sidebars -->
    <?php include 'includes/sidebars.php'; ?>

    <!-- Orders Section -->
    <div class="pt-6 sm:pt-8 pb-12 sm:pb-16 md:pb-20 min-h-screen bg-white">
        <div class="container mx-auto px-4 lg:px-12">
            <!-- Page Header -->
            <div class="mb-12 md:mb-16">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-6">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="h-[2px] w-8 bg-brand"></span>
                            <span class="text-brand font-bold uppercase tracking-widest text-xs">My Orders</span>
                        </div>
                        <h1 class="text-3xl md:text-5xl font-serif font-black text-slate-900 tracking-tight">Order History</h1>
                        <p class="text-slate-500 mt-2">View and manage all your orders</p>
                    </div>
                    <a href="shop.php" class="px-8 py-4 bg-brand text-white font-black rounded-2xl hover:bg-teal-600 transition-all shadow-xl shadow-brand/20 flex items-center gap-3">
                        <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                        Continue Shopping
                    </a>
                </div>
            </div>

            <!-- Orders List -->
            <div id="orders-container" class="space-y-4 sm:space-y-6">
                <!-- Orders will be populated by JavaScript -->
            </div>

            <!-- Empty State -->
            <div id="empty-orders" class="hidden text-center py-20 md:py-32">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-slate-50 rounded-full mb-8">
                    <i data-lucide="package" class="w-12 h-12 text-slate-300"></i>
                </div>
                <h3 class="text-2xl md:text-3xl font-serif font-black text-slate-900 mb-4">No Orders Yet</h3>
                <p class="text-slate-500 mb-8 max-w-md mx-auto font-medium">You haven't placed any orders yet. Start shopping to see your orders here!</p>
                <a href="shop.php" class="inline-block px-8 py-4 bg-brand text-white font-black rounded-2xl hover:bg-teal-600 transition-all shadow-xl shadow-brand/20">
                    Browse Products
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

    <!-- Cart and Wishlist Scripts -->
    <script src="js/cart-wishlist.js"></script>

    <script>
        // Newsletter subscription
        function subscribeNewsletter() {
            const email = document.getElementById('newsletterEmail')?.value;
            if (email) {
                alert('Thank you for subscribing!');
                document.getElementById('newsletterEmail').value = '';
            }
        }

        // Format price with Indian numbering
        function formatPrice(amount) {
            const num = parseFloat(amount) || 0;
            if (num === 0) return '0';
            return num.toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        }
        
        // Format shipping - show "Free" if 0
        function formatShipping(amount) {
            const num = parseFloat(amount) || 0;
            if (num === 0) return '<span class="text-green-600">Free</span>';
            return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        }

        // Load and Render Orders from API
        async function loadOrders() {
            const ordersContainer = document.getElementById('orders-container');
            const emptyOrders = document.getElementById('empty-orders');

            if (!ordersContainer || !emptyOrders) return;

            // Show loading state
            ordersContainer.innerHTML = `
                <div class="text-center py-16">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-brand border-t-transparent"></div>
                    <p class="mt-6 text-slate-500 font-medium">Loading your orders...</p>
                </div>
            `;
            ordersContainer.classList.remove('hidden');
            emptyOrders.classList.add('hidden');

            try {
                const response = await fetch('/api/v1/orders.php', {
                    method: 'GET',
                    credentials: 'include'
                });
                
                const result = await response.json();
                
                if (!result.success) {
                    if (response.status === 401) {
                        // Redirect to login
                        window.location.href = 'login.php?return=' + encodeURIComponent('/orders.php') + '&message=login_required';
                        return;
                    }
                    throw new Error(result.message || 'Failed to load orders');
                }
                
                const orders = result.data || [];
                
                if (orders.length === 0) {
                    ordersContainer.classList.add('hidden');
                    emptyOrders.classList.remove('hidden');
                    return;
                }

                emptyOrders.classList.add('hidden');
                ordersContainer.classList.remove('hidden');

                ordersContainer.innerHTML = orders.map(order => {
                    const orderDate = new Date(order.order_date);
                    const formattedDate = orderDate.toLocaleDateString('en-IN', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    });
                    const formattedTime = orderDate.toLocaleTimeString('en-IN', { 
                        hour: '2-digit', 
                        minute: '2-digit' 
                    });
                    
                    const itemsCount = order.items_count || 0;
                    const statusColors = {
                        'pending': 'bg-yellow-100 text-yellow-700',
                        'confirmed': 'bg-green-100 text-green-700',
                        'processing': 'bg-blue-100 text-blue-700',
                        'shipped': 'bg-purple-100 text-purple-700',
                        'out_for_delivery': 'bg-indigo-100 text-indigo-700',
                        'delivered': 'bg-gray-100 text-gray-700',
                        'cancelled': 'bg-slate-100 text-slate-700',
                        'refunded': 'bg-orange-100 text-orange-700'
                    };
                    const statusColor = statusColors[order.status] || 'bg-gray-100 text-gray-700';
                    const statusLabel = order.status ? order.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Pending';
                    
                    const paymentStatusColors = {
                        'pending': 'text-yellow-600',
                        'completed': 'text-green-600',
                        'failed': 'text-teal-600',
                        'refunded': 'text-orange-600'
                    };
                    const paymentColor = paymentStatusColors[order.payment_status] || 'text-gray-600';

                    const canCancel = ['pending', 'confirmed'].includes(order.status);
                    const cancelButton = canCancel ? `
                        <button onclick="event.preventDefault(); cancelOrder('${order.order_number}')" class="px-6 py-3 bg-white border-2 border-slate-200 text-slate-700 rounded-2xl hover:bg-slate-50 hover:border-red-300 hover:text-red-600 transition-all font-black text-sm flex items-center gap-2">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            Cancel
                        </button>
                    ` : '';

                    return `
                        <div class="order-card bg-white rounded-3xl shadow-sm border border-slate-100 p-6 md:p-8 hover:shadow-xl transition-all">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                                <div class="flex-1">
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-3">
                                        <h3 class="text-xl md:text-2xl font-serif font-black text-slate-900">${order.order_number || 'N/A'}</h3>
                                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider ${statusColor}">
                                            ${statusLabel}
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-500 font-medium">
                                        <i data-lucide="calendar" class="w-4 h-4 inline mr-1"></i>
                                        ${formattedDate} at ${formattedTime}
                                    </p>
                                </div>
                                <div class="flex gap-3 w-full md:w-auto">
                                    ${cancelButton}
                                    <a href="order-overview.php?order=${order.order_number}" class="flex-1 md:flex-none px-6 py-3 bg-brand text-white rounded-2xl hover:bg-teal-600 transition-all font-black text-sm flex items-center justify-center gap-2 shadow-lg shadow-brand/20">
                                        View Details
                                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-6 border-t border-slate-100">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Items</p>
                                    <p class="text-base font-black text-slate-900">${itemsCount} item${itemsCount !== 1 ? 's' : ''}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Total Amount</p>
                                    <p class="text-base font-black text-brand">₹${formatPrice(order.total || order.total_amount)}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Payment</p>
                                    <p class="text-base font-black text-slate-900">${order.payment_method ? order.payment_method.toUpperCase() : 'N/A'}</p>
                                    <p class="text-xs ${paymentColor} font-bold mt-1">${order.payment_status ? order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1) : ''}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Shipping</p>
                                    <p class="text-base font-black text-slate-900">${formatShipping(order.shipping || order.shipping_cost)}</p>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');

                lucide.createIcons();
                
            } catch (error) {
                console.error('Error loading orders:', error);
                ordersContainer.innerHTML = `
                    <div class="text-center py-16 bg-white rounded-3xl shadow-sm border border-slate-100">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-slate-50 rounded-full mb-6">
                            <i data-lucide="alert-circle" class="w-10 h-10 text-slate-400"></i>
                        </div>
                        <h3 class="text-xl font-serif font-black text-slate-900 mb-3">Failed to load orders</h3>
                        <p class="text-slate-500 mb-6 font-medium">${error.message || 'Please try again later'}</p>
                        <button onclick="loadOrders()" class="px-8 py-4 bg-brand text-white font-black rounded-2xl hover:bg-teal-600 transition-all shadow-xl shadow-brand/20">
                            <i data-lucide="refresh-cw" class="w-5 h-5 inline mr-2"></i>
                            Retry
                        </button>
                    </div>
                `;
                lucide.createIcons();
            }
        }

        // Cancel order function
        async function cancelOrder(orderNumber) {
            const confirmed = await Tivora.confirm(`Are you sure you want to cancel order ${orderNumber}? This action cannot be undone.`);
            if (confirmed) {
                try {
                    const response = await fetch('/api/v1/orders.php', {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ order_number: orderNumber })
                    });
                    const result = await response.json();
                    
                    if (result.success) {
                        await Tivora.alert('Order cancelled successfully.', 'success');
                        loadOrders(); // Reload orders list
                    } else {
                        await Tivora.alert(result.message || 'Failed to cancel order.', 'error');
                    }
                } catch (error) {
                    console.error('Error cancelling order:', error);
                    await Tivora.alert('An error occurred while cancelling your order.', 'error');
                }
            }
        }
        window.cancelOrder = cancelOrder;

        // Initialize
        loadOrders();
        lucide.createIcons();
        
        // Initialize cart/wishlist system
        if (typeof initCartWishlist === 'function') {
            initCartWishlist();
        }
    </script>
</body>
</html>