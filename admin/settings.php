<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | NAZMI BOUTIQUE Admin</title>
    
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
            background: #f0fdfa;
            color: #14b8a6;
        }

        .admin-sidebar-item.active {
            background: #f0fdfa;
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

        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            width: 48px;
            height: 24px;
            background-color: #e5e7eb;
            border-radius: 12px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .toggle-switch.active {
            background-color: #14b8a6;
        }

        .toggle-switch::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            background-color: white;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            transition: transform 0.3s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .toggle-switch.active::after {
            transform: translateX(24px);
        }

        /* Settings Section */
        .settings-section {
            margin-bottom: 32px;
        }

        .settings-section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.125rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e5e7eb;
        }

        .settings-section-title i {
            width: 24px;
            height: 24px;
            color: #14b8a6;
        }

        .setting-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .setting-row:last-child {
            border-bottom: none;
        }

        .setting-info {
            flex: 1;
        }

        .setting-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 4px;
        }

        .setting-description {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .setting-control {
            flex-shrink: 0;
            margin-left: 24px;
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

            .setting-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .setting-control {
                margin-left: 0;
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

        /* Loading Spinner */
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="antialiased">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="admin-sidebar">
        <!-- Logo -->
        <div class="p-6 border-b border-gray-200">
            <a href="../index.php" class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-teal-600">NAZMI BOUTIQUE</h2>
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
            <a href="roles.php" class="admin-sidebar-item">
                <i data-lucide="shield"></i>
                <span>Roles</span>
            </a>
            <a href="api_integration.php" class="admin-sidebar-item">
                <i data-lucide="plug"></i>
                <span>API Integration</span>
            </a>
            <a href="settings.php" class="admin-sidebar-item active">
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
                <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
            </div>
            <button onclick="saveAllSettings()" id="save-btn" class="flex items-center gap-2 px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span id="save-btn-text">Save Changes</span>
                <div id="save-spinner" class="spinner hidden"></div>
            </button>
        </header>

        <!-- Content -->
        <div class="p-6 lg:p-8">
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
                <div class="card settings-section">
                    <h2 class="settings-section-title">
                        <i data-lucide="receipt"></i>
                        Tax Settings (GST)
                    </h2>
                    
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Enable GST Tax</div>
                            <div class="setting-description">Enable or disable GST tax calculation on orders</div>
                        </div>
                        <div class="setting-control">
                            <div class="toggle-switch" data-setting="tax_enabled" onclick="toggleSetting(this)"></div>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Tax Rate (%)</div>
                            <div class="setting-description">GST percentage to apply on orders</div>
                        </div>
                        <div class="setting-control">
                            <input type="number" id="tax_rate" data-setting="tax_rate" min="0" max="100" step="0.01"
                                class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-right"
                                onchange="markUnsaved()">
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Prices Include Tax</div>
                            <div class="setting-description">Are product prices already inclusive of tax</div>
                        </div>
                        <div class="setting-control">
                            <div class="toggle-switch" data-setting="tax_inclusive" onclick="toggleSetting(this)"></div>
                        </div>
                    </div>
                </div>

                <!-- Payment Settings -->
                <div class="card settings-section">
                    <h2 class="settings-section-title">
                        <i data-lucide="credit-card"></i>
                        Payment Methods
                    </h2>
                    
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Enable Online Payment (Razorpay)</div>
                            <div class="setting-description">Allow customers to pay online via Razorpay gateway</div>
                        </div>
                        <div class="setting-control">
                            <div class="toggle-switch" data-setting="payment_online_enabled" onclick="toggleSetting(this)"></div>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Razorpay API Key</div>
                            <div class="setting-description">Your Razorpay public API key</div>
                        </div>
                        <div class="setting-control">
                            <input type="text" id="razorpay_key" data-setting="razorpay_key" placeholder="rzp_live_xxxx"
                                class="w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                onchange="markUnsaved()">
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Razorpay Secret Key</div>
                            <div class="setting-description">Your Razorpay secret API key (kept secure)</div>
                        </div>
                        <div class="setting-control">
                            <input type="password" id="razorpay_secret" data-setting="razorpay_secret" placeholder="••••••••"
                                class="w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                onchange="markUnsaved()">
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Enable Cash on Delivery (COD)</div>
                            <div class="setting-description">Allow customers to pay cash upon delivery</div>
                        </div>
                        <div class="setting-control">
                            <div class="toggle-switch" data-setting="payment_cod_enabled" onclick="toggleSetting(this)"></div>
                        </div>
                    </div>
                </div>

                <!-- Authentication Settings -->
                <div class="card settings-section">
                    <h2 class="settings-section-title">
                        <i data-lucide="user-check"></i>
                        Authentication Settings
                    </h2>
                    
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Enable User Login</div>
                            <div class="setting-description">Allow existing users to log into their accounts</div>
                        </div>
                        <div class="setting-control">
                            <div class="toggle-switch" data-setting="auth_login_enabled" onclick="toggleSetting(this)"></div>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Enable User Registration</div>
                            <div class="setting-description">Allow new users to create accounts</div>
                        </div>
                        <div class="setting-control">
                            <div class="toggle-switch" data-setting="auth_signup_enabled" onclick="toggleSetting(this)"></div>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Enable Forgot Password</div>
                            <div class="setting-description">Allow users to reset their passwords via email</div>
                        </div>
                        <div class="setting-control">
                            <div class="toggle-switch" data-setting="auth_forgot_password_enabled" onclick="toggleSetting(this)"></div>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Enable Guest Checkout</div>
                            <div class="setting-description">Allow customers to checkout without creating an account</div>
                        </div>
                        <div class="setting-control">
                            <div class="toggle-switch" data-setting="auth_guest_checkout_enabled" onclick="toggleSetting(this)"></div>
                        </div>
                    </div>
                </div>

                <!-- Email/Notification Settings -->
                <div class="card settings-section">
                    <h2 class="settings-section-title">
                        <i data-lucide="mail"></i>
                        Email & Notifications
                    </h2>
                    
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Enable Email Service</div>
                            <div class="setting-description">Master switch to enable/disable all email functionality</div>
                        </div>
                        <div class="setting-control">
                            <div class="toggle-switch" data-setting="email_service_enabled" onclick="toggleSetting(this)"></div>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Enable Email Notifications</div>
                            <div class="setting-description">Send automated email notifications to customers</div>
                        </div>
                        <div class="setting-control">
                            <div class="toggle-switch" data-setting="email_notifications_enabled" onclick="toggleSetting(this)"></div>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Order Confirmation Emails</div>
                            <div class="setting-description">Send email when an order is placed</div>
                        </div>
                        <div class="setting-control">
                            <div class="toggle-switch" data-setting="email_order_confirmation" onclick="toggleSetting(this)"></div>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Shipping Update Emails</div>
                            <div class="setting-description">Send email when order status changes</div>
                        </div>
                        <div class="setting-control">
                            <div class="toggle-switch" data-setting="email_shipping_updates" onclick="toggleSetting(this)"></div>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">Welcome Email</div>
                            <div class="setting-description">Send welcome email when user registers</div>
                        </div>
                        <div class="setting-control">
                            <div class="toggle-switch" data-setting="email_welcome_email" onclick="toggleSetting(this)"></div>
                        </div>
                    </div>
                </div>

                <!-- SMTP Settings -->
                <div class="card settings-section">
                    <h2 class="settings-section-title">
                        <i data-lucide="server"></i>
                        SMTP Configuration
                    </h2>
                    
                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">SMTP Host</div>
                            <div class="setting-description">Email server hostname (e.g., smtp.gmail.com)</div>
                        </div>
                        <div class="setting-control">
                            <input type="text" id="smtp_host" data-setting="smtp_host" placeholder="smtp.example.com"
                                class="w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                onchange="markUnsaved()">
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">SMTP Port</div>
                            <div class="setting-description">Email server port (typically 587 for TLS, 465 for SSL)</div>
                        </div>
                        <div class="setting-control">
                            <input type="number" id="smtp_port" data-setting="smtp_port" placeholder="587"
                                class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-right"
                                onchange="markUnsaved()">
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">SMTP Username</div>
                            <div class="setting-description">Username for SMTP authentication</div>
                        </div>
                        <div class="setting-control">
                            <input type="text" id="smtp_username" data-setting="smtp_username" placeholder="your@email.com"
                                class="w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                onchange="markUnsaved()">
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">SMTP Password</div>
                            <div class="setting-description">Password for SMTP authentication</div>
                        </div>
                        <div class="setting-control">
                            <input type="password" id="smtp_password" data-setting="smtp_password" placeholder="••••••••"
                                class="w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                onchange="markUnsaved()">
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">SMTP Encryption</div>
                            <div class="setting-description">Encryption method (TLS or SSL)</div>
                        </div>
                        <div class="setting-control">
                            <select id="smtp_encryption" data-setting="smtp_encryption"
                                class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                onchange="markUnsaved()">
                                <option value="tls">TLS</option>
                                <option value="ssl">SSL</option>
                                <option value="none">None</option>
                            </select>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">From Name</div>
                            <div class="setting-description">Sender name for outgoing emails</div>
                        </div>
                        <div class="setting-control">
                            <input type="text" id="email_from_name" data-setting="email_from_name" placeholder="NAZMI BOUTIQUE"
                                class="w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                onchange="markUnsaved()">
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <div class="setting-label">From Email Address</div>
                            <div class="setting-description">Sender email address for outgoing emails</div>
                        </div>
                        <div class="setting-control">
                            <input type="email" id="email_from_address" data-setting="email_from_address" placeholder="noreply@example.com"
                                class="w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                onchange="markUnsaved()">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>


    <script>
        // API Base URL
        const API_BASE = '/api/v1/admin';
        
        // Store settings state
        let settings = {};
        let hasUnsavedChanges = false;

        // Initialize Lucide Icons
        lucide.createIcons();

        // Load settings on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadSettings();
        });

        // Load settings from API
        async function loadSettings() {
            try {
                const response = await fetch(`${API_BASE}/settings.php`);
                const result = await response.json();
                
                if (result.success) {
                    settings = result.data.settings;
                    applySettingsToUI();
                    document.getElementById('loading-state').classList.add('hidden');
                    document.getElementById('settings-content').classList.remove('hidden');
                } else {
                    await Tivora.alert('Failed to load settings: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Error loading settings:', error);
                await Tivora.alert('Failed to load settings', 'error');
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
                if (settings[key] !== undefined) {
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
            document.getElementById('save-btn').classList.add('ring-2', 'ring-yellow-400', 'ring-offset-2');
        }

        // Clear unsaved marker
        function clearUnsaved() {
            hasUnsavedChanges = false;
            document.getElementById('save-btn').classList.remove('ring-2', 'ring-yellow-400', 'ring-offset-2');
        }

        // Save all settings
        async function saveAllSettings() {
            const saveBtn = document.getElementById('save-btn');
            const saveBtnText = document.getElementById('save-btn-text');
            const saveSpinner = document.getElementById('save-spinner');

            // Show loading state
            saveBtnText.textContent = 'Saving...';
            saveSpinner.classList.remove('hidden');
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
                settingsToSave[key] = input.value;
            });

            try {
                const response = await fetch(`${API_BASE}/settings.php`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ settings: settingsToSave })
                });

                const result = await response.json();

                if (result.success) {
                    await Tivora.alert('Settings saved successfully!', 'success');
                    clearUnsaved();
                    // Update local settings state
                    Object.assign(settings, settingsToSave);
                } else {
                    await Tivora.alert('Failed to save settings: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Error saving settings:', error);
                await Tivora.alert('Failed to save settings', 'error');
            } finally {
                // Reset button state
                saveBtnText.textContent = 'Save Changes';
                saveSpinner.classList.add('hidden');
                saveBtn.disabled = false;
            }
        }


        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('mobile-menu-overlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        // Warn before leaving with unsaved changes
        window.addEventListener('beforeunload', (e) => {
            if (hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Reinitialize icons periodically
        setInterval(() => {
            lucide.createIcons();
        }, 1000);
    </script>
</body>
</html>
