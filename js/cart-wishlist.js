/**
 * Tivora Electronics - Cart and Wishlist Management
 * Handles API calls and UI updates for cart and wishlist
 */

let cartData = { items: [], total: 0, count: 0 };
let wishlistData = { items: [], count: 0 };

// Initialize counters and data on page load
async function initCartWishlist() {
    await updateCounters();

    // Attach event listeners to existing buttons
    attachProductButtonListeners();

    // Cart and Wishlist button click handlers for header
    document.getElementById('cart-btn')?.addEventListener('click', openCart);
    document.getElementById('cart-btn-mobile')?.addEventListener('click', openCart);
    document.getElementById('wishlist-btn')?.addEventListener('click', openWishlist);
    document.getElementById('wishlist-btn-mobile')?.addEventListener('click', openWishlist);

    // Close sidebars when clicking overlay
    document.getElementById('cart-overlay')?.addEventListener('click', closeCart);
    document.getElementById('wishlist-overlay')?.addEventListener('click', closeWishlist);

    // Mobile Menu Toggle - works on all pages
    const menuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    if (menuBtn && mobileMenu) {
        menuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!menuBtn.contains(e.target) && !mobileMenu.contains(e.target)) {
                mobileMenu.classList.add('hidden');
            }
        });

        // Close mobile menu when clicking a link
        mobileMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
            });
        });
    }

    // Navbar Scroll Effect - works on all pages
    const navbar = document.getElementById('navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('glass-nav', 'shadow-sm');
                navbar.classList.remove('py-4');
                navbar.classList.add('py-2');
            } else {
                navbar.classList.remove('glass-nav', 'shadow-sm', 'py-2');
                navbar.classList.add('py-4');
            }
        });
    }
}

// Update counters and internal data from API
async function updateCounters(providedCartData = null, providedWishlistData = null) {
    try {
        if (providedCartData) {
            cartData = providedCartData;
        }
        if (providedWishlistData) {
            wishlistData = providedWishlistData;
        }

        // If either is missing, fetch them in parallel
        if (!providedCartData || !providedWishlistData) {
            const fetches = [];
            if (!providedCartData) fetches.push(fetch('api/v1/cart.php').then(r => r.json()));
            else fetches.push(Promise.resolve({ success: true, data: providedCartData }));

            if (!providedWishlistData) fetches.push(fetch('api/v1/wishlist.php').then(r => r.json()));
            else fetches.push(Promise.resolve({ success: true, data: providedWishlistData }));

            const [cartResult, wishlistResult] = await Promise.all(fetches);

            if (cartResult.success && !providedCartData) {
                cartData = cartResult.data;
            }
            if (wishlistResult.success && !providedWishlistData) {
                wishlistData = wishlistResult.data;
            }
        }

        updateCountBadges();
        updateWishlistButtonStates();
        updateAddToCartButtonStates();

    } catch (error) {
        console.error('Error updating cart/wishlist:', error);
    }
}

function updateCountBadges() {
    // Try multiple possible IDs for compatibility
    const cartCount = document.getElementById('cart-count') || document.getElementById('cartBadge');
    const wishlistCount = document.getElementById('wishlist-count') || document.getElementById('wishlistBadge');
    const cartCountMobile = document.getElementById('cart-count-mobile') || document.getElementById('cartBadge-mobile');
    const wishlistCountMobile = document.getElementById('wishlist-count-mobile') || document.getElementById('wishlistBadge-mobile');

    // Calculate counts from items array if count property is missing
    const cartCountValue = cartData.count !== undefined ? cartData.count : (cartData.items ? cartData.items.reduce((sum, item) => sum + (item.quantity || 1), 0) : 0);
    const wishlistCountValue = wishlistData.count !== undefined ? wishlistData.count : (wishlistData.items ? wishlistData.items.length : 0);

    [cartCount, cartCountMobile].forEach(el => {
        if (el) {
            el.textContent = cartCountValue;
            if (cartCountValue > 0) {
                el.classList.add('show');
                el.style.display = 'flex';
                el.classList.remove('hidden');
            } else {
                el.classList.remove('show');
                el.style.display = 'none';
                el.classList.add('hidden');
            }
        }
    });

    [wishlistCount, wishlistCountMobile].forEach(el => {
        if (el) {
            el.textContent = wishlistCountValue;
            if (wishlistCountValue > 0) {
                el.classList.add('show');
                el.style.display = 'flex';
                el.classList.remove('hidden');
            } else {
                el.classList.remove('show');
                el.style.display = 'none';
                el.classList.add('hidden');
            }
        }
    });
}

