<?php
session_start();
require_once "../../config/database.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title>QR Product Scanner</title>

<script src="https://cdn.tailwindcss.com?plugins=forms"></script>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

</head>

<body class="bg-slate-100">

<?php include "sidebar.php"; ?> 

<div class="max-w-5xl mx-auto mt-10">

    <div class="bg-white rounded-2xl shadow p-6">

        <h2 class="text-2xl font-bold mb-5">
            QR Product Scanner
        </h2>

        <div class="grid md:grid-cols-2 gap-5">

            <!-- Laptop Camera -->
            <div class="border rounded-xl p-5">

                <h3 class="font-bold text-lg mb-3">
                    💻 Laptop Camera
                </h3>

                <button
                    id="startCamera"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-xl flex items-center gap-2">

                    <i class="fa-solid fa-camera"></i>

                    Start Camera

                </button>

                <div
                    id="reader"
                    class="mt-5 w-full aspect-square bg-gray-100 rounded-xl overflow-hidden"></div>

            </div>

            <!-- Wireless Scanner -->
            <div class="border rounded-xl p-5">

                <h3 class="font-bold text-lg mb-3">
                    📡 Wireless QR Scanner (Recommended)
                </h3>

                <input
                    id="barcode"
                    autofocus
                    class="w-full border rounded-xl p-4 text-xl"
                    placeholder="Scan QR Code Here">

                <p class="text-gray-500 text-sm mt-3">

                    Connect the USB/Bluetooth scanner then simply scan the QR code.

                </p>

            </div>

        </div>

    </div>

    <div
    id="productCard"
    class="hidden mt-8 bg-white rounded-2xl shadow-lg border overflow-hidden">

        <div class="grid md:grid-cols-2">

            <div class="bg-gray-100 p-6 flex items-center justify-center">

                <img
                    id="productImage"
                    src=""
                    class="w-full h-72 object-contain">

            </div>

            <div class="p-6">

                <h2
                id="productName"
                class="text-3xl font-bold mb-4">
                </h2>

                <table class="w-full text-lg">
                    <tbody>
                        <tr>
                            <td class="font-semibold py-1">Product Code</td>
                            <td id="productCode"></td>
                        </tr>
                        <tr>
                            <td class="font-semibold py-1">Category</td>
                            <td id="productCategory"></td>
                        </tr>
                        <tr>
                            <td class="font-semibold py-1">Supplier</td>
                            <td id="productSupplier"></td>
                        </tr>
                        <tr>
                            <td class="font-semibold py-1">Size</td>
                            <td id="productSize"></td>
                        </tr>
                        <tr>
                            <td class="font-semibold py-1">Available Stock</td>
                            <td id="productStock"></td>
                        </tr>
                        <tr>
                            <td class="font-semibold py-1">Unit Price</td>
                            <td>₱<span id="productPrice"></span></td>
                        </tr>
                        <tr>
                            <td class="font-semibold py-1">Status</td>
                            <td id="productStatus"></td>
                        </tr>
                    </tbody>
                </table>

            </div>

        </div>

    </div>

    <script>
        const wirelessInput = document.getElementById("barcode");
        const startCameraButton = document.getElementById("startCamera");
        let html5QrCode;

        function findProduct(code) {
            fetch("../../process/admin/find_product.php?code=" + encodeURIComponent(code))
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showProduct(data);
                    } else {
                        alert("Product not found.");
                        document.getElementById("productCard").classList.add("hidden");
                    }
                });
        }

        function showProduct(data) {
            document.getElementById("productCard").classList.remove("hidden");
            document.getElementById("productImage").src = "../../" + data.image;
            document.getElementById("productName").innerHTML = data.name;
            document.getElementById("productCode").innerHTML = data.code;
            document.getElementById("productCategory").innerHTML = data.category;
            document.getElementById("productSupplier").innerHTML = data.supplier;
            document.getElementById("productSize").innerHTML = data.size;
            document.getElementById("productStock").innerHTML = data.stock + " pcs";
            document.getElementById("productPrice").innerHTML = parseFloat(data.price).toFixed(2);
            document.getElementById("productStatus").innerHTML = data.status;
        }

        // Wireless Scanner Logic
        wirelessInput.focus();
        wirelessInput.addEventListener("keydown", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                if (this.value.trim() !== "") {
                    findProduct(this.value.trim());
                }
                this.value = "";
                this.focus();
            }
        });

        // Camera Scanner Logic
        startCameraButton.addEventListener("click", () => {
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("reader");
            }

            startCameraButton.disabled = true;
            startCameraButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Starting...';

            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText, decodedResult) => {
                    findProduct(decodedText);
                    html5QrCode.stop();
                    startCameraButton.disabled = false;
                    startCameraButton.innerHTML = '<i class="fa-solid fa-camera"></i> Start Camera';
                },
                (errorMessage) => {
                    // parse error, ignore.
                })
            .catch((err) => {
                alert("Unable to start camera. Please ensure permissions are granted.");
                startCameraButton.disabled = false;
                startCameraButton.innerHTML = '<i class="fa-solid fa-camera"></i> Start Camera';
            });
        });
    </script>

</body>

</html>