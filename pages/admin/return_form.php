<?php
$pageTitle = "New Supplier Return";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../includes/admin_auth.php";

requireAdmin();

// 1. Fetch Suppliers
$suppliers = [];
$suppResult = mysqli_query($conn, "SELECT id, supplier_name FROM suppliers WHERE status = 'Active' ORDER BY supplier_name ASC");
if ($suppResult) {
    while ($row = mysqli_fetch_assoc($suppResult)) {
        $suppliers[] = $row;
    }
}

// 2. Fetch Products
$productData = [];
$prodDataResult = mysqli_query($conn, "
    SELECT 
        p.id, p.product_code, p.product_name, p.category, p.product_size, 
        p.supplier, p.current_stock, p.unit_cost, p.front_image, p.qr_code 
    FROM products p
    ORDER BY p.product_name ASC
");
if ($prodDataResult) {
    while ($row = mysqli_fetch_assoc($prodDataResult)) {
        $productData[] = $row;
    }
}

$defaultReturnNo = "SRET-" . date('Ymd') . "-" . rand(1000, 9999);
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
                    <i class="fa-solid fa-rotate-left text-amber-600"></i> New Supplier Return
                </h1>
                <p class="text-sm text-slate-500 mt-0.5">Record products being returned from ISU Merchandising back to suppliers.</p>
            </div>
            <a href="returns.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3.5 py-2 rounded-lg font-semibold text-xs transition flex items-center gap-1.5">
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

        <form action="../../process/admin/save_supplier_return.php" method="POST" id="returnForm" enctype="multipart/form-data" class="space-y-6">

            <!-- 1. RETURN PRODUCTS -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-6">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[10px]">1</span> 
                    Return Products
                </h2>

                <!-- Scan Method Options Grid -->
                <div class="grid md:grid-cols-3 gap-4 mb-6">
                    <button type="button" id="scannerModeBtn" class="p-4 rounded-xl border-2 text-left transition flex items-center justify-between border-amber-500 bg-amber-50/40 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-lg"><i class="fa-solid fa-qrcode"></i></div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">Wireless Scanner</h3>
                                <p class="text-[10px] text-slate-500">Recommended</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-amber-600 text-white">Active</span>
                    </button>
                    <button type="button" id="cameraModeBtn" class="p-4 rounded-xl border-2 text-left transition flex items-center justify-between border-slate-200 bg-white hover:border-blue-300">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg"><i class="fa-solid fa-camera"></i></div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">Laptop Camera</h3>
                                <p class="text-[10px] text-slate-500">Scan using webcam</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">Switch</span>
                    </button>
                    <button type="button" id="manualModeBtn" class="p-4 rounded-xl border-2 text-left transition flex items-center justify-between border-slate-200 bg-white hover:border-emerald-300">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg"><i class="fa-solid fa-magnifying-glass"></i></div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">Manual Search</h3>
                                <p class="text-[10px] text-slate-500">Search or choose from list</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">Switch</span>
                    </button>
                </div>

                <!-- Wireless Scanner Input -->
                <div id="wirelessArea" class="transition-all animate-fade mb-6">
                    <input id="wirelessScanner" type="text" autofocus autocomplete="off" class="w-full rounded-xl border-2 border-amber-500 bg-slate-50/50 px-4 py-3.5 text-sm text-center font-mono text-slate-500 focus:ring-2 focus:ring-amber-500 focus:bg-white transition shadow-sm" placeholder="Click here & scan barcode/QR code...">
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
                                <input id="manualSearchInput" type="text" autocomplete="off" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500" placeholder="Type product name, code or QR to search...">
                                <div id="manualResultsContainer" class="absolute left-0 right-0 z-20 mt-1 bg-white border border-slate-200 rounded-xl overflow-hidden hidden max-h-60 overflow-y-auto shadow-lg">
                                    <div id="manualResultsBody" class="divide-y divide-slate-100"></div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center text-xs text-slate-400 font-medium">— OR SELECT FROM LIST —</div>
                        <div>
                            <select id="manualProductSelect" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500" onchange="handleManualSelect(this)">
                                <option value="">-- Choose Product from List --</option>
                                <?php foreach ($productData as $p): ?>
                                    <option value="<?= $p['id']; ?>"><?= htmlspecialchars($p['product_name']); ?> (<?= htmlspecialchars($p['product_size'] ?? 'Std'); ?>) - Stock: <?= $p['current_stock']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. SUPPLIER RETURN INFORMATION -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-6">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[10px]">2</span> 
                    Supplier Return Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Return Date *</label>
                        <input type="datetime-local" name="return_date" value="<?= date('Y-m-d\TH:i'); ?>" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Return Slip No. *</label>
                        <input type="text" name="return_no" value="<?= $defaultReturnNo; ?>" readonly class="w-full bg-slate-100 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-500 cursor-not-allowed focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Supplier (Auto-filled) *</label>
                        <input type="text" name="supplier" id="supplierDisplay" readonly placeholder="Auto-filled when product is added..." class="w-full bg-slate-100 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-500 cursor-not-allowed focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Reference No. (Optional)</label>
                        <input type="text" name="reference_no" placeholder="e.g., PO-123, DR-456" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:bg-white transition">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Remarks (Optional)</label>
                        <input type="text" name="remarks" placeholder="Enter notes about this return..." class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:bg-white transition">
                    </div>
                </div>
            </div>

            <!-- 3. PRODUCTS TO RETURN (CART) -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[10px]">3</span> 
                        Products to Return
                    </h2>
                    <span id="cartCountBadge" class="text-xs font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">0 Items</span>
                </div>
                
                <div id="cartCardsContainer" class="p-6 space-y-4">
                    <div id="emptyCartNotice" class="text-center py-8 text-slate-400 bg-slate-50/30 rounded-xl border border-dashed border-slate-200 text-xs">
                        No products added yet. Scan or select a product above.
                    </div>
                </div>
            </div>

            <!-- SUMMARY & SUBMIT -->
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
                        <span class="block text-[10px] uppercase font-bold text-slate-400">Total Return Value</span>
                        <span id="grandTot" class="font-bold text-base text-amber-600">₱0.00</span>
                    </div>
                </div>
                <button type="submit" id="submitBtn" disabled class="bg-slate-200 text-slate-400 px-6 py-2.5 rounded-lg font-bold text-xs shadow-none transition cursor-not-allowed flex items-center gap-2">
                    <i class="fa-solid fa-check"></i> Save Supplier Return
                </button>
            </div>
        </form>
    </div>
</main>

<!-- TOAST CONTAINER -->
<div id="toast" class="fixed bottom-6 right-6 z-50 flex flex-col gap-2 pointer-events-none"></div>

<script>
const productsData = <?= json_encode($productData); ?>;
const suppliersData = <?= json_encode($suppliers); ?>;
let cart = [];
let html5QrCode = null;
let scanLocked = false;

window.onload = () => {
    const scannerInput = document.getElementById("wirelessScanner");
    scannerInput.focus();

    document.getElementById("wirelessArea").addEventListener("click", () => scannerInput.focus());

    let scanTimeout = null;
    scannerInput.addEventListener("input", function() {
        clearTimeout(scanTimeout);
        const code = this.value.trim();
        if (!code) return;
        scanTimeout = setTimeout(() => processScannedCode(code), 150);
    });

    scannerInput.addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            clearTimeout(scanTimeout);
            const code = this.value.trim();
            if (code) processScannedCode(code);
        }
    });

    document.getElementById("manualSearchInput").addEventListener("input", performManualSearch);

    document.getElementById("scannerModeBtn").onclick = () => {
        setActiveTab('scanner');
        closeScanner();
        scannerInput.focus();
    };
    document.getElementById("cameraModeBtn").onclick = () => {
        setActiveTab('camera');
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(() => openScanner())
            .catch(() => {
                alert("Please allow camera permission.");
                document.getElementById("scannerModeBtn").click();
            });
    };
    document.getElementById("manualModeBtn").onclick = () => {
        setActiveTab('manual');
        closeScanner();
        document.getElementById("manualSearchInput").focus();
    };
    
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
    } else {
        filtered.forEach(p => {
            const imgSrc = p.front_image ? `../../${p.front_image}` : "../../assets/images/default.png";
            const div = document.createElement("div");
            div.className = "p-3 hover:bg-slate-50 transition flex items-center justify-between gap-3 cursor-pointer";
            div.onclick = () => {
                addProductToReturn(p);
                document.getElementById("manualSearchInput").value = "";
                resultsContainer.classList.add("hidden");
            };
            div.innerHTML = `
                <div class="flex items-center gap-3">
                    <img src="${imgSrc}" class="w-10 h-10 rounded-lg object-contain border bg-white p-0.5 shadow-sm flex-shrink-0" onerror="this.src='../../assets/images/default.png'">
                    <div class="text-xs min-w-0">
                        <h4 class="font-bold text-slate-900">${p.product_name} (${p.product_size || 'Std'})</h4>
                        <p class="text-slate-500 font-mono">Code: ${p.product_code || '-'} | Stock: ${p.current_stock}</p>
                    </div>
                </div>
                <button type="button" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition"><i class="fa-solid fa-plus"></i> Add</button>
            `;
            container.appendChild(div);
        });
    }
    resultsContainer.classList.remove("hidden");
}

