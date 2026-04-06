<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Management | Tivora Admin</title>
    
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
            max-width: 700px;
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

        /* Permission Checkbox Grid */
        .permission-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
        }

        .permission-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .permission-item:hover {
            background: #f9fafb;
            border-color: #14b8a6;
        }

        .permission-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #14b8a6;
            cursor: pointer;
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

            .permission-grid {
                grid-template-columns: 1fr;
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
            <a href="roles.php" class="admin-sidebar-item active">
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
                <h1 class="text-2xl font-bold text-gray-900">Role Management</h1>
            </div>
            <div class="flex items-center gap-4">
                <button onclick="openCreateRoleModal()" class="px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors flex items-center gap-2 text-sm font-medium">
                    <i data-lucide="shield-plus"></i>
                    <span>Create Role</span>
                </button>
                <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center">
                    <i data-lucide="shield" class="w-5 h-5 text-teal-600"></i>
                </div>
            </div>
        </header>

        <!-- Roles Content -->
        <div class="p-6 lg:p-8">
            <!-- Roles Table Card -->
            <div class="stat-card">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">All Roles</h2>
                    <div class="flex items-center gap-3">
                        <input 
                            type="text" 
                            id="search-roles" 
                            placeholder="Search roles..." 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                            onkeyup="filterRoles()"
                        >
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Role Name</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Description</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Module Access</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Users</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Created</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="roles-table-body" class="divide-y divide-gray-200">
                            <!-- Roles will be dynamically inserted here -->
                        </tbody>
                    </table>
                    <div id="no-roles-message" class="text-center py-12 text-gray-500 hidden">
                        <i data-lucide="shield" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                        <p class="text-lg font-medium mb-2">No roles found</p>
                        <p class="text-sm">Click "Create Role" to create your first custom role</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Create/Edit Role Modal -->
    <div class="modal-overlay" id="role-modal-overlay" onclick="closeRoleModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900" id="role-modal-title">Create New Role</h2>
                <button onclick="closeRoleModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-lg">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form id="role-form" onsubmit="submitRoleForm(event)" class="p-6 space-y-6">
                <!-- Role Name -->
                <div>
                    <label for="role-name" class="block text-sm font-medium text-gray-700 mb-2">
                        Role Name <span class="text-teal-600">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="role-name" 
                        name="name" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-teal-500 focus:ring-2 focus:ring-red-100 outline-none transition-all"
                        placeholder="e.g., Sales Manager, Inventory Admin"
                    >
                </div>

                <!-- Description -->
                <div>
                    <label for="role-description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea 
                        id="role-description" 
                        name="description" 
                        rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-teal-500 focus:ring-2 focus:ring-red-100 outline-none transition-all resize-none"
                        placeholder="Brief description of this role's responsibilities"
                    ></textarea>
                </div>

                <!-- Module Permissions -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Module Access Permissions <span class="text-teal-600">*</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-4">Select which modules users with this role can access</p>
                    <div class="permission-grid" id="permissions-grid">
                        <!-- Permissions will be dynamically inserted here -->
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200">
                    <button 
                        type="submit"
                        class="flex-1 bg-teal-500 text-white py-3 px-6 rounded-lg font-semibold hover:bg-teal-600 transition-colors shadow-md shadow-red-100 flex items-center justify-center gap-2"
                    >
                        <i data-lucide="save" class="w-5 h-5"></i>
                        <span id="role-submit-text">Create Role</span>
                    </button>
                    <button 
                        type="button"
                        onclick="closeRoleModal()"
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
        let allPermissions = [];
        let permissionModuleMap = {}; // Maps module names to permission IDs

        // Available modules with labels and icons
        const availableModules = {
            dashboard: { label: 'Dashboard', icon: 'layout-dashboard' },
            stock: { label: 'Stock Management', icon: 'package' },
            finance: { label: 'Finance', icon: 'dollar-sign' },
            coupons: { label: 'Discount Coupons', icon: 'ticket' },
            orders: { label: 'Orders', icon: 'shopping-bag' },
            requests: { label: 'B2B Requests', icon: 'inbox' },
            reports: { label: 'Reports', icon: 'file-text' },
            users: { label: 'Users', icon: 'users' }
        };

        let editingRoleId = null;

        // Load and render roles from API
        async function loadRoles() {
            const tbody = document.getElementById('roles-table-body');
            const noRolesMsg = document.getElementById('no-roles-message');
            
            try {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">Loading roles...</td></tr>';
                
                const response = await fetch(`${API_BASE}/roles.php`, {
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
                
                if (!result.success || !result.data || result.data.length === 0) {
                    tbody.innerHTML = '';
                    noRolesMsg.classList.remove('hidden');
                    return;
                }
                
                allRoles = result.data;
                noRolesMsg.classList.add('hidden');
                
                tbody.innerHTML = result.data.map(role => {
                    const userCount = role.user_count || 0;
                    
                    // Get enabled modules from permissions count (simplified)
                    const hasPermissions = (role.permission_count || 0) > 0;
                    const moduleBadges = hasPermissions ? 
                        '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">Has Access</span>' :
                        '<span class="text-gray-400 text-xs">No modules</span>';
                    
                    return `
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 text-sm text-gray-900 font-medium">${role.name}</td>
                            <td class="py-3 px-4 text-sm text-gray-700">${role.description || 'No description'}</td>
                            <td class="py-3 px-4 text-sm text-gray-700">
                                <div class="flex flex-wrap gap-1">
                                    ${moduleBadges}
                                </div>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-700">
                                <span class="font-medium">${userCount}</span> ${userCount === 1 ? 'user' : 'users'}
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-500">${formatDate(role.created_at)}</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <button onclick="editRole(${role.id})" class="text-blue-600 hover:text-blue-700 p-1" title="Edit">
                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                    </button>
                                    <button onclick="deleteRole(${role.id})" class="text-teal-600 hover:text-red-700 p-1" title="Delete">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');
                
                lucide.createIcons();
            } catch (error) {
                console.error('Error loading roles:', error);
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-teal-600">Error loading roles. Please try again.</td></tr>';
                noRolesMsg.classList.add('hidden');
            }
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function filterRoles() {
            const searchTerm = document.getElementById('search-roles').value.toLowerCase();
            const rows = document.querySelectorAll('#roles-table-body tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        }

        // Initialize permissions grid
        function initializePermissionsGrid(permissions = {}) {
            const grid = document.getElementById('permissions-grid');
            grid.innerHTML = Object.entries(availableModules).map(([key, module]) => {
                const isChecked = permissions[key] || false;
                return `
                    <div class="permission-item">
                        <input 
                            type="checkbox" 
                            id="perm-${key}" 
                            name="permissions" 
                            value="${key}"
                            ${isChecked ? 'checked' : ''}
                        >
                        <label for="perm-${key}" class="text-sm font-medium text-gray-700 cursor-pointer flex-1">
                            <i data-lucide="${module.icon}" class="w-4 h-4 inline mr-1"></i>
                            ${module.label}
                        </label>
                    </div>
                `;
            }).join('');
            
            lucide.createIcons();
        }

        // Create Role Modal Functions
        function openCreateRoleModal() {
            editingRoleId = null;
            document.getElementById('role-modal-title').textContent = 'Create New Role';
            document.getElementById('role-submit-text').textContent = 'Create Role';
            document.getElementById('role-form').reset();
            initializePermissionsGrid();
            
            const overlay = document.getElementById('role-modal-overlay');
            overlay.classList.add('show');
        }

        async function editRole(roleId) {
            try {
                const response = await fetch(`${API_BASE}/roles.php?id=${roleId}`, {
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' }
                });
                
                if (!response.ok) {
                    await Tivora.alert('Failed to load role details', 'error');
                    return;
                }
                
                const result = await response.json();
                
                if (!result.success || !result.data) {
                    await Tivora.alert('Role not found', 'error');
                    return;
                }
                
                const role = result.data;
                editingRoleId = roleId;
                document.getElementById('role-modal-title').textContent = 'Edit Role';
                document.getElementById('role-submit-text').textContent = 'Update Role';
                
                // Fill form
                document.getElementById('role-name').value = role.name;
                document.getElementById('role-description').value = role.description || '';
                
                // Get role permissions and map to modules
                const rolePermissions = role.permissions || [];
                const enabledModules = {};
                
                // Map permissions to modules
                Object.keys(availableModules).forEach(moduleKey => {
                    const modulePermIds = permissionModuleMap[moduleKey] || [];
                    const hasAnyPermission = modulePermIds.some(permId => 
                        rolePermissions.some(rp => rp.id == permId)
                    );
                    enabledModules[moduleKey] = hasAnyPermission;
                });
                
                // Initialize permissions grid
                initializePermissionsGrid(enabledModules);
                
                const overlay = document.getElementById('role-modal-overlay');
                overlay.classList.add('show');
            } catch (error) {
                console.error('Error loading role:', error);
                await Tivora.alert('Error loading role. Please try again.', 'error');
            }
        }

        function closeRoleModal(event) {
            if (!event || event.target.id === 'role-modal-overlay') {
                const overlay = document.getElementById('role-modal-overlay');
                overlay.classList.remove('show');
                
                // Reset form
                document.getElementById('role-form').reset();
                editingRoleId = null;
            }
        }

        // Load permissions and map modules to permission IDs
        async function loadPermissions() {
            try {
                const response = await fetch(`${API_BASE}/permissions.php`, {
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    if (result.success && result.data) {
                        allPermissions = result.data;
                        // Map module names to permission IDs
                        Object.keys(availableModules).forEach(moduleKey => {
                            const modulePerms = result.data.filter(p => p.module === moduleKey);
                            permissionModuleMap[moduleKey] = modulePerms.map(p => p.id);
                        });
                    }
                }
            } catch (error) {
                console.error('Error loading permissions:', error);
            }
        }

        // Convert checked modules to permission IDs
        function getPermissionIdsFromModules() {
            const permissionIds = [];
            Object.keys(availableModules).forEach(moduleKey => {
                const checkbox = document.getElementById(`perm-${moduleKey}`);
                if (checkbox && checkbox.checked) {
                    const modulePermIds = permissionModuleMap[moduleKey] || [];
                    permissionIds.push(...modulePermIds);
                }
            });
            return permissionIds;
        }

        async function submitRoleForm(event) {
            event.preventDefault();
            
            const form = document.getElementById('role-form');
            const formData = new FormData(form);
            
            const name = formData.get('name').trim();
            const description = formData.get('description').trim();
            
            // Get checked modules and convert to permission IDs
            const permissionIds = getPermissionIdsFromModules();
            
            // Check if at least one permission is selected
            if (permissionIds.length === 0) {
                await Tivora.alert('Please select at least one module permission!', 'warning');
                return;
            }
            
            if (!name) {
                await Tivora.alert('Role name is required!', 'warning');
                return;
            }
            
            try {
                const url = editingRoleId ? `${API_BASE}/roles.php?id=${editingRoleId}` : `${API_BASE}/roles.php`;
                const method = editingRoleId ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        name: name,
                        description: description,
                        permissions: permissionIds,
                        is_active: 1
                    })
                });
                
                const result = await response.json();
                
                if (!response.ok || !result.success) {
                    await Tivora.alert(result.message || 'Failed to save role', 'error');
                    return;
                }
                
                await Tivora.alert(editingRoleId ? 'Role updated successfully!' : 'Role created successfully!', 'success');
                closeRoleModal();
                loadRoles();
            } catch (error) {
                console.error('Error saving role:', error);
                await Tivora.alert('Error saving role. Please try again.', 'error');
            }
        }

        async function deleteRole(roleId) {
            const role = allRoles.find(r => r.id == roleId);
            
            if (!role) {
                await Tivora.alert('Role not found', 'error');
                return;
            }
            
            if (roleId == 1) {
                await Tivora.alert('Cannot delete Super Admin role!', 'error');
                return;
            }
            
            if (role.user_count > 0) {
                await Tivora.alert(`Cannot delete this role! ${role.user_count} user(s) are currently assigned to this role. Please reassign them first.`, 'warning');
                return;
            }
            
            if (!await Tivora.confirm(`Are you sure you want to delete the role "${role.name}"?`)) {
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE}/roles.php?id=${roleId}`, {
                    method: 'DELETE',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' }
                });
                
                const result = await response.json();
                
                if (!response.ok || !result.success) {
                    await Tivora.alert(result.message || 'Failed to delete role', 'error');
                    return;
                }
                
                await Tivora.alert('Role deleted successfully!', 'success');
                loadRoles();
            } catch (error) {
                console.error('Error deleting role:', error);
                await Tivora.alert('Error deleting role. Please try again.', 'error');
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
        document.addEventListener('DOMContentLoaded', async () => {
            await loadPermissions(); // Load permissions first
            await loadRoles(); // Then load roles
            initializePermissionsGrid(); // Initialize empty permissions grid
        });

        // Reinitialize icons
        setInterval(() => {
            lucide.createIcons();
        }, 1000);
    </script>
</body>
</html>
