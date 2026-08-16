<?php
$pageTitle = "Sales/Out Dashboard";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";

requireAdmin();

// --- HANDLE GET ACTIONS (DELETE) ---
if (isset($_GET['action_type']) && $_GET['action_type'] === 'delete') {
    $sale_id = (int)($_GET['id'] ?? 0);

    if ($sale_id > 0) {
        // Verify that the deletion was authorized by a Super Admin in the last 5 minutes
        if (isset($_SESSION['delete_authorized']) && $_SESSION['delete_authorized'] === true && (time() - $_SESSION['delete_auth_time'] < 300)) {
            
            mysqli_begin_transaction($conn);

            try {
                // Get items from the sale to restock them
                $itemsQuery = mysqli_query($conn, "SELECT product_id, quantity FROM sale_items WHERE sale_id = $sale_id");
                if (!$itemsQuery) throw new Exception("Could not retrieve sale items for restocking.");

                while ($item = mysqli_fetch_assoc($itemsQuery)) {
                    // Restock product
                    $updateStockQuery = "UPDATE products SET current_stock = current_stock + {$item['quantity']} WHERE id = {$item['product_id']}";
                    if (!mysqli_query($conn, $updateStockQuery)) {
                        throw new Exception("Failed to restock product ID {$item['product_id']}.");
                    }
                }

                // Delete the sale record itself
                if (!mysqli_query($conn, "DELETE FROM sales WHERE id = $sale_id")) {
                    throw new Exception("Failed to delete the main sale record.");
                }

                mysqli_commit($conn);
                
                logActivity($conn, $_SESSION['user_id'], $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Deleted Sale Record #{$sale_id}. Reason: " . ($_SESSION['delete_auth_reason'] ?? 'N/A'));
                $_SESSION['success_message'] = "Sale record deleted and items have been restocked successfully.";

            } catch (Exception $e) {
                mysqli_rollback($conn);
                $_SESSION['error_message'] = "Error during deletion: " . $e->getMessage();
            }

            unset($_SESSION['delete_authorized'], $_SESSION['delete_auth_time'], $_SESSION['delete_auth_reason']);
        } else {
            $_SESSION['error_message'] = "Unauthorized deletion attempt. Please re-authenticate.";
        }
    }
    header("Location: inventory_outsales.php");
    exit();
}

// --- 1. DASHBOARD STATISTICS METRICS ---
$totalSales = 0;
$todaySales = 0;
$totalItemsSold = 0;
$totalRevenue = 0.00;

// Total Sales Transactions
$resTotal = mysqli_query($conn, "SELECT COUNT(*) AS total FROM sales");
if ($resTotal && $row = mysqli_fetch_assoc($resTotal)) {
    $totalSales = (int)$row['total'];
}

// Today's Sales Transactions
$resToday = mysqli_query($conn, "SELECT COUNT(*) AS total FROM sales WHERE DATE(sale_date) = CURDATE()");
if ($resToday && $row = mysqli_fetch_assoc($resToday)) {
    $todaySales = (int)$row['total'];
}

// Total Items Sold
$resItems = mysqli_query($conn, "SELECT COALESCE(SUM(quantity), 0) AS total FROM sale_items");
if ($resItems && $row = mysqli_fetch_assoc($resItems)) {
    $totalItemsSold = (int)$row['total'];
}

// Total Revenue Generated
$resRevenue = mysqli_query($conn, "SELECT COALESCE(SUM(total), 0) AS total_revenue FROM sales");
if ($resRevenue && $row = mysqli_fetch_assoc($resRevenue)) {
    $totalRevenue = (float)$row['total_revenue'];
}

// --- 2. SEARCH & RECENT SALES ---
$search = trim($_GET['search'] ?? '');
$searchQuery = "";

if (!empty($search)) {
    $safeSearch = mysqli_real_escape_string($conn, $search);
    $searchQuery = " WHERE s.invoice_no LIKE '%$safeSearch%' OR s.customer_name LIKE '%$safeSearch%' ";
}

// --- 3. PAGINATION SETUP ---
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limit = 5; // Records per page
$offset = ($page - 1) * $limit;

// Count total records for pagination
$countQuery = "SELECT COUNT(*) as total FROM sales s $searchQuery";
$countResult = mysqli_query($conn, $countQuery);
$totalRecords = mysqli_fetch_assoc($countResult)['total'] ?? 0;
$totalPages = ceil($totalRecords / $limit);
if ($totalPages < 1) $totalPages = 1;


$sales = [];
$query = "
    SELECT 
        s.id, 
        s.invoice_no, 
        s.sale_date, 
        s.customer_name, 
        s.total,
        COALESCE(SUM(si.quantity), 0) as total_items
    FROM sales s
    LEFT JOIN sale_items si ON s.id = si.sale_id
    $searchQuery
    GROUP BY s.id
    ORDER BY s.sale_date DESC, s.id DESC
    LIMIT $limit OFFSET $offset
";

$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Fetch first product details for image preview matching delivery layout
        $itemMetaQuery = mysqli_query($conn, "
            SELECT p.product_name, p.front_image, p.product_size 
            FROM sale_items si 
            JOIN products p ON si.product_id = p.id 
            WHERE si.sale_id = {$row['id']} 
            LIMIT 1
        ");
        if ($itemMetaQuery && $itemMetaRow = mysqli_fetch_assoc($itemMetaQuery)) {
            $row['product_name'] = $itemMetaRow['product_name'];
            $row['front_image'] = $itemMetaRow['front_image'];
            $row['product_size'] = $itemMetaRow['product_size'];
        } else {
            $row['product_name'] = 'Various Products';
            $row['front_image'] = '';
            $row['product_size'] = '';
        }
        $sales[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <!-- Bootstrap CSS for Modal Compatibility -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

    <div class="space-y-6 max-w-7xl mx-auto">
        
        <!-- NOTIFICATIONS -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span><?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></span>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-red-600"></i>
                <span><?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></span>
            </div>
        <?php endif; ?>

        <!-- HEADER -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Sales & Outflows Management</h1>
                <p class="text-sm text-slate-500 mt-0.5">Track and process point-of-sale transactions and item outflows.</p>
            </div>
            <a href="sale_form.php" class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2.5 rounded-xl font-semibold text-sm flex items-center gap-2 shadow transition">
                <i class="fa fa-plus"></i> New Sale Transaction
            </a>
        </div>

        <!-- KPI CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 border-l-4 border-l-blue-500">
                <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">Total Sales Transactions</p>
                <h2 class="text-3xl font-extrabold text-slate-800 mt-1"><?= number_format($totalSales); ?></h2>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 border-l-4 border-l-emerald-500">
                <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">Today's Sales</p>
                <h2 class="text-3xl font-extrabold text-slate-800 mt-1"><?= number_format($todaySales); ?></h2>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 border-l-4 border-l-emerald-500">
                <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">Total Items Sold</p>
                <h2 class="text-3xl font-extrabold text-slate-800 mt-1"><?= number_format($totalItemsSold); ?></h2>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 border-l-4 border-l-amber-500">
                <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">Total Revenue</p>
                <h2 class="text-3xl font-extrabold text-amber-600 mt-1">₱<?= number_format($totalRevenue, 2); ?></h2>
            </div>
        </div>

        <!-- RECENT SALES TABLE -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <form method="GET" action="" class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <h2 class="text-base font-bold text-slate-800 whitespace-nowrap">Recent Sales Records</h2>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <div class="relative flex-1 sm:w-72">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 text-sm"><i class="fa-solid fa-magnifying-glass text-xs"></i></span>
                            <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Search invoice no, customer..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition">
                        </div>
                        <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition shadow-sm"><i class="fa-solid fa-filter"></i></button>
                        <?php if(!empty($search)): ?><a href="inventory_outsales.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2.5 rounded-xl text-sm font-medium transition" title="Reset Filters"><i class="fa-solid fa-arrows-rotate"></i></a><?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 font-semibold">Product / Invoice No.</th>
                            <th class="px-6 py-3 font-semibold">Customer / Recipient</th>
                            <th class="px-6 py-3 font-semibold">Date</th>
                            <th class="px-6 py-3 font-semibold">Items</th>
                            <th class="px-6 py-3 font-semibold text-right">Total Value</th>
                            <th class="px-6 py-3 font-semibold text-center">Status</th>
                            <th class="px-6 py-3 font-semibold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php if (empty($sales)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-12 text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300"></i>
                                        <p class="font-medium text-slate-600">No sales or outflow records found.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sales as $sale): 
                                $img = !empty($sale['front_image']) ? "../../" . $sale['front_image'] : "../../assets/images/default.png";
                            ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <img src="<?= htmlspecialchars($img); ?>" class="w-12 h-12 rounded-xl object-contain border bg-white p-1 shadow-sm flex-shrink-0" onerror="this.src='../../assets/images/default.png'">
                                            <div>
                                                <h4 class="font-bold text-slate-900 text-xs"><?= htmlspecialchars($sale['product_name']); ?></h4>
                                                <p class="text-slate-500 font-mono text-xs"><?= htmlspecialchars($sale['invoice_no']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-slate-800"><?= htmlspecialchars($sale['customer_name'] ?? 'Walk-in Customer'); ?></td>
                                    <td class="px-6 py-4 text-slate-600 text-xs whitespace-nowrap"><?= date('M d, Y, g:i A', strtotime($sale['sale_date'])); ?></td>
                                    <td class="px-6 py-4 font-bold text-slate-700 text-xs"><?= (int)$sale['total_items']; ?> pcs</td>
                                    <td class="px-6 py-4 text-right font-bold text-amber-600">₱<?= number_format((float)$sale['total'], 2); ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                                            <i class="fa-solid fa-circle-check mr-1.5 text-[10px]"></i> Completed
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="sale_view.php?id=<?= $sale['id']; ?>" class="bg-blue-50 hover:bg-blue-100 text-blue-600 p-2 rounded-lg text-xs font-bold transition" title="View Details">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <button onclick="window.open('sale_print.php?id=<?= $sale['id']; ?>', '_blank')" class="bg-slate-100 hover:bg-slate-200 text-slate-600 p-2 rounded-lg text-xs font-bold transition" title="Print Receipt">
                                                <i class="fa-solid fa-print"></i>
                                            </button>
                                            <button type="button" onclick="confirmDeleteSale(<?= $sale['id']; ?>, 'Invoice #<?= htmlspecialchars($sale['invoice_no']); ?>')" class="bg-red-50 hover:bg-red-100 text-red-600 p-2 rounded-lg text-xs font-bold transition" title="Delete Sale">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION CONTROLS -->
            <?php if ($totalPages > 1): ?>
            <div class="p-6 border-t border-slate-200 flex justify-end items-center gap-2">
                <?php 
                    $queryParams = $_GET;
                    if ($page > 1): 
                        $queryParams['p'] = $page - 1;
                ?>
                    <a href="?<?= http_build_query($queryParams); ?>" class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50 shadow-sm transition"><i class="fa-solid fa-chevron-left text-xs"></i></a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): $queryParams['p'] = $i; ?>
                    <a href="?<?= http_build_query($queryParams); ?>" class="px-3.5 py-2 border rounded-xl text-sm font-medium shadow-sm transition <?= ($i == $page) ? 'bg-amber-600 text-white border-amber-600' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'; ?>"><?= $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): $queryParams['p'] = $page + 1; ?>
                    <a href="?<?= http_build_query($queryParams); ?>" class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50 shadow-sm transition"><i class="fa-solid fa-chevron-right text-xs"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>

</main>

<!-- SUPER ADMIN DELETE AUTHENTICATION MODAL -->
<div class="modal fade" id="deleteSaleAuthModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-xl overflow-hidden">
            <div class="modal-header bg-red-600 text-white px-6 py-4">
                <h5 class="modal-title font-bold flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> Super Admin Delete Authorization
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteSaleAuthForm">
                <div class="modal-body p-6 space-y-4">
                    <input type="hidden" id="delete_sale_id">
                    
                    <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-xl text-xs">
                        <p><span class="font-bold">Warning:</span> You are about to delete <span id="delete_sale_name_label" class="font-semibold underline"></span>. This action is irreversible and requires Super Admin privileges.</p>
                        <p class="mt-1"><span class="font-bold">Important:</span> All items from this sale will be automatically restocked.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Super Admin Username</label>
                        <input type="text" id="delete_auth_username" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Password</label>
                        <input type="password" id="delete_auth_password" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Reason for Deletion</label>
                        <textarea id="delete_auth_reason" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required placeholder="e.g., Incorrect transaction / Test data"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-slate-50 border-t border-slate-100 px-6 py-3 flex justify-end gap-2">
                    <button type="button" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-medium transition shadow-sm">
                        <i class="fa-solid fa-trash mr-1.5"></i> Verify & Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmDeleteSale(saleId, saleName) {
        document.getElementById('delete_sale_id').value = saleId;
        document.getElementById('delete_sale_name_label').innerText = saleName;
        
        document.getElementById('delete_auth_username').value = '';
        document.getElementById('delete_auth_password').value = '';
        document.getElementById('delete_auth_reason').value = '';

        var deleteModal = new bootstrap.Modal(document.getElementById('deleteSaleAuthModal'));
        deleteModal.show();
    }

    document.getElementById("deleteSaleAuthForm").addEventListener("submit", function(e) {
        e.preventDefault();
        let saleId = document.getElementById("delete_sale_id").value;
        fetch("../../includes/verify_delete_auth.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                item_id: saleId,
                username: document.getElementById("delete_auth_username").value,
                password: document.getElementById("delete_auth_password").value,
                reason: document.getElementById("delete_auth_reason").value
            })
        }).then(res => res.json()).then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById("deleteSaleAuthModal")).hide();
                window.location.href = `inventory_outsales.php?action_type=delete&id=${saleId}`;
            } else {
                alert("Authorization Failed: " + data.message);
            }
        }).catch(err => alert("An error occurred during verification."));
    });
</script>
</body>
</html>