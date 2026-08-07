<?php
session_start();
require_once "../../config/database.php";
require_once "../../includes/viewer_auth.php";
require_once "../../includes/viewer_functions.php";

requireViewer();

$inventoryReport = getInventoryReport($conn);
$productReport = getProductReport($conn, 50);
$stockSummary = getStockSummaryReport($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reports - Viewer Dashboard</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: white; padding: 30px; border-radius: 10px; margin-bottom: 30px; }
        h1 { color: #333; }
        .back-link { color: #3498db; text-decoration: none; }
        .section { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Read-Only Reports</h1>
            <p><a href="index.php" class="back-link">← Back to Dashboard</a></p>
        </div>
        
        <div class="section">
            <h2>Inventory Summary</h2>
            <p>Total Products: <?php echo $inventoryReport['total_products']; ?></p>
            <p>Total Stock Value: ₱<?php echo number_format($inventoryReport['total_value'], 2); ?></p>
            <p>Average Stock per Product: <?php echo round($inventoryReport['avg_stock'], 2); ?></p>
        </div>
        
        <div class="section">
            <h2>Stock Status Summary</h2>
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Count</th>
                        <th>Total Quantity</th>
                        <th>Total Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stockSummary as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo $row['count']; ?></td>
                        <td><?php echo $row['total_quantity']; ?></td>
                        <td>₱<?php echo number_format($row['total_value'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
