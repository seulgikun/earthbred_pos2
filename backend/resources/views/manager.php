<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earthbred - Manager Dashboard</title>
    <meta name="description" content="Earthbred Coffee Studio Manager Administration Console. Monitor daily sales, unresolved alerts, inventory logs, and performance metrics.">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/manager.css') ?>?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
                <li class="mgr-nav-item active">
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
                <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager/sales-report'">
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
        <header class="mgr-header">
            <h2 class="mgr-page-title">Manager Dashboard</h2>
        </header>

        <!-- Inner Content Scroll Area -->
        <div class="mgr-content">
            <!-- Greeting -->
            <div class="mgr-greeting-wrap">
                <h3 class="mgr-greeting-title">Dashboard</h3>
                <p class="mgr-greeting-sub">Here's your overview for today.</p>
            </div>

            <!-- KPI Row (3 Cards) -->
            <section class="mgr-kpi-row">
                <!-- Today's Sales -->
                <div class="mgr-kpi-card">
                    <button class="mgr-kpi-arrow-btn"><i class="fa-solid fa-arrow-up-right-from-square"></i></button>
                    <p class="mgr-kpi-label">Today's Sales</p>
                    <p class="mgr-kpi-value mgr-kpi-val" id="kpiTodaySales">₱0</p>
                    <p class="mgr-kpi-trend" id="kpiTodaySalesTrend">--</p>
                </div>
                <!-- Orders Today -->
                <div class="mgr-kpi-card">
                    <button class="mgr-kpi-arrow-btn"><i class="fa-solid fa-arrow-up-right-from-square"></i></button>
                    <p class="mgr-kpi-label">Orders Today</p>
                    <p class="mgr-kpi-value mgr-kpi-val" id="kpiTodayOrders">0</p>
                    <p class="mgr-kpi-trend" id="kpiTodayOrdersTrend">--</p>
                </div>
                <!-- Average Order Value -->
                <div class="mgr-kpi-card">
                    <button class="mgr-kpi-arrow-btn"><i class="fa-solid fa-arrow-up-right-from-square"></i></button>
                    <p class="mgr-kpi-label">Avg Order Value</p>
                    <p class="mgr-kpi-value mgr-kpi-val" id="kpiAOV">₱0</p>
                    <p class="mgr-kpi-trend" id="kpiAOVTrend">--</p>
                </div>
            </section>

            <!-- Middle Grid: Weekly Sales Bar Chart + Alerts/Notifications -->
            <div class="mgr-mid-grid">
                <!-- Weekly Sales Card -->
                <div class="mgr-panel">
                    <h4 class="mgr-panel-title">Weekly Sales (Last 7 Days)</h4>
                    <div class="mgr-chart-container">
                        <canvas id="mgrSalesChart"></canvas>
                    </div>
                </div>

                <!-- Alert & Notifications Card -->
                <div class="mgr-panel">
                    <h4 class="mgr-panel-title">Alert &amp; Notifications</h4>
                    <div class="mgr-alerts-list" id="mgrAlertsList">
                        <div class="mgr-alert-banner mgr-alert-banner-warn">
                            <i class="fa-solid fa-spinner fa-spin"></i> Loading alerts...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Panel: Top Selling Items -->
            <section class="mgr-bottom-panel">
                <h4 class="mgr-panel-title" id="mgrTopItemsTitle">Top Selling Items Today</h4>
                <div class="mgr-table-container">
                    <table class="mgr-table">
                        <thead>
                            <tr>
                                <th>ITEM</th>
                                <th>CATEGORY</th>
                                <th>QTY SOLD</th>
                                <th>REVENUE</th>
                                <th>SHARE OF TOP SALES</th>
                            </tr>
                        </thead>
                        <tbody id="mgrTopItemsBody">
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
<script src="<?= asset('js/manager.js') ?>?v=<?= time() ?>"></script>
<script src="<?= asset('js/sidebar-toggle.js') ?>?v=<?= time() ?>"></script>
</body>
</html>

