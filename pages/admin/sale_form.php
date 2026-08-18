<?php
$pageTitle = "New Sale Transaction";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/activity_log.php";
require_once "../../includes/admin_auth.php";

requireAdmin();

// Generate unique invoice number for the form display and processing
$defaultInvNo = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);

// Handle form submission for processing a sale
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? 'Cash');
    $discount = (float)($_POST['discount'] ?? 0.00);
    $products = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $created_by = $_SESSION['user_id'] ?? 1;
    // Get the invoice number from POST, or fallback to generation
    $invoice_no = trim($_POST['invoice_no'] ?? $defaultInvNo);

    if (empty($customer_name)) {
        $_SESSION['error_message'] = "Customer / Recipient Name is a required field.";
        header("Location: sale_form.php");
        exit();
    } elseif (empty($products)) {
        $_SESSION['error_message'] = "Please add at least one product to the cart.";
        header("Location: sale_form.php");
        exit();
    }

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        $subtotal = 0.00;
        $items_to_insert = [];

        // Validate stock and compute subtotal
        for ($i = 0; $i < count($products); $i++) {
            $product_id = (int)$products[$i];
            $qty = (int)$quantities[$i];

            if ($product_id <= 0 || $qty <= 0) continue;

            // Fetch current product price and stock
            $pQuery = "SELECT product_name, unit_price, current_stock FROM products WHERE id = ? FOR UPDATE";
            $stmtP = mysqli_prepare($conn, $pQuery);
            mysqli_stmt_bind_param($stmtP, "i", $product_id);
            mysqli_stmt_execute($stmtP);
            $pRes = mysqli_stmt_get_result($stmtP);
            $product = mysqli_fetch_assoc($pRes);

            if (!$product) {
                throw new Exception("Product ID {$product_id} not found.");
            }

            if ($product['current_stock'] < $qty) {
                throw new Exception("Insufficient stock for '{$product['product_name']}'. Available: {$product['current_stock']}, Requested: {$qty}");
            }

            $unit_price = (float)$product['unit_price'];
            $item_subtotal = $unit_price * $qty;
            $subtotal += $item_subtotal;

            $items_to_insert[] = [
                'product_id' => $product_id,
                'quantity' => $qty,
                'unit_price' => $unit_price,
                'subtotal' => $item_subtotal
            ];
        }

        if (empty($items_to_insert)) {
            throw new Exception("No valid items selected for sale.");
        }

        $grand_total = max(0, $subtotal - $discount);
        $sale_date = date('Y-m-d H:i:s');

        // Insert into sales table
        $saleQuery = "INSERT INTO sales (invoice_no, customer_name, subtotal, discount, total, payment_method, created_by, sale_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtSale = mysqli_prepare($conn, $saleQuery);
        mysqli_stmt_bind_param($stmtSale, "ssdddsis", $invoice_no, $customer_name, $subtotal, $discount, $grand_total, $payment_method, $created_by, $sale_date);
        mysqli_stmt_execute($stmtSale);
        $sale_id = mysqli_insert_id($conn);

        // Insert items and reduce inventory stock automatically
        foreach ($items_to_insert as $item) {
            // Insert sale item
            $itemQuery = "INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)";
            $stmtItem = mysqli_prepare($conn, $itemQuery);
            mysqli_stmt_bind_param($stmtItem, "iiidd", $sale_id, $item['product_id'], $item['quantity'], $item['unit_price'], $item['subtotal']);
            mysqli_stmt_execute($stmtItem);

            // Deduct stock real-time
            $updateStock = "UPDATE products SET current_stock = current_stock - ? WHERE id = ?";
            $stmtStock = mysqli_prepare($conn, $updateStock);
            mysqli_stmt_bind_param($stmtStock, "ii", $item['quantity'], $item['product_id']);
            mysqli_stmt_execute($stmtStock);

            // NEW: Log to stock_movements table
            $new_stock = $product['current_stock'] - $item['quantity'];
            $remarks = "Sales Invoice " . $invoice_no;
            $stockMoveStmt = $conn->prepare("
                INSERT INTO stock_movements 
                (product_id, transaction_type, reference_table, reference_id, qty_in, qty_out, balance_after, remarks, created_by) 
                VALUES (?, 'Sale', 'sales', ?, 0, ?, ?, ?, ?)
            ");
            if (!$stockMoveStmt) {
                throw new Exception("Failed to prepare stock movement statement: " . $conn->error);
            }
            $stockMoveStmt->bind_param("iiiisi", $item['product_id'], $sale_id, $item['quantity'], $new_stock, $remarks, $created_by);
            $stockMoveStmt->execute();
            $stockMoveStmt->close();
        }

        mysqli_commit($conn);
        logActivity($conn, $created_by, $_SESSION['fullname'], $_SESSION['username'], $_SESSION['role'], "Completed Sale #{$invoice_no}. Total: P" . number_format($grand_total, 2));

        $_SESSION['success_message'] = "Sale transaction successfully completed! Invoice: {$invoice_no}";
        header("Location: inventory_outsales.php");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error_message'] = "Transaction Failed: " . $e->getMessage();
        header("Location: sale_form.php");
        exit();
    }
}

