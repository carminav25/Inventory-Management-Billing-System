<?php
session_start();
require_once "../../config/database.php";
require_once "../../includes/superadmin_auth.php";
require_once "../../includes/superadmin_functions.php";
require_once "../../includes/activity_log.php";

// Page-specific variables
$pageTitle = "Security Center";
$breadcrumbs = [
    ['name' => 'Security Center']
];
requireSuperAdmin();

// Pagination for failed logins
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Show 10 entries per page
$offset = ($page - 1) * $limit;

// Get security data
$failedLogins = getFailedLoginAttempts($conn, $limit, $offset);
$totalFailedLogins = countFailedLoginAttempts($conn, false); // Get all failed logins
$totalPages = ceil($totalFailedLogins / $limit);
$lockedAccounts = getLockedAccounts($conn);

// Handle account unlock
$unlockMessage = '';
if (isset($_GET['unlock_id'])) {
    $unlockId = intval($_GET['unlock_id']);
    if (unlockUserAccount($conn, $unlockId)) {
        $user = getUserById($conn, $unlockId);
        logActivity(
            $conn,
            getCurrentUserId(),
            getCurrentUserFullName(),
            getCurrentUsername(),
            getCurrentUserRole(),
            "Unlocked account: {$user['firstname']} {$user['lastname']} ({$user['email']})"
        );
        $unlockMessage = "Account has been unlocked successfully.";
        // Refresh locked accounts
        $lockedAccounts = getLockedAccounts($conn);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Center</title>
    <!-- Tailwind CSS -->
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

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto flex flex-col" id="main-content">
        <?php include_once "topbar.php"; ?>

        <div class="p-6 md:p-8 flex-1">
            <!-- Success Message -->
            <?php if ($unlockMessage): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert">
                    <p class="font-bold">Success</p>
                    <p><?php echo $unlockMessage; ?></p>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-5">
                    <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center text-red-600 text-2xl shrink-0"><i class="fa-solid fa-user-lock"></i></div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Locked Accounts</p>
                        <h2 class="text-3xl font-bold text-gray-800"><?php echo count($lockedAccounts); ?></h2>
                        <p class="text-xs text-gray-400 mt-1">Manual action may be required</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-5">
                    <div class="w-14 h-14 rounded-full bg-orange-100 flex items-center justify-center text-orange-500 text-2xl shrink-0"><i class="fa-solid fa-shield-halved"></i></div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Failed Logins (Today)</p>
                        <h2 class="text-3xl font-bold text-gray-800"><?php echo countFailedLoginAttempts($conn, true); ?></h2>
                        <p class="text-xs text-gray-400 mt-1">Attempts since midnight</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-5">
                    <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-2xl shrink-0"><i class="fa-solid fa-clock-rotate-left"></i></div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Failed Logins</p>
                        <h2 class="text-3xl font-bold text-gray-800"><?php echo $totalFailedLogins; ?></h2>
                        <p class="text-xs text-gray-400 mt-1">All-time recorded attempts</p>
                    </div>
                </div>
            </div>
            
            <!-- Locked Accounts Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
                <h2 class="text-lg font-bold text-gray-800 mb-4 pb-4 border-b">Locked Accounts</h2>
                <?php if (!empty($lockedAccounts)): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-brand-dark text-white text-xs uppercase tracking-wider">
                                    <th class="p-4 font-medium">User</th>
                                    <th class="p-4 font-medium text-center">Role</th>
                                    <th class="p-4 font-medium">Lock Type</th>
                                    <th class="p-4 font-medium">Last IP</th>
                                    <th class="p-4 font-medium text-center">Attempts</th>
                                    <th class="p-4 font-medium">Time Remaining</th>
                                    <th class="p-4 font-medium text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm text-gray-700">
                                <?php foreach ($lockedAccounts as $account): ?>
                                    <?php
                                        $is_permanent = (bool)$account['is_permanently_locked'];
                                        $lock_type_class = $is_permanent ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-600';
                                        $lock_type_label = $is_permanent ? 'Permanent' : 'Temporary';
                                    ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="p-4 font-medium"><?php echo htmlspecialchars($account['firstname'] . ' ' . $account['lastname']); ?></td>
                                        <td class="p-4 text-center"><?php echo htmlspecialchars($account['role'] ?? 'N/A'); ?></td>
                                        <td class="p-4">
                                            <span class="<?php echo $lock_type_class; ?> px-3 py-1 rounded-full text-xs font-medium"><?php echo $lock_type_label; ?></span>
                                        </td>
                                        <td class="p-4 font-mono"><?php echo htmlspecialchars($account['last_ip'] ?? 'N/A'); ?></td>
                                        <td class="p-4 text-center font-mono"><?php echo htmlspecialchars($account['failed_attempts']); ?></td>
                                        <td class="p-4">
                                            <span class="time-remaining font-medium text-red-600">
                                                <?php echo $is_permanent ? 'Manual unlock required' : getTimeUntilUnlock($account['lock_until']); ?>
                                                <?php if (!$is_permanent && !empty($account['lock_until'])): ?>
                                                    <span class="countdown-timer" data-unlock-time="<?php echo (new DateTime($account['lock_until']))->getTimestamp(); ?>"></span>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td class="p-4 text-center">
                                            <?php if ($is_permanent): ?>
                                                <a href="?unlock_id=<?php echo $account['id']; ?>" class="bg-green-500 hover:bg-green-600 text-white px-4 py-1.5 rounded-lg text-xs font-medium" onclick="return confirm('Unlock this account?')">
                                                    <i class="fa-solid fa-lock-open mr-1"></i> Unlock
                                                </a>
                                            <?php else: ?>
                                                <button class="bg-gray-300 text-white px-4 py-1.5 rounded-lg text-xs font-medium cursor-not-allowed" disabled>Waiting...</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-500 py-8">No locked accounts at this time.</p>
                <?php endif; ?>
            </div>
            
            <!-- Failed Login Attempts Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
                <h2 class="text-lg font-bold text-gray-800 mb-4 pb-4 border-b">Recent Failed Login Attempts</h2>
                <?php if (!empty($failedLogins)): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-brand-dark text-white text-xs uppercase tracking-wider">
                                    <th class="p-4 font-medium">User</th>
                                    <th class="p-4 font-medium">Username</th>
                                    <th class="p-4 font-medium">IP Address</th>
                                    <th class="p-4 font-medium">Attempt Time</th>
                                    <th class="p-4 font-medium">Reason</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm text-gray-700">
                                <?php foreach ($failedLogins as $attempt): ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="p-4 font-medium"><?php echo htmlspecialchars($attempt['fullname'] ?? 'Unknown'); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($attempt['username'] ?? 'N/A'); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($attempt['ip_address']); ?></td>
                                        <td class="p-4"><?php echo formatTimestamp($attempt['created_at']); ?></td>
                                        <td class="p-4 text-red-600"><?php echo htmlspecialchars($attempt['action']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <!-- Pagination -->
                        <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                            <p class="text-sm text-gray-500">
                                Showing <?php echo $totalFailedLogins > 0 ? $offset + 1 : 0; ?> to <?php echo min($offset + $limit, $totalFailedLogins); ?> of <?php echo $totalFailedLogins; ?> entries
                            </p>
                            <div class="flex gap-2">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-1.5 text-sm text-gray-500 border border-gray-200 rounded-md hover:bg-gray-50">Previous</a>
                                <?php endif; ?>
                                <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-1.5 text-sm text-gray-500 border border-gray-200 rounded-md hover:bg-gray-50">Next</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-500 py-8">No failed login attempts recorded.</p>
                <?php endif; ?>
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

            // Live countdown timers for temporary locks
            const countdownElements = document.querySelectorAll('.countdown-timer');
            countdownElements.forEach(element => {
                const unlockTime = parseInt(element.getAttribute('data-unlock-time')) * 1000;

                const updateCountdown = () => {
                    const now = new Date().getTime();
                    const distance = unlockTime - now;

                    if (distance < 0) {
                        element.parentElement.innerHTML = '<span class="text-green-600">Unlocked</span>';
                        clearInterval(interval);
                        // Optionally, hide the row or refresh the page after a delay
                        setTimeout(() => { window.location.reload(); }, 2000);
                        return;
                    }

                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    element.textContent = `${minutes}m ${seconds}s`;
                };

                const interval = setInterval(updateCountdown, 1000);
            });
        });
    </script>
</body>
</html>
