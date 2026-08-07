<?php
// =====================================================
// REPORT TABLE
// Inventory Report Component
// =====================================================
if (!isset($conn)) {
    require_once "../../config/database.php";
}
// ==========================================
// REQUIRED VARIABLES FROM reports.php
// Prevent Intelephense "Undefined variable"
// ==========================================
$fromDate = $fromDate ?? ($_GET['from'] ?? date('Y-m-01'));
$toDate = $toDate ?? ($_GET['to'] ?? date('Y-m-t'));
$reportType = $reportType ?? ($_GET['type'] ?? 'monthly');
$search = $search ?? trim($_GET['search'] ?? '');
$category = $category ?? trim($_GET['category'] ?? '');
$supplier = $supplier ?? trim($_GET['supplier'] ?? '');
// =====================================================
// SEARCH & FILTER PARAMETERS
// =====================================================
$where = "WHERE 1=1";

if (!empty($search)) {
    $searchEsc = mysqli_real_escape_string($conn, $search);
    $where .= " AND (
        p.product_name LIKE '%$searchEsc%'
        OR p.product_code LIKE '%$searchEsc%'
    )";
}

if (!empty($category)) {
    $categoryEsc = mysqli_real_escape_string($conn, $category);
    $where .= " AND p.category = '$categoryEsc'";
}

if (!empty($supplier)) {
    $supplierEsc = mysqli_real_escape_string($conn, $supplier);
    $where .= " AND p.supplier='$supplierEsc'";
}

// =====================================================
// PAGINATION CONFIGURATION
// =====================================================
$rowsPerPage = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $rowsPerPage;

// =====================================================
// TOTAL RECORDS COUNT
// =====================================================
$countQuery = mysqli_query($conn, "SELECT COUNT(*) total FROM products p $where");
$countRow = mysqli_fetch_assoc($countQuery);
$totalRows = (int)$countRow['total'];
$totalPages = max(1, ceil($totalRows / $rowsPerPage));

