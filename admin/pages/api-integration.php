<?php
/**
 * API Integration Page Content
 * Manages POS API keys and credentials
 */
?>
<!-- Create API Key Button (Fallback if not in header) -->
<div id="api-key-create-button-container" class="mb-6 flex justify-end" style="display: none;">
    <button onclick="if (typeof openCreateApiKeyModal === 'function') { openCreateApiKeyModal(); } else { alert('Function not available'); }" 
            class="flex items-center gap-2 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Create API Key</span>
    </button>
</div>

<!-- API Keys Table Card -->
<div class="stat-card">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-900">API Keys</h2>
        <div class="flex items-center gap-3">
            <input
                type="text"
                id="search-api-keys"
                placeholder="Search API keys..."
                class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                onkeyup="filterApiKeys()"
            >
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Key Name</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">API Key</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Tenant</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Requests</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Last Used</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Created</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody id="api-keys-table-body" class="divide-y divide-gray-200">
                <!-- API keys will be dynamically inserted here -->
            </tbody>
        </table>
        <div id="no-api-keys-message" class="text-center py-12 text-gray-500 hidden">
            <i data-lucide="plug" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
            <p class="text-lg font-medium mb-2">No API keys found</p>
            <p class="text-sm">Click "Create API Key" to create your first API key</p>
        </div>
    </div>
</div>

<!-- Create/Edit API Key Modal -->
<div class="modal-overlay" id="api-key-modal-overlay" onclick="closeApiKeyModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900" id="api-key-modal-title">Create New API Key</h2>
            <button onclick="closeApiKeyModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-lg">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <form id="api-key-form" onsubmit="submitApiKeyForm(event)" class="p-6 space-y-6">
            <!-- Key Name -->
            <div>
                <label for="key-name" class="block text-sm font-medium text-gray-700 mb-2">
                    Key Name <span class="text-teal-600">*</span>
                </label>
                <input
                    type="text"
                    id="key-name"
                    name="key_name"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none transition-all"
                    placeholder="e.g., NomadsCipher POS Main"
                >
            </div>

            <!-- Tenant Info -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="tenant-id" class="block text-sm font-medium text-gray-700 mb-2">
                        Tenant ID (Optional)
                    </label>
                    <input
                        type="text"
                        id="tenant-id"
                        name="tenant_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none transition-all"
                        placeholder="POS Tenant ID"
                    >
                </div>
                <div>
                    <label for="tenant-name" class="block text-sm font-medium text-gray-700 mb-2">
                        Tenant Name (Optional)
                    </label>
                    <input
                        type="text"
                        id="tenant-name"
                        name="tenant_name"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none transition-all"
                        placeholder="e.g., Tivora Store"
                    >
                </div>
            </div>

            <!-- Expiration -->
            <div>
                <label for="expires-at" class="block text-sm font-medium text-gray-700 mb-2">
                    Expiration Date (Optional)
                </label>
                <input
                    type="datetime-local"
                    id="expires-at"
                    name="expires_at"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none transition-all"
                >
            </div>

            <!-- Allowed IPs -->
            <div>
                <label for="allowed-ips" class="block text-sm font-medium text-gray-700 mb-2">
                    Allowed IP Addresses (Optional)
                </label>
                <input
                    type="text"
                    id="allowed-ips"
                    name="allowed_ips"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none transition-all"
                    placeholder="192.168.1.1, 10.0.0.1 (comma-separated)"
                >
                <p class="mt-1 text-xs text-gray-500">Leave empty to allow all IPs</p>
            </div>

            <!-- Scopes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    API Scopes
                </label>
                <div class="space-y-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="scopes[]" value="products" id="scope-products" checked>
                        <span>Products (Read/Write)</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="scopes[]" value="orders" id="scope-orders" checked>
                        <span>Orders (Read)</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="scopes[]" value="finance" id="scope-finance" checked>
                        <span>Finance (Read)</span>
                    </label>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Notes (Optional)
                </label>
                <textarea
                    id="notes"
                    name="notes"
                    rows="3"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none transition-all resize-none"
                    placeholder="Additional notes about this API key"
                ></textarea>
            </div>

            <!-- Active Toggle (Edit only) -->
            <div id="active-toggle-container" class="hidden flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Active</label>
                    <p class="text-xs text-gray-500">Enable or disable this API key</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="is-active" name="is_active" value="1" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-500"></div>
                </label>
            </div>

            <!-- Form Actions -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200">
                <button
                    type="submit"
                    class="flex-1 bg-teal-500 text-white py-3 px-6 rounded-lg font-semibold hover:bg-teal-600 transition-colors shadow-md shadow-teal-100 flex items-center justify-center gap-2"
                >
                    <i data-lucide="save" class="w-5 h-5"></i>
                    <span id="api-key-submit-text">Create API Key</span>
                </button>
                <button
                    type="button"
                    onclick="closeApiKeyModal()"
                    class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 hover:border-gray-400 transition-colors"
                >
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Credentials Display Modal -->
<div class="modal-overlay" id="credentials-modal-overlay" onclick="closeCredentialsModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-yellow-900">⚠️ Important: Save These Credentials</h2>
            <button onclick="closeCredentialsModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-lg">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="p-6 bg-yellow-50">
            <p class="text-sm text-yellow-800 mb-4">The secret will not be shown again. Copy and store it securely.</p>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client ID (API Key)</label>
                    <div class="flex items-center gap-2">
                        <input type="text" id="display-api-key" readonly
                               class="flex-1 px-4 py-2 bg-white border border-gray-300 rounded-lg font-mono text-sm">
                        <button onclick="copyToClipboard('display-api-key')" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg">
                            <i data-lucide="copy" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client Secret</label>
                    <div class="flex items-center gap-2">
                        <input type="text" id="display-api-secret" readonly
                               class="flex-1 px-4 py-2 bg-white border border-gray-300 rounded-lg font-mono text-sm">
                        <button onclick="copyToClipboard('display-api-secret')" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg">
                            <i data-lucide="copy" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <button onclick="closeCredentialsModal()" class="px-6 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600">
                    I've Saved These Credentials
                </button>
            </div>
        </div>
    </div>
