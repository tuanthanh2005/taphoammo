<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-home"></i> Dashboard</h2>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-box"></i> Tổng đơn hàng</h5>
                    <h2><?= $stats['total_orders'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-money-bill-wave"></i> Tổng chi tiêu</h5>
                    <h2><?= money($stats['total_spent']) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-wallet"></i> Số dư ví</h5>
                    <h2><?= money($wallet['balance']) ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock"></i> Đơn hàng gần đây</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentOrders)): ?>
                        <p class="text-muted">Chưa có đơn hàng nào</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Ngày đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td><strong><?= e($order['order_code']) ?></strong></td>
                                    <td><?= Helper::formatDate($order['created_at']) ?></td>
                                    <td><?= money($order['total_amount']) ?></td>
                                    <td>
                                        <?php if ($order['payment_status'] === 'paid'): ?>
                                            <span class="badge bg-success">Đã thanh toán</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Chờ thanh toán</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= url('/user/orders/' . $order['id']) ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Chi tiết
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <a href="<?= url('/user/orders') ?>" class="btn btn-outline-primary">Xem tất cả đơn hàng</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
