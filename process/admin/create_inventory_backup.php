<?php
session_start();

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";
require_once "../../includes/activity_log.php";

// Ensure only Admin or Super Admin can perform this action
requireAdmin();

$redirectURL = '../../pages/admin/backup_restore.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $backupDir = __DIR__ . '/../../backups/inventory';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }

    $timestamp = date('Y-m-d_H-i-s');
    $backupFile = $backupDir . '/inventory_backup_' . $timestamp . '.sql';
    
    // Define the full path to mysqldump.exe for XAMPP
    $mysqldumpPath = 'C:\xampp\mysql\bin\mysqldump.exe';
    
    // Conditionally add the password to avoid issues with empty passwords
    $passwordArg = !empty($dbPassword) ? "-p\"{$dbPassword}\"" : "";

    // List of inventory-related tables to back up
    $inventoryTables = "products suppliers deliveries delivery_items sales sale_items returns";
    
    // Create backup using mysqldump for specific tables
    $command = "\"{$mysqldumpPath}\" -h {$dbHost} -u {$dbUser} {$passwordArg} {$dbName} --tables {$inventoryTables} > \"{$backupFile}\" 2>&1";
    
    // Execute backup
    exec($command, $output, $returnCode);
    
    if ($returnCode === 0 && file_exists($backupFile) && filesize($backupFile) > 0) {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Created Inventory Backup: " . basename($backupFile));
        $_SESSION['success_message'] = "Inventory backup created successfully: " . basename($backupFile);
    } else {
        // If the file is empty or doesn't exist, it failed.
        if (file_exists($backupFile)) {
            unlink($backupFile); // Clean up empty/failed file
        }
        $_SESSION['error_message'] = "Failed to create inventory backup. Error: " . implode(" ", $output);
    }
} else {
    $_SESSION['error_message'] = "Invalid request.";
}

header("Location: {$redirectURL}");
exit();
?>