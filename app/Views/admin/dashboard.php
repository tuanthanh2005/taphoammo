<?php ob_start(); ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h3 class="fw-bold mb-0">Trung tâm Điều hành Hệ thống</h3>
            <p class="text-muted small">Chào mừng trở lại, Admin. Đây là tổng quan hoạt động sàn hôm nay.</p>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                <i class="far fa-calendar-alt me-1"></i> <?= date('d/m/Y') ?>
            </div>
            <div class="badge bg-success-subtle text-success px-3 py-2 rounded-pill ms-2">
                <i class="fas fa-signal me-1"></i> Hệ thống: Ổn định
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-4 mb-4">
        <!-- Revenue Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="stats-icon bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="fas fa-wallet fa-lg"></i>
                        </div>
                        <div class="text-end">
                            <span class="small opacity-75">Tổng lợi nhuận Admin</span>
                            <h3 class="fw-bold mb-0"><?= money($stats['admin_revenue']) ?></h3>
                        </div>
                    </div>
                    <div class="pt-2 border-top border-white border-opacity-10 d-flex justify-content-between align-items-center">
                        <span class="small opacity-75">Hôm nay:</span>
                        <span class="fw-bold">+<?= money($stats['revenue_today']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="stats-icon bg-warning-subtle text-warning rounded-circle p-3">
                            <i class="fas fa-shopping-bag fa-lg"></i>
                        </div>
                        <div class="text-end">
                            <span class="small text-muted">Tổng đơn hàng</span>
                            <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats['total_orders']) ?></h3>
                        </div>
                    </div>
                    <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Doanh thu sàn:</span>
                        <span class="fw-bold text-dark"><?= money($stats['total_revenue']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="stats-icon bg-info-subtle text-info rounded-circle p-3">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                        <div class="text-end">
                            <span class="small text-muted">Người dùng / Seller</span>
                            <h3 class="fw-bold mb-0 text-dark"><?= $stats['total_users'] ?> / <?= $stats['total_sellers'] ?></h3>
                        </div>
                    </div>
                    <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Tổng sản phẩm:</span>
                        <span class="fw-bold text-dark"><?= number_format($stats['total_products']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="stats-icon bg-danger-subtle text-danger rounded-circle p-3">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                        <div class="text-end">
                            <span class="small text-muted">Cần xử lý gấp</span>
                            <h3 class="fw-bold mb-0 text-danger"><?= $stats['pending_withdrawals'] + $stats['pending_deposits'] + $stats['open_disputes'] ?></h3>
                        </div>
                    </div>
                    <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Khiếu nại đang mở:</span>
                        <span class="badge bg-danger rounded-pill"><?= $stats['open_disputes'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4">
        <!-- Left Column: Tasks & Monitoring -->
        <div class="col-lg-8">
            <!-- Pending Withdrawals -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-money-bill-wave text-primary me-2"></i> Yêu cầu rút tiền chờ duyệt</h5>
                    <a href="<?= url('/admin/withdrawals') ?>" class="btn btn-sm btn-light rounded-pill px-3">Tất cả</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="small fw-bold text-muted">
                                    <th class="ps-4">Người dùng</th>
                                    <th>Số tiền</th>
                                    <th>Phương thức</th>
                                    <th class="text-end pe-4">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pendingWithdrawals)): ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted">Không có yêu cầu chờ duyệt</td></tr>
                                <?php else: ?>
                                    <?php foreach ($pendingWithdrawals as $w): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold"><?= e($w['user_name']) ?></div>
                                                <div class="text-muted small"><?= e($w['email']) ?></div>
                                            </td>
                                            <td class="fw-bold text-primary"><?= money($w['amount']) ?></td>
                                            <td><span class="badge bg-light text-dark border"><?= e($w['method']) ?></span></td>
                                            <td class="text-end pe-4">
                                                <a href="<?= url('/admin/withdrawals') ?>" class="btn btn-sm btn-primary rounded-pill px-3">Duyệt ngay</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-shopping-cart text-success me-2"></i> Đơn hàng mới nhất</h5>
                    <a href="<?= url('/admin/orders') ?>" class="btn btn-sm btn-light rounded-pill px-3">Tất cả</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="small fw-bold text-muted">
                                    <th class="ps-4">Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end pe-4">Thời gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $o): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">#<?= $o['order_code'] ?></td>
                                        <td><?= e($o['user_name']) ?></td>
                                        <td class="fw-bold"><?= money($o['total_amount']) ?></td>
                                        <td>
                                            <span class="badge rounded-pill bg-<?= $o['payment_status'] === 'paid' ? 'success' : 'warning' ?>">
                                                <?= $o['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chờ' ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4 text-muted small"><?= date('H:i d/m', strtotime($o['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Top Performance & Disputes -->
        <div class="col-lg-4">
            <!-- Active Disputes -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold text-danger"><i class="fas fa-exclamation-triangle me-2"></i> Khiếu nại nóng</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (empty($recentDisputes)): ?>
                            <li class="list-group-item text-center py-4 text-muted">Sàn đang yên bình</li>
                        <?php else: ?>
                            <?php foreach ($recentDisputes as $d): ?>
                                <li class="list-group-item p-3 border-0 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="fw-bold small"><?= e($d['buyer_name']) ?></span>
                                        <span class="badge bg-danger-subtle text-danger rounded-pill" style="font-size: 10px;"><?= $d['status'] ?></span>
                                    </div>
                                    <div class="text-muted small mb-2"><?= e($d['product_name'] ?: 'Toàn bộ đơn hàng #' . $d['order_id']) ?></div>
                                    <a href="<?= url('/admin/disputes') ?>" class="btn btn-sm btn-outline-danger w-100 rounded-pill" style="font-size: 11px;">Xử lý ngay</a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Top Sellers -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-trophy text-warning me-2"></i> Top Seller Doanh Thu</h5>
                </div>
                <div class="card-body p-0">
                    <div class="px-3">
                        <?php foreach ($topSellers as $index => $s): ?>
                            <div class="d-flex align-items-center py-3 <?= $index < 4 ? 'border-bottom' : '' ?>">
                                <div class="rank-circle me-3 bg-light rounded-circle d-flex align-items-center justify-content-center fw-bold text-muted" style="width: 32px; height: 32px; flex-shrink: 0;">
                                    <?= $index + 1 ?>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="fw-bold text-truncate"><?= e($s['name']) ?></div>
                                    <div class="text-muted small text-truncate"><?= e($s['email']) ?></div>
                                </div>
                                <div class="text-end ms-2">
                                    <div class="fw-bold text-success small"><?= money($s['total_revenue']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1); }
.bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
.bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1); }
.bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1); }
.bg-info-subtle { background-color: rgba(13, 202, 240, 0.1); }

.card { transition: transform 0.2s; }
.card:hover { transform: translateY(-2px); }

.stats-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.table thead th {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Điều hành hệ thống';
require_once __DIR__ . '/../layouts/admin.php';
?>
