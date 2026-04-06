<?php
/**
 * Settings Page Content
 * Manages system settings
 */
?>
<!-- Save Button (Fallback if not in header) -->
<div id="settings-save-button-container" class="mb-6 flex justify-end" style="display: none;">
    <button id="save-btn-fallback" onclick="if (typeof saveAllSettings === 'function') { saveAllSettings(); } else { alert('Function not available'); }" 
            class="flex items-center gap-2 px-6 py-3 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-semibold shadow-md hover:shadow-lg">
        <i data-lucide="save" class="w-5 h-5"></i>
        <span id="save-btn-text-fallback">Save Changes</span>
        <div id="save-spinner-fallback" class="hidden w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
    </button>
</div>

<!-- Loading State -->
<div id="loading-state" class="flex items-center justify-center py-20">
    <div class="text-center">
        <div class="w-12 h-12 border-4 border-teal-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <p class="text-gray-500">Loading settings...</p>
    </div>
</div>

<!-- Settings Content -->
<div id="settings-content" class="hidden">
    <!-- Tax Settings -->
    <div class="stat-card settings-section mb-6">
        <h2 class="settings-section-title flex items-center gap-3 text-lg font-semibold text-gray-900 mb-4 pb-3 border-b border-gray-200">
            <i data-lucide="receipt" class="w-5 h-5 text-teal-600"></i>
            Tax Settings (GST)
        </h2>
        
        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">Enable GST Tax</div>
                <div class="setting-description text-sm text-gray-500">Enable or disable GST tax calculation on orders</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <div class="toggle-switch relative w-12 h-6 bg-gray-200 rounded-full cursor-pointer transition-colors" data-setting="tax_enabled" onclick="toggleSetting(this)">
                    <div class="toggle-switch-handle absolute w-5 h-5 bg-white rounded-full top-0.5 left-0.5 transition-transform shadow-sm"></div>
                </div>
            </div>
        </div>

        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">Tax Rate (%)</div>
                <div class="setting-description text-sm text-gray-500">GST percentage to apply on orders</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <input type="number" id="tax_rate" data-setting="tax_rate" min="0" max="100" step="0.01"
                    class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-right"
                    onchange="markUnsaved()">
            </div>
        </div>

        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">Prices Include Tax</div>
                <div class="setting-description text-sm text-gray-500">Are product prices already inclusive of tax</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <div class="toggle-switch relative w-12 h-6 bg-gray-200 rounded-full cursor-pointer transition-colors" data-setting="tax_inclusive" onclick="toggleSetting(this)">
                    <div class="toggle-switch-handle absolute w-5 h-5 bg-white rounded-full top-0.5 left-0.5 transition-transform shadow-sm"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Settings -->
    <div class="stat-card settings-section mb-6">
        <h2 class="settings-section-title flex items-center gap-3 text-lg font-semibold text-gray-900 mb-4 pb-3 border-b border-gray-200">
            <i data-lucide="credit-card" class="w-5 h-5 text-teal-600"></i>
            Payment Methods
        </h2>
        
        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">Enable Online Payment (Razorpay)</div>
                <div class="setting-description text-sm text-gray-500">Allow customers to pay online via Razorpay gateway</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <div class="toggle-switch relative w-12 h-6 bg-gray-200 rounded-full cursor-pointer transition-colors" data-setting="payment_online_enabled" onclick="toggleSetting(this)">
                    <div class="toggle-switch-handle absolute w-5 h-5 bg-white rounded-full top-0.5 left-0.5 transition-transform shadow-sm"></div>
                </div>
            </div>
        </div>

        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">Razorpay API Key</div>
                <div class="setting-description text-sm text-gray-500">Your Razorpay public API key</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <input type="text" id="razorpay_key" data-setting="razorpay_key" placeholder="rzp_live_xxxx"
                    class="w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                    onchange="markUnsaved()">
            </div>
        </div>

        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">Razorpay Secret Key</div>
                <div class="setting-description text-sm text-gray-500">Your Razorpay secret API key (kept secure)</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <input type="password" id="razorpay_secret" data-setting="razorpay_secret" placeholder="••••••••"
                    class="w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                    onchange="markUnsaved()">
            </div>
        </div>

        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">Enable Cash on Delivery (COD)</div>
                <div class="setting-description text-sm text-gray-500">Allow customers to pay cash upon delivery</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <div class="toggle-switch relative w-12 h-6 bg-gray-200 rounded-full cursor-pointer transition-colors" data-setting="payment_cod_enabled" onclick="toggleSetting(this)">
                    <div class="toggle-switch-handle absolute w-5 h-5 bg-white rounded-full top-0.5 left-0.5 transition-transform shadow-sm"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Authentication Settings -->
    <div class="stat-card settings-section mb-6">
        <h2 class="settings-section-title flex items-center gap-3 text-lg font-semibold text-gray-900 mb-4 pb-3 border-b border-gray-200">
            <i data-lucide="user-check" class="w-5 h-5 text-teal-600"></i>
            Authentication Settings
        </h2>
        
        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">Enable User Login</div>
                <div class="setting-description text-sm text-gray-500">Allow existing users to log into their accounts</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <div class="toggle-switch relative w-12 h-6 bg-gray-200 rounded-full cursor-pointer transition-colors" data-setting="auth_login_enabled" onclick="toggleSetting(this)">
                    <div class="toggle-switch-handle absolute w-5 h-5 bg-white rounded-full top-0.5 left-0.5 transition-transform shadow-sm"></div>
                </div>
            </div>
        </div>

        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">Enable User Registration</div>
                <div class="setting-description text-sm text-gray-500">Allow new users to create accounts</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <div class="toggle-switch relative w-12 h-6 bg-gray-200 rounded-full cursor-pointer transition-colors" data-setting="auth_signup_enabled" onclick="toggleSetting(this)">
                    <div class="toggle-switch-handle absolute w-5 h-5 bg-white rounded-full top-0.5 left-0.5 transition-transform shadow-sm"></div>
                </div>
            </div>
        </div>

        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">Enable Forgot Password</div>
                <div class="setting-description text-sm text-gray-500">Allow users to reset their passwords via email</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <div class="toggle-switch relative w-12 h-6 bg-gray-200 rounded-full cursor-pointer transition-colors" data-setting="auth_forgot_password_enabled" onclick="toggleSetting(this)">
                    <div class="toggle-switch-handle absolute w-5 h-5 bg-white rounded-full top-0.5 left-0.5 transition-transform shadow-sm"></div>
                </div>
            </div>
        </div>

        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">Enable Guest Checkout</div>
                <div class="setting-description text-sm text-gray-500">Allow customers to checkout without creating an account</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <div class="toggle-switch relative w-12 h-6 bg-gray-200 rounded-full cursor-pointer transition-colors" data-setting="auth_guest_checkout_enabled" onclick="toggleSetting(this)">
                    <div class="toggle-switch-handle absolute w-5 h-5 bg-white rounded-full top-0.5 left-0.5 transition-transform shadow-sm"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Email/Notification Settings -->
    <div class="stat-card settings-section mb-6">
        <h2 class="settings-section-title flex items-center gap-3 text-lg font-semibold text-gray-900 mb-4 pb-3 border-b border-gray-200">
            <i data-lucide="mail" class="w-5 h-5 text-teal-600"></i>
            Email & Notifications
        </h2>
        
        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">Enable Email Service</div>
                <div class="setting-description text-sm text-gray-500">Master switch to enable/disable all email functionality</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <div class="toggle-switch relative w-12 h-6 bg-gray-200 rounded-full cursor-pointer transition-colors" data-setting="email_service_enabled" onclick="toggleSetting(this)">
                    <div class="toggle-switch-handle absolute w-5 h-5 bg-white rounded-full top-0.5 left-0.5 transition-transform shadow-sm"></div>
                </div>
            </div>
        </div>

        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">Enable Email Notifications</div>
                <div class="setting-description text-sm text-gray-500">Send automated email notifications to customers</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <div class="toggle-switch relative w-12 h-6 bg-gray-200 rounded-full cursor-pointer transition-colors" data-setting="email_notifications_enabled" onclick="toggleSetting(this)">
                    <div class="toggle-switch-handle absolute w-5 h-5 bg-white rounded-full top-0.5 left-0.5 transition-transform shadow-sm"></div>
                </div>
            </div>
        </div>

        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">Order Confirmation Emails</div>
                <div class="setting-description text-sm text-gray-500">Send email when an order is placed</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <div class="toggle-switch relative w-12 h-6 bg-gray-200 rounded-full cursor-pointer transition-colors" data-setting="email_order_confirmation" onclick="toggleSetting(this)">
                    <div class="toggle-switch-handle absolute w-5 h-5 bg-white rounded-full top-0.5 left-0.5 transition-transform shadow-sm"></div>
                </div>
            </div>
        </div>

        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">Shipping Update Emails</div>
                <div class="setting-description text-sm text-gray-500">Send email when order status changes</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <div class="toggle-switch relative w-12 h-6 bg-gray-200 rounded-full cursor-pointer transition-colors" data-setting="email_shipping_updates" onclick="toggleSetting(this)">
                    <div class="toggle-switch-handle absolute w-5 h-5 bg-white rounded-full top-0.5 left-0.5 transition-transform shadow-sm"></div>
                </div>
            </div>
        </div>

        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">Welcome Email</div>
                <div class="setting-description text-sm text-gray-500">Send welcome email when user registers</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <div class="toggle-switch relative w-12 h-6 bg-gray-200 rounded-full cursor-pointer transition-colors" data-setting="email_welcome_email" onclick="toggleSetting(this)">
                    <div class="toggle-switch-handle absolute w-5 h-5 bg-white rounded-full top-0.5 left-0.5 transition-transform shadow-sm"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- SMTP Settings -->
    <div class="stat-card settings-section mb-6">
        <h2 class="settings-section-title flex items-center gap-3 text-lg font-semibold text-gray-900 mb-4 pb-3 border-b border-gray-200">
            <i data-lucide="server" class="w-5 h-5 text-teal-600"></i>
            SMTP Configuration
        </h2>
        
        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">SMTP Host</div>
                <div class="setting-description text-sm text-gray-500">Email server hostname (e.g., smtp.gmail.com)</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <input type="text" id="smtp_host" data-setting="smtp_host" placeholder="smtp.example.com"
                    class="w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                    onchange="markUnsaved()">
            </div>
        </div>

        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">SMTP Port</div>
                <div class="setting-description text-sm text-gray-500">Email server port (typically 587 for TLS, 465 for SSL)</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <input type="number" id="smtp_port" data-setting="smtp_port" placeholder="587"
                    class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-right"
                    onchange="markUnsaved()">
            </div>
        </div>

        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">SMTP Username</div>
                <div class="setting-description text-sm text-gray-500">Username for SMTP authentication</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <input type="text" id="smtp_username" data-setting="smtp_username" placeholder="your@email.com"
                    class="w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                    onchange="markUnsaved()">
            </div>
        </div>

        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">SMTP Password</div>
                <div class="setting-description text-sm text-gray-500">Password for SMTP authentication</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <input type="password" id="smtp_password" data-setting="smtp_password" placeholder="••••••••"
                    class="w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                    onchange="markUnsaved()">
            </div>
        </div>

        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">SMTP Encryption</div>
                <div class="setting-description text-sm text-gray-500">Encryption method (TLS or SSL)</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <select id="smtp_encryption" data-setting="smtp_encryption"
                    class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                    onchange="markUnsaved()">
                    <option value="tls">TLS</option>
                    <option value="ssl">SSL</option>
                    <option value="none">None</option>
                </select>
            </div>
        </div>

        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">From Name</div>
                <div class="setting-description text-sm text-gray-500">Sender name for outgoing emails</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <input type="text" id="email_from_name" data-setting="email_from_name" placeholder="NAZMI BOUTIQUE"
                    class="w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                    onchange="markUnsaved()">
            </div>
        </div>

        <div class="setting-row flex justify-between items-center py-4 border-b border-gray-100 last:border-b-0">
            <div class="setting-info flex-1">
                <div class="setting-label font-medium text-gray-700 mb-1">From Email Address</div>
                <div class="setting-description text-sm text-gray-500">Sender email address for outgoing emails</div>
            </div>
            <div class="setting-control flex-shrink-0 ml-6">
                <input type="email" id="email_from_address" data-setting="email_from_address" placeholder="noreply@example.com"
                    class="w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                    onchange="markUnsaved()">
            </div>
        </div>
    </div>
