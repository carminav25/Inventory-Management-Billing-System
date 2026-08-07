<?php
session_start();

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";
require_once "../../includes/activity_log.php";

// Ensure only Admin or Super Admin can perform this action
requireAdmin();

$redirectURL = '../../pages/admin/backup_restore.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['backup_file'])) {
    $_SESSION['error_message'] = "Invalid request or no file uploaded.";
    header("Location: {$redirectURL}");
    exit();
}

$file = $_FILES['backup_file'];

// 1. Validate the uploaded file
if ($file['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error_message'] = "File upload error: " . $file['error'];
    header("Location: {$redirectURL}");
    exit();
}

$fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
if (strtolower($fileExtension) !== 'sql') {
    $_SESSION['error_message'] = "Invalid file type. Please upload a .sql backup file.";
    header("Location: {$redirectURL}");
    exit();
}

$sqlContent = file_get_contents($file['tmp_name']);
if (empty($sqlContent)) {
    $_SESSION['error_message'] = "The uploaded backup file is empty.";
    header("Location: {$redirectURL}");
    exit();
}

// 2. Define inventory tables to be affected (in reverse order of dependency for truncation)
$inventoryTables = [
    'sale_items',
    'delivery_items',
    'returns',
    'sales',
    'deliveries',
    'products',
    'suppliers'
];

mysqli_begin_transaction($conn);

try {
    // 3. Prepare the database for restore
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=0;");

    // 4. Truncate (empty) the inventory tables
    foreach ($inventoryTables as $table) {
        mysqli_query($conn, "TRUNCATE TABLE `{$table}`;");
    }

    // 5. Execute the multi-query SQL from the backup file
    if (!mysqli_multi_query($conn, $sqlContent)) {
        throw new Exception("Error executing SQL script: " . mysqli_error($conn));
    }

    // Clear any remaining results from multi_query
    while (mysqli_next_result($conn)) {;}

    // 6. Re-enable foreign key checks and commit
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=1;");
    mysqli_commit($conn);

    logActivity($conn, $_SESSION['user_id'], $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Restored inventory from backup file: " . $file['name']);
    $_SESSION['success_message'] = "Inventory has been successfully restored from the backup file.";

} catch (Exception $e) {
    mysqli_rollback($conn);
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=1;"); // Ensure checks are re-enabled on failure
    $_SESSION['error_message'] = "Restore failed: " . $e->getMessage();
}

header("Location: {$redirectURL}");
exit();
?>