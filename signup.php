<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - ISU</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/signup.css">
    <link rel="stylesheet" href="assets/css/semantic-theme.css">
</head>

<?php
session_start();
require_once "config/database.php";

$signupError = $_SESSION['signup_error'] ?? '';
$oldInput = $_SESSION['old_input'] ?? [];
unset($_SESSION['signup_error'], $_SESSION['old_input']);

$userCount = 0;
$isSuperAdminSignup = false;

$result = $conn->query("SELECT COUNT(*) AS total FROM users");
if ($result) {
    $row = $result->fetch_assoc();
    $userCount = intval($row['total']);
}

$isSuperAdminSignup = ($userCount === 0);
?>

<body class="auth-shell">

    <div class="container main-container d-flex align-items-center justify-content-center">
        <div class="row w-100 align-items-center g-5">

            <div class="col-lg-5 d-none d-lg-block left-panel">
                <div class="text-center text-lg-start">
                    <img src="assets/images/logo.png" alt="ISU Logo" class="logo mb-3">
                    <h1 class="brand-title">ISABELA STATE UNIVERSITY</h1>
                    <p class="brand-subtitle mb-4">Cauayan Campus (CBAO)</p>
                    
                    <h2 class="system-title mb-3">Inventory Management<br>& Billing System</h2>
                    <p class="system-desc mb-5">Sign in or create a new account to manage the dashboard, browse the product directory, and use the transaction modules.</p>

                    <div class="feature-item d-flex align-items-start mb-4">
                        <div>
                            <h5>Account Policy & Roles</h5>
                            <p>Super Admin, Admin, and Viewer roles. The first signup becomes Super Admin, the second is Admin. All subsequent signups default to Viewer accounts.</p>
                        </div>
                    </div>
                    
                    <div class="feature-item d-flex align-items-start mb-4">
                        <div>
                            <h5>Dashboard</h5>
                            <p>See sales, inventory flow, low-stock alerts, and forecasting.</p>
                        </div>
                    </div>
                    
                    <div class="feature-item d-flex align-items-start mb-4">
                        <div>
                            <h5>Product Directory</h5>
                            <p>Browse categories, open product profiles, and manage active items.</p>
                        </div>
                    </div>

                    <div class="feature-item d-flex align-items-start">
                        <div>
                            <h5>Smart Scanning</h5>
                            <p>Use QR or barcode scanning on the delivery, sales, and returns pages.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 d-flex justify-content-center">
                <div class="signup-card w-100">
                    <div class="text-center mb-4">
                        <h2 class="form-title">Create Account</h2>
                        <?php if ($isSuperAdminSignup): ?>
                            <p class="text-muted text-sm"><i class="bi bi-shield-check me-1"></i>Super Admin Registration</p>
                        <?php elseif ($userCount === 1): ?>
                            <p class="text-muted text-sm"><i class="bi bi-person-gear me-1"></i>Admin Registration</p>
                        <?php else: ?>
                            <p class="text-muted text-sm"><i class="bi bi-eye me-1"></i>Viewer Registration</p>
                        <?php endif; ?>
                    </div>

                    <?php if ($signupError): ?>
                        <div class="alert alert-danger text-center">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?php echo htmlspecialchars($signupError); ?>
                        </div>
                    <?php endif; ?>

                    <form action="process/register.php" method="POST">
                        
                        <!-- BEAUTIFIED AUTOMATIC ROLE ASSIGNMENT BOX -->
                        <div class="role-assignment-box mb-4">
                            <!-- Header -->
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-success-subtle">
                                <i class="bi bi-shield-check text-isu-green fs-5 me-2"></i>
                                <h6 class="mb-0 fw-bold text-isu-green" style="font-size: 15px;">Automatic Role Assignment</h6>
                            </div>
                            
                            <!-- Role List -->
                            <div class="d-flex flex-column gap-2">
                                <!-- Super Admin -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted fw-medium" style="font-size: 13px;"> Primary Account</span>
                                    <i class="bi bi-arrow-right text-muted opacity-50" style="font-size: 12px;"></i>
                                    <span class="role-badge badge-super-admin">
                                        <i class="bi bi-shield-fill-check me-1"></i> Super Admin
                                    </span>
                                </div>
                                <!-- Admin -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted fw-medium" style="font-size: 13px;">Secondary Account</span>
                                    <i class="bi bi-arrow-right text-muted opacity-50" style="font-size: 12px;"></i>
                                    <span class="role-badge badge-admin">
                                        <i class="bi bi-person-gear me-1"></i> Admin
                                    </span>
                                </div>
                                <!-- Viewer -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted fw-medium" style="font-size: 13px;">Additional Accounts</span>
                                    <i class="bi bi-arrow-right text-muted opacity-50" style="font-size: 12px;"></i>
                                    <span class="role-badge badge-viewer">
                                        <i class="bi bi-eye-fill me-1"></i> Viewer
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- INPUTS -->
                        <div class="row g-3 mb-3">

                            <div class="col-sm-6">

                                <div class="input-icon-wrapper floating-field">

                                    <input
                                        type="text"
                                        id="firstname"
                                        name="firstname"
                                        class="form-control custom-input floating-input"
                                        placeholder=" "
                                        value="<?php echo htmlspecialchars($oldInput['firstname'] ?? ''); ?>"
                                        required>

                                    <label for="firstname">
                                        First Name
                                    </label>
                                </div>
                            </div>


                            <div class="col-sm-6">

                                <div class="input-icon-wrapper floating-field">

                                    <input
                                        type="text"
                                        id="lastname"
                                        name="lastname"
                                        class="form-control custom-input floating-input"
                                        placeholder=" "
                                        value="<?php echo htmlspecialchars($oldInput['lastname'] ?? ''); ?>"
                                        required>

                                    <label for="lastname">
                                        Last Name
                                    </label>
                                </div>
                            </div>

                        </div>

                        <!-- Username -->
                        <div class="mb-3">

                            <div class="input-icon-wrapper floating-field">

                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    maxlength="20"
                                    autocomplete="username"
                                    placeholder=" "
                                    value="<?php echo htmlspecialchars($oldInput['username'] ?? ''); ?>"
                                    required
                                    class="form-control custom-input floating-input">

                                <label for="username">
                                    Username
                                </label>
                            </div>

                            <small
                                id="usernameMessage"
                                class="validation-message d-none">
                            </small>

                            <small
                                id="usernameSuggestion"
                                class="username-suggestion d-none">
                            </small>
                        </div>

                        <div class="mb-3">
                            <div class="input-icon-wrapper floating-field">
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    class="form-control custom-input floating-input"
                                    placeholder=" "
                                    value="<?php echo htmlspecialchars($oldInput['email'] ?? ''); ?>"
                                    required
                                >
                                <label for="email">Email Address</label>
                            
                                <i
                                    id="emailStatus"
                                    class="bi validation-status d-none"
                                ></i>
                            </div>
                            
                            <div
                                id="emailMessage"
                                class="password-message d-none"
                            ></div>
                        </div>

                        <div class="mb-3">
                            <div class="input-icon-wrapper floating-field">
                                <input
                                    type="tel"
                                    name="mobile"
                                    id="mobile"
                                    class="form-control custom-input floating-input"
                                    placeholder=" "
                                    value="<?php echo htmlspecialchars($oldInput['mobile'] ?? ''); ?>"
                                    inputmode="numeric"
                                >
                                <label for="mobile">Mobile Number (Optional)</label>
                            
                                <i
                                    id="mobileStatus"
                                    class="bi validation-status d-none"
                                ></i>
                            </div>
                            
                            <div
                                id="mobileMessage"
                                class="password-message d-none"
                            ></div>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <div class="password-field floating-field">
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="form-control custom-input floating-input"
                                    placeholder=" "
                                    autocomplete="new-password"
                                    required
                                >

                                <label for="password">Password</label>

                                <i
                                    class="bi bi-eye-slash password-toggle-icon"
                                    id="togglePassword"
                                    role="button"
                                    tabindex="0"
                                    aria-label="Show password"
                                    title="Show password"
                                ></i>
                            </div>

                            <div
                                id="passwordMessage"
                                class="password-message d-none">
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-2">
                            <div class="confirm-password-field floating-field">
                                <input
                                    type="password"
                                    id="confirm"
                                    name="confirm_password"
                                    class="form-control custom-input floating-input"
                                    placeholder=" "
                                    autocomplete="new-password"
                                    required
                                >

                                <label for="confirm">Confirm Password</label>

                                <i
                                    class="bi bi-eye-slash password-toggle-icon"
                                    id="toggleConfirmPassword"
                                    role="button"
                                    tabindex="0"
                                    aria-label="Show password"
                                    title="Show password"
                                ></i>
                            </div>

                            <div
                                id="confirmMessage"
                                class="password-message d-none">
                            </div>
                        </div>

                        <?php if ($isSuperAdminSignup): ?>
                        <!-- RECOVERY SECRET PASSWORD (Super Admin Only) -->
                        <div class="role-assignment-box mb-4">
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-warning-subtle">
                                <i class="bi bi-key-fill text-warning fs-5 me-2"></i>
                                <h6 class="mb-0 fw-bold text-warning" style="font-size: 15px;">Recovery Secret Password</h6>
                            </div>
                            <p class="text-muted small mb-3">Create a recovery secret to regain access if you forget your login password. This is shown only during Super Admin signup.</p>                            
                            <div class="recovery-password-field floating-field">
                                <input
                                    type="password"
                                    name="recovery_secret_password"
                                    id="recovery_pass"
                                    class="form-control custom-input floating-input"
                                    placeholder=" "
                                    autocomplete="new-password"
                                    required
                                >

                                <label for="recovery_pass">
                                    Recovery Secret Password
                                </label>

                                <i
                                    class="bi bi-eye-slash password-toggle-icon"
                                    id="toggleRecoveryPassword"
                                    role="button"
                                    tabindex="0"
                                    aria-label="Show password"
                                    title="Show password"
                                ></i>
                            </div>

                            <div class="recovery-password-field floating-field">
                                <input
                                    type="password"
                                    name="recovery_secret_confirm"
                                    id="recovery_pass_confirm"
                                    class="form-control custom-input floating-input"
                                    placeholder=" "
                                    autocomplete="new-password"
                                    required
                                >

                                <label for="recovery_pass_confirm">
                                    Confirm Recovery Secret
                                </label>

                                <i
                                    class="bi bi-eye-slash password-toggle-icon"
                                    id="toggleRecoveryConfirm"
                                    role="button"
                                    tabindex="0"
                                    aria-label="Show password"
                                    title="Show password"
                                ></i>
                            </div>
                        </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-success w-100 submit-btn">
                            <i class="bi bi-person-plus me-2"></i>Create Account
                        </button>

                        <div class="text-center mt-4 form-footer">
                            <span class="text-muted">Already have an account?</span>
                            <a href="login.php" class="ms-1 fw-bold">Sign in here</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/signup.js"></script>
</body>
</html>
