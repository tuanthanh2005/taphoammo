<?php 
ob_start();
?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h6><i class="fas fa-users"></i> Tổng người dùng</h6>
                <h2><?= $stats['total_users'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h6><i class="fas fa-store"></i> Tổng Sellers</h6>
                <h2><?= $stats['total_sellers'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h6><i class="fas fa-box"></i> Tổng sản phẩm</h6>
                <h2><?= $stats['total_products'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h6><i class="fas fa-shopping-cart"></i> Tổng đơn hàng</h6>
                <h2><?= $stats['total_orders'] ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h6><i class="fas fa-money-bill-wave"></i> Tổng doanh thu</h6>
                <h2><?= money($stats['total_revenue']) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h6><i class="fas fa-chart-line"></i> Doanh thu Admin</h6>
                <h2><?= money($stats['admin_revenue']) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h6><i class="fas fa-exclamation-triangle"></i> Chờ xử lý</h6>
                <p class="mb-0">Sản phẩm: <strong><?= $stats['pending_products'] ?></strong></p>
                <p class="mb-0">Rút tiền: <strong><?= $stats['pending_withdrawals'] ?></strong></p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Đơn hàng gần đây</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentOrders)): ?>
                    <p class="text-muted">Chưa có đơn hàng</p>
                <?php else: ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Số tiền</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><small><?= e($order['order_code']) ?></small></td>
                                <td><small><?= e($order['user_name']) ?></small></td>
                                <td><?= money($order['total_amount']) ?></td>
                                <td>
                                    <?php if ($order['payment_status'] === 'paid'): ?>
                                        <span class="badge bg-success">Paid</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <a href="<?= url('/admin/orders') ?>" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Yêu cầu rút tiền chờ duyệt</h5>
            </div>
            <div class="card-body">
                <?php if (empty($pendingWithdrawals)): ?>
                    <p class="text-muted">Không có yêu cầu nào</p>
                <?php else: ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Seller</th>
                                <th>Số tiền</th>
                                <th>Thực nhận</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingWithdrawals as $w): ?>
                            <tr>
                                <td><small><?= e($w['user_name']) ?></small></td>
                                <td><?= money($w['amount']) ?></td>
                                <td class="text-success"><?= money($w['receive_amount']) ?></td>
                                <td>
                                    <a href="<?= url('/admin/withdrawals') ?>" class="btn btn-xs btn-primary">Xử lý</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <a href="<?= url('/admin/withdrawals') ?>" class="btn btn-sm btn-outline-warning">Xem tất cả</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../layouts/admin.php';
?>
