<?php
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
        p.product_size
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
    <title>Sales Receipt - <?= htmlspecialchars($sale['invoice_no']); ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            body {
                background: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
            .print-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-800 py-8">

    <!-- FLOATING ACTION BAR (Hidden on Print) -->
    <div class="max-w-3xl mx-auto mb-6 flex justify-between items-center no-print px-4">
        <a href="sale_view.php?id=<?= $sale['id']; ?>" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl font-bold text-sm shadow-sm transition flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Back to Details
        </a>
        <button onclick="window.print()" class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2 rounded-xl font-bold text-sm shadow transition flex items-center gap-2">
            <i class="fa-solid fa-print"></i> Print Receipt
        </button>
    </div>

    <!-- PRINTABLE RECEIPT CONTAINER -->
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-slate-200 print-container space-y-6">
        
        <!-- HEADER / LOGO -->
        <div class="flex justify-between items-start border-b border-slate-200 pb-6">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 uppercase tracking-tight">ISU Merchandising Office</h1>
                <p class="text-xs text-slate-500 mt-0.5">Official Merchandise Release Slip</p>
            </div>
            <div class="text-right">
                <span class="inline-block bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider mb-1">
                    Completed
                </span>
                <p class="text-xs text-slate-400 font-mono">Invoice: <?= htmlspecialchars($sale['invoice_no']); ?></p>
            </div>
        </div>

        <!-- META DETAILS -->
        <div class="grid grid-cols-2 gap-4 text-sm bg-slate-50 p-4 rounded-xl border border-slate-100">
            <div class="space-y-1.5">
                <p><span class="font-bold text-slate-500 uppercase text-[11px]">Customer:</span> <span class="text-slate-800 font-semibold"><?= htmlspecialchars($sale['customer_name'] ?? 'Walk-in Customer'); ?></span></p>
                <p><span class="font-bold text-slate-500 uppercase text-[11px]">Release Slip No:</span> <span class="font-mono font-semibold text-slate-800"><?= htmlspecialchars($sale['invoice_no']); ?></span></p>
                <p><span class="font-bold text-slate-500 uppercase text-[11px]">Payment Status:</span> <span class="text-slate-800 font-semibold">Paid at University Cashier</span></p>
            </div>
            <div class="space-y-1.5 text-right sm:text-left">
                <p><span class="font-bold text-slate-500 uppercase text-[11px]">Date & Time:</span> <span class="text-slate-800 font-semibold"><?= date('F j, Y, g:i A', strtotime($sale['sale_date'])); ?></span></p>
                <p><span class="font-bold text-slate-500 uppercase text-[11px]">Released By:</span> <span class="text-slate-800 font-semibold"><?= ucwords(strtolower($cashierName)); ?></span></p>
            </div>
        </div>

        <!-- ITEMS TABLE -->
        <div>
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-100 text-[11px] uppercase tracking-wider text-slate-600 border-b border-slate-200">
                        <th class="p-3 font-bold">Product Name / Code</th>
                        <th class="p-3 font-bold">Size</th>
                        <th class="p-3 font-bold text-center">Qty</th>
                        <th class="p-3 font-bold text-right">Unit Price</th>
                        <th class="p-3 font-bold text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-6 text-slate-400">No items found for this transaction.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="p-3 font-bold text-slate-800">
                                    <?= htmlspecialchars($item['product_name']); ?>
                                    <span class="block font-mono text-[10px] text-slate-400 font-normal"><?= htmlspecialchars($item['product_code']); ?></span>
                                </td>
                                <td class="p-3 text-slate-600"><?= htmlspecialchars($item['product_size'] ?? 'Standard'); ?></td>
                                <td class="p-3 text-center font-bold text-slate-800"><?= (int)$item['quantity']; ?></td>
                                <td class="p-3 text-right text-slate-600">₱<?= number_format((float)$item['unit_price'], 2); ?></td>
                                <td class="p-3 text-right font-bold text-slate-800">₱<?= number_format((float)$item['subtotal'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- SUMMARY TOTALS -->
        <div class="border-t border-slate-200 pt-4 flex justify-between items-center text-sm">
            <div class="space-y-0.5 text-slate-600 text-xs">
                <p>Total Unique Items: <strong class="text-slate-800"><?= count($items); ?></strong></p>
                <p>Total Quantity Sold: <strong class="text-slate-800"><?= number_format($totalQty); ?> pcs</strong></p>
            </div>
            <div class="text-right space-y-1">
                <p class="text-xs text-slate-500">Subtotal: <span class="font-bold text-slate-700">₱<?= number_format((float)$sale['subtotal'], 2); ?></span></p>
                <p class="text-xs text-slate-500">Discount: <span class="font-bold text-red-600">-₱<?= number_format((float)$sale['discount'], 2); ?></span></p>
                <div>
                    <span class="text-xs uppercase font-bold text-slate-400 block">Grand Total</span>
                    <span class="text-xl font-extrabold text-amber-600">₱<?= number_format((float)$sale['total'], 2); ?></span>
                </div>
            </div>
        </div>

        <!-- SIGNATURE LINES -->
        <div class="grid grid-cols-2 gap-12 pt-12 text-center text-xs">
            <div class="space-y-8">
                <div class="border-b border-slate-400 pb-1 font-bold text-slate-800 uppercase">
                    <?= htmlspecialchars($sale['customer_name'] ?? 'Recipient / Student'); ?>
                </div>
                <p class="text-slate-500 font-medium">Recipient / Student</p>
            </div>
            <div class="space-y-8">
                <div class="border-b border-slate-400 pb-1 font-bold text-slate-800 uppercase">
                    <?= ucwords(strtolower($cashierName)); ?>
                </div>
                <p class="text-slate-500 font-medium">Merchandise Releasing Officer</p>
            </div>
        </div>

    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('auto') === 'true') {
            window.onload = () => window.print();
        }
    </script>
</body>
</html>