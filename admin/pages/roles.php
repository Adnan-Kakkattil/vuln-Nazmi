<?php
/**
 * Roles Page Content
 * Manages roles and permissions
 */
?>
<!-- Roles Table Card -->
<div class="stat-card">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-900">All Roles</h2>
        <div class="flex items-center gap-3">
            <button onclick="openCreateRoleModal()" class="flex items-center gap-2 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-medium">
                <i data-lucide="shield-plus" class="w-4 h-4"></i>
                <span>Create Role</span>
            </button>
            <input
                type="text"
                id="search-roles"
                placeholder="Search roles..."
                class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
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

<!-- Create/Edit Role Modal -->
<div class="modal-overlay" id="role-modal-overlay" onclick="closeRoleModal(event)">
    <div class="modal" onclick="event.stopPropagation()" style="max-width: 750px;">
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center">
                    <i data-lucide="shield-plus" class="w-5 h-5 text-teal-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900" id="role-modal-title">Create New Role</h2>
            </div>
            <button onclick="closeRoleModal()" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <form id="role-form" onsubmit="submitRoleForm(event)" class="space-y-6">
            <!-- Role Name -->
            <div>
                <label for="role-name" class="block text-sm font-semibold text-gray-700 mb-2">
                    Role Name <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="role-name"
                    name="name"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors bg-white"
                    placeholder="e.g., Sales Manager, Inventory Admin"
                >
                <p class="text-xs text-gray-500 mt-1.5 flex items-center gap-1">
                    <i data-lucide="info" class="w-3 h-3"></i>
                    Enter a unique name for this role
                </p>
            </div>

            <!-- Description -->
            <div>
                <label for="role-description" class="block text-sm font-semibold text-gray-700 mb-2">
                    Description
                </label>
                <textarea
                    id="role-description"
                    name="description"
                    rows="3"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors resize-none bg-white"
                    placeholder="Brief description of this role's responsibilities"
                ></textarea>
                <p class="text-xs text-gray-500 mt-1.5">Optional description of the role's purpose</p>
            </div>

            <!-- Module Permissions -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Module Access Permissions <span class="text-red-500">*</span>
                </label>
                <p class="text-xs text-gray-500 mb-4 flex items-center gap-1">
                    <i data-lucide="info" class="w-3 h-3"></i>
                    Select which modules users with this role can access in the admin panel
                </p>
                <div class="permission-grid" id="permissions-grid">
                    <!-- Permissions will be dynamically inserted here -->
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex gap-3 mt-8 pt-6 border-t border-gray-200">
                <button
                    type="submit"
                    class="flex-1 px-6 py-3 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-semibold shadow-md hover:shadow-lg flex items-center justify-center gap-2"
                >
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span id="role-submit-text">Create Role</span>
                </button>
                <button
                    type="button"
                    onclick="closeRoleModal()"
                    class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-colors font-semibold"
                >
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Enhanced Role Modal Styles */
    #role-modal-overlay .modal {
        padding: 32px;
    }
    
    #role-modal-overlay input[type="text"],
    #role-modal-overlay textarea {
        font-size: 14px;
    }
    
    #role-modal-overlay input:focus,
    #role-modal-overlay textarea:focus {
        box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
    }
    
    #role-modal-overlay label {
        user-select: none;
    }
    
    /* Permission Checkbox Grid */
    .permission-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 12px;
    }

    .permission-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        transition: all 0.2s ease;
        background: white;
        cursor: pointer;
        position: relative;
    }

    .permission-item:hover {
        background: #f9fafb;
        border-color: #14b8a6;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }

    .permission-item input[type="checkbox"]:checked + label {
        color: #14b8a6;
        font-weight: 600;
    }

    .permission-item:has(input[type="checkbox"]:checked) {
        background: #f0fdfa;
        border-color: #14b8a6;
        box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
    }

    .permission-item input[type="checkbox"] {
        width: 20px;
        height: 20px;
        accent-color: #14b8a6;
        cursor: pointer;
        flex-shrink: 0;
    }

    .permission-item label {
        cursor: pointer;
        flex: 1;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .permission-item label i {
        flex-shrink: 0;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .permission-grid {
            grid-template-columns: 1fr;
        }
        
        #role-modal-overlay .modal {
            padding: 24px;
        }
    }
</style>

<script>
    // API Base URL
    const API_BASE = '/api/v1/admin';

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
        
        if (!tbody) return;
        
        try {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">Loading roles...</td></tr>';
            
            const response = await fetch(`${API_BASE}/roles.php`, {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.message || 'Failed to load roles');
            }
            
            if (!result.success || !result.data || result.data.length === 0) {
                tbody.innerHTML = '';
                if (noRolesMsg) noRolesMsg.classList.remove('hidden');
                return;
            }
            
            allRoles = result.data;
            if (noRolesMsg) noRolesMsg.classList.add('hidden');
            
            tbody.innerHTML = result.data.map(role => {
                const userCount = role.user_count || 0;
                
                // Get enabled modules from permissions count (simplified)
                const hasPermissions = (role.permission_count || 0) > 0;
                const moduleBadges = hasPermissions ? 
                    '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">Has Access</span>' :
                    '<span class="text-gray-400 text-xs">No modules</span>';
                
                return `
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 text-sm text-gray-900 font-medium">${escapeHtml(role.name)}</td>
                        <td class="py-3 px-4 text-sm text-gray-700">${escapeHtml(role.description || 'No description')}</td>
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
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-teal-600">Error loading roles. Please try again.</td></tr>';
            }
            if (noRolesMsg) noRolesMsg.classList.add('hidden');
        }
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function filterRoles() {
        const searchTerm = (document.getElementById('search-roles')?.value || '').toLowerCase();
        const rows = document.querySelectorAll('#roles-table-body tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    }

    // Initialize permissions grid
    function initializePermissionsGrid(permissions = {}) {
        const grid = document.getElementById('permissions-grid');
        if (!grid) return;
        
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
                        <i data-lucide="${module.icon}" class="w-4 h-4"></i>
                        <span>${module.label}</span>
                    </label>
                </div>
            `;
        }).join('');
        
        // Initialize icons after rendering
        setTimeout(() => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }, 50);
    }

    // Create Role Modal Functions
    function openCreateRoleModal() {
        editingRoleId = null;
        const modalTitle = document.getElementById('role-modal-title');
        const roleSubmitText = document.getElementById('role-submit-text');
        const roleForm = document.getElementById('role-form');
        
        if (modalTitle) modalTitle.textContent = 'Create New Role';
        if (roleSubmitText) roleSubmitText.textContent = 'Create Role';
        if (roleForm) roleForm.reset();
        initializePermissionsGrid();
        
        const overlay = document.getElementById('role-modal-overlay');
        if (overlay) {
            overlay.classList.add('show');
            // Focus on role name input
            setTimeout(() => {
                const roleNameInput = document.getElementById('role-name');
                if (roleNameInput) roleNameInput.focus();
            }, 100);
        }
        
        // Initialize icons after opening modal
        setTimeout(() => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }, 100);
    }

    async function editRole(roleId) {
        try {
            const response = await fetch(`${API_BASE}/roles.php?id=${roleId}`, {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();
            
            if (!response.ok || !result.success) {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert(result.message || 'Failed to load role details', 'error');
                } else {
                    alert(result.message || 'Failed to load role details');
                }
                return;
            }
            
            if (!result.success || !result.data) {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Role not found', 'error');
                } else {
                    alert('Role not found');
                }
                return;
            }
            
            const role = result.data;
            editingRoleId = roleId;
            
            const modalTitle = document.getElementById('role-modal-title');
            const roleSubmitText = document.getElementById('role-submit-text');
            const roleName = document.getElementById('role-name');
            const roleDescription = document.getElementById('role-description');
            
            if (modalTitle) modalTitle.textContent = 'Edit Role';
            if (roleSubmitText) roleSubmitText.textContent = 'Update Role';
            if (roleName) roleName.value = role.name;
            if (roleDescription) roleDescription.value = role.description || '';
            
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
            if (overlay) overlay.classList.add('show');
        } catch (error) {
            console.error('Error loading role:', error);
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Error loading role. Please try again.', 'error');
            } else {
                alert('Error loading role. Please try again.');
            }
        }
    }

    function closeRoleModal(event) {
        if (event && event.target !== event.currentTarget && !event.target.closest('.modal-content')) {
            return;
        }
        const overlay = document.getElementById('role-modal-overlay');
        if (overlay) {
            overlay.classList.remove('show');
            
            // Reset form
            const roleForm = document.getElementById('role-form');
            if (roleForm) roleForm.reset();
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
            } else {
                // If permissions API doesn't exist or fails, use module names as permission identifiers
                console.warn('Permissions API not available, using module-based permissions');
                Object.keys(availableModules).forEach(moduleKey => {
                    // Use module key as a fallback permission ID
                    permissionModuleMap[moduleKey] = [moduleKey];
                });
            }
        } catch (error) {
            console.error('Error loading permissions:', error);
            // Fallback: use module names as permission identifiers
            Object.keys(availableModules).forEach(moduleKey => {
                permissionModuleMap[moduleKey] = [moduleKey];
            });
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
        
        const roleForm = document.getElementById('role-form');
        if (!roleForm) return;
        
        const formData = new FormData(roleForm);
        
        const name = formData.get('name')?.trim() || '';
        const description = formData.get('description')?.trim() || '';
        
        // Get checked modules and convert to permission IDs
        const permissionIds = getPermissionIdsFromModules();
        
        // Check if at least one permission is selected
        if (permissionIds.length === 0) {
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Please select at least one module permission!', 'warning');
            } else {
                alert('Please select at least one module permission!');
            }
            return;
        }
        
        if (!name) {
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Role name is required!', 'warning');
            } else {
                alert('Role name is required!');
            }
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
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert(result.message || 'Failed to save role', 'error');
                } else {
                    alert(result.message || 'Failed to save role');
                }
                return;
            }
            
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert(editingRoleId ? 'Role updated successfully!' : 'Role created successfully!', 'success');
            } else {
                alert(editingRoleId ? 'Role updated successfully!' : 'Role created successfully!');
            }
            closeRoleModal();
            await loadRoles();
        } catch (error) {
            console.error('Error saving role:', error);
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Error saving role. Please try again.', 'error');
            } else {
                alert('Error saving role. Please try again.');
            }
        }
    }

    async function deleteRole(roleId) {
        const role = allRoles.find(r => r.id == roleId);
        
        if (!role) {
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Role not found', 'error');
            } else {
                alert('Role not found');
            }
            return;
        }
        
        if (roleId == 1) {
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Cannot delete Super Admin role!', 'error');
            } else {
                alert('Cannot delete Super Admin role!');
            }
            return;
        }
        
        if (role.user_count > 0) {
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert(`Cannot delete this role! ${role.user_count} user(s) are currently assigned to this role. Please reassign them first.`, 'warning');
            } else {
                alert(`Cannot delete this role! ${role.user_count} user(s) are currently assigned to this role. Please reassign them first.`);
            }
            return;
        }
        
        if (typeof Tivora !== 'undefined' && Tivora.confirm) {
            if (!await Tivora.confirm(`Are you sure you want to delete the role "${role.name}"?`)) {
                return;
            }
        } else {
            if (!confirm(`Are you sure you want to delete the role "${role.name}"?`)) {
                return;
            }
        }
        
        try {
            const response = await fetch(`${API_BASE}/roles.php?id=${roleId}`, {
                method: 'DELETE',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            
            const result = await response.json();
            
            if (!response.ok || !result.success) {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert(result.message || 'Failed to delete role', 'error');
                } else {
                    alert(result.message || 'Failed to delete role');
                }
                return;
            }
            
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Role deleted successfully!', 'success');
            } else {
                alert('Role deleted successfully!');
            }
            await loadRoles();
        } catch (error) {
            console.error('Error deleting role:', error);
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Error deleting role. Please try again.', 'error');
            } else {
                alert('Error deleting role. Please try again.');
            }
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', async () => {
        // Initialize icons first
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        
        await loadPermissions(); // Load permissions first
        await loadRoles(); // Then load roles
        initializePermissionsGrid(); // Initialize empty permissions grid
        
        // Re-initialize icons periodically for dynamic content
        setInterval(() => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }, 1000);
        
        // Add Escape key listener to close modal
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modal = document.getElementById('role-modal-overlay');
                if (modal && modal.classList.contains('show')) {
                    closeRoleModal();
                }
            }
        });
    });

    // Make openCreateRoleModal globally accessible
    window.openCreateRoleModal = openCreateRoleModal;
</script>