// Fetch available products for selection with full details including category and image
$productsList = [];
$res = mysqli_query($conn, "SELECT id, product_code, product_name, category, product_size, unit_price, current_stock, front_image, qr_code FROM products WHERE current_stock > 0 ORDER BY product_name ASC");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $productsList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- html5-qrcode -->
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

        /* Clean quantity input in the sales cart */
        .cart-qty-input {
            width: 48px !important;
            min-width: 48px !important;
            max-width: 48px !important;
            height: 40px !important;
            padding: 0 !important;
            margin: 0 !important;
            border: 0 !important;
            border-left: 1px solid #e2e8f0 !important;
            border-right: 1px solid #e2e8f0 !important;
            border-radius: 0 !important;
            background: #fff !important;
            text-align: center !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            line-height: 40px !important;
            box-shadow: none !important;
            outline: none !important;
            appearance: textfield !important;
            -moz-appearance: textfield !important;
        }

        .cart-qty-input::-webkit-outer-spin-button,
        .cart-qty-input::-webkit-inner-spin-button {
            -webkit-appearance: none !important;
            margin: 0 !important;
        }

        .cart-qty-stepper {
            height: 40px;
            display: inline-flex;
            align-items: center;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
            flex-shrink: 0;
        }

        .cart-qty-btn {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            background: #f8fafc;
            color: #475569;
            cursor: pointer;
            transition: background .15s ease, color .15s ease;
        }

        .cart-qty-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

<?php include "sidebar.php"; ?> 

