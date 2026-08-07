<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Recovery - ISU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/signup.css">
    <style>
        #strength-bar {
            transition: width 0.3s ease;
        }
    </style>
</head>

<?php
session_start();
require_once "config/database.php";
require_once "includes/auth_helpers.php";
require_once "includes/activity_log.php";

$userId = intval($_GET['user_id'] ?? 0);
$selectedUser = null;
$error = '';
$success = '';
$step = 'recovery';

if ($userId <= 0) {
    header("Location: forgot_password.php");
    exit();
}

$userStmt = $conn->prepare("SELECT id, firstname, lastname, username, email FROM users WHERE id = ? LIMIT 1");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userResult->num_rows === 0) {
    header("Location: forgot_password.php");
    exit();
}

$selectedUser = $userResult->fetch_assoc();

if (isRecoveryLocked($conn, $userId)) {
    $error = 'Too many failed attempts. Account recovery is locked for 30 minutes.';
    $_SESSION['recovery_locked'] = true;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'verify_recovery') {
        $recoverySecret = $_POST['recovery_secret'] ?? '';
        $recoveryCode = $_POST['recovery_code'] ?? '';

        $verifStmt = $conn->prepare("SELECT recovery_password, recovery_code FROM users WHERE id = ?");
        $verifStmt->bind_param("i", $userId);
        $verifStmt->execute();
        $verifResult = $verifStmt->get_result();
        $verifRow = $verifResult->fetch_assoc();

        if (password_verify($recoverySecret, $verifRow['recovery_password']) && $recoveryCode === $verifRow['recovery_code']) {
            resetRecoveryAttempts($conn, $userId);
            logActivity(
                $conn,
                $userId,
                $selectedUser['firstname'] . " " . $selectedUser['lastname'],
                $selectedUser['username'],
                "Super Admin",
                "Recovery Successful"
            );
            $_SESSION['recovery_verified'] = true;
            $_SESSION['recovery_user_id'] = $userId;
            $_SESSION['recovery_username'] = $selectedUser['username'];
            $step = 'reset_password';
        } else {
            $result = incrementRecoveryAttempts($conn, $userId);
            if ($result === 'locked') {
                logActivity(
                    $conn,
                    $userId,
                    $selectedUser['firstname'] . " " . $selectedUser['lastname'],
                    $selectedUser['username'],
                    "Super Admin",
                    "Recovery Locked - Too many failed attempts"
                );
                $error = 'Too many failed attempts. Account recovery is locked for 30 minutes.';
            } else {
                $error = 'Incorrect Recovery Secret or Recovery Code.';
                logActivity(
                    $conn,
                    $userId,
                    $selectedUser['firstname'] . " " . $selectedUser['lastname'],
                    $selectedUser['username'],
                    "Super Admin",
                    "Recovery Failed"
                );
            }
        }
    } elseif ($action === 'reset_password') {
        if (!isset($_SESSION['recovery_verified']) || $_SESSION['recovery_user_id'] !== $userId) {
            header("Location: forgot_password.php");
            exit();
        }

        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($newPassword === '' || $confirmPassword === '') {
            $error = 'Please enter and confirm your new password.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } else {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $resetStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $resetStmt->bind_param("si", $newHash, $userId);

            if ($resetStmt->execute()) {
                logActivity(
                    $conn,
                    $userId,
                    $selectedUser['firstname'] . " " . $selectedUser['lastname'],
                    $selectedUser['username'],
                    "Super Admin",
                    "Password Changed via Recovery"
                );
                $_SESSION['recovery_verified'] = false;
                unset($_SESSION['recovery_user_id']);
                unset($_SESSION['recovery_username']);
                $success = 'Password successfully reset! You can now log in with your new password.';
                $step = 'success';
            } else {
                $error = 'Failed to reset password. Please try again.';
            }
        }
    }
}

if (isset($_SESSION['recovery_verified']) && $_SESSION['recovery_verified'] === true) {
    $step = 'reset_password';
}
?>


