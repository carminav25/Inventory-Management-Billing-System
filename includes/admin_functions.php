<?php

/**
 * Admin Helper Functions
 * 
 * This file contains utility functions used throughout the Admin module
 */

/**
 * Get total count of products
 */
function getTotalProducts($conn) {
    $result = $conn->query("
        SELECT COUNT(*) AS total
        FROM products
    ");
    if ($result) {
        return $result->fetch_assoc()['total'];
    }
    return 0;
}

/**
 * Get total quantity of all items in stock
 */
function getTotalStockQuantity($conn) {
    $result = $conn->query("
        SELECT SUM(current_stock) AS total
        FROM products
    ");
    if ($result) {
        return (int)($result->fetch_assoc()['total'] ?? 0);
    }
    return 0;
}
/**
 * Get total count of active products (stock > 0)
 */
function getTotalActiveProducts($conn) {
    $result = $conn->query("
        SELECT COUNT(*) AS total
        FROM products WHERE current_stock > 0
    ");
    if ($result) {
        return $result->fetch_assoc()['total'];
    }
    return 0;
}
/**
 * Get total count of categories
 */
function getTotalCategories($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM categories");
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

/**
 * Get total count of suppliers
 */
function getTotalSuppliers($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM suppliers");
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

/**
 * Get total count of customers
 */
function getTotalCustomers($conn) {
    return 0;
}

/**
 * Get total stock value
 */
function getTotalStockValue($conn) {
    $result = $conn->query("SELECT SUM(current_stock * unit_price) as total_value FROM products");
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total_value'] ?? 0;
    }
    return 0;
}

/**
 * Get count of low stock items
 */
function getLowStockItems($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM products WHERE current_stock > 0 AND current_stock <= reorder_level");
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

/**
 * Get count of out of stock items
 */
function getOutOfStockItems($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM products WHERE current_stock = 0");
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

/**
 * Get today's sales total
 */
function getTodaysSalesTotal($conn) {
    // Stubbed out as the 'sales' table is not yet implemented.
    return 0;
}

/**
 * Get monthly sales total
 */
function getMonthlySalesTotal($conn) {
    // Stubbed out as the 'sales' table is not yet implemented.
    return 0;
}

/**
 * Get pending deliveries count
 */
function getPendingDeliveries($conn) {
    // Stubbed out as the 'delivery_receipts' table is not yet implemented.
    return 0;
}

/**
 * Get pending returns count
 */
function getPendingReturns($conn) {
    // Stubbed out as the 'returns' table is not yet implemented.
    return 0;
}

/**
 * Get all categories
 */
function getAllCategories($conn, $status = 'active') {
    $query = "SELECT * FROM categories ORDER BY name ASC";
    
    $result = $conn->query($query);
    $categories = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    
    return $categories;
}

/**
 * Get category by ID
 */
function getCategoryById($conn, $categoryId) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get all suppliers
 */
function getAllSuppliers($conn, $status = 'active') {
    $query = "SELECT * FROM suppliers ORDER BY company_name ASC";
    
    $result = $conn->query($query);
    $suppliers = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $suppliers[] = $row;
        }
    }
    
    return $suppliers;
}

/**
 * Get supplier by ID
 */
function getSupplierById($conn, $supplierId) {
    $stmt = $conn->prepare("SELECT * FROM suppliers WHERE id = ?");
    $stmt->bind_param("i", $supplierId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get all products
 */
function getAllProducts($conn, $status = 'active', $limit = null, $offset = 0) {
    $query = "SELECT * FROM products ORDER BY product_name ASC";
    
    if ($limit) {
        $query .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);
    }
    
    $result = $conn->query($query);
    $products = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    return $products;
}

/**
 * Get product by ID
 */
function getProductById($conn, $productId) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get low stock products
 */
function getLowStockProducts($conn, $limit = 10) {
    $query = "SELECT * FROM products WHERE current_stock > 0 AND current_stock <= reorder_level ORDER BY current_stock ASC LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Get out of stock products
 */
function getOutOfStockProducts($conn, $limit = 10) {
    $query = "SELECT * FROM products WHERE current_stock = 0 ORDER BY product_name ASC LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Get fast moving products (high sales volume)
 */
function getFastMovingProducts($conn, $limit = 10) {
    $query = "SELECT p.id, p.product_name, COUNT(si.id) as sales_count, SUM(si.quantity) as total_quantity FROM products p LEFT JOIN sale_items si ON p.id = si.product_id GROUP BY p.id ORDER BY total_quantity DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Get slow moving products (low sales volume)
 */
function getSlowMovingProducts($conn, $limit = 10) {
    $query = "SELECT p.id, p.product_name, COUNT(si.id) as sales_count, SUM(si.quantity) as total_quantity, p.current_stock FROM products p LEFT JOIN sale_items si ON p.id = si.product_id GROUP BY p.id ORDER BY total_quantity ASC LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Get all customers
 */
function getAllCustomers($conn, $limit = null, $offset = 0) {
    $query = "SELECT * FROM customers ORDER BY firstname, lastname ASC";
    
    if ($limit) {
        $query .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);
    }
    
    $result = $conn->query($query);
    $customers = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }
    
    return $customers;
}

/**
 * Get customer by ID
 */
function getCustomerById($conn, $customerId) {
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->bind_param("i", $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get recent activity logs
 */
function getRecentActivityLogs($conn, $limit = 5) {
    $query = "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $logs = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
    }
    
    return $logs;
}

/**
 * Get top products by stock
 */
function getTopProducts($conn, $limit = 5) {
    $stmt = $conn->prepare("
        SELECT product_name, category, current_stock as stock_quantity, reorder_level, front_image 
        FROM products 
        ORDER BY current_stock DESC 
        LIMIT ?
    ");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    return $products;
}

/**
 * Get upcoming deliveries
 */
function getUpcomingDeliveries($conn)
{
    $sql = "
        SELECT
            s.supplier_name,
            d.delivery_date
        FROM deliveries d
        LEFT JOIN suppliers s
            ON d.supplier_id = s.id
        WHERE d.delivery_date >= CURDATE()
        ORDER BY d.delivery_date ASC
        LIMIT 2
    ";

    $result = mysqli_query($conn, $sql);

    $data = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }

    return $data;
}

/**
 * Get monthly inventory chart data
 */
function getMonthlyInventoryChart($conn) {
    $sql = "
        SELECT
            DATE(transaction_date) AS transaction_day,

            SUM(
                CASE
                    WHEN transaction_type='Inventory In'
                    THEN quantity
                    ELSE 0
                END
            ) AS stock_in,

            SUM(
                CASE
                    WHEN transaction_type='Inventory Out'
                    THEN quantity
                    ELSE 0
                END
            ) AS stock_out

        FROM inventory_transactions

        WHERE MONTH(transaction_date)=MONTH(CURDATE())

        AND YEAR(transaction_date)=YEAR(CURDATE())

        GROUP BY DATE(transaction_date)

        ORDER BY DATE(transaction_date)
    ";

    $result = mysqli_query($conn, $sql);

    $data=[];

    if ($result) {
        while($row=mysqli_fetch_assoc($result))
        {
            $data[]=$row;
        }
    }

    return $data;
}

/**
 * Get monthly sales data
 */
function getMonthlySalesData($conn) {
    // Fetches sales grouped by month for the current year
    $stmt = $conn->query("SELECT MONTH(sale_date) as month, SUM(total_amount) as total 
                          FROM sales 
                          WHERE YEAR(sale_date) = YEAR(CURDATE()) 
                          GROUP BY MONTH(sale_date)");
    $sales = [];
    if ($stmt) {
        while ($row = $stmt->fetch_assoc()) {
            $sales[] = $row;
        }
    }
    return $sales;
}

/**
 * Get recent transactions
 */
function getRecentTransactions($conn, $limit = 5) {
    $sql = "
        SELECT
            p.product_name,
            it.transaction_type,
            it.quantity,
            it.transaction_date
        FROM inventory_transactions it
        JOIN products p ON it.product_id = p.id
        ORDER BY it.transaction_date DESC
        LIMIT ?
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    return $data;
}

function getCategoryDistribution($conn) {
    $sql = "
        SELECT
            category,
            COUNT(*) AS total
        FROM products
        GROUP BY category
        ORDER BY total DESC
    ";

    $result = mysqli_query($conn,$sql);

    $data = [];

    while($row=mysqli_fetch_assoc($result))
    {
        $data[]=$row;
    }

    return $data;
}


 
/**
 * Get inventory movement data
 */
function getInventoryMovementData($conn) {
    // This is a placeholder as the 'transactions' table is not defined.
    return [];
}

/**
 * Get inventory movement summary for the current month
 */
function getInventoryMovementSummary($conn) {
    $summary = [
        'beginning_inventory' => 0,
        'total_in' => 0,
        'total_out' => 0,
        'ending_inventory' => 0,
    ];

    // Get current total stock (ending inventory)
    $endingResult = $conn->query("SELECT SUM(current_stock) as total FROM products");
    if ($endingResult) {
        $summary['ending_inventory'] = (int)($endingResult->fetch_assoc()['total'] ?? 0);
    }

    // Get total IN for today
    $totalInResult = $conn->query("
        SELECT SUM(quantity) as total 
        FROM inventory_transactions 
        WHERE transaction_type = 'Inventory In' AND DATE(transaction_date) = CURDATE()
    ");
    if ($totalInResult) {
        $summary['total_in'] = (int)($totalInResult->fetch_assoc()['total'] ?? 0);
    }

    // Get total OUT for today
    $totalOutResult = $conn->query("
        SELECT SUM(quantity) as total 
        FROM inventory_transactions 
        WHERE transaction_type = 'Inventory Out' AND DATE(transaction_date) = CURDATE()
    ");
    if ($totalOutResult) {
        $summary['total_out'] = (int)($totalOutResult->fetch_assoc()['total'] ?? 0);
    }

    // Calculate beginning inventory
    $summary['beginning_inventory'] = $summary['ending_inventory'] - $summary['total_in'] + $summary['total_out'];

    return $summary;
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

/**
 * Format timestamp
 */
function formatTimestamp($timestamp) {
    if (empty($timestamp)) {
        return 'N/A';
    }
    
    return date('M d, Y - h:i A', strtotime($timestamp));
}

/**
 * Get sales data for a date range
 */
function getSalesData($conn, $startDate, $endDate) {
    $query = "SELECT DATE(sale_date) as date, SUM(total_amount) as total, COUNT(*) as count FROM sales WHERE sale_date BETWEEN ? AND ? GROUP BY DATE(sale_date) ORDER BY date ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    return $data;
}

/**
 * Get the date of the last backup.
 */
function getLastBackupDate($conn) {
    $backupDir = '../../backups/';
    $latest_mtime = 0;
    $latest_filename = '';
    if (is_dir($backupDir)) {
        if ($dh = opendir($backupDir)) {
            while (($file = readdir($dh)) !== false) {
                if (pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
                    $mtime = filemtime($backupDir . $file);
                    if ($mtime > $latest_mtime) {
                        $latest_mtime = $mtime;
                        $latest_filename = $file;
                    }
                }
            }
            closedir($dh);
        }
    }
    return $latest_mtime > 0 ? date('M d, Y', $latest_mtime) : 'Never';
}

/**
 * Get the total number of backups.
 */
function getTotalBackups($conn) {
    $backupDir = '../../backups/';
    $count = 0;
    if (is_dir($backupDir) && $handle = opendir($backupDir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != ".." && pathinfo($entry, PATHINFO_EXTENSION) == 'sql') {
                $count++;
            }
        }
        closedir($handle);
    }
    return $count;
}

/**
 * Get the size of the database.
 */
function getDatabaseSize($conn, $dbName) {
    $query = "SELECT table_schema AS 'Database', ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.TABLES WHERE table_schema = '{$dbName}' GROUP BY table_schema;";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['Size (MB)'] . ' MB';
    }
    return 'N/A';
}

/**
 * Get the status of backups.
 */
function getBackupStatus($conn) {
    $lastBackupDate = getLastBackupDate($conn);
    if ($lastBackupDate === 'Never') {
        return ['text' => 'No Backups', 'class' => 'border-red-500'];
    }
    return ['text' => 'Healthy', 'class' => 'border-green-500'];
}

/**
 * Get a list of existing backups.
 */
function getBackups() {
    $backupDir = '../../backups/';
    $backups = [];
    if (is_dir($backupDir) && $handle = opendir($backupDir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != ".." && pathinfo($entry, PATHINFO_EXTENSION) == 'sql') {
                $filepath = $backupDir . $entry;
                $backups[] = [
                    'file_name' => $entry,
                    'size' => round(filesize($filepath) / 1024 / 1024, 2) . ' MB',
                    'date' => date('M d, Y', filemtime($filepath)),
                ];
            }
        }
        closedir($handle);
    }
    // Sort by date descending
    usort($backups, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    return $backups;
}
