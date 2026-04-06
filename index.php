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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BLine Boutique | Elegance Redefined</title>
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

        .toast-active {
            bottom: 2.5rem;
            opacity: 1;
        }

        @keyframes bounce-subtle {
            0%, 100% { transform: translateY(-50%) scale(1); }
            50% { transform: translateY(-55%) scale(1.05); }
        }
        
        .hero-zoom {
            animation: slowZoom 20s infinite alternate;
        }

        @keyframes slowZoom {
            from { transform: scale(1); }
            to { transform: scale(1.1); }
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

        /* Search Autocomplete Styles */
        #searchAutocomplete {
            animation: fadeIn 0.2s ease-out;
        }

        .autocomplete-item {
            transition: all 0.2s ease;
        }

        .autocomplete-item:hover {
            background-color: #f0fdfa;
        }

        .autocomplete-item.active {
            background-color: #f0fdfa;
        }

        .search-highlight {
            font-weight: 700;
            color: var(--brand-color);
        }

        /* Search Loader Styles */
        #searchLoader, #mobileSearchLoader {
            transition: opacity 0.2s ease;
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

        .skeleton-category {
            height: 20px;
            width: 100px;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeleton-shimmer 1.5s infinite;
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-white text-slate-800">

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-[-100px] left-1/2 -translate-x-1/2 z-[100] bg-zinc-900 text-white px-6 py-3 rounded-full shadow-2xl flex items-center gap-3 transition-all duration-500 opacity-0">
        <i data-lucide="check" class="text-brand w-5 h-5"></i>
        <span id="toast-message">Added to cart!</span>
    </div>

    <!-- Scroll to Top -->
    <button id="scrollTop" class="fixed bottom-10 right-10 z-[100] bg-white border border-slate-200 p-4 rounded-full shadow-xl hover:bg-brand hover:text-white transition-all text-slate-600 hidden">
        <i data-lucide="arrow-up" class="w-6 h-6"></i>
    </button>

    <?php include 'header.php'; ?>

    <?php if (isset($_GET['promo']) && $_GET['promo'] !== ''): ?>
    <div class="bg-amber-100 text-center py-2 text-sm text-slate-800"><?php echo $_GET['promo']; ?></div>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="relative h-[50vh] md:h-[80vh] w-full overflow-hidden bg-slate-900">
        <img 
            src="https://images.unsplash.com/photo-1617627143750-d86bc21e42bb?q=80&w=2000&auto=format&fit=crop" 
            alt="Hero" 
            class="w-full h-full object-cover opacity-80 hero-zoom"
        />
        <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent flex flex-col items-start justify-center px-6 md:px-24">
            <div class="max-w-3xl space-y-6">
                <p class="text-brand font-black uppercase tracking-[0.4em] text-sm md:text-lg">Spring / Summer 2024</p>
                <h2 class="text-5xl md:text-8xl font-serif font-black text-white leading-[0.9]">Couture for <br/> the Modern<br/> Woman.</h2>
                <p class="text-lg md:text-2xl text-slate-200 font-light max-w-xl">
                    Discover unparalleled craftsmanship and timeless elegance in our latest boutique arrivals.
                </p>
                <button onclick="window.location.href='shop.php'" class="bg-brand hover:bg-white hover:text-slate-900 text-white px-10 py-5 rounded-full font-black text-lg transition-all shadow-2xl flex items-center gap-3">
                    SHOP ALL COLLECTIONS <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </button>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="py-12 md:py-24 bg-white">
        <div class="container mx-auto px-4 lg:px-12">
            
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="h-[2px] w-8 bg-brand"></span>
                        <span class="text-brand font-bold uppercase tracking-widest text-xs">Curated Selection</span>
                    </div>
                    <h2 id="sectionTitle" class="text-3xl md:text-5xl font-black text-slate-900 tracking-tight">Featured Collections</h2>
                </div>
                
                <div class="flex items-center gap-4 border-b border-slate-200 pb-2">
                    <i data-lucide="sliders-horizontal" class="w-4.5 h-4.5 text-slate-400"></i>
                    <select id="sortSelect" class="bg-transparent border-none text-sm font-bold focus:ring-0 outline-none cursor-pointer text-slate-600" onchange="renderProducts()">
                        <option value="popularity">Sort: Popularity</option>
                        <option value="newest">Sort: Newest First</option>
                        <option value="price_low">Sort: Price (Low - High)</option>
                        <option value="price_high">Sort: Price (High - Low)</option>
                    </select>
                </div>
            </div>

            <!-- Product Grid -->
            <div id="productGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-x-4 md:gap-x-8 gap-y-12">
                <!-- Skeleton Loaders (shown initially) -->
                <div id="productSkeletons" class="col-span-full grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-x-4 md:gap-x-8 gap-y-12">
                    <!-- Skeleton cards will be generated by JS -->
                </div>
            </div>

            <!-- Amazon-style Load More -->
            <div id="loadMoreSection" class="mt-24 border-t border-slate-100 pt-16 flex flex-col items-center hidden">
                <div class="flex items-center gap-4 mb-8">
                    <span id="showingText" class="text-sm font-bold text-slate-400 italic">Showing 15 of 450 items</span>
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
        // State
        let currentCategory = "All";
        let searchQuery = "";
        let allProducts = [];
        let displayedCount = 15;
        let currentPage = 1;
        let isLoading = false;
        let categories = [];
        let searchDebounceTimer = null;
        let autocompleteSuggestions = [];
        let selectedSuggestionIndex = -1;

        // DOM Elements
        const productGrid = document.getElementById('productGrid');
        const categoryContainer = document.getElementById('categoryContainer');
        const searchInput = document.getElementById('searchInput');
        const searchAutocomplete = document.getElementById('searchAutocomplete');
        const autocompleteContent = document.getElementById('autocompleteContent');
        const sectionTitle = document.getElementById('sectionTitle');
        const cartBadge = document.getElementById('cartBadge');
        const wishlistBadge = document.getElementById('wishlistBadge');
        const scrollTopBtn = document.getElementById('scrollTop');
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toast-message');
        const loadMoreSection = document.getElementById('loadMoreSection');
        const showingText = document.getElementById('showingText');
        const progressBar = document.getElementById('progressBar');

        // Initialize
        async function init() {
            // Initialize cart and wishlist data first
            if (!window.cartData) {
                window.cartData = { items: [], count: 0, total: 0 };
            }
            if (!window.wishlistData) {
                window.wishlistData = { items: [], count: 0 };
            }
            
            // Load cart and wishlist data BEFORE loading products
            // This ensures cart state is available when products are rendered
            await updateCartWishlistCounters();
            
            await loadCategories();
            await loadProducts();
            lucide.createIcons();
            setupEventListeners();
        }

        function setupEventListeners() {
            // Search input with debounced autocomplete
            searchInput?.addEventListener('input', (e) => {
                const query = e.target.value.trim();
                searchQuery = query.toLowerCase();
                
                // Clear previous debounce timer
                if (searchDebounceTimer) {
                    clearTimeout(searchDebounceTimer);
                }
                
                // Hide autocomplete if query is too short
                if (query.length < 2) {
                    hideAutocomplete();
                    hideSearchLoader();
                    renderProducts();
                    return;
                }
                
                // Show loading indicator immediately when user starts typing
                if (query.length >= 2) {
                    showSearchLoader();
                }
                
                // Debounce autocomplete requests (300ms delay)
                searchDebounceTimer = setTimeout(() => {
                    loadAutocompleteSuggestions(query);
                }, 300);
                
                // Update products immediately for local filtering
                renderProducts();
            });

            // Handle keyboard navigation in autocomplete
            searchInput?.addEventListener('keydown', (e) => {
                if (!searchAutocomplete || searchAutocomplete.classList.contains('hidden')) {
                    if (e.key === 'Enter') {
                        handleSearch();
                    }
                    return;
                }
                
                switch(e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        selectedSuggestionIndex = Math.min(selectedSuggestionIndex + 1, autocompleteSuggestions.length - 1);
                        updateAutocompleteSelection();
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        selectedSuggestionIndex = Math.max(selectedSuggestionIndex - 1, -1);
                        updateAutocompleteSelection();
                        break;
                    case 'Enter':
                        e.preventDefault();
                        if (selectedSuggestionIndex >= 0 && autocompleteSuggestions[selectedSuggestionIndex]) {
                            selectSuggestionByIndex(selectedSuggestionIndex);
                        } else {
                            handleSearch();
                        }
                        break;
                    case 'Escape':
                        hideAutocomplete();
                        break;
                }
            });

            // Hide autocomplete when clicking outside
            document.addEventListener('click', (e) => {
                const searchContainer = document.getElementById('searchContainer');
                if (searchContainer && !searchContainer.contains(e.target)) {
                    hideAutocomplete();
                }
            });

            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) scrollTopBtn?.classList.remove('hidden');
                else scrollTopBtn?.classList.add('hidden');
            });

            scrollTopBtn?.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        // Load autocomplete suggestions from API
        async function loadAutocompleteSuggestions(query) {
            if (!query || query.length < 2) {
                hideAutocomplete();
                hideSearchLoader();
                return;
            }
            
            // Show skeleton loaders in autocomplete
            showAutocompleteSkeletons();
            
            try {
                const response = await fetch(`api/v1/search.php?q=${encodeURIComponent(query)}&type=autocomplete`);
                const data = await response.json();
                
                // Hide loader
                hideSearchLoader();
                
                if (data.success && data.suggestions) {
                    autocompleteSuggestions = data.suggestions;
                    selectedSuggestionIndex = -1;
                    const echo = document.getElementById('searchReflectedEcho');
                    if (echo && query && query.length >= 2) {
                        echo.classList.remove('hidden');
                        echo.innerHTML = 'Suggestions for <span class="text-slate-800">' + query + '</span>';
                    }
                    renderAutocomplete();
                } else {
                    hideAutocomplete();
                }
            } catch (error) {
                console.error('Error loading autocomplete:', error);
                hideAutocomplete();
                hideSearchLoader();
            }
        }

        // Show skeleton loaders for autocomplete
        function showAutocompleteSkeletons() {
            if (!autocompleteContent || !searchAutocomplete) return;
            
            // Show loading spinner in search input
            showSearchLoader();
            
            autocompleteContent.innerHTML = '';
            for (let i = 0; i < 5; i++) {
                autocompleteContent.innerHTML += `
                    <div class="autocomplete-item flex items-center gap-3 p-3 rounded-lg animate-pulse">
                        <div class="skeleton w-4 h-4 rounded flex-shrink-0"></div>
                        <div class="flex-1 space-y-2">
                            <div class="skeleton skeleton-text w-3/4 h-4"></div>
                            <div class="skeleton skeleton-text skeleton-text-sm w-1/2 h-3"></div>
                        </div>
                        <div class="skeleton w-4 h-4 rounded flex-shrink-0"></div>
                    </div>
                `;
            }
            searchAutocomplete.classList.remove('hidden');
        }

        // Show search loader spinner
        function showSearchLoader() {
            const searchLoader = document.getElementById('searchLoader');
            const searchIcon = document.getElementById('searchIcon');
            if (searchLoader) {
                searchLoader.classList.remove('hidden');
            }
            if (searchIcon) {
                searchIcon.style.opacity = '0.5';
            }
        }

        // Hide search loader spinner
        function hideSearchLoader() {
            const searchLoader = document.getElementById('searchLoader');
            const searchIcon = document.getElementById('searchIcon');
            if (searchLoader) {
                searchLoader.classList.add('hidden');
            }
            if (searchIcon) {
                searchIcon.style.opacity = '1';
            }
        }

        // Render autocomplete dropdown
        function renderAutocomplete() {
            if (!autocompleteContent || !searchAutocomplete) return;
            
            if (autocompleteSuggestions.length === 0) {
                hideAutocomplete();
                return;
            }
            
            const html = autocompleteSuggestions.map((suggestion, index) => {
                const highlightedName = highlightSearchTerm(suggestion.name, searchInput?.value || '');
                const icon = suggestion.type === 'category' ? 'tag' : 'package';
                const suggestionId = suggestion.id || '';
                const suggestionSlug = suggestion.slug || '';
                const suggestionName = (suggestion.name || '').replace(/'/g, "\\'");
                const suggestionType = suggestion.type || 'product';
                const suggestionCategory = (suggestion.category || '').replace(/'/g, "\\'");
                
                return `
                    <div 
                        class="autocomplete-item flex items-center gap-3 p-3 rounded-lg cursor-pointer ${index === selectedSuggestionIndex ? 'active' : ''}"
                        data-index="${index}"
                        data-suggestion-id="${suggestionId}"
                        data-suggestion-slug="${suggestionSlug}"
                        data-suggestion-name="${suggestionName}"
                        data-suggestion-type="${suggestionType}"
                        data-suggestion-category="${suggestionCategory}"
                        onclick="selectSuggestionByIndex(${index})"
                        onmouseenter="selectedSuggestionIndex = ${index}; updateAutocompleteSelection()"
                    >
                        <i data-lucide="${icon}" class="w-4 h-4 text-slate-400"></i>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-slate-900">${highlightedName}</div>
                            ${suggestion.category ? `<div class="text-xs text-slate-500">${suggestion.category}</div>` : ''}
                        </div>
                        <i data-lucide="arrow-right" class="w-4 h-4 text-slate-300"></i>
                    </div>
                `;
            }).join('');
            
            autocompleteContent.innerHTML = html;
            searchAutocomplete.classList.remove('hidden');
            lucide.createIcons();
        }

        // Highlight search term in suggestion text
        function highlightSearchTerm(text, query) {
            if (!query) return text;
            const regex = new RegExp(`(${query})`, 'gi');
            return text.replace(regex, '<span class="search-highlight">$1</span>');
        }

        // Update autocomplete selection visual state
        function updateAutocompleteSelection() {
            const items = autocompleteContent?.querySelectorAll('.autocomplete-item');
            if (!items) return;
            
            items.forEach((item, index) => {
                if (index === selectedSuggestionIndex) {
                    item.classList.add('active');
                    item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                } else {
                    item.classList.remove('active');
                }
            });
        }

        // Select a suggestion by index
        function selectSuggestionByIndex(index) {
            if (index >= 0 && index < autocompleteSuggestions.length) {
                const suggestion = autocompleteSuggestions[index];
                selectSuggestion(suggestion);
            }
        }

        // Select a suggestion
        function selectSuggestion(suggestion) {
            if (!suggestion) return;
            
            if (suggestion.type === 'product' && suggestion.id) {
                window.location.href = `product.php?id=${suggestion.id}`;
            } else if (suggestion.type === 'category' && suggestion.slug) {
                window.location.href = `shop.php?category=${encodeURIComponent(suggestion.slug)}`;
            } else {
                // Fallback to search
                if (searchInput && suggestion.name) {
                    searchInput.value = suggestion.name;
                }
                handleSearch();
            }
            hideAutocomplete();
        }

        // Hide autocomplete dropdown
        function hideAutocomplete() {
            if (searchAutocomplete) {
                searchAutocomplete.classList.add('hidden');
            }
            const echo = document.getElementById('searchReflectedEcho');
            if (echo) {
                echo.classList.add('hidden');
                echo.innerHTML = '';
            }
            selectedSuggestionIndex = -1;
            autocompleteSuggestions = [];
            hideSearchLoader();
        }

        async function loadCategories() {
            // Show category skeletons
            showCategorySkeletons();
            
            try {
                const response = await fetch('api/v1/categories.php');
                const data = await response.json();
                if (data.success && (data.categories || data.data)) {
                    const cats = data.categories || data.data || [];
                    categories = ['All', ...cats.map(c => c.name)];
                } else {
                    categories = ["All", "Ethnic Wear", "Western Wear", "Lehengas", "Party Wear", "Accessories", "New Arrivals", "Best Sellers"];
                }
                renderCategories();
            } catch (error) {
                console.error('Error loading categories:', error);
                categories = ["All", "Ethnic Wear", "Western Wear", "Lehengas", "Party Wear", "Accessories", "New Arrivals", "Best Sellers"];
                renderCategories();
            }
        }

        // Show skeleton loaders for categories
        function showCategorySkeletons() {
            const categoryContainer = document.getElementById('categoryContainer');
            if (!categoryContainer) return;
            
            categoryContainer.innerHTML = '';
            for (let i = 0; i < 8; i++) {
                categoryContainer.innerHTML += `
                    <div class="skeleton-category"></div>
                `;
            }
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
            
            // Mobile menu categories
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
            sectionTitle.innerText = cat === "All" ? "Featured Collections" : cat;
            renderCategories();
            renderProducts();
        }

        async function loadProducts() {
            if (isLoading) return;
            isLoading = true;
            
            // Show skeletons while loading
            showProductSkeletons(15);

            try {
                let url = 'api/v1/products.php?';
                if (currentCategory !== "All") {
                    url += `category=${encodeURIComponent(currentCategory)}&`;
                }
                if (searchQuery) {
                    url += `search=${encodeURIComponent(searchQuery)}&`;
                }
                url += `featured=1&limit=100`;

                const response = await fetch(url);
                const data = await response.json();
                
                // Hide skeletons before rendering
                hideProductSkeletons();
                
                if (data.success && (data.products || data.data)) {
                    allProducts = data.products || data.data || [];
                    displayedCount = 15;
                    console.log('Loaded products:', allProducts.length);
                    renderProducts();
                } else {
                    console.error('API Error:', data);
                    productGrid.innerHTML = `
                        <div class="col-span-full text-center py-20 bg-slate-50 rounded-3xl">
                            <h3 class="text-xl font-bold mb-2 text-slate-400">No products found.</h3>
                            <p class="text-sm text-slate-500">${data.message || 'Please try again later.'}</p>
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

            const filtered = allProducts.filter(p => {
                const matchesCat = currentCategory === "All" || 
                    (p.category && p.category.name === currentCategory) || 
                    p.category_name === currentCategory;
                const matchesSearch = !searchQuery || 
                    (p.name && p.name.toLowerCase().includes(searchQuery.toLowerCase())) ||
                    (p.description && p.description.toLowerCase().includes(searchQuery.toLowerCase()));
                return matchesCat && matchesSearch;
            });

            // Apply sorting
            const sortValue = document.getElementById('sortSelect')?.value || 'popularity';
            let sorted = [...filtered];
            if (sortValue === 'newest') {
                sorted.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0));
            } else if (sortValue === 'price_low') {
                sorted.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));
            } else if (sortValue === 'price_high') {
                sorted.sort((a, b) => parseFloat(b.price) - parseFloat(a.price));
            }

            const toDisplay = sorted.slice(0, displayedCount);
            const total = sorted.length;

            // Hide skeletons before rendering
            hideProductSkeletons();

            if (toDisplay.length === 0) {
                productGrid.innerHTML = `
                    <div class="col-span-full text-center py-20 bg-slate-50 rounded-3xl">
                        <h3 class="text-xl font-bold mb-2 text-slate-400">No items found matching your search.</h3>
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
                const cartButtonText = isInCart ? 'GO TO CART' : 'ADD TO CART';
                const cartButtonClass = isInCart 
                    ? 'w-full bg-brand text-white py-3 rounded-lg font-bold text-xs shadow-xl flex items-center justify-center gap-2 hover:bg-teal-600 transition-colors pointer-events-auto'
                    : 'w-full bg-white text-slate-900 py-3 rounded-lg font-bold text-xs shadow-xl flex items-center justify-center gap-2 hover:bg-brand hover:text-white transition-colors pointer-events-auto';
                const cartButtonIcon = isInCart ? 'shopping-cart' : 'shopping-bag';
                const cartButtonAction = isInCart 
                    ? `event.stopPropagation(); if (typeof openCart === 'function') { openCart(); } else if (typeof toggleDrawer === 'function') { toggleDrawer('cart'); }`
                    : `event.stopPropagation(); addToCartFromGrid(${product.id}, '${product.name.replace(/'/g, "\\'")}')`;
                
                return `
                <div class="group cursor-pointer product-card" onclick="window.location.href='product.php?id=${product.id}'">
                    <div class="relative aspect-[3/4] overflow-hidden rounded-xl bg-slate-50 mb-5">
                        <img src="${imageUrl}" alt="${product.name}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                        ${discount > 0 ? `<div class="absolute top-3 left-3 bg-slate-900 text-white text-[9px] font-black px-2 py-1 rounded tracking-tighter">-${discount}% OFF</div>` : ''}
                        ${product.is_featured ? '<div class="absolute top-3 left-3 bg-brand text-white text-[9px] font-black px-2 py-1 rounded tracking-tighter">FEATURED</div>' : ''}
                        
                        <div class="quick-add absolute inset-0 bg-black/5 opacity-0 transition-all flex flex-col items-center justify-end p-4 translate-y-4 pointer-events-none">
                            <button onclick="${cartButtonAction}" class="${cartButtonClass}">
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
                showingText.innerText = `Showing ${displayedCount} of ${total} items`;
                const progress = (displayedCount / total) * 100;
                progressBar.style.width = `${progress}%`;
            } else {
                loadMoreSection.classList.add('hidden');
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
                    
                    // Show success message
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

        function showToast(msg) {
            if (toast && toastMsg) {
                toastMsg.innerText = msg;
                toast.classList.add('toast-active');
                setTimeout(() => toast.classList.remove('toast-active'), 3000);
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

        function handleSearch() {
            const query = searchInput?.value?.trim();
            if (query && query.length >= 2) {
                // Use secure search API
                window.location.href = `shop.php?search=${encodeURIComponent(query)}`;
            } else if (query && query.length < 2) {
                showToast('Please enter at least 2 characters to search');
            }
            hideAutocomplete();
        }

        // Make handleSearch globally available for header.php
        window.handleSearch = handleSearch;

        function subscribeNewsletter() {
            const email = document.getElementById('newsletterEmail')?.value;
            if (email) {
                showToast('Thank you for subscribing!');
                document.getElementById('newsletterEmail').value = '';
            }
        }

        // Checkout function
        function checkout() {
            if (window.cartData && window.cartData.count > 0) {
                window.location.href = 'checkout.php';
            } else {
                showToast('Your cart is empty');
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
                
                // Show loading skeletons
                if (type === 'cart') {
                    showCartSkeletons();
                    if (typeof renderCart === 'function') {
                        renderCart().then(() => hideCartSkeletons()).catch(() => hideCartSkeletons());
                    } else {
                        loadCartData().then(() => hideCartSkeletons()).catch(() => hideCartSkeletons());
                    }
                } else if (type === 'wishlist') {
                    showWishlistSkeletons();
                    if (typeof renderWishlist === 'function') {
                        renderWishlist().then(() => hideWishlistSkeletons()).catch(() => hideWishlistSkeletons());
                    } else {
                        loadWishlistData().then(() => hideWishlistSkeletons()).catch(() => hideWishlistSkeletons());
                    }
                }
            }
        }

        // Show cart loading skeletons
        function showCartSkeletons() {
            const cartItems = document.getElementById('cartItems');
            const cartLoadingSkeleton = document.getElementById('cartLoadingSkeleton');
            if (cartLoadingSkeleton) {
                cartLoadingSkeleton.classList.remove('hidden');
            }
        }

        // Hide cart loading skeletons
        function hideCartSkeletons() {
            const cartLoadingSkeleton = document.getElementById('cartLoadingSkeleton');
            if (cartLoadingSkeleton) {
                cartLoadingSkeleton.classList.add('hidden');
            }
        }

        // Show wishlist loading skeletons
        function showWishlistSkeletons() {
            const wishlistLoadingSkeleton = document.getElementById('wishlistLoadingSkeleton');
            if (wishlistLoadingSkeleton) {
                wishlistLoadingSkeleton.classList.remove('hidden');
            }
        }

        // Hide wishlist loading skeletons
        function hideWishlistSkeletons() {
            const wishlistLoadingSkeleton = document.getElementById('wishlistLoadingSkeleton');
            if (wishlistLoadingSkeleton) {
                wishlistLoadingSkeleton.classList.add('hidden');
            }
        }

        // Load cart data
        async function loadCartData() {
            try {
                const response = await fetch('api/v1/cart.php');
                const data = await response.json();
                if (data.success && typeof renderCart === 'function') {
                    await renderCart();
                }
            } catch (error) {
                console.error('Error loading cart:', error);
            }
        }

        // Load wishlist data
        async function loadWishlistData() {
            try {
                const response = await fetch('api/v1/wishlist.php');
                const data = await response.json();
                if (data.success && typeof renderWishlist === 'function') {
                    await renderWishlist();
                }
            } catch (error) {
                console.error('Error loading wishlist:', error);
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
        window.selectSuggestionByIndex = selectSuggestionByIndex;
        window.selectSuggestion = selectSuggestion;

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