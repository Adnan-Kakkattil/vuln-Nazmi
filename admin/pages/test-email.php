<?php
/**
 * Test Email Page
 * Allows admin to test email configuration
 */
?>
<div class="stat-card">
    <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-3">
        <i data-lucide="mail" class="w-6 h-6 text-teal-600"></i>
        Test Email Configuration
    </h2>
    
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-start gap-3">
            <i data-lucide="info" class="w-5 h-5 text-blue-600 mt-0.5"></i>
            <div>
                <p class="text-sm font-medium text-blue-900 mb-1">Email Testing</p>
                <p class="text-sm text-blue-700">Enter an email address to send a test email. This will verify your SMTP configuration is working correctly.</p>
            </div>
        </div>
    </div>
    
    <form id="test-email-form" onsubmit="sendTestEmail(event)" class="space-y-6">
        <div class="space-y-2">
            <label for="test-email-address" class="block text-sm font-medium text-gray-700">
                Test Email Address <span class="text-red-500">*</span>
            </label>
            <input
                type="email"
                id="test-email-address"
                name="email"
                required
                placeholder="your.email@example.com"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
            >
            <p class="text-xs text-gray-500">A test email will be sent to this address to verify SMTP configuration.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <button
                type="submit"
                id="test-email-btn"
                class="flex items-center gap-2 px-6 py-3 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-semibold"
            >
                <i data-lucide="send" class="w-5 h-5"></i>
                <span id="test-email-btn-text">Send Test Email</span>
                <div id="test-email-spinner" class="hidden w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            </button>
            <button
                type="button"
                onclick="loadCurrentEmail()"
                class="px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
            >
                Use My Email
            </button>
        </div>
    </form>
    
    <div id="test-email-result" class="mt-6 hidden"></div>
</div>

<script>
    (function() {
        // API Base URL
        const apiBase = (typeof API_BASE !== 'undefined') ? API_BASE : (window.API_BASE || '/api/v1/admin');
        
        // Load current admin email
        function loadCurrentEmail() {
            // Try to get email from page or use default
            const emailInput = document.getElementById('test-email-address');
            if (emailInput && !emailInput.value) {
                // You can fetch admin email from API or use a default
                emailInput.value = '';
                emailInput.focus();
            }
        }
        
        // Send test email
        async function sendTestEmail(event) {
            event.preventDefault();
            
            const emailInput = document.getElementById('test-email-address');
            const submitBtn = document.getElementById('test-email-btn');
            const btnText = document.getElementById('test-email-btn-text');
            const spinner = document.getElementById('test-email-spinner');
            const resultDiv = document.getElementById('test-email-result');
            
            const email = emailInput.value.trim();
            
            if (!email) {
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Please enter an email address', 'warning');
                } else {
                    alert('Please enter an email address');
                }
                return;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            btnText.textContent = 'Sending...';
            spinner.classList.remove('hidden');
            resultDiv.classList.add('hidden');
            
            try {
                const response = await fetch(`${apiBase}/test-email.php`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email: email })
                });
                
                const result = await response.json();
                
                // Show result
                resultDiv.classList.remove('hidden');
                
                if (result.success) {
                    resultDiv.innerHTML = `
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <i data-lucide="check-circle" class="w-6 h-6 text-green-600 mt-0.5"></i>
                                <div>
                                    <p class="font-medium text-green-900 mb-1">Test Email Sent Successfully!</p>
                                    <p class="text-sm text-green-700">A test email has been sent to <strong>${escapeHtml(email)}</strong>. Please check your inbox.</p>
                                    <p class="text-xs text-green-600 mt-2">Sent at: ${result.data?.sent_at || 'Just now'}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    if (typeof Tivora !== 'undefined' && Tivora.alert) {
                        await Tivora.alert(result.message || 'Test email sent successfully!', 'success');
                    }
                } else {
                    resultDiv.innerHTML = `
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <i data-lucide="alert-circle" class="w-6 h-6 text-red-600 mt-0.5"></i>
                                <div>
                                    <p class="font-medium text-red-900 mb-1">Failed to Send Test Email</p>
                                    <p class="text-sm text-red-700">${escapeHtml(result.message || 'An error occurred while sending the test email.')}</p>
                                    ${result.error ? `<p class="text-xs text-red-600 mt-2">Error: ${escapeHtml(result.error)}</p>` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                    
                    if (typeof Tivora !== 'undefined' && Tivora.alert) {
                        await Tivora.alert(result.message || 'Failed to send test email', 'error');
                    }
                }
                
                lucide.createIcons();
                
            } catch (error) {
                console.error('Test email error:', error);
                resultDiv.classList.remove('hidden');
                resultDiv.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <i data-lucide="alert-circle" class="w-6 h-6 text-red-600 mt-0.5"></i>
                            <div>
                                <p class="font-medium text-red-900 mb-1">Connection Error</p>
                                <p class="text-sm text-red-700">Failed to connect to the server. Please check your connection and try again.</p>
                            </div>
                        </div>
                    </div>
                `;
                
                if (typeof Tivora !== 'undefined' && Tivora.alert) {
                    await Tivora.alert('Connection error. Please try again.', 'error');
                }
                
                lucide.createIcons();
            } finally {
                // Reset button state
                submitBtn.disabled = false;
                btnText.textContent = 'Send Test Email';
                spinner.classList.add('hidden');
            }
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Make functions globally accessible
        window.sendTestEmail = sendTestEmail;
        window.loadCurrentEmail = loadCurrentEmail;
        
        // Initialize icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    })();
</script>
