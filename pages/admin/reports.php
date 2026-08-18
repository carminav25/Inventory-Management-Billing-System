<?php
session_start();

/* Always load the latest database values when the report page is opened. */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

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
    ) as counted
");
$totalRows = (int)mysqli_fetch_assoc($countQuery)['total'];

$isPrintAll = isset($_GET['print_all']) && $_GET['print_all'] == '1';
$rowsPerPage = $isPrintAll ? 999999 :5; 
$totalPages = max(1, ceil($totalRows / $rowsPerPage));
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$page = min($page, $totalPages);
$offset = ($page - 1) * $rowsPerPage;

$paginationParams = $_GET;
unset($paginationParams['print_all']);
$paginationParams['page'] = $page;
$pageWindowStart = max(1, $page - 2);
$pageWindowEnd = min($totalPages, $page + 2);

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

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>

    <style>
        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            min-height: 100%;
        }

        body {
            background: #f5f7fb;
            color: #0f172a;
            font-family: Arial, Helvetica, sans-serif;
            overflow-x: hidden;
        }

        a {
            text-decoration: none !important;
        }

        .report-main {
            margin-left: 270px;
            width: calc(100% - 270px);
            min-height: 100vh;

            /* Match the spacing used by Supplier Management */
            padding: 12px 20px 32px;
        }

        .report-container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
        }

        /* Same header spacing and alignment as Supplier Management */
        .page-header {
            width: 100%;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            gap: 16px;

            background: #ffffff;
            padding: 24px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 2px rgba(15,23,42,.04);
            margin-bottom: 24px;

            text-align: left;
        }

        .page-header h1 {
            margin: 0;
            color: #0f172a;
            font-size: 24px;
            line-height: 1.25;
            font-weight: 700;
            letter-spacing: -.02em;
            text-align: left;
        }

        .page-header p {
            margin: 3px 0 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.5;
            text-align: left;
        }

        /* Same four-card visual language as Product Management */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            min-width: 0;
            min-height: 120px;
            padding: 24px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-left-width: 4px;
            border-radius: 16px;
            box-shadow: 0 1px 2px rgba(15,23,42,.04);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Inventory Value stays at the far right of the first row */
        .stat-card.inventory-value-card {
            grid-column: 4;
            grid-row: 1;
        }

        .stat-label {
            margin: 0;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .stat-value {
            margin: 4px 0 0;
            color: #1e293b;
            font-size: 30px;
            line-height: 1.1;
            font-weight: 800;
        }

        .stat-value.money {
            font-size: 22px;
        }

        /* Same white panel style as Product List */
        .report-panel {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 1px 2px rgba(15,23,42,.04);
            margin-bottom: 24px;
        }

        .panel-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }

        .panel-heading h2 {
            margin: 0;
            color: #1e293b;
            font-size: 24px;
            font-weight: 700;
        }

        .panel-heading p {
            margin: 3px 0 0;
            color: #64748b;
            font-size: 14px;
        }

        .action-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            color: #ffffff;
            transition: .18s ease;
        }

        .action-print { background: #f97316; }
        .action-print:hover { background: #ea580c; }

        .action-excel { background: #059669; }
        .action-excel:hover { background: #047857; }

        .filter-grid {
            display: grid;
            grid-template-columns: minmax(180px, 1.25fr) repeat(5, minmax(130px, 1fr));
            gap: 12px;
            align-items: center;
        }

        /* Rounded filter controls - match Product/Supplier Management */
        .filter-control,
        input.filter-control,
        select.filter-control {
            width: 100%;
            min-width: 0;
            height: 42px;
            padding: 10px 12px;
            border: 1px solid #dbe3ea !important;
            border-radius: 12px !important;
            background: #f8fafc !important;
            color: #334155;
            font-size: 13px;
            line-height: 1.2;
            outline: none;
            box-shadow: none;
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }

        input[type="date"].filter-control {
            border-radius: 12px !important;
            overflow: hidden;
            -webkit-appearance: none;
            appearance: none;
        }

        .filter-control:hover {
            border-color: #cbd5e1 !important;
            background: #ffffff !important;
        }

        .filter-control::placeholder {
            color: #64748b;
            opacity: 1;
        }

        select.filter-control {
            cursor: pointer;
        }

        input[name="search"].filter-control {
            padding-left: 38px;
            border-radius: 12px !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='7'%3E%3C/circle%3E%3Cline x1='20' y1='20' x2='16.65' y2='16.65'%3E%3C/line%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 12px center;
            background-size: 16px 16px;
        }

        .filter-control:focus,
        input.filter-control:focus,
        select.filter-control:focus {
            background: #ffffff !important;
            border-color: #10b981 !important;
            border-radius: 12px !important;
            box-shadow: 0 0 0 3px rgba(16,185,129,.10) !important;
        }

        .filter-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 14px;
        }

        .filter-btn,
        .reset-btn {
            min-height: 42px;
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            transition: .18s ease;
        }

        .filter-btn {
            border: 0;
            background: #065f46;
            color: #ffffff;
            cursor: pointer;
        }

        .filter-btn:hover {
            background: #047857;
        }

        .reset-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            color: #475569;
        }

        .reset-btn:hover {
            background: #e2e8f0;
        }

        .report-status-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 0 0 14px;
        }

        .report-status-row .date-range {
            margin: 0;
        }

        .refresh-report-btn {
            border: 1px solid #dbe3ea;
            background: #ffffff;
            color: #065f46;
            min-height: 38px;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: .18s ease;
        }

        .refresh-report-btn:hover {
            background: #ecfdf5;
            border-color: #10b981;
        }

        .legacy-date-range {
            display: none;
        }

        .date-range {
            margin: 0 0 14px;
            color: #64748b;
            font-size: 14px;
        }

        .date-range strong {
            color: #475569;
        }

        .table-panel {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(15,23,42,.04);
            margin-bottom: 24px;
        }

        .table-scroll {
            width: 100%;
            overflow-x: auto;
        }

        .report-table {
            width: 100%;
            min-width: 1050px;
            border-collapse: collapse;
            white-space: nowrap;
        }

        .report-table thead {
            background: #f8fafc;
            color: #94a3b8;
        }

        .report-table th {
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
            text-align: left;
        }

        .report-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
            color: #475569;
        }

        .report-table tbody tr:hover {
            background: #f8fafc;
        }

        .report-table .product-name {
            color: #1e293b;
            font-weight: 700;
        }

        .report-table .code {
            color: #64748b;
            font-family: monospace;
            font-size: 12px;
        }

        .report-product-image {
            width: 44px !important;
            height: 44px !important;
            min-width: 44px !important;
            min-height: 44px !important;
            max-width: 44px !important;
            max-height: 44px !important;
            object-fit: contain !important;
            display: block !important;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }

        .totals-row {
            background: #f8fafc;
        }

        .totals-row td {
            border-top: 2px solid #e2e8f0;
            border-bottom: 0;
            font-weight: 700;
        }

        .pagination-wrap {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 30px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 13px;
        }

        .pagination-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            align-items: center;
            gap: 6px;
        }

        .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            min-height: 38px;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #ffffff;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
        }

        .page-link:hover {
            background: #f8fafc;
        }

        .page-link.active {
            background: #059669;
            border-color: #059669;
            color: #ffffff;
        }

        @media (max-width: 1250px) {
            .filter-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 900px) {
            .filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 1000px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767px) {
            .report-main {
                margin-left: 0;
                width: 100%;
                padding: 14px;
            }

            .page-header {
                padding: 20px;
            }

            .report-panel {
                padding: 18px;
                border-radius: 18px;
            }

            .panel-heading {
                flex-direction: column;
                align-items: flex-start;
            }

            .action-group {
                width: 100%;
            }

            .action-btn {
                flex: 1;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .filter-control,
            input.filter-control,
            select.filter-control {
                border-radius: 12px !important;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .stat-card.inventory-value-card {
                grid-column: auto;
                grid-row: auto;
            }

            .report-status-row {
                align-items: stretch;
                flex-direction: column;
            }

            .refresh-report-btn {
                width: 100%;
            }
        }

        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
            }

            .report-main {
                margin: 0 !important;
                width: 100% !important;
                padding: 0 !important;
            }

            .no-print {
                display: none !important;
            }

            .page-header,
            .report-panel,
            .table-panel {
                box-shadow: none !important;
            }

            .report-table {
                min-width: 100% !important;
            }

            .report-table th {
                background: #065f46 !important;
                color: #ffffff !important;
            }
        }
    </style>
