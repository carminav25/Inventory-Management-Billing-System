<?php
$pageTitle = "Sale Details";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";

requireAdmin();

$sale_id = (int)($_GET['id'] ?? 0);

if ($sale_id <= 0) {
    $_SESSION['error_message'] = "Invalid sale ID specified.";
    header("Location: inventory_outsales.php");
    exit();
}

// Fetch sale header details along with user/cashier info
$saleQuery = "
    SELECT 
        s.*, 
        u.username as cashier_username,
        u.firstname,
        u.lastname
    FROM sales s
    LEFT JOIN users u ON s.created_by = u.id
    WHERE s.id = ?
";
$stmt = mysqli_prepare($conn, $saleQuery);
mysqli_stmt_bind_param($stmt, "i", $sale_id);
mysqli_stmt_execute($stmt);
$saleResult = mysqli_stmt_get_result($stmt);
$sale = mysqli_fetch_assoc($saleResult);

if (!$sale) {
    $_SESSION['error_message'] = "Sale transaction record not found.";
    header("Location: inventory_outsales.php");
    exit();
}

// Format cashier name properly
$cashierName = trim(($sale['firstname'] ?? '') . ' ' . ($sale['lastname'] ?? ''));
if (empty($cashierName)) {
    $cashierName = $sale['cashier_username'] ?? 'Administrator';
}

// Fetch sale items
$itemsQuery = "
    SELECT 
        si.*, 
        p.product_code, 
        p.product_name, 
        p.product_size, 
        p.front_image,
        p.category
    FROM sale_items si
    LEFT JOIN products p ON si.product_id = p.id
    WHERE si.sale_id = ?
";
$stmtItems = mysqli_prepare($conn, $itemsQuery);
mysqli_stmt_bind_param($stmtItems, "i", $sale_id);
mysqli_stmt_execute($stmtItems);
$itemsResult = mysqli_stmt_get_result($stmtItems);

$items = [];
$totalQty = 0;

while ($item = mysqli_fetch_assoc($itemsResult)) {
    $totalQty += $item['quantity'];
    $items[] = $item;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle . ' - ' . $sale['invoice_no']); ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        a { text-decoration: none !important; }
    </style>
</head>

<body class="bg-slate-50 font-sans text-slate-800">

<?php include "sidebar.php"; ?> 

<main class="ml-0 md:ml-[270px] min-h-screen bg-[#f5f7fb] px-4 py-2 md:px-5 md:py-3 transition-all duration-300">

    <div class="space-y-6 max-w-5xl mx-auto">
        
        <!-- HEADER & ACTIONS -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div>
                <span class="text-xs uppercase tracking-wider font-bold text-slate-400">Merchandise Release Details</span>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight font-mono mt-0.5"><?= htmlspecialchars($sale['invoice_no']); ?></h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="inventory_outsales.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2.5 rounded-xl font-bold text-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
                <button onclick="window.open('sale_print.php?id=<?= $sale['id']; ?>', '_blank')" class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow transition flex items-center gap-2">
                    <i class="fa-solid fa-print"></i> Print Receipt
                </button>
            </div>
        </div>

        <!-- SALE INFORMATION CARD -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3">Transaction Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-400">Release Slip Number</span>
                    <span class="font-mono font-bold text-slate-800 text-base"><?= htmlspecialchars($sale['invoice_no']); ?></span>
                </div>
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-400">Customer / Recipient</span>
                    <span class="font-bold text-slate-800 text-base"><?= htmlspecialchars($sale['customer_name'] ?? 'Walk-in Customer'); ?></span>
                </div>
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-400">Release Date</span>
                    <span class="font-bold text-slate-800 text-base"><?= date('F j, Y, g:i A', strtotime($sale['sale_date'])); ?></span>
                </div>
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-400">Released By</span>
                    <span class="font-medium text-slate-700"><?= ucwords(strtolower($cashierName)); ?></span>
                </div>
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-400">Payment Status</span>
                    <span class="font-medium text-slate-700">Paid at University Cashier</span>
                </div>
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-400">Status</span>
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-bold mt-0.5">
                        <i class="fa-solid fa-circle-check mr-1"></i> Completed
                    </span>
                </div>
            </div>
        </div>

        <!-- PRODUCTS SOLD TABLE -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Products Released</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 font-semibold">Image</th>
                            <th class="px-6 py-3 font-semibold">Product</th>
                            <th class="px-6 py-3 font-semibold">Size</th>
                            <th class="px-6 py-3 font-semibold text-center">Quantity</th>
                            <th class="px-6 py-3 font-semibold text-right">Unit Price</th>
                            <th class="px-6 py-3 font-semibold text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-12 text-slate-400">No items recorded for this sale transaction.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-3">
                                        <img src="<?= !empty($item['front_image']) ? '../../' . htmlspecialchars($item['front_image']) : '../../assets/images/no-image.png'; ?>" class="w-10 h-10 rounded-xl object-contain bg-white border border-slate-200 p-0.5">
                                    </td>
                                    <td class="px-6 py-3 font-bold text-slate-800">
                                        <?= htmlspecialchars($item['product_name'] ?? 'Unknown Product'); ?>
                                        <span class="block text-[11px] font-mono text-slate-400 font-normal"><?= htmlspecialchars($item['product_code'] ?? ''); ?></span>
                                    </td>
                                    <td class="px-6 py-3 text-slate-600 font-medium"><?= htmlspecialchars($item['product_size'] ?? 'Standard'); ?></td>
                                    <td class="px-6 py-3 text-center font-bold text-slate-700"><?= (int)$item['quantity']; ?> pcs</td>
                                    <td class="px-6 py-3 text-right text-slate-600">₱<?= number_format((float)$item['unit_price'], 2); ?></td>
                                    <td class="px-6 py-3 text-right font-bold text-amber-600">₱<?= number_format((float)$item['subtotal'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- SUMMARY FOOTER -->
            <div class="bg-slate-50 px-6 py-5 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm">
                <div class="flex items-center gap-8">
                    <div>
                        <span class="block text-xs uppercase font-semibold text-slate-500">Total Unique Items</span>
                        <span class="font-bold text-xl text-slate-800"><?= count($items); ?></span>
                    </div>
                    <div>
                        <span class="block text-xs uppercase font-semibold text-slate-500">Total Quantity Sold</span>
                        <span class="font-bold text-xl text-slate-800"><?= number_format($totalQty); ?> pcs</span>
                    </div>
                </div>
                <div class="space-y-1 text-right">
                    <div>
                        <span class="text-xs uppercase font-semibold text-slate-500 mr-4">Subtotal:</span>
                        <span class="font-bold text-slate-700">₱<?= number_format((float)$sale['subtotal'], 2); ?></span>
                    </div>
                    <div>
                        <span class="text-xs uppercase font-semibold text-slate-500 mr-4">Discount:</span>
                        <span class="font-bold text-red-600">-₱<?= number_format((float)$sale['discount'], 2); ?></span>
                    </div>
                    <div class="border-t border-slate-200 pt-1">
                        <span class="text-xs uppercase font-semibold text-slate-500 mr-4">Grand Total:</span>
                        <span class="text-2xl font-extrabold text-amber-600">₱<?= number_format((float)$sale['total'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</main>

</body>
</html>
