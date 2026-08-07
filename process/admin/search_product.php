<?php
require_once "../config/database.php";

header("Content-Type: application/json");

$q = trim($_GET['q'] ?? '');

if ($q == '') {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
SELECT
    id,
    product_name,
    category,
    current_stock,
    unit_cost,
    front_image,
    product_code
FROM products
WHERE product_name LIKE ?
   OR product_code LIKE ?
ORDER BY product_name ASC
LIMIT 20
");

$search = "%{$q}%";
$stmt->bind_param("ss", $search, $search);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);