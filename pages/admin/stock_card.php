<?php
$pageTitle = "Stock Card";
$breadcrumbs = [
    ["name" => "Products", "link" => "products.php"],
    ["name" => "Stock Card"]
];

require_once('layout.php');

$productId = $_GET['id'] ?? 0;
// $product = getProductById($conn, $productId);
// $stockHistory = getStockHistoryByProductId($conn, $productId);
?>

<div class="space-y-6">
            <!-- PAGE CONTENT HERE -->
            <h2 class="text-2xl font-bold text-gray-800 mb-5">Stock Card for Product ID: <?php echo htmlspecialchars($productId); ?></h2>

            <!-- Table for stock history -->
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                <p class="text-center text-gray-500 py-10">A detailed table showing the stock movement (In, Out, Balance) for this product will be implemented here.</p>
            </div>
</div>

<?php include_once('../../includes/footer.php'); ?>