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
    <title id="page-title">Product | BLine Boutique</title>
    
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
            overflow-x: hidden;
        }
        
        .font-serif {
            font-family: 'Playfair Display', serif;
        }
        
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
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

        .drawer-overlay {
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .drawer {
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* 360 View Simulation Styles */
        #product-view-container {
            cursor: ew-resize;
            touch-action: none;
        }

        .view-360-indicator {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); opacity: 0.8; }
        }

        .size-btn.active {
            background-color: var(--brand-color);
            color: white;
            border-color: var(--brand-color);
        }

        /* Toast Notification */
        #toast {
            transition: all 0.3s ease-out;
        }

        #toast.toast-active {
            bottom: 20px;
            opacity: 1;
        }

        /* Mobile optimizations */
        @media (max-width: 640px) {
            button, a[role="button"] {
                min-height: 44px;
                min-width: 44px;
            }
            
            input, select, textarea {
                font-size: 16px !important;
            }
        }
    </style>
</head>
<body class="bg-white text-slate-800">

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-[-100px] left-1/2 -translate-x-1/2 z-[110] bg-zinc-900 text-white px-6 py-3 rounded-full shadow-2xl flex items-center gap-3 transition-all duration-500 opacity-0">
        <i data-lucide="check" class="text-brand w-5 h-5"></i>
        <span id="toast-message">Added to cart!</span>
    </div>

    <!-- Include Header -->
    <?php include 'header.php'; ?>

    <!-- Include Sidebars -->
    <?php include 'includes/sidebars.php'; ?>

    <!-- Product Detail Section -->
    <main class="container mx-auto px-4 lg:px-12 py-12 md:py-20">
        <div id="product-content" class="flex flex-col lg:flex-row gap-16">
            <!-- Loading state will be shown here -->
        </div>

        <!-- Product Details Section -->
        <div id="product-details-section" class="mt-24 space-y-12 hidden">
            <h3 class="text-2xl font-serif font-black border-b border-slate-100 pb-6">Product Details</h3>
            <div class="grid md:grid-cols-3 gap-12" id="product-details-content">
                <!-- Details will be dynamically added -->
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-50 py-16 mt-20 border-t border-slate-100">
        <div class="container mx-auto px-4 lg:px-12 text-center">
            <h2 class="text-3xl font-serif font-black mb-8">BLine<span class="text-brand">.</span></h2>
            <div class="flex justify-center gap-8 mb-12 text-[10px] font-black uppercase tracking-widest text-slate-400">
                <a href="index.php#about" class="hover:text-brand">About Us</a>
                <a href="index.php#contact" class="hover:text-brand">Shipping</a>
                <a href="#" class="hover:text-brand">Returns</a>
                <a href="#" class="hover:text-brand">Privacy</a>
            </div>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-300">© 2024 BLine Boutique International</p>
        </div>
    </footer>

    <!-- Cart and Wishlist Scripts -->
    <script src="js/cart-wishlist.js"></script>

    <script>
        // Product data loaded from API
        let product = null;
        let isLoading = true;
        let currentFrame = 0;
        let isDragging = false;
        let startX = 0;
        let frameImages = [];

        // Get product ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const productId = urlParams.get('id');
        const productSlug = urlParams.get('slug');

        // Show loading state
        function showLoading() {
            document.getElementById('product-content').innerHTML = `
                <div class="w-full flex flex-col items-center justify-center py-20">
                    <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-brand mb-4"></div>
                    <p class="text-slate-500 text-lg">Loading product details...</p>
                </div>
            `;
        }

        // Show error state
        function showError(message) {
            document.getElementById('product-content').innerHTML = `
                <div class="w-full flex flex-col items-center justify-center py-20">
                    <i data-lucide="alert-circle" class="w-16 h-16 text-red-400 mb-4"></i>
                    <h2 class="text-2xl font-bold text-slate-900 mb-2">Product Not Found</h2>
                    <p class="text-slate-500 mb-6">${message}</p>
                    <a href="shop.php" class="px-6 py-3 bg-brand text-white rounded-xl font-semibold hover:bg-teal-600 transition-colors">
                        Browse All Products
                    </a>
                </div>
            `;
            lucide.createIcons();
        }

        // Load product from API
        async function loadProduct() {
            if (!productId && !productSlug) {
                showError('No product ID provided');
                return;
            }

            showLoading();

            try {
                const apiUrl = productId 
                    ? `api/v1/products.php?id=${productId}`
                    : `api/v1/products.php?slug=${encodeURIComponent(productSlug)}`;
                    
                const response = await fetch(apiUrl);
                const result = await response.json();

                if (result.success && result.data) {
                    product = result.data;
                    renderProduct();
                } else {
                    showError(result.message || 'Product not found');
                }
            } catch (error) {
                console.error('Error loading product:', error);
                showError('Failed to load product details. Please try again.');
            }
        }

        // Render product content
        function renderProduct() {
            // Update page title
            document.getElementById('page-title').textContent = `${product.name} | BLine Boutique`;

            // Get primary image or first image
            const primaryImage = product.primary_image || (product.images && product.images.length > 0 
                ? (typeof product.images[0] === 'string' ? product.images[0] : (product.images[0].url || product.images[0].image_url))
                : 'logo.png');
            
            // Filter and process images
            const allImages = product.images && product.images.length > 0 
                ? product.images
                    .map(img => {
                        if (typeof img === 'string') return img;
                        return img.url || img.image_url || img;
                    })
                    .filter(url => {
                        if (!url) return false;
                        if (url.startsWith('/uploads/') || url.startsWith('uploads/')) {
                            return true;
                        }
                        if (url.startsWith('data:image/') && url.length > 100) {
                            const base64Part = url.split(',')[1];
                            if (base64Part && base64Part.length > 50) {
                                return true;
                            }
                        }
                        return false;
                    })
                : [];
            
            // Ensure we have at least one image
            if (allImages.length === 0) {
                allImages.push(primaryImage || 'logo.png');
            }

            // Create frame images array for 360 view (duplicate images if needed)
            frameImages = allImages.length >= 3 ? allImages : [
                ...allImages,
                ...Array(Math.max(0, 10 - allImages.length)).fill(allImages[0])
            ].slice(0, 10);

            // Calculate discount percentage
            const discountPercentage = product.original_price && product.price < product.original_price
                ? Math.round(((product.original_price - product.price) / product.original_price) * 100)
                : product.discount_percentage || 0;

            // Build product HTML
            const productHTML = `
                <!-- Left: Interactive 360 View -->
                <div class="lg:w-3/5 space-y-6">
                    <div class="relative group bg-slate-50 rounded-[2.5rem] overflow-hidden aspect-[4/5] md:aspect-square flex items-center justify-center">
                        <!-- Instruction Overlay -->
                        <div id="instruction-overlay" class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-black/5 pointer-events-none transition-opacity duration-700">
                            <div class="bg-white/90 backdrop-blur-md p-6 rounded-full shadow-2xl view-360-indicator mb-4">
                                <i data-lucide="rotate-3d" class="w-8 h-8 text-brand"></i>
                            </div>
                            <p class="text-slate-900 font-bold tracking-widest text-xs uppercase bg-white/80 px-4 py-2 rounded-full">Drag to rotate 360°</p>
                        </div>

                        <!-- 360 Image Simulation Container -->
                        <div id="product-view-container" class="w-full h-full flex items-center justify-center select-none">
                            <img id="main-product-image" 
                                 src="${frameImages[0]}" 
                                 alt="${product.name}" 
                                 class="max-h-full w-auto object-contain transition-transform duration-500 scale-100 group-hover:scale-105">
                        </div>

                        <!-- Side Thumbnails -->
                        ${frameImages.length >= 3 ? `
                        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex gap-3 z-30">
                            ${[0, Math.floor(frameImages.length / 3), Math.floor(frameImages.length * 2 / 3)].map((idx, i) => `
                                <button onclick="changeFrame(${idx})" class="w-12 h-12 rounded-xl overflow-hidden border-2 ${i === 0 ? 'border-brand' : 'border-transparent'} p-0.5 bg-white hover:border-brand transition-all">
                                    <img src="${frameImages[idx]}" alt="View ${i + 1}" class="w-full h-full object-cover rounded-lg">
                                </button>
                            `).join('')}
                        </div>
                        ` : ''}
                    </div>
                </div>

                <!-- Right: Product Info -->
                <div class="lg:w-2/5 flex flex-col justify-center space-y-10">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 flex-wrap">
                            ${product.is_new ? `
                                <span class="text-brand font-black text-xs uppercase tracking-widest bg-brand/10 px-3 py-1 rounded-full">New Arrival</span>
                            ` : ''}
                            ${discountPercentage > 0 ? `
                                <span class="text-brand font-black text-xs uppercase tracking-widest bg-brand/10 px-3 py-1 rounded-full">${discountPercentage}% OFF</span>
                            ` : ''}
                            ${product.rating ? `
                                <div class="flex text-yellow-400">
                                    ${Array(5).fill(0).map((_, i) => {
                                        const rating = product.rating || 0;
                                        if (i < Math.floor(rating)) {
                                            return '<i data-lucide="star" class="w-4 h-4 fill-current"></i>';
                                        } else if (i < rating) {
                                            return '<i data-lucide="star-half" class="w-4 h-4 fill-current"></i>';
                                        } else {
                                            return '<i data-lucide="star" class="w-4 h-4"></i>';
                                        }
                                    }).join('')}
                                    <span class="text-slate-400 text-xs font-bold ml-2">(${product.review_count || 0} Reviews)</span>
                                </div>
                            ` : ''}
                        </div>
                        <h1 class="text-4xl md:text-5xl font-serif font-black text-slate-900 leading-tight">${product.name}</h1>
                        <div class="flex items-baseline gap-4">
                            <span class="text-3xl font-black text-slate-900">₹${product.price.toLocaleString('en-IN')}</span>
                            ${product.original_price ? `
                                <span class="text-lg text-slate-400 line-through">₹${product.original_price.toLocaleString('en-IN')}</span>
                            ` : ''}
                            ${discountPercentage > 0 ? `
                                <span class="text-brand font-bold">(${discountPercentage}% OFF)</span>
                            ` : ''}
                        </div>
                    </div>

                    ${product.short_description || product.full_description ? `
                    <p class="text-slate-500 leading-relaxed font-medium">
                        ${product.short_description || product.full_description.substring(0, 200) + '...'}
                    </p>
                    ` : ''}

                    <!-- Selection -->
                    <div class="space-y-8">
                        ${product.variants && product.variants.length > 0 ? `
                        <div>
                            <h4 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4">Select ${product.variants[0].name || 'Variant'}</h4>
                            <div class="flex flex-wrap gap-3">
                                ${product.variants.map((variant, idx) => `
                                    <button onclick="selectVariant('${variant.id}', this)" class="size-btn px-6 py-3 border border-slate-200 rounded-xl font-bold hover:border-brand transition-all ${idx === 0 ? 'active' : ''}" data-variant-id="${variant.id}">
                                        ${variant.name || variant.value}
                                    </button>
                                `).join('')}
                            </div>
                        </div>
                        ` : ''}

                        <!-- Stock Status -->
                        <div class="flex items-center gap-2">
                            ${product.in_stock ? `
                                <span class="flex items-center gap-2 text-green-600">
                                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                                    <span class="font-medium">In Stock</span>
                                    ${product.stock_quantity && product.stock_quantity <= (product.low_stock_threshold || 10) ? `
                                        <span class="text-orange-500 text-sm">(Only ${product.stock_quantity} left)</span>
                                    ` : ''}
                                </span>
                            ` : `
                                <span class="flex items-center gap-2 text-red-600">
                                    <i data-lucide="x-circle" class="w-5 h-5"></i>
                                    <span class="font-medium">Out of Stock</span>
                                </span>
                            `}
                        </div>

                        <!-- Quantity Selector -->
                        ${product.in_stock ? `
                        <div class="flex items-center gap-4">
                            <label class="font-semibold text-slate-900">Quantity:</label>
                            <div class="flex items-center gap-3 border border-slate-200 rounded-lg">
                                <button id="quantity-minus" class="p-2 hover:bg-slate-100 transition-colors">
                                    <i data-lucide="minus" class="w-4 h-4"></i>
                                </button>
                                <span id="quantity-value" class="px-4 py-2 font-semibold min-w-[50px] text-center">1</span>
                                <button id="quantity-plus" class="p-2 hover:bg-slate-100 transition-colors">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                        ` : ''}

                        <div class="flex flex-col sm:flex-row gap-4 pt-4">
                            <button id="add-to-cart-product" 
                                class="add-to-cart-btn flex-[2] bg-brand text-white py-5 rounded-2xl font-black text-lg shadow-xl shadow-brand/20 hover:bg-teal-600 transition-all flex items-center justify-center gap-3 ${!product.in_stock ? 'opacity-50 cursor-not-allowed' : ''}"
                                data-product-id="${product.id}"
                                data-product-name="${product.name}"
                                ${!product.in_stock ? 'disabled' : ''}>
                                <i data-lucide="shopping-bag" class="w-6 h-6"></i>
                                <span class="btn-text">${product.in_stock ? 'ADD TO BAG' : 'OUT OF STOCK'}</span>
                            </button>
                            <button id="wishlist-product" 
                                class="wishlist-btn flex-1 bg-white border-2 border-slate-900 text-slate-900 py-5 rounded-2xl font-black text-lg hover:bg-slate-900 hover:text-white transition-all flex items-center justify-center gap-3"
                                data-product-id="${product.id}"
                                data-product-name="${product.name}">
                                <i data-lucide="heart" class="w-6 h-6"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Product Features -->
                    <div class="grid grid-cols-2 gap-6 pt-8 border-t border-slate-100">
                        <div class="flex items-start gap-3">
                            <i data-lucide="truck" class="text-brand w-5 h-5 mt-1"></i>
                            <div>
                                <h5 class="text-sm font-bold">Free Delivery</h5>
                                <p class="text-xs text-slate-400">On orders above ₹2,500</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <i data-lucide="shield-check" class="text-brand w-5 h-5 mt-1"></i>
                            <div>
                                <h5 class="text-sm font-bold">${product.warranty_months || 12} Months Warranty</h5>
                                <p class="text-xs text-slate-400">Quality guaranteed</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('product-content').innerHTML = productHTML;

            // Render product details
            renderProductDetails();

            // Initialize icons and listeners
            lucide.createIcons();
            setup360View();
            setupEventListeners();
            
            // Update cart/wishlist states
            if (typeof window.updateProductButtonStates === 'function') {
                window.updateProductButtonStates();
            }
        }

        // Render product details section
        function renderProductDetails() {
            const detailsSection = document.getElementById('product-details-section');
            const detailsContent = document.getElementById('product-details-content');
            
            if (!product.full_description && (!product.specifications || Object.keys(product.specifications).length === 0)) {
                detailsSection.classList.add('hidden');
                return;
            }

            detailsSection.classList.remove('hidden');

            let detailsHTML = '';

            // Description
            if (product.full_description) {
                detailsHTML += `
                    <div class="space-y-4">
                        <h4 class="font-black text-xs uppercase tracking-widest text-brand">Description</h4>
                        <p class="text-sm text-slate-500 leading-relaxed font-medium">${product.full_description}</p>
                    </div>
                `;
            }

            // Specifications
            if (product.specifications && Object.keys(product.specifications).length > 0) {
                const specEntries = Object.entries(product.specifications).slice(0, 6);
                detailsHTML += `
                    <div class="space-y-4">
                        <h4 class="font-black text-xs uppercase tracking-widest text-brand">Specifications</h4>
                        <ul class="text-sm text-slate-500 space-y-2 font-medium">
                            ${specEntries.map(([key, value]) => `
                                <li class="flex items-center gap-2">
                                    <i data-lucide="minus" class="w-3 h-3 text-brand"></i>
                                    <span><strong>${key}:</strong> ${value}</span>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                `;
            }

            // Features
            if (product.features && product.features.length > 0) {
                detailsHTML += `
                    <div class="space-y-4">
                        <h4 class="font-black text-xs uppercase tracking-widest text-brand">Features</h4>
                        <ul class="text-sm text-slate-500 space-y-2 font-medium">
                            ${product.features.map(feature => `
                                <li class="flex items-center gap-2">
                                    <i data-lucide="check" class="w-3 h-3 text-brand"></i>
                                    ${feature.text || feature}
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                `;
            }

            detailsContent.innerHTML = detailsHTML;
            lucide.createIcons();
        }

        // Setup 360 view
        function setup360View() {
            const container = document.getElementById('product-view-container');
            const mainImage = document.getElementById('main-product-image');
            const overlay = document.getElementById('instruction-overlay');
            
            if (!container || !mainImage || frameImages.length === 0) return;

            // Mouse events
            container.addEventListener('mousedown', (e) => {
                isDragging = true;
                startX = e.pageX;
                if (overlay) overlay.style.opacity = '0';
            });

            window.addEventListener('mouseup', () => {
                isDragging = false;
            });

            window.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                const x = e.pageX;
                const diff = x - startX;
                const threshold = 50;

                if (Math.abs(diff) > threshold) {
                    if (diff > 0) {
                        currentFrame = (currentFrame + 1) % frameImages.length;
                    } else {
                        currentFrame = (currentFrame - 1 + frameImages.length) % frameImages.length;
                    }
                    mainImage.src = frameImages[currentFrame];
                    startX = x;
                }
            });

            // Touch events
            container.addEventListener('touchstart', (e) => {
                isDragging = true;
                startX = e.touches[0].pageX;
                if (overlay) overlay.style.opacity = '0';
            });

            window.addEventListener('touchend', () => {
                isDragging = false;
            });

            window.addEventListener('touchmove', (e) => {
                if (!isDragging) return;
                const x = e.touches[0].pageX;
                const diff = x - startX;
                const threshold = 30;

                if (Math.abs(diff) > threshold) {
                    if (diff > 0) {
                        currentFrame = (currentFrame + 1) % frameImages.length;
                    } else {
                        currentFrame = (currentFrame - 1 + frameImages.length) % frameImages.length;
                    }
                    mainImage.src = frameImages[currentFrame];
                    startX = x;
                }
            });
        }

        // Change frame
        function changeFrame(index) {
            currentFrame = index;
            const mainImage = document.getElementById('main-product-image');
            if (mainImage) {
                mainImage.src = frameImages[currentFrame];
            }
            const overlay = document.getElementById('instruction-overlay');
            if (overlay) overlay.style.opacity = '0';
        }

        // Select variant
        function selectVariant(variantId, btn) {
            document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            // Store selected variant for cart
            product.selectedVariantId = variantId;
        }

        // Setup event listeners
        function setupEventListeners() {
            // Quantity management
            let quantity = 1;
            const maxQuantity = product.stock_quantity || 10;
            
            const minusBtn = document.getElementById('quantity-minus');
            const plusBtn = document.getElementById('quantity-plus');
            const quantityValue = document.getElementById('quantity-value');
            
            if (minusBtn && quantityValue) {
                minusBtn.addEventListener('click', () => {
                    if (quantity > 1) {
                        quantity--;
                        quantityValue.textContent = quantity;
                    }
                });
            }
            
            if (plusBtn && quantityValue) {
                plusBtn.addEventListener('click', () => {
                    if (quantity < maxQuantity) {
                        quantity++;
                        quantityValue.textContent = quantity;
                    }
                });
            }

            // Product page add to cart button
            const productAddToCartBtn = document.getElementById('add-to-cart-product');
            if (productAddToCartBtn && product.in_stock) {
                productAddToCartBtn.onclick = function(e) {
                    e.preventDefault();
                    const qty = parseInt(document.getElementById('quantity-value')?.textContent || 1);
                    if (this.getAttribute('data-in-cart') === 'true') {
                        if (typeof toggleDrawer === 'function') toggleDrawer('cart');
                    } else {
                        if (typeof addToCart === 'function') {
                            addToCart(product.id, product.name, qty);
                            showToast('Added to your bag!');
                        }
                    }
                };
            }

            // Product page wishlist button
            const productWishlistBtn = document.getElementById('wishlist-product');
            if (productWishlistBtn) {
                productWishlistBtn.onclick = function(e) {
                    e.preventDefault();
                    if (typeof toggleWishlist === 'function') {
                        toggleWishlist(product.id, product.name);
                        showToast('Added to wishlist!');
                    }
                };
            }
        }

        // Toast notification
        function showToast(msg) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            if (toast && toastMessage) {
                toastMessage.textContent = msg;
                toast.classList.add('toast-active');
                toast.style.opacity = "1";
                setTimeout(() => {
                    toast.classList.remove('toast-active');
                    toast.style.opacity = "0";
                }, 3000);
            }
        }

        // Make functions globally available
        window.changeFrame = changeFrame;
        window.selectVariant = selectVariant;
        window.showToast = showToast;

        // Initialize Lucide Icons
        lucide.createIcons();

        // Load product on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadProduct();
            
            // Initialize cart/wishlist system
            if (typeof initCartWishlist === 'function') {
                initCartWishlist();
            }
        });
    </script>
</body>
</html>
