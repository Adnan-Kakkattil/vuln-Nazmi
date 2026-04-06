<?php
// Start session if not already started and headers not sent
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$is_auth_page = in_array($current_page, ['login.php', 'forgot-password.php', 'reset-password.php', 'signup.php']);
$is_checkout = ($current_page == 'checkout.php');
$is_home = ($current_page == 'index.php');
$is_shop = ($current_page == 'shop.php');

// Check if user is logged in (regular user OR admin)
$isLoggedIn = (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) || 
              (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']));
$isAdmin = isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);

// Get name and email based on user type
if ($isAdmin) {
    $userName = $_SESSION['admin_name'] ?? '';
    $userEmail = $_SESSION['admin_email'] ?? '';
} else {
    $userName = $_SESSION['user_name'] ?? '';
    $userEmail = $_SESSION['user_email'] ?? '';
}

// Get first name for display
$userFirstName = '';
if ($userName) {
    $nameParts = explode(' ', $userName);
    $userFirstName = $nameParts[0];
}

// Get search query from URL if available
$searchQuery = $_GET['search'] ?? '';
?>
<!-- Top Banner -->
<div class="bg-brand text-white text-center py-2.5 text-xs md:text-sm font-bold tracking-wide">
    DIWALI SPECIAL: FLAT 40% OFF ON ETHNIC WEAR | USE CODE: NAZMI40
</div>

