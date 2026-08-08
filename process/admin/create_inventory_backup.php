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

    // List of all required inventory-related tables to back up (matched with restore validation)
    $inventoryTables = "categories products suppliers deliveries delivery_items sales sale_items returns inventory_transactions inventory_stock_cards";
    
    // 1. Create the backup signature header
    $signature = "-- ======================================================\n";
    $signature .= "-- ISU INVENTORY MANAGEMENT & BILLING SYSTEM\n";
    $signature .= "-- INVENTORY BACKUP FILE\n";
    $signature .= "-- Version: 1.0\n";
    $signature .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $signature .= "-- ======================================================\n\n";

    // Write the signature to the file first
    file_put_contents($backupFile, $signature);

    // 2. Append the mysqldump output to the file using >>
    $command = "\"{$mysqldumpPath}\" -h {$dbHost} -u {$dbUser} {$passwordArg} {$dbName} --tables {$inventoryTables} >> \"{$backupFile}\" 2>&1";
    
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