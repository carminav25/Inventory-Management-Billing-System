<?php

session_start();

require_once "../../config/database.php";

require_once "../../includes/superadmin_functions.php";

$logs=getRecentActivityLogs($conn,8);

if (empty($logs)) {
    echo '<div class="p-8 text-center text-gray-500">No activity available.</div>';
} else {
    foreach($logs as $log){

    ?>
    
    <div class="flex items-start gap-4 p-5 border-b hover:bg-gray-50">
    
        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
            <i class="fa-solid fa-clock-rotate-left text-green-700"></i>
        </div>
    
        <div class="flex-1">
            <h4 class="font-semibold">
                <?= htmlspecialchars($log['fullname'] ?? 'System') ?>
            </h4>
            <p class="text-sm text-gray-600 mt-1">
                <?= htmlspecialchars($log['action']) ?>
            </p>
            <p class="text-xs text-gray-400 mt-2">
                <?= date("M d, Y h:i A",strtotime($log['created_at'])) ?>
            </p>
        </div>
    </div>
    <?php
    }
}