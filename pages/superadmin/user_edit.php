<?php
session_start();
require_once "../../config/database.php";
require_once "../../includes/superadmin_auth.php";
require_once "../../includes/superadmin_functions.php";
require_once "../../includes/activity_log.php";

// Page-specific variables
$pageTitle = "Create/Edit User";
requireSuperAdmin();

$user = null;
$isEdit = false;
$errors = [];
$success = false;

// Check if editing an existing user
if (isset($_GET['id'])) {
    $userId = intval($_GET['id']);
    $user = getUserById($conn, $userId);
    if ($user) {
        $isEdit = true;
        $pageTitle = "Edit User";
        $breadcrumbs = [['name' => 'User Management', 'link' => 'users.php'], ['name' => 'Edit']];
    } else {
        $pageTitle = "Create New User";
        $breadcrumbs = [['name' => 'User Management', 'link' => 'users.php'], ['name' => 'Create']];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $role = $_POST['role'] ?? '';
    
    // Check if email already exists (for new users or if email changed)
    if (empty($errors)) {
        $existingUser = getUserByEmail($conn, $email);
        if ($existingUser && (!$isEdit || $existingUser['id'] != $user['id'])) {
            $errors[] = "Email already exists.";
        }
    }
    
    // If no errors, save user
    if (empty($errors)) {
        if ($isEdit) {
            // Update existing user
            $query = "UPDATE users
SET
    firstname = ?,
    lastname = ?,
    email = ?,
    mobile = ?,
    role = ?
WHERE id = ?";
            $params = [$firstname, $lastname, $email, $mobile, $role, $user['id']];
            $types = 'sssssi';
            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                logActivity(
                    $conn,
                    getCurrentUserId(),
                    getCurrentUserFullName(),
                    getCurrentUsername(),
                    getCurrentUserRole(),
                    "Updated user: {$firstname} {$lastname} ({$email})"
                );
                $success = true;
            }
        } else {
            // Create new user
            // Automatically generate a temporary password
            $tempPassword = bin2hex(random_bytes(4)); // e.g., a8f29cd1
            $hash = password_hash($tempPassword, PASSWORD_DEFAULT);
            $username = strtolower(str_replace(' ', '.', $firstname . '.' . $lastname));
            
            // Ensure unique username
            $counter = 1;
            $originalUsername = $username;
            while (getUserByUsername($conn, $username)) {
                $username = $originalUsername . $counter;
                $counter++;
            }
            
            $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, username, email, mobile, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())");
            $stmt->bind_param("sssssss", $firstname, $lastname, $username, $email, $mobile, $hash, $role);
            
            if ($stmt->execute()) {
                logActivity(
                    $conn,
                    getCurrentUserId(),
                    getCurrentUserFullName(),
                    getCurrentUsername(),
                    getCurrentUserRole(),
                    "Created new user: {$firstname} {$lastname} ({$email}) - Role: {$role}"
                );
                $success = true;
                $user = null;
                // Store generated credentials in session to display them
                $_SESSION['new_user_credentials'] = [
                    'username' => $username,
                    'password' => $tempPassword
                ];
                $isEdit = false;
            }
        }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <?php if ($success): ?>
                <?php if (isset($_SESSION['new_user_credentials'])): 
                    $credentials = $_SESSION['new_user_credentials'];
                    unset($_SESSION['new_user_credentials']);
                ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-6 mb-6 rounded-lg" role="alert">
                        <p class="font-bold text-lg">User Created Successfully!</p>
                        <p class="mt-2">Please provide the following credentials to the new user and advise them to change their password upon first login.</p>
                        <div class="mt-4 bg-green-200/50 p-4 rounded-md font-mono text-sm">
                            <div><strong>Username:</strong> <?php echo htmlspecialchars($credentials['username']); ?></div>
                            <div><strong>Temporary Password:</strong> <?php echo htmlspecialchars($credentials['password']); ?></div>
                        </div>
                        <a href="users.php" class="font-bold underline mt-4 inline-block">Return to User List</a>
                    </div>
                <?php else: ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert">
                        <p class="font-bold">Success!</p>
                        <p>User has been updated successfully. <a href="users.php" class="font-bold underline">Return to User List</a>.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
                    <p class="font-bold">Please fix the following errors:</p>
                    <ul class="mt-2 list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:p-8 max-w-3xl mx-auto">
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                            <input type="text" id="firstname" name="firstname" required value="<?php echo htmlspecialchars($user['firstname'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-brand-green focus:ring-brand-green text-sm">
                        </div>
                        <div>
                            <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                            <input type="text" id="lastname" name="lastname" required value="<?php echo htmlspecialchars($user['lastname'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-brand-green focus:ring-brand-green text-sm">
                        </div>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-brand-green focus:ring-brand-green text-sm">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="mobile" class="block text-sm font-medium text-gray-700 mb-1">Mobile</label>
                            <input type="text" id="mobile" name="mobile" value="<?php echo htmlspecialchars($user['mobile'] ?? ''); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-brand-green focus:ring-brand-green text-sm">
                        </div>
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                            <select id="role" name="role" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-brand-green focus:ring-brand-green text-sm">
                                <option value="">-- Select Role --</option>
                                <option value="Admin" <?php echo (isset($user) && $user['role'] === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="Viewer" <?php echo (isset($user) && $user['role'] === 'Viewer') ? 'selected' : ''; ?>>Viewer</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end gap-4 pt-4">
                        <a href="users.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2 rounded-lg text-sm font-medium transition-colors">Cancel</a>
                        <button type="submit" class="bg-brand-green hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
                            <i class="fa-solid fa-check"></i> <?php echo $isEdit ? 'Update User' : 'Create User'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Basic sidebar toggle functionality for mobile
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
        });

    </script>
</body>
</html>
