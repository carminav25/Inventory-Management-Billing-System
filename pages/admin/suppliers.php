<?php
$pageTitle = "Supplier Management";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";
require_once "../../includes/activity_log.php";

requireAdmin();

// --- HANDLE GET ACTIONS (DELETE) ---
if (isset($_GET['action_type']) && $_GET['action_type'] === 'delete') {
    $supplier_id = (int)($_GET['id'] ?? 0);

    if ($supplier_id > 0) {
        // Verify that the deletion was authorized by a Super Admin in the last 5 minutes
        if (isset($_SESSION['delete_authorized']) && $_SESSION['delete_authorized'] === true && (time() - $_SESSION['delete_auth_time'] < 300)) {
            
            // Before deleting, check if the supplier is linked to any products.
            $supplierNameQuery = mysqli_query($conn, "SELECT supplier_name FROM suppliers WHERE id = $supplier_id");
            $supplierData = mysqli_fetch_assoc($supplierNameQuery);
            $supplierName = $supplierData['supplier_name'] ?? '';

            $checkQuery = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM products WHERE supplier = ?");
            mysqli_stmt_bind_param($checkQuery, "s", $supplierName);
            mysqli_stmt_execute($checkQuery);
            $checkResult = mysqli_fetch_assoc(mysqli_stmt_get_result($checkQuery));

            if ($checkResult['count'] > 0) {
                $_SESSION['error_message'] = "Cannot delete supplier. It is linked to {$checkResult['count']} product(s). Please reassign products before deleting.";
            } else {
                $delQuery = mysqli_query($conn, "DELETE FROM suppliers WHERE id = $supplier_id");
                if ($delQuery) {
                    logActivity($conn, $_SESSION['user_id'], $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Deleted Supplier #{$supplier_id} ({$supplierName}). Reason: " . ($_SESSION['delete_auth_reason'] ?? 'N/A'));
                    $_SESSION['success_message'] = "Supplier record deleted successfully.";
                } else {
                    $_SESSION['error_message'] = "Failed to delete supplier record.";
                }
            }
            unset($_SESSION['delete_authorized'], $_SESSION['delete_auth_time'], $_SESSION['delete_auth_reason']);
        } else {
            $_SESSION['error_message'] = "Unauthorized deletion attempt. Please re-authenticate.";
        }
    }
    header("Location: suppliers.php");
    exit();
}

// --- 1. HANDLE POST ACTIONS (ADD / EDIT) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_type = $_POST['action_type'] ?? '';

    if ($action_type === 'add') {
        $supplier_name  = trim($_POST['supplier_name'] ?? '');
        $contact_person = trim($_POST['contact_person'] ?? '');
        $contact_number = trim($_POST['contact_number'] ?? ''); // Keep this for re-population on error
        $email          = trim($_POST['email'] ?? '');
        $address        = trim($_POST['address'] ?? '');
        $status         = trim($_POST['status'] ?? 'Active');
        $user_id        = $_SESSION['user_id'] ?? 1;

        // Server-side validation for contact number
        $isValidContact = empty($contact_number) || preg_match('/^(09\d{9}|\+639\d{9})$/', $contact_number);

        // Server-side validation for supplier email
        // Blank email is allowed, but entered email must use an approved provider.
        $isValidEmail = empty($email) || preg_match(
            '/^[A-Za-z0-9._%+-]+@(gmail\.com|yahoo\.com|outlook\.com|hotmail\.com|live\.com|icloud\.com|proton\.me|protonmail\.com|isu\.edu\.ph)$/i',
            $email
        );

        if (!$isValidContact) {
            $_SESSION['error_message'] = "Invalid contact number format. Use 09xxxxxxxxx or +639xxxxxxxxxx.";
            header("Location: suppliers.php");
            exit();
        } elseif (!$isValidEmail) {
            $_SESSION['error_message'] = "Enter a valid email address (Gmail, Yahoo, Outlook, Hotmail, Live, iCloud, Proton, or ISU).";
            header("Location: suppliers.php");
            exit();
        } elseif (empty($supplier_name)) {
            $_SESSION['error_message'] = "Supplier name is required.";
            header("Location: suppliers.php");
            exit();
        }

        // Check for duplicate supplier name before inserting
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM suppliers WHERE supplier_name = ?");
        mysqli_stmt_bind_param($checkStmt, "s", $supplier_name);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);
        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            $_SESSION['error_message'] = "Supplier '{$supplier_name}' already exists.";
            header("Location: suppliers.php");
            exit();
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO suppliers (supplier_name, contact_person, contact_number, email, address, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, "ssssss", $supplier_name, $contact_person, $contact_number, $email, $address, $status);
        
        if (mysqli_stmt_execute($stmt)) {
            logActivity($conn, $user_id, $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Added new supplier: {$supplier_name}");
            $_SESSION['success_message'] = "Supplier successfully added!";
        } else {
            $_SESSION['error_message'] = "Database error: " . mysqli_error($conn);
        }
        header("Location: suppliers.php");
        exit();
    }

    if ($action_type === 'edit') {
        $supplier_id    = (int)($_POST['supplier_id'] ?? 0);
        $supplier_name  = trim($_POST['supplier_name'] ?? '');
        $contact_person = trim($_POST['contact_person'] ?? ''); 
        $contact_number = trim($_POST['contact_number'] ?? ''); // Keep for re-population
        $email          = trim($_POST['email'] ?? '');
        $address        = trim($_POST['address'] ?? '');
        $status         = trim($_POST['status'] ?? 'Active');
        $user_id        = $_SESSION['user_id'] ?? 1;

        // Server-side validation for contact number
        $isValidContact = empty($contact_number) || preg_match('/^(09\d{9}|\+639\d{9})$/', $contact_number);

        // Server-side validation for supplier email
        // Blank email is allowed, but entered email must use an approved provider.
        $isValidEmail = empty($email) || preg_match(
            '/^[A-Za-z0-9._%+-]+@(gmail\.com|yahoo\.com|outlook\.com|hotmail\.com|live\.com|icloud\.com|proton\.me|protonmail\.com|isu\.edu\.ph)$/i',
            $email
        );

        if (!$isValidContact) {
            $_SESSION['error_message'] = "Invalid contact number format. Use 09xxxxxxxxx or +639xxxxxxxxxx.";
            header("Location: suppliers.php");
            exit();
        } elseif (!$isValidEmail) {
            $_SESSION['error_message'] = "Enter a valid email address (Gmail, Yahoo, Outlook, Hotmail, Live, iCloud, Proton, or ISU).";
            header("Location: suppliers.php");
            exit();
        } elseif ($supplier_id <= 0 || empty($supplier_name)) {
            $_SESSION['error_message'] = "Invalid supplier details.";
            header("Location: suppliers.php");
            exit();
        }

        $stmt = mysqli_prepare($conn, "UPDATE suppliers SET supplier_name = ?, contact_person = ?, contact_number = ?, email = ?, address = ?, status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssssssi", $supplier_name, $contact_person, $contact_number, $email, $address, $status, $supplier_id);
        
        if (mysqli_stmt_execute($stmt)) {
            logActivity($conn, $user_id, $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Updated supplier details for: {$supplier_name}");
            $_SESSION['success_message'] = "Supplier successfully updated!";
        } else {
            $_SESSION['error_message'] = "Database error: " . mysqli_error($conn);
        }
        header("Location: suppliers.php");
        exit();
    }
}

// --- 2. METRICS & STATISTICS ---
$totalSuppliers = 0;
$activeSuppliersCount = 0;
$inactiveSuppliersCount = 0;

$resStats = mysqli_query($conn, "
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) AS active_count,
        SUM(CASE WHEN status = 'Inactive' THEN 1 ELSE 0 END) AS inactive_count
    FROM suppliers
");
if ($resStats && $row = mysqli_fetch_assoc($resStats)) {
    $totalSuppliers = (int)$row['total'];
    $activeSuppliersCount = (int)$row['active_count'];
    $inactiveSuppliersCount = (int)$row['inactive_count'];
}

// --- 3. SEARCH & FETCH SUPPLIERS ---
$search = trim($_GET['search'] ?? '');
$contactFilter = trim($_GET['contact'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$whereClauses = [];

if (!empty($search)) {
    $safeSearch = mysqli_real_escape_string($conn, $search);
    $whereClauses[] = "(supplier_name LIKE '%$safeSearch%' OR contact_person LIKE '%$safeSearch%' OR email LIKE '%$safeSearch%' OR contact_number LIKE '%$safeSearch%')";
}

if (!empty($contactFilter)) {
    $safeContact = mysqli_real_escape_string($conn, $contactFilter);
    $whereClauses[] = "contact_person = '$safeContact'";
}

if (!empty($statusFilter)) {
    $safeStatus = mysqli_real_escape_string($conn, $statusFilter);
    $whereClauses[] = "status = '$safeStatus'";
}

$searchQuery = "";
if (!empty($whereClauses)) {
    $searchQuery = " WHERE " . implode(" AND ", $whereClauses);
}

// --- 4. PAGINATION SETUP ---
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$limit = 5; // Suppliers per page

$countQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM suppliers $searchQuery");
$totalRecords = 0;
if ($countQuery && $countRow = mysqli_fetch_assoc($countQuery)) {
    $totalRecords = (int)$countRow['total'];
}

$totalPages = max(1, (int)ceil($totalRecords / $limit));
$page = min($page, $totalPages);
$offset = ($page - 1) * $limit;

$paginationParams = $_GET;
$paginationParams['p'] = $page;

$suppliers = [];
$query = "SELECT * FROM suppliers $searchQuery ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $suppliers[] = $row;
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
    <!-- Bootstrap CSS for Modal Compatibility -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        a { text-decoration: none !important; }
        .modal-backdrop { z-index: 1040 !important; }
        .modal { z-index: 1050 !important; }
            /* Supplier Email Validation */
        .supplier-email-wrap { position: relative; }

        .supplier-email-field.email-invalid {
            border-color: #ef3340 !important;
            box-shadow: 0 0 0 1px rgba(239, 51, 64, 0.08);
        }

        .supplier-email-field.email-valid {
            border-color: #10b981 !important;
        }

        .supplier-email-error-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #ef3340;
            display: none;
            pointer-events: none;
        }

        .supplier-email-error-icon.show { display: block; }

        .supplier-email-error-message {
            display: none;
            margin-top: 6px;
            color: #ef3340;
            font-size: 12px;
            line-height: 1.35;
            align-items: flex-start;
            gap: 5px;
        }

        .supplier-email-error-message.show { display: flex; }
        .supplier-email-error-message i { margin-top: 1px; }
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

        <!-- HEADER -->

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Supplier Management</h1>
                <p class="text-sm text-slate-500 mt-0.5">Manage partner vendors, contact persons, and delivery sources.</p>
            </div> 
        </div>

        <!-- KPI CARDS -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">

    <!-- Total Suppliers -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-xs font-semibold text-slate-400 uppercase">
            Total Suppliers
        </p>

        <h2 class="text-3xl font-extrabold text-slate-800 mt-1">
            <?= number_format($totalSuppliers); ?>
        </h2>
    </div>

    <!-- Active Suppliers -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-xs font-semibold text-slate-400 uppercase">
            Active Suppliers
        </p>

        <h2 class="text-3xl font-extrabold text-slate-800 mt-1">
            <?= number_format($activeSuppliersCount); ?>
        </h2>
    </div>

    <!-- Inactive Suppliers -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-xs font-semibold text-slate-400 uppercase">
            Inactive Suppliers
        </p>

        <h2 class="text-3xl font-extrabold text-red-600 mt-1">
            <?= number_format($inactiveSuppliersCount); ?>
        </h2>
    </div>

</div>

        <!-- ======================= SUPPLIER LIST CARD ======================= -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 mb-8">

            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">
                        Supplier List
                    </h2>
                    <p class="text-slate-500 text-sm mt-1">
                        Search, filter and manage supplier records.
                    </p>
                </div>

                <button
                    type="button"
                    onclick="openAddSupplierModal()"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2.5 rounded-xl font-semibold transition">
                    <i class="fa-solid fa-plus mr-2"></i>
                    Add Supplier
                </button>
            </div>

            <!-- Filters -->
            <form method="GET" action=""
                  class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 flex-1">

                    <!-- Search -->
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input
                            type="text"
                            name="search"
                            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                            placeholder="Search supplier..."
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm
                            focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    

                    <!-- Contact Person -->
                    <select
                        name="contact"
                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm
                        focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Contact Persons</option>
                        <?php
                        $contacts = mysqli_query($conn,"
                            SELECT DISTINCT contact_person
                            FROM suppliers
                            WHERE contact_person <> ''
                            ORDER BY contact_person
                        ");
                        while($row = mysqli_fetch_assoc($contacts)):
                        ?>
                            <option
                                value="<?= htmlspecialchars($row['contact_person']); ?>"
                                <?= (($_GET['contact'] ?? '') == $row['contact_person']) ? 'selected' : '';?>>
                                <?= htmlspecialchars($row['contact_person']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <!-- Supplier Status -->
                    <select
                        name="status"
                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm
                        focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Suppliers</option>
                        <option value="Active" <?= (($_GET['status'] ?? '') == 'Active') ? 'selected' : '';?>>
                            Active Suppliers
                        </option>
                        <option value="Inactive" <?= (($_GET['status'] ?? '') == 'Inactive') ? 'selected' : '';?>>
                            Inactive Suppliers
                        </option>
                    </select>

                    <!-- Filter Button -->
                    <div class="flex items-center gap-2">
                        <button
                            type="submit"
                            class="flex-1 bg-slate-800 hover:bg-slate-900 text-white px-5 py-2.5 rounded-xl
                            text-sm font-medium transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-filter"></i>
                            Filter
                        </button>
                        
                        <?php if (!empty($_GET['search']) || !empty($_GET['contact']) || !empty($_GET['status'])): ?>
                            <a href="suppliers.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-3.5 py-2.5 rounded-xl text-sm font-medium transition flex items-center justify-center" title="Reset Filters">
                                <i class="fa-solid fa-arrows-rotate"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                </div>

            </form>
        </div>

        <!-- SUPPLIERS TABLE -->
        <!-- ======================= TABLE ======================= -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 font-semibold">Supplier Name</th>
                            <th class="px-6 py-3 font-semibold">Contact Person</th>
                            <th class="px-6 py-3 font-semibold">Contact Number</th>
                            <th class="px-6 py-3 font-semibold">Email</th>
                            <th class="px-6 py-3 font-semibold">Address</th>
                            <th class="px-6 py-3.5 text-center font-semibold">Supplier Status</th>
                            <th class="px-6 py-3 font-semibold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php if (empty($suppliers)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-12 text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fa-solid fa-truck-field text-3xl mb-2 text-slate-300"></i>
                                        <p class="font-medium text-slate-600">No suppliers found.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($suppliers as $sup): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 font-bold text-slate-800"><?= htmlspecialchars($sup['supplier_name']); ?></td>
                                    <td class="px-6 py-4 text-slate-700"><?= htmlspecialchars($sup['contact_person'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 font-mono text-slate-600"><?= htmlspecialchars($sup['contact_number'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($sup['email'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($sup['address'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if ($sup['status'] === 'Active'): ?>
                                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">Active</span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200 text-xs font-bold">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" onclick="openEditModal(<?= htmlspecialchars(json_encode($sup)); ?>)" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 rounded-lg transition text-sm" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button type="button" onclick="confirmDeleteSupplier(<?= $sup['id']; ?>, '<?= htmlspecialchars($sup['supplier_name'], ENT_QUOTES); ?>')" class="p-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition text-sm" title="Delete">
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
        </div>

        <!-- PAGINATION CONTROLS -->
        <?php if ($totalPages > 1): ?>
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 mb-8">
                <div class="text-sm text-slate-500">
                    Showing <strong><?= $totalRecords > 0 ? ($offset + 1) : 0 ?></strong> - <strong><?= min($offset + $limit, $totalRecords) ?></strong> of <strong><?= $totalRecords ?></strong> suppliers
                </div>
                <div class="flex flex-wrap justify-end items-center gap-2">
                    <?php if ($page > 1): ?>
                        <?php $paginationParams['p'] = $page - 1; ?>
                        <a href="?<?= http_build_query($paginationParams); ?>" class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50 shadow-sm transition" aria-label="Previous page">
                            <i class="fa-solid fa-chevron-left text-xs"></i>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php $paginationParams['p'] = $i; ?>
                        <a href="?<?= http_build_query($paginationParams); ?>" class="px-3.5 py-2 border rounded-xl text-sm font-medium shadow-sm transition <?= ($i == $page) ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'; ?>">
                            <?= $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <?php $paginationParams['p'] = $page + 1; ?>
                        <a href="?<?= http_build_query($paginationParams); ?>" class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50 shadow-sm transition" aria-label="Next page">
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        

    </div>

    

</main>

<!-- ADD SUPPLIER MODAL -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-2xl border-0 shadow-xl overflow-hidden">
            <div class="modal-header bg-slate-50 border-b border-slate-100 px-6 py-4">
                <h5 class="modal-title font-bold text-slate-800">Add New Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-6">
                <form action="" method="POST">
                    <input type="hidden" name="action_type" value="add">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Supplier Name <span class="text-red-500">*</span></label>
                            <input type="text" name="supplier_name" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Contact Person</label>
                            <input type="text" name="contact_person" placeholder="e.g., Juan Dela Cruz" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Contact Number</label>
                            <input type="text" name="contact_number" pattern="^(09\d{9}|\+639\d{9})$" title="Format: 09xxxxxxxxx or +639xxxxxxxxxx" placeholder="09xxxxxxxxx" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Email Address</label>
                            <input type="email" name="email" class="supplier-email-field w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" autocomplete="email">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Status</label>
                            <select name="status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Address</label>
                            <textarea name="address" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-medium transition" data-bs-dismiss="modal"><i class="fa-solid fa-xmark mr-2"></i>Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-sm font-medium shadow-sm transition flex items-center gap-2"><i class="fa-solid fa-floppy-disk"></i>Save Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- EDIT SUPPLIER MODAL -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-2xl border-0 shadow-xl overflow-hidden">
            <div class="modal-header bg-slate-50 border-b border-slate-100 px-6 py-4">
                <h5 class="modal-title font-bold text-slate-800">Edit Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-6">
                <form action="" method="POST">
                    <input type="hidden" name="action_type" value="edit">
                    <input type="hidden" name="supplier_id" id="edit_supplier_id">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Supplier Name <span class="text-red-500">*</span></label>
                            <input type="text" name="supplier_name" id="edit_supplier_name" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Contact Person</label>
                            <input type="text" name="contact_person" id="edit_contact_person" placeholder="e.g., Juan Dela Cruz" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Contact Number</label>
                            <input type="text" name="contact_number" id="edit_contact_number" pattern="^(09\d{9}|\+639\d{9})$" title="Format: 09xxxxxxxxx or +639xxxxxxxxxx" placeholder="09xxxxxxxxx" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Email Address</label>
                            <input type="email" name="email" id="edit_email" class="supplier-email-field w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" autocomplete="email">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Status</label>
                            <select name="status" id="edit_status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Address</label>
                            <textarea name="address" id="edit_address" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-medium transition" data-bs-dismiss="modal"><i class="fa-solid fa-xmark mr-2"></i>Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-sm font-medium shadow-sm transition flex items-center gap-2"><i class="fa-solid fa-floppy-disk"></i>Update Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- SUPER ADMIN DELETE AUTHENTICATION MODAL -->
<div class="modal fade" id="deleteSupplierAuthModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-xl overflow-hidden">
            <div class="modal-header bg-red-600 text-white px-6 py-4">
                <h5 class="modal-title font-bold flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> Super Admin Delete Authorization
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteSupplierAuthForm">
                <div class="modal-body p-6 space-y-4">
                    <input type="hidden" id="delete_supplier_id">
                    
                    <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-xl text-xs">
                        <p><span class="font-bold">Warning:</span> You are about to delete the supplier <span id="delete_supplier_name_label" class="font-semibold underline"></span>. This action is irreversible.</p>
                        <p class="mt-1"><span class="font-bold">Note:</span> Deletion will fail if the supplier is linked to any products.</p>
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
                        <textarea id="delete_auth_reason" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required placeholder="e.g., Duplicate entry / No longer a partner"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-slate-50 border-t border-slate-100 px-6 py-3 flex justify-end gap-2">
                    <button type="button" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-medium transition shadow-sm flex items-center gap-2">
                        <i class="fa-solid fa-trash"></i> Verify & Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openAddSupplierModal() {
        new bootstrap.Modal(document.getElementById('addSupplierModal')).show();
    }

    function openEditModal(sup) {
        document.getElementById('edit_supplier_id').value = sup.id;
        document.getElementById('edit_supplier_name').value = sup.supplier_name || '';
        document.getElementById('edit_contact_person').value = sup.contact_person || '';
        document.getElementById('edit_contact_number').value = sup.contact_number || '';
        document.getElementById('edit_email').value = sup.email || '';
        document.getElementById('edit_address').value = sup.address || '';
        document.getElementById('edit_status').value = sup.status || 'Active';

        var editModal = new bootstrap.Modal(document.getElementById('editSupplierModal'));
        editModal.show();
    }

    function confirmDeleteSupplier(supplierId, supplierName) {
        document.getElementById('delete_supplier_id').value = supplierId;
        document.getElementById('delete_supplier_name_label').innerText = supplierName;
        
        document.getElementById('delete_auth_username').value = '';
        document.getElementById('delete_auth_password').value = '';
        document.getElementById('delete_auth_reason').value = '';

        var deleteModal = new bootstrap.Modal(document.getElementById('deleteSupplierAuthModal'));
        deleteModal.show();
    }

    document.getElementById("deleteSupplierAuthForm").addEventListener("submit", function(e) {
        e.preventDefault();
        let supplierId = document.getElementById("delete_supplier_id").value;
        fetch("../../includes/verify_delete_auth.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                item_id: supplierId,
                username: document.getElementById("delete_auth_username").value,
                password: document.getElementById("delete_auth_password").value,
                reason: document.getElementById("delete_auth_reason").value
            })
        }).then(res => res.json()).then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById("deleteSupplierAuthModal")).hide();
                window.location.href = `suppliers.php?action_type=delete&id=${supplierId}`;
            } else {
                alert("Authorization Failed: " + data.message);
            }
        }).catch(err => alert("An error occurred during verification."));
    });
