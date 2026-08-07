<?php
session_start();

require_once "../../config/database.php";
require_once "../../includes/superadmin_auth.php";

requireSuperAdmin();

header("Content-Type: application/json");

$data = [
    'userRegistration' => [
        'labels' => [],
        'data' => []
    ],
    'loginActivity' => [
        'labels' => [],
        'data' => []
    ],
    'failedLogins' => [
        'labels' => [],
        'data' => []
    ],
    'roleDistribution' => [
        'labels' => [],
        'data' => []
    ]
];

// User Registration (Last 7 Months)
$userChartLabels = [];
$userChartData = [];
$sql = "
SELECT
DATE_FORMAT(created_at,'%b') AS month,
COUNT(*) AS total
FROM users
WHERE created_at >= DATE_SUB(CURDATE(),INTERVAL 6 MONTH)
AND status <> 'Deleted'
GROUP BY DATE_FORMAT(created_at, '%Y-%m')
ORDER BY MONTH(created_at)
";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $data['userRegistration']['labels'][] = $row['month'];
    $data['userRegistration']['data'][] = $row['total'];
}

// Login Activity (Last 7 days)
$loginLabels = [];
$loginData = [];
$sql = "
SELECT
DATE(created_at) day,
COUNT(*) total
FROM activity_logs
WHERE action LIKE '%login%'
AND created_at>=DATE_SUB(CURDATE(),INTERVAL 7 DAY)
GROUP BY DATE(created_at)
ORDER BY DATE(created_at)
";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $data['loginActivity']['labels'][] = date("M d", strtotime($row['day']));
    $data['loginActivity']['data'][] = $row['total'];
}

// Role Distribution
$sql = "SELECT role, COUNT(*) AS total FROM users WHERE status <> 'Deleted' GROUP BY role ORDER BY role";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $data['roleDistribution']['labels'][] = $row['role'];
    $data['roleDistribution']['data'][] = $row['total'];
}

// Failed Login Attempts (Last 7 days)
$failedLabels = [];
$failedData = [];
$sql = "
SELECT
DATE(created_at) day,
COUNT(*) total
FROM activity_logs
WHERE action LIKE '%failed%'
AND created_at>=DATE_SUB(CURDATE(),INTERVAL 7 DAY)
GROUP BY DATE(created_at)
ORDER BY DATE(created_at)
";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $data['failedLogins']['labels'][] = date("M d", strtotime($row['day']));
    $data['failedLogins']['data'][] = $row['total'];
}

echo json_encode($data);
?>