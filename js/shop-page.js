/**
 * Tivora Electronics - Shop Page
 * Handles product loading, filtering, sorting, and search
 */

// State management
let allProducts = [];
let filteredProducts = [];
let categories = [];
let currentFilters = {
    search: '',
    category: '',
    priceMin: null,
    priceMax: null,
    sort: 'created_at',
    inStock: false
};
let isLoading = false;

// Initialize shop page
async function initShopPage() {
    showLoading();
    
    // Load categories and products in parallel
    await Promise.all([
        loadCategories(),
        loadProducts()
    ]);
    
    // Setup event listeners
    setupEventListeners();
    
    // Check URL parameters for initial filters
    applyURLFilters();
}

// Load categories from API
async function loadCategories() {
    try {
        const response = await fetch('api/v1/categories.php');
        const result = await response.json();
        
        if (result.success && result.data) {
            categories = result.data;
            renderCategoryFilter();
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Load products from API
async function loadProducts() {
    try {
        isLoading = true;
        showLoading();
        
        const response = await fetch('api/v1/products.php');
        const result = await response.json();
        
        if (result.success && result.data) {
            allProducts = result.data;
            applyFilters();
        } else {
            showError(result.message || 'Failed to load products');
        }
    } catch (error) {
        console.error('Error loading products:', error);
        showError('An error occurred while loading products');
    } finally {
        isLoading = false;
    }
}

// Render category filter dropdown
function renderCategoryFilter() {
    const categorySelect = document.getElementById('filter-category');
    if (!categorySelect) return;
    
    // Keep the "All Categories" option
    categorySelect.innerHTML = '<option value="">All Categories</option>';
    
    // Add categories
    categories.forEach(category => {
        const option = document.createElement('option');
        option.value = category.id;
        option.textContent = category.name;
        categorySelect.appendChild(option);
    });
}

// Setup event listeners
function setupEventListeners() {
    // Search input with debounce
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                currentFilters.search = e.target.value.toLowerCase().trim();
                applyFilters();
            }, 300);
        });
    }
    
    // Category filter
    const categorySelect = document.getElementById('filter-category');
    if (categorySelect) {
        categorySelect.addEventListener('change', (e) => {
            currentFilters.category = e.target.value;
            applyFilters();
        });
    }
    
    // Price filter
    const priceSelect = document.getElementById('filter-price');
    if (priceSelect) {
        priceSelect.addEventListener('change', (e) => {
            const value = e.target.value;
            if (value === '') {
                currentFilters.priceMin = null;
                currentFilters.priceMax = null;
            } else if (value.includes('+')) {
                currentFilters.priceMin = parseInt(value.replace('+', ''));
                currentFilters.priceMax = null;
            } else {
                const [min, max] = value.split('-').map(v => parseInt(v));
                currentFilters.priceMin = min;
                currentFilters.priceMax = max;
            }
            applyFilters();
        });
    }
    
    // In stock filter
    const stockCheckbox = document.getElementById('filter-stock');
    if (stockCheckbox) {
        stockCheckbox.addEventListener('change', (e) => {
            currentFilters.inStock = e.target.checked;
            applyFilters();
        });
    }
    
    // Sort option
    const sortSelect = document.getElementById('sort-option');
    if (sortSelect) {
        sortSelect.addEventListener('change', (e) => {
            currentFilters.sort = e.target.value;
            applyFilters();
        });
    }
    
    // Clear filters button
    const clearBtn = document.getElementById('clear-filters');
    if (clearBtn) {
        clearBtn.addEventListener('click', clearFilters);
    }
}

// Apply URL parameters as filters
function applyURLFilters() {
    const urlParams = new URLSearchParams(window.location.search);
    
    const category = urlParams.get('category');
    if (category) {
        currentFilters.category = category;
        const categorySelect = document.getElementById('filter-category');
        if (categorySelect) categorySelect.value = category;
    }
    
    const search = urlParams.get('search');
    if (search) {
        currentFilters.search = search.toLowerCase();
        const searchInput = document.getElementById('search-input');
        if (searchInput) searchInput.value = search;
    }
    
    if (category || search) {
        applyFilters();
    }
}

