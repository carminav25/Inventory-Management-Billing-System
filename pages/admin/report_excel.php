<?php
session_start();
require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";
requireAdmin();

// ==========================================================
// EXCEL EXPORT CONFIGURATION
// ==========================================================
$type = $_GET['type'] ?? 'monthly';
switch ($type) {
    case 'today':
        $fromDate = date('Y-m-d');
        $toDate   = date('Y-m-d');
        $title    = "Today's Inventory Report";
        break;

    case 'weekly':
        $fromDate = date('Y-m-d', strtotime('-6 days'));
        $toDate   = date('Y-m-d');
        $title    = "Last 7 Days Inventory Report";
        break;

    case 'yearly':
        $fromDate = date('Y-01-01');
        $toDate   = date('Y-12-31');
        $title    = "Yearly Inventory Report";
        break;

    default:
        $fromDate = date('Y-m-01');
        $toDate   = date('Y-m-t');
        $title    = "Monthly Inventory Report";
        break;
}

if (!empty($_GET['from'])) {
    $fromDate = $_GET['from'];
}
if (!empty($_GET['to'])) {
    $toDate = $_GET['to'];
}

$category = $_GET['category'] ?? '';
$search   = $_GET['search'] ?? '';

$where = "WHERE 1=1";
if ($category != "") {
    $categoryEsc = mysqli_real_escape_string($conn, $category);
    $where .= " AND p.category = '$categoryEsc'";
}
if ($search != "") {
    $searchEsc = mysqli_real_escape_string($conn, $search);
    $where .= " AND (p.product_name LIKE '%$searchEsc%' OR p.product_code LIKE '%$searchEsc%')";
}

$fromInterval = $fromDate . " 00:00:00";
$toInterval   = $toDate . " 23:59:59";

$productQuery = mysqli_query($conn, "
    SELECT p.* 
    FROM products p 
    $where 
    ORDER BY p.product_name ASC
");

// Set Headers for Excel Download (.xls)
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Inventory_Report_" . date("Ymd_His") . ".xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inventory Report Excel Export</title>
</head>
<body>
    <table>
        <tr>
            <td colspan="11" align="center" style="font-size: 16px; font-weight: bold;">ISABELA STATE UNIVERSITY</td>
        </tr>
        <tr>
            <td colspan="11" align="center" style="font-size: 14px;">Merchandising Office</td>
        </tr>
        <tr>
            <td colspan="11" align="center" style="font-size: 14px; font-weight: bold; color: #0b7a38;"><?= htmlspecialchars($title) ?></td>
        </tr>
        <tr>
            <td colspan="11" align="center" style="font-size: 12px;">Report Period: <?= date('F d, Y', strtotime($fromDate)) ?> - <?= date('F d, Y', strtotime($toDate)) ?></td>
        </tr>
        <tr>
            <td colspan="11"></td>
        </tr>
    </table>

    <table border="1">
        <thead>
            <tr style="background-color: #0b7a38; color: #ffffff; font-weight: bold; text-align: center;">
                <th>Product Code</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Beginning Stock</th>
                <th>Inventory In</th>
                <th>Inventory Out</th>
                <th>Supplier Returns</th>
                <th>Ending Stock</th>
                <th>Unit Cost (PHP)</th>
                <th>Inventory Value (PHP)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grandBeginning = 0;
            $grandIn        = 0;
            $grandOut       = 0;
            $grandReturn    = 0;
            $grandEnding    = 0;
            $grandValue     = 0;

            if (mysqli_num_rows($productQuery) > 0):
                while ($product = mysqli_fetch_assoc($productQuery)):
                    $productId    = (int)$product['id'];
                    $currentStock = (int)$product['current_stock'];
                    $unitCost     = (float)$product['unit_cost'];
                    $reorderLevel = (int)$product['reorder_level'];

                    // Inventory In
                    $inventoryIn = 0;
                    $qIn = mysqli_query($conn, "
                        SELECT COALESCE(SUM(di.quantity), 0) total 
                        FROM delivery_items di 
                        JOIN deliveries d ON d.id = di.delivery_id 
                        WHERE di.product_id = '$productId' 
                        AND d.delivery_date BETWEEN '$fromInterval' AND '$toInterval'
                    ");
                    if ($rIn = mysqli_fetch_assoc($qIn)) {
                        $inventoryIn = (int)$rIn['total'];
                    }

                    // Inventory Out
                    $inventoryOut = 0;
                    $qOut = mysqli_query($conn, "
                        SELECT COALESCE(SUM(si.quantity), 0) total 
                        FROM sale_items si 
                        JOIN sales s ON s.id = si.sale_id 
                        WHERE si.product_id = '$productId' 
                        AND s.sale_date BETWEEN '$fromInterval' AND '$toInterval'
                    ");
                    if ($rOut = mysqli_fetch_assoc($qOut)) {
                        $inventoryOut = (int)$rOut['total'];
                    }

                    // Supplier Returns
                    $returnQty = 0;
                    $qRet = mysqli_query($conn, "
                        SELECT COALESCE(SUM(quantity), 0) total 
                        FROM returns 
                        WHERE product_id = '$productId' 
                        AND status = 'Returned' 
                        AND return_date BETWEEN '$fromInterval' AND '$toInterval'
                    ");
                    if ($rRet = mysqli_fetch_assoc($qRet)) {
                        $returnQty = (int)$rRet['total'];
                    }

                    // Calculations
                    $beginningStock = $currentStock - $inventoryIn + $inventoryOut + $returnQty;
                    if ($beginningStock < 0) {
                        $beginningStock = 0;
                    }
                    $endingStock    = $currentStock;
                    $inventoryValue = $endingStock * $unitCost;

                    // Status
                    $status = "Available";
                    if ($endingStock <= 0) {
                        $status = "Out of Stock";
                    } elseif ($endingStock <= $reorderLevel) {
                        $status = "Low Stock";
                    }

                    // Grand Totals
                    $grandBeginning += $beginningStock;
                    $grandIn        += $inventoryIn;
                    $grandOut       += $inventoryOut;
                    $grandReturn    += $returnQty;
                    $grandEnding    += $endingStock;
                    $grandValue     += $inventoryValue;
            ?>
            <tr>
                <td align="center" style="mso-number-format:'\@';"><?= htmlspecialchars($product['product_code']) ?></td>
                <td><?= htmlspecialchars($product['product_name']) ?></td>
                <td><?= htmlspecialchars($product['category']) ?></td>
                <td align="center"><?= $beginningStock ?></td>
                <td align="center"><?= $inventoryIn ?></td>
                <td align="center"><?= $inventoryOut ?></td>
                <td align="center"><?= $returnQty ?></td>
                <td align="center"><strong><?= $endingStock ?></strong></td>
                <td align="right"><?= number_format($unitCost, 2) ?></td>
                <td align="right"><strong><?= number_format($inventoryValue, 2) ?></strong></td>
                <td align="center"><?= $status ?></td>
            </tr>
            <?php 
                endwhile;
            endif;
            ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="3" align="right">GRAND TOTALS:</td>
                <td align="center"><?= $grandBeginning ?></td>
                <td align="center"><?= $grandIn ?></td>
                <td align="center"><?= $grandOut ?></td>
                <td align="center"><?= $grandReturn ?></td>
                <td align="center"><?= $grandEnding ?></td>
                <td></td>
                <td align="right"><?= number_format($grandValue, 2) ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>