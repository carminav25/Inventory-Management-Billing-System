<?php
$pageTitle = "Backup & Restore";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";
require_once "../../includes/activity_log.php";
require_once "../../includes/admin_functions.php";

requireAdmin();

// --- HANDLE GET ACTIONS (DELETE) ---
if (isset($_GET['action_type']) && $_GET['action_type'] === 'delete') {
    $backup_file = basename($_GET['file'] ?? ''); // Use basename to prevent directory traversal
    $backupFolder = "../../backups/inventory/";

    if (!empty($backup_file)) {
        // Verify that the deletion was authorized by a Super Admin in the last 5 minutes
        if (isset($_SESSION['delete_authorized']) && $_SESSION['delete_authorized'] === true && (time() - $_SESSION['delete_auth_time'] < 300)) {
            
            $filePath = $backupFolder . $backup_file;

            if (file_exists($filePath) && strpos(realpath($filePath), realpath($backupFolder)) === 0) {
                if (unlink($filePath)) {
                    logActivity($conn, $_SESSION['user_id'], $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Deleted Backup File: {$backup_file}. Reason: " . ($_SESSION['delete_auth_reason'] ?? 'N/A'));
                    $_SESSION['success_message'] = "Backup file '{$backup_file}' deleted successfully.";
                } else {
                    $_SESSION['error_message'] = "Failed to delete backup file '{$backup_file}'. Check file permissions.";
                }
            } else {
                $_SESSION['error_message'] = "Backup file not found or invalid path.";
            }
            
            unset($_SESSION['delete_authorized'], $_SESSION['delete_auth_time'], $_SESSION['delete_auth_reason']);
        } else {
            $_SESSION['error_message'] = "Unauthorized deletion attempt. Please re-authenticate.";
        }
    }
    header("Location: backup_restore.php");
    exit();
}

$backupFolder = "../../backups/inventory/";

if (!is_dir($backupFolder)) {
    mkdir($backupFolder, 0777, true);
}

$backups = glob($backupFolder . "*.sql");

$totalBackups = count($backups);

$lastBackupDate = "No Backup";

if ($totalBackups > 0) {
    usort($backups, function ($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    $lastBackupDate = date("M d, Y h:i A", filemtime($backups[0]));
}

/* Database Size */
$dbSize = "0 MB";

$sql = "
SELECT ROUND(SUM(data_length + index_length)/1024/1024,2) AS size
FROM information_schema.tables
WHERE table_schema='{$dbName}'
";

$result = mysqli_query($conn, $sql);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $dbSize = ($row['size'] ?? 0) . " MB";
}

$backupStatus = [
    "text"  => ($totalBackups > 0) ? "Healthy" : "No Backup",
    "class" => ($totalBackups > 0)
        ? "border-green-500"
        : "border-red-500"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin Backup & Restore'); ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        a { text-decoration: none !important; }
    </style>
</head>

<body class="bg-[#f5f7fb] font-sans">

<?php include "sidebar.php"; ?> 

<main class="ml-0 md:ml-[270px] min-h-screen bg-[#f5f7fb] p-6 transition-all duration-300">

<div class="max-w-7xl mx-auto">
    <!-- NOTIFICATIONS -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm flex items-center justify-between mb-4">
            <span><i class="fa-solid fa-circle-check mr-2"></i> <?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></span>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm flex items-center justify-between mb-4">
            <span><i class="fa-solid fa-circle-exclamation mr-2"></i> <?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></span>
        </div>
    <?php endif; ?>
</div>

<div class="space-y-6">
    <!-- STATS CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 border-l-4 border-l-blue-500">
            <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Last Backup</p>
            <h2 class="text-2xl font-bold text-slate-800 mt-1"><?= $lastBackupDate ?></h2>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 border-l-4 border-l-purple-500">
            <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Total Backups</p>
            <h2 class="text-2xl font-bold text-slate-800 mt-1"><?= $totalBackups ?></h2>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 border-l-4 border-l-emerald-500">
            <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Database Size</p>
            <h2 class="text-2xl font-bold text-slate-800 mt-1"><?= $dbSize ?></h2>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 border-l-4 <?= $backupStatus['class'] ?>">
            <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Backup Status</p>
            <h2 class="text-2xl font-bold text-slate-800 mt-1"><?= $backupStatus['text'] ?></h2>
        </div>
    </div>

    <!-- BACKUP ACTIONS -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Create New Backup</h3>
        <form action="../../process/admin/create_inventory_backup.php" method="POST">
            <button
                type="submit"
                class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-xl shadow transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Create Inventory Backup
            </button>
        </form>
    </div>

    <!-- EXISTING BACKUPS TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-800">Existing Backups</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] text-slate-400 uppercase tracking-wider">
                        <th class="p-4">Backup Name</th>
                        <th class="p-4">Type</th>
                        <th class="p-4">Size</th>
                        <th class="p-4">Date</th>
                        <th class="p-4">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    <?php if (!empty($backups)): ?>
                        <?php foreach ($backups as $backup):
                            $file = basename($backup);
                            $size = round(filesize($backup) / 1024, 2) . " KB";
                            $date = date("M d, Y", filemtime($backup));
                        ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-4 font-mono text-blue-600"><?= htmlspecialchars($file); ?></td>
                                <td class="p-4 text-slate-600">Full</td>
                                <td class="p-4 text-slate-600"><?= htmlspecialchars($size); ?></td>
                                <td class="p-4 text-slate-600"><?= htmlspecialchars($date); ?></td>
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <a href="../../backups/inventory/<?= urlencode($file); ?>" class="text-blue-600 hover:text-blue-800" title="Download" download>
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                        <button type="button" onclick="confirmDeleteBackup('<?= htmlspecialchars($file, ENT_QUOTES); ?>')" class="text-red-600 hover:text-red-800" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400">No backups found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>    

    <!-- RESTORE INVENTORY -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-7">

        <h3 class="text-xl font-bold text-slate-800 mb-6">
            Restore Inventory Backup
        </h3>

        <div class="bg-amber-50 border-l-4 border-amber-500 p-5 rounded-xl mb-6">
            <h4 class="font-bold text-amber-700">
                Warning!
            </h4>
            <p class="text-amber-700 text-sm mt-1">
                Restoring this backup will overwrite the current inventory records only. 
                User accounts, Super Admin settings, activity logs, and system configuration will NOT be affected.
            </p>
        </div>

        <form
            action="../../process/admin/restore_inventory_backup.php"
            method="POST"
            enctype="multipart/form-data">

            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">
                Select SQL Backup File
            </label>

            <input
                type="file"
                name="backup_file"
                accept=".sql"
                required
                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">

            <button
                type="submit"
                class="mt-6 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl font-semibold shadow transition flex items-center gap-2">
                <i class="fa fa-upload"></i>
                Restore Inventory
            </button>
        </form>
    </div>
</div>

</main>

<!-- SUPER ADMIN DELETE AUTHENTICATION MODAL -->
<div class="modal fade" id="deleteBackupAuthModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-xl overflow-hidden">
            <div class="modal-header bg-red-600 text-white px-6 py-4">
                <h5 class="modal-title font-bold flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> Super Admin Delete Authorization
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteBackupAuthForm">
                <div class="modal-body p-6 space-y-4">
                    <input type="hidden" id="delete_backup_file">
                    
                    <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-xl text-xs">
                        <p><span class="font-bold">Warning:</span> You are about to permanently delete the backup file <span id="delete_backup_name_label" class="font-semibold underline"></span>. This action is irreversible.</p>
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
                        <textarea id="delete_auth_reason" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required placeholder="e.g., Obsolete backup / Freeing up space"></textarea>
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
    function confirmDeleteBackup(fileName) {
        document.getElementById('delete_backup_file').value = fileName;
        document.getElementById('delete_backup_name_label').innerText = fileName;
        
        document.getElementById('delete_auth_username').value = '';
        document.getElementById('delete_auth_password').value = '';
        document.getElementById('delete_auth_reason').value = '';

        var deleteModal = new bootstrap.Modal(document.getElementById('deleteBackupAuthModal'));
        deleteModal.show();
    }

    document.getElementById("deleteBackupAuthForm").addEventListener("submit", function(e) {
        e.preventDefault();
        let fileName = document.getElementById("delete_backup_file").value;
        fetch("../../includes/verify_delete_auth.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                item_id: 1, // Using a dummy ID since we're deleting a file, not a DB record
                username: document.getElementById("delete_auth_username").value,
                password: document.getElementById("delete_auth_password").value,
                reason: document.getElementById("delete_auth_reason").value
            })
        }).then(res => res.json()).then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById("deleteBackupAuthModal")).hide();
                window.location.href = `backup_restore.php?action_type=delete&file=${encodeURIComponent(fileName)}`;
            } else {
                alert("Authorization Failed: " + data.message);
            }
        }).catch(err => alert("An error occurred during verification."));
    });
</script>
</body>
</html>