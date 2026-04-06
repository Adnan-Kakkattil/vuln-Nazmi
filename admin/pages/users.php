<?php
/**
 * Users Page Content
 * User management page with create/edit/delete functionality
 */
?>
<!-- Users Table Card -->
<div class="stat-card">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-900">All Users</h2>
        <div class="flex items-center gap-3">
            <input 
                type="text" 
                id="search-users" 
                placeholder="Search users..." 
                class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
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

<!-- Create/Edit User Modal -->
<div class="modal-overlay" id="create-user-modal-overlay" onclick="closeCreateUserModal(event)">
    <div class="modal" onclick="event.stopPropagation()" style="max-width: 700px;">
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center">
                    <i data-lucide="user-plus" class="w-5 h-5 text-teal-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900" id="user-modal-title">Create New User</h2>
            </div>
            <button onclick="closeCreateUserModal()" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <form id="create-user-form" onsubmit="submitCreateUserForm(event)" class="space-y-6">
            <!-- First Name -->
            <div>
                <label for="user-first-name" class="block text-sm font-semibold text-gray-700 mb-2">
                    First Name <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="user-first-name" 
                    name="first_name" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors bg-white"
                    placeholder="Enter first name"
                >
                <p class="text-xs text-gray-500 mt-1.5 flex items-center gap-1">
                    <i data-lucide="info" class="w-3 h-3"></i>
                    Enter the user's first name
                </p>
            </div>

            <!-- Last Name -->
            <div>
                <label for="user-last-name" class="block text-sm font-semibold text-gray-700 mb-2">
                    Last Name <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="user-last-name" 
                    name="last_name" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors bg-white"
                    placeholder="Enter last name"
                >
            </div>

            <!-- Email -->
            <div>
                <label for="user-email" class="block text-sm font-semibold text-gray-700 mb-2">
                    Email Address <span class="text-red-500">*</span>
                </label>
                <input 
                    type="email" 
                    id="user-email" 
                    name="email" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors bg-white"
                    placeholder="user@example.com"
                >
                <p class="text-xs text-gray-500 mt-1.5 flex items-center gap-1">
                    <i data-lucide="info" class="w-3 h-3"></i>
                    This email will be used for login
                </p>
            </div>

            <!-- Password -->
            <div>
                <label for="user-password" class="block text-sm font-semibold text-gray-700 mb-2">
                    Password <span class="text-red-500" id="password-required-star">*</span>
                </label>
                <input 
                    type="password" 
                    id="user-password" 
                    name="password" 
                    minlength="8"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors bg-white"
                    placeholder="Enter password (minimum 8 characters)"
                >
                <p class="text-xs text-gray-500 mt-1.5" id="password-help-text">Password must be at least 8 characters. <span id="password-optional-text" class="hidden">Leave blank to keep current password.</span></p>
            </div>

            <!-- Confirm Password -->
            <div id="password-confirm-group">
                <label for="user-password-confirm" class="block text-sm font-semibold text-gray-700 mb-2">
                    Confirm Password <span class="text-red-500" id="password-confirm-required-star">*</span>
                </label>
                <input 
                    type="password" 
                    id="user-password-confirm" 
                    name="passwordConfirm" 
                    minlength="8"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors bg-white"
                    placeholder="Confirm password"
                >
            </div>

            <!-- Role Selection -->
            <div>
                <label for="user-role" class="block text-sm font-semibold text-gray-700 mb-2">
                    Role <span class="text-red-500">*</span>
                </label>
                <select 
                    id="user-role" 
                    name="role" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors bg-white"
                >
                    <option value="">Select a role</option>
                    <optgroup label="Roles" id="roles-optgroup"></optgroup>
                </select>
                <p class="text-xs text-gray-500 mt-1.5 flex items-center gap-1">
                    <i data-lucide="info" class="w-3 h-3"></i>
                    Select a role to automatically assign module permissions
                </p>
            </div>

            <!-- Status -->
            <div>
                <label for="user-status" class="block text-sm font-semibold text-gray-700 mb-2">
                    Status
                </label>
                <select 
                    id="user-status" 
                    name="status" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors bg-white"
                >
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <!-- Form Actions -->
            <div class="flex gap-3 mt-8 pt-6 border-t border-gray-200">
                <button 
                    type="submit"
                    class="flex-1 px-6 py-3 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-semibold shadow-md hover:shadow-lg flex items-center justify-center gap-2"
                    id="user-submit-button"
                >
                    <i data-lucide="user-plus" class="w-4 h-4" id="user-submit-icon"></i>
                    <span id="user-submit-text">Create User</span>
                </button>
                <button 
                    type="button"
                    onclick="closeCreateUserModal()"
                    class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-colors font-semibold"
                >
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Users page-specific JavaScript
    (function() {
        // Ensure API_BASE is defined
        if (typeof window.API_BASE === 'undefined') {
            window.API_BASE = '/api/v1/admin';
        }
        const API_BASE = window.API_BASE || '/api/v1/admin';
        
        // Global state
        let allRoles = [];
        let allUsers = [];
        let editingUserId = null;

        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initUsersPage);
        } else {
            initUsersPage();
        }

        function initUsersPage() {
            // Set up header action button
            const pageActions = document.getElementById('page-actions');
            if (pageActions) {
                pageActions.innerHTML = `
                    <button onclick="openCreateUserModal()" class="px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors flex items-center gap-2 text-sm font-medium">
                        <i data-lucide="user-plus"></i>
                        <span>Add User</span>
                    </button>
                `;
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }
            
            loadUsers();
            loadRolesIntoDropdown();
        }

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
                    // Filter out Customer role (role_id = 2) - only show admin roles
                    const activeRoles = result.data.filter(role => role.is_active == 1 && role.id != 2);
                    allRoles = activeRoles;
                    
                    if (activeRoles.length === 0) {
                        rolesOptgroup.innerHTML = '<option value="">No active admin roles available</option>';
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
        window.openCreateUserModal = function() {
            editingUserId = null;
            document.getElementById('user-modal-title').textContent = 'Create New User';
            document.getElementById('user-submit-text').textContent = 'Create User';
            document.getElementById('user-submit-icon').setAttribute('data-lucide', 'user-plus');
            
            document.getElementById('user-password').required = true;
            document.getElementById('user-password-confirm').required = true;
            document.getElementById('password-required-star').classList.remove('hidden');
            document.getElementById('password-confirm-required-star').classList.remove('hidden');
            document.getElementById('password-optional-text').classList.add('hidden');
            
            const overlay = document.getElementById('create-user-modal-overlay');
            loadRolesIntoDropdown();
            document.getElementById('create-user-form').reset();
            overlay.classList.add('show');
            
            setTimeout(() => {
                lucide.createIcons();
            }, 100);
        };

        window.closeCreateUserModal = function(event) {
            if (!event || event.target.id === 'create-user-modal-overlay') {
                const overlay = document.getElementById('create-user-modal-overlay');
                overlay.classList.remove('show');
                document.getElementById('create-user-form').reset();
                editingUserId = null;
            }
        };

        window.submitCreateUserForm = async function(event) {
            event.preventDefault();
            
            const form = document.getElementById('create-user-form');
            const formData = new FormData(form);
            
            const firstName = formData.get('first_name').trim();
            const lastName = formData.get('last_name').trim();
            const email = formData.get('email').trim().toLowerCase();
            const password = formData.get('password');
            const passwordConfirm = formData.get('passwordConfirm');
            const roleId = formData.get('role');
            const isActive = formData.get('status') || '1';
            
            if (!firstName || !lastName || !email) {
                await Tivora.alert('All required fields must be filled!', 'warning');
                return;
            }
            
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
        };

        window.editUser = async function(userId) {
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
                
                document.getElementById('user-modal-title').textContent = 'Edit User';
                document.getElementById('user-submit-text').textContent = 'Update User';
                document.getElementById('user-submit-icon').setAttribute('data-lucide', 'save');
                
                document.getElementById('user-password').required = false;
                document.getElementById('user-password-confirm').required = false;
                document.getElementById('password-required-star').classList.add('hidden');
                document.getElementById('password-confirm-required-star').classList.add('hidden');
                document.getElementById('password-optional-text').classList.remove('hidden');
                
                await loadRolesIntoDropdown();
                
                document.getElementById('user-first-name').value = user.first_name || '';
                document.getElementById('user-last-name').value = user.last_name || '';
                document.getElementById('user-email').value = user.email || '';
                document.getElementById('user-role').value = user.role_id || '';
                document.getElementById('user-status').value = user.is_active || 1;
                
                document.getElementById('user-password').value = '';
                document.getElementById('user-password-confirm').value = '';
                
                const overlay = document.getElementById('create-user-modal-overlay');
                overlay.classList.add('show');
                
                setTimeout(() => {
                    lucide.createIcons();
                }, 100);
            } catch (error) {
                console.error('Error loading user:', error);
                await Tivora.alert('Error loading user. Please try again.', 'error');
            }
        };

        window.deleteUser = async function(userId) {
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
        };
    })();
</script>
