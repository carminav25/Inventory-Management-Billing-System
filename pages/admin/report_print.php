<?php
session_start();
require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";
requireAdmin();

/*
=========================================================
PRINT INVENTORY MOVEMENT REPORT
=========================================================
*/

// Report Type & Date Configuration
$reportType = $_GET['type'] ?? 'monthly';
switch ($reportType) {
    case 'today':
        $fromDate = date('Y-m-d');
        $toDate   = date('Y-m-d');
        $title    = "Today's Inventory Movement Report";
        $refType  = 'TDY';
        break;
    case 'weekly':
        $fromDate = date('Y-m-d', strtotime('-6 days'));
        $toDate   = date('Y-m-d');
        $title    = "Weekly Inventory Movement Report";
        $refType  = 'WK';
        break;
    case 'yearly':
        $fromDate = date('Y-01-01');
        $toDate   = date('Y-12-31');
        $title    = "Yearly Inventory Movement Report";
        $refType  = 'YR';
        break;
    case 'custom':
        $fromDate = $_GET['from'] ?? date('Y-m-01');
        $toDate   = $_GET['to'] ?? date('Y-m-t');
        $title    = "Custom Date Inventory Movement Report";
        $refType  = 'CST';
        break;
    default:
        $fromDate = date('Y-m-01');
        $toDate   = date('Y-m-t');
        $title    = "Monthly Inventory Movement Report";
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
// SQL QUERY CONSTRUCTION (AGGREGATE SUBQUERIES)
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

$productQuery = mysqli_query($conn, "
    SELECT 
        p.id, p.front_image, p.product_code, p.product_name, p.category, p.supplier, p.current_stock, p.unit_cost, p.reorder_level,
        COALESCE(d.stock_in, 0) AS stock_in,
        COALESCE(s.stock_out, 0) AS stock_out,
        COALESCE(r.stock_return, 0) AS stock_return,
        p.current_stock AS ending_stock,
        (p.current_stock - COALESCE(d.stock_in, 0) + COALESCE(s.stock_out, 0) - COALESCE(r.stock_return, 0)) AS opening_stock
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
    HAVING stock_in > 0 OR stock_out > 0 OR stock_return > 0 OR ending_stock > 0
    ORDER BY p.product_name ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($reportRefNumber) ?> - Print Inventory Report</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<style>
* {
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}
body {
    margin: 20px;
    color: #222;
    font-size: 12px;
    background: #fff;
}
.report-card {
    max-width: 210mm;
    margin: 0 auto;
    background: #fff;
    padding: 10mm;
}
.header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin-bottom: 15px;
    border-bottom: 2px solid #065F46;
    padding-bottom: 15px;
}
.logo {
    width: 80px;
    height: 80px;
    object-fit: contain;
}
.school {
    text-align: center;
}
.school h2 {
    margin: 0;
    font-size: 22px;
    color: #065F46;
}
.school h3 {
    margin: 3px 0;
    font-size: 15px;
    font-weight: normal;
    color: #4b5563;
}
.school h4 {
    margin: 6px 0;
    font-size: 16px;
    color: #065F46;
}
.meta-grid {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    background: #f8fafc;
    padding: 10px 15px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 11px;
}
.meta-grid div {
    line-height: 1.5;
}
.summary-boxes {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 8px;
    margin-bottom: 15px;
}
.summary-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 8px;
    border-radius: 6px;
    text-align: center;
}
.summary-box span {
    font-size: 10px;
    color: #64748b;
    text-transform: uppercase;
    font-weight: bold;
}
.summary-box h3 {
    margin: 4px 0 0;
    font-size: 14px;
    color: #0f172a;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
th {
    background: #065F46;
    color: #fff;
    padding: 8px 6px;
    font-size: 10px;
    border: 1px solid #054e38;
    text-transform: uppercase;
}
td {
    border: 1px solid #cbd5e1;
    padding: 6px;
    font-size: 11px;
}
.text-center { text-align: center; }
.text-right { text-align: right; }
img.product {
    width: 40px;
    height: 40px;
    border: 1px solid #ddd;
    object-fit: contain;
    border-radius: 4px;
}
.badge {
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 9px;
    font-weight: bold;
    display: inline-block;
}
.badge-green { background: #dcfce7; color: #166534; }
.badge-orange { background: #fef9c3; color: #854d0e; }
.badge-red { background: #fee2e2; color: #991b1b; }

tfoot td {
    font-weight: bold;
    background: #f1f5f9;
}
.signature {
    margin-top: 40px;
    display: flex;
    justify-content: space-between;
    page-break-inside: avoid;
}
.signature div {
    width: 200px;
    text-align: center;
    font-size: 11px;
}
.signature hr {
    margin-bottom: 5px;
    border: none;
    border-top: 1px solid #333;
}
@media print {
    .print-hidden {
        display: none !important;
    }
    body {
        background: #fff;
        margin: 0;
        font-size: 10px;
    }
    .report-card {
        padding: 0;
        max-width: 100%;
    }
}
</style>
</head>
<body>

<div style="max-width:210mm; margin:0 auto 15px auto;" class="print-hidden flex justify-between items-center">
    <a href="reports.php" style="padding: 8px 16px; background: #e2e8f0; color: #334155; text-decoration: none; border-radius: 6px; font-weight: bold;">
        <i class="fa fa-arrow-left mr-1"></i> Back to Reports
    </a>
    <button onclick="window.print()" style="padding: 8px 20px; background: #065F46; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
        <i class="fa fa-print mr-1"></i> Print / Save as PDF
    </button>
</div>

<div class="report-card">
    <!-- Header -->
    <div class="header">
        <img src="../../assets/images/isu-logo.png" class="logo" alt="ISU Logo" onerror="this.style.display='none'">
        <div class="school">
            <h2>ISABELA STATE UNIVERSITY</h2>
            <h3>Merchandising Office | Inventory Management & Billing System</h3>
            <h4><?= htmlspecialchars($title) ?></h4>
        </div>
    </div>

    <!-- Meta Information Grid -->
    <div class="meta-grid">
        <div>
            <strong>Report Reference:</strong> <?= htmlspecialchars($reportRefNumber) ?><br>
            <strong>Report Type:</strong> <?= ucfirst($reportType) ?>
        </div>
        <div>
            <strong>Report Period:</strong> <?= date('F d, Y', strtotime($fromDate)) ?> to <?= date('F d, Y', strtotime($toDate)) ?>
        </div>
        <div>
            <strong>Generated By:</strong> <?= htmlspecialchars($_SESSION['fullname'] ?? 'Administrator') ?><br>
            <strong>Generated Date:</strong> <?= date('F d, Y h:i A') ?>
        </div>
    </div>

    <!-- Summary Metrics Calculation (Pass 1 or Live Accumulation) -->
    <?php
    // Pre-calculate grand totals for the summary boxes via array cache or temporary loop
    $productsArray = [];
    $totalProductsCount = 0;
    $sumBeginning = 0;
    $sumReceived = 0;
    $sumSold = 0;
    $sumReturned = 0;
    $sumEnding = 0;
    $sumValue = 0;

    while ($p = mysqli_fetch_assoc($productQuery)) {
        $productsArray[] = $p;
        $totalProductsCount++;
        $sumBeginning += (int)$p['opening_stock'];
        $sumReceived  += (int)$p['stock_in'];
        $sumSold      += (int)$p['stock_out'];
        $sumReturned  += (int)$p['stock_return'];
        $sumEnding    += (int)$p['ending_stock'];
        $sumValue     += (int)$p['ending_stock'] * (float)$p['unit_cost'];
    }
    ?>

    <!-- Summary Box Cards -->
    <div class="summary-boxes">
        <div class="summary-box">
            <span>Total Products</span>
            <h3><?= number_format($totalProductsCount) ?></h3>
        </div>
        <div class="summary-box">
            <span>Beginning Stock</span>
            <h3><?= number_format($sumBeginning) ?></h3>
        </div>
        <div class="summary-box">
            <span>Received</span>
            <h3 style="color:#059669;"><?= number_format($sumReceived) ?></h3>
        </div>
        <div class="summary-box">
            <span>Sold</span>
            <h3 style="color:#dc2626;"><?= number_format($sumSold) ?></h3>
        </div>
        <div class="summary-box">
            <span>Returned</span>
            <h3 style="color:#d97706;"><?= number_format($sumReturned) ?></h3>
        </div>
        <div class="summary-box">
            <span>Ending Stock</span>
            <h3><?= number_format($sumEnding) ?></h3>
        </div>
        <div class="summary-box" style="grid-column: span 2;">
            <span>Total Inventory Value</span>
            <h3 style="color:#4f46e5;">₱<?= number_format($sumValue, 2) ?></h3>
        </div>
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th width="5%">Image</th>
                <th width="9%">Code</th>
                <th width="16%">Product Name</th>
                <th width="10%">Category</th>
                <th width="9%">Supplier</th>
                <th width="6%">Opening</th>
                <th width="6%">Recv</th>
                <th width="6%">Sold</th>
                <th width="6%">Ret</th>
                <th width="6%">Ending</th>
                <th width="8%">Status</th>
                <th width="8%">Unit Cost</th>
                <th width="10%">Value</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grandBeginning = 0;
            $grandReceived  = 0;
            $grandSold      = 0;
            $grandReturned  = 0;
            $grandEnding    = 0;
            $grandValue     = 0;

            if (count($productsArray) > 0):
                foreach ($productsArray as $product):
                    $beg   = (int)$product['opening_stock'];
                    $in    = (int)$product['stock_in'];
                    $out   = (int)$product['stock_out'];
                    $ret   = (int)$product['stock_return'];
                    $end   = (int)$product['ending_stock'];
                    $cost  = (float)$product['unit_cost'];
                    $reord = (int)$product['reorder_level'];
                    $val   = $end * $cost;

                    $grandBeginning += $beg;
                    $grandReceived  += $in;
                    $grandSold      += $out;
                    $grandReturned  += $ret;
                    $grandEnding    += $end;
                    $grandValue     += $val;

                    // Image handling
                    $img = "../../assets/images/no-image.png";
                    if (!empty($product['front_image'])) {
                        $possible = "../../assets/uploads/products/" . basename($product['front_image']);
                        if (file_exists($possible)) {
                            $img = $possible;
                        }
                    }

                    // Status determination & badge styling
                    if ($end <= 0) {
                        $badgeClass = 'badge-red';
                        $statusText = 'Out of Stock';
                    } elseif ($end <= $reord) {
                        $badgeClass = 'badge-orange';
                        $statusText = 'Low Stock';
                    } else {
                        $badgeClass = 'badge-green';
                        $statusText = 'Available';
                    }
            ?>
            <tr>
                <td class="text-center">
                    <img src="<?= htmlspecialchars($img) ?>" class="product" alt="Product" onerror="this.src='../../assets/images/no-image.png';">
                </td>
                <td class="text-center font-mono"><?= htmlspecialchars($product['product_code']) ?></td>
                <td><strong><?= htmlspecialchars($product['product_name']) ?></strong></td>
                <td><?= htmlspecialchars($product['category']) ?></td>
                <td><?= htmlspecialchars($product['supplier']) ?></td>
                <td class="text-center"><?= number_format($beg) ?></td>
                <td class="text-center" style="color:#059669; font-weight:bold;"><?= number_format($in) ?></td>
                <td class="text-center" style="color:#dc2626; font-weight:bold;"><?= number_format($out) ?></td>
                <td class="text-center" style="color:#d97706; font-weight:bold;"><?= number_format($ret) ?></td>
                <td class="text-center" style="font-weight:bold; background:#f8fafc;"><?= number_format($end) ?></td>
                <td class="text-center">
                    <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                </td>
                <td class="text-right">₱<?= number_format($cost, 2) ?></td>
                <td class="text-right"><strong>₱<?= number_format($val, 2) ?></strong></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="13" class="text-center py-4 text-gray-500">No inventory movement records found for this period.</td></tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right">GRAND TOTALS</td>
                <td class="text-center"><?= number_format($grandBeginning) ?></td>
                <td class="text-center" style="color:#059669;"><?= number_format($grandReceived) ?></td>
                <td class="text-center" style="color:#dc2626;"><?= number_format($grandSold) ?></td>
                <td class="text-center" style="color:#d97706;"><?= number_format($grandReturned) ?></td>
                <td class="text-center"><?= number_format($grandEnding) ?></td>
                <td></td>
                <td></td>
                <td class="text-right">₱<?= number_format($grandValue, 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- Report Notes -->
    <div style="margin-top: 20px; padding: 10px 15px; border: 1px solid #cbd5e1; background: #f8fafc; border-radius: 6px;">
        <strong style="color: #065F46; font-size: 11px;">Report Accounting Notes:</strong>
        <ul style="margin: 5px 0 0 15px; padding: 0; line-height: 1.5; font-size: 10px; color: #475569;">
            <li><strong>Beginning Stock:</strong> Ending Stock before the selected reporting date interval.</li>
            <li><strong>Received:</strong> Total quantity received from suppliers via delivery orders.</li>
            <li><strong>Sold:</strong> Total quantity released through customer sales transactions.</li>
            <li><strong>Returned:</strong> Items processed and marked as returned status during the period.</li>
            <li><strong>Ending Stock:</strong> Actual current available stock balance.</li>
            <li><strong>Inventory Value:</strong> Ending Stock multiplied by the Unit Cost.</li>
        </ul>
    </div>

    <!-- Signatures -->
    <div class="signature">
        <div>
            <br><br><br>
            <hr>
            <strong>Prepared By</strong><br>
            <?= htmlspecialchars($_SESSION['fullname'] ?? 'Staff') ?><br>
            <small style="color:#666;"><?= htmlspecialchars($_SESSION['role'] ?? 'Merchandising Staff') ?></small>
        </div>
        <div>
            <br><br><br>
            <hr>
            <strong>Checked By</strong><br>
            Merchandising Office Head
        </div>
        <div>
            <br><br><br>
            <hr>
            <strong>Approved By</strong><br>
            Campus Administrator
        </div>
    </div>

    <!-- Print Footer Info -->
    <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #64748b; border-top: 1px solid #e2e8f0; pt-2;">
        Generated by <strong>ISU Inventory Management & Billing System</strong> | Printed on <?= date('F d, Y h:i:s A') ?>
    </div>
</div>

<script>
window.onload = function () {
    window.print();
};
</script>
</body>
</html>