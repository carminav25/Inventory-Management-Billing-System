<?php
$pageTitle = "New Delivery";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";

requireAdmin();

// 1. Fetch Suppliers
$suppliers = [];
$suppResult = mysqli_query($conn, "SELECT id, supplier_name FROM suppliers ORDER BY supplier_name ASC");
if ($suppResult) {
    while ($row = mysqli_fetch_assoc($suppResult)) {
        $suppliers[] = $row;
    }
}

// 2. Fetch Products
$productData = [];
$prodDataResult = mysqli_query($conn, "
    SELECT 
        id, 
        product_code, 
        product_name, 
        category,
        product_size, 
        supplier,
        current_stock, 
        reorder_level,
        unit_cost, 
        unit_price,
        status,
        front_image, 
        qr_code 
    FROM products 
    ORDER BY product_name ASC
");
if ($prodDataResult) {
    while ($row = mysqli_fetch_assoc($prodDataResult)) {
        $productData[] = $row;
    }
}

$defaultDelNo = "DEL-" . date('Ymd') . "-" . rand(1000, 9999);

// NOTE: This logic should be moved to 'process/admin/save_delivery.php'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type']) && $_POST['action_type'] === 'save_delivery') {
    $delivery_no = trim($_POST['delivery_no']);
    $supplier_id = (int)$_POST['supplier_id'];
    $delivery_date = $_POST['delivery_date'];
    $dr_number = trim($_POST['dr_number'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');
    $received_by = $_SESSION['user_id'];
    $items = $_POST['items'] ?? [];

    if (empty($items)) {
        $_SESSION['error_message'] = "Cannot save an empty delivery. Please add at least one product.";
        header("Location: delivery_form.php");
        exit();
    }

    mysqli_begin_transaction($conn);

    try {
        // 1. Insert into deliveries table
        $stmt = $conn->prepare("INSERT INTO deliveries (delivery_no, supplier_id, delivery_date, dr_number, remarks, received_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisssi", $delivery_no, $supplier_id, $delivery_date, $dr_number, $remarks, $received_by);
        $stmt->execute();
        $delivery_id = $stmt->insert_id;

        // 2. Loop through items
        foreach ($items as $item) {
            $product_id = (int)$item['product_id'];
            $quantity = (int)$item['quantity'];
            $cost_price = (float)$item['cost_price'];

            // Insert into delivery_items
            $itemStmt = $conn->prepare("INSERT INTO delivery_items (delivery_id, product_id, quantity, cost_price) VALUES (?, ?, ?, ?)");
            $itemStmt->bind_param("iiid", $delivery_id, $product_id, $quantity, $cost_price);
            $itemStmt->execute();

            // Update product stock
            $stockStmt = $conn->prepare("UPDATE products SET current_stock = current_stock + ?, unit_cost = ? WHERE id = ?");
            $stockStmt->bind_param("idi", $quantity, $cost_price, $product_id);
            $stockStmt->execute();

            // NEW: Log to stock_movements
            $stockResult = $conn->query("SELECT current_stock FROM products WHERE id = $product_id");
            $new_stock = $stockResult->fetch_assoc()['current_stock'];
            $moveRemarks = "Delivery " . $delivery_no;
            $moveStmt = $conn->prepare("INSERT INTO stock_movements (product_id, transaction_type, reference_table, reference_id, qty_in, qty_out, balance_after, remarks, created_by) VALUES (?, 'Delivery', 'deliveries', ?, ?, 0, ?, ?, ?)");
            $moveStmt->bind_param("iiiisi", $product_id, $delivery_id, $quantity, $new_stock, $moveRemarks, $received_by);
            $moveStmt->execute();
        }

        mysqli_commit($conn);
        $_SESSION['success_message'] = "Delivery #{$delivery_no} has been successfully recorded.";
        header("Location: inventory_indeliveries.php");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error_message'] = "Transaction failed: " . $e->getMessage();
        header("Location: delivery_form.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        a { text-decoration: none !important; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade { animation: fadeIn 0.25s ease-out forwards; }
        #reader {
            width: 100%;
            height: 320px;
            border-radius: 12px;
            overflow: hidden;
            background: #000;
        }
    </style>
</head>

<body class="bg-slate-50 font-sans text-slate-800 antialiased">

<?php include "sidebar.php"; ?> 

<main class="ml-0 md:ml-[270px] min-h-screen bg-[#f5f7fb] px-4 py-2 md:px-5 md:py-3 transition-all duration-300">
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- HEADER -->
        <div class="flex justify-between items-center bg-white px-6 py-4 rounded-xl shadow-sm border border-slate-200/80">
            <div>
                <h1 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-truck-ramp-box text-emerald-600"></i> New Delivery
                </h1>
                <p class="text-sm text-slate-500 mt-0.5">Receive incoming inventory via wireless QR scanner, webcam, or manual selection.</p>
            </div>
            <a href="inventory_indeliveries.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3.5 py-2 rounded-lg font-semibold text-xs transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>

        <form action="delivery_form.php" method="POST" id="deliveryForm" class="space-y-6">
            <input type="hidden" name="action_type" value="save_delivery">

            <!-- 1. RECEIVE PRODUCTS -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-6">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[10px]">1</span> 
                    Add Products to Delivery
                </h2>

                <!-- Scan Method Options Grid (3 Cards) -->
                <div class="grid md:grid-cols-3 gap-4 mb-6">
                    <!-- Wireless Scanner Button -->
                    <button type="button" id="scannerModeBtn" class="p-4 rounded-xl border-2 text-left transition flex items-center justify-between border-emerald-500 bg-emerald-50/40 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-qrcode"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">Wireless Scanner</h3>
                                <p class="text-[10px] text-slate-500">Recommended</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-600 text-white">Active</span>
                    </button>

                    <!-- Laptop Camera Button -->
                    <button type="button" id="cameraModeBtn" class="p-4 rounded-xl border-2 text-left transition flex items-center justify-between border-slate-200 bg-white hover:border-blue-300">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-camera"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">Laptop Camera</h3>
                                <p class="text-[10px] text-slate-500">Scan using webcam</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">Switch</span>
                    </button>

                    <!-- Manual Selection Button -->
                    <button type="button" id="manualModeBtn" class="p-4 rounded-xl border-2 text-left transition flex items-center justify-between border-slate-200 bg-white hover:border-amber-300">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">Manual Search</h3>
                                <p class="text-[10px] text-slate-500">Search or choose from list</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">Switch</span>
                    </button>
                </div>

                <!-- Wireless Scanner Input Box -->
                <div id="wirelessArea" class="transition-all animate-fade mb-6">
                    <input
                        id="wirelessScanner"
                        type="text"
                        autofocus
                        autocomplete="off"
                        class="w-full rounded-xl border-2 border-emerald-500 bg-slate-50/50 px-4 py-3.5 text-sm text-center font-mono text-slate-500 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition shadow-sm"
                        placeholder="Click here & scan barcode/QR code...">
                </div>

                <!-- Laptop Camera Area -->
                <div id="cameraArea" class="hidden transition-all animate-fade mb-6">
                    <div class="max-w-xl mx-auto bg-slate-50 border border-slate-200 rounded-xl p-5 text-center">
                        <h3 class="font-bold text-sm text-slate-800 mb-3 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-video text-blue-600"></i> Live Camera QR View
                        </h3>
                        <div id="reader" class="mb-2 shadow-inner"></div>
                        <p class="text-xs text-slate-500 flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-circle-info text-blue-500"></i> Hold the QR code about 15–25 cm from the camera lens.
                        </p>
                    </div>
                </div>

                <!-- Manual Selection Area -->
                <div id="manualArea" class="hidden transition-all animate-fade mb-6">
                    <div class="max-w-2xl mx-auto bg-slate-50 border border-slate-200 rounded-xl p-5 space-y-4">
                        <div>
                            <h3 class="font-bold text-sm text-slate-800 mb-1 flex items-center gap-2">
                                <i class="fa-solid fa-magnifying-glass text-amber-600"></i> Search Product
                            </h3>
                            <p class="text-xs text-slate-500 mb-2">Type by Product Name, Code or QR</p>
                            <div class="relative">
                                <input
                                    id="manualSearchInput"
                                    type="text"
                                    autocomplete="off"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500"
                                    placeholder="Type product name, code or QR to search...">
                                
                                <!-- Search Results Dropdown List -->
                                <div id="manualResultsContainer" class="absolute left-0 right-0 z-20 mt-1 bg-white border border-slate-200 rounded-xl overflow-hidden hidden max-h-60 overflow-y-auto shadow-lg">
                                    <div id="manualResultsBody" class="divide-y divide-slate-100"></div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center text-xs text-slate-400 font-medium">— OR SELECT FROM LIST —</div>

                        <div>
                            <select id="manualProductSelect" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500" onchange="handleManualSelect(this)">
                                <option value="">-- Choose Product from List --</option>
                                <?php foreach ($productData as $p): ?>
                                    <option value="<?= $p['id']; ?>">
                                        <?= htmlspecialchars($p['product_name']); ?> (<?= htmlspecialchars($p['product_size'] ?? 'Standard'); ?>) - Cost: ₱<?= number_format($p['unit_cost'], 2); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Product Found Preview Card -->
                <div id="productPreview" class="hidden mt-6 animate-fade bg-slate-50 border border-emerald-200 rounded-xl p-5">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2.5 mb-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-check"></i> Product Verified
                        </span>
                        <span id="previewCode" class="text-xs font-mono font-semibold bg-white px-2 py-0.5 rounded border text-slate-700"></span>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 items-center sm:items-start">
                        <img id="previewImage" src="" class="w-24 h-24 rounded-lg object-contain border bg-white p-1 shadow-sm flex-shrink-0">
                        <div class="flex-1 min-w-0 w-full space-y-1 text-xs">
                            <h3 id="previewName" class="text-base font-bold text-slate-900 mb-1"></h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 text-slate-600">
                                <p>Supplier: <span id="previewSupplier" class="font-medium text-slate-800"></span></p>
                                <p>Category: <span id="previewCategory" class="font-medium text-slate-800"></span></p>
                                <p>Size: <span id="previewSize" class="font-medium text-slate-800"></span></p>
                                <p>Status: <span id="previewStatus" class="font-medium text-slate-800"></span></p>
                                <p>Current Stock: <span id="previewStock" class="font-medium text-slate-800"></span></p>
                                <p>Reorder Level: <span id="previewReorder" class="font-medium text-slate-800"></span></p>
                                <p>Unit Cost: <span id="previewCost" class="font-bold text-emerald-600"></span></p>
                                <p>Unit Price: <span id="previewPrice" class="font-bold text-slate-800"></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unregistered Product Notice -->
                <div id="productNotRegistered" class="hidden mt-6 animate-fade bg-rose-50 border border-rose-200 rounded-xl p-5 text-center">
                    <div class="w-10 h-10 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-2 text-sm">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h3 class="font-bold text-rose-900 text-base mb-1">Product Not Registered</h3>
                    <p class="text-xs text-rose-600 mb-4">The scanned QR code is not linked to any existing product record.</p>
                    <button type="button" onclick="dismissUnregisteredNotice()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg font-bold text-xs transition">
                        Scan Again
                    </button>
                </div>
            </div>

            <!-- 2. DELIVERY INFO -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-6">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[10px]">2</span> 
                    Delivery Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Delivery Date *</label>
                        <input type="datetime-local" name="delivery_date" id="deliveryDateInput" value="<?= date('Y-m-d\TH:i'); ?>" required onchange="checkFormValidity()" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    </div>
                    <div>
                        <!-- FIXED: Made Delivery Number read-only since it's auto-generated -->
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Delivery Number *</label>
                        <input type="text" name="delivery_no" id="deliveryNoInput" value="<?= $defaultDelNo; ?>" readonly class="w-full bg-slate-100 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-500 cursor-not-allowed focus:outline-none">
                    </div>
                    <div>
                        <!-- Supplier Field: Auto-filled based on added product -->
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Supplier (Auto-filled) *</label>
                        <input type="hidden" name="supplier_id" id="supplierInput" required>
                        <input type="text" id="supplierDisplay" readonly placeholder="Auto-filled when product is added..." class="w-full bg-slate-100 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-500 cursor-not-allowed focus:outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Reference Number (DR/SI) *</label>
                        <input type="text" name="dr_number" id="drNumberInput" placeholder="Enter reference number" required oninput="checkFormValidity()" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Remarks (Optional)</label>
                        <input type="text" name="remarks" id="remarksInput" placeholder="Enter notes about this delivery..." oninput="checkFormValidity()" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    </div>
                </div>
            </div>

            <!-- 3. PRODUCTS RECEIVED (CART) -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[10px]">3</span> 
                        Delivery Cart
                    </h2>
                    <span id="cartCountBadge" class="text-xs font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">0 Items</span>
                </div>
                
                <div id="cartCardsContainer" class="p-6 space-y-4">
                    <div id="emptyCartNotice" class="text-center py-8 text-slate-400 bg-slate-50/30 rounded-xl border border-dashed border-slate-200 text-xs">
                        No products added yet. Scan or search an item above.
                    </div>
                </div>
                
                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex gap-6 text-xs">
                        <div>
                            <span class="block text-[10px] uppercase font-bold text-slate-400">Products</span>
                            <span id="totItems" class="font-bold text-base text-slate-800">0</span>
                        </div>
                        <div>
                            <span class="block text-[10px] uppercase font-bold text-slate-400">Total Qty</span>
                            <span id="totQty" class="font-bold text-base text-slate-800">0</span>
                        </div>
                        <div>
                            <span class="block text-[10px] uppercase font-bold text-slate-400">Grand Total</span>
                            <span id="grandTot" class="font-bold text-base text-emerald-600">₱0.00</span>
                        </div>
                    </div>
                    <!-- FIXED: Button state manipulation via logic below -->
                    <button type="submit" id="submitBtn" disabled class="bg-slate-200 text-slate-400 px-6 py-2.5 rounded-lg font-bold text-xs shadow-none transition cursor-not-allowed">
                        Receive Delivery
                    </button>
                </div>
            </div>
        </form>
    </div>
</main>

<!-- TOAST CONTAINER -->
<div id="toast" class="fixed bottom-6 right-6 z-50 flex flex-col gap-2 pointer-events-none"></div>

<script>
const products = <?= json_encode($productData); ?>;
let cart = [];
let html5QrCode = null;
let scanLocked = false; 
const suppliers = <?= json_encode($suppliers); ?>;

window.onload = () => {
    const scannerInput = document.getElementById("wirelessScanner");
    scannerInput.focus();

    document.getElementById("wirelessArea").addEventListener("click", () => {
        scannerInput.focus();
    });

    let scanTimeout = null;
    scannerInput.addEventListener("input", function() {
        clearTimeout(scanTimeout);
        const code = this.value.trim();
        if (!code) return;

        scanTimeout = setTimeout(() => {
            processScannedCode(code);
        }, 150);
    });

    scannerInput.addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            clearTimeout(scanTimeout);
            const code = this.value.trim();
            if (code) {
                processScannedCode(code);
            }
        }
    });

    const searchInput = document.getElementById("manualSearchInput");
    searchInput.addEventListener("input", function() {
        performManualSearch();
    });

    const scannerBtn = document.getElementById("scannerModeBtn");
    const cameraBtn = document.getElementById("cameraModeBtn");
    const manualBtn = document.getElementById("manualModeBtn");
    
    scannerBtn.onclick = () => {
        setActiveTab('scanner');
        closeScanner();
        scannerInput.focus();
    };

    cameraBtn.onclick = () => {
        setActiveTab('camera');
        openScanner();
    };

    manualBtn.onclick = () => {
        setActiveTab('manual');
        closeScanner();
        searchInput.focus();
    };
    
    checkFormValidity();
};

function setActiveTab(mode) {
    const buttons = { scanner: 'scannerModeBtn', camera: 'cameraModeBtn', manual: 'manualModeBtn' };
    const areas = { scanner: 'wirelessArea', camera: 'cameraArea', manual: 'manualArea' };
    const activeClasses = { 
        scanner: 'border-emerald-500 bg-emerald-50/40', 
        camera: 'border-blue-500 bg-blue-50/40', 
        manual: 'border-amber-500 bg-amber-50/40' 
    };
    const activeSpan = { 
        scanner: 'bg-emerald-600 text-white', 
        camera: 'bg-blue-600 text-white', 
        manual: 'bg-amber-600 text-white' 
    };

    for (const key in buttons) {
        const btn = document.getElementById(buttons[key]);
        const area = document.getElementById(areas[key]);
        btn.className = "p-4 rounded-xl border-2 text-left transition flex items-center justify-between border-slate-200 bg-white hover:border-slate-300";
        btn.querySelector("span").className = "text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500";
        btn.querySelector("span").innerText = "Switch";
        area.classList.add("hidden");
    }

    const activeBtn = document.getElementById(buttons[mode]);
    activeBtn.className = `p-4 rounded-xl border-2 text-left transition flex items-center justify-between shadow-sm ${activeClasses[mode]}`;
    
    const spanElem = activeBtn.querySelector("span");
    spanElem.className = `text-[10px] font-bold px-2.5 py-1 rounded-full ${activeSpan[mode]}`;
    spanElem.innerText = "Active";
    
    document.getElementById(areas[mode]).classList.remove("hidden");
}

function performManualSearch() {
    const query = document.getElementById("manualSearchInput").value.trim().toLowerCase();
    const resultsContainer = document.getElementById("manualResultsContainer");
    const container = document.getElementById("manualResultsBody");
    container.innerHTML = "";

    if (!query) {
        resultsContainer.classList.add("hidden");
        return;
    }

    const filtered = products.filter(p => 
        (p.product_name && p.product_name.toLowerCase().includes(query)) ||
        (p.product_code && p.product_code.toLowerCase().includes(query)) ||
        (p.qr_code && p.qr_code.toLowerCase().includes(query))
    );

    if (filtered.length === 0) {
        container.innerHTML = `<div class="text-center py-4 text-slate-400 text-xs">No matching products found.</div>`;
        resultsContainer.classList.remove("hidden");
        return;
    }

    filtered.forEach(p => {
        const imgSrc = p.front_image ? "../../" + p.front_image : "../../assets/images/default.png";
        const div = document.createElement("div");
        div.className = "p-3 hover:bg-slate-50 transition flex items-center justify-between gap-3 cursor-pointer";
        div.onclick = () => {
            addProductToDelivery(p);
            document.getElementById("manualSearchInput").value = "";
            resultsContainer.classList.add("hidden");
        };
        div.innerHTML = `
            <div class="flex items-center gap-3">
                <img src="${imgSrc}" class="w-10 h-10 rounded-lg object-contain border bg-white p-0.5 shadow-sm flex-shrink-0" onerror="this.src='../../assets/images/default.png'">
                <div class="text-xs min-w-0">
                    <h4 class="font-bold text-slate-900">${p.product_name} (${p.product_size || 'Standard'})</h4>
                    <p class="text-slate-500 font-mono">Code: ${p.product_code || '-'} | Cost: ₱${parseFloat(p.unit_cost || 0).toFixed(2)}</p>
                </div>
            </div>
            <button type="button" class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">
                <i class="fa-solid fa-plus"></i> Add
            </button>
        `;
        container.appendChild(div);
    });

    resultsContainer.classList.remove("hidden");
}

function handleManualSelect(selectElem) {
    const productId = selectElem.value;
    if (!productId) return;

    const matched = products.find(p => p.id == productId);
    if (matched) {
        addProductToDelivery(matched);
    }
    selectElem.value = "";
}

function showToast(msg, type='success') {
    const t = document.createElement('div');
    t.className = `pointer-events-auto px-3.5 py-2.5 rounded-lg shadow-md border text-xs font-bold flex gap-2 animate-fade ${type==='success'?'bg-emerald-50 border-emerald-200 text-emerald-800':'bg-rose-50 border-rose-200 text-rose-800'}`;
    t.innerHTML = `<i class="fa-solid ${type==='success'?'fa-circle-check':'fa-triangle-exclamation'} mt-0.5"></i> ${msg}`;
    document.getElementById('toast').appendChild(t);
    setTimeout(() => { t.style.opacity='0'; setTimeout(()=>t.remove(), 200); }, 2500);
}

async function openScanner() {
    scanLocked = false;
    if (!html5QrCode) {
        html5QrCode = new Html5Qrcode("reader");
    }

    try {
        if(html5QrCode.isScanning) {
            await html5QrCode.stop();
        }

        const cameras = await Html5Qrcode.getCameras();
        if (cameras && cameras.length > 0) {
            await html5QrCode.start(
                cameras[0].id,
                { fps: 15, qrbox: { width: 200, height: 200 }, aspectRatio: 1.0, rememberLastUsedCamera: true },
                onScanSuccess
            );
        } else {
            showToast("No camera detected.", "error");
        }
    } catch(e) {
        showToast("Unable to start camera. Please ensure permissions are granted.", "error");
    }
}

async function closeScanner() {
    if (html5QrCode && html5QrCode.isScanning) {
        try {
            await html5QrCode.stop();
        } catch(e){}
    }
    scanLocked = false;
}

function onScanSuccess(decodedText) {
    if (scanLocked) return;
    scanLocked = true;
    processScannedCode(decodedText.trim());
    closeScanner();
    document.getElementById("scannerModeBtn").click();
}

function processScannedCode(code) {
    const scannerInput = document.getElementById("wirelessScanner");
    document.getElementById("productNotRegistered").classList.add("hidden");
    
    fetch("../../process/admin/find_product.php?code=" + encodeURIComponent(code))
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById("productPreview").classList.remove("hidden");
            document.getElementById("previewImage").src = "../../" + (data.image || 'assets/images/default.png');
            document.getElementById("previewName").innerHTML = data.name;
            document.getElementById("previewCode").innerHTML = data.code;
            document.getElementById("previewCategory").innerHTML = data.category;
            document.getElementById("previewSupplier").innerHTML = data.supplier;
            document.getElementById("previewSize").innerHTML = data.size || 'Standard';
            document.getElementById("previewStatus").innerHTML = data.status;
            document.getElementById("previewStock").innerHTML = (data.stock ?? 0) + " pcs";
            document.getElementById("previewReorder").innerHTML = (data.reorder_level ?? 0) + " pcs";
            document.getElementById("previewCost").innerHTML = "₱" + parseFloat(data.cost || 0).toFixed(2);
            document.getElementById("previewPrice").innerHTML = "₱" + parseFloat(data.price || 0).toFixed(2);
            
            addProductToDelivery(data);
        } else {
            document.getElementById("productPreview").classList.add("hidden");
            document.getElementById("productNotRegistered").classList.remove("hidden");
            showToast("Product not registered.", "error");
        }
        if(scannerInput) {
            scannerInput.value = "";
            scannerInput.focus();
        }
        scanLocked = false;
    })
    .catch(() => {
        showToast("Failed to process QR code.", "error");
        if(scannerInput) {
            scannerInput.value = "";
            scannerInput.focus();
        }
        scanLocked = false;
    });
}

