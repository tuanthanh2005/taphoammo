<?php 
ob_start();
$isBalanceLow = false; // Không còn bắt buộc minimum balance
$currentBalance = $wallet['balance'] ?? 0;
?>

<?php if ($isBalanceLow): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Ví chưa có tiền!</h4>
    <hr>
    <p class="mb-2">Bạn cần nạp tiền vào ví để có thể nhập stock và bắt đầu kinh doanh.</p>
    <p class="mb-0 small"><i class="fas fa-info-circle"></i> Khi nhập stock, hệ thống sẽ trừ <strong>100% giá trị stock</strong> làm tiền cọc. Tiền sẽ được hoàn lại khi khách mua hàng.</p>
    <ul class="mt-2 mb-3 small">
        <li>Tạo sản phẩm mới</li>
        <li>Nhập stock/kho hàng</li>
        <li>Bắt đầu kinh doanh</li>
    </ul>
    <a href="<?= url('/seller/wallet') ?>" class="btn btn-warning btn-lg">
        <i class="fas fa-wallet"></i> Nạp tiền ngay
    </a>
    <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="alert">Đóng</button>
</div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-box"></i> Tổng sản phẩm</h6>
                <h2><?= $stats['total_products'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-shopping-cart"></i> Tổng đơn hàng</h6>
                <h2><?= $stats['total_orders'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-money-bill-wave"></i> Doanh thu</h6>
                <h2><?= money($stats['total_revenue']) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-wallet"></i> Số dư khả dụng</h6>
                <h2><?= money($wallet['balance']) ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Sản phẩm bán chạy</h5>
            </div>
            <div class="card-body">
                <?php if (empty($bestProducts)): ?>
                    <p class="text-muted">Chưa có dữ liệu</p>
                <?php else: ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Đã bán</th>
                                <th>Giá</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bestProducts as $product): ?>
                            <tr>
                                <td><?= e(Helper::truncate($product['name'], 30)) ?></td>
                                <td><span class="badge bg-success"><?= $product['total_sold'] ?? 0 ?></span></td>
                                <td><?= money($product['price']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock"></i> Đơn hàng gần đây</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentOrders)): ?>
                    <p class="text-muted">Chưa có đơn hàng</p>
                <?php else: ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Sản phẩm</th>
                                <th>Số tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><small><?= e($order['order_code']) ?></small></td>
                                <td><small><?= e(Helper::truncate($order['product_name'], 25)) ?></small></td>
                                <td><strong><?= money($order['seller_amount']) ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fab fa-telegram text-primary"></i> Cài đặt thông báo Telegram</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">Nhận thông báo ngay lập tức qua Telegram khi có khách đặt hàng hoặc nhắn tin cho bạn.</p>
                <form action="<?= url('/seller/telegram/update') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Telegram Chat ID của bạn</label>
                        <div class="input-group">
                            <input type="text" name="telegram_chat_id" class="form-control" value="<?= e(Auth::user()['telegram_chat_id'] ?? '') ?>" placeholder="VD: 123456789">
                            <button type="submit" class="btn btn-primary">Lưu</button>
                        </div>
                        <div class="form-text mt-2">
                            <strong>Hướng dẫn lấy Chat ID:</strong><br>
                            1. Mở ứng dụng Telegram.<br>
                            2. Tìm kiếm bot <strong>@userinfobot</strong> và bấm Bắt đầu (Start).<br>
                            3. Copy chuỗi số phần "Id" và dán vào ô bên trên.<br>
                            4. Đảm bảo bạn đã nhắn <code>/start</code> cho bot hệ thống để nhận được thông báo.
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-body text-center py-5">
                <h5 class="mb-3">Bắt đầu bán hàng ngay!</h5>
                <p class="text-muted mb-4">Đăng sản phẩm và quản lý kho hàng của bạn để gia tăng thu nhập.</p>
                <a href="<?= url('/seller/products/create') ?>" class="btn btn-success rounded-pill px-4 py-2 fw-bold">
                    <i class="fas fa-plus me-2"></i> Thêm sản phẩm mới
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Seller Dashboard';
require_once __DIR__ . '/../layouts/seller.php';
?>
