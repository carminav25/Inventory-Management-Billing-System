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
        $deleteAuthorized = ($_SESSION['delete_authorized'] ?? false) === true;
        $deleteAuthTime = (int) ($_SESSION['delete_auth_time'] ?? 0);

        if ($deleteAuthorized && (time() - $deleteAuthTime < 300)) {
            
            $filePath = $backupFolder . $backup_file;

            $resolvedFilePath = realpath($filePath);
            $resolvedBackupFolder = realpath($backupFolder);

            if ($resolvedFilePath !== false && $resolvedBackupFolder !== false && strpos($resolvedFilePath, $resolvedBackupFolder) === 0) {
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
if ($backups === false) {
    $backups = [];
}

$totalBackups = count($backups);

$lastBackupDate = "No Backup";

if ($totalBackups > 0) {
    usort($backups, function ($a, $b) {
        $aTime = is_file($a) ? filemtime($a) : 0;
        $bTime = is_file($b) ? filemtime($b) : 0;
        return $bTime <=> $aTime;
    });

    $latestBackup = $backups[0];
    if (is_file($latestBackup)) {
        $lastBackupDate = date("M d, Y h:i A", filemtime($latestBackup));
    }
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
    <title><?= htmlspecialchars($pageTitle ?? 'Backup & Restore'); ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body { background:#f5f7fb; color:#0f172a; font-family:Arial, Helvetica, sans-serif; }
        a { text-decoration:none !important; }
        main {
            margin-left:270px;
            width:calc(100% - 270px);
            min-height:100vh;
            padding:24px;
            background:#f5f7fb;
        }
        .page-wrap { width:100%; max-width:1280px; margin:0 auto; }
        .page-header {
            background:#fff;
            border:1px solid #e2e8f0;
            border-radius:16px;
            padding:24px;
            box-shadow:0 2px 8px rgba(15,23,42,.04);
            margin-bottom:24px;
        }
        .page-header h1 { margin:0; font-size:26px; line-height:1.25; font-weight:700; color:#0f172a; letter-spacing:-.02em; }
        .page-header p { margin:4px 0 0; font-size:14px; color:#64748b; }
        .panel { background:#fff; border:1px solid #e2e8f0; border-radius:16px; box-shadow:0 2px 8px rgba(15,23,42,.035); }
        .stats-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:20px; margin-bottom:24px; }
        .stat-card { min-width:0; min-height:112px; padding:20px; border-left:4px solid #10b981; }
        .stat-label { margin:0; color:#94a3b8; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; }
        .stat-value { margin:8px 0 0; color:#0f172a; font-size:20px; line-height:1.3; font-weight:700; }
        .last-date { display:block; }
        .last-time { display:block; margin-top:2px; color:#475569; font-size:14px; font-weight:600; }
        .section { padding:24px; margin-bottom:24px; }
        .section-title { margin:0; color:#1e293b; font-size:18px; font-weight:700; }
        .section-subtitle { margin:4px 0 0; color:#64748b; font-size:13px; }
        .primary-btn { display:inline-flex; align-items:center; justify-content:center; min-height:42px; padding:10px 18px; border:0; border-radius:10px; background:#059669; color:#fff; font-size:13px; font-weight:700; cursor:pointer; transition:.2s; }
        .primary-btn:hover { background:#047857; transform:translateY(-1px); }
        .secondary-btn { display:inline-flex; align-items:center; justify-content:center; min-height:42px; padding:10px 18px; border:1px solid #e2e8f0; border-radius:10px; background:#f8fafc; color:#475569; font-size:13px; font-weight:700; cursor:pointer; }
        .table-wrap { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; }
        th { padding:14px 18px; background:#f8fafc; color:#94a3b8; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; text-align:left; white-space:nowrap; }
        td { padding:15px 18px; border-top:1px solid #f1f5f9; color:#475569; font-size:13px; }
        tbody tr:hover { background:#f8fafc; }
        .file-name { color:#334155; font-family:Consolas,monospace; font-size:12px; font-weight:600; }
        .action-row { display:flex; gap:8px; align-items:center; }
        .table-btn { display:inline-flex; align-items:center; justify-content:center; min-height:34px; padding:7px 12px; border-radius:8px; font-size:12px; font-weight:700; border:0; cursor:pointer; }
        .download-btn { background:#ecfdf5; color:#047857; }
        .download-btn:hover { background:#d1fae5; }
        .delete-btn { background:#fef2f2; color:#dc2626; }
        .delete-btn:hover { background:#fee2e2; }
        .warning { background:#fffbeb; border:1px solid #fde68a; border-left:4px solid #f59e0b; border-radius:10px; padding:16px; margin:20px 0; }
        .warning-title { margin:0; color:#b45309; font-size:14px; font-weight:700; }
        .warning-text { margin:5px 0 0; color:#92400e; font-size:13px; line-height:1.6; }
        .file-input { width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:10px; background:#f8fafc; color:#475569; font-size:13px; }
        .file-input:focus { outline:none; border-color:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,.1); background:#fff; }
        .form-label { display:block; margin-bottom:7px; color:#64748b; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; }
        .alert { padding:13px 16px; border-radius:10px; margin-bottom:16px; font-size:13px; }
        .alert-success { background:#ecfdf5; border:1px solid #a7f3d0; color:#047857; }
        .alert-error { background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; }
        .empty { padding:40px 20px; text-align:center; color:#94a3b8; }
        #deleteBackupAuthModal { display:none; position:fixed; inset:0; z-index:9999; padding:20px; background:rgba(15,23,42,.48); align-items:center; justify-content:center; }
        #deleteBackupAuthModal.show { display:flex; }
        .modal-dialog { width:100%; max-width:500px; }
        .modal-content { background:#fff; border-radius:16px; border:1px solid #e2e8f0; overflow:hidden; box-shadow:0 25px 70px rgba(15,23,42,.25); }
        .modal-header { display:flex; justify-content:space-between; align-items:center; padding:16px 20px; background:#fef2f2; border-bottom:1px solid #fee2e2; }
        .modal-title { margin:0; color:#991b1b; font-size:16px; font-weight:700; }
        .modal-close { border:0; background:transparent; color:#64748b; font-size:12px; font-weight:700; cursor:pointer; padding:6px; }
        .modal-body { padding:20px; }
        .modal-footer { display:flex; justify-content:flex-end; gap:8px; padding:14px 20px; background:#f8fafc; border-top:1px solid #e2e8f0; }
        .auth-field { margin-top:14px; }
        .auth-label { display:block; margin-bottom:6px; color:#475569; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
        .auth-input { width:100%; min-height:42px; padding:10px 12px; border:1px solid #cbd5e1; border-radius:9px; background:#f8fafc; color:#0f172a; font-size:13px; outline:none; }
        .auth-input:focus { border-color:#ef4444; background:#fff; box-shadow:0 0 0 3px rgba(239,68,68,.1); }
        textarea.auth-input { min-height:88px; resize:vertical; }
        .cancel-btn,.verify-btn { min-height:40px; padding:9px 15px; border-radius:9px; font-size:12px; font-weight:700; cursor:pointer; }
        .cancel-btn { border:1px solid #e2e8f0; background:#fff; color:#475569; }
        .verify-btn { border:1px solid #dc2626; background:#dc2626; color:#fff; }
        @media(max-width:1100px) { .stats-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media(max-width:767px) { main { margin-left:0; width:100%; padding:16px; } .page-header { padding:20px; } }
        @media(max-width:600px) { .stats-grid { grid-template-columns:1fr; gap:14px; } .section { padding:18px; } .page-header h1 { font-size:23px; } .modal-footer { flex-direction:column-reverse; } .cancel-btn,.verify-btn { width:100%; } }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
<main>
<div class="page-wrap">

    <!-- SAME HEADER STYLE AS PRODUCT MANAGEMENT -->
    <div class="page-header">
        <h1>Backup &amp; Restore</h1>
        <p>Manage inventory backups, restore saved records, and maintain database protection.</p>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="panel stat-card">
            <p class="stat-label">Last Backup</p>
            <?php if ($totalBackups > 0): ?>
                <h2 class="stat-value"><span class="last-date"><?= htmlspecialchars(date("M d, Y", filemtime($backups[0]))) ?></span><span class="last-time"><?= htmlspecialchars(date("h:i A", filemtime($backups[0]))) ?></span></h2>
            <?php else: ?>
                <h2 class="stat-value">No Backup</h2>
            <?php endif; ?>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 border-l-4 border-l-emerald-500">
            <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Total Backups</p>
            <h2 class="text-2xl font-bold text-slate-800 mt-1"><?= $totalBackups ?></h2>
        <div class="panel stat-card">
            <p class="stat-label">Total Backups</p>
            <h2 class="stat-value"><?= (int)$totalBackups ?></h2>
        </div>
        <div class="panel stat-card">
            <p class="stat-label">Database Size</p>
            <h2 class="stat-value"><?= htmlspecialchars($dbSize) ?></h2>
        </div>
        <div class="panel stat-card" style="border-left-color:<?= $totalBackups > 0 ? '#10b981' : '#ef4444' ?>;">
            <p class="stat-label">Backup Status</p>
            <h2 class="stat-value"><?= htmlspecialchars($backupStatus['text']) ?></h2>
        </div>
    </div>

    <div class="panel section">
        <h2 class="section-title">Create New Backup</h2>
        <p class="section-subtitle">Create a complete SQL backup of the inventory database.</p>
        <form action="../../process/admin/create_inventory_backup.php" method="POST" class="mt-5">
            <button type="submit" class="primary-btn">Create Inventory Backup</button>
        </form>
    </div>

    <div class="panel section" style="padding:0; overflow:hidden;">
        <div style="padding:20px 24px; border-bottom:1px solid #f1f5f9;">
            <h2 class="section-title">Existing Backups</h2>
            <p class="section-subtitle">Download or securely delete previously created inventory backups.</p>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Backup Name</th><th>Type</th><th>Size</th><th>Date</th><th>Action</th></tr></thead>
                <tbody>
                <?php if (!empty($backups)): ?>
                    <?php foreach ($backups as $backup): $file=basename($backup); $size=round(filesize($backup)/1024,2).' KB'; $date=date('M d, Y',filemtime($backup)); ?>
                    <tr>
                        <td class="file-name"><?= htmlspecialchars($file) ?></td>
                        <td>Full</td>
                        <td><?= htmlspecialchars($size) ?></td>
                        <td><?= htmlspecialchars($date) ?></td>
                        <td><div class="action-row"><a class="table-btn download-btn" href="../../backups/inventory/<?= urlencode($file) ?>" download>Download</a><button type="button" class="table-btn delete-btn" onclick="confirmDeleteBackup('<?= htmlspecialchars($file, ENT_QUOTES) ?>')">Delete</button></div></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="empty">No backups found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel section">
        <h2 class="section-title">Restore Inventory Backup</h2>
        <p class="section-subtitle">Upload a saved SQL backup to restore inventory records.</p>
        <div class="warning">
            <p class="warning-title">Warning!</p>
            <p class="warning-text">Restoring this backup will overwrite the current inventory records only. User accounts, Super Admin settings, activity logs, and system configuration will NOT be affected.</p>
        </div>
        <form action="../../process/admin/restore_inventory_backup.php" method="POST" enctype="multipart/form-data">
            <label class="form-label">Select SQL Backup File</label>
            <input class="file-input" type="file" name="backup_file" accept=".sql" required>
            <button type="submit" class="primary-btn" style="margin-top:16px;">Restore Inventory</button>
        </form>
    </div>
</div>
</main>

<div id="deleteBackupAuthModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="deleteBackupAuthTitle">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 id="deleteBackupAuthTitle" class="modal-title">Super Admin Delete Authorization</h5><button type="button" class="modal-close" onclick="closeDeleteBackupModal()">Close</button></div>
        <form id="deleteBackupAuthForm">
            <div class="modal-body">
                <input type="hidden" id="delete_backup_file">
                <div class="alert alert-error" style="margin:0;"><strong>Warning:</strong> You are about to permanently delete <span id="delete_backup_name_label" style="font-weight:700;text-decoration:underline;"></span>. This action is irreversible.</div>
                <div class="auth-field"><label for="delete_auth_username" class="auth-label">Super Admin Username</label><input type="text" id="delete_auth_username" class="auth-input" autocomplete="username" required></div>
                <div class="auth-field"><label for="delete_auth_password" class="auth-label">Password</label><input type="password" id="delete_auth_password" class="auth-input" autocomplete="current-password" required></div>
                <div class="auth-field"><label for="delete_auth_reason" class="auth-label">Reason for Deletion</label><textarea id="delete_auth_reason" rows="3" class="auth-input" required placeholder="e.g., Obsolete backup / Freeing up space"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="cancel-btn" onclick="closeDeleteBackupModal()">Cancel</button><button type="submit" class="verify-btn">Verify &amp; Delete</button></div>
        </form>
    </div></div>
</div>
<script>
    const deleteBackupModal = document.getElementById('deleteBackupAuthModal');

    function confirmDeleteBackup(fileName) {
        document.getElementById('delete_backup_file').value = fileName;
        document.getElementById('delete_backup_name_label').innerText = fileName;

        document.getElementById('delete_auth_username').value = '';
        document.getElementById('delete_auth_password').value = '';
        document.getElementById('delete_auth_reason').value = '';

        deleteBackupModal.classList.add('show');
        deleteBackupModal.setAttribute('aria-hidden', 'false');

        setTimeout(function () {
            document.getElementById('delete_auth_username').focus();
        }, 50);
    }

    function closeDeleteBackupModal() {
        deleteBackupModal.classList.remove('show');
        deleteBackupModal.setAttribute('aria-hidden', 'true');
    }

    deleteBackupModal.addEventListener('click', function (event) {
        if (event.target === deleteBackupModal) {
            closeDeleteBackupModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (
            event.key === 'Escape' &&
            deleteBackupModal.classList.contains('show')
        ) {
            closeDeleteBackupModal();
        }
    });

    document.getElementById("deleteBackupAuthForm").addEventListener("submit", function(e) {
        e.preventDefault();

        const fileName =
            document.getElementById("delete_backup_file").value;

        fetch("../../includes/verify_delete_auth.php", {
            method: "POST",
            headers: {
                "Content-Type":
                    "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({
                item_id: 1,
                username:
                    document.getElementById("delete_auth_username").value,
                password:
                    document.getElementById("delete_auth_password").value,
                reason:
                    document.getElementById("delete_auth_reason").value
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeDeleteBackupModal();

                window.location.href =
                    `backup_restore.php?action_type=delete&file=${encodeURIComponent(fileName)}`;
            } else {
                alert("Authorization Failed: " + data.message);
            }
        })
        .catch(() => {
            alert("An error occurred during verification.");
        });
    });
</script>
</body>
</html>
