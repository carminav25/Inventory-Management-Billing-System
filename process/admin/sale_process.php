<?php
session_start();
require_once('../../config/database.php');
require_once('../../includes/admin_auth.php');
require_once('../../includes/activity_log.php');

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $totalPrice = $_POST['total_price'];

    if (empty($productId) || empty($quantity) || $quantity <= 0) {
        header("Location: ../../pages/admin/sale_form.php?status=error&message=Invalid input.");
        exit;
    }

    try {
        $conn->begin_transaction();

        // 1. Deduct stock
        $stmt = $conn->prepare("UPDATE products SET current_stock = current_stock - ? WHERE id = ? AND current_stock >= ?");
        $stmt->bind_param("iii", $quantity, $productId, $quantity);
        $stmt->execute();

        // 2. Record Transaction (assuming a 'transactions' table exists)
        // $transStmt = $conn->prepare("INSERT INTO transactions (product_id, type, quantity, date) VALUES (?, 'Sale', ?, NOW())");
        // $transStmt->bind_param("ii", $productId, $quantity);
        // $transStmt->execute();

        $conn->commit();
        logActivity($conn, getCurrentUserId(), getCurrentUserFullName(), getCurrentUsername(), getCurrentUserRole(), "Processed sale for product ID {$productId}, quantity {$quantity}.");
        header("Location: ../../pages/admin/inventory_outsales.php?status=success");
    } catch (Exception $e) {
        $conn->rollBack();
        header("Location: ../../pages/admin/sale_form.php?status=error&message=" . urlencode($e->getMessage()));
    }
}
?>