</head>

<body <?= $isPrintAll ? 'onload="window.print()"' : '' ?>>

<?php if (!$isPrintAll): ?>
    <?php include "sidebar.php"; ?>
<?php endif; ?>

<main class="<?= $isPrintAll ? '' : 'report-main' ?>">

    <div class="<?= $isPrintAll ? '' : 'report-container' ?> space-y-6 max-w-7xl mx-auto">


        <!-- HEADER & TOP ACTION BUTTONS -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Inventory Movement Reports</h1>
                <p class="text-sm text-slate-500 mt-0.5">View inventory movement, stock levels, sales, returns, and inventory values.</p>
            </div>
                            <div class="action-group">
                    <a
                        href="report_print.php?<?= http_build_query($_GET) ?>"
                        target="_blank"
                        class="action-btn action-print"
                    >
                        Print
                    </a>

                    <a
                        href="report_excel.php?<?= http_build_query($_GET) ?>"
                        class="action-btn action-excel"
                    >
                        Excel
                    </a>
                </div>
        </div>

    <!-- REPORT STAT CARDS: SAME DESIGN LANGUAGE AS PRODUCTS -->
        <div id="dashboardMetrics" class="stats-grid">

            <div class="stat-card">
                <div>
                    <p class="stat-label">Beginning Stock</p>
                    <h2 id="cardBeg" class="stat-value" style="color:#2563eb;">0</h2>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <p class="stat-label">Received Stock</p>
                    <h2 id="cardRec" class="stat-value" style="color:#059669;">0</h2>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <p class="stat-label">Sold Stock</p>
                    <h2 id="cardSol" class="stat-value" style="color:#dc2626;">0</h2>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <p class="stat-label">Ending Stock</p>
                    <h2 id="cardEnd" class="stat-value" style="color:#4f46e5;">0</h2>
                </div>
            </div>

            <div class="stat-card inventory-value-card">
                <div>
                    <p class="stat-label">Inventory Value</p>
                    <h2 id="cardVal" class="stat-value money">₱0.00</h2>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <p class="stat-label">Low Stock Items</p>
                    <h2 class="stat-value" style="color:#d97706;">
                        <?= (int)$metrics['low_stock'] ?>
                    </h2>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <p class="stat-label">Out of Stock Items</p>
                    <h2 class="stat-value" style="color:#dc2626;">
                        <?= (int)$metrics['out_of_stock'] ?>
                    </h2>
                </div>
            </div>

        </div>


        
        <!-- DATE RANGE -->
        <p class="date-range legacy-date-range">
            Showing records from
            <strong><?= date("F d, Y", strtotime($fromDate)) ?></strong>
            to
            <strong><?= date("F d, Y", strtotime($toDate)) ?></strong>
        </p>

     <!-- FILTER PANEL -->
        <div class="report-panel no-print">
            <div class="panel-heading">
                <div>
                    <h2>Report Filters</h2>
                    <p>Search, filter, and manage inventory report records.</p>
                </div>


            </div>

            <form method="GET">


                <input
                    type="hidden"
                    name="type"
                    value="<?= htmlspecialchars($reportType) ?>"
                >

                <div class="filter-grid">
                    <input
                        type="text"
                        name="search"
                        value="<?= htmlspecialchars($search) ?>"
                        placeholder="Search product name/code..."
                        class="filter-control"
                        autocomplete="off"
                    >

                    <input
                        type="date"
                        id="from"
                        name="from"
                        value="<?= htmlspecialchars($fromDate) ?>"
                        class="filter-control"
                        aria-label="Start date"
                    >

                    <input
                        type="date"
                        id="to"
                        name="to"
                        value="<?= htmlspecialchars($toDate) ?>"
                        class="filter-control"
                        aria-label="End date"
                    >

                    <select name="category" class="filter-control">
                        <option value="">All Categories</option>
                        <?php
                        $catDropdownQuery = mysqli_query(
                            $conn,
                            "SELECT DISTINCT category FROM products
                             WHERE category IS NOT NULL AND category != ''
                             ORDER BY category ASC"
                        );

                        if ($catDropdownQuery):
                            while ($catRow = mysqli_fetch_assoc($catDropdownQuery)):
                                $selected = ($category == $catRow['category']) ? 'selected' : '';
                        ?>
                            <option
                                value="<?= htmlspecialchars($catRow['category']) ?>"
                                <?= $selected ?>
                            >
                                <?= htmlspecialchars($catRow['category']) ?>
                            </option>
                        <?php
                            endwhile;
                        endif;
                        ?>
                    </select>

                    

                    <select name="status" class="filter-control">
                        <option value="">All Status</option>
                        <option value="Available" <?= $statusFilter == 'Available' ? 'selected' : '' ?>>Available</option>
                        <option value="Low Stock" <?= $statusFilter == 'Low Stock' ? 'selected' : '' ?>>Low Stock</option>
                        <option value="Out of Stock" <?= $statusFilter == 'Out of Stock' ? 'selected' : '' ?>>Out of Stock</option>
                    </select>



                    <select name="supplier" class="filter-control">
                        <option value="">All Suppliers</option>
                        <?php
                        $supDropdownQuery = mysqli_query(
                            $conn,
                            "SELECT DISTINCT supplier FROM products
                             WHERE supplier IS NOT NULL AND supplier != ''
                             ORDER BY supplier ASC"
                        );

                        if ($supDropdownQuery):
                            while ($supRow = mysqli_fetch_assoc($supDropdownQuery)):
                                $selected = ($supplier == $supRow['supplier']) ? 'selected' : '';
                        ?>
                            <option
                                value="<?= htmlspecialchars($supRow['supplier']) ?>"
                                <?= $selected ?>
                            >
                                <?= htmlspecialchars($supRow['supplier']) ?>
                            </option>
                        <?php
                            endwhile;
                        endif;
                        ?>
                    </select>
                </div>

                <div class="filter-actions">
                    <button
                        type="submit"
                        id="filterBtn"
                        class="filter-btn"
                    >
                        Filter
                    </button>

                    <a href="reports.php" class="reset-btn">
                        Reset
                    </a>
                </div>
            </form>
        </div>


        <!-- DATE RANGE -->
        <div class="report-status-row">
            <p class="date-range">
                Showing records from
                <strong><?= date("F d, Y", strtotime($fromDate)) ?></strong>
                to
                <strong><?= date("F d, Y", strtotime($toDate)) ?></strong>
            </p>
            <button type="button" class="refresh-report-btn" onclick="refreshReport()" title="Reload the latest database data">
                Refresh Report
            </button>
        </div>


       
        <!-- REPORT TABLE: SAME CONTAINER/TABLE STYLE AS PRODUCT PAGE -->
        <div class="table-panel">
            <div class="table-scroll">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th style="text-align:center;">Opening Stock</th>
                            <th style="text-align:center;">Received</th>
                            <th style="text-align:center;">Sold</th>
                            <th style="text-align:center;">Returned</th>
                            <th style="text-align:center;">Ending Stock</th>
                            <th style="text-align:center;">Status</th>
                            <th style="text-align:right;">Inventory Value</th>
                        </tr>
                    </thead>

                    <tbody>
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

                            $img = "../../assets/images/no-image.png";

                            if (!empty($product['front_image'])) {
                                $possible =
                                    "../../assets/uploads/products/" .
                                    basename($product['front_image']);

                                if (file_exists($possible)) {
                                    $img = $possible;
                                }
                            }

                            if ($end <= 0) {
                                $badge = 'background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;';
                                $status = 'Out of Stock';
                            } elseif ($end <= $reorder) {
                                $badge = 'background:#fffbeb;color:#b45309;border:1px solid #fde68a;';
                                $status = 'Low Stock';
                            } else {
                                $badge = 'background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;';
                                $status = 'Available';
                            }
                    ?>

                        <tr>
                            <td>
                                <img
                                    src="<?= htmlspecialchars($img) ?>"
                                    class="report-product-image"
                                    alt="<?= htmlspecialchars($product['product_name']) ?>"
                                    onerror="this.src='../../assets/images/no-image.png';"
                                >
                            </td>

                            <td class="code">
                                <?= htmlspecialchars($product['product_code']) ?>
                            </td>

                            <td class="product-name">
                                <?= htmlspecialchars($product['product_name']) ?>
                            </td>

                            <td style="text-align:center;font-weight:600;">
                                <?= number_format($beg) ?>
                            </td>

                            <td style="text-align:center;font-weight:700;color:#059669;">
                                <?= number_format($in) ?>
                            </td>

                            <td style="text-align:center;font-weight:700;color:#dc2626;">
                                <?= number_format($out) ?>
                            </td>

                            <td style="text-align:center;font-weight:700;color:#d97706;">
                                <?= number_format($ret) ?>
                            </td>

                            <td style="text-align:center;font-weight:800;color:#0f172a;">
                                <?= number_format($end) ?>
                            </td>

                            <td style="text-align:center;">
                                <span
                                    class="status-badge"
                                    style="<?= $badge ?>"
                                >
                                    <?= $status ?>
                                </span>
                            </td>

                            <td style="text-align:right;font-weight:700;color:#4338ca;">
                                ₱<?= number_format($val, 2) ?>
                            </td>
                        </tr>

                    <?php
                        endwhile;
                    else:
                    ?>

                        <tr>
                            <td
                                colspan="10"
                                style="text-align:center;padding:48px 20px;color:#94a3b8;"
                            >
                                No records found.
                            </td>
                        </tr>

                    <?php endif; ?>
                    </tbody>

                    <tfoot>
                        <tr class="totals-row">
                            <td colspan="3" style="text-align:right;color:#475569;">
                                GRAND TOTALS
                            </td>

                            <td style="text-align:center;">
                                <?= number_format($totalBeg) ?>
                            </td>

                            <td style="text-align:center;color:#047857;">
                                <?= number_format($totalReceived) ?>
                            </td>

                            <td style="text-align:center;color:#b91c1c;">
                                <?= number_format($totalSold) ?>
                            </td>

                            <td style="text-align:center;color:#b45309;">
                                <?= number_format($totalReturned) ?>
                            </td>

                            <td style="text-align:center;color:#0f172a;">
                                <?= number_format($totalEnding) ?>
                            </td>

                            <td></td>

                            <td style="text-align:right;color:#4338ca;">
                                ₱<?= number_format($totalValue, 2) ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- PAGINATION -->
        <?php if (!$isPrintAll && $totalPages > 1): ?>

            <div class="pagination-wrap no-print">

                <span>
                    Showing
                    <?= $totalRows > 0 ? ($offset + 1) : 0 ?>
                    to
                    <?= min($offset + $rowsPerPage, $totalRows) ?>
                    of
                    <?= $totalRows ?>
                    entries
                </span>

                <div class="pagination-links">

                    <?php if ($page > 1): ?>
                        <?php $paginationParams['page'] = $page - 1; ?>

                        <a
                            href="?<?= http_build_query($paginationParams) ?>"
                            class="page-link"
                            aria-label="Previous page"
                        >
                            Previous
                        </a>
                    <?php endif; ?>

                    <?php if ($pageWindowStart > 1): ?>

                        <?php $paginationParams['page'] = 1; ?>

                        <a
                            href="?<?= http_build_query($paginationParams) ?>"
                            class="page-link"
                        >
                            1
                        </a>

                        <?php if ($pageWindowStart > 2): ?>
                            <span style="padding:0 5px;color:#94a3b8;">...</span>
                        <?php endif; ?>

                    <?php endif; ?>

                    <?php for ($i = $pageWindowStart; $i <= $pageWindowEnd; $i++): ?>

                        <?php $paginationParams['page'] = $i; ?>

                        <a
                            href="?<?= http_build_query($paginationParams) ?>"
                            class="page-link <?= $i == $page ? 'active' : '' ?>"
                        >
                            <?= $i ?>
                        </a>

                    <?php endfor; ?>

                    <?php if ($pageWindowEnd < $totalPages): ?>

                        <?php if ($pageWindowEnd < $totalPages - 1): ?>
                            <span style="padding:0 5px;color:#94a3b8;">...</span>
                        <?php endif; ?>

                        <?php $paginationParams['page'] = $totalPages; ?>

                        <a
                            href="?<?= http_build_query($paginationParams) ?>"
                            class="page-link"
                        >
                            <?= $totalPages ?>
                        </a>

                    <?php endif; ?>

                    <?php if ($page < $totalPages): ?>

                        <?php $paginationParams['page'] = $page + 1; ?>

                        <a
                            href="?<?= http_build_query($paginationParams) ?>"
                            class="page-link"
                            aria-label="Next page"
                        >
                            Next
                        </a>

                    <?php endif; ?>

                </div>
            </div>

        <?php endif; ?>

    </div>
