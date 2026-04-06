<?php
/**
 * Admin Index - Redirects to Dashboard
 * This file handles the /admin/ route
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
$isAdminLoggedIn = isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);

if ($isAdminLoggedIn) {
    // Redirect to admin dashboard
    header('Location: dashboard.php');
    exit;
} else {
    // Redirect to login page
    header('Location: ../login.php?message=admin_required');
    exit;
}
