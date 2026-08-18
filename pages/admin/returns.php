<?php
$pageTitle = "Supplier Returns Management";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";
require_once "../../includes/activity_log.php";

requireAdmin();

// 2. DELETE RETURN RECORD VIA GET/REQUEST
if (isset($_GET['action_type']) && $_GET['action_type'] === 'delete') {
    $return_id = (int)($_GET['id'] ?? 0);

    if ($return_id > 0) {
        if (isset($_SESSION['delete_authorized']) && $_SESSION['delete_authorized'] === true && (time() - $_SESSION['delete_auth_time'] < 300)) {
            $delQuery = mysqli_query($conn, "DELETE FROM returns WHERE id = $return_id");
            if ($delQuery) {
                logActivity($conn, $_SESSION['user_id'], $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Deleted supplier return record #{$return_id}. Reason: " . ($_SESSION['delete_auth_reason'] ?? 'N/A'));
                $_SESSION['success_message'] = "Supplier return record deleted successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to delete supplier return record.";
            }
            unset($_SESSION['delete_authorized'], $_SESSION['delete_auth_time'], $_SESSION['delete_auth_reason']);
        } else {
            $_SESSION['error_message'] = "Unauthorized deletion attempt. Please re-authenticate.";
        }
    }
    header("Location: returns.php");
    exit();
}

// --- METRICS / KPI CALCULATIONS ---
$totalReturnsCount = 0;
$pendingReturnsCount = 0;
$returnedSuppliersCount = 0;
$rejectedReturnsCount = 0;
$totalReturnedQty = 0;

$qtyQuery = mysqli_query($conn,"
    SELECT COALESCE(SUM(quantity),0) AS qty
    FROM returns
");

if($qty = mysqli_fetch_assoc($qtyQuery)){
    $totalReturnedQty = (int)$qty['qty'];
}

$kpiQuery = mysqli_query($conn, "
    SELECT 
        COUNT(DISTINCT return_no) AS total_returns,
        COUNT(DISTINCT CASE WHEN status = 'Returned' THEN return_no END) AS returned_to_supplier,
        COUNT(DISTINCT CASE WHEN status = 'Approved' THEN return_no END) AS approved_by_supplier,
        COUNT(DISTINCT CASE WHEN status = 'Rejected' THEN return_no END) AS rejected_by_supplier
    FROM returns
");
if ($kpiRes = mysqli_fetch_assoc($kpiQuery)) {
    $totalReturnsCount = (int)$kpiRes['total_returns'];
    $returnedSuppliersCount = (int)$kpiRes['returned_to_supplier'];
    $approvedBySupplierCount = (int)$kpiRes['approved_by_supplier'];
    $rejectedReturnsCount = (int)$kpiRes['rejected_by_supplier'];
}

// --- SEARCH & FETCH RETURNS LIST ---
$search = trim($_GET['search'] ?? '');
$searchQuery = "";
if (!empty($search)) {
    $safeSearch = mysqli_real_escape_string($conn, $search);
    $searchQuery = " WHERE p.product_name LIKE '%$safeSearch%' OR p.product_code LIKE '%$safeSearch%' OR r.supplier LIKE '%$safeSearch%' OR r.return_no LIKE '%$safeSearch%' ";
}

// --- PAGINATION SETUP ---
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limit = 5; // Records per page
$offset = ($page - 1) * $limit;

// Count total records for pagination
$countQuery = "SELECT COUNT(r.id) as total FROM returns r LEFT JOIN products p ON r.product_id = p.id $searchQuery";
$countResult = mysqli_query($conn, $countQuery);
$totalRecords = mysqli_fetch_assoc($countResult)['total'] ?? 0;
$totalPages = ceil($totalRecords / $limit);
if ($totalPages < 1) $totalPages = 1;

$returnsList = [];
$query = "
    SELECT 
        r.*, 
        p.product_code, 
        p.product_name, 
        p.product_size,
        p.front_image,
        u.username as processed_by_username
    FROM returns r
    LEFT JOIN products p ON r.product_id = p.id
    LEFT JOIN users u ON r.processed_by = u.id
    $searchQuery
    ORDER BY r.return_date DESC
    LIMIT $limit OFFSET $offset
";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $returnsList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS for Modals -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        a { text-decoration: none !important; }
        .modal-backdrop { z-index: 1040 !important; }
        .modal { z-index: 1050 !important; }
        @media print {
            body * { visibility: hidden; }
            #printSection, #printSection * { visibility: visible; }
            #printSection { position: absolute; left: 0; top: 0; width: 100%; }
        }
    </style>
</head>

<body class="bg-slate-50 font-sans text-slate-800">

<?php include "sidebar.php"; ?> 

<main class="ml-0 md:ml-[270px] min-h-screen bg-[#f5f7fb] px-4 py-2 md:px-5 md:py-3 transition-all duration-300">
    <div class="space-y-6 max-w-7xl mx-auto">
        
        <!-- NOTIFICATIONS -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm flex items-center justify-between">
                <span><i class="fa-solid fa-circle-check mr-2"></i> <?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></span>
                <button onclick="this.parentElement.remove();" class="text-emerald-500 hover:text-emerald-700"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm flex items-center justify-between">
                <span><i class="fa-solid fa-circle-exclamation mr-2"></i> <?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></span>
                <button onclick="this.parentElement.remove();" class="text-red-500 hover:text-red-700"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>

        <!-- HEADER & TOP ACTION BUTTONS -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Supplier Returns Management</h1>
                <p class="text-sm text-slate-500 mt-0.5">Record and manage products returned from ISU Merchandising back to suppliers.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="return_form.php" class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2.5 rounded-xl font-semibold text-sm flex items-center gap-2 shadow transition">
                    </i> New Supplier Return
                </a>
            </div>
        </div>

        <!-- KPI STATS CARDS (4) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Returns -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 border-l-4 border-l-blue-500">
                <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">
                    Total Returns
                </p>

                <h2 class="text-3xl font-extrabold text-slate-800 mt-2">
                    <?= number_format($totalReturnsCount); ?>
                </h2>

            
            </div>

            <!-- Returned to Supplier -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 border-l-4 border-l-emerald-500">
                <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">
                    Returned to Supplier
                </p>

                <h2 class="text-3xl font-extrabold text-emerald-600 mt-2">
                    <?= number_format($returnedSuppliersCount); ?>
                </h2>

              
            </div>

            <!-- Total Quantity Returned -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 border-l-4 border-l-amber-500">
                <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">
                    Total Quantity Returned
                </p>

                <h2 class="text-3xl font-extrabold text-amber-600 mt-2">
                    <?= number_format($totalReturnedQty); ?>
                </h2>

               
            </div>
        </div>

        <!-- RETURNS TABLE -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <form method="GET" action="" class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <h2 class="text-base font-bold text-slate-800 whitespace-nowrap">Supplier Return History</h2>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <div class="flex-1 sm:w-72">
                            <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Search Return No, Supplier, Product..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition">
                        </div>
                        <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition shadow-sm">Filter</button>
                        <?php if(!empty($search)): ?><a href="returns.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2.5 rounded-xl text-sm font-medium transition" title="Reset Filters"><i class="fa-solid fa-arrows-rotate"></i></a><?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <tr class="whitespace-nowrap">
                            <th class="px-6 py-3 font-semibold">Product</th>
                            <th class="px-6 py-3 font-semibold">Supplier</th>
                            <th class="px-6 py-3 font-semibold">Return Slip</th>
                            <th class="px-6 py-3 font-semibold text-center">Quantity</th>
                            <th class="px-6 py-3 font-semibold">Reason</th>
                            <th class="px-6 py-3 font-semibold">Status</th>
                            <th class="px-6 py-3 font-semibold">Returned Date</th>
                            <th class="px-6 py-3 font-semibold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php if (empty($returnsList)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-12 text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fa-solid fa-rotate-left text-3xl mb-2 text-slate-300"></i>
                                        <p class="font-medium text-slate-600">No supplier return records found.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($returnsList as $ret): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <?php $img_path = !empty($ret['front_image']) ? $ret['front_image'] : $ret['image_path']; ?>
                                            <img src="<?= !empty($img_path) ? '../../' . htmlspecialchars($img_path) : '../../assets/images/default.png'; ?>" class="w-14 h-14 rounded-xl object-contain border bg-white p-1 shadow-sm flex-shrink-0" onerror="this.src='../../assets/images/default.png'">
                                            <div>
                                                <h4 class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($ret['product_name']); ?></h4>
                                                <p class="text-slate-500 font-mono text-xs"><?= htmlspecialchars($ret['product_code']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-slate-700 whitespace-nowrap"><?= htmlspecialchars($ret['supplier'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 font-mono text-slate-600"><?= htmlspecialchars($ret['return_no']); ?></td>
                                    <td class="px-6 py-4 text-center font-bold text-slate-700"><?= (int)$ret['quantity']; ?> pcs</td>
                                    <td class="px-6 py-4 text-slate-600 italic whitespace-normal max-w-xs"><?= htmlspecialchars($ret['reason']); ?></td>
                                    <td class="px-6 py-4 text-slate-600 whitespace-nowrap"><?= date('M d, Y, g:i A', strtotime($ret['return_date'])); ?></td>
                                    <td class="px-6 py-4">
                                        <?php if ($ret['status'] === 'Returned'): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
                                                <i class="fa-solid fa-check-circle mr-1"></i>
                                                Returned
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">
                                                <i class="fa-solid fa-ban mr-1"></i>
                                                Cancelled
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="return_view.php?return_no=<?= urlencode($ret['return_no']); ?>" class="bg-blue-50 hover:bg-blue-100 text-blue-600 px-2.5 py-1.5 rounded-lg text-xs font-bold transition" title="View Details">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <a href="return_print.php?return_no=<?= urlencode($ret['return_no']); ?>" target="_blank" class="bg-amber-50 hover:bg-amber-100 text-amber-600 px-2.5 py-1.5 rounded-lg text-xs font-bold transition" title="Print Return Slip">
                                                <i class="fa-solid fa-print"></i>
                                            </a>
                                            <button type="button" onclick="confirmDeleteReturn(<?= $ret['id']; ?>, 'Return Slip <?= htmlspecialchars($ret['return_no']); ?>')" class="bg-red-50 hover:bg-red-100 text-red-600 px-2.5 py-1.5 rounded-lg text-xs font-bold transition" title="Delete">
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
<div class="modal fade" id="deleteReturnAuthModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-xl overflow-hidden bg-white">
            <div class="modal-header bg-red-600 text-white px-6 py-4">
                <h5 class="modal-title font-bold flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> Super Admin Delete Authorization
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteReturnAuthForm">
                <div class="modal-body p-6 space-y-4">
                    <input type="hidden" id="delete_return_id">
                    
                    <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-xl text-xs">
                        <span class="font-bold">Warning:</span> You are about to delete <span id="delete_return_name_label" class="font-semibold underline"></span>. This action is irreversible and requires Super Admin privileges.
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
                        <textarea id="delete_auth_reason" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required placeholder="e.g., Duplicate entry / incorrect log"></textarea>
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
    function confirmDeleteReturn(returnId, returnName) {
        document.getElementById('delete_return_id').value = returnId;
        document.getElementById('delete_return_name_label').innerText = returnName;
        
        document.getElementById('delete_auth_username').value = '';
        document.getElementById('delete_auth_password').value = '';
        document.getElementById('delete_auth_reason').value = '';

        var deleteModal = new bootstrap.Modal(document.getElementById('deleteReturnAuthModal'));
        deleteModal.show();
    }

    // Handle AJAX Super Admin Delete Verification for Returns
    document.getElementById("deleteReturnAuthForm").addEventListener("submit", function(e) {
        e.preventDefault();

        let returnId = document.getElementById("delete_return_id").value;
        let username = document.getElementById("delete_auth_username").value;
        let password = document.getElementById("delete_auth_password").value;
        let reason = document.getElementById("delete_auth_reason").value;

        fetch("../../includes/verify_delete_auth.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                item_id: returnId,
                username: username,
                password: password,
                reason: reason
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById("deleteReturnAuthModal")).hide();
                window.location.href = `returns.php?action_type=delete&id=${returnId}`;
            } else {
                alert("Authorization Failed: " + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert("An error occurred during verification.");
        });
    });
</script>

</body>
</html>