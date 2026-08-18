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


/*
|--------------------------------------------------------------------------
| TODAY'S SUMMARY
|--------------------------------------------------------------------------
*/

$todayDeliveries = 0;

$tableCheckDeliveries = mysqli_query(
    $conn,
    "SHOW TABLES LIKE 'deliveries'"
);

if ($tableCheckDeliveries && mysqli_num_rows($tableCheckDeliveries) > 0) {

    $result = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM deliveries
         WHERE DATE(delivery_date) = CURDATE()"
    );

    if ($result && $row = mysqli_fetch_assoc($result)) {
        $todayDeliveries = (int)$row['total'];
    }
}


$todaySales = 0;

$tableCheckSales = mysqli_query(
    $conn,
    "SHOW TABLES LIKE 'sales'"
);

if ($tableCheckSales && mysqli_num_rows($tableCheckSales) > 0) {

    $result = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM sales
         WHERE DATE(sale_date) = CURDATE()"
    );

    if ($result && $row = mysqli_fetch_assoc($result)) {
        $todaySales = (int)$row['total'];
    }
}


$todayReturns = 0;

$tableCheckReturns = mysqli_query(
    $conn,
    "SHOW TABLES LIKE 'returns'"
);

if ($tableCheckReturns && mysqli_num_rows($tableCheckReturns) > 0) {

    $result = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM returns
         WHERE DATE(return_date) = CURDATE()"
    );

    if ($result && $row = mysqli_fetch_assoc($result)) {
        $todayReturns = (int)$row['total'];
    }
}


$todayTransactions =
    $todayDeliveries +
    $todaySales +
    $todayReturns;


/*
|--------------------------------------------------------------------------
| CURRENT INVENTORY VALUE
|--------------------------------------------------------------------------
*/

$inventoryValue = 0;

$inventoryValueQuery = mysqli_query(
    $conn,
    "SELECT COALESCE(
        SUM(current_stock * unit_cost),
        0
    ) AS inventory_value
    FROM products"
);

if (
    $inventoryValueQuery &&
    $row = mysqli_fetch_assoc($inventoryValueQuery)
) {
    $inventoryValue = (float)$row['inventory_value'];
}


/*
|--------------------------------------------------------------------------
| QUICK STATISTICS
|--------------------------------------------------------------------------
*/

$deliveriesWeek = 0;

if (
    $tableCheckDeliveries &&
    mysqli_num_rows($tableCheckDeliveries) > 0
) {

    $res = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM deliveries
         WHERE delivery_date >=
         DATE_SUB(CURDATE(), INTERVAL 7 DAY)"
    );

    if ($res && $r = mysqli_fetch_assoc($res)) {
        $deliveriesWeek = (int)$r['total'];
    }
}


$salesMonth = 0;

if (
    $tableCheckSales &&
    mysqli_num_rows($tableCheckSales) > 0
) {

    $res = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM sales
         WHERE MONTH(sale_date) = MONTH(CURDATE())
         AND YEAR(sale_date) = YEAR(CURDATE())"
    );

    if ($res && $r = mysqli_fetch_assoc($res)) {
        $salesMonth = (int)$r['total'];
    }
}


$returnsMonth = 0;

if (
    $tableCheckReturns &&
    mysqli_num_rows($tableCheckReturns) > 0
) {

    $res = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM returns
         WHERE MONTH(return_date) = MONTH(CURDATE())
         AND YEAR(return_date) = YEAR(CURDATE())"
    );

    if ($res && $r = mysqli_fetch_assoc($res)) {
        $returnsMonth = (int)$r['total'];
    }
}


/*
|--------------------------------------------------------------------------
| LOW STOCK ALERTS
|--------------------------------------------------------------------------
*/

$lowStockProducts = [];

$result = mysqli_query(
    $conn,
    "SELECT
        id,
        product_name,
        current_stock,
        reorder_level,
        front_image
     FROM products
     WHERE current_stock <= reorder_level
     ORDER BY current_stock ASC
     LIMIT 10"
);

if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {
        $lowStockProducts[] = $row;
    }
}


/*
|--------------------------------------------------------------------------
| INVENTORY STATUS LOGIC
|--------------------------------------------------------------------------
| Available: current_stock > reorder_level
| Low Stock: current_stock > 0 AND current_stock <= reorder_level
| Out of Stock: current_stock <= 0
*/

$availableStockCount = 0;
$lowStockCount = 0;
$outOfStockCount = 0;
$inventoryStatusProducts = [];

