<?php
$pageTitle = "Delivery Details";

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

// Fetch delivery header details along with supplier and user info using CONCAT and COALESCE
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

logActivity($conn, $_SESSION['user_id'], $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Viewed Delivery #{$delivery['delivery_no']}");

// Fetch delivery items
$itemsQuery = "
    SELECT 
        di.*, 
        p.product_code, 
        p.product_name, 
        p.product_size, 
        p.front_image,
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
    <title><?= htmlspecialchars($pageTitle . ' - ' . $delivery['delivery_no']); ?></title>
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

<main class="ml-0 md:ml-[270px] min-h-screen p-6 transition-all duration-300">

    <div class="space-y-6 max-w-5xl mx-auto">
        
        <!-- HEADER & ACTIONS -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div>
                <span class="text-xs uppercase tracking-wider font-bold text-slate-400">Delivery Details</span>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight font-mono mt-0.5"><?= htmlspecialchars($delivery['delivery_no']); ?></h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="inventory_indeliveries.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2.5 rounded-xl font-bold text-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
                <button onclick="window.open('delivery_print.php?id=<?= $delivery['id']; ?>', '_blank')" class="bg-[#0f7b3d] hover:bg-[#15803d] text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow transition flex items-center gap-2">
                    <i class="fa-solid fa-print"></i> Print Delivery
                </button>
            </div>
        </div>

        <!-- DELIVERY INFORMATION CARD -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3">Delivery Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-400">Delivery No.</span>
                    <span class="font-mono font-bold text-slate-800 text-base"><?= htmlspecialchars($delivery['delivery_no']); ?></span>
                </div>
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-400">Supplier</span>
                    <span class="font-bold text-slate-800 text-base"><?= htmlspecialchars($delivery['supplier_name'] ?? 'N/A'); ?></span>
                </div>
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-400">Delivery Date</span>
                    <span class="font-bold text-slate-800 text-base"><?= date('F j, Y, g:i A', strtotime($delivery['delivery_date'])); ?></span>
                </div>
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-400">DR Number</span>
                    <span class="font-medium text-slate-700"><?= !empty($delivery['dr_number']) ? htmlspecialchars($delivery['dr_number']) : 'None'; ?></span>
                </div>
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-400">Received By</span>
                    <span class="font-medium text-slate-700"><?= ucwords(strtolower($delivery['received_by_name'])); ?></span>
                </div>
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-400">Status</span>
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-bold mt-0.5">
                        <i class="fa-solid fa-circle-check mr-1"></i> <?= htmlspecialchars($delivery['status'] ?? 'Completed'); ?>
                    </span>
                </div>
                <?php if (!empty($delivery['remarks'])): ?>
                <div class="md:col-span-3">
                    <span class="block text-xs uppercase font-semibold text-slate-400">Remarks</span>
                    <p class="text-slate-600 mt-1 bg-slate-50 p-3 rounded-xl border border-slate-100"><?= htmlspecialchars($delivery['remarks']); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- PRODUCTS RECEIVED TABLE -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Products Received</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 font-semibold">Image</th>
                            <th class="px-6 py-3 font-semibold">Product</th>
                            <th class="px-6 py-3 font-semibold">Size</th>
                            <th class="px-6 py-3 font-semibold text-center">Quantity</th>
                            <th class="px-6 py-3 font-semibold text-right">Cost Price</th>
                            <th class="px-6 py-3 font-semibold text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-12 text-slate-400">No items recorded for this delivery.</td>
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
                                    <td class="px-6 py-3 text-right text-slate-600">₱<?= number_format((float)$item['cost_price'], 2); ?></td>
                                    <td class="px-6 py-3 text-right font-bold text-[#0f7b3d]">₱<?= number_format((float)($item['quantity'] * $item['cost_price']), 2); ?></td>
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
                        <span class="block text-xs uppercase font-semibold text-slate-500">Total Products</span>
                        <span class="font-bold text-xl text-slate-800"><?= count($items); ?></span>
                    </div>
                    <div>
                        <span class="block text-xs uppercase font-semibold text-slate-500">Total Quantity</span>
                        <span class="font-bold text-xl text-slate-800"><?= number_format($totalQty); ?> pcs</span>
                    </div>
                </div>
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-500 text-right">Grand Total</span>
                    <span class="text-2xl font-extrabold text-[#0f7b3d]">₱<?= number_format($grandTotal, 2); ?></span>
                </div>
            </div>
        </div>

    </div>

</main>

</body>
</html>