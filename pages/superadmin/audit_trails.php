<?php
$pageTitle = "Audit Trail";
$breadcrumbs = [
    ["name" => "Audit Trail"]
];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";

requireAdmin();

function audit_table_columns(mysqli $conn): array
{
    $columns = [];
    $result = $conn->query("SHOW COLUMNS FROM activity_logs");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
    }
    return $columns;
}

function audit_module_from_action(string $action): string
{
    $text = strtolower($action);
    $map = [
        'Product' => ['product', 'stock', 'inventory'],
        'Supplier' => ['supplier'],
        'Inventory In' => ['delivery', 'received'],
        'Inventory Out' => ['sale', 'invoice', 'sold'],
        'Supplier Returns' => ['return'],
        'Backup' => ['backup', 'restore'],
        'Users' => ['user', 'login', 'password'],
    ];

    foreach ($map as $module => $needles) {
        foreach ($needles as $needle) {
            if (strpos($text, $needle) !== false) {
                return $module;
            }
        }
    }

    return 'System';
}

function audit_status_from_action(string $action): array
{
    $text = strtolower($action);
    if (strpos($text, 'failed') !== false || strpos($text, 'deleted') !== false || strpos($text, 'unauthorized') !== false) {
        return ['label' => 'Attention', 'class' => 'app-badge-danger'];
    }
    if (strpos($text, 'updated') !== false || strpos($text, 'edited') !== false || strpos($text, 'restored') !== false) {
        return ['label' => 'Updated', 'class' => 'app-badge-warning'];
    }
    if (strpos($text, 'created') !== false || strpos($text, 'added') !== false || strpos($text, 'logged in') !== false) {
        return ['label' => 'Success', 'class' => 'app-badge-success'];
    }
    return ['label' => 'Recorded', 'class' => 'app-badge-info'];
}

$hasActivityTable = false;
$tableCheck = $conn->query("SHOW TABLES LIKE 'activity_logs'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    $hasActivityTable = true;
}

$columns = $hasActivityTable ? audit_table_columns($conn) : [];
$hasModule = in_array('module', $columns, true);
$hasStatus = in_array('status', $columns, true);

$search = trim($_GET['search'] ?? '');
$roleFilter = trim($_GET['role'] ?? '');
$moduleFilter = trim($_GET['module'] ?? '');
$dateFilter = trim($_GET['date'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$where = ["1=1"];
if ($hasActivityTable && $search !== '') {
    $safe = $conn->real_escape_string($search);
    $where[] = "(fullname LIKE '%$safe%' OR username LIKE '%$safe%' OR action LIKE '%$safe%' OR ip_address LIKE '%$safe%')";
}
if ($hasActivityTable && $roleFilter !== '') {
    $safe = $conn->real_escape_string($roleFilter);
    $where[] = "role = '$safe'";
}
if ($hasActivityTable && $dateFilter !== '') {
    $safe = $conn->real_escape_string($dateFilter);
    $where[] = "DATE(created_at) = '$safe'";
}
if ($hasActivityTable && $moduleFilter !== '' && $hasModule) {
    $safe = $conn->real_escape_string($moduleFilter);
    $where[] = "module = '$safe'";
}

$whereSql = implode(' AND ', $where);
$logs = [];
$totalLogs = 0;
$todayLogs = 0;
$uniqueUsers = 0;
$attentionLogs = 0;

if ($hasActivityTable) {
    $countResult = $conn->query("SELECT COUNT(*) AS total FROM activity_logs WHERE $whereSql");
    if ($countResult && $row = $countResult->fetch_assoc()) {
        $totalLogs = (int)$row['total'];
    }

    $todayResult = $conn->query("SELECT COUNT(*) AS total FROM activity_logs WHERE DATE(created_at) = CURDATE()");
    if ($todayResult && $row = $todayResult->fetch_assoc()) {
        $todayLogs = (int)$row['total'];
    }

    $usersResult = $conn->query("SELECT COUNT(DISTINCT user_id) AS total FROM activity_logs");
    if ($usersResult && $row = $usersResult->fetch_assoc()) {
        $uniqueUsers = (int)$row['total'];
    }

    $attentionResult = $conn->query("SELECT COUNT(*) AS total FROM activity_logs WHERE action LIKE '%failed%' OR action LIKE '%deleted%' OR action LIKE '%unauthorized%'");
    if ($attentionResult && $row = $attentionResult->fetch_assoc()) {
        $attentionLogs = (int)$row['total'];
    }

    $selectModule = $hasModule ? "module" : "'' AS module";
    $selectStatus = $hasStatus ? "status" : "'' AS status";
    $result = $conn->query("
        SELECT id, user_id, fullname, username, role, action, ip_address, created_at, $selectModule, $selectStatus
        FROM activity_logs
        WHERE $whereSql
        ORDER BY created_at DESC, id DESC
        LIMIT $limit OFFSET $offset
    ");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
    }
}

if ($moduleFilter !== '' && !$hasModule) {
    $logs = array_values(array_filter($logs, function ($log) use ($moduleFilter) {
        return audit_module_from_action($log['action'] ?? '') === $moduleFilter;
    }));
}

$totalPages = max(1, (int)ceil($totalLogs / $limit));
$queryBase = $_GET;
unset($queryBase['page']);

include "sidebar.php";
include "topbar.php";
?>

<main class="ml-0 md:ml-[270px] mt-[75px] min-h-screen app-page p-6 pb-24 transition-all duration-300">
    <div class="app-shell mx-auto space-y-6">
        <section class="app-page-header">
            <div>
                <h1>Audit Trail</h1>
                <p>Review inventory activity, user actions, IP addresses, and system events.</p>
            </div>
            <div class="text-sm text-slate-500 font-semibold">
                <i class="fa-regular fa-calendar text-[#0B7A4B] mr-2"></i><?= date('F j, Y'); ?>
            </div>
        </section>

        <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
            <div class="app-stat-card">
                <div><div class="label">Total Logs</div><div class="value"><?= number_format($totalLogs); ?></div></div>
                <div class="icon bg-emerald-50 text-emerald-700"><i class="fa-solid fa-clock-rotate-left"></i></div>
            </div>
            <div class="app-stat-card">
                <div><div class="label">Today's Logs</div><div class="value"><?= number_format($todayLogs); ?></div></div>
                <div class="icon bg-blue-50 text-blue-700"><i class="fa-solid fa-calendar-day"></i></div>
            </div>
            <div class="app-stat-card">
                <div><div class="label">Active Users</div><div class="value"><?= number_format($uniqueUsers); ?></div></div>
                <div class="icon bg-emerald-50 text-emerald-700"><i class="fa-solid fa-users"></i></div>
            </div>
            <div class="app-stat-card">
                <div><div class="label">Needs Review</div><div class="value"><?= number_format($attentionLogs); ?></div></div>
                <div class="icon bg-red-50 text-red-700"><i class="fa-solid fa-triangle-exclamation"></i></div>
            </div>
        </section>

        <section class="app-filter">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-7 gap-3">
                <div class="xl:col-span-2 relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input class="app-input w-full pl-11" type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Search user, activity, IP">
                </div>
                <select class="app-input w-full" name="role">
                    <option value="">All Roles</option>
                    <?php foreach (['Admin', 'Super Admin', 'Viewer'] as $role): ?>
                        <option value="<?= $role; ?>" <?= $roleFilter === $role ? 'selected' : ''; ?>><?= $role; ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="app-input w-full" name="module">
                    <option value="">All Modules</option>
                    <?php foreach (['Product', 'Supplier', 'Inventory In', 'Inventory Out', 'Supplier Returns', 'Backup', 'Users', 'System'] as $module): ?>
                        <option value="<?= $module; ?>" <?= $moduleFilter === $module ? 'selected' : ''; ?>><?= $module; ?></option>
                    <?php endforeach; ?>
                </select>
                <input class="app-input w-full" type="date" name="date" value="<?= htmlspecialchars($dateFilter); ?>">
                <button class="app-btn app-btn-primary" type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
                <div class="flex gap-3">
                    <a class="app-btn app-btn-light flex-1" href="audit_trails.php"><i class="fa-solid fa-rotate-left"></i></a>
                    <button class="app-btn app-btn-secondary flex-1" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i></button>
                </div>
            </form>
        </section>

        <section class="app-table-card">
            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Module</th>
                            <th>Activity</th>
                            <th>IP Address</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$hasActivityTable): ?>
                            <tr><td colspan="8" class="text-center text-slate-500 py-12">The activity_logs table does not exist yet. Run setup_database.php to initialize audit logging.</td></tr>
                        <?php elseif (empty($logs)): ?>
                            <tr><td colspan="8" class="text-center text-slate-500 py-12">No audit records match the selected filters.</td></tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <?php
                                    $activity = $log['action'] ?? '';
                                    $module = $log['module'] ?: audit_module_from_action($activity);
                                    $status = $log['status'] ? ['label' => $log['status'], 'class' => 'app-badge-muted'] : audit_status_from_action($activity);
                                    $timestamp = strtotime($log['created_at'] ?? 'now');
                                ?>
                                <tr>
                                    <td class="font-semibold text-slate-800"><?= date('M d, Y', $timestamp); ?></td>
                                    <td class="text-slate-500"><?= date('h:i A', $timestamp); ?></td>
                                    <td>
                                        <div class="font-semibold text-slate-800"><?= htmlspecialchars($log['fullname'] ?: $log['username'] ?: 'System'); ?></div>
                                        <div class="text-xs text-slate-500"><?= htmlspecialchars($log['username'] ?: 'system'); ?></div>
                                    </td>
                                    <td><span class="app-badge app-badge-muted"><?= htmlspecialchars($log['role'] ?: 'Unknown'); ?></span></td>
                                    <td class="font-semibold text-slate-700"><?= htmlspecialchars($module); ?></td>
                                    <td class="max-w-xl text-slate-600"><?= htmlspecialchars($activity ?: 'No activity details provided.'); ?></td>
                                    <td class="font-mono text-slate-500"><?= htmlspecialchars($log['ip_address'] ?: 'N/A'); ?></td>
                                    <td><span class="app-badge <?= $status['class']; ?>"><?= htmlspecialchars($status['label']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-5 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm text-slate-500 mb-0">Showing page <?= $page; ?> of <?= $totalPages; ?></p>
                <div class="app-pagination flex items-center gap-2">
                    <?php $prev = max(1, $page - 1); $next = min($totalPages, $page + 1); ?>
                    <?php $queryBase['page'] = $prev; ?>
                    <a href="?<?= http_build_query($queryBase); ?>">Previous</a>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <?php $queryBase['page'] = $i; ?>
                        <a class="<?= $i === $page ? 'active' : ''; ?>" href="?<?= http_build_query($queryBase); ?>"><?= $i; ?></a>
                    <?php endfor; ?>
                    <?php $queryBase['page'] = $next; ?>
                    <a href="?<?= http_build_query($queryBase); ?>">Next</a>
                </div>
            </div>
        </section>
    </div>
</main>

<?php include_once('../../includes/footer.php'); ?>
