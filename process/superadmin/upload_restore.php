<?php
session_start();

require_once "../../config/database.php";
require_once "../../includes/superadmin_auth.php";
require_once "../../includes/activity_log.php";

requireSuperAdmin();

$redirect = "../../pages/superadmin/backup_restore.php";

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: $redirect");
    exit();
}

if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] != UPLOAD_ERR_OK) {
    $_SESSION['restore_error'] = "No backup file was uploaded or an error occurred during upload.";
    header("Location:$redirect");
    exit();
}

$file = $_FILES['backup_file'];

// Security: Check file size (max 100MB)
if ($file['size'] > 100 * 1024 * 1024) {
    $_SESSION['restore_error'] = "Backup file is too large (max 100MB).";
    header("Location:$redirect");
    exit();
}

$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if ($extension != "sql") {
    $_SESSION['restore_error'] = "Invalid file type. Only .sql files are allowed.";
    header("Location:$redirect");
    exit();
}

$tempFile = $file['tmp_name'];

// Security: Verify the backup file content before restoring
$handle = fopen($tempFile, "r");
$firstLine = fgets($handle);
fclose($handle);

if (strpos($firstLine, "MariaDB dump") === false && strpos($firstLine, "MySQL dump") === false) {
    $_SESSION['restore_error'] = "Invalid SQL backup file. The file does not appear to be a valid MariaDB or MySQL dump.";
    header("Location:$redirect");
    exit();
}

$mysql = "C:\\xampp\\mysql\\bin\\mysql.exe";

$passwordArg = !empty($dbPassword)
    ? "-p\"{$dbPassword}\""
    : "";

$command = "\"{$mysql}\" -h {$dbHost} -u {$dbUser} {$passwordArg} {$dbName} < \"{$tempFile}\" 2>&1";

exec($command, $output, $return);

if ($return == 0) {
    logActivity(
        $conn,
        getCurrentUserId(),
        getCurrentUserFullName(),
        getCurrentUsername(),
        getCurrentUserRole(),
        "Restored database using uploaded backup: " . $file['name']
    );

    $_SESSION['restore_success'] = "Database restored successfully from '" . htmlspecialchars($file['name']) . "'.";

} else {
    $_SESSION['restore_error'] = "Restore failed.<br>" . implode("<br>", array_map('htmlspecialchars', $output));
}

header("Location:$redirect");
exit();
?>