<?php

// ==========================================================
// INVENTORY REPORT FUNCTIONS
// ISU Inventory Management & Billing System
// ==========================================================

if (!isset($conn)) {
    require_once "../../config/database.php";
}

/*
|--------------------------------------------------------------------------
| Total Products
|--------------------------------------------------------------------------
*/

function getTotalProducts($conn)
{
    $sql = mysqli_query($conn,"
        SELECT COUNT(*) total
        FROM products
    ");

    $row = mysqli_fetch_assoc($sql);

    return (int)$row['total'];
}


/*
|--------------------------------------------------------------------------
| Inventory Value
|--------------------------------------------------------------------------
*/

function getInventoryValue($conn)
{
    $sql = mysqli_query($conn,"
        SELECT
        COALESCE(SUM(current_stock * unit_cost),0) total
        FROM products
    ");

    $row = mysqli_fetch_assoc($sql);

    return (float)$row['total'];
}


/*
|--------------------------------------------------------------------------
| Inventory In
|--------------------------------------------------------------------------
*/

function getInventoryIn($conn,$from,$to)
{
    $sql=mysqli_query($conn,"
        SELECT
        COALESCE(SUM(di.quantity),0) total
        FROM delivery_items di

        INNER JOIN deliveries d
        ON di.delivery_id=d.id

        WHERE d.delivery_date
        BETWEEN '$from'
        AND '$to'
    ");

    $row=mysqli_fetch_assoc($sql);

    return (int)$row['total'];
}


/*
|--------------------------------------------------------------------------
| Inventory Out
|--------------------------------------------------------------------------
*/

function getInventoryOut($conn,$from,$to)
{
    $sql=mysqli_query($conn,"
        SELECT
        COALESCE(SUM(si.quantity),0) total
        FROM sale_items si

        INNER JOIN sales s
        ON si.sale_id=s.id

        WHERE s.sale_date
        BETWEEN '$from'
        AND '$to'
    ");

    $row=mysqli_fetch_assoc($sql);

    return (int)$row['total'];
}


/*
|--------------------------------------------------------------------------
| Supplier Returns
|--------------------------------------------------------------------------
*/

function getSupplierReturns($conn,$from,$to)
{
    $sql=mysqli_query($conn,"
        SELECT
        COALESCE(SUM(quantity),0) total
        FROM returns

        WHERE return_date
        BETWEEN '$from'
        AND '$to'
    ");

    $row=mysqli_fetch_assoc($sql);

    return (int)$row['total'];
}


/*
|--------------------------------------------------------------------------
| Beginning Stock
|--------------------------------------------------------------------------
*/

function getBeginningStock($ending,$in,$out,$return)
{

    $beginning =
        $ending
        -
        $in
        +
        $out
        +
        $return;

    if($beginning<0){

        $beginning=0;

    }

    return $beginning;

}


/*
|--------------------------------------------------------------------------
| Ending Stock
|--------------------------------------------------------------------------
*/

function getEndingStock($currentStock)
{
    return (int)$currentStock;
}


/*
|--------------------------------------------------------------------------
| Inventory Status
|--------------------------------------------------------------------------
*/

function getStockStatus($stock,$reorder)
{

    if($stock<=0){

        return [
            "text"=>"Out of Stock",
            "class"=>"bg-red-100 text-red-700"
        ];

    }

    if($stock<=$reorder){

        return [
            "text"=>"Low Stock",
            "class"=>"bg-yellow-100 text-yellow-700"
        ];

    }

    return [

        "text"=>"Available",

        "class"=>"bg-green-100 text-green-700"

    ];

}


/*
|--------------------------------------------------------------------------
| Total Inventory Cost
|--------------------------------------------------------------------------
*/

function getInventoryCost($qty,$cost)
{
    return $qty*$cost;
}


/*
|--------------------------------------------------------------------------
| Currency Format
|--------------------------------------------------------------------------
*/

function peso($amount)
{
    return "₱".number_format($amount,2);
}


/*
|--------------------------------------------------------------------------
| Number Format
|--------------------------------------------------------------------------
*/

function qty($qty)
{
    return number_format($qty);
}

?>