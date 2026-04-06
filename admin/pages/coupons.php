<?php
/**
 * Discount Coupons Page Content
 * Manages discount coupons
 */
?>
<!-- Page Header with Add Button -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Discount Coupons</h2>
        <p class="text-gray-500 mt-1">Manage discount codes and promotions</p>
    </div>
    <button onclick="if (typeof openAddCouponModal === 'function') { openAddCouponModal(); } else { alert('Function not available'); }" 
            class="flex items-center gap-2 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Add Coupon</span>
    </button>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="card">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                <i data-lucide="ticket" class="w-6 h-6 text-blue-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Coupons</p>
                <p class="text-2xl font-bold text-gray-900" id="total-coupons">0</p>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-6 h-6 text-green-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Active Coupons</p>
                <p class="text-2xl font-bold text-gray-900" id="active-coupons">0</p>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-teal-100 flex items-center justify-center">
                <i data-lucide="x-circle" class="w-6 h-6 text-teal-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Expired Coupons</p>
                <p class="text-2xl font-bold text-gray-900" id="expired-coupons">0</p>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                <i data-lucide="users" class="w-6 h-6 text-purple-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Usage</p>
                <p class="text-2xl font-bold text-gray-900" id="total-usage">0</p>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter Bar -->
<div class="card mb-6">
    <div class="flex flex-col md:flex-row gap-4 items-center">
        <div class="flex-1 w-full">
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                <input type="text" id="search-input" placeholder="Search coupons by code or description..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>
        </div>
        <select id="filter-status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
            <option value="all">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="expired">Expired</option>
        </select>
        <button onclick="resetFilters()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
</div>

<!-- Coupons Table -->
<div class="card">
    <div class="overflow-x-auto">
        <table class="w-full" id="coupons-table">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Coupon Code</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Discount</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Min. Purchase</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Usage</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Expiry Date</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                    <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody id="coupons-tbody" class="divide-y divide-gray-200">
                <!-- Coupons will be dynamically loaded here -->
            </tbody>
        </table>
        <div id="empty-state" class="hidden py-12 text-center">
            <i data-lucide="ticket-x" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
            <p class="text-gray-500 text-lg font-medium mb-2">No coupons found</p>
            <p class="text-gray-400 text-sm">Add your first coupon to get started</p>
        </div>
    </div>
</div>

