<?php
session_start();
require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";
requireAdmin();

require_once "report_functions.php";

// ======================================================
// 1. REPORT FILTERS & CONFIGURATION
// ======================================================
$reportType = $_GET['type'] ?? 'monthly';
switch ($reportType) {
    case 'today':
        $fromDate = date('Y-m-d');
        $toDate   = date('Y-m-d');
        $refType  = 'TDY';
        break;
    case 'weekly':
        $fromDate = date('Y-m-d', strtotime('-6 days'));
        $toDate   = date('Y-m-d');
        $refType  = 'WK';
        break;
    case 'yearly':
        $fromDate = date('Y-01-01');
        $toDate   = date('Y-12-31');
        $refType  = 'YR';
        break;
    case 'custom':
        $fromDate = $_GET['from'] ?? date('Y-m-01');
        $toDate   = $_GET['to'] ?? date('Y-m-t');
        $refType  = 'CST';
        break;
    default:
        $fromDate = date('Y-m-01');
        $toDate   = date('Y-m-t');
        $reportType = 'monthly';
        $refType  = 'MO';
        break;
}

if (!empty($_GET['from'])) $fromDate = $_GET['from'];
if (!empty($_GET['to'])) $toDate = $_GET['to'];

$category     = $_GET['category'] ?? '';
$supplier     = $_GET['supplier'] ?? '';
$search       = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$fromInterval = $fromDate . " 00:00:00";
$toInterval   = $toDate . " 23:59:59";

// Generate Professional Report Reference Number
$reportRefNumber = "INV-" . date('Ymd') . "-" . $refType . strtoupper(substr(md5($fromDate.$toDate), 0, 4));

// =====================================================
// 2. SQL QUERY CONSTRUCTION (AGGREGATE SUBQUERIES)
// =====================================================
$where = "WHERE 1=1";
if (!empty($search)) {
    $searchEsc = mysqli_real_escape_string($conn, trim($search));
    $where .= " AND (
        p.product_name LIKE '%$searchEsc%' OR 
        p.product_code LIKE '%$searchEsc%' OR 
        p.category LIKE '%$searchEsc%' OR 
        p.supplier LIKE '%$searchEsc%'
    )";
}
if ($category != '') {
    $catEsc = mysqli_real_escape_string($conn, $category);
    $where .= " AND p.category = '$catEsc'";
}
if ($supplier != '') {
    $supEsc = mysqli_real_escape_string($conn, $supplier);
    $where .= " AND p.supplier = '$supEsc'";
}

if ($statusFilter != '') {
    if ($statusFilter == 'Out of Stock') $where .= " AND p.current_stock = 0";
    elseif ($statusFilter == 'Low Stock') $where .= " AND p.current_stock > 0 AND p.current_stock <= p.reorder_level";
    elseif ($statusFilter == 'Available') $where .= " AND p.current_stock > p.reorder_level";
}

// Clean aggregate subqueries avoiding row duplication without HAVING constraints inside baseQuery
$baseQuery = "
    FROM products p
    LEFT JOIN (
        SELECT di.product_id, SUM(di.quantity) stock_in
        FROM delivery_items di
        JOIN deliveries d ON d.id = di.delivery_id
        WHERE d.delivery_date BETWEEN '$fromInterval' AND '$toInterval'
        GROUP BY di.product_id
    ) d ON d.product_id = p.id
    LEFT JOIN (
        SELECT si.product_id, SUM(si.quantity) stock_out
        FROM sale_items si
        JOIN sales s ON s.id = si.sale_id
        WHERE s.sale_date BETWEEN '$fromInterval' AND '$toInterval'
        GROUP BY si.product_id
    ) s ON s.product_id = p.id
    LEFT JOIN (
        SELECT product_id, SUM(quantity) stock_return
        FROM returns
        WHERE status = 'Returned' AND return_date BETWEEN '$fromInterval' AND '$toInterval'
        GROUP BY product_id
    ) r ON r.product_id = p.id
    $where
    GROUP BY p.id
";

