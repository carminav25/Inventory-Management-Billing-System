<?php
session_start();

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";
require_once "../../includes/superadmin_functions.php"; 

// Ensure user is a Super Admin
requireAdmin();

$pageTitle = "Dashboard Overview";
$dbName = 'inventory_system'; // Database name for size query

// --- PHP DATA FETCHING ---
try {
    // KPI Cards
    $totalUsers = function_exists('getTotalUsers') ? getTotalUsers($conn) : 0;
    $adminCount = function_exists('getTotalAdmins') ? getTotalAdmins($conn) : 0;
    $viewerCount = function_exists('getTotalViewers') ? getTotalViewers($conn) : 0;
    $superAdminCount = function_exists('getTotalSuperAdmins') ? getTotalSuperAdmins($conn) : 0;
    
    $lockedUsers = function_exists('getTotalLockedAccounts') ? getTotalLockedAccounts($conn) : 0;
    $failedLoginsToday = function_exists('countFailedLoginAttempts') ? countFailedLoginAttempts($conn, true) : 0;
    $totalBackups = function_exists('getTotalBackups') ? getTotalBackups($conn) : 0;

    // Database / System Info
    $isDbHealthy = function_exists('isDatabaseHealthy') ? isDatabaseHealthy($conn) : false;
    $dbSizeMB = function_exists('getDatabaseSize') ? getDatabaseSize($conn, $dbName) : '0 MB';
    $phpVersion = phpversion();
    
    // Fetch MySQL Version
    $mysqlVersionResult = $conn->query("SELECT VERSION() as v");
    $mysqlVersion = ($mysqlVersionResult && $mysqlVersionResult->num_rows > 0) ? $mysqlVersionResult->fetch_assoc()['v'] : 'Unknown';

    // Last Backup Info
    $lastBackupDateRaw = function_exists('getLastBackupDate') ? getLastBackupDate($conn) : null;
    $lastBackup = $lastBackupDateRaw ? date('M d, Y h:i A', strtotime($lastBackupDateRaw)) : "No backups";
    $lastBackupShort = $lastBackupDateRaw ? date('Today, h:i A', strtotime($lastBackupDateRaw)) : "N/A";
    $backupStatus = function_exists('getBackupStatus') ? getBackupStatus($conn) : "Pending";

    // Recent Activity
    $recentActivities = function_exists('getRecentActivityLogs') ? getRecentActivityLogs($conn, 4) : [];

    // --- CHART DATA PREPARATION ---
    // Role Distribution Percentages
    $totalCalculated = $superAdminCount + $adminCount + $viewerCount;
    $saPct = $totalCalculated > 0 ? round(($superAdminCount / $totalCalculated) * 100) : 0;
    $admPct = $totalCalculated > 0 ? round(($adminCount / $totalCalculated) * 100) : 0;
    $viewPct = $totalCalculated > 0 ? round(($viewerCount / $totalCalculated) * 100) : 0;

    // Monthly Registrations (This Year) - Dynamic Fallback
    $monthlyReg = array_fill(1, 12, 0); // Initialize Jan-Dec with 0
    $yearQuery = $conn->query("
        SELECT MONTH(created_at) as month, COUNT(*) as count 
        FROM users 
        WHERE YEAR(created_at) = YEAR(CURDATE()) 
        GROUP BY MONTH(created_at)
    ");
    if ($yearQuery) {
        while ($row = $yearQuery->fetch_assoc()) {
            $monthlyReg[(int)$row['month']] = (int)$row['count'];
        }
    }
    $monthlyRegData = array_values($monthlyReg); // Re-index for JS

} catch (Exception $e) {
    // Fallback zero-states if queries fail
    $totalUsers = $adminCount = $viewerCount = $superAdminCount = $lockedUsers = $failedLoginsToday = $totalBackups = 0;
    $saPct = $admPct = $viewPct = 0;
    $monthlyRegData = [0,0,0,0,0,0,0,0,0,0,0,0];
    $recentActivities = [];
    $isDbHealthy = false;
    $dbSizeMB = '0 MB';
    $phpVersion = 'Error';
    $mysqlVersion = 'Error';
    $lastBackup = "Error";
    $lastBackupShort = "Error";
    $backupStatus = "Error";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Administrator Dashboard</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .chart-container { position: relative; height: 220px; width: 100%; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Forces the sidebar to use the ISU Brand Dark Green */
        aside, #sidebar, .sidebar { background-color: #045c42 !important; }
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

        <!-- DASHBOARD SCROLLABLE CONTAINER -->
        <main class="flex-1 overflow-y-auto bg-[#f4f7f6]" id="dashboard-content">
            <div class="p-6 max-w-7xl mx-auto space-y-6">

                <!-- WELCOME & QUICK STATUS -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-[22px] text-gray-800">Welcome back, <span class="font-bold text-emerald-800"><?php echo htmlspecialchars(strtoupper($_SESSION['fullname'] ?? 'CARMINA VALLEJO')); ?></span></h1>
                        <p class="text-[13px] text-gray-500 mt-1">Super Administrator Dashboard</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="bg-white border border-gray-200 rounded-full px-4 py-2 flex items-center gap-3 shadow-sm">
                            <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div class="leading-tight pr-2">
                                <div class="text-[11px] font-bold text-gray-800">Database</div>
                                <div class="text-[10px] text-emerald-600 font-semibold"><?php echo $isDbHealthy ? 'Healthy' : 'Error'; ?></div>
                            </div>
                        </div>
                        <div class="bg-white border border-gray-200 rounded-full px-4 py-2 flex items-center gap-3 shadow-sm">
                            <div class="w-8 h-8 rounded-full bg-sky-50 text-sky-600 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-database"></i>
                            </div>
                            <div class="leading-tight pr-2">
                                <div class="text-[11px] font-bold text-gray-800">Last Backup</div>
                                <div class="text-[10px] text-gray-500"><?php echo $lastBackupShort; ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI CARDS (6 Cols) -->
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
                    <!-- Total Users -->
                    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-gray-400 tracking-wider uppercase">TOTAL USERS</span>
                            <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <i class="fa-solid fa-users text-xs"></i>
                            </div>
                        </div>
                        <div class="mt-2">
                            <h3 class="text-3xl font-black text-gray-800"><?php echo $totalUsers; ?></h3>
                            <p class="text-[10px] text-gray-500 mt-1">All registered users</p>
                        </div>
                    </div>
                    <!-- Admins -->
                    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-gray-400 tracking-wider uppercase">ADMINS</span>
                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                                <i class="fa-solid fa-user-shield text-xs"></i>
                            </div>
                        </div>
                        <div class="mt-2">
                            <h3 class="text-3xl font-black text-gray-800"><?php echo $adminCount; ?></h3>
                            <p class="text-[10px] text-gray-500 mt-1">System administrators</p>
                        </div>
                    </div>
                    <!-- Viewers -->
                    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-gray-400 tracking-wider uppercase">VIEWERS</span>
                            <div class="w-8 h-8 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </div>
                        </div>
                        <div class="mt-2">
                            <h3 class="text-3xl font-black text-gray-800"><?php echo $viewerCount; ?></h3>
                            <p class="text-[10px] text-gray-500 mt-1">System viewers</p>
                        </div>
                    </div>
                    <!-- Locked Accounts -->
                    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-gray-400 tracking-wider uppercase">LOCKED ACCOUNTS</span>
                            <div class="w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center">
                                <i class="fa-solid fa-lock text-xs"></i>
                            </div>
                        </div>
                        <div class="mt-2">
                            <h3 class="text-3xl font-black text-gray-800"><?php echo $lockedUsers; ?></h3>
                            <p class="text-[10px] text-gray-500 mt-1">Require attention</p>
                        </div>
                    </div>
                    <!-- Failed Logins Today -->
                    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-gray-400 tracking-wider uppercase">FAILED LOGINS</span>
                            <div class="w-8 h-8 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center">
                                <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                            </div>
                        </div>
                        <div class="mt-2">
                            <h3 class="text-3xl font-black text-gray-800"><?php echo $failedLoginsToday; ?></h3>
                            <p class="text-[10px] text-gray-500 mt-1">Failed attempts</p>
                        </div>
                    </div>
                    <!-- Backups -->
                    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-gray-400 tracking-wider uppercase">BACKUPS</span>
                            <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <i class="fa-solid fa-database text-xs"></i>
                            </div>
                        </div>
                        <div class="mt-2">
                            <h3 class="text-3xl font-black text-gray-800"><?php echo $totalBackups; ?></h3>
                            <p class="text-[10px] text-gray-500 mt-1">Total backups</p>
                        </div>
                    </div>
                </div>

                <!-- ALERTS & QUICK ACTIONS -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Priority Alerts -->
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                        <div class="p-4 border-b border-gray-50 flex items-center gap-2">
                            <i class="fa-solid fa-bullhorn text-emerald-600"></i>
                            <h3 class="font-bold text-[13px] text-gray-800 uppercase tracking-wide">Priority Alerts</h3>
                        </div>
                        <div class="p-2 space-y-1">
                            <a href="security_center.php" class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition group border-b border-gray-50">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center text-red-500">
                                        <i class="fa-solid fa-lock text-xs"></i>
                                    </div>
                                    <span class="text-sm text-gray-700 font-medium"><?php echo $lockedUsers; ?> locked account(s) requires attention.</span>
                                </div>
                                <i class="fa-solid fa-chevron-right text-gray-300 text-xs group-hover:text-emerald-500"></i>
                            </a>
                            <a href="security_center.php" class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition group border-b border-gray-50">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-orange-50 flex items-center justify-center text-orange-500">
                                        <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                                    </div>
                                    <span class="text-sm text-gray-700 font-medium"><?php echo $failedLoginsToday; ?> failed login attempts today.</span>
                                </div>
                                <i class="fa-solid fa-chevron-right text-gray-300 text-xs group-hover:text-emerald-500"></i>
                            </a>
                            <a href="backup_restore.php" class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-500">
                                        <i class="fa-solid fa-database text-xs"></i>
                                    </div>
                                    <span class="text-sm text-gray-700 font-medium">Last backup was <?php echo strtolower($lastBackupShort); ?>.</span>
                                </div>
                                <i class="fa-solid fa-chevron-right text-gray-300 text-xs group-hover:text-emerald-500"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm flex flex-col">
                        <div class="p-4 border-b border-gray-50 flex items-center gap-2">
                            <i class="fa-solid fa-bolt text-emerald-600"></i>
                            <h3 class="font-bold text-[13px] text-gray-800 uppercase tracking-wide">Quick Actions</h3>
                        </div>
                        <div class="p-4 grid grid-cols-2 md:grid-cols-4 gap-3 flex-1 items-center">
                            <a href="users.php?action=create" class="bg-[#34C38F] hover:bg-[#1E8F67] text-white rounded-xl p-4 flex flex-col items-center justify-center gap-2 transition shadow-sm h-full">
                                <i class="fa-solid fa-user-plus text-2xl"></i>
                                <span class="text-xs font-semibold mt-1 text-center">Create User</span>
                            </a>
                            <a href="users.php" class="bg-[#5A9BFF] hover:bg-[#3D7AE6] text-white rounded-xl p-4 flex flex-col items-center justify-center gap-2 transition shadow-sm h-full">
                                <i class="fa-solid fa-users-gear text-2xl"></i>
                                <span class="text-xs font-semibold mt-1 text-center">Manage Users</span>
                            </a>
                            <a href="security_center.php" class="bg-[#E76B6B] hover:bg-[#D55454] text-white rounded-xl p-4 flex flex-col items-center justify-center gap-2 transition shadow-sm h-full">
                                <i class="fa-solid fa-shield-halved text-2xl"></i>
                                <span class="text-xs font-semibold mt-1 text-center leading-tight">Security Center</span>
                            </a>
                            <a href="backup_restore.php" class="bg-[#0F6D57] hover:bg-[#1E8F67] text-white rounded-xl p-4 flex flex-col items-center justify-center gap-2 transition shadow-sm h-full">
                                <i class="fa-solid fa-database text-2xl"></i>
                                <span class="text-xs font-semibold mt-1 text-center leading-tight">Backup & Restore</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CHARTS AND ACTIVITY ROW -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Line Chart -->
                    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm flex flex-col">
                        <h3 class="font-bold text-[11px] text-gray-800 uppercase tracking-wide mb-4">USER REGISTRATION <span class="text-gray-400 font-normal">(THIS YEAR)</span></h3>
                        <div class="chart-container flex-1">
                            <canvas id="registrationChart"></canvas>
                        </div>
                    </div>

                    <!-- Donut Chart -->
                    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm flex flex-col">
                        <h3 class="font-bold text-[11px] text-gray-800 uppercase tracking-wide mb-4">ROLE DISTRIBUTION</h3>
                        <div class="flex-1 flex flex-col md:flex-row items-center justify-center gap-6">
                            <div class="w-32 h-32 relative">
                                <canvas id="roleChart"></canvas>
                            </div>
                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-2.5 h-2.5 rounded-full bg-[#10b981]"></div>
                                    <div class="text-xs">
                                        <p class="font-semibold text-gray-700">Super Admin</p>
                                        <p class="text-gray-500"><?php echo $superAdminCount; ?> (<?php echo $saPct; ?>%)</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-2.5 h-2.5 rounded-full bg-[#0B7A4B]"></div>
                                    <div class="text-xs">
                                        <p class="font-semibold text-gray-700">Admin</p>
                                        <p class="text-gray-500"><?php echo $adminCount; ?> (<?php echo $admPct; ?>%)</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-2.5 h-2.5 rounded-full bg-[#f59e0b]"></div>
                                    <div class="text-xs">
                                        <p class="font-semibold text-gray-700">Viewer</p>
                                        <p class="text-gray-500"><?php echo $viewerCount; ?> (<?php echo $viewPct; ?>%)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity Logs -->
                    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-[11px] text-gray-800 uppercase tracking-wide">RECENT ACTIVITY</h3>
                            <a href="activity_logs.php" class="text-[11px] font-bold text-emerald-600 hover:underline">View All</a>
                        </div>
                        <div class="space-y-4 flex-1">
                            <?php if(!empty($recentActivities)): ?>
                                <?php foreach($recentActivities as $act): 
                                    $actionLow = strtolower($act['action']);
                                    $icon = "fa-bolt text-emerald-500"; $bg = "bg-emerald-50";
                                    
                                    if(strpos($actionLow, 'locked') !== false) {
                                        $icon = "fa-lock text-red-500"; $bg = "bg-red-50";
                                    } elseif(strpos($actionLow, 'reset password') !== false) {
                                        $icon = "fa-key text-blue-500"; $bg = "bg-blue-50";
                                    } elseif(strpos($actionLow, 'backup') !== false) {
                                        $icon = "fa-database text-sky-500"; $bg = "bg-sky-50";
                                    } elseif(strpos($actionLow, 'login') !== false || strpos($actionLow, 'logged in') !== false) {
                                        $icon = "fa-eye text-amber-500"; $bg = "bg-amber-50";
                                        if(strpos($actionLow, 'failed') !== false) { $icon = "fa-triangle-exclamation text-orange-500"; $bg = "bg-orange-50"; }
                                    } elseif(strpos($actionLow, 'create') !== false) {
                                        $icon = "fa-user-plus text-emerald-500"; $bg = "bg-emerald-50";
                                    }
                                ?>
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full <?php echo $bg; ?> flex items-center justify-center shrink-0 mt-0.5">
                                        <i class="fa-solid <?php echo $icon; ?> text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-semibold text-gray-800 leading-snug">
                                            <span class="font-bold"><?php echo htmlspecialchars($act['username'] ?? 'System'); ?></span> 
                                            <span class="font-normal text-gray-600"><?php echo htmlspecialchars($act['action']); ?></span>
                                        </p>
                                        <p class="text-[10px] text-gray-400 mt-0.5">
                                            <?php echo date('d M Y, h:i A', strtotime($act['created_at'])); ?>
                                        </p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-xs text-gray-400 text-center py-4">No recent activities found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- SYSTEM HEALTH FOOTER -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mt-2">
                    <h3 class="font-bold text-[11px] text-gray-800 uppercase tracking-wide mb-4">SYSTEM HEALTH</h3>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 divide-x divide-gray-100">
                        <div class="flex items-center gap-3 px-2">
                            <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-emerald-600">
                                <i class="fa-solid fa-database text-lg"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-800">Database</p>
                                <p class="text-[10px] font-semibold text-emerald-600">Healthy</p>
                                <p class="text-[9px] text-gray-400">Connected</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 px-4">
                            <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-600">
                                <i class="fa-solid fa-code text-lg"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-800">PHP Version</p>
                                <p class="text-[10px] text-gray-600"><?php echo $phpVersion; ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 px-4">
                            <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-600">
                                <i class="fa-solid fa-server text-lg"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-800">MySQL Version</p>
                                <p class="text-[10px] text-gray-600 truncate w-24" title="<?php echo $mysqlVersion; ?>"><?php echo $mysqlVersion; ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 px-4">
                            <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-600">
                                <i class="fa-solid fa-hard-drive text-lg"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-800">Disk Usage</p>
                                <p class="text-[10px] text-gray-600"><?php echo $dbSizeMB; ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 px-4">
                            <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-emerald-600">
                                <i class="fa-solid fa-circle-check text-lg"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-800">Backup Status</p>
                                <p class="text-[10px] font-semibold text-emerald-600"><?php echo $backupStatus; ?></p>
                                <p class="text-[9px] text-gray-400"><?php echo $lastBackupShort; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer spacing -->
                <div class="pb-6"></div>

            </div>
        </main>
    </div>

    <!-- Chart.js & AJAX Script -->
    <script>
        let regChartInstance = null;
        let roleChartInstance = null;

        function initCharts() {
            // Line Chart: Yearly Registrations
            const ctxReg = document.getElementById('registrationChart');
            if (ctxReg) {
                if(regChartInstance) regChartInstance.destroy();
                regChartInstance = new Chart(ctxReg.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        datasets: [{
                            label: 'Users Registered',
                            data: <?php echo json_encode($monthlyRegData); ?>,
                            borderColor: '#10b981', // Emerald green
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            fill: true,
                            tension: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { 
                                beginAtZero: true, 
                                max: Math.max(...<?php echo json_encode($monthlyRegData); ?>, 10), // Scale nicely
                                ticks: { stepSize: 2, font: { size: 9 }, color: '#9ca3af' }, 
                                grid: { color: '#f3f4f6' } 
                            },
                            x: { 
                                ticks: { font: { size: 9 }, color: '#9ca3af' }, 
                                grid: { display: false } 
                            }
                        }
                    }
                });
            }

            // Donut Chart: Role Distribution
            const ctxRole = document.getElementById('roleChart');
            if (ctxRole) {
                if(roleChartInstance) roleChartInstance.destroy();
                roleChartInstance = new Chart(ctxRole.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Super Admin', 'Admin', 'Viewer'],
                        datasets: [{
                            data: [<?php echo $superAdminCount; ?>, <?php echo $adminCount; ?>, <?php echo $viewerCount; ?>],
                            backgroundColor: ['#10b981', '#0B7A4B', '#f59e0b'],
                            borderWidth: 0,
                            cutout: '65%'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { enabled: true } }
                    }
                });
            }
        }

        // Mobile Sidebar Toggle
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        function toggleSidebar() {
            if(sidebar) sidebar.classList.toggle('-translate-x-full');
            if(overlay) overlay.classList.toggle('hidden');
        }
        if(sidebarToggle) sidebarToggle.addEventListener('click', toggleSidebar);
        if(overlay) overlay.addEventListener('click', toggleSidebar);

        // Initialize Charts on load
        window.addEventListener('DOMContentLoaded', initCharts);

        // AJAX Dashboard Auto-Refresh (Live updates without reloading full page)
        setInterval(async () => {
            try {
                // Fetch the current page URL silently in the background
                const response = await fetch(window.location.href);
                const html = await response.text();
                
                // Parse the returned HTML string into a document
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Swap just the main dashboard content container 
                const newContent = doc.getElementById('dashboard-content').innerHTML;
                document.getElementById('dashboard-content').innerHTML = newContent;
                
                // Re-initialize the charts so they re-render correctly after DOM swap
                initCharts();
            } catch (error) {
                console.error("AJAX background refresh failed:", error);
            }
        }, 30000); // 30 seconds

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