// Clear all filters
function clearFilters() {
    // Reset state
    currentFilters = {
        search: '',
        category: '',
        priceMin: null,
        priceMax: null,
        sort: 'created_at',
        inStock: false
    };
    
    // Reset UI
    const searchInput = document.getElementById('search-input');
    const categorySelect = document.getElementById('filter-category');
    const priceSelect = document.getElementById('filter-price');
    const sortSelect = document.getElementById('sort-option');
    const stockCheckbox = document.getElementById('filter-stock');
    
    if (searchInput) searchInput.value = '';
    if (categorySelect) categorySelect.value = '';
    if (priceSelect) priceSelect.value = '';
    if (sortSelect) sortSelect.value = 'created_at';
    if (stockCheckbox) stockCheckbox.checked = false;
    
    // Apply filters
    applyFilters();
    
    // Clear URL params
    window.history.replaceState({}, '', window.location.pathname);
}

// Apply all filters and sort
function applyFilters() {
    filteredProducts = allProducts.filter(product => {
        // Search filter
        if (currentFilters.search) {
            const searchTerm = currentFilters.search;
            const matchesSearch = 
                product.name.toLowerCase().includes(searchTerm) ||
                (product.description && product.description.toLowerCase().includes(searchTerm)) ||
                (product.sku && product.sku.toLowerCase().includes(searchTerm));
            if (!matchesSearch) return false;
        }
        
        // Category filter
        if (currentFilters.category) {
            if (product.category.id !== parseInt(currentFilters.category)) {
                return false;
            }
        }
        
        // Price filter
        if (currentFilters.priceMin !== null) {
            if (product.price < currentFilters.priceMin) return false;
        }
        if (currentFilters.priceMax !== null) {
            if (product.price > currentFilters.priceMax) return false;
        }
        
        // Always hide out-of-stock products
        if (!product.in_stock) {
            return false;
        }
        
        return true;
    });
    
    // Apply sorting
    sortProducts();
    
    // Render
    renderProducts();
    updateResultsCount();
}

// Sort products based on current sort option
function sortProducts() {
    switch (currentFilters.sort) {
        case 'price_asc':
            filteredProducts.sort((a, b) => a.price - b.price);
            break;
        case 'price_desc':
            filteredProducts.sort((a, b) => b.price - a.price);
            break;
        case 'name':
            filteredProducts.sort((a, b) => a.name.localeCompare(b.name));
            break;
        case 'name_desc':
            filteredProducts.sort((a, b) => b.name.localeCompare(a.name));
            break;
        case 'discount':
            filteredProducts.sort((a, b) => (b.discount_percentage || 0) - (a.discount_percentage || 0));
            break;
        case 'created_at':
        default:
            // Keep original order (newest first from API)
            break;
    }
}

