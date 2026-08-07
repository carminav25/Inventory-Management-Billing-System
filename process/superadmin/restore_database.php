<?php
session_start();

require_once "../../config/database.php";
require_once "../../includes/superadmin_auth.php";
require_once "../../includes/activity_log.php";

// Ensure only Super Admin can perform this action
requireSuperAdmin();

$redirectURL = '../../pages/superadmin/backup_restore.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['backup_id'])) {
    header("Location: {$redirectURL}");
    exit();
}

$backupId = intval($_POST['backup_id']);

// Fetch backup details from the database
$stmt = $conn->prepare("SELECT * FROM backup_history WHERE id = ?");
$stmt->bind_param("i", $backupId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['restore_error'] = "Backup record not found.";
    header("Location: {$redirectURL}");
    exit();
}

$backup = $result->fetch_assoc();
$backupFilePath = $backup['file_path'];

if (!file_exists($backupFilePath)) {
    $_SESSION['restore_error'] = "Backup file not found on the server: " . basename($backupFilePath);
    header("Location: {$redirectURL}");
    exit();
}

// Define the full path to mysql.exe for XAMPP
$mysqlPath = 'C:\xampp\mysql\bin\mysql.exe';

// Prepare the restore command
$passwordArg = !empty($dbPassword) ? "-p\"{$dbPassword}\"" : "";
$command = "\"{$mysqlPath}\" -h {$dbHost} -u {$dbUser} {$passwordArg} {$dbName} < \"{$backupFilePath}\" 2>&1";

// Execute the restore command
exec($command, $output, $returnCode);

if ($returnCode === 0) {
    logActivity(
        $conn,
        getCurrentUserId(),
        getCurrentUserFullName(),
        getCurrentUsername(),
        getCurrentUserRole(),
        "Restored database from backup: " . $backup['file_name']
    );
    $_SESSION['restore_success'] = "Database successfully restored from " . $backup['file_name'];
} else {
    $_SESSION['restore_error'] = "Failed to restore database. Error: " . implode("\n", $output);
}

header("Location: {$redirectURL}");
exit();