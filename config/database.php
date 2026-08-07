<?php

$dbHost = "localhost";
$dbUser = "root";
$dbPassword = "user123";
$dbName = "inventory_system";

$conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Set the application's default timezone to Philippine Time.
date_default_timezone_set('Asia/Manila');

// Set the connection's timezone to match PHP's timezone to ensure date consistency.
$conn->query("SET time_zone = '" . date('P') . "'");

?>