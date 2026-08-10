<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earthbred - Sales Report Console</title>
    <meta name="description" content="Earthbred Coffee Studio Manager Sales Report Console. Track daily, weekly, and monthly revenue and export reports to PDF.">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/manager.css') ?>?v=<?= time() ?>">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- html2pdf -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body>
<div class="mgr-app">

    <!-- =========================================
         SIDEBAR
    ========================================= -->
    <aside class="mgr-sidebar">
        <div class="mgr-logo-section">
            <h1 class="mgr-logo-main">earthbred</h1>
            <p class="mgr-logo-sub">Coffee Studio</p>
        </div>

        <div class="mgr-user-profile">
            <i class="fa-solid fa-circle-user mgr-profile-icon"></i>
            <div class="mgr-user-info">
                <p class="mgr-user-name">Juan Reyes</p>
                <p class="mgr-user-id">MGR-001</p>
            </div>
        </div>

        <nav class="mgr-nav">
            <h3 class="mgr-nav-heading">NAVIGATION</h3>
            <ul class="mgr-nav-list">
                <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager'">
                    <i class="fa-solid fa-chart-line mgr-nav-icon"></i> Dashboard
                </li>
            </ul>

            <h3 class="mgr-nav-heading">OPERATIONS</h3>
            <ul class="mgr-nav-list">
                <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager/products'">
                    <i class="fa-solid fa-tags mgr-nav-icon"></i> Product Management
                </li>
                <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager/shift-notes'">
                    <i class="fa-solid fa-note-sticky mgr-nav-icon"></i> Shift Notes
                </li>
                <li class="mgr-nav-item active">
                    <i class="fa-solid fa-file-invoice-dollar mgr-nav-icon"></i> Sales Reports
                </li>
                <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager/inventory'">
                    <i class="fa-solid fa-boxes-stacked mgr-nav-icon"></i> Inventory
                </li>
            </ul>

            <h3 class="mgr-nav-heading">TOOLS</h3>
            <ul class="mgr-nav-list">
                <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager/ai'">
                    <i class="fa-solid fa-robot mgr-nav-icon"></i> AI Gemini Assistant
                </li>
                <li class="mgr-nav-item owner-only-link" onclick="window.location.href='<?= url('') ?>/manager/accounts'">
                    <i class="fa-solid fa-users mgr-nav-icon"></i> Account Management
                </li>
            </ul>
        </nav>

        <div class="mgr-sidebar-footer">
            <div class="mgr-clock-out" onclick="window.location.href='<?= url('') ?>/login'">
                <i class="fa-solid fa-power-off"></i> Clock Out
            </div>
        </div>
    </aside>
    <script>
        if (localStorage.getItem('userRole') !== 'owner') {
            document.querySelectorAll('.owner-only-link').forEach(el => el.style.display = 'none');
        }
        if (localStorage.getItem('userName')) {
            document.querySelector('.mgr-user-name').textContent = localStorage.getItem('userName');
        }
    </script>

    <!-- =========================================
         MAIN CONTENT AREA
    ========================================= -->
    <main class="mgr-main">
        <!-- Top header -->
        <header class="mgr-header" style="justify-content: space-between;">
            <h2 class="mgr-page-title">Manager Dashboard</h2>
            <button class="resolve-btn" id="exportSalesReportPdfBtn" style="background-color: var(--brand-primary); font-family: 'Montserrat', sans-serif; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-file-pdf"></i> Export PDF
            </button>
        </header>

        <!-- Inner Content Scroll Area -->
        <div class="mgr-content">
            <!-- Greeting / Title Section -->
            <div class="mgr-greeting-wrap">
                <h3 class="mgr-greeting-title">Sales Report</h3>
                <p class="mgr-greeting-sub" id="salesReportPeriodLabel">Here's your overview for today.</p>
            </div>

            <!-- Filter Buttons -->
            <div class="filter-buttons-container" style="margin-bottom: 1rem;">
                <button class="filter-btn active" data-range="daily">Daily</button>
                <button class="filter-btn" data-range="weekly">Weekly</button>
                <button class="filter-btn" data-range="monthly">Monthly</button>
            </div>

            <!-- KPI Row (3 Cards) -->
            <section class="mgr-kpi-row">
                <!-- Sales -->
                <div class="mgr-kpi-card">
                    <p class="mgr-kpi-label" id="kpiSalesLabel">Today's Sales</p>
                    <p class="mgr-kpi-value mgr-kpi-val" id="kpiSalesVal">₱0</p>
                    <p class="mgr-kpi-trend" id="kpiSalesSubtext" style="color: var(--text-muted);">Total Revenue</p>
                </div>
                <!-- Orders -->
                <div class="mgr-kpi-card">
                    <p class="mgr-kpi-label" id="kpiOrdersLabel">Orders Today</p>
                    <p class="mgr-kpi-value mgr-kpi-val" id="kpiOrdersVal">0</p>
                    <p class="mgr-kpi-trend" id="kpiOrdersSubtext" style="color: var(--text-muted);">Total Orders</p>
                </div>
                <!-- Average Order Value -->
                <div class="mgr-kpi-card">
                    <p class="mgr-kpi-label" id="kpiAOVLabel">Avg Order Value</p>
                    <p class="mgr-kpi-value mgr-kpi-val" id="kpiAOVVal">₱0</p>
                    <p class="mgr-kpi-trend" id="kpiAOVSubtext" style="color: var(--text-muted);">Average basket size</p>
                </div>
            </section>

            <!-- Revenue Chart Card -->
            <div class="mgr-panel" style="margin-top: 1rem;">
                <h4 class="mgr-panel-title">Revenue Chart</h4>
                <div class="mgr-chart-container" style="height: 280px; position: relative;">
                    <canvas id="salesReportChart"></canvas>
                </div>
            </div>

            <!-- Top Selling Items Card -->
            <section class="mgr-bottom-panel" style="margin-top: 1rem; margin-bottom: 2rem;">
                <h4 class="mgr-panel-title" id="topItemsSectionTitle">Top Selling Items Today</h4>
                <div class="mgr-table-container">
                    <table class="mgr-table">
                        <thead>
                            <tr>
                                <th>ITEM</th>
                                <th>CATEGORY</th>
                                <th>QTY SOLD</th>
                                <th>REVENUE</th>
                                <th style="width: 220px;">SHARE</th>
                            </tr>
                        </thead>
                        <tbody id="topItemsTableBody">
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 2rem; color: #5c4a40;">
                                    <i class="fa-solid fa-spinner fa-spin"></i> Loading top selling items...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