function dismissUnregisteredNotice() {
    document.getElementById("productNotRegistered").classList.add("hidden");
    document.getElementById("wirelessScanner").focus();
}

function addProductToDelivery(productData) {
    const supplierInput = document.getElementById('supplierInput');
    const supplierDisplay = document.getElementById('supplierDisplay');
    const productSupplierName = (productData.supplier || '').trim();
    
    // Improved matching logic: Case-insensitive and trimmed lookup
    let matchedSupplier = suppliers.find(s => 
        s.supplier_name.trim().toLowerCase() === productSupplierName.toLowerCase()
    );

    // Auto-fill logic based on cart contents
    if (cart.length === 0) {
        if (matchedSupplier) {
            supplierInput.value = matchedSupplier.id;
            supplierDisplay.value = matchedSupplier.supplier_name;
        } else {
            // Fail-safe if product's supplier string doesn't match db record perfectly.
            showToast(`Warning: We couldn't properly link the supplier "${productSupplierName}" to the database. Delivery might be blocked.`, "error");
            supplierInput.value = "";
            supplierDisplay.value = productSupplierName !== '' ? productSupplierName : "Unknown Supplier";
        }
    } else {
        // Enforce same-supplier rule
        const currentSupplierId = supplierInput.value;
        if (matchedSupplier && matchedSupplier.id != currentSupplierId) {
            showToast(`This product belongs to a different supplier (${productSupplierName}). Deliveries must be sorted per supplier.`, "error");
            return;
        } else if (!matchedSupplier) {
            showToast("Supplier mismatch error. Please check product records.", "error");
            return;
        }
    }

    const id = productData.id;
    const cost = parseFloat(productData.unit_cost || productData.cost) || 0;
    
    let exist = cart.find(i => i.id == id);
    if(exist) {
        exist.qty += 1;
        exist.cost = cost;
        showToast("Quantity updated.");
    } else {
        cart.push({ 
            id: productData.id,
            code: productData.product_code || productData.code,
            name: productData.product_name || productData.name,
            category: productData.category || '-',
            size: productData.product_size || productData.size || 'Standard',
            supplier: productData.supplier || '-',
            qty: 1, 
            cost: cost,
            image: productData.front_image || productData.image 
        });
        showToast("Added to delivery: " + (productData.product_name || productData.name));
    }
    
    renderCart();
    checkFormValidity();
}