</main>

<script>
function refreshReport() {
    const url = new URL(window.location.href);

    // Keep all current filters, but force a fresh server request.
    url.searchParams.set("_refresh", Date.now().toString());

    window.location.replace(url.toString());
}

document.addEventListener("DOMContentLoaded", function () {

    const from = document.getElementById("from");
    const to = document.getElementById("to");
    const btn = document.getElementById("filterBtn");

    function validateDates() {
        if (!from || !to || !btn) return;

        if (from.value === "" || to.value === "") {
            btn.disabled = true;
            btn.style.opacity = "0.5";
            btn.style.cursor = "not-allowed";
        } else {
            btn.disabled = false;
            btn.style.opacity = "1";
            btn.style.cursor = "pointer";
        }
    }

    validateDates();

    if (from) from.addEventListener("change", validateDates);
    if (to) {
        to.addEventListener("change", validateDates);

        to.addEventListener("change", function () {
            if (from.value > to.value) {
                alert("End date cannot be earlier than Start date.");
                to.value = "";
                validateDates();
            }
        });
    }

    const cardBeg = document.getElementById("cardBeg");
    const cardRec = document.getElementById("cardRec");
    const cardSol = document.getElementById("cardSol");
    const cardEnd = document.getElementById("cardEnd");
    const cardVal = document.getElementById("cardVal");

    if (cardBeg) cardBeg.innerText = "<?= number_format($totalBeg) ?>";
    if (cardRec) cardRec.innerText = "<?= number_format($totalReceived) ?>";
    if (cardSol) cardSol.innerText = "<?= number_format($totalSold) ?>";
    if (cardEnd) cardEnd.innerText = "<?= number_format($totalEnding) ?>";
    if (cardVal) cardVal.innerText = "₱<?= number_format($totalValue, 2) ?>";
});
</script>

<script>
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

    </script>
</body>
</html>