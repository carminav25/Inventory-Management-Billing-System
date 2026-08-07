<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recovery Code - ISU Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/signup.css">
</head>

<?php
session_start();

if (!isset($_SESSION['recovery_code']) || !isset($_SESSION['super_admin_created'])) {
    header("Location: login.php");
    exit();
}

$recoveryCode = $_SESSION['recovery_code'];
$superAdminName = $_SESSION['super_admin_name'] ?? 'Super Admin';
?>


<body class="bg-light">
    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="row w-100 align-items-center g-5">
            <div class="col-lg-5 d-none d-lg-block">
                <div class="text-center text-lg-start">
                    <img src="assets/images/logo.png" alt="ISU Logo" class="logo mb-3" style="max-width: 100px;">
                    <h1 class="brand-title">ISABELA STATE UNIVERSITY</h1>
                    <p class="brand-subtitle mb-4">Cauayan Campus (CBAO)</p>
                    <h2 class="system-title mb-3">Recovery Code Generated</h2>
                    <p class="system-desc mb-5">Your Super Admin account has been created. Save your Recovery Code in a secure location. You will need it if you forget your login password.</p>
                    <div class="feature-item d-flex align-items-start mb-4">
                        <div class="feature-icon text-warning"><i class="bi bi-exclamation-triangle-fill"></i></div>
                        <div>
                            <h5>Save It Now</h5>
                            <p>This code is only shown once. Write it down or store it in a secure password manager.</p>
                        </div>
                    </div>
                    <div class="feature-item d-flex align-items-start mb-4">
                        <div class="feature-icon text-info"><i class="bi bi-shield-check"></i></div>
                        <div>
                            <h5>Recovery Process</h5>
                            <p>Use your Recovery Secret and this code to regain access to your account.</p>
                        </div>
                    </div>
                    <div class="feature-item d-flex align-items-start">
                        <div class="feature-icon text-success"><i class="bi bi-check-circle-fill"></i></div>
                        <div>
                            <h5>Account Ready</h5>
                            <p>You can now log in with your username and password.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 d-flex justify-content-center">
                <div class="signup-card w-100">
                    <div class="text-center mb-4">
                        <div class="top-icon mx-auto mb-3" style="font-size: 48px; color: #ffc107;">
                            <i class="bi bi-key-fill"></i>
                        </div>
                        <h2 class="form-title">Super Admin Recovery Code</h2>
                        <p class="text-muted text-sm">Welcome <?php echo htmlspecialchars($superAdminName); ?></p>
                    </div>

                    <div class="alert alert-warning border-2 border-warning mb-4">
                        <div class="d-flex gap-3">
                            <div>
                                <i class="bi bi-exclamation-triangle-fill" style="font-size: 24px;"></i>
                            </div>
                            <div>
                                <strong>⚠ Important:</strong> Save this Recovery Code. It will only be shown once.
                            </div>
                        </div>
                    </div>

                    <div class="role-assignment-box mb-4">
                        <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-warning-subtle">
                            <i class="bi bi-key-fill text-warning fs-5 me-2"></i>
                            <h6 class="mb-0 fw-bold text-warning" style="font-size: 15px;">Your Recovery Code</h6>
                        </div>
                        <div class="text-center my-4">
                            <div style="
                                background: #f8f9fa;
                                border: 2px dashed #ffc107;
                                border-radius: 10px;
                                padding: 30px;
                                font-family: 'Courier New', monospace;
                            ">
                                <div style="font-size: 14px; color: #666; margin-bottom: 15px;">Recovery Code</div>
                                <div style="
                                    font-size: 32px;
                                    font-weight: 700;
                                    color: #333;
                                    letter-spacing: 3px;
                                    user-select: all;
                                    cursor: pointer;
                                    padding: 15px;
                                    background: white;
                                    border-radius: 8px;
                                    border: 1px solid #ddd;
                                " id="recovery-code-display">
                                    <?php echo htmlspecialchars($recoveryCode); ?>
                                </div>
                                <div style="font-size: 12px; color: #999; margin-top: 15px;">Click to select</div>
                            </div>
                        </div>
                        <p class="text-muted small mt-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Keep this code with your Recovery Secret Password. Both are required to recover your account.
                        </p>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button class="btn btn-warning" onclick="copyToClipboard()">
                            <i class="bi bi-clipboard me-2"></i> Copy Code
                        </button>
                        <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="bi bi-printer me-2"></i> Print this Page
                        </button>
                    </div>

                    <div class="text-center form-footer">
                        <a href="login.php" class="btn btn-success w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Go to Login
                        </a>
                    </div>

                    <div class="alert alert-info mt-4 small" style="background: #e7f3ff; border-color: #b3d9ff;">
                        <strong>Next Steps:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Log in with your username and password</li>
                            <li>Store your Recovery Code in a secure location</li>
                            <li>You won't be able to see this code again</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard() {
            const codeElement = document.getElementById('recovery-code-display');
            const code = codeElement.textContent.trim();
            navigator.clipboard.writeText(code).then(() => {
                const btn = event.target;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check me-2"></i> Copied!';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                }, 2000);
            });
        }
    </script>
</body>
</html>
