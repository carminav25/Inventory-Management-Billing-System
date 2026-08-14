<?php
$pageTitle = "Dashboard";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";

requireAdmin();

/*
|--------------------------------------------------------------------------
| DASHBOARD STATISTICS & METRICS
|--------------------------------------------------------------------------
*/

// Total Products & Products Added Today
$totalProducts = 0;
$productsToday = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products");
if ($result && $row = mysqli_fetch_assoc($result)) {
    $totalProducts = (int)$row['total'];
}
$resultTodayProd = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE DATE(created_at) = CURDATE()");
if ($resultTodayProd && $row = mysqli_fetch_assoc($resultTodayProd)) {
    $productsToday = (int)$row['total'];
}

// Total Suppliers & New Suppliers
$totalSuppliers = 0;
$suppliersNew = 0;
$tableCheckSuppliers = mysqli_query($conn, "SHOW TABLES LIKE 'suppliers'");
if ($tableCheckSuppliers && mysqli_num_rows($tableCheckSuppliers) > 0) {
    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM suppliers");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $totalSuppliers = (int)$row['total'];
    }
    $resultNewSup = mysqli_query($conn, "SELECT COUNT(*) AS total FROM suppliers WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
    if ($resultNewSup && $row = mysqli_fetch_assoc($resultNewSup)) {
        $suppliersNew = (int)$row['total'];
    }
}

// Available Stock
$totalStock = 0;
$result = mysqli_query($conn, "SELECT SUM(current_stock) AS total FROM products");
if ($result && $row = mysqli_fetch_assoc($result)) {
    $totalStock = (int)($row['total'] ?? 0);
}

// Inventory Value & Value Change
$inventoryValue = 0;
$result = mysqli_query($conn, "
    SELECT SUM(current_stock * unit_cost) AS total
    FROM products
");
if ($result && $row = mysqli_fetch_assoc($result)) {
    $inventoryValue = (float)($row['total'] ?? 0);
}

// Low Stock & Out of Stock
$lowStock = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE current_stock <= reorder_level AND current_stock > 0");
if ($result && $row = mysqli_fetch_assoc($result)) {
    $lowStock = (int)$row['total'];
}

$outOfStock = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE current_stock <= 0");
if ($result && $row = mysqli_fetch_assoc($result)) {
    $outOfStock = (int)$row['total'];
}


/*
|--------------------------------------------------------------------------
| TODAY'S SUMMARY (Deliveries, Sales, Returns)
|--------------------------------------------------------------------------
*/

$todayDeliveries = 0;
$tableCheckDeliveries = mysqli_query($conn, "SHOW TABLES LIKE 'deliveries'");
if ($tableCheckDeliveries && mysqli_num_rows($tableCheckDeliveries) > 0) {
    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM deliveries WHERE DATE(delivery_date)=CURDATE()");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $todayDeliveries = (int)$row['total'];
    }
}

$todaySales = 0;
$tableCheckSales = mysqli_query($conn, "SHOW TABLES LIKE 'sales'");
if ($tableCheckSales && mysqli_num_rows($tableCheckSales) > 0) {
    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM sales WHERE DATE(sale_date)=CURDATE()");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $todaySales = (int)$row['total'];
    }
}

$todayReturns = 0;
$tableCheckReturns = mysqli_query($conn, "SHOW TABLES LIKE 'returns'");
if ($tableCheckReturns && mysqli_num_rows($tableCheckReturns) > 0) {
    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM returns WHERE DATE(return_date)=CURDATE()");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $todayReturns = (int)$row['total'];
    }
}

$todayTransactions = $todayDeliveries + $todaySales + $todayReturns;


/*
|--------------------------------------------------------------------------
| QUICK STATISTICS (Deliveries this week, Sales this month, Returns this month)
|--------------------------------------------------------------------------
*/

$deliveriesWeek = 0;
if ($tableCheckDeliveries && mysqli_num_rows($tableCheckDeliveries) > 0) {
    $res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM deliveries WHERE delivery_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
    if($res && $r = mysqli_fetch_assoc($res)) { $deliveriesWeek = (int)$r['total']; }
}

$salesMonth = 0;
if ($tableCheckSales && mysqli_num_rows($tableCheckSales) > 0) {
    $res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM sales WHERE MONTH(sale_date) = MONTH(CURDATE()) AND YEAR(sale_date) = YEAR(CURDATE())");
    if($res && $r = mysqli_fetch_assoc($res)) { $salesMonth = (int)$r['total']; }
}

