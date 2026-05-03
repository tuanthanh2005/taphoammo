<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-box"></i> Đơn hàng của tôi</h2>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($orders)): ?>
                <p class="text-muted">Bạn chưa có đơn hàng nào</p>
                <a href="<?= url('/products') ?>" class="btn btn-success">
                    <i class="fas fa-shopping-cart"></i> Mua sắm ngay
                </a>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Ngày đặt</th>
                                <th>Tổng tiền</th>
                                <th>Thanh toán</th>
                                <th>Trạng thái</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
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
                                    <?php if ($order['order_status'] === 'completed'): ?>
                                        <span class="badge bg-success">Hoàn thành</span>
                                    <?php else: ?>
                                        <span class="badge bg-info">Đang xử lý</span>
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
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
