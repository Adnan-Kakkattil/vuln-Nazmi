-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 06, 2026 at 05:23 AM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u324921317_qaadmin`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_log`
--

CREATE TABLE `admin_activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'User who performed the action (admin users only)',
  `action` varchar(100) NOT NULL COMMENT 'create, update, delete, view',
  `module` varchar(50) NOT NULL COMMENT 'order, product, user, etc.',
  `entity_id` int(11) DEFAULT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Before/after values as JSON' CHECK (json_valid(`changes`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_activity_log`
--

INSERT INTO `admin_activity_log` (`id`, `user_id`, `action`, `module`, `entity_id`, `entity_type`, `changes`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 5, 'update', 'settings', NULL, 'system_settings', '{\"updated_keys\":[\"tax_enabled\",\"tax_inclusive\",\"payment_online_enabled\",\"payment_cod_enabled\",\"auth_login_enabled\",\"auth_signup_enabled\",\"auth_forgot_password_enabled\",\"auth_guest_checkout_enabled\",\"email_service_enabled\",\"email_notifications_enabled\",\"email_order_confirmation\",\"email_shipping_updates\",\"email_welcome_email\",\"tax_rate\",\"razorpay_key\",\"razorpay_secret\",\"smtp_host\",\"smtp_port\",\"smtp_username\",\"smtp_encryption\",\"email_from_name\",\"email_from_address\"]}', NULL, NULL, '2026-02-03 13:33:31'),
(2, 5, 'update', 'settings', NULL, 'system_settings', '{\"updated_keys\":[\"tax_enabled\",\"tax_inclusive\",\"payment_online_enabled\",\"payment_cod_enabled\",\"auth_login_enabled\",\"auth_signup_enabled\",\"auth_forgot_password_enabled\",\"auth_guest_checkout_enabled\",\"email_service_enabled\",\"email_notifications_enabled\",\"email_order_confirmation\",\"email_shipping_updates\",\"email_welcome_email\",\"tax_rate\",\"razorpay_key\",\"razorpay_secret\",\"smtp_host\",\"smtp_port\",\"smtp_username\",\"smtp_encryption\",\"email_from_name\",\"email_from_address\"]}', NULL, NULL, '2026-02-03 13:33:46'),
(3, 5, 'update', 'settings', NULL, 'system_settings', '{\"updated_keys\":[\"tax_enabled\",\"tax_inclusive\",\"payment_online_enabled\",\"payment_cod_enabled\",\"auth_login_enabled\",\"auth_signup_enabled\",\"auth_forgot_password_enabled\",\"auth_guest_checkout_enabled\",\"email_service_enabled\",\"email_notifications_enabled\",\"email_order_confirmation\",\"email_shipping_updates\",\"email_welcome_email\",\"tax_rate\",\"razorpay_key\",\"razorpay_secret\",\"smtp_host\",\"smtp_port\",\"smtp_username\",\"smtp_password\",\"smtp_encryption\",\"email_from_name\",\"email_from_address\"]}', NULL, NULL, '2026-02-03 14:00:13'),
(4, 5, 'logout', 'auth', NULL, NULL, NULL, '117.196.23.84', NULL, '2026-02-03 14:20:11'),
(5, 5, 'logout', 'auth', NULL, NULL, NULL, '117.196.23.84', NULL, '2026-02-03 14:24:36'),
(6, 5, 'logout', 'auth', NULL, NULL, NULL, '117.196.23.84', NULL, '2026-02-03 14:28:29'),
(7, 5, 'create', 'admin_users', 6, 'admin_user', '{\"email\":\"admin@nazmiboutique.com\",\"role_id\":1}', '115.242.173.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '2026-02-03 15:10:15'),
(8, 5, 'logout', 'auth', NULL, NULL, NULL, '115.242.173.162', NULL, '2026-02-03 15:10:21'),
(9, 6, 'create', 'products', 1, 'product', NULL, NULL, NULL, '2026-02-03 16:31:30'),
(10, 6, 'update', 'orders', 3, 'order', NULL, NULL, NULL, '2026-02-05 17:07:21'),
(11, 6, 'update', 'orders', 3, 'order', NULL, NULL, NULL, '2026-02-05 17:11:48'),
(12, 6, 'update', 'orders', 3, 'order', NULL, NULL, NULL, '2026-02-05 17:12:17'),
(13, 6, 'create', 'products', 2, 'product', NULL, NULL, NULL, '2026-02-15 16:08:06');

-- --------------------------------------------------------

--
-- Table structure for table `b2b_customers`
--

CREATE TABLE `b2b_customers` (
  `id` int(11) NOT NULL,
  `b2b_request_id` int(11) DEFAULT NULL,
  `company_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `gst_number` varchar(50) DEFAULT NULL,
  `credit_limit` decimal(12,2) DEFAULT 0.00,
  `payment_terms` varchar(100) DEFAULT NULL COMMENT 'Net 30, Net 60, etc.',
  `discount_percentage` decimal(5,2) DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'active',
  `assigned_to` int(11) DEFAULT NULL COMMENT 'Account manager',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `b2b_requests`
--

CREATE TABLE `b2b_requests` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `business_type` varchar(100) DEFAULT NULL COMMENT 'retailer, distributor, corporate, etc.',
  `gst_number` varchar(50) DEFAULT NULL,
  `address_line1` varchar(255) DEFAULT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'India',
  `monthly_volume_estimate` varchar(100) DEFAULT NULL COMMENT '1-10, 11-50, 50+',
  `product_categories` text DEFAULT NULL COMMENT 'JSON or comma-separated',
  `special_requirements` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending' COMMENT 'pending, under_review, approved, rejected',
  `notes` text DEFAULT NULL COMMENT 'Admin notes',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL COMMENT 'For guest users',
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL COMMENT 'Price at time of adding (snapshot)',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `session_id`, `product_id`, `variant_id`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
(1, NULL, '7mhgbugneqte0el87o447acffl', 1, NULL, 1, 499.00, '2026-02-04 10:43:39', '2026-02-04 10:43:39'),
(2, NULL, 'kh1aqjm3u1d0bjkbp8a43lrljc', 1, NULL, 1, 499.00, '2026-02-04 11:13:55', '2026-02-04 11:13:55'),
(3, NULL, 'a7pfgl1bav2bgfmsfmh6v2lver', 1, NULL, 1, 499.00, '2026-02-05 16:46:04', '2026-02-05 16:46:04'),
(9, NULL, '3etk4tbv3qacaqhu7nk80tv5su', 1, NULL, 1, 499.00, '2026-02-14 02:44:08', '2026-02-14 02:44:08'),
(14, NULL, 'ejdd1ueubopfe96mbaabsvtroj', 1, NULL, 1, 499.00, '2026-02-19 14:07:55', '2026-02-19 14:07:55'),
(22, NULL, 'btf4vuiip0naieeanb72j55se6', 2, NULL, 1, 100.00, '2026-02-21 14:07:58', '2026-02-21 14:07:58');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL COMMENT 'For subcategories',
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `parent_id`, `image`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Women\'s Fashion', 'womens-fashion', 'Elegant women\'s clothing and accessories', NULL, NULL, 0, 1, '2026-02-03 08:18:58', '2026-02-03 08:18:58'),
(2, 'Men\'s Fashion', 'mens-fashion', 'Stylish men\'s apparel and accessories', NULL, NULL, 0, 1, '2026-02-03 08:18:58', '2026-02-03 08:18:58'),
(3, 'Accessories', 'accessories', 'Fashion accessories and jewelry', NULL, NULL, 0, 1, '2026-02-03 08:18:58', '2026-02-03 08:18:58'),
(4, 'Ethnic Wear', 'ethnic-wear', 'Traditional and ethnic clothing', NULL, NULL, 0, 1, '2026-02-03 08:18:58', '2026-02-03 08:18:58'),
(5, 'Western Wear', 'western-wear', 'Modern western clothing', NULL, NULL, 0, 1, '2026-02-03 08:18:58', '2026-02-03 08:18:58'),
(6, 'Lehengas', 'lehengas', 'Beautiful lehenga collections', NULL, NULL, 0, 1, '2026-02-03 08:18:58', '2026-02-03 08:18:58'),
(7, 'Party Wear', 'party-wear', 'Party and occasion wear', NULL, NULL, 0, 1, '2026-02-03 08:18:58', '2026-02-03 08:18:58');

-- --------------------------------------------------------

--
-- Table structure for table `contact_requests`
--

CREATE TABLE `contact_requests` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` varchar(50) DEFAULT 'new' COMMENT 'new, read, replied, resolved',
  `replied_by` int(11) DEFAULT NULL,
  `replied_at` timestamp NULL DEFAULT NULL,
  `reply_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` varchar(50) NOT NULL COMMENT 'percentage, fixed_amount',
  `discount_value` decimal(10,2) NOT NULL,
  `minimum_purchase_amount` decimal(10,2) DEFAULT 0.00,
  `maximum_discount_amount` decimal(10,2) DEFAULT NULL COMMENT 'For percentage discounts',
  `usage_limit` int(11) DEFAULT NULL COMMENT 'Total usage limit (NULL = unlimited)',
  `usage_limit_per_user` int(11) DEFAULT 1,
  `valid_from` timestamp NOT NULL,
  `valid_until` timestamp NOT NULL,
  `applicable_to` varchar(50) DEFAULT 'all' COMMENT 'all, category, product',
  `applicable_category_id` int(11) DEFAULT NULL,
  `applicable_product_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of product IDs as JSON' CHECK (json_valid(`applicable_product_ids`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupon_usage`
--

CREATE TABLE `coupon_usage` (
  `id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'NULL for guest',
  `discount_amount` decimal(10,2) NOT NULL,
  `used_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `receipt_url` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expense_categories`
--

CREATE TABLE `expense_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `financial_transactions`
--

CREATE TABLE `financial_transactions` (
  `id` int(11) NOT NULL,
  `transaction_type` varchar(50) NOT NULL COMMENT 'sale, refund, expense, purchase',
  `reference_type` varchar(50) DEFAULT NULL COMMENT 'order, purchase_order, expense, etc.',
  `reference_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'INR',
  `description` text DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `low_stock_products`
-- (See below for the actual view)
--
CREATE TABLE `low_stock_products` (
`id` int(11)
,`sku` varchar(100)
,`name` varchar(255)
,`stock_quantity` int(11)
,`low_stock_threshold` int(11)
,`is_low_stock` int(1)
);

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscriptions`
--

CREATE TABLE `newsletter_subscriptions` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `subscribed_at` timestamp NULL DEFAULT current_timestamp(),
  `unsubscribed_at` timestamp NULL DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL COMMENT 'checkout, footer, popup, etc.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL COMMENT 'ORD-1234567890',
  `user_id` int(11) DEFAULT NULL COMMENT 'NULL for guest orders',
  `guest_email` varchar(255) DEFAULT NULL COMMENT 'For guest checkout',
  `guest_name` varchar(255) DEFAULT NULL COMMENT 'For guest checkout',
  `shipping_address_id` int(11) DEFAULT NULL,
  `shipping_first_name` varchar(100) NOT NULL,
  `shipping_last_name` varchar(100) NOT NULL,
  `shipping_address_line1` varchar(255) NOT NULL,
  `shipping_address_line2` varchar(255) DEFAULT NULL,
  `shipping_city` varchar(100) NOT NULL,
  `shipping_state` varchar(100) NOT NULL,
  `shipping_pincode` varchar(10) NOT NULL,
  `shipping_country` varchar(100) DEFAULT 'India',
  `shipping_phone` varchar(20) NOT NULL,
  `billing_address_line1` varchar(255) DEFAULT NULL,
  `billing_address_line2` varchar(255) DEFAULT NULL,
  `billing_city` varchar(100) DEFAULT NULL,
  `billing_state` varchar(100) DEFAULT NULL,
  `billing_pincode` varchar(10) DEFAULT NULL,
  `billing_country` varchar(100) DEFAULT NULL,
  `shipping_method_id` int(11) DEFAULT NULL,
  `shipping_cost` decimal(10,2) DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 18.00 COMMENT 'GST %',
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL COMMENT 'online, cod, upi',
  `payment_status` varchar(50) DEFAULT 'pending' COMMENT 'pending, processing, completed, failed, refunded',
  `payment_transaction_id` varchar(255) DEFAULT NULL,
  `payment_gateway_response` text DEFAULT NULL COMMENT 'JSON response from gateway',
  `status` varchar(50) DEFAULT 'confirmed' COMMENT 'confirmed, processing, shipped, delivered, cancelled, returned',
  `status_notes` text DEFAULT NULL,
  `order_date` timestamp NULL DEFAULT current_timestamp(),
  `confirmed_date` timestamp NULL DEFAULT NULL,
  `processing_date` timestamp NULL DEFAULT NULL,
  `shipped_date` timestamp NULL DEFAULT NULL,
  `delivered_date` timestamp NULL DEFAULT NULL,
  `cancelled_date` timestamp NULL DEFAULT NULL,
  `cancelled_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `internal_notes` text DEFAULT NULL COMMENT 'Admin notes',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `guest_email`, `guest_name`, `shipping_address_id`, `shipping_first_name`, `shipping_last_name`, `shipping_address_line1`, `shipping_address_line2`, `shipping_city`, `shipping_state`, `shipping_pincode`, `shipping_country`, `shipping_phone`, `billing_address_line1`, `billing_address_line2`, `billing_city`, `billing_state`, `billing_pincode`, `billing_country`, `shipping_method_id`, `shipping_cost`, `subtotal`, `discount_amount`, `tax_amount`, `tax_rate`, `total_amount`, `payment_method`, `payment_status`, `payment_transaction_id`, `payment_gateway_response`, `status`, `status_notes`, `order_date`, `confirmed_date`, `processing_date`, `shipped_date`, `delivered_date`, `cancelled_date`, `cancelled_reason`, `notes`, `internal_notes`, `created_at`, `updated_at`) VALUES
(1, 'ORD-20260205221644-3397', 6, 'admin@nazmiboutique.com', 'Nazmi Admin', NULL, 'Nazmi', 'Admin', 'Cyber park Sahya', '', 'Calicut', 'Kerala', '643205', 'India', '9744466737', NULL, NULL, NULL, NULL, NULL, NULL, 1, 0.00, 499.00, 0.00, 89.82, 18.00, 588.82, 'cod', 'pending', NULL, NULL, 'confirmed', NULL, '2026-02-05 16:46:44', '2026-02-05 16:46:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-05 16:46:44', '2026-02-05 16:46:44'),
(2, 'ORD-20260205222529-7281', 6, 'admin@nazmiboutique.com', 'Nazmi Admin', NULL, 'Nazmi', 'Admin', 'Cyber park Sahya', '', 'Calicut', 'Kerala', '643205', 'India', '9744466737', NULL, NULL, NULL, NULL, NULL, NULL, 1, 0.00, 499.00, 0.00, 89.82, 18.00, 588.82, 'cod', 'pending', NULL, NULL, 'confirmed', NULL, '2026-02-05 16:55:29', '2026-02-05 16:55:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-05 16:55:29', '2026-02-05 16:55:29'),
(3, 'ORD-20260205223249-1763', 4, 'contact.adnanks@gmail.com', 'Adnan K S', NULL, 'Adnan', 'K S', 'Cyber park Sahya', '', 'Calicut', 'Kerala', '673016', 'India', '9744466737', NULL, NULL, NULL, NULL, NULL, NULL, 1, 0.00, 499.00, 0.00, 89.82, 18.00, 588.82, 'cod', 'completed', NULL, NULL, 'delivered', NULL, '2026-02-05 17:02:48', '2026-02-05 17:02:48', '2026-02-05 17:07:21', NULL, '2026-02-05 17:12:17', NULL, NULL, NULL, NULL, '2026-02-05 17:02:48', '2026-02-05 17:12:17'),
(4, 'ORD-20260205225202-6585', 6, 'admin@nazmiboutique.com', 'Nazmi Admin', NULL, 'Nazmi', 'Admin', 'Cyber park Sahya', '', 'Calicut', 'Kerala', '673016', 'India', '9744466737', NULL, NULL, NULL, NULL, NULL, NULL, 1, 0.00, 499.00, 0.00, 89.82, 18.00, 588.82, 'cod', 'pending', NULL, NULL, 'confirmed', NULL, '2026-02-05 17:22:01', '2026-02-05 17:22:01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-05 17:22:01', '2026-02-05 17:22:01'),
(5, 'ORD-20260205230711-7446', 6, 'admin@nazmiboutique.com', 'Nazmi Admin', NULL, 'Nazmi', 'Admin', 'Cyber park Sahya', '', 'Calicut', 'Kerala', '673016', 'India', '9744466737', NULL, NULL, NULL, NULL, NULL, NULL, 1, 0.00, 499.00, 0.00, 89.82, 18.00, 588.82, 'cod', 'pending', NULL, NULL, 'confirmed', NULL, '2026-02-05 17:37:11', '2026-02-05 17:37:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-05 17:37:11', '2026-02-05 17:37:11'),
(6, 'ORD-20260219192537-8960', 7, 'alihamdaneckoduvally5@gmail.com', 'Ali Ec', NULL, 'Ali', 'Ec', 'KODUVALLY,KODUVALLY PO', '', 'KODUVALLY', 'Kerala', '673572', 'India', '9745121739', NULL, NULL, NULL, NULL, NULL, NULL, 1, 0.00, 499.00, 0.00, 89.82, 18.00, 588.82, 'cod', 'pending', NULL, NULL, 'confirmed', NULL, '2026-02-19 13:55:37', '2026-02-19 13:55:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-19 13:55:37', '2026-02-19 13:55:37'),
(7, 'ORD-20260219204438-2986', 1, 'contact.adnanks@gmail.com', 'Adnan Kakkattil', NULL, 'Adnan', 'Kakkattil', 'Kakkattil House mannathivayal kayunni POST 643205 kayunni POST 643205', '', 'Chermabadi', 'Punjab', '643205', 'India', '9488766222', NULL, NULL, NULL, NULL, NULL, NULL, 1, 0.00, 499.00, 0.00, 89.82, 18.00, 588.82, 'cod', 'pending', NULL, NULL, 'confirmed', NULL, '2026-02-19 15:14:38', '2026-02-19 15:14:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-19 15:14:38', '2026-02-19 15:14:38'),
(8, 'ORD-20260219204639-8539', 1, 'contact.adnanks@gmail.com', 'RoomNumber404 Kakkattil', NULL, 'RoomNumber404', 'Kakkattil', 'Kochi', '', 'Kochi', 'Kerala', '643205', 'India', '9488766222', NULL, NULL, NULL, NULL, NULL, NULL, 2, 450.00, 499.00, 0.00, 89.82, 18.00, 1038.82, 'cod', 'pending', NULL, NULL, 'confirmed', NULL, '2026-02-19 15:16:39', '2026-02-19 15:16:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-19 15:16:39', '2026-02-19 15:16:39'),
(9, 'ORD-20260219205352-4911', 8, 'ashlin@nomadscipher.com', 'Adnan Kakkattil', NULL, 'Adnan', 'Kakkattil', 'Kakkattil House mannathivayal kayunni POST 643205 kayunni POST 643205', '', 'Chermabadi', 'Tamil Nadu', '643205', 'India', '9948876622', NULL, NULL, NULL, NULL, NULL, NULL, 1, 0.00, 499.00, 0.00, 89.82, 18.00, 588.82, 'cod', 'pending', NULL, NULL, 'confirmed', NULL, '2026-02-19 15:23:53', '2026-02-19 15:23:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-19 15:23:53', '2026-02-19 15:23:53'),
(10, 'ORD-20260219205734-1169', 8, 'ashlin@nomadscipher.com', 'RoomNumber404 Ziyad', NULL, 'RoomNumber404', 'Ziyad', 'Kochi', '', 'Kochi', 'Kerala', '643205', 'India', '9948876622', NULL, NULL, NULL, NULL, NULL, NULL, 1, 0.00, 499.00, 0.00, 89.82, 18.00, 588.82, 'cod', 'pending', NULL, NULL, 'confirmed', NULL, '2026-02-19 15:27:35', '2026-02-19 15:27:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-19 15:27:35', '2026-02-19 15:27:35'),
(11, 'ORD-20260219210153-0623', 8, 'ashlin@nomadscipher.com', 'Adnan Ziyad', NULL, 'Adnan', 'Ziyad', 'Kakkattil House mannathivayal kayunni POST 643205', '', 'Chermabadi', 'Tamil Nadu', '643205', 'India', '9987654231', NULL, NULL, NULL, NULL, NULL, NULL, 2, 450.00, 499.00, 0.00, 4.99, 1.00, 953.99, 'cod', 'pending', NULL, NULL, 'confirmed', NULL, '2026-02-19 15:31:54', '2026-02-19 15:31:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-19 15:31:54', '2026-02-19 15:31:54'),
(12, 'ORD-20260220060701-9669', 6, 'admin@nazmiboutique.com', 'Nazmi Admin', NULL, 'Nazmi', 'Admin', 'sheebothi house valat post wayanad', '', 'manathavady', 'Kerala', '670644', 'India', '7510731406', NULL, NULL, NULL, NULL, NULL, NULL, 1, 0.00, 100.00, 0.00, 1.00, 1.00, 101.00, 'cod', 'pending', NULL, NULL, 'confirmed', NULL, '2026-02-20 00:37:01', '2026-02-20 00:37:01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-20 00:37:01', '2026-02-20 00:37:01'),
(13, 'ORD-20260220063934-0529', 6, 'admin@nazmiboutique.com', 'Nazmi Admin', NULL, 'Nazmi', 'Admin', 'sheebothi house valat post wayanad', '', 'manathavady', 'Kerala', '670644', 'India', '7510731406', NULL, NULL, NULL, NULL, NULL, NULL, 2, 450.00, 100.00, 0.00, 1.00, 1.00, 551.00, 'cod', 'pending', NULL, NULL, 'cancelled', NULL, '2026-02-20 01:09:34', '2026-02-20 01:09:34', NULL, NULL, NULL, '2026-02-21 14:11:09', 'Cancelled by customer', NULL, NULL, '2026-02-20 01:09:34', '2026-02-21 14:11:09'),
(14, 'ORD-20260221194003-8246', 6, 'admin@nazmiboutique.com', 'Nazmi Admin', NULL, 'Nazmi', 'Admin', 'sheebothi house valat post wayanad', '', 'manathavady', 'Kerala', '670644', 'India', '7510731406', NULL, NULL, NULL, NULL, NULL, NULL, 1, 0.00, 100.00, 0.00, 1.00, 1.00, 101.00, 'cod', 'pending', NULL, NULL, 'confirmed', NULL, '2026-02-21 14:10:03', '2026-02-21 14:10:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-21 14:10:03', '2026-02-21 14:10:03');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL COMMENT 'Snapshot of product name at time of order',
  `product_sku` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL COMMENT 'Price at time of order (snapshot)',
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variant_id`, `product_name`, `product_sku`, `quantity`, `unit_price`, `discount_amount`, `tax_amount`, `total_price`, `created_at`) VALUES
(1, 1, 1, NULL, 'pashmira', 'ps1', 1, 499.00, 0.00, 0.00, 499.00, '2026-02-05 16:46:44'),
(2, 2, 1, NULL, 'pashmira', 'ps1', 1, 499.00, 0.00, 0.00, 499.00, '2026-02-05 16:55:29'),
(3, 3, 1, NULL, 'pashmira', 'ps1', 1, 499.00, 0.00, 0.00, 499.00, '2026-02-05 17:02:48'),
(4, 4, 1, NULL, 'pashmira', 'ps1', 1, 499.00, 0.00, 0.00, 499.00, '2026-02-05 17:22:02'),
(5, 5, 1, NULL, 'pashmira', 'ps1', 1, 499.00, 0.00, 0.00, 499.00, '2026-02-05 17:37:11'),
(6, 6, 1, NULL, 'pashmira', 'ps1', 1, 499.00, 0.00, 0.00, 499.00, '2026-02-19 13:55:37'),
(7, 7, 1, NULL, 'pashmira', 'ps1', 1, 499.00, 0.00, 0.00, 499.00, '2026-02-19 15:14:38'),
(8, 8, 1, NULL, 'pashmira', 'ps1', 1, 499.00, 0.00, 0.00, 499.00, '2026-02-19 15:16:39'),
(9, 9, 1, NULL, 'pashmira', 'ps1', 1, 499.00, 0.00, 0.00, 499.00, '2026-02-19 15:23:53'),
(10, 10, 1, NULL, 'pashmira', 'ps1', 1, 499.00, 0.00, 0.00, 499.00, '2026-02-19 15:27:35'),
(11, 11, 1, NULL, 'pashmira', 'ps1', 1, 499.00, 0.00, 0.00, 499.00, '2026-02-19 15:31:54'),
(12, 12, 2, NULL, 'Zintrix', 'test', 1, 100.00, 0.00, 0.00, 100.00, '2026-02-20 00:37:01'),
(13, 13, 2, NULL, 'Zintrix', 'test', 1, 100.00, 0.00, 0.00, 100.00, '2026-02-20 01:09:34'),
(14, 14, 2, NULL, 'Zintrix', 'test', 1, 100.00, 0.00, 0.00, 100.00, '2026-02-21 14:10:03');

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL COMMENT 'NULL if system/automatic',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `status`, `notes`, `changed_by`, `created_at`) VALUES
(1, 1, 'confirmed', 'Order placed successfully', NULL, '2026-02-05 16:46:44'),
(2, 2, 'confirmed', 'Order placed successfully', NULL, '2026-02-05 16:55:29'),
(3, 3, 'confirmed', 'Order placed successfully', NULL, '2026-02-05 17:02:49'),
(4, 3, 'processing', NULL, 6, '2026-02-05 17:07:21'),
(5, 3, 'delivered', NULL, 6, '2026-02-05 17:12:17'),
(6, 4, 'confirmed', 'Order placed successfully', NULL, '2026-02-05 17:22:03'),
(7, 5, 'confirmed', 'Order placed successfully', NULL, '2026-02-05 17:37:11'),
(8, 6, 'confirmed', 'Order placed successfully', NULL, '2026-02-19 13:55:37'),
(9, 7, 'confirmed', 'Order placed successfully', NULL, '2026-02-19 15:14:39'),
(10, 8, 'confirmed', 'Order placed successfully', NULL, '2026-02-19 15:16:40'),
(11, 9, 'confirmed', 'Order placed successfully', NULL, '2026-02-19 15:23:54'),
(12, 10, 'confirmed', 'Order placed successfully', NULL, '2026-02-19 15:27:35'),
(13, 11, 'confirmed', 'Order placed successfully', NULL, '2026-02-19 15:31:54'),
(14, 12, 'confirmed', 'Order placed successfully', NULL, '2026-02-20 00:37:01'),
(15, 13, 'confirmed', 'Order placed successfully', NULL, '2026-02-20 01:09:34'),
(16, 14, 'confirmed', 'Order placed successfully', NULL, '2026-02-21 14:10:03'),
(17, 13, 'cancelled', 'Cancelled by customer', NULL, '2026-02-21 14:11:09');

-- --------------------------------------------------------

--
-- Table structure for table `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `transaction_id` varchar(255) NOT NULL COMMENT 'Gateway transaction ID',
  `payment_method` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'INR',
  `status` varchar(50) NOT NULL COMMENT 'pending, processing, success, failed, refunded',
  `gateway` varchar(50) DEFAULT NULL COMMENT 'razorpay, stripe, etc.',
  `gateway_response` text DEFAULT NULL COMMENT 'JSON response',
  `failure_reason` text DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT 0.00,
  `refund_reason` text DEFAULT NULL,
  `refunded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL COMMENT 'dashboard, orders, stock, finance, etc.',
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `module`, `description`, `created_at`) VALUES
(1, 'view_dashboard', 'dashboard', 'View dashboard statistics', '2026-02-03 08:18:58'),
(2, 'view_orders', 'orders', 'View orders', '2026-02-03 08:18:58'),
(3, 'manage_orders', 'orders', 'Create, update, cancel orders', '2026-02-03 08:18:58'),
(4, 'update_order_status', 'orders', 'Update order status', '2026-02-03 08:18:58'),
(5, 'view_products', 'products', 'View products', '2026-02-03 08:18:58'),
(6, 'manage_products', 'products', 'Create, update, delete products', '2026-02-03 08:18:58'),
(7, 'view_stock', 'stock', 'View stock levels', '2026-02-03 08:18:58'),
(8, 'manage_stock', 'stock', 'Update stock, create purchase orders', '2026-02-03 08:18:58'),
(9, 'view_finance', 'finance', 'View financial reports', '2026-02-03 08:18:58'),
(10, 'manage_finance', 'finance', 'Manage expenses, transactions', '2026-02-03 08:18:58'),
(11, 'view_users', 'users', 'View all users (admin and customer)', '2026-02-03 08:18:58'),
(12, 'manage_users', 'users', 'Manage all users and roles', '2026-02-03 08:18:58'),
(13, 'view_coupons', 'coupons', 'View coupons', '2026-02-03 08:18:58'),
(14, 'manage_coupons', 'coupons', 'Create, update, delete coupons', '2026-02-03 08:18:58'),
(15, 'view_b2b', 'b2b', 'View B2B requests', '2026-02-03 08:18:58'),
(16, 'manage_b2b', 'b2b', 'Approve/reject B2B requests', '2026-02-03 08:18:58'),
(17, 'view_reports', 'reports', 'View reports', '2026-02-03 08:18:58');

-- --------------------------------------------------------

--
-- Table structure for table `pos_api_keys`
--

CREATE TABLE `pos_api_keys` (
  `id` int(11) NOT NULL,
  `api_key` varchar(50) NOT NULL COMMENT 'naz_xxxxxxxxxxxxxxxx',
  `api_secret_hash` varchar(255) NOT NULL COMMENT 'Hashed secret',
  `name` varchar(255) NOT NULL COMMENT 'Integration name',
  `description` text DEFAULT NULL,
  `scopes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of allowed scopes: products, orders, finance' CHECK (json_valid(`scopes`)),
  `is_active` tinyint(1) DEFAULT 1,
  `expires_at` timestamp NULL DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `key_name` varchar(255) DEFAULT NULL COMMENT 'Alias for name column',
  `api_secret` varchar(255) DEFAULT NULL COMMENT 'Alias for api_secret_hash',
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'POS tenant ID',
  `tenant_name` varchar(255) DEFAULT NULL COMMENT 'POS tenant name',
  `allowed_ips` varchar(255) DEFAULT NULL COMMENT 'Comma-separated list of allowed IP addresses',
  `notes` text DEFAULT NULL COMMENT 'Additional notes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pos_api_keys`
--

INSERT INTO `pos_api_keys` (`id`, `api_key`, `api_secret_hash`, `name`, `description`, `scopes`, `is_active`, `expires_at`, `last_used_at`, `created_by`, `created_at`, `updated_at`, `key_name`, `api_secret`, `tenant_id`, `tenant_name`, `allowed_ips`, `notes`) VALUES
(1, 'naz_c1d3811f30f10b49f40f4d2a', '$2y$12$7JwcQ2/zrNWgAXyOQdOQW.gCxxYx3Ag1KfTv3SZlbf97ufMMWvPgO', 'QA Ecom', NULL, '[\"products\",\"orders\",\"finance\"]', 1, NULL, '2026-02-20 07:47:22', 6, '2026-02-05 17:14:33', '2026-02-20 07:47:22', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pos_integration_logs`
--

CREATE TABLE `pos_integration_logs` (
  `id` int(11) NOT NULL,
  `api_key_id` int(11) DEFAULT NULL,
  `api_key` varchar(50) DEFAULT NULL,
  `request_type` varchar(50) NOT NULL COMMENT 'products, orders, finance',
  `request_method` varchar(10) NOT NULL COMMENT 'GET, POST, PUT, DELETE',
  `endpoint` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status_code` int(11) DEFAULT NULL,
  `response_time_ms` int(11) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `request_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_data`)),
  `response_summary` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pos_integration_logs`
--

INSERT INTO `pos_integration_logs` (`id`, `api_key_id`, `api_key`, `request_type`, `request_method`, `endpoint`, `ip_address`, `user_agent`, `status_code`, `response_time_ms`, `error_message`, `request_data`, `response_summary`, `created_at`) VALUES
(1, 1, 'naz_c1d3811f30f10b49f40f4d2a', 'auth', 'POST', '/api/v1/integration/auth/token', '2a02:4780:11:1436:0:135d:e7e5:1', NULL, 200, 217, NULL, NULL, 'Authentication successful', '2026-02-05 17:14:51'),
(2, 1, 'naz_c1d3811f30f10b49f40f4d2a', 'products', 'GET', '/api/v1/integration/products', '2a02:4780:11:1436:0:135d:e7e5:1', NULL, 200, 2, NULL, NULL, NULL, '2026-02-05 17:15:11'),
(3, 1, 'naz_c1d3811f30f10b49f40f4d2a', 'orders', 'GET', '/api/v1/integration/orders', '2a02:4780:11:1436:0:135d:e7e5:1', NULL, 200, 1, NULL, NULL, NULL, '2026-02-05 17:15:16'),
(4, 1, 'naz_c1d3811f30f10b49f40f4d2a', 'finance', 'GET', '/api/v1/integration/finance?summary=1', '2a02:4780:11:1436:0:135d:e7e5:1', NULL, 200, 1, NULL, NULL, NULL, '2026-02-05 17:15:21'),
(5, 1, 'naz_c1d3811f30f10b49f40f4d2a', 'finance', 'GET', '/api/v1/integration/finance?summary=1', '2a02:4780:11:1436:0:135d:e7e5:1', NULL, 200, 1, NULL, NULL, NULL, '2026-02-05 17:22:52'),
(6, 1, 'naz_c1d3811f30f10b49f40f4d2a', 'orders', 'GET', '/api/v1/integration/orders', '2a02:4780:11:1436:0:135d:e7e5:1', NULL, 200, 1, NULL, NULL, NULL, '2026-02-05 17:22:58'),
(7, 1, 'naz_c1d3811f30f10b49f40f4d2a', 'products', 'GET', '/api/v1/integration/products', '2a02:4780:11:1436:0:135d:e7e5:1', NULL, 200, 82, NULL, NULL, NULL, '2026-02-05 17:23:03'),
(8, 1, 'naz_c1d3811f30f10b49f40f4d2a', 'auth', 'POST', '/api/v1/integration/auth/token', '2a02:4780:11:1436:0:135d:e7e5:1', NULL, 200, 231, NULL, NULL, 'Authentication successful', '2026-02-18 05:35:50'),
(9, 1, 'naz_c1d3811f30f10b49f40f4d2a', 'auth', 'POST', '/api/v1/integration/auth/token', '2a02:4780:11:1436:0:135d:e7e5:1', NULL, 200, 223, NULL, NULL, 'Authentication successful', '2026-02-20 07:47:03'),
(10, 1, 'naz_c1d3811f30f10b49f40f4d2a', 'auth', 'POST', '/api/v1/integration/auth/token', '2a02:4780:11:1436:0:135d:e7e5:1', NULL, 200, 228, NULL, NULL, 'Authentication successful', '2026-02-20 07:47:22');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_description` text DEFAULT NULL,
  `full_description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL COMMENT 'For showing discount',
  `discount_percentage` int(11) DEFAULT 0,
  `cost_price` decimal(10,2) DEFAULT NULL COMMENT 'Admin: Cost price for profit calculation',
  `status` varchar(50) DEFAULT 'active' COMMENT 'active, inactive, out_of_stock, discontinued',
  `is_featured` tinyint(1) DEFAULT 0,
  `is_new` tinyint(1) DEFAULT 0,
  `stock_quantity` int(11) DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 10,
  `weight_kg` decimal(5,2) DEFAULT NULL,
  `dimensions_cm` varchar(50) DEFAULT NULL COMMENT '120x75x8',
  `warranty_months` int(11) DEFAULT 12,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `view_count` int(11) DEFAULT 0,
  `sold_count` int(11) DEFAULT 0,
  `rating_average` decimal(3,2) DEFAULT 0.00 COMMENT '0.00 to 5.00',
  `rating_count` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `sku`, `name`, `slug`, `short_description`, `full_description`, `category_id`, `price`, `original_price`, `discount_percentage`, `cost_price`, `status`, `is_featured`, `is_new`, `stock_quantity`, `low_stock_threshold`, `weight_kg`, `dimensions_cm`, `warranty_months`, `meta_title`, `meta_description`, `meta_keywords`, `view_count`, `sold_count`, `rating_average`, `rating_count`, `created_at`, `updated_at`) VALUES
(1, 'ps1', 'pashmira', 'pashmira', NULL, NULL, 5, 499.00, 300.00, 0, 400.00, 'active', 1, 1, 9, 10, NULL, NULL, 12, NULL, NULL, NULL, 10, 11, 0.00, 0, '2026-02-03 16:31:30', '2026-02-20 00:36:17'),
(2, 'test', 'Zintrix', 'zintrix', NULL, NULL, 4, 100.00, 100.00, 0, 100.00, 'active', 1, 1, 18, 10, NULL, NULL, 12, NULL, NULL, NULL, 5, 2, 0.00, 0, '2026-02-15 16:08:06', '2026-02-21 23:53:07');

-- --------------------------------------------------------

--
-- Table structure for table `product_features`
--

CREATE TABLE `product_features` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `icon_name` varchar(50) DEFAULT NULL COMMENT 'Lucide icon name',
  `feature_text` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `alt_text`, `sort_order`, `is_primary`, `created_at`) VALUES
(1, 1, '/uploads/products/product_1_1770136290_0.jpeg', NULL, 0, 1, '2026-02-03 16:31:30'),
(2, 2, '/uploads/products/product_2_1771171686_0.jpeg', NULL, 0, 1, '2026-02-15 16:08:06');

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'NULL for guest reviews',
  `order_id` int(11) DEFAULT NULL COMMENT 'Verify purchase',
  `rating` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `review_text` text DEFAULT NULL,
  `pros` text DEFAULT NULL,
  `cons` text DEFAULT NULL,
  `is_verified_purchase` tinyint(1) DEFAULT 0,
  `is_approved` tinyint(1) DEFAULT 0 COMMENT 'Admin moderation',
  `helpful_count` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `product_sales_summary`
-- (See below for the actual view)
--
CREATE TABLE `product_sales_summary` (
`product_id` int(11)
,`product_name` varchar(255)
,`sku` varchar(100)
,`times_ordered` bigint(21)
,`total_quantity_sold` decimal(32,0)
,`total_revenue` decimal(32,2)
,`average_selling_price` decimal(14,6)
);

-- --------------------------------------------------------

--
-- Table structure for table `product_specifications`
--

CREATE TABLE `product_specifications` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `spec_key` varchar(100) NOT NULL COMMENT 'Screen Size, Resolution, RAM, etc.',
  `spec_value` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_specifications`
--

INSERT INTO `product_specifications` (`id`, `product_id`, `spec_key`, `spec_value`, `sort_order`, `created_at`) VALUES
(1, 1, 'Brand', 'nazmi', 0, '2026-02-03 16:31:30'),
(2, 1, 'Material', 'cotton', 1, '2026-02-03 16:31:30'),
(3, 1, 'Pattern', 'printed', 2, '2026-02-03 16:31:30'),
(4, 1, 'Style', 'modern', 3, '2026-02-03 16:31:30'),
(5, 1, 'Season', 'summer', 4, '2026-02-03 16:31:30'),
(6, 1, 'Occasion', 'wedding', 5, '2026-02-03 16:31:30'),
(7, 2, 'Brand', 'nazmi', 0, '2026-02-15 16:08:06');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_type` varchar(50) NOT NULL COMMENT 'size, os, color',
  `variant_value` varchar(100) NOT NULL COMMENT '43", Android 11, Black',
  `price_modifier` decimal(10,2) DEFAULT 0.00 COMMENT 'Additional cost',
  `stock_quantity` int(11) DEFAULT 0,
  `sku_suffix` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL,
  `po_number` varchar(100) NOT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `supplier_contact` varchar(255) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'pending' COMMENT 'pending, ordered, received, cancelled',
  `ordered_date` timestamp NULL DEFAULT NULL,
  `expected_date` timestamp NULL DEFAULT NULL,
  `received_date` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` int(11) NOT NULL,
  `purchase_order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `received_quantity` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review_votes`
--

CREATE TABLE `review_votes` (
  `id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_helpful` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'Administrator with full system access', 1, '2026-02-03 08:18:58', '2026-02-03 08:18:58'),
(2, 'Customer', 'Regular customer role for frontend users', 1, '2026-02-03 08:18:58', '2026-02-03 08:18:58');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES
(1, 1, 16, '2026-02-03 08:18:58'),
(2, 1, 14, '2026-02-03 08:18:58'),
(3, 1, 10, '2026-02-03 08:18:58'),
(4, 1, 3, '2026-02-03 08:18:58'),
(5, 1, 6, '2026-02-03 08:18:58'),
(6, 1, 8, '2026-02-03 08:18:58'),
(7, 1, 12, '2026-02-03 08:18:58'),
(8, 1, 4, '2026-02-03 08:18:58'),
(9, 1, 15, '2026-02-03 08:18:58'),
(10, 1, 13, '2026-02-03 08:18:58'),
(11, 1, 1, '2026-02-03 08:18:58'),
(12, 1, 9, '2026-02-03 08:18:58'),
(13, 1, 2, '2026-02-03 08:18:58'),
(14, 1, 5, '2026-02-03 08:18:58'),
(15, 1, 17, '2026-02-03 08:18:58'),
(16, 1, 7, '2026-02-03 08:18:58'),
(17, 1, 11, '2026-02-03 08:18:58');

-- --------------------------------------------------------

--
-- Stand-in structure for view `sales_summary`
-- (See below for the actual view)
--
CREATE TABLE `sales_summary` (
`sale_date` date
,`total_orders` bigint(21)
,`unique_customers` bigint(21)
,`total_revenue` decimal(32,2)
,`total_subtotal` decimal(32,2)
,`total_tax` decimal(32,2)
,`total_shipping` decimal(32,2)
,`total_discounts` decimal(32,2)
,`average_order_value` decimal(14,6)
);

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `tracking_number` varchar(255) DEFAULT NULL,
  `carrier` varchar(100) DEFAULT NULL COMMENT 'FedEx, DTDC, Delhivery, etc.',
  `status` varchar(50) DEFAULT 'pending' COMMENT 'pending, in_transit, out_for_delivery, delivered, exception',
  `estimated_delivery_date` date DEFAULT NULL,
  `actual_delivery_date` timestamp NULL DEFAULT NULL,
  `shipment_date` timestamp NULL DEFAULT NULL,
  `current_location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipment_tracking_events`
--

CREATE TABLE `shipment_tracking_events` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `event_type` varchar(50) NOT NULL COMMENT 'dispatched, in_transit, out_for_delivery, delivered, etc.',
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `event_date` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipping_methods`
--

CREATE TABLE `shipping_methods` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Standard Delivery, Express Delivery',
  `code` varchar(50) NOT NULL COMMENT 'standard, express',
  `cost` decimal(10,2) DEFAULT 0.00,
  `estimated_days_min` int(11) NOT NULL,
  `estimated_days_max` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipping_methods`
--

INSERT INTO `shipping_methods` (`id`, `name`, `code`, `cost`, `estimated_days_min`, `estimated_days_max`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Standard Delivery', 'standard', 0.00, 3, 5, 1, '2026-02-03 08:18:58', '2026-02-03 08:18:58'),
(2, 'Express Delivery', 'express', 450.00, 1, 2, 1, '2026-02-03 08:18:58', '2026-02-03 08:18:58');

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `movement_type` varchar(50) NOT NULL COMMENT 'purchase, sale, return, adjustment, damage',
  `quantity` int(11) NOT NULL COMMENT 'Positive for additions, negative for deductions',
  `previous_quantity` int(11) NOT NULL,
  `new_quantity` int(11) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL COMMENT 'order, purchase_order, adjustment',
  `reference_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `product_id`, `movement_type`, `quantity`, `previous_quantity`, `new_quantity`, `reference_type`, `reference_id`, `notes`, `created_by`, `created_at`) VALUES
(1, 1, 'sale', -1, 20, 19, 'order', 1, 'Order: ORD-20260205221644-3397', NULL, '2026-02-05 16:46:44'),
(2, 1, 'sale', -1, 19, 18, 'order', 2, 'Order: ORD-20260205222529-7281', NULL, '2026-02-05 16:55:29'),
(3, 1, 'sale', -1, 18, 17, 'order', 3, 'Order: ORD-20260205223249-1763', NULL, '2026-02-05 17:02:48'),
(4, 1, 'sale', -1, 17, 16, 'order', 4, 'Order: ORD-20260205225202-6585', NULL, '2026-02-05 17:22:02'),
(5, 1, 'sale', -1, 16, 15, 'order', 5, 'Order: ORD-20260205230711-7446', NULL, '2026-02-05 17:37:11'),
(6, 1, 'sale', -1, 15, 14, 'order', 6, 'Order: ORD-20260219192537-8960', NULL, '2026-02-19 13:55:37'),
(7, 1, 'sale', -1, 14, 13, 'order', 7, 'Order: ORD-20260219204438-2986', NULL, '2026-02-19 15:14:39'),
(8, 1, 'sale', -1, 13, 12, 'order', 8, 'Order: ORD-20260219204639-8539', NULL, '2026-02-19 15:16:40'),
(9, 1, 'sale', -1, 12, 11, 'order', 9, 'Order: ORD-20260219205352-4911', NULL, '2026-02-19 15:23:53'),
(10, 1, 'sale', -1, 11, 10, 'order', 10, 'Order: ORD-20260219205734-1169', NULL, '2026-02-19 15:27:35'),
(11, 1, 'sale', -1, 10, 9, 'order', 11, 'Order: ORD-20260219210153-0623', NULL, '2026-02-19 15:31:54'),
(12, 2, 'sale', -1, 20, 19, 'order', 12, 'Order: ORD-20260220060701-9669', NULL, '2026-02-20 00:37:01'),
(13, 2, 'sale', -1, 19, 18, 'order', 13, 'Order: ORD-20260220063934-0529', NULL, '2026-02-20 01:09:34'),
(14, 2, 'sale', -1, 18, 17, 'order', 14, 'Order: ORD-20260221194003-8246', NULL, '2026-02-21 14:10:03'),
(15, 2, 'restock', 1, 17, 18, 'order_cancel', 13, 'Order Cancelled: ORD-20260220063934-0529', NULL, '2026-02-21 14:11:09');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` varchar(50) DEFAULT 'string' COMMENT 'string, number, boolean, json',
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL COMMENT 'general, payment, shipping, email, etc.',
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `category`, `updated_by`, `updated_at`) VALUES
(1, 'site_name', 'NAZMI BOUTIQUE', 'string', 'Website name', 'general', NULL, '2026-02-03 08:18:58'),
(2, 'site_email', 'info@nazmiboutique.com', 'string', 'Contact email', 'general', NULL, '2026-02-03 08:18:58'),
(3, 'site_phone', '+91 6238762189', 'string', 'Contact phone', 'general', NULL, '2026-02-03 08:18:58'),
(4, 'currency', 'INR', 'string', 'Default currency', 'general', NULL, '2026-02-03 08:18:58'),
(5, 'currency_symbol', '₹', 'string', 'Currency symbol', 'general', NULL, '2026-02-03 08:18:58'),
(6, 'low_stock_threshold', '10', 'number', 'Low stock alert threshold', 'stock', NULL, '2026-02-03 08:18:58'),
(7, 'order_prefix', 'ORD-', 'string', 'Order number prefix', 'orders', NULL, '2026-02-03 08:18:58'),
(8, 'tax_enabled', '1', 'boolean', 'Enable/Disable GST Tax', 'tax', 5, '2026-02-03 14:00:13'),
(9, 'tax_rate', '1', 'number', 'GST percentage', 'tax', 5, '2026-02-03 14:00:13'),
(10, 'tax_inclusive', '0', 'boolean', 'Are product prices inclusive of tax', 'tax', 5, '2026-02-03 14:00:13'),
(11, 'payment_online_enabled', '0', 'boolean', 'Enable/Disable Online Payment (Razorpay)', 'payment', 5, '2026-02-03 14:00:13'),
(12, 'payment_cod_enabled', '1', 'boolean', 'Enable/Disable Cash on Delivery', 'payment', 5, '2026-02-03 14:00:13'),
(13, 'razorpay_key', '', 'string', 'Razorpay API Key', 'payment', 5, '2026-02-03 14:00:13'),
(14, 'razorpay_secret', 'Adnan@66202', 'string', 'Razorpay API Secret', 'payment', 5, '2026-02-03 14:00:13'),
(15, 'auth_login_enabled', '1', 'boolean', 'Enable/Disable User Login', 'auth', 5, '2026-02-03 14:00:13'),
(16, 'auth_signup_enabled', '1', 'boolean', 'Enable/Disable User Signup/Registration', 'auth', 5, '2026-02-03 14:00:13'),
(17, 'auth_forgot_password_enabled', '1', 'boolean', 'Enable/Disable Forgot Password', 'auth', 5, '2026-02-03 14:00:13'),
(18, 'auth_guest_checkout_enabled', '0', 'boolean', 'Enable/Disable Guest Checkout', 'auth', 5, '2026-02-03 14:00:13'),
(19, 'email_service_enabled', '1', 'boolean', 'Enable/Disable Email Service', 'email', 5, '2026-02-03 14:21:03'),
(20, 'email_notifications_enabled', '1', 'boolean', 'Enable/Disable Email Notifications', 'email', 5, '2026-02-03 14:21:03'),
(21, 'email_order_confirmation', '1', 'boolean', 'Send order confirmation emails', 'email', 5, '2026-02-03 14:21:03'),
(22, 'email_shipping_updates', '1', 'boolean', 'Send shipping update emails', 'email', 5, '2026-02-03 14:21:03'),
(23, 'email_welcome_email', '1', 'boolean', 'Send welcome email on registration', 'email', 5, '2026-02-03 14:21:03'),
(24, 'smtp_host', 'smtp.hostinger.com', 'string', 'SMTP Host', 'email', 5, '2026-02-03 14:21:03'),
(25, 'smtp_port', '465', 'number', 'SMTP Port', 'email', 5, '2026-02-03 14:21:03'),
(26, 'smtp_username', 'mail@archizeon.com', 'string', 'SMTP Username', 'email', 5, '2026-02-03 14:21:03'),
(27, 'smtp_password', 'Adnan@66202', 'string', 'SMTP Password (encrypted)', 'email', 5, '2026-02-03 14:21:03'),
(28, 'smtp_encryption', 'ssl', 'string', 'SMTP Encryption (tls/ssl)', 'email', 5, '2026-02-03 14:21:03'),
(29, 'email_from_name', 'NAZMI BOUTIQUE', 'string', 'From Name for emails', 'email', 5, '2026-02-03 14:21:03'),
(30, 'email_from_address', 'mail@archizeon.com', 'string', 'From Email Address', 'email', 5, '2026-02-03 14:21:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL COMMENT 'bcrypt hash',
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role_id` int(11) DEFAULT 2 COMMENT '1=Admin, 2=Customer (default)',
  `email_verified` tinyint(1) DEFAULT 0,
  `email_verification_token` varchar(255) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `newsletter_subscribed` tinyint(1) DEFAULT 0,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `role_id`, `email_verified`, `email_verification_token`, `password_reset_token`, `password_reset_expires`, `is_active`, `newsletter_subscribed`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'customer1@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', '+91 9876543210', 2, 1, NULL, NULL, NULL, 1, 1, NULL, '2026-02-03 08:18:58', '2026-02-03 08:18:58'),
(2, 'customer2@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', '+91 9876543211', 2, 1, NULL, NULL, NULL, 1, 0, NULL, '2026-02-03 08:18:58', '2026-02-03 08:18:58'),
(3, 'customer3@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Priya', 'Sharma', '+91 9876543212', 2, 1, NULL, NULL, NULL, 1, 1, NULL, '2026-02-03 08:18:58', '2026-02-03 08:18:58'),
(4, 'contact.adnanks@gmail.com', '$2y$10$12N787I7i20KkJVzJDHoM.B1VPz0SAVNXE63/o/HAm9JsyuMOZsqW', 'Adnan', 'K S', '+919744466737', 2, 0, '0ee376bd61bbaf531e2a12abc5a309b7c5a89eca7ed798b0ce8cccd108a79b8b', '65a96453e6b1ef3afd1354a603052a357bb26bea6b8f0594d2c81da4e36f1a26', '2026-02-03 20:50:17', 1, 0, '2026-02-19 15:20:02', '2026-02-03 13:29:16', '2026-02-19 15:20:02'),
(5, 'adnan@nomadscipher.com', '$2y$10$lTtrhncsxzPRHMoIuE8cO.jZ0UIzF0dLr947X22o5V0Uwj3gpOogC', 'Adnan', 'Kakkattil', NULL, 1, 0, NULL, NULL, NULL, 1, 0, '2026-02-03 15:08:17', '2026-02-03 13:31:22', '2026-02-03 15:08:17'),
(6, 'admin@nazmiboutique.com', '$2y$12$6nmT0X6FGlWQOQV1W/V0kO.pBklWeqpsUDYQyEDWsEvZkWz7LO.QG', 'Nazmi', 'Admin', '', 1, 0, NULL, NULL, NULL, 1, 0, '2026-02-21 23:43:55', '2026-02-03 15:10:15', '2026-02-21 23:43:55'),
(7, 'alihamdaneckoduvally5@gmail.com', '$2y$10$eZ.huGQioJgGB7Drx0I8Oegtm3zBcK7JnrANZzUJnHF8fsthxLfNm', 'Ali', 'Ec', '9745121739', 2, 0, '15df4054193142c2a9194dcd72005281973767578d7d27bafc5be8fede413e63', NULL, NULL, 1, 0, NULL, '2026-02-19 13:54:21', '2026-02-19 13:54:21'),
(8, 'ashlin@nomadscipher.com', '$2y$10$PeBIDRHGJzhY8l3h1BzoJ.vv.Ipp1p/OwyL6JIzCzw6W84oYd33L2', 'Ashlin', 'Ziyad', '09488766222', 1, 1, 'decda1185db1fa73e6d1437c71a431f4799628c6f8bbe9541765d6e59bce6e96', NULL, NULL, 1, 0, '2026-02-19 15:21:53', '2026-02-19 15:01:55', '2026-02-19 15:21:53');

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(200) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `country` varchar(100) DEFAULT 'India',
  `is_default` tinyint(1) DEFAULT 0,
  `address_type` varchar(50) DEFAULT 'home' COMMENT 'home, work, other',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist_items`
--

CREATE TABLE `wishlist_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlist_items`
--

INSERT INTO `wishlist_items` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(3, 6, 2, '2026-02-21 23:53:00'),
(4, 6, 1, '2026-02-21 23:53:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_activity_log_user_id` (`user_id`),
  ADD KEY `idx_admin_activity_log_module` (`module`),
  ADD KEY `idx_admin_activity_log_created_at` (`created_at`);

--
-- Indexes for table `b2b_customers`
--
ALTER TABLE `b2b_customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_b2b_customer_request` (`b2b_request_id`),
  ADD KEY `fk_b2b_customer_assigned` (`assigned_to`);

--
-- Indexes for table `b2b_requests`
--
ALTER TABLE `b2b_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_b2b_reviewed_by` (`reviewed_by`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product_variant` (`user_id`,`product_id`,`variant_id`),
  ADD UNIQUE KEY `unique_session_product_variant` (`session_id`,`product_id`,`variant_id`),
  ADD KEY `fk_cart_variant` (`variant_id`),
  ADD KEY `idx_cart_items_user_id` (`user_id`),
  ADD KEY `idx_cart_items_session_id` (`session_id`),
  ADD KEY `idx_cart_items_product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_category_parent` (`parent_id`);

--
-- Indexes for table `contact_requests`
--
ALTER TABLE `contact_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_contact_replied_by` (`replied_by`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `fk_coupon_category` (`applicable_category_id`),
  ADD KEY `fk_coupon_created_by` (`created_by`);

--
-- Indexes for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_coupon_usage_order` (`order_id`),
  ADD KEY `idx_coupon_usage_coupon_id` (`coupon_id`),
  ADD KEY `idx_coupon_usage_user_id` (`user_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_expense_category` (`category_id`),
  ADD KEY `fk_expense_created_by` (`created_by`);

--
-- Indexes for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `financial_transactions`
--
ALTER TABLE `financial_transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `newsletter_subscriptions`
--
ALTER TABLE `newsletter_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `fk_order_address` (`shipping_address_id`),
  ADD KEY `fk_order_shipping_method` (`shipping_method_id`),
  ADD KEY `idx_orders_order_number` (`order_number`),
  ADD KEY `idx_orders_user_id` (`user_id`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_order_date` (`order_date`),
  ADD KEY `idx_orders_payment_status` (`payment_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_item_variant` (`variant_id`),
  ADD KEY `idx_order_items_order_id` (`order_id`),
  ADD KEY `idx_order_items_product_id` (`product_id`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_history_order` (`order_id`),
  ADD KEY `fk_order_history_changed_by` (`changed_by`);

--
-- Indexes for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `idx_payment_transactions_order_id` (`order_id`),
  ADD KEY `idx_payment_transactions_transaction_id` (`transaction_id`),
  ADD KEY `idx_payment_transactions_status` (`status`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `pos_api_keys`
--
ALTER TABLE `pos_api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_key` (`api_key`),
  ADD KEY `fk_api_created_by` (`created_by`),
  ADD KEY `idx_pos_api_keys_api_key` (`api_key`),
  ADD KEY `idx_pos_api_keys_is_active` (`is_active`);

--
-- Indexes for table `pos_integration_logs`
--
ALTER TABLE `pos_integration_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pos_integration_logs_api_key_id` (`api_key_id`),
  ADD KEY `idx_pos_integration_logs_created_at` (`created_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_products_sku` (`sku`),
  ADD KEY `idx_products_slug` (`slug`),
  ADD KEY `idx_products_category_id` (`category_id`),
  ADD KEY `idx_products_status` (`status`),
  ADD KEY `idx_products_price` (`price`),
  ADD KEY `idx_products_created_at` (`created_at`);

--
-- Indexes for table `product_features`
--
ALTER TABLE `product_features`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_feature` (`product_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_image` (`product_id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_review_product` (`product_id`),
  ADD KEY `fk_review_user` (`user_id`),
  ADD KEY `fk_review_order` (`order_id`);

--
-- Indexes for table `product_specifications`
--
ALTER TABLE `product_specifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_spec` (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_variant_product` (`product_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `fk_po_created_by` (`created_by`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_poi_po` (`purchase_order_id`),
  ADD KEY `fk_poi_product` (`product_id`);

--
-- Indexes for table `review_votes`
--
ALTER TABLE `review_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_review_user` (`review_id`,`user_id`),
  ADD KEY `fk_vote_user` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  ADD KEY `fk_rp_permission` (`permission_id`);

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tracking_number` (`tracking_number`),
  ADD KEY `fk_shipment_order` (`order_id`);

--
-- Indexes for table `shipment_tracking_events`
--
ALTER TABLE `shipment_tracking_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tracking_shipment` (`shipment_id`);

--
-- Indexes for table `shipping_methods`
--
ALTER TABLE `shipping_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_stock_created_by` (`created_by`),
  ADD KEY `idx_stock_movements_product_id` (`product_id`),
  ADD KEY `idx_stock_movements_created_at` (`created_at`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `fk_settings_updated_by` (`updated_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_phone` (`phone`),
  ADD KEY `idx_users_role_id` (`role_id`),
  ADD KEY `idx_users_created_at` (`created_at`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_address` (`user_id`);

--
-- Indexes for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `fk_wishlist_product` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `b2b_customers`
--
ALTER TABLE `b2b_customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `b2b_requests`
--
ALTER TABLE `b2b_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `contact_requests`
--
ALTER TABLE `contact_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `financial_transactions`
--
ALTER TABLE `financial_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `newsletter_subscriptions`
--
ALTER TABLE `newsletter_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `pos_api_keys`
--
ALTER TABLE `pos_api_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pos_integration_logs`
--
ALTER TABLE `pos_integration_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product_features`
--
ALTER TABLE `product_features`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_specifications`
--
ALTER TABLE `product_specifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `review_votes`
--
ALTER TABLE `review_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipment_tracking_events`
--
ALTER TABLE `shipment_tracking_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipping_methods`
--
ALTER TABLE `shipping_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

-- --------------------------------------------------------

--
-- Structure for view `low_stock_products`
--
DROP TABLE IF EXISTS `low_stock_products`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u324921317_QAdatbase`@`127.0.0.1` SQL SECURITY DEFINER VIEW `low_stock_products`  AS SELECT `products`.`id` AS `id`, `products`.`sku` AS `sku`, `products`.`name` AS `name`, `products`.`stock_quantity` AS `stock_quantity`, `products`.`low_stock_threshold` AS `low_stock_threshold`, `products`.`stock_quantity`<= `products`.`low_stock_threshold` AS `is_low_stock` FROM `products` WHERE `products`.`status` = 'active' AND `products`.`stock_quantity` <= `products`.`low_stock_threshold` ;

-- --------------------------------------------------------

--
-- Structure for view `product_sales_summary`
--
DROP TABLE IF EXISTS `product_sales_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u324921317_QAdatbase`@`127.0.0.1` SQL SECURITY DEFINER VIEW `product_sales_summary`  AS SELECT `p`.`id` AS `product_id`, `p`.`name` AS `product_name`, `p`.`sku` AS `sku`, count(`oi`.`id`) AS `times_ordered`, sum(`oi`.`quantity`) AS `total_quantity_sold`, sum(`oi`.`total_price`) AS `total_revenue`, avg(`oi`.`unit_price`) AS `average_selling_price` FROM ((`products` `p` left join `order_items` `oi` on(`p`.`id` = `oi`.`product_id`)) left join `orders` `o` on(`oi`.`order_id` = `o`.`id` and `o`.`status` <> 'cancelled')) GROUP BY `p`.`id`, `p`.`name`, `p`.`sku` ;

-- --------------------------------------------------------

--
-- Structure for view `sales_summary`
--
DROP TABLE IF EXISTS `sales_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u324921317_QAdatbase`@`127.0.0.1` SQL SECURITY DEFINER VIEW `sales_summary`  AS SELECT cast(`o`.`order_date` as date) AS `sale_date`, count(distinct `o`.`id`) AS `total_orders`, count(distinct `o`.`user_id`) AS `unique_customers`, sum(`o`.`total_amount`) AS `total_revenue`, sum(`o`.`subtotal`) AS `total_subtotal`, sum(`o`.`tax_amount`) AS `total_tax`, sum(`o`.`shipping_cost`) AS `total_shipping`, sum(`o`.`discount_amount`) AS `total_discounts`, avg(`o`.`total_amount`) AS `average_order_value` FROM `orders` AS `o` WHERE `o`.`status` <> 'cancelled' GROUP BY cast(`o`.`order_date` as date) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `b2b_customers`
--
ALTER TABLE `b2b_customers`
  ADD CONSTRAINT `fk_b2b_customer_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_b2b_customer_request` FOREIGN KEY (`b2b_request_id`) REFERENCES `b2b_requests` (`id`);

--
-- Constraints for table `b2b_requests`
--
ALTER TABLE `b2b_requests`
  ADD CONSTRAINT `fk_b2b_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`);

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_category_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `contact_requests`
--
ALTER TABLE `contact_requests`
  ADD CONSTRAINT `fk_contact_replied_by` FOREIGN KEY (`replied_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `fk_coupon_category` FOREIGN KEY (`applicable_category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `fk_coupon_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD CONSTRAINT `fk_coupon_usage_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`),
  ADD CONSTRAINT `fk_coupon_usage_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `fk_coupon_usage_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `fk_expense_category` FOREIGN KEY (`category_id`) REFERENCES `expense_categories` (`id`),
  ADD CONSTRAINT `fk_expense_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_address` FOREIGN KEY (`shipping_address_id`) REFERENCES `user_addresses` (`id`),
  ADD CONSTRAINT `fk_order_shipping_method` FOREIGN KEY (`shipping_method_id`) REFERENCES `shipping_methods` (`id`),
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_order_item_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`);

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `fk_order_history_changed_by` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_order_history_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD CONSTRAINT `fk_payment_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `pos_api_keys`
--
ALTER TABLE `pos_api_keys`
  ADD CONSTRAINT `fk_api_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `pos_integration_logs`
--
ALTER TABLE `pos_integration_logs`
  ADD CONSTRAINT `fk_log_api_key` FOREIGN KEY (`api_key_id`) REFERENCES `pos_api_keys` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `product_features`
--
ALTER TABLE `product_features`
  ADD CONSTRAINT `fk_product_feature` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_product_image` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `fk_review_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `fk_review_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `product_specifications`
--
ALTER TABLE `product_specifications`
  ADD CONSTRAINT `fk_product_spec` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `fk_variant_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `fk_po_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `fk_poi_po` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_poi_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `review_votes`
--
ALTER TABLE `review_votes`
  ADD CONSTRAINT `fk_vote_review` FOREIGN KEY (`review_id`) REFERENCES `product_reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vote_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shipments`
--
ALTER TABLE `shipments`
  ADD CONSTRAINT `fk_shipment_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `shipment_tracking_events`
--
ALTER TABLE `shipment_tracking_events`
  ADD CONSTRAINT `fk_tracking_shipment` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `fk_stock_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_stock_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `fk_settings_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `fk_user_address` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