$statusResult = mysqli_query(
    $conn,
    "SELECT id, product_name, current_stock, reorder_level, front_image
     FROM products
     ORDER BY product_name ASC"
);

if ($statusResult) {
    while ($row = mysqli_fetch_assoc($statusResult)) {
        $currentStock = (int)($row['current_stock'] ?? 0);
        $reorderLevel = (int)($row['reorder_level'] ?? 0);

        if ($currentStock <= 0) {
            $status = 'Out of Stock';
            $outOfStockCount++;
        } elseif ($currentStock <= $reorderLevel) {
            $status = 'Low Stock';
            $lowStockCount++;
        } else {
            $status = 'Available';
            $availableStockCount++;
        }

        $row['inventory_status'] = $status;
        $inventoryStatusProducts[] = $row;
    }
}

$totalInventoryStatusProducts = $availableStockCount + $lowStockCount + $outOfStockCount;


/*
|--------------------------------------------------------------------------
| RECENT ACTIVITIES
|--------------------------------------------------------------------------
*/

$recentActivities = [];

$tableCheckActivities = mysqli_query(
    $conn,
    "SHOW TABLES LIKE 'activity_logs'"
);

if (
    $tableCheckActivities &&
    mysqli_num_rows($tableCheckActivities) > 0
) {

    $activityColumns = [];

    $columnsResult = mysqli_query(
        $conn,
        "SHOW COLUMNS FROM activity_logs"
    );

    if ($columnsResult) {

        while ($column = mysqli_fetch_assoc($columnsResult)) {
            $activityColumns[] = $column['Field'];
        }
    }

    $moduleSelect =
        in_array('module', $activityColumns, true)
        ? 'module'
        : "'' AS module";

    $result = mysqli_query(
        $conn,
        "SELECT
            fullname,
            action,
            {$moduleSelect},
            created_at
         FROM activity_logs
         ORDER BY id DESC
         LIMIT 6"
    );

    if ($result) {

        while ($row = mysqli_fetch_assoc($result)) {
            $recentActivities[] = $row;
        }
    }
}


/*
|--------------------------------------------------------------------------
| CATEGORY DISTRIBUTION
|--------------------------------------------------------------------------
*/

$categoryLabels = [];
$categoryTotals = [];

$result = mysqli_query(
    $conn,
    "SELECT
        category,
        COUNT(*) total
     FROM products
     GROUP BY category
     ORDER BY total DESC"
);

if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {

        $categoryLabels[] = $row['category'];
        $categoryTotals[] = (int)$row['total'];
    }
}


/*
|--------------------------------------------------------------------------
| INVENTORY MOVEMENT - LAST 7 DAYS
|--------------------------------------------------------------------------
*/

$movementLabels = [];
$deliveriesData = [];
$salesData = [];
$returnsData = [];


$hasDeliveries =
    (
        $tableCheckDeliveries &&
        mysqli_num_rows($tableCheckDeliveries) > 0
    );

$hasSales =
    (
        $tableCheckSales &&
        mysqli_num_rows($tableCheckSales) > 0
    );

$hasReturns =
    (
        $tableCheckReturns &&
        mysqli_num_rows($tableCheckReturns) > 0
    );


for ($i = 6; $i >= 0; $i--) {

    $date = date(
        "Y-m-d",
        strtotime("-{$i} days")
    );

    $movementLabels[] = date(
        "M d",
        strtotime($date)
    );


    $dCount = 0;
    $sCount = 0;
    $rCount = 0;


    if ($hasDeliveries) {

        $q = mysqli_query(
            $conn,
            "SELECT COUNT(*) total
             FROM deliveries
             WHERE DATE(delivery_date) = '$date'"
        );

        if ($q && $r = mysqli_fetch_assoc($q)) {
            $dCount = (int)$r['total'];
        }
    }


    if ($hasSales) {

        $q = mysqli_query(
            $conn,
            "SELECT COUNT(*) total
             FROM sales
             WHERE DATE(sale_date) = '$date'"
        );

        if ($q && $r = mysqli_fetch_assoc($q)) {
            $sCount = (int)$r['total'];
        }
    }


    if ($hasReturns) {

        $q = mysqli_query(
            $conn,
            "SELECT COUNT(*) total
             FROM returns
             WHERE DATE(return_date) = '$date'"
        );

        if ($q && $r = mysqli_fetch_assoc($q)) {
            $rCount = (int)$r['total'];
        }
    }


    $deliveriesData[] = $dCount;
    $salesData[] = $sCount;
    $returnsData[] = $rCount;
}