<body>
    <div class="container main-container d-flex align-items-center justify-content-center">
        <div class="row w-100 align-items-center g-5">
            <div class="col-lg-5 d-none d-lg-block left-panel">
                <div class="text-center text-lg-start">
                    <img src="assets/images/logo.png" alt="ISU Logo" class="logo mb-3">
                    <h1 class="brand-title">ISABELA STATE UNIVERSITY</h1>
                    <p class="brand-subtitle mb-4">Cauayan Campus (CBAO)</p>
                    <h2 class="system-title mb-3">Super Admin Account Recovery</h2>
                    <p class="system-desc mb-5">Use your Recovery Secret Password and Recovery Code to regain access to your account.</p>
                    <div class="feature-item d-flex align-items-start mb-4">
                        <div class="feature-icon"><i class="bi bi-key-fill"></i></div>
                        <div>
                            <h5>Two-Factor Verification</h5>
                            <p>Verify using both your Recovery Secret and unique Recovery Code.</p>
                        </div>
                    </div>
                    <div class="feature-item d-flex align-items-start mb-4">
                        <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                        <div>
                            <h5>Secure Reset</h5>
                            <p>Your account password is securely hashed before storage.</p>
                        </div>
                    </div>
                    <div class="feature-item d-flex align-items-start">
                        <div class="feature-icon"><i class="bi bi-lock"></i></div>
                        <div>
                            <h5>Protected Access</h5>
                            <p>Too many failed attempts will lock recovery for 30 minutes.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 d-flex justify-content-center">
                <div class="signup-card w-100">
                    <?php if ($step === 'success'): ?>
                        <div class="text-center mb-4">
                            <div class="top-icon mx-auto mb-3" style="font-size: 48px; color: #28a745;">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <h2 class="form-title">Password Reset Successful</h2>
                        </div>
                        <div class="alert alert-success mb-4">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                        <a href="login.php" class="btn btn-success w-100 mb-3">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Go to Login
                        </a>
                    <?php else: ?>
                        <div class="text-center mb-4">
                            <div class="top-icon mx-auto mb-3">
                                <i class="bi bi-key-fill"></i>
                            </div>
                            <h2 class="form-title">Account Recovery</h2>
                            <p class="text-muted text-sm">Super Admin: <?php echo htmlspecialchars($selectedUser['username']); ?></p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger text-center">
                                <i class="bi bi-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($step === 'recovery'): ?>
                            <form action="recover_account.php?user_id=<?php echo urlencode($userId); ?>" method="POST">
                                <input type="hidden" name="action" value="verify_recovery">

                                <div class="role-assignment-box mb-4">
                                    <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-info-subtle">
                                        <i class="bi bi-shield-check text-info fs-5 me-2"></i>
                                        <h6 class="mb-0 fw-bold text-info" style="font-size: 15px;">Verify Identity</h6>
                                    </div>
                                    <p class="text-muted small mb-3">Enter your Recovery Secret Password and Recovery Code that you saved during account creation.</p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-muted small">Recovery Secret Password</label>
                                    <div class="input-icon-wrapper">
                                        <i class="bi bi-key left-icon"></i>
                                        <input type="password" name="recovery_secret" class="form-control custom-input" placeholder="Enter your Recovery Secret Password" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label text-muted small">Recovery Code</label>
                                    <div class="input-icon-wrapper">
                                        <i class="bi bi-qr-code left-icon"></i>
                                        <input type="text" name="recovery_code" class="form-control custom-input" placeholder="e.g., A8XQ-92PK-77LM" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-info w-100 submit-btn mb-3">
                                    <i class="bi bi-check-circle me-2"></i> Verify and Proceed
                                </button>
                            </form>
                        <?php elseif ($step === 'reset_password'): ?>
                            <form action="recover_account.php?user_id=<?php echo urlencode($userId); ?>" method="POST">
                                <input type="hidden" name="action" value="reset_password">

                                <div class="role-assignment-box mb-4">
                                    <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-success-subtle">
                                        <i class="bi bi-shield-fill-check text-success fs-5 me-2"></i>
                                        <h6 class="mb-0 fw-bold text-success" style="font-size: 15px;">Create New Password</h6>
                                    </div>
                                    <p class="text-muted small">Your identity has been verified. Now create a strong new password.</p>
                                </div>

                                <div class="mb-3">
                                    <div class="input-icon-wrapper">
                                        <i class="bi bi-lock left-icon"></i>
                                        <input type="password" id="new-password" name="new_password" class="form-control custom-input" placeholder="New Password" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="input-icon-wrapper">
                                        <i class="bi bi-lock-fill left-icon"></i>
                                        <input type="password" id="confirm-password" name="confirm_password" class="form-control custom-input" placeholder="Confirm Password" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label text-muted small">Password Strength</label>
                                    <div class="progress" style="height: 10px;">
                                        <div id="strength-bar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                                    </div>
                                    <div id="strength-text" class="mt-2 text-muted" style="font-size: 13px;">Enter a password to see strength.</div>
                                </div>

                                <button type="submit" class="btn btn-success w-100 submit-btn">
                                    <i class="bi bi-check-circle me-2"></i> Reset Password
                                </button>
                            </form>
                        <?php endif; ?>

                        <div class="text-center form-footer mt-4 border-top pt-4">
                            <a href="forgot_password.php" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i> Back
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($step === 'reset_password'): ?>
    <script>
        const passwordInput = document.getElementById('new-password');
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');

        function evaluatePassword(value) {
            let score = 0;
            if (value.length >= 8) score += 1;
            if (/[A-Z]/.test(value)) score += 1;
            if (/[a-z]/.test(value)) score += 1;
            if (/[0-9]/.test(value)) score += 1;
            if (/[^A-Za-z0-9]/.test(value)) score += 1;
            return score;
        }

        function updateStrength() {
            const value = passwordInput.value;
            const score = evaluatePassword(value);
            const percent = Math.min(100, score * 20);
            let label = 'Very Weak';
            let color = 'bg-danger';

            if (score >= 4) {
                label = 'Strong';
                color = 'bg-success';
            } else if (score === 3) {
                label = 'Medium';
                color = 'bg-warning';
            } else if (score === 2) {
                label = 'Weak';
                color = 'bg-danger';
            }

            strengthBar.style.width = percent + '%';
            strengthBar.className = 'progress-bar ' + color;
            strengthText.textContent = value ? label : 'Enter a password to see strength.';
        }

        passwordInput.addEventListener('input', updateStrength);
    </script>
    <?php endif; ?>
</body>
</html>
