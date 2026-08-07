<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";
require_once "../../includes/activity_log.php";

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $supplier_name = trim($_POST['supplier_name']);
    $contact_person = trim($_POST['contact_person'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? ''); // Keep for re-population
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $status = 'Active'; // Default status for new suppliers

    // Server-side validation for contact number
    $isValidContact = empty($contact_number) || preg_match('/^(09\d{9}|\+639\d{9})$/', $contact_number);

    if (!$isValidContact) {
        $_SESSION['error_message'] = "Invalid contact number format. Use 09xxxxxxxxx or +639xxxxxxxxxx.";
        header("Location: ../../pages/admin/add_supplier.php");
        exit;
    } elseif (empty($supplier_name)) {
        $_SESSION['error_message'] = "Supplier name is required.";
        header("Location: ../../pages/admin/add_supplier.php");
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO suppliers (supplier_name, contact_person, contact_number, email, address, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("ssssss", $supplier_name, $contact_person, $contact_number, $email, $address, $status);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Supplier '{$supplier_name}' added successfully.";
        logActivity($conn, $_SESSION['user_id'], $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Added new supplier '{$supplier_name}'");

    } else {
        $_SESSION['error_message'] = "Failed to add supplier. Error: " . $stmt->error;
    }

    header("Location: ../../pages/admin/suppliers.php");
    exit;
}