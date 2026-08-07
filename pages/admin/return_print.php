<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";

requireAdmin();

$return_no = trim($_GET['return_no'] ?? '');

if (empty($return_no)) {
    die("Invalid return slip number.");
}

// Fetch return header
$stmt = mysqli_prepare($conn, "SELECT r.*, u.firstname, u.lastname, u.username FROM returns r LEFT JOIN users u ON r.processed_by = u.id WHERE r.return_no = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $return_no);
mysqli_stmt_execute($stmt);
$return_header = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$return_header) {
    die("Return record not found.");
}

$processedByName = trim(($return_header['firstname'] ?? '') . ' ' . ($return_header['lastname'] ?? ''));
if (empty($processedByName)) {
    $processedByName = $return_header['username'] ?? 'Administrator';
}

// Fetch items
$itemsStmt = mysqli_prepare($conn, "SELECT r.quantity, r.reason, p.product_code, p.product_name, p.product_size FROM returns r JOIN products p ON r.product_id = p.id WHERE r.return_no = ?");
mysqli_stmt_bind_param($itemsStmt, "s", $return_no);
mysqli_stmt_execute($itemsStmt);
$itemsResult = mysqli_stmt_get_result($itemsStmt);
$items = [];
while($row = mysqli_fetch_assoc($itemsResult)) {
    $items[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Return Slip - <?= htmlspecialchars($return_header['return_no']); ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .print-container { box-shadow: none !important; border: none !important; padding: 0 !important; margin: 0 !important; width: 100% !important; }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-800 py-8">

    <div class="max-w-3xl mx-auto mb-6 flex justify-between items-center no-print px-4">
        <a href="returns.php" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl font-bold text-sm shadow-sm transition flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Back to History
        </a>
        <button onclick="window.print()" class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2 rounded-xl font-bold text-sm shadow transition flex items-center gap-2">
            <i class="fa-solid fa-print"></i> Print Slip
        </button>
    </div>

    <div class="max-w-3xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-slate-200 print-container space-y-6">
        
        <!-- HEADER WITH LOGO & ORANGE ACCENT (Matching Sales/Delivery Receipts Layout) -->
        <div class="flex justify-between items-start border-b-2 border-amber-600 pb-6">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 uppercase tracking-tight">ISABELA STATE UNIVERSITY</h1>
                <p class="text-xs font-bold text-slate-600 uppercase tracking-widest mt-0.5">Merchandising Office</p>
                <p class="text-[11px] text-slate-400">Cauayan City Campus</p>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider inline-block mb-2 <?= $return_header['status'] === 'Returned' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($return_header['status'] === 'Approved' ? 'bg-blue-100 text-blue-800 border border-blue-200' : ($return_header['status'] === 'Rejected' ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-amber-100 text-amber-800 border border-amber-200')); ?>">
                    <?= htmlspecialchars($return_header['status']); ?>
                </span>
                <p class="text-xs text-slate-400 font-mono">Ref: <?= htmlspecialchars($return_header['return_no']); ?></p>
            </div>
        </div>

        <!-- UNIFIED INFORMATION PANEL (Exact layout match to Sales & Delivery Receipts) -->
        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80 grid grid-cols-2 gap-4 text-xs">
            <div class="space-y-1.5">
                <p><span class="font-bold text-slate-500 uppercase text-[10px]">Supplier:</span> <span class="text-slate-800 font-semibold text-sm block"><?= htmlspecialchars($return_header['supplier'] ?? 'N/A'); ?></span></p>
                <p><span class="font-bold text-slate-500 uppercase text-[10px]">Return Slip No:</span> <span class="font-mono font-semibold text-slate-800"><?= htmlspecialchars($return_header['return_no']); ?></span></p>
                <p><span class="font-bold text-slate-500 uppercase text-[10px]">Reference No:</span> <span class="font-mono font-semibold text-slate-800"><?= htmlspecialchars($return_header['reference_no'] ?: 'N/A'); ?></span></p>
            </div>
            <div class="space-y-1.5">
                <p><span class="font-bold text-slate-500 uppercase text-[10px]">Prepared By:</span> <span class="text-slate-800 font-semibold"><?= ucwords(strtolower($processedByName)); ?></span></p>
            </div>
        </div>

        <!-- PRODUCTS TABLE WITH PHOTO -->
        <div>
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100 text-[10px] uppercase tracking-wider text-slate-600 border-b border-slate-200">
                        <th class="p-3 font-bold">Product Name / Code</th>
                        <th class="p-3 font-bold">Size</th>
                        <th class="p-3 font-bold text-center">Qty</th>
                        <th class="p-3 font-bold">Reason for Return</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    <?php 
                    $totalQty = 0;
                    foreach ($items as $item): 
                        $totalQty += (int)$item['quantity'];
                    ?>
                        <tr>
                            <td class="p-3 font-bold text-slate-800">
                                <?= htmlspecialchars($item['product_name']); ?>
                                <span class="block font-mono text-[10px] text-slate-400 font-normal"><?= htmlspecialchars($item['product_code']); ?></span>
                            </td>
                            <td class="p-3 text-slate-600"><?= htmlspecialchars($item['product_size'] ?? 'Standard'); ?></td>
                            <td class="p-3 text-center font-bold text-slate-800"><?= (int)$item['quantity']; ?> pcs</td>
                            <td class="p-3 text-slate-600 italic"><?= htmlspecialchars($item['reason']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- SUMMARY FOOTER BLOCK (Matching Delivery/Sales layout style) -->
        <div class="border-t border-slate-200 pt-3 flex justify-between items-center text-xs">
            <div>
                <p class="text-slate-500">Total Unique Items: <strong class="text-slate-800"><?= count($items); ?></strong></p>
                <p class="text-slate-500">Total Quantity Returned: <strong class="text-slate-800"><?= $totalQty; ?> pcs</strong></p>
            </div>
        </div>

        <!-- REMARKS SECTION -->
        <?php if(!empty($return_header['remarks'])): ?>
        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/80 text-xs space-y-1">
            <span class="font-bold uppercase tracking-wider text-slate-400 text-[10px] block">Remarks</span>
            <p class="text-slate-700 italic"><?= htmlspecialchars($return_header['remarks']); ?></p>
        </div>
        <?php endif; ?>

        <!-- SIGNATURE TABLE (3 Columns matching Delivery Receipt) -->
        <div class="grid grid-cols-3 gap-8 pt-8 text-center text-xs">
            <div class="space-y-4">
                <div class="border-b border-slate-400 pb-1 font-bold text-slate-800 uppercase"><?= ucwords(strtolower($processedByName)); ?></div>
                <p class="text-slate-500 font-medium">Prepared By (Staff / Admin)</p>
            </div>
            <div class="space-y-4">
                <div class="border-b border-slate-400 pb-1"></div>
                <p class="text-slate-500 font-medium">Checked By (Merchandising Officer)</p>
            </div>
            <div class="space-y-4">
                <div class="border-b border-slate-400 pb-1 font-bold text-slate-800 uppercase"><?= htmlspecialchars($return_header['supplier'] ?? 'Supplier'); ?></div>
                <p class="text-slate-500 font-medium">Supplier / Representative</p>
            </div>
        </div>

        <!-- SYSTEM GENERATED FOOTER -->
        <div class="text-center pt-4 text-[11px] text-slate-400 border-t border-slate-200 space-y-0.5">
            <p class="font-bold text-slate-600">Inventory Management & Billing System — ISU Merchandising Office</p>
            <p>This document is system generated.</p>
            <p class="font-mono text-[10px]">Printed: <?= date('F j, Y, g:i A'); ?></p>
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