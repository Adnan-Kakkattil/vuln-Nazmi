<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | BLine Boutique</title>
    
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

        .password-strength {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .password-strength.weak {
            width: 33%;
            background: #ef4444;
        }

        .password-strength.medium {
            width: 66%;
            background: #f59e0b;
        }

        .password-strength.strong {
            width: 100%;
            background: #10b981;
        }

        input, select, textarea {
            font-size: 16px !important;
        }

        /* Full Screen Error Message */
        .error-screen {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #f8fafc;
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            animation: fadeIn 0.3s ease-out;
        }

        .error-screen.show {
            display: flex;
        }

        .error-card {
            background: white;
            border-radius: 2.5rem;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            animation: slideUp 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .error-content {
            padding: 4rem 3rem;
            text-align: center;
        }

        .error-icon-wrapper {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            background: #fee2e2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.5s ease-out 0.2s both;
        }

        .error-icon-wrapper i {
            width: 64px;
            height: 64px;
            color: #dc2626;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .error-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 1rem;
        }

        .error-message-text {
            color: #4b5563;
            font-size: 1.125rem;
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .error-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .error-btn {
            padding: 1rem 2rem;
            border-radius: 1rem;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .error-btn-primary {
            background: #111827;
            color: white;
        }

        .error-btn-primary:hover {
            background: var(--brand-color);
        }

        /* Hide form when error is shown */
        .login-container.hide {
            display: none;
        }
    </style>
</head>
<body>

    <div class="login-container" id="loginContainer">
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
                    <h2 class="text-4xl font-serif font-black mb-4 leading-tight">Create a Strong <br/> Password.</h2>
                    <p class="text-white/80 font-medium">Make sure it's secure and memorable.</p>
                </div>
                <div class="absolute top-12 left-12">
                    <h1 class="text-3xl font-serif font-black tracking-tighter text-white">
                        BLine<span class="text-brand">.</span>
                    </h1>
                </div>
            </div>

            <!-- Right Side: Reset Password Form -->
            <div class="login-form-side">
                <div class="mb-10 md:hidden text-center">
                    <h1 class="text-3xl font-serif font-black tracking-tighter text-slate-900">
                        BLine<span class="text-brand">.</span>
                    </h1>
                </div>

                <div class="mb-10">
                    <h2 class="text-3xl font-serif font-black text-slate-900 mb-2">Reset Password</h2>
                    <p class="text-slate-400 font-medium">Enter your new password below. Make sure it's strong and secure.</p>
                </div>

                <!-- Illustration -->
                <div class="mb-8 flex justify-center">
                    <div class="w-24 h-24 bg-brand/10 rounded-full flex items-center justify-center">
                        <i data-lucide="shield-check" class="w-12 h-12 text-brand opacity-60"></i>
                    </div>
                </div>

                <form id="reset-password-form" onsubmit="handleResetPassword(event)" class="space-y-6">
                    <!-- Email (Read-only) -->
                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Email Address</label>
                        <div class="relative">
                            <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                            <input 
                                type="email" 
                                id="reset-email" 
                                name="email" 
                                readonly
                                class="form-input pl-12 bg-slate-50 text-slate-500 cursor-not-allowed"
                                placeholder="your.email@example.com"
                            >
                        </div>
                    </div>

                    <!-- New Password -->
                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">New Password</label>
                        <div class="relative">
                            <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                            <input 
                                type="password" 
                                id="reset-password" 
                                name="password" 
                                placeholder="Enter new password"
                                class="form-input pl-12 pr-12" 
                                required
                                oninput="checkPasswordStrength()"
                            >
                            <button 
                                type="button"
                                onclick="togglePasswordVisibility('reset-password', 'reset-password-toggle-icon')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                            >
                                <i data-lucide="eye" id="reset-password-toggle-icon" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <!-- Password Strength Indicator -->
                        <div class="mt-2">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs text-slate-400">Password strength</span>
                                <span id="password-strength-text" class="text-xs font-medium text-slate-400"></span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-1">
                                <div id="password-strength-bar" class="password-strength h-1 rounded-full"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Confirm Password</label>
                        <div class="relative">
                            <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                            <input 
                                type="password" 
                                id="reset-confirm-password" 
                                name="confirmPassword" 
                                placeholder="Confirm new password"
                                class="form-input pl-12 pr-12" 
                                required
                                oninput="checkPasswordMatch()"
                            >
                            <button 
                                type="button"
                                onclick="togglePasswordVisibility('reset-confirm-password', 'reset-confirm-password-toggle-icon')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                            >
                                <i data-lucide="eye" id="reset-confirm-password-toggle-icon" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <p id="password-match-message" class="mt-1 text-xs text-green-600 hidden"></p>
                    </div>

                    <!-- Password Requirements -->
                    <div class="bg-slate-50 rounded-lg p-4 text-xs text-slate-600 space-y-2">
                        <p class="font-black text-slate-700 mb-2">Password must contain:</p>
                        <ul class="space-y-1">
                            <li id="req-length" class="flex items-center gap-2">
                                <i data-lucide="circle" class="w-3 h-3"></i>
                                At least 8 characters
                            </li>
                            <li id="req-uppercase" class="flex items-center gap-2">
                                <i data-lucide="circle" class="w-3 h-3"></i>
                                One uppercase letter
                            </li>
                            <li id="req-lowercase" class="flex items-center gap-2">
                                <i data-lucide="circle" class="w-3 h-3"></i>
                                One lowercase letter
                            </li>
                            <li id="req-number" class="flex items-center gap-2">
                                <i data-lucide="circle" class="w-3 h-3"></i>
                                One number
                            </li>
                        </ul>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        id="reset-submit-btn"
                        class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black text-lg shadow-xl hover:bg-brand transition-all disabled:bg-slate-300 disabled:cursor-not-allowed"
                    >
                        RESET PASSWORD
                    </button>
                </form>

                <p class="mt-10 text-center text-sm font-medium text-slate-400">
                    Back to 
                    <a href="login.php" class="text-brand font-black hover:underline underline-offset-4 ml-1">LOGIN</a>
                </p>
            </div>

        </div>
    </div>

    <!-- Full Screen Error Message -->
    <div id="errorScreen" class="error-screen">
        <div class="error-card">
            <div class="error-content">
                <div class="error-icon-wrapper">
                    <i data-lucide="alert-circle"></i>
                </div>
                <h2 id="errorTitle" class="error-title">Invalid Reset Link</h2>
                <p id="errorMessage" class="error-message-text">
                    This password reset link is invalid or has expired.<br>
                    Please request a new password reset link.
                </p>
                <div class="error-actions">
                    <button onclick="goToForgotPassword()" class="error-btn error-btn-primary">
                        <i data-lucide="arrow-right"></i>
                        Request New Reset Link
                    </button>
                    <button onclick="goToLogin()" class="error-btn" style="background: #f1f5f9; color: #475569;">
                        <i data-lucide="log-in"></i>
                        Back to Login
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Get email and token from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const email = urlParams.get('email');
        const token = urlParams.get('token');
        
        // Validate token on page load
        let tokenValid = false;

        // Validate token immediately on page load
        async function validateToken() {
            if (!token || !email) {
                showErrorScreen('Invalid Reset Link', 'This password reset link is missing required information. Please request a new password reset link.');
                return false;
            }

            // Validate token format (64 hex characters)
            if (!/^[a-f0-9]{64}$/i.test(token)) {
                showErrorScreen('Invalid Reset Link', 'This password reset link has an invalid format. Please request a new password reset link.');
                return false;
            }

            try {
                // Check token validity with API (validate_only flag)
                const response = await fetch('/api/v1/auth/reset-password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email,
                        token: token,
                        validate_only: true
                    })
                });

                const result = await response.json();
                
                if (result.success === true && result.valid === true) {
                    tokenValid = true;
                    if (email) {
                        document.getElementById('reset-email').value = email;
                    }
                    return true;
                } else {
                    // Token is invalid or expired
                    const errorMessage = result.message || 'This password reset link is invalid or has expired. Please request a new password reset link.';
                    showErrorScreen('Invalid or Expired Reset Link', errorMessage);
                    return false;
                }
            } catch (error) {
                console.error('Token validation error:', error);
                // On error, show error screen to be safe
                showErrorScreen('Validation Error', 'Unable to validate reset link. Please request a new password reset link.');
                return false;
            }
        }

        // Show error screen
        function showErrorScreen(title, message) {
            const errorScreen = document.getElementById('errorScreen');
            const loginContainer = document.getElementById('loginContainer');
            const errorTitle = document.getElementById('errorTitle');
            const errorMessage = document.getElementById('errorMessage');
            
            // Hide the form
            if (loginContainer) {
                loginContainer.classList.add('hide');
            }
            
            // Set error message
            if (title) errorTitle.textContent = title;
            if (message) errorMessage.innerHTML = message.replace(/\n/g, '<br>');
            
            // Show error screen
            errorScreen.classList.add('show');
            
            // Reinitialize icons
            lucide.createIcons();
        }

        function goToForgotPassword() {
            window.location.href = 'forgot-password.php';
        }

        function goToLogin() {
            window.location.href = 'login.php';
        }

        // Validate token on page load - block access if invalid
        validateToken().then(valid => {
            if (!valid) {
                // Error screen is already shown by validateToken()
                console.log('Token validation failed - access blocked');
            }
        });

        // Toggle Password Visibility
        function togglePasswordVisibility(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                passwordInput.type = 'password';
                toggleIcon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }

        // Check Password Strength
        function checkPasswordStrength() {
            const password = document.getElementById('reset-password').value;
            const strengthBar = document.getElementById('password-strength-bar');
            const strengthText = document.getElementById('password-strength-text');
            
            let strength = 0;
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password)
            };
            
            updateRequirementIcon('req-length', requirements.length);
            updateRequirementIcon('req-uppercase', requirements.uppercase);
            updateRequirementIcon('req-lowercase', requirements.lowercase);
            updateRequirementIcon('req-number', requirements.number);
            
            Object.values(requirements).forEach(req => {
                if (req) strength++;
            });
            
            strengthBar.className = 'password-strength h-1 rounded-full';
            if (strength === 0) {
                strengthBar.style.width = '0%';
                strengthText.textContent = '';
            } else if (strength <= 2) {
                strengthBar.classList.add('weak');
                strengthText.textContent = 'Weak';
                strengthText.className = 'text-xs font-medium text-red-600';
            } else if (strength === 3) {
                strengthBar.classList.add('medium');
                strengthText.textContent = 'Medium';
                strengthText.className = 'text-xs font-medium text-yellow-600';
            } else {
                strengthBar.classList.add('strong');
                strengthText.textContent = 'Strong';
                strengthText.className = 'text-xs font-medium text-green-600';
            }
            
            checkPasswordMatch();
        }

        function updateRequirementIcon(reqId, met) {
            const reqElement = document.getElementById(reqId);
            const icon = reqElement.querySelector('i');
            if (met) {
                icon.setAttribute('data-lucide', 'check-circle');
                icon.className = 'w-3 h-3 text-green-600';
                reqElement.className = 'flex items-center gap-2 text-green-600';
            } else {
                icon.setAttribute('data-lucide', 'circle');
                icon.className = 'w-3 h-3 text-slate-400';
                reqElement.className = 'flex items-center gap-2 text-slate-600';
            }
            lucide.createIcons();
        }

        // Check Password Match
        function checkPasswordMatch() {
            const password = document.getElementById('reset-password').value;
            const confirmPassword = document.getElementById('reset-confirm-password').value;
            const matchMessage = document.getElementById('password-match-message');
            
            if (confirmPassword.length === 0) {
                matchMessage.classList.add('hidden');
                return;
            }
            
            if (password === confirmPassword) {
                matchMessage.textContent = 'Passwords match';
                matchMessage.className = 'mt-1 text-xs text-green-600';
                matchMessage.classList.remove('hidden');
            } else {
                matchMessage.textContent = 'Passwords do not match';
                matchMessage.className = 'mt-1 text-xs text-red-600';
                matchMessage.classList.remove('hidden');
            }
        }

        // Handle Reset Password Form Submission
        async function handleResetPassword(event) {
            event.preventDefault();
            
            // Check if token was validated
            if (!tokenValid) {
                const valid = await validateToken();
                if (!valid) {
                    return;
                }
            }
            
            const password = document.getElementById('reset-password').value;
            const confirmPassword = document.getElementById('reset-confirm-password').value;
            const email = document.getElementById('reset-email').value;
            const token = urlParams.get('token');
            
            if (!token || !email) {
                showErrorScreen('Invalid Reset Link', 'This password reset link is missing required information. Please request a new password reset link.');
                return;
            }
            
            if (password !== confirmPassword) {
                alert('Passwords do not match.');
                return;
            }
            
            if (password.length < 8) {
                alert('Password must be at least 8 characters long.');
                return;
            }
            
            const submitBtn = document.getElementById('reset-submit-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin inline-block"></i> Resetting...';
            lucide.createIcons();
            
            try {
                const response = await fetch('/api/v1/auth/reset-password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email,
                        token: token,
                        password: password,
                        confirm_password: confirmPassword
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Show success and redirect
                    alert(result.message || 'Password has been reset successfully!');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1000);
                } else {
                    // Show error screen if token invalid
                    if (result.message && (result.message.includes('Invalid') || result.message.includes('expired'))) {
                        showErrorScreen('Invalid or Expired Reset Link', result.message);
                    } else {
                        alert(result.message || 'Failed to reset password. Please try again.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                        lucide.createIcons();
                    }
                }
            } catch (error) {
                console.error('Reset password error:', error);
                alert('An error occurred. Please try again later.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                lucide.createIcons();
            }
        }
    </script>
</body>
</html>
