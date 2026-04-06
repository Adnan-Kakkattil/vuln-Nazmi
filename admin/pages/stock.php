<?php
/**
 * Stock Management Page Content
 * Manages product inventory and categories
 */
?>
<!-- Add Product Button - Always Visible -->
<div class="mb-6 flex justify-between items-center">
    <div></div>
    <button onclick="openAddProductModal()" 
            id="add-product-btn"
            class="flex items-center gap-2 px-6 py-3 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors shadow-md font-medium">
        <i data-lucide="plus" class="w-5 h-5"></i>
        <span>Add Product</span>
    </button>
</div>

<!-- Search and Filter Bar -->
<div class="stat-card mb-6">
    <div class="flex flex-col md:flex-row gap-4 items-center">
        <div class="flex-1 w-full">
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                <input type="text" id="search-input" placeholder="Search products by name, SKU, or category..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>
        </div>
        <select id="filter-status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
            <option value="all">All Stock Status</option>
            <option value="high">High Stock</option>
            <option value="medium">Medium Stock</option>
            <option value="low">Low Stock</option>
            <option value="out">Out of Stock</option>
        </select>
        <select id="filter-category" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
            <option value="all">All Categories</option>
            <!-- Categories will be loaded dynamically -->
        </select>
    </div>
</div>