</div>

<style>
    .toggle-switch.active {
        background-color: #14b8a6;
    }
    
    .toggle-switch.active .toggle-switch-handle {
        transform: translateX(24px);
    }
</style>

<script>
    // API Base URL
    const API_BASE = '/api/v1/admin';
    
    // Store settings state
    let settings = {};
    let hasUnsavedChanges = false;

    // Load settings on page load
    document.addEventListener('DOMContentLoaded', () => {
        loadSettings();
        
        // Check if save button exists in header, if not show fallback
        setTimeout(() => {
            const headerSaveBtn = document.getElementById('save-btn');
            const fallbackContainer = document.getElementById('settings-save-button-container');
            
            if (!headerSaveBtn && fallbackContainer) {
                // Header button not found, show fallback button
                fallbackContainer.style.display = 'flex';
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            } else if (headerSaveBtn) {
                // Header button exists, ensure it's visible
                headerSaveBtn.style.display = 'flex';
            }
        }, 200);
    });

    // Load settings from API
    async function loadSettings() {
        try {
            const response = await fetch(`${API_BASE}/settings.php`, {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            
            if (!response.ok) {
                throw new Error('Failed to load settings');
            }
            
            const result = await response.json();
            
            if (result.success) {
                settings = result.data.settings || {};
                applySettingsToUI();
                document.getElementById('loading-state').classList.add('hidden');
                document.getElementById('settings-content').classList.remove('hidden');
            } else {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Failed to load settings: ' + (result.message || 'Unknown error'), 'error');
                } else {
                    alert('Failed to load settings: ' + (result.message || 'Unknown error'));
                }
            }
        } catch (error) {
            console.error('Error loading settings:', error);
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Failed to load settings', 'error');
            } else {
                alert('Failed to load settings');
            }
        }
    }

    // Apply loaded settings to UI elements
    function applySettingsToUI() {
        // Handle toggle switches
        document.querySelectorAll('.toggle-switch').forEach(toggle => {
            const key = toggle.getAttribute('data-setting');
            if (settings[key] !== undefined) {
                if (settings[key] === true || settings[key] === '1' || settings[key] === 1) {
                    toggle.classList.add('active');
                } else {
                    toggle.classList.remove('active');
                }
            }
        });

        // Handle input fields
        document.querySelectorAll('input[data-setting], select[data-setting]').forEach(input => {
            const key = input.getAttribute('data-setting');
            if (settings[key] !== undefined && settings[key] !== null) {
                input.value = settings[key];
            }
        });
    }

    // Toggle setting switch
    function toggleSetting(element) {
        element.classList.toggle('active');
        const key = element.getAttribute('data-setting');
        settings[key] = element.classList.contains('active');
        markUnsaved();
    }

    // Mark as having unsaved changes
    function markUnsaved() {
        hasUnsavedChanges = true;
        const saveBtn = document.getElementById('save-btn') || document.getElementById('save-btn-fallback');
        if (saveBtn) {
            saveBtn.classList.add('ring-2', 'ring-yellow-400', 'ring-offset-2');
        }
    }

    // Clear unsaved marker
    function clearUnsaved() {
        hasUnsavedChanges = false;
        const saveBtn = document.getElementById('save-btn') || document.getElementById('save-btn-fallback');
        if (saveBtn) {
            saveBtn.classList.remove('ring-2', 'ring-yellow-400', 'ring-offset-2');
        }
    }

    // Save all settings
    async function saveAllSettings() {
        // Try header button first, then fallback button
        let saveBtn = document.getElementById('save-btn');
        let saveBtnText = document.getElementById('save-btn-text');
        let saveSpinner = document.getElementById('save-spinner');
        
        // If header button not found, use fallback
        if (!saveBtn || !saveBtnText) {
            saveBtn = document.getElementById('save-btn-fallback');
            saveBtnText = document.getElementById('save-btn-text-fallback');
            saveSpinner = document.getElementById('save-spinner-fallback');
        }

        if (!saveBtn || !saveBtnText) {
            console.error('Save button not found');
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Save button not found. Please refresh the page.', 'error');
            } else {
                alert('Save button not found. Please refresh the page.');
            }
            return;
        }

        // Show loading state
        saveBtnText.textContent = 'Saving...';
        if (saveSpinner) saveSpinner.classList.remove('hidden');
        saveBtn.disabled = true;

        // Collect all settings from UI
        const settingsToSave = {};

        // Get toggle values
        document.querySelectorAll('.toggle-switch').forEach(toggle => {
            const key = toggle.getAttribute('data-setting');
            settingsToSave[key] = toggle.classList.contains('active');
        });

        // Get input values
        document.querySelectorAll('input[data-setting], select[data-setting]').forEach(input => {
            const key = input.getAttribute('data-setting');
            const value = input.value;
            // Don't save empty password fields
            if (input.type === 'password' && !value) {
                return;
            }
            settingsToSave[key] = value;
        });

        try {
            const response = await fetch(`${API_BASE}/settings.php`, {
                method: 'PUT',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ settings: settingsToSave })
            });

            const result = await response.json();

            if (result.success) {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Settings saved successfully!', 'success');
                } else {
                    alert('Settings saved successfully!');
                }
                clearUnsaved();
                // Update local settings state
                Object.assign(settings, settingsToSave);
            } else {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Failed to save settings: ' + (result.message || 'Unknown error'), 'error');
                } else {
                    alert('Failed to save settings: ' + (result.message || 'Unknown error'));
                }
            }
        } catch (error) {
            console.error('Error saving settings:', error);
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('Failed to save settings', 'error');
            } else {
                alert('Failed to save settings');
            }
        } finally {
            // Reset button state
            saveBtnText.textContent = 'Save Changes';
            if (saveSpinner) saveSpinner.classList.add('hidden');
            saveBtn.disabled = false;
        }
    }

    // Make saveAllSettings globally accessible
    window.saveAllSettings = saveAllSettings;

    // Warn before leaving with unsaved changes
    window.addEventListener('beforeunload', (e) => {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
</script>
