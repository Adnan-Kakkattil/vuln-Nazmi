<?php
/**
 * Finance Page Content
 * Manages financial transactions
 */
?>
<!-- Financial Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="card">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                <i data-lucide="arrow-down-circle" class="w-6 h-6 text-green-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Credit</p>
                <p class="text-2xl font-bold text-gray-900" id="total-credit">₹0</p>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-teal-100 flex items-center justify-center">
                <i data-lucide="arrow-up-circle" class="w-6 h-6 text-teal-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Debit</p>
                <p class="text-2xl font-bold text-gray-900" id="total-debit">₹0</p>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                <i data-lucide="shopping-cart" class="w-6 h-6 text-blue-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Purchases</p>
                <p class="text-2xl font-bold text-gray-900" id="total-purchase">₹0</p>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                <i data-lucide="wallet" class="w-6 h-6 text-purple-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Balance</p>
                <p class="text-2xl font-bold text-gray-900" id="balance">₹0</p>
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
                <input type="text" id="search-input" placeholder="Search transactions by description, reference..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>
        </div>
        <select id="filter-type" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
            <option value="all">All Types</option>
            <option value="credit">Credit</option>
            <option value="debit">Debit</option>
            <option value="purchase">Purchase</option>
        </select>
        <input type="date" id="filter-date" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
        <button onclick="resetFilters()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
</div>

<!-- Transactions Table -->
<div class="card">
    <div class="overflow-x-auto">
        <table class="w-full" id="transactions-table">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Date</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Type</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Description</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Reference</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Amount</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Category</th>
                    <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody id="transactions-tbody" class="divide-y divide-gray-200">
                <!-- Transactions will be dynamically loaded here -->
            </tbody>
        </table>
        <div id="empty-state" class="hidden py-12 text-center">
            <i data-lucide="receipt-x" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
            <p class="text-gray-500 text-lg font-medium mb-2">No transactions found</p>
            <p class="text-gray-400 text-sm">Add your first transaction to get started</p>
        </div>
    </div>
</div>

<!-- Add/Edit Transaction Modal -->
<div class="modal-overlay" id="transaction-modal">
    <div class="modal">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900" id="modal-title">Add Transaction</h2>
            <button onclick="closeTransactionModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <form id="transaction-form" onsubmit="saveTransaction(event)">
            <input type="hidden" id="transaction-id">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Type *</label>
                    <select id="transaction-type" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                        onchange="updateTransactionType()">
                        <option value="">Select Type</option>
                        <option value="credit">Credit (Income)</option>
                        <option value="debit">Debit (Expense)</option>
                        <option value="purchase">Purchase</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                        <input type="date" id="transaction-date" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount (₹) *</label>
                        <input type="number" id="transaction-amount" min="0" step="0.01" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                    <input type="text" id="transaction-description" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                        placeholder="Brief description of the transaction">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select id="transaction-category"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">Select Category</option>
                            <option value="sales">Sales</option>
                            <option value="purchase">Purchase</option>
                            <option value="rent">Rent</option>
                            <option value="salary">Salary</option>
                            <option value="utilities">Utilities</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="marketing">Marketing</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                        <input type="text" id="transaction-reference"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                            placeholder="Invoice/Receipt/Check No.">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea id="transaction-notes" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                        placeholder="Additional notes (optional)"></textarea>
                </div>

                <!-- Add to Stock Section (shown only for Purchase type) -->
                <div id="stock-section" class="stock-section">
                    <div class="mb-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" id="add-to-stock"
                                class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500"
                                onchange="toggleStockFields()">
                            <span class="text-sm font-medium text-gray-700">Add this purchase to stock inventory</span>
                        </label>
                    </div>

                    <div id="stock-fields" style="display: none;">
                        <h3 class="stock-section-title">Stock Details</h3>
                        
                        <!-- Product Selection -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Existing Product (Optional)</label>
                            <select id="stock-product-select" onchange="handleProductChange()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                <option value="">-- New Product --</option>
                                <!-- Products will be loaded dynamically -->
                            </select>
                            <p class="text-[10px] text-gray-500 mt-1">Select a product to update its stock, or leave as "New Product" to add a new one.</p>
                        </div>

                        <!-- Basic Stock Info -->
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
                                    <input type="text" id="stock-product-name"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">SKU *</label>
                                    <input type="text" id="stock-sku"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                                    <select id="stock-category"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                        <option value="">Select Category</option>
                                        <!-- Categories will be loaded dynamically -->
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                                    <input type="number" id="stock-quantity" min="0"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Brand</label>
                                    <input type="text" id="stock-brand"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                                    <input type="text" id="stock-model"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Product Description</label>
                                <textarea id="stock-description" rows="2"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-medium">
                    Save Transaction
                </button>
                <button type="button" onclick="closeTransactionModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Stock Section Styles */
    .stock-section {
        border-top: 2px solid #e5e7eb;
        padding-top: 20px;
        margin-top: 20px;
        display: none;
    }

    .stock-section.show {
        display: block;
    }

    .stock-section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 16px;
    }