$totalTransactionsSum =
    array_sum($deliveriesData) +
    array_sum($salesData) +
    array_sum($returnsData);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($pageTitle ?? 'Admin Dashboard'); ?>
    </title>


   <!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>

<!-- Bootstrap CSS -->
<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<!-- Font Awesome -->
<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
>

<!-- Semantic Theme -->
<link
    rel="stylesheet"
    href="../../assets/css/semantic-theme.css"
>

<!-- ADMIN UI
     IMPORTANT: KEEP THIS LAST
     This controls the sidebar and common admin layout.
-->
<link
    rel="stylesheet"
    href="../../assets/css/admin-ui.css?v=<?= time(); ?>"
>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

    a {
        text-decoration: none !important;
    }

    body {
        margin: 0;
        background-color: #F5F7FA;
        font-family: 'Poppins', Arial, sans-serif;
        overflow-x: hidden;
    }

    /*
    Prevent Tailwind from changing the sidebar layout.
    */
    #sidebar {
        box-sizing: border-box;
    }

    #sidebar *,
    #sidebar *::before,
    #sidebar *::after {
        box-sizing: border-box;
    }

    /*
    Make sure the logo stays inside the sidebar.
    */
    #sidebar .sidebar-logo {
        max-width: 52px !important;
        max-height: 52px !important;
        width: auto !important;
        height: auto !important;
        object-fit: contain !important;
    }

    /*
    Make sure the sidebar itself stays fixed.
    */
    #sidebar {
        position: fixed !important;
        left: 0 !important;
        top: 0 !important;
        bottom: 0 !important;
        width: 270px !important;
        height: 100vh !important;
        overflow-y: hidden !important;
        overflow-x: hidden !important;
        z-index: 1000 !important;
    }

    /*
    Dashboard stays beside sidebar.
    */
    main {
        margin-left: 270px !important;
        width: calc(100% - 270px) !important;
        min-height: 100vh;
    }

    /*
    Remove Bootstrap link styling from sidebar.
    */
    #sidebar a {
        text-decoration: none !important;
    }

    @media (max-width: 768px) {

        #sidebar {
            width: 270px !important;
        }

        main {
            margin-left: 270px !important;
            width: calc(100% - 270px) !important;
        }

    }

</style>

</head>


<body class="bg-[#f5f7fb] font-sans">


<!-- =========================================================
     EXISTING SIDEBAR
     DO NOT CHANGE SIDEBAR.PHP
========================================================= -->

<?php include "sidebar.php"; ?>


<!-- =========================================================
     MAIN DASHBOARD
