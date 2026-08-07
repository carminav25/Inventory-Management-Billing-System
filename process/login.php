<?php
session_start();
require_once "../config/database.php";
require_once "../includes/activity_log.php";

$identity = trim($_POST['identity'] ?? '');
$password = $_POST['password'] ?? '';

$_SESSION['old_input']['identity'] = $identity;

if (empty($identity) || empty($password)) {
    $_SESSION['login_error'] = 'Please enter both username/email and password.';
    header("Location: ../login.php");
    exit();
}

$stmt = $conn->prepare("SELECT id, firstname, lastname, username, password, role, status, lock_until, is_permanently_locked FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $identity, $identity);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Check if account is disabled
    if ($user['status'] === 'Inactive') {
        $_SESSION['login_error'] = 'Your account has been disabled. Please contact Super Admin for assistance at carriz125@gmail.com.';
        logActivity(
            $conn, $user['id'], $user['firstname'] . ' ' . $user['lastname'],
            $user['username'], $user['role'],
            'Failed login attempt (account disabled)'
        );
        header("Location: ../login.php");
        exit();
    }

    // Check for permanent lock first
    if ($user['is_permanently_locked']) {
        $_SESSION['login_error'] = 'This account has been permanently locked.';
        $_SESSION['login_locked_permanent'] = true;
        logActivity(
            $conn, $user['id'], $user['firstname'] . ' ' . $user['lastname'],
            $user['username'], $user['role'],
            'Failed login attempt (account permanently locked)'
        );
        header("Location: ../login.php");
        exit();
    }

    // Check if account is locked
    if ($user['lock_until'] !== null) {
        $now = new DateTime();
        $locked_until = new DateTime($user['lock_until']);
        if ($now < $locked_until) {
            $_SESSION['login_error'] = 'Account is locked due to too many failed login attempts.';
            $_SESSION['login_locked'] = true;
            $_SESSION['login_locked_until'] = $locked_until->getTimestamp();
            logActivity(
                $conn,
                $user['id'],
                $user['firstname'] . ' ' . $user['lastname'],
                $user['username'],
                $user['role'],
                'Failed login attempt (account locked)'
            );
            header("Location: ../login.php");
            exit();
        } else {
            // Lock has expired, reset attempts
            $conn->query("UPDATE users SET failed_attempts = 0, lock_until = NULL WHERE id = " . $user['id']);
        }
    }

    if (password_verify($password, $user['password'])) {
        // Password is correct, start the session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['fullname'] = $user['firstname'] . ' ' . $user['lastname'];
        $_SESSION['role'] = $user['role'];

        // Reset failed login attempts on successful login
        $conn->query("UPDATE users SET failed_attempts = 0, lock_until = NULL WHERE id = " . $user['id']);

        // Clear old input on successful login
        unset($_SESSION['old_input']);

        logActivity(
            $conn,
            $user['id'],
            $_SESSION['fullname'],
            $user['username'],
            $user['role'],
            'Logged in successfully'
        );

        // Redirect based on role
        if ($user['role'] === 'Super Admin') {
            header("Location: ../pages/superadmin/index.php");
        } elseif ($user['role'] === 'Admin') {
            header("Location: ../pages/admin/index.php");
        } elseif ($user['role'] === 'Viewer') {
            header("Location: ../pages/viewer/index.php");
        } else {
            header("Location: ../pages/dashboard.php");
        }
        exit();
    } else {
        // User found, but password was incorrect.
        $_SESSION['login_error'] = 'Invalid username, email, or password.';

        // Increment failed login attempts
        $conn->query("UPDATE users SET failed_attempts = failed_attempts + 1 WHERE id = " . $user['id']);
        $checkStmt = $conn->query("SELECT failed_attempts FROM users WHERE id = " . $user['id']);
        $attempts = (int)$checkStmt->fetch_assoc()['failed_attempts'];

        $logAction = 'Failed login attempt (incorrect password)';

        if ($attempts >= 3) {
            // Set permanent lock
            $conn->query("UPDATE users SET is_permanently_locked = 1, lock_until = NULL WHERE id = " . $user['id']);
            $_SESSION['login_error'] = 'This account has been permanently locked due to too many failed attempts.';
            $_SESSION['login_locked_permanent'] = true;
            $logAction = 'Failed login attempt (account permanently locked)';
        } else {
            // Attempts 1 and 2 warning
            $remaining_attempts = 3 - $attempts;
            $plural = $remaining_attempts > 1 ? 's' : '';
            $_SESSION['login_error'] = "Incorrect password. You have {$remaining_attempts} attempt{$plural} remaining before your account is locked.";
        }

        logActivity(
            $conn,
            $user['id'],
            $user['firstname'] . ' ' . $user['lastname'],
            $user['username'],
            $user['role'],
            $logAction
        );
        header("Location: ../login.php");
        exit();
    }
}

// If we get here, it means the user was not found.
$_SESSION['login_error'] = 'Invalid username, email, or password.';

// Per user request, do not log failed login attempts for non-existent users.
// logActivity(
//     $conn, 0, 'Unknown', $identity, null, 'Failed login attempt (user not found)'
// );

header("Location: ../login.php");
exit();