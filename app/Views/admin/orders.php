<?php ob_start(); ?>

<div class="container-fluid py-4">
    <!-- Header & Stats -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-primary text-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                            <i class="fas fa-shopping-bag fa-lg"></i>
                        </div>
                        <h6 class="mb-0 text-white-50">Tổng số đơn hàng thành công</h6>
                    </div>
                    <h2 class="mb-0 fw-bold"><?= number_format($stats['total_orders'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-dark text-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                            <i class="fas fa-money-bill-wave fa-lg text-success"></i>
                        </div>
                        <h6 class="mb-0 text-white-50">Tổng doanh thu toàn sàn</h6>
                    </div>
                    <h2 class="mb-0 fw-bold"><?= money($stats['total_revenue'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-success text-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                            <i class="fas fa-hand-holding-usd fa-lg"></i>
                        </div>
                        <h6 class="mb-0 text-white-50">Admin nhận (Phí 5%)</h6>
                    </div>
                    <h2 class="mb-0 fw-bold"><?= money($stats['total_admin_fees'] ?? 0) ?></h2>
                    <div class="mt-2 small text-white-50">
                        Dựa trên toàn bộ sản phẩm đã được thanh toán.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2 text-primary"></i>Danh sách đơn hàng</h5>
            
            <form action="<?= url('/admin/orders') ?>" method="GET" class="d-flex gap-2">
                <div class="input-group" style="max-width: 300px;">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Mã đơn, tên, email..." value="<?= e($search ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary px-4 fw-bold">Tìm kiếm</button>
                <?php if (!empty($search)): ?>
                    <a href="<?= url('/admin/orders') ?>" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-uppercase small fw-bold">
                            <th class="ps-4 py-3">Mã đơn</th>
                            <th>Người mua</th>
                            <th>Tổng tiền</th>
                            <th>Admin (5%)</th>
                            <th>Người bán nhận</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="fas fa-shopping-cart fa-3x mb-3 text-muted opacity-25"></i>
                                        <p class="text-muted">Chưa có đơn hàng nào.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <?php 
                                    $adminFee = (float)($order['admin_fee'] ?? 0);
                                    $sellerReceive = (float)$order['total_amount'] - $adminFee;
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold">#<?= $order['order_code'] ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px; font-size: 11px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <div class="small fw-bold text-dark"><?= e($order['user_name']) ?></div>
                                                <div class="text-muted" style="font-size: 11px;"><?= e($order['user_email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= money($order['total_amount']) ?></div>
                                        <div class="small">
                                            <?php if ($order['payment_status'] === 'paid'): ?>
                                                <span class="text-success" style="font-size: 10px;"><i class="fas fa-check-circle"></i> Đã trả</span>
                                            <?php else: ?>
                                                <span class="text-warning" style="font-size: 10px;"><i class="fas fa-clock"></i> Chờ trả</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-success">+<?= money($adminFee) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-primary"><?= money($sellerReceive) ?></div>
                                    </td>
                                    <td>
                                        <?php if ($order['order_status'] === 'completed'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success rounded-pill px-3">Hoàn thành</span>
                                        <?php elseif ($order['order_status'] === 'processing'): ?>
                                            <span class="badge bg-info-subtle text-info border border-info rounded-pill px-3">Đang xử lý</span>
                                        <?php elseif ($order['order_status'] === 'pending'): ?>
                                            <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill px-3">Chờ xử lý</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger rounded-pill px-3">Đã hủy</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small text-muted">
                                        <?= date('d/m/Y', strtotime($order['created_at'])) ?><br>
                                        <span style="font-size: 10px;"><?= date('H:i', strtotime($order['created_at'])) ?></span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="<?= url('/admin/orders/' . $order['id']) ?>" class="btn btn-light btn-sm rounded-circle border shadow-sm" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (!empty($orders) && count($orders) >= 50): ?>
        <div class="card-footer bg-white border-0 py-3">
            <nav>
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link rounded-start-pill px-3" href="?page=<?= $currentPage - 1 ?>">Trước</a>
                    </li>
                    <li class="page-item active"><a class="page-link px-3" href="#"><?= $currentPage ?></a></li>
                    <li class="page-item">
                        <a class="page-link rounded-end-pill px-3" href="?page=<?= $currentPage + 1 ?>">Sau</a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1); }
.bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
.bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1); }
.bg-info-subtle { background-color: rgba(13, 202, 240, 0.1); }
.bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1); }
.table th { font-weight: 600; font-size: 12px; color: #666; }
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý đơn hàng';
require_once __DIR__ . '/../layouts/admin.php';
?>
