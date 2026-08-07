<?php
session_start();

require_once('../../config/database.php');
require_once('../../includes/admin_auth.php');
require_once('../../phpqrcode/qrlib.php');
require_once('../../includes/activity_log.php');

requireAdmin();

$redirectURL = '/InventoryManagementSystem/pages/admin/products.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_code = trim($_POST['product_code'] ?? '');

    if (empty($product_code)) {
        $nextIdQuery = $conn->query("SELECT MAX(id) AS max_id FROM products");
        $nextIdRow = $nextIdQuery ? $nextIdQuery->fetch_assoc() : [];
        $nextId = ((int)($nextIdRow['max_id'] ?? 0)) + 1;
        $product_code = 'PROD-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    $name         = trim($_POST['product_name'] ?? '');
    $cat          = trim($_POST['category'] ?? '');
    $product_size = trim($_POST['product_size'] ?? '');
    $sup          = trim($_POST['supplier'] ?? '');
    $cost         = trim($_POST['unit_cost'] ?? '');
    $price        = trim($_POST['unit_price'] ?? ''); 
    $stock        = trim($_POST['current_stock'] ?? '');
    $reorder      = trim($_POST['reorder_level'] ?? '');
    $desc         = $_POST['description'] ?? '';

    // Validate mandatory fields
    if (empty($name) || empty($cat) || empty($product_size) || empty($sup) || $cost === '' || $price === '' || $stock === '' || $reorder === '') {
        $_SESSION['error_message'] = "All text fields except description are required.";
        header("Location: {$redirectURL}");
        exit;
    }

    // Validate that the image file is provided
    if (!isset($_FILES['front_image']) || $_FILES['front_image']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error_message'] = "Product image is required.";
        header("Location: {$redirectURL}");
        exit;
    }

    // PHP Duplicate Check
    $checkDup = $conn->prepare("SELECT id FROM products WHERE product_name = ? AND product_size = ? LIMIT 1");
    $checkDup->bind_param("ss", $name, $product_size);
    $checkDup->execute();
    $dupResult = $checkDup->get_result();

    if ($dupResult->num_rows > 0) {
        $_SESSION['error_message'] = "Error: A product with the name '{$name}' and size '{$product_size}' already exists!";
        header("Location: {$redirectURL}");
        exit;
    }
    $checkDup->close();
    
    // Handle File Uploads
    $uploadDir = __DIR__ . '/../../assets/uploads/products/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $front_path = null;
    $front_fileName = uniqid() . '_front_' . basename($_FILES['front_image']['name']);
    if (move_uploaded_file($_FILES['front_image']['tmp_name'], $uploadDir . $front_fileName)) {
        $front_path = 'assets/uploads/products/' . $front_fileName;
    } else {
        $_SESSION['error_message'] = "Failed to upload product image.";
        header("Location: {$redirectURL}");
        exit;
    }

    $qr_code_path = 'assets/qrcodes/' . $product_code . '.png';
    $inventory_value = (float)$cost * (int)$stock;
    
    $stmt = $conn->prepare("
        INSERT INTO products (
            product_code, product_name, category, product_size, supplier, unit_cost, 
            unit_price, current_stock, reorder_level, front_image, qr_code, description, status, inventory_value
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Available', ?)
    ");
    
    $stmt->bind_param("sssssddiissdd", $product_code, $name, $cat, $product_size, $sup, $cost, $price, $stock, $reorder, $front_path, $qr_code_path, $desc, $inventory_value);

    if ($stmt->execute()) {
        $qrCodeDir = __DIR__ . '/../../assets/qrcodes/';
        if (!is_dir($qrCodeDir)) {
            mkdir($qrCodeDir, 0777, true);
        }
        
        $qrCodeFilePath = $qrCodeDir . $product_code . '.png';
        QRcode::png($product_code, $qrCodeFilePath, QR_ECLEVEL_H, 8);

        logActivity($conn, $_SESSION['user_id'], $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Added product '{$name} ({$product_size})'");

        $_SESSION['success_message'] = "Product '{$name}' ({$product_size}) added successfully.";
    } else {
        // Catch database constraint violations directly
        if ($conn->errno === 1062) {
            $_SESSION['error_message'] = "Error: This product name and size combination already exists.";
        } else {
            $_SESSION['error_message'] = "Error adding product to database: " . $stmt->error;
        }
    }
    $stmt->close();
}

header("Location: {$redirectURL}");
exit;
?>