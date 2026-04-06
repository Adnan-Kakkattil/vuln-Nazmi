<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B2B Requests | BLine Boutique Admin</title>
    
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
            max-width: 800px;
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
            <a href="requests.php" class="admin-sidebar-item active">
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
                <h1 class="text-2xl font-bold text-gray-900">B2B Requests</h1>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-sm text-gray-600">
                    <span id="request-count">0</span> requests
                </div>
                <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center">
                    <i data-lucide="inbox" class="w-5 h-5 text-teal-600"></i>
                </div>
            </div>
        </header>

        <!-- Requests Content -->
        <div class="p-6 lg:p-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm text-gray-500">Total Requests</h3>
                        <i data-lucide="inbox" class="w-5 h-5 text-gray-400"></i>
                    </div>
                    <p class="text-2xl font-bold text-gray-900" id="total-requests">0</p>
                </div>
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm text-gray-500">Pending</h3>
                        <i data-lucide="clock" class="w-5 h-5 text-yellow-400"></i>
                    </div>
                    <p class="text-2xl font-bold text-yellow-600" id="pending-requests">0</p>
                </div>
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm text-gray-500">Approved</h3>
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-400"></i>
                    </div>
                    <p class="text-2xl font-bold text-green-600" id="approved-requests">0</p>
                </div>
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm text-gray-500">Rejected</h3>
                        <i data-lucide="x-circle" class="w-5 h-5 text-red-400"></i>
                    </div>
                    <p class="text-2xl font-bold text-teal-600" id="rejected-requests">0</p>
                </div>
            </div>

            <!-- Requests Table Card -->
            <div class="stat-card">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">All Requests</h2>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <select 
                            id="status-filter" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                            onchange="filterRequests()"
                        >
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <input 
                            type="text" 
                            id="search-requests" 
                            placeholder="Search requests..." 
                            class="flex-1 sm:flex-none px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                            onkeyup="filterRequests()"
                        >
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Date</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Name</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Company</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Email</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Phone</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="requests-table-body" class="divide-y divide-gray-200">
                            <!-- Requests will be dynamically inserted here -->
                        </tbody>
                    </table>
                    <div id="no-requests-message" class="text-center py-12 text-gray-500 hidden">
                        <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                        <p class="text-lg font-medium mb-2">No requests found</p>
                        <p class="text-sm">B2B requests will appear here when submitted from the website</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Request Details Modal -->
    <div class="modal-overlay" id="request-modal-overlay" onclick="closeRequestModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">Request Details</h2>
                <button onclick="closeRequestModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-lg">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="p-6" id="request-modal-content">
                <!-- Request details will be inserted here -->
            </div>
        </div>
    </div>

    <script>
        // B2B requests data loaded from API
        let requests = [];
        
        // API base URL
        const API_BASE = '/api/v1/admin';

        // Initialize Lucide Icons
        lucide.createIcons();

        // Load requests on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadRequests();
        });

        // Load and render requests from API
        async function loadRequests() {
            try {
                const response = await fetch(`${API_BASE}/b2b-requests.php`);
                const result = await response.json();
                
                if (result.success) {
                    requests = result.data || [];
                    renderRequests();
                    updateStats();
                } else {
                    console.error('Failed to load requests:', result.message);
                }
            } catch (error) {
                console.error('Error loading requests:', error);
            }
        }

        // Render requests table
        function renderRequests(filteredRequests = null) {
            const tbody = document.getElementById('requests-table-body');
            const noRequestsMsg = document.getElementById('no-requests-message');
            const requestsToRender = filteredRequests || requests;
            
            tbody.innerHTML = '';
            
            if (requestsToRender.length === 0) {
                noRequestsMsg.classList.remove('hidden');
                return;
            }
            
            noRequestsMsg.classList.add('hidden');
            
            // Sort by date (newest first)
            const sortedRequests = [...requestsToRender].sort((a, b) => {
                return new Date(b.createdAt || b.date) - new Date(a.createdAt || a.date);
            });

            tbody.innerHTML = sortedRequests.map((request, index) => {
                const requestId = request.id;
                const status = request.status || 'pending';
                return `
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 text-sm text-gray-900">${formatDate(request.createdAt || request.date)}</td>
                        <td class="py-3 px-4 text-sm text-gray-900 font-medium">${request.name || request.contact_person || 'N/A'}</td>
                        <td class="py-3 px-4 text-sm text-gray-700">${request.company || request.company_name || 'N/A'}</td>
                        <td class="py-3 px-4 text-sm text-gray-700">${request.email || 'N/A'}</td>
                        <td class="py-3 px-4 text-sm text-gray-700">${request.phone || 'N/A'}</td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusBadgeColor(status)}">
                                ${status.charAt(0).toUpperCase() + status.slice(1)}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <button onclick="viewRequestDetails(${requestId})" class="text-blue-600 hover:text-blue-700 p-1" title="View Details">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                                <select 
                                    onchange="updateRequestStatus(${requestId}, this.value)" 
                                    class="text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-red-500"
                                    value="${status}"
                                >
                                    <option value="pending" ${status === 'pending' ? 'selected' : ''}>Pending</option>
                                    <option value="approved" ${status === 'approved' ? 'selected' : ''}>Approved</option>
                                    <option value="rejected" ${status === 'rejected' ? 'selected' : ''}>Rejected</option>
                                </select>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
            
            lucide.createIcons();
        }

        // Update stats
        function updateStats() {
            const total = requests.length;
            const pending = requests.filter(r => !r.status || r.status === 'pending').length;
            const approved = requests.filter(r => r.status === 'approved').length;
            const rejected = requests.filter(r => r.status === 'rejected').length;

            document.getElementById('total-requests').textContent = total;
            document.getElementById('pending-requests').textContent = pending;
            document.getElementById('approved-requests').textContent = approved;
            document.getElementById('rejected-requests').textContent = rejected;
            document.getElementById('request-count').textContent = total;
        }

        function getStatusBadgeColor(status) {
            const colors = {
                'pending': 'bg-yellow-100 text-yellow-800',
                'approved': 'bg-green-100 text-green-800',
                'rejected': 'bg-teal-100 text-red-800'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function filterRequests() {
            const statusFilter = document.getElementById('status-filter').value;
            const searchTerm = document.getElementById('search-requests').value.toLowerCase();
            
            // Client-side filtering
            let filtered = requests.filter(request => {
                const matchesSearch = !searchTerm || 
                    (request.name || request.contact_person || '').toLowerCase().includes(searchTerm) ||
                    (request.company || request.company_name || '').toLowerCase().includes(searchTerm) ||
                    (request.email || '').toLowerCase().includes(searchTerm) ||
                    (request.phone || '').toLowerCase().includes(searchTerm);
                
                const matchesStatus = !statusFilter || request.status === statusFilter;
                
                return matchesSearch && matchesStatus;
            });
            
            renderRequests(filtered);
            updateStatsForFiltered(filtered);
        }

        function updateStatsForFiltered(filteredRequests) {
            const pending = filteredRequests.filter(r => !r.status || r.status === 'pending').length;
            const approved = filteredRequests.filter(r => r.status === 'approved').length;
            const rejected = filteredRequests.filter(r => r.status === 'rejected').length;

            document.getElementById('pending-requests').textContent = pending;
            document.getElementById('approved-requests').textContent = approved;
            document.getElementById('rejected-requests').textContent = rejected;
        }

        function viewRequestDetails(requestId) {
            const request = requests.find(r => r.id === requestId);
            
            if (!request) return;

            const modalContent = document.getElementById('request-modal-content');
            modalContent.innerHTML = `
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <p class="text-base text-gray-900">${request.name || request.contact_person || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                            <p class="text-base text-gray-900">${request.company || request.company_name || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <p class="text-base text-gray-900">
                                <a href="mailto:${request.email}" class="text-blue-600 hover:underline">${request.email || 'N/A'}</a>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <p class="text-base text-gray-900">
                                <a href="tel:${request.phone}" class="text-blue-600 hover:underline">${request.phone || 'N/A'}</a>
                            </p>
                        </div>
                        ${request.business_type ? `
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Business Type</label>
                            <p class="text-base text-gray-900">${request.business_type}</p>
                        </div>
                        ` : ''}
                        ${request.gst_number ? `
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">GST Number</label>
                            <p class="text-base text-gray-900">${request.gst_number}</p>
                        </div>
                        ` : ''}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${getStatusBadgeColor(request.status || 'pending')}">
                                ${(request.status || 'pending').charAt(0).toUpperCase() + (request.status || 'pending').slice(1)}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Request Date</label>
                            <p class="text-base text-gray-900">${formatDate(request.createdAt || request.date)}</p>
                        </div>
                    </div>
                    ${request.address_line1 || request.city || request.state ? `
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-base text-gray-900">
                                ${request.address_line1 || ''}<br>
                                ${request.address_line2 || ''}<br>
                                ${[request.city, request.state, request.pincode].filter(Boolean).join(', ')}<br>
                                ${request.country || 'India'}
                            </p>
                        </div>
                    </div>
                    ` : ''}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Requirements</label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-base text-gray-900 whitespace-pre-wrap">${request.special_requirements || request.requirements || 'N/A'}</p>
                        </div>
                    </div>
                    ${request.notes ? `
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Admin Notes</label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-base text-gray-900 whitespace-pre-wrap">${request.notes}</p>
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
            
            document.getElementById('request-modal-overlay').classList.add('show');
            lucide.createIcons();
        }

        function closeRequestModal(event) {
            if (!event || event.target.id === 'request-modal-overlay') {
                document.getElementById('request-modal-overlay').classList.remove('show');
            }
        }

        async function updateRequestStatus(requestId, newStatus) {
            try {
                const response = await fetch(`${API_BASE}/b2b-requests.php`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: parseInt(requestId),
                        status: newStatus
                    })
                });

                const result = await response.json();

                if (result.success) {
                    await loadRequests();
                    await Tivora.alert('Request status updated successfully!', 'success');
                } else {
                    await Tivora.alert('Error: ' + (result.message || 'Failed to update request status'), 'error');
                }
            } catch (error) {
                console.error('Error updating request status:', error);
                await Tivora.alert('An error occurred while updating the request status. Please try again.', 'error');
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