<main class="ml-0 md:ml-[270px] min-h-screen bg-[#f5f7fb] px-4 py-2 md:px-5 md:py-3 transition-all duration-300">
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- HEADER --> 
        <div class="flex justify-between items-center bg-white px-6 py-4 rounded-xl shadow-sm border border-slate-200/80">
            <div>
                <h1 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-cart-shopping text-amber-600"></i> New Sale Transaction
                </h1>
                <p class="text-sm text-slate-500 mt-0.5">Process point-of-sale transactions with complete product details and scanning support.</p>
            </div>
            <a href="inventory_outsales.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3.5 py-2 rounded-lg font-semibold text-xs transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>

        <!-- NOTIFICATIONS -->
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-red-600"></i>
                <span><?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" id="saleForm" class="max-w-full space-y-6">
            
            <!-- 1. ADD PRODUCTS -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-6">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[10px]">1</span> 
                    Add Product to Cart
                </h2>

                <!-- Scan & Manual Method Options Grid (3 Cards) -->
                <!-- Reduced bottom margin (mb-4 instead of mb-6) to fix gap spacing -->
                <div class="grid md:grid-cols-3 gap-4 mb-4">
                    <button type="button" id="scannerModeBtn" class="p-4 rounded-xl border-2 text-left transition flex items-center justify-between border-amber-500 bg-amber-50/40 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-qrcode"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">Wireless Scanner</h3>
                                <p class="text-[11px] text-slate-500">Recommended</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-amber-600 text-white">Active</span>
                    </button>

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

                    <button type="button" id="manualModeBtn" class="p-4 rounded-xl border-2 text-left transition flex items-center justify-between border-slate-200 bg-white hover:border-emerald-300">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
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

                <!-- Wireless Scanner Input Area -->
                <div id="wirelessArea" class="transition-all animate-fade mb-6">
                    <input
                        id="wirelessScanner"
                        type="text"
                        autofocus
                        autocomplete="off"
                        class="w-full rounded-xl border-2 border-amber-500 bg-slate-50/50 px-4 py-3 text-sm text-center font-mono text-slate-500 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500 focus:bg-white transition shadow-sm"
                        placeholder="Click here & scan barcode/QR code...">
                </div>

                <!-- Laptop Camera Area -->
                <div id="cameraArea" class="hidden transition-all animate-fade mb-6">
                    <div class="max-w-xl mx-auto bg-slate-50 border border-slate-200 rounded-xl p-5 text-center">
                        <h3 class="font-bold text-sm text-slate-800 mb-3 flex items-center justify-center gap-2"><i class="fa-solid fa-video text-blue-600"></i> Live Camera QR View</h3>
                        <div id="reader" class="mb-2 shadow-inner"></div>
                        <p class="text-xs text-slate-500 flex items-center justify-center gap-1.5"><i class="fa-solid fa-circle-info text-blue-500"></i> Hold the QR code about 15–25 cm from the camera lens.</p>
                    </div>
                </div>

                <!-- Manual Selection Area -->
                <div id="manualArea" class="hidden transition-all animate-fade mb-6">
                    <div class="max-w-2xl mx-auto bg-slate-50 border border-slate-200 rounded-xl p-5 space-y-4">
                        <div>
                            <h3 class="font-bold text-sm text-slate-800 mb-1 flex items-center gap-2"><i class="fa-solid fa-magnifying-glass text-emerald-600"></i> Search Product</h3>
                            <p class="text-xs text-slate-500 mb-2">Type by Product Name, Code or QR</p>
                            <div class="relative">
                                <input
                                    id="manualSearchInput"
                                    type="text"
                                    autocomplete="off"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500"
                                    placeholder="Type product name, code or QR to search...">
                                
                                <!-- Live Search Results Dropdown List -->
                                <div id="manualResultsContainer" class="absolute left-0 right-0 z-20 mt-1 bg-white border border-slate-200 rounded-xl overflow-hidden hidden max-h-60 overflow-y-auto shadow-lg">
                                    <div id="manualResultsBody" class="divide-y divide-slate-100"></div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center text-xs text-slate-400 font-medium">— OR SELECT FROM LIST —</div>
                        <div>
                            <select id="manualProductSelect" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500" onchange="handleManualSelect(this)">
                                <option value="">-- Choose Product from List --</option>
                                <?php foreach ($productsList as $p): ?>
                                    <option value="<?= $p['id']; ?>"><?= htmlspecialchars($p['product_name']); ?> (<?= htmlspecialchars($p['product_size'] ?? 'Std'); ?>) - Stock: <?= $p['current_stock']; ?> - ₱<?= number_format($p['unit_price'], 2); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. CUSTOMER INFORMATION -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-6">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[10px]">2</span> 
                    Customer Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Invoice Number *</label>
                        <input type="text" name="invoice_no" value="<?= $defaultInvNo; ?>" readonly class="w-full bg-slate-100 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-500 cursor-not-allowed focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Customer Name *</label>
                        <input type="text" name="customer_name" id="customerNameInput" placeholder="e.g., Juan Dela Cruz" oninput="checkFormValidity()" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Payment Method</label>
                        <input type="text" name="payment_method" value="Cash" readonly class="w-full bg-slate-100 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-500 cursor-not-allowed focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Discount (₱)</label>
                        <input type="number" step="0.01" min="0" name="discount" id="discountInput" value="0.00" oninput="calculateTotals()" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:bg-white transition">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Reference Number (Optional)</label>
                        <input type="text" name="reference_no" placeholder="Enter reference number" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:bg-white transition">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Remarks (Optional)</label>
                        <input type="text" name="remarks" placeholder="Enter notes about this sale..." class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:bg-white transition">
                    </div>
                </div>
            </div>

            <!-- 3. SALES CART -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[10px]">3</span> 
                        Sales Cart
                    </h2>
                    <span id="cartCountBadge" class="text-xs font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">0 Items</span>
                </div>
                
                <!-- Cart Cards Container -->
                <div id="cartCardsContainer" class="p-6 space-y-4">
                    <div id="emptyCartNotice" class="text-center py-8 text-slate-400 bg-slate-50/30 rounded-xl border border-dashed border-slate-200 text-xs">
                        No products added yet. Scan or select a product above.
                    </div>
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
                        <span class="block text-[10px] uppercase font-bold text-slate-400">Subtotal</span>
                        <span id="displaySubtotal" class="font-bold text-base text-slate-800">₱0.00</span>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase font-bold text-slate-400">Discount</span>
                        <span id="displayDiscount" class="font-bold text-base text-red-600">-₱0.00</span>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase font-bold text-slate-400">Grand Total</span>
                        <span id="displayGrandTotal" class="font-bold text-base text-amber-600">₱0.00</span>
                    </div>
                </div>
                <button type="submit" id="submitBtn" disabled class="bg-slate-200 text-slate-400 px-6 py-2.5 rounded-lg font-bold text-xs shadow-none transition cursor-not-allowed">
                    <i class="fa-solid fa-check"></i> Complete Sale & Deduct Stock
                </button>
            </div>

        </form>
    </div>