========================================================= -->
<main class="min-h-screen bg-[#f5f7fb] px-4 py-2 md:px-5 md:py-2">



    <!-- =====================================================
         ROW 1
         WELCOME + SEARCH + QUICK ACTIONS
    ====================================================== -->

    <div
        class="
            flex
            flex-col
            xl:flex-row
            xl:items-center
            xl:justify-between
            gap-3
            mb-3
            bg-white
            p-4
            md:p-5
            rounded-2xl
            shadow-sm
            border
            border-slate-200
        "
    >


        <!-- Welcome -->

        <div>

            <div class="flex items-center gap-2">

                <h1
                    class="
                        text-2xl
                        md:text-3xl
                        font-bold
                        text-slate-900
                        tracking-tight
                    "
                >
                    Dashboard
                </h1>

            </div>


         

            <p class="text-xs text-slate-400 mt-0.5">

                Today:
                <?= date('F j, Y, g:i A'); ?>

            </p>

        </div>


        <!-- Global Search -->

        <div
            class="
                relative
                flex-1
                max-w-md
                mx-0
                xl:mx-4
            "
        >

            <input
                type="text"
                id="globalDashboardSearch"
                placeholder="Search product, supplier, invoice..."
                class="
                    w-full
                    pl-4
                    pr-4
                    py-2.5
                    bg-slate-50
                    border
                    border-slate-200
                    rounded-xl
                    text-sm
                    focus:outline-none
                    focus:ring-2
                    focus:ring-emerald-500
                    focus:bg-white
                    transition
                "
            >

        </div>


        <!-- Quick Actions -->

        <div
            class="
                flex
                items-center
                gap-3
            "
        >

            <a
                href="products.php"
                class="
                    bg-emerald-600
                    hover:bg-emerald-700
                    text-white
                    px-4
                    py-2.5
                    rounded-xl
                    shadow
                    transition
                    text-sm
                    font-medium
                "
            >
                Product
            </a>


            <a
                href="inventory_indeliveries.php"
                class="
                    bg-blue-600
                    hover:bg-blue-700
                    text-white
                    px-4
                    py-2.5
                    rounded-xl
                    shadow
                    transition
                    text-sm
                    font-medium
                "
            >
                Delivery
            </a>


            <a
                href="inventory_outsales.php"
                class="
                    bg-amber-500
                    hover:bg-amber-600
                    text-white
                    px-4
                    py-2.5
                    rounded-xl
                    shadow
                    transition
                    text-sm
                    font-medium
                "
            >
                Sale
            </a>


            <a
                href="reports.php"
                class="
                    bg-slate-700
                    hover:bg-slate-800
                    text-white
                    px-4
                    py-2.5
                    rounded-xl
                    shadow
                    transition
                    text-sm
                    font-medium
                "
            >
                Reports
            </a>

        </div>

    </div>



    <!-- =====================================================
         ROW 2
         SUMMARY CARDS
    ====================================================== -->

    <div
        class="
            grid
            grid-cols-1
            sm:grid-cols-2
            lg:grid-cols-3
            xl:grid-cols-5
            gap-4
            mb-3
        "
    >


        <!-- Today's Deliveries -->

        <div
            class="
                dashboard-metric-card
                bg-white
                rounded-2xl
                shadow-sm
                p-4
                border
                border-slate-200
                flex
                justify-between
                items-center
                gap-3
            "
        >

            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-xs
                        font-semibold
                        text-slate-400
                        uppercase
                        tracking-wider
                    "
                >
                    Today's Deliveries
                </p>


                <h3
                    class="
                        metric-value
                        text-2xl
                        font-bold
                        text-emerald-600
                        mt-1
                    "
                >
                    <?= number_format($todayDeliveries); ?>
                </h3>

            </div>

        </div>



        <!-- Today's Sales -->

        <div
            class="
                dashboard-metric-card
                bg-white
                rounded-2xl
                shadow-sm
                p-4
                border
                border-slate-200
                flex
                justify-between
                items-center
                gap-3
            "
        >

            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-xs
                        font-semibold
                        text-slate-400
                        uppercase
                        tracking-wider
                    "
                >
                    Today's Sales
                </p>


                <h3
                    class="
                        metric-value
                        text-2xl
                        font-bold
                        text-blue-600
                        mt-1
                    "
                >
                    <?= number_format($todaySales); ?>
                </h3>

            </div>

        </div>



        <!-- Today's Returns -->

        <div
            class="
                dashboard-metric-card
                bg-white
                rounded-2xl
                shadow-sm
                p-4
                border
                border-slate-200
                flex
                justify-between
                items-center
                gap-3
            "
        >

            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-xs
                        font-semibold
                        text-slate-400
                        uppercase
                        tracking-wider
                    "
                >
                    Today's Returns
                </p>


                <h3
                    class="
                        metric-value
                        text-2xl
                        font-bold
                        text-amber-600
                        mt-1
                    "
                >
                    <?= number_format($todayReturns); ?>
                </h3>

            </div>

        </div>



        <!-- Total Transactions -->

        <div
            class="
                dashboard-metric-card
                bg-white
                rounded-2xl
                shadow-sm
                p-4
                border
                border-slate-200
                flex
                justify-between
                items-center
                gap-3
            "
        >

            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-xs
                        font-semibold
                        text-slate-400
                        uppercase
                        tracking-wider
                    "
                >
                    Total Transactions
                </p>


                <h3
                    class="
                        metric-value
                        text-2xl
                        font-bold
                        text-purple-600
                        mt-1
                    "
                >
                    <?= number_format($todayTransactions); ?>
                </h3>

            </div>

        </div>



        <!-- Inventory Value -->

        <div
            class="
                dashboard-metric-card
                bg-white
                rounded-2xl
                shadow-sm
                p-4
                border
                border-slate-200
                flex
                items-center
                gap-3
                min-w-0
                overflow-hidden
            "
        >

            <div class="min-w-0 flex-1">

                <p
                    class="
                        text-xs
                        font-semibold
                        text-slate-400
                        uppercase
                        tracking-wider
                        whitespace-nowrap
                    "
                >
                    Inventory Value
                </p>


                <h3
                    class="
                        text-xl
                        font-bold
                        text-emerald-600
                        mt-1
                        whitespace-nowrap
                        leading-tight
                    "
                >
                    ₱<?= number_format($inventoryValue, 2); ?>
                </h3>

            </div>

        </div>

    </div>



    <!-- =====================================================
         INVENTORY STATUS LOGIC
    ====================================================== -->

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-3 overflow-hidden">

        <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-800">Inventory Status</h2>
                <p class="text-xs text-slate-400 mt-0.5">Automatic product stock classification based on current stock and reorder level.</p>
            </div>
            <span class="text-xs font-semibold text-slate-500">
                <?= number_format($totalInventoryStatusProducts); ?> Products Analyzed
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-4">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-emerald-700">Available Stock</p>
                        <p class="text-2xl font-extrabold text-emerald-700 mt-1"><?= number_format($availableStockCount); ?></p>
                    </div>
                </div>
                <p class="text-[11px] text-emerald-700/70 mt-1">Stock is above reorder level.</p>
            </div>

            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-amber-700">Low Stock</p>
                        <p class="text-2xl font-extrabold text-amber-700 mt-1"><?= number_format($lowStockCount); ?></p>
                    </div>
                </div>
                <p class="text-[11px] text-amber-700/70 mt-1">Stock is at or below reorder level.</p>
            </div>

            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-red-700">Out of Stock</p>
                        <p class="text-2xl font-extrabold text-red-700 mt-1"><?= number_format($outOfStockCount); ?></p>
                    </div>
                </div>
                <p class="text-[11px] text-red-700/70 mt-1">No available stock remaining.</p>
            </div>
        </div>

        <?php if (!empty($inventoryStatusProducts)): ?>
            <div class="border-t border-slate-100 px-5 py-3">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Product Status Logic</h3>
                    <a href="products.php" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">View Products</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="text-[10px] uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="py-2 pr-4 font-semibold">Product</th>
                                <th class="py-2 px-4 font-semibold text-center">Current Stock</th>
                                <th class="py-2 px-4 font-semibold text-center">Reorder Level</th>
                                <th class="py-2 pl-4 font-semibold text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach (array_slice($inventoryStatusProducts, 0, 6) as $statusProduct): ?>
                                <?php
                                    $status = $statusProduct['inventory_status'];
                                    $badgeClass = $status === 'Available'
                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                        : ($status === 'Low Stock'
                                            ? 'bg-amber-50 text-amber-700 border-amber-200'
                                            : 'bg-red-50 text-red-700 border-red-200');
                                ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="py-2.5 pr-4 text-sm font-semibold text-slate-700"><?= htmlspecialchars($statusProduct['product_name']); ?></td>
                                    <td class="py-2.5 px-4 text-center text-sm font-bold text-slate-700"><?= number_format((int)$statusProduct['current_stock']); ?></td>
                                    <td class="py-2.5 px-4 text-center text-sm text-slate-500"><?= number_format((int)$statusProduct['reorder_level']); ?></td>
                                    <td class="py-2.5 pl-4 text-right">
                                        <span class="inline-flex px-2.5 py-1 rounded-full border text-[10px] font-bold <?= $badgeClass; ?>"><?= htmlspecialchars($status); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="border-t border-slate-100 px-5 py-5 text-center text-sm text-slate-400">No products are currently available for inventory status analysis.</div>
        <?php endif; ?>
    </div>


    <!-- =====================================================
         ROW 3
         INVENTORY MOVEMENT + CATEGORIES
    ====================================================== -->

    <div
        class="
            grid
            grid-cols-1
            xl:grid-cols-10
            gap-4
            mb-3
        "
    >


        <!-- Inventory Movement -->

        <div
            class="
                xl:col-span-7
                bg-white
                rounded-2xl
                shadow-sm
                border
                border-slate-200
                flex
                flex-col
            "
        >

            <div
                class="
                    flex
                    items-center
                    justify-between
                    px-5
                    py-4
                    border-b
                    border-slate-100
                "
            >

                <div>

                    <h2
                        class="
                            text-base
                            font-bold
                            text-slate-800
                        "
                    >
                        Inventory Movement
                    </h2>


                    <p
                        class="
                            text-xs
                            text-slate-400
                        "
                    >
                        Deliveries, Sales, and Returns over the last 7 days.
                    </p>

                </div>

            </div>


            <div
                class="
                    p-5
                    flex-1
                    flex
                    items-center
                "
            >

                <div class="w-full h-72">

                    <canvas id="movementChart"></canvas>

                </div>

            </div>

        </div>



        <!-- Categories -->

        <div
            class="
                xl:col-span-3
                bg-white
                rounded-2xl
                shadow-sm
                border
                border-slate-200
                flex
                flex-col
            "
        >

            <div
                class="
                    flex
                    items-center
                    justify-between
                    px-5
                    py-4
                    border-b
                    border-slate-100
                "
            >

                <div>

                    <h2
                        class="
                            text-base
                            font-bold
                            text-slate-800
                        "
                    >
                        Categories
                    </h2>


                    <p
                        class="
                            text-xs
                            text-slate-400
                        "
                    >
                        Distribution share.
                    </p>

                </div>

            </div>


            <div
                class="
                    p-5
                    flex-1
                    flex
                    items-center
                    justify-center
                "
            >

                <div class="w-full h-64">

                    <canvas id="categoryChart"></canvas>

                </div>

            </div>

        </div>

    </div>



    <!-- =====================================================
         ROW 4
         RECENT ACTIVITY + LOW STOCK
    ====================================================== -->

    <div
        class="
            grid
            grid-cols-1
            xl:grid-cols-2
            gap-4
            mb-3
        "
    >


        <!-- Recent Activity -->

        <div
            class="
                bg-white
                rounded-2xl
                shadow-sm
                border
                border-slate-200
                flex
                flex-col
            "
        >

            <div
                class="
                    flex
                    items-center
                    justify-between
                    px-5
                    py-4
                    border-b
                    border-slate-100
                "
            >

                <div>

                    <h2
                        class="
                            text-base
                            font-bold
                            text-slate-800
                        "
                    >
                        Recent Activity Timeline
                    </h2>


                    <p
                        class="
                            text-xs
                            text-slate-400
                        "
                    >
                        Audit trail of recent administrative actions.
                    </p>

                </div>


                <a
                    href="audit_trails.php"
                    class="
                        text-emerald-600
                        hover:text-emerald-700
                        font-medium
                        text-xs
                    "
                >
                    View All
                </a>

            </div>


            <div class="p-5 flex-1">

                <?php if (empty($recentActivities)): ?>

                    <p
                        class="
                            text-center
                            text-slate-400
                            text-sm
                            py-4
                        "
                    >
                        No recent activity logs recorded.
                    </p>

                <?php else: ?>

                    <div class="space-y-6">

                        <?php foreach ($recentActivities as $act): ?>

                            <div
                                class="
                                    relative
                                    flex
                                    items-start
                                    gap-3
                                "
                            >

                                <div>

                                    <div
                                        class="
                                            text-sm
                                            font-semibold
                                            text-slate-800
                                        "
                                    >
                                        <?= htmlspecialchars(
                                            $act['fullname']
                                        ); ?>
                                    </div>


                                    <div
                                        class="
                                            text-xs
                                            text-slate-600
                                            mt-0.5
                                        "
                                    >
                                        <?= htmlspecialchars(
                                            $act['action']
                                        ); ?>
                                    </div>


                                    <div
                                        class="
                                            text-[10px]
                                            text-slate-400
                                            mt-1.5
                                        "
                                    >

                                        <?php
                                        if (!empty($act['module'])) {
                                            echo htmlspecialchars(
                                                $act['module']
                                            ) . ' &bull; ';
                                        }
                                        ?>

                                        <?= date(
                                            'M d, Y | h:i A',
                                            strtotime(
                                                $act['created_at']
                                            )
                                        ); ?>

                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>

        </div>



        <!-- Low Stock Alerts -->

        <div
            class="
                bg-white
                rounded-2xl
                shadow-sm
                border
                border-slate-200
                flex
                flex-col
            "
        >

            <div
                class="
                    flex
                    justify-between
                    items-center
                    px-5
                    py-4
                    border-b
                    border-slate-100
                "
            >

                <div>

                    <h2
                        class="
                            text-base
                            font-bold
                            text-slate-800
                        "
                    >
                        Low Stock Alerts
                    </h2>


                    <p
                        class="
                            text-xs
                            text-slate-400
                        "
                    >
                        Products falling at or below reorder levels.
                    </p>

                </div>


                <span
                    class="
                        bg-amber-100
                        text-amber-700
                        px-2.5
                        py-0.5
                        rounded-full
                        text-xs
                        font-semibold
                    "
                >
                    <?= count($lowStockProducts); ?> Items
                </span>

            </div>


            <div
                class="
                    divide-y
                    divide-slate-100
                    flex-1
                    overflow-y-auto
                    max-h-[380px]
                "
            >

                <?php if (empty($lowStockProducts)): ?>

                    <div
                        class="
                            p-8
                            text-center
                            text-emerald-600
                            h-full
                            flex
                            flex-col
                            items-center
                            justify-center
                        "
                    >

                        <p
                            class="
                                font-bold
                                text-sm
                            "
                        >
                            Inventory Healthy
                        </p>


                        <p
                            class="
                                text-xs
                                text-slate-400
                                mt-1
                            "
                        >
                            No products require reordering right now.
                        </p>


                        <p
                            class="
                                text-[10px]
                                text-slate-300
                                mt-4
                            "
                        >
                            Last checked:
                            <?= date('g:i A'); ?>
                        </p>

                    </div>

                <?php else: ?>

                    <div
                        class="
                            divide-y
                            divide-slate-100
                        "
                    >

                        <?php foreach (
                            $lowStockProducts
                            as $product
                        ): ?>

                            <div
                                class="
                                    px-6
                                    py-3.5
                                    flex
                                    items-center
                                    justify-between
                                    gap-3
                                    hover:bg-slate-50
                                    transition
                                "
                            >

                                <div
                                    class="
                                        flex
                                        items-center
                                        gap-3
                                    "
                                >

                                    <?php
                                    $imagePath =
                                        !empty($product['front_image'])
                                        ? "../../" .
                                          htmlspecialchars(
                                              $product['front_image']
                                          )
                                        : "";
                                    ?>

                                    <?php if ($imagePath): ?>

                                        <img
                                            src="<?= $imagePath; ?>"
                                            class="
                                                w-10
                                                h-10
                                                rounded-xl
                                                object-contain
                                                bg-white
                                                border
                                                border-slate-200
                                                p-0.5
                                            "
                                            alt="Product"
                                        >

                                    <?php else: ?>

                                        <div
                                            class="
                                                w-10
                                                h-10
                                                rounded-xl
                                                bg-slate-100
                                                border
                                                border-slate-200
                                                flex
                                                items-center
                                                justify-center
                                            "
                                        >

                                            <i
                                                class="
                                                    fa-solid
                                                    fa-box
                                                    text-slate-400
                                                "
                                            ></i>

                                        </div>

                                    <?php endif; ?>


                                    <div>

                                        <p
                                            class="
                                                font-medium
                                                text-sm
                                                text-slate-800
                                            "
                                        >
                                            <?= htmlspecialchars(
                                                $product['product_name']
                                            ); ?>
                                        </p>


                                        <p
                                            class="
                                                text-xs
                                                text-red-600
                                                font-medium
                                                mt-0.5
                                            "
                                        >
                                            Remaining:

                                            <span class="font-bold">
                                                <?= $product['current_stock']; ?>
                                            </span>

                                            (Reorder:
                                            <?= $product['reorder_level']; ?>)
                                        </p>

                                    </div>

                                </div>


                                <a
                                    href="inventory_indeliveries.php"
                                    class="
                                        px-3
                                        py-1.5
                                        bg-emerald-600
                                        hover:bg-emerald-700
                                        text-white
                                        rounded-xl
                                        text-xs
                                        font-semibold
                                        shadow
                                        transition
                                        whitespace-nowrap
                                        flex
                                        items-center
                                        gap-2
                                    "
                                >
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



