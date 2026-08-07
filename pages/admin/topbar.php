<header id="page-header" class="fixed top-0 left-0 md:left-[270px] right-0 h-[75px] bg-white px-6 flex items-center justify-between border-b shadow-sm z-40">    <div>
        <div class="flex items-center gap-3">
            <button id="sidebar-toggle" class="md:hidden text-gray-500 hover:text-gray-700 focus:outline-none">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
            <h1 class="text-[22px] font-bold text-[#111827]"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
        </div>

               <?php if (isset($breadcrumbs)): ?>
        <div class="text-[13px] flex items-center mt-0.5 ml-1 md:ml-0">
            <a href="index.php" class="text-[#0B7A4B] hover:underline">Dashboard</a> 
            <?php foreach ($breadcrumbs as $breadcrumb): ?>
                <i class="fa-solid fa-chevron-right text-[10px] text-gray-500 mx-2"></i> 
                <?php if (isset($breadcrumb['link'])): ?>
                    <a href="<?php echo $breadcrumb['link']; ?>" class="text-[#0B7A4B] hover:underline"><?php echo $breadcrumb['name']; ?></a>
                <?php else: ?>
                    <span class="text-gray-600"><?php echo $breadcrumb['name']; ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="flex items-center gap-4">
        <div class="hidden sm:flex items-center gap-2 text-sm font-semibold text-gray-600 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
            <i class="fa-regular fa-clock text-[#0B7A4B]"></i>
            <span id="clock"></span>
        </div>
        
        <div class="text-right ml-2 cursor-pointer">
            <p class="text-sm font-bold text-gray-700 tracking-wide"><?php echo htmlspecialchars(ucwords(strtolower($_SESSION['fullname']))); ?></p>
            <p class="text-[11px] text-gray-400 text-right"><?php echo htmlspecialchars($_SESSION['role']); ?></p>
        </div>
    </div>
    <script>
        function updateClock() {
            document.getElementById("clock").innerHTML = new Date().toLocaleTimeString();
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>
</header>
                
