<?php

/**
 * Viewer Helper Functions
 * 
 * This file contains utility functions used throughout the Viewer module.
 * All functions provide read-only access to data.
 */

/**
 * Get all active products with pagination
 */
function getViewerProducts($conn, $limit = 20, $offset = 0) {
    $query = "SELECT p.*, c.name as category_name, s.company_name as supplier_name FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              LEFT JOIN suppliers s ON p.supplier_id = s.id 
              WHERE p.status='active' 
              ORDER BY p.name ASC 
              LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Get total count of active products
 */
function getTotalViewerProducts($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM products WHERE status='active'");
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

/**
 * Get product by ID (read-only)
 */
function getViewerProductById($conn, $productId) {
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name, s.company_name as supplier_name 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            LEFT JOIN suppliers s ON p.supplier_id = s.id 
                            WHERE p.id = ? AND p.status='active'");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Search products by name or category
 */
function searchViewerProducts($conn, $searchTerm, $limit = 20, $offset = 0) {
    $searchTerm = '%' . $conn->real_escape_string($searchTerm) . '%';
    
    $query = "SELECT p.*, c.name as category_name, s.company_name as supplier_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              LEFT JOIN suppliers s ON p.supplier_id = s.id 
              WHERE p.status='active' AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)
              ORDER BY p.name ASC 
              LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssii", $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Get products by category
 */
function getViewerProductsByCategory($conn, $categoryId, $limit = 20, $offset = 0) {
    $query = "SELECT p.*, c.name as category_name, s.company_name as supplier_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              LEFT JOIN suppliers s ON p.supplier_id = s.id 
              WHERE p.status='active' AND p.category_id = ? 
              ORDER BY p.name ASC 
              LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $categoryId, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Get all active categories
 */
function getViewerCategories($conn) {
    $result = $conn->query("SELECT * FROM categories WHERE status='active' ORDER BY name ASC");
    $categories = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    
    return $categories;
}

/**
 * Get products with low stock (for viewer alerts)
 */
function getViewerLowStockProducts($conn, $limit = 10) {
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.stock_quantity > 0 AND p.stock_quantity <= p.reorder_level AND p.status='active' 
              ORDER BY p.stock_quantity ASC 
              LIMIT ?";
    
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
function getViewerOutOfStockProducts($conn, $limit = 10) {
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.stock_quantity = 0 AND p.status='active' 
              ORDER BY p.name ASC 
              LIMIT ?";
    
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
 * Get inventory status summary
 */
function getInventoryStatusSummary($conn) {
    $summary = [];
    
    // Available stock
    $result = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock_quantity > p.reorder_level AND status='active'");
    $summary['available'] = $result->fetch_assoc()['total'] ?? 0;
    
    // Low stock
    $result = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock_quantity > 0 AND stock_quantity <= reorder_level AND status='active'");
    $summary['low_stock'] = $result->fetch_assoc()['total'] ?? 0;
    
    // Out of stock
    $result = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock_quantity = 0 AND status='active'");
    $summary['out_of_stock'] = $result->fetch_assoc()['total'] ?? 0;
    
    return $summary;
}

/**
 * Get recent system announcements
 */
function getSystemAnnouncements($conn, $limit = 5) {
    $query = "SELECT * FROM activity_logs WHERE action LIKE '%announcement%' OR action LIKE '%system%' ORDER BY date DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $announcements = [];
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    
    return $announcements;
}

/**
 * Get recent activity for viewer
 */
function getViewerRecentActivity($conn, $limit = 10) {
    $query = "SELECT * FROM activity_logs WHERE role IN ('Admin', 'Super Admin') ORDER BY date DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    
    return $activities;
}

/**
 * Get inventory report data
 */
function getInventoryReport($conn) {
    $report = [];
    
    // Total products
    $result = $conn->query("SELECT COUNT(*) as total FROM products WHERE status='active'");
    $report['total_products'] = $result->fetch_assoc()['total'] ?? 0;
    
    // Total stock value
    $result = $conn->query("SELECT SUM(stock_quantity * price) as total_value FROM products WHERE status='active'");
    $report['total_value'] = $result->fetch_assoc()['total_value'] ?? 0;
    
    // Average stock per product
    $result = $conn->query("SELECT AVG(stock_quantity) as avg_stock FROM products WHERE status='active'");
    $report['avg_stock'] = $result->fetch_assoc()['avg_stock'] ?? 0;
    
    // Stock by category
    $query = "SELECT c.name, COUNT(p.id) as product_count, SUM(p.stock_quantity) as total_stock 
              FROM categories c 
              LEFT JOIN products p ON c.id = p.category_id AND p.status='active' 
              WHERE c.status='active' 
              GROUP BY c.id, c.name 
              ORDER BY c.name ASC";
    $result = $conn->query($query);
    $report['by_category'] = [];
    while ($row = $result->fetch_assoc()) {
        $report['by_category'][] = $row;
    }
    
    return $report;
}

/**
 * Get product report data
 */
function getProductReport($conn, $limit = 100) {
    $query = "SELECT p.id, p.name, p.price, p.cost, p.stock_quantity, p.reorder_level, c.name as category_name, s.company_name as supplier_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              LEFT JOIN suppliers s ON p.supplier_id = s.id 
              WHERE p.status='active' 
              ORDER BY p.name ASC 
              LIMIT ?";
    
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
 * Get stock summary report
 */
function getStockSummaryReport($conn) {
    $summary = [];
    
    // Products by stock status
    $query = "SELECT 
                CASE 
                    WHEN stock_quantity = 0 THEN 'Out of Stock'
                    WHEN stock_quantity <= reorder_level THEN 'Low Stock'
                    ELSE 'Available'
                END as status,
                COUNT(*) as count,
                SUM(stock_quantity) as total_quantity,
                SUM(stock_quantity * price) as total_value
              FROM products 
              WHERE status='active' 
              GROUP BY status";
    
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $summary[] = $row;
    }
    
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
 * Get stock status label and color
 */
function getStockStatusBadge($product) {
    if ($product['stock_quantity'] == 0) {
        return ['status' => 'Out of Stock', 'class' => 'danger'];
    } elseif ($product['stock_quantity'] <= $product['reorder_level']) {
        return ['status' => 'Low Stock', 'class' => 'warning'];
    } else {
        return ['status' => 'Available', 'class' => 'success'];
    }
}
