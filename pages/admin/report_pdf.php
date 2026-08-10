<?php
session_start();
require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";
requireAdmin();
// =============================================
// DOMPDF
// =============================================
require_once "../../vendor/autoload.php";
use Dompdf\Dompdf;
use Dompdf\Options;
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');
$dompdf = new Dompdf($options);


// =============================================
// REPORT TYPE
// =============================================
$reportType = $_GET['type'] ?? 'monthly';

switch ($reportType) {

    case 'today':
        $fromDate = date('Y-m-d');
        $toDate   = date('Y-m-d');
        $pageTitle = "Today's Inventory Report";
        break;

    case 'weekly':
        $fromDate = date('Y-m-d', strtotime('-6 days'));
        $toDate   = date('Y-m-d');
        $pageTitle = "Last 7 Days Inventory Report";
        break;

    case 'yearly':
        $fromDate = date('Y-01-01');
        $toDate   = date('Y-12-31');
        $pageTitle = "Yearly Inventory Report";
        break;

    default:

        $fromDate = date('Y-m-01');
        $toDate   = date('Y-m-t');
        $pageTitle = "Monthly Inventory Report";
}


// =============================================
// CUSTOM DATE
// =============================================

if (!empty($_GET['from'])) {

    $fromDate = $_GET['from'];

}

if (!empty($_GET['to'])) {

    $toDate = $_GET['to'];

}


// =============================================
// FILTERS
// =============================================

$search = trim($_GET['search'] ?? '');

$category = trim($_GET['category'] ?? '');

$where = "WHERE 1=1";

if ($search != "") {

    $search = mysqli_real_escape_string($conn, $search);

    $where .= "
        AND
        (
            p.product_name LIKE '%$search%'
            OR
            p.product_code LIKE '%$search%'
        )
    ";

}

if ($category != "") {

    $category = mysqli_real_escape_string($conn, $category);

    $where .= "
        AND
        p.category='$category'
    ";

}


// =============================================
// PRODUCTS
// =============================================

$productQuery = mysqli_query($conn, "
SELECT
    p.*
FROM products p

$where

ORDER BY p.product_name ASC
");


// =============================================
// TOTALS
// =============================================

$grandBeginning = 0;
$grandIn        = 0;
$grandOut       = 0;
$grandReturn    = 0;
$grandEnding    = 0;
$grandValue     = 0;

$rowCount = mysqli_num_rows($productQuery);

$productFilterLabel = $category !== '' ? htmlspecialchars($category) : 'All Products';
$reportPeriod = date('F d, Y', strtotime($fromDate)) . ' - ' . date('F d, Y', strtotime($toDate));
$generatedAt = date('F d, Y h:i A');


// =============================================
// START HTML
// =============================================

$html = "";
$html .= '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>body{

font-family:DejaVu Sans,sans-serif;

font-size:11px;

color:#222;

margin:20px;

}

.header{

width:100%;

border-bottom:2px solid #0b7a38;

padding-bottom:10px;

margin-bottom:20px;

}

.logo{

width:70px;

height:70px;

float:left;

}

.school{

text-align:center;

}

.school h1{

margin:0;

font-size:22px;

}

.school h2{

margin:3px 0;

font-size:15px;

font-weight:normal;

}

.school h3{

margin:6px 0;

font-size:17px;

color:#0b7a38;

}

.clear{

clear:both;

}

.info{

margin-top:15px;

font-size:11px;

}

.info td{

padding:3px 0;

}table.report{

width:100%;

border-collapse:collapse;

margin-top:20px;

}table.report th{

background:#0b7a38;

color:#fff;

padding:8px;

border:1px solid #ddd;

font-size:10px;

}table.report td{

border:1px solid #ddd;

padding:6px;

font-size:10px;

vertical-align:middle;

}

.center{

text-align:center;

}

.right{

text-align:right;

}

.product{

width:45px;

height:45px;

object-fit:cover;

}tfoot td{

background:#f3f4f6;

font-weight:bold;

}

.signature{

margin-top:60px;

width:100%;

}

.signature td{

text-align:center;

padding-top:45px;

}

.line{

border-top:1px solid #222;

padding-top:5px;

display:block;

}

.footer{

margin-top:25px;

text-align:center;

font-size:10px;

color:#666;

}</style></head><body><div class="header"><table width="100%"><tr><td width="90"><img src="'.realpath(__DIR__ . '/../../assets/images/isu-logo.png').'"class="logo"></td><td class="school"><h1>

ISABELA STATE UNIVERSITY</h1><h2>

Merchandising Office</h2><h3>

'.$pageTitle.'</h3></td></tr></table></div><table class="info" width="100%"><tr><td><strong>Report Period:</strong>

'.date('F d, Y',strtotime($fromDate)).'

-

'.date('F d, Y',strtotime($toDate)).'</td><td align="right"><strong>Generated:</strong>

