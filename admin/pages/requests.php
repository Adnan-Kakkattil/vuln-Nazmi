<?php
/**
 * B2B Requests Page Content
 * Manages B2B partnership requests
 */
?>
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
                class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
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
                class="flex-1 sm:flex-none px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
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
        
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        if (requestsToRender.length === 0) {
            if (noRequestsMsg) noRequestsMsg.classList.remove('hidden');
            return;
        }
        
        if (noRequestsMsg) noRequestsMsg.classList.add('hidden');
        
        // Sort by date (newest first)
        const sortedRequests = [...requestsToRender].sort((a, b) => {
            return new Date(b.createdAt || b.date || b.created_at) - new Date(a.createdAt || a.date || a.created_at);
        });

        tbody.innerHTML = sortedRequests.map((request, index) => {
            const requestId = request.id;
            const status = request.status || 'pending';
            return `
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-4 text-sm text-gray-900">${formatDate(request.createdAt || request.date || request.created_at)}</td>
                    <td class="py-3 px-4 text-sm text-gray-900 font-medium">${escapeHtml(request.name || request.contact_person || 'N/A')}</td>
                    <td class="py-3 px-4 text-sm text-gray-700">${escapeHtml(request.company || request.company_name || 'N/A')}</td>
                    <td class="py-3 px-4 text-sm text-gray-700">${escapeHtml(request.email || 'N/A')}</td>
                    <td class="py-3 px-4 text-sm text-gray-700">${escapeHtml(request.phone || 'N/A')}</td>
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
                                class="text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-teal-500"
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

        const totalRequestsEl = document.getElementById('total-requests');
        const pendingRequestsEl = document.getElementById('pending-requests');
        const approvedRequestsEl = document.getElementById('approved-requests');
        const rejectedRequestsEl = document.getElementById('rejected-requests');
        const requestCountEl = document.getElementById('request-count');

        if (totalRequestsEl) totalRequestsEl.textContent = total;
        if (pendingRequestsEl) pendingRequestsEl.textContent = pending;
        if (approvedRequestsEl) approvedRequestsEl.textContent = approved;
        if (rejectedRequestsEl) rejectedRequestsEl.textContent = rejected;
        if (requestCountEl) requestCountEl.textContent = total;
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

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function filterRequests() {
        const statusFilter = document.getElementById('status-filter')?.value || '';
        const searchTerm = (document.getElementById('search-requests')?.value || '').toLowerCase();
        
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

        const pendingRequestsEl = document.getElementById('pending-requests');
        const approvedRequestsEl = document.getElementById('approved-requests');
        const rejectedRequestsEl = document.getElementById('rejected-requests');

        if (pendingRequestsEl) pendingRequestsEl.textContent = pending;
        if (approvedRequestsEl) approvedRequestsEl.textContent = approved;
        if (rejectedRequestsEl) rejectedRequestsEl.textContent = rejected;
    }

    function viewRequestDetails(requestId) {
        const request = requests.find(r => r.id === requestId);
        
        if (!request) return;

        const modalContent = document.getElementById('request-modal-content');
        if (!modalContent) return;

        modalContent.innerHTML = `
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <p class="text-base text-gray-900">${escapeHtml(request.name || request.contact_person || 'N/A')}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                        <p class="text-base text-gray-900">${escapeHtml(request.company || request.company_name || 'N/A')}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <p class="text-base text-gray-900">
                            <a href="mailto:${escapeHtml(request.email || '')}" class="text-blue-600 hover:underline">${escapeHtml(request.email || 'N/A')}</a>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <p class="text-base text-gray-900">
                            <a href="tel:${escapeHtml(request.phone || '')}" class="text-blue-600 hover:underline">${escapeHtml(request.phone || 'N/A')}</a>
                        </p>
                    </div>
                    ${request.business_type ? `
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Business Type</label>
                        <p class="text-base text-gray-900">${escapeHtml(request.business_type)}</p>
                    </div>
                    ` : ''}
                    ${request.gst_number ? `
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">GST Number</label>
                        <p class="text-base text-gray-900">${escapeHtml(request.gst_number)}</p>
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
                        <p class="text-base text-gray-900">${formatDate(request.createdAt || request.date || request.created_at)}</p>
                    </div>
                </div>
                ${request.address_line1 || request.city || request.state ? `
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-base text-gray-900">
                            ${escapeHtml(request.address_line1 || '')}<br>
                            ${escapeHtml(request.address_line2 || '')}<br>
                            ${[request.city, request.state, request.pincode].filter(Boolean).map(escapeHtml).join(', ')}<br>
                            ${escapeHtml(request.country || 'India')}
                        </p>
                    </div>
                </div>
                ` : ''}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Requirements</label>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-base text-gray-900 whitespace-pre-wrap">${escapeHtml(request.special_requirements || request.requirements || 'N/A')}</p>
                    </div>
                </div>
                ${request.notes ? `
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Admin Notes</label>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-base text-gray-900 whitespace-pre-wrap">${escapeHtml(request.notes)}</p>
                    </div>
                </div>
                ` : ''}
            </div>
        `;
        
        const modal = document.getElementById('request-modal-overlay');
        if (modal) {
            modal.classList.add('show');
            lucide.createIcons();
        }
    }

    function closeRequestModal(event) {
        if (!event || event.target.id === 'request-modal-overlay') {
            const modal = document.getElementById('request-modal-overlay');
            if (modal) modal.classList.remove('show');
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
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Request status updated successfully!', 'success');
                } else {
                    alert('Request status updated successfully!');
                }
            } else {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Error: ' + (result.message || 'Failed to update request status'), 'error');
                } else {
                    alert('Error: ' + (result.message || 'Failed to update request status'));
                }
            }
        } catch (error) {
            console.error('Error updating request status:', error);
            if (typeof Tivora !== 'undefined' && Tivora.alert) {
                await Tivora.alert('An error occurred while updating the request status. Please try again.', 'error');
            } else {
                alert('An error occurred while updating the request status. Please try again.');
            }
        }
    }
</script>
