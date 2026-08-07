<?php
session_start();
require_once "../../config/database.php";
require_once "../../includes/superadmin_auth.php";
require_once "../../includes/superadmin_functions.php";
require_once "../../includes/activity_log.php";

// Check if user is Super Admin
requireSuperAdmin();

// Get user ID
$userId = intval($_GET['id'] ?? 0);

if ($userId > 0) {
    $user = getUserById($conn, $userId);
    
    if ($user && $user['role'] !== 'Super Admin') {
        if (activateUserAccount($conn, $userId)) {
            logActivity(
                $conn,
                getCurrentUserId(),
                getCurrentUserFullName(),
                getCurrentUsername(),
                getCurrentUserRole(),
                "Activated user account: {$user['firstname']} {$user['lastname']} ({$user['email']})"
            );
        }
    }
}

header("Location: ../../pages/superadmin/users.php");
exit();
