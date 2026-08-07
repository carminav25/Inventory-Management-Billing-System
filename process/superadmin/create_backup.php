<?php
session_start();

require_once "../../config/database.php";
require_once "../../includes/superadmin_auth.php";
require_once "../../includes/activity_log.php";

// Ensure only Super Admin can perform this action
requireSuperAdmin();

$redirectURL = '../../pages/superadmin/backup_restore.php';

// Helper function from backup_restore.php
function formatBytes(int|float $bytes): string {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'backup') {
    $backupDir = __DIR__ . '/../../backups';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }

    $timestamp = date('Y-m-d_H-i-s');
    $backupFile = $backupDir . '/backup_' . $timestamp . '.sql';
    
    // Define the full path to mysqldump.exe for XAMPP
    $mysqldumpPath = 'C:\xampp\mysql\bin\mysqldump.exe';
    
    // Conditionally add the password to avoid issues with empty passwords
    $passwordArg = !empty($dbPassword) ? "-p\"{$dbPassword}\"" : "";
    
    // Create backup using mysqldump
    $command = "\"{$mysqldumpPath}\" -h {$dbHost} -u {$dbUser} {$passwordArg} {$dbName} > \"{$backupFile}\" 2>&1";
    
    // Execute backup
    exec($command, $output, $returnCode);
    
    if ($returnCode === 0 && file_exists($backupFile) && filesize($backupFile) > 0) {
        $fileSize = formatBytes(filesize($backupFile));
        $stmt = $conn->prepare("
            INSERT INTO backup_history (backup_name, backup_type, file_name, file_path, file_size, created_by, created_role, status, remarks)
            VALUES (?, 'Database', ?, ?, ?, ?, ?, 'Completed', ?)
        ");

        $backupName = "Database Backup - " . date("M d, Y h:i A");
        $fileName = basename($backupFile);
        $filePath = $backupFile;
        $userID = getCurrentUserId();
        $userRole = getCurrentUserRole();
        $remarks = "Backup created successfully";

        $stmt->bind_param("ssssiss", $backupName, $fileName, $filePath, $fileSize, $userID, $userRole, $remarks);
        $stmt->execute();

        logActivity($conn, $userID, getCurrentUserFullName(), getCurrentUsername(), $userRole, "Created Database Backup");
        $_SESSION['restore_success'] = "Database backup created successfully: " . basename($backupFile);
    } else {
        // If the file is empty or doesn't exist, it failed.
        if (file_exists($backupFile)) {
            unlink($backupFile); // Clean up empty/failed file
        }
        $_SESSION['restore_error'] = "Failed to create backup. Please check server permissions and database credentials. Error: " . implode(" ", $output);
    }
} else {
    $_SESSION['restore_error'] = "Invalid request.";
}

header("Location: {$redirectURL}");
exit();

?>