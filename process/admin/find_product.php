<?php
require_once "../../config/database.php";

header("Content-Type: application/json");

$code = trim($_GET['code'] ?? '');

if ($code === '') {
    echo json_encode(["success" => false, "message" => "Empty code provided"]);
    exit;
}

// Check if input is a full URL or contains a filename path matching the QR code stored in DB
$searchCode = $code;
if (filter_var($code, FILTER_VALIDATE_URL)) {
    $pathInfo = pathinfo(parse_url($code, PHP_URL_PATH));
    if (isset($pathInfo['filename'])) {
        $searchCode = $pathInfo['filename'];
    }
} else {
    $pathInfo = pathinfo($code);
    if (isset($pathInfo['filename']) && strpos($code, '/') !== false) {
        $searchCode = $pathInfo['filename'];
    }
}

$stmt = mysqli_prepare($conn, "
SELECT
    id,
    product_code,
    product_name,
    category,
    product_size,
    supplier,
    current_stock,
    reorder_level,
    unit_cost,
    unit_price,
    front_image,
    qr_code
FROM products
WHERE product_code = ?
OR qr_code = ?
OR qr_code LIKE CONCAT('%/', ?, '.png')
OR qr_code LIKE CONCAT('%', ?)
LIMIT 1
");

mysqli_stmt_bind_param($stmt, "ssss", $code, $code, $searchCode, $code);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {

    $status = "Available";

    if ($row['current_stock'] == 0) {
        $status = "Out of Stock";
    } elseif ($row['current_stock'] <= $row['reorder_level']) {
        $status = "Low Stock";
    }

    echo json_encode([
        "success" => true,
        "id" => $row['id'],
        "code" => $row['product_code'],
        "name" => $row['product_name'],
        "category" => $row['category'],
        "size" => $row['product_size'],
        "supplier" => $row['supplier'],
        "stock" => $row['current_stock'],
        "reorder_level" => $row['reorder_level'],
        "cost" => $row['unit_cost'],
        "price" => $row['unit_price'],
        "status" => $status,
        "image" => $row['front_image']
    ]);

} else {
    echo json_encode([
        "success" => false,
        "message" => "Product not found for code: " . $code
    ]);
}
?>