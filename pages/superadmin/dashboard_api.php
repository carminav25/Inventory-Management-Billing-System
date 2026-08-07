<?php

session_start();

require_once "../../config/database.php";
require_once "../../includes/superadmin_auth.php";
require_once "../../includes/superadmin_functions.php";

requireSuperAdmin();

$data = [

    'totalUsers'     => getTotalUsers($conn),
    'admins'         => getTotalAdmins($conn),
    'viewers'        => getTotalViewers($conn),
    'lockedUsers'    => getTotalLockedAccounts($conn),
    'failedToday'    => countFailedLoginAttempts($conn, true),
    'failedAll'      => countFailedLoginAttempts($conn, false),
    'totalBackups'   => getTotalBackups($conn),
    'databaseSize'   => getDatabaseSize($conn, $dbName),
    'databaseStatus' => isDatabaseHealthy($conn),
    'serverTime'     => date("h:i:s A"),
    'onlineUsers'    => (getActivitySummary($conn)['online_users'] ?? 0)

];

header("Content-Type: application/json");

echo json_encode($data);