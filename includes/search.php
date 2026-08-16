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
    echo "<div class='search-result-item text-sm'>
            <i class='fa fa-box'></i> " . htmlspecialchars($r['product_name']) . "
          </div>";
}

?>
