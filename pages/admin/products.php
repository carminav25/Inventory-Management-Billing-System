<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../../config/database.php");
require_once("../../includes/admin_auth.php");
require_once("../../includes/admin_functions.php");

requireAdmin();

$pageTitle = "Products";

$breadcrumbs = [
    ["name" => "Products"]
];

// Search, Filter, and Pagination parameters
$search = trim($_GET['search'] ?? '');
$categoryFilter = $_GET['category'] ?? '';
$supplierFilter = $_GET['supplier'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limit = 5; // Products per page
$offset = ($page - 1) * $limit;

// Build dynamic WHERE clause based on filters
$whereClauses = ["1=1"];
if (!empty($search)) {
    $s = $conn->real_escape_string($search);
    $whereClauses[] = "(product_name LIKE '%$s%' OR product_code LIKE '%$s%')";
}
if (!empty($categoryFilter)) {
    $cat = $conn->real_escape_string($categoryFilter);
    $whereClauses[] = "category = '$cat'";
}
if (!empty($supplierFilter)) {
    $sup = $conn->real_escape_string($supplierFilter);
    $whereClauses[] = "supplier = '$sup'";
}
if (!empty($statusFilter)) {
    if ($statusFilter == 'Available') {
        $whereClauses[] = "current_stock > reorder_level";
    } elseif ($statusFilter == 'Low Stock') {
        $whereClauses[] = "current_stock <= reorder_level AND current_stock > 0";
    } elseif ($statusFilter == 'Out of Stock') {
        $whereClauses[] = "current_stock = 0";
    }
}
$whereSql = implode(" AND ", $whereClauses);

// Fetch counts and metrics
$statsQuery = "
    SELECT
        COUNT(*) AS totalProducts,
        SUM(CASE WHEN current_stock > reorder_level THEN 1 ELSE 0 END) AS activeProducts,
        SUM(CASE WHEN current_stock <= reorder_level AND current_stock > 0 THEN 1 ELSE 0 END) AS lowStockItems,
        SUM(CASE WHEN current_stock = 0 THEN 1 ELSE 0 END) AS outOfStockItems
    FROM products
";
$statsResult = $conn->query($statsQuery);
$stats = $statsResult ? $statsResult->fetch_assoc() : [];

$totalProducts = $stats['totalProducts'] ?? 0;
$activeProducts = $stats['activeProducts'] ?? 0;
$lowStockItems = $stats['lowStockItems'] ?? 0;
$outOfStockItems = $stats['outOfStockItems'] ?? 0;

// Filtered count for pagination
$countRes = $conn->query("SELECT COUNT(*) FROM products WHERE $whereSql");
$filteredTotal = $countRes ? $countRes->fetch_row()[0] : 0;
$totalPages = ceil($filteredTotal / $limit);
if ($totalPages < 1) $totalPages = 1;

// Fetch filtered products list
$productsSql = "SELECT * FROM products WHERE $whereSql ORDER BY id DESC LIMIT $limit OFFSET $offset";
$productsRes = $conn->query($productsSql);
$products = [];
if (!$productsRes) {
    die($conn->error);
}
if ($productsRes) {
    while ($row = $productsRes->fetch_assoc()) {
        $products[] = $row;
    }
}

// Fetch categories & suppliers for dropdown options
$categoriesList = $conn->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");

// Fetch Active Suppliers for Filter
$suppliersList = $conn->query("
    SELECT supplier_name
    FROM suppliers
    WHERE status = 'Active'
    ORDER BY supplier_name ASC");

// Fetch active suppliers for Add/Edit modals
$activeSuppliers = [];
$activeSuppliersResult = $conn->query("SELECT id, supplier_name FROM suppliers WHERE status='Active' ORDER BY supplier_name");
if ($activeSuppliersResult) {
    while($row = $activeSuppliersResult->fetch_assoc()) $activeSuppliers[] = $row;
}

// Generate Next Sequential Product Code (e.g. PROD-000001)
$nextIdQuery = $conn->query("SELECT MAX(id) AS max_id FROM products");
$nextIdRow = $nextIdQuery ? $nextIdQuery->fetch_assoc() : [];
$nextId = ((int)($nextIdRow['max_id'] ?? 0)) + 1;
$autoProductCode = 'PROD-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Products'); ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS for Modals compatibility -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        a { text-decoration: none !important; }
        .modal-backdrop { z-index: 1040 !important; }
        .modal { z-index: 1050 !important; }
    </style>
</head>

<body class="bg-[#f5f7fb] font-sans">

<?php include "sidebar.php"; ?>

<main class="ml-0 md:ml-[270px] min-h-screen bg-[#f5f7fb] px-4 py-2 md:px-5 md:py-3 transition-all duration-300">

    <div class="space-y-6 max-w-7xl mx-auto">

    <!-- NOTIFICATION ALERTS -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm flex items-center justify-between">
            <span> <?php echo htmlspecialchars($_SESSION['error_message']); ?></span>
            <button onclick="this.parentElement.remove();" class="text-red-500 hover:text-red-700"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 text-sm flex items-center justify-between">
            <span> <?php echo htmlspecialchars($_SESSION['success_message']); ?></span>
            <button onclick="this.parentElement.remove();" class="text-emerald-500 hover:text-emerald-700"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <!-- PAGE HEADER -->
     <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
     
       <div>
     <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Product Management</h1>
         <p class="text-sm text-slate-500 mt-0.5">Manage inventory products, categories, suppliers, stock levels, QR codes, and pricing.</p>
    </div>
    </div>

    <!-- STAT CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 min-h-[116px]">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Products</p>
                <h2 class="text-3xl font-extrabold text-slate-800 mt-1"><?php echo $totalProducts; ?></h2>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 min-h-[116px]">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Products</p>
                <h2 class="text-3xl font-extrabold text-slate-800 mt-1"><?php echo $activeProducts; ?></h2>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 min-h-[116px]">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Low Stock</p>
                <h2 class="text-3xl font-extrabold text-amber-600 mt-1"><?php echo $lowStockItems; ?></h2>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 min-h-[116px]">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Out of Stock</p>
                <h2 class="text-3xl font-extrabold text-red-600 mt-1"><?php echo $outOfStockItems; ?></h2>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 mb-8">

        <div class="flex justify-between items-center mb-6">

            <div>

                <h2 class="text-2xl font-bold text-slate-800">
                    Product List
                </h2>

                <p class="text-slate-500 text-sm">
                    Search, filter and manage inventory products.
                </p>

            </div>

            <button onclick="openAddProductModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2.5 rounded-xl font-semibold">
                Add Product

            </button>

        </div>

        <form method="GET" action="" class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 flex-1">
                <!-- Search -->
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 text-sm">
                    </span>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search product name/code..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>
                
                <!-- Category Dropdown -->
                <select name="category" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="">All Categories</option>
                    <?php if ($categoriesList): while($cat = $categoriesList->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo ($categoryFilter == $cat['category']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['category']); ?>
                        </option>
                    <?php endwhile; endif; ?>
                </select>
                
                <!-- Supplier Dropdown -->
                <select name="supplier" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="">All Suppliers</option>
                    <?php if ($suppliersList): while ($sup = $suppliersList->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($sup['supplier_name']); ?>" <?= ($supplierFilter == $sup['supplier_name']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($sup['supplier_name']); ?>
                        </option>
                    <?php endwhile; endif; ?>
                </select>
                
                <!-- Status Dropdown -->
                <select name="status" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="">All Statuses</option>
                    <option value="Available" <?php echo ($statusFilter == 'Available') ? 'selected' : ''; ?>>Available</option>
                    <option value="Low Stock" <?php echo ($statusFilter == 'Low Stock') ? 'selected' : ''; ?>>Low Stock</option>
                    <option value="Out of Stock" <?php echo ($statusFilter == 'Out of Stock') ? 'selected' : ''; ?>>Out of Stock</option>
                </select>
            </div>

            <!-- Filter Action Buttons -->
            <div class="flex items-center gap-2 self-end lg:self-center">
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition shadow-sm flex items-center gap-2"> Filter
                </button>
                <?php if(!empty($search) || !empty($categoryFilter) || !empty($supplierFilter) || !empty($statusFilter)): ?>
                    <a href="products.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2.5 rounded-xl text-sm font-medium transition" title="Reset Filters">
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- PRODUCTS TABLE CONTAINER -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-12">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left border-collapse whitespace-nowrap">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-4 py-3.5 font-semibold">Image</th>
                        <th class="px-4 py-3.5 font-semibold">Product Code</th>
                        <th class="px-4 py-3.5 font-semibold">Product Name</th>
                        <th class="px-4 py-3.5 font-semibold">Category</th>
                        <th class="px-4 py-3.5 font-semibold">Size</th>
                        <th class="px-4 py-3.5 font-semibold">Supplier</th>
                        <th class="px-4 py-3.5 font-semibold">Unit Price</th>
                        <th class="px-4 py-3.5 font-semibold text-center">Stock Qty</th>
                        <th class="px-4 py-3.5 font-semibold text-center">Reorder</th>
                        <th class="px-4 py-3.5 font-semibold text-center">Status</th>
                        <th class="px-4 py-3.5 font-semibold text-center">QR Code</th>
                        <th class="px-4 py-3.5 font-semibold text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php if (count($products) > 0): foreach ($products as $product): ?>
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="px-4 py-3">
                            <?php 
                                $imagePath = !empty($product['front_image']) ? '../../' . $product['front_image'] : '../../assets/images/no-image.png';
                            ?>
                            <img src="<?php echo htmlspecialchars($imagePath); ?>" class="w-10 h-10 rounded-xl object-contain bg-white border border-slate-200 p-0.5">
                        </td>
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-600"><?php echo htmlspecialchars($product['product_code'] ?? 'N/A'); ?></td>
                        <td class="px-4 py-3 font-semibold text-slate-800"><?php echo htmlspecialchars($product['product_name']); ?></td>
                        <td class="px-4 py-3 text-slate-500"><?php echo htmlspecialchars($product['category'] ?? 'N/A'); ?></td>
                        <td class="px-4 py-3 text-slate-500 font-medium"><?php echo htmlspecialchars($product['product_size'] ?? 'N/A'); ?></td>
                        <td class="px-4 py-3 text-slate-500"><?php echo htmlspecialchars($product['supplier'] ?? 'N/A'); ?></td>
                        <td class="px-4 py-3 font-semibold text-slate-700">₱<?php echo number_format($product['unit_price'] ?? 0, 2); ?></td>
                        <td class="px-4 py-3 text-center font-bold text-slate-800"><?php echo htmlspecialchars($product['current_stock']); ?></td>
                        <td class="px-4 py-3 text-center text-slate-500"><?php echo htmlspecialchars($product['reorder_level']); ?></td>
                        <td class="px-4 py-3 text-center">
                            <?php 
                                $status = 'Available';
                                $statusClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                                if ($product['current_stock'] == 0) {
                                    $status = 'Out of Stock';
                                    $statusClass = 'bg-red-50 text-red-700 border border-red-200';
                                } elseif ($product['current_stock'] <= $product['reorder_level']) {
                                    $status = 'Low Stock';
                                    $statusClass = 'bg-amber-50 text-amber-700 border border-amber-200';
                                }
                            ?>
                            <span class="<?php echo $statusClass; ?> px-2.5 py-1 rounded-full text-xs font-semibold inline-block"><?php echo $status; ?></span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if (!empty($product['qr_code'])): ?>
                                <img src="../../<?php echo htmlspecialchars($product['qr_code']); ?>" class="w-10 h-10 mx-auto border rounded p-0.5 bg-white">
                            <?php else: ?>
                                <span class="text-slate-400 text-xs">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick="openViewModal(<?php echo htmlspecialchars(json_encode($product)); ?>)" class="p-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg transition text-sm" title="View details">
                                </button>
                                <button type="button" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($product)); ?>)" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 rounded-lg transition text-sm" title="Edit product">
                                </button>
                                <?php if (!empty($product['qr_code'])): ?>
                                    <a href="../../<?php echo htmlspecialchars($product['qr_code']); ?>" download class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition text-sm" title="Download QR">
                                    </a>
                                <?php endif; ?>
                                <button type="button" onclick="confirmDeleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['product_name'], ENT_QUOTES); ?>')" class="p-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition text-sm" title="Delete product">
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="12" class="px-6 py-12 text-center text-slate-400">
                            <p class="text-sm font-medium">No products found matching your filters.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Controls -->
    <?php if ($totalPages > 1): ?>
    <div class="flex justify-end items-center gap-2 mb-8">
        <?php 
            $queryParams = $_GET;
            if ($page > 1): 
                $queryParams['p'] = $page - 1;
        ?>
            <a href="?<?php echo http_build_query($queryParams); ?>" class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50 shadow-sm transition"></a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): 
            $queryParams['p'] = $i;
        ?>
            <a href="?<?php echo http_build_query($queryParams); ?>" class="px-3.5 py-2 border rounded-xl text-sm font-medium shadow-sm transition <?php echo ($i == $page) ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): 
            $queryParams['p'] = $page + 1;
        ?>
            <a href="?<?php echo http_build_query($queryParams); ?>" class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50 shadow-sm transition"></a>
        <?php endif; ?>
    </div>
    <?php endif; ?>


<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-2xl border-0 shadow-xl overflow-hidden">
            <div class="modal-header bg-slate-50 border-b border-slate-100 px-6 py-4">
                <h5 class="modal-title font-bold text-slate-800" id="addProductModalLabel">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-6">
                <form action="../../process/admin/add_product.php" method="POST" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Product Name <span class="text-red-500">*</span></label>
                            <input type="text" name="product_name" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Product Code <span class="text-red-500">*</span></label>
                            <input type="text" name="product_code" class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm font-mono text-slate-600" value="<?php echo $autoProductCode; ?>" readonly required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Category <span class="text-red-500">*</span></label>
                            <input type="text" name="category" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Product Size <span class="text-red-500">*</span></label>
                            <select name="product_size" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                <option value="">Select Size</option>
                                <option value="XS">Extra Small (XS)</option>
                                <option value="Small">Small (S)</option>
                                <option value="Medium">Medium (M)</option>
                                <option value="Large">Large (L)</option>
                                <option value="XL">Extra Large (XL)</option>
                                <option value="XXL">2XL</option>
                                <option value="XXXL">3XL</option>
                                <option value="Free Size">Free Size</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Supplier <span class="text-red-500">*</span></label>
                            <select name="supplier" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                <option value="">Select Supplier</option>
                                <?php foreach($activeSuppliers as $supplier): ?>
                                    <option value="<?= htmlspecialchars($supplier['supplier_name']); ?>">
                                        <?= htmlspecialchars($supplier['supplier_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Unit Cost <span class="text-red-500">*</span></label>
                            <input type="number" name="unit_cost" step="0.01" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Selling Price <span class="text-red-500">*</span></label>
                            <input type="number" name="unit_price" step="0.01" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Initial Stock <span class="text-red-500">*</span></label>
                            <input type="number" name="current_stock" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Reorder Level <span class="text-red-500">*</span></label>
                            <input type="number" name="reorder_level" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Description (Optional)</label>
                            <textarea name="description" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Product Image <span class="text-red-500">*</span></label>
                            <input type="file" name="front_image" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm" required>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium shadow-sm transition flex items-center gap-2"> Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View Product Modal -->
<div class="modal fade" id="viewProductModal" tabindex="-1" aria-labelledby="viewProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-2xl border-0 shadow-xl overflow-hidden">
            <div class="modal-header bg-slate-50 border-b border-slate-100 px-6 py-4">
                <h5 class="modal-title font-bold text-slate-800" id="viewProductModalLabel">Product Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                    <div class="text-center space-y-4">
                        <img id="viewProductImage" src="" class="w-full h-44 rounded-xl object-contain bg-white border border-slate-200 shadow-sm p-1" alt="Product Image">
                        
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 inline-block">
                            <img id="viewProductQR" src="" class="w-28 h-28 mx-auto" alt="QR Code">
                        </div>
                    </div>
                    <div class="md:col-span-2 space-y-3">
                        <div>
                            <h3 id="viewProductName" class="font-bold text-xl text-slate-900"></h3>
                            <p id="viewProductCode" class="text-xs font-mono text-slate-400 mt-0.5"></p>
                        </div>
                        <div class="grid grid-cols-2 gap-y-2.5 text-sm pt-2 border-t border-slate-100">
                            <div><span class="text-slate-400">Category:</span> <span id="viewProductCategory" class="font-medium text-slate-700"></span></div>
                            <div><span class="text-slate-400">Size:</span> <span id="viewProductSize" class="font-medium text-slate-700"></span></div>
                            <div><span class="text-slate-400">Supplier:</span> <span id="viewProductSupplier" class="font-medium text-slate-700"></span></div>
                            <div><span class="text-slate-400">Unit Cost:</span> <span id="viewProductCost" class="font-medium text-slate-700"></span></div>
                            <div><span class="text-slate-400">Selling Price:</span> <span id="viewProductPrice" class="font-medium text-slate-700"></span></div>
                            <div><span class="text-slate-400">Current Stock:</span> <span id="viewProductStock" class="font-bold text-slate-800"></span></div>
                            <div><span class="text-slate-400">Reorder Level:</span> <span id="viewProductReorder" class="font-medium text-slate-700"></span></div>
                            <div class="col-span-2 pt-2"><span class="text-slate-400 block mb-1">Status:</span> <span id="viewProductStatus"></span></div>
                            <div class="col-span-2 pt-2"><span class="text-slate-400 block mb-1">Description:</span> <p id="viewProductDescription" class="text-slate-600 bg-slate-50 p-3 rounded-xl text-xs"></p></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-2xl border-0 shadow-xl overflow-hidden">
            <div class="modal-header bg-slate-50 border-b border-slate-100 px-6 py-4">
                <h5 class="modal-title font-bold text-slate-800" id="editProductModalLabel">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-6">
                <form action="../../process/admin/update_product.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="editProductId">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Product Name <span class="text-red-500">*</span></label>
                            <input type="text" name="product_name" id="editProductName" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Product Code <span class="text-red-500">*</span></label>
                            <input type="text" name="product_code" id="editProductCode" class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm font-mono text-slate-600" readonly required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Category <span class="text-red-500">*</span></label>
                            <input type="text" name="category" id="editProductCategory" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Product Size <span class="text-red-500">*</span></label>
                            <select name="product_size" id="editProductSize" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                <option value="">Select Size</option>
                                <option value="XS">Extra Small (XS)</option>
                                <option value="Small">Small (S)</option>
                                <option value="Medium">Medium (M)</option>
                                <option value="Large">Large (L)</option>
                                <option value="XL">Extra Large (XL)</option>
                                <option value="XXL">2XL</option>
                                <option value="XXXL">3XL</option>
                                <option value="Free Size">Free Size</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Supplier <span class="text-red-500">*</span></label>
                            <select name="supplier" id="editProductSupplier" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                <option value="">Select Supplier</option>
                                <?php foreach($activeSuppliers as $supplier): ?>
                                    <option value="<?= htmlspecialchars($supplier['supplier_name']); ?>">
                                        <?= htmlspecialchars($supplier['supplier_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Unit Cost <span class="text-red-500">*</span></label>
                            <input type="number" name="unit_cost" id="editProductCost" step="0.01" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Selling Price <span class="text-red-500">*</span></label>
                            <input type="number" name="unit_price" id="editProductPrice" step="0.01" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-xs font-semibold text-slate-500 uppercase">Current Stock <span class="text-red-500">*</span></label>
                                <button type="button" onclick="requestUnlockStock()" class="text-[11px] text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1">
                                    <i class="fa-solid fa-lock text-[10px]" id="stockLockIcon"></i> <span id="stockLockText">Unlock via Super Admin</span>
                                <button type="button" onclick="requestUnlockStock()" class="text-[11px] text-purple-600 hover:text-purple-700 font-semibold flex items-center gap-1"> <span id="stockLockText">Unlock via Super Admin</span>
                                </button>
                            </div>
                            <input type="number" name="current_stock" id="editProductStock" class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed focus:outline-none" readonly required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Reorder Level <span class="text-red-500">*</span></label>
                            <input type="number" name="reorder_level" id="editProductReorder" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Description (Optional)</label>
                            <textarea name="description" id="editProductDescription" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                        </div>
                        <div class="md:col-span-2 flex items-center gap-4">
                            <img id="editProductImage" src="" class="w-16 h-16 rounded-xl object-contain bg-white border border-slate-200 p-0.5">
                            <div class="flex-1">
                                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Update Product Image (Optional)</label>
                                <input type="file" name="front_image" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium shadow-sm transition flex items-center gap-2"> Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
    SUPER ADMIN AUTHENTICATION MODAL
========================================== -->
<div class="modal fade" id="stockAuthModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-xl overflow-hidden">
            <div class="modal-header bg-red-600 text-white px-6 py-4">
                <h5 class="modal-title font-bold flex items-center gap-2"> Super Admin Authorization
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="stockAuthForm">
                <div class="modal-body p-6 space-y-4">
                    <input type="hidden" id="auth_product_id">
                    <input type="hidden" id="auth_new_stock">

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Super Admin Username</label>
                        <input type="text" id="auth_username" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Password</label>
                        <input type="password" id="auth_password" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Reason for Adjustment</label>
                        <textarea id="auth_reason" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required placeholder="e.g., Physical inventory count correction"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-slate-50 border-t border-slate-100 px-6 py-3 flex justify-end gap-2">
                    <button type="button" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-medium transition shadow-sm flex items-center gap-2"> Verify & Unlock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- SUPER ADMIN DELETE AUTHENTICATION MODAL -->
<!-- ========================================== -->
<div class="modal fade" id="deleteAuthModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-xl overflow-hidden">
            <div class="modal-header bg-red-600 text-white px-6 py-4">
                <h5 class="modal-title font-bold flex items-center gap-2"> Super Admin Delete Authorization
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteAuthForm">
                <div class="modal-body p-6 space-y-4">
                    <input type="hidden" id="delete_product_id">
                    
                    <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-xl text-xs">
                        <span class="font-bold">Warning:</span> You are about to delete <span id="delete_product_name_label" class="font-semibold underline"></span>. This action is irreversible and requires Super Admin privileges.
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Super Admin Username</label>
                        <input type="text" id="delete_auth_username" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Password</label>
                        <input type="password" id="delete_auth_password" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Reason for Deletion</label>
                        <textarea id="delete_auth_reason" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500" required placeholder="e.g., Discontinued item / Data cleanup"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-slate-50 border-t border-slate-100 px-6 py-3 flex justify-end gap-2">
                    <button type="button" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-medium transition shadow-sm flex items-center gap-2"> Verify & Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openViewModal(product) {
        document.getElementById('viewProductName').innerText = product.product_name;
        document.getElementById('viewProductCode').innerText = product.product_code;
        document.getElementById('viewProductCategory').innerText = product.category || 'N/A';
        document.getElementById('viewProductSize').innerText = product.product_size || 'N/A';
        document.getElementById('viewProductSupplier').innerText = product.supplier || 'N/A';
        document.getElementById('viewProductCost').innerText = '₱' + parseFloat(product.unit_cost || 0).toFixed(2);
        document.getElementById('viewProductPrice').innerText = '₱' + parseFloat(product.unit_price || 0).toFixed(2);
        document.getElementById('viewProductStock').innerText = product.current_stock + ' pcs';
        document.getElementById('viewProductReorder').innerText = product.reorder_level + ' pcs';
        document.getElementById('viewProductDescription').innerText = product.description || 'N/A';

        let status = 'Available';
        let statusClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
        if (product.current_stock == 0) {
            status = 'Out of Stock';
            statusClass = 'bg-red-50 text-red-700 border border-red-200';
        } else if (product.current_stock <= product.reorder_level) {
            status = 'Low Stock';
            statusClass = 'bg-amber-50 text-amber-700 border border-amber-200';
        }
        document.getElementById('viewProductStatus').innerHTML = `<span class="${statusClass} px-3 py-1 rounded-full text-xs font-semibold inline-block">${status}</span>`;

        const imagePath = product.front_image ? `../../${product.front_image}` : '../../assets/images/no-image.png';
        document.getElementById('viewProductImage').src = imagePath;
        
        const qrPath = product.qr_code ? `../../${product.qr_code}` : '';
        const qrImg = document.getElementById('viewProductQR');
        qrImg.src = qrPath;
        qrImg.style.display = qrPath ? 'block' : 'none';

        var myModal = new bootstrap.Modal(document.getElementById('viewProductModal'));
        myModal.show();
    }

    function openEditModal(product) {
        document.getElementById('editProductId').value = product.id;
        document.getElementById('editProductName').value = product.product_name;
        document.getElementById('editProductCode').value = product.product_code;
        document.getElementById('editProductCategory').value = product.category || '';
        document.getElementById('editProductSize').value = product.product_size || '';
        document.getElementById('editProductSupplier').value = product.supplier || '';
        document.getElementById('editProductCost').value = product.unit_cost || 0;
        document.getElementById('editProductPrice').value = product.unit_price || 0;
        document.getElementById('editProductStock').value = product.current_stock;
        document.getElementById('editProductReorder').value = product.reorder_level;
        document.getElementById('editProductDescription').value = product.description || '';
        
        // Reset stock locking UI state on open
        let stockInput = document.getElementById('editProductStock');
        stockInput.readOnly = true;
        stockInput.className = "w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed focus:outline-none";
        document.getElementById('stockLockIcon').className = "fa-solid fa-lock text-[10px]";
        document.getElementById('stockLockText').innerText = "Unlock via Super Admin";

        const imagePath = product.front_image ? `../../${product.front_image}` : '../../assets/images/no-image.png';
        document.getElementById('editProductImage').src = imagePath;

        var myModal = new bootstrap.Modal(document.getElementById('editProductModal'));
        myModal.show();
    }

    function requestUnlockStock() {
        document.getElementById('auth_product_id').value = document.getElementById('editProductId').value;
        document.getElementById('auth_new_stock').value = document.getElementById('editProductStock').value;
        
        // Reset modal fields
        document.getElementById('auth_username').value = '';
        document.getElementById('auth_password').value = '';
        document.getElementById('auth_reason').value = '';

        var authModal = new bootstrap.Modal(document.getElementById('stockAuthModal'));
        authModal.show();
    }

    // Handle AJAX Super Admin Verification
    document.getElementById("stockAuthForm").addEventListener("submit", function(e) {
        e.preventDefault();

        fetch("../../includes/verify_stock_auth.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({
                product_id: document.getElementById("auth_product_id").value,
                new_stock: document.getElementById("auth_new_stock").value,
                username: document.getElementById("auth_username").value,
                password: document.getElementById("auth_password").value,
                reason: document.getElementById("auth_reason").value
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let stockInput = document.getElementById('editProductStock');
                stockInput.readOnly = false;
                stockInput.className = "w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white";
                
                document.getElementById('stockLockIcon').className = "fa-solid fa-lock-open text-[10px]";
                document.getElementById('stockLockText').innerText = "Stock Unlocked";

                bootstrap.Modal.getInstance(document.getElementById("stockAuthModal")).hide();
                alert("Super Admin authorization verified successfully. Current stock field is now unlocked for editing.");
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert("An error occurred during verification.");
        });
    });

    function openAddProductModal() {
        const modalElement = document.getElementById('addProductModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
    }

    function confirmDeleteProduct(productId, productName) {
        document.getElementById('delete_product_id').value = productId;
        document.getElementById('delete_product_name_label').innerText = productName;
        
        // Reset form inputs
        document.getElementById('delete_auth_username').value = '';
        document.getElementById('delete_auth_password').value = '';
        document.getElementById('delete_auth_reason').value = '';

        var deleteModal = new bootstrap.Modal(document.getElementById('deleteAuthModal'));
        deleteModal.show();
    }

    // Handle AJAX Super Admin Delete Verification
    document.getElementById("deleteAuthForm").addEventListener("submit", function(e) {
        e.preventDefault();

        let productId = document.getElementById("delete_product_id").value;
        let username = document.getElementById("delete_auth_username").value;
        let password = document.getElementById("delete_auth_password").value;
        let reason = document.getElementById("delete_auth_reason").value;

        fetch("../../includes/verify_delete_auth.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({
                product_id: productId,
                username: username,
                password: password,
                reason: reason
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById("deleteAuthModal")).hide();
                // Redirect to actual deletion processing file with ID
                window.location.href = `../../process/admin/delete_product.php?id=${productId}`;
            } else {
                alert("Authorization Failed: " + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert("An error occurred during verification.");
        });
    });
</script>



</body>
</html>