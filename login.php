<?php
session_start();

$loginError = $_SESSION['login_error'] ?? '';
$isLocked = $_SESSION['login_locked'] ?? false;
$lockedUntil = $_SESSION['login_locked_until'] ?? null;
$isPermanentlyLocked = $_SESSION['login_locked_permanent'] ?? false;
$oldInput = $_SESSION['old_input'] ?? [];
unset($_SESSION['login_error'], $_SESSION['old_input'], $_SESSION['login_locked'], $_SESSION['login_locked_until'], $_SESSION['login_locked_permanent']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ISU Inventory & Billing</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Reuse your existing signup CSS for identical design -->
    <link rel="stylesheet" href="assets/css/signup.css">

    <style>
        /* Specific styles for Login Page components */
        .form-check-input:checked {
            background-color: var(--isu-green);
            border-color: var(--isu-green);
        }
        
        .divider-container {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 25px 0;
        }
        
        .divider-line {
            width: 100%;
            height: 1px;
            background-color: #e0e0e0;
        }
        
        .divider-text {
            position: absolute;
            background-color: #fff;
            padding: 0 15px;
            color: #888;
            font-size: 13px;
            font-weight: 600;
        }

        .login-role-btn {
            border-radius: 10px;
            padding: 8px 15px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: 0.3s;
        }

        .btn-super-admin {
            border: 1px solid var(--isu-green);
            color: var(--isu-green);
            background: transparent;
        }

        .btn-super-admin:hover {
            background: #f4fbf7;
            color: var(--isu-green-hover);
        }

        .btn-admin {
            border: 1px solid #ccc;
            color: #555;
            background: transparent;
        }

        .btn-admin:hover {
            background: #f8f9fa;
            border-color: #aaa;
            color: #333;
        }
    </style>
    <link rel="stylesheet" href="assets/css/semantic-theme.css">
</head>

<body class="auth-shell d-flex flex-column min-vh-100">

    <div class="container main-container d-flex align-items-center justify-content-center flex-grow-1">
        <div class="row w-100 align-items-center g-5">

            <!-- LEFT SIDE (Identical to your current signup) -->
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

            <!-- RIGHT SIDE (Login Form Card) -->
            <div class="col-lg-7 d-flex justify-content-center">
                <div class="signup-card w-100">
                    <div class="text-center mb-4">
    <h2 class="form-title">Welcome Back!</h2>
    <p class="text-muted text-sm">Sign in to your account to continue</p>
</div>

                    <?php if ($loginError): ?>
                        <div class="alert alert-danger text-center mb-4">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <span><?php echo htmlspecialchars($loginError); ?></span>
                            <?php if ($isLocked && $lockedUntil): ?>
                                <div class="mt-2">
                                    <small>Please try again in <strong id="countdown-timer"></strong>.</small>
                                </div>
                            <?php elseif ($isPermanentlyLocked): ?>
                                <div class="mt-2">
                                    <small>Please contact the Super Admin to have your account unlocked.</small><br>
                                    <small>Email: <a href="mailto:carriz125@gmail.com">carriz125@gmail.com</a></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <form action="process/login.php" method="POST">
                        <!-- =================================================
                             USERNAME / EMAIL
                             ================================================= -->

                        <div class="mb-3">

                            <div class="floating-field">

                                <input
                                    type="text"
                                    id="identity"
                                    name="identity"
                                    class="floating-input"
                                    placeholder=" "
                                    autocomplete="username"
                                    value="<?php echo htmlspecialchars($oldInput['identity'] ?? ''); ?>"
                                    required>

                                <label for="identity">
                                    Username or Email
                                </label>

                            </div>

                        </div>


                        <!-- =================================================
                             PASSWORD
                             ================================================= -->

                        <div class="mb-3">

                            <div class="floating-field">

                                <input
                                    type="password"
                                    id="login-password"
                                    name="password"
                                    class="floating-input"
                                    placeholder=" "
                                    autocomplete="current-password"
                                    required>

                                <label for="login-password">
                                    Password
                                </label>

                            </div>
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="d-flex justify-content-between align-items-center mb-4 px-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rememberMe" checked>
                                <label class="form-check-label text-dark fw-medium" for="rememberMe" style="font-size: 13.5px;">
                                    Remember me
                                </label>
                            </div>
                            <a href="forgot_password.php" class="text-isu-green fw-semibold text-decoration-none" style="font-size: 13.5px;">Forgot Password?</a>
                        </div>

                        <!-- Login Button -->
                        <button type="submit" class="btn btn-success w-100 submit-btn mb-2">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Log In
                        </button>

                    
                        <!-- Create Account Link -->
                        <div class="text-center mt-4 form-footer border-top pt-4">
                            <span class="text-muted">Don't have an account?</span>
                            <a href="signup.php" class="ms-1 fw-bold">Create Account</a>                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer added specifically for the login view as requested in the image -->
    <footer class="text-center py-4 text-muted w-100 mt-auto" style="font-size: 12px; font-weight: 500;">
        © 2026 Isabela State University Cauayan Campus. <br> All rights reserved.
    </footer>

    <!-- Script for Eye Toggle -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            const passInput =
                document.getElementById("login-password");

            if (passInput) {

                const wrapper =
                    passInput.parentElement;

                wrapper.style.position = "relative";

                const eye =
                    document.createElement("i");

                eye.className =
                    "bi bi-eye-slash password-toggle-icon";

                wrapper.appendChild(eye);


                eye.addEventListener("click", function () {

                    if (passInput.type === "password") {

                        passInput.type = "text";

                        eye.className =
                            "bi bi-eye password-toggle-icon";

                    } else {

                        passInput.type = "password";

                        eye.className =
                            "bi bi-eye-slash password-toggle-icon";

                    }

                });

            }
        });

        const loginForm = document.querySelector('form[action="process/login.php"]');

        <?php if ($isLocked && $lockedUntil): ?>
        const lockedUntil = <?php echo $lockedUntil; ?> * 1000; // Convert to JS timestamp (ms)
        const countdownElement = document.getElementById('countdown-timer');
        const formInputs = loginForm.querySelectorAll('input, button');
 
        // Disable form while locked
        formInputs.forEach(input => input.disabled = true);

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = lockedUntil - now;

            if (distance < 0) {
                countdownElement.innerHTML = "a few moments. Please refresh.";
                formInputs.forEach(input => input.disabled = false); // Re-enable form
                clearInterval(countdownInterval);
                return;
            }

            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            countdownElement.innerHTML = `${minutes}m ${seconds}s`;
        }

        const countdownInterval = setInterval(updateCountdown, 1000);
        <?php endif; ?>

        <?php if ($isPermanentlyLocked): ?>
            loginForm.querySelectorAll('input, button').forEach(input => {
                input.disabled = true;
            });
        <?php endif; ?>
    </script>
</body>
</html>
