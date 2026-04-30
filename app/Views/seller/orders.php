<?php ob_start(); ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2 class="mb-0">Quản lý đơn hàng</h2>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã ĐH</th>
                        <th>Khách hàng</th>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt</th>
                        <th>Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">Chưa có đơn hàng nào</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><span class="fw-bold">#<?= $order['order_code'] ?></span></td>
                                <td><?= e($order['buyer_name']) ?></td>
                                <td>
                                    <strong><?= e($order['product_name']) ?></strong>
                                    <?php if (!empty($order['note'])): ?>
                                        <div class="small text-muted mt-1">
                                            <i class="fas fa-comment-dots"></i> Ghi chú: <span class="text-dark italic"><?= e($order['note']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>x<?= $order['quantity'] ?></td>
                                <td class="fw-bold text-success"><?= money($order['seller_amount']) ?></td>
                                <td>
                                    <?php if ($order['payment_status'] === 'paid'): ?>
                                        <span class="badge bg-success">Đã thanh toán</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Chờ thanh toán</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <a href="<?= url('/seller/orders/' . $order['order_id']) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Xem
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (!empty($orders) && count($orders) == 20): ?>
    <div class="card-footer bg-white border-top-0">
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-end mb-0">
                <?php if ($currentPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $currentPage - 1 ?>">Trước</a>
                    </li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $currentPage + 1 ?>">Sau</a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Đơn hàng';
require_once __DIR__ . '/../layouts/seller.php';
?>
