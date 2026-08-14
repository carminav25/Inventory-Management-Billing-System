<?php
session_start();
require_once "../../config/database.php";
require_once "../../includes/viewer_auth.php";
require_once "../../includes/viewer_functions.php";

requireViewer();

$productId = intval($_GET['id'] ?? 0);
$product = getViewerProductById($conn, $productId);

if (!$product) {
    header("Location: products.php");
    exit();
}

$status = getStockStatusBadge($product);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($product['name']); ?> - Product Details</title>
    <style>
        body { font-family: Arial; background: #f5f7fa; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #333; }
        .back-link { color: #0B7A4B; text-decoration: none; }
        .product-details { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 20px; }
        .product-image { background: #f5f5f5; padding: 20px; border-radius: 10px; }
        .badge { padding: 8px 16px; border-radius: 20px; font-weight: bold; display: inline-block; margin: 10px 0; }
        .badge.success { background: #d4edda; color: #155724; }
        .badge.warning { background: #fff3cd; color: #856404; }
        .badge.danger { background: #f8d7da; color: #721c24; }
        .price { font-size: 24px; font-weight: bold; color: #0B7A4B; margin: 15px 0; }
        .info-row { margin: 10px 0; }
        .info-label { font-weight: bold; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <p><a href="products.php" class="back-link">← Back to Products</a></p>
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        
        <div class="product-details">
            <div>
                <div class="product-image">
                    <?php if (!empty($product['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Product" style="width: 100%; border-radius: 10px;">
                    <?php else: ?>
                        <p style="text-align: center; color: #999;">No Image Available</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div>
                <div class="price">₱<?php echo number_format($product['price'], 2); ?></div>
                <span class="badge <?php echo $status['class']; ?>"><?php echo $status['status']; ?></span>
                
                <div class="info-row">
                    <span class="info-label">Category:</span> <?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?>
                </div>
                <div class="info-row">
                    <span class="info-label">Supplier:</span> <?php echo htmlspecialchars($product['supplier_name'] ?? 'N/A'); ?>
                </div>
                <div class="info-row">
                    <span class="info-label">Stock Quantity:</span> <?php echo $product['stock_quantity']; ?>
                </div>
                <div class="info-row">
                    <span class="info-label">Reorder Level:</span> <?php echo $product['reorder_level']; ?>
                </div>
                <div class="info-row">
                    <span class="info-label">Unit of Measure:</span> <?php echo htmlspecialchars($product['unit_of_measure'] ?? 'N/A'); ?>
                </div>
                
                <?php if (!empty($product['description'])): ?>
                <div class="info-row" style="margin-top: 20px;">
                    <span class="info-label">Description:</span><br>
                    <?php echo htmlspecialchars($product['description']); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
