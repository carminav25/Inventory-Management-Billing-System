<?php
session_start();
require_once('../../config/database.php');
require_once('../../includes/admin_auth.php');
require_once('../../includes/admin_functions.php');
require_once('../../includes/activity_log.php');

requireAdmin();

$redirectURL = '../../pages/admin/products.php';

if (!isset($_GET['id'])) {
    header("Location: {$redirectURL}");
    exit;
}

$productId = intval($_GET['id']);

// First, get product info for logging before deleting
$product = getProductById($conn, $productId);

if ($product) {
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);

    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['user_id'], $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Deleted product '{$product['product_name']}'. Reason: " . ($_SESSION['delete_auth_reason'] ?? 'Not specified'));
        $_SESSION['success_message'] = "Product '{$product['product_name']}' has been deleted.";
    } else {
        $_SESSION['error_message'] = "Failed to delete product.";
    }
} else {
    $_SESSION['error_message'] = "Product not found.";
}

header("Location: {$redirectURL}");
exit;
?>