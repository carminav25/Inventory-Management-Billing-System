<?php
$pageTitle = "Deliveries/In Dashboard";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";

requireAdmin();

// --- HANDLE GET ACTIONS (DELETE) ---
if (isset($_GET['action_type']) && $_GET['action_type'] === 'delete') {
    $delivery_id = (int)($_GET['id'] ?? 0);

    if ($delivery_id > 0) {
        // Verify that the deletion was authorized by a Super Admin in the last 5 minutes
        if (isset($_SESSION['delete_authorized']) && $_SESSION['delete_authorized'] === true && (time() - $_SESSION['delete_auth_time'] < 300)) {
            
            mysqli_begin_transaction($conn);

            try {
                // Get items from the delivery to deduct them from stock
                $itemsQuery = mysqli_query($conn, "SELECT product_id, quantity FROM delivery_items WHERE delivery_id = $delivery_id");
                if (!$itemsQuery) throw new Exception("Could not retrieve delivery items for stock deduction.");

                while ($item = mysqli_fetch_assoc($itemsQuery)) {
                    // Deduct product from stock
                    $updateStockQuery = "UPDATE products SET current_stock = current_stock - {$item['quantity']} WHERE id = {$item['product_id']}";
                    if (!mysqli_query($conn, $updateStockQuery)) {
                        throw new Exception("Failed to deduct product ID {$item['product_id']} from stock.");
                    }
                }

                // Delete the delivery record itself
                if (!mysqli_query($conn, "DELETE FROM deliveries WHERE id = $delivery_id")) {
                    throw new Exception("Failed to delete the main delivery record.");
                }
                // Note: delivery_items will be deleted automatically due to ON DELETE CASCADE constraint.

                mysqli_commit($conn);
                
                logActivity($conn, $_SESSION['user_id'], $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Deleted Delivery Record #{$delivery_id}. Reason: " . ($_SESSION['delete_auth_reason'] ?? 'N/A'));
                $_SESSION['success_message'] = "Delivery record deleted and items have been deducted from stock successfully.";

            } catch (Exception $e) {
                mysqli_rollback($conn);
                $_SESSION['error_message'] = "Error during deletion: " . $e->getMessage();
            }

            unset($_SESSION['delete_authorized'], $_SESSION['delete_auth_time'], $_SESSION['delete_auth_reason']);
        } else {
            $_SESSION['error_message'] = "Unauthorized deletion attempt. Please re-authenticate.";
        }
    }
    header("Location: inventory_indeliveries.php");
    exit();
}

// --- 1. DASHBOARD STATISTICS METRICS ---
$totalDeliveries = 0;
$todayDeliveries = 0;
$totalItemsReceived = 0;
$totalInventoryValue = 0.00;

// Total Deliveries
$resTotal = mysqli_query($conn, "SELECT COUNT(*) AS total FROM deliveries");
if ($resTotal && $row = mysqli_fetch_assoc($resTotal)) {
    $totalDeliveries = (int)$row['total'];
}

// Today's Deliveries
$resToday = mysqli_query($conn, "SELECT COUNT(*) AS total FROM deliveries WHERE DATE(delivery_date) = CURDATE()");
if ($resToday && $row = mysqli_fetch_assoc($resToday)) {
    $todayDeliveries = (int)$row['total'];
}

// Total Items Received
$resItems = mysqli_query($conn, "SELECT COALESCE(SUM(quantity), 0) AS total FROM delivery_items");
if ($resItems && $row = mysqli_fetch_assoc($resItems)) {
    $totalItemsReceived = (int)$row['total'];
}

// Total Inventory Value Received
$resValue = mysqli_query($conn, "SELECT COALESCE(SUM(quantity * cost_price), 0) AS inventory_value FROM delivery_items");
if ($resValue && $row = mysqli_fetch_assoc($resValue)) {
    $totalInventoryValue = (float)$row['inventory_value'];
}

// --- 2. SEARCH & RECENT DELIVERIES ---
$search = trim($_GET['search'] ?? '');
$searchQuery = "";

if (!empty($search)) {
    $safeSearch = mysqli_real_escape_string($conn, $search);
    $searchQuery = " WHERE d.delivery_no LIKE '%$safeSearch%' OR s.supplier_name LIKE '%$safeSearch%' OR d.dr_number LIKE '%$safeSearch%' ";
}

// --- 3. PAGINATION SETUP ---
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limit = 5; // Records per page
$offset = ($page - 1) * $limit;

// Count total records for pagination
$countQuery = "SELECT COUNT(DISTINCT d.id) as total FROM deliveries d LEFT JOIN suppliers s ON d.supplier_id = s.id $searchQuery";
$countResult = mysqli_query($conn, $countQuery);
$totalRecords = mysqli_fetch_assoc($countResult)['total'] ?? 0;
$totalPages = ceil($totalRecords / $limit);
if ($totalPages < 1) $totalPages = 1;

$deliveries = [];
$query = "
    SELECT
        d.id,
        d.delivery_no,
        d.delivery_date,
        d.status,
        s.supplier_name,
        COALESCE(SUM(di.quantity),0) AS total_items,
        COALESCE(SUM(di.quantity * di.cost_price),0) AS total_value,
        MIN(p.front_image) AS product_image,
        MIN(p.product_name) AS product_name
    FROM deliveries d
    LEFT JOIN suppliers s ON d.supplier_id = s.id
    LEFT JOIN delivery_items di ON d.id = di.delivery_id
    LEFT JOIN products p ON di.product_id = p.id
    $searchQuery
    GROUP BY d.id
    ORDER BY d.delivery_date DESC, d.id DESC
    LIMIT $limit OFFSET $offset
";

$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $deliveries[] = $row;
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
    <!-- Bootstrap CSS for modal behavior -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome (kept for compatibility with existing code) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        a { text-decoration: none !important; }

        html, body {
            overflow-x: hidden;
        }

        main {
            box-sizing: border-box;
        }

        .modal {
            z-index: 1060 !important;
        }

        .modal-backdrop {
            z-index: 1050 !important;
        }

        .delivery-stat-card {
            min-height: 116px;
        }

        @media (max-width: 767px) {
            main {
                margin-left: 0 !important;
                padding: 1rem !important;
            }
        }
    </style>
</head>

<body class="bg-slate-50 font-sans text-slate-800">

<?php include "sidebar.php"; ?> 

<main class="ml-0 md:ml-[270px] min-h-screen bg-[#f5f7fb] px-4 py-2 md:px-5 md:py-3 transition-all duration-300">

    <div class="space-y-6 max-w-7xl mx-auto">
        
        <!-- NOTIFICATIONS -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm">
                <span><?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></span>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
                <span><?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></span>
            </div>
        <?php endif; ?>

        <!-- HEADER -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Delivery Management</h1>
                <p class="text-sm text-slate-500 mt-0.5">Record and manage incoming deliveries from suppliers.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="delivery_form.php" class="bg-emerald-700 hover:bg-emerald-800 text-white px-5 py-2.5 rounded-xl font-semibold text-sm shadow transition"> New Delivery
                </a>
            </div>
        </div>

        <!-- KPI CARDS - PRODUCT STYLE -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            <!-- Total Deliveries -->
            <div class="delivery-stat-card bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">
                        Total Deliveries
                    </p>
                    <h2 class="text-3xl font-extrabold text-slate-800 mt-1">
                        <?= number_format($totalDeliveries); ?>
                    </h2>
                </div>
            </div>

            <!-- Today's Deliveries -->
            <div class="delivery-stat-card bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">
                        Today's Deliveries
                    </p>
                    <h2 class="text-3xl font-extrabold text-slate-800 mt-1">
                        <?= number_format($todayDeliveries); ?>
                    </h2>
                </div>
            </div>

            <!-- Total Items Received -->
            <div class="delivery-stat-card bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">
                        Total Items Received
                    </p>
                    <h2 class="text-3xl font-extrabold text-slate-800 mt-1">
                        <?= number_format($totalItemsReceived); ?>
                    </h2>
                </div>
            </div>

            <!-- Total Inventory Value -->
            <div class="delivery-stat-card bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">
                        Total Inventory Value
                    </p>
                    <h2 class="text-3xl font-extrabold text-emerald-700 mt-1">
                        ₱<?= number_format($totalInventoryValue, 2); ?>
                    </h2>
                </div>
            </div>

        </div>

        <!-- RECENT DELIVERIES TABLE -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <form method="GET" action="" class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <h2 class="text-base font-bold text-slate-800 whitespace-nowrap">Recent Deliveries</h2>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <div class="relative flex-1 sm:w-72">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 text-sm"></span>
                            <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Search delivery no, supplier..." class="w-full pl-4 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        </div>
                        <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition shadow-sm"><i class="fa-solid fa-filter"></i></button>
                        <?php if(!empty($search)): ?><a href="inventory_indeliveries.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2.5 rounded-xl text-sm font-medium transition" title="Reset Filters"><i class="fa-solid fa-arrows-rotate"></i></a><?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 font-semibold">Product / Delivery No.</th>
                            <th class="px-6 py-3 font-semibold">Supplier</th>
                            <th class="px-6 py-3 font-semibold">Date</th>
                            <th class="px-6 py-3 font-semibold text-center">Items</th>
                            <th class="px-6 py-3 font-semibold text-right">Total Value</th>
                            <th class="px-6 py-3 font-semibold text-center">Status</th>
                            <th class="px-6 py-3 font-semibold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php if (empty($deliveries)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-12 text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <p class="font-medium text-slate-600">No delivery records found.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($deliveries as $del): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <?php if(!empty($del['product_image'])): ?>
                                                <img src="../../<?= htmlspecialchars($del['product_image']); ?>" class="w-14 h-14 rounded-xl object-cover border">
                                            <?php else: ?>
                                                <div class="w-14 h-14 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400"></div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="font-bold text-slate-800">
                                                    <?= htmlspecialchars($del['product_name'] ?? 'N/A'); ?>
                                                </div>
                                                <div class="text-xs text-slate-500 font-mono"><?= htmlspecialchars($del['delivery_no']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-slate-800"><?= htmlspecialchars($del['supplier_name'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 text-slate-600"><?= date('M d, Y, g:i A', strtotime($del['delivery_date'])); ?></td>
                                    <td class="px-6 py-4 text-center font-bold text-slate-700"><?= (int)$del['total_items']; ?> pcs</td>
                                    <td class="px-6 py-4 text-right font-bold text-emerald-700">₱<?= number_format((float)$del['total_value'], 2); ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold"> Completed
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="delivery_view.php?id=<?= $del['id']; ?>" class="bg-blue-50 hover:bg-blue-100 text-blue-600 p-2 rounded-lg text-xs font-bold transition" title="View Details">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <button type="button" onclick="window.open('delivery_print.php?id=<?= $del['id']; ?>', '_blank')" class="bg-slate-100 hover:bg-slate-200 text-slate-600 p-2 rounded-lg text-xs font-bold transition" title="Print Receipt">
                                                <i class="fa-solid fa-print"></i>
                                            </button>
                                            <button type="button" onclick="confirmDeleteDelivery(<?= $del['id']; ?>, 'Delivery #<?= htmlspecialchars($del['delivery_no']); ?>')" class="bg-red-50 hover:bg-red-100 text-red-600 p-2 rounded-lg text-xs font-bold transition" title="Delete Delivery">
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
                    <a href="?<?= http_build_query($queryParams); ?>" class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50 shadow-sm transition" title="Previous">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): $queryParams['p'] = $i; ?>
                    <a href="?<?= http_build_query($queryParams); ?>" class="px-3.5 py-2 border rounded-xl text-sm font-medium shadow-sm transition <?= ($i == $page) ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'; ?>"><?= $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): $queryParams['p'] = $page + 1; ?>
                    <a href="?<?= http_build_query($queryParams); ?>" class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50 shadow-sm transition" title="Next">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>


</main>

<!-- SUPER ADMIN DELETE AUTHENTICATION MODAL -->
<div class="modal fade" id="deleteDeliveryAuthModal" style="display:none;" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-xl overflow-hidden">
            <div class="modal-header bg-red-600 text-white px-6 py-4">
                <h5 class="modal-title font-bold flex items-center gap-2"> Super Admin Delete Authorization
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteDeliveryAuthForm">
                <div class="modal-body p-6 space-y-4">
                    <input type="hidden" id="delete_delivery_id">
                    
                    <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-xl text-xs">
                        <p><span class="font-bold">Warning:</span> You are about to delete <span id="delete_delivery_name_label" class="font-semibold underline"></span>. This action is irreversible and requires Super Admin privileges.</p>
                        <p class="mt-1"><span class="font-bold">Important:</span> All items from this delivery will be automatically deducted from stock.</p>
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
                        <textarea id="delete_auth_reason" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required placeholder="e.g., Duplicate entry / incorrect delivery log"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-slate-50 border-t border-slate-100 px-6 py-3 flex justify-end gap-2">
                    <button type="button" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-medium transition shadow-sm flex items-center gap-2"> Verify & Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("deleteDeliveryAuthModal");
    if (modal && !modal.classList.contains("show")) modal.style.display = "none";
});
</script>
<script>
    function confirmDeleteDelivery(deliveryId, deliveryName) {
        document.getElementById('delete_delivery_id').value = deliveryId;
        document.getElementById('delete_delivery_name_label').innerText = deliveryName;
        
        // Reset form inputs
        document.getElementById('delete_auth_username').value = '';
        document.getElementById('delete_auth_password').value = '';
        document.getElementById('delete_auth_reason').value = '';

        var deleteModal = new bootstrap.Modal(document.getElementById('deleteDeliveryAuthModal'));
        deleteModal.show();
    }

    document.getElementById("deleteDeliveryAuthForm").addEventListener("submit", function(e) {
        e.preventDefault();
        let deliveryId = document.getElementById("delete_delivery_id").value;
        fetch("../../includes/verify_delete_auth.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                item_id: deliveryId,
                username: document.getElementById("delete_auth_username").value,
                password: document.getElementById("delete_auth_password").value,
                reason: document.getElementById("delete_auth_reason").value
            })
        }).then(res => res.json()).then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById("deleteDeliveryAuthModal")).hide();
                window.location.href = `inventory_indeliveries.php?action_type=delete&id=${deliveryId}`;
            } else {
                alert("Authorization Failed: " + data.message);
            }
        }).catch(err => alert("An error occurred during verification."));
    });
</script>
</body>
</html>