<?php
require_once('../config/database.php');

$query = $_GET['q'] ?? '';
if (strlen($query) < 2) {
    exit; // Only search if 2+ chars
}

$searchTerm = "%{$query}%";
$stmt = $conn->prepare("SELECT product_name, 'product' as type FROM products WHERE product_name LIKE ? LIMIT 5");
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$results = $stmt->get_result();

while ($r = $results->fetch_assoc()) {
    echo "<div class='p-3 border-b hover:bg-gray-50 cursor-pointer text-sm' style='padding: 12px; border-bottom: 1px solid #eee; cursor: pointer;'>
            <i class='fa fa-box text-gray-400 mr-2' style='margin-right: 8px; color: #999;'></i> " . htmlspecialchars($r['product_name']) . "
          </div>";
}

?>