<!-- =========================================================
     DASHBOARD SCRIPTS
========================================================= -->

<script>


/*
|--------------------------------------------------------------------------
| GLOBAL SEARCH
|--------------------------------------------------------------------------
*/

const searchBox =
    document.getElementById(
        "globalDashboardSearch"
    );


if (searchBox) {

    searchBox.addEventListener(
        "keypress",
        function(e) {

            if (e.key === "Enter") {

                let q =
                    this.value.trim();

                if (q !== "") {

                    window.location.href =
                        "products.php?search=" +
                        encodeURIComponent(q);
                }
            }

        }
    );

}



/*
|--------------------------------------------------------------------------
| CHART DATA
|--------------------------------------------------------------------------
*/

const movementLabels =
    <?= json_encode($movementLabels); ?>;

const deliveriesData =
    <?= json_encode($deliveriesData); ?>;

const salesData =
    <?= json_encode($salesData); ?>;

const returnsData =
    <?= json_encode($returnsData); ?>;


const categoryLabels =
    <?= json_encode($categoryLabels); ?>;

const categoryData =
    <?= json_encode($categoryTotals); ?>;



/*
|--------------------------------------------------------------------------
| COLORS
|--------------------------------------------------------------------------
*/

const colorSuccess =
    "#059669";

const colorPrimary =
    "#2563EB";

