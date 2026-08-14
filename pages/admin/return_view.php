<?php
$pageTitle = "Supplier Return Details";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";

requireAdmin();

$return_no = trim($_GET['return_no'] ?? '');

if (empty($return_no)) {
    $_SESSION['error_message'] = "Invalid return slip number specified.";
    header("Location: returns.php");
    exit();
}

// Fetch return header details along with user/cashier info
$returnQuery = "
    SELECT 
        r.*, 
        u.username as processed_by_username,
        u.firstname,
        u.lastname
    FROM returns r
    LEFT JOIN users u ON r.processed_by = u.id
    WHERE r.return_no = ?
";
$stmt = mysqli_prepare($conn, $returnQuery);
mysqli_stmt_bind_param($stmt, "s", $return_no);
mysqli_stmt_execute($stmt);
$returnResult = mysqli_stmt_get_result($stmt);
$return_header = mysqli_fetch_assoc($returnResult);

if (!$return_header) {
    $_SESSION['error_message'] = "Return record not found.";
    header("Location: returns.php");
    exit();
}

// Format processor name properly
$processedByName = trim(($return_header['firstname'] ?? '') . ' ' . ($return_header['lastname'] ?? ''));
if (empty($processedByName)) {
    $processedByName = $return_header['processed_by_username'] ?? 'Administrator';
}

// Fetch return items
$itemsQuery = "
    SELECT 
        r.*, 
        p.product_code, 
        p.product_name, 
        p.product_size, 
        p.front_image,
        p.category,
        p.unit_cost
    FROM returns r
    LEFT JOIN products p ON r.product_id = p.id
    WHERE r.return_no = ?
";
$stmtItems = mysqli_prepare($conn, $itemsQuery);
mysqli_stmt_bind_param($stmtItems, "s", $return_no);
mysqli_stmt_execute($stmtItems);
$itemsResult = mysqli_stmt_get_result($stmtItems);

$items = [];
$totalQty = 0;
$totalValue = 0;

while ($item = mysqli_fetch_assoc($itemsResult)) {
    $totalQty += $item['quantity'];
    $totalValue += $item['quantity'] * $item['unit_cost'];
    $items[] = $item;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle . ' - ' . $return_header['return_no']); ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> a { text-decoration: none !important; } </style>
</head>

<body class="bg-slate-50 font-sans text-slate-800">

<?php include "sidebar.php"; ?> 

<main class="ml-0 md:ml-[270px] min-h-screen bg-[#f5f7fb] px-4 py-2 md:px-5 md:py-3 transition-all duration-300">
    <div class="space-y-6 max-w-5xl mx-auto">
        
        <!-- HEADER & ACTIONS -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div>
                <span class="text-xs uppercase tracking-wider font-bold text-slate-400">Supplier Return Details</span>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight font-mono mt-0.5"><?= htmlspecialchars($return_header['return_no']); ?></h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="returns.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2.5 rounded-xl font-bold text-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
                <button onclick="window.open('return_print.php?return_no=<?= urlencode($return_header['return_no']); ?>', '_blank')" class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow transition flex items-center gap-2">
                    <i class="fa-solid fa-print"></i> Print Slip
                </button>
            </div>
        </div>

        <!-- RETURN INFORMATION CARD -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3">Return Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-400">Return Slip Number</span>
                    <span class="font-mono font-bold text-slate-800 text-base"><?= htmlspecialchars($return_header['return_no']); ?></span>
                </div>
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-400">Supplier</span>
                    <span class="font-bold text-slate-800 text-base"><?= htmlspecialchars($return_header['supplier'] ?? 'N/A'); ?></span>
                </div>
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-400">Return Date</span>
                    <span class="font-bold text-slate-800 text-base"><?= date('F j, Y, g:i A', strtotime($return_header['return_date'])); ?></span>
                </div>
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-400">Processed By</span>
                    <span class="font-medium text-slate-700"><?= ucwords(strtolower($processedByName)); ?></span>
                </div>
                <div>
                    <span class="block text-xs uppercase font-semibold text-slate-400">Reference No.</span>
                    <span class="font-medium text-slate-700"><?= htmlspecialchars($return_header['reference_no'] ?: 'N/A'); ?></span>
                </div>
            </div>
        </div>

        <!-- PRODUCTS RETURNED TABLE -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100"><h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Products Returned</h2></div>
            <div class="p-6 space-y-4">
                <?php foreach ($items as $item): ?>
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div class="flex items-center gap-4 flex-1">
                            <img src="<?= !empty($item['front_image']) ? '../../' . htmlspecialchars($item['front_image']) : '../../assets/images/default.png'; ?>" class="w-16 h-16 rounded-xl object-contain bg-white border border-slate-200 p-1 shadow-sm flex-shrink-0">
                            <div class="space-y-1 text-xs">
                                <h3 class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($item['product_name']); ?></h3>
                                <p class="text-slate-500 font-mono">Code: <strong class="text-slate-700"><?= htmlspecialchars($item['product_code']); ?></strong></p>
                                <p class="text-slate-600"><strong>Reason:</strong> <span class="italic"><?= htmlspecialchars($item['reason']); ?></span></p>
                                <?php if(!empty($item['image_path'])): ?>
                                    <a href="../../<?= htmlspecialchars($item['image_path']); ?>" target="_blank" class="text-blue-600 hover:underline font-semibold"><i class="fa-solid fa-paperclip"></i> View Attached Photo</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="text-center"><span class="block text-[10px] uppercase font-bold text-slate-400">Quantity</span><span class="text-sm font-bold text-slate-700"><?= (int)$item['quantity']; ?> pcs</span></div>
                            <div class="text-right"><span class="block text-[10px] uppercase font-bold text-slate-400">Unit Cost</span><span class="text-sm font-semibold text-slate-700">₱<?= number_format((float)$item['unit_cost'], 2); ?></span></div>
                            <div class="text-right min-w-[80px]"><span class="block text-[10px] uppercase font-bold text-slate-400">Total Value</span><span class="font-bold text-amber-600 text-sm">₱<?= number_format((float)$item['quantity'] * (float)$item['unit_cost'], 2); ?></span></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="bg-slate-50 px-6 py-5 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm">
                <div class="flex items-center gap-8">
                    <div><span class="block text-xs uppercase font-semibold text-slate-500">Total Products</span><span class="font-bold text-xl text-slate-800"><?= count($items); ?></span></div>
                    <div><span class="block text-xs uppercase font-semibold text-slate-500">Total Quantity</span><span class="font-bold text-xl text-slate-800"><?= number_format($totalQty); ?> pcs</span></div>
                </div>
                <div class="text-right">
                    <span class="text-xs uppercase font-semibold text-slate-500">Total Return Value:</span>
                    <span class="text-2xl font-extrabold text-amber-600">₱<?= number_format($totalValue, 2); ?></span>
                </div>
            </div>
        </div>
    </div>
</main>

</body>
</html>