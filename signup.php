<?php
// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Nazmi Boutique</title>
    
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
            padding: 1rem;
        }

        @media (min-width: 640px) {
            .login-container {
                padding: 1.5rem;
            }
        }

        .login-card {
            display: flex;
            width: 100%;
            max-width: 1000px;
            background: white;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
        }

        @media (min-width: 768px) {
            .login-card {
                border-radius: 2.5rem;
            }
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
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
        }

        @media (min-width: 640px) {
            .login-form-side {
                padding: 2.5rem 2rem;
            }
        }

        @media (min-width: 768px) {
            .login-form-side { 
                width: 50%; 
                padding: 4rem; 
                overflow-y: visible;
            }
        }

        .form-input {
            width: 100%;
            background-color: #f1f5f9;
            border: 2px solid transparent;
            border-radius: 0.75rem;
            padding: 0.875rem 1rem;
            outline: none;
            transition: all 0.2s;
            font-weight: 500;
            font-size: 16px;
        }

        @media (min-width: 640px) {
            .form-input {
                border-radius: 1rem;
                padding: 1rem 1.25rem;
            }
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
            font-size: 16px !important;
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
                    alt="Nazmi Boutique Fashion" 
                    class="absolute inset-0 w-full h-full object-cover"
                >
                <div class="absolute inset-0 bg-black/20"></div>
                <div class="absolute bottom-12 left-12 right-12 text-white">
                    <h2 class="text-4xl font-serif font-black mb-4 leading-tight">Start Your Journey <br/> with Style.</h2>
                    <p class="text-white/80 font-medium">Join our community and discover exclusive collections.</p>
                </div>
                <div class="absolute top-12 left-12">
                    <h1 class="text-3xl font-serif font-black tracking-tighter text-white">
                        NAZMI<span class="text-brand">.</span>
                    </h1>
                </div>
            </div>

            <!-- Right Side: Signup Form -->
            <div class="login-form-side">
                <div class="mb-6 sm:mb-8 md:hidden text-center">
                    <h1 class="text-2xl sm:text-3xl font-serif font-black tracking-tighter text-slate-900 mb-4">
                        NAZMI<span class="text-brand">.</span>
                    </h1>
                </div>

                <div class="mb-5 sm:mb-6 md:mb-8">
                    <p class="text-xs text-slate-400 font-medium mb-2 sm:mb-3 md:mb-4">Join NAZMI BOUTIQUE for a better shopping experience</p>
                    <h2 class="text-2xl sm:text-3xl font-serif font-black text-slate-900 mb-2">Create Account</h2>
                </div>

                <form id="signup-form" onsubmit="handleSignup(event)" class="space-y-3.5 sm:space-y-4 md:space-y-5">
                    <!-- Name Fields -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">First Name</label>
                            <input 
                                type="text" 
                                id="first-name" 
                                name="first_name" 
                                placeholder="John"
                                class="form-input" 
                                required
                            >
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Last Name</label>
                            <input 
                                type="text" 
                                id="last-name" 
                                name="last_name"
                                placeholder="Doe"
                                class="form-input"
                            >
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Email Address</label>
                        <div class="relative">
                            <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                            <input 
                                type="email" 
                                id="signup-email" 
                                name="email" 
                                placeholder="name@example.com"
                                class="form-input pl-12" 
                                required
                            >
                        </div>
                        <span class="error-message" id="signup-email-error"></span>
                    </div>

                    <!-- Phone -->
                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Phone Number</label>
                        <div class="relative">
                            <i data-lucide="phone" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                            <input 
                                type="tel" 
                                id="signup-phone" 
                                name="phone"
                                placeholder="+91 98765 43210"
                                class="form-input pl-12"
                            >
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Password</label>
                        <div class="relative">
                            <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                            <input 
                                type="password" 
                                id="signup-password" 
                                name="password" 
                                placeholder="Min. 8 characters"
                                class="form-input pl-12 pr-12" 
                                required
                                minlength="8"
                            >
                            <button 
                                type="button"
                                onclick="togglePasswordVisibility('signup-password', 'password-toggle-icon')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                            >
                                <i data-lucide="eye" id="password-toggle-icon" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">Must be at least 8 characters long</p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Confirm Password</label>
                        <div class="relative">
                            <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                            <input 
                                type="password" 
                                id="confirm-password" 
                                name="confirm_password" 
                                placeholder="Repeat your password"
                                class="form-input pl-12 pr-12" 
                                required
                            >
                            <button 
                                type="button"
                                onclick="togglePasswordVisibility('confirm-password', 'confirm-toggle-icon')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                            >
                                <i data-lucide="eye" id="confirm-toggle-icon" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <span class="error-message" id="confirm-password-error"></span>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="flex items-start gap-2.5 sm:gap-3 ml-1">
                        <input 
                            id="terms" 
                            type="checkbox" 
                            required
                            class="w-4 h-4 rounded text-brand focus:ring-brand mt-0.5 flex-shrink-0"
                        >
                        <label for="terms" class="text-xs sm:text-sm font-medium text-slate-500 cursor-pointer leading-relaxed">
                            I agree to the <a href="terms.php" class="text-brand font-black hover:underline">Terms & Conditions</a> and <a href="privacy.php" class="text-brand font-black hover:underline">Privacy Policy</a>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        id="signup-btn"
                        class="w-full bg-slate-900 text-white py-3 sm:py-3.5 md:py-4 rounded-xl sm:rounded-2xl font-black text-sm sm:text-base md:text-lg shadow-xl hover:bg-brand transition-all"
                    >
                        <span id="signup-btn-text">CREATE ACCOUNT</span>
                    </button>
                </form>

                <div class="mt-5 sm:mt-6 md:mt-8">
                    <div class="relative flex items-center justify-center mb-6 sm:mb-8">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-slate-100"></div>
                        </div>
                        <span class="relative px-4 bg-white text-[10px] font-black text-slate-300 uppercase tracking-[0.2em]">Or continue with</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:gap-4 mb-6 sm:mb-0">
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

                <p class="mt-6 sm:mt-8 md:mt-10 text-center text-xs sm:text-sm font-medium text-slate-400">
                    Already have an account? 
                    <a href="login.php" class="text-brand font-black hover:underline underline-offset-4 ml-1">SIGN IN</a>
                </p>
            </div>

        </div>
    </div>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // API Base URL
        const API_BASE = 'api/v1';

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

        // Handle Signup Form Submission
        async function handleSignup(event) {
            event.preventDefault();
            
            const firstName = document.getElementById('first-name').value.trim();
            const lastName = document.getElementById('last-name').value.trim();
            const email = document.getElementById('signup-email').value.trim().toLowerCase();
            const phone = document.getElementById('signup-phone').value.trim();
            const password = document.getElementById('signup-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const termsAccepted = document.getElementById('terms').checked;
            
            // Validation
            if (!firstName) {
                return;
            }
            
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                return;
            }
            
            if (password.length < 8) {
                return;
            }
            
            if (password !== confirmPassword) {
                document.getElementById('confirm-password-error').textContent = 'Passwords do not match';
                document.getElementById('confirm-password-error').classList.add('show');
                return;
            }
            
            if (!termsAccepted) {
                return;
            }
            
            const submitBtn = document.getElementById('signup-btn');
            const btnText = document.getElementById('signup-btn-text');
            const originalText = btnText.textContent;
            submitBtn.disabled = true;
            btnText.textContent = 'Creating account...';
            
            try {
                const response = await fetch(`${API_BASE}/auth.php`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'register',
                        first_name: firstName,
                        last_name: lastName,
                        email: email,
                        phone: phone,
                        password: password
                    })
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    submitBtn.disabled = false;
                    btnText.textContent = originalText;
                    return;
                }

                setTimeout(() => {
                    const urlParams = new URLSearchParams(window.location.search);
                    const returnUrl = urlParams.get('return');
                    if (returnUrl && !returnUrl.includes('login.php') && !returnUrl.includes('signup.php')) {
                        window.location.href = decodeURIComponent(returnUrl);
                    } else {
                        window.location.href = 'index.php';
                    }
                }, 500);

            } catch (error) {
                console.error('Signup error:', error);
                submitBtn.disabled = false;
                btnText.textContent = originalText;
            }
        }

        // Check if user is already logged in
        async function checkExistingSession() {
            try {
                const response = await fetch(`${API_BASE}/auth.php`, {
                    method: 'GET',
                    credentials: 'include'
                });
                const result = await response.json();
                
                if (result.success && result.authenticated) {
                    const urlParams = new URLSearchParams(window.location.search);
                    const returnUrl = urlParams.get('return') || 'index.php';
                    window.location.href = decodeURIComponent(returnUrl);
                }
            } catch (error) {
                console.error('Session check error:', error);
            }
        }

        // Check session on page load
        document.addEventListener('DOMContentLoaded', async () => {
            await checkExistingSession();
        });
    </script>
</body>
</html>