</main>

<!-- TOAST CONTAINER -->
<div id="toast" class="fixed bottom-6 right-6 z-50 flex flex-col gap-2 pointer-events-none"></div>

<script>
const productsData = <?= json_encode($productsList); ?>;
let cart = [];
let html5QrCode = null;
let scanLocked = false;

window.onload = function() {
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

    // Manual search input event listener
    const searchInput = document.getElementById("manualSearchInput");
    searchInput.addEventListener("input", function() {
        performManualSearch();
    });

    // Tab Switching Logic (3 Modes)
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

        navigator.mediaDevices.getUserMedia({ video: true })
            .then(() => openScanner())
            .catch(() => {
                alert("Please allow camera permission.");
                scannerBtn.click();
            });
    };

    manualBtn.onclick = () => {
        setActiveTab('manual');
        closeScanner();
        searchInput.focus();
    };
    
    calculateTotals();
    checkFormValidity();
};

function setActiveTab(mode) {
    const buttons = { scanner: 'scannerModeBtn', camera: 'cameraModeBtn', manual: 'manualModeBtn' };
    const areas = { scanner: 'wirelessArea', camera: 'cameraArea', manual: 'manualArea' };
    const activeClasses = { 
        scanner: 'border-amber-500 bg-amber-50/40', 
        camera: 'border-blue-500 bg-blue-50/40', 
        manual: 'border-emerald-500 bg-emerald-50/40' 
    };
    const activeSpan = { 
        scanner: 'bg-amber-600 text-white', 
        camera: 'bg-blue-600 text-white', 
        manual: 'bg-emerald-600 text-white' 
    };

    for (const key in buttons) {
        const btn = document.getElementById(buttons[key]);
        const area = document.getElementById(areas[key]);
        btn.className = "p-4 rounded-xl border-2 text-left transition flex items-center justify-between border-slate-200 bg-white";
        btn.querySelector("span").className = "text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500";
        btn.querySelector("span").innerText = "Switch";
        area.classList.add("hidden");
    }

    const activeBtn = document.getElementById(buttons[mode]);
    activeBtn.classList.add(...activeClasses[mode].split(' '));
    activeBtn.querySelector("span").className = `text-[10px] font-bold px-2.5 py-1 rounded-full ${activeSpan[mode]}`;
    activeBtn.querySelector("span").innerText = "Active";
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

    const filtered = productsData.filter(p => 
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
            addProductToCart(p);
            document.getElementById("manualSearchInput").value = "";
            resultsContainer.classList.add("hidden");
        };
        div.innerHTML = `
            <div class="flex items-center gap-3">
                <img src="${imgSrc}" class="w-10 h-10 rounded-lg object-contain border bg-white p-0.5 shadow-sm flex-shrink-0" onerror="this.src='../../assets/images/default.png'">
                <div class="text-xs min-w-0">
                    <h4 class="font-bold text-slate-900">${p.product_name} (${p.product_size || 'Std'})</h4>
                    <p class="text-slate-500 font-mono">Code: ${p.product_code || '-'} | Stock: ${p.current_stock} | Price: ₱${parseFloat(p.unit_price || 0).toFixed(2)}</p>
                </div>
            </div>
            <button type="button" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">
                <i class="fa-solid fa-plus"></i> Add
            </button>
        `;
        container.appendChild(div);
    });

    resultsContainer.classList.remove("hidden");
}