function handleManualSelect(selectElem) {
    const productId = selectElem.value;
    if (!productId) return;
    const matched = productsData.find(p => p.id == productId);
    if (matched) addProductToReturn(matched);
    selectElem.value = "";
}

function showToast(msg, type = 'success') {
    const t = document.createElement('div');
    t.className = `pointer-events-auto px-3.5 py-2.5 rounded-xl shadow-md border text-xs font-bold flex gap-2 animate-fade ${type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800'}`;
    t.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'} mt-0.5"></i> ${msg}`;
    document.getElementById('toast').appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 200); }, 2500);
}

async function openScanner() {
    scanLocked = false;
    if (!html5QrCode) html5QrCode = new Html5Qrcode("reader");
    try {
        const cameras = await Html5Qrcode.getCameras();
        if (!cameras.length) {
            showToast("No camera detected.", "error");
            return;
        }
        if (html5QrCode.isScanning) await html5QrCode.stop();
        await html5QrCode.start(cameras[0].id, { fps: 15, qrbox: { width: 200, height: 200 }, aspectRatio: 1.0, rememberLastUsedCamera: true }, onScanSuccess);
    } catch (err) {
        showToast("Unable to start camera.", "error");
    }
}

async function closeScanner() {
    if (html5QrCode && html5QrCode.isScanning) {
        try { await html5QrCode.stop(); } catch (e) {}
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
        addProductToReturn(matched);
    } else {
        showToast("Product not found.", "error");
    }
    scannerInput.value = "";
    scannerInput.focus();
    scanLocked = false;
}

