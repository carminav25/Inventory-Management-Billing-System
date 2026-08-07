<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";
require_once "../../includes/activity_log.php";

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['id'], $_GET['action'])) {
    $return_id = (int)($_POST['return_id'] ?? $_GET['id'] ?? 0);
    $action = trim($_POST['action'] ?? $_GET['action'] ?? '');
    $user_id = $_SESSION['user_id'] ?? 1;

    if ($return_id <= 0 || !in_array($action, ['Approve', 'Reject'])) {
        $_SESSION['error_message'] = "Invalid return action request.";
        header("Location: ../../pages/admin/returns.php");
        exit();
    }

    mysqli_begin_transaction($conn);

    try {
        $stmt = mysqli_prepare($conn, "SELECT r.*, p.product_name FROM returns r LEFT JOIN products p ON r.product_id = p.id WHERE r.id = ? FOR UPDATE");
        mysqli_stmt_bind_param($stmt, "i", $return_id);
        mysqli_stmt_execute($stmt);
        $returnRecord = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if (!$returnRecord) {
            throw new Exception("Return request not found.");
        }

        if ($returnRecord['status'] !== 'Pending') {
            throw new Exception("This return request has already been processed.");
        }

        $product_id = $returnRecord['product_id'];
        $quantity = $returnRecord['quantity'];

        if ($action === 'Approve') {
            $newStatus = 'Approved';

            // Restock only if condition was marked 'Good'
            if ($returnRecord['condition_status'] === 'Good') {
                $updateStock = "UPDATE products SET current_stock = current_stock + ? WHERE id = ?";
                $stmtStock = mysqli_prepare($conn, $updateStock);
                mysqli_stmt_bind_param($stmtStock, "ii", $quantity, $product_id);
                mysqli_stmt_execute($stmtStock);
            }

            logActivity($conn, $user_id, $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Approved return request #{$return_id} for {$returnRecord['product_name']} ({$quantity} pcs).");
            $_SESSION['success_message'] = "Return request #{$return_id} approved successfully.";

        } else {
            $newStatus = 'Rejected';
            logActivity($conn, $user_id, $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Rejected return request #{$return_id} for {$returnRecord['product_name']}.");
            $_SESSION['success_message'] = "Return request #{$return_id} has been rejected.";
        }

        $updateReturn = "UPDATE returns SET status = ? WHERE id = ?";
        $stmtUpdate = mysqli_prepare($conn, $updateReturn);
        mysqli_stmt_bind_param($stmtUpdate, "si", $newStatus, $return_id);
        mysqli_stmt_execute($stmtUpdate);

        mysqli_commit($conn);

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }

    header("Location: ../../pages/admin/returns.php");
    exit();
}
?>