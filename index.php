<?php
// ISU CBAO Merch Billing
// Public Homepage
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ISU CBAO Merch Billing</title>

    <meta name="description"
          content="Inventory Management & Billing System for Isabela State University CBAO Merchandising Office.">

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#065f2f">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="ISU Merch Billing">
    <link rel="apple-touch-icon" href="/assets/images/icon-192.png">

    <style>

        /* =========================================================
           SYSTEM COLORS
        ========================================================= */

        :root {
            --isu-green: #064E3B;
            --isu-green-hover: #0F766E;
            --isu-accent: #10B981;
            --isu-light: #ECFDF5;

            --text-dark: #1f2937;
            --text-muted: #64748B;

            --white: #ffffff;
            --border: #E5E7EB;
            --background: #F5F7FA;

            --shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
        }


        /* =========================================================
           RESET
        ========================================================= */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        html {
            scroll-behavior: smooth;
        }


        body {
            font-family: Arial, Helvetica, sans-serif;
            background: var(--white);
            color: var(--text-dark);
            line-height: 1.6;
        }


        /* =========================================================
           HEADER
        ========================================================= */

        header {
            width: 100%;
            background: var(--white);
            border-bottom: 1px solid var(--border);

            position: sticky;
            top: 0;
            z-index: 1000;
        }


        .header-container {
            max-width: 1150px;
            margin: 0 auto;

            min-height: 78px;
            padding: 10px 25px;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }


        /* LOGO */

        .logo-area {
            display: flex;
            align-items: center;
            gap: 12px;

            text-decoration: none;
        }


        .logo-area img {
            width: 58px;
            height: 58px;

            object-fit: contain;
        }


        .logo-text {
            line-height: 1.25;
        }


        .logo-text strong {
            display: block;

            color: var(--isu-green);

            font-size: 17px;
            font-weight: 700;
        }


        .logo-text span {
            display: block;

            color: var(--text-muted);

            font-size: 11px;
            margin-top: 3px;
        }


        /* NAVIGATION */

        .navigation {
            display: flex;
            align-items: center;
            gap: 25px;
        }


        .navigation a {
            color: var(--text-dark);

            text-decoration: none;

            font-size: 14px;
            font-weight: 600;

            transition: color 0.2s ease;
        }


        .navigation a:hover {
            color: var(--isu-green);
        }


        /* LOGIN BUTTON */

        .login-button {
            background: var(--isu-green);

            color: var(--white) !important;

            padding: 9px 19px;

            border-radius: 6px;

            transition:
                background 0.2s ease,
                transform 0.2s ease;
        }


        .login-button:hover {
            background: var(--isu-green-hover);

            transform: translateY(-1px);
        }


        /* =========================================================
           INTRODUCTION / HERO
        ========================================================= */

        .intro {
            background: linear-gradient(135deg, #f8fafc 0%, var(--isu-light) 100%);

            border-bottom: 1px solid #dfe7ee;
        }


        .intro-container {
            max-width: 1150px;
            margin: 0 auto;

            min-height: 540px;

            padding: 70px 25px;

            display: grid;

            grid-template-columns: 1.5fr 0.8fr;

            gap: 60px;

            align-items: center;
        }


        .intro-content {
            max-width: 680px;
        }


        .intro-content h1 {
            color: var(--isu-green);

            font-size: 42px;

            line-height: 1.15;

            font-weight: 700;

            margin-bottom: 12px;
        }


        .intro-content h2 {
            color: var(--text-dark);

            font-size: 21px;

            font-weight: 600;

            margin-bottom: 18px;
        }


        .intro-content p {
            color: var(--text-muted);

            max-width: 680px;

            font-size: 16px;

            line-height: 1.7;

            margin-bottom: 25px;
        }


        /* INTRO BUTTON */

        .intro-button {
            display: inline-block;

            background: var(--isu-accent);

            color: var(--white);

            text-decoration: none;

            padding: 11px 23px;

            border-radius: 6px;

            font-size: 14px;

            font-weight: 600;

            transition:
                background 0.2s ease,
                transform 0.2s ease;
        }


        .intro-button:hover {
            background: var(--isu-green-hover);

            transform: translateY(-1px);
        }


        /* HERO LOGO */

        .intro-logo {
            display: flex;
            justify-content: center;
            align-items: center;
        }


        .intro-logo img {
            width: 260px;
            height: 260px;

            object-fit: contain;
        }


        /* =========================================================
           GENERAL SECTION
        ========================================================= */

        .section {
            max-width: 1150px;

            margin: 0 auto;

            padding: 65px 25px;
        }


        .section-title {
            text-align: center;

            margin-bottom: 40px;
        }


        .section-title h2 {
            color: var(--isu-green);

            font-size: 28px;

            font-weight: 700;

            margin-bottom: 8px;
        }


        .section-title p {
            color: var(--text-muted);

            font-size: 14px;
        }


        /* =========================================================
           FEATURES
        ========================================================= */

        .features {
            background: var(--white);
        }


        .feature-grid {
            display: grid;

            grid-template-columns: repeat(3, 1fr);

            gap: 20px;
        }


        .feature {
            background: var(--white);

            border: 1px solid var(--border);

            border-radius: 8px;

            padding: 25px;

            min-height: 150px;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease;
        }


        .feature:hover {
            border-color: #a7f3d0;

            box-shadow: var(--shadow);
        }


        .feature h3 {
            color: var(--isu-green);

            font-size: 17px;

            font-weight: 600;

            margin-bottom: 8px;
        }


        .feature p {
            color: var(--text-muted);

            font-size: 14px;

            line-height: 1.6;
        }


        /* =========================================================
           WORKFLOW
        ========================================================= */

        .workflow-section {
            background: var(--background);

            border-top: 1px solid #e5ebe7;
            border-bottom: 1px solid #e5ebe7;
        }


        .workflow {
            max-width: 1150px;

            margin: 0 auto;

            padding: 65px 25px;
        }


        .workflow-steps {
            display: grid;

            grid-template-columns: repeat(4, 1fr);

            gap: 15px;
        }


        .step {
            background: var(--white);

            border: 1px solid var(--border);

            border-radius: 8px;

            padding: 25px 15px;

            text-align: center;
        }


        .step-number {
            width: 38px;
            height: 38px;

            margin: 0 auto 12px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: var(--isu-accent);

            color: var(--white);

            border-radius: 50%;

            font-size: 14px;

            font-weight: 700;
        }


        .step h3 {
            color: var(--text-dark);

            font-size: 16px;

            margin-bottom: 5px;
        }


        .step p {
            color: var(--text-muted);

            font-size: 13px;

            line-height: 1.5;
        }


        /* =========================================================
           ABOUT
        ========================================================= */

        .about-content {
            max-width: 850px;

            margin: 0 auto;

            text-align: center;
        }


        .about-content p {
            color: var(--text-muted);

            font-size: 15px;

            line-height: 1.7;

            margin-bottom: 15px;
        }


        /* =========================================================
           LOGIN SECTION
        ========================================================= */

        .login-section {
            background: linear-gradient(135deg, var(--isu-green) 0%, var(--isu-green-hover) 100%);

            color: var(--white);

            text-align: center;

            padding: 55px 25px;
        }


        .login-section h2 {
            font-size: 28px;

            font-weight: 600;

            margin-bottom: 8px;
        }


        .login-section p {
            color: #dcece1;

            font-size: 14px;

            margin-bottom: 22px;
        }


        .login-section a {
            display: inline-block;

            background: var(--white);

            color: var(--isu-green);

            text-decoration: none;

            padding: 11px 25px;

            border-radius: 6px;

            font-size: 14px;

            font-weight: 600;

            transition:
                background 0.2s ease,
                transform 0.2s ease;
        }


        .login-section a:hover {
            background: #f8fafc;

            transform: translateY(-1px);
        }


        /* =========================================================
           FOOTER
        ========================================================= */

        footer {
            background: linear-gradient(180deg, var(--isu-green) 0%, #022c22 100%);

            color: var(--white);

            padding: 25px;
        }


        .footer-container {
            max-width: 1150px;

            margin: 0 auto;

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 25px;
        }


        .footer-container strong {
            color: var(--white);

            font-size: 14px;
        }


        .footer-container p {
            color: #b9cfc1;

            font-size: 12px;

            margin-top: 3px;
        }


        .footer-links {
            display: flex;

            gap: 18px;

            flex-wrap: wrap;
        }


        .footer-links a {
            color: #d5e3da;

            text-decoration: none;

            font-size: 12px;
        }


        .footer-links a:hover {
            color: var(--white);
        }


        .copyright {
            max-width: 1150px;

            margin: 20px auto 0;

            padding-top: 15px;

            border-top: 1px solid rgba(255,255,255,0.15);

            text-align: center;

            color: #9db5a5;

            font-size: 11px;
        }


        /* =========================================================
           RESPONSIVE - TABLET
        ========================================================= */

        @media (max-width: 850px) {

            .intro-container {
                grid-template-columns: 1fr;

                text-align: center;

                gap: 35px;
            }


            .intro-content {
                margin: 0 auto;
            }


            .intro-content p {
                margin-left: auto;
                margin-right: auto;
            }


            .feature-grid {
                grid-template-columns: repeat(2, 1fr);
            }


            .workflow-steps {
                grid-template-columns: repeat(2, 1fr);
            }


            .footer-container {
                flex-direction: column;

                text-align: center;
            }
        }


        /* =========================================================
           RESPONSIVE - MOBILE
        ========================================================= */

        @media (max-width: 600px) {

            .header-container {
                min-height: 68px;

                padding: 8px 15px;
            }


            .logo-area img {
                width: 48px;
                height: 48px;
            }


            .logo-text strong {
                font-size: 14px;
            }


            .logo-text span {
                display: none;
            }


            .navigation {
                gap: 8px;
            }


            .navigation a:not(.login-button) {
                display: none;
            }


            .login-button {
                padding: 8px 15px;
            }


            .intro-container {
                padding: 55px 20px;

                min-height: auto;
            }


            .intro-content h1 {
                font-size: 32px;
            }


            .intro-content h2 {
                font-size: 18px;
            }


            .intro-content p {
                font-size: 14px;
            }


            .intro-logo img {
                width: 200px;
                height: 200px;
            }


            .section,
            .workflow {
                padding: 50px 20px;
            }


            .section-title h2 {
                font-size: 24px;
            }


            .feature-grid,
            .workflow-steps {
                grid-template-columns: 1fr;
            }


            .feature {
                min-height: auto;
            }


            .footer-links {
                justify-content: center;
            }
        }

    </style>
</head>


<body>


<!-- =========================================================
     HEADER
========================================================= -->

<header>

    <div class="header-container">

        <a href="#home" class="logo-area">

            <img
                src="assets/images/logo.png"
                alt="ISU CBAO Merch Billing Logo"
            >

            <div class="logo-text">

                <strong>
                    ISU CBAO Merch Billing
                </strong>

                <span>
                    Inventory Management & Billing System
                </span>

            </div>

        </a>


        <nav class="navigation">

            <a href="#home">
                Home
            </a>

            <a href="#features">
                Features
            </a>

            <a href="#workflow">
                Workflow
            </a>

            <a href="#about">
                About
            </a>

            <a href="login.php" class="login-button">
                Login
            </a>

        </nav>

    </div>

</header>



<!-- =========================================================
     INTRODUCTION
========================================================= -->

<section class="intro" id="home">

    <div class="intro-container">

        <div class="intro-content">

            <h1>
                Inventory Management<br>
                & Billing System
            </h1>


            <h2>
                ISU CBAO Merch Billing
            </h2>


            <p>
                The Inventory Management & Billing System is a web
                application developed for the Isabela State University
                CBAO Merchandising Office. It helps organize product
                records, monitor inventory, manage suppliers, process
                sales and billing transactions, and generate reports.
            </p>


            <a
                href="login.php"
                class="intro-button"
            >
                Login to System
            </a>

        </div>


        <div class="intro-logo">

           <img
    src="assets/images/logo.png"
    alt="ISU CBAO Merch Billing Logo"
>

        </div>

    </div>

</section>



<!-- =========================================================
     FEATURES
========================================================= -->

<section class="features" id="features">

    <div class="section">

        <div class="section-title">

            <h2>
                System Features
            </h2>

            <p>
                Main functions available in the Inventory Management
                & Billing System.
            </p>

        </div>


        <div class="feature-grid">


            <!-- PRODUCT -->

            <div class="feature">

                <h3>
                    Product Management
                </h3>

                <p>
                    Add, view, update, and manage product information,
                    categories, prices, images, and QR codes.
                </p>

            </div>


            <!-- INVENTORY -->

            <div class="feature">

                <h3>
                    Inventory Management
                </h3>

                <p>
                    Monitor available stocks, inventory movement,
                    beginning inventory, running balance, and
                    reorder levels.
                </p>

            </div>


            <!-- SUPPLIER -->

            <div class="feature">

                <h3>
                    Supplier Management
                </h3>

                <p>
                    Maintain supplier information and keep track
                    of products received from suppliers.
                </p>

            </div>


            <!-- SALES -->

            <div class="feature">

                <h3>
                    Sales & Billing
                </h3>

                <p>
                    Record sales and billing transactions while
                    updating the related inventory records.
                </p>

            </div>


            <!-- QR -->

            <div class="feature">

                <h3>
                    QR Code Scanner
                </h3>

                <p>
                    Scan product QR codes to quickly access product
                    information and assist with transactions.
                </p>

            </div>


            <!-- REPORTS -->

            <div class="feature">

                <h3>
                    Reports
                </h3>

                <p>
                    View and generate reports related to inventory,
                    sales, transactions, and system activities.
                </p>

            </div>


        </div>

    </div>

</section>



<!-- =========================================================
     WORKFLOW
========================================================= -->

<section class="workflow-section" id="workflow">

    <div class="workflow">


        <div class="section-title">

            <h2>
                System Workflow
            </h2>

            <p>
                Products → Inventory → Sales/Billing → Reports
            </p>

        </div>


        <div class="workflow-steps">


            <!-- STEP 1 -->

            <div class="step">

                <div class="step-number">
                    1
                </div>

                <h3>
                    Products
                </h3>

                <p>
                    Product information is recorded and organized.
                </p>

            </div>


            <!-- STEP 2 -->

            <div class="step">

                <div class="step-number">
                    2
                </div>

                <h3>
                    Inventory
                </h3>

                <p>
                    Stock levels and inventory movements are monitored.
                </p>

            </div>


            <!-- STEP 3 -->

            <div class="step">

                <div class="step-number">
                    3
                </div>

                <h3>
                    Sales & Billing
                </h3>

                <p>
                    Transactions are recorded and inventory is updated.
                </p>

            </div>


            <!-- STEP 4 -->

            <div class="step">

                <div class="step-number">
                    4
                </div>

                <h3>
                    Reports
                </h3>

                <p>
                    Information is summarized through system reports.
                </p>

            </div>


        </div>

    </div>

</section>



<!-- =========================================================
     ABOUT
========================================================= -->

<section class="section" id="about">

    <div class="section-title">

        <h2>
            About the System
        </h2>

    </div>


    <div class="about-content">

        <p>
            The ISU CBAO Merch Billing System is intended to support
            the merchandising operations of Isabela State University
            Cauayan Campus.
        </p>


        <p>
            The system provides a centralized way of managing
            products, inventory, suppliers, sales, billing,
            QR-based product identification, and reports.
        </p>

    </div>

</section>



<!-- =========================================================
     LOGIN SECTION
========================================================= -->

<section class="login-section">

    <h2>
        Access the System
    </h2>


    <p>
        Login to continue to the Inventory Management & Billing System.
    </p>


    <a href="login.php">
        Login to System
    </a>

</section>



<!-- =========================================================
     FOOTER
========================================================= -->

<footer>

    <div class="footer-container">


        <div>

            <strong>
                ISU CBAO Merch Billing
            </strong>

            <p>
                Inventory Management & Billing System
            </p>

        </div>


        <div class="footer-links">

            <a href="#home">
                Home
            </a>

            <a href="#features">
                Features
            </a>

            <a href="#workflow">
                Workflow
            </a>

            <a href="#about">
                About
            </a>

            <a href="login.php">
                Login
            </a>

        </div>


    </div>


    <div class="copyright">

        © <?php echo date('Y'); ?>
        Isabela State University Cauayan Campus.
        All rights reserved.

    </div>

</footer>

<!-- PWA SERVICE WORKER -->
<script>
if ("serviceWorker" in navigator) {
    window.addEventListener("load", function () {
        navigator.serviceWorker
            .register("/service-worker.js")
            .then(function (registration) {
                console.log(
                    "ISU CBAO Merch Billing Service Worker registered:",
                    registration.scope
                );
            })
            .catch(function (error) {
                console.error(
                    "Service Worker registration failed:",
                    error
                );
            });
    });
}
</script>

</body>
</html>