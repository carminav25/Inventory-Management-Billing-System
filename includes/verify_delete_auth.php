<?php
session_start();
require_once "../config/database.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $itemId = (int)($_POST['item_id'] ?? $_POST['product_id'] ?? 0); // Accept generic 'item_id' or fallback to 'product_id'
    $reason = trim($_POST['reason'] ?? '');

    if (empty($username) || empty($password) || $itemId <= 0 || empty($reason)) {
        echo json_encode(['success' => false, 'message' => 'All fields including reason are required. ID:'.$itemId]);
        exit();
    }

    // Check user credentials and role (Super Admin / Admin)
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verify password (supports hashed or plain depending on your system configuration)
        if (password_verify($password, $row['password'])) {
            if ($row['role'] === 'Super Admin') {
                
                // Optional: Log the deletion authorization reason to an activity log table if you have one
                // Store authorization details in the session to be picked up by the delete script.
                $_SESSION['delete_authorized'] = true;
                $_SESSION['delete_auth_time'] = time();
                $_SESSION['delete_auth_reason'] = $reason;

                echo json_encode(['success' => true]);
                exit();
            } else {
                echo json_encode(['success' => false, 'message' => 'Account does not have Super Admin privileges.']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid password.']);
            exit();
        }
    }

    echo json_encode(['success' => false, 'message' => 'Super Admin account not found.']);
    exit();
}