<!-- Stock Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="stat-card">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                <i data-lucide="package-check" class="w-6 h-6 text-green-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Products</p>
                <p class="text-2xl font-bold text-gray-900" id="total-products">0</p>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                <i data-lucide="package" class="w-6 h-6 text-blue-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">In Stock</p>
                <p class="text-2xl font-bold text-gray-900" id="in-stock-count">0</p>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-6 h-6 text-yellow-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Low Stock</p>
                <p class="text-2xl font-bold text-gray-900" id="low-stock-count">0</p>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                <i data-lucide="package-x" class="w-6 h-6 text-red-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Out of Stock</p>
                <p class="text-2xl font-bold text-gray-900" id="out-stock-count">0</p>
            </div>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="stat-card">
    <div class="overflow-x-auto">
        <table class="w-full" id="products-table">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Product</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">SKU</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Category</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Stock</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Price</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                    <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody id="products-tbody" class="divide-y divide-gray-200">
                <!-- Products will be dynamically loaded here -->
            </tbody>
        </table>
        <div id="empty-state" class="hidden py-12 text-center">
            <i data-lucide="package-x" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
            <p class="text-gray-500 text-lg font-medium mb-2">No products found</p>
            <p class="text-gray-400 text-sm">Add your first product to get started</p>
        </div>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div class="modal-overlay" id="product-modal">
    <div class="modal">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900" id="modal-title">Add Product</h2>
            <button onclick="closeProductModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <form id="product-form" onsubmit="saveProduct(event)">
            <input type="hidden" id="product-id">
            <div class="space-y-4">
                <!-- Basic Information Section -->
                <div>
                    <h3 class="spec-section-title">Basic Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
                            <input type="text" id="product-name" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">SKU *</label>
                                <input type="text" id="product-sku" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                                <div class="flex gap-2">
                                    <select id="product-category" required
                                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                        <option value="">Select Category</option>
                                        <!-- Categories will be loaded dynamically -->
                                    </select>
                                    <button type="button" onclick="openCreateCategoryModal()"
                                        class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors border border-gray-300"
                                        title="Create New Category">
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Brand</label>
                                <input type="text" id="product-brand"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                    placeholder="e.g., Zara, H&M, Forever 21">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Original Price (₹)</label>
                                <input type="number" id="product-original-price" min="0" step="0.01"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                    placeholder="Original price for discount display">
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Price (₹) *</label>
                                <input type="number" id="product-price" min="0" step="0.01" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cost Price (₹)</label>
                                <input type="number" id="product-cost-price" min="0" step="0.01"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                    placeholder="Cost price for profit">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                                <input type="number" id="product-quantity" min="0" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select id="product-status"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="out_of_stock">Out of Stock</option>
                                    <option value="discontinued">Discontinued</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Low Stock Threshold</label>
                                <input type="number" id="product-low-stock-threshold" min="0" value="10"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" id="product-featured" class="w-4 h-4 text-teal-600 rounded focus:ring-teal-500">
                                    <span class="text-sm font-medium text-gray-700">Featured Product</span>
                                </label>
                            </div>
                            <div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" id="product-new" class="w-4 h-4 text-teal-600 rounded focus:ring-teal-500">
                                    <span class="text-sm font-medium text-gray-700">New Arrival</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea id="product-description" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Product Images</label>
                            <div class="mt-2">
                                <input type="file" id="product-images" multiple accept="image/*"
                                    class="hidden" onchange="handleImageUpload(event)">
                                <label for="product-images" class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg hover:border-teal-500 hover:bg-teal-50 transition-colors">
                                    <i data-lucide="upload" class="w-5 h-5 text-gray-500"></i>
                                    <span class="text-sm font-medium text-gray-700">Click to upload images</span>
                                    <span class="text-xs text-gray-500">(Multiple images supported)</span>
                                </label>
                            </div>
                            <div id="image-preview-container" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                                <!-- Image previews will be added here -->
                            </div>
                            <style>
                                .image-preview-wrapper {
                                    position: relative;
                                    aspect-ratio: 1;
                                    border-radius: 8px;
                                    overflow: hidden;
                                    border: 1px solid #e5e7eb;
                                }
                                .image-preview-wrapper img {
                                    width: 100%;
                                    height: 100%;
                                    object-fit: cover;
                                }
                                .image-preview-remove {
                                    position: absolute;
                                    top: 4px;
                                    right: 4px;
                                    background: rgba(239, 68, 68, 0.9);
                                    color: white;
                                    border: none;
                                    border-radius: 4px;
                                    width: 24px;
                                    height: 24px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    cursor: pointer;
                                    transition: background 0.2s;
                                }
                                .image-preview-remove:hover {
                                    background: rgba(220, 38, 38, 1);
                                }
                            </style>
                            <p class="text-xs text-gray-500 mt-2">Supported formats: JPG, PNG, GIF, WEBP. Max 5MB per image.</p>
                        </div>
                    </div>
                </div>

                <!-- Product Details Section -->
                <div class="spec-section">
                    <h3 class="spec-section-title">Product Details</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Material/Fabric</label>
                                <input type="text" id="spec-material"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                    placeholder="e.g., Cotton, Silk, Polyester, Linen">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pattern</label>
                                <select id="spec-pattern"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    <option value="">Select Pattern</option>
                                    <option value="solid">Solid</option>
                                    <option value="printed">Printed</option>
                                    <option value="striped">Striped</option>
                                    <option value="polka-dot">Polka Dot</option>
                                    <option value="floral">Floral</option>
                                    <option value="geometric">Geometric</option>
                                    <option value="abstract">Abstract</option>
                                    <option value="embroidery">Embroidery</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fit Type</label>
                                <select id="spec-fit"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    <option value="">Select Fit</option>
                                    <option value="regular">Regular Fit</option>
                                    <option value="slim">Slim Fit</option>
                                    <option value="loose">Loose Fit</option>
                                    <option value="oversized">Oversized</option>
                                    <option value="fitted">Fitted</option>
                                    <option value="relaxed">Relaxed</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Style</label>
                                <select id="spec-style"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    <option value="">Select Style</option>
                                    <option value="casual">Casual</option>
                                    <option value="formal">Formal</option>
                                    <option value="party">Party</option>
                                    <option value="ethnic">Ethnic</option>
                                    <option value="western">Western</option>
                                    <option value="sporty">Sporty</option>
                                    <option value="vintage">Vintage</option>
                                    <option value="modern">Modern</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Season</label>
                                <select id="spec-season"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    <option value="">Select Season</option>
                                    <option value="spring">Spring</option>
                                    <option value="summer">Summer</option>
                                    <option value="fall">Fall/Autumn</option>
                                    <option value="winter">Winter</option>
                                    <option value="all-season">All Season</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Occasion</label>
                                <select id="spec-occasion"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    <option value="">Select Occasion</option>
                                    <option value="everyday">Everyday</option>
                                    <option value="party">Party</option>
                                    <option value="wedding">Wedding</option>
                                    <option value="office">Office</option>
                                    <option value="casual-outing">Casual Outing</option>
                                    <option value="festival">Festival</option>
                                    <option value="sports">Sports</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Care Instructions</label>
                            <textarea id="spec-care-instructions" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                placeholder="e.g., Machine wash cold, Do not bleach, Hang dry, Iron on low heat"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Size & Color Variants Section -->
                <div class="spec-section">
                    <h3 class="spec-section-title">Size & Color Information</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Available Sizes</label>
                                <input type="text" id="spec-sizes"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                    placeholder="e.g., S, M, L, XL, XXL (comma separated)">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Available Colors</label>
                                <input type="text" id="spec-colors"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                    placeholder="e.g., Red, Blue, Black, White (comma separated)">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Weight (kg)</label>
                                <input type="number" id="product-weight" min="0" step="0.01"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                    placeholder="e.g., 0.5">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Dimensions (cm)</label>
                                <input type="text" id="product-dimensions"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                                    placeholder="e.g., 50x40x10 (Length x Width x Height)">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-medium">
                    Save Product
                </button>
                <button type="button" onclick="closeProductModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Create Category Modal -->
