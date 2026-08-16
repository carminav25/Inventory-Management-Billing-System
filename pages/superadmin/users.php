<?php
session_start();
require_once "../../config/database.php";
require_once "../../includes/superadmin_auth.php";
require_once "../../includes/superadmin_functions.php";

// Page-specific variables
$pageTitle = "User Management";
$breadcrumbs = [
    ['name' => 'User Management']
];
requireSuperAdmin();

// Get filters
$roleFilter = $_GET['role'] ?? 'all';
$statusFilter = $_GET['status'] ?? 'all';

// Get all users for the table based on filters
$users = getAllUsers($conn, $roleFilter, $statusFilter);

// Get stats for the cards
$totalUsers = getTotalUsers($conn);
$totalAdmins = getTotalAdmins($conn);
$totalViewers = getTotalViewers($conn);
$totalDisabled = getTotalDisabledUsers($conn);

// Function to get user initials
function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $w) {
        $initials .= mb_substr($w, 0, 1);
    }
    return strtoupper($initials);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <!-- Tailwind CSS -->
    <!-- AlpineJS for dropdowns -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <!-- FontAwesome -->
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

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 overflow-y-auto flex flex-col h-full bg-[#f0f4f5]" id="main-content">
        
        <!-- TOPBAR (Audit Trail Design) -->
        <?php include_once "topbar.php"; ?>

        <!-- Page Content -->
        <div class="p-6 md:p-8 flex-1">
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Card 1 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 border-l-4 border-l-green-500 flex items-center gap-5">
                    <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-2xl">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Users</p>
                        <h2 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($totalUsers); ?></h2>
                        <p class="text-xs text-gray-400 mt-1">All registered users</p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 border-l-4 border-l-blue-600 flex items-center gap-5">
                    <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-2xl">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Admins</p>
                        <h2 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($totalAdmins); ?></h2>
                        <p class="text-xs text-gray-400 mt-1">System administrators</p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 border-l-4 border-l-yellow-400 flex items-center gap-5">
                    <div class="w-14 h-14 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-500 text-2xl">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Viewers</p>
                        <h2 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($totalViewers); ?></h2>
                        <p class="text-xs text-gray-400 mt-1">System viewers</p>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 border-l-4 border-l-red-500 flex items-center gap-5">
                    <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center text-red-500 text-2xl">
                        <i class="fa-solid fa-user-lock"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Disabled Accounts</p>
                        <h2 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($totalDisabled); ?></h2>
                        <p class="text-xs text-gray-400 mt-1">Inactive users</p>
                    </div>
                </div>
            </div>

            <!-- Main Table Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                
                <!-- Toolbar -->
                <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                    <div class="flex items-center gap-4 flex-1">
                        <!-- Search -->
                        <div class="relative w-full max-w-md">
                            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i> 
                            <input type="text" id="user-search" placeholder="Search by name, username or email..." class="w-full pl-11 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-green-500 text-sm" onkeyup="filterTable()">
                        </div>
                        
                        <!-- Filters -->
                        <div class="relative w-40">
                            <select id="role-filter" class="w-full appearance-none bg-white border border-gray-200 text-gray-700 py-2 px-4 pr-8 rounded-lg text-sm focus:outline-none" onchange="filterTable()">
                                <option>All Roles</option>
                                <option>Super Admin</option>
                                <option>Admin</option>
                                <option>Viewer</option>
                            </select>
                            <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i> 
                        </div>
                        
                        <div class="relative w-40">
                            <select id="status-filter" class="w-full appearance-none bg-white border border-gray-200 text-gray-700 py-2 px-4 pr-8 rounded-lg text-sm focus:outline-none" onchange="filterTable()">
                                <option>All Status</option>
                                <option>Active</option>
                                <option>Deleted</option>
                                <option>Inactive</option>
                            </select>
                            <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Create Button -->
                    <a href="user_edit.php" class="bg-brand-green hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
                        <i class="fa-solid fa-user-plus"></i> Create New User
                    </a>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table id="users-table" class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-brand-dark text-white text-xs uppercase tracking-wider">
                                <th class="p-4 rounded-tl-lg font-medium">User</th>
                                <th class="p-4 font-medium">Username</th>
                                <th class="p-4 font-medium">Email</th>
                                <th class="p-4 font-medium">Role</th>
                                <th class="p-4 font-medium">Status</th>
                                <th class="p-4 font-medium">Created</th>
                                <th class="p-4 rounded-tr-lg font-medium text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-gray-700">
                            <?php foreach ($users as $user): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-bold text-sm"><?php echo getInitials($user['firstname'] . ' ' . $user['lastname']); ?></div>
                                            <span class="font-bold text-gray-800"><?php echo htmlspecialchars(strtoupper($user['firstname'] . ' ' . $user['lastname'])); ?></span>
                                        </div>
                                    </td>
                                    <td class="p-4"><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td class="p-4"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="p-4">
                                        <?php
                                            $role_class = 'bg-sky-100 text-sky-700';
                                            if ($user['role'] === 'Super Admin') $role_class = 'bg-emerald-100 text-emerald-700';
                                            if ($user['role'] === 'Admin') $role_class = 'bg-blue-100 text-blue-700';
                                        ?>
                                        <span class="<?php echo $role_class; ?> px-3 py-1 rounded-full text-xs font-medium"><?php echo htmlspecialchars($user['role']); ?></span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <?php
                                                $is_permanently_locked = $user['is_permanently_locked'] == 1;
                                                $is_temporarily_locked = $user['lock_until'] !== null && new DateTime() < new DateTime($user['lock_until']);
                                                if ($user['status'] === 'Active'): ?>
                                                <span class="bg-green-100 text-green-600 px-3 py-1 rounded-full text-xs font-medium flex items-center gap-1.5 w-max">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span> Active
                                                </span>
                                            <?php elseif ($user['status'] === 'Deleted'): ?>
                                                <span class="bg-gray-200 text-gray-600 px-3 py-1 rounded-full text-xs font-medium flex items-center gap-1.5 w-max">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span> Deleted
                                                </span>
                                            <?php else: // Inactive ?>
                                                <span class="bg-red-100 text-red-500 px-3 py-1 rounded-full text-xs font-medium flex items-center gap-1.5 w-max">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Inactive
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($is_permanently_locked || $is_temporarily_locked): ?>
                                                <i class="fa-solid fa-lock text-red-500" title="<?php echo $is_permanently_locked ? 'Permanently Locked' : 'Temporarily Locked'; ?>"></i>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-medium text-gray-800"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo date('h:i A', strtotime($user['created_at'])); ?></div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="user_edit.php?id=<?php echo $user['id']; ?>" class="w-8 h-8 rounded border border-gray-200 text-green-600 hover:bg-green-50 flex items-center justify-center"><i class="fa-regular fa-pen-to-square"></i></a>
                                            <?php if ($user['role'] !== 'Super Admin' && $user['id'] !== $_SESSION['user_id']): ?>
                                                <?php if ($user['status'] === 'Active'): ?>
                                                    <a href="../../process/superadmin/disable_user.php?id=<?php echo $user['id']; ?>" onclick="return confirm('Disable this user?')" class="w-8 h-8 rounded border border-gray-200 text-red-500 hover:bg-red-50 flex items-center justify-center"><i class="fa-solid fa-lock"></i></a>
                                                <?php else: ?>
                                                    <a href="../../process/superadmin/activate_user.php?id=<?php echo $user['id']; ?>" class="w-8 h-8 rounded border border-gray-200 text-green-500 hover:bg-green-50 flex items-center justify-center"><i class="fa-solid fa-lock-open"></i></a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                 <button class="w-8 h-8 rounded border border-gray-200 text-gray-300 flex items-center justify-center cursor-not-allowed" disabled><i class="fa-solid fa-lock"></i></button>
                                            <?php endif; ?>
                                            <div class="relative" x-data="{ open: false }">
                                                <?php if ($user['role'] !== 'Super Admin' && $user['id'] !== $_SESSION['user_id']): ?>
                                                    <a href="../../process/superadmin/delete_user.php?id=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to delete this user? Their personal data will be anonymized, but their activity logs will be kept. This action cannot be undone.')" class="w-8 h-8 rounded border border-gray-200 text-red-600 hover:bg-red-50 flex items-center justify-center" title="Delete User">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination -->
                <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-100">
                    <p id="table-info" class="text-sm text-gray-500">Showing <?php echo count($users); ?> of <?php echo $totalUsers; ?> entries</p>
                    <div class="flex gap-1">
                        <button id="prev-page" class="px-3 py-1.5 text-sm text-gray-500 border border-gray-200 rounded-md hover:bg-gray-50" disabled>Previous</button>
                        <div id="page-numbers" class="flex gap-1"></div>
                        <button id="next-page" class="px-3 py-1.5 text-sm text-gray-500 border border-gray-200 rounded-md hover:bg-gray-50" disabled>Next</button>
                    </div>
                </div> 

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
            sidebarToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleSidebar();
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', toggleSidebar);
        }

        function filterTable() {
            const searchTerm = document.getElementById('user-search').value.toLowerCase();
            const roleValue = document.getElementById('role-filter').value;
            const statusValue = document.getElementById('status-filter').value;
            const tableRows = document.querySelectorAll('#users-table tbody tr');
            let visibleRows = 0;

            tableRows.forEach(row => {
                const name = row.cells[0].textContent.toLowerCase();
                const username = row.cells[1].textContent.toLowerCase();
                const email = row.cells[2].textContent.toLowerCase();
                const role = row.cells[3].textContent.trim();
                const status = row.cells[4].textContent.trim();

                const matchesSearch = name.includes(searchTerm) || username.includes(searchTerm) || email.includes(searchTerm);
                const matchesRole = roleValue === 'All Roles' || role === roleValue;
                const matchesStatus = statusValue === 'All Status' || status.startsWith(statusValue);

                if (matchesSearch && matchesRole && matchesStatus) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            });

            const tableInfo = document.getElementById('table-info');
            tableInfo.textContent = `Showing ${visibleRows} of <?php echo $totalUsers; ?> entries`;
        }

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