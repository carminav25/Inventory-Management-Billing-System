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

/* ------------------------------------------------------
   File Size Validation
------------------------------------------------------ */
$maxSize = 50 * 1024 * 1024; // 50 MB

if ($file['size'] > $maxSize) {
    $_SESSION['error_message'] = "Backup file is too large. Maximum size is 50 MB.";
    header("Location: {$redirectURL}");
    exit();
}

/* ------------------------------------------------------
   MIME Type Validation
------------------------------------------------------ */
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedMime = [
    "application/sql",
    "text/plain",
    "application/octet-stream"
];

if (!in_array($mime, $allowedMime)) {
    $_SESSION['error_message'] = "Invalid backup file.";
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

/* ------------------------------------------------------
   Verify Backup Signature
------------------------------------------------------ */
if (
    strpos(
        $sqlContent,
        "ISU INVENTORY MANAGEMENT & BILLING SYSTEM"
    ) === false
) {
    $_SESSION['error_message'] =
        "Invalid backup file. This file was not created by the ISU Inventory Management & Billing System.";
    header("Location: {$redirectURL}");
    exit();
}

/* ------------------------------------------------------
   Verify Backup Version
------------------------------------------------------ */
if (strpos($sqlContent, "Version: 1.0") === false) {
    $_SESSION['error_message'] = "Unsupported backup version.";
    header("Location: {$redirectURL}");
    exit();
}

/* ------------------------------------------------------
   Validate Inventory Tables
------------------------------------------------------ */
$requiredTables = [
    "products",
    "categories",
    "suppliers",
    "deliveries",
    "delivery_items",
    "sales",
    "sale_items",
    "returns",
    "inventory_transactions",
    "inventory_stock_cards"
];

$missingTables = [];

foreach ($requiredTables as $table) {
    $createTable =
        stripos($sqlContent, "CREATE TABLE `$table`") !== false;

    $insertTable =
        stripos($sqlContent, "INSERT INTO `$table`") !== false;

    if (!$createTable && !$insertTable) {
        $missingTables[] = $table;
    }
}

if (!empty($missingTables)) {
    $_SESSION['error_message'] =
        "Invalid inventory backup. Missing table(s): "
        . implode(", ", $missingTables);
    header("Location: {$redirectURL}");
    exit();
}

/* ------------------------------------------------------
   Check Every SQL Statement (Updated to allow standard dump features)
------------------------------------------------------ */
$allowedStatements = [
    "CREATE TABLE",
    "INSERT INTO",
    "DROP TABLE IF EXISTS",
    "DROP VIEW IF EXISTS",
    "DROP VIEW",
    "CREATE VIEW",
    "CREATE ALGORITHM",
    "LOCK TABLES",
    "UNLOCK TABLES",
    "ALTER TABLE",
    "SET ",
    "START TRANSACTION",
    "COMMIT",
    "USE ",
    "/*!",
    "DELIMITER"
];

$statements = explode(";", $sqlContent);

foreach ($statements as $statement) {
    $statement = trim($statement);

    if ($statement === "" || strpos($statement, "--") === 0 || strpos($statement, "#") === 0) {
        continue;
    }

    $allowed = false;

    foreach ($allowedStatements as $keyword) {
        if (stripos($statement, $keyword) === 0) {
            $allowed = true;
            break;
        }
    }

    if (!$allowed) {
        $_SESSION['error_message'] = "Unsupported SQL statement detected.";
        header("Location: {$redirectURL}");
        exit();
    }
}

// 2. Define inventory tables to be affected (in reverse order of dependency for truncation)
$inventoryTables = [
    'sale_items',
    'delivery_items',
    'returns',
    'sales',
    'deliveries',
    'inventory_stock_cards',
    'inventory_transactions',
    'products',
    'categories',
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
    $_SESSION['success_message'] = "Inventory backup restored successfully. Products, suppliers, deliveries, sales, and inventory records have been updated.";

} catch (Exception $e) {
    mysqli_rollback($conn);
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=1;"); // Ensure checks are re-enabled on failure
    $_SESSION['error_message'] = "Restore failed: " . $e->getMessage();
}

header("Location: {$redirectURL}");
exit();
?>