<div class="modal-overlay" id="category-modal">
    <div class="modal" style="max-width: 500px;">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900">Create New Category</h2>
            <button onclick="closeCategoryModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <form id="category-form" onsubmit="saveCategory(event)">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category Name *</label>
                    <input type="text" id="category-name" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                        placeholder="e.g., Smart TVs">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="category-description" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                        placeholder="Optional description"></textarea>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-medium">
                    Create Category
                </button>
                <button type="button" onclick="closeCategoryModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Update Stock Modal -->
<div class="modal-overlay" id="stock-modal">
    <div class="modal" style="max-width: 400px;">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900">Update Stock</h2>
            <button onclick="closeStockModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <form id="stock-form" onsubmit="updateStock(event)">
            <input type="hidden" id="stock-product-id">
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Product: <span class="font-medium text-gray-900" id="stock-product-name"></span></p>
                <p class="text-sm text-gray-600">Current Stock: <span class="font-medium text-gray-900" id="stock-current-quantity"></span></p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Action *</label>
                <select id="stock-action" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="add">Add Stock</option>
                    <option value="remove">Remove Stock</option>
                    <option value="set">Set Stock</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                <input type="number" id="stock-quantity" min="0" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-medium">
                    Update Stock
                </button>
                <button type="button" onclick="closeStockModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-overlay.show {
        display: flex;
    }

    .modal {
        background: white;
        border-radius: 12px;
        padding: 32px;
        max-width: 800px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        transform: scale(0.95);
        opacity: 0;
        transition: all 0.3s ease;
    }

    .modal-overlay.show .modal {
        transform: scale(1);
        opacity: 1;
    }

    .spec-section {
        border-top: 2px solid #e5e7eb;
        padding-top: 20px;
        margin-top: 20px;
    }

    .spec-section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 16px;
    }

    /* Stock Badge Styles */
    .stock-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .stock-high {
        background: #dcfce7;
        color: #166534;
    }

    .stock-medium {
        background: #fef3c7;
        color: #92400e;
    }

    .stock-low {
        background: #fee2e2;
        color: #991b1b;
    }

    .stock-out {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Image Preview Styles */
    .image-preview-wrapper {
        position: relative;
        aspect-ratio: 1;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .image-preview-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .image-preview-remove {
        position: absolute;
        top: 4px;
        right: 4px;
        background: rgba(239, 68, 68, 0.9);
        color: white;
        border: none;
        border-radius: 4px;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s;
    }

    .image-preview-remove:hover {
        background: rgba(220, 38, 38, 1);
    }
</style>

<script>
    // Products data loaded from API
    let products = [];
    let categories = [];
    
    // Store uploaded images temporarily
    let uploadedImages = [];

    // API base URL
    const API_BASE = '/api/v1/admin';

    // Load products and categories on page load
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize icons immediately
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        
        // Ensure add product button icon is rendered
        setTimeout(() => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }, 100);
        
        loadCategories();
        loadProducts();
        
        // Reinitialize icons periodically for dynamic content
        setInterval(() => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }, 1000);
    });

    // Load categories from API
    async function loadCategories() {
        try {
            const response = await fetch(`${API_BASE}/categories.php`, {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();
            
            if (result.success) {
                categories = result.data;
                
                // Populate product category dropdown
                const categorySelect = document.getElementById('product-category');
                categorySelect.innerHTML = '<option value="">Select Category</option>';
                
                // Populate filter category dropdown
                const filterCategorySelect = document.getElementById('filter-category');
                const filterOptions = filterCategorySelect.querySelectorAll('option:not(:first-child)');
                filterOptions.forEach(opt => opt.remove());
                
                categories.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.name;
                    categorySelect.appendChild(option);
                    
                    // Also add to filter dropdown (using id for filtering)
                    const filterOption = document.createElement('option');
                    filterOption.value = cat.id;
                    filterOption.textContent = cat.name;
                    filterCategorySelect.appendChild(filterOption);
                });
            }
        } catch (error) {
            console.error('Failed to load categories:', error);
        }
    }

    // Load products from API
    async function loadProducts() {
        try {
            const response = await fetch(`${API_BASE}/products.php`, {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();
            
            if (result.success) {
                products = result.data.map(p => ({
                    ...p,
                    quantity: p.stock_quantity || 0,
                    status: calculateStockStatus(p.stock_quantity || 0)
                }));
                renderProducts();
                updateSummary();
            } else {
                console.error('Failed to load products:', result.message);
            }
        } catch (error) {
            console.error('Error loading products:', error);
        }
    }

    // Render products table
    function renderProducts(filteredProducts = null) {
        const tbody = document.getElementById('products-tbody');
        const emptyState = document.getElementById('empty-state');
        const productsToRender = filteredProducts || products;

        tbody.innerHTML = '';

        if (productsToRender.length === 0) {
            emptyState.classList.remove('hidden');
            return;
        }

        emptyState.classList.add('hidden');

        productsToRender.forEach(product => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';
            
            // Get product image
            const productImage = product.images && product.images.length > 0 
                ? product.images[0].image_url 
                : null;
            const imageDisplay = productImage 
                ? `<img src="${productImage.startsWith('http') || productImage.startsWith('/') ? productImage : '/' + productImage}" alt="${product.name}" class="w-full h-full object-cover">`
                : `<i data-lucide="package" class="w-5 h-5 text-gray-400"></i>`;
            
            row.innerHTML = `
                <td class="py-3 px-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden">
                            ${imageDisplay}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">${escapeHtml(product.name)}</p>
                            <p class="text-xs text-gray-500">${escapeHtml((product.short_description || product.full_description || 'No description').substring(0, 50))}${(product.short_description || product.full_description || '').length > 50 ? '...' : ''}</p>
                        </div>
                    </div>
                </td>
                <td class="py-3 px-4 text-sm text-gray-700">${escapeHtml(product.sku)}</td>
                <td class="py-3 px-4 text-sm text-gray-700">${escapeHtml(product.category_name || 'N/A')}</td>
                <td class="py-3 px-4 text-sm font-medium text-gray-900">${product.stock_quantity || 0}</td>
                <td class="py-3 px-4 text-sm font-medium text-gray-900">₹${parseFloat(product.price || 0).toLocaleString('en-IN')}</td>
                <td class="py-3 px-4">
                    <span class="stock-badge stock-${product.status}">${getStockStatusText(product.status)}</span>
                </td>
                <td class="py-3 px-4">
                    <div class="flex items-center justify-end gap-2">
                        <button onclick="openUpdateStockModal(${product.id})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Update Stock">
                            <i data-lucide="package-plus" class="w-4 h-4"></i>
                        </button>
                        <button onclick="editProduct(${product.id})" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                            <i data-lucide="edit" class="w-4 h-4"></i>
                        </button>
                        <button onclick="deleteProduct(${product.id})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });

        lucide.createIcons();
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Get stock status text
    function getStockStatusText(status) {
        const statusMap = { high: 'In Stock', medium: 'Medium', low: 'Low Stock', out: 'Out of Stock' };
        return statusMap[status] || status;
    }

    // Calculate stock status
    function calculateStockStatus(quantity) {
        if (quantity === 0) return 'out';
        if (quantity < 10) return 'low';
        if (quantity < 30) return 'medium';
        return 'high';
    }

    // Update summary cards
    function updateSummary() {
        const total = products.length;
        const inStock = products.filter(p => (p.stock_quantity || 0) > 0).length;
        const lowStock = products.filter(p => {
            const qty = p.stock_quantity || 0;
            return qty > 0 && qty <= (p.low_stock_threshold || 10);
        }).length;
        const outStock = products.filter(p => (p.stock_quantity || 0) === 0).length;

        document.getElementById('total-products').textContent = total;
        document.getElementById('in-stock-count').textContent = inStock;
        document.getElementById('low-stock-count').textContent = lowStock;
        document.getElementById('out-stock-count').textContent = outStock;
    }

    // Search and filter
    document.getElementById('search-input').addEventListener('input', filterProducts);
    document.getElementById('filter-status').addEventListener('change', filterProducts);
    document.getElementById('filter-category').addEventListener('change', filterProducts);

    function filterProducts() {
        const searchTerm = document.getElementById('search-input').value.toLowerCase();
        const statusFilter = document.getElementById('filter-status').value;
        const categoryFilter = document.getElementById('filter-category').value;

        let filtered = products.filter(product => {
            const matchesSearch = product.name.toLowerCase().includes(searchTerm) ||
                                product.sku.toLowerCase().includes(searchTerm) ||
                                (product.category_name && product.category_name.toLowerCase().includes(searchTerm));
            const matchesStatus = statusFilter === 'all' || product.status === statusFilter;
            const matchesCategory = categoryFilter === 'all' || 
                (product.category_id && product.category_id == categoryFilter);

            return matchesSearch && matchesStatus && matchesCategory;
        });

        renderProducts(filtered);
    }

    // Handle Image Upload
    function handleImageUpload(event) {
        const files = event.target.files;
        const maxSize = 5 * 1024 * 1024; // 5MB
        const maxImages = 10;

        if (uploadedImages.length + files.length > maxImages) {
            alert(`Maximum ${maxImages} images allowed. You can upload ${maxImages - uploadedImages.length} more.`);
            return;
        }

        Array.from(files).forEach(file => {
            if (file.size > maxSize) {
                alert(`File ${file.name} is too large. Maximum size is 5MB.`);
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                uploadedImages.push({
                    name: file.name,
                    data: e.target.result,
                    id: Date.now() + Math.random()
                });
                renderImagePreviews();
            };
            reader.readAsDataURL(file);
        });
    }

    // Render Image Previews
    function renderImagePreviews() {
        const container = document.getElementById('image-preview-container');
        container.innerHTML = '';

        uploadedImages.forEach((image, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'image-preview-wrapper';
            // Handle both base64 data and URLs
            const imageSrc = image.data.startsWith('data:') || image.data.startsWith('/') 
                ? image.data 
                : (image.data.startsWith('http') ? image.data : '/' + image.data);
            wrapper.innerHTML = `
                <img src="${imageSrc}" alt="${image.name || 'Product image'}">
                <button type="button" onclick="removeImage(${index})" class="image-preview-remove" title="Remove image">
                    <i data-lucide="x"></i>
                </button>
            `;
            container.appendChild(wrapper);
        });

        lucide.createIcons();
    }

    // Remove Image
    function removeImage(index) {
        uploadedImages.splice(index, 1);
        renderImagePreviews();
    }

    // Add Product Modal
    function openAddProductModal() {
        document.getElementById('modal-title').textContent = 'Add Product';
        document.getElementById('product-form').reset();
        document.getElementById('product-id').value = '';
        uploadedImages = [];
        renderImagePreviews();
        
        // Reset all boutique specification fields
        document.getElementById('spec-material').value = '';
        document.getElementById('spec-pattern').value = '';
        document.getElementById('spec-fit').value = '';
        document.getElementById('spec-style').value = '';
        document.getElementById('spec-season').value = '';
        document.getElementById('spec-occasion').value = '';
        document.getElementById('spec-care-instructions').value = '';
        document.getElementById('spec-sizes').value = '';
        document.getElementById('spec-colors').value = '';
        document.getElementById('product-weight').value = '';
        document.getElementById('product-dimensions').value = '';
        document.getElementById('product-original-price').value = '';
        document.getElementById('product-cost-price').value = '';
        document.getElementById('product-status').value = 'active';
        document.getElementById('product-low-stock-threshold').value = '10';
        document.getElementById('product-featured').checked = false;
        document.getElementById('product-new').checked = false;
        
        document.getElementById('product-modal').classList.add('show');
        lucide.createIcons();
    }

    // Edit Product
    function editProduct(id) {
        const product = products.find(p => p.id === id);
        if (!product) return;

        document.getElementById('modal-title').textContent = 'Edit Product';
        document.getElementById('product-id').value = product.id;
        document.getElementById('product-name').value = product.name;
        document.getElementById('product-sku').value = product.sku;
        document.getElementById('product-category').value = product.category_id || '';
        document.getElementById('product-brand').value = (product.specifications && product.specifications['Brand']) || '';
        document.getElementById('product-quantity').value = product.stock_quantity || 0;
        document.getElementById('product-price').value = product.price;
        document.getElementById('product-description').value = product.short_description || product.full_description || '';
        
        // Load images - preserve existing URLs
        uploadedImages = (product.images || []).map((img, idx) => ({
            name: img.alt_text || 'image',
            data: img.image_url || '',
            id: Date.now() + idx + Math.random(),
            isExisting: true // Mark as existing image
        }));
        renderImagePreviews();
        
        // Load product fields
        document.getElementById('product-original-price').value = product.original_price || '';
        document.getElementById('product-cost-price').value = product.cost_price || '';
        document.getElementById('product-status').value = product.status || 'active';
        document.getElementById('product-low-stock-threshold').value = product.low_stock_threshold || 10;
        document.getElementById('product-weight').value = product.weight_kg || '';
        document.getElementById('product-dimensions').value = product.dimensions_cm || '';
        document.getElementById('product-featured').checked = product.is_featured == 1;
        document.getElementById('product-new').checked = product.is_new == 1;
        
        // Load specifications if they exist
        const specs = product.specifications || {};
        document.getElementById('spec-material').value = specs['Material'] || '';
        document.getElementById('spec-pattern').value = specs['Pattern'] || '';
        document.getElementById('spec-fit').value = specs['Fit Type'] || '';
        document.getElementById('spec-style').value = specs['Style'] || '';
        document.getElementById('spec-season').value = specs['Season'] || '';
        document.getElementById('spec-occasion').value = specs['Occasion'] || '';
        document.getElementById('spec-care-instructions').value = specs['Care Instructions'] || '';
        document.getElementById('spec-sizes').value = specs['Available Sizes'] || '';
        document.getElementById('spec-colors').value = specs['Available Colors'] || '';
        
        document.getElementById('product-modal').classList.add('show');
        lucide.createIcons();
    }

    // Close Product Modal
    function closeProductModal() {
        document.getElementById('product-modal').classList.remove('show');
    }

    // Create Category Modal
    function openCreateCategoryModal() {
        document.getElementById('category-name').value = '';
        document.getElementById('category-description').value = '';
        document.getElementById('category-modal').classList.add('show');
        lucide.createIcons();
    }

    // Close Category Modal
    function closeCategoryModal() {
        document.getElementById('category-modal').classList.remove('show');
    }

    // Save Category
    async function saveCategory(event) {
        event.preventDefault();

        const categoryData = {
            name: document.getElementById('category-name').value.trim(),
            description: document.getElementById('category-description').value.trim() || null
        };

        try {
            const response = await fetch(`${API_BASE}/categories.php`, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(categoryData)
            });

            const result = await response.json();

            if (result.success) {
                // Reload categories
                await loadCategories();
                
                // Select the newly created category
                document.getElementById('product-category').value = result.data.id;
                
                closeCategoryModal();
                
                // Show success message
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Category created successfully!', 'success');
                } else {
                    alert('Category created successfully!');
                }
            } else {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Error: ' + (result.message || 'Failed to create category'), 'error');
                } else {
                    alert('Error: ' + (result.message || 'Failed to create category'));
                }
            }
        } catch (error) {
            console.error('Error creating category:', error);
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('An error occurred while creating the category. Please try again.', 'error');
            } else {
                alert('An error occurred while creating the category. Please try again.');
            }
        }
    }

    // Save Product
    async function saveProduct(event) {
        event.preventDefault();

        const id = document.getElementById('product-id').value;
        
        // Validate required fields
        const name = document.getElementById('product-name').value.trim();
        const sku = document.getElementById('product-sku').value.trim();
        const categoryId = document.getElementById('product-category').value;
        const quantity = document.getElementById('product-quantity').value;
        const price = document.getElementById('product-price').value;
        
        if (!name || !sku || !categoryId || quantity === '' || price === '') {
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Please fill in all required fields (Name, SKU, Category, Quantity, Price)', 'warning');
            } else {
                alert('Please fill in all required fields (Name, SKU, Category, Quantity, Price)');
            }
            return;
        }
        
        // Validate SKU format (alphanumeric, dashes, underscores)
        if (!/^[A-Za-z0-9_-]+$/.test(sku)) {
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('SKU must contain only letters, numbers, dashes, and underscores', 'warning');
            } else {
                alert('SKU must contain only letters, numbers, dashes, and underscores');
            }
            return;
        }
        
        // Validate price and quantity are positive numbers
        if (parseFloat(price) < 0 || parseInt(quantity) < 0) {
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Price and quantity must be positive numbers', 'warning');
            } else {
                alert('Price and quantity must be positive numbers');
            }
            return;
        }
        
        // Build specifications object for boutique products
        const specifications = {};
        const brand = document.getElementById('product-brand').value.trim();
        
        if (brand) specifications['Brand'] = brand;
        
        // Add all boutique specification fields
        const specFields = {
            'Material': document.getElementById('spec-material').value.trim(),
            'Pattern': document.getElementById('spec-pattern').value.trim(),
            'Fit Type': document.getElementById('spec-fit').value.trim(),
            'Style': document.getElementById('spec-style').value.trim(),
            'Season': document.getElementById('spec-season').value.trim(),
            'Occasion': document.getElementById('spec-occasion').value.trim(),
            'Care Instructions': document.getElementById('spec-care-instructions').value.trim(),
            'Available Sizes': document.getElementById('spec-sizes').value.trim(),
            'Available Colors': document.getElementById('spec-colors').value.trim()
        };
        
        Object.keys(specFields).forEach(key => {
            if (specFields[key]) {
                specifications[key] = specFields[key];
            }
        });
        
        // Filter images - send base64 (new) images and existing file paths
        const imagesToSend = uploadedImages.map(img => {
            // If it's a base64 string (new image), send it as-is
            if (img.data.startsWith('data:')) {
                return img.data;
            }
            // If it's an existing file path, ensure it starts with /uploads/
            if (img.data.startsWith('/uploads/') || img.data.startsWith('uploads/')) {
                return img.data.startsWith('/') ? img.data : '/' + img.data;
            }
            // If it's a full URL or relative path, convert to proper format
            if (img.data.includes('uploads/products/')) {
                const pathMatch = img.data.match(/uploads\/products\/[^\/]+/);
                if (pathMatch) {
                    return '/' + pathMatch[0];
                }
            }
            // Default: return as-is
            return img.data;
        });
        
        const productData = {
            name: document.getElementById('product-name').value,
            sku: document.getElementById('product-sku').value,
            category_id: parseInt(document.getElementById('product-category').value),
            stock_quantity: parseInt(document.getElementById('product-quantity').value),
            price: parseFloat(document.getElementById('product-price').value),
            original_price: document.getElementById('product-original-price').value ? parseFloat(document.getElementById('product-original-price').value) : null,
            cost_price: document.getElementById('product-cost-price').value ? parseFloat(document.getElementById('product-cost-price').value) : null,
            status: document.getElementById('product-status').value || 'active',
            low_stock_threshold: parseInt(document.getElementById('product-low-stock-threshold').value) || 10,
            weight_kg: document.getElementById('product-weight').value ? parseFloat(document.getElementById('product-weight').value) : null,
            dimensions_cm: document.getElementById('product-dimensions').value.trim() || null,
            is_featured: document.getElementById('product-featured').checked ? 1 : 0,
            is_new: document.getElementById('product-new').checked ? 1 : 0,
            short_description: document.getElementById('product-description').value || null,
            full_description: document.getElementById('product-description').value || null,
            images: imagesToSend, // Mix of base64 (new) and URLs (existing)
            specifications: specifications
        };

        try {
            const url = `${API_BASE}/products.php`;
            const method = id ? 'PUT' : 'POST';
            
            if (id) {
                productData.id = parseInt(id);
            }

            const response = await fetch(url, {
                method: method,
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(productData)
            });

            const result = await response.json();

            if (result.success) {
                // Reload products
                await loadProducts();
                closeProductModal();
                
                // Show success message
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert(id ? 'Product updated successfully!' : 'Product created successfully!', 'success');
                } else {
                    alert(id ? 'Product updated successfully!' : 'Product created successfully!');
                }
            } else {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Error: ' + (result.message || 'Failed to save product'), 'error');
                } else {
                    alert('Error: ' + (result.message || 'Failed to save product'));
                }
            }
        } catch (error) {
            console.error('Error saving product:', error);
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('An error occurred while saving the product. Please try again.', 'error');
            } else {
                alert('An error occurred while saving the product. Please try again.');
            }
        }
    }

    // Update Stock Modal
    function openUpdateStockModal(id) {
        const product = products.find(p => p.id === id);
        if (!product) return;

        document.getElementById('stock-product-id').value = product.id;
        document.getElementById('stock-product-name').textContent = product.name;
        document.getElementById('stock-current-quantity').textContent = product.stock_quantity || 0;
        document.getElementById('stock-quantity').value = '';
        document.getElementById('stock-modal').classList.add('show');
        lucide.createIcons();
    }

    // Close Stock Modal
    function closeStockModal() {
        document.getElementById('stock-modal').classList.remove('show');
    }

    // Update Stock
    async function updateStock(event) {
        event.preventDefault();

        const id = parseInt(document.getElementById('stock-product-id').value);
        const action = document.getElementById('stock-action').value;
        const quantity = parseInt(document.getElementById('stock-quantity').value);

        const product = products.find(p => p.id === id);
        if (!product) return;

        let newQuantity;
        if (action === 'add') {
            newQuantity = (product.stock_quantity || 0) + quantity;
        } else if (action === 'remove') {
            newQuantity = Math.max(0, (product.stock_quantity || 0) - quantity);
        } else if (action === 'set') {
            newQuantity = quantity;
        }

        try {
            const response = await fetch(`${API_BASE}/products.php`, {
                method: 'PUT',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: id,
                    stock_quantity: newQuantity
                })
            });

            const result = await response.json();

            if (result.success) {
                await loadProducts();
                closeStockModal();
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Stock updated successfully!', 'success');
                } else {
                    alert('Stock updated successfully!');
                }
            } else {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Error: ' + (result.message || 'Failed to update stock'), 'error');
                } else {
                    alert('Error: ' + (result.message || 'Failed to update stock'));
                }
            }
        } catch (error) {
            console.error('Error updating stock:', error);
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('An error occurred while updating stock. Please try again.', 'error');
            } else {
                alert('An error occurred while updating stock. Please try again.');
            }
        }
    }

    // Delete Product
    async function deleteProduct(id) {
        if (typeof Tivora !== 'undefined' && Tivora.confirm) {
            if (!await Tivora.confirm('Are you sure you want to delete this product?')) {
                return;
            }
        } else {
            if (!confirm('Are you sure you want to delete this product?')) {
                return;
            }
        }

        try {
            const response = await fetch(`${API_BASE}/products.php?id=${id}`, {
                method: 'DELETE',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });

            const result = await response.json();

            if (result.success) {
                await loadProducts();
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Product deleted successfully!', 'success');
                } else {
                    alert('Product deleted successfully!');
                }
            } else {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Error: ' + (result.message || 'Failed to delete product'), 'error');
                } else {
                    alert('Error: ' + (result.message || 'Failed to delete product'));
                }
            }
        } catch (error) {
            console.error('Error deleting product:', error);
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('An error occurred while deleting the product. Please try again.', 'error');
            } else {
                alert('An error occurred while deleting the product. Please try again.');
            }
        }
    }

    // Make functions globally accessible
    window.openAddProductModal = openAddProductModal;
    window.closeProductModal = closeProductModal;
    window.openCreateCategoryModal = openCreateCategoryModal;
    window.closeCategoryModal = closeCategoryModal;
    window.openUpdateStockModal = openUpdateStockModal;
    window.closeStockModal = closeStockModal;
    window.saveProduct = saveProduct;
    window.saveCategory = saveCategory;
    window.updateStock = updateStock;
    window.deleteProduct = deleteProduct;
    window.editProduct = editProduct;
    window.handleImageUpload = handleImageUpload;
    window.removeImage = removeImage;
    window.selectReportType = selectReportType;
    window.applyQuickDate = applyQuickDate;
    window.generateReport = generateReport;
</script>
