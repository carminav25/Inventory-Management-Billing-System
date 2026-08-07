<?php
session_start();
require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";
require_once "../../includes/admin_functions.php";

requireAdmin();

$productId = $_GET['id'] ?? 0;
$product = getProductById($conn, $productId);

if (!$product) {
    $_SESSION['error_message'] = "Product not found.";
    header("Location: products.php");
    exit;
}

require_once('../../includes/topbar.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - <?php echo htmlspecialchars($product['product_name']); ?></title>
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include '../../includes/sidebar.php'; ?>
<div class="main-content">
    <div style="background:white; padding:30px; border-radius:12px; border:1px solid #E8E8E8; max-width:700px; margin:auto;">
        <h2 style="font-size:20px; font-weight:bold; margin-bottom:20px;">Edit Product</h2>
        
        <form action="../../process/update_product.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">
            
            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px;">Product Name</label>
                <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
            </div>
            
            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px;">Selling Price</label>
                <input type="number" name="unit_price" value="<?php echo htmlspecialchars($product['unit_price']); ?>" step="0.01" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
            </div>
            
            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px;">Current Image</label>
                <img src="<?php echo !empty($product['image_path']) ? '../../' . $product['image_path'] : '../../assets/images/no-image.png'; ?>" alt="Current Image" style="width:80px; height:80px; border-radius:6px; object-fit:cover; margin-bottom:10px;">
                <label style="display:block; margin-bottom:5px;">Change Image (Optional)</label>
                <input type="file" name="product_image" accept="image/*" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
            </div>
            
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" style="background:#059669; color:white; padding:12px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:bold;">Update Product</button>
                <a href="products.php" style="background:#A0AEC0; color:white; padding:12px 20px; border-radius:6px; text-decoration:none; font-weight:bold;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>
</body>
</html>