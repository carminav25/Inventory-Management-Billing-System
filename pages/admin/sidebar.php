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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?= htmlspecialchars($pageTitle ?? 'Admin Dashboard'); ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/admin-ui.css">
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        body { background-color: #F5F7FA; font-family: 'Poppins', Arial, sans-serif; overflow:hidden; }
        .bg-brand-green { background-color: #0B7A4B; }
        main { height:100vh; overflow-y:auto; }
        /* Global override to eliminate Bootstrap link underlines inside the sidebar */
        #sidebar a { text-decoration: none !important; }
    </style>
    <link rel="stylesheet" href="../../assets/css/semantic-theme.css">
</head>

<!-- Sidebar Component -->

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


                    <a href="inventory_indeliveries.php" class="no-underline px-6 py-2.5 flex items-center gap-3 text-sm transition-colors <?= in_array($current_page, ['inventory_indeliveries.php', 'delivery_form.php', 'delivery_view.php', 'delivery_print.php']) ? 'bg-[#0B7A4B] rounded-lg mx-3 px-3 text-white font-medium shadow-md' : 'text-emerald-100 hover:text-white hover:bg-white/10 rounded-lg mx-3 px-3'; ?>">
                        <i class="fa-solid fa-truck-ramp-box w-5 text-center text-lg"></i> Inventory In (Deliveries)
                    </a>

                    <a href="inventory_outsales.php" class="no-underline px-6 py-2.5 flex items-center gap-3 text-sm transition-colors <?= in_array($current_page, ['inventory_outsales.php', 'sale_form.php', 'sale_view.php', 'sale_print.php']) ? 'bg-[#0B7A4B] rounded-lg mx-3 px-3 text-white font-medium shadow-md' : 'text-emerald-100 hover:text-white hover:bg-white/10 rounded-lg mx-3 px-3'; ?>">
                        <i class="fa-solid fa-cart-shopping w-5 text-center text-lg"></i> Inventory Out (Sales)
                    </a>

                    <a href="returns.php" class="no-underline px-6 py-2.5 flex items-center gap-3 text-sm transition-colors <?= in_array($current_page, ['returns.php', 'return_form.php', 'return_view.php', 'return_print.php']) ? 'bg-[#0B7A4B] rounded-lg mx-3 px-3 text-white font-medium shadow-md' : 'text-emerald-100 hover:text-white hover:bg-white/10 rounded-lg mx-3 px-3'; ?>">
                        <i class="fa-solid fa-rotate-left w-5 text-center text-lg"></i> Supplier Returns
                    </a>


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

                    <a href="reports.php" class="no-underline px-6 py-2.5 flex items-center gap-3 text-sm transition-colors <?= ($current_page == 'reports.php') ? 'bg-[#0B7A4B] rounded-lg mx-3 px-3 text-white font-medium shadow-md' : 'text-emerald-100 hover:text-white hover:bg-white/10 rounded-lg mx-3 px-3'; ?>">
                        <i class="fa-solid fa-file-lines w-5 text-center text-lg"></i> Inventory Report
                  </a>


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


                    <a href="backup_restore.php" class="no-underline px-6 py-2.5 flex items-center gap-3 text-sm transition-colors <?= ($current_page == 'backup_restore.php') ? 'bg-[#0B7A4B] rounded-lg mx-3 px-3 text-white font-medium shadow-md' : 'text-emerald-100 hover:text-white hover:bg-white/10 rounded-lg mx-3 px-3'; ?>">
                        <i class="fa-solid fa-database w-5 text-center text-lg"></i> Backup & Restore
                    </a>
                </div>

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
