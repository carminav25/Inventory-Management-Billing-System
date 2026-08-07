<?php
session_start();

require_once "../../config/database.php";
require_once "../../includes/superadmin_auth.php";
require_once "../../includes/superadmin_functions.php";
require_once "../../includes/activity_log.php";

// Page-specific variables
$pageTitle = "Backup & Restore";
$breadcrumbs = [['name' => 'Backup & Restore']];

// Check if user is Super Admin
requireSuperAdmin();

$message = '';
$error = '';
$backups = [];

if (isset($_SESSION['restore_success'])) {
    $message = $_SESSION['restore_success'];
    unset($_SESSION['restore_success']);
}

if (isset($_SESSION['restore_error'])) {
    $error = $_SESSION['restore_error'];
    unset($_SESSION['restore_error']);
}

// Get stats for cards
$lastBackupDate = getLastBackupDate($conn);
$totalBackups = getTotalBackups($conn);
$dbSize = getDatabaseSize($conn, $dbName);
$backupStatus = getBackupStatus($conn);

// Get backup directory
$backupDir = __DIR__ . '/../../backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Handle backup download
if (isset($_GET['download'])) {
    $filename = basename($_GET['download']);
    $filepath = $backupDir . '/' . $filename;

    // Security check: ensure the file is a backup file and exists
    if (file_exists($filepath) && strpos($filename, 'backup_') === 0) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit();
    }
}

// Get list of backups
$sql = "SELECT backup_history.*, users.firstname, users.lastname FROM backup_history LEFT JOIN users ON users.id=backup_history.created_by ORDER BY backup_date DESC";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $backups[] = $row;
}

function formatBytes(int|float $bytes): string {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
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
            <!-- Messages -->
            <?php if ($message): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert">
                    <p class="font-bold">Success</p>
                    <p><?php echo $message; ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
                    <p class="font-bold">Error</p>
                    <p><?php echo $error; ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Last Backup -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Last Backup</h3>
                    <p class="text-2xl font-bold text-gray-800 mt-2">
                        <?php echo $lastBackupDate ? date('M d, Y', strtotime($lastBackupDate)) : 'N/A'; ?>
                    </p>
                    <p class="text-xs text-gray-400"><?php echo $lastBackupDate ? date('h:i A', strtotime($lastBackupDate)) : 'No backups found'; ?></p>
                </div>
                <!-- Total Backups -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Total Backups</h3>
                    <p class="text-2xl font-bold text-gray-800 mt-2"><?php echo $totalBackups; ?></p>
                    <p class="text-xs text-gray-400">Stored backup files</p>
                </div>
                <!-- Database Size -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Database Size</h3>
                    <p class="text-2xl font-bold text-gray-800 mt-2"><?php echo $dbSize; ?></p>
                    <p class="text-xs text-gray-400">Current database size</p>
                </div>
                <!-- Backup Status -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Backup Status</h3>
                    <?php $isCompleted = ($backupStatus === 'Completed'); ?>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="w-3 h-3 rounded-full <?php echo $isCompleted ? 'bg-green-500' : 'bg-red-500'; ?>"></span>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $backupStatus; ?></p>
                    </div>
                    <p class="text-xs text-gray-400">Last backup operation</p>
                </div>
            </div>

            <!-- Create Backup Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
                <h2 class="text-lg font-bold text-gray-800 mb-4 pb-4 border-b border-gray-100">Create Backup</h2>
                <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-800 p-4 mb-6 rounded-r-lg">
                    <p><strong>Note:</strong> Regular backups are essential for data protection. Backups are stored on the server and should be downloaded and stored securely.</p>
                </div>
                <form method="POST" action="../../process/superadmin/create_backup.php" class="backup-form">
                    <input type="hidden" name="action" value="backup">
                    <button type="submit" class="bg-brand-green hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors" onclick="return confirm('Create a new database backup? This may take a few moments.')">
                        <i class="fa-solid fa-plus-circle"></i> Create Database Backup
                    </button>
                </form>
            </div>
            
            <!-- Backups List Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
                <h2 class="text-lg font-bold text-gray-800 mb-4 pb-4 border-b border-gray-100">Existing Backups</h2>
                <?php if (!empty($backups)): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-brand-dark text-white text-xs uppercase tracking-wider">
                                    <th class="p-4 rounded-tl-lg font-medium">Backup Name</th>
                                    <th class="p-4 font-medium">Type</th>
                                    <th class="p-4 font-medium">Size</th>
                                    <th class="p-4 font-medium">Created By</th>
                                    <th class="p-4 font-medium">Role</th>
                                    <th class="p-4 font-medium">Date</th>
                                    <th class="p-4 font-medium">Status</th>
                                    <th class="p-4 rounded-tr-lg font-medium text-center">Actions</th>
                                </tr> 
                            </thead>
                            <tbody class="text-sm text-gray-700">
                                <?php foreach ($backups as $backup): ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="p-4 font-medium text-gray-800"><?php echo htmlspecialchars($backup['backup_name']); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($backup['backup_type']); ?></td>
                                        <td class="p-4 font-mono"><?php echo htmlspecialchars($backup['file_size']); ?></td> 
                                        <td class="p-4"><?php echo htmlspecialchars($backup['firstname'] . ' ' . $backup['lastname']); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($backup['created_role']); ?></td>
                                        <td class="p-4"><?php echo date('M d, Y - h:i A', strtotime($backup['backup_date'])); ?></td>
                                        <td class="p-4"><span class="bg-green-100 text-green-600 px-3 py-1 rounded-full text-xs font-medium">🟢 <?php echo htmlspecialchars($backup['status']); ?></span></td>
                                        <td class="p-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="?download=<?php echo urlencode($backup['file_name']); ?>" class="w-8 h-8 rounded border border-gray-200 text-blue-600 hover:bg-blue-50 flex items-center justify-center" title="Download"><i class="fa-solid fa-download"></i></a>
                                                <a href="../../process/superadmin/delete_backup.php?id=<?php echo $backup['id']; ?>" class="w-8 h-8 rounded border border-gray-200 text-red-500 hover:bg-red-50 flex items-center justify-center" title="Delete" onclick="return confirm('Are you sure you want to permanently delete this backup? This action cannot be undone.')"><i class="fa-solid fa-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-500 py-8">No backups available. Create one now to get started.</p>
                <?php endif; ?>
            </div>
            
            <!-- Restore Information Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

                <h2 class="text-xl font-bold mb-4">
                    Restore Database
                </h2>

                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded mb-5">
                    <strong>Warning!</strong><br>
                    Restoring a backup will overwrite the current database.
                    Create a backup first before restoring.
                </div>

                <form action="../../process/superadmin/upload_restore.php" method="POST" enctype="multipart/form-data">

                    <label class="block text-sm font-medium mb-2">
                        Select SQL Backup File
                    </label>

                    <input type="file" name="backup_file" accept=".sql" required class="block w-full border rounded-lg p-3">

                    <button type="submit" onclick="return confirm('Restore this database? Current data will be overwritten.')" class="mt-5 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg">

                        <i class="fa-solid fa-upload"></i>

                        Restore Database

                    </button>

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