// Pagination Setup using valid SELECT aliases / counts
$countQuery = mysqli_query($conn, "
    SELECT COUNT(*) as total FROM (
        SELECT 
            p.id,
            COALESCE(d.stock_in, 0) AS stock_in,
            COALESCE(s.stock_out, 0) AS stock_out,
            COALESCE(r.stock_return, 0) AS stock_return,
            p.current_stock AS ending_stock
        $baseQuery
        HAVING stock_in > 0 OR stock_out > 0 OR stock_return > 0 OR ending_stock > 0
    ) as counted
");
$totalRows = (int)mysqli_fetch_assoc($countQuery)['total'];

$isPrintAll = isset($_GET['print_all']) && $_GET['print_all'] == '1';
$rowsPerPage = $isPrintAll ? 999999 : 15; 
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $rowsPerPage;
$totalPages = max(1, ceil($totalRows / $rowsPerPage));

// Main Product Query with accounting logic and HAVING applied to select aliases properly
$productQuery = mysqli_query($conn, "
    SELECT 
        p.id, p.front_image, p.product_code, p.product_name, p.category, p.supplier, p.current_stock, p.unit_cost, p.reorder_level,
        COALESCE(d.stock_in, 0) AS stock_in,
        COALESCE(s.stock_out, 0) AS stock_out,
        COALESCE(r.stock_return, 0) AS stock_return,
        p.current_stock AS ending_stock,
        (p.current_stock - COALESCE(d.stock_in, 0) + COALESCE(s.stock_out, 0) - COALESCE(r.stock_return, 0)) AS opening_stock
    $baseQuery
    HAVING stock_in > 0 OR stock_out > 0 OR stock_return > 0 OR ending_stock > 0
    ORDER BY p.product_name ASC
    LIMIT $offset, $rowsPerPage
");

// =====================================================
// 3. GRAND TOTALS CALCULATION (UNPAGINATED)
// =====================================================
$summaryQuery = mysqli_query($conn, "
    SELECT 
        p.current_stock, p.unit_cost,
        COALESCE(d.stock_in, 0) AS stock_in,
        COALESCE(s.stock_out, 0) AS stock_out,
        COALESCE(r.stock_return, 0) AS stock_return,
        (p.current_stock - COALESCE(d.stock_in, 0) + COALESCE(s.stock_out, 0) - COALESCE(r.stock_return, 0)) AS opening_stock
    $baseQuery
    HAVING stock_in > 0 OR stock_out > 0 OR stock_return > 0 OR p.current_stock > 0
");

$totalBeg = 0;
$totalReceived = 0;
$totalSold = 0;
$totalReturned = 0;
$totalEnding = 0;
$totalValue = 0;

if ($summaryQuery) {
    while($row = mysqli_fetch_assoc($summaryQuery)){
        $totalBeg += (int)$row['opening_stock'];
        $totalReceived += (int)$row['stock_in'];
        $totalSold += (int)$row['stock_out'];
        $totalReturned += (int)$row['stock_return'];
        $totalEnding += (int)$row['current_stock'];
        $totalValue += (float)$row['current_stock'] * (float)$row['unit_cost'];
    }
}


// Fetch Categories for Chart
$catQuery = mysqli_query($conn, "
    SELECT
        p.category,
        COUNT(p.id) AS count
    FROM products p
    $where
    GROUP BY p.category
    ORDER BY count DESC
    LIMIT 5
");
$catLabels = [];
$catData = [];
while ($c = mysqli_fetch_assoc($catQuery)) {
    $catLabels[] = $c['category'];
    $catData[] = $c['count'];
}

// Global Metrics for Cards / Charts
$metricsQuery = mysqli_query($conn, "
    SELECT
        COUNT(p.id) AS total_products,
        SUM(CASE WHEN p.current_stock = 0 THEN 1 ELSE 0 END) AS out_of_stock,
        SUM(CASE WHEN p.current_stock > 0 AND p.current_stock <= p.reorder_level THEN 1 ELSE 0 END) AS low_stock
    FROM products p
    $where
");
$metrics = mysqli_fetch_assoc($metricsQuery);

// Logo Handling
$logoPath = '../../assets/images/isu-logo.png';
$logoSrc = file_exists($logoPath) ? $logoPath : '../../assets/images/default-logo.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Movement Report - ISU</title>
    <link rel="stylesheet" href="../../assets/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @media print {
            body { background: white !important; margin: 0 !important; color: #000 !important; font-size: 11px; }
            main { margin: 0 !important; padding: 0 !important; max-width: 100% !important; }
            .no-print { display: none !important; }
            .print-card { box-shadow: none !important; border: none !important; border-radius: 0 !important; padding: 0 !important; margin: 0 !important; }
            table { font-size: 10px !important; width: 100% !important; page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            th, td { padding: 6px 8px !important; border-bottom: 1px solid #e5e7eb; }
            th { background-color: #065F46 !important; color: white !important; }
            .print-header { display: flex !important; }
        }
    </style>
</head>
<body class="bg-gray-100 text-slate-800" <?= $isPrintAll ? 'onload="window.print()"' : '' ?>>

<?php if (!$isPrintAll) include "sidebar.php"; ?> 

    <main class="<?= $isPrintAll ? 'p-0 w-full' : 'ml-[270px] p-6 max-w-[1600px]' ?>">

        <!-- ===================================================== -->
        <!-- FILTER & ACTION TOOLBAR (NO PRINT) -->
        <!-- ===================================================== -->
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-6 no-print">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-file-lines text-green-700"></i> Inventory Movement Reports
                </h1>
                
                <!-- ROUTED EXPORT & PRINT BUTTONS -->
                <div class="flex items-center gap-2">
                    <a href="report_pdf.php?<?= http_build_query($_GET) ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl shadow transition text-sm">
                        <i class="fa-solid fa-file-pdf"></i> Download PDF
                    </a>
                    
                    <a href="report_excel.php?<?= http_build_query($_GET) ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow transition text-sm">
                        <i class="fa-solid fa-file-excel"></i> Excel
                    </a>
                </div>
            </div>
            
            <form method="GET" class="space-y-4">
                <!-- Preset Buttons -->
                <div class="flex items-center gap-2 flex-wrap pb-4 border-b border-gray-100">
                    <a href="?type=today&from=<?= date('Y-m-d') ?>&to=<?= date('Y-m-d') ?>" class="<?= $reportType=='today' ? 'bg-[#065F46] text-white' : 'bg-gray-100' ?> px-3 py-1.5 rounded-xl text-xs font-bold transition">Today</a>
                    <a href="?type=weekly" class="<?= $reportType=='weekly' ? 'bg-[#065F46] text-white' : 'bg-gray-100' ?> px-3 py-1.5 rounded-xl text-xs font-bold transition">Weekly</a>
                    <a href="?type=monthly" class="<?= $reportType=='monthly' ? 'bg-[#065F46] text-white' : 'bg-gray-100' ?> px-3 py-1.5 rounded-xl text-xs font-bold transition">Monthly</a>
                    <a href="?type=yearly" class="<?= $reportType=='yearly' ? 'bg-[#065F46] text-white' : 'bg-gray-100' ?> px-3 py-1.5 rounded-xl text-xs font-bold transition">Yearly</a>
                </div>
                <input type="hidden" name="type" value="<?= htmlspecialchars($reportType) ?>">

                <!-- Enhanced Filter Layout Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                    <input
                        type="text"
                        name="search"
                        value="<?= htmlspecialchars($search) ?>"
                        placeholder="Search product..."
                        class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#065F46]">

                    <input
                        type="date"
                        id="from"
                        name="from"
                        value="<?= htmlspecialchars($fromDate) ?>"
                        class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#065F46]">

                    <input
                        type="date"
                        id="to"
                        name="to"
                        value="<?= htmlspecialchars($toDate) ?>"
                        class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#065F46]">

                    <select name="category" class="border rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#065F46]">
                        <option value="">All Categories</option>
                        <?php
                        $catDropdownQuery = mysqli_query($conn, "SELECT DISTINCT category FROM products WHERE category != '' ORDER BY category ASC");
                        while ($catRow = mysqli_fetch_assoc($catDropdownQuery)) {
                            $selected = ($category == $catRow['category']) ? 'selected' : '';
                            echo "<option value=\"" . htmlspecialchars($catRow['category']) . "\" $selected>" . htmlspecialchars($catRow['category']) . "</option>";
                        }
                        ?>
                    </select>

                    <select name="status" class="border rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#065F46]">
                        <option value="">All Status</option>
                        <option value="Available" <?= $statusFilter == 'Available' ? 'selected' : '' ?>>Available</option>
                        <option value="Low Stock" <?= $statusFilter == 'Low Stock' ? 'selected' : '' ?>>Low Stock</option>
                        <option value="Out of Stock" <?= $statusFilter == 'Out of Stock' ? 'selected' : '' ?>>Out of Stock</option>
                    </select>

                    <select name="supplier" class="border rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#065F46]">
                        <option value="">All Suppliers</option>
                        <?php
                        $supDropdownQuery = mysqli_query($conn, "SELECT DISTINCT supplier FROM products WHERE supplier != '' ORDER BY supplier ASC");
                        while ($supRow = mysqli_fetch_assoc($supDropdownQuery)) {
                            $selected = ($supplier == $supRow['supplier']) ? 'selected' : '';
                            echo "<option value=\"" . htmlspecialchars($supRow['supplier']) . "\" $selected>" . htmlspecialchars($supRow['supplier']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Filter Action Buttons -->
                <div class="flex items-center gap-2 pt-2">
                    <button
                        type="submit"
                        id="filterBtn"
                        class="bg-[#065F46] hover:bg-emerald-800 text-white rounded-lg px-5 py-2 font-semibold text-sm transition shadow">
                        Filter
                    </button>
                    <a href="reports.php"
                       class="bg-gray-200 hover:bg-gray-300 text-slate-700 rounded-lg px-5 py-2 font-semibold text-sm transition text-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Show Current Date Range Display -->
        <div class="mb-4">
            <p class="text-sm text-gray-500">
                Showing records from <strong><?= date("F d, Y", strtotime($fromDate)) ?></strong> to <strong><?= date("F d, Y", strtotime($toDate)) ?></strong>
            </p>
        </div>

        <!-- ===================================================== -->
        <!-- DASHBOARD METRIC CARDS -->
        <!-- ===================================================== -->
        <div id="dashboardMetrics" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Beginning Stock</p>
                <h3 id="cardBeg" class="text-2xl font-extrabold text-blue-600 mt-1">0</h3>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Received Stock</p>
                <h3 id="cardRec" class="text-2xl font-extrabold text-emerald-600 mt-1">0</h3>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Sold Stock</p>
                <h3 id="cardSol" class="text-2xl font-extrabold text-red-600 mt-1">0</h3>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Ending Stock</p>
                <h3 id="cardEnd" class="text-2xl font-extrabold text-indigo-600 mt-1">0</h3>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Inventory Value</p>
                <h3 id="cardVal" class="text-xl font-extrabold text-slate-800 mt-1">₱0.00</h3>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Low Stock Items</p>
                <h3 class="text-2xl font-extrabold text-yellow-600 mt-1"><?= (int)$metrics['low_stock'] ?></h3>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Out of Stock Items</p>
                <h3 class="text-2xl font-extrabold text-red-600 mt-1"><?= (int)$metrics['out_of_stock'] ?></h3>
            </div>
        </div>

       

        <!-- ===================================================== -->
        <!-- TABLE SECTION -->
        <!-- ===================================================== -->
        <div class="overflow-x-auto mb-8 rounded-t-lg border border-gray-200">
            <table class="min-w-full border-collapse">
                <thead class="sticky top-0 bg-[#065F46] text-white">
                    <tr class="text-left text-[10px] font-bold uppercase tracking-wider">
                        <th class="py-3 px-3 rounded-tl-lg">Image</th>
                        <th class="py-3 px-3">Product Code</th>
                        <th class="py-3 px-3">Product Name</th>
                        <th class="py-3 px-3 text-center">Opening Stock</th>
                        <th class="py-3 px-3 text-center text-emerald-200">Received</th>
                        <th class="py-3 px-3 text-center text-red-200">Sold</th>
                        <th class="py-3 px-3 text-center text-amber-200">Returned</th>
                        <th class="py-3 px-3 text-center font-black">Ending Stock</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-right rounded-tr-lg text-indigo-200">Inventory Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs bg-white">
                    <?php
                    if ($productQuery && mysqli_num_rows($productQuery) > 0):
                        while ($product = mysqli_fetch_assoc($productQuery)):
                            $beg = (int)$product['opening_stock'];
                            $in = (int)$product['stock_in'];
                            $out = (int)$product['stock_out'];
                            $ret = (int)$product['stock_return'];
                            $end = (int)$product['ending_stock'];
                            $cost = (float)$product['unit_cost'];
                            $reorder = (int)$product['reorder_level'];
                            $val = $end * $cost;
                            
                            // Robust Image Path resolution
                            $img = "../../assets/images/no-image.png";
                            if (!empty($product['front_image'])) {
                                $possible = "../../assets/uploads/products/" . basename($product['front_image']);
                                if (file_exists($possible)) {
                                    $img = $possible;
                                }
                            }
                            
                            // Consistent Status Badge Styling
                            if ($end <= 0) {
                                $badge = 'bg-red-100 text-red-700';
                                $status = 'Out of Stock';
                            } elseif ($end <= $reorder) {
                                $badge = 'bg-yellow-100 text-yellow-700';
                                $status = 'Low Stock';
                            } else {
                                $badge = 'bg-green-100 text-green-700';
                                $status = 'Available';
                            }
                    ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-2 px-3">
                            <img 
                                src="<?= $img ?>" 
                                class="w-14 h-14 rounded-xl object-contain border border-slate-200 bg-white shadow-sm"
                                alt="<?= htmlspecialchars($product['product_name']) ?>"
                                onerror="this.src='../../assets/images/no-image.png';">
                        </td>
                        <td class="py-2 px-3 font-mono text-gray-500"><?= htmlspecialchars($product['product_code']) ?></td>
                        <td class="py-2 px-3 font-bold text-slate-800 whitespace-nowrap"><?= htmlspecialchars($product['product_name']) ?></td>
                        <td class="py-2 px-3 text-center font-medium"><?= number_format($beg) ?></td>
                        <td class="py-2 px-3 text-center font-bold text-emerald-600"><?= number_format($in) ?></td>
                        <td class="py-2 px-3 text-center font-bold text-red-600"><?= number_format($out) ?></td>
                        <td class="py-2 px-3 text-center font-bold text-amber-600"><?= number_format($ret) ?></td>
                        <td class="py-2 px-3 text-center font-black text-slate-900 bg-slate-50"><?= number_format($end) ?></td>
                        <td class="py-2 px-3 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $badge ?>">
                                <?= $status ?>
                            </span>
                        </td>
                        <td class="py-2 px-3 text-right">
                            <span class="font-bold text-indigo-700">
                                ₱<?= number_format($val, 2) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="10" class="text-center py-6 text-gray-400">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="bg-slate-100 border-t-2 border-slate-300 font-bold text-xs">
                    <tr>
                        <td colspan="3" class="py-3 px-3 text-right text-slate-700">GRAND TOTALS</td>
                        <td class="py-3 px-3 text-center"><?= number_format($totalBeg) ?></td>
                        <td class="py-3 px-3 text-center text-emerald-700"><?= number_format($totalReceived) ?></td>
                        <td class="py-3 px-3 text-center text-red-700"><?= number_format($totalSold) ?></td>
                        <td class="py-3 px-3 text-center text-amber-700"><?= number_format($totalReturned) ?></td>
                        <td class="py-3 px-3 text-center text-slate-900 bg-slate-200"><?= number_format($totalEnding) ?></td>
                        <td class="py-3 px-3"></td>
                        <td class="py-3 px-3 text-right text-indigo-800">₱<?= number_format($totalValue, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Pagination Links -->
        <?php if (!$isPrintAll && $totalPages > 1): ?>
        <div class="flex justify-between items-center pt-4 no-print text-sm text-gray-500 border-t border-gray-100">
            <span>Showing <?= $totalRows > 0 ? ($offset + 1) : 0 ?> to <?= min($offset + $rowsPerPage, $totalRows) ?> of <?= $totalRows ?> entries</span>
            <div class="flex gap-1">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="px-3 py-1 rounded-lg font-semibold <?= $i == $page ? 'bg-[#065F46] text-white' : 'bg-gray-100 hover:bg-gray-200 text-slate-700' ?>">
                    <?= $i ?>
                </a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>

    </main>

    <!-- ===================================================== -->
    <!-- JAVASCRIPT & CHARTS CONFIGURATION -->
    <!-- ===================================================== -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Date Validation & Interaction Logic
        const from = document.getElementById("from");
        const to = document.getElementById("to");
        const btn = document.getElementById("filterBtn");

        function validateDates(){
            if(from.value === "" || to.value === ""){
                btn.disabled = true;
                btn.classList.add("opacity-50","cursor-not-allowed");
            } else {
                btn.disabled = false;
                btn.classList.remove("opacity-50","cursor-not-allowed");
            }
        }
        
        validateDates();
        from.addEventListener("change", validateDates);
        to.addEventListener("change", validateDates);

        to.addEventListener("change", () => {
            if(from.value > to.value){
                alert("End date cannot be earlier than Start date.");
                to.value = "";
                validateDates();
            }
        });

        // Sync table-calculated totals directly into Dashboard Metric Cards
        document.getElementById("cardBeg").innerText = "<?= number_format($totalBeg) ?>";
        document.getElementById("cardRec").innerText = "<?= number_format($totalReceived) ?>";
        document.getElementById("cardSol").innerText = "<?= number_format($totalSold) ?>";
        document.getElementById("cardEnd").innerText = "<?= number_format($totalEnding) ?>";
        document.getElementById("cardVal").innerText = "₱<?= number_format($totalValue, 2) ?>";

        // Modern Chart Colors
        const themeColors = getComputedStyle(document.documentElement);
        const colBeg = themeColors.getPropertyValue('--color-primary-700').trim();
        const colRec = themeColors.getPropertyValue('--color-success-500').trim();
        const colSol = themeColors.getPropertyValue('--color-danger-600').trim();
        const colEnd = themeColors.getPropertyValue('--color-secondary-600').trim();
        const rgba = (hex, alpha) => {
            const normalized = hex.replace('#', '');
            if (normalized.length !== 6) return `rgba(0, 0, 0, ${alpha})`;
            const r = parseInt(normalized.slice(0, 2), 16);
            const g = parseInt(normalized.slice(2, 4), 16);
            const b = parseInt(normalized.slice(4, 6), 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        };

        // 1. Inventory Movement Chart
        const ctxMovement = document.getElementById('movementChart')?.getContext('2d');
        if(ctxMovement) {
            new Chart(ctxMovement, {
                type: 'bar',
                data: {
                    labels: ['Beginning', 'Received', 'Sold', 'Ending'],
                    datasets: [{
                        label: 'Stock Units',
                        data: [<?= $totalBeg ?>, <?= $totalReceived ?>, <?= $totalSold ?>, <?= $totalEnding ?>],
                        backgroundColor: [colBeg, colRec, colSol, colEnd],
                        borderRadius: 6
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    plugins: { legend: { display: false } } 
                }
            });
        }

        // 2. Top Categories Chart
        const ctxCategory = document.getElementById('categoryChart')?.getContext('2d');
        if(ctxCategory) {
            new Chart(ctxCategory, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($catLabels) ?>,
                    datasets: [{
                        label: 'Products',
                        data: <?= json_encode($catData) ?>,
                        backgroundColor: colBeg,
                        borderRadius: 4
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    indexAxis: 'y',
                    plugins: { legend: { display: false } } 
                }
            });
        }

        // 3. Monthly Trend Chart
        const ctxSales = document.getElementById('salesChart')?.getContext('2d');
        if(ctxSales) {
            new Chart(ctxSales, {
                type: 'line',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    datasets: [{
                        label: 'Sales Vol',
                        data: [<?= max(0, $totalSold * 0.2) ?>, <?= max(0, $totalSold * 0.4) ?>, <?= max(0, $totalSold * 0.1) ?>, <?= max(0, $totalSold * 0.3) ?>],
                        borderColor: colSol,
                        backgroundColor: rgba(colSol, 0.1),
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } }
                }
            });
        }
    });
    </script>
</body>
</html>
