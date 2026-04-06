<?php
/**
 * Common Admin Styles
 * Include this file in the <head> section of admin pages
 * 
 * Usage: include 'common-styles.php';
 */
?>
<style>
    :root {
        --brand-color: #14b8a6;
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: #f9fafb;
        color: #1a1a1a;
    }

    .text-brand { color: var(--brand-color); }
    .bg-brand { background-color: var(--brand-color); }
    .border-brand { border-color: var(--brand-color); }

    /* Sidebar Styles */
    .admin-sidebar {
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        width: 260px;
        background: white;
        border-right: 1px solid #e5e7eb;
        transition: transform 0.3s ease;
        z-index: 1000;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }

    .admin-sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .admin-sidebar::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .admin-sidebar::-webkit-scrollbar-thumb {
        background: var(--brand-color);
        border-radius: 3px;
    }

    .admin-sidebar-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        color: #6b7280;
        text-decoration: none;
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }

    .admin-sidebar-item:hover {
        background: #f0fdfa;
        color: var(--brand-color);
    }

    .admin-sidebar-item.active {
        background: #f0fdfa;
        color: var(--brand-color);
        border-left-color: var(--brand-color);
        font-weight: 600;
    }

    .admin-sidebar-item i {
        width: 20px;
        height: 20px;
    }

    /* Main Content Area */
    .admin-main {
        margin-left: 260px;
        min-height: 100vh;
        transition: margin-left 0.3s ease;
    }

    /* Top Header */
    .admin-header {
        background: white;
        border-bottom: 1px solid #e5e7eb;
        padding: 16px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 100;
    }

    /* Mobile Menu Overlay */
    .mobile-menu-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        backdrop-filter: blur(4px);
    }

    .mobile-menu-overlay.show {
        display: block;
    }

    /* Card Styles */
    .card, .stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
    }

    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(20, 184, 166, 0.15);
        transform: translateY(-2px);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .admin-sidebar {
            transform: translateX(-100%);
        }

        .admin-sidebar.show {
            transform: translateX(0);
        }

        .admin-main {
            margin-left: 0;
        }
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    ::-webkit-scrollbar-thumb {
        background: var(--brand-color);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #0d9488;
    }

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-overlay.show {
        display: flex;
    }

    .modal-content, .modal {
        background: white;
        border-radius: 12px;
        max-width: 600px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        transform: scale(0.95);
        opacity: 0;
        transition: all 0.3s ease;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .modal-overlay.show .modal-content,
    .modal-overlay.show .modal {
        transform: scale(1);
        opacity: 1;
    }

    .modal-large {
        max-width: 900px;
    }

    /* Order Detail Styles */
    .order-detail-header {
        background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
        color: white;
        padding: 24px 32px;
        border-radius: 16px 16px 0 0;
    }

    .order-detail-body {
        padding: 24px 32px;
    }

    .detail-section {
        margin-bottom: 24px;
    }

    .detail-section:last-child {
        margin-bottom: 0;
    }

    .detail-section-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .detail-card {
        background: #f9fafb;
        border-radius: 12px;
        padding: 16px;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 8px 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        color: #6b7280;
        font-size: 0.875rem;
    }

    .detail-value {
        color: #111827;
        font-weight: 500;
        font-size: 0.875rem;
        text-align: right;
    }

    .order-item {
        display: flex;
        gap: 12px;
        padding: 12px;
        background: white;
        border-radius: 8px;
        margin-bottom: 8px;
        border: 1px solid #e5e7eb;
    }

    .order-item:last-child {
        margin-bottom: 0;
    }

    .order-item-image {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .order-item-details {
        flex: 1;
        min-width: 0;
    }

    .order-item-name {
        font-weight: 500;
        color: #111827;
        font-size: 0.875rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .order-item-sku {
        color: #9ca3af;
        font-size: 0.75rem;
    }

    .order-item-qty-price {
        text-align: right;
        flex-shrink: 0;
    }

    .order-item-price {
        font-weight: 600;
        color: #111827;
    }

    .order-item-qty {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .totals-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: 0.875rem;
    }

    .totals-row.total {
        font-size: 1rem;
        font-weight: 700;
        color: #14b8a6;
        border-top: 2px solid #e5e7eb;
        padding-top: 12px;
        margin-top: 4px;
    }

    .timeline {
        position: relative;
        padding-left: 24px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 7px;
        top: 8px;
        bottom: 8px;
        width: 2px;
        background: #e5e7eb;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 16px;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-dot {
        position: absolute;
        left: -24px;
        top: 2px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #e5e7eb;
        border: 3px solid white;
        box-shadow: 0 0 0 2px #e5e7eb;
    }

    .timeline-dot.active {
        background: #22c55e;
        box-shadow: 0 0 0 2px #22c55e;
    }

    .timeline-dot.cancelled {
        background: #14b8a6;
        box-shadow: 0 0 0 2px #14b8a6;
    }

    .timeline-content {
        font-size: 0.875rem;
    }

    .timeline-title {
        font-weight: 500;
        color: #111827;
    }

    .timeline-date {
        color: #9ca3af;
        font-size: 0.75rem;
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: capitalize;
    }

    .status-confirmed {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-processing {
        background: #fef3c7;
        color: #92400e;
    }

    .status-shipped {
        background: #e9d5ff;
        color: #6b21a8;
    }

    .status-delivered {
        background: #d1fae5;
        color: #065f46;
    }

    .status-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Payment Badges */
    .payment-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .payment-cod {
        background: #fef3c7;
        color: #92400e;
    }

    .payment-online {
        background: #dbeafe;
        color: #1e40af;
    }

    .payment-pending {
        color: #d97706;
    }

    .payment-completed {
        color: #059669;
    }

    .payment-failed {
        color: #0d9488;
    }
</style>
