-- ============================================================================
-- BLine Boutique - COMPLETE DATABASE SETUP (MySQL/MariaDB Compatible)
-- ============================================================================
-- This file creates the complete database structure with:
-- - All tables and relationships
-- - Admin and Customer roles
-- - Initial seed data
-- - System settings
-- Database: MySQL 5.7+ / MariaDB 10.2+
-- ============================================================================

-- Create database (uncomment if needed)
-- CREATE DATABASE IF NOT EXISTS bline_boutique CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE bline_boutique;

-- Disable foreign key checks temporarily to avoid dependency issues
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- 1. AUTHENTICATION & USER MANAGEMENT
-- ============================================================================

-- User Roles (Simplified: Admin and Customer)
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permissions (Granular permissions for roles)
CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    module VARCHAR(50) NOT NULL COMMENT 'dashboard, orders, stock, finance, etc.',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role-Permission Mapping (Many-to-Many)
CREATE TABLE IF NOT EXISTS role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_role_permission (role_id, permission_id),
    CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_rp_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users (Both Admin and Customer - Role-based access control)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL COMMENT 'bcrypt hash',
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(20),
    role_id INT DEFAULT 2 COMMENT '1=Admin, 2=Customer (default)',
    email_verified TINYINT(1) DEFAULT 0,
    email_verification_token VARCHAR(255),
    password_reset_token VARCHAR(255),
    password_reset_expires TIMESTAMP NULL,
    is_active TINYINT(1) DEFAULT 1,
    newsletter_subscribed TINYINT(1) DEFAULT 0,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Addresses (Multiple addresses per user)
CREATE TABLE IF NOT EXISTS user_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(200),
    phone VARCHAR(20),
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    pincode VARCHAR(10) NOT NULL,
    country VARCHAR(100) DEFAULT 'India',
    is_default TINYINT(1) DEFAULT 0,
    address_type VARCHAR(50) DEFAULT 'home' COMMENT 'home, work, other',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_address FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. PRODUCT CATALOG
-- ============================================================================

