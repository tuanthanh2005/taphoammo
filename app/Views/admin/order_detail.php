<?php 
ob_start();
?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2 class="mb-0">Chi tiết đơn hàng #<?= e($order['order_code']) ?></h2>
        <a href="<?= url('/admin/orders') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Danh sách sản phẩm</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Seller</th>
                                <th>Đơn giá</th>
                                <th>SL</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= e($item['product_name']) ?></strong>
                                    <div><span class="badge bg-secondary"><?= e($item['item_status'] ?? 'processing') ?></span></div>
                                </td>
                                <td><?= e($item['seller_name']) ?></td>
                                <td><?= money($item['price']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td class="fw-bold"><?= money($item['subtotal']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Tổng cộng:</td>
                                <td class="fw-bold text-danger"><?= money($order['total_amount']) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin người mua</h5>
            </div>
            <div class="card-body">
                <p><strong>Họ tên:</strong> <?= e($order['user_name']) ?></p>
                <p><strong>Email:</strong> <?= e($order['user_email']) ?></p>
            </div>
        </div>
        
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin thanh toán</h5>
            </div>
            <div class="card-body">
                <p><strong>Trạng thái:</strong> 
                    <?php if ($order['payment_status'] === 'paid'): ?>
                        <span class="badge bg-success">Đã thanh toán</span>
                    <?php else: ?>
                        <span class="badge bg-warning"><?= e($order['payment_status']) ?></span>
                    <?php endif; ?>
                </p>
                <p><strong>Phương thức:</strong> <span class="text-uppercase"><?= e($order['payment_method']) ?></span></p>
                <p><strong>Ngày tạo:</strong> <?= date('d/m/Y H:i:s', strtotime($order['created_at'])) ?></p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Chi tiết đơn hàng';
require_once __DIR__ . '/../layouts/admin.php';
?>