</div>

<style>
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
</style>

<script>
    // API Base URL - use global if available (from dashboard.php), otherwise define
    // Since dashboard.php already declares const API_BASE, we just use it directly
    // If it doesn't exist, we'll define it in window scope
    if (typeof window.API_BASE === 'undefined' && typeof API_BASE === 'undefined') {
        window.API_BASE = '/api/v1/admin';
    }
    
    // Use the existing API_BASE or window.API_BASE
    const apiBase = (typeof API_BASE !== 'undefined') ? API_BASE : (window.API_BASE || '/api/v1/admin');

    // Global state
    let allApiKeys = [];
    let editingApiKeyId = null;

    // Load and render API keys from API
    async function loadApiKeys() {
        const tbody = document.getElementById('api-keys-table-body');
        const noKeysMsg = document.getElementById('no-api-keys-message');
        
        if (!tbody) return;
        
        try {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-gray-500">Loading API keys...</td></tr>';
            
            const response = await fetch(`${apiBase}/api-keys.php`, {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            
            if (!response.ok) {
                throw new Error('Failed to load API keys');
            }
            
            const result = await response.json();
            
            if (!result.success || !result.data || result.data.length === 0) {
                tbody.innerHTML = '';
                if (noKeysMsg) noKeysMsg.classList.remove('hidden');
                return;
            }
            
            allApiKeys = result.data;
            if (noKeysMsg) noKeysMsg.classList.add('hidden');
            
            tbody.innerHTML = result.data.map(key => {
                const isExpired = key.expires_at && new Date(key.expires_at) < new Date();
                const statusBadge = key.is_active 
                    ? '<span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">Active</span>'
                    : '<span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-full">Inactive</span>';
                const expiredBadge = isExpired 
                    ? '<span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-full ml-1">Expired</span>'
                    : '';
                
                return `
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 text-sm text-gray-900 font-medium">${escapeHtml(key.key_name)}</td>
                        <td class="py-3 px-4 text-sm">
                            <code class="text-gray-700 font-mono">${escapeHtml(key.api_key)}</code>
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-600">
                            ${key.tenant_name ? escapeHtml(key.tenant_name) : '—'}
                        </td>
                        <td class="py-3 px-4 text-sm">
                            ${statusBadge}${expiredBadge}
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-600">
                            ${numberFormat(key.request_count || 0)}
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-600">
                            ${key.last_used_at ? formatDate(key.last_used_at) : 'Never'}
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-500">${formatDate(key.created_at)}</td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <button onclick="editApiKey(${key.id})" class="text-blue-600 hover:text-blue-700 p-1" title="Edit">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </button>
                                <button onclick="deleteApiKey(${key.id})" class="text-red-600 hover:text-red-700 p-1" title="Delete">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
            
            lucide.createIcons();
        } catch (error) {
            console.error('Error loading API keys:', error);
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-teal-600">Error loading API keys. Please try again.</td></tr>';
            }
            if (noKeysMsg) noKeysMsg.classList.add('hidden');
        }
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function numberFormat(num) {
        return new Intl.NumberFormat().format(num);
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function filterApiKeys() {
        const searchTerm = (document.getElementById('search-api-keys')?.value || '').toLowerCase();
        const rows = document.querySelectorAll('#api-keys-table-body tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    }

    // Create API Key Modal Functions
    function openCreateApiKeyModal() {
        editingApiKeyId = null;
        const modalTitle = document.getElementById('api-key-modal-title');
        const submitText = document.getElementById('api-key-submit-text');
        const activeToggle = document.getElementById('active-toggle-container');
        const form = document.getElementById('api-key-form');
        
        if (modalTitle) modalTitle.textContent = 'Create New API Key';
        if (submitText) submitText.textContent = 'Create API Key';
        if (activeToggle) activeToggle.classList.add('hidden');
        if (form) form.reset();
        
        // Reset checkboxes
        document.getElementById('scope-products').checked = true;
        document.getElementById('scope-orders').checked = true;
        document.getElementById('scope-finance').checked = true;
        
        const overlay = document.getElementById('api-key-modal-overlay');
        if (overlay) overlay.classList.add('show');
    }

    async function editApiKey(apiKeyId) {
        try {
            const response = await fetch(`${apiBase}/api-keys.php?id=${apiKeyId}`, {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            
            if (!response.ok) {
                const result = await response.json();
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert(result.message || 'Failed to load API key details', 'error');
                } else {
                    alert(result.message || 'Failed to load API key details');
                }
                return;
            }
            
            const result = await response.json();
            
            if (!result.success || !result.data) {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('API key not found', 'error');
                } else {
                    alert('API key not found');
                }
                return;
            }
            
            const key = result.data;
            editingApiKeyId = apiKeyId;
            
            const modalTitle = document.getElementById('api-key-modal-title');
            const submitText = document.getElementById('api-key-submit-text');
            const activeToggle = document.getElementById('active-toggle-container');
            
            if (modalTitle) modalTitle.textContent = 'Edit API Key';
            if (submitText) submitText.textContent = 'Update API Key';
            if (activeToggle) activeToggle.classList.remove('hidden');
            
            // Fill form
            document.getElementById('key-name').value = key.key_name || '';
            document.getElementById('tenant-id').value = key.tenant_id || '';
            document.getElementById('tenant-name').value = key.tenant_name || '';
            document.getElementById('allowed-ips').value = key.allowed_ips || '';
            document.getElementById('notes').value = key.notes || '';
            document.getElementById('is-active').checked = key.is_active == 1;
            
            if (key.expires_at) {
                const date = new Date(key.expires_at);
                const localDate = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
                document.getElementById('expires-at').value = localDate.toISOString().slice(0, 16);
            }
            
            // Set scopes
            const scopes = key.scopes || [];
            document.getElementById('scope-products').checked = scopes.includes('products');
            document.getElementById('scope-orders').checked = scopes.includes('orders');
            document.getElementById('scope-finance').checked = scopes.includes('finance');
            
            const overlay = document.getElementById('api-key-modal-overlay');
            if (overlay) overlay.classList.add('show');
        } catch (error) {
            console.error('Error loading API key:', error);
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Error loading API key. Please try again.', 'error');
            } else {
                alert('Error loading API key. Please try again.');
            }
        }
    }

    function closeApiKeyModal(event) {
        if (!event || event.target.id === 'api-key-modal-overlay') {
            const overlay = document.getElementById('api-key-modal-overlay');
            if (overlay) overlay.classList.remove('show');
            
            const form = document.getElementById('api-key-form');
            if (form) form.reset();
            editingApiKeyId = null;
        }
    }

    async function submitApiKeyForm(event) {
        event.preventDefault();
        
        const form = document.getElementById('api-key-form');
        if (!form) return;
        
        const formData = new FormData(form);
        const scopes = Array.from(formData.getAll('scopes[]'));
        
        const data = {
            key_name: formData.get('key_name')?.trim() || '',
            tenant_id: formData.get('tenant_id')?.trim() || null,
            tenant_name: formData.get('tenant_name')?.trim() || null,
            expires_at: formData.get('expires_at') || null,
            allowed_ips: formData.get('allowed_ips')?.trim() || null,
            scopes: scopes,
            notes: formData.get('notes')?.trim() || null
        };
        
        if (editingApiKeyId) {
            data.is_active = document.getElementById('is-active').checked ? 1 : 0;
        }
        
        if (!data.key_name) {
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Key name is required!', 'warning');
            } else {
                alert('Key name is required!');
            }
            return;
        }
        
        try {
            const url = editingApiKeyId ? `${apiBase}/api-keys.php?id=${editingApiKeyId}` : `${apiBase}/api-keys.php`;
            const method = editingApiKeyId ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (!response.ok || !result.success) {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert(result.message || 'Failed to save API key', 'error');
                } else {
                    alert(result.message || 'Failed to save API key');
                }
                return;
            }
            
            // If new API key created, show credentials
            if (!editingApiKeyId && result.data && result.data.api_secret) {
                showCredentials(result.data.api_key, result.data.api_secret);
            } else {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert(editingApiKeyId ? 'API key updated successfully!' : 'API key created successfully!', 'success');
                } else {
                    alert(editingApiKeyId ? 'API key updated successfully!' : 'API key created successfully!');
                }
            }
            
            closeApiKeyModal();
            await loadApiKeys();
        } catch (error) {
            console.error('Error saving API key:', error);
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Error saving API key. Please try again.', 'error');
            } else {
                alert('Error saving API key. Please try again.');
            }
        }
    }

    function showCredentials(apiKey, apiSecret) {
        document.getElementById('display-api-key').value = apiKey;
        document.getElementById('display-api-secret').value = apiSecret;
        const overlay = document.getElementById('credentials-modal-overlay');
        if (overlay) overlay.classList.add('show');
    }

    function closeCredentialsModal(event) {
        if (!event || event.target.id === 'credentials-modal-overlay') {
            const overlay = document.getElementById('credentials-modal-overlay');
            if (overlay) overlay.classList.remove('show');
        }
    }

    async function deleteApiKey(apiKeyId) {
        const key = allApiKeys.find(k => k.id == apiKeyId);
        
        if (!key) {
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('API key not found', 'error');
            } else {
                alert('API key not found');
            }
            return;
        }
        
        if (typeof Tivora !== 'undefined' && Tivora.confirm) {
            if (!await Tivora.confirm(`Are you sure you want to delete the API key "${key.key_name}"?`)) {
                return;
            }
        } else {
            if (!confirm(`Are you sure you want to delete the API key "${key.key_name}"?`)) {
                return;
            }
        }
        
        try {
            const response = await fetch(`${apiBase}/api-keys.php?id=${apiKeyId}`, {
                method: 'DELETE',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            
            const result = await response.json();
            
            if (!response.ok || !result.success) {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert(result.message || 'Failed to delete API key', 'error');
                } else {
                    alert(result.message || 'Failed to delete API key');
                }
                return;
            }
            
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('API key deleted successfully!', 'success');
            } else {
                alert('API key deleted successfully!');
            }
            await loadApiKeys();
        } catch (error) {
            console.error('Error deleting API key:', error);
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Error deleting API key. Please try again.', 'error');
            } else {
                alert('Error deleting API key. Please try again.');
            }
        }
    }

    function copyToClipboard(elementId) {
        const element = document.getElementById(elementId);
        element.select();
        element.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(element.value);
        
        const button = event.target.closest('button');
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i data-lucide="check"></i>';
        lucide.createIcons();
        
        setTimeout(() => {
            button.innerHTML = originalHTML;
            lucide.createIcons();
        }, 2000);
    }

    // Initialize on page load
    (function() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initApiIntegration);
        } else {
            initApiIntegration();
        }

        function initApiIntegration() {
            loadApiKeys();
            
            // Check if Create API Key button exists in header, if not show fallback
            setTimeout(() => {
                const headerCreateBtn = document.querySelector('#page-actions [onclick*="openCreateApiKeyModal"]');
                const fallbackContainer = document.getElementById('api-key-create-button-container');
                
                if (!headerCreateBtn && fallbackContainer) {
                    // Header button not found, show fallback button
                    fallbackContainer.style.display = 'flex';
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                } else if (headerCreateBtn) {
                    // Header button exists, ensure it's visible
                    headerCreateBtn.style.display = 'flex';
                }
            }, 300);
        }
    })();

    // Make openCreateApiKeyModal globally accessible
    window.openCreateApiKeyModal = openCreateApiKeyModal;
</script>