<!-- Header -->
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-100">
    <div class="container mx-auto px-4 lg:px-12 flex items-center justify-between h-20 md:h-24">
        
        <!-- Logo -->
        <div class="flex-shrink-0 cursor-pointer lg:w-1/4" onclick="window.location.href='index.php'">
            <h1 class="text-2xl md:text-4xl font-serif font-black tracking-tighter text-slate-900">
                NAZMI<span class="text-brand">.</span>
            </h1>
        </div>

        <!-- Search Bar with Autocomplete -->
        <div class="hidden lg:flex flex-1 max-w-2xl px-8">
            <div class="relative w-full" id="searchContainer">
                <input 
                    id="searchInput"
                    type="text" 
                    placeholder="Search for Kurtas, Lehengas, Designers..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-full py-3.5 pl-6 pr-12 focus:ring-2 focus:ring-brand/20 focus:border-brand outline-none transition-all text-sm"
                    value="<?php echo htmlspecialchars($searchQuery); ?>"
                    autocomplete="off"
                >
                <div class="absolute right-2 top-1/2 -translate-y-1/2 bg-brand p-2.5 rounded-full text-white cursor-pointer hover:bg-teal-600" onclick="handleSearch ? handleSearch() : (window.location.href='shop.php?search=' + encodeURIComponent(document.getElementById('searchInput').value))">
                    <i data-lucide="search" class="w-[18px] h-[18px]"></i>
                </div>
                
                <!-- Autocomplete Dropdown -->
                <div id="searchAutocomplete" class="hidden absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-2xl border border-slate-200 z-50 max-h-96 overflow-y-auto">
                    <div id="autocompleteContent" class="p-2">
                        <!-- Suggestions will be inserted here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Icons -->
        <div class="flex items-center gap-5 md:gap-8 lg:w-1/4 justify-end">
            <?php if ($isLoggedIn): ?>
            <!-- User Dropdown (Logged In) -->
            <div class="hidden md:flex relative" id="user-dropdown-container">
                <button id="user-dropdown-btn" class="flex flex-col items-center cursor-pointer group">
                    <div class="w-8 h-8 bg-teal-100 rounded-full flex items-center justify-center mb-1">
                        <span class="text-teal-600 font-semibold text-sm"><?php echo strtoupper(substr($userFirstName, 0, 1)); ?></span>
                    </div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 group-hover:text-brand"><?php echo htmlspecialchars($userFirstName); ?></span>
                </button>
                <div id="user-dropdown-menu" class="hidden absolute right-0 mt-12 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                    <div class="px-4 py-2 border-b border-gray-100">
                        <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($userName); ?></p>
                        <p class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars($userEmail); ?></p>
                        <?php if ($isAdmin): ?>
                        <span class="inline-block mt-1 px-2 py-0.5 text-xs font-medium bg-teal-100 text-teal-600 rounded">Admin</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($isAdmin): ?>
                    <a href="admin/index.php" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-600">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        Admin Panel
                    </a>
                    <?php endif; ?>
                    <a href="account.php" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-600">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        My Account
                    </a>
                    <a href="orders.php" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-600">
                        <i data-lucide="package" class="w-4 h-4"></i>
                        My Orders
                    </a>
                    <div class="border-t border-gray-100 mt-2 pt-2">
                        <button onclick="logoutUser()" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-teal-600 hover:bg-teal-50">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            Logout
                        </button>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="hidden md:flex flex-col items-center cursor-pointer group" onclick="window.location.href='login.php'">
                <i data-lucide="user" class="w-5 h-5 group-hover:text-brand transition-colors"></i>
                <span class="text-[10px] uppercase font-bold mt-1 text-slate-400 group-hover:text-brand">Login</span>
            </div>
            <?php endif; ?>
            
            <?php if (!$is_auth_page): ?>
            <div class="relative flex flex-col items-center cursor-pointer group" onclick="typeof toggleDrawer === 'function' ? toggleDrawer('wishlist') : (typeof openWishlist === 'function' ? openWishlist() : console.log('Wishlist function not available'))">
                <i id="wishlistIcon" data-lucide="heart" class="w-5 h-5 group-hover:text-red-500 transition-colors"></i>
                <span class="text-[10px] uppercase font-bold mt-1 text-slate-400 group-hover:text-red-500">Wishlist</span>
                <span id="wishlistBadge" class="absolute -top-1 right-0 bg-red-50 text-red-500 border border-red-200 text-[9px] w-4 h-4 rounded-full hidden items-center justify-center font-bold">0</span>
            </div>
            <div class="relative flex flex-col items-center cursor-pointer group" onclick="typeof toggleDrawer === 'function' ? toggleDrawer('cart') : (typeof openCart === 'function' ? openCart() : console.log('Cart function not available'))">
                <i data-lucide="shopping-bag" class="w-5 h-5 group-hover:text-brand transition-colors"></i>
                <span class="text-[10px] uppercase font-bold mt-1 text-slate-400 group-hover:text-brand">Cart</span>
                <span id="cartBadge" class="absolute -top-1 right-0 bg-brand text-white text-[9px] w-4 h-4 rounded-full hidden items-center justify-center font-bold">0</span>
            </div>
            <?php endif; ?>
            
            <button class="lg:hidden" onclick="toggleMobileMenu(true)">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </div>
    </div>

    <!-- Categories Bar -->
    <?php if (!$is_auth_page && !$is_checkout): ?>
    <div class="hidden lg:block bg-white border-t border-slate-50 overflow-x-auto no-scrollbar">
        <div id="categoryContainer" class="container mx-auto px-12 flex justify-center gap-12 py-4">
            <!-- Categories injected by JS -->
        </div>
    </div>
    <?php endif; ?>
</header>