'.date('F d, Y h:i A').'</td></tr></table><table class="report"><thead><tr><th width="8%">Image</th><th width="12%">Code</th><th width="20%">Product</th><th width="12%">Category</th><th width="8%">Opening</th><th width="8%">In</th><th width="8%">Out</th><th width="8%">Return</th><th width="8%">Ending</th><th width="8%">Cost</th><th width="10%">Value</th></tr></thead><tbody>

';
while($product=mysqli_fetch_assoc($productQuery)){
$productId=(int)$product['id'];
$currentStock=(int)$product['current_stock'];
$unitCost=(float)$product['unit_cost'];
$image=$product['front_image'];
$reorder=(int)$product['reorder_level'];
/*==================================================
INVENTORY IN
==================================================*/
$inventoryIn=0;
$q=mysqli_query($conn,"
SELECT
COALESCE(SUM(di.quantity),0) total

FROM delivery_items di

INNER JOIN deliveries d

ON d.id=di.delivery_id

WHERE di.product_id='$productId'

AND d.delivery_date
BETWEEN '$fromDate'
AND '$toDate'
");
if($r=mysqli_fetch_assoc($q))
$inventoryIn=(int)$r['total'];
/*==================================================
INVENTORY OUT
==================================================*/
$inventoryOut=0;
$q=mysqli_query($conn,"
SELECT
COALESCE(SUM(si.quantity),0) total

FROM sale_items si

INNER JOIN sales s

ON s.id=si.sale_id

WHERE si.product_id='$productId'

AND s.sale_date
BETWEEN '$fromDate'
AND '$toDate'
");
if($r=mysqli_fetch_assoc($q))
$inventoryOut=(int)$r['total'];
/*==================================================
SUPPLIER RETURNS
==================================================*/
$returnQty=0;
$q=mysqli_query($conn,"
SELECT
COALESCE(SUM(quantity),0) total

FROM returns

WHERE product_id='$productId'

AND status='Returned'

AND return_date
BETWEEN '$fromDate'
AND '$toDate'
");
if($r=mysqli_fetch_assoc($q))
$returnQty=(int)$r['total'];
/*==================================================
BEGINNING STOCK
==================================================*/
$beginningStock=$currentStock-$inventoryIn+$inventoryOut+$returnQty;
if($beginningStock<0)
$beginningStock=0;
/*==================================================
ENDING STOCK
==================================================*/
$endingStock=$currentStock;
/*==================================================
INVENTORY VALUE
==================================================*/
$inventoryValue=$endingStock*$unitCost;
/*==================================================
GRAND TOTALS
==================================================*/
$grandBeginning+=$beginningStock;
$grandIn+=$inventoryIn;
$grandOut+=$inventoryOut;
$grandReturn+=$returnQty;
$grandEnding+=$endingStock;
$grandValue+=$inventoryValue;
/*==================================================
BUILD HTML
==================================================*/
$html.=' 

<tr>

<td>

'.htmlspecialchars($product['product_code'] . ' - ' . $product['product_name']).'

</td>

<td align="center">

'.htmlspecialchars($product['category']).'

</td>

<td class="center">

'.number_format($beginningStock).'

</td>

<td class="center">

'.number_format($inventoryIn).'

</td>

<td class="center">

'.number_format($inventoryOut).'

</td>

<td class="center">

'.number_format($returnQty).'

</td>

<td class="center">

'.number_format($endingStock).'

</td>

<td class="right">

₱'.number_format($unitCost,2).'

</td>

<td class="right">
₱'.number_format($inventoryValue,2).'

</td>

</tr>

';

}
$html .= '</tbody></table><div class="summary-grid"><div class="summary-card"><strong>Total Beginning</strong><span>'.number_format($grandBeginning).'</span></div><div class="summary-card"><strong>Total Received</strong><span>'.number_format($grandIn).'</span></div><div class="summary-card"><strong>Total Sold</strong><span>'.number_format($grandOut).'</span></div><div class="summary-card"><strong>Total Inventory Value</strong><span>₱'.number_format($grandValue,2).'</span></div></div><table class="signature"><tr><td><span class="line"></span><strong>Prepared By</strong><br>'.htmlspecialchars($_SESSION['fullname']).'<br>'.htmlspecialchars($_SESSION['role']).'</td><td><span class="line"></span><strong>Checked By</strong><br>Merchandising Office Coordinator</td><td><span class="line"></span><strong>Approved By</strong><br>Campus Administrator</td></tr></table><div class="footer-note">Generated by <strong>ISU Inventory Management & Billing System</strong><br>'.date('F d, Y h:i:s A').'</div></div></div></body></html>';

/*==========================================================
GENERATE PDF
==========================================================*/

$dompdf->loadHtml($html);

$dompdf->setPaper("A4","landscape");

$dompdf->render();

$dompdf->stream(

"Inventory_Report_".date("Ymd_His").".pdf",

array(

"Attachment"=>true

)

);

exit;