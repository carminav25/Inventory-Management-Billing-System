<?php
session_start();
require_once "../../config/database.php";
require_once "../../includes/viewer_auth.php";

requireViewer();
?>
<!DOCTYPE html>
<html>
<head>
    <title>QR Code Scanner - Viewer Dashboard</title>
    <style>
        body { font-family: Arial; background: #f5f7fa; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #333; }
        .back-link { color: #0B7A4B; text-decoration: none; }
        .info { background: #ecfdf5; border-left: 4px solid #0B7A4B; padding: 15px; margin: 20px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>QR Code Scanner</h1>
        <p><a href="index.php" class="back-link">← Back to Dashboard</a></p>
        
        <div class="info">
            <strong>QR Code Scanner:</strong> Use this feature to scan product QR codes and view product information.
        </div>
        
        <p>QR code scanning functionality coming soon...</p>
    </div>
</body>
</html>