<!-- Add/Edit Coupon Modal -->
<div class="modal-overlay" id="coupon-modal">
    <div class="modal" style="max-width: 700px;">
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center">
                    <i data-lucide="ticket" class="w-5 h-5 text-teal-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900" id="modal-title">Add Coupon</h2>
            </div>
            <button onclick="closeCouponModal()" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <form id="coupon-form" onsubmit="saveCoupon(event)">
            <input type="hidden" id="coupon-id">
            <div class="space-y-6">
                <!-- Coupon Code Section -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Coupon Code <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="coupon-code" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors"
                        placeholder="e.g., SAVE20, WELCOME10" style="text-transform: uppercase;">
                    <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                        <i data-lucide="info" class="w-3 h-3"></i>
                        Enter a unique coupon code (uppercase)
                    </p>
                </div>

                <!-- Discount Type & Value -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Discount Type <span class="text-red-500">*</span>
                        </label>
                        <select id="discount-type" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors bg-white"
                            onchange="updateDiscountType()">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (₹)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Discount Value <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="discount-value" min="0" step="0.01" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors"
                            placeholder="Enter amount or percentage">
                    </div>
                </div>

                <!-- Minimum Purchase & Usage Limit -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Minimum Purchase (₹)
                        </label>
                        <input type="number" id="min-purchase" min="0" step="0.01"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors"
                            placeholder="0 for no minimum">
                        <p class="text-xs text-gray-500 mt-1">Leave 0 for no minimum requirement</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Usage Limit
                        </label>
                        <input type="number" id="usage-limit" min="0"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors"
                            placeholder="0 for unlimited">
                        <p class="text-xs text-gray-500 mt-1">Leave 0 for unlimited usage</p>
                    </div>
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Start Date
                        </label>
                        <input type="date" id="start-date"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors">
                        <p class="text-xs text-gray-500 mt-1">Optional start date</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Expiry Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="expiry-date" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors">
                        <p class="text-xs text-gray-500 mt-1">Coupon expiration date</p>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea id="coupon-description" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors resize-none"
                        placeholder="Brief description of the coupon (optional)"></textarea>
                </div>

                <!-- Active Status -->
                <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <input type="checkbox" id="coupon-active" checked
                        class="w-5 h-5 text-teal-600 border-gray-300 rounded focus:ring-teal-500 cursor-pointer">
                    <label for="coupon-active" class="flex items-center gap-2 cursor-pointer">
                        <span class="text-sm font-semibold text-gray-700">Active Coupon</span>
                        <span class="text-xs text-gray-500">(Enable this coupon immediately)</span>
                    </label>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex gap-3 mt-8 pt-6 border-t border-gray-200">
                <button type="submit" class="flex-1 px-6 py-3 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-semibold shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Coupon</span>
                </button>
                <button type="button" onclick="closeCouponModal()" class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-colors font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Enhanced Modal Styles */
    #coupon-modal .modal {
        padding: 32px;
    }
    
    #coupon-modal input[type="text"],
    #coupon-modal input[type="number"],
    #coupon-modal input[type="date"],
    #coupon-modal select,
    #coupon-modal textarea {
        font-size: 14px;
    }
    
    #coupon-modal input:focus,
    #coupon-modal select:focus,
    #coupon-modal textarea:focus {
        box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
    }
    
    #coupon-modal label {
        user-select: none;
    }
    
    #coupon-modal input[type="checkbox"] {
        accent-color: #14b8a6;
    }
</style>

