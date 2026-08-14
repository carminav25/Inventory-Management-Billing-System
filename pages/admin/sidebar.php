<?php

// Determine current page filename to highlight the active menu item
$current_page = basename($_SERVER['PHP_SELF']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";

// Ensure user is an Admin
requireAdmin();

?>

<style>

/* =========================================================
   ISU CBAO MERCH BILLING
   CLEAN LIGHT SIDEBAR
   NO SCROLLING
   NO MENU ICONS
   OFFICIAL LOGO RETAINED
   ========================================================= */


/* =========================================================
   SIDEBAR
   ========================================================= */

#sidebar {

    width: 270px;
    height: 100vh;

    background: #ffffff;

    color: #1f2937;

    border-right: 1px solid #e5e7eb;

    box-shadow:
        4px 0 18px rgba(0, 0, 0, 0.05);

    position: fixed;

    top: 0;
    left: 0;

    z-index: 50;

    overflow: hidden;
}


/* =========================================================
   BRAND HEADER
   ========================================================= */

#sidebar .sidebar-brand {

    height: 92px;

    padding: 0 20px;

    display: flex;

    align-items: center;

    gap: 12px;

    background: #ecfdf5;

    border-bottom: 1px solid #d1fae5;
}


/* =========================================================
   LOGO CONTAINER
   ========================================================= */

#sidebar .sidebar-logo-wrap {

    width: 48px;
    height: 48px;

    flex: 0 0 48px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #ffffff;

    border: 1px solid #d1fae5;

    border-radius: 12px;

    overflow: hidden;

    box-shadow:
        0 2px 6px rgba(6, 95, 70, 0.05);
}


/* =========================================================
   OFFICIAL LOGO
   ========================================================= */

#sidebar .sidebar-logo {

    width: 42px;
    height: 42px;

    object-fit: contain;

    display: block;
}


/* =========================================================
   BRAND TEXT
   ========================================================= */

#sidebar .brand-text {

    min-width: 0;
}


#sidebar .brand-title {

    color: #065f46;

    font-size: 13px;

    line-height: 1.3;

    font-weight: 700;

    letter-spacing: .01em;
}


#sidebar .brand-subtitle {

    color: #4b7f6b;

    font-size: 9.5px;

    line-height: 1.3;

    font-weight: 400;

    margin-top: 3px;
}


/* =========================================================
   SIDEBAR CONTENT
   ========================================================= */

#sidebar .sidebar-content {

    height: calc(100vh - 92px);

    padding: 14px 0 68px;

    background: #ffffff;

    overflow: hidden;
}


/* =========================================================
   SECTIONS
   ========================================================= */

#sidebar .sidebar-section {

    margin-bottom: 12px;
}


#sidebar .section-title {

    margin: 0 20px 4px;

    color: #059669;

    font-size: 9px;

    line-height: 1.2;

    font-weight: 700;

    letter-spacing: .13em;

    text-transform: uppercase;
}


/* =========================================================
   MENU LINKS
   ========================================================= */

#sidebar .sidebar-link {

    display: flex;

    align-items: center;

    min-height: 39px;

    margin: 2px 12px;

    padding: 8px 14px;

    border-radius: 9px;

    color: #334155;

    background: transparent;

    text-decoration: none !important;

    font-size: 13px;

    line-height: 1.2;

    font-weight: 500;

    transition:
        background-color .18s ease,
        color .18s ease,
        transform .18s ease,
        box-shadow .18s ease;
}


/* =========================================================
   HOVER
   ========================================================= */

#sidebar .sidebar-link:hover {

    color: #065f46;

    background: #ecfdf5;

    transform: translateX(2px);
}


/* =========================================================
   ACTIVE MENU
   ========================================================= */

#sidebar .sidebar-link.active {

    color: #ffffff;

    background: #10b981;

    font-weight: 700;

    box-shadow:
        0 4px 12px rgba(16, 185, 129, .20);
}


#sidebar .sidebar-link.active:hover {

    color: #ffffff;

    background: #10b981;

    transform: none;
}


/* =========================================================
   LOGOUT AREA
   ========================================================= */

#sidebar .sidebar-footer {

    position: absolute;

    left: 0;
    right: 0;
    bottom: 0;

    padding: 10px 12px;

    background: #ffffff;

    border-top: 1px solid #e5e7eb;
}


/* =========================================================
   LOGOUT BUTTON
   ========================================================= */

#sidebar .logout-link {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 100%;

    height: 40px;

    border-radius: 9px;

    color: #475569;

    background: #f8fafc;

    border: 1px solid #e5e7eb;

    text-decoration: none !important;

    font-size: 13px;

    font-weight: 600;

    transition:
        background-color .18s ease,
        color .18s ease,
        border-color .18s ease;
}


/* =========================================================
   LOGOUT HOVER
   ========================================================= */