</style>

<script>
    // Transactions data loaded from API
    let transactions = [];
    let categories = [];
    let products = [];
    
    // API base URL
    const API_BASE = '/api/v1/admin';

    // Load transactions and categories on page load
    document.addEventListener('DOMContentLoaded', () => {
        // Set default date to today
        const dateInput = document.getElementById('transaction-date');
        if (dateInput) {
            dateInput.valueAsDate = new Date();
        }
        
        loadCategories();
        loadProducts();
        loadTransactions();
    });

    // Load categories from API
    async function loadCategories() {
        try {
            const response = await fetch(`${API_BASE}/categories.php`);
            const result = await response.json();
            
            if (result.success) {
                categories = result.data;
                
                // Populate stock category dropdown
                const stockCategorySelect = document.getElementById('stock-category');
                if (stockCategorySelect) {
                    stockCategorySelect.innerHTML = '<option value="">Select Category</option>';
                    
                    categories.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.id;
                        option.textContent = cat.name;
                        stockCategorySelect.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.error('Failed to load categories:', error);
        }
    }

    // Load products from API
    async function loadProducts() {
        try {
            const response = await fetch(`${API_BASE}/products.php`);
            const result = await response.json();
            
            if (result.success) {
                products = result.data || [];
                
                // Populate product select dropdown
                const productSelect = document.getElementById('stock-product-select');
                if (productSelect) {
                    const existingOptions = productSelect.querySelectorAll('option:not(:first-child)');
                    existingOptions.forEach(opt => opt.remove());
                    
                    products.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.id;
                        option.textContent = `${product.name} (${product.sku})`;
                        productSelect.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.error('Failed to load products:', error);
        }
    }

    // Handle product selection change
    function handleProductChange() {
        const selectedProductId = document.getElementById('stock-product-select').value;
        
        if (selectedProductId) {
            const product = products.find(p => p.id == selectedProductId);
            if (product) {
                document.getElementById('stock-product-name').value = product.name;
                document.getElementById('stock-sku').value = product.sku;
                document.getElementById('stock-category').value = product.category_id || '';
                
                // Make fields read-only
                document.getElementById('stock-product-name').readOnly = true;
                document.getElementById('stock-sku').readOnly = true;
                document.getElementById('stock-category').disabled = true;
                
                document.getElementById('stock-product-name').classList.add('bg-gray-50');
                document.getElementById('stock-sku').classList.add('bg-gray-50');
            }
        } else {
            // Reset fields
            document.getElementById('stock-product-name').value = '';
            document.getElementById('stock-sku').value = '';
            document.getElementById('stock-category').value = '';
            
            document.getElementById('stock-product-name').readOnly = false;
            document.getElementById('stock-sku').readOnly = false;
            document.getElementById('stock-category').disabled = false;
            
            document.getElementById('stock-product-name').classList.remove('bg-gray-50');
            document.getElementById('stock-sku').classList.remove('bg-gray-50');
        }
    }

    // Load transactions from API
    async function loadTransactions() {
        try {
            const response = await fetch(`${API_BASE}/transactions.php`);
            const result = await response.json();
            
            if (result.success) {
                transactions = result.data || [];
                renderTransactions();
                updateSummary();
            } else {
                console.error('Failed to load transactions:', result.message);
            }
        } catch (error) {
            console.error('Error loading transactions:', error);
        }
    }

    // Render transactions table
    function renderTransactions(filteredTransactions = null) {
        const tbody = document.getElementById('transactions-tbody');
        const emptyState = document.getElementById('empty-state');
        const transactionsToRender = filteredTransactions || transactions.sort((a, b) => new Date(b.date) - new Date(a.date));

        if (!tbody) return;

        tbody.innerHTML = '';

        if (transactionsToRender.length === 0) {
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }

        if (emptyState) emptyState.classList.add('hidden');

        transactionsToRender.forEach(transaction => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';
            
            const date = new Date(transaction.date);
            const formattedDate = date.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
            
            const amountClass = transaction.type === 'credit' ? 'text-green-600' : 'text-teal-600';
            const amountPrefix = transaction.type === 'credit' ? '+' : '-';
            
            const actions = transaction.is_system 
                ? `<a href="dashboard.php?page=orders&search=${transaction.reference}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors inline-block" title="View Order">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                   </a>`
                : `<div class="flex items-center justify-end gap-2 text-right">
                        <button onclick="editTransaction(${transaction.id})" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                            <i data-lucide="edit" class="w-4 h-4"></i>
                        </button>
                        <button onclick="deleteTransaction(${transaction.id})" class="p-2 text-teal-600 hover:bg-teal-50 rounded-lg transition-colors" title="Delete">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>`;

            const paymentBadge = transaction.is_system 
                ? `<div class="mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase ${transaction.payment_status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'}">
                        ${transaction.payment_status}
                    </span>
                   </div>`
                : '';

            row.innerHTML = `
                <td class="py-3 px-4 text-sm text-gray-700">${formattedDate}</td>
                <td class="py-3 px-4">
                    <span class="transaction-badge transaction-${transaction.type}">${transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1)}</span>
                </td>
                <td class="py-3 px-4 text-sm text-gray-900 font-medium">
                    ${transaction.description}
                    ${paymentBadge}
                </td>
                <td class="py-3 px-4 text-sm text-gray-600">
                    ${transaction.is_system 
                        ? `<span class="bg-gray-100 px-2 py-1 rounded text-xs font-mono">${transaction.reference}</span>`
                        : (transaction.reference || '-')}
                </td>
                <td class="py-3 px-4 text-sm font-semibold ${amountClass}">${amountPrefix}₹${parseFloat(transaction.amount || 0).toLocaleString('en-IN')}</td>
                <td class="py-3 px-4 text-sm text-gray-700 capitalize">${transaction.category || '-'}</td>
                <td class="py-3 px-4 text-right">
                    ${actions}
                </td>
            `;
            tbody.appendChild(row);
        });

        lucide.createIcons();
    }

    // Update summary cards
    function updateSummary() {
        // ONLY count credits that are COMPLETED (Money in hand)
        const totalCredit = transactions
            .filter(t => t.type === 'credit' && t.payment_status === 'completed')
            .reduce((sum, t) => sum + parseFloat(t.amount || 0), 0);
        
        const totalDebit = transactions
            .filter(t => t.type === 'debit')
            .reduce((sum, t) => sum + parseFloat(t.amount || 0), 0);
        
        const totalPurchase = transactions
            .filter(t => t.type === 'purchase')
            .reduce((sum, t) => sum + parseFloat(t.amount || 0), 0);
        
        const balance = totalCredit - totalDebit - totalPurchase;

        // Format numbers properly without leading zeros
        const formatCurrency = (amount) => {
            return parseFloat(amount).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        };

        const totalCreditEl = document.getElementById('total-credit');
        const totalDebitEl = document.getElementById('total-debit');
        const totalPurchaseEl = document.getElementById('total-purchase');
        const balanceEl = document.getElementById('balance');

        if (totalCreditEl) totalCreditEl.textContent = `₹${formatCurrency(totalCredit)}`;
        if (totalDebitEl) totalDebitEl.textContent = `₹${formatCurrency(totalDebit)}`;
        if (totalPurchaseEl) totalPurchaseEl.textContent = `₹${formatCurrency(totalPurchase)}`;
        
        if (balanceEl) {
            balanceEl.textContent = `₹${formatCurrency(Math.abs(balance))}`;
            balanceEl.className = balance >= 0 ? 'text-2xl font-bold text-green-600' : 'text-2xl font-bold text-teal-600';
        }
    }

    // Search and filter
    const searchInput = document.getElementById('search-input');
    const filterType = document.getElementById('filter-type');
    const filterDate = document.getElementById('filter-date');

    if (searchInput) searchInput.addEventListener('input', filterTransactions);
    if (filterType) filterType.addEventListener('change', filterTransactions);
    if (filterDate) filterDate.addEventListener('change', filterTransactions);

    function filterTransactions() {
        const searchTerm = (document.getElementById('search-input')?.value || '').toLowerCase();
        const typeFilter = document.getElementById('filter-type')?.value || 'all';
        const dateFilter = document.getElementById('filter-date')?.value || '';

        // Client-side filtering (can be enhanced with server-side filtering)
        let filtered = transactions.filter(transaction => {
            const matchesSearch = transaction.description.toLowerCase().includes(searchTerm) ||
                                (transaction.reference && transaction.reference.toLowerCase().includes(searchTerm)) ||
                                (transaction.category && transaction.category.toLowerCase().includes(searchTerm));
            const matchesType = typeFilter === 'all' || transaction.type === typeFilter;
            const matchesDate = !dateFilter || transaction.date === dateFilter;

            return matchesSearch && matchesType && matchesDate;
        });

        renderTransactions(filtered);
    }

    // Reset filters
    function resetFilters() {
        const searchInput = document.getElementById('search-input');
        const filterType = document.getElementById('filter-type');
        const filterDate = document.getElementById('filter-date');
        
        if (searchInput) searchInput.value = '';
        if (filterType) filterType.value = 'all';
        if (filterDate) filterDate.value = '';
        renderTransactions();
    }

    // Add Transaction Modal
    function openAddTransactionModal() {
        const modalTitle = document.getElementById('modal-title');
        const transactionForm = document.getElementById('transaction-form');
        const transactionId = document.getElementById('transaction-id');
        const transactionDate = document.getElementById('transaction-date');
        const stockSection = document.getElementById('stock-section');
        const addToStock = document.getElementById('add-to-stock');
        const stockProductSelect = document.getElementById('stock-product-select');

        if (modalTitle) modalTitle.textContent = 'Add Transaction';
        if (transactionForm) transactionForm.reset();
        if (transactionId) transactionId.value = '';
        if (transactionDate) transactionDate.valueAsDate = new Date();
        if (stockSection) stockSection.classList.remove('show');
        if (addToStock) addToStock.checked = false;
        toggleStockFields();
        if (stockProductSelect) stockProductSelect.value = '';
        handleProductChange();
        
        const modal = document.getElementById('transaction-modal');
        if (modal) {
            modal.classList.add('show');
            lucide.createIcons();
        }
    }

    // Update transaction type UI
    function updateTransactionType() {
        const type = document.getElementById('transaction-type')?.value;
        const stockSection = document.getElementById('stock-section');
        
        if (!stockSection) return;
        
        if (type === 'purchase') {
            stockSection.classList.add('show');
        } else {
            stockSection.classList.remove('show');
            const addToStock = document.getElementById('add-to-stock');
            if (addToStock) addToStock.checked = false;
            toggleStockFields();
        }
    }

    // Toggle stock fields visibility
    function toggleStockFields() {
        const addToStock = document.getElementById('add-to-stock');
        const stockFields = document.getElementById('stock-fields');
        
        if (!addToStock || !stockFields) return;
        
        if (addToStock.checked) {
            stockFields.style.display = 'block';
            // Make required fields actually required
            const productName = document.getElementById('stock-product-name');
            const sku = document.getElementById('stock-sku');
            const category = document.getElementById('stock-category');
            const quantity = document.getElementById('stock-quantity');
            
            if (productName) productName.required = true;
            if (sku) sku.required = true;
            if (category) category.required = true;
            if (quantity) quantity.required = true;
        } else {
            stockFields.style.display = 'none';
            // Remove required attribute
            const productName = document.getElementById('stock-product-name');
            const sku = document.getElementById('stock-sku');
            const category = document.getElementById('stock-category');
            const quantity = document.getElementById('stock-quantity');
            
            if (productName) productName.required = false;
            if (sku) sku.required = false;
            if (category) category.required = false;
            if (quantity) quantity.required = false;
        }
    }

    // Edit Transaction
    function editTransaction(id) {
        const transaction = transactions.find(t => t.id === id);
        if (!transaction) return;

        const modalTitle = document.getElementById('modal-title');
        const transactionId = document.getElementById('transaction-id');
        const transactionType = document.getElementById('transaction-type');
        const transactionDate = document.getElementById('transaction-date');
        const transactionAmount = document.getElementById('transaction-amount');
        const transactionDescription = document.getElementById('transaction-description');
        const transactionCategory = document.getElementById('transaction-category');
        const transactionReference = document.getElementById('transaction-reference');
        const transactionNotes = document.getElementById('transaction-notes');
        const addToStock = document.getElementById('add-to-stock');

        if (modalTitle) modalTitle.textContent = 'Edit Transaction';
        if (transactionId) transactionId.value = transaction.id;
        if (transactionType) transactionType.value = transaction.type;
        if (transactionDate) transactionDate.value = transaction.date;
        if (transactionAmount) transactionAmount.value = transaction.amount;
        if (transactionDescription) transactionDescription.value = transaction.description;
        if (transactionCategory) transactionCategory.value = transaction.category || '';
        if (transactionReference) transactionReference.value = transaction.reference || '';
        if (transactionNotes) transactionNotes.value = transaction.notes || '';
        
        // Update stock section visibility
        updateTransactionType();
        if (transaction.addToStock && addToStock) {
            addToStock.checked = true;
            toggleStockFields();
        }
        
        const modal = document.getElementById('transaction-modal');
        if (modal) {
            modal.classList.add('show');
            lucide.createIcons();
        }
    }

    // Close Transaction Modal
    function closeTransactionModal() {
        const modal = document.getElementById('transaction-modal');
        if (modal) modal.classList.remove('show');
    }

    // Save Transaction
    async function saveTransaction(event) {
        event.preventDefault();

        const transactionId = document.getElementById('transaction-id');
        const transactionType = document.getElementById('transaction-type');
        const addToStock = document.getElementById('add-to-stock');

        if (!transactionId || !transactionType) return;

        const id = transactionId.value;
        const transactionTypeValue = transactionType.value;
        const addToStockValue = addToStock?.checked || false;

        // Validate stock fields if add to stock is checked
        if (transactionTypeValue === 'purchase' && addToStockValue) {
            const productName = document.getElementById('stock-product-name')?.value;
            const sku = document.getElementById('stock-sku')?.value;
            const category = document.getElementById('stock-category')?.value;
            const quantity = document.getElementById('stock-quantity')?.value;

            if (!productName || !sku || !category || !quantity) {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Please fill in all required stock fields (Product Name, SKU, Category, Quantity)', 'warning');
                } else {
                    alert('Please fill in all required stock fields (Product Name, SKU, Category, Quantity)');
                }
                return;
            }
        }

        const transactionDate = document.getElementById('transaction-date');
        const transactionAmount = document.getElementById('transaction-amount');
        const transactionDescription = document.getElementById('transaction-description');
        const transactionCategory = document.getElementById('transaction-category');
        const transactionReference = document.getElementById('transaction-reference');
        const transactionNotes = document.getElementById('transaction-notes');

        const transaction = {
            type: transactionTypeValue,
            date: transactionDate?.value || '',
            amount: parseFloat(transactionAmount?.value || 0),
            description: transactionDescription?.value || '',
            category: transactionCategory?.value || null,
            reference: transactionReference?.value || null,
            notes: transactionNotes?.value || null
        };

        try {
            // If adding to stock, create/update product via products API
            if (transactionTypeValue === 'purchase' && addToStockValue) {
                const selectedProductId = document.getElementById('stock-product-select')?.value;
                const qty = parseInt(document.getElementById('stock-quantity')?.value || 0);
                const totalAmount = parseFloat(transactionAmount?.value || 0);
                
                const stockData = {
                    name: document.getElementById('stock-product-name')?.value || '',
                    sku: document.getElementById('stock-sku')?.value || '',
                    category_id: parseInt(document.getElementById('stock-category')?.value || 0),
                    stock_quantity: qty,
                    price: qty > 0 ? (totalAmount / qty) : 0,
                    short_description: document.getElementById('stock-description')?.value || null,
                    specifications: {}
                };
                
                const brand = document.getElementById('stock-brand')?.value.trim();
                const model = document.getElementById('stock-model')?.value.trim();
                if (brand) stockData.specifications['Brand'] = brand;
                if (model) stockData.specifications['Model'] = model;

                try {
                    if (selectedProductId) {
                        // Update existing product by ID
                        const product = products.find(p => p.id == selectedProductId);
                        if (product) {
                            const updateData = {
                                id: product.id,
                                stock_quantity: product.stock_quantity + stockData.stock_quantity
                            };
                            await fetch(`${API_BASE}/products.php`, {
                                method: 'PUT',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(updateData)
                            });
                        }
                    } else {
                        // First check if SKU exists even if "New Product" was selected
                        const checkResponse = await fetch(`${API_BASE}/products.php?search=${stockData.sku}`);
                        const checkResult = await checkResponse.json();
                        const existingProduct = checkResult.success && checkResult.data 
                            ? checkResult.data.find(p => p.sku === stockData.sku) 
                            : null;

                        if (existingProduct) {
                            // SKU exists, just update quantity
                            await fetch(`${API_BASE}/products.php`, {
                                method: 'PUT',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    id: existingProduct.id,
                                    stock_quantity: existingProduct.stock_quantity + stockData.stock_quantity
                                })
                            });
                        } else {
                            // Truly a new product
                            await fetch(`${API_BASE}/products.php`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(stockData)
                            });
                        }
                    }
                    
                    // Refresh products list after update
                    await loadProducts();
                } catch (stockError) {
                    console.error('Error handling stock:', stockError);
                }
            }

            // Save transaction via API
            const url = `${API_BASE}/transactions.php`;
            const method = id ? 'PUT' : 'POST';
            
            if (id) {
                transaction.id = parseInt(id);
            }

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(transaction)
            });

            const result = await response.json();

            if (result.success) {
                // Reload transactions
                await loadTransactions();
                closeTransactionModal();
                
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert(id ? 'Transaction updated successfully!' : 'Transaction created successfully!', 'success');
                } else {
                    alert(id ? 'Transaction updated successfully!' : 'Transaction created successfully!');
                }
            } else {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Error: ' + (result.message || 'Failed to save transaction'), 'error');
                } else {
                    alert('Error: ' + (result.message || 'Failed to save transaction'));
                }
            }
        } catch (error) {
            console.error('Error saving transaction:', error);
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('An error occurred while saving the transaction. Please try again.', 'error');
            } else {
                alert('An error occurred while saving the transaction. Please try again.');
            }
        }
    }

    // Delete Transaction
    async function deleteTransaction(id) {
        if (typeof Tivora !== 'undefined' && Tivora.confirm) {
            if (!await Tivora.confirm('Are you sure you want to delete this transaction?')) {
                return;
            }
        } else {
            if (!confirm('Are you sure you want to delete this transaction?')) {
                return;
            }
        }

        try {
            const response = await fetch(`${API_BASE}/transactions.php?id=${id}`, {
                method: 'DELETE'
            });

            const result = await response.json();

            if (result.success) {
                await loadTransactions();
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Transaction deleted successfully!', 'success');
                } else {
                    alert('Transaction deleted successfully!');
                }
            } else {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Error: ' + (result.message || 'Failed to delete transaction'), 'error');
                } else {
                    alert('Error: ' + (result.message || 'Failed to delete transaction'));
                }
            }
        } catch (error) {
            console.error('Error deleting transaction:', error);
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('An error occurred while deleting the transaction. Please try again.', 'error');
            } else {
                alert('An error occurred while deleting the transaction. Please try again.');
            }
        }
    }

    // Make openAddTransactionModal globally accessible
    window.openAddTransactionModal = openAddTransactionModal;
</script>
