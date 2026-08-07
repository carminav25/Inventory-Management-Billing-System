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
    header("Location: ../../pages/admin/returns.php");
    exit();
}

$return_no = trim($_POST['return_no'] ?? 'SRET-' . date('Ymd-His'));
$return_date = $_POST['return_date'] ? date('Y-m-d H:i:s', strtotime($_POST['return_date'])) : date('Y-m-d H:i:s');
$reference_no = trim($_POST['reference_no'] ?? ''); // This was already corrected in a previous step
$supplier = trim($_POST['supplier'] ?? '');
$items = $_POST['items'] ?? [];
$user_id = (int)($_SESSION['user_id'] ?? 0);

if (empty($items)) {
    $_SESSION['error_message'] = "Please add at least one product to the supplier return list.";
    header("Location: ../../pages/admin/return_form.php");
    exit();
}

$conn->begin_transaction();

try {
    $returnStmt = $conn->prepare(
        "INSERT INTO returns (return_no, supplier, reference_no, product_id, quantity, reason, image_path, status, processed_by, return_date, remarks) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    foreach ($items as $idx => $item) {
        $product_id = (int)$item['product_id'];
        $quantity = (int)$item['quantity'];
        $reason = trim($item['reason']);
        
        // Every new supplier return starts as 'Returned'
        $status = 'Returned';

        // Handle image upload for each item
        $image_path = null;
        if (isset($_FILES['items']['name'][$idx]['photo']) && $_FILES['items']['error'][$idx]['photo'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../assets/uploads/returns/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $fileName = uniqid() . '_supplier_return_' . basename($_FILES['items']['name'][$idx]['photo']);
            if (move_uploaded_file($_FILES['items']['tmp_name'][$idx]['photo'], $uploadDir . $fileName)) {
                $image_path = 'assets/uploads/returns/' . $fileName;
            }
        }

        $remarks = trim($_POST['remarks'] ?? '');

        $returnStmt->bind_param("sssiisssiss", $return_no, $supplier, $reference_no, $product_id, $quantity, $reason, $image_path, $status, $user_id, $return_date, $remarks);
        $returnStmt->execute();

        // Deduct stock since items are being returned outward back to the supplier
        $updateStock = $conn->prepare("UPDATE products SET current_stock = current_stock - ? WHERE id = ?");
        $updateStock->bind_param("ii", $quantity, $product_id);
        $updateStock->execute();
        $updateStock->close();
    }

    logActivity($conn, $user_id, $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Recorded new supplier return slip #{$return_no} for supplier: {$supplier}");
    $conn->commit();

    $_SESSION['success_message'] = "Supplier return slip #{$return_no} has been successfully recorded.";
    header("Location: ../../pages/admin/returns.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
    header("Location: ../../pages/admin/return_form.php");
    exit();
}
?>