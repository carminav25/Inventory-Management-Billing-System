<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";
require_once "../../includes/activity_log.php";

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Invalid request method.";
    header("Location: ../../pages/admin/inventory_indeliveries.php");
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Sanitize and retrieve form data
    $delivery_no = trim($_POST['delivery_no'] ?? "DEL-" . date('Ymd-His'));
    $delivery_date = $_POST['delivery_date'] ? date('Y-m-d H:i:s', strtotime($_POST['delivery_date'])) : date('Y-m-d H:i:s');
    $dr_number = trim($_POST['dr_number'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');
    $items = $_POST['items'] ?? [];
    $received_by = (int)($_SESSION['user_id'] ?? 0);
    $status = "Completed";
    if (empty($delivery_date) || empty($items)) {
        throw new Exception("Delivery date, and at least one item are required.");
    }

    // Determine supplier_id from the first item
    $first_product_id = (int)($items[0]['product_id'] ?? 0);
    if ($first_product_id <= 0) {
        throw new Exception("Invalid first product in the delivery.");
    }

    // Get supplier name from product
    $prodStmt = $conn->prepare("SELECT supplier FROM products WHERE id = ?");
    $prodStmt->bind_param("i", $first_product_id);
    $prodStmt->execute();
    $product = $prodStmt->get_result()->fetch_assoc();
    $supplierName = $product['supplier'] ?? null;
    $prodStmt->close();

    // Get supplier ID from name
    $suppStmt = $conn->prepare("SELECT id FROM suppliers WHERE supplier_name = ? LIMIT 1");
    $suppStmt->bind_param("s", $supplierName);
    $suppStmt->execute();
    $supplier = $suppStmt->get_result()->fetch_assoc();
    $supplier_id = (int)($supplier['id'] ?? 0);
    $suppStmt->close();

    // Validate items before proceeding
    foreach ($items as $item) {
        $quantity = (int)($item['quantity'] ?? 0);
        $cost_price = (float)($item['cost_price'] ?? 0);
        if ($quantity <= 0 || $cost_price < 0) {
            throw new Exception("Invalid quantity or cost price for an item.");
        }
    }

    // 1. Insert into deliveries table
    $stmt = $conn->prepare("
        INSERT INTO deliveries (delivery_no, supplier_id, delivery_date, dr_number, remarks, received_by, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sisssis", $delivery_no, $supplier_id, $delivery_date, $dr_number, $remarks, $received_by, $status);
    $stmt->execute();
    $delivery_id = $stmt->insert_id;
    $stmt->close();

    if (!$delivery_id) {
        throw new Exception("Failed to create delivery record: " . $conn->error);
    }

    // 2. Prepare statements for items loop
    $itemStmt = $conn->prepare("
        INSERT INTO delivery_items (delivery_id, product_id, quantity, cost_price) 
        VALUES (?, ?, ?, ?)
    ");
    $updateStockStmt = $conn->prepare("
        UPDATE products SET current_stock = current_stock + ? WHERE id = ?
    ");
    $getStockStmt = $conn->prepare("SELECT current_stock FROM products WHERE id = ?");
    
    // Check if inventory_transactions table exists before preparing the statement
    $transTableExists = $conn->query("SHOW TABLES LIKE 'inventory_transactions'")->num_rows > 0;
    $transStmt = null;
    if ($transTableExists) {
        $transStmt = $conn->prepare("
            INSERT INTO inventory_transactions (
                product_id, transaction_type, quantity, previous_stock, 
                current_stock, reference_no, remarks, performed_by
            ) VALUES (?, 'Stock In', ?, ?, ?, ?, ?, ?)
        ");
    }

    foreach ($items as $item) {
        $product_id = (int)$item['product_id'];
        $quantity = (int)$item['quantity'];
        $cost_price = (float)$item['cost_price'];

        // Get current stock before update for transaction logging
        $previous_stock = 0;
        if ($transStmt) {
            $getStockStmt->bind_param("i", $product_id);
            $getStockStmt->execute();
            $productResult = $getStockStmt->get_result()->fetch_assoc();
            $previous_stock = (int)($productResult['current_stock'] ?? 0);
        }
        $current_stock = $previous_stock + $quantity;

        // Insert into delivery_items
        $itemStmt->bind_param("iiid", $delivery_id, $product_id, $quantity, $cost_price);
        if (!$itemStmt->execute()) {
            throw new Exception("Failed to save delivery item: " . $itemStmt->error);
        }

        // Update product stock
        $updateStockStmt->bind_param("ii", $quantity, $product_id);
        if (!$updateStockStmt->execute()) {
            throw new Exception("Failed to update product stock: " . $updateStockStmt->error);
        }

        // Log to stock card
        $stockCardStmt = $conn->prepare("
            INSERT INTO inventory_stock_cards
            (product_id, transaction_date, transaction_type, reference_no, stock_in, stock_out, running_balance, remarks, created_by)
            VALUES (?, NOW(), 'Delivery', ?, ?, 0, ?, 'Supplier Delivery', ?)
        ");
        if (!$stockCardStmt) {
            throw new Exception("Failed to prepare stock card statement: " . $conn->error);
        }
        
        // We need the new stock level for the running balance
        $getNewStockStmt = $conn->prepare("SELECT current_stock FROM products WHERE id = ?");
        $getNewStockStmt->bind_param("i", $product_id);
        $getNewStockStmt->execute();
        $newStockResult = $getNewStockStmt->get_result()->fetch_assoc();
        $new_stock = $newStockResult['current_stock'] ?? $previous_stock + $quantity;
        $getNewStockStmt->close();

        $stockCardStmt->bind_param("isiii", $product_id, $delivery_no, $quantity, $new_stock, $received_by);
        $stockCardStmt->execute();

        // Record into inventory transactions if table exists
        if ($transStmt) {
            $transRemarks = "Received from Delivery";
            $transStmt->bind_param("iiisisi", $product_id, $quantity, $previous_stock, $current_stock, $delivery_no, $transRemarks, $received_by);
            $transStmt->execute();
        }
    }

    $itemStmt->close();
    $updateStockStmt->close();
    $getStockStmt->close();
    if (isset($stockCardStmt)) {
        $stockCardStmt->close();
    }
    if ($transStmt) {
        $transStmt->close();
    }

    // 3. Log the activity
    // Get supplier name for a more descriptive log
    $suppLogStmt = $conn->prepare("SELECT supplier_name FROM suppliers WHERE id = ?");
    $suppLogStmt->bind_param("i", $supplier_id);
    $suppLogStmt->execute();
    $suppResult = $suppLogStmt->get_result()->fetch_assoc();
    $logSupplierName = $suppResult['supplier_name'] ?? "ID {$supplier_id}";
    $suppLogStmt->close();
    logActivity($conn, $received_by, $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Received Delivery #{$delivery_no} from {$logSupplierName}");

    // Commit transaction
    $conn->commit();

    $_SESSION['success_message'] = "Delivery #{$delivery_no} has been successfully recorded and stocks updated.";
    header("Location: ../../pages/admin/inventory_indeliveries.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
    header("Location: ../../pages/admin/delivery_form.php");
    exit();
}
?>