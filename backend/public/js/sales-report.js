/* ============================================================
   sales-report.js — Earthbred Sales Report Console JS
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

    const BASE = (function() {
        const pathname = window.location.pathname;
        const idx = pathname.toLowerCase().indexOf('/backend/public');
        return idx !== -1 ? pathname.substring(0, idx + '/backend/public'.length) : '';
    })();
    let salesData = null;
    let salesChartInstance = null;
    let activeRange = 'daily';

    // Format helpers
    const formatCurrency = (val) => '₱' + parseFloat(val).toLocaleString('en-PH', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });

    // =========================================================
    // INITIALIZE CHART
    // =========================================================
    function renderChart(labels, values) {
        const ctx = document.getElementById('salesReportChart');
        if (!ctx) return;

        if (salesChartInstance) {
            salesChartInstance.destroy();
        }

        // Color palette matching mockup
        const barColor = '#c59958'; // Warm gold/brown bar
        const hoverColor = '#b68a52';

        salesChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue',
                    data: values,
                    backgroundColor: barColor,
                    hoverBackgroundColor: hoverColor,
                    borderRadius: 6,
                    borderWidth: 0,
                    barPercentage: 0.55
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // Hide legend as shown in mockup
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => ` ${context.dataset.label}: ${formatCurrency(context.parsed.y)}`
                        },
                        titleFont: { family: 'Poppins', size: 12 },
                        bodyFont: { family: 'Poppins', size: 12 }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#8d786c',
                            font: { family: 'Montserrat', size: 11, weight: '700' }
                        }
                    },
                    y: {
                        grid: {
                            color: '#f5edd6',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#8d786c',
                            font: { family: 'Montserrat', size: 10, weight: '600' },
                            callback: function(value) {
                                if (value >= 1000) {
                                    return '₱' + (value / 1000) + 'k';
                                }
                                return '₱' + value;
                            }
                        }
                    }
                }
            }
        });
    }

    // =========================================================
    // RENDER TOP SELLING ITEMS TABLE
    // =========================================================
    function renderTopItemsTable(items) {
        const tbody = document.getElementById('topItemsTableBody');
        tbody.innerHTML = '';

        if (!items || items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem; color: #8d786c;">No sales recorded for this period.</td></tr>';
            return;
        }

        items.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><span class="mgr-item-name">${item.product_name}</span></td>
                <td><span class="mgr-category-tag">${item.category}</span></td>
                <td><span class="mgr-qty-sold">${item.qty_sold}</span></td>
                <td><span class="mgr-revenue">${formatCurrency(item.revenue)}</span></td>
                <td>
                    <div class="mgr-share-bar-wrap">
                        <div class="mgr-share-bar-bg">
                            <div class="mgr-share-bar-fill" style="width: ${item.share_percent}%; background-color: #c59958;"></div>
                        </div>
                        <span class="mgr-share-percent">${item.share_percent}%</span>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // =========================================================
    // RENDER REPORT CONTENT BASED ON ACTIVE RANGE
    // =========================================================
    function applyRange(range) {
        activeRange = range;
        if (!salesData || !salesData[range]) return;

        const data = salesData[range];

        // Update greeting subtext
        const periodLabel = document.getElementById('salesReportPeriodLabel');
        if (range === 'daily') {
            periodLabel.textContent = "Here's your overview for today.";
            document.getElementById('kpiSalesLabel').textContent = "Today's Sales";
            document.getElementById('kpiOrdersLabel').textContent = "Orders Today";
            document.getElementById('topItemsSectionTitle').textContent = "Top Selling Items Today";
        } else if (range === 'weekly') {
            periodLabel.textContent = "Here's your weekly overview (Last 7 Days).";
            document.getElementById('kpiSalesLabel').textContent = "Weekly Sales";
            document.getElementById('kpiOrdersLabel').textContent = "Orders This Week";
            document.getElementById('topItemsSectionTitle').textContent = "Top Selling Items This Week";
        } else if (range === 'monthly') {
            periodLabel.textContent = "Here's your monthly overview (Last 30 Days).";
            document.getElementById('kpiSalesLabel').textContent = "Monthly Sales";
            document.getElementById('kpiOrdersLabel').textContent = "Orders This Month";
            document.getElementById('topItemsSectionTitle').textContent = "Top Selling Items This Month";
        }

        // Update KPIs
        document.getElementById('kpiSalesVal').textContent = formatCurrency(data.revenue);
        document.getElementById('kpiOrdersVal').textContent = data.orders_count;
        document.getElementById('kpiAOVVal').textContent = formatCurrency(data.aov);

        // Update Chart
        renderChart(data.chart.labels, data.chart.values);

        // Update Top Items
        renderTopItemsTable(data.top_items);
    }

    // =========================================================
    // FETCH SALES DATA
    // =========================================================
    function fetchSalesData() {
        fetch(`${BASE}/api/manager/sales-data`)
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    salesData = res;
                    applyRange('daily');
                } else {
                    console.error('Failed to load sales data');
                }
            })
            .catch(err => {
                console.error('Error fetching sales data:', err);
            });
    }

    fetchSalesData();

    // =========================================================
    // TAB NAVIGATION
    // =========================================================
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const range = this.getAttribute('data-range');
            applyRange(range);
        });
    });

    // =========================================================
    // EXPORT PDF
    // =========================================================
    const exportBtn = document.getElementById('exportSalesReportPdfBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', () => {
            if (!salesData || !salesData[activeRange]) {
                alert('No sales report data available to export.');
                return;
            }

            const data = salesData[activeRange];
            const now = new Date();
            const dateStr = now.toLocaleDateString('en-PH', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });
            const timeStr = now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });

            // 1. Set text headers in template
            document.getElementById('pdfReportPeriod').textContent = `${activeRange} Report`;
            document.getElementById('pdfGeneratedTime').textContent = `Generated: ${dateStr} at ${timeStr}`;
            document.getElementById('pdfFooterTimestamp').textContent = `${dateStr} &bull; ${timeStr}`;

            // 2. Set KPI labels and values in template
            if (activeRange === 'daily') {
                document.getElementById('pdfKpiSalesLabel').textContent = "Today's Sales";
                document.getElementById('pdfKpiOrdersLabel').textContent = "Orders Today";
                document.getElementById('pdfTableHeading').textContent = "Top Selling Items Today";
            } else if (activeRange === 'weekly') {
                document.getElementById('pdfKpiSalesLabel').textContent = "Weekly Sales";
                document.getElementById('pdfKpiOrdersLabel').textContent = "Orders This Week";
                document.getElementById('pdfTableHeading').textContent = "Top Selling Items This Week";
            } else if (activeRange === 'monthly') {
                document.getElementById('pdfKpiSalesLabel').textContent = "Monthly Sales";
                document.getElementById('pdfKpiOrdersLabel').textContent = "Orders This Month";
                document.getElementById('pdfTableHeading').textContent = "Top Selling Items This Month";
            }

            document.getElementById('pdfKpiSalesVal').textContent = formatCurrency(data.revenue);
            document.getElementById('pdfKpiOrdersVal').textContent = data.orders_count;
            document.getElementById('pdfKpiAOVVal').textContent = formatCurrency(data.aov);

            // 3. Convert Chart to Image and place it in PDF template
            const chartCanvas = document.getElementById('salesReportChart');
            const pdfChartImg = document.getElementById('pdfSalesChartImg');
            if (chartCanvas && pdfChartImg) {
                pdfChartImg.src = chartCanvas.toDataURL('image/png');
            }

            // 4. Fill Top Items Table in PDF template
            const pdfTbody = document.getElementById('pdfTopItemsBody');
            pdfTbody.innerHTML = '';

            if (data.top_items && data.top_items.length > 0) {
                data.top_items.forEach((item, index) => {
                    const tr = document.createElement('tr');
                    tr.style.borderBottom = '1px solid #f5edd6';
                    tr.innerHTML = `
                        <td style="padding: 10px; font-weight: 700;">#${index + 1}</td>
                        <td style="padding: 10px; font-weight: 600;">${item.product_name}</td>
                        <td style="padding: 10px;">${item.category}</td>
                        <td style="padding: 10px; text-align: right;">${item.qty_sold}</td>
                        <td style="padding: 10px; text-align: right; font-weight: 600;">${formatCurrency(item.revenue)}</td>
                        <td style="padding: 10px; text-align: right; font-weight: 700; color: #8d786c;">${item.share_percent}%</td>
                    `;
                    pdfTbody.appendChild(tr);
                });
            } else {
                pdfTbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px; color: #8d786c;">No sales recorded for this period.</td></tr>';
            }

            // 5. Trigger PDF download
            const pdfTemplate = document.getElementById('pdfSalesReportTemplate');
            pdfTemplate.style.display = 'block';

            const filename = `earthbred_sales_report_${activeRange}_${now.toISOString().slice(0, 10)}.pdf`;

            const opt = {
                margin:       [0.4, 0.4, 0.4, 0.4],
                filename:     filename,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };

            html2pdf()
                .set(opt)
                .from(document.getElementById('pdfSalesContent'))
                .save()
                .then(() => {
                    pdfTemplate.style.display = 'none';
                })
                .catch(err => {
                    console.error('PDF export error:', err);
                    pdfTemplate.style.display = 'none';
                    alert('Error exporting sales report PDF.');
                });
        });
    }

});