function updateCartQty(id, change) {
    let item = cart.find(i => i.id == id);
    if (!item) return;

    let newQty = item.qty + change;
    if (newQty < 1) {
        removeItem(id);
        return;
    }
    item.qty = newQty;
    renderCart();
    checkFormValidity();
}

function setCardQty(id, val) {
    let item = cart.find(i => i.id == id);
    if (!item) return;

    let qty = parseInt(val) || 1;
    if (qty < 1) qty = 1;
    item.qty = qty;
    renderCart();
    checkFormValidity();
}

function removeItem(id) {
    cart = cart.filter(i => i.id != id);
    
    // Reset supplier if the cart becomes empty
    if (cart.length === 0) {
        document.getElementById('supplierInput').value = "";
        document.getElementById('supplierDisplay').value = "";
    }
    
    renderCart();
    checkFormValidity();
}

function renderCart() {
    const container = document.getElementById('cartCardsContainer');
    container.innerHTML = '';
    
    if(cart.length === 0) {
        container.innerHTML = `
            <div id="emptyCartNotice" class="text-center py-8 text-slate-400 bg-slate-50/30 rounded-xl border border-dashed border-slate-200 text-xs">
                No products added yet. Scan or search an item above.
            </div>
        `;
        document.getElementById('cartCountBadge').innerText = '0 Items';
        updateSummary(0, 0, 0);
        return;
    }

    let tQty = 0, tGrand = 0;
    cart.forEach((i, idx) => {
        let tot = i.qty * i.cost;
        tQty += i.qty; 
        tGrand += tot;
        const imgSrc = i.image ? "../../" + i.image : "../../assets/images/default.png";

        container.innerHTML += `
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 animate-fade">
                <input type="hidden" name="items[${idx}][product_id]" value="${i.id}">
                
                <div class="flex items-start gap-4">
                    <img src="${imgSrc}" class="w-16 h-16 rounded-xl object-contain border bg-white p-1 shadow-sm flex-shrink-0" onerror="this.src='../../assets/images/default.png'">
                    <div class="space-y-0.5 text-xs">
                        <h3 class="font-bold text-slate-900 text-sm">${i.name}</h3>
                        <p class="text-slate-500 font-mono">Code : <span class="text-slate-700">${i.code}</span></p>
                        <p class="text-slate-500">Supplier : <span class="font-medium text-slate-700">${i.supplier}</span></p>
                        <p class="text-slate-500">Category : <span class="font-medium text-slate-700">${i.category}</span> | Size : <span class="font-medium text-slate-700">${i.size}</span></p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between md:justify-end gap-6 w-full md:w-auto border-t md:border-t-0 pt-3 md:pt-0 border-slate-200">
                    <div class="flex items-center bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                        <button type="button" onclick="updateCartQty(${i.id}, -1)" class="px-3 py-2 bg-slate-50 hover:bg-slate-100 text-slate-600 transition text-xs font-bold">
                            <i class="fa-solid fa-minus"></i>
                        </button>
                        <input type="number" name="items[${idx}][quantity]" value="${i.qty}" min="1" onchange="setCardQty(${i.id}, this.value)" class="w-12 text-center text-xs font-bold border-0 focus:ring-0 p-0">
                        <button type="button" onclick="updateCartQty(${i.id}, 1)" class="px-3 py-2 bg-slate-50 hover:bg-slate-100 text-slate-600 transition text-xs font-bold">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>

                    <div>
                        <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Unit Cost</label>
                        <input type="number" step="0.01" name="items[${idx}][cost_price]" value="${i.cost}" onchange="updateRowCost(${i.id}, this.value)" class="w-24 border rounded-xl p-1.5 text-right text-xs bg-white focus:ring-2 focus:ring-emerald-500 outline-none shadow-sm">
                    </div>

                    <div class="text-right min-w-[70px]">
                        <span class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Total</span>
                        <span class="font-bold text-emerald-600 text-sm">₱${tot.toFixed(2)}</span>
                    </div>

                    <div>
                        <button type="button" onclick="removeItem(${i.id})" class="bg-red-50 hover:bg-red-100 text-red-600 p-2.5 rounded-xl transition flex items-center justify-center">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    document.getElementById('cartCountBadge').innerText = `${cart.length} Product${cart.length > 1 ? 's' : ''}`;
    updateSummary(cart.length, tQty, tGrand);
}

function updateRowCost(id, val) {
    let item = cart.find(i => i.id == id);
    if (item) {
        item.cost = parseFloat(val) || 0;
        renderCart();
        checkFormValidity();
    }
}

function updateSummary(items, qty, grand) {
    document.getElementById('totItems').innerText = items;
    document.getElementById('totQty').innerText = qty;
    document.getElementById('grandTot').innerText = `₱${grand.toFixed(2)}`;
}

// FIXED: Validate form correctly to enable the submit button
function checkFormValidity() {
    const deliveryDate = document.getElementById('deliveryDateInput').value.trim();
    const deliveryNo = document.getElementById('deliveryNoInput').value.trim();
    const supplierId = document.getElementById('supplierInput').value.trim();
    const drNumber = document.getElementById('drNumberInput').value.trim();
    
    const isInfoComplete = deliveryDate !== "" && deliveryNo !== "" && supplierId !== "" && drNumber !== "";
    const hasItems = cart.length > 0;
    
    const btn = document.getElementById('submitBtn');
    if (isInfoComplete && hasItems) {
        btn.disabled = false;
        btn.className = "bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-lg font-bold text-xs shadow-sm transition cursor-pointer";
    } else {
        btn.disabled = true;
        btn.className = "bg-slate-200 text-slate-400 px-6 py-2.5 rounded-lg font-bold text-xs shadow-none transition cursor-not-allowed";
    }
}
</script>

</body>
</html>