function showToast(msg, type='success') {
    const t = document.createElement('div');
    t.className = `pointer-events-auto px-3.5 py-2.5 rounded-xl shadow-md border text-xs font-bold flex gap-2 animate-fade ${type==='success'?'bg-emerald-50 border-emerald-200 text-emerald-800':'bg-rose-50 border-rose-200 text-rose-800'}`;
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
        const cameras = await Html5Qrcode.getCameras();
        if (!cameras.length) {
            showToast("No camera detected.", "error");
            return;
        }

        if (html5QrCode.isScanning) {
            try { await html5QrCode.stop(); } catch(e) {}
        }

        await html5QrCode.start(
            cameras[0].id,
            { fps: 15, qrbox: { width: 200, height: 200 }, aspectRatio: 1.0, rememberLastUsedCamera: true },
            onScanSuccess
        );
    } catch(err){
        showToast("Unable to start camera.", "error");
    }
}

async function closeScanner() {
    if (html5QrCode && html5QrCode.isScanning) {
        try { await html5QrCode.stop(); } catch(e){}
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
    const matched = productsData.find(p => p.product_code === code || p.qr_code === code);
    
    if (matched) {
        addProductToCart(matched);
    } else {
        showToast("Product not found or out of stock.", "error");
    }
    
    scannerInput.value = "";
    scannerInput.focus();
    scanLocked = false;
}

function handleManualSelect(selectElem) {
    const productId = selectElem.value;
    if (!productId) return;

    const matched = productsData.find(p => p.id == productId);
    if (matched) {
        addProductToCart(matched);
    }
    selectElem.value = ""; // Reset dropdown back to default placeholder
}

function addProductToCart(product) {
    let exist = cart.find(i => i.id == product.id);
    if (exist) {
        if (exist.qty < parseInt(product.current_stock)) {
            exist.qty += 1;
            showToast("Quantity updated.");
        } else {
            showToast("Reached maximum available stock limit.", "error");
            return;
        }
    } else {
        cart.push({
            id: product.id,
            code: product.product_code || '-',
            name: product.product_name,
            category: product.category || '-',
            size: product.product_size || 'Std',
            price: parseFloat(product.unit_price) || 0,
            stock: parseInt(product.current_stock) || 0,
            image: product.front_image || '',
            qty: 1
        });
        showToast("Added to cart: " + product.product_name);
    }
    renderCart();
}

function updateCartQty(id, change) {
    let item = cart.find(i => i.id == id);
    if (!item) return;

    let newQty = item.qty + change;
    if (newQty > item.stock) {
        showToast("Reached maximum available stock limit.", "error");
        return;
    }
    if (newQty < 1) {
        removeItem(id);
        return;
    }
    item.qty = newQty;
    renderCart();
}

function setCardQty(id, val) {
    let item = cart.find(i => i.id == id);
    if (!item) return;

    let qty = parseInt(val) || 1;
    if (qty > item.stock) {
        qty = item.stock;
        showToast("Exceeds available stock.", "error");
    }
    if (qty < 1) qty = 1;
    item.qty = qty;
    renderCart();
}

function removeItem(id) {
    cart = cart.filter(i => i.id != id);
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cartCardsContainer');
    container.innerHTML = '';

    if (cart.length === 0) {
        container.innerHTML = `
            <div id="emptyCartNotice" class="text-center py-8 text-slate-400 bg-slate-50/30 rounded-xl border border-dashed border-slate-200 text-xs">
                No products added yet. Scan or select a product above.
            </div>
        `;
        calculateTotals();
        return;
    }

    cart.forEach((i, idx) => {
        let itemTotal = i.qty * i.price;
        const imgSrc = i.image ? "../../" + i.image : "../../assets/images/default.png";

        container.innerHTML += `
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 animate-fade">
                <!-- Hidden inputs for form submission -->
                <input type="hidden" name="product_id[]" value="${i.id}">
                <input type="hidden" name="quantity[]" value="${i.qty}">

                <!-- Left: Product Image & Meta -->
                <div class="flex items-center gap-4">
                    <img src="${imgSrc}" class="w-16 h-16 rounded-xl object-contain border bg-white p-1 shadow-sm flex-shrink-0" onerror="this.src='../../assets/images/default.png'">
                    <div class="space-y-1 text-xs">
                        <h3 class="font-bold text-slate-900 text-sm">${i.name}</h3>
                        <div class="text-slate-500 flex flex-wrap gap-x-3 gap-y-0.5 font-mono">
                            <span>Code: <strong class="text-slate-700">${i.code}</strong></span>
                            <span>•</span>
                            <span>Category: <strong class="text-slate-700">${i.category}</strong></span>
                            <span>•</span>
                            <span>Size: <strong class="text-slate-700">${i.size}</strong></span>
                        </div>
                        <p class="text-xs text-amber-700 font-medium">Stock Available: ${i.stock} pcs</p>
                    </div>
                </div>

                <!-- Right: Controls (Qty Stepper, Price, Subtotal, Delete) -->
                <div class="flex flex-wrap items-center justify-between md:justify-end gap-4 lg:gap-6 w-full md:w-auto border-t md:border-t-0 pt-3 md:pt-0 border-slate-200">
                    <!-- Qty Stepper Component -->
                    <div class="cart-qty-stepper">
                        <button type="button"
                                onclick="updateCartQty(${i.id}, -1)"
                                class="cart-qty-btn"
                                aria-label="Decrease quantity">
                            <i class="fa-solid fa-minus text-xs"></i>
                        </button>

                        <input type="number"
                               value="${i.qty}"
                               min="1"
                               max="${i.stock}"
                               onchange="setCardQty(${i.id}, this.value)"
                               class="cart-qty-input"
                               aria-label="Quantity">

                        <button type="button"
                                onclick="updateCartQty(${i.id}, 1)"
                                class="cart-qty-btn"
                                aria-label="Increase quantity">
                            <i class="fa-solid fa-plus text-xs"></i>
                        </button>
                    </div>

                    <!-- Unit Price -->
                    <div class="text-right">
                        <span class="block text-[10px] uppercase font-bold text-slate-400">Unit Price</span>
                        <span class="text-xs font-semibold text-slate-700">₱${i.price.toFixed(2)}</span>
                    </div>

                    <!-- Subtotal -->
                    <div class="text-right min-w-[70px]">
                        <span class="block text-[10px] uppercase font-bold text-slate-400">Subtotal</span>
                        <span class="font-bold text-amber-600 text-sm">₱${itemTotal.toFixed(2)}</span>
                    </div>

                    <!-- Remove Action -->
                    <div>
                        <button type="button" onclick="removeItem(${i.id})" class="bg-red-50 hover:bg-red-100 text-red-600 p-2.5 rounded-xl transition flex items-center justify-center">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    let totalQty = 0;
    cart.forEach(i => {
        subtotal += i.qty * i.price;
        totalQty += i.qty;
    });

    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const grandTotal = Math.max(0, subtotal - discount);

    document.getElementById('cartCountBadge').innerText = `${cart.length} Item${cart.length !== 1 ? 's' : ''}`;
    document.getElementById('totItems').innerText = cart.length;
    document.getElementById('totQty').innerText = totalQty;
    document.getElementById('displaySubtotal').innerText = '₱' + subtotal.toFixed(2);
    document.getElementById('displayDiscount').innerText = '-₱' + discount.toFixed(2);
    document.getElementById('displayGrandTotal').innerText = '₱' + grandTotal.toFixed(2);

    checkFormValidity();
}

// Ensure both Customer Name is present AND cart is not empty before enabling the Submit button
function checkFormValidity() {
    const customerName = document.getElementById('customerNameInput').value.trim();
    const hasItems = cart.length > 0;
    const btn = document.getElementById('submitBtn');

    if (customerName !== "" && hasItems) {
        btn.disabled = false;
        btn.className = "bg-amber-600 hover:bg-amber-700 text-white px-6 py-2.5 rounded-lg font-bold text-xs shadow-sm transition cursor-pointer flex items-center gap-2";
    } else {
        btn.disabled = true;
        btn.className = "bg-slate-200 text-slate-400 px-6 py-2.5 rounded-lg font-bold text-xs shadow-none transition cursor-not-allowed flex items-center gap-2";
    }
}
</script>

</body>
</html>