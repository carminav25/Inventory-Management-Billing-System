<?php
session_start();
header('Content-Type: application/json');

// Include database configuration
require_once("../config/database.php");

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$newStock  = isset($_POST['new_stock']) ? (int)$_POST['new_stock'] : 0;
$username  = trim($_POST['username'] ?? '');
$password  = $_POST['password'] ?? '';
$reason    = trim($_POST['reason'] ?? '');

if (empty($username) || empty($password) || empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// Query the 'users' table using username or email
$stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ? LIMIT 1");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Verify hashed password
    if (password_verify($password, $user['password'])) {
        
        // Check if the user has Super Admin privileges (matching your database value like 'Super Admin')
        // This check must be strict and match the check in update_product.php
        if ($user['role'] !== 'Super Admin') {
            echo json_encode(['success' => false, 'message' => 'Access denied. Account does not have Super Admin privileges.']);
            exit;
        }

        // Set session flags for the update script to consume
        $_SESSION['stock_authorized'] = "1";
        $_SESSION['stock_auth_time'] = time(); // For expiration check
        $_SESSION['authorized_product'] = $productId;
        $_SESSION['approved_by'] = $user['id'];
        $_SESSION['approved_name'] = $user['username'];
        $_SESSION['stock_reason'] = $reason;

        echo json_encode(['success' => true, 'message' => 'Authorized successfully.']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid password.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Username or email not found in the users table.']);
    exit;
}
?>