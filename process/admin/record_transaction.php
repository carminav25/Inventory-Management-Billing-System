<?php
session_start();
require_once('../config/database.php');
require_once('../includes/admin_auth.php');
require_once('../includes/activity_log.php');

requireAdmin();

$redirectURL = '../pages/admin/qr_scanner.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$redirectURL}");
    exit;
}

$productId = $_POST['product_id'] ?? 0;
$quantity = (int)($_POST['quantity'] ?? 0);
$type = $_POST['transaction_type'] ?? '';
$remarks = $_POST['remarks'] ?? '';

if (empty($productId) || empty($quantity) || !in_array($type, ['Inventory In', 'Sale', 'Return'])) {
    $_SESSION['error_message'] = "Invalid transaction data provided.";
    header("Location: {$redirectURL}");
    exit;
}

try {
    $conn->begin_transaction();

    // 1. Update Product Stock
    if ($type === 'Inventory In' || $type === 'Return') {
        $stmt = $conn->prepare("UPDATE products SET current_stock = current_stock + ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $productId);
    } else { // 'Sale'
        $stmt = $conn->prepare("UPDATE products SET current_stock = current_stock - ? WHERE id = ? AND current_stock >= ?");
        $stmt->bind_param("iii", $quantity, $productId, $quantity);
    }
    $stmt->execute();

    // 2. Log the transaction in a dedicated table (assuming 'inventory_transactions' table exists)
    $logStmt = $conn->prepare("INSERT INTO inventory_transactions (product_id, transaction_type, quantity, remarks, user_id) VALUES (?, ?, ?, ?, ?)");
    $currentUserId = getCurrentUserId();
    $logStmt->bind_param("isisi", $productId, $type, $quantity, $remarks, $currentUserId);
    $logStmt->execute();

    $conn->commit();

    logActivity(
        $conn,
        $currentUserId,
        getCurrentUserFullName(),
        getCurrentUsername(),
        getCurrentUserRole(),
        "Recorded transaction: {$type} of {$quantity} for product ID {$productId}."
    );
    $_SESSION['success_message'] = "Transaction recorded successfully.";
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error_message'] = "Transaction failed: " . $e->getMessage();
}

header("Location: {$redirectURL}");
exit;
?>