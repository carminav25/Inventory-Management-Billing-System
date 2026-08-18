<?php

$current_page = basename($_SERVER['PHP_SELF']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";

requireAdmin();

?>

<link
    rel="stylesheet"
    href="../../assets/css/sidebar.css?v=<?= filemtime(__DIR__ . '/../../assets/css/sidebar.css'); ?>"
>
<link
    rel="stylesheet"
    href="../../assets/css/admin-design.css?v=<?= filemtime(__DIR__ . '/../../assets/css/admin-design.css'); ?>"
>

<aside id="sidebar" aria-label="Admin navigation">

    <!-- =====================================================
         GREEN BRAND HEADER
    ====================================================== -->

    <div class="sidebar-brand">

        <div class="sidebar-logo-wrap">

            <img
                src="../../assets/images/logo.png"
                alt="ISU CBAO Merch Billing Logo"
                class="sidebar-logo"
            >

        </div>


        <div class="brand-text">

            <div class="brand-title">
                ISU CBAO Merch Billing
            </div>

            <div class="brand-subtitle">
                Inventory Management &amp; Billing System
            </div>

        </div>

    </div>


    <!-- =====================================================
         SIDEBAR NAVIGATION
    ====================================================== -->

    <nav class="sidebar-nav">

        <!-- =========================
             MAIN
        ========================== -->

        <div class="section-title">
            MAIN
        </div>


        <a
            href="index.php"
            class="sidebar-link <?= ($current_page === 'index.php') ? 'active' : ''; ?>"
        >
            <span class="sidebar-label">
                Dashboard
            </span>
        </a>


        <!-- =========================
             INVENTORY
        ========================== -->

        <div class="section-title">
            INVENTORY
        </div>


        <a
            href="products.php"
            class="sidebar-link <?= in_array(
                $current_page,
                [
                    'products.php',
                    'add_product.php',
                    'edit_product.php'
                ],
                true
            ) ? 'active' : ''; ?>"
        >
            <span class="sidebar-label">
                Products
            </span>
        </a>


        <a
            href="suppliers.php"
            class="sidebar-link <?= in_array(
                $current_page,
                [
                    'suppliers.php',
                    'add_supplier.php',
                    'edit_supplier.php'
                ],
                true
            ) ? 'active' : ''; ?>"
        >
            <span class="sidebar-label">
                Suppliers
            </span>
        </a>


        <a
            href="inventory_indeliveries.php"
            class="sidebar-link <?= in_array(
                $current_page,
                [
                    'inventory_indeliveries.php',
                    'delivery_form.php',
                    'delivery_view.php',
                    'delivery_print.php'
                ],
                true
            ) ? 'active' : ''; ?>"
        >
            <span class="sidebar-label">
                Inventory In
            </span>
        </a>


        <a
            href="inventory_outsales.php"
            class="sidebar-link <?= in_array(
                $current_page,
                [
                    'inventory_outsales.php',
                    'sale_form.php',
                    'sale_view.php',
                    'sale_print.php'
                ],
                true
            ) ? 'active' : ''; ?>"
        >
            <span class="sidebar-label">
                Inventory Out
            </span>
        </a>


        <a
            href="returns.php"
            class="sidebar-link <?= in_array(
                $current_page,
                [
                    'returns.php',
                    'return_form.php',
                    'return_view.php',
                    'return_print.php'
                ],
                true
            ) ? 'active' : ''; ?>"
        >
            <span class="sidebar-label">
                Supplier Returns
            </span>
        </a>


        <!-- =========================
             REPORTS
        ========================== -->

        <div class="section-title">
            REPORTS
        </div>


        <a
            href="reports.php"
            class="sidebar-link <?= ($current_page === 'reports.php') ? 'active' : ''; ?>"
        >
            <span class="sidebar-label">
                Inventory Report
            </span>
        </a>


        <!-- =========================
             SYSTEM
        ========================== -->

        <div class="section-title">
            SYSTEM
        </div>


        <a
            href="backup_restore.php"
            class="sidebar-link <?= ($current_page === 'backup_restore.php') ? 'active' : ''; ?>"
        >
            <span class="sidebar-label">
                Backup &amp; Restore
            </span>
        </a>

    </nav>


    <!-- =====================================================
         LOGOUT
    ====================================================== -->

    <div class="sidebar-footer">

        <a
            href="../../logout.php"
            class="logout-link"
            onclick="return confirm('Are you sure you want to logout?');"
        >
            <span class="logout-label">
                Logout
            </span>
        </a>

    </div>

</aside>
