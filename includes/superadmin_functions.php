<?php

/**
 * Super Admin Helper Functions
 * 
 * This file contains utility functions used throughout the Super Admin module
 */

/**
 * Get total count of users
 */
function getTotalUsers($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM users WHERE status != 'Deleted'");
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

/**
 * Get count of Super Admins
 */
function getTotalSuperAdmins($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='Super Admin' AND status <> 'Deleted'");
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

/**
 * Get count of Admins
 */
function getTotalAdmins($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='Admin' AND status <> 'Deleted'");
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

/**
 * Get count of Viewers
 */
function getTotalViewers($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='Viewer' AND status <> 'Deleted'");
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

/**
 * Get count of active users
 */
function getTotalActiveUsers($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM users WHERE status='Active' AND status <> 'Deleted'");
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

/**
 * Get count of disabled users
 */
function getTotalDisabledUsers($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM users WHERE status='Inactive' AND status <> 'Deleted'");
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

/**
 * Get count of locked accounts
 */
function getTotalLockedAccounts($conn) {
    $result = $conn->query("
        SELECT COUNT(*) AS total
        FROM users
        WHERE ((lock_until IS NOT NULL AND lock_until > NOW()) OR is_permanently_locked = 1) AND status <> 'Deleted'
    ");

    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

/**
 * Get all users with optional filters
 */
function getAllUsers($conn, $role = null, $status = null, $limit = null, $offset = 0) {
    $query = "SELECT id, firstname, lastname, username, email, mobile, role, status, created_at, lock_until, is_permanently_locked FROM users WHERE 1=1";

    if ($role && $role !== 'all') {
        $query .= " AND role = '" . $conn->real_escape_string($role) . "'";
    }

    // If a specific status is provided, filter by it.
    // Otherwise (if 'all' or null), filter out the 'Deleted' users.
    if ($status && $status !== 'all' && $status !== 'All Status' && $status !== 'All Statuses') {
        $query .= " AND status = '" . $conn->real_escape_string($status) . "'";
    } else {
        // By default, do not show deleted users unless explicitly requested by filtering for 'Deleted' status.
        $query .= " AND status != 'Deleted'";
    }

    $query .= " ORDER BY created_at DESC";
    
    if ($limit) {
        $query .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);
    }
    
    $result = $conn->query($query);
    $users = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    
    return $users;
}

/**
 * Get a specific user by ID
 */
function getUserById($conn, $userId) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get a specific user by username
 */
function getUserByUsername($conn, $username) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get a specific user by email
 */
function getUserByEmail($conn, $email) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Check if database connection is working
 */
function isDatabaseHealthy($conn) {
    // Check if the connection object is valid and if the connection is still alive.
    if ($conn && !$conn->connect_error) {
        // The ping() method is the proper way to check for a live connection.
        return $conn->ping();
    }
    return false;
}

/**
 * Get database size in MB
 */
function getDatabaseSize($conn, $dbName) {
    $query = "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size FROM information_schema.TABLES WHERE table_schema = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $dbName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return ($row['size'] ?? 0) . ' MB';
    }
    
    return '0 MB';
}

/**
 * Get recent activity logs
 */
function getRecentActivityLogs($conn, $limit = 10) {
    $query = "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    
    return $logs;
}

/**
 * Get activity logs with filters
 */
function getActivityLogs($conn, $filters = [], $limit = 50, $offset = 0) {
    $query = "SELECT * FROM activity_logs WHERE 1=1";
    
    if (isset($filters['search_term']) && $filters['search_term']) {
        $searchTerm = $conn->real_escape_string($filters['search_term']);
        $query .= " AND (username LIKE '%{$searchTerm}%' OR action LIKE '%{$searchTerm}%')";
    }
    if (isset($filters['user_id']) && $filters['user_id']) {
        $query .= " AND user_id = " . intval($filters['user_id']);
    }
    
    if (isset($filters['role']) && $filters['role']) {
        $query .= " AND role = '" . $conn->real_escape_string($filters['role']) . "'";
    }
    
    if (isset($filters['start_date']) && $filters['start_date']) {
        $query .= " AND created_at >= '" . $conn->real_escape_string($filters['start_date']) . "'";
    }
    
    if (isset($filters['end_date']) && $filters['end_date']) {
        $query .= " AND created_at <= '" . $conn->real_escape_string($filters['end_date']) . "'";
    }
    
    $query .= " ORDER BY created_at DESC LIMIT " . intval($limit) . " OFFSET " . intval($offset);
    
    $result = $conn->query($query);
    $logs = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
    }
    
    return $logs;
}

/**
 * Count activity logs with filters
 */
function countActivityLogs($conn, $filters = []) {
    $query = "SELECT COUNT(*) as total FROM activity_logs WHERE 1=1";
    
    if (isset($filters['search_term']) && $filters['search_term']) {
        $searchTerm = $conn->real_escape_string($filters['search_term']);
        $query .= " AND (username LIKE '%{$searchTerm}%' OR action LIKE '%{$searchTerm}%')";
    }
    if (isset($filters['user_id']) && $filters['user_id']) {
        $query .= " AND user_id = " . intval($filters['user_id']);
    }
    
    if (isset($filters['role']) && $filters['role']) {
        $query .= " AND role = '" . $conn->real_escape_string($filters['role']) . "'";
    }
    
    if (isset($filters['start_date']) && $filters['start_date']) {
        $query .= " AND created_at >= '" . $conn->real_escape_string($filters['start_date']) . "'";
    }
    
    if (isset($filters['end_date']) && $filters['end_date']) {
        $query .= " AND created_at <= '" . $conn->real_escape_string($filters['end_date']) . "'";
    }
    
    $result = $conn->query($query);
    
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    
    return 0;
}

/**
 * Get failed login attempts
 */
function getFailedLoginAttempts($conn, $limit = 10, $offset = 0) {
    $query = "SELECT * FROM activity_logs WHERE action LIKE '%failed login%' OR action LIKE '%wrong password%' ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    
    return $logs;
}

/**
 * Count failed login attempts
 */
function countFailedLoginAttempts($conn, $todayOnly = false) {
    $query = "
        SELECT COUNT(*) AS total
        FROM activity_logs
        WHERE action LIKE 'Failed login%'
    ";

    if ($todayOnly) {
        $query .= " AND DATE(created_at) = CURDATE()";
    }

    $result = $conn->query($query);
    return $result ? (int)($result->fetch_assoc()['total'] ?? 0) : 0;
}

/**
 * Get locked accounts
 */
function getLockedAccounts($conn) {
    $query = "
        SELECT
            id,
            firstname,
            lastname,
            username,
            email,
            role,
            lock_until,
            is_permanently_locked,
            failed_attempts
        FROM users
        WHERE (lock_until IS NOT NULL AND lock_until > NOW()) OR is_permanently_locked = 1
    ";

    $result = $conn->query($query);
    
    $accounts = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $accounts[] = $row;
        }
    }
    
    return $accounts;
}

/**
 * Unlock a user account
 */
function unlockUserAccount($conn, $userId) {
    $stmt = $conn->prepare("
        UPDATE users
        SET
            failed_attempts = 0,            
            is_permanently_locked = 0,
            lock_until = NULL
        WHERE id = ?
    ");

    $stmt->bind_param("i", $userId);
    return $stmt->execute();
}

/**
 * Disable a user account
 */
function disableUserAccount($conn, $userId) {
    $stmt = $conn->prepare("UPDATE users SET status = 'Inactive' WHERE id = ?");
    $stmt->bind_param("i", $userId);
    return $stmt->execute();
}

/**
 * Activate a user account
 */
function activateUserAccount($conn, $userId) {
    $stmt = $conn->prepare("UPDATE users SET status = 'Active' WHERE id = ?");
    $stmt->bind_param("i", $userId);
    return $stmt->execute();
}

/**
 * Reset a user's password
 */
function resetUserPassword($conn, $userId, $newPassword) {
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hash, $userId);
    return $stmt->execute();
}

/**
 * Format timestamp for display
 */
function formatTimestamp($timestamp) {
    if (empty($timestamp)) {
        return 'N/A';
    }
    
    return date('M d, Y - h:i A', strtotime($timestamp));
}

/**
 * Get time remaining until unlock
 */
function getTimeUntilUnlock($lockTime) {
    if (empty($lockTime)) {
        return '';
    }
    
    $now = new DateTime();
    $locked = new DateTime($lockTime);
    
    if ($now > $locked) {
        return 'Expired';
    }
    
    $interval = $now->diff($locked);    
    
    if ($interval->days > 0) {
        return $interval->days . ' day(s) ' . $interval->h . ' hour(s)';
    } elseif ($interval->h > 0) {
        return $interval->h . ' hour(s) ' . $interval->i . ' minute(s)';
    } else {
        return $interval->i . ' minute(s) ' . $interval->s . ' second(s)';
    }
}

/**
 * Get total count of backups
 */
function getTotalBackups($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM backup_history");
    if ($result) {
        return $result->fetch_assoc()['total'] ?? 0;
    }
    return 0;
}

/**
 * Get the date of the last backup
 */
function getLastBackupDate($conn) {
    $result = $conn->query("SELECT MAX(backup_date) as last_backup FROM backup_history");
    if ($result) {
        return $result->fetch_assoc()['last_backup'] ?? null;
    }
    return null;
}

/**
 * Get backup status (simple check for now)
 */
function getBackupStatus($conn) {
    $result = $conn->query("SELECT status FROM backup_history ORDER BY backup_date DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc()['status'] ?? 'Unknown';
    }
    return "No Backups";
}

/**
 * Get activity summary statistics
 */
function getActivitySummary($conn) {
    $summary = [
        'last_login' => null,
        'most_active_user' => ['fullname' => 'N/A', 'log_count' => 0],
        'most_common_action' => ['action' => 'N/A', 'action_count' => 0],
        'online_users' => 0,
        'today_activities' => 0,
    ];

    // Get last login for any user
    $lastLoginQuery = "SELECT created_at FROM activity_logs WHERE action = 'Logged in successfully' ORDER BY created_at DESC LIMIT 1";
    $lastLoginResult = $conn->query($lastLoginQuery);
    if ($lastLoginResult && $lastLoginResult->num_rows > 0) {
        $summary['last_login'] = $lastLoginResult->fetch_assoc()['created_at'];
    }

    // Get most active user
    $mostActiveUserQuery = "
        SELECT fullname, COUNT(*) as log_count
        FROM activity_logs
        WHERE user_id != 0
        GROUP BY user_id, fullname
        ORDER BY log_count DESC
        LIMIT 1
    ";
    $mostActiveUserResult = $conn->query($mostActiveUserQuery);
    if ($mostActiveUserResult && $mostActiveUserResult->num_rows > 0) {
        $summary['most_active_user'] = $mostActiveUserResult->fetch_assoc();
    }

    // Get most common action
    $mostCommonActionQuery = "
        SELECT action, COUNT(*) as action_count
        FROM activity_logs
        GROUP BY action
        ORDER BY action_count DESC
        LIMIT 1
    ";
    $mostCommonActionResult = $conn->query($mostCommonActionQuery);
    if ($mostCommonActionResult && $mostCommonActionResult->num_rows > 0) {
        $summary['most_common_action'] = $mostCommonActionResult->fetch_assoc();
    }

    // Get online users (logged in within the last 15 minutes)
    $onlineUsersQuery = "
        SELECT COUNT(DISTINCT user_id) as online_count
        FROM activity_logs
        WHERE created_at >= NOW() - INTERVAL 15 MINUTE
    ";
    $onlineUsersResult = $conn->query($onlineUsersQuery);
    if ($onlineUsersResult && $onlineUsersResult->num_rows > 0) {
        $summary['online_users'] = $onlineUsersResult->fetch_assoc()['online_count'];
    }

    // Get total activities for today
    $todayActivitiesQuery = "
        SELECT COUNT(*) as total
        FROM activity_logs
        WHERE DATE(created_at) = CURDATE()
    ";
    $todayActivitiesResult = $conn->query($todayActivitiesQuery);
    if ($todayActivitiesResult && $todayActivitiesResult->num_rows > 0) {
        $summary['today_activities'] = $todayActivitiesResult->fetch_assoc()['total'];
    }
    return $summary;
}