function addProductToReturn(product) {
    const supplierDisplay = document.getElementById('supplierDisplay');
    const productSupplierName = (product.supplier || '').trim();

    if (cart.length === 0) {
        supplierDisplay.value = productSupplierName;
    } else {
        if (supplierDisplay.value !== productSupplierName) {
            showToast(`This product belongs to a different supplier (${productSupplierName}). Returns must be sorted per supplier.`, "error");
            return;
        }
    }

    let exist = cart.find(i => i.id == product.id);
    if (exist) {
        if (exist.qty < parseInt(product.current_stock)) {
            exist.qty += 1;
            showToast("Quantity updated.");
        } else {
            showToast("Return quantity cannot exceed available stock.", "error");
            return;
        }
    } else {
        cart.push({
            id: product.id,
            code: product.product_code || '-',
            name: product.product_name,
            category: product.category || '-',
            size: product.product_size || 'Std',
            supplier: product.supplier || '-',
            cost: parseFloat(product.unit_cost) || 0,
            stock: parseInt(product.current_stock) || 0,
            image: product.front_image || '',
            qty: 1,
            reason: 'Supplier Delivered Wrong Item' // Default reason
        });
        showToast("Added to return list: " + product.product_name);
    }
    renderCart();
}

function updateCartQty(id, change) {
    let item = cart.find(i => i.id == id);
    if (!item) return;
    let newQty = item.qty + change;
    if (newQty > item.stock) {
        showToast("Return quantity cannot exceed available stock.", "error");
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

function updateItemReason(id, reason) {
    let item = cart.find(i => i.id == id);
    if (item) item.reason = reason;
}

function removeItem(id) {
    cart = cart.filter(i => i.id != id);
    if (cart.length === 0) {
        document.getElementById('supplierDisplay').value = "";
    }
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cartCardsContainer');
    container.innerHTML = '';

    if (cart.length === 0) {
        container.innerHTML = `<div id="emptyCartNotice" class="text-center py-8 text-slate-400 bg-slate-50/30 rounded-xl border border-dashed border-slate-200 text-xs">No products added yet. Scan or select a product above.</div>`;
    } else {
        cart.forEach((i, idx) => {
            const imgSrc = i.image ? `../../${i.image}` : "../../assets/images/default.png";
            const itemTotal = i.qty * i.cost;
            container.innerHTML += `
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 animate-fade">
                    <input type="hidden" name="items[${idx}][product_id]" value="${i.id}">
                    <div class="flex items-center gap-4 flex-1">
                        <img src="${imgSrc}" class="w-16 h-16 rounded-xl object-contain border bg-white p-1 shadow-sm flex-shrink-0" onerror="this.src='../../assets/images/default.png'">
                        <div class="space-y-1 text-xs">
                            <h3 class="font-bold text-slate-900 text-sm">${i.name}</h3>
                            <div class="text-slate-500 flex flex-wrap gap-x-3 gap-y-0.5 font-mono">
                                <span>Code: <strong class="text-slate-700">${i.code}</strong></span><span>•</span>
                                <span>Category: <strong class="text-slate-700">${i.category}</strong></span><span>•</span>
                                <span>Size: <strong class="text-slate-700">${i.size}</strong></span>
                            </div>
                            <p class="text-xs text-amber-700 font-medium">Stock Available: ${i.stock} pcs</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center justify-between md:justify-end gap-4 w-full md:w-auto border-t md:border-t-0 pt-3 md:pt-0 border-slate-200">
                        <div class="flex items-center bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                            <button type="button" onclick="updateCartQty(${i.id}, -1)" class="px-3 py-2 bg-slate-50 hover:bg-slate-100 text-slate-600 transition text-xs font-bold"><i class="fa-solid fa-minus"></i></button>
                            <input type="number" name="items[${idx}][quantity]" value="${i.qty}" min="1" max="${i.stock}" onchange="setCardQty(${i.id}, this.value)" class="w-12 text-center text-xs font-bold border-0 focus:ring-0 p-0">
                            <button type="button" onclick="updateCartQty(${i.id}, 1)" class="px-3 py-2 bg-slate-50 hover:bg-slate-100 text-slate-600 transition text-xs font-bold"><i class="fa-solid fa-plus"></i></button>
                        </div>
                        <div class="w-full md:w-48">
                            <select name="items[${idx}][reason]" onchange="updateItemReason(${i.id}, this.value)" class="w-full text-xs border-slate-200 rounded-lg focus:ring-amber-500 focus:border-amber-500">
                                <option>Supplier Delivered Wrong Item</option>
                                <option>Supplier Delivered Damaged Item</option>
                                <option>Factory Defect</option>
                                <option>Quality Defect</option>
                                <option>Incorrect Size</option>
                                <option>Incorrect Color</option>
                                <option>Excess Delivered Quantity</option>
                                <option>Other Supplier Issue</option>
                            </select>
                        </div>
                        <div class="w-full md:w-auto">
                            <input type="file" name="items[${idx}][photo]" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                        </div>
                        <div>
                            <button type="button" onclick="removeItem(${i.id})" class="bg-red-50 hover:bg-red-100 text-red-600 p-2.5 rounded-xl transition flex items-center justify-center"><i class="fa-solid fa-trash text-xs"></i></button>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    calculateTotals();
}

function calculateTotals() {
    let totalQty = 0;
    let grandTotal = 0;
    cart.forEach(i => {
        totalQty += i.qty;
        grandTotal += i.qty * i.cost;
    });

    document.getElementById('cartCountBadge').innerText = `${cart.length} Item${cart.length !== 1 ? 's' : ''}`;
    document.getElementById('totItems').innerText = cart.length;
    document.getElementById('totQty').innerText = totalQty;
    document.getElementById('grandTot').innerText = '₱' + grandTotal.toFixed(2);

    checkFormValidity();
}

function checkFormValidity() {
    const supplier = document.getElementById('supplierDisplay').value.trim();
    const hasItems = cart.length > 0;
    const btn = document.getElementById('submitBtn');

    if (supplier !== "" && hasItems) {
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