$returnsMonth = 0;
if ($tableCheckReturns && mysqli_num_rows($tableCheckReturns) > 0) {
    $res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM returns WHERE MONTH(return_date) = MONTH(CURDATE()) AND YEAR(return_date) = YEAR(CURDATE())");
    if($res && $r = mysqli_fetch_assoc($res)) { $returnsMonth = (int)$r['total']; }
}


/*
|--------------------------------------------------------------------------
| LOW STOCK ALERTS
|--------------------------------------------------------------------------
*/

$lowStockProducts = [];
$result = mysqli_query($conn, "SELECT id, product_name, current_stock, reorder_level, front_image FROM products WHERE current_stock <= reorder_level ORDER BY current_stock ASC LIMIT 10");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $lowStockProducts[] = $row;
    }
}


/*
|--------------------------------------------------------------------------
| RECENT ACTIVITIES (Timeline Feed)
|--------------------------------------------------------------------------
*/

$recentActivities = [];
$tableCheckActivities = mysqli_query($conn, "SHOW TABLES LIKE 'activity_logs'");
if ($tableCheckActivities && mysqli_num_rows($tableCheckActivities) > 0) {
    $activityColumns = [];
    $columnsResult = mysqli_query($conn, "SHOW COLUMNS FROM activity_logs");
    if ($columnsResult) {
        while ($column = mysqli_fetch_assoc($columnsResult)) {
            $activityColumns[] = $column['Field'];
        }
    }
    $moduleSelect = in_array('module', $activityColumns, true) ? 'module' : "'' AS module";
    $result = mysqli_query($conn, "SELECT fullname, action, {$moduleSelect}, created_at FROM activity_logs ORDER BY id DESC LIMIT 6");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $recentActivities[] = $row;
        }
    }
}


/*
|--------------------------------------------------------------------------
| CATEGORY DISTRIBUTION (Chart)
|--------------------------------------------------------------------------
*/

$categoryLabels = [];
$categoryTotals = [];
$result = mysqli_query($conn, "SELECT category, COUNT(*) total FROM products GROUP BY category ORDER BY total DESC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $categoryLabels[] = $row['category'];
        $categoryTotals[] = (int)$row['total'];
    }
}


/*
|--------------------------------------------------------------------------
| INVENTORY MOVEMENT (Last 7 Days - Multi-Series Chart)
|--------------------------------------------------------------------------
*/

$movementLabels = [];
$deliveriesData = [];
$salesData = [];
$returnsData = [];

$hasDeliveries = ($tableCheckDeliveries && mysqli_num_rows($tableCheckDeliveries) > 0);
$hasSales = ($tableCheckSales && mysqli_num_rows($tableCheckSales) > 0);
$hasReturns = ($tableCheckReturns && mysqli_num_rows($tableCheckReturns) > 0);

for ($i = 6; $i >= 0; $i--) {
    $date = date("Y-m-d", strtotime("-{$i} days"));
    $movementLabels[] = date("M d", strtotime($date));

    $dCount = 0; $sCount = 0; $rCount = 0;

    if ($hasDeliveries) {
        $q = mysqli_query($conn, "SELECT COUNT(*) total FROM deliveries WHERE DATE(delivery_date)='$date'");
        if ($q && $r = mysqli_fetch_assoc($q)) { $dCount = (int)$r['total']; }
    }
    if ($hasSales) {
        $q = mysqli_query($conn, "SELECT COUNT(*) total FROM sales WHERE DATE(sale_date)='$date'");
        if ($q && $r = mysqli_fetch_assoc($q)) { $sCount = (int)$r['total']; }
    }
    if ($hasReturns) {
        $q = mysqli_query($conn, "SELECT COUNT(*) total FROM returns WHERE DATE(return_date)='$date'");
        if ($q && $r = mysqli_fetch_assoc($q)) { $rCount = (int)$r['total']; }
    }

    $deliveriesData[] = $dCount;
    $salesData[] = $sCount;
    $returnsData[] = $rCount;
}

$totalTransactionsSum = array_sum($deliveriesData) + array_sum($salesData) + array_sum($returnsData);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin Dashboard'); ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        a { text-decoration: none !important; }
    </style>
</head>

<body class="bg-[#f5f7fb] font-sans">

<?php include "sidebar.php"; ?> 

