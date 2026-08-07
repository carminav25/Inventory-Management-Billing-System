<?php

/**
 * Gets the real IP address from the user, even if behind a proxy.
 */
function get_client_ip() {
    // Check for shared internet/ISP IP
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    // Check for IPs passing through proxies
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // The 'X-Forwarded-For' header can contain a comma-separated list of IPs.
        // The first one in the list is the original client IP.
        $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ip_list[0]);
    }
    // Check for the remote address
    if (!empty($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    }
    return 'UNKNOWN';
}

function logActivity($conn, $user_id, $fullname, $username, $role, $action)
{
    $ip = get_client_ip();

    // Ensure the activity_logs table exists and the required columns are present.
    $tables = $conn->query("SHOW TABLES LIKE 'activity_logs'");
    if (!$tables || $tables->num_rows === 0) {
        return false;
    }

    // Truncate the action to prevent "Data too long for column" errors.
    $action = substr($action, 0, 255);

    // Ensure role is never NULL
    if (empty($role)) {
        $role = 'Unknown';
    }

    $stmt = $conn->prepare(
        "INSERT INTO activity_logs (user_id, fullname, username, role, action, ip_address) VALUES (?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "isssss",
        $user_id,
        $fullname,
        $username,
        $role,
        $action,
        $ip
    );

    $stmt->execute();
    $stmt->close();

    return true;
}
