<?php
session_start();
require_once "../../config/database.php";
require_once "../../includes/viewer_auth.php";
require_once "../../includes/viewer_functions.php";

requireViewer();

$lowStockProducts = getViewerLowStockProducts($conn, 100);
$outOfStockProducts = getViewerOutOfStockProducts($conn, 100);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inventory Status - Viewer Dashboard</title>
    <style>
        body { font-family: Arial; background: #f5f7fa; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: white; padding: 30px; border-radius: 10px; margin-bottom: 30px; }
        h1 { color: #333; }
        .back-link { color: #0B7A4B; text-decoration: none; }
        .section { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; }
        .badge { padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; }
        .badge.warning { background: #fff3cd; color: #856404; }
        .badge.danger { background: #f8d7da; color: #721c24; }
    </style>
    <link rel="stylesheet" href="../../assets/css/semantic-theme.css">
</head>
<body class="viewer-shell">
    <div class="container">
        <div class="header">
            <h1>Inventory Status</h1>
            <p><a href="index.php" class="back-link">← Back to Dashboard</a></p>
        </div>
        
        <div class="section">
            <h2>Low Stock Items</h2>
            <?php if (!empty($lowStockProducts)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Reorder Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lowStockProducts as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                            <td><?php echo $product['stock_quantity']; ?></td>
                            <td><?php echo $product['reorder_level']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No low stock items</p>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>Out of Stock Items</h2>
            <?php if (!empty($outOfStockProducts)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Category</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($outOfStockProducts as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No out of stock items</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
