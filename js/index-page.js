// Note: Navbar scroll effect and Mobile Menu Toggle are now handled in cart-wishlist.js (shared across all pages)

// Intersection Observer for Animations
const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.1
};

const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Add simple fade-in effect to sections as you scroll (optional JS enhancement)
document.querySelectorAll('section > div').forEach((el) => {
    el.style.transition = 'all 0.8s ease-out';
});

// Quote Modal Functions
function openQuoteModal() {
    const overlay = document.getElementById('quote-modal-overlay');
    const modal = document.querySelector('#quote-modal > div');

    if (overlay && modal) {
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        setTimeout(() => {
            overlay.style.opacity = '1';
            modal.style.transform = 'scale(1)';
            modal.style.opacity = '1';
        }, 10);

        setTimeout(() => {
            if (window.lucide) lucide.createIcons();
        }, 100);
    }
}

function closeQuoteModal() {
    const overlay = document.getElementById('quote-modal-overlay');
    const modal = document.querySelector('#quote-modal > div');

    if (overlay && modal) {
        overlay.style.opacity = '0';
        modal.style.transform = 'scale(0.95)';
        modal.style.opacity = '0';

        setTimeout(() => {
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
            document.getElementById('quote-form')?.reset();
        }, 300);
    }
}

async function submitQuoteForm(event) {
    event.preventDefault();
    const form = document.getElementById('quote-form');
    const formData = new FormData(form);
    const quoteData = {
        name: formData.get('name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        company: formData.get('company'),
        requirements: formData.get('requirements'),
        notes: formData.get('notes') || ''
    };

    // Validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(quoteData.email)) {
        await Tivora.alert('Please enter a valid email address.', 'error');
        return;
    }

    // Phone validation (10 digits)
    const phoneRegex = /^[0-9]{10}$/;
    // Remove any non-digits for validation if needed, or just check the input
    const cleanPhone = quoteData.phone.replace(/\D/g, '');
    if (!phoneRegex.test(cleanPhone)) {
        await Tivora.alert('Please enter a valid 10-digit mobile number.', 'error');
        return;
    }

    try {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div> Submitting...';

        const response = await fetch('api/v1/b2b/requests.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(quoteData)
        });
        const result = await response.json();

        if (result.success) {
            await Tivora.alert('Your quote request has been submitted successfully! We will contact you soon.', 'success');
            form.reset();
            closeQuoteModal();
        } else {
            await Tivora.alert(result.message || 'Failed to submit quote request. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error submitting quote request:', error);
        await Tivora.alert('An error occurred while submitting your request. Please try again later.', 'error');
    } finally {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i data-lucide="send" class="w-5 h-5"></i> Submit Request';
            if (window.lucide) lucide.createIcons();
        }
    }
}

// Load products from API
async function loadProducts() {
    try {
        const response = await fetch('api/v1/products.php?limit=6&sort=created_at');
        const result = await response.json();
        if (result.success) {
            if (result.data && result.data.length > 0) {
                renderProducts(result.data);
            } else {
                showProductError('No products available at the moment.');
            }
        } else {
            showProductError(result.message || 'Failed to load products');
        }
    } catch (error) {
        console.error('Error loading products:', error);
        showProductError('An error occurred while loading products');
    }
}

function renderProducts(products) {
    const productsGrid = document.getElementById('products-grid');
    if (!productsGrid) return;

    // Filter out products that are out of stock - hide them completely
    const inStockProducts = products.filter(product => product.in_stock);

    if (inStockProducts.length === 0) {
        productsGrid.innerHTML = '<p class="col-span-full text-center text-gray-500 py-8">No products available at the moment.</p>';
        return;
    }

    productsGrid.innerHTML = inStockProducts.map(product => {
        const discountBadge = product.discount_percentage ?
            `<div class="absolute top-2 left-2 sm:top-4 sm:left-4 z-10 bg-teal-500 text-white text-xs font-bold px-2 py-1 sm:px-3 sm:py-1.5 rounded-full shadow-lg">-${product.discount_percentage}% SALE</div>` : '';
        const originalPrice = product.original_price ?
            `<span class="text-sm sm:text-base md:text-lg text-gray-400 line-through">₹${parseFloat(product.original_price).toLocaleString('en-IN')}</span>` : '';
        const productImage = product.image ?
            `<img src="${product.image}" alt="${product.name}" class="object-cover w-full h-48 sm:h-64 md:h-80 transform group-hover:scale-105 transition-transform duration-500">` :
            `<div class="w-full h-48 sm:h-64 md:h-80 bg-gray-200 flex items-center justify-center text-gray-400">No Image</div>`;

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
                            <p class="text-xs sm:text-sm text-gray-500 mb-3 sm:mb-4">${product.description || 'High-quality Smart TV'}</p>
                            <div class="flex items-center gap-2 sm:gap-4 mb-4 sm:mb-6">
                                <span class="text-xl sm:text-2xl md:text-3xl font-bold text-teal-600">₹${parseFloat(product.price).toLocaleString('en-IN')}</span>
                                ${originalPrice}
                            </div>
                            <div class="flex gap-2 sm:gap-3">
                                <button data-product-id="${product.id}" data-product-name="${product.name.replace(/'/g, '&apos;')}" data-product-price="${product.price}" class="add-to-cart-btn flex-1 bg-teal-500 text-white py-2.5 sm:py-3 px-4 sm:px-6 rounded-xl text-sm sm:text-base font-medium hover:bg-teal-600 transition-colors flex items-center justify-center gap-2 shadow-md shadow-teal-100">
                                    <i data-lucide="shopping-bag" class="w-4 h-4"></i> <span class="hidden sm:inline">Add to Cart</span><span class="sm:hidden">Cart</span>
                                </button>
                                <button data-product-id="${product.id}" data-product-name="${product.name.replace(/'/g, '&apos;')}" data-product-price="${product.price}" class="wishlist-btn">
                                    <i data-lucide="heart" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
    }).join('');

    if (window.lucide) lucide.createIcons();
    if (window.attachProductButtonListeners) window.attachProductButtonListeners();
}

function showProductError(message) {
    const productsGrid = document.getElementById('products-grid');
    if (productsGrid) {
        productsGrid.innerHTML = `
                    <div class="col-span-full text-center py-12 text-red-500">
                        <i data-lucide="alert-circle" class="w-12 h-12 mx-auto mb-4"></i>
                        <p>${message}</p>
                    </div>
                `;
        if (window.lucide) lucide.createIcons();
    }
}

// Expose functions to window for inline handlers
window.openQuoteModal = openQuoteModal;
window.closeQuoteModal = closeQuoteModal;
window.submitQuoteForm = submitQuoteForm;
window.loadProducts = loadProducts;

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    loadProducts();
});
