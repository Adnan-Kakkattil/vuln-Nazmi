<?php
// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) || 
              (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']));
$isAdmin = isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);

// Get user info
if ($isAdmin) {
    $userName = $_SESSION['admin_name'] ?? '';
    $userEmail = $_SESSION['admin_email'] ?? '';
} else {
    $userName = $_SESSION['user_name'] ?? '';
    $userEmail = $_SESSION['user_email'] ?? '';
}

// Get search query from URL
$searchQuery = $_GET['search'] ?? '';
$filterCategory = $_GET['filter'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop All Products | BLine Boutique</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="shortcut icon" type="image/png" href="logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="js/cart-wishlist.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        :root {
            --brand-color: #14b8a6;
        }

        .bg-brand { background-color: var(--brand-color); }
        .text-brand { color: var(--brand-color); }
        .border-brand { border-color: var(--brand-color); }
        .ring-brand { --tw-ring-color: var(--brand-color); }

        .product-card:hover .quick-add {
            transform: translateY(0);
            opacity: 1;
            z-index: 10;
        }
        
        .quick-add {
            z-index: 10;
        }
        
        .quick-add button {
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: visible;
        }

        /* Drawer Styles */
        .drawer-overlay {
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .drawer {
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out forwards;
        }

        /* Skeleton Loader Styles */
        @keyframes skeleton-pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        @keyframes skeleton-shimmer {
            0% {
                background-position: -1000px 0;
            }
            100% {
                background-position: 1000px 0;
            }
        }

        .skeleton {
            animation: skeleton-pulse 1.5s ease-in-out infinite;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeleton-shimmer 1.5s infinite;
            border-radius: 8px;
        }

        .skeleton-product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        .skeleton-image {
            aspect-ratio: 3/4;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeleton-shimmer 1.5s infinite;
        }

        .skeleton-text {
            height: 16px;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeleton-shimmer 1.5s infinite;
            border-radius: 4px;
        }

        .skeleton-text-sm {
            height: 12px;
        }

        .skeleton-text-lg {
            height: 20px;
        }

        .skeleton-filter {
            height: 40px;
            width: 150px;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeleton-shimmer 1.5s infinite;
            border-radius: 8px;
        }
    </style>
</head>
<body class="bg-white text-slate-800">

    <?php include 'header.php'; ?>

    <!-- Main Content -->
    <main class="py-12 md:py-24 bg-white">
        <div class="container mx-auto px-4 lg:px-12">
            
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="h-[2px] w-8 bg-brand"></span>
                        <span class="text-brand font-bold uppercase tracking-widest text-xs">Shop Collection</span>
                    </div>
                    <h1 class="text-3xl md:text-5xl font-black text-slate-900 tracking-tight">All Products</h1>
                    <p id="shopSearchContext" class="text-slate-500 mt-2">Discover our complete range of premium fashion</p>
                </div>
            </div>

            <!-- Filters and Sort -->
            <div class="mb-12 space-y-6">
                <!-- Mobile Search -->
                <div class="lg:hidden">
                    <div class="relative">
                        <input 
                            id="searchInputMobile"
                            type="text" 
                            placeholder="Search products..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-full py-3.5 pl-6 pr-12 focus:ring-2 focus:ring-brand/20 focus:border-brand outline-none transition-all text-sm"
                            value="<?php echo htmlspecialchars($searchQuery); ?>"
                        >
                        <div class="absolute right-2 top-1/2 -translate-y-1/2 bg-brand p-2.5 rounded-full text-white cursor-pointer hover:bg-teal-600" onclick="handleSearch()">
                            <i data-lucide="search" class="w-[18px] h-[18px]"></i>
                        </div>
                    </div>
                </div>

                <!-- Filters Row -->
                <div class="flex flex-wrap items-center gap-4 border-b border-slate-200 pb-4">
                    <div class="flex items-center gap-2">
                        <i data-lucide="sliders-horizontal" class="w-4.5 h-4.5 text-slate-400"></i>
                        <span class="text-sm font-bold text-slate-600">Filters:</span>
                    </div>
                    
                    <select id="filter-category" class="bg-transparent border border-slate-200 rounded-lg px-4 py-2 text-sm font-bold focus:ring-1 focus:ring-brand outline-none cursor-pointer text-slate-600">
                        <option value="">All Categories</option>
                    </select>

                    <select id="filter-price" class="bg-transparent border border-slate-200 rounded-lg px-4 py-2 text-sm font-bold focus:ring-1 focus:ring-brand outline-none cursor-pointer text-slate-600">
                        <option value="">All Prices</option>
                        <option value="0-10000">Under ₹10,000</option>
                        <option value="10000-20000">₹10,000 - ₹20,000</option>
                        <option value="20000-35000">₹20,000 - ₹35,000</option>
                        <option value="35000-50000">₹35,000 - ₹50,000</option>
                        <option value="50000+">Above ₹50,000</option>
                    </select>

                    <label class="flex items-center gap-2 cursor-pointer px-4 py-2 border border-slate-200 rounded-lg hover:border-brand transition-colors bg-white">
                        <input type="checkbox" id="filter-stock" class="w-4 h-4 text-brand border-slate-300 rounded focus:ring-brand">
                        <span class="text-slate-600 text-sm font-bold">In Stock</span>
                    </label>

                    <button id="clear-filters" class="px-4 py-2 text-slate-400 hover:text-brand transition-colors text-sm font-bold flex items-center gap-2">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                        Clear
                    </button>

                    <div class="ml-auto flex items-center gap-4 border-l border-slate-200 pl-4">
                        <span class="text-sm font-bold text-slate-400">Sort:</span>
                        <select id="sortSelect" class="bg-transparent border-none text-sm font-bold focus:ring-0 outline-none cursor-pointer text-slate-600">
                            <option value="popularity">Popularity</option>
                            <option value="newest">Newest First</option>
                            <option value="price_low">Price (Low - High)</option>
                            <option value="price_high">Price (High - Low)</option>
                            <option value="name">Name (A - Z)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Product Grid -->
            <div id="productGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-x-4 md:gap-x-8 gap-y-12">
                <!-- Skeleton Loaders (shown initially) -->
                <div id="productSkeletons" class="col-span-full grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-x-4 md:gap-x-8 gap-y-12">
                    <!-- Skeleton cards will be generated by JS -->
                </div>
            </div>

            <!-- Results Count and Load More -->
            <div id="loadMoreSection" class="mt-24 border-t border-slate-100 pt-16 flex flex-col items-center hidden">
                <div class="flex items-center gap-4 mb-8">
                    <span id="resultsText" class="text-sm font-bold text-slate-400 italic">Showing 15 of 450 items</span>
                    <div class="w-48 h-1 bg-slate-100 rounded-full overflow-hidden">
                        <div id="progressBar" class="h-full bg-brand transition-all duration-500"></div>
                    </div>
                </div>
                <button onclick="loadMoreProducts()" class="group relative px-12 py-5 overflow-hidden rounded-full border-2 border-slate-900 font-black text-lg transition-all hover:text-white">
                    <span class="absolute inset-0 w-0 bg-slate-900 transition-all duration-500 ease-out group-hover:w-full"></span>
                    <span class="relative">LOAD MORE PRODUCTS</span>
                </button>
            </div>
        </div>
    </main>

    <!-- Trust Badges -->
    <section class="bg-slate-50 py-20 border-y border-slate-100">
        <div class="container mx-auto px-12 grid grid-cols-2 lg:grid-cols-4 gap-12">
            <div class="flex flex-col items-center text-center group">
                <i data-lucide="map-pin" class="text-brand w-8 h-8 mb-6 group-hover:scale-110 transition-transform"></i>
                <h5 class="font-black text-lg mb-2 uppercase tracking-tighter">Global Shipping</h5>
                <p class="text-sm text-slate-500 font-medium">Express delivery to 150+ countries</p>
            </div>
            <div class="flex flex-col items-center text-center group">
                <i data-lucide="shopping-bag" class="text-brand w-8 h-8 mb-6 group-hover:scale-110 transition-transform"></i>
                <h5 class="font-black text-lg mb-2 uppercase tracking-tighter">Secure Checkout</h5>
                <p class="text-sm text-slate-500 font-medium">100% safe payment processing</p>
            </div>
            <div class="flex flex-col items-center text-center group">
                <i data-lucide="check" class="text-brand w-8 h-8 mb-6 group-hover:scale-110 transition-transform"></i>
                <h5 class="font-black text-lg mb-2 uppercase tracking-tighter">Authentic Goods</h5>
                <p class="text-sm text-slate-500 font-medium">Curated designer collections only</p>
            </div>
            <div class="flex flex-col items-center text-center group">
                <i data-lucide="phone" class="text-brand w-8 h-8 mb-6 group-hover:scale-110 transition-transform"></i>
                <h5 class="font-black text-lg mb-2 uppercase tracking-tighter">24/7 Concierge</h5>
                <p class="text-sm text-slate-500 font-medium">Dedicated styling assistants</p>
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
                        <li onclick="setCategory('New Arrivals')" class="text-sm font-bold text-slate-600 hover:text-brand transition-colors cursor-pointer">New Arrivals</li>
                        <li onclick="setCategory('Best Sellers')" class="text-sm font-bold text-slate-600 hover:text-brand transition-colors cursor-pointer">Best Sellers</li>
                        <li onclick="setCategory('Ethnic Wear')" class="text-sm font-bold text-slate-600 hover:text-brand transition-colors cursor-pointer">Ethnic Wear</li>
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

    <!-- Include Sidebars -->
    <?php include 'includes/sidebars.php'; ?>

    <script>
        function getQueryParam(name) {
            try {
                const p = new URLSearchParams(window.location.search);
                const v = p.get(name);
                if (v === null || v === '') return '';
                return decodeURIComponent(v.replace(/\+/g, ' '));
            } catch (e) {
                return '';
            }
        }

        function applyShopSearchClientEcho() {
            const ctx = document.getElementById('shopSearchContext');
            if (!ctx) return;
            if (searchQueryRaw && searchQueryRaw.trim().length >= 2) {
                ctx.innerHTML = 'Searching for <strong>' + searchQueryRaw + '</strong>';
            } else {
                ctx.textContent = 'Discover our complete range of premium fashion';
            }
        }

        // State (search from URL — raw string reflected into DOM; lowercased copy used for API / matching)
        let currentCategory = "<?php echo htmlspecialchars($filterCategory ?: 'All'); ?>";
        let searchQueryRaw = getQueryParam('search');
        let searchQuery = searchQueryRaw.toLowerCase();
        let allProducts = [];
        let displayedCount = 15;
        let isLoading = false;
        let categories = [];
        let categoriesData = []; // Store full category objects for ID lookup
        let selectedCategory = '';
        let selectedPriceRange = '';
        let inStockOnly = false;

        // DOM Elements
        const productGrid = document.getElementById('productGrid');
        const categoryContainer = document.getElementById('categoryContainer');
        const searchInput = document.getElementById('searchInput');
        const searchInputMobile = document.getElementById('searchInputMobile');
        const cartBadge = document.getElementById('cartBadge');
        const wishlistBadge = document.getElementById('wishlistBadge');
        const loadMoreSection = document.getElementById('loadMoreSection');
        const resultsText = document.getElementById('resultsText');
        const progressBar = document.getElementById('progressBar');
        const filterCategory = document.getElementById('filter-category');
        const filterPrice = document.getElementById('filter-price');
        const filterStock = document.getElementById('filter-stock');
        const sortSelect = document.getElementById('sortSelect');
        const clearFiltersBtn = document.getElementById('clear-filters');

        // Initialize
        async function init() {
            // Initialize cart and wishlist data first
            if (!window.cartData) {
                window.cartData = { items: [], count: 0, total: 0 };
            }
            if (!window.wishlistData) {
                window.wishlistData = { items: [], count: 0 };
            }
            
            // Load cart and wishlist data before rendering products
            await updateCartWishlistCounters();
            
            showProductSkeletons();
            showFilterSkeletons();
            await loadCategories();
            if (searchInput && searchQueryRaw) searchInput.value = searchQueryRaw;
            if (searchInputMobile && searchQueryRaw) searchInputMobile.value = searchQueryRaw;
            applyShopSearchClientEcho();
            await loadProducts();
            setupEventListeners();
            lucide.createIcons();
        }

        // Show skeleton loaders for products
        function showProductSkeletons(count = 15) {
            if (!productGrid) return;
            
            const skeletonHTML = Array.from({ length: count }, () => `
                <div class="skeleton-product-card">
                    <div class="skeleton-image mb-5 rounded-xl"></div>
                    <div class="space-y-2 px-1">
                        <div class="skeleton-text skeleton-text-sm w-20"></div>
                        <div class="skeleton-text skeleton-text-lg w-full"></div>
                        <div class="skeleton-text skeleton-text-sm w-3/4"></div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="skeleton-text w-16 h-6"></div>
                            <div class="skeleton-text w-12 h-4"></div>
                        </div>
                    </div>
                </div>
            `).join('');
            
            productGrid.innerHTML = skeletonHTML;
        }

        // Hide skeleton loaders for products
        function hideProductSkeletons() {
            if (!productGrid) return;
            // Skeletons will be replaced by actual products, so no need to explicitly hide
            // This function exists for consistency with the code structure
        }

        // Show skeleton loaders for filters
        function showFilterSkeletons() {
            const filterCategory = document.getElementById('filter-category');
            if (filterCategory) {
                filterCategory.innerHTML = '<option value="">Loading...</option>';
            }
        }

        function setupEventListeners() {
            searchInput?.addEventListener('input', (e) => {
                searchQueryRaw = e.target.value;
                searchQuery = e.target.value.toLowerCase();
                applyShopSearchClientEcho();
                renderProducts();
            });

            searchInputMobile?.addEventListener('input', (e) => {
                searchQueryRaw = e.target.value;
                searchQuery = e.target.value.toLowerCase();
                applyShopSearchClientEcho();
                renderProducts();
            });

            searchInput?.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') handleSearch();
            });

            searchInputMobile?.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') handleSearch();
            });

            filterCategory?.addEventListener('change', (e) => {
                selectedCategory = e.target.value;
                showProductSkeletons(15);
                renderProducts();
            });

            filterPrice?.addEventListener('change', (e) => {
                selectedPriceRange = e.target.value;
                showProductSkeletons(15);
                renderProducts();
            });

            filterStock?.addEventListener('change', (e) => {
                inStockOnly = e.target.checked;
                showProductSkeletons(15);
                renderProducts();
            });

            sortSelect?.addEventListener('change', () => {
                renderProducts();
            });

            clearFiltersBtn?.addEventListener('click', () => {
                selectedCategory = '';
                selectedPriceRange = '';
                inStockOnly = false;
                searchQuery = '';
                searchQueryRaw = '';
                applyShopSearchClientEcho();
                if (filterCategory) filterCategory.value = '';
                if (filterPrice) filterPrice.value = '';
                if (filterStock) filterStock.checked = false;
                if (searchInput) searchInput.value = '';
                if (searchInputMobile) searchInputMobile.value = '';
                renderProducts();
            });
        }

        async function loadCategories() {
            try {
                const response = await fetch('api/v1/categories.php');
                const data = await response.json();
                if (data.success && (data.categories || data.data)) {
                    const cats = data.categories || data.data || [];
                    categoriesData = cats; // Store full objects
                    categories = ['All', ...cats.map(c => c.name)];
                    if (filterCategory) {
                        filterCategory.innerHTML = '<option value="">All Categories</option>' + 
                            cats.map(c => `<option value="${c.name}">${c.name}</option>`).join('');
                    }
                } else {
                    categories = ["All", "Ethnic Wear", "Western Wear", "Lehengas", "Party Wear", "Accessories", "New Arrivals", "Best Sellers"];
                    categoriesData = [];
                }
                renderCategories();
            } catch (error) {
                console.error('Error loading categories:', error);
                categories = ["All", "Ethnic Wear", "Western Wear", "Lehengas", "Party Wear", "Accessories", "New Arrivals", "Best Sellers"];
                renderCategories();
            }
        }

        function renderCategories() {
            if (!categoryContainer) return;
            
            const html = categories.map(cat => `
                <button 
                    onclick="setCategory('${cat}')"
                    class="text-[11px] font-black uppercase tracking-[0.2em] transition-all relative py-1 hover:text-brand ${currentCategory === cat ? 'text-brand' : 'text-slate-500'}"
                >
                    ${cat}
                    ${currentCategory === cat ? '<span class="absolute -bottom-1 left-0 right-0 h-0.5 bg-brand rounded-full"></span>' : ''}
                </button>
            `).join('');
            
            categoryContainer.innerHTML = html;
            
            const mobileList = document.getElementById('mobileCategoryList');
            if (mobileList) {
                mobileList.innerHTML = categories.map(cat => `
                    <button onclick="setCategory('${cat}'); toggleMobileMenu(false);" class="text-2xl font-bold text-left ${currentCategory === cat ? 'text-brand' : 'text-slate-900'}">
                        ${cat}
                    </button>
                `).join('');
            }
        }

        function setCategory(cat) {
            currentCategory = cat;
            selectedCategory = cat === 'All' ? '' : cat;
            if (filterCategory) filterCategory.value = selectedCategory;
            showProductSkeletons(15);
            renderCategories();
            renderProducts();
        }

        async function loadProducts() {
            if (isLoading) return;
            isLoading = true;
            
            // Show skeletons while loading
            showProductSkeletons(15);

            try {
                let url;
                let data;
                
                // Use search API if there's a search query, otherwise use products API
                if (searchQuery && searchQuery.trim().length >= 2) {
                    // Use global search API for better search results
                    url = 'api/v1/search.php?q=' + encodeURIComponent(searchQuery);
                    if (selectedCategory && selectedCategory !== 'All') {
                        // Find category ID from categoriesData array
                        const categoryObj = categoriesData.find(c => c.name === selectedCategory);
                        if (categoryObj && categoryObj.id) {
                            url += `&category_id=${categoryObj.id}`;
                        }
                    }
                    if (selectedPriceRange) {
                        const [min, max] = selectedPriceRange.split('-');
                        if (min) url += `&min_price=${min}`;
                        if (max && max !== '+') url += `&max_price=${max}`;
                    }
                    if (inStockOnly) {
                        url += `&in_stock=true`;
                    }
                    url += `&per_page=100`;
                    
                    const response = await fetch(url);
                    data = await response.json();
                    
                    // Search API returns products in 'products' or 'data' field
                    if (data.success && (data.products || data.data)) {
                        allProducts = data.products || data.data || [];
                    } else {
                        allProducts = [];
                    }
                } else {
                    // Use products API for regular browsing
                    url = 'api/v1/products.php?';
                    if (selectedCategory && selectedCategory !== 'All') {
                        url += `category=${encodeURIComponent(selectedCategory)}&`;
                    }
                    url += `limit=100`;

                    const response = await fetch(url);
                    data = await response.json();
                    
                    if (data.success && (data.products || data.data)) {
                        allProducts = data.products || data.data || [];
                    } else {
                        allProducts = [];
                    }
                }

                // Hide skeletons before rendering
                hideProductSkeletons();
                
                if (allProducts.length > 0) {
                    displayedCount = 15;
                    console.log('Loaded products:', allProducts.length);
                    renderProducts();
                } else {
                    productGrid.innerHTML = `
                        <div class="col-span-full text-center py-20 bg-slate-50 rounded-3xl">
                            <h3 class="text-xl font-bold mb-2 text-slate-400">No products found.</h3>
                            <p class="text-sm text-slate-500">${data.message || 'Try adjusting your search or filters.'}</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading products:', error);
                hideProductSkeletons();
                productGrid.innerHTML = `
                    <div class="col-span-full text-center py-20 bg-slate-50 rounded-3xl">
                        <h3 class="text-xl font-bold mb-2 text-slate-400">Error loading products. Please try again.</h3>
                    </div>
                `;
            } finally {
                isLoading = false;
            }
        }

        function renderProducts() {
            // Show skeletons if products are being filtered/searched
            if (allProducts.length === 0 && !isLoading) {
                showProductSkeletons(15);
                return;
            }

            let filtered = allProducts.filter(p => {
                const matchesSearch = !searchQuery || 
                    (p.name && p.name.toLowerCase().includes(searchQuery.toLowerCase())) ||
                    (p.description && p.description.toLowerCase().includes(searchQuery.toLowerCase()));
                const matchesCategory = !selectedCategory || 
                    (p.category && p.category.name === selectedCategory) || 
                    p.category_name === selectedCategory;
                
                let matchesPrice = true;
                if (selectedPriceRange) {
                    const price = parseFloat(p.price || 0);
                    if (selectedPriceRange === '0-10000') matchesPrice = price < 10000;
                    else if (selectedPriceRange === '10000-20000') matchesPrice = price >= 10000 && price <= 20000;
                    else if (selectedPriceRange === '20000-35000') matchesPrice = price >= 20000 && price <= 35000;
                    else if (selectedPriceRange === '35000-50000') matchesPrice = price >= 35000 && price <= 50000;
                    else if (selectedPriceRange === '50000+') matchesPrice = price > 50000;
                }
                
                const matchesStock = !inStockOnly || (parseInt(p.stock_quantity || 0) > 0);
                
                return matchesSearch && matchesCategory && matchesPrice && matchesStock;
            });

            // Apply sorting
            const sortValue = sortSelect?.value || 'popularity';
            if (sortValue === 'newest') {
                filtered.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0));
            } else if (sortValue === 'price_low') {
                filtered.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));
            } else if (sortValue === 'price_high') {
                filtered.sort((a, b) => parseFloat(b.price) - parseFloat(a.price));
            } else if (sortValue === 'name') {
                filtered.sort((a, b) => a.name.localeCompare(b.name));
            }

            const toDisplay = filtered.slice(0, displayedCount);
            const total = filtered.length;

            // Hide skeletons before rendering
            hideProductSkeletons();

            if (toDisplay.length === 0) {
                productGrid.innerHTML = `
                    <div class="col-span-full text-center py-20 bg-slate-50 rounded-3xl">
                        <h3 class="text-xl font-bold mb-2 text-slate-400">No items found matching your filters.</h3>
                    </div>
                `;
                loadMoreSection.classList.add('hidden');
                return;
            }

            productGrid.innerHTML = toDisplay.map(product => {
                const price = parseFloat(product.price || 0);
                const originalPrice = parseFloat(product.original_price || product.price || 0);
                const discount = originalPrice > price ? Math.round(((originalPrice - price) / originalPrice) * 100) : 0;
                const imageUrl = (product.images && product.images.length > 0) 
                    ? (typeof product.images[0] === 'string' ? product.images[0] : (product.images[0].url || product.images[0].image_url || product.image))
                    : (product.image || 'https://via.placeholder.com/600');
                // Ensure cart and wishlist data exist before checking
                const cartItems = window.cartData?.items || [];
                const wishlistItems = window.wishlistData?.items || [];
                
                // Compare using strict equality, ensuring both are numbers
                const isWishlisted = wishlistItems.some(item => parseInt(item.product_id) === parseInt(product.id));
                const isInCart = cartItems.some(item => parseInt(item.product_id) === parseInt(product.id));
                const inStock = parseInt(product.stock_quantity || 0) > 0;
                const cartButtonText = isInCart ? 'GO TO CART' : 'ADD TO CART';
                const cartButtonClass = isInCart 
                    ? `w-full bg-brand text-white py-3 rounded-lg font-bold text-xs shadow-xl flex items-center justify-center gap-2 hover:bg-teal-600 transition-colors pointer-events-auto ${!inStock ? 'opacity-50 cursor-not-allowed' : ''}`
                    : `w-full bg-white text-slate-900 py-3 rounded-lg font-bold text-xs shadow-xl flex items-center justify-center gap-2 hover:bg-brand hover:text-white transition-colors pointer-events-auto ${!inStock ? 'opacity-50 cursor-not-allowed' : ''}`;
                const cartButtonIcon = isInCart ? 'shopping-cart' : 'shopping-bag';
                const cartButtonAction = isInCart 
                    ? `event.stopPropagation(); if (typeof openCart === 'function') { openCart(); } else if (typeof toggleDrawer === 'function') { toggleDrawer('cart'); }`
                    : `event.stopPropagation(); addToCartFromGrid(${product.id}, '${product.name.replace(/'/g, "\\'")}')`;
                const cartButtonDisabled = !inStock ? 'disabled' : '';
                
                return `
                <div class="group cursor-pointer product-card" onclick="window.location.href='product.php?id=${product.id}'">
                    <div class="relative aspect-[3/4] overflow-hidden rounded-xl bg-slate-50 mb-5">
                        <img src="${imageUrl}" alt="${product.name}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                        ${discount > 0 ? `<div class="absolute top-3 left-3 bg-slate-900 text-white text-[9px] font-black px-2 py-1 rounded tracking-tighter">-${discount}% OFF</div>` : ''}
                        ${product.is_featured ? '<div class="absolute top-3 left-3 bg-brand text-white text-[9px] font-black px-2 py-1 rounded tracking-tighter">FEATURED</div>' : ''}
                        ${!inStock ? '<div class="absolute top-3 left-3 bg-red-500 text-white text-[9px] font-black px-2 py-1 rounded tracking-tighter">OUT OF STOCK</div>' : ''}
                        
                        <div class="quick-add absolute inset-0 bg-black/5 opacity-0 transition-all flex flex-col items-center justify-end p-4 translate-y-4 pointer-events-none">
                            <button onclick="${cartButtonAction}" class="${cartButtonClass}" ${cartButtonDisabled}>
                                <i data-lucide="${cartButtonIcon}" class="w-3.5 h-3.5"></i> <span>${cartButtonText}</span>
                            </button>
                        </div>

                        <button onclick="event.stopPropagation(); toggleWishlistFromGrid(event, ${product.id})" class="absolute top-3 right-3 p-2 rounded-full shadow-lg transition-all ${isWishlisted ? 'bg-white text-red-500' : 'bg-white/80 text-slate-400 hover:text-red-500'}">
                            <i data-lucide="heart" class="w-4 h-4 ${isWishlisted ? 'fill-current' : ''}"></i>
                        </button>
                    </div>

                    <div class="space-y-1 px-1">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">${(product.category && product.category.name) || product.category_name || 'Uncategorized'}</p>
                        <h4 class="font-bold text-slate-800 text-sm md:text-base line-clamp-1 group-hover:text-brand transition-colors">${product.name || 'Untitled Product'}</h4>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-base font-black text-slate-900 tracking-tight">₹${price.toLocaleString('en-IN')}</span>
                            ${originalPrice > price ? `<span class="text-xs text-slate-400 line-through font-medium">₹${originalPrice.toLocaleString('en-IN')}</span>` : ''}
                        </div>
                    </div>
                </div>
            `}).join('');

            // Update load more section
            if (total > displayedCount) {
                loadMoreSection.classList.remove('hidden');
                resultsText.innerText = `Showing ${displayedCount} of ${total} items`;
                const progress = (displayedCount / total) * 100;
                progressBar.style.width = `${progress}%`;
            } else {
                loadMoreSection.classList.add('hidden');
                if (total > 0) {
                    resultsText.innerText = `Showing all ${total} items`;
                }
            }

            lucide.createIcons();
        }

        function loadMoreProducts() {
            // Show skeletons for new products being loaded
            const currentProducts = productGrid.querySelectorAll('.product-card').length;
            if (currentProducts > 0) {
                // Add skeleton loaders at the end
                const skeletonContainer = document.getElementById('productSkeletons');
                if (skeletonContainer) {
                    skeletonContainer.classList.remove('hidden');
                    skeletonContainer.innerHTML = '';
                    for (let i = 0; i < 5; i++) {
                        skeletonContainer.innerHTML += `
                            <div class="skeleton-product-card">
                                <div class="skeleton-image mb-5 rounded-xl"></div>
                                <div class="space-y-2 px-1">
                                    <div class="skeleton-text skeleton-text-sm w-20"></div>
                                    <div class="skeleton-text skeleton-text-lg w-full"></div>
                                    <div class="skeleton-text skeleton-text-sm w-3/4"></div>
                                    <div class="flex items-center gap-2 mt-2">
                                        <div class="skeleton-text w-16 h-6"></div>
                                        <div class="skeleton-text w-12 h-4"></div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                }
            }
            
            displayedCount += 15;
            renderProducts();
        }

        async function addToCartFromGrid(productId, productName) {
            try {
                const response = await fetch('api/v1/cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ product_id: productId, quantity: 1 })
                });
                const data = await response.json();
                
                if (data.success) {
                    // Initialize cartData if not exists
                    if (!window.cartData) {
                        window.cartData = { items: [], count: 0, total: 0 };
                    }
                    
                    // Update cart data from API response
                    window.cartData = data.data || { items: [], count: 0, total: 0 };
                    
                    // Update using cart-wishlist.js function if available
                    if (typeof updateCounters === 'function') {
                        await updateCounters(data.data, null);
                    }
                    
                    // Update header counters
                    await updateCartWishlistCounters();
                    
                    if (typeof showToast === 'function') {
                        showToast(`Added ${productName} to your bag!`);
                    }
                    
                    // Re-render products to update button states (shows "Go to Cart" if in cart)
                    renderProducts();
                } else {
                    if (data.requires_login) {
                        if (typeof showToast === 'function') {
                            showToast('Please login to add items to cart');
                        }
                        setTimeout(() => window.location.href = 'login.php', 1000);
                    } else {
                        if (typeof showToast === 'function') {
                            showToast(data.message || 'Failed to add to cart');
                        }
                    }
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
                if (typeof showToast === 'function') {
                    showToast('Error adding to cart');
                }
            }
        }

        async function toggleWishlistFromGrid(e, productId) {
            e.stopPropagation();
            try {
                // Initialize wishlistData if not exists
                if (!window.wishlistData) {
                    window.wishlistData = { items: [], count: 0 };
                }
                
                const isWishlisted = window.wishlistData.items.some(item => item.product_id == productId);
                const url = 'api/v1/wishlist.php';
                const method = isWishlisted ? 'DELETE' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ product_id: productId })
                });
                const data = await response.json();
                
                if (data.success) {
                    // Update wishlist data from API response
                    window.wishlistData = data.data || { items: [], count: 0 };
                    
                    // Update using cart-wishlist.js function if available
                    if (typeof updateCounters === 'function') {
                        await updateCounters(null, data.data);
                    }
                    
                    // Update header counters
                    await updateCartWishlistCounters();
                    
                    // Show success message
                    if (typeof showToast === 'function') {
                        showToast(isWishlisted ? 'Removed from wishlist' : 'Added to wishlist!');
                    }
                    
                    // Update wishlist button state (red when added)
                    const button = e.target.closest('button');
                    if (button) {
                        const heartIcon = button.querySelector('i[data-lucide="heart"]');
                        const newIsWishlisted = window.wishlistData.items.some(item => item.product_id == productId);
                        
                        if (heartIcon) {
                            if (newIsWishlisted) {
                                // Added to wishlist - make it red
                                button.classList.remove('bg-white/80', 'text-slate-400');
                                button.classList.add('bg-white', 'text-red-500');
                                heartIcon.classList.add('fill-current');
                            } else {
                                // Removed from wishlist - make it gray
                                button.classList.remove('bg-white', 'text-red-500');
                                button.classList.add('bg-white/80', 'text-slate-400');
                                heartIcon.classList.remove('fill-current');
                            }
                            if (window.lucide) window.lucide.createIcons();
                        }
                    }
                    
                    renderProducts(); // Re-render to update wishlist icons
                } else {
                    if (data.requires_login) {
                        if (typeof showToast === 'function') {
                            showToast('Please login to add items to wishlist');
                        }
                        setTimeout(() => window.location.href = 'login.php', 1000);
                    } else {
                        if (typeof showToast === 'function') {
                            showToast(data.message || 'Failed to update wishlist');
                        }
                    }
                }
            } catch (error) {
                console.error('Error toggling wishlist:', error);
                if (typeof showToast === 'function') {
                    showToast('Error updating wishlist');
                }
            }
        }

        async function updateCartWishlistCounters() {
            try {
                // Always fetch cart and wishlist data from API to ensure we have the latest state
                const [cartRes, wishlistRes] = await Promise.all([
                    fetch('api/v1/cart.php', { credentials: 'include' }).then(r => r.json()).catch(() => ({ success: false, data: { items: [], count: 0, total: 0 } })),
                    fetch('api/v1/wishlist.php', { credentials: 'include' }).then(r => r.json()).catch(() => ({ success: false, data: { items: [], count: 0 } }))
                ]);
                
                // Always update window.cartData and window.wishlistData from API response
                if (cartRes.success && cartRes.data) {
                    window.cartData = cartRes.data;
                } else {
                    // If API fails, ensure we have empty cart structure
                    window.cartData = window.cartData || { items: [], count: 0, total: 0 };
                }
                
                if (wishlistRes.success && wishlistRes.data) {
                    window.wishlistData = wishlistRes.data;
                } else {
                    // If API fails, ensure we have empty wishlist structure
                    window.wishlistData = window.wishlistData || { items: [], count: 0 };
                }
                
                // Also update using cart-wishlist.js if available (for consistency)
                if (typeof updateCounters === 'function') {
                    await updateCounters(window.cartData, window.wishlistData);
                }
                
                const cartCount = window.cartData?.count || 0;
                const wishlistCount = window.wishlistData?.count || 0;
                
                // Update cart badge
                if (cartBadge) {
                    if (cartCount > 0) {
                        cartBadge.innerText = cartCount;
                        cartBadge.classList.remove('hidden');
                        cartBadge.classList.add('flex');
                    } else {
                        cartBadge.classList.add('hidden');
                        cartBadge.classList.remove('flex');
                    }
                }
                
                // Update wishlist badge
                if (wishlistBadge) {
                    if (wishlistCount > 0) {
                        wishlistBadge.innerText = wishlistCount;
                        wishlistBadge.classList.remove('hidden');
                        wishlistBadge.classList.add('flex');
                    } else {
                        wishlistBadge.classList.add('hidden');
                        wishlistBadge.classList.remove('flex');
                    }
                }
            } catch (error) {
                console.error('Error updating counters:', error);
                // Ensure we have default values even on error
                window.cartData = window.cartData || { items: [], count: 0, total: 0 };
                window.wishlistData = window.wishlistData || { items: [], count: 0 };
            }
        }

        function handleSearch() {
            const query = searchInput?.value || searchInputMobile?.value || '';
            if (query) {
                searchQueryRaw = query;
                searchQuery = query.toLowerCase();
                applyShopSearchClientEcho();
                renderProducts();
            }
        }

        function toggleMobileMenu(show) {
            const menu = document.getElementById('mobileMenu');
            if (!menu) return;
            if (show) {
                menu.classList.remove('hidden');
                setTimeout(() => menu.classList.remove('translate-x-full'), 10);
            } else {
                menu.classList.add('translate-x-full');
                setTimeout(() => menu.classList.add('hidden'), 300);
            }
        }

        function subscribeNewsletter() {
            const email = document.getElementById('newsletterEmail')?.value;
            if (email) {
                alert('Thank you for subscribing!');
                document.getElementById('newsletterEmail').value = '';
            }
        }

        // Checkout function
        function checkout() {
            if (window.cartData && window.cartData.count > 0) {
                window.location.href = 'checkout.php';
            }
        }

        // Drawer Functions
        function toggleDrawer(type) {
            closeAllDrawers();
            const drawerOverlay = document.getElementById('drawerOverlay');
            const drawer = type === 'cart' ? document.getElementById('cartDrawer') : document.getElementById('wishlistDrawer');
            
            if (drawerOverlay && drawer) {
                drawerOverlay.classList.remove('opacity-0', 'pointer-events-none');
                drawer.classList.remove('translate-x-full');
                document.body.style.overflow = 'hidden';
                
                if (type === 'cart' && typeof renderCart === 'function') {
                    renderCart();
                } else if (type === 'wishlist' && typeof renderWishlist === 'function') {
                    renderWishlist();
                }
            }
        }

        function closeAllDrawers() {
            const drawerOverlay = document.getElementById('drawerOverlay');
            const cartDrawer = document.getElementById('cartDrawer');
            const wishlistDrawer = document.getElementById('wishlistDrawer');
            
            if (cartDrawer) cartDrawer.classList.add('translate-x-full');
            if (wishlistDrawer) wishlistDrawer.classList.add('translate-x-full');
            if (drawerOverlay) {
                drawerOverlay.classList.add('opacity-0', 'pointer-events-none');
            }
            document.body.style.overflow = 'auto';
        }

        function openCart() {
            toggleDrawer('cart');
        }

        function closeCart() {
            closeAllDrawers();
        }

        function openWishlist() {
            toggleDrawer('wishlist');
        }

        function closeWishlist() {
            closeAllDrawers();
        }

        // Make functions globally available
        window.openCart = openCart;
        window.closeCart = closeCart;
        window.openWishlist = openWishlist;
        window.closeWishlist = closeWishlist;
        window.closeAllDrawers = closeAllDrawers;
        window.toggleDrawer = toggleDrawer;
        window.checkout = checkout;

        // Initialize cart/wishlist system
        if (typeof initCartWishlist === 'function') {
            initCartWishlist();
        }

        // Initialize on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    </script>
</body>
</html>
