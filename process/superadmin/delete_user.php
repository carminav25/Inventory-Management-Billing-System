<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

require_once "../../config/database.php";
require_once "../../includes/superadmin_auth.php";
require_once "../../includes/activity_log.php";

// Ensure only Super Admin can perform this action
if (function_exists('requireSuperAdmin')) {
    requireSuperAdmin();
}

$redirectURL = '../../pages/superadmin/users.php';

if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "No user ID specified.";
    header("Location: {$redirectURL}");
    exit();
}

$userId = intval($_GET['id']);
$currentUserId = function_exists('getCurrentUserId') ? getCurrentUserId() : 0;

// Prevent deleting the currently logged-in user
if ($userId === $currentUserId) {
    $_SESSION['error_message'] = "You cannot delete your own account.";
    header("Location: {$redirectURL}");
    exit();
}

// Fetch user details to check role and for logging
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userToDelete = $result->fetch_assoc();

if (!$userToDelete) {
    $_SESSION['error_message'] = "User not found.";
    header("Location: {$redirectURL}");
    exit();
}

// Prevent deleting other Super Admins
if ($userToDelete['role'] === 'Super Admin') {
    $_SESSION['error_message'] = "Super Admin accounts cannot be deleted.";
    header("Location: {$redirectURL}");
    exit();
}

// Start a transaction
$conn->begin_transaction();

try {
    // 1. Soft-delete the user: update status, clear personal info, and reset lock status.
    // This preserves the user ID for foreign key integrity in activity_logs.
    $deletedUsername = "deleted_" . $userId;
    $deletedEmail = "deleted_" . $userId . "@deleted.local";
    $softDeleteStmt = $conn->prepare("
        UPDATE users
        SET
            status = 'Deleted',
            username = ?,
            firstname = 'Deleted',
            lastname = 'User',
            email = ?,
            mobile = NULL,
            failed_attempts = 0,
            lock_until = NULL,
            is_permanently_locked = 0
        WHERE id = ?
    ");
    $softDeleteStmt->bind_param("ssi", $deletedUsername, $deletedEmail, $userId);
    $softDeleteStmt->execute();

    // Commit the transaction
    $conn->commit();

    // Log the action safely
    if (function_exists('logActivity')) {
        $logUser = function_exists('getCurrentUserFullName') ? getCurrentUserFullName() : 'Super Admin';
        $logUsername = function_exists('getCurrentUsername') ? getCurrentUsername() : 'superadmin';
        $logRole = function_exists('getCurrentUserRole') ? getCurrentUserRole() : 'Super Admin';
        logActivity($conn, $currentUserId, $logUser, $logUsername, $logRole, "Soft-deleted user: " . $userToDelete['username'] . " (ID: " . $userId . ")");
    }

    $_SESSION['success_message'] = "User '" . htmlspecialchars($userToDelete['username']) . "' has been successfully deleted and their data anonymized.";
    header("Location: {$redirectURL}");
    exit();

} catch (Throwable $exception) {
    $conn->rollback(); // Roll back changes on error
    
    // STOP AND DISPLAY THE EXACT ERROR INSTEAD OF REDIRECTING SILENTLY
    echo "<div style='background: #fef2f2; color: #991b1b; padding: 20px; font-family: monospace; border: 1px solid #fecaca; margin: 20px; border-radius: 12px;'>";
    echo "<h3>Deletion Failed (Debug View)</h3>";
    echo "<p><strong>Error Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . $exception->getFile() . " on line " . $exception->getLine() . "</p>";
    echo "<br><a href='{$redirectURL}' style='color: #0B7A4B; font-weight: 700;'>&larr; Return to Users Page</a>";
    echo "</div>";
    exit();
}
