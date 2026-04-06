<?php
// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Login page - handles user authentication
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | BLine Boutique</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="shortcut icon" type="image/png" href="logo.png">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Custom Styles -->
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #ffffff; 
        }
        .font-serif { 
            font-family: 'Playfair Display', serif; 
        }
        
        :root {
            --brand-color: #14b8a6;
        }

        .bg-brand { background-color: var(--brand-color); }
        .text-brand { color: var(--brand-color); }
        .border-brand { border-color: var(--brand-color); }
        .ring-brand { --tw-ring-color: var(--brand-color); }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8fafc;
            padding: 1.5rem;
        }

        .login-card {
            display: flex;
            width: 100%;
            max-width: 1000px;
            background: white;
            border-radius: 2.5rem;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
        }

        .login-image-side {
            width: 50%;
            position: relative;
            display: none;
        }

        @media (min-width: 768px) {
            .login-image-side { display: block; }
        }

        .login-form-side {
            width: 100%;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        @media (min-width: 768px) {
            .login-form-side { width: 50%; padding: 4rem; }
        }

        .form-input {
            width: 100%;
            background-color: #f1f5f9;
            border: 2px solid transparent;
            border-radius: 1rem;
            padding: 1rem 1.25rem;
            outline: none;
            transition: all 0.2s;
            font-weight: 500;
        }

        .form-input:focus {
            background-color: white;
            border-color: var(--brand-color);
            box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.1);
        }

        .form-input.input-error {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
        }

        .form-input.input-valid {
            border-color: #22c55e !important;
        }

        .error-message {
            display: none;
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        .error-message.show {
            display: block;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.875rem;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .social-btn:hover {
            background-color: #f8fafc;
            border-color: #cbd5e1;
        }

        input, select, textarea {
            font-size: 16px !important; /* Prevents zoom on iOS */
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-card">
            
            <!-- Left Side: Image -->
            <div class="login-image-side">
                <img 
                    src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=1200&auto=format&fit=crop" 
                    alt="BLine Boutique Fashion" 
                    class="absolute inset-0 w-full h-full object-cover"
                >
                <div class="absolute inset-0 bg-black/20"></div>
                <div class="absolute bottom-12 left-12 right-12 text-white">
                    <h2 class="text-4xl font-serif font-black mb-4 leading-tight">Join the World <br/> of Elegance.</h2>
                    <p class="text-white/80 font-medium">Experience the finest artisanal collection curated just for you.</p>
                </div>
                <div class="absolute top-12 left-12">
                    <h1 class="text-3xl font-serif font-black tracking-tighter text-white">
                        BLine<span class="text-brand">.</span>
                    </h1>
                </div>
            </div>

            <!-- Right Side: Login Form -->
            <div class="login-form-side">
                <div class="mb-10 md:hidden text-center">
                    <h1 class="text-3xl font-serif font-black tracking-tighter text-slate-900">
                        BLine<span class="text-brand">.</span>
                    </h1>
                </div>

                <!-- Login Required Message -->
                <?php
                $message = $_GET['message'] ?? '';
                if ($message === 'login_required'):
                ?>
                <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-amber-500 flex-shrink-0"></i>
                        <p class="text-sm text-amber-700">Please log in or create an account to complete your checkout.</p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Error Message Container (hidden by default) -->
                <div id="login-error-message" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg hidden">
                    <div class="flex items-center gap-3">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-red-500 flex-shrink-0"></i>
                        <p id="login-error-text" class="text-sm text-red-700 font-medium"></p>
                    </div>
                </div>

                <div class="mb-10">
                    <h2 class="text-3xl font-serif font-black text-slate-900 mb-2">Welcome Back</h2>
                    <p class="text-slate-400 font-medium">Please enter your details to sign in.</p>
                </div>

                <form id="login-form" onsubmit="handleLogin(event)" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Email Address</label>
                        <div class="relative">
                            <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                            <input 
                                type="email" 
                                id="login-email" 
                                name="email" 
                                placeholder="name@example.com" 
                                class="form-input pl-12" 
                                required
                                onblur="validateEmailField(this)"
                                oninput="clearFieldError(this); hideLoginError();"
                            >
                        </div>
                        <span class="error-message" id="login-email-error"></span>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center ml-1">
                            <label class="text-xs font-black uppercase tracking-widest text-slate-400">Password</label>
                            <a href="forgot-password.php" class="text-[10px] font-black text-brand uppercase tracking-widest hover:underline">Forgot?</a>
                        </div>
                        <div class="relative">
                            <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                            <input 
                                type="password" 
                                id="login-password" 
                                name="password" 
                                placeholder="••••••••" 
                                class="form-input pl-12 pr-12" 
                                required
                                oninput="hideLoginError();"
                            >
                            <button 
                                type="button"
                                onclick="togglePasswordVisibility()"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                            >
                                <i data-lucide="eye" id="password-toggle-icon" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 ml-1">
                        <input 
                            type="checkbox" 
                            id="remember-me" 
                            class="w-4 h-4 rounded text-brand focus:ring-brand"
                        >
                        <label for="remember-me" class="text-sm font-medium text-slate-500">Remember for 30 days</label>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black text-lg shadow-xl hover:bg-brand transition-all"
                    >
                        SIGN IN
                    </button>
                </form>

                <div class="mt-8">
                    <div class="relative flex items-center justify-center mb-8">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-slate-100"></div>
                        </div>
                        <span class="relative px-4 bg-white text-[10px] font-black text-slate-300 uppercase tracking-[0.2em]">Or continue with</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" class="social-btn">
                            <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5" alt="Google">
                            Google
                        </button>
                        <button type="button" class="social-btn">
                            <img src="https://www.svgrepo.com/show/475647/facebook-color.svg" class="w-5 h-5" alt="Facebook">
                            Facebook
                        </button>
                    </div>
                </div>

                <p class="mt-10 text-center text-sm font-medium text-slate-400">
                    Don't have an account? 
                    <a href="signup.php" class="text-brand font-black hover:underline underline-offset-4 ml-1">CREATE ACCOUNT</a>
                </p>
            </div>

        </div>
    </div>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Validation Functions
        function validateEmailField(input) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const errorId = input.id + '-error';
            const errorMsg = document.getElementById(errorId);
            
            if (!input.value.trim()) {
                showFieldError(input, errorMsg, 'Email address is required');
                return false;
            }
            
            if (!emailRegex.test(input.value.trim())) {
                showFieldError(input, errorMsg, 'Please enter a valid email address');
                return false;
            }
            
            showFieldSuccess(input, errorMsg);
            return true;
        }

        function showFieldError(input, errorElement, message) {
            input.classList.add('input-error');
            input.classList.remove('input-valid');
            if (errorElement) {
                errorElement.textContent = message;
                errorElement.classList.add('show');
            }
        }

        function showFieldSuccess(input, errorElement) {
            input.classList.remove('input-error');
            input.classList.add('input-valid');
            if (errorElement) {
                errorElement.classList.remove('show');
                errorElement.textContent = '';
            }
        }

        function clearFieldError(input) {
            input.classList.remove('input-error');
            const errorId = input.id + '-error';
            const errorElement = document.getElementById(errorId);
            if (errorElement) {
                errorElement.classList.remove('show');
            }
        }

        // Show login error message
        function showLoginError(message) {
            const errorContainer = document.getElementById('login-error-message');
            const errorText = document.getElementById('login-error-text');
            
            if (errorContainer && errorText) {
                errorText.textContent = message || 'Login failed. Please check your credentials and try again.';
                errorContainer.classList.remove('hidden');
                
                // Scroll to error message
                errorContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                
                // Add error styling to form inputs
                const emailInput = document.getElementById('login-email');
                const passwordInput = document.getElementById('login-password');
                if (emailInput) {
                    emailInput.classList.add('input-error');
                }
                if (passwordInput) {
                    passwordInput.classList.add('input-error');
                }
            }
        }

        // Hide login error message
        function hideLoginError() {
            const errorContainer = document.getElementById('login-error-message');
            if (errorContainer) {
                errorContainer.classList.add('hidden');
            }
        }

        // Toggle Password Visibility
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('login-password');
            const toggleIcon = document.getElementById('password-toggle-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                passwordInput.type = 'password';
                toggleIcon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }

        // API Base URL
        const API_BASE = 'api/v1';

        // Check if user is already logged in on page load
        async function checkExistingSession() {
            try {
                const response = await fetch(`${API_BASE}/auth.php`, {
                    method: 'GET',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' }
                });

                if (response.ok) {
                    const result = await response.json();
                    if (result.success && result.authenticated) {
                        const urlParams = new URLSearchParams(window.location.search);
                        const returnUrl = urlParams.get('return');
                        
                        if (returnUrl && !returnUrl.includes('login.php')) {
                            window.location.href = decodeURIComponent(returnUrl);
                            return true;
                        }
                        
                        redirectUser(result.data, result.is_admin || false);
                        return true;
                    }
                }
                return false;
            } catch (error) {
                console.error('Session check error:', error);
                return false;
            }
        }

        // Redirect user after login
        function redirectUser(userData, isAdmin = false) {
            const urlParams = new URLSearchParams(window.location.search);
            const returnUrl = urlParams.get('return');
            
            if (returnUrl && !returnUrl.includes('login.php')) {
                window.location.href = decodeURIComponent(returnUrl);
                return;
            }
            
            if (isAdmin) {
                window.location.href = 'admin/index.php';
                return;
            }

            window.location.href = 'index.php';
        }

        // Handle Login Form Submission
        async function handleLogin(event) {
            event.preventDefault();
            
            const emailInput = document.getElementById('login-email');
            const password = document.getElementById('login-password').value;
            const rememberMe = document.getElementById('remember-me').checked;
            
            if (!validateEmailField(emailInput)) {
                return;
            }
            
            if (!password || password.length < 8) {
                return;
            }
            
            const email = emailInput.value.trim().toLowerCase();
            
            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin inline-block"></i> Signing in...';
            lucide.createIcons();
            
            try {
                const response = await fetch(`${API_BASE}/auth.php`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'login',
                        email: email,
                        password: password,
                        remember: rememberMe
                    })
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    // Show error message to user
                    showLoginError(result.message || 'Login failed. Please check your credentials and try again.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    lucide.createIcons();
                    return;
                }

                const isAdmin = result.is_admin || false;
                
                setTimeout(() => {
                    redirectUser(result.data, isAdmin);
                }, 500);

            } catch (error) {
                console.error('Login error:', error);
                // Show error message for network or other errors
                showLoginError('Network error. Please check your connection and try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                lucide.createIcons();
            }
        }

        // Check session on page load
        document.addEventListener('DOMContentLoaded', async () => {
            await checkExistingSession();
        });
    </script>
</body>
</html>
