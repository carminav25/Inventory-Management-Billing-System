<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Complete - ISU Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .setup-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
        }
        .setup-title {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 700;
        }
        .message-item {
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .message-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .message-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            justify-content: center;
        }
        .action-buttons a {
            flex: 1;
            text-align: center;
        }
    </style>
</head>

<?php
session_start();
$messages = $_SESSION['setup_messages'] ?? [];
$errors = $_SESSION['setup_errors'] ?? [];
unset($_SESSION['setup_messages']);
unset($_SESSION['setup_errors']);
?>


<body>
    <div class="setup-card">
        <h2 class="setup-title">
            <i class="bi bi-check-circle-fill text-success me-2"></i> Setup Status
        </h2>

        <div class="messages">
            <?php foreach ($messages as $msg): ?>
                <div class="message-item <?php echo strpos($msg, '✓') !== false ? 'message-success' : 'message-info'; ?>">
                    <?php if (strpos($msg, '✓') !== false): ?>
                        <i class="bi bi-check-circle-fill"></i>
                    <?php else: ?>
                        <i class="bi bi-info-circle-fill"></i>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($msg); ?>
                </div>
            <?php endforeach; ?>

            <?php foreach ($errors as $err): ?>
                <div class="message-item message-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?php echo htmlspecialchars($err); ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="action-buttons">
            <a href="signup.php" class="btn btn-success">
                <i class="bi bi-arrow-right me-2"></i> Go to Signup
            </a>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-house me-2"></i> Home
            </a>
        </div>
    </div>
</body>
</html>
