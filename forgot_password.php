<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - ISU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/signup.css">
</head>

<?php
session_start();
require_once "config/database.php";
require_once "includes/auth_helpers.php";

$step = $_GET['step'] ?? 'search';
$identity = trim($_POST['identity'] ?? '');
$selectedUser = null;
$superAdminContact = null;
$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'search') {
    if ($identity === '') {
        $error = 'Please enter your username or email.';
    } else {
        $stmt = $conn->prepare("SELECT id, firstname, lastname, username, email, role FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param("ss", $identity, $identity);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $selectedUser = $result->fetch_assoc();

            if ($selectedUser['role'] !== 'Super Admin') {
                $contactStmt = $conn->prepare("SELECT firstname, lastname, email FROM users WHERE role = 'Super Admin' LIMIT 1");
                $contactStmt->execute();
                $contactResult = $contactStmt->get_result();
                if ($contactResult && $contactResult->num_rows > 0) {
                    $superAdminContact = $contactResult->fetch_assoc();
                }
            }
        } else {
            $error = 'No account was found for that username or email.';
        }
    }
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
                    <h2 class="system-title mb-3">Inventory Management<br>& Billing System</h2>
                    <p class="system-desc mb-5">Find your account to recover access or reset your password.</p>
                    <div class="feature-item d-flex align-items-start mb-4">
                        <div class="feature-icon"><i class="bi bi-search"></i></div>
                        <div>
                            <h5>Search Your Account</h5>
                            <p>Use your username or email to find your account.</p>
                        </div>
                    </div>
                    <div class="feature-item d-flex align-items-start mb-4">
                        <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                        <div>
                            <h5>Role-Based Recovery</h5>
                            <p>Your recovery options depend on your account role.</p>
                        </div>
                    </div>
                    <div class="feature-item d-flex align-items-start">
                        <div class="feature-icon"><i class="bi bi-lock"></i></div>
                        <div>
                            <h5>Secure Process</h5>
                            <p>Account recovery is protected and logged.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 d-flex justify-content-center">
                <div class="signup-card w-100">
                    <div class="text-center mb-4">
                        <div class="top-icon mx-auto mb-3">
                            <i class="bi bi-key-fill"></i>
                        </div>
                        <h2 class="form-title">Forgot Password</h2>
                        <p class="text-muted text-sm">Find your account to proceed with password recovery.</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger text-center"><i class="bi bi-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if (!$selectedUser): ?>
                        <!-- SEARCH FORM -->
                        <form action="forgot_password.php?step=search" method="POST">
                            <div class="mb-3">
                                <div class="input-icon-wrapper">
                                    <i class="bi bi-person left-icon"></i>
                                    <input type="text" name="identity" class="form-control custom-input" placeholder="Username or Email" value="<?php echo htmlspecialchars($identity); ?>" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success w-100 submit-btn mb-4">Search Account</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($selectedUser): ?>
                        <div class="role-assignment-box mb-4">
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-success-subtle">
                                <i class="bi bi-check-circle-fill text-isu-green fs-5 me-2"></i>
                                <h6 class="mb-0 fw-bold text-isu-green" style="font-size: 15px;">Account Found</h6>
                            </div>
                            <div class="mb-3">
                                <strong>Username:</strong><br>
                                <?php echo htmlspecialchars($selectedUser['username']); ?>
                            </div>
                            <div class="mb-3">
                                <strong>Name:</strong><br>
                                <?php echo htmlspecialchars($selectedUser['firstname'] . ' ' . $selectedUser['lastname']); ?>
                            </div>
                            <div class="mb-3">
                                <strong>Role:</strong><br>
                                <?php 
                                    $roleBadge = 'badge-viewer';
                                    $roleIcon = 'eye';
                                    if ($selectedUser['role'] === 'Super Admin') {
                                        $roleBadge = 'badge-super-admin';
                                        $roleIcon = 'shield-fill-check';
                                    } elseif ($selectedUser['role'] === 'Admin') {
                                        $roleBadge = 'badge-admin';
                                        $roleIcon = 'person-gear';
                                    }
                                ?>
                                <span class="role-badge <?php echo $roleBadge; ?>">
                                    <i class="bi bi-<?php echo $roleIcon; ?> me-1"></i><?php echo htmlspecialchars($selectedUser['role']); ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($selectedUser['role'] === 'Super Admin'): ?>
                            <!-- SUPER ADMIN RECOVERY -->
                            <div class="alert alert-info mb-4">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Super Admin Recovery:</strong> Use your Recovery Secret Password and Recovery Code to reset.
                            </div>
                            <a href="recover_account.php?user_id=<?php echo urlencode($selectedUser['id']); ?>" class="btn btn-success w-100 submit-btn">
                                <i class="bi bi-key-fill me-2"></i> Proceed to Recovery
                            </a>
                        <?php elseif ($selectedUser['role'] === 'Admin'): ?>
                            <!-- ADMIN CANNOT RESET -->
                            <div class="alert alert-warning mb-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="text-warning" style="font-size: 24px; line-height: 1; margin-top: 3px;">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-2">Cannot Reset Password</h5>
                                        <p class="mb-2 text-muted">Admin & Viewer accounts cannot reset their own password. Please contact the Super Administrator.</p>
                                        <?php if ($superAdminContact): ?>
                                            <div class="mt-2 text-muted">
                                                <small>Super Admin contact:</small><br>
                                                <span><?php echo htmlspecialchars($superAdminContact['firstname'] . ' ' . $superAdminContact['lastname']); ?></span><br>
                                                <span><?php echo htmlspecialchars($superAdminContact['email']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>

                            <!-- VIEWER CANNOT RESET -->
                            <div class="alert alert-warning mb-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="text-warning" style="font-size: 24px; line-height: 1; margin-top: 3px;">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-2">Cannot Reset Password</h5>
                                        <p class="mb-2 text-muted">Viewer accounts cannot reset their own password. Please contact your Administrator or the Super Admin below.</p>
                                        <?php if ($superAdminContact): ?>
                                            <div class="mt-2 text-muted">
                                                <small>Super Admin contact:</small><br>
                                                <span><?php echo htmlspecialchars($superAdminContact['firstname'] . ' ' . $superAdminContact['lastname']); ?></span><br>
                                                <span><?php echo htmlspecialchars($superAdminContact['email']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="text-center mt-4">
                            <a href="forgot_password.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i> Search Again
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="text-center form-footer mt-4 border-top pt-4">
                        <span class="text-muted">Remember your password?</span>
                        <a href="login.php" class="ms-1 fw-bold">Login here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
