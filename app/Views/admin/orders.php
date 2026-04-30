<?php ob_start(); ?>

<div class="container-fluid py-4">
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
                            <th>Người mua</th>
                            <th>Tổng tiền</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">Không có dữ liệu</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><span class="fw-bold">#<?= $order['order_code'] ?></span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="mb-0"><?= e($order['user_name']) ?></h6>
                                                <small class="text-muted"><?= e($order['user_email']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-bold text-danger"><?= money($order['total_amount']) ?></td>
                                    <td>
                                        <?php if ($order['payment_status'] === 'paid'): ?>
                                            <span class="badge bg-success">Đã thanh toán</span>
                                        <?php elseif ($order['payment_status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Chờ thanh toán</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger text-uppercase"><?= $order['payment_status'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($order['order_status'] === 'completed'): ?>
                                            <span class="badge bg-success">Hoàn thành</span>
                                        <?php elseif ($order['order_status'] === 'processing'): ?>
                                            <span class="badge bg-info">Đang xử lý</span>
                                        <?php elseif ($order['order_status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Chờ xử lý</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Đã hủy</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= url('/admin/orders/' . $order['id']) ?>" class="btn btn-sm btn-outline-primary" title="Xem chi tiết"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (!empty($orders) && count($orders) == 50): ?>
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
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý đơn hàng';
require_once __DIR__ . '/../layouts/admin.php';
?>
