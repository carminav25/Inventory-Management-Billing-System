<?php
session_start();
require_once "../../config/database.php";
require_once "../../includes/superadmin_auth.php";
require_once "../../includes/superadmin_functions.php";

// Page-specific variables
$pageTitle = "Activity Logs";
$breadcrumbs = [
    ['name' => 'Activity Logs']
];
requireSuperAdmin();

// Get filters
$filters = [];
if (isset($_GET['search_term']) && !empty($_GET['search_term'])) {
    $filters['search_term'] = $_GET['search_term'];
}
if (isset($_GET['action']) && !empty($_GET['action'])) {
    $filters['action'] = $_GET['action'];
}
if (isset($_GET['role']) && !empty($_GET['role'])) {
    $filters['role'] = $_GET['role'];
}
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $filters['start_date'] = $_GET['start_date'];
}
if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $filters['end_date'] = $_GET['end_date'];
}

$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get activity logs
$logs = getActivityLogs($conn, $filters, $limit, $offset);
$totalLogs = countActivityLogs($conn, $filters);
$totalPages = ceil($totalLogs / $limit);

// Stats for cards
$totalLogsToday = countActivityLogs($conn, ['start_date' => date('Y-m-d')]);
$totalFailedLoginsToday = countFailedLoginAttempts($conn, true);
$totalLockedAccounts = getTotalLockedAccounts($conn);
$activitySummary = getActivitySummary($conn);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Inventory System</title>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom brand colors */
        .bg-brand-dark { background-color: #064e3b; }
        .bg-brand-active { background-color: #0f766e; }
        .text-brand-dark { color: #064e3b; }
        .bg-brand-green { background-color: #10b981; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    
    <!-- 
      NOTE: superadmin_dashboard.css was removed from here because its 
      fixed positioning is what caused the sidebar to overlap the main content.
      Pure Tailwind flexbox handles the side-by-side layout automatically. 
    -->
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-[#eef2fa] flex items-center justify-center text-blue-600 text-xl shrink-0"><i class="fa-solid fa-clipboard-list"></i></div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Total Logs</p>
                        <h2 class="text-2xl font-bold text-gray-800"><?php echo number_format($totalLogs ?? 0); ?></h2>
                        <p class="text-[11px] text-gray-400 mt-0.5">All system activities</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-[#ebf5ef] flex items-center justify-center text-green-600 text-xl shrink-0"><i class="fa-regular fa-calendar-check"></i></div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Today's Activities</p>
                        <h2 class="text-2xl font-bold text-gray-800"><?php echo number_format($totalLogsToday ?? 0); ?></h2>
                        <p class="text-[11px] text-gray-400 mt-0.5">Logs recorded today</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-[#fef5e6] flex items-center justify-center text-orange-500 text-xl shrink-0"><i class="fa-solid fa-shield-halved"></i></div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Failed Login Attempts</p>
                        <h2 class="text-2xl font-bold text-gray-800"><?php echo number_format($totalFailedLoginsToday ?? 0); ?></h2>
                        <p class="text-[11px] text-gray-400 mt-0.5">Today</p>
                    </div>
                </div>
                <a href="security_center.php" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 hover:shadow-md hover:border-gray-200 transition-all">
                    <div class="w-12 h-12 rounded-full bg-[#fce8e8] flex items-center justify-center text-red-500 text-xl shrink-0">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-0.5">Locked Accounts</p>
                        <h2 class="text-2xl font-bold text-gray-800"><?php echo number_format($totalLockedAccounts ?? 0); ?></h2>
                        <p class="text-[11px] text-gray-400 mt-0.5">Currently locked</p>
                    </div>
                </a>
            </div>

            <!-- Filter Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
                <form method="GET" action="activity_logs.php" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="relative lg:col-span-2">
                            <input type="text" name="search_term" placeholder="Search by username or action..." class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-brand-green text-[13px] placeholder-gray-400" value="<?php echo htmlspecialchars($filters['search_term'] ?? ''); ?>">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-[13px]"></i>
                        </div>
                        <div class="relative">
                            <select name="role" class="w-full appearance-none bg-white border border-gray-200 text-gray-700 py-2 pl-3 pr-8 rounded-lg text-[13px] focus:outline-none">
                                <option value="">All Roles</option>
                                <option value="Super Admin" <?php echo ($filters['role'] ?? '') === 'Super Admin' ? 'selected' : ''; ?>>Super Admin</option>
                                <option value="Admin" <?php echo ($filters['role'] ?? '') === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="Viewer" <?php echo ($filters['role'] ?? '') === 'Viewer' ? 'selected' : ''; ?>>Viewer</option>
                            </select>
                            <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-[10px] pointer-events-none"></i>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="relative">
                                <label for="start_date" class="absolute -top-2 left-2 text-[10px] bg-white px-1 text-gray-500">From</label>
                                <input type="date" id="start_date" name="start_date" class="w-full pl-3 pr-2 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-brand-green text-[13px] text-gray-700" value="<?php echo htmlspecialchars($filters['start_date'] ?? ''); ?>">
                            </div>
                            <div class="relative">
                                <label for="end_date" class="absolute -top-2 left-2 text-[10px] bg-white px-1 text-gray-500">To</label>
                                <input type="date" id="end_date" name="end_date" class="w-full pl-3 pr-2 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-brand-green text-[13px] text-gray-700" value="<?php echo htmlspecialchars($filters['end_date'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-4 mt-2">
                        <div class="flex items-center gap-3">
                            <button type="submit" class="bg-brand-dark hover:bg-[#082a20] text-white px-5 py-2 rounded-lg text-[13px] font-medium flex items-center gap-2 transition-colors">
                                <i class="fa-solid fa-filter"></i> Apply Filter
                            </button>
                            <a href="activity_logs.php" class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 px-5 py-2 rounded-lg text-[13px] font-medium flex items-center gap-2 transition-colors shadow-sm">
                                <i class="fa-solid fa-arrow-rotate-right"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Main Layout: Table + Side Panels -->
            <div class="flex flex-col xl:flex-row gap-6">
                
                <!-- Center Table Panel -->
                <div class="w-full xl:w-[72%] bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                    
                    <!-- Logs Table -->
                    <div class="overflow-x-auto flex-1 p-4 pt-4">
                        <table class="w-full text-left border-collapse min-w-[850px]">
                            <thead class="bg-brand-dark text-white">
                                <tr class="bg-brand-dark text-white text-[10px] uppercase tracking-wider">
                                    <th class="p-3 rounded-l-md font-medium w-6"></th>
                                    <th class="p-3 font-medium">TIME</th>
                                    <th class="p-3 font-medium">USER</th>
                                    <th class="p-3 font-medium">ACTION</th>
                                    <th class="p-3 font-medium">MODULE</th>
                                    <th class="p-3 font-medium">ROLE</th>
                                    <th class="p-3 font-medium text-center rounded-r-md">STATUS</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs text-gray-700">
                                <?php if (!empty($logs)):
                                    function getInitials($name) {
                                        $words = explode(' ', $name);
                                        $initials = '';
                                        if(isset($words[0]) && !empty($words[0])) $initials .= mb_substr($words[0], 0, 1);
                                        if(isset($words[count($words)-1]) && count($words) > 1 && !empty($words[count($words)-1])) $initials .= mb_substr($words[count($words)-1], 0, 1);
                                        return strtoupper($initials);
                                    }

                                    function getModuleFromAction($action) {
                                        $action_lower = strtolower($action);
                                        if (strpos($action_lower, 'user') !== false || strpos($action_lower, 'login') !== false || strpos($action_lower, 'logout') !== false || strpos($action_lower, 'password') !== false || strpos($action_lower, 'account') !== false) return 'User Management';
                                        if (strpos($action_lower, 'backup') !== false || strpos($action_lower, 'restore') !== false) return 'Backup & Restore';
                                        if (strpos($action_lower, 'report') !== false) return 'Reports';
                                        if (strpos($action_lower, 'product') !== false || strpos($action_lower, 'stock') !== false) return 'Inventory';
                                        if (strpos($action_lower, 'sale') !== false || strpos($action_lower, 'billing') !== false) return 'Sales';
                                        return 'General';
                                    }

                                    function getStatusFromAction($action) {
                                        $action_lower = strtolower($action);
                                        if (strpos($action_lower, 'failed') !== false) {
                                            return ['label' => 'Failed', 'class' => 'bg-red-100 text-red-600'];
                                        }
                                        if (strpos($action_lower, 'warning') !== false) {
                                            return ['label' => 'Warning', 'class' => 'bg-yellow-100 text-yellow-600'];
                                        }
                                        return ['label' => 'Success', 'class' => 'bg-green-100 text-green-600'];
                                    }
                                ?>
                                    <?php foreach ($logs as $log): ?>
                                        <tr class="border-b border-gray-50 hover:bg-gray-50">
                                            <td class="p-3 text-center">
                                                <?php
                                                    $icon = 'fa-solid fa-info-circle text-gray-400';
                                                    $action_lower = strtolower($log['action']);
                                                    if (strpos($action_lower, 'login') !== false && strpos($action_lower, 'failed') === false) $icon = 'fa-solid fa-circle-check text-green-500';
                                                    elseif (strpos($action_lower, 'failed') !== false) $icon = 'fa-solid fa-circle-xmark text-red-500';
                                                    elseif (strpos($action_lower, 'create') !== false) $icon = 'fa-solid fa-user-plus text-blue-500';
                                                    elseif (strpos($action_lower, 'update') !== false) $icon = 'fa-regular fa-pen-to-square text-yellow-500';
                                                    elseif (strpos($action_lower, 'reset') !== false) $icon = 'fa-solid fa-key text-purple-500';
                                                    elseif (strpos($action_lower, 'logout') !== false) $icon = 'fa-solid fa-right-from-bracket text-gray-400';
                                                    elseif (strpos($action_lower, 'backup') !== false) $icon = 'fa-solid fa-database text-yellow-500';
                                                    elseif (strpos($action_lower, 'unlock') !== false) $icon = 'fa-solid fa-lock-open text-green-500';
                                                ?>
                                                <i class="<?php echo $icon; ?> text-sm"></i>
                                            </td>
                                            <td class="p-3">
                                                <div class="text-gray-800 font-medium"><?php echo date('M d, Y', strtotime($log['created_at'])); ?></div>
                                                <div class="text-[11px] text-gray-500"><?php echo date('h:i A', strtotime($log['created_at'])); ?></div>
                                            </td>
                                            <td class="p-3">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="w-7 h-7 rounded-full bg-[#ebf5ef] text-green-700 flex items-center justify-center font-bold text-[10px]"><?php echo getInitials($log['fullname'] ?? 'System'); ?></div>
                                                    <div>
                                                        <div class="font-medium text-gray-800"><?php echo htmlspecialchars($log['fullname'] ?? 'System'); ?></div>
                                                        <div class="text-[10px] text-gray-500"><?php echo htmlspecialchars($log['username'] ?? 'N/A'); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="p-3">
                                                <div class="text-gray-800"><?php echo htmlspecialchars(ucfirst($log['action'])); ?></div>
                                            </td>
                                            <td class="p-3">
                                                <span class="text-gray-600 font-medium text-[11px]"><?php echo getModuleFromAction($log['action']); ?></span>
                                            </td>
                                            <td class="p-3">
                                                <?php
                                                    $role_class = 'bg-gray-100 text-gray-600'; // Default
                                                    if ($log['role'] === 'Super Admin') $role_class = 'bg-orange-100 text-orange-600';
                                                    if ($log['role'] === 'Admin') $role_class = 'bg-blue-100 text-blue-600';
                                                    if ($log['role'] === 'Viewer') $role_class = 'bg-purple-100 text-purple-600';
                                                    $status = getStatusFromAction($log['action']);
                                                ?>
                                                <span class="<?php echo $role_class; ?> px-2.5 py-1 rounded-full text-[10px] font-medium"><?php echo htmlspecialchars($log['role'] ?? 'N/A'); ?></span>
                                            </td>
                                            <td class="p-3 text-center">
                                                <span class="<?php echo $status['class']; ?> px-2.5 py-1 rounded-full text-[10px] font-medium"><?php echo $status['label']; ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center p-10 text-gray-500">No activity logs found for the selected filters.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-white mt-auto">
                        <p class="text-[11px] text-gray-500 font-medium">Showing <?php echo $totalLogs > 0 ? $offset + 1 : 0; ?> to <?php echo min($offset + $limit, $totalLogs); ?> of <?php echo $totalLogs; ?> entries</p>
                        <div class="flex gap-1">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query($filters); ?>" class="px-3 py-1.5 text-[11px] font-medium text-gray-500 border border-gray-200 rounded hover:bg-gray-50">Previous</a>
                            <?php endif; ?>
                            <?php
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $page + 2);

                                if ($startPage > 1) {
                                    echo '<a href="?page=1&'.http_build_query($filters).'" class="w-7 h-7 flex items-center justify-center text-[11px] font-medium text-gray-600 border border-transparent hover:border-gray-200 rounded-md">1</a>';
                                    if ($startPage > 2) echo '<span class="w-7 h-7 flex items-center justify-center text-[11px] text-gray-400">...</span>';
                                }

                                for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&<?php echo http_build_query($filters); ?>" class="w-7 h-7 flex items-center justify-center text-[11px] font-medium <?php echo $i == $page ? 'text-white bg-brand-dark rounded shadow-sm' : 'text-gray-600 border border-transparent hover:border-gray-200 rounded'; ?>"><?php echo $i; ?></a>
                            <?php endfor; 

                                if ($endPage < $totalPages) {
                                    if ($endPage < $totalPages - 1) echo '<span class="w-7 h-7 flex items-center justify-center text-[11px] text-gray-400">...</span>';
                                    echo '<a href="?page='.$totalPages.'&'.http_build_query($filters).'" class="w-7 h-7 flex items-center justify-center text-[11px] font-medium text-gray-600 border border-transparent hover:border-gray-200 rounded-md">'.$totalPages.'</a>';
                                }
                            ?>
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query($filters); ?>" class="px-3 py-1.5 text-[11px] font-medium text-gray-700 border border-gray-200 rounded-md hover:bg-gray-50 shadow-sm">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Summary & Timeline -->
                <div class="w-full xl:w-[28%] flex flex-col gap-6">
                    
                    <!-- Activity Summary Widget -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <div class="border-l-2 border-brand-green pl-2 mb-5">
                            <h3 class="text-[10px] font-bold text-gray-700 uppercase tracking-wider">Activity Summary</h3>
                        </div>
                        
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#ebf5ef] text-green-600 flex items-center justify-center shrink-0"><i class="fa-regular fa-clock text-sm"></i></div>
                                <div>
                                    <p class="text-[12px] font-semibold text-gray-800">Last System Login</p>
                                    <p class="text-[10px] text-gray-500"><?php echo $activitySummary['last_login'] ? formatTimestamp($activitySummary['last_login']) : 'No logins recorded'; ?></p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#ebf5ef] text-green-600 flex items-center justify-center shrink-0"><i class="fa-solid fa-user-check text-sm"></i></div>
                                <div>
                                    <p class="text-[12px] font-semibold text-gray-800">Most Active User</p>
                                    <p class="text-[10px] text-gray-500"><?php echo htmlspecialchars($activitySummary['most_active_user']['fullname']); ?> (<?php echo $activitySummary['most_active_user']['log_count']; ?> logs)</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-yellow-50 text-yellow-500 flex items-center justify-center shrink-0"><i class="fa-solid fa-chart-line text-sm"></i></div>
                                <div>
                                    <p class="text-[12px] font-semibold text-gray-800" title="<?php echo htmlspecialchars($activitySummary['most_common_action']['action']); ?>">Most Common Action</p>
                                    <p class="text-[10px] text-gray-500 truncate w-40"><?php echo htmlspecialchars($activitySummary['most_common_action']['action']); ?> (<?php echo $activitySummary['most_common_action']['action_count']; ?> times)</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center shrink-0"><i class="fa-solid fa-shield-halved text-sm"></i></div>
                                <div>
                                    <p class="text-[12px] font-semibold text-gray-800">Failed Logins (Today)</p>
                                    <p class="text-[10px] text-gray-500"><?php echo number_format($totalFailedLoginsToday); ?> attempts</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#eef2fa] text-blue-500 flex items-center justify-center shrink-0"><i class="fa-solid fa-users text-sm"></i></div>
                                <div>
                                    <p class="text-[12px] font-semibold text-gray-800">Active Users (15 min)</p>
                                    <p class="text-[10px] text-gray-500"><?php echo $activitySummary['online_users']; ?> users recently active</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity Timeline Widget -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex-1 flex flex-col">
                        <div class="border-l-2 border-brand-green pl-2 mb-5">
                            <h3 class="text-[10px] font-bold text-gray-700 uppercase tracking-wider">Recent Activity Timeline</h3>
                        </div>
                        
                        <div class="relative border-l border-gray-200 ml-2.5 pb-2 space-y-5 flex-1">
                            <?php 
                                if(function_exists('getRecentActivityLogs')): 
                                    $recentLogs = getRecentActivityLogs($conn, 10); 
                                    foreach($recentLogs as $log): 
                            ?>
                            <div class="relative pl-6">
                                <div class="absolute w-[9px] h-[9px] bg-white rounded-full border-2 border-green-500 -left-[5px] top-1"></div>
                                <p class="text-[10px] text-gray-800 font-semibold mb-0.5"><?php echo date('h:i A', strtotime($log['created_at'])); ?></p>
                                <p class="text-[11px] text-gray-500"><?php echo htmlspecialchars($log['fullname'] ?? 'System'); ?> <?php echo htmlspecialchars($log['action']); ?></p>
                            </div>
                            <?php 
                                    endforeach; 
                                else:
                            ?>
                                <p class="text-xs text-gray-400">Timeline function not available.</p>
                            <?php endif; ?>
                        </div>

        </div>
    </main>
    <script>
        // Sidebar toggle functionality for mobile responsiveness
        const sidebar = document.querySelector('aside');
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
        });
    </script>
</body>
</html>