</script>


<script>
(function () {
    const EMAIL_MESSAGE =
        'Enter a valid email address (Gmail, Yahoo, Outlook, Hotmail, Live, iCloud, Proton, or ISU).';

    const EMAIL_REGEX =
        /^[A-Za-z0-9._%+-]+@(gmail\.com|yahoo\.com|outlook\.com|hotmail\.com|live\.com|icloud\.com|proton\.me|protonmail\.com|isu\.edu\.ph)$/i;

    function resetEmailState(input) {
        input.classList.remove('email-invalid', 'email-valid');

        const wrapper = input.closest('.supplier-email-wrap');
        if (!wrapper) return;

        wrapper.querySelector('.supplier-email-error-icon')?.classList.remove('show');
        wrapper.nextElementSibling?.classList.remove('show');
    }

    function validateSupplierEmail(input) {
        const email = input.value.trim();

        if (email === '') {
            resetEmailState(input);
            return true;
        }

        const valid = EMAIL_REGEX.test(email);
        const wrapper = input.closest('.supplier-email-wrap');

        if (valid) {
            input.classList.remove('email-invalid');
            input.classList.add('email-valid');

            wrapper?.querySelector('.supplier-email-error-icon')?.classList.remove('show');
            wrapper?.nextElementSibling?.classList.remove('show');
            return true;
        }

        input.classList.add('email-invalid');
        input.classList.remove('email-valid');

        wrapper?.querySelector('.supplier-email-error-icon')?.classList.add('show');
        wrapper?.nextElementSibling?.classList.add('show');

        return false;
    }

    function setupSupplierEmail(input) {
        if (input.dataset.emailValidationReady === '1') return;
        input.dataset.emailValidationReady = '1';

        const wrapper = document.createElement('div');
        wrapper.className = 'supplier-email-wrap';

        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        const icon = document.createElement('span');
        icon.className = 'supplier-email-error-icon';
        icon.innerHTML = '<i class="fa-solid fa-circle-xmark"></i>';
        wrapper.appendChild(icon);

        const message = document.createElement('div');
        message.className = 'supplier-email-error-message';
        message.innerHTML =
            '<i class="fa-solid fa-circle-exclamation"></i>' +
            '<span>' + EMAIL_MESSAGE + '</span>';

        wrapper.parentNode.insertBefore(message, wrapper.nextSibling);

        input.addEventListener('input', function () {
            validateSupplierEmail(this);
        });

        input.addEventListener('blur', function () {
            validateSupplierEmail(this);
        });

        const form = input.closest('form');

        if (form) {
            form.addEventListener('submit', function (event) {
                if (!validateSupplierEmail(input)) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    input.focus();
                    return false;
                }
            }, true);
        }
    }

    function initializeSupplierEmailValidation() {
        document.querySelectorAll('input.supplier-email-field').forEach(setupSupplierEmail);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeSupplierEmailValidation);
    } else {
        initializeSupplierEmailValidation();
    }
})();
</script>

</body>
</html>
