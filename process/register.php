<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth_helpers.php";

// =========================
// Get Form Data
// =========================

$firstname = trim($_POST['firstname']);
$lastname  = trim($_POST['lastname']);
$username  = trim($_POST['username']);
$email     = trim($_POST['email']);
$mobile    = trim($_POST['mobile']);

$password  = $_POST['password'];
$confirm   = $_POST['confirm_password'];

$recoverySecret = $_POST['recovery_secret_password'] ?? '';
$recoverySecretConfirm = $_POST['recovery_secret_confirm'] ?? '';


// =========================
// Password Match
// =========================

if ($password != $confirm) {
    $_SESSION['signup_error'] = "Passwords do not match.";
    $_SESSION['old_input'] = $_POST;
    header("Location: ../signup.php");
    exit();
}


// =========================
// Automatic Role Assignment
// =========================

$result = $conn->query("SELECT COUNT(*) AS total FROM users");
$row = $result->fetch_assoc();

$totalUsers = $row['total'];

// Assign role based on user count
if($totalUsers == 0){
    $role = "Super Admin";
}
elseif($totalUsers == 1){
    $role = "Admin";
}
else{
    $role = "Viewer";
}


// =========================
// Only One Super Admin
// =========================

if($role == "Super Admin"){

    $check = $conn->prepare("SELECT id FROM users WHERE role='Super Admin'");
    $check->execute();

    if($check->get_result()->num_rows > 0){
        $_SESSION['signup_error'] = "A Super Admin account already exists. Cannot create another one.";
        $_SESSION['old_input'] = $_POST;
        header("Location: ../signup.php");
        exit();
    }

    if ($recoverySecret !== $recoverySecretConfirm) {
        $_SESSION['signup_error'] = "Recovery secret passwords do not match.";
        $_SESSION['old_input'] = $_POST;
        header("Location: ../signup.php");
        exit();
    }

    if (strlen($recoverySecret) < 8) {
        $_SESSION['signup_error'] = "Recovery secret password must be at least 8 characters long.";
        $_SESSION['old_input'] = $_POST;
        header("Location: ../signup.php");
        exit();
    }
}


// =========================
// Check Username
// =========================

$sql = $conn->prepare("SELECT id FROM users WHERE username=?");
$sql->bind_param("s",$username);
$sql->execute();

if($sql->get_result()->num_rows>0){
    $_SESSION['signup_error'] = "Username already exists. Please choose a different one.";
    $_SESSION['old_input'] = $_POST;
    header("Location: ../signup.php");
    exit();
}


// =========================
// Check Email
// =========================

$sql = $conn->prepare("SELECT id FROM users WHERE email=?");
$sql->bind_param("s",$email);
$sql->execute();

if($sql->get_result()->num_rows>0){
    $_SESSION['signup_error'] = "Email address is already registered. Please use a different one.";
    $_SESSION['old_input'] = $_POST;
    header("Location: ../signup.php");
    exit();
}


// =========================
// Encrypt Password & Prepare Recovery
// =========================

$hash = password_hash($password,PASSWORD_DEFAULT);
$recoveryHash = null;
$recoveryCode = null;
$failed_attempts = 0; // Initialize for all users
if ($role === "Super Admin" || $role === "Admin") {
    $recoveryHash = password_hash($recoverySecret, PASSWORD_DEFAULT);
    $recoveryCode = generateRecoveryCode();
}
// =========================
// Save User
// =========================

$sql = $conn->prepare(
"INSERT INTO users
(firstname,lastname,username,email,mobile,password,role,recovery_password,recovery_code,failed_attempts)
VALUES
(?,?,?,?,?,?,?,?,?,?)"
);

$sql->bind_param(
"sssssssssi",
$firstname,
$lastname,
$username,
$email,
$mobile,
$hash,
$role,
$recoveryHash,
$recoveryCode,
$failed_attempts
);


// =========================
// Execute
// =========================

if($sql->execute()){
    require_once "../includes/activity_log.php";

    $newUserId = $conn->insert_id;
    $fullname = $firstname . " " . $lastname;
    logActivity(
        $conn,
        $newUserId,
        $fullname,
        $username,
        $role,
        "Created a new account"
    );
    
    if ($role === "Super Admin") {
        $_SESSION['recovery_code'] = $recoveryCode;
        $_SESSION['super_admin_created'] = true;
        $_SESSION['super_admin_name'] = $fullname;
        header("Location: ../recovery_code_display.php");
    } else {
        header("Location: ../login.php");
    }
} else {
    $_SESSION['signup_error'] = "Registration failed due to a server error. Please try again.";
    $_SESSION['old_input'] = $_POST;
    header("Location: ../signup.php");
    exit();
}
?>