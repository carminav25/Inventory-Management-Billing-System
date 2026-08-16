<?php
session_start();
require_once "../../config/database.php";
require_once "../../includes/viewer_auth.php";
require_once "../../includes/viewer_functions.php";

// Check if user is Viewer
requireViewer();

// Get dashboard data
$inventorySummary = getInventoryStatusSummary($conn);
$lowStockProducts = getViewerLowStockProducts($conn, 5);
$outOfStockProducts = getViewerOutOfStockProducts($conn, 5);
$recentActivity = getViewerRecentActivity($conn, 5);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viewer Dashboard - Inventory Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0B7A4B 0%, #065F46 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: #333;
            font-size: 28px;
        }
        
        .header p {
            color: #666;
            margin-top: 5px;
        }
        
        .user-info {
            text-align: right;
        }
        
        .user-info p {
            color: #666;
            margin: 5px 0;
        }
        
        .logout-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        .nav-menu {
            background: white;
            padding: 0;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .nav-menu ul {
            list-style: none;
            display: flex;
            flex-wrap: wrap;
        }
        
        .nav-menu li {
            flex: 1;
            min-width: 150px;
        }
        
        .nav-menu a {
            display: block;
            padding: 15px;
            text-decoration: none;
            color: #333;
            border-right: 1px solid #eee;
            transition: background 0.3s;
            text-align: center;
        }
        
        .nav-menu li:last-child a {
            border-right: none;
        }
        
        .nav-menu a:hover {
            background: #f5f5f5;
            color: #0B7A4B;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #0B7A4B;
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        
        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
        
        .stat-card.warning {
            border-left-color: #f39c12;
        }
        
        .stat-card.danger {
            border-left-color: #e74c3c;
        }
        
        .section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th {
            background: #f5f5f5;
            padding: 12px;
            text-align: left;
            color: #666;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
        }
        
        .table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            color: #333;
        }
        
        .table tr:hover {
            background: #f9f9f9;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        
        .badge.success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge.warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge.danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .info-box {
            background: #ecfdf5;
            border-left: 4px solid #0B7A4B;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: #333;
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .user-info {
                text-align: center;
                margin-top: 15px;
            }
            
            .nav-menu ul {
                flex-direction: column;
            }
            
            .nav-menu li {
                width: 100%;
            }
            
            .nav-menu a {
                border-right: none;
                border-bottom: 1px solid #eee;
            }
            
            .nav-menu li:last-child a {
                border-bottom: none;
            }
        }
    </style>
    <link rel="stylesheet" href="../../assets/css/semantic-theme.css">
</head>
<body class="viewer-shell">
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
                <h1>Viewer Dashboard</h1>
                <p>Inventory Management System - Read-Only Access</p>
            </div>
            <div class="user-info">
                <p><strong><?php echo htmlspecialchars($_SESSION['fullname']); ?></strong></p>
                <p><?php echo htmlspecialchars($_SESSION['role']); ?></p>
                <a href="../../process/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        
        <!-- Info Box -->
        <div class="info-box">
            <strong>Note:</strong> You have read-only access to the inventory system. You can view products, inventory status, and reports, but cannot make any modifications.
        </div>
        
        <!-- Navigation Menu -->
        <div class="nav-menu">
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="inventory_status.php">Inventory Status</a></li>
                <li><a href="qr_scanner.php">QR Scanner</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="help.php">Help</a></li>
            </ul>
        </div>
        
        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Available Stock</h3>
                <div class="value"><?php echo $inventorySummary['available'] ?? 0; ?></div>
            </div>
            <div class="stat-card warning">
                <h3>Low Stock Items</h3>
                <div class="value"><?php echo $inventorySummary['low_stock'] ?? 0; ?></div>
            </div>
            <div class="stat-card danger">
                <h3>Out of Stock Items</h3>
                <div class="value"><?php echo $inventorySummary['out_of_stock'] ?? 0; ?></div>
            </div>
        </div>
        
        <!-- Low Stock Alerts -->
        <?php if (!empty($lowStockProducts)): ?>
        <div class="section">
            <h2>⚠️ Low Stock Alerts</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Reorder Level</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lowStockProducts as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                            <td><?php echo $product['stock_quantity']; ?></td>
                            <td><?php echo $product['reorder_level']; ?></td>
                            <td><span class="badge warning">Low Stock</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Out of Stock Products -->
        <?php if (!empty($outOfStockProducts)): ?>
        <div class="section">
            <h2>📦 Out of Stock Products</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($outOfStockProducts as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                            <td><span class="badge danger">Out of Stock</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Recent Activity -->
        <div class="section">
            <h2>Recent System Activity</h2>
            <?php if (!empty($recentActivity)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentActivity as $activity): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($activity['fullname'] ?? $activity['username'] ?? 'Unknown'); ?></td>
                                <td><?php echo htmlspecialchars($activity['action']); ?></td>
                                <td><?php echo formatTimestamp($activity['date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>No recent activity</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
