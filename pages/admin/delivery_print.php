<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";
require_once "../../includes/activity_log.php";

requireAdmin();

$delivery_id = (int)($_GET['id'] ?? 0);

if ($delivery_id <= 0) {
    $_SESSION['error_message'] = "Invalid delivery ID specified.";
    header("Location: inventory_indeliveries.php");
    exit();
}

// Fetch delivery header details along with supplier and user info
$deliveryQuery = "
    SELECT 
        d.*, 
        s.supplier_name,
        COALESCE(
            NULLIF(CONCAT(TRIM(u.firstname), ' ', TRIM(u.lastname)), ' '),
            u.username,
            'Administrator'
        ) AS received_by_name
    FROM deliveries d
    LEFT JOIN suppliers s ON d.supplier_id = s.id
    LEFT JOIN users u ON d.received_by = u.id
    WHERE d.id = ?
";
$stmt = mysqli_prepare($conn, $deliveryQuery);
mysqli_stmt_bind_param($stmt, "i", $delivery_id);
mysqli_stmt_execute($stmt);
$deliveryResult = mysqli_stmt_get_result($stmt);
$delivery = mysqli_fetch_assoc($deliveryResult);

if (!$delivery) {
    $_SESSION['error_message'] = "Delivery record not found.";
    header("Location: inventory_indeliveries.php");
    exit();
}