<!-- Mobile Menu -->
<div id="mobileMenu" class="fixed inset-0 z-[110] bg-white transform translate-x-full transition-transform duration-300 hidden">
    <div class="p-6 h-full flex flex-col">
        <div class="flex justify-between items-center mb-12">
            <h1 class="text-2xl font-serif font-black">NAZMI<span class="text-brand">.</span></h1>
            <button onclick="toggleMobileMenu(false)"><i data-lucide="x" class="w-8 h-8"></i></button>
        </div>
        
        <!-- Mobile Search -->
        <div class="mb-6 relative" id="mobileSearchContainer">
            <input 
                id="mobileSearchInput"
                type="text" 
                placeholder="Search for products..."
                class="w-full bg-slate-50 border border-slate-200 rounded-full py-3 pl-6 pr-12 focus:ring-2 focus:ring-brand/20 focus:border-brand outline-none transition-all text-sm"
                autocomplete="off"
            >
            <div class="absolute right-2 top-1/2 -translate-y-1/2 bg-brand p-2.5 rounded-full text-white cursor-pointer hover:bg-teal-600" onclick="handleMobileSearch()">
                <i data-lucide="search" class="w-[18px] h-[18px]"></i>
            </div>
            
            <!-- Mobile Autocomplete Dropdown -->
            <div id="mobileSearchAutocomplete" class="hidden absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-2xl border border-slate-200 z-50 max-h-64 overflow-y-auto">
                <div id="mobileAutocompleteContent" class="p-2">
                    <!-- Mobile suggestions will be inserted here -->
                </div>
            </div>
        </div>
        
        <div id="mobileCategoryList" class="flex flex-col gap-8">
            <!-- Mobile categories injected by JS -->
        </div>
        <div class="mt-auto border-t pt-8 grid grid-cols-2 gap-4">
            <button onclick="window.location.href='<?php echo $isLoggedIn ? 'account.php' : 'login.php'; ?>'" class="bg-slate-900 text-white py-4 rounded-xl font-bold"><?php echo $isLoggedIn ? 'Account' : 'Sign In'; ?></button>
            <button onclick="window.location.href='shop.php'" class="border border-slate-200 py-4 rounded-xl font-bold">Shop</button>
        </div>
    </div>
</div>

<!-- User Dropdown and Mobile Menu Script -->
<script>
// User Dropdown Toggle
document.addEventListener('DOMContentLoaded', function() {
    const dropdownBtn = document.getElementById('user-dropdown-btn');
    const dropdownMenu = document.getElementById('user-dropdown-menu');
    
    if (dropdownBtn && dropdownMenu) {
        dropdownBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('hidden');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });
    }
});

// Mobile Menu Toggle
function toggleMobileMenu(show) {
    const menu = document.getElementById('mobileMenu');
    if (!menu) return;
    if (show) {
        menu.classList.remove('hidden');
        setTimeout(() => menu.classList.remove('translate-x-full'), 10);
        document.body.style.overflow = 'hidden';
    } else {
        menu.classList.add('translate-x-full');
        setTimeout(() => menu.classList.add('hidden'), 300);
        document.body.style.overflow = 'auto';
    }
}

// Make toggleMobileMenu globally available
window.toggleMobileMenu = toggleMobileMenu;

// Logout Function
async function logoutUser() {
    try {
        const response = await fetch('api/v1/auth.php', {
            method: 'DELETE',
            credentials: 'include'
        });
        
        const result = await response.json();
        
        if (result.success) {
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 100);
        } else {
            alert('Logout failed. Please try again.');
        }
    } catch (error) {
        console.error('Logout error:', error);
        window.location.href = 'index.php';
    }
}

// Make logoutUser globally available
window.logoutUser = logoutUser;

// Search handler
function handleSearch() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput && searchInput.value.trim().length >= 2) {
        window.location.href = 'shop.php?search=' + encodeURIComponent(searchInput.value.trim());
    } else if (searchInput && searchInput.value.trim().length < 2) {
        alert('Please enter at least 2 characters to search');
    }
}

// Mobile search handler
function handleMobileSearch() {
    const mobileSearchInput = document.getElementById('mobileSearchInput');
    if (mobileSearchInput && mobileSearchInput.value.trim().length >= 2) {
        window.location.href = 'shop.php?search=' + encodeURIComponent(mobileSearchInput.value.trim());
        toggleMobileMenu(false);
    } else if (mobileSearchInput && mobileSearchInput.value.trim().length < 2) {
        alert('Please enter at least 2 characters to search');
    }
}

// Make functions globally available
window.handleSearch = handleSearch;
window.handleMobileSearch = handleMobileSearch;

// Search input enter key handler
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                handleSearch();
            }
        });
    }
});
</script>
