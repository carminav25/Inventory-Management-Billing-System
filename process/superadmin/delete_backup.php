<?php
session_start();

require_once "../../config/database.php";
require_once "../../includes/superadmin_auth.php";
require_once "../../includes/activity_log.php";

// Ensure only Super Admin can perform this action
requireSuperAdmin();

$redirectURL = '../../pages/superadmin/backup_restore.php';

if (!isset($_GET['id'])) {
    $_SESSION['restore_error'] = "No backup selected.";
    header("Location: {$redirectURL}");
    exit();
}

$backupId = intval($_GET['id']);

// Get backup information
$stmt = $conn->prepare("SELECT * FROM backup_history WHERE id = ?");
$stmt->bind_param("i", $backupId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['restore_error'] = "Backup not found.";
    header("Location: {$redirectURL}");
    exit();
}

$backup = $result->fetch_assoc();

// Delete the backup file
if (file_exists($backup['file_path'])) {
    unlink($backup['file_path']);
}

// Delete record from database
$stmt = $conn->prepare("DELETE FROM backup_history WHERE id = ?");
$stmt->bind_param("i", $backupId);

if ($stmt->execute()) {

    logActivity(
        $conn,
        getCurrentUserId(),
        getCurrentUserFullName(),
        getCurrentUsername(),
        getCurrentUserRole(),
        "Deleted database backup: " . $backup['file_name']
    );

    $_SESSION['restore_success'] = "Backup deleted successfully.";

} else {

    $_SESSION['restore_error'] = "Unable to delete backup.";

}

header("Location: {$redirectURL}");
exit();