const colorWarning =
    "#D97706";

const colorSecondary =
    "#7C3AED";

const colorSurface =
    "#ffffff";


function rgba(hex, alpha) {

    const normalized =
        hex.replace("#", "");

    if (normalized.length !== 6) {

        return `rgba(0, 0, 0, ${alpha})`;
    }


    const r =
        parseInt(
            normalized.slice(0, 2),
            16
        );

    const g =
        parseInt(
            normalized.slice(2, 4),
            16
        );

    const b =
        parseInt(
            normalized.slice(4, 6),
            16
        );


    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}



/*
|--------------------------------------------------------------------------
| INVENTORY MOVEMENT CHART
|--------------------------------------------------------------------------
*/

const movementCanvas =
    document.getElementById(
        "movementChart"
    );


if (movementCanvas) {

    new Chart(
        movementCanvas,
        {

            type: "line",

            data: {

                labels:
                    movementLabels,

                datasets: [

                    {
                        label:
                            "Deliveries",

                        data:
                            deliveriesData,

                        borderColor:
                            colorSuccess,

                        backgroundColor:
                            rgba(
                                colorSuccess,
                                0.08
                            ),

                        borderWidth:
                            2.5,

                        pointRadius:
                            3,

                        fill:
                            true,

                        tension:
                            0.35
                    },


                    {
                        label:
                            "Sales",

                        data:
                            salesData,

                        borderColor:
                            colorPrimary,

                        backgroundColor:
                            rgba(
                                colorPrimary,
                                0.08
                            ),

                        borderWidth:
                            2.5,

                        pointRadius:
                            3,

                        fill:
                            true,

                        tension:
                            0.35
                    },


                    {
                        label:
                            "Returns",

                        data:
                            returnsData,

                        borderColor:
                            colorWarning,

                        backgroundColor:
                            rgba(
                                colorWarning,
                                0.08
                            ),

                        borderWidth:
                            2.5,

                        pointRadius:
                            3,

                        fill:
                            true,

                        tension:
                            0.35
                    }

                ]

            },


            options: {

                responsive:
                    true,

                maintainAspectRatio:
                    false,


                plugins: {

                    legend: {

                        display:
                            true,

                        position:
                            "top",

                        labels: {

                            boxWidth:
                                12,

                            font: {
                                size: 11
                            }

                        }

                    }

                },


                scales: {

                    y: {

                        beginAtZero:
                            true,

                        ticks: {

                            precision:
                                0,

                            callback:
                                function(value) {

                                    if (
                                        value % 1 === 0
                                    ) {

                                        return value;
                                    }
                                }

                        },

                        grid: {

                            color:
                                "rgba(0,0,0,0.03)"
                        }

                    },


                    x: {

                        grid: {
                            display: false
                        }

                    }

                }

            }

        }
    );

}



/*
|--------------------------------------------------------------------------
| CATEGORY DOUGHNUT CHART
|--------------------------------------------------------------------------
*/

const categoryCanvas =
    document.getElementById(
        "categoryChart"
    );


if (categoryCanvas) {

    new Chart(
        categoryCanvas,
        {

            type:
                "doughnut",


            data: {

                labels:
                    categoryLabels,

                datasets: [

                    {

                        data:
                            categoryData,

                        backgroundColor: [

                            colorSuccess,

                            colorPrimary,

                            colorWarning,

                            colorSecondary,

                            "rgba(20, 184, 166, 0.9)",

                            "rgba(249, 115, 22, 0.9)",

                            "rgba(96, 165, 250, 0.9)",

                            "rgba(220, 38, 38, 0.9)"

                        ],

                        borderWidth:
                            2,

                        borderColor:
                            colorSurface

                    }

                ]

            },


            options: {

                responsive:
                    true,

                maintainAspectRatio:
                    false,


                plugins: {

                    legend: {

                        position:
                            "bottom",

                        labels: {

                            boxWidth:
                                10,

                            font: {
                                size: 10
                            }

                        }

                    }

                },


                cutout:
                    "72%"

            }

        }
    );

}



/*
|--------------------------------------------------------------------------
| AUTO REFRESH
|--------------------------------------------------------------------------
|
| Refresh dashboard every 5 minutes.
|
*/

setTimeout(
    function() {
        location.reload();
    },
    300000
);

</script>


</body>

</html>