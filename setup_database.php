<?php
require_once "config/database.php";

$messages = [];
$errors = [];

try {
    $tables = $conn->query("SHOW TABLES LIKE 'users'");
    if (!$tables || $tables->num_rows === 0) {
        throw new Exception("Users table does not exist. Please create it first.");
    }

    $columns = $conn->query("SHOW COLUMNS FROM users");
    $existingColumns = [];
    while ($col = $columns->fetch_assoc()) {
        $existingColumns[] = $col['Field'];
    }

    if (!in_array('recovery_password', $existingColumns)) {
        $conn->query("ALTER TABLE users ADD COLUMN recovery_password VARCHAR(255) NULL");
        $messages[] = "✓ Added recovery_password column";
    } else {
        $messages[] = "• recovery_password column already exists";
    }

    if (!in_array('recovery_code', $existingColumns)) {
        $conn->query("ALTER TABLE users ADD COLUMN recovery_code VARCHAR(255) NULL");
        $messages[] = "✓ Added recovery_code column";
    } else {
        $messages[] = "• recovery_code column already exists";
    }

    if (!in_array('recovery_attempts', $existingColumns)) {
        $conn->query("ALTER TABLE users ADD COLUMN recovery_attempts INT DEFAULT 0");
        $messages[] = "✓ Added recovery_attempts column";
    } else {
        $messages[] = "• recovery_attempts column already exists";
    }

    if (!in_array('status', $existingColumns)) {
        $conn->query("ALTER TABLE users ADD COLUMN status VARCHAR(50) DEFAULT 'active'");
        $messages[] = "✓ Added status column to users";
    } else {
        $messages[] = "• status column already exists";
    }

    if (!in_array('lock_until', $existingColumns)) {
        $conn->query("ALTER TABLE users ADD COLUMN lock_until DATETIME NULL");
        $messages[] = "✓ Added lock_until column";
    } else {
        $messages[] = "• lock_until column already exists";
    }

    if (!in_array('failed_attempts', $existingColumns)) {
        $conn->query("ALTER TABLE users ADD COLUMN failed_attempts INT DEFAULT 0");
        $messages[] = "✓ Added failed_attempts column";
    } else {
        $messages[] = "• failed_attempts column already exists";
    }

    if (!in_array('is_permanently_locked', $existingColumns)) {
        $conn->query("ALTER TABLE users ADD COLUMN is_permanently_locked TINYINT(1) DEFAULT 0");
        $messages[] = "✓ Added is_permanently_locked column";
    } else {
        $messages[] = "• is_permanently_locked column already exists";
    }

    // Remove old redundant columns if they exist
    if (in_array('recovery_locked_until', $existingColumns)) {
        $conn->query("ALTER TABLE users DROP COLUMN recovery_locked_until");
    }
    if (in_array('login_locked_until', $existingColumns)) {
        $conn->query("ALTER TABLE users DROP COLUMN login_locked_until");
    }
    if (in_array('failed_login_attempts', $existingColumns)) {
        $conn->query("ALTER TABLE users DROP COLUMN failed_login_attempts");
    }

    $logTables = $conn->query("SHOW TABLES LIKE 'activity_logs'");
    if (!$logTables || $logTables->num_rows === 0) {
        $createLogsTable = "
        CREATE TABLE activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            fullname VARCHAR(255) NULL,
            username VARCHAR(255) NULL,
            role VARCHAR(255) NULL,
            action VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_username (username),
            INDEX idx_date (date)
        )
        ";
        $conn->query($createLogsTable);
        $messages[] = "✓ Created activity_logs table";
    } else {
        $messages[] = "• activity_logs table already exists";

        $existingLogColumns = [];
        $logColumns = $conn->query("SHOW COLUMNS FROM activity_logs");
        while ($col = $logColumns->fetch_assoc()) {
            $existingLogColumns[] = $col['Field'];
        }

        $logColumnAdds = [
            'user_id INT NULL',
            'fullname VARCHAR(255) NULL',
            'username VARCHAR(255) NULL',
            'role VARCHAR(255) NULL',
            'ip_address VARCHAR(45) NULL'
        ];

        foreach ($logColumnAdds as $columnDef) {
            $columnName = preg_replace('/ .*$/', '', $columnDef);
            if (!in_array($columnName, $existingLogColumns)) {
                $conn->query("ALTER TABLE activity_logs ADD COLUMN {$columnDef}");
                $messages[] = "✓ Added {$columnName} column to activity_logs";
            }
        }
    }

    $_SESSION['setup_messages'] = $messages;
    $_SESSION['setup_errors'] = [];
} catch (Exception $e) {
    $errors[] = "Error: " . $e->getMessage();
    $_SESSION['setup_errors'] = $errors;
}


    $categoryTables = $conn->query("SHOW TABLES LIKE 'categories'");
    if (!$categoryTables || $categoryTables->num_rows === 0) {
        $createCategoriesTable = "
        CREATE TABLE categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL UNIQUE,
            description TEXT NULL,
            status VARCHAR(50) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
        ";
        $conn->query($createCategoriesTable);
        $messages[] = "✓ Created categories table";
    } else {
        $messages[] = "• categories table already exists";
    }


    $supplierTables = $conn->query("SHOW TABLES LIKE 'suppliers'");
    if (!$supplierTables || $supplierTables->num_rows === 0) {
        $createSuppliersTable = "
        CREATE TABLE suppliers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            company_name VARCHAR(255) NOT NULL UNIQUE,
            contact_person VARCHAR(255) NULL,
            address TEXT NULL,
            email VARCHAR(255) NULL,
            mobile_number VARCHAR(50) NULL,
            status VARCHAR(50) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
        ";
        $conn->query($createSuppliersTable);
        $messages[] = "✓ Created suppliers table";
    } else {
        $messages[] = "• suppliers table already exists";
    }


    $productTables = $conn->query("SHOW TABLES LIKE 'products'");
    if (!$productTables || $productTables->num_rows === 0) {
        $createProductsTable = "
        CREATE TABLE products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT NULL,
            category_id INT NULL,
            supplier_id INT NULL,
            price DECIMAL(10, 2) NOT NULL,
            cost DECIMAL(10, 2) NULL,
            stock_quantity INT DEFAULT 0,
            reorder_level INT DEFAULT 0,
            unit_of_measure VARCHAR(50) NULL,
            image_url VARCHAR(255) NULL,
            qr_code_path VARCHAR(255) NULL,
            status VARCHAR(50) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
            FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
        )
        ";
        $conn->query($createProductsTable);
        $messages[] = "✓ Created products table";
    } else {
        $messages[] = "• products table already exists";
    }

    $productVariantsTables = $conn->query("SHOW TABLES LIKE 'product_variants'");
    if (!$productVariantsTables || $productVariantsTables->num_rows === 0) {
        $createProductVariantsTable = "
        CREATE TABLE product_variants (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            variant_name VARCHAR(255) NOT NULL, -- e.g., Size, Color
            variant_value VARCHAR(255) NOT NULL, -- e.g., Small, Red
            stock_quantity INT DEFAULT 0,
            price_adjustment DECIMAL(10, 2) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )
        ";
        $conn->query($createProductVariantsTable);
        $messages[] = "✓ Created product_variants table";
    } else {
        $messages[] = "• product_variants table already exists";
    }
    $purchaseOrderTables = $conn->query("SHOW TABLES LIKE 'purchase_orders'");
    if (!$purchaseOrderTables || $purchaseOrderTables->num_rows === 0) {
        $createPurchaseOrdersTable = "
        CREATE TABLE purchase_orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            supplier_id INT NOT NULL,
            order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            expected_delivery_date DATE NULL,
            status VARCHAR(50) DEFAULT 'pending',
            total_amount DECIMAL(10, 2) DEFAULT 0.00,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
        )
        ";
        $conn->query($createPurchaseOrdersTable);
        $messages[] = "✓ Created purchase_orders table";
    } else {
        $messages[] = "• purchase_orders table already exists";
    }

    $purchaseOrderItemTables = $conn->query("SHOW TABLES LIKE 'purchase_order_items'");
    if (!$purchaseOrderItemTables || $purchaseOrderItemTables->num_rows === 0) {
        $createPurchaseOrderItemsTable = "
        CREATE TABLE purchase_order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            purchase_order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            unit_price DECIMAL(10, 2) NOT NULL,
            subtotal DECIMAL(10, 2) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )
        ";
        $conn->query($createPurchaseOrderItemsTable);
        $messages[] = "✓ Created purchase_order_items table";
    } else {
        $messages[] = "• purchase_order_items table already exists";
    }
    $deliveryReceiptTables = $conn->query("SHOW TABLES LIKE 'delivery_receipts'");
    if (!$deliveryReceiptTables || $deliveryReceiptTables->num_rows === 0) {
        $createDeliveryReceiptsTable = "
        CREATE TABLE delivery_receipts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            purchase_order_id INT NULL,
            supplier_id INT NOT NULL,
            receipt_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            received_by INT NULL, -- User ID of the person who received the delivery
            status VARCHAR(50) DEFAULT 'received',
            notes TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE SET NULL,
            FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
            FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
        )
        ";
        $conn->query($createDeliveryReceiptsTable);
        $messages[] = "✓ Created delivery_receipts table";
    } else {
        $messages[] = "• delivery_receipts table already exists";
    }

    $deliveryReceiptItemTables = $conn->query("SHOW TABLES LIKE 'delivery_receipt_items'");
    if (!$deliveryReceiptItemTables || $deliveryReceiptItemTables->num_rows === 0) {
        $createDeliveryReceiptItemsTable = "
        CREATE TABLE delivery_receipt_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            delivery_receipt_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity_ordered INT NOT NULL,
            quantity_received INT NOT NULL,
            unit_cost DECIMAL(10, 2) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (delivery_receipt_id) REFERENCES delivery_receipts(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )
        ";
        $conn->query($createDeliveryReceiptItemsTable);
        $messages[] = "✓ Created delivery_receipt_items table";
    } else {
        $messages[] = "• delivery_receipt_items table already exists";
    }
    $customerTables = $conn->query("SHOW TABLES LIKE 'customers'");
    if (!$customerTables || $customerTables->num_rows === 0) {
        $createCustomersTable = "
        CREATE TABLE customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            firstname VARCHAR(255) NOT NULL,
            lastname VARCHAR(255) NOT NULL,
            email VARCHAR(255) NULL UNIQUE,
            mobile VARCHAR(50) NULL,
            address TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
        ";
        $conn->query($createCustomersTable);
        $messages[] = "✓ Created customers table";
    } else {
        $messages[] = "• customers table already exists";
    }
    $salesTables = $conn->query("SHOW TABLES LIKE 'sales'");
    if (!$salesTables || $salesTables->num_rows === 0) {
        $createSalesTable = "
        CREATE TABLE sales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NULL,
            sale_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            total_amount DECIMAL(10, 2) NOT NULL,
            payment_status VARCHAR(50) DEFAULT 'pending',
            invoice_number VARCHAR(100) NULL UNIQUE,
            created_by INT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )
        ";
        $conn->query($createSalesTable);
        $messages[] = "✓ Created sales table";
    } else {
        $messages[] = "• sales table already exists";
    }

    $saleItemsTables = $conn->query("SHOW TABLES LIKE 'sale_items'");
    if (!$saleItemsTables || $saleItemsTables->num_rows === 0) {
        $createSaleItemsTable = "
        CREATE TABLE sale_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sale_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            unit_price DECIMAL(10, 2) NOT NULL,
            subtotal DECIMAL(10, 2) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )
        ";
        $conn->query($createSaleItemsTable);
        $messages[] = "✓ Created sale_items table";
    } else {
        $messages[] = "• sale_items table already exists";
    }
    $returnsTables = $conn->query("SHOW TABLES LIKE 'returns'");
    if (!$returnsTables || $returnsTables->num_rows === 0) {
        $createReturnsTable = "
        CREATE TABLE returns (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sale_id INT NULL,
            customer_id INT NULL,
            return_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            reason TEXT NULL,
            status VARCHAR(50) DEFAULT 'pending',
            processed_by INT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE SET NULL,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
            FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
        )
        ";
        $conn->query($createReturnsTable);
        $messages[] = "✓ Created returns table";
    } else {
        $messages[] = "• returns table already exists";
    }

    $returnItemsTables = $conn->query("SHOW TABLES LIKE 'return_items'");
    if (!$returnItemsTables || $returnItemsTables->num_rows === 0) {
        $createReturnItemsTable = "
        CREATE TABLE return_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            return_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            condition_on_return TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (return_id) REFERENCES returns(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )
        ";
        $conn->query($createReturnItemsTable);
        $messages[] = "✓ Created return_items table";
    } else {
        $messages[] = "• return_items table already exists";
    }

    $backupHistoryTables = $conn->query("SHOW TABLES LIKE 'backup_history'");
    if (!$backupHistoryTables || $backupHistoryTables->num_rows === 0) {
        $createBackupHistoryTable = "
        CREATE TABLE backup_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            backup_name VARCHAR(255),
            backup_type ENUM('Database','Full') DEFAULT 'Database',
            file_name VARCHAR(255),
            file_path VARCHAR(255),
            file_size VARCHAR(50),
            created_by INT,
            created_role ENUM('Super Admin','Admin'),
            backup_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            status ENUM('Completed','Failed'),
            remarks TEXT,
            FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
        )
        ";
        $conn->query($createBackupHistoryTable);
        $messages[] = "✓ Created backup_history table";
    } else {
        $messages[] = "• backup_history table already exists";
    }

    // ===================================================================
    // NOTIFICATIONS TABLE (Notification System)
    // ===================================================================
    $notifTable = $conn->query("SHOW TABLES LIKE 'notifications'");
    if (!$notifTable || $notifTable->num_rows === 0) {
        $createNotificationsTable = "
        CREATE TABLE notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NULL,
            link VARCHAR(255) DEFAULT NULL,
            type ENUM('Low Stock','Delivery','Sales','Backup','Security','System') NOT NULL DEFAULT 'System',
            priority TINYINT NOT NULL DEFAULT 0,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_user_read (user_id, is_read),
            INDEX idx_priority (priority),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
        ";
        $conn->query($createNotificationsTable);
        $messages[] = "✓ Created notifications table";
    } else {
        $messages[] = "• notifications table already exists";

        $existingNotifColumns = [];
        $notifColumns = $conn->query("SHOW COLUMNS FROM notifications");
        if ($notifColumns) {
            while ($col = $notifColumns->fetch_assoc()) {
                $existingNotifColumns[] = $col['Field'];
            }
        }

        // Add missing columns for older installations
        if (!in_array('link', $existingNotifColumns)) {
            $conn->query("ALTER TABLE notifications ADD COLUMN link VARCHAR(255) DEFAULT NULL AFTER message");
            $messages[] = "✓ Added link column to notifications";
        }
        if (!in_array('priority', $existingNotifColumns)) {
            $conn->query("ALTER TABLE notifications ADD COLUMN priority TINYINT NOT NULL DEFAULT 0 AFTER type");
            $messages[] = "✓ Added priority column to notifications";
        }

        // Extend type enum to support Delivery / Sales
        $notifTypeCol = $conn->query("SHOW COLUMNS FROM notifications LIKE 'type'");
        if ($notifTypeCol && $notifTypeRow = $notifTypeCol->fetch_assoc()) {
            if (stripos($notifTypeRow['Type'], 'Delivery') === false) {
                $conn->query("ALTER TABLE notifications MODIFY type ENUM('Low Stock','Delivery','Sales','Backup','Security','System') NOT NULL DEFAULT 'System'");
                $messages[] = "✓ Extended notifications type enum";
            }
        }
    }

    // Now that all checks are done, set session variables and redirect
    $_SESSION['setup_messages'] = $messages;
    $_SESSION['setup_errors'] = $errors;
    header("Location: setup_complete.php");
    exit();
