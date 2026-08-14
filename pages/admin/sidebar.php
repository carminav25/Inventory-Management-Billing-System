<?php

// Determine current page filename to highlight the active menu item

$current_page = basename($_SERVER['PHP_SELF']);

?>
<?php
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
</head>

<!-- Sidebar Component -->

<aside
id="sidebar"
class="fixed top-0 left-0
w-[270px]
h-screen
bg-[#065F46]
text-white
shadow-lg
overflow-y-auto
z-50
transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out"
>
        <!-- Brand / Logo Header -->

        <div class="px-6 py-6 flex items-center gap-3">

            <img src="../../assets/images/logo.png" alt="Logo" class="w-10 h-10 rounded-full object-cover">

            <span class="font-bold text-sm tracking-wide text-white leading-tight">Inventory Management & Billing System</span>

        </div>



        <!-- Navigation Menu -->

        <nav class="py-2 flex flex-col gap-5 text-xs font-medium">

            <!-- MAIN -->

            <div>

                <p class="px-6 text-[10px] font-bold text-emerald-300 uppercase tracking-wider mb-2">Main</p>

                <div class="space-y-1">

                    <a href="index.php" class="no-underline px-6 py-2.5 flex items-center gap-3 text-sm transition-colors <?= ($current_page == 'index.php') ? 'bg-[#0B7A4B] rounded-lg mx-3 px-3 text-white font-medium shadow-md' : 'text-emerald-100 hover:text-white hover:bg-white/10 rounded-lg mx-3 px-3'; ?>">
                        <i class="fa-solid fa-chart-line w-5 text-center text-lg"></i> Dashboard
                    </a>

                </div>

         

            <!-- INVENTORY -->

            <div>

                <p class="px-6 text-[10px] font-bold text-emerald-300 uppercase tracking-wider mb-2">Inventory</p>

                <div class="space-y-1">

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

                </div>

            



            



            <!-- REPORTS -->

            <div>

                <p class="px-6 text-[10px] font-bold text-emerald-300 uppercase tracking-wider mb-2">Reports</p>

                <div class="space-y-1">

                    <a href="reports.php" class="no-underline px-6 py-2.5 flex items-center gap-3 text-sm transition-colors <?= ($current_page == 'reports.php') ? 'bg-[#0B7A4B] rounded-lg mx-3 px-3 text-white font-medium shadow-md' : 'text-emerald-100 hover:text-white hover:bg-white/10 rounded-lg mx-3 px-3'; ?>">
                        <i class="fa-solid fa-file-lines w-5 text-center text-lg"></i> Inventory Report
                  </a>

                    </div>

                    </div>




            <!-- SYSTEM -->

            <div>

                <p class="px-6 text-[10px] font-bold text-emerald-300 uppercase tracking-wider mb-2">System</p>


                    <div class="space-y-1">

                    <a href="backup_restore.php" class="no-underline px-6 py-2.5 flex items-center gap-3 text-sm transition-colors <?= ($current_page == 'backup_restore.php') ? 'bg-[#0B7A4B] rounded-lg mx-3 px-3 text-white font-medium shadow-md' : 'text-emerald-100 hover:text-white hover:bg-white/10 rounded-lg mx-3 px-3'; ?>">
                        <i class="fa-solid fa-database w-5 text-center text-lg"></i> Backup & Restore
                    </a>
                </div>

            </div>

        </nav>
                        
    <!-- Bottom Logout Area -->
<div class="absolute bottom-0 left-0 right-0 p-4 border-t-0">        <a href="../../logout.php" class="no-underline flex items-center gap-3 px-3 py-2.5 text-sm text-emerald-100 hover:bg-red-500/20 hover:text-red-200 transition-colors rounded-xl w-full">
            <i class="fa-solid fa-right-from-bracket w-5 text-center text-lg"></i> Logout
        </a>
    </div>
                   

</aside>