// =====================================================
// PRODUCT PAGINATED QUERY
// =====================================================
$productQuery = mysqli_query($conn, "
    SELECT p.* 
    FROM products p 
    $where 
    ORDER BY p.product_name ASC 
    LIMIT $offset, $rowsPerPage
");

// =====================================================
// REPORT DATE INTERVALS
// =====================================================
$fromInterval = $fromDate . " 00:00:00";
$toInterval   = $toDate . " 23:59:59";
?>

<div class="bg-white rounded-3xl shadow overflow-hidden">
    <!-- Table Header Bar -->
    <div class="px-6 py-5 border-b flex justify-between items-center flex-wrap gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Inventory Report Ledger
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                <?= date('F d, Y', strtotime($fromDate)); ?> - <?= date('F d, Y', strtotime($toDate)); ?>
            </p>
        </div>
        <div>
            <span class="bg-green-100 text-green-700 px-4 py-2 rounded-xl text-sm font-semibold uppercase tracking-wider">
                <?= htmlspecialchars($reportType); ?>
            </span>
        </div>
    </div>

    <!-- Responsive Table Wrapper -->
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-slate-50 border-b">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Product</th>
                    <th class="px-4 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-500">Beginning<br>Stock</th>
                    <th class="px-4 py-4 text-center text-xs font-bold uppercase tracking-wider text-green-700">Inventory<br>In</th>
                    <th class="px-4 py-4 text-center text-xs font-bold uppercase tracking-wider text-red-600">Inventory<br>Out</th>
                    <th class="px-4 py-4 text-center text-xs font-bold uppercase tracking-wider text-orange-600">Supplier<br>Returns</th>
                    <th class="px-4 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-500">Ending<br>Stock</th>
                    <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Unit<br>Cost</th>
                    <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-wider text-emerald-700">Inventory<br>Value</th>
                    <th class="px-5 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                <?php 
                if (mysqli_num_rows($productQuery) > 0):
                    while($product = mysqli_fetch_assoc($productQuery)):
                        $productId    = (int)$product['id'];
                        $currentStock = (int)$product['current_stock'];
                        $unitCost     = (float)$product['unit_cost'];
                        $reorderLevel = (int)$product['reorder_level'];
                        $image        = $product['front_image'];
                        $productName  = $product['product_name'];
                        $productCode  = $product['product_code'];
                        $categoryName = $product['category'];

                        // 1. Inventory In Calculation
                        $inventoryIn = 0;
                        $qIn = mysqli_query($conn, "
                            SELECT COALESCE(SUM(di.quantity), 0) total 
                            FROM delivery_items di 
                            JOIN deliveries d ON di.delivery_id = d.id 
                            WHERE di.product_id = '$productId' 
                            AND d.delivery_date BETWEEN '$fromInterval' AND '$toInterval'
                        ");
                        if ($rIn = mysqli_fetch_assoc($qIn)) {
                            $inventoryIn = (int)$rIn['total'];
                        }

                        // 2. Inventory Out Calculation
                        $inventoryOut = 0;
                        $qOut = mysqli_query($conn, "
                            SELECT COALESCE(SUM(si.quantity), 0) total 
                            FROM sale_items si 
                            JOIN sales s ON si.sale_id = s.id 
                            WHERE si.product_id = '$productId' 
                            AND s.sale_date BETWEEN '$fromInterval' AND '$toInterval'
                        ");
                        if ($rOut = mysqli_fetch_assoc($qOut)) {
                            $inventoryOut = (int)$rOut['total'];
                        }

                        // 3. Supplier Returns Calculation
                        $supplierReturn = 0;
                        $qRet = mysqli_query($conn, "
                            SELECT COALESCE(SUM(quantity), 0) total 
                            FROM returns 
                            WHERE product_id = '$productId' 
                            AND status = 'Returned' 
                            AND return_date BETWEEN '$fromInterval' AND '$toInterval'
                        ");
                        if ($rRet = mysqli_fetch_assoc($qRet)) {
                            $supplierReturn = (int)$rRet['total'];
                        }

                        // 4. Beginning & Ending Stock Calculations
                        $beginningStock = $currentStock - $inventoryIn + $inventoryOut + $supplierReturn;
                        if ($beginningStock < 0) {
                            $beginningStock = 0;
                        }
                        $endingStock = $currentStock;
                        $inventoryValue = $endingStock * $unitCost;

                        // 5. Status Badge Configuration
                        $status = "Available";
                        $badge = "bg-green-100 text-green-700";
                        if ($endingStock <= 0) {
                            $status = "Out of Stock";
                            $badge = "bg-red-100 text-red-700";
                        } elseif ($endingStock <= $reorderLevel) {
                            $status = "Low Stock";
                            $badge = "bg-yellow-100 text-yellow-700";
                        }

                        $imagePath = "../../assets/images/no-image.png";
                        if (!empty($image)) {
                            $possiblePath = "../../" . $image;
                            if (file_exists($possiblePath)) {
                                $imagePath = $possiblePath;
                            }
                        }
                ?>
                <tr class="hover:bg-green-50/50 transition duration-200">
                    <!-- Product Details -->
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-4 min-w-[150px]">
                            <img src="<?= htmlspecialchars($imagePath) ?>" class="w-14 h-14 rounded-xl object-cover border bg-gray-50 flex-shrink-0" onerror="this.src='../../assets/images/no-image.png'">
                            <div>
                                <h4 class="font-bold text-slate-800"><?= htmlspecialchars($productName) ?></h4>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($productCode) ?></p>
                                <p class="text-xs text-gray-400"><?= htmlspecialchars($categoryName) ?></p>
                            </div>
                        </div>
                    </td>
                    <!-- Beginning Stock -->
                    <td class="text-center font-semibold text-slate-700">
                        <?= number_format($beginningStock); ?>
                    </td>
                    <!-- Inventory In -->
                    <td class="text-center">
                        <span class="text-green-700 font-bold">+<?= number_format($inventoryIn); ?></span>
                    </td>
                    <!-- Inventory Out -->
                    <td class="text-center">
                        <span class="text-red-600 font-bold">-<?= number_format($inventoryOut); ?></span>
                    </td>
                    <!-- Supplier Returns -->
                    <td class="text-center">
                        <span class="text-orange-500 font-bold">-<?= number_format($supplierReturn); ?></span>
                    </td>
                    <!-- Ending Stock -->
                    <td class="text-center font-extrabold text-slate-800">
                        <?= number_format($endingStock); ?>
                    </td>
                    <!-- Unit Cost -->
                    <td class="text-right text-slate-600">
                        ₱<?= number_format($unitCost, 2); ?>
                    </td>
                    <!-- Inventory Value -->
                    <td class="text-right font-bold text-emerald-700">
                        ₱<?= number_format($inventoryValue, 2); ?>
                    </td>
                    <!-- Status Badge -->
                    <td class="text-center">
                        <span class="<?= $badge ?> px-3 py-1 rounded-full text-xs font-bold inline-block">
                            <?= $status; ?>
                        </span>
                    </td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="9" class="text-center py-12 text-gray-500">
                        <i class="fa-solid fa-box-open text-5xl mb-3 text-gray-300"></i>
                        <br>
                        No products found matching your current filter criteria.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ===================================================== -->
    <!-- GRAND TOTALS SUMMARY SECTION -->
    <!-- ===================================================== -->
    <?php
    $summaryQuery = mysqli_query($conn, "SELECT COALESCE(SUM(current_stock), 0) total_stock, COALESCE(SUM(current_stock * unit_cost), 0) total_value FROM products p $where");
    $summary = mysqli_fetch_assoc($summaryQuery);
    ?>
    <div class="bg-slate-50 border-t px-6 py-5">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <p class="text-xs uppercase text-gray-500 font-semibold">Total Filtered Products</p>
                <h3 class="text-2xl font-bold text-slate-800"><?= number_format($totalRows) ?></h3>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500 font-semibold">Inventory In (Period)</p>
                <h3 class="text-2xl font-bold text-green-700">
                    <?php
                    // Calculate total aggregated In for current filter scope
                    $sumInQuery = mysqli_query($conn, "
                        SELECT COALESCE(SUM(di.quantity), 0) total 
                        FROM delivery_items di 
                        JOIN deliveries d ON di.delivery_id = d.id 
                        JOIN products p ON di.product_id = p.id 
                        $where AND d.delivery_date BETWEEN '$fromInterval' AND '$toInterval'
                    ");
                    $sumInRow = mysqli_fetch_assoc($sumInQuery);
                    echo number_format((int)$sumInRow['total']);
                    ?>
                </h3>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500 font-semibold">Inventory Out (Period)</p>
                <h3 class="text-2xl font-bold text-red-600">
                    <?php
                    $sumOutQuery = mysqli_query($conn, "
                        SELECT COALESCE(SUM(si.quantity), 0) total 
                        FROM sale_items si 
                        JOIN sales s ON si.sale_id = s.id 
                        JOIN products p ON si.product_id = p.id 
                        $where AND s.sale_date BETWEEN '$fromInterval' AND '$toInterval'
                    ");
                    $sumOutRow = mysqli_fetch_assoc($sumOutQuery);
                    echo number_format((int)$sumOutRow['total']);
                    ?>
                </h3>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500 font-semibold">Supplier Returns (Period)</p>
                <h3 class="text-2xl font-bold text-orange-600">
                    <?php
                    $sumRetQuery = mysqli_query($conn, "
                        SELECT COALESCE(SUM(r.quantity), 0) total 
                        FROM returns r 
                        JOIN products p ON r.product_id = p.id 
                        $where AND r.status = 'Returned' AND r.return_date BETWEEN '$fromInterval' AND '$toInterval'
                    ");
                    $sumRetRow = mysqli_fetch_assoc($sumRetQuery);
                    echo number_format((int)$sumRetRow['total']);
                    ?>
                </h3>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- VALUATION FOOTER -->
    <!-- ===================================================== -->
    <div class="bg-white border-t px-6 py-5 flex justify-between items-center flex-wrap gap-4">
        <div>
            <h3 class="text-lg font-bold text-slate-800">Filtered Inventory Asset Valuation</h3>
            <p class="text-gray-500 text-sm">Calculated from total ending stock value of current selection criteria.</p>
        </div>
        <div class="text-right">
            <h2 class="text-3xl font-extrabold text-emerald-700">
                ₱<?= number_format($summary['total_value'], 2) ?>
            </h2>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- PAGINATION CONTROLS -->
    <!-- ===================================================== -->
    <?php if ($totalPages > 1): ?>
    <div class="bg-white border-t px-6 py-5 flex justify-between items-center flex-wrap gap-4">
        <div class="text-sm text-gray-500">
            Showing <strong><?= $totalRows > 0 ? ($offset + 1) : 0 ?></strong> - <strong><?= min($offset + $rowsPerPage, $totalRows) ?></strong> of <strong><?= $totalRows ?></strong> products
        </div>
        <div class="flex gap-2">
            <?php if ($page > 1): ?>
            <a href="?type=<?= $reportType ?>&page=<?= $page - 1 ?>&from=<?= $fromDate ?>&to=<?= $toDate ?>&category=<?= urlencode($category) ?>
&supplier=<?= urlencode($supplier) ?>
&search=<?= urlencode($search) ?>"
               class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?type=<?= $reportType ?>&page=<?= $i ?>&from=<?= $fromDate ?>&to=<?= $toDate ?>&category=<?= urlencode($category) ?>
&supplier=<?= urlencode($supplier) ?>
&search=<?= urlencode($search) ?>"
               class="px-4 py-2 rounded-lg font-semibold transition <?= $i == $page ? 'bg-green-700 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
            <a href="?type=<?= $reportType ?>&page=<?= $page + 1 ?>&from=<?= $fromDate ?>&to=<?= $toDate ?>&category=<?= urlencode($category) ?>
&supplier=<?= urlencode($supplier) ?>
&search=<?= urlencode($search) ?>"
               class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>