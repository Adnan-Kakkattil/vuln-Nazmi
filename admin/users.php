<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | BLine Boutique Admin</title>
    
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
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
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
        }

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

        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            transform: scale(0.95);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .modal-overlay.show .modal-content {
            transform: scale(1);
            opacity: 1;
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
                <img src="../Tivora_wordmark_red.avif" alt="BLine Boutique" class="h-8 w-auto">
                <span class="text-xs text-gray-500 font-medium ml-1">Admin</span>
            </a>
        </div>

        <!-- Navigation Menu -->
        <nav class="py-4">
            <a href="dashboard.php" class="admin-sidebar-item">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>
            <a href="stock.php" class="admin-sidebar-item">
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
            <a href="report.php" class="admin-sidebar-item">
                <i data-lucide="file-text"></i>
                <span>Reports</span>
            </a>
            <a href="users.php" class="admin-sidebar-item active">
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
                <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
            </div>
            <div class="flex items-center gap-4">
                <button onclick="openCreateUserModal()" class="px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors flex items-center gap-2 text-sm font-medium">
                    <i data-lucide="user-plus"></i>
                    <span>Add User</span>
                </button>
                <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center">
                    <i data-lucide="user" class="w-5 h-5 text-teal-600"></i>
                </div>
            </div>
        </header>

        <!-- Users Content -->
        <div class="p-6 lg:p-8">
            <!-- Users Table Card -->
            <div class="stat-card">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">All Users</h2>
                    <div class="flex items-center gap-3">
                        <input 
                            type="text" 
                            id="search-users" 
                            placeholder="Search users..." 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                            onkeyup="filterUsers()"
                        >
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Name</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Email</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Role</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Created</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body" class="divide-y divide-gray-200">
                            <!-- Users will be dynamically inserted here -->
                        </tbody>
                    </table>
                    <div id="no-users-message" class="text-center py-12 text-gray-500 hidden">
                        <i data-lucide="users" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                        <p class="text-lg font-medium mb-2">No users found</p>
                        <p class="text-sm">Click "Add User" to create your first user</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Create/Edit User Modal -->
    <div class="modal-overlay" id="create-user-modal-overlay" onclick="closeCreateUserModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900" id="user-modal-title">Create New User</h2>
                <button onclick="closeCreateUserModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-lg">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form id="create-user-form" onsubmit="submitCreateUserForm(event)" class="p-6 space-y-5">
                <!-- First Name -->
                <div>
                    <label for="user-first-name" class="block text-sm font-medium text-gray-700 mb-2">
                        First Name <span class="text-teal-600">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="user-first-name" 
                        name="first_name" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-teal-500 focus:ring-2 focus:ring-red-100 outline-none transition-all"
                        placeholder="Enter first name"
                    >
                </div>

                <!-- Last Name -->
                <div>
                    <label for="user-last-name" class="block text-sm font-medium text-gray-700 mb-2">
                        Last Name <span class="text-teal-600">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="user-last-name" 
                        name="last_name" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-teal-500 focus:ring-2 focus:ring-red-100 outline-none transition-all"
                        placeholder="Enter last name"
                    >
                </div>

                <!-- Email -->
                <div>
                    <label for="user-email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address <span class="text-teal-600">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="user-email" 
                        name="email" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-teal-500 focus:ring-2 focus:ring-red-100 outline-none transition-all"
                        placeholder="user@example.com"
                    >
                    <p class="text-xs text-gray-500 mt-1">This email will be used for login</p>
                </div>

                <!-- Password -->
                <div>
                    <label for="user-password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password <span class="text-teal-600" id="password-required-star">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="user-password" 
                        name="password" 
                        minlength="8"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-teal-500 focus:ring-2 focus:ring-red-100 outline-none transition-all"
                        placeholder="Enter password (minimum 8 characters)"
                    >
                    <p class="text-xs text-gray-500 mt-1" id="password-help-text">Password must be at least 8 characters. <span id="password-optional-text" class="hidden">Leave blank to keep current password.</span></p>
                </div>

                <!-- Confirm Password -->
                <div id="password-confirm-group">
                    <label for="user-password-confirm" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirm Password <span class="text-teal-600" id="password-confirm-required-star">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="user-password-confirm" 
                        name="passwordConfirm" 
                        minlength="8"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-teal-500 focus:ring-2 focus:ring-red-100 outline-none transition-all"
                        placeholder="Confirm password"
                    >
                </div>

                <!-- Role Selection -->
                <div>
                    <label for="user-role" class="block text-sm font-medium text-gray-700 mb-2">
                        Role <span class="text-teal-600">*</span>
                    </label>
                    <select 
                        id="user-role" 
                        name="role" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-teal-500 focus:ring-2 focus:ring-red-100 outline-none transition-all"
                    >
                        <option value="">Select a role</option>
                        <!-- Roles will be loaded dynamically -->
                        <optgroup label="Roles" id="roles-optgroup"></optgroup>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Select a role to automatically assign module permissions</p>
                </div>

                <!-- Status -->
                <div>
                    <label for="user-status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status
                    </label>
                    <select 
                        id="user-status" 
                        name="status" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-teal-500 focus:ring-2 focus:ring-red-100 outline-none transition-all"
                    >
                        <option value="1" selected>Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200">
                    <button 
                        type="submit"
                        class="flex-1 bg-teal-500 text-white py-3 px-6 rounded-lg font-semibold hover:bg-teal-600 transition-colors shadow-md shadow-red-100 flex items-center justify-center gap-2"
                        id="user-submit-button"
                    >
                        <i data-lucide="user-plus" class="w-5 h-5" id="user-submit-icon"></i>
                        <span id="user-submit-text">Create User</span>
                    </button>
                    <button 
                        type="button"
                        onclick="closeCreateUserModal()"
                        class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 hover:border-gray-400 transition-colors"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // API Base URL
        const API_BASE = '../api/v1/admin';

        // Global state
        let allRoles = [];
        let allUsers = [];
        let editingUserId = null;

        // Load and render users from API
        async function loadUsers() {
            const tbody = document.getElementById('users-table-body');
            const noUsersMsg = document.getElementById('no-users-message');
            
            try {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">Loading users...</td></tr>';
                
                const response = await fetch(`${API_BASE}/users.php`, {
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' }
                });
                
                if (!response.ok) {
                    if (response.status === 401) {
                        window.location.href = '../login.php';
                        return;
                    }
                    throw new Error('Failed to load users');
                }
                
                const result = await response.json();
                
                if (!result.success || !result.data || result.data.length === 0) {
                    tbody.innerHTML = '';
                    noUsersMsg.classList.remove('hidden');
                    return;
                }
                
                allUsers = result.data;
                noUsersMsg.classList.add('hidden');
                
                tbody.innerHTML = result.data.map(user => `
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 text-sm text-gray-900 font-medium">${user.first_name} ${user.last_name}</td>
                        <td class="py-3 px-4 text-sm text-gray-700">${user.email}</td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getRoleBadgeColor(user.role_name)}">
                                ${user.role_name || 'No Role'}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${user.is_active == 1 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                ${user.is_active == 1 ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-500">${formatDate(user.created_at)}</td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <button onclick="editUser(${user.id})" class="text-blue-600 hover:text-blue-700 p-1" title="Edit">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </button>
                                <button onclick="deleteUser(${user.id})" class="text-teal-600 hover:text-red-700 p-1" title="Deactivate">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
                
                lucide.createIcons();
            } catch (error) {
                console.error('Error loading users:', error);
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-teal-600">Error loading users. Please try again.</td></tr>';
                noUsersMsg.classList.add('hidden');
            }
        }

        function getRoleBadgeColor(roleName) {
            if (!roleName) return 'bg-gray-100 text-gray-800';
            const colors = {
                'Super Admin': 'bg-teal-100 text-red-800',
                'Admin': 'bg-teal-100 text-red-800',
                'Manager': 'bg-blue-100 text-blue-800',
                'Support': 'bg-green-100 text-green-800',
                'Viewer': 'bg-gray-100 text-gray-800'
            };
            return colors[roleName] || 'bg-gray-100 text-gray-800';
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function filterUsers() {
            const searchTerm = document.getElementById('search-users').value.toLowerCase();
            const rows = document.querySelectorAll('#users-table-body tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        }

        // Load roles into dropdown from API
        async function loadRolesIntoDropdown() {
            const rolesOptgroup = document.getElementById('roles-optgroup');
            
            if (!rolesOptgroup) {
                console.error('Roles optgroup element not found');
                return;
            }
            
            try {
                // Show loading state
                rolesOptgroup.innerHTML = '<option value="">Loading roles...</option>';
                
                const response = await fetch(`${API_BASE}/roles.php?status=1`, {
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' }
                });
                
                if (!response.ok) {
                    if (response.status === 401) {
                        window.location.href = '../login.php';
                        return;
                    }
                    throw new Error('Failed to load roles');
                }
                
                const result = await response.json();
                
                if (result.success && result.data) {
                    // Filter only active roles (is_active = 1)
                    const activeRoles = result.data.filter(role => role.is_active == 1);
                    allRoles = activeRoles;
                    
                    if (activeRoles.length === 0) {
                        rolesOptgroup.innerHTML = '<option value="">No active roles available</option>';
                        return;
                    }
                    
                    rolesOptgroup.innerHTML = activeRoles.map(role => {
                        return `<option value="${role.id}">${role.name}${role.description ? ' - ' + role.description : ''}</option>`;
                    }).join('');
                } else {
                    rolesOptgroup.innerHTML = '<option value="">No roles found</option>';
                }
            } catch (error) {
                console.error('Error loading roles:', error);
                rolesOptgroup.innerHTML = '<option value="">Error loading roles</option>';
            }
        }

        // Create User Modal Functions
        function openCreateUserModal() {
            editingUserId = null;
            document.getElementById('user-modal-title').textContent = 'Create New User';
            document.getElementById('user-submit-text').textContent = 'Create User';
            document.getElementById('user-submit-icon').setAttribute('data-lucide', 'user-plus');
            
            // Make password required for create
            document.getElementById('user-password').required = true;
            document.getElementById('user-password-confirm').required = true;
            document.getElementById('password-required-star').classList.remove('hidden');
            document.getElementById('password-confirm-required-star').classList.remove('hidden');
            document.getElementById('password-optional-text').classList.add('hidden');
            
            const overlay = document.getElementById('create-user-modal-overlay');
            
            // Load roles
            loadRolesIntoDropdown();
            
            // Reset form
            document.getElementById('create-user-form').reset();
            
            // Show modal
            overlay.classList.add('show');
            
            // Reinitialize icons
            setTimeout(() => {
                lucide.createIcons();
            }, 100);
        }

        function closeCreateUserModal(event) {
            if (!event || event.target.id === 'create-user-modal-overlay') {
                const overlay = document.getElementById('create-user-modal-overlay');
                overlay.classList.remove('show');
                
                // Reset form
                document.getElementById('create-user-form').reset();
                editingUserId = null;
            }
        }

        async function submitCreateUserForm(event) {
            event.preventDefault();
            
            const form = document.getElementById('create-user-form');
            const formData = new FormData(form);
            
            // Get form values
            const firstName = formData.get('first_name').trim();
            const lastName = formData.get('last_name').trim();
            const email = formData.get('email').trim().toLowerCase();
            const password = formData.get('password');
            const passwordConfirm = formData.get('passwordConfirm');
            const roleId = formData.get('role');
            const isActive = formData.get('status') || '1';
            
            // Validation
            if (!firstName || !lastName || !email) {
                await Tivora.alert('All required fields must be filled!', 'warning');
                return;
            }
            
            // Password validation (required for create, optional for edit)
            if (!editingUserId) {
                if (!password) {
                    await Tivora.alert('Password is required for new users!', 'warning');
                    return;
                }
                if (password !== passwordConfirm) {
                    await Tivora.alert('Passwords do not match!', 'warning');
                    return;
                }
                if (password.length < 8) {
                    await Tivora.alert('Password must be at least 8 characters long!', 'warning');
                    return;
                }
            } else {
                // For edit: if password is provided, validate it
                if (password) {
                    if (password !== passwordConfirm) {
                        await Tivora.alert('Passwords do not match!', 'warning');
                        return;
                    }
                    if (password.length < 8) {
                        await Tivora.alert('Password must be at least 8 characters long!', 'warning');
                        return;
                    }
                }
            }
            
            if (!roleId) {
                await Tivora.alert('Please select a role!', 'warning');
                return;
            }
            
            try {
                const url = editingUserId ? `${API_BASE}/users.php?id=${editingUserId}` : `${API_BASE}/users.php`;
                const method = editingUserId ? 'PUT' : 'POST';
                
                const requestBody = {
                    first_name: firstName,
                    last_name: lastName,
                    email: email,
                    role_id: parseInt(roleId),
                    is_active: parseInt(isActive)
                };
                
                // Only include password if provided (for edit) or always (for create)
                if (password) {
                    requestBody.password = password;
                }
                
                const response = await fetch(url, {
                    method: method,
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(requestBody)
                });
                
                const result = await response.json();
                
                if (!response.ok || !result.success) {
                    await Tivora.alert(result.message || `Failed to ${editingUserId ? 'update' : 'create'} user`, 'error');
                    return;
                }
                
                await Tivora.alert(`User ${editingUserId ? 'updated' : 'created'} successfully!`, 'success');
                closeCreateUserModal();
                loadUsers();
            } catch (error) {
                console.error(`Error ${editingUserId ? 'updating' : 'creating'} user:`, error);
                await Tivora.alert(`Error ${editingUserId ? 'updating' : 'creating'} user. Please try again.`, 'error');
            }
        }

        async function editUser(userId) {
            try {
                const response = await fetch(`${API_BASE}/users.php?id=${userId}`, {
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' }
                });
                
                if (!response.ok) {
                    await Tivora.alert('Failed to load user details', 'error');
                    return;
                }
                
                const result = await response.json();
                
                if (!result.success || !result.data) {
                    await Tivora.alert('User not found', 'error');
                    return;
                }
                
                const user = result.data;
                editingUserId = userId;
                
                // Update modal title and button
                document.getElementById('user-modal-title').textContent = 'Edit User';
                document.getElementById('user-submit-text').textContent = 'Update User';
                document.getElementById('user-submit-icon').setAttribute('data-lucide', 'save');
                
                // Make password optional for edit
                document.getElementById('user-password').required = false;
                document.getElementById('user-password-confirm').required = false;
                document.getElementById('password-required-star').classList.add('hidden');
                document.getElementById('password-confirm-required-star').classList.add('hidden');
                document.getElementById('password-optional-text').classList.remove('hidden');
                
                // Load roles first
                await loadRolesIntoDropdown();
                
                // Fill form with user data
                document.getElementById('user-first-name').value = user.first_name || '';
                document.getElementById('user-last-name').value = user.last_name || '';
                document.getElementById('user-email').value = user.email || '';
                document.getElementById('user-role').value = user.role_id || '';
                document.getElementById('user-status').value = user.is_active || 1;
                
                // Clear password fields
                document.getElementById('user-password').value = '';
                document.getElementById('user-password-confirm').value = '';
                
                // Show modal
                const overlay = document.getElementById('create-user-modal-overlay');
                overlay.classList.add('show');
                
                // Reinitialize icons
                setTimeout(() => {
                    lucide.createIcons();
                }, 100);
            } catch (error) {
                console.error('Error loading user:', error);
                await Tivora.alert('Error loading user. Please try again.', 'error');
            }
        }

        async function deleteUser(userId) {
            if (!await Tivora.confirm('Are you sure you want to deactivate this user?')) {
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE}/users.php?id=${userId}`, {
                    method: 'DELETE',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' }
                });
                
                const result = await response.json();
                
                if (!response.ok || !result.success) {
                    await Tivora.alert(result.message || 'Failed to delete user', 'error');
                    return;
                }
                
                await Tivora.alert('User deactivated successfully!', 'success');
                loadUsers();
            } catch (error) {
                console.error('Error deleting user:', error);
                await Tivora.alert('Error deleting user. Please try again.', 'error');
            }
        }

        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('mobile-menu-overlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadUsers();
            loadRolesIntoDropdown();
        });

        // Reinitialize icons
        setInterval(() => {
            lucide.createIcons();
        }, 1000);
    </script>
</body>
</html>
