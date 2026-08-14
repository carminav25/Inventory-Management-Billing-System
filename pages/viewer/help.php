<?php
session_start();
require_once "../../config/database.php";
require_once "../../includes/viewer_auth.php";

requireViewer();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Help Center - Viewer Dashboard</title>
    <style>
        body { font-family: Arial; background: #f5f7fa; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #333; }
        .back-link { color: #0B7A4B; text-decoration: none; }
        .section { margin: 30px 0; }
        .section h2 { color: #333; margin-top: 20px; }
        .faq { margin: 15px 0; }
        .faq-q { font-weight: bold; color: #0B7A4B; }
        .faq-a { color: #666; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Help Center</h1>
        <p><a href="index.php" class="back-link">← Back to Dashboard</a></p>
        
        <div class="section">
            <h2>User Guide</h2>
            <p>Welcome to the Inventory Management System Viewer. This guide will help you navigate the system.</p>
            
            <h3>Dashboard</h3>
            <p>The dashboard shows you an overview of the inventory status, including available stock, low stock items, and out of stock products.</p>
            
            <h3>Product Directory</h3>
            <p>Browse all available products, search by name, and filter by category. You can view detailed information about each product.</p>
            
            <h3>Inventory Status</h3>
            <p>View the current inventory status, including low stock and out of stock items.</p>
            
            <h3>Reports</h3>
            <p>Access read-only reports about inventory, stock status, and product information.</p>
        </div>
        
        <div class="section">
            <h2>FAQs</h2>
            
            <div class="faq">
                <div class="faq-q">Q: Can I modify product information?</div>
                <div class="faq-a">A: No, as a Viewer, you have read-only access. You can only view information.</div>
            </div>
            
            <div class="faq">
                <div class="faq-q">Q: How do I search for products?</div>
                <div class="faq-a">A: Go to the Product Directory and use the search box to find products by name.</div>
            </div>
            
            <div class="faq">
                <div class="faq-q">Q: Can I export reports?</div>
                <div class="faq-a">A: Report export functionality may be available. Contact your administrator for details.</div>
            </div>
            
            <div class="faq">
                <div class="faq-q">Q: How do I change my password?</div>
                <div class="faq-a">A: Go to your Profile page to change your password and personal information.</div>
            </div>
        </div>
        
        <div class="section">
            <h2>Contact Support</h2>
            <p>If you need further assistance, please contact your system administrator.</p>
        </div>
    </div>
</body>
</html>
