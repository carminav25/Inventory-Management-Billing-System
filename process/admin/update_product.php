<?php
session_start();

require_once "../../config/database.php";
require_once "../../includes/activity_log.php";
require_once "../../includes/admin_auth.php";

requireAdmin();

$redirectURL = '../../pages/admin/products.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id      = (int)($_POST['id'] ?? 0);
    $product_name    = trim($_POST['product_name'] ?? '');
    $category        = trim($_POST['category'] ?? '');
    $product_size    = trim($_POST['product_size'] ?? '');
    $supplier        = trim($_POST['supplier'] ?? '');
    $unit_cost       = (float)($_POST['unit_cost'] ?? 0);
    $unit_price      = (float)($_POST['unit_price'] ?? 0);
    $current_stock   = (int)($_POST['current_stock'] ?? 0);
    $reorder_level   = (int)($_POST['reorder_level'] ?? 0);
    $description     = trim($_POST['description'] ?? '');
    $requested_by    = (int)($_SESSION['user_id'] ?? 0);

    if ($product_id <= 0) {
        $_SESSION['error_message'] = "Invalid product identifier.";
        header("Location: {$redirectURL}");
        exit;
    }

    // Fetch the original product row to check if current stock has been modified
    $stmt = $conn->prepare("SELECT current_stock FROM products WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $existingProduct = $stmt->get_result()->fetch_assoc();

    if (!$existingProduct) {
        $_SESSION['error_message'] = "Product record could not be found.";
        header("Location: {$redirectURL}");
        exit;
    }

    $old_stock = (int)$existingProduct['current_stock'];

    // If stock value was altered, run strict security validations
    if ($old_stock !== $current_stock) {
        // 1. Rely only on session data instead of trusting POST
        if (empty($_SESSION['stock_authorized']) || $_SESSION['stock_authorized'] !== "1") {
            $_SESSION['error_message'] = "Super Admin authorization is required to modify current stock levels.";
            header("Location: {$redirectURL}");
            exit;
        }

        // 2. Prevent using an old authorization (5-minute expiration check)
        if (time() - ($_SESSION['stock_auth_time'] ?? 0) > 300) {
            unset($_SESSION['stock_authorized'], $_SESSION['stock_auth_time'], $_SESSION['authorized_product'], $_SESSION['approved_by'], $_SESSION['approved_name'], $_SESSION['stock_reason']);
            $_SESSION['error_message'] = "Authorization expired. Please re-authenticate.";
            header("Location: {$redirectURL}");
            exit;
        }

        // 8. Ensure authorization applies strictly to this specific product
        if ((int)($_SESSION['authorized_product'] ?? 0) !== $product_id) {
            $_SESSION['error_message'] = "Authorization mismatch for this product.";
            header("Location: {$redirectURL}");
            exit;
        }

        // 1. Verify the approving account still exists, is active, and retains the 'Super Admin' role at save time
        $approver_id = (int)$_SESSION['approved_by'];
        $checkUser = $conn->prepare("
            SELECT id 
            FROM users 
            WHERE id = ? 
              AND role = 'Super Admin' 
              AND status = 'Active' 
            LIMIT 1
        ");
        $checkUser->bind_param("i", $approver_id);
        $checkUser->execute();
        if (!$checkUser->get_result()->fetch_assoc()) {
            unset($_SESSION['stock_authorized'], $_SESSION['stock_auth_time'], $_SESSION['authorized_product'], $_SESSION['approved_by'], $_SESSION['approved_name'], $_SESSION['stock_reason']);
            $_SESSION['error_message'] = "Approving account is no longer valid, active, or holds Super Admin privileges.";
            header("Location: {$redirectURL}");
            exit;
        }
    }

    // Begin database transaction for safe multi-table operations
    mysqli_begin_transaction($conn);

    try {
        // 1. Update the Product Details using Optimistic Locking for stock safety
        if ($old_stock !== $current_stock) {
            $updateStmt = $conn->prepare("
                UPDATE products
                SET product_name = ?, category = ?, product_size = ?, supplier = ?, unit_cost = ?, unit_price = ?, current_stock = ?, reorder_level = ?, description = ?
                WHERE id = ? AND current_stock = ?
            ");
            $updateStmt->bind_param(
                "ssssddiisii",
                $product_name,
                $category,
                $product_size,
                $supplier,
                $unit_cost,
                $unit_price,
                $current_stock,
                $reorder_level,
                $description,
                $product_id,
                $old_stock
            );
        } else {
            // Normal update if stock hasn't changed
            $updateStmt = $conn->prepare("
                UPDATE products
                SET product_name = ?, category = ?, product_size = ?, supplier = ?, unit_cost = ?, unit_price = ?, reorder_level = ?, description = ?
                WHERE id = ?
            ");
            $updateStmt->bind_param(
                "ssssddisi",
                $product_name,
                $category,
                $product_size,
                $supplier,
                $unit_cost,
                $unit_price,
                $reorder_level,
                $description,
                $product_id
            );
        }

        // 2. Check execution success cleanly
        if (!$updateStmt->execute()) {
            throw new Exception($updateStmt->error);
        }

        // Check for concurrent updates if stock was targeted
        if ($old_stock !== $current_stock && $updateStmt->affected_rows === 0) {
            throw new Exception("The product stock was modified by another user. Please reopen the product and try again.");
        }

        // 3. If stock changed, record the manual adjustment audit trail
        if ($old_stock !== $current_stock) {
            $approved_by = (int)$_SESSION['approved_by'];
            $reason      = $_SESSION['stock_reason'] ?? 'Manual stock adjustment';
            $status      = 'Approved';

            $adjStmt = $conn->prepare("
                INSERT INTO stock_adjustments
                (product_id, old_stock, new_stock, reason, requested_by, approved_by, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $adjStmt->bind_param(
                "iiisiis",
                $product_id,
                $old_stock,
                $current_stock,
                $reason,
                $requested_by,
                $approved_by,
                $status
            );
            if (!$adjStmt->execute()) {
                throw new Exception($adjStmt->error);
            }
        }

        // 4. Log the action
        if ($old_stock !== $current_stock) {
            $approver  = $_SESSION['approved_name'] ?? 'Super Admin';
            $action    = "Adjusted stock of '{$product_name}' from {$old_stock} to {$current_stock}. Approved by {$approver}. Reason: " . ($_SESSION['stock_reason'] ?? 'Manual adjustment');
            logActivity($conn, $requested_by, $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], $action);
        } else {
            logActivity($conn, $requested_by, $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Updated product '{$product_name}'");
        }

        // Commit transaction changes successfully
        mysqli_commit($conn);

        // Clear out single-use Super Admin session flags
        unset($_SESSION['approved_by']);
        unset($_SESSION['approved_name']);
        unset($_SESSION['stock_reason']);
        unset($_SESSION['stock_authorized']);
        unset($_SESSION['stock_auth_time']);
        unset($_SESSION['authorized_product']);

        $_SESSION['success_message'] = "Product updated successfully.";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error_message'] = "Transaction failed: " . $e->getMessage();
    }
}

header("Location: {$redirectURL}");
exit;