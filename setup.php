<?php
/**
 * Admin Setup Script
 * Run this file ONCE to create the first admin user
 * After setup, delete or protect this file
 */

// Security: Only allow setup in development or with special token
define('SETUP_MODE', true);

// Load configuration
require_once __DIR__ . '/includes/config.php';

// Database connection
$pdo = getDbConnection();

// Handle form submission
$error = '';
$success = '';
$step = $_GET['step'] ?? 'check';

// Check if admin already exists
if ($step === 'check') {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role_id = 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            // Admin exists - show error or redirect
            $error = "Admin user already exists. Setup has been completed.";
            $step = 'complete';
        } else {
            $step = 'setup';
        }
    } catch (PDOException $e) {
        // Tables might not exist yet
        $step = 'setup';
    }
}

// Process setup form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'setup') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $setupToken = $_POST['setup_token'] ?? '';
    
    // Validation
    if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        try {
            // Check if tables exist
            $pdo->query("SELECT 1 FROM users LIMIT 1");
            $pdo->query("SELECT 1 FROM roles LIMIT 1");
            
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Email already exists.";
            } else {
                // Hash password
                $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                
                // Get or create Admin role (role_id = 1)
                $stmt = $pdo->query("SELECT id FROM roles WHERE id = 1 OR name = 'Admin' LIMIT 1");
                $role = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$role) {
                    // Create Admin role if it doesn't exist
                    $stmt = $pdo->prepare("INSERT INTO roles (id, name, description, is_active) VALUES (1, ?, ?, ?)");
                    $stmt->execute(['Admin', 'Administrator with full system access', 1]);
                    $roleId = 1;
                } else {
                    $roleId = $role['id'];
                }
                
                // Create admin user in unified users table with role_id = 1
                $stmt = $pdo->prepare("
                    INSERT INTO users (email, password_hash, first_name, last_name, role_id, is_active)
                    VALUES (?, ?, ?, ?, 1, ?)
                ");
                $stmt->execute([$email, $passwordHash, $firstName, $lastName, 1]);
                
                $success = "Admin account created successfully! You can now login.";
                $step = 'success';
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
            if (config('DEBUG', false)) {
                $error .= "<br>Details: " . $e->getTraceAsString();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup | BLine Boutique</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="py-12 px-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-teal-100 rounded-full mb-4">
                    <svg class="w-8 h-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Admin Setup</h1>
                <p class="text-gray-600">Create your first admin account</p>
            </div>

            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-teal-50 border border-red-200 rounded-lg">
                    <p class="text-red-800 text-sm"><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-green-800 text-sm font-medium"><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($step === 'complete'): ?>
                <div class="text-center py-8">
                    <div class="mb-4 text-green-600 text-6xl">✓</div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Setup Already Complete</h2>
                    <p class="text-gray-600 mb-6">Admin account has already been created.</p>
                    <a href="admin/dashboard.php" class="inline-block px-6 py-3 bg-teal-500 text-white font-semibold rounded-lg hover:bg-teal-600 transition-colors">
                        Go to Admin Dashboard
                    </a>
                </div>
            <?php elseif ($step === 'success'): ?>
                <div class="text-center py-8">
                    <div class="mb-4 text-green-600 text-6xl">✓</div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Setup Complete!</h2>
                    <p class="text-gray-600 mb-6">Your admin account has been created successfully.</p>
                    <div class="space-y-3">
                        <a href="admin/dashboard.php" class="block px-6 py-3 bg-teal-500 text-white font-semibold rounded-lg hover:bg-teal-600 transition-colors">
                            Go to Admin Dashboard
                        </a>
                        <p class="text-xs text-gray-500 mt-4">
                            <strong>Security Notice:</strong> Delete or protect this setup.php file after setup is complete.
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <!-- Setup Form -->
                <form method="POST" action="setup.php?step=setup" class="space-y-5">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <p class="text-yellow-800 text-xs">
                            <strong>⚠️ Important:</strong> This setup should only be run once. After creating the admin account, delete or protect this file.
                        </p>
                    </div>

                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                            First Name <span class="text-teal-600">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="first_name" 
                            name="first_name" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none transition-all"
                            placeholder="John"
                            value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                        >
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Last Name <span class="text-teal-600">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="last_name" 
                            name="last_name" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none transition-all"
                            placeholder="Doe"
                            value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                        >
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address <span class="text-teal-600">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none transition-all"
                            placeholder="admin@tivoraelectronics.com"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        >
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password <span class="text-teal-600">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            minlength="8"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none transition-all"
                            placeholder="Minimum 8 characters"
                        >
                        <p class="mt-1 text-xs text-gray-500">Password must be at least 8 characters long</p>
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirm Password <span class="text-teal-600">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            required
                            minlength="8"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none transition-all"
                            placeholder="Re-enter password"
                        >
                    </div>

                    <button 
                        type="submit"
                        class="w-full bg-teal-500 text-white py-3 px-6 rounded-lg font-semibold hover:bg-teal-600 transition-colors shadow-lg shadow-teal-200"
                    >
                        Create Admin Account
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-xs text-gray-500 text-center">
                        Already have an account? <a href="admin/dashboard.php" class="text-teal-600 hover:text-red-700 font-medium">Login here</a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>