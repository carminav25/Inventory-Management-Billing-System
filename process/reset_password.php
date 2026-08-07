<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!in_array($_SESSION['role'], ["Super Admin", "Admin"])) {
    header("Location: ../pages/superadmin/index.php");
    exit();
}

require_once "../config/database.php";
require_once "../includes/activity_log.php";

$userId = intval($_POST['user_id'] ?? 0);
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

$redirect = "../pages/superadmin/reset_password.php";
if ($userId > 0) {
    $redirect .= "?user_id={$userId}";
}

if ($userId <= 0) {
    $_SESSION['reset_error'] = 'Invalid user selected for password reset.';
    header("Location: {$redirect}");
    exit();
}

if ($newPassword === '' || $confirmPassword === '') {
    $_SESSION['reset_error'] = 'Please enter the new password and confirm it.';
    header("Location: {$redirect}");
    exit();
}

if ($newPassword !== $confirmPassword) {
    $_SESSION['reset_error'] = 'Passwords do not match.';
    header("Location: {$redirect}");
    exit();
}

if (strlen($newPassword) < 8) {
    $_SESSION['reset_error'] = 'Password must be at least 8 characters long.';
    header("Location: {$redirect}");
    exit();
}

$userStmt = $conn->prepare("SELECT id, username, firstname, lastname, role, recovery_password FROM users WHERE id = ? LIMIT 1");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userResult->num_rows === 0) {
    $_SESSION['reset_error'] = 'Unable to find the selected user account.';
    header("Location: {$redirect}");
    exit();
}

$targetUser = $userResult->fetch_assoc();

// Security Check: Prevent Admin from resetting Super Admin's password
if ($_SESSION['role'] === 'Admin' && $targetUser['role'] === 'Super Admin') {
    $_SESSION['reset_error'] = 'Admins are not permitted to reset the password of a Super Admin.';
    header("Location: {$redirect}");
    exit();
}

// Security Check: Require recovery secret for Super Admin to reset another Super Admin
if ($_SESSION['role'] === 'Super Admin' && $targetUser['role'] === 'Super Admin' && $targetUser['id'] !== $_SESSION['user_id']) {
    $recoverySecret = $_POST['recovery_secret'] ?? '';
    if (empty($recoverySecret)) {
        $_SESSION['reset_error'] = 'The Recovery Secret is required to reset another Super Admin\'s password.';
        header("Location: {$redirect}");
        exit();
    }
    if ($recoverySecret !== $targetUser['recovery_password']) {
        $_SESSION['reset_error'] = 'The provided Recovery Secret is incorrect.';
        header("Location: {$redirect}");
        exit();
    }
}

$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

$updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$updateStmt->bind_param("si", $newHash, $userId);


if ($updateStmt->execute()) {
    logActivity(
        $conn,
        $_SESSION['user_id'] ?? 0,
        $_SESSION['fullname'] ?? 'Unknown User',
        $_SESSION['username'] ?? '',
        $_SESSION['role'] ?? 'Unknown',
        'Reset password for ' . $targetUser['username']
    );
    $_SESSION['reset_success'] = 'Password Reset Successful. The user can now log in using the new password.';
} else {
    $_SESSION['reset_error'] = 'Failed to update the password. Please try again.';
}

header("Location: {$redirect}");
exit();
