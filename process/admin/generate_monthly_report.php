<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";
require_once "../../includes/activity_log.php";

requireAdmin();

$redirectURL = '../../pages/admin/reports.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Invalid request method.";
    header("Location: {$redirectURL}");
    exit();
}

$report_month = $_POST['report_month'] ?? date('Y-m');
$report_year = date('Y', strtotime($report_month));
$report_mon = date('m', strtotime($report_month));

mysqli_begin_transaction($conn);

try {
    // 1. Get all products to generate a report for each one
    $products_res = mysqli_query($conn, "SELECT id, product_name FROM products");
    if (!$products_res) {
        throw new Exception("Failed to retrieve products.");
    }

    // 2. Delete old report data for the selected month to prevent duplicates
    $delete_stmt = mysqli_prepare($conn, "DELETE FROM inventory_monthly_reports WHERE report_month = ?");
    mysqli_stmt_bind_param($delete_stmt, "s", $report_month);
    mysqli_stmt_execute($delete_stmt);

    // 3. Loop through each product and calculate its monthly movement
    while ($product = mysqli_fetch_assoc($products_res)) {
        $product_id = $product['id'];

        // A. Get Beginning Stock (last running_balance from the PREVIOUS month)
        $begin_stmt = mysqli_prepare($conn, "
            SELECT running_balance FROM inventory_stock_cards 
            WHERE product_id = ? AND transaction_date < ? 
            ORDER BY transaction_date DESC, id DESC LIMIT 1
        ");
        $first_day_of_month = $report_month . '-01';
        mysqli_stmt_bind_param($begin_stmt, "is", $product_id, $first_day_of_month);
        mysqli_stmt_execute($begin_stmt);
        $begin_res = mysqli_stmt_get_result($begin_stmt);
        $beginning_stock = mysqli_fetch_assoc($begin_res)['running_balance'] ?? 0;

        // B. Get Total IN and OUT for the current month
        $movement_stmt = mysqli_prepare($conn, "
            SELECT 
                COALESCE(SUM(stock_in), 0) as total_in,
                COALESCE(SUM(stock_out), 0) as total_out
            FROM inventory_stock_cards
            WHERE product_id = ? AND YEAR(transaction_date) = ? AND MONTH(transaction_date) = ?
        ");
        mysqli_stmt_bind_param($movement_stmt, "iii", $product_id, $report_year, $report_mon);
        mysqli_stmt_execute($movement_stmt);
        $movement_res = mysqli_stmt_get_result($movement_stmt);
        $movement = mysqli_fetch_assoc($movement_res);
        $total_in = $movement['total_in'];
        $total_out = $movement['total_out'];

        // C. Calculate Ending Stock
        $ending_stock = $beginning_stock + $total_in - $total_out;

        // D. Insert the calculated report into the monthly reports table
        $insert_stmt = mysqli_prepare($conn, "
            INSERT INTO inventory_monthly_reports (report_month, product_id, beginning_stock, stock_in, stock_out, ending_stock)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($insert_stmt, "siiiii", $report_month, $product_id, $beginning_stock, $total_in, $total_out, $ending_stock);
        mysqli_stmt_execute($insert_stmt);
    }

    mysqli_commit($conn);
    logActivity($conn, $_SESSION['user_id'], $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Generated Monthly Inventory Report for " . date('F Y', strtotime($report_month)));
    $_SESSION['success_message'] = "Monthly report for " . date('F Y', strtotime($report_month)) . " generated successfully!";

} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error_message'] = "Report generation failed: " . $e->getMessage();
}

header("Location: {$redirectURL}?tab=monthly");
exit();
?>