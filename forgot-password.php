<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Nazmi Boutique</title>
    
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

        input, select, textarea {
            font-size: 16px !important;
        }

        /* Full Screen Success Message */
        .success-screen {
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

        .success-screen.show {
            display: flex;
        }

        .success-card {
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

        .success-content {
            padding: 4rem 3rem;
            text-align: center;
        }

        .success-icon-wrapper {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            background: #d1fae5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.5s ease-out 0.2s both;
        }

        .success-icon-wrapper i {
            width: 64px;
            height: 64px;
            color: #059669;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .success-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 1rem;
        }

        .success-message {
            color: #4b5563;
            font-size: 1.125rem;
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .success-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .success-btn {
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

        .success-btn-primary {
            background: #111827;
            color: white;
        }

        .success-btn-primary:hover {
            background: var(--brand-color);
        }

        /* Hide form when success is shown */
        .login-container.hide {
            display: none;
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
                    <h2 class="text-4xl font-serif font-black mb-4 leading-tight">Reset Your Password <br/> Securely.</h2>
                    <p class="text-white/80 font-medium">We'll help you get back to your account in no time.</p>
                </div>
                <div class="absolute top-12 left-12">
                    <h1 class="text-3xl font-serif font-black tracking-tighter text-white">
                        NAZMI<span class="text-brand">.</span>
                    </h1>
                </div>
            </div>

            <!-- Right Side: Forgot Password Form -->
            <div class="login-form-side">
                <div class="mb-10 md:hidden text-center">
                    <h1 class="text-3xl font-serif font-black tracking-tighter text-slate-900">
                        NAZMI<span class="text-brand">.</span>
                    </h1>
                </div>

                <div class="mb-10">
                    <h2 class="text-3xl font-serif font-black text-slate-900 mb-2">Forgot Password?</h2>
                    <p class="text-slate-400 font-medium">No worries! Enter your email address and we'll send you a link to reset your password.</p>
                </div>

                <!-- Illustration -->
                <div class="mb-8 flex justify-center">
                    <div class="w-32 h-32 bg-brand/10 rounded-full flex items-center justify-center">
                        <i data-lucide="mail" class="w-16 h-16 text-brand opacity-60"></i>
                    </div>
                </div>

                <form id="forgot-password-form" onsubmit="handleForgotPassword(event)" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Email Address</label>
                        <div class="relative">
                            <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                            <input 
                                type="email" 
                                id="forgot-email" 
                                name="email" 
                                placeholder="name@example.com"
                                class="form-input pl-12" 
                                required
                                onblur="validateEmailField(this)"
                                oninput="clearFieldError(this)"
                            >
                        </div>
                        <span class="error-message" id="forgot-email-error"></span>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black text-lg shadow-xl hover:bg-brand transition-all"
                    >
                        SEND RESET LINK
                    </button>
                </form>

                <p class="mt-10 text-center text-sm font-medium text-slate-400">
                    Remember your password? 
                    <a href="login.php" class="text-brand font-black hover:underline underline-offset-4 ml-1">SIGN IN</a>
                </p>
            </div>

        </div>
    </div>

    <!-- Full Screen Success Message -->
    <div id="successScreen" class="success-screen">
        <div class="success-card">
            <div class="success-content">
                <div class="success-icon-wrapper">
                    <i data-lucide="check-circle"></i>
                </div>
                <h2 id="successTitle" class="success-title">Email Sent Successfully!</h2>
                <p id="successMessage" class="success-message">
                    If an account with that email exists, a password reset link has been sent.<br>
                    Please check your inbox and spam folder.
                </p>
                <div class="success-actions">
                    <button onclick="goToLogin()" class="success-btn success-btn-primary">
                        <i data-lucide="arrow-right"></i>
                        Go to Login
                    </button>
                    <button onclick="tryAgain()" class="success-btn" style="background: #f1f5f9; color: #475569;">
                        <i data-lucide="refresh-cw"></i>
                        Send Another Email
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Full Screen Success Functions
        function showSuccessScreen(message = null) {
            const successScreen = document.getElementById('successScreen');
            const loginContainer = document.querySelector('.login-container');
            const successMessage = document.getElementById('successMessage');
            
            // Hide the form
            if (loginContainer) {
                loginContainer.classList.add('hide');
            }
            
            // Set custom message if provided
            if (message) {
                successMessage.innerHTML = message.replace(/\n/g, '<br>');
            }
            
            // Show success screen
            successScreen.classList.add('show');
            
            // Reinitialize icons
            lucide.createIcons();
        }

        function hideSuccessScreen() {
            const successScreen = document.getElementById('successScreen');
            const loginContainer = document.querySelector('.login-container');
            
            successScreen.classList.remove('show');
            
            // Show the form again
            if (loginContainer) {
                loginContainer.classList.remove('hide');
            }
        }

        function goToLogin() {
            window.location.href = 'login.php';
        }

        function tryAgain() {
            hideSuccessScreen();
            // Reset form
            const form = document.getElementById('forgot-password-form');
            if (form) {
                form.reset();
            }
            // Focus on email input
            const emailInput = document.getElementById('forgot-email');
            if (emailInput) {
                emailInput.focus();
            }
        }

        // Simple error alert function (for errors only)
        function showError(message) {
            alert(message);
        }

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

        // Handle Forgot Password Form Submission
        async function handleForgotPassword(event) {
            event.preventDefault();
            
            const emailInput = document.getElementById('forgot-email');
            
            if (!validateEmailField(emailInput)) {
                return;
            }
            
            const email = emailInput.value.trim();
            
            if (!email) {
                return;
            }
            
            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin inline-block"></i> Sending...';
            lucide.createIcons();
            
            try {
                console.log('Sending forgot password request for:', email);
                
                const response = await fetch('/api/v1/auth/forgot-password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email: email })
                });
                
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);
                
                // Check if response is ok
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('API Error Response:', errorText);
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Try to parse JSON
                let result;
                try {
                    const responseText = await response.text();
                    console.log('Response text:', responseText);
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    throw new Error('Invalid response from server');
                }
                
                console.log('Parsed result:', result);
                
                if (result.success) {
                    // Show full-screen success message
                    const message = result.message || 'If an account with that email exists, a password reset link has been sent. Please check your inbox and spam folder.';
                    showSuccessScreen(message);
                    
                    // Reset button state
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    lucide.createIcons();
                } else {
                    // Show error message (keep form visible for retry)
                    showError(result.message || 'Failed to send reset link. Please try again.');
                    
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    lucide.createIcons();
                }
            } catch (error) {
                console.error('Forgot password error:', error);
                console.error('Error details:', error.message, error.stack);
                
                // Show error message (keep form visible)
                alert('An error occurred. Please try again later. If the problem persists, contact support.');
                
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                lucide.createIcons();
            }
        }
    </script>
</body>
</html>
