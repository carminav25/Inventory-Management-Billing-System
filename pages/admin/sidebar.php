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

<style>
/* =========================================================
   SIDEBAR BRAND SIZE FIX
   ========================================================= */

#sidebar .sidebar-brand {
    min-height: 125px !important;
    height: 125px !important;
    padding: 18px 15px !important;

    display: flex !important;
    align-items: center !important;
    gap: 12px !important;

    box-sizing: border-box !important;
}

/* Logo container */
#sidebar .sidebar-logo-wrap {
    width: 54px !important;
    height: 54px !important;

    min-width: 54px !important;
    min-height: 54px !important;

    flex-shrink: 0 !important;

    display: flex !important;
    align-items: center !important;
    justify-content: center !important;

    background: #ffffff !important;
    border-radius: 14px !important;
    overflow: hidden !important;
}

/* Actual logo */
#sidebar .sidebar-logo {
    width: 44px !important;
    height: 44px !important;

    max-width: 44px !important;
    max-height: 44px !important;

    object-fit: contain !important;
    display: block !important;
}

/* Brand text container */
#sidebar .brand-text {
    min-width: 0 !important;
    flex: 1 !important;

    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
}

/* Main title */
#sidebar .brand-title {
    font-size: 15px !important;
    line-height: 1.2 !important;
    font-weight: 800 !important;

    color: #ffffff !important;

    white-space: nowrap !important;
}

/* Subtitle */
#sidebar .brand-subtitle {
    margin-top: 5px !important;

    font-size: 8.5px !important;
    line-height: 1.25 !important;
    font-weight: 500 !important;

    color: rgba(255,255,255,0.95) !important;

    white-space: nowrap !important;
}

/* Keep sidebar navigation below the larger brand */
#sidebar .sidebar-nav {
    padding-top: 8px !important;
}

/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 767px) {

    #sidebar .sidebar-brand {
        min-height: 110px !important;
        height: 110px !important;
        padding: 15px !important;
    }

    #sidebar .sidebar-logo-wrap {
        width: 50px !important;
        height: 50px !important;
        min-width: 50px !important;
        min-height: 50px !important;
    }

    #sidebar .sidebar-logo {
        width: 40px !important;
        height: 40px !important;
        max-width: 40px !important;
        max-height: 40px !important;
    }

    #sidebar .brand-title {
        font-size: 14px !important;
    }

    #sidebar .brand-subtitle {
        font-size: 8px !important;
    }
}
</style>


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