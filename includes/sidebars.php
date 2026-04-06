<!-- Drawer Overlay -->
<div id="drawerOverlay" class="fixed inset-0 z-[90] drawer-overlay opacity-0 pointer-events-none" onclick="closeAllDrawers()"></div>

<!-- Cart Sidebar -->
<div id="cartDrawer" class="fixed top-0 right-0 h-full w-full sm:w-[400px] bg-white z-[100] shadow-2xl drawer translate-x-full flex flex-col">
    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
        <h3 class="text-xl font-bold flex items-center gap-3">
            <i data-lucide="shopping-bag" class="text-brand"></i> Your Bag
        </h3>
        <button onclick="closeAllDrawers()" class="p-2 hover:bg-slate-100 rounded-full transition-colors">
            <i data-lucide="x" class="w-6 h-6 text-slate-400"></i>
        </button>
    </div>
    <div id="cartItems" class="flex-1 overflow-y-auto p-6 space-y-6 no-scrollbar">
        <!-- Cart items injected here -->
    </div>
    <div id="cartFooter" class="p-6 border-t border-slate-100 bg-slate-50/50">
        <div class="flex justify-between items-center mb-6">
            <span class="text-slate-500 font-medium">Subtotal</span>
            <span id="cartTotal" class="text-2xl font-black text-slate-900">₹0</span>
        </div>
        <button onclick="window.checkout ? window.checkout() : (window.location.href='checkout.php')" class="w-full bg-brand text-white py-4 rounded-2xl font-black text-lg shadow-xl shadow-brand/20 hover:bg-teal-600 transition-all flex items-center justify-center gap-3">
            PROCEED TO CHECKOUT <i data-lucide="arrow-right" class="w-5 h-5"></i>
        </button>
    </div>
</div>

<!-- Wishlist Sidebar -->
<div id="wishlistDrawer" class="fixed top-0 right-0 h-full w-full sm:w-[400px] bg-white z-[100] shadow-2xl drawer translate-x-full flex flex-col">
    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
        <h3 class="text-xl font-bold flex items-center gap-3">
            <i data-lucide="heart" class="text-red-500"></i> Wishlist
        </h3>
        <button onclick="closeAllDrawers()" class="p-2 hover:bg-slate-100 rounded-full transition-colors">
            <i data-lucide="x" class="w-6 h-6 text-slate-400"></i>
        </button>
    </div>
    <div id="wishlistItems" class="flex-1 overflow-y-auto p-6 space-y-6 no-scrollbar">
        <!-- Wishlist items injected here -->
    </div>
</div>