</div>

<!-- =========================================
     PDF REPORT TEMPLATE (Hidden from UI)
========================================= -->
<div id="pdfSalesReportTemplate" style="display: none;">
    <div id="pdfSalesContent" class="pdf-report" style="font-family: 'Poppins', sans-serif; padding: 40px; background-color: #ffffff; color: #2c1a14; border: 1.5px solid #eadeca; border-radius: 14px;">
        <!-- Brand Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eadeca; padding-bottom: 20px; margin-bottom: 20px;">
            <div>
                <h1 style="font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 2.2rem; color: #2c1a14; margin: 0; line-height: 1;">earthbred</h1>
                <p style="font-family: 'Montserrat', sans-serif; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 4px; color: #8d786c; margin: 5px 0 0 0;">Coffee Studio</p>
            </div>
            <div style="text-align: right;">
                <h2 style="font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 1.25rem; color: #2c1a14; margin: 0; text-transform: uppercase; letter-spacing: 1px;">SALES REPORT</h2>
                <p id="pdfReportPeriod" style="font-size: 0.82rem; color: #8d786c; font-weight: 700; margin: 5px 0 0 0; text-transform: uppercase;"></p>
                <p id="pdfGeneratedTime" style="font-size: 0.75rem; color: #8d786c; margin: 2px 0 0 0;"></p>
            </div>
        </div>

        <!-- KPI Row -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;">
            <div style="background-color: #faf5eb; border: 1.5px solid #eadeca; border-radius: 12px; padding: 15px; text-align: center;">
                <p style="font-size: 0.78rem; font-weight: 700; color: #8d786c; text-transform: uppercase; margin: 0 0 5px 0;" id="pdfKpiSalesLabel">Revenue</p>
                <p style="font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 1.6rem; color: #2c1a14; margin: 0;" id="pdfKpiSalesVal">₱0</p>
            </div>
            <div style="background-color: #faf5eb; border: 1.5px solid #eadeca; border-radius: 12px; padding: 15px; text-align: center;">
                <p style="font-size: 0.78rem; font-weight: 700; color: #8d786c; text-transform: uppercase; margin: 0 0 5px 0;" id="pdfKpiOrdersLabel">Orders Today</p>
                <p style="font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 1.6rem; color: #2c1a14; margin: 0;" id="pdfKpiOrdersVal">0</p>
            </div>
            <div style="background-color: #faf5eb; border: 1.5px solid #eadeca; border-radius: 12px; padding: 15px; text-align: center;">
                <p style="font-size: 0.78rem; font-weight: 700; color: #8d786c; text-transform: uppercase; margin: 0 0 5px 0;">Avg Order Value</p>
                <p style="font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 1.6rem; color: #2c1a14; margin: 0;" id="pdfKpiAOVVal">₱0</p>
            </div>
        </div>

        <!-- Revenue Chart Section -->
        <div style="border: 1.5px solid #eadeca; border-radius: 12px; padding: 20px; margin-bottom: 30px; text-align: center;">
            <h3 style="font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 0.85rem; color: #8d786c; text-transform: uppercase; margin: 0 0 15px 0; text-align: left; letter-spacing: 0.8px;">Revenue Graph</h3>
            <img id="pdfSalesChartImg" style="width: 100%; max-height: 240px; object-fit: contain; margin: 0 auto; display: block;" alt="Revenue Chart">
        </div>

        <!-- Top Selling Items Section -->
        <div style="border: 1.5px solid #eadeca; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
            <h3 id="pdfTableHeading" style="font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 0.85rem; color: #8d786c; text-transform: uppercase; margin: 0 0 15px 0; letter-spacing: 0.8px;">Top Selling Items</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #eadeca;">
                        <th style="text-align: left; padding: 8px 10px; color: #8d786c; font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 0.72rem; text-transform: uppercase;">Rank</th>
                        <th style="text-align: left; padding: 8px 10px; color: #8d786c; font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 0.72rem; text-transform: uppercase;">Item Name</th>
                        <th style="text-align: left; padding: 8px 10px; color: #8d786c; font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 0.72rem; text-transform: uppercase;">Category</th>
                        <th style="text-align: right; padding: 8px 10px; color: #8d786c; font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 0.72rem; text-transform: uppercase;">Qty Sold</th>
                        <th style="text-align: right; padding: 8px 10px; color: #8d786c; font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 0.72rem; text-transform: uppercase;">Revenue</th>
                        <th style="text-align: right; padding: 8px 10px; color: #8d786c; font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 0.72rem; text-transform: uppercase;">Share</th>
                    </tr>
                </thead>
                <tbody id="pdfTopItemsBody">
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div style="border-top: 2px solid #eadeca; padding-top: 15px; margin-top: 30px; display: flex; justify-content: space-between; align-items: center; font-size: 0.7rem; color: #8d786c;">
            <p style="margin: 0;">Generated by Earthbred POS System &bull; <span id="pdfFooterTimestamp"></span></p>
            <p style="margin: 0; font-weight: 600; text-transform: uppercase;">Internal Use Only</p>
        </div>
    </div>
</div>

<script src="<?= asset('js/sales-report.js') ?>?v=<?= time() ?>"></script>
<script src="<?= asset('js/sidebar-toggle.js') ?>?v=<?= time() ?>"></script>
</body>
</html>