#sidebar .logout-link:hover {

    color: #dc2626;

    background: #fef2f2;

    border-color: #fecaca;
}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 767px) {

    #sidebar {

        width: 270px;

    }

}

</style>


<!-- =========================================================
     SIDEBAR
     ========================================================= -->

<aside
    id="sidebar"
    aria-label="Admin navigation"
>


    <!-- =====================================================
         BRAND / LOGO HEADER
         ===================================================== -->

    <div class="sidebar-brand">


        <!-- OFFICIAL ISU LOGO -->

        <div class="sidebar-logo-wrap">

            <img
                src="../../assets/images/logo.png"
                alt="ISU CBAO Merch Billing Logo"
                class="sidebar-logo"
            >

        </div>


        <!-- SYSTEM NAME -->

        <div class="brand-text">

            <div class="brand-title">

                ISU CBAO Merch Billing

            </div>


                    <a href="index.php" class="no-underline px-6 py-2.5 flex items-center gap-3 text-sm transition-colors <?= ($current_page == 'index.php') ? 'bg-[#0B7A4B] rounded-lg mx-3 px-3 text-white font-medium shadow-md' : 'text-emerald-100 hover:text-white hover:bg-white/10 rounded-lg mx-3 px-3'; ?>">
                        <i class="fa-solid fa-chart-line w-5 text-center text-lg"></i> Dashboard
                    </a>
            <div class="brand-subtitle">

                Inventory Management &amp; Billing System

            </div>

        </div>


    </div>


                    <a href="products.php" class="no-underline px-6 py-2.5 flex items-center gap-3 text-sm transition-colors <?= ($current_page == 'products.php' || $current_page == 'add_product.php') ? 'bg-[#0B7A4B] rounded-lg mx-3 px-3 text-white font-medium shadow-md' : 'text-emerald-100 hover:text-white hover:bg-white/10 rounded-lg mx-3 px-3'; ?>">
                        <i class="fa-solid fa-box w-5 text-center text-lg"></i> Products
                    </a>

                    <a href="suppliers.php" class="no-underline px-6 py-2.5 flex items-center gap-3 text-sm transition-colors <?= ($current_page == 'suppliers.php' || $current_page == 'add_supplier.php') ? 'bg-[#0B7A4B] rounded-lg mx-3 px-3 text-white font-medium shadow-md' : 'text-emerald-100 hover:text-white hover:bg-white/10 rounded-lg mx-3 px-3'; ?>">
                        <i class="fa-solid fa-truck w-5 text-center text-lg"></i> Suppliers
                    </a>
    <!-- =====================================================
         NAVIGATION
         ===================================================== -->

    <div class="sidebar-content">


        <!-- =================================================
             MAIN
             ================================================= -->

        <section class="sidebar-section">

            <div class="section-title">
                Main
            </div>


            <a
                href="index.php"
                class="sidebar-link <?= ($current_page === 'index.php') ? 'active' : ''; ?>"
            >
                Dashboard
            </a>

        </section>


        <!-- =================================================
             INVENTORY
             ================================================= -->

        <section class="sidebar-section">

            <div class="section-title">
                Inventory
            </div>


            <!-- PRODUCTS -->

            <a
                href="products.php"
                class="sidebar-link <?= in_array(
                    $current_page,
                    [
                        'products.php',
                        'add_product.php'
                    ],
                    true
                ) ? 'active' : ''; ?>"
            >
                Products
            </a>


            <!-- SUPPLIERS -->

            <a
                href="suppliers.php"
                class="sidebar-link <?= in_array(
                    $current_page,
                    [
                        'suppliers.php',
                        'add_supplier.php'
                    ],
                    true
                ) ? 'active' : ''; ?>"
            >
                Suppliers
            </a>


            <!-- INVENTORY IN -->

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
                Inventory In (Deliveries)
            </a>


            <!-- INVENTORY OUT -->

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
                Inventory Out (Sales)
            </a>


            <!-- SUPPLIER RETURNS -->

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
                Supplier Returns
            </a>

        </section>


        <!-- =================================================
             REPORTS
             ================================================= -->

        <section class="sidebar-section">

            <div class="section-title">
                Reports
            </div>


            <a
                href="reports.php"
                class="sidebar-link <?= ($current_page === 'reports.php') ? 'active' : ''; ?>"
            >
                Inventory Report
            </a>

        </section>


        <!-- =================================================
             SYSTEM
             ================================================= -->

        <section class="sidebar-section">

            <div class="section-title">
                System
            </div>


            <a
                href="backup_restore.php"
                class="sidebar-link <?= ($current_page === 'backup_restore.php') ? 'active' : ''; ?>"
            >
                Backup &amp; Restore
            </a>

        </section>


    </div>


    <!-- =====================================================
         LOGOUT
         ===================================================== -->

    <div class="sidebar-footer">

        <a
            href="../../logout.php"
            class="logout-link"
            onclick="return confirm('Are you sure you want to logout?');"
        >
            Logout
        </a>

    </div>


</aside>
```
