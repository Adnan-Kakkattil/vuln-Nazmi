<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Management | Tivora Admin</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../logo.png">
    <link rel="shortcut icon" type="image/png" href="../logo.png">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="js/tivora-alerts.js"></script>

    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            color: #1a1a1a;
        }

        /* Sidebar Styles */
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 260px;
            background: white;
            border-right: 1px solid #e5e7eb;
            transition: transform 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }

        .admin-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .admin-sidebar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .admin-sidebar::-webkit-scrollbar-thumb {
            background: #14b8a6;
            border-radius: 3px;
        }

        .admin-sidebar-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #6b7280;
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .admin-sidebar-item:hover {
            background: #fef2f2;
            color: #14b8a6;
        }

        .admin-sidebar-item.active {
            background: #fef2f2;
            color: #14b8a6;
            border-left-color: #14b8a6;
            font-weight: 600;
        }

        .admin-sidebar-item i {
            width: 20px;
            height: 20px;
        }

        /* Main Content Area */
        .admin-main {
            margin-left: 260px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* Top Header */
        .admin-header {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal {
            background: white;
            border-radius: 16px;
            padding: 32px;
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
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

        /* Stock Badge */
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

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-sidebar.show {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0;
            }

            .mobile-menu-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }

            .mobile-menu-overlay.show {
                display: block;
            }

            .modal {
                padding: 24px;
                width: 95%;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #14b8a6;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #0d9488;
        }
    </style>
</head>
<body class="antialiased">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="admin-sidebar">
        <!-- Logo -->
        <div class="p-6 border-b border-gray-200">
            <a href="../index.php" class="flex items-center gap-2">
                <img src="../Tivora_wordmark_red.avif" alt="Tivora Electronics" class="h-8 w-auto">
                <span class="text-xs text-gray-500 font-medium ml-1">Admin</span>
            </a>
        </div>

        <!-- Navigation Menu -->
        <nav class="py-4">
            <a href="dashboard.php" class="admin-sidebar-item">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>
            <a href="stock.php" class="admin-sidebar-item active">
                <i data-lucide="package"></i>
                <span>Stock Management</span>
            </a>
            <a href="finance.php" class="admin-sidebar-item">
                <i data-lucide="dollar-sign"></i>
                <span>Finance</span>
            </a>
            <a href="coupons.php" class="admin-sidebar-item">
                <i data-lucide="ticket"></i>
                <span>Discount Coupons</span>
            </a>
            <a href="orders.php" class="admin-sidebar-item">
                <i data-lucide="shopping-bag"></i>
                <span>Orders</span>
            </a>
            <a href="requests.php" class="admin-sidebar-item">
                <i data-lucide="inbox"></i>
                <span>B2B Requests</span>
            </a>
            <a href="report.php" class="admin-sidebar-item">
                <i data-lucide="file-text"></i>
                <span>Reports</span>
            </a>
            <a href="users.php" class="admin-sidebar-item">
                <i data-lucide="users"></i>
                <span>Users</span>
            </a>
            <a href="roles.php" class="admin-sidebar-item">
                <i data-lucide="shield"></i>
                <span>Roles</span>
            </a>
            <a href="api_integration.php" class="admin-sidebar-item">
                <i data-lucide="plug"></i>
                <span>API Integration</span>
            </a>
            <a href="settings.php" class="admin-sidebar-item">
                <i data-lucide="settings"></i>
                <span>Settings</span>
            </a>
        </nav>

        <!-- Bottom Section -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200">
            <a href="../index.php" class="admin-sidebar-item">
                <i data-lucide="arrow-left"></i>
                <span>Back to Website</span>
            </a>
            <a href="../login.php" class="admin-sidebar-item">
                <i data-lucide="log-out"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobile-menu-overlay" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <main class="admin-main">
        <!-- Top Header -->
        <header class="admin-header">
            <div class="flex items-center gap-4">
                <button id="sidebar-toggle" class="lg:hidden text-gray-600 hover:text-teal-600" onclick="toggleSidebar()">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h1 class="text-2xl font-bold text-gray-900">Stock Management</h1>
            </div>
            <button onclick="openAddProductModal()" class="flex items-center gap-2 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add Product</span>
            </button>
        </header>

        <!-- Content -->
        <div class="p-6 lg:p-8">
            <!-- Search and Filter Bar -->
            <div class="card mb-6">
                <div class="flex flex-col md:flex-row gap-4 items-center">
                    <div class="flex-1 w-full">
                        <div class="relative">
                            <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                            <input type="text" id="search-input" placeholder="Search products by name, SKU, or category..." 
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                    <select id="filter-status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="all">All Stock Status</option>
                        <option value="high">High Stock</option>
                        <option value="medium">Medium Stock</option>
                        <option value="low">Low Stock</option>
                        <option value="out">Out of Stock</option>
                    </select>
                    <select id="filter-category" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="all">All Categories</option>
                        <!-- Categories will be loaded dynamically -->
                    </select>
                </div>
            </div>

            <!-- Stock Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="card">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                            <i data-lucide="check-circle" class="w-6 h-6 text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Products</p>
                            <p class="text-2xl font-bold text-gray-900" id="total-products">0</p>
                        </div>
                    </div>
                </div>
                <div class="card">
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
                <div class="card">
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
                <div class="card">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-teal-100 flex items-center justify-center">
                            <i data-lucide="x-circle" class="w-6 h-6 text-teal-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Out of Stock</p>
                            <p class="text-2xl font-bold text-gray-900" id="out-stock-count">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="card">
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
        </div>
    </main>

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
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">SKU *</label>
                                    <input type="text" id="product-sku" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                                    <div class="flex gap-2">
                                        <select id="product-category" required 
                                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
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
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                                    <input type="text" id="product-model" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                                    <input type="number" id="product-quantity" min="0" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Price (₹) *</label>
                                    <input type="number" id="product-price" min="0" step="0.01" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea id="product-description" rows="3" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Product Images</label>
                                <div class="mt-2">
                                    <input type="file" id="product-images" multiple accept="image/*" 
                                        class="hidden" onchange="handleImageUpload(event)">
                                    <label for="product-images" class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg hover:border-red-500 hover:bg-teal-50 transition-colors">
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

                    <!-- Display Specifications Section -->
                    <div class="spec-section">
                        <h3 class="spec-section-title">Display Specifications</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Screen Size (Inch)</label>
                                    <input type="text" id="spec-screen-size" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                        placeholder="e.g., 86 INCH">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Resolution</label>
                                    <input type="text" id="spec-resolution" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                        placeholder="e.g., 3840*2160 PIXELS">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Frameless</label>
                                    <select id="spec-frameless" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                        <option value="">Select</option>
                                        <option value="yes">Yes</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Panel Brand</label>
                                    <input type="text" id="spec-panel-brand" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                        placeholder="e.g., BOE PANEL">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hardware Specifications Section -->
                    <div class="spec-section">
                        <h3 class="spec-section-title">Hardware Specifications</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Mainboard</label>
                                    <input type="text" id="spec-mainboard" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                        placeholder="e.g., LG WEB OS CVTE-BOARD">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Mainboard Watt</label>
                                    <input type="text" id="spec-mainboard-watt" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                        placeholder="e.g., 180 WATT">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cabinet (Metal/Fiber)</label>
                                <select id="spec-cabinet" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <option value="">Select</option>
                                    <option value="metal">Metal</option>
                                    <option value="fiber">Fiber</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">CPU</label>
                                    <input type="text" id="spec-cpu" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                        placeholder="e.g., 4 CORE">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">GPU</label>
                                    <input type="text" id="spec-gpu" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                        placeholder="e.g., 2 CORE">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">RAM</label>
                                    <input type="text" id="spec-ram" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                        placeholder="e.g., 1.5 GB">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ROM</label>
                                    <input type="text" id="spec-rom" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                        placeholder="e.g., 8 GB">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Software & Operating System Section -->
                    <div class="spec-section">
                        <h3 class="spec-section-title">Software & Operating System</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Operating System</label>
                                    <input type="text" id="spec-os" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                        placeholder="e.g., LG WEB OS">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Android/Linux Version</label>
                                    <input type="text" id="spec-android-version" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                        placeholder="e.g., LINUX">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Supported Apps</label>
                                <textarea id="spec-supported-apps" rows="3" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                    placeholder="e.g., NETFLIX, YOUTUBE, AMAZON PRIME VIDEO, etc."></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Video Support</label>
                                <input type="text" id="spec-video-support" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                    placeholder="e.g., MPEG, M3U8, MP4, MOV, M4V, MKV, etc.">
                            </div>
                        </div>
                    </div>

                    <!-- Audio Specifications Section -->
                    <div class="spec-section">
                        <h3 class="spec-section-title">Audio Specifications</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Sound Output</label>
                                    <input type="text" id="spec-sound-output" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                        placeholder="e.g., 20W*2 SOUNDBAR">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Audio Features</label>
                                    <input type="text" id="spec-audio-features" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                        placeholder="e.g., HD SOUND WITH DOLBY">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Earphone Output</label>
                                <input type="text" id="spec-earphone-output" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                    placeholder="e.g., 1">
                            </div>
                        </div>
                    </div>

                    <!-- Connectivity Specifications Section -->
                    <div class="spec-section">
                        <h3 class="spec-section-title">Connectivity</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Wi-Fi</label>
                                <input type="text" id="spec-wifi" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                    placeholder="e.g., 2.4GHZ IEEE802.11B/G/N">
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">USB Ports</label>
                                    <input type="number" id="spec-usb" min="0" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                        placeholder="e.g., 3">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">HDMI Ports</label>
                                    <input type="number" id="spec-hdmi" min="0" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                        placeholder="e.g., 3">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Earphone Output</label>
                                    <input type="number" id="spec-earphone" min="0" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                        placeholder="e.g., 1">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Bluetooth 1 (For Remote)</label>
                                    <select id="spec-bluetooth1" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                        <option value="">Select</option>
                                        <option value="yes">Yes</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Bluetooth 2 (For Soundbar)</label>
                                    <select id="spec-bluetooth2" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                        <option value="">Select</option>
                                        <option value="yes">Yes</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Remote Type</label>
                                <input type="text" id="spec-remote" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                    placeholder="e.g., MAGICREMOTE">
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            placeholder="e.g., Smart TVs">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="category-description" rows="3" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
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
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="add">Add Stock</option>
                        <option value="remove">Remove Stock</option>
                        <option value="set">Set Stock</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                    <input type="number" id="stock-quantity" min="0" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
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

    <script>
        // Products data loaded from API
        let products = [];
        let categories = [];
        
        // Store uploaded images temporarily
        let uploadedImages = [];

        // API base URL
        const API_BASE = '/api/v1/admin';

        // Initialize Lucide Icons
        lucide.createIcons();

        // Load products and categories on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadCategories();
            loadProducts();
        });

        // Load categories from API
        async function loadCategories() {
            try {
                const response = await fetch(`${API_BASE}/categories.php`);
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
                const response = await fetch(`${API_BASE}/products.php`);
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
                row.innerHTML = `
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i data-lucide="tv" class="w-5 h-5 text-gray-400"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">${product.name}</p>
                                <p class="text-xs text-gray-500">${product.description || 'No description'}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-700">${product.sku}</td>
                    <td class="py-3 px-4 text-sm text-gray-700">${product.category_name || 'N/A'}</td>
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
                            <button onclick="deleteProduct(${product.id})" class="p-2 text-teal-600 hover:bg-teal-50 rounded-lg transition-colors" title="Delete">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });

            lucide.createIcons();
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
            // Reset all specification fields
            document.getElementById('spec-screen-size').value = '';
            document.getElementById('spec-resolution').value = '';
            document.getElementById('spec-frameless').value = '';
            document.getElementById('spec-panel-brand').value = '';
            document.getElementById('spec-mainboard').value = '';
            document.getElementById('spec-mainboard-watt').value = '';
            document.getElementById('spec-cabinet').value = '';
            document.getElementById('spec-cpu').value = '';
            document.getElementById('spec-gpu').value = '';
            document.getElementById('spec-ram').value = '';
            document.getElementById('spec-rom').value = '';
            document.getElementById('spec-os').value = '';
            document.getElementById('spec-android-version').value = '';
            document.getElementById('spec-supported-apps').value = '';
            document.getElementById('spec-video-support').value = '';
            document.getElementById('spec-sound-output').value = '';
            document.getElementById('spec-audio-features').value = '';
            document.getElementById('spec-earphone-output').value = '';
            document.getElementById('spec-wifi').value = '';
            document.getElementById('spec-usb').value = '';
            document.getElementById('spec-hdmi').value = '';
            document.getElementById('spec-earphone').value = '';
            document.getElementById('spec-bluetooth1').value = '';
            document.getElementById('spec-bluetooth2').value = '';
            document.getElementById('spec-remote').value = '';
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
            document.getElementById('product-model').value = (product.specifications && product.specifications['Model']) || '';
            document.getElementById('product-quantity').value = product.stock_quantity || 0;
            document.getElementById('product-price').value = product.price;
            document.getElementById('product-description').value = product.short_description || product.full_description || '';
            
            // Load images
            uploadedImages = (product.images || []).map(img => ({
                name: img.alt_text || 'image',
                data: img.image_url || '',
                id: Date.now() + Math.random()
            }));
            renderImagePreviews();
            
            // Load specifications if they exist
            const specs = product.specifications || {};
            document.getElementById('spec-screen-size').value = specs['Screen Size'] || '';
            document.getElementById('spec-resolution').value = specs['Resolution'] || '';
            document.getElementById('spec-frameless').value = specs['Frameless'] || '';
            document.getElementById('spec-panel-brand').value = specs['Panel Brand'] || '';
            document.getElementById('spec-mainboard').value = specs['Mainboard'] || '';
            document.getElementById('spec-mainboard-watt').value = specs['Mainboard Watt'] || '';
            document.getElementById('spec-cabinet').value = specs['Cabinet'] || '';
            document.getElementById('spec-cpu').value = specs['CPU'] || '';
            document.getElementById('spec-gpu').value = specs['GPU'] || '';
            document.getElementById('spec-ram').value = specs['RAM'] || '';
            document.getElementById('spec-rom').value = specs['ROM'] || '';
            document.getElementById('spec-os').value = specs['Operating System'] || '';
            document.getElementById('spec-android-version').value = specs['Android/Linux Version'] || '';
            document.getElementById('spec-supported-apps').value = specs['Supported Apps'] || '';
            document.getElementById('spec-video-support').value = specs['Video Support'] || '';
            document.getElementById('spec-sound-output').value = specs['Sound Output'] || '';
            document.getElementById('spec-audio-features').value = specs['Audio Features'] || '';
            document.getElementById('spec-earphone-output').value = specs['Earphone Output'] || '';
            document.getElementById('spec-wifi').value = specs['Wi-Fi'] || '';
            document.getElementById('spec-usb').value = specs['USB Ports'] || '';
            document.getElementById('spec-hdmi').value = specs['HDMI Ports'] || '';
            document.getElementById('spec-earphone').value = specs['Earphone Output Ports'] || '';
            document.getElementById('spec-bluetooth1').value = specs['Bluetooth 1 (For Remote)'] || '';
            document.getElementById('spec-bluetooth2').value = specs['Bluetooth 2 (For Soundbar)'] || '';
            document.getElementById('spec-remote').value = specs['Remote Type'] || '';
            
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
                    alert('Category created successfully!');
                } else {
                    alert('Error: ' + (result.message || 'Failed to create category'));
                }
            } catch (error) {
                console.error('Error creating category:', error);
                alert('An error occurred while creating the category. Please try again.');
            }
        }

        // Save Product
        async function saveProduct(event) {
            event.preventDefault();

            const id = document.getElementById('product-id').value;
            
            // Build specifications object
            const specifications = {};
            const brand = document.getElementById('product-brand').value.trim();
            const model = document.getElementById('product-model').value.trim();
            
            if (brand) specifications['Brand'] = brand;
            if (model) specifications['Model'] = model;
            
            // Add all specification fields
            const specFields = {
                'Screen Size': document.getElementById('spec-screen-size').value.trim(),
                'Resolution': document.getElementById('spec-resolution').value.trim(),
                'Frameless': document.getElementById('spec-frameless').value.trim(),
                'Panel Brand': document.getElementById('spec-panel-brand').value.trim(),
                'Mainboard': document.getElementById('spec-mainboard').value.trim(),
                'Mainboard Watt': document.getElementById('spec-mainboard-watt').value.trim(),
                'Cabinet': document.getElementById('spec-cabinet').value.trim(),
                'CPU': document.getElementById('spec-cpu').value.trim(),
                'GPU': document.getElementById('spec-gpu').value.trim(),
                'RAM': document.getElementById('spec-ram').value.trim(),
                'ROM': document.getElementById('spec-rom').value.trim(),
                'Operating System': document.getElementById('spec-os').value.trim(),
                'Android/Linux Version': document.getElementById('spec-android-version').value.trim(),
                'Supported Apps': document.getElementById('spec-supported-apps').value.trim(),
                'Video Support': document.getElementById('spec-video-support').value.trim(),
                'Sound Output': document.getElementById('spec-sound-output').value.trim(),
                'Audio Features': document.getElementById('spec-audio-features').value.trim(),
                'Earphone Output': document.getElementById('spec-earphone-output').value.trim(),
                'Wi-Fi': document.getElementById('spec-wifi').value.trim(),
                'USB Ports': document.getElementById('spec-usb').value.trim(),
                'HDMI Ports': document.getElementById('spec-hdmi').value.trim(),
                'Earphone Output Ports': document.getElementById('spec-earphone').value.trim(),
                'Bluetooth 1 (For Remote)': document.getElementById('spec-bluetooth1').value.trim(),
                'Bluetooth 2 (For Soundbar)': document.getElementById('spec-bluetooth2').value.trim(),
                'Remote Type': document.getElementById('spec-remote').value.trim()
            };
            
            Object.keys(specFields).forEach(key => {
                if (specFields[key]) {
                    specifications[key] = specFields[key];
                }
            });
            
            const productData = {
                name: document.getElementById('product-name').value,
                sku: document.getElementById('product-sku').value,
                category_id: parseInt(document.getElementById('product-category').value),
                stock_quantity: parseInt(document.getElementById('product-quantity').value),
                price: parseFloat(document.getElementById('product-price').value),
                short_description: document.getElementById('product-description').value || null,
                full_description: document.getElementById('product-description').value || null,
                images: uploadedImages.map(img => img.data), // Base64 strings
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
                    
                    // Show success message (you can add a toast notification here)
                    alert(id ? 'Product updated successfully!' : 'Product created successfully!');
                } else {
                    alert('Error: ' + (result.message || 'Failed to save product'));
                }
            } catch (error) {
                console.error('Error saving product:', error);
                alert('An error occurred while saving the product. Please try again.');
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
                    await Tivora.alert('Stock updated successfully!', 'success');
                } else {
                    await Tivora.alert('Error: ' + (result.message || 'Failed to update stock'), 'error');
                }
            } catch (error) {
                console.error('Error updating stock:', error);
                await Tivora.alert('An error occurred while updating stock. Please try again.', 'error');
            }
        }

        // Delete Product
        async function deleteProduct(id) {
            if (!await Tivora.confirm('Are you sure you want to delete this product?')) {
                return;
            }

            try {
                const response = await fetch(`${API_BASE}/products.php?id=${id}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    await loadProducts();
                    await Tivora.alert('Product deleted successfully!', 'success');
                } else {
                    await Tivora.alert('Error: ' + (result.message || 'Failed to delete product'), 'error');
                }
            } catch (error) {
                console.error('Error deleting product:', error);
                await Tivora.alert('An error occurred while deleting the product. Please try again.', 'error');
            }
        }

        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('mobile-menu-overlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        // Reinitialize icons
        setInterval(() => {
            lucide.createIcons();
        }, 1000);
    </script>
</body>
</html>