<script>
    // Coupons data loaded from API
    let coupons = [];
    
    // API base URL
    const API_BASE = '/api/v1/admin';

    // Load coupons on page load
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize icons first
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        loadCoupons();
    });

    // Load coupons from API
    async function loadCoupons() {
        try {
            const response = await fetch(`${API_BASE}/coupons.php`, {
                credentials: 'include'
            });
            const result = await response.json();
            
            if (result.success) {
                coupons = result.data || [];
                renderCoupons();
                updateSummary();
            } else {
                console.error('Failed to load coupons:', result.message);
            }
        } catch (error) {
            console.error('Error loading coupons:', error);
        }
    }

    // Render coupons table
    function renderCoupons(filteredCoupons = null) {
        const tbody = document.getElementById('coupons-tbody');
        const emptyState = document.getElementById('empty-state');
        const couponsToRender = filteredCoupons || coupons;

        if (!tbody) return;

        tbody.innerHTML = '';

        if (couponsToRender.length === 0) {
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }

        if (emptyState) emptyState.classList.add('hidden');

        couponsToRender.forEach(coupon => {
            const status = getCouponStatus(coupon);
            const discountText = coupon.type === 'percentage' 
                ? `${coupon.value}%` 
                : `₹${coupon.value}`;
            
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';
            row.innerHTML = `
                <td class="py-3 px-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">${coupon.code}</p>
                        <p class="text-xs text-gray-500">${coupon.description || 'No description'}</p>
                    </div>
                </td>
                <td class="py-3 px-4">
                    <span class="discount-badge">${discountText}</span>
                </td>
                <td class="py-3 px-4 text-sm text-gray-700">
                    ${coupon.minPurchase > 0 ? `₹${parseFloat(coupon.minPurchase || 0).toLocaleString('en-IN')}` : 'No minimum'}
                </td>
                <td class="py-3 px-4 text-sm text-gray-700">
                    ${coupon.usedCount || 0}${coupon.usageLimit > 0 ? ` / ${coupon.usageLimit}` : ' / ∞'}
                </td>
                <td class="py-3 px-4 text-sm text-gray-700">
                    ${new Date(coupon.expiryDate).toLocaleDateString('en-IN')}
                </td>
                <td class="py-3 px-4">
                    <span class="status-badge status-${status}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>
                </td>
                <td class="py-3 px-4">
                    <div class="flex items-center justify-end gap-2">
                        <button onclick="copyCouponCode('${coupon.code}')" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Copy Code">
                            <i data-lucide="copy" class="w-4 h-4"></i>
                        </button>
                        <button onclick="editCoupon(${coupon.id})" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                            <i data-lucide="edit" class="w-4 h-4"></i>
                        </button>
                        <button onclick="deleteCoupon(${coupon.id})" class="p-2 text-teal-600 hover:bg-teal-50 rounded-lg transition-colors" title="Delete">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });

        // Initialize icons after rendering
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    // Get coupon status
    function getCouponStatus(coupon) {
        const today = new Date();
        const expiryDate = new Date(coupon.expiryDate);
        const startDate = coupon.startDate ? new Date(coupon.startDate) : null;

        if (!coupon.active) return 'inactive';
        if (expiryDate < today) return 'expired';
        if (startDate && startDate > today) return 'inactive';
        if (coupon.usageLimit > 0 && (coupon.usedCount || 0) >= coupon.usageLimit) return 'expired';
        
        return 'active';
    }

    // Update summary cards
    function updateSummary() {
        const total = coupons.length;
        const active = coupons.filter(c => getCouponStatus(c) === 'active').length;
        const expired = coupons.filter(c => getCouponStatus(c) === 'expired').length;
        const totalUsage = coupons.reduce((sum, c) => sum + (c.usedCount || 0), 0);

        const totalCouponsEl = document.getElementById('total-coupons');
        const activeCouponsEl = document.getElementById('active-coupons');
        const expiredCouponsEl = document.getElementById('expired-coupons');
        const totalUsageEl = document.getElementById('total-usage');

        if (totalCouponsEl) totalCouponsEl.textContent = total;
        if (activeCouponsEl) activeCouponsEl.textContent = active;
        if (expiredCouponsEl) expiredCouponsEl.textContent = expired;
        if (totalUsageEl) totalUsageEl.textContent = totalUsage;
    }

    // Search and filter
    const searchInput = document.getElementById('search-input');
    const filterStatus = document.getElementById('filter-status');

    if (searchInput) searchInput.addEventListener('input', filterCoupons);
    if (filterStatus) filterStatus.addEventListener('change', filterCoupons);

    function filterCoupons() {
        const searchTerm = (document.getElementById('search-input')?.value || '').toLowerCase();
        const statusFilter = document.getElementById('filter-status')?.value || 'all';

        let filtered = coupons.filter(coupon => {
            const matchesSearch = coupon.code.toLowerCase().includes(searchTerm) ||
                                (coupon.description && coupon.description.toLowerCase().includes(searchTerm));
            const matchesStatus = statusFilter === 'all' || getCouponStatus(coupon) === statusFilter;

            return matchesSearch && matchesStatus;
        });

        renderCoupons(filtered);
    }

    // Reset filters
    function resetFilters() {
        const searchInput = document.getElementById('search-input');
        const filterStatus = document.getElementById('filter-status');
        
        if (searchInput) searchInput.value = '';
        if (filterStatus) filterStatus.value = 'all';
        renderCoupons();
    }

    // Update discount type UI
    function updateDiscountType() {
        const type = document.getElementById('discount-type')?.value;
        const valueInput = document.getElementById('discount-value');
        
        if (!valueInput) return;
        
        if (type === 'percentage') {
            valueInput.setAttribute('max', '100');
            valueInput.setAttribute('placeholder', '0-100');
        } else {
            valueInput.removeAttribute('max');
            valueInput.setAttribute('placeholder', 'Enter amount in ₹');
        }
    }

    // Add Coupon Modal
    function openAddCouponModal() {
        const modalTitle = document.getElementById('modal-title');
        const couponForm = document.getElementById('coupon-form');
        const couponId = document.getElementById('coupon-id');
        const couponActive = document.getElementById('coupon-active');
        const expiryDate = document.getElementById('expiry-date');

        if (modalTitle) modalTitle.textContent = 'Add Coupon';
        if (couponForm) couponForm.reset();
        if (couponId) couponId.value = '';
        if (couponActive) couponActive.checked = true;
        if (expiryDate) {
            const nextMonth = new Date();
            nextMonth.setMonth(nextMonth.getMonth() + 1);
            expiryDate.valueAsDate = nextMonth;
        }
        
        const modal = document.getElementById('coupon-modal');
        if (modal) {
            modal.classList.add('show');
            // Initialize icons after opening modal
            setTimeout(() => {
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }, 100);
            // Focus on coupon code input
            setTimeout(() => {
                const codeInput = document.getElementById('coupon-code');
                if (codeInput) codeInput.focus();
            }, 150);
        }
    }

    // Edit Coupon
    function editCoupon(id) {
        const coupon = coupons.find(c => c.id === id);
        if (!coupon) return;

        const modalTitle = document.getElementById('modal-title');
        const couponId = document.getElementById('coupon-id');
        const couponCode = document.getElementById('coupon-code');
        const discountType = document.getElementById('discount-type');
        const discountValue = document.getElementById('discount-value');
        const minPurchase = document.getElementById('min-purchase');
        const usageLimit = document.getElementById('usage-limit');
        const startDate = document.getElementById('start-date');
        const expiryDate = document.getElementById('expiry-date');
        const couponDescription = document.getElementById('coupon-description');
        const couponActive = document.getElementById('coupon-active');

        if (modalTitle) modalTitle.textContent = 'Edit Coupon';
        if (couponId) couponId.value = coupon.id;
        if (couponCode) couponCode.value = coupon.code;
        if (discountType) discountType.value = coupon.type;
        if (discountValue) discountValue.value = coupon.value;
        if (minPurchase) minPurchase.value = coupon.minPurchase || '';
        if (usageLimit) usageLimit.value = coupon.usageLimit || '';
        if (startDate) startDate.value = coupon.startDate || '';
        if (expiryDate) expiryDate.value = coupon.expiryDate;
        if (couponDescription) couponDescription.value = coupon.description || '';
        if (couponActive) couponActive.checked = coupon.active;
        
        updateDiscountType();
        
        const modal = document.getElementById('coupon-modal');
        if (modal) {
            modal.classList.add('show');
            // Initialize icons after opening modal
            setTimeout(() => {
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }, 100);
            // Focus on coupon code input
            setTimeout(() => {
                const codeInput = document.getElementById('coupon-code');
                if (codeInput) codeInput.focus();
            }, 150);
        }
    }

    // Close Coupon Modal
    function closeCouponModal() {
        const modal = document.getElementById('coupon-modal');
        if (modal) {
            modal.classList.remove('show');
            // Reset form if needed
            const form = document.getElementById('coupon-form');
            if (form) {
                form.reset();
            }
        }
    }
    
    // Close modal when clicking outside
    document.addEventListener('click', (e) => {
        const modal = document.getElementById('coupon-modal');
        if (modal && e.target === modal) {
            closeCouponModal();
        }
    });
    
    // Close modal on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modal = document.getElementById('coupon-modal');
            if (modal && modal.classList.contains('show')) {
                closeCouponModal();
            }
        }
    });

    // Save Coupon
    async function saveCoupon(event) {
        event.preventDefault();

        const couponId = document.getElementById('coupon-id');
        const couponCode = document.getElementById('coupon-code');

        if (!couponId || !couponCode) return;

        const id = couponId.value;
        const code = couponCode.value.toUpperCase().trim();
        
        if (!code) {
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Coupon code is required', 'warning');
            } else {
                alert('Coupon code is required');
            }
            return;
        }

        const discountType = document.getElementById('discount-type');
        const discountValue = document.getElementById('discount-value');
        const minPurchase = document.getElementById('min-purchase');
        const usageLimit = document.getElementById('usage-limit');
        const startDate = document.getElementById('start-date');
        const expiryDate = document.getElementById('expiry-date');
        const couponDescription = document.getElementById('coupon-description');
        const couponActive = document.getElementById('coupon-active');

        const coupon = {
            code: code,
            name: code, // Use code as name if not provided
            type: discountType?.value || 'percentage',
            value: parseFloat(discountValue?.value || 0),
            minPurchase: parseFloat(minPurchase?.value || 0),
            usageLimit: parseInt(usageLimit?.value || 0),
            startDate: startDate?.value || null,
            expiryDate: expiryDate?.value || '',
            description: couponDescription?.value || null,
            active: couponActive?.checked || false
        };

        // Validate discount value
        if (coupon.type === 'percentage' && coupon.value > 100) {
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Percentage discount cannot exceed 100%', 'warning');
            } else {
                alert('Percentage discount cannot exceed 100%');
            }
            return;
        }

        try {
            const url = `${API_BASE}/coupons.php`;
            const method = id ? 'PUT' : 'POST';
            
            if (id) {
                coupon.id = parseInt(id);
            }

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(coupon)
            });

            const result = await response.json();

            if (result.success) {
                // Reload coupons
                await loadCoupons();
                closeCouponModal();
                
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert(id ? 'Coupon updated successfully!' : 'Coupon created successfully!', 'success');
                } else {
                    alert(id ? 'Coupon updated successfully!' : 'Coupon created successfully!');
                }
            } else {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Error: ' + (result.message || 'Failed to save coupon'), 'error');
                } else {
                    alert('Error: ' + (result.message || 'Failed to save coupon'));
                }
            }
        } catch (error) {
            console.error('Error saving coupon:', error);
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('An error occurred while saving the coupon. Please try again.', 'error');
            } else {
                alert('An error occurred while saving the coupon. Please try again.');
            }
        }
    }

    // Delete Coupon
    async function deleteCoupon(id) {
        if (typeof Tivora !== 'undefined' && Tivora.confirm) {
            if (!await Tivora.confirm('Are you sure you want to delete this coupon?')) {
                return;
            }
        } else {
            if (!confirm('Are you sure you want to delete this coupon?')) {
                return;
            }
        }

        try {
            const response = await fetch(`${API_BASE}/coupons.php?id=${id}`, {
                method: 'DELETE',
                credentials: 'include'
            });

            const result = await response.json();

            if (result.success) {
                await loadCoupons();
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Coupon deleted successfully!', 'success');
                } else {
                    alert('Coupon deleted successfully!');
                }
            } else {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Error: ' + (result.message || 'Failed to delete coupon'), 'error');
                } else {
                    alert('Error: ' + (result.message || 'Failed to delete coupon'));
                }
            }
        } catch (error) {
            console.error('Error deleting coupon:', error);
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('An error occurred while deleting the coupon. Please try again.', 'error');
            } else {
                alert('An error occurred while deleting the coupon. Please try again.');
            }
        }
    }

    // Copy coupon code to clipboard
    async function copyCouponCode(code) {
        try {
            await navigator.clipboard.writeText(code);
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert(`Coupon code "${code}" copied to clipboard!`, 'success');
            } else {
                alert(`Coupon code "${code}" copied to clipboard!`);
            }
        } catch (err) {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = code;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert(`Coupon code "${code}" copied to clipboard!`, 'success');
            } else {
                alert(`Coupon code "${code}" copied to clipboard!`);
            }
        }
    }

    // Make openAddCouponModal globally accessible
    window.openAddCouponModal = openAddCouponModal;
    
    // Initialize icons on page load and after content updates
    function initializeIcons() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
    
    // Initialize icons immediately and after DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeIcons);
    } else {
        initializeIcons();
    }
    
    // Re-initialize icons after a short delay to catch dynamic content
    setTimeout(initializeIcons, 500);
    setTimeout(initializeIcons, 1000);
</script>