logActivity($conn, $_SESSION['user_id'], $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Printed Delivery #{$delivery['delivery_no']}");

// Fetch delivery items
$itemsQuery = "
    SELECT 
        di.*, 
        p.product_code, 
        p.product_name, 
        p.product_size, 
        p.category
    FROM delivery_items di
    LEFT JOIN products p ON di.product_id = p.id
    WHERE di.delivery_id = ?
";
$stmtItems = mysqli_prepare($conn, $itemsQuery);
mysqli_stmt_bind_param($stmtItems, "i", $delivery_id);
mysqli_stmt_execute($stmtItems);
$itemsResult = mysqli_stmt_get_result($stmtItems);

$items = [];
$totalQty = 0;
$grandTotal = 0.00;

while ($item = mysqli_fetch_assoc($itemsResult)) {
    $itemTotal = $item['quantity'] * $item['cost_price'];
    $totalQty += $item['quantity'];
    $grandTotal += $itemTotal;
    $items[] = $item;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Receipt - <?= htmlspecialchars($delivery['delivery_no']); ?></title>
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
        <a href="delivery_view.php?id=<?= $delivery['id']; ?>" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl font-bold text-sm shadow-sm transition flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Back to Details
        </a>
        <button onclick="window.print()" class="bg-[#0f7b3d] hover:bg-[#15803d] text-white px-6 py-2 rounded-xl font-bold text-sm shadow transition flex items-center gap-2">
            <i class="fa-solid fa-print"></i> Print Receipt
        </button>
    </div>

    <!-- PRINTABLE RECEIPT CONTAINER -->
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-slate-200 print-container space-y-6">
        
        <!-- HEADER / LOGO -->
        <div class="flex justify-between items-start border-b border-slate-200 pb-6">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 uppercase tracking-tight">ISU Merchandising Office</h1>
                <p class="text-xs text-slate-500 mt-0.5">Official Inventory Delivery Receipt</p>
            </div>
            <div class="text-right">
                <span class="inline-block bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider mb-1">
                    <?= htmlspecialchars($delivery['status'] ?? 'Completed'); ?>
                </span>
                <p class="text-xs text-slate-400 font-mono">Ref: <?= htmlspecialchars($delivery['delivery_no']); ?></p>
            </div>
        </div>

        <!-- META DETAILS -->
        <div class="grid grid-cols-2 gap-4 text-sm bg-slate-50 p-4 rounded-xl border border-slate-100">
            <div class="space-y-1.5">
                <p><span class="font-bold text-slate-500 uppercase text-[11px]">Supplier:</span> <span class="text-slate-800 font-semibold"><?= htmlspecialchars($delivery['supplier_name'] ?? 'N/A'); ?></span></p>
                <p><span class="font-bold text-slate-500 uppercase text-[11px]">Delivery No:</span> <span class="font-mono font-semibold text-slate-800"><?= htmlspecialchars($delivery['delivery_no']); ?></span></p>
                <p><span class="font-bold text-slate-500 uppercase text-[11px]">DR Number:</span> <span class="text-slate-800 font-semibold"><?= !empty($delivery['dr_number']) ? htmlspecialchars($delivery['dr_number']) : 'None'; ?></span></p>
            </div>
            <div class="space-y-1.5 text-right sm:text-left">
                <p><span class="font-bold text-slate-500 uppercase text-[11px]">Delivery Date:</span> <span class="text-slate-800 font-semibold"><?= date('F j, Y, g:i A', strtotime($delivery['delivery_date'])); ?></span></p>
                <p><span class="font-bold text-slate-500 uppercase text-[11px]">Received By:</span> <span class="text-slate-800 font-semibold"><?= ucwords(strtolower($delivery['received_by_name'])); ?></span></p>
                <?php if (!empty($delivery['remarks'])): ?>
                    <p><span class="font-bold text-slate-500 uppercase text-[11px]">Remarks:</span> <span class="text-slate-800 italic"><?= htmlspecialchars($delivery['remarks']); ?></span></p>
                <?php endif; ?>
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
                        <th class="p-3 font-bold text-right">Cost Price</th>
                        <th class="p-3 font-bold text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-6 text-slate-400">No items found for this delivery.</td>
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
                                <td class="p-3 text-right text-slate-600">₱<?= number_format((float)$item['cost_price'], 2); ?></td>
                                <td class="p-3 text-right font-bold text-slate-800">₱<?= number_format((float)($item['quantity'] * $item['cost_price']), 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- SUMMARY TOTALS -->
        <div class="border-t border-slate-200 pt-4 flex justify-between items-center text-sm">
            <div class="space-y-0.5 text-slate-600 text-xs">
                <p>Total Unique Products: <strong class="text-slate-800"><?= count($items); ?></strong></p>
                <p>Total Quantity Received: <strong class="text-slate-800"><?= number_format($totalQty); ?> pcs</strong></p>
            </div>
            <div class="text-right">
                <span class="text-xs uppercase font-bold text-slate-400 block">Grand Total</span>
                <span class="text-xl font-extrabold text-[#0f7b3d]">₱<?= number_format($grandTotal, 2); ?></span>
            </div>
        </div>

        <!-- SIGNATURE LINES -->
        <div class="grid grid-cols-3 gap-6 pt-12 text-center text-xs">
            <div class="space-y-8">
                <div class="border-b border-slate-400 pb-1 font-bold text-slate-800 uppercase">
                    <?= ucwords(strtolower($delivery['received_by_name'])); ?>
                </div>
                <p class="text-slate-500 font-medium">Received By (Staff / Admin)</p>
            </div>
            <div class="space-y-8">
                <div class="border-b border-slate-400 pb-1 font-bold text-slate-800 uppercase">
                    <?= htmlspecialchars($delivery['supplier_name'] ?? 'Authorized Rep'); ?>
                </div>
                <p class="text-slate-500 font-medium">Supplier / Representative</p>
            </div>
            <div class="space-y-8">
                <div class="border-b border-slate-400 pb-1 font-bold text-slate-800 uppercase">
                    ISU Management
                </div>
                <p class="text-slate-500 font-medium">Approved By</p>
            </div>
        </div>

    </div>

    <script>
        // Optional auto-print trigger if passed via URL parameter e.g. delivery_print.php?id=1&auto=true
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('auto') === 'true') {
            window.onload = () => window.print();
        }
    </script>
</body>
</html>