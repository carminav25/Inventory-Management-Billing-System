<!-- SIDEBAR -->
<aside id="sidebar" class="w-[260px] bg-brand-dark text-white flex-col h-full shrink-0 fixed inset-y-0 left-0 z-50 transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 ease-in-out md:flex">
    <!-- Logo Area -->
    <div class="px-6 py-6 flex items-center gap-3">
        <img src="../../assets/images/logo.png" alt="Logo" class="w-10 h-10 rounded-full object-cover">
        <span class="font-bold text-base tracking-wide text-white">Inventory Management & Billing System</span>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto py-2 flex flex-col gap-5 no-scrollbar">
        <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
        <!-- Main Section -->
        <div>
            <p class="px-6 text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">MAIN</p>
            <a href="index.php" class="px-6 py-2.5 flex items-center gap-3 text-sm transition-colors <?php echo ($currentPage == 'index.php') ? 'bg-brand-active rounded-lg mx-3 px-3 text-white font-medium shadow-md' : 'text-gray-300 hover:text-white hover:bg-white/10 rounded-lg mx-3'; ?>">
                <i class="fa-solid fa-window-maximize w-5 text-center"></i> Dashboard
            </a>
        </div>

        <!-- User Management Section -->
        <div>
            <p class="px-6 text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">USER MANAGEMENT</p>
            <a href="users.php" class="px-6 py-2.5 flex items-center gap-3 text-sm transition-colors <?php echo in_array($currentPage, ['users.php', 'user_edit.php']) ? 'bg-brand-active rounded-lg mx-3 px-3 text-white font-medium shadow-md' : 'text-gray-300 hover:text-white hover:bg-white/10 rounded-lg mx-3'; ?>">
                <i class="fa-solid fa-users w-5 text-center"></i> Users
            </a>
            <a href="reset_password.php" class="px-6 py-2.5 flex items-center gap-3 text-sm transition-colors <?php echo ($currentPage == 'reset_password.php') ? 'bg-brand-active rounded-lg mx-3 px-3 text-white font-medium shadow-md' : 'text-gray-300 hover:text-white hover:bg-white/10 rounded-lg mx-3'; ?>">
                <i class="fa-solid fa-key w-5 text-center"></i> Reset Passwords
            </a>
        </div>

        <!-- System Section -->
        <div>
            <p class="px-6 text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">SYSTEM</p>
            <a href="activity_logs.php" class="px-6 py-2.5 flex items-center gap-3 text-sm transition-colors <?php echo ($currentPage == 'activity_logs.php') ? 'bg-brand-active rounded-lg mx-3 px-3 text-white font-medium shadow-md' : 'text-gray-300 hover:text-white hover:bg-white/10 rounded-lg mx-3'; ?>">
                <i class="fa-solid fa-file-lines w-5 text-center"></i> Activity Logs
            </a>
            <a href="security_center.php" class="px-6 py-2.5 flex items-center gap-3 text-sm transition-colors <?php echo ($currentPage == 'security_center.php') ? 'bg-brand-active rounded-lg mx-3 px-3 text-white font-medium shadow-md' : 'text-gray-300 hover:text-white hover:bg-white/10 rounded-lg mx-3'; ?>">
                <i class="fa-solid fa-shield-halved w-5 text-center"></i> Security Center
            </a>
            <a href="backup_restore.php" class="px-6 py-2.5 flex items-center gap-3 text-sm transition-colors <?php echo ($currentPage == 'backup_restore.php') ? 'bg-brand-active rounded-lg mx-3 px-3 text-white font-medium shadow-md' : 'text-gray-300 hover:text-white hover:bg-white/10 rounded-lg mx-3'; ?>">
                <i class="fa-solid fa-database w-5 text-center"></i> Backup & Restore
            </a>
        </div>
    </nav>
    
    <!-- Bottom Logout Area -->
    <div class="px-4 py-4 border-t border-white/10">
        <a href="../../process/logout.php" class="flex items-center gap-3 px-2 py-2 text-sm text-gray-300 hover:text-white transition-colors w-full">
            <i class="fa-solid fa-right-from-bracket w-5 text-center"></i> Logout
        </a>
    </div>
</aside>