// Render products grid
function renderProducts() {
    const container = document.getElementById('products-container');
    if (!container) return;
    
    if (filteredProducts.length === 0) {
        container.innerHTML = `
            <div class="col-span-full text-center py-20">
                <i data-lucide="search-x" class="w-16 h-16 text-gray-400 mx-auto mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No products found</h3>
                <p class="text-gray-500 mb-6">Try adjusting your filters or search terms</p>
                <button onclick="clearFilters()" class="px-6 py-3 bg-teal-500 text-white rounded-xl hover:bg-teal-600 transition-colors font-medium">
                    Clear All Filters
                </button>
            </div>
        `;
        if (window.lucide) lucide.createIcons();
        return;
    }
    
    container.innerHTML = filteredProducts.map(product => {
        // Discount badge
        const discountBadge = product.discount_percentage ? 
            `<div class="absolute top-2 left-2 sm:top-4 sm:left-4 z-10 bg-teal-500 text-white text-xs font-bold px-2 py-1 sm:px-3 sm:py-1.5 rounded-full shadow-lg">-${product.discount_percentage}% SALE</div>` : '';
        
        // Original price
        const originalPrice = product.original_price ? 
            `<span class="text-sm sm:text-base md:text-lg text-gray-400 line-through">₹${parseFloat(product.original_price).toLocaleString('en-IN')}</span>` : '';
        
        // Product image
        const productImage = product.image ? 
            `<img src="${product.image}" alt="${product.name}" class="object-cover w-full h-48 sm:h-64 md:h-80 transform group-hover:scale-105 transition-transform duration-500">` :
            `<div class="w-full h-48 sm:h-64 md:h-80 bg-gray-200 flex items-center justify-center text-gray-400">
                <i data-lucide="image-off" class="w-12 h-12"></i>
            </div>`;
        
        // Category badge
        const categoryBadge = product.category && product.category.name ? 
            `<span class="text-xs text-teal-600 font-medium bg-teal-50 px-2 py-1 rounded-full">${product.category.name}</span>` : '';
        
        return `
            <div class="group relative bg-gray-50 rounded-2xl sm:rounded-3xl overflow-hidden border border-gray-100 transition-all hover:shadow-xl hover:shadow-red-50">
                ${discountBadge}
                <a href="product.php?id=${product.id}" class="block aspect-w-16 aspect-h-10 overflow-hidden bg-gray-200 cursor-pointer">
                    ${productImage}
                </a>
                <div class="p-4 sm:p-6 md:p-8">
                    <div class="flex justify-between items-start mb-2">
                        <a href="product.php?id=${product.id}" class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 group-hover:text-teal-600 transition-colors cursor-pointer">${product.name}</a>
                    </div>
                    ${categoryBadge ? `<div class="mb-2">${categoryBadge}</div>` : ''}
                    <p class="text-xs sm:text-sm text-gray-500 mb-3 sm:mb-4 line-clamp-2">${product.description || 'High-quality Smart TV'}</p>
                    
                    <div class="flex items-center gap-2 sm:gap-4 mb-4 sm:mb-6">
                        <span class="text-xl sm:text-2xl md:text-3xl font-bold text-teal-600">₹${parseFloat(product.price).toLocaleString('en-IN')}</span>
                        ${originalPrice}
                    </div>

                    <div class="flex gap-2 sm:gap-3">
                        <button 
                            data-product-id="${product.id}" 
                            data-product-name="${product.name.replace(/'/g, '&apos;')}" 
                            data-product-price="${product.price}" 
                            class="add-to-cart-btn flex-1 bg-teal-500 text-white py-2.5 sm:py-3 px-4 sm:px-6 rounded-xl text-sm sm:text-base font-medium hover:bg-teal-600 transition-colors flex items-center justify-center gap-2 shadow-md shadow-teal-100"
                        >
                            <i data-lucide="shopping-bag" class="w-4 h-4"></i> 
                            <span class="hidden sm:inline">Add to Cart</span>
                            <span class="sm:hidden">Cart</span>
                        </button>
                        <button 
                            data-product-id="${product.id}" 
                            data-product-name="${product.name.replace(/'/g, '&apos;')}" 
                            data-product-price="${product.price}" 
                            class="wishlist-btn"
                        >
                            <i data-lucide="heart" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    // Re-initialize icons
    if (window.lucide) lucide.createIcons();
    
    // Attach button listeners
    if (window.attachProductButtonListeners) {
        window.attachProductButtonListeners();
    }
    
    // Update cart/wishlist button states
    if (window.updateAddToCartButtonStates) {
        window.updateAddToCartButtonStates();
    }
    if (window.updateWishlistButtonStates) {
        window.updateWishlistButtonStates();
    }
}

// Update results count
function updateResultsCount() {
    const resultsNumber = document.getElementById('results-number');
    const resultsText = document.getElementById('results-text');
    
    if (resultsNumber) {
        resultsNumber.textContent = filteredProducts.length;
    }
    
    if (resultsText) {
        const totalProducts = allProducts.length;
        if (filteredProducts.length === totalProducts) {
            resultsText.innerHTML = `Showing all <strong>${filteredProducts.length}</strong> products`;
        } else {
            resultsText.innerHTML = `Showing <strong>${filteredProducts.length}</strong> of ${totalProducts} products`;
        }
    }
}

// Show loading state
function showLoading() {
    const container = document.getElementById('products-container');
    if (!container) return;
    
    container.innerHTML = `
        <div class="col-span-full text-center py-20">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-teal-500 mb-4"></div>
            <p class="text-gray-500">Loading products...</p>
        </div>
    `;
}

// Show error state
function showError(message) {
    const container = document.getElementById('products-container');
    if (!container) return;
    
    container.innerHTML = `
        <div class="col-span-full text-center py-20">
            <i data-lucide="alert-circle" class="w-16 h-16 text-red-400 mx-auto mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Error Loading Products</h3>
            <p class="text-gray-500 mb-6">${message}</p>
            <button onclick="loadProducts()" class="px-6 py-3 bg-teal-500 text-white rounded-xl hover:bg-teal-600 transition-colors font-medium">
                Try Again
            </button>
        </div>
    `;
    if (window.lucide) lucide.createIcons();
}

// Expose functions to window
window.initShopPage = initShopPage;
window.clearFilters = clearFilters;
window.loadProducts = loadProducts;

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', initShopPage);