-- Product Categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    parent_id INT COMMENT 'For subcategories',
    image VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_category_parent FOREIGN KEY (parent_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    short_description TEXT,
    full_description TEXT,
    category_id INT,
    price DECIMAL(10, 2) NOT NULL,
    original_price DECIMAL(10, 2) COMMENT 'For showing discount',
    discount_percentage INT DEFAULT 0,
    cost_price DECIMAL(10, 2) COMMENT 'Admin: Cost price for profit calculation',
    status VARCHAR(50) DEFAULT 'active' COMMENT 'active, inactive, out_of_stock, discontinued',
    is_featured TINYINT(1) DEFAULT 0,
    is_new TINYINT(1) DEFAULT 0,
    stock_quantity INT DEFAULT 0,
    low_stock_threshold INT DEFAULT 10,
    weight_kg DECIMAL(5, 2),
    dimensions_cm VARCHAR(50) COMMENT '120x75x8',
    warranty_months INT DEFAULT 12,
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords TEXT,
    view_count INT DEFAULT 0,
    sold_count INT DEFAULT 0,
    rating_average DECIMAL(3, 2) DEFAULT 0.00 COMMENT '0.00 to 5.00',
    rating_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Images (Multiple images per product)
CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_image FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Specifications (Key-Value pairs for technical specs)
CREATE TABLE IF NOT EXISTS product_specifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    spec_key VARCHAR(100) NOT NULL COMMENT 'Screen Size, Resolution, RAM, etc.',
    spec_value VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_spec FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Features (Highlighted features)
CREATE TABLE IF NOT EXISTS product_features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    icon_name VARCHAR(50) COMMENT 'Lucide icon name',
    feature_text VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_feature FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Variants (Future: Size, Color, OS variants)
CREATE TABLE IF NOT EXISTS product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    variant_type VARCHAR(50) NOT NULL COMMENT 'size, os, color',
    variant_value VARCHAR(100) NOT NULL COMMENT '43", Android 11, Black',
    price_modifier DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Additional cost',
    stock_quantity INT DEFAULT 0,
    sku_suffix VARCHAR(50),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_variant_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. INVENTORY & STOCK MANAGEMENT
-- ============================================================================

-- Stock Movements (Audit trail for stock changes)
CREATE TABLE IF NOT EXISTS stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    movement_type VARCHAR(50) NOT NULL COMMENT 'purchase, sale, return, adjustment, damage',
    quantity INT NOT NULL COMMENT 'Positive for additions, negative for deductions',
    previous_quantity INT NOT NULL,
    new_quantity INT NOT NULL,
    reference_type VARCHAR(50) COMMENT 'order, purchase_order, adjustment',
    reference_id INT,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_stock_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_stock_created_by FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Purchase Orders (Admin: For ordering stock from suppliers)
CREATE TABLE IF NOT EXISTS purchase_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_number VARCHAR(100) UNIQUE NOT NULL,
    supplier_name VARCHAR(255),
    supplier_contact VARCHAR(255),
    total_amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending' COMMENT 'pending, ordered, received, cancelled',
    ordered_date TIMESTAMP NULL,
    expected_date TIMESTAMP NULL,
    received_date TIMESTAMP NULL,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_po_created_by FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Purchase Order Items
CREATE TABLE IF NOT EXISTS purchase_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    received_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_poi_po FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_poi_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. CART & WISHLIST
-- ============================================================================

-- Shopping Cart
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(255) COMMENT 'For guest users',
    product_id INT NOT NULL,
    variant_id INT,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10, 2) NOT NULL COMMENT 'Price at time of adding (snapshot)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_product_variant (user_id, product_id, variant_id),
    UNIQUE KEY unique_session_product_variant (session_id, product_id, variant_id),
    CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_variant FOREIGN KEY (variant_id) REFERENCES product_variants(id),
    CONSTRAINT chk_cart_user_or_session CHECK ((user_id IS NOT NULL) OR (session_id IS NOT NULL)),
    CONSTRAINT chk_cart_quantity CHECK (quantity > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Wishlist
CREATE TABLE IF NOT EXISTS wishlist_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_product (user_id, product_id),
    CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. ORDERS
-- ============================================================================

-- Shipping Methods
CREATE TABLE IF NOT EXISTS shipping_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT 'Standard Delivery, Express Delivery',
    code VARCHAR(50) UNIQUE NOT NULL COMMENT 'standard, express',
    cost DECIMAL(10, 2) DEFAULT 0.00,
    estimated_days_min INT NOT NULL,
    estimated_days_max INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL COMMENT 'ORD-1234567890',
    user_id INT COMMENT 'NULL for guest orders',
    guest_email VARCHAR(255) COMMENT 'For guest checkout',
    guest_name VARCHAR(255) COMMENT 'For guest checkout',
    
    -- Shipping Information
    shipping_address_id INT,
    shipping_first_name VARCHAR(100) NOT NULL,
    shipping_last_name VARCHAR(100) NOT NULL,
    shipping_address_line1 VARCHAR(255) NOT NULL,
    shipping_address_line2 VARCHAR(255),
    shipping_city VARCHAR(100) NOT NULL,
    shipping_state VARCHAR(100) NOT NULL,
    shipping_pincode VARCHAR(10) NOT NULL,
    shipping_country VARCHAR(100) DEFAULT 'India',
    shipping_phone VARCHAR(20) NOT NULL,
    
    -- Billing Information (can be different from shipping)
    billing_address_line1 VARCHAR(255),
    billing_address_line2 VARCHAR(255),
    billing_city VARCHAR(100),
    billing_state VARCHAR(100),
    billing_pincode VARCHAR(10),
    billing_country VARCHAR(100),
    
    -- Shipping Method
    shipping_method_id INT,
    shipping_cost DECIMAL(10, 2) DEFAULT 0.00,
    
    -- Totals
    subtotal DECIMAL(10, 2) NOT NULL,
    discount_amount DECIMAL(10, 2) DEFAULT 0.00,
    tax_amount DECIMAL(10, 2) DEFAULT 0.00,
    tax_rate DECIMAL(5, 2) DEFAULT 18.00 COMMENT 'GST %',
    total_amount DECIMAL(10, 2) NOT NULL,
    
    -- Payment
    payment_method VARCHAR(50) NOT NULL COMMENT 'online, cod, upi',
    payment_status VARCHAR(50) DEFAULT 'pending' COMMENT 'pending, processing, completed, failed, refunded',
    payment_transaction_id VARCHAR(255),
    payment_gateway_response TEXT COMMENT 'JSON response from gateway',
    
    -- Order Status
    status VARCHAR(50) DEFAULT 'confirmed' COMMENT 'confirmed, processing, shipped, delivered, cancelled, returned',
    status_notes TEXT,
    
    -- Dates
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmed_date TIMESTAMP NULL,
    processing_date TIMESTAMP NULL,
    shipped_date TIMESTAMP NULL,
    delivered_date TIMESTAMP NULL,
    cancelled_date TIMESTAMP NULL,
    cancelled_reason TEXT,
    
    -- Additional
    notes TEXT,
    internal_notes TEXT COMMENT 'Admin notes',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_order_address FOREIGN KEY (shipping_address_id) REFERENCES user_addresses(id),
    CONSTRAINT fk_order_shipping_method FOREIGN KEY (shipping_method_id) REFERENCES shipping_methods(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order Items
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    variant_id INT,
    product_name VARCHAR(255) NOT NULL COMMENT 'Snapshot of product name at time of order',
    product_sku VARCHAR(100),
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL COMMENT 'Price at time of order (snapshot)',
    discount_amount DECIMAL(10, 2) DEFAULT 0.00,
    tax_amount DECIMAL(10, 2) DEFAULT 0.00,
    total_price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_item_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_item_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_order_item_variant FOREIGN KEY (variant_id) REFERENCES product_variants(id),
    CONSTRAINT chk_order_item_quantity CHECK (quantity > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order Status History (Audit trail)
CREATE TABLE IF NOT EXISTS order_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    notes TEXT,
    changed_by INT COMMENT 'NULL if system/automatic',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_history_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_history_changed_by FOREIGN KEY (changed_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shipments & Tracking
CREATE TABLE IF NOT EXISTS shipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    tracking_number VARCHAR(255) UNIQUE,
    carrier VARCHAR(100) COMMENT 'FedEx, DTDC, Delhivery, etc.',
    status VARCHAR(50) DEFAULT 'pending' COMMENT 'pending, in_transit, out_for_delivery, delivered, exception',
    estimated_delivery_date DATE,
    actual_delivery_date TIMESTAMP NULL,
    shipment_date TIMESTAMP NULL,
    current_location VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_shipment_order FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shipment Tracking Events
CREATE TABLE IF NOT EXISTS shipment_tracking_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipment_id INT NOT NULL,
    event_type VARCHAR(50) NOT NULL COMMENT 'dispatched, in_transit, out_for_delivery, delivered, etc.',
    location VARCHAR(255),
    description TEXT,
    event_date TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tracking_shipment FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. PAYMENTS
-- ============================================================================

-- Payment Transactions
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    transaction_id VARCHAR(255) UNIQUE NOT NULL COMMENT 'Gateway transaction ID',
    payment_method VARCHAR(50) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'INR',
    status VARCHAR(50) NOT NULL COMMENT 'pending, processing, success, failed, refunded',
    gateway VARCHAR(50) COMMENT 'razorpay, stripe, etc.',
    gateway_response TEXT COMMENT 'JSON response',
    failure_reason TEXT,
    refund_amount DECIMAL(10, 2) DEFAULT 0.00,
    refund_reason TEXT,
    refunded_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_payment_order FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 7. DISCOUNTS & COUPONS
-- ============================================================================

-- Discount Coupons
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    discount_type VARCHAR(50) NOT NULL COMMENT 'percentage, fixed_amount',
    discount_value DECIMAL(10, 2) NOT NULL,
    minimum_purchase_amount DECIMAL(10, 2) DEFAULT 0.00,
    maximum_discount_amount DECIMAL(10, 2) COMMENT 'For percentage discounts',
    usage_limit INT COMMENT 'Total usage limit (NULL = unlimited)',
    usage_limit_per_user INT DEFAULT 1,
    valid_from TIMESTAMP NOT NULL,
    valid_until TIMESTAMP NOT NULL,
    applicable_to VARCHAR(50) DEFAULT 'all' COMMENT 'all, category, product',
    applicable_category_id INT,
    applicable_product_ids JSON COMMENT 'Array of product IDs as JSON',
    is_active TINYINT(1) DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_coupon_category FOREIGN KEY (applicable_category_id) REFERENCES categories(id),
    CONSTRAINT fk_coupon_created_by FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Coupon Usage Tracking
CREATE TABLE IF NOT EXISTS coupon_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT NOT NULL,
    order_id INT NOT NULL,
    user_id INT COMMENT 'NULL for guest',
    discount_amount DECIMAL(10, 2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_coupon_usage_coupon FOREIGN KEY (coupon_id) REFERENCES coupons(id),
    CONSTRAINT fk_coupon_usage_order FOREIGN KEY (order_id) REFERENCES orders(id),
    CONSTRAINT fk_coupon_usage_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 8. B2B REQUESTS (Business-to-Business)
-- ============================================================================

-- B2B Requests
CREATE TABLE IF NOT EXISTS b2b_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    business_type VARCHAR(100) COMMENT 'retailer, distributor, corporate, etc.',
    gst_number VARCHAR(50),
    address_line1 VARCHAR(255),
    address_line2 VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    country VARCHAR(100) DEFAULT 'India',
    monthly_volume_estimate VARCHAR(100) COMMENT '1-10, 11-50, 50+',
    product_categories TEXT COMMENT 'JSON or comma-separated',
    special_requirements TEXT,
    status VARCHAR(50) DEFAULT 'pending' COMMENT 'pending, under_review, approved, rejected',
    notes TEXT COMMENT 'Admin notes',
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_b2b_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- B2B Customers (Approved B2B clients)
CREATE TABLE IF NOT EXISTS b2b_customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    b2b_request_id INT,
    company_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    gst_number VARCHAR(50),
    credit_limit DECIMAL(12, 2) DEFAULT 0.00,
    payment_terms VARCHAR(100) COMMENT 'Net 30, Net 60, etc.',
    discount_percentage DECIMAL(5, 2) DEFAULT 0.00,
    status VARCHAR(50) DEFAULT 'active',
    assigned_to INT COMMENT 'Account manager',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_b2b_customer_request FOREIGN KEY (b2b_request_id) REFERENCES b2b_requests(id),
    CONSTRAINT fk_b2b_customer_assigned FOREIGN KEY (assigned_to) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 9. REVIEWS & RATINGS
-- ============================================================================

-- Product Reviews
CREATE TABLE IF NOT EXISTS product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT COMMENT 'NULL for guest reviews',
    order_id INT COMMENT 'Verify purchase',
    rating INT NOT NULL,
    title VARCHAR(255),
    review_text TEXT,
    pros TEXT,
    cons TEXT,
    is_verified_purchase TINYINT(1) DEFAULT 0,
    is_approved TINYINT(1) DEFAULT 0 COMMENT 'Admin moderation',
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_review_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_review_order FOREIGN KEY (order_id) REFERENCES orders(id),
    CONSTRAINT chk_review_rating CHECK (rating >= 1 AND rating <= 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Review Helpful Votes
CREATE TABLE IF NOT EXISTS review_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    user_id INT NOT NULL,
    is_helpful TINYINT(1) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_review_user (review_id, user_id),
    CONSTRAINT fk_vote_review FOREIGN KEY (review_id) REFERENCES product_reviews(id) ON DELETE CASCADE,
    CONSTRAINT fk_vote_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 10. CONTACT & SUPPORT
-- ============================================================================

-- Contact Form Submissions
CREATE TABLE IF NOT EXISTS contact_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'new' COMMENT 'new, read, replied, resolved',
    replied_by INT,
    replied_at TIMESTAMP NULL,
    reply_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_contact_replied_by FOREIGN KEY (replied_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Newsletter Subscriptions
CREATE TABLE IF NOT EXISTS newsletter_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at TIMESTAMP NULL,
    source VARCHAR(100) COMMENT 'checkout, footer, popup, etc.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 11. FINANCIAL & REPORTING (Admin)
-- ============================================================================

-- Financial Transactions (All money movements)
CREATE TABLE IF NOT EXISTS financial_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_type VARCHAR(50) NOT NULL COMMENT 'sale, refund, expense, purchase',
    reference_type VARCHAR(50) COMMENT 'order, purchase_order, expense, etc.',
    reference_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'INR',
    description TEXT,
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Expense Categories
CREATE TABLE IF NOT EXISTS expense_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Expenses
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    description TEXT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    expense_date DATE NOT NULL,
    payment_method VARCHAR(50),
    vendor VARCHAR(255),
    receipt_url VARCHAR(255),
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_expense_category FOREIGN KEY (category_id) REFERENCES expense_categories(id),
    CONSTRAINT fk_expense_created_by FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 12. SYSTEM & AUDIT
-- ============================================================================

-- Admin Activity Log
CREATE TABLE IF NOT EXISTS admin_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT COMMENT 'User who performed the action (admin users only)',
    action VARCHAR(100) NOT NULL COMMENT 'create, update, delete, view',
    module VARCHAR(50) NOT NULL COMMENT 'order, product, user, etc.',
    entity_id INT,
    entity_type VARCHAR(50),
    changes JSON COMMENT 'Before/after values as JSON',
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System Settings
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(50) DEFAULT 'string' COMMENT 'string, number, boolean, json',
    description TEXT,
    category VARCHAR(50) COMMENT 'general, payment, shipping, email, etc.',
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_settings_updated_by FOREIGN KEY (updated_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 13. API INTEGRATION (POS Integration)
-- ============================================================================

-- API Integration Keys
CREATE TABLE IF NOT EXISTS pos_api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_key VARCHAR(50) UNIQUE NOT NULL COMMENT 'naz_xxxxxxxxxxxxxxxx',
    api_secret_hash VARCHAR(255) NOT NULL COMMENT 'Hashed secret',
    name VARCHAR(255) NOT NULL COMMENT 'Integration name',
    description TEXT,
    scopes JSON COMMENT 'Array of allowed scopes: products, orders, finance',
    is_active TINYINT(1) DEFAULT 1,
    expires_at TIMESTAMP NULL,
    last_used_at TIMESTAMP NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_api_created_by FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API Integration Logs
CREATE TABLE IF NOT EXISTS pos_integration_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_key_id INT,
    api_key VARCHAR(50),
    request_type VARCHAR(50) NOT NULL COMMENT 'products, orders, finance',
    request_method VARCHAR(10) NOT NULL COMMENT 'GET, POST, PUT, DELETE',
    endpoint VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    status_code INT,
    response_time_ms INT,
    error_message TEXT,
    request_data JSON,
    response_summary TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_api_key FOREIGN KEY (api_key_id) REFERENCES pos_api_keys(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 14. INDEXES FOR PERFORMANCE
-- ============================================================================

-- Users Indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_phone ON users(phone);
CREATE INDEX idx_users_role_id ON users(role_id);
CREATE INDEX idx_users_created_at ON users(created_at);

-- Products Indexes
CREATE INDEX idx_products_sku ON products(sku);
CREATE INDEX idx_products_slug ON products(slug);
CREATE INDEX idx_products_category_id ON products(category_id);
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_products_price ON products(price);
CREATE INDEX idx_products_created_at ON products(created_at);

-- Cart Indexes
CREATE INDEX idx_cart_items_user_id ON cart_items(user_id);
CREATE INDEX idx_cart_items_session_id ON cart_items(session_id);
CREATE INDEX idx_cart_items_product_id ON cart_items(product_id);

-- Orders Indexes
CREATE INDEX idx_orders_order_number ON orders(order_number);
CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_order_date ON orders(order_date);
CREATE INDEX idx_orders_payment_status ON orders(payment_status);

-- Order Items Indexes
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_order_items_product_id ON order_items(product_id);

-- Stock Movements Indexes
CREATE INDEX idx_stock_movements_product_id ON stock_movements(product_id);
CREATE INDEX idx_stock_movements_created_at ON stock_movements(created_at);

-- Payment Transactions Indexes
CREATE INDEX idx_payment_transactions_order_id ON payment_transactions(order_id);
CREATE INDEX idx_payment_transactions_transaction_id ON payment_transactions(transaction_id);
CREATE INDEX idx_payment_transactions_status ON payment_transactions(status);

-- Coupon Usage Indexes
CREATE INDEX idx_coupon_usage_coupon_id ON coupon_usage(coupon_id);
CREATE INDEX idx_coupon_usage_user_id ON coupon_usage(user_id);

-- Activity Log Indexes
CREATE INDEX idx_admin_activity_log_user_id ON admin_activity_log(user_id);
CREATE INDEX idx_admin_activity_log_module ON admin_activity_log(module);
CREATE INDEX idx_admin_activity_log_created_at ON admin_activity_log(created_at);

-- API Integration Indexes
CREATE INDEX idx_pos_api_keys_api_key ON pos_api_keys(api_key);
CREATE INDEX idx_pos_api_keys_is_active ON pos_api_keys(is_active);
CREATE INDEX idx_pos_integration_logs_api_key_id ON pos_integration_logs(api_key_id);
CREATE INDEX idx_pos_integration_logs_created_at ON pos_integration_logs(created_at);

-- ============================================================================
-- 15. INITIAL DATA (Seed Data)
-- ============================================================================

-- Insert Roles: Admin and Customer
INSERT INTO roles (name, description, is_active) VALUES
('Admin', 'Administrator with full system access', 1),
('Customer', 'Regular customer role for frontend users', 1)
ON DUPLICATE KEY UPDATE name=name;

-- Insert Default Permissions
INSERT INTO permissions (name, module, description) VALUES
-- Dashboard
('view_dashboard', 'dashboard', 'View dashboard statistics'),
-- Orders
('view_orders', 'orders', 'View orders'),
('manage_orders', 'orders', 'Create, update, cancel orders'),
('update_order_status', 'orders', 'Update order status'),
-- Products
('view_products', 'products', 'View products'),
('manage_products', 'products', 'Create, update, delete products'),
-- Stock
('view_stock', 'stock', 'View stock levels'),
('manage_stock', 'stock', 'Update stock, create purchase orders'),
-- Finance
('view_finance', 'finance', 'View financial reports'),
('manage_finance', 'finance', 'Manage expenses, transactions'),
-- Users Management (for admins managing other users)
('view_users', 'users', 'View all users (admin and customer)'),
('manage_users', 'users', 'Manage all users and roles'),
-- Coupons
('view_coupons', 'coupons', 'View coupons'),
('manage_coupons', 'coupons', 'Create, update, delete coupons'),
-- B2B
('view_b2b', 'b2b', 'View B2B requests'),
('manage_b2b', 'b2b', 'Approve/reject B2B requests'),
-- Reports
('view_reports', 'reports', 'View reports')
ON DUPLICATE KEY UPDATE name=name;

-- Assign ALL permissions to Admin role (role_id = 1)
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions
ON DUPLICATE KEY UPDATE role_id=role_id;

-- Insert Default Shipping Methods
INSERT INTO shipping_methods (name, code, cost, estimated_days_min, estimated_days_max, is_active) VALUES
('Standard Delivery', 'standard', 0.00, 3, 5, 1),
('Express Delivery', 'express', 450.00, 1, 2, 1)
ON DUPLICATE KEY UPDATE name=name;

-- Insert Default Categories
INSERT INTO categories (name, slug, description, is_active) VALUES
('Women\'s Fashion', 'womens-fashion', 'Elegant women\'s clothing and accessories', 1),
('Men\'s Fashion', 'mens-fashion', 'Stylish men\'s apparel and accessories', 1),
('Accessories', 'accessories', 'Fashion accessories and jewelry', 1),
('Ethnic Wear', 'ethnic-wear', 'Traditional and ethnic clothing', 1),
('Western Wear', 'western-wear', 'Modern western clothing', 1),
('Lehengas', 'lehengas', 'Beautiful lehenga collections', 1),
('Party Wear', 'party-wear', 'Party and occasion wear', 1)
ON DUPLICATE KEY UPDATE name=name;

-- Insert System Settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, category) VALUES
-- General Settings
('site_name', 'BLine Boutique', 'string', 'Website name', 'general'),
('site_email', 'info@blineboutique.com', 'string', 'Contact email', 'general'),
('site_phone', '+91 6238762189', 'string', 'Contact phone', 'general'),
('currency', 'INR', 'string', 'Default currency', 'general'),
('currency_symbol', '₹', 'string', 'Currency symbol', 'general'),
('low_stock_threshold', '10', 'number', 'Low stock alert threshold', 'stock'),
('order_prefix', 'ORD-', 'string', 'Order number prefix', 'orders'),

-- Tax Settings
('tax_enabled', '1', 'boolean', 'Enable/Disable GST Tax', 'tax'),
('tax_rate', '18', 'number', 'GST percentage', 'tax'),
('tax_inclusive', '0', 'boolean', 'Are product prices inclusive of tax', 'tax'),

-- Payment Settings
('payment_online_enabled', '1', 'boolean', 'Enable/Disable Online Payment (Razorpay)', 'payment'),
('payment_cod_enabled', '1', 'boolean', 'Enable/Disable Cash on Delivery', 'payment'),
('razorpay_key', '', 'string', 'Razorpay API Key', 'payment'),
('razorpay_secret', '', 'string', 'Razorpay API Secret', 'payment'),

-- Authentication Settings
('auth_login_enabled', '1', 'boolean', 'Enable/Disable User Login', 'auth'),
('auth_signup_enabled', '1', 'boolean', 'Enable/Disable User Signup/Registration', 'auth'),
('auth_forgot_password_enabled', '1', 'boolean', 'Enable/Disable Forgot Password', 'auth'),
('auth_guest_checkout_enabled', '1', 'boolean', 'Enable/Disable Guest Checkout', 'auth'),

-- Email/Notification Settings
('email_service_enabled', '1', 'boolean', 'Enable/Disable Email Service', 'email'),
('email_notifications_enabled', '1', 'boolean', 'Enable/Disable Email Notifications', 'email'),
('email_order_confirmation', '1', 'boolean', 'Send order confirmation emails', 'email'),
('email_shipping_updates', '1', 'boolean', 'Send shipping update emails', 'email'),
('email_welcome_email', '1', 'boolean', 'Send welcome email on registration', 'email'),
('smtp_host', '', 'string', 'SMTP Host', 'email'),
('smtp_port', '587', 'number', 'SMTP Port', 'email'),
('smtp_username', '', 'string', 'SMTP Username', 'email'),
('smtp_password', '', 'string', 'SMTP Password (encrypted)', 'email'),
('smtp_encryption', 'tls', 'string', 'SMTP Encryption (tls/ssl)', 'email'),
('email_from_name', 'BLine Boutique', 'string', 'From Name for emails', 'email'),
('email_from_address', 'noreply@blineboutique.com', 'string', 'From Email Address', 'email')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- ============================================================================
-- 16. SAMPLE CUSTOMER USERS (Optional - for testing)
-- ============================================================================

-- Sample Customer Users
-- Password for all: Customer123!
-- Note: These are example users for testing. In production, create users through signup.
-- To generate password hash, use PHP: password_hash('Customer123!', PASSWORD_BCRYPT)
-- Example hash (Customer123!): $2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

INSERT INTO users (email, password_hash, first_name, last_name, phone, email_verified, is_active, newsletter_subscribed) VALUES
('customer1@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', '+91 9876543210', 1, 1, 1),
('customer2@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', '+91 9876543211', 1, 1, 0),
('customer3@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Priya', 'Sharma', '+91 9876543212', 1, 1, 1)
ON DUPLICATE KEY UPDATE email=email;

-- ============================================================================
-- 17. VIEWS FOR REPORTING
-- ============================================================================

-- Sales Summary View
CREATE OR REPLACE VIEW sales_summary AS
SELECT 
    DATE(o.order_date) as sale_date,
    COUNT(DISTINCT o.id) as total_orders,
    COUNT(DISTINCT o.user_id) as unique_customers,
    SUM(o.total_amount) as total_revenue,
    SUM(o.subtotal) as total_subtotal,
    SUM(o.tax_amount) as total_tax,
    SUM(o.shipping_cost) as total_shipping,
    SUM(o.discount_amount) as total_discounts,
    AVG(o.total_amount) as average_order_value
FROM orders o
WHERE o.status NOT IN ('cancelled')
GROUP BY DATE(o.order_date);

-- Product Sales View
CREATE OR REPLACE VIEW product_sales_summary AS
SELECT 
    p.id as product_id,
    p.name as product_name,
    p.sku,
    COUNT(oi.id) as times_ordered,
    SUM(oi.quantity) as total_quantity_sold,
    SUM(oi.total_price) as total_revenue,
    AVG(oi.unit_price) as average_selling_price
FROM products p
LEFT JOIN order_items oi ON p.id = oi.product_id
LEFT JOIN orders o ON oi.order_id = o.id AND o.status NOT IN ('cancelled')
GROUP BY p.id, p.name, p.sku;

-- Stock Alert View
CREATE OR REPLACE VIEW low_stock_products AS
SELECT 
    id,
    sku,
    name,
    stock_quantity,
    low_stock_threshold,
    (stock_quantity <= low_stock_threshold) as is_low_stock
FROM products
WHERE status = 'active' AND stock_quantity <= low_stock_threshold;

-- ============================================================================
-- 18. RE-ENABLE FOREIGN KEY CHECKS
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- END OF DATABASE SETUP
-- ============================================================================
-- 
-- IMPORTANT NOTES:
-- 1. ROLE-BASED ACCESS CONTROL: All users (admin and customer) are stored in the users table
-- 2. Admin users have role_id = 1 (Admin role)
-- 3. Customer users have role_id = 2 (Customer role - default)
-- 4. Admin users should be created using setup.php (not included here)
-- 5. Customer users can be created through the signup process
-- 6. Default password for sample customers: Customer123!
-- 7. All roles and permissions are set up
-- 8. System settings are configured with defaults
-- 
-- To create your first admin user, run: setup.php
-- The admin user will be created in the users table with role_id = 1
-- ============================================================================