// Add to cart function
async function addToCart(productId, productName, quantity = 1, removeFromWishlistAfter = false) {
    try {
        const response = await fetch('api/v1/cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ product_id: productId, quantity: quantity })
        });
        const result = await response.json();

        if (result.success) {
            // Update using data returned from API to avoid extra GET request
            await updateCounters(result.data, null);

            // Update cart sidebar if it's open
            const cartSidebar = document.getElementById('cart-sidebar') || document.getElementById('cartDrawer');
            if (cartSidebar && (cartSidebar.classList.contains('show') || !cartSidebar.classList.contains('translate-x-full'))) {
                renderCart();
            }

            // Show success message
            if (typeof showToast === 'function') {
                showToast(`Added ${productName} to your bag!`);
            }

            // If moving from wishlist, remove from wishlist after adding to cart
            if (removeFromWishlistAfter) {
                try {
                    // Check if item is in wishlist before removing
                    const isInWishlist = wishlistData && wishlistData.items && wishlistData.items.some(item => item.product_id == productId);
                    
                    if (isInWishlist) {
                        const wishlistResponse = await fetch('api/v1/wishlist.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            credentials: 'include',
                            body: JSON.stringify({ product_id: productId })
                        });
                        const wishlistResult = await wishlistResponse.json();
                        
                        if (wishlistResult.success) {
                            // Update wishlist data
                            await updateCounters(null, wishlistResult.data);
                            
                            // Update wishlist sidebar if it's open
                            const wishlistSidebar = document.getElementById('wishlist-sidebar') || document.getElementById('wishlistDrawer');
                            if (wishlistSidebar && (wishlistSidebar.classList.contains('show') || !wishlistSidebar.classList.contains('translate-x-full'))) {
                                renderWishlist();
                            }
                        }
                    }
                } catch (wishlistError) {
                    console.error('Error removing from wishlist:', wishlistError);
                }
            }
        } else {
            if (result.requires_login) {
                if (typeof showToast === 'function') {
                    showToast('Please login to add items to cart');
                }
                setTimeout(() => window.location.href = 'login.php', 1000);
            } else {
                if (typeof showToast === 'function') {
                    showToast(result.message || 'Failed to add to cart');
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

// Toggle wishlist function
async function toggleWishlist(productId, productName) {
    try {
        const response = await fetch('api/v1/wishlist.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId })
        });
        const result = await response.json();

        if (result.success) {
            // Update using data returned from API to avoid extra GET request
            await updateCounters(null, result.data);

            // Update wishlist sidebar if it's open
            if (document.getElementById('wishlist-sidebar')?.classList.contains('show')) {
                renderWishlist();
            }
        } else {
            if (result.requires_login) {
                setTimeout(() => window.location.href = 'login.php', 500);
            }
        }
    } catch (error) {
        console.error('Error toggling wishlist:', error);
    }
}

// Cart and Wishlist Drawer Functions
function toggleDrawer(type) {
    closeAllDrawers();
    const drawerOverlay = document.getElementById('drawerOverlay');
    const drawer = type === 'cart' ? document.getElementById('cartDrawer') : document.getElementById('wishlistDrawer');
    
    if (drawerOverlay && drawer) {
        drawerOverlay.classList.remove('opacity-0', 'pointer-events-none');
        drawer.classList.remove('translate-x-full');
        document.body.style.overflow = 'hidden';
        
        if (type === 'cart') {
            renderCart();
        } else if (type === 'wishlist') {
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

async function renderCart() {
    const cartItems = document.getElementById('cartItems');
    const cartFooter = document.getElementById('cartFooter');
    const cartTotal = document.getElementById('cartTotal');

    if (!cartItems) return;

    if (cartData.items.length === 0) {
        cartItems.innerHTML = `
            <div class="text-center py-20">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="shopping-bag" class="text-slate-200 w-10 h-10"></i>
                </div>
                <h4 class="text-lg font-bold text-slate-400">Your bag is empty</h4>
                <button onclick="closeAllDrawers()" class="mt-4 text-brand font-bold text-sm underline underline-offset-4">START SHOPPING</button>
            </div>
        `;
        if (cartFooter) cartFooter.style.display = 'none';
        if (window.lucide) window.lucide.createIcons();
        return;
    }

    if (cartFooter) cartFooter.style.display = 'block';
    if (cartTotal) cartTotal.textContent = `₹${cartData.total.toLocaleString('en-IN')}`;

    cartItems.innerHTML = cartData.items.map(item => `
        <div class="flex gap-4 group animate-fadeIn">
            <div class="w-24 h-32 rounded-xl overflow-hidden bg-slate-50 flex-shrink-0">
                <img src="${item.image || '431.webp'}" alt="${item.name}" class="w-full h-full object-cover">
            </div>
            <div class="flex-1 py-1">
                <h4 class="font-bold text-slate-800 leading-tight mb-1 line-clamp-2">${item.name}</h4>
                <p class="text-sm font-black text-slate-900 mb-4">₹${(item.unit_price * item.quantity).toLocaleString('en-IN')}</p>
                <div class="flex items-center justify-between">
                    <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden">
                        <button onclick="updateCartQuantity(${item.product_id}, -1)" class="px-3 py-1 hover:bg-slate-50 text-slate-400">-</button>
                        <span class="px-3 py-1 font-bold text-sm min-w-[32px] text-center">${item.quantity}</span>
                        <button onclick="updateCartQuantity(${item.product_id}, 1)" class="px-3 py-1 hover:bg-slate-50 text-slate-400">+</button>
                    </div>
                    <button onclick="removeFromCart(${item.product_id})" class="text-xs font-bold text-red-400 hover:text-red-500 uppercase tracking-tighter">Remove</button>
                </div>
            </div>
        </div>
    `).join('');

    if (window.lucide) window.lucide.createIcons();
}

async function renderWishlist() {
    const wishlistItems = document.getElementById('wishlistItems');
    if (!wishlistItems) return;

    if (wishlistData.items.length === 0) {
        wishlistItems.innerHTML = `
            <div class="text-center py-20">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="heart" class="text-slate-200 w-10 h-10"></i>
                </div>
                <h4 class="text-lg font-bold text-slate-400">Your wishlist is empty</h4>
            </div>
        `;
        if (window.lucide) window.lucide.createIcons();
        return;
    }

    wishlistItems.innerHTML = wishlistData.items.map(item => `
        <div class="flex gap-4 animate-fadeIn">
            <div class="w-24 h-32 rounded-xl overflow-hidden bg-slate-50 flex-shrink-0">
                <img src="${item.image || '431.webp'}" alt="${item.name}" class="w-full h-full object-cover">
            </div>
            <div class="flex-1 py-1">
                <h4 class="font-bold text-slate-800 leading-tight mb-1 line-clamp-1">${item.name}</h4>
                <p class="text-sm font-black text-slate-900 mb-6">₹${item.price.toLocaleString('en-IN')}</p>
                <div class="flex items-center gap-4">
                    <button onclick="moveToBagFromWishlist(${item.product_id}, '${item.name.replace(/'/g, "\\'")}')" class="flex-1 bg-slate-900 text-white py-2 rounded-lg text-xs font-bold hover:bg-brand transition-colors">
                        MOVE TO BAG
                    </button>
                    <button onclick="removeFromWishlist(${item.product_id})" class="p-2 text-slate-300 hover:text-red-500">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');

    if (window.lucide) window.lucide.createIcons();
}

async function updateCartQuantity(productId, change) {
    const item = cartData.items.find(item => item.product_id === productId);
    if (item) {
        const newQuantity = item.quantity + change;
        if (newQuantity <= 0) {
            removeFromCart(productId);
        } else {
            try {
                const response = await fetch('api/v1/cart.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ product_id: productId, quantity: newQuantity })
                });
                const result = await response.json();
                if (result.success) {
                    await updateCounters();
                    if (document.getElementById('cartDrawer') && !document.getElementById('cartDrawer').classList.contains('translate-x-full')) {
                        renderCart();
                    }
                }
            } catch (error) {
                console.error('Error updating cart quantity:', error);
            }
        }
    }
}

async function removeFromCart(productId) {
    try {
        const response = await fetch('api/v1/cart.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId })
        });
        const result = await response.json();
        if (result.success) {
            await updateCounters();
            if (document.getElementById('cartDrawer') && !document.getElementById('cartDrawer').classList.contains('translate-x-full')) {
                renderCart();
            }
        }
    } catch (error) {
        console.error('Error removing from cart:', error);
    }
}

async function removeFromWishlist(productId) {
    try {
        const response = await fetch('api/v1/wishlist.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId })
        });
        const result = await response.json();
        if (result.success) {
            await updateCounters();
            if (document.getElementById('wishlistDrawer') && !document.getElementById('wishlistDrawer').classList.contains('translate-x-full')) {
                renderWishlist();
            }
        }
    } catch (error) {
        console.error('Error removing from wishlist:', error);
    }
}

function updateWishlistButtonStates() {
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        const productId = parseInt(btn.getAttribute('data-product-id'));
        if (wishlistData.items.some(item => item.product_id === productId)) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    setTimeout(() => {
        document.querySelectorAll('.wishlist-btn.active svg path').forEach(path => {
            path.setAttribute('fill', '#14b8a6');
            path.setAttribute('stroke', '#14b8a6');
        });
        document.querySelectorAll('.wishlist-btn:not(.active) svg path').forEach(path => {
            path.setAttribute('fill', 'none');
            path.setAttribute('stroke', '#9ca3af');
        });
    }, 10);
}

function updateAddToCartButtonStates() {
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        const productId = parseInt(btn.getAttribute('data-product-id'));
        const isInCart = cartData.items.some(item => item.product_id === productId);
        const iconElement = btn.querySelector('i[data-lucide]');
        // Support multiple span selectors: .btn-text (product page), span.hidden.sm:inline (shop/index pages)
        const textSpan = btn.querySelector('.btn-text, span.hidden.sm\\:inline');

        if (isInCart) {
            btn.classList.remove('bg-teal-500', 'hover:bg-teal-600', 'text-white', 'shadow-lg', 'shadow-teal-200');
            btn.classList.add('bg-white', 'text-teal-600', 'border-2', 'border-teal-500', 'hover:bg-teal-50');
            if (iconElement) iconElement.setAttribute('data-lucide', 'shopping-cart');
            if (textSpan) textSpan.textContent = 'Go to Cart';
            btn.setAttribute('data-in-cart', 'true');
        } else {
            btn.classList.remove('bg-white', 'text-teal-600', 'border-2', 'border-teal-500', 'hover:bg-teal-50');
            btn.classList.add('bg-teal-500', 'hover:bg-teal-600', 'text-white');
            if (iconElement) iconElement.setAttribute('data-lucide', 'shopping-bag');
            if (textSpan) textSpan.textContent = 'Add to Cart';
            btn.removeAttribute('data-in-cart');
        }
    });
    if (window.lucide) window.lucide.createIcons();
}

function attachProductButtonListeners() {
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.onclick = function (e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');
            if (this.getAttribute('data-in-cart') === 'true') {
                openCart();
            } else {
                addToCart(productId, productName);
            }
        };
    });

    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        btn.onclick = function (e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');
            toggleWishlist(productId, productName);
        };
    });
}

// Move to bag from wishlist (adds to cart and removes from wishlist)
async function moveToBagFromWishlist(productId, productName) {
    await addToCart(productId, productName, 1, true);
}

// Expose functions to window for use by other scripts
window.attachProductButtonListeners = attachProductButtonListeners;
window.addToCart = addToCart;
window.moveToBagFromWishlist = moveToBagFromWishlist;
window.toggleWishlist = toggleWishlist;
window.openCart = openCart;
window.closeCart = closeCart;
window.openWishlist = openWishlist;
window.closeWishlist = closeWishlist;
window.toggleDrawer = toggleDrawer;
window.closeAllDrawers = closeAllDrawers;
window.updateCartQuantity = updateCartQuantity;
window.removeFromCart = removeFromCart;
window.removeFromWishlist = removeFromWishlist;
window.checkout = checkout;

function checkout() {
    if (cartData.items.length === 0) {
        if (typeof showToast === 'function') {
            showToast('Your cart is empty');
        }
        return;
    }
    window.location.href = 'checkout.php';
}

// Initialize on load
document.addEventListener('DOMContentLoaded', initCartWishlist);
