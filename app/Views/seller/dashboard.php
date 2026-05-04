<?php 
ob_start();
$currentBalance = $wallet['balance'] ?? 0;
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h3 class="fw-bold mb-0">Bảng điều khiển Người bán</h3>
            <p class="text-muted small">Chào mừng, <?= e(Auth::user()['name']) ?>. Chúc bạn có một ngày bán hàng hiệu quả!</p>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="<?= url('/seller/products/create') ?>" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus me-2"></i> Thêm sản phẩm
            </a>
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
                            <i class="fas fa-dollar-sign fa-lg"></i>
                        </div>
                        <div class="text-end">
                            <span class="small opacity-75">Tổng doanh thu</span>
                            <h3 class="fw-bold mb-0"><?= money($stats['total_revenue']) ?></h3>
                        </div>
                    </div>
                    <div class="pt-2 border-top border-white border-opacity-10 d-flex justify-content-between align-items-center">
                        <span class="small opacity-75">Ví hiện tại:</span>
                        <span class="fw-bold"><?= money($currentBalance) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="stats-icon bg-success-subtle text-success rounded-circle p-3">
                            <i class="fas fa-shopping-bag fa-lg"></i>
                        </div>
                        <div class="text-end">
                            <span class="small text-muted">Đơn hàng đã bán</span>
                            <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats['total_orders']) ?></h3>
                        </div>
                    </div>
                    <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Lịch sử đơn:</span>
                        <a href="<?= url('/seller/orders') ?>" class="text-success text-decoration-none small fw-bold">Xem tất cả</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="stats-icon bg-warning-subtle text-warning rounded-circle p-3">
                            <i class="fas fa-box fa-lg"></i>
                        </div>
                        <div class="text-end">
                            <span class="small text-muted">Sản phẩm đang bán</span>
                            <h3 class="fw-bold mb-0 text-dark"><?= $stats['total_products'] ?></h3>
                        </div>
                    </div>
                    <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Quản lý kho:</span>
                        <a href="<?= url('/seller/products') ?>" class="text-warning text-decoration-none small fw-bold">Chi tiết</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="stats-icon bg-info-subtle text-info rounded-circle p-3">
                            <i class="fas fa-comment-dots fa-lg"></i>
                        </div>
                        <div class="text-end">
                            <span class="small text-muted">Hỗ trợ khách hàng</span>
                            <h3 class="fw-bold mb-0 text-dark">Tin nhắn</h3>
                        </div>
                    </div>
                    <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Phản hồi:</span>
                        <a href="<?= url('/seller/chat') ?>" class="text-info text-decoration-none small fw-bold">Mở Chat</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="row g-4 mb-4">
        <!-- Recent Orders -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-clock text-primary me-2"></i> Đơn hàng vừa bán</h5>
                    <a href="<?= url('/seller/orders') ?>" class="btn btn-sm btn-light rounded-pill px-3">Tất cả</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-uppercase small fw-bold text-muted">
                                    <th class="ps-4 py-3">Mã đơn</th>
                                    <th>Sản phẩm</th>
                                    <th>Người mua</th>
                                    <th>Số tiền</th>
                                    <th class="pe-4 text-end">Ngày mua</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentOrders)): ?>
                                    <tr><td colspan="5" class="text-center py-5 text-muted">Chưa có đơn hàng nào</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">#<?= e($order['order_code']) ?></td>
                                        <td>
                                            <div class="text-dark fw-semibold"><?= e(Helper::truncate($order['product_name'], 30)) ?></div>
                                        </td>
                                        <td><span class="text-muted small"><?= e($order['buyer_name']) ?></span></td>
                                        <td class="fw-bold text-success"><?= money($order['seller_amount']) ?></td>
                                        <td class="pe-4 text-end text-muted small"><?= date('H:i d/m', strtotime($order['created_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Selling -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-trophy text-warning me-2"></i> Sản phẩm bán chạy</h5>
                </div>
                <div class="card-body p-0">
                    <div class="px-3">
                        <?php if (empty($bestProducts)): ?>
                            <div class="text-center py-5 text-muted">Chưa có dữ liệu</div>
                        <?php else: ?>
                            <?php foreach ($bestProducts as $index => $product): ?>
                                <div class="d-flex align-items-center py-3 <?= $index < count($bestProducts) - 1 ? 'border-bottom' : '' ?>">
                                    <div class="stats-icon bg-light rounded-circle p-2 me-3" style="width: 40px; height: 40px;">
                                        <span class="fw-bold text-muted small"><?= $index + 1 ?></span>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="fw-bold text-truncate small"><?= e($product['name']) ?></div>
                                        <div class="text-muted" style="font-size: 11px;">Giá: <?= money($product['price']) ?></div>
                                    </div>
                                    <div class="text-end ms-2">
                                        <span class="badge bg-success-subtle text-success rounded-pill"><?= $product['total_sold'] ?> đã bán</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Telegram & Actions -->
    <div class="row g-4">
        <!-- Telegram Settings -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fab fa-telegram text-primary me-2"></i> Thông báo Telegram</h5>
                </div>
                <div class="card-body pt-0">
                    <p class="text-muted small mb-3">Nhận tin nhắn báo đơn hàng mới ngay lập tức qua Telegram.</p>
                    <form action="/seller/telegram/update" method="POST">
                        <?= csrf_field() ?>
                        <div class="input-group mb-3">
                            <input type="text" name="telegram_chat_id" class="form-control rounded-start-pill ps-4" value="<?= e(Auth::user()['telegram_chat_id'] ?? '') ?>" placeholder="Nhập Chat ID của bạn...">
                            <button type="submit" class="btn btn-primary rounded-end-pill px-4">Cập nhật</button>
                        </div>
                    </form>
                    <div class="bg-light rounded-3 p-3 mt-2">
                        <div class="small text-muted">
                            <strong>Lấy Chat ID:</strong> Chat với <code>@userinfobot</code> trên Telegram để lấy dãy số "Id" và dán vào ô trên. Sau đó hãy nhớ bấm /start với bot hệ thống.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Withdraw Action -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 bg-dark text-white">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="me-4 stats-icon bg-white bg-opacity-10 rounded-circle p-4">
                        <i class="fas fa-money-bill-transfer fa-2x"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">Rút tiền về tài khoản</h4>
                        <p class="text-white text-opacity-50 small mb-3">Rút lợi nhuận từ ví seller về ngân hàng của bạn.</p>
                        <a href="<?= url('/seller/withdrawals') ?>" class="btn btn-outline-light rounded-pill px-4">
                            Tạo lệnh rút tiền
                        </a>
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

.stats-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card { transition: all 0.2s; }
.card:hover { transform: translateY(-3px); }

.table thead th {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Seller Dashboard';
require_once __DIR__ . '/../layouts/seller.php';
?>
