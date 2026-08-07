<?php
require_once "../config/database.php";

header("Content-Type: application/json");

$code = trim($_GET['code'] ?? '');

$stmt = $conn->prepare("
SELECT *
FROM products
WHERE product_code=?
LIMIT 1
");

$stmt->bind_param("s", $code);
$stmt->execute();

$result = $stmt->get_result();

if ($product = $result->fetch_assoc()) {
    echo json_encode([
        "success" => true,
        "product" => $product
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Product not found."
    ]);
}