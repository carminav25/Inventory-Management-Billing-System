<?php
header('Content-Type: application/json');
require_once('../config/database.php');

$response = [
    'success' => false,
    'message' => 'Product not found or invalid QR code.'
];

$productCode = $_GET['code'] ?? '';

if (!empty($productCode)) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_code = ?");
    if ($stmt) {
        $stmt->bind_param("s", $productCode);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $response['success'] = true;
            $response['message'] = 'Product found.';
            $response['product'] = $product;
        }
        $stmt->close();
    }
}

echo json_encode($response);
exit;