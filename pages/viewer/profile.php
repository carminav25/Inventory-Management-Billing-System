<?php
session_start();
require_once "../../config/database.php";
require_once "../../includes/viewer_auth.php";

requireViewer();

$userId = getCurrentUserId();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile - Viewer Dashboard</title>
    <style>
        body { font-family: Arial; background: #f5f7fa; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #333; }
        .back-link { color: #0B7A4B; text-decoration: none; }
        .profile-info { margin: 20px 0; }
        .info-row { margin: 10px 0; }
        .label { font-weight: bold; color: #666; }
    </style>
    <link rel="stylesheet" href="../../assets/css/semantic-theme.css">
</head>
<body class="viewer-shell">
    <div class="container">
        <h1>My Profile</h1>
        <p><a href="index.php" class="back-link">← Back to Dashboard</a></p>
        
        <div class="profile-info">
            <div class="info-row">
                <span class="label">Name:</span> <?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?>
            </div>
            <div class="info-row">
                <span class="label">Username:</span> <?php echo htmlspecialchars($user['username']); ?>
            </div>
            <div class="info-row">
                <span class="label">Email:</span> <?php echo htmlspecialchars($user['email']); ?>
            </div>
            <div class="info-row">
                <span class="label">Mobile:</span> <?php echo htmlspecialchars($user['mobile']); ?>
            </div>
            <div class="info-row">
                <span class="label">Role:</span> <?php echo htmlspecialchars($user['role']); ?>
            </div>
            <div class="info-row">
                <span class="label">Member Since:</span> <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
            </div>
        </div>
    </div>
</body>
</html>
