<?php
session_start();
require_once "../../includes/activity_log.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

if (!in_array($_SESSION['role'], ["Super Admin", "Admin"])) {
    // Redirect non-admins to their respective dashboards
    if ($_SESSION['role'] === 'Viewer') {
        header("Location: ../viewer/index.php");
        exit();
    } else {
        header("Location: ../../login.php"); // Fallback to login
        exit();
    }
}

require_once "../../config/database.php";
require_once "../../includes/superadmin_functions.php"; // For formatTimestamp

// Page-specific variables
$pageTitle = "Reset User Password";
$breadcrumbs = [['name' => 'User Management', 'link' => 'users.php'], ['name' => 'Reset Password']];


$selectedUser = null;
$error = '';
$success = '';
$identity = trim($_GET['identity'] ?? '');
$userId = intval($_GET['user_id'] ?? 0);

if (isset($_SESSION['reset_success'])) {
    $success = $_SESSION['reset_success'];
    unset($_SESSION['reset_success']);
}

if (isset($_SESSION['reset_error'])) {
    $error = $_SESSION['reset_error'];
    unset($_SESSION['reset_error']);
}

if ($userId > 0) {
    $stmt = $conn->prepare("SELECT id, firstname, lastname, username, email, role FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $selectedUser = $result->fetch_assoc();
    } else {
        $error = 'The selected user account was not found.';
    }
} elseif ($identity !== '') {
    $stmt = $conn->prepare("SELECT id, firstname, lastname, username, email, role FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->bind_param("ss", $identity, $identity);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $selectedUser = $result->fetch_assoc();
        $userId = $selectedUser['id'];
    } else {
        $error = 'No matching user account was found.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap Icons (for password toggle eyes & icons consistency) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* Custom brand colors */
        .bg-brand-dark { background-color: #064e3b; }
        .bg-brand-active { background-color: #0f766e; }
        .text-brand-dark { color: #064e3b; }
        .bg-brand-green { background-color: #10b981; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>

<body class="bg-[#f0f4f5] flex h-screen font-sans overflow-hidden">
    
    <?php include_once "sidebar.php"; ?>

    <!-- Overlay for mobile sidebar -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden"></div>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto flex flex-col" id="main-content">
        <?php include_once "topbar.php"; ?>

        <div class="p-6 md:p-8 flex-1">
            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
                    <p class="font-bold">Error</p>
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert">
                    <p class="font-bold">Success</p>
                    <p><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Search and Info Column -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 pb-4 border-b border-gray-100">Find User</h3>
                        <form action="reset_password.php" method="GET" class="space-y-4">
                            <div>
                                <label for="identity" class="block text-sm font-medium text-gray-700 mb-1">Username or Email</label>
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                    <input type="text" name="identity" id="identity" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-green text-sm" placeholder="e.g., jdelacruz" value="<?php echo htmlspecialchars($identity); ?>">
                                </div>
                            </div>
                            <button type="submit" class="w-full bg-brand-dark hover:bg-black text-white px-5 py-2.5 rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition-colors">
                                <i class="fa-solid fa-search"></i> Search User
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Reset Form Column -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 pb-4 border-b border-gray-100">Set New Password</h3>
                        
                        <?php if ($selectedUser): ?>
                            <div class="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-lg mb-6">
                                <p class="font-bold text-sm mb-2">Selected User:</p>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                                    <div><strong>Name:</strong> <?php echo htmlspecialchars($selectedUser['firstname'] . ' ' . $selectedUser['lastname']); ?></div>
                                    <div><strong>Username:</strong> <?php echo htmlspecialchars($selectedUser['username']); ?></div>
                                    <div><strong>Role:</strong> <?php echo htmlspecialchars($selectedUser['role']); ?></div>
                                </div>
                            </div>

                            <form action="../../process/reset_password.php" method="POST" id="user-reset-form" class="space-y-4">
                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($selectedUser['id']); ?>">
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php if ($selectedUser['role'] === 'Super Admin' && $_SESSION['role'] === 'Super Admin' && $selectedUser['id'] !== $_SESSION['user_id']): ?>
                                        <div class="md:col-span-2">
                                            <label for="recovery-secret" class="block text-sm font-medium text-red-700 mb-1">Recovery Secret *</label>
                                            <div class="relative">
                                                <i class="fa-solid fa-shield-halved absolute left-3 top-1/2 transform -translate-y-1/2 text-red-400"></i>
                                                <input type="password" name="recovery_secret" id="recovery-secret" class="w-full pl-10 pr-4 py-2 border-red-300 rounded-lg shadow-sm focus:border-red-500 focus:ring-red-500 text-sm" required placeholder="Enter target user's secret">
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">Required to reset another Super Admin's password.</p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- New Password Field -->
                                    <div>
                                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                        <div class="relative">
                                            <input type="password" name="new_password" id="password" class="w-full pl-3 pr-10 py-2 border border-gray-300 rounded-lg shadow-sm focus:border-brand-green focus:ring-brand-green text-sm" required placeholder="New Password">
                                        </div>
                                    </div>
                                    
                                    <!-- Confirm Password Field -->
                                    <div>
                                        <label for="confirm" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                                        <div class="relative">
                                            <input type="password" name="confirm_password" id="confirm" class="w-full pl-3 pr-10 py-2 border border-gray-300 rounded-lg shadow-sm focus:border-brand-green focus:ring-brand-green text-sm" required placeholder="Confirm Password">
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <?php if ($_SESSION['role'] === 'Admin' && $selectedUser['id'] === $_SESSION['user_id']): ?>
                                        <button type="button" class="w-full bg-gray-400 text-white px-5 py-2.5 rounded-lg text-sm font-medium flex items-center justify-center gap-2 cursor-not-allowed" disabled>
                                            <i class="fa-solid fa-ban"></i> Cannot Reset Own Password
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" class="w-full bg-brand-green hover:bg-green-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition-colors">
                                            <i class="fa-solid fa-key"></i> Reset Password
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="text-center py-10">
                                <i class="fa-solid fa-user-magnifying-glass text-4xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500">Search for a user to begin the password reset process.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Sidebar and UI Scripts
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        function toggleSidebar() {
            if (sidebar) {
                sidebar.classList.toggle('-translate-x-full');
                sidebar.classList.toggle('translate-x-0');
            }
            if (sidebarOverlay) {
                sidebarOverlay.classList.toggle('hidden');
            }
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', (e) => { e.stopPropagation(); toggleSidebar(); });
        }

        if (sidebarOverlay) { sidebarOverlay.addEventListener('click', toggleSidebar); }

        // Sticky header on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.getElementById('main-content');
            const pageHeader = document.getElementById('page-header');

            if (mainContent && pageHeader) {
                mainContent.addEventListener('scroll', () => {
                    if (mainContent.scrollTop > 10) {
                        pageHeader.classList.remove('bg-white', 'shadow-sm');
                        pageHeader.classList.add('bg-white/80', 'shadow-md', 'backdrop-blur-sm');
                    } else {
                        pageHeader.classList.add('bg-white', 'shadow-sm');
                        pageHeader.classList.remove('bg-white/80', 'shadow-md', 'backdrop-blur-sm');
                    }
                });
            }

            // ============================
            // Password Show / Hide & Strength Validation Integration
            // ============================
            const password = document.getElementById("password");
            const confirm = document.getElementById("confirm");

            function addToggle(inputElement) {
                if (inputElement) {
                    const wrapper = inputElement.parentElement;
                    if (window.getComputedStyle(wrapper).position === "static") {
                        wrapper.style.position = "relative";
                    }
                    
                    inputElement.style.paddingRight = "40px";

                    const eye = document.createElement("i");
                    eye.className = "bi bi-eye-slash password-toggle-icon";
                    
                    eye.style.position = "absolute";
                    eye.style.right = "15px";
                    eye.style.top = "50%";
                    eye.style.transform = "translateY(-50%)";
                    eye.style.cursor = "pointer";
                    eye.style.zIndex = "10";
                    eye.style.color = "#6c757d";

                    wrapper.appendChild(eye);

                    eye.onclick = function() {
                        if (inputElement.type === "password") {
                            inputElement.type = "text";
                            eye.className = "bi bi-eye password-toggle-icon text-dark";
                        } else {
                            inputElement.type = "password";
                            eye.className = "bi bi-eye-slash password-toggle-icon";
                        }
                    }
                }
            }

            addToggle(password);
            addToggle(confirm);

            // Password Strength & Guidelines
            const strength = document.createElement("small");
            strength.style.display = "block";
            strength.style.marginTop = "6px";
            strength.style.fontSize = "12px";
            strength.style.fontWeight = "500";
            strength.style.paddingLeft = "5px";

            const defaultPasswordMsg = '<i class="bi bi-exclamation-circle me-1"></i> Must be at least 8 characters with 1 uppercase, 1 number, and 1 special character.';

            if (password) {
                password.parentElement.insertAdjacentElement("afterend", strength);

                password.addEventListener("keyup", function() {
                    let pass = password.value;

                    if (pass.length === 0) {
                        strength.innerHTML = "";
                        return;
                    }

                    let score = 0;
                    if (pass.length >= 8) score++;
                    if (/[A-Z]/.test(pass)) score++;
                    if (/[a-z]/.test(pass)) score++;
                    if (/[0-9]/.test(pass)) score++;
                    if (/[!@#$%^&*]/.test(pass)) score++;

                    if (score < 4 || pass.length < 8) {
                        strength.innerHTML = defaultPasswordMsg;
                        strength.style.color = "red";
                    } else if (score === 4) {
                        strength.innerHTML = "✔ Good Password";
                        strength.style.color = "#d4a000";
                    } else {
                        strength.innerHTML = "✔ Strong Password";
                        strength.style.color = "#198754";
                    }
                });
            }

            // Password Match
            const match = document.createElement("small");
            match.style.display = "block";
            match.style.marginTop = "4px";
            match.style.fontSize = "12px";
            match.style.fontWeight = "600";
            match.style.paddingLeft = "5px";

            if (confirm) {
                confirm.parentElement.insertAdjacentElement("afterend", match);

                const validatePasswordMatch = () => {
                    if (confirm.value !== "") {
                        if (password.value === confirm.value) {
                            match.innerHTML = "✔ Passwords match";
                            match.style.color = "#198754";
                        } else {
                            match.innerHTML = "✖ Passwords do not match";
                            match.style.color = "red";
                        }
                    } else {
                        match.innerHTML = "";
                    }
                };

                if (password) {
                    password.addEventListener("keyup", validatePasswordMatch);
                }
                confirm.addEventListener("keyup", validatePasswordMatch);
            }

            // Form Submit Interception Check
            const resetForm = document.getElementById("user-reset-form");
            if (resetForm) {
                resetForm.addEventListener("submit", function(e) {
                    let passVal = password.value;
                    let isValidLength = passVal.length >= 8;
                    let hasUpper = /[A-Z]/.test(passVal);
                    let hasNumber = /[0-9]/.test(passVal);
                    let hasSpecial = /[!@#$%^&*]/.test(passVal);

                    if (!isValidLength || !hasUpper || !hasNumber || !hasSpecial) {
                        e.preventDefault();
                        alert("Password must be at least 8 characters long and include at least one uppercase letter, one number, and one special character.");
                        password.focus();
                        return;
                    }

                    if (password.value !== confirm.value) {
                        e.preventDefault();
                        alert("Passwords do not match. Please ensure both fields are identical.");
                        confirm.focus();
                        return;
                    }
                });
            }
        });
    </script>
</body>
</html>