<main class="ml-0 md:ml-[270px] min-h-screen bg-[#f5f7fb] p-6 transition-all duration-300">

    <!-- =======================================================
        ROW 1: WELCOME + SEARCH + NOTIFICATIONS + QUICK ACTIONS
    ======================================================= -->
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 mb-8 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Dashboard</h1>
                <span class="text-2xl">👋</span>
            </div>
            <p class="text-slate-500 mt-1">
                Welcome back, <span class="font-semibold text-emerald-700"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Administrator'); ?></span>
            </p>
            <p class="text-xs text-slate-400 mt-0.5">
                <i class="fa-solid fa-calendar-days mr-1"></i> Today: <?= date('F j, Y, g:i A'); ?>
            </p>
        </div>

        <!-- Global Search Bar -->
        <div class="relative flex-1 max-w-md mx-0 xl:mx-4">
            <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <input type="text" id="globalDashboardSearch" placeholder="Search product, supplier, invoice..." 
                   class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
        </div>

        <!-- Action Shortcuts & Notifications Dropdown Trigger -->
        <div class="flex items-center gap-3">
            <!-- Quick Action Buttons -->
            <a href="products.php" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl shadow transition text-sm font-medium flex items-center gap-2">
                <i class="fa-solid fa-box"></i> Product
            </a>
            <a href="inventory_indeliveries.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl shadow transition text-sm font-medium flex items-center gap-2">
                <i class="fa-solid fa-truck-ramp-box"></i> Delivery
            </a>
            <a href="inventory_outsales.php" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2.5 rounded-xl shadow transition text-sm font-medium flex items-center gap-2">
                <i class="fa-solid fa-cart-shopping"></i> Sale
            </a>
            <a href="reports.php" class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2.5 rounded-xl shadow transition text-sm font-medium flex items-center gap-2">
                <i class="fa-solid fa-chart-pie"></i> Reports
            </a>
        </div>

    </div>


    <!-- =======================================================
        ROW 2: 6 KPI CARDS WITH TREND SUBTITLES
    ======================================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">

        <!-- Products -->
        <a href="products.php" class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-6 border border-slate-200 border-l-4 border-l-emerald-600 relative overflow-hidden group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs uppercase tracking-wider font-semibold text-slate-400">Total Products</p>
                    <h2 class="text-3xl font-extrabold text-slate-800 mt-1"><?= number_format($totalProducts); ?></h2>
                    <p class="text-xs text-emerald-600 font-medium mt-2 flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> +<?= $productsToday; ?> added today
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition text-2xl">
                    <i class="fa-solid fa-box"></i>
                </div>
            </div>
        </a>

        <!-- Suppliers -->
        <a href="suppliers.php" class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-6 border border-slate-200 border-l-4 border-l-blue-600 relative overflow-hidden group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs uppercase tracking-wider font-semibold text-slate-400">Total Suppliers</p>
                    <h2 class="text-3xl font-extrabold text-slate-800 mt-1"><?= number_format($totalSuppliers); ?></h2>
                    <p class="text-xs text-blue-600 font-medium mt-2 flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> +<?= $suppliersNew; ?> this week
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition text-2xl">
                    <i class="fa-solid fa-truck"></i>
                </div>
            </div>
        </a>

        <!-- Inventory Value -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200 border-l-4 border-l-emerald-500 relative overflow-hidden">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs uppercase tracking-wider font-semibold text-slate-400">Inventory Value</p>
                    <h2 class="text-3xl font-extrabold text-slate-800 mt-1">₱<?= number_format($inventoryValue, 2); ?></h2>
                    <p class="text-xs text-emerald-600 font-medium mt-2 flex items-center gap-2">
                        <i class="fa-solid fa-circle-check"></i> Updated just now
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-coins"></i>
                </div>
            </div>
        </div>

        <!-- Available Stocks -->
        <a href="products.php" class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-6 border border-slate-200 border-l-4 border-l-cyan-600 relative overflow-hidden group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs uppercase tracking-wider font-semibold text-slate-400">Available Stocks</p>
                    <h2 class="text-3xl font-extrabold text-slate-800 mt-1"><?= number_format($totalStock); ?></h2>
                    <p class="text-xs text-cyan-600 font-medium mt-2 flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked"></i> Across all categories
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center group-hover:scale-110 transition text-2xl">
                    <i class="fa-solid fa-warehouse"></i>
                </div>
            </div>
        </a>

        <!-- Low Stock -->
        <a href="products.php?filter=low_stock" class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-6 border border-slate-200 border-l-4 border-l-amber-500 relative overflow-hidden group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs uppercase tracking-wider font-semibold text-slate-400">Low Stock Items</p>
                    <h2 class="text-3xl font-extrabold text-amber-600 mt-1"><?= number_format($lowStock); ?></h2>
                    <p class="text-xs text-amber-600 font-medium mt-2 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation"></i> Requires reorder
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition text-2xl">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
        </a>

        <!-- Out Of Stock -->
        <a href="products.php?filter=out_of_stock" class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-6 border border-slate-200 border-l-4 border-l-red-600 relative overflow-hidden group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs uppercase tracking-wider font-semibold text-slate-400">Out of Stock</p>
                    <h2 class="text-3xl font-extrabold text-red-600 mt-1"><?= number_format($outOfStock); ?></h2>
                    <p class="text-xs text-red-600 font-medium mt-2 flex items-center gap-2">
                        <i class="fa-solid fa-circle-xmark"></i> Action required
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center group-hover:scale-110 transition text-2xl">
                    <i class="fa-solid fa-ban"></i>
                </div>
            </div>
        </a>

    </div>


    <!-- =======================================================
        ROW 3: TODAY'S 4 SUMMARY CARDS
    ======================================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">

        <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200 flex justify-between items-center">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Today's Deliveries</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-1"><?= number_format($todayDeliveries); ?></h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200 flex justify-between items-center">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Today's Sales</p>
                <h3 class="text-2xl font-bold text-blue-600 mt-1"><?= number_format($todaySales); ?></h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200 flex justify-between items-center">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Today's Returns</p>
                <h3 class="text-2xl font-bold text-amber-600 mt-1"><?= number_format($todayReturns); ?></h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-rotate-left"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200 flex justify-between items-center">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Transactions</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-1"><?= number_format($todayTransactions); ?></h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>

    </div>


    <!-- =======================================================
        ROW 4: CHARTS (70% Movement, 30% Category Distribution)
    ======================================================= -->
    <div class="grid grid-cols-1 xl:grid-cols-10 gap-6 mb-8">

        <!-- Inventory Movement (70% / 7 cols) -->
        <div class="xl:col-span-7 bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-800">Inventory Movement</h2>
                    <p class="text-xs text-slate-400">Deliveries, Sales, and Returns over the last 7 days.</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
            <div class="p-6 flex-1 flex items-center">
                <div class="w-full h-72">
                    <canvas id="movementChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Category Distribution (30% / 3 cols) -->
        <div class="xl:col-span-3 bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-800">Categories</h2>
                    <p class="text-xs text-slate-400">Distribution share.</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
            </div>
            <div class="p-6 flex-1 flex items-center justify-center">
                <div class="w-full h-64">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

    </div>


    <!-- =======================================================
        ROW 5: RECENT ACTIVITY TIMELINE & LOW STOCK ALERTS (SIDE BY SIDE)
    ======================================================= -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">

        <!-- Recent Activity Timeline -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-800">Recent Activity Timeline</h2>
                    <p class="text-xs text-slate-400">Audit trail of recent administrative actions.</p>
                </div>
                <a href="audit_trails.php" class="text-emerald-600 hover:text-emerald-700 font-medium text-xs flex items-center gap-2">
                    View All <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
            <div class="p-6 flex-1">
                <?php if(empty($recentActivities)): ?>
                    <p class="text-center text-slate-400 text-sm py-4">No recent activity logs recorded.</p>
                <?php else: ?>
                    <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                        <?php foreach($recentActivities as $act): ?>
                            <div class="relative flex items-start gap-3">
                                <span class="absolute -left-6 top-1 w-3 h-3 rounded-full bg-emerald-500 ring-4 ring-white"></span>
                                <div>
                                    <div class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($act['fullname']); ?></div>
                                    <div class="text-xs text-slate-600 mt-0.5"><?= htmlspecialchars($act['action']); ?></div>
                                    <div class="text-[10px] text-slate-400 mt-1.5">
                                        <?= !empty($act['module']) ? htmlspecialchars($act['module']) . ' &bull;' : ''; ?> <?= date('M d, Y | h:i A', strtotime($act['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col">
            <div class="flex justify-between items-center px-6 py-5 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-800">Low Stock Alerts</h2>
                    <p class="text-xs text-slate-400">Products falling at or below reorder levels.</p>
                </div>
                <span class="bg-amber-100 text-amber-700 px-2.5 py-0.5 rounded-full text-xs font-semibold">
                    <?= count($lowStockProducts); ?> Items
                </span>
            </div>
            <div class="divide-y divide-slate-100 flex-1 overflow-y-auto max-h-[380px]">
                <?php if(empty($lowStockProducts)): ?>
                    <div class="p-8 text-center text-emerald-600 h-full flex flex-col items-center justify-center">
                        <i class="fa-solid fa-circle-check text-3xl mb-2"></i>
                        <p class="font-bold text-sm">Inventory Healthy</p>
                        <p class="text-xs text-slate-400 mt-1">No products require reordering right now.</p>
                        <p class="text-[10px] text-slate-300 mt-4">Last checked: <?= date('g:i A'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-slate-100">
                        <?php foreach($lowStockProducts as $product): ?>
                            <div class="px-6 py-3.5 flex items-center justify-between gap-3 hover:bg-slate-50 transition">
                                <div class="flex items-center gap-3">
                                    <img src="../../<?= htmlspecialchars($product['front_image']); ?>" class="w-10 h-10 rounded-xl object-contain bg-white border border-slate-200 p-0.5">
                                    <div>
                                        <p class="font-medium text-sm text-slate-800"><?= htmlspecialchars($product['product_name']); ?></p>
                                        <p class="text-xs text-red-600 font-medium mt-0.5">Remaining: <span class="font-bold"><?= $product['current_stock']; ?></span> (Reorder: <?= $product['reorder_level']; ?>)</p>
                                    </div>
                                </div>
                                <a href="inventory_indeliveries.php" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow transition whitespace-nowrap flex items-center gap-2">
                                    Restock
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</main>



<!-- =======================================================
    DASHBOARD INTERACTIVE CHARTS & SCRIPTS
======================================================= -->
<script>
// Global Search Bar Handler (Enter Key Redirect)
document.getElementById("globalDashboardSearch").addEventListener("keypress", function(e) {
    if (e.key === "Enter") {
        let q = this.value.trim();
        if (q !== "") {
            window.location.href = "products.php?search=" + encodeURIComponent(q);
        }
    }
});

// Chart.js Data Ingestion
const movementLabels = <?= json_encode($movementLabels); ?>;
const deliveriesData = <?= json_encode($deliveriesData); ?>;
const salesData = <?= json_encode($salesData); ?>;
const returnsData = <?= json_encode($returnsData); ?>;

const categoryLabels = <?= json_encode($categoryLabels); ?>;
const categoryData = <?= json_encode($categoryTotals); ?>;

// 1. Inventory Movement Multi-Series Chart
new Chart(document.getElementById('movementChart'), {
    type: 'line',
    data: {
        labels: movementLabels,
        datasets: [
            {
                label: 'Deliveries',
                data: deliveriesData,
                borderColor: '#10B981',
                backgroundColor: 'rgba(16,185,129,0.08)',
                borderWidth: 2.5,
                pointRadius: 3,
                fill: true,
                tension: 0.35
            },
            {
                label: 'Sales',
                data: salesData,
                borderColor: '#0B7A4B',
                backgroundColor: 'rgba(11,122,75,0.08)',
                borderWidth: 2.5,
                pointRadius: 3,
                fill: true,
                tension: 0.35
            },
            {
                label: 'Returns',
                data: returnsData,
                borderColor: '#F59E0B',
                backgroundColor: 'rgba(245,158,11,0.08)',
                borderWidth: 2.5,
                pointRadius: 3,
                fill: true,
                tension: 0.35
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: { boxWidth: 12, font: { size: 11 } }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0,
                    callback: function(value) { if (value % 1 === 0) { return value; } }
                },
                grid: { color: 'rgba(0,0,0,0.03)' }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});

// 2. Category Distribution Doughnut Chart
new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: categoryLabels,
        datasets: [{
            data: categoryData,
            backgroundColor: [
                '#10B981', '#0B7A4B', '#F59E0B', '#065F46', 
                '#0F766E', '#0B7A4B', '#14B8A6', '#F97316'
            ],
            borderWidth: 2,
            borderColor: '#ffffff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { boxWidth: 10, font: { size: 10 } }
            }
        },
        cutout: '72%'
    }
});

// Auto-refresh dashboard data every 5 minutes
setTimeout(function() {
    location.reload();
}, 300000);
</script>
</body>
</html>
