<?php
// ============================================================
// VIEW — app/Views/dashboard/index.php
// ============================================================
/**
 * Biến nhận từ DashboardController::index():
 * @var array  $summary     ['income', 'expense', 'balance']
 * @var string $chartJson   JSON string cho Chart.js (donut + bar)
 * @var int    $month       Tháng hiện tại
 * @var int    $year        Năm hiện tại
 * @var string $pageTitle   Tiêu đề trang
 * @var string $extraCss    URL file CSS
 * @var bool   $needChartJs Cờ load thư viện Chart.js
 */
// ============================================================
$pageTitle = 'Dashboard';
$extraCss  = BASE_URL . '/css/dashboard.css';
require BASE_PATH . '/app/Views/partials/layout.php';
?>

<!-- Stat cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-4">
        <div class="card stat-card income-card">
            <div class="stat-label">
                <i class="bi bi-arrow-down-circle text-success"></i>Tổng thu
            </div>
            <div class="stat-value text-success">
                +<?= number_format($summary['income'], 0, ',', '.') ?>đ
            </div>
            <div class="stat-period">Tháng <?= $month ?>/<?= $year ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card stat-card expense-card">
            <div class="stat-label">
                <i class="bi bi-arrow-up-circle text-danger"></i>Tổng chi
            </div>
            <div class="stat-value text-danger">
                -<?= number_format($summary['expense'], 0, ',', '.') ?>đ
            </div>
            <div class="stat-period">Tháng <?= $month ?>/<?= $year ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <?php $bal = $summary['balance']; $balPositive = $bal >= 0; ?>
        <div class="card stat-card <?= $balPositive ? 'balance-card-positive' : 'balance-card-negative' ?>">
            <div class="stat-label">
                <i class="bi bi-wallet2"></i>Số dư
            </div>
            <div class="stat-value <?= $balPositive ? 'text-primary' : 'text-danger' ?>">
                <?= ($balPositive ? '+' : '') . number_format($bal, 0, ',', '.') ?>đ
            </div>
        </div>
    </div>
</div>


<!-- Biểu đồ -->
<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="chart-card h-100">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Thu & Chi gần đây</div>
                    <div class="chart-subtitle">4 tuần qua</div>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="barChart" style="max-height:280px;width:100%"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="chart-card h-100">
            <div class="chart-header">
                <div class="chart-title">Chi theo danh mục</div>
            </div>
            <div class="chart-body d-flex flex-column align-items-center">
                <canvas id="donutChart" style="max-height:240px;max-width:240px"></canvas>
                <div id="donutLegend" class="mt-3 w-100"></div>
            </div>
        </div>
    </div>
</div>

<!-- Nút chức năng nhanh -->
<div class="dashboard-actions">
    <a href="<?= BASE_URL ?>/transactions/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Thêm giao dịch
    </a>
    <a href="<?= BASE_URL ?>/report" class="btn btn-light border">
        <i class="bi bi-bar-chart me-1"></i>Xem báo cáo chi tiết
    </a>
    <a href="<?= BASE_URL ?>/report/export_csv" class="btn btn-light border">
        <i class="bi bi-download me-1"></i>Xuất CSV tháng này
    </a>
</div>

<?php ob_start(); ?>
<script>
(function () {
    // Đọc data từ PHP — json_encode đã encode đúng UTF-8
    const chartData = <?= $chartJson ?>;

    // ── Biểu đồ cột ──────────────────────────────────────────
    const barCtx = document.getElementById('barChart');
    if (barCtx) {
        new Chart(barCtx, {
            type: 'bar',
            data: chartData.bar,
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label + ': '
                                + new Intl.NumberFormat('vi-VN').format(ctx.raw) + 'đ'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: val =>
                                new Intl.NumberFormat('vi-VN',
                                    {notation:'compact'}).format(val) + 'đ'
                        }
                    }
                }
            }
        });
    }

    // ── Biểu đồ tròn ─────────────────────────────────────────
    const donutCtx = document.getElementById('donutChart');
    if (donutCtx) {
        const donutData = chartData.donut;

        if (!donutData.labels || donutData.labels.length === 0) {
            donutCtx.closest('.card-body').innerHTML =
                '<p class="text-muted text-center my-4">Chưa có dữ liệu chi tiêu tháng này.</p>';
        } else {
            new Chart(donutCtx, {
                type: 'doughnut',
                data: donutData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.label + ': '
                                    + new Intl.NumberFormat('vi-VN').format(ctx.raw) + 'đ'
                            }
                        }
                    }
                }
            });

            // Legend thủ công — hiện tên + số tiền
            const legend = document.getElementById('donutLegend');
            if (legend && donutData.labels) {
                legend.innerHTML = donutData.labels.map((lbl, i) => {
                    const color = donutData.datasets[0].backgroundColor[i];
                    const val   = new Intl.NumberFormat('vi-VN')
                                      .format(donutData.datasets[0].data[i]);
                    return `<div class="d-flex align-items-center gap-2 mb-1">
                        <span style="width:12px;height:12px;border-radius:50%;
                              background:${color};flex-shrink:0;display:inline-block"></span>
                        <span class="small">${lbl}: <strong>${val}đ</strong></span>
                    </div>`;
                }).join('');
            }
        }
    }
})();
</script>
<?php 
$extraJs = ob_get_clean(); 
require BASE_PATH . '/app/Views/partials/footer.php'; 
?>
