<?php
session_start();

// Use __DIR__ for robust, absolute pathing
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/includes/activity_log.php";

// Log the activity before destroying the session
if (isset($_SESSION['user_id'])) {
    logActivity(
        $conn,
        $_SESSION['user_id'],
        $_SESSION['fullname'] ?? 'Unknown User',
        $_SESSION['username'] ?? '',
        $_SESSION['role'] ?? 'Unknown',
        "Logged out"
    );
}

// Unset all session variables for a clean logout
$_SESSION = array();

// Destroy the session completely
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
session_destroy();

// Redirect to the login page in the root directory
header("Location: login.php");
exit();