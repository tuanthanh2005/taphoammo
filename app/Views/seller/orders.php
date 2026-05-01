<?php ob_start(); ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="fw-bold mb-1"><i class="fas fa-shopping-basket text-primary me-2"></i> Quản lý Đơn hàng</h4>
            <p class="text-muted small mb-0">Theo dõi và xử lý các đơn hàng khách hàng đã mua từ gian hàng của bạn.</p>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                Tổng cộng: <?= count($orders) ?> đơn hàng (trang <?= $currentPage ?>)
            </div>
            
            <div class="mt-3">
                <form action="<?= url('/seller/orders') ?>" method="GET" class="d-flex gap-2 justify-content-md-end">
                    <div class="input-group" style="max-width: 300px;">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Tìm mã đơn, tên sản phẩm..." value="<?= e($search ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill fw-bold">Tìm</button>
                    <?php if (!empty($search)): ?>
                        <a href="<?= url('/seller/orders') ?>" class="btn btn-outline-secondary rounded-pill px-3"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Orders Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-uppercase small fw-bold text-muted">
                            <th class="ps-4 py-3">Thông báo</th>
                            <th>Mã đơn</th>
                            <th>Sản phẩm</th>
                            <th>Khách hàng</th>
                            <th>Doanh thu</th>
                            <th>Trạng thái</th>
                            <th class="pe-4 text-end">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="fas fa-shopping-cart fa-3x mb-3 text-muted opacity-25"></i>
                                        <p class="text-muted">Chưa có khách hàng nào đặt hàng từ bạn.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="ps-4">
                                        <?php if (!($order['is_read'] ?? 1)): ?>
                                            <span class="badge bg-danger rounded-pill pulse-badge">Mới</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border rounded-pill">Đã xem</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">#<?= e($order['order_code']) ?></div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            <i class="far fa-clock me-1"></i> <?= date('H:i d/m/Y', strtotime($order['created_at'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= e(Helper::truncate($order['product_name'], 40)) ?></div>
                                        <div class="text-muted small">Số lượng: x<?= $order['quantity'] ?></div>
                                        <?php if (!empty($order['note'])): ?>
                                            <div class="mt-1">
                                                <span class="badge bg-light text-dark fw-normal border" style="font-size: 10px;">
                                                    <i class="fas fa-comment-dots text-muted me-1"></i> Ghi chú: <?= e(Helper::truncate($order['note'], 30)) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold small text-dark"><?= e($order['buyer_name']) ?></div>
                                        <div class="text-muted" style="font-size: 11px;">ID: #<?= $order['order_id'] ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-success"><?= money($order['seller_amount']) ?></div>
                                        <div class="text-muted" style="font-size: 10px;">Thanh toán: <?= $order['payment_status'] === 'paid' ? 'Đã xong' : 'Chờ' ?></div>
                                    </td>
                                    <td>
                                        <?php
                                        $statusConfigs = [
                                            'processing' => ['bg' => 'bg-warning-subtle', 'text' => 'text-warning', 'label' => 'Đang xử lý'],
                                            'delivered' => ['bg' => 'bg-success-subtle', 'text' => 'text-success', 'label' => 'Đã giao hàng'],
                                            'issue' => ['bg' => 'bg-danger-subtle', 'text' => 'text-danger', 'label' => 'Có vấn đề'],
                                            'refunded' => ['bg' => 'bg-secondary-subtle', 'text' => 'text-secondary', 'label' => 'Đã hoàn tiền']
                                        ];
                                        $cfg = $statusConfigs[$order['item_status'] ?? 'processing'] ?? ['bg' => 'bg-light', 'text' => 'text-dark', 'label' => $order['item_status']];
                                        ?>
                                        <span class="badge rounded-pill <?= $cfg['bg'] ?> <?= $cfg['text'] ?> px-3 fw-normal" style="font-size: 11px;">
                                            <?= $cfg['label'] ?>
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="<?= url('/seller/orders/' . $order['order_id']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                                            <i class="fas fa-eye me-1"></i> Xem đơn
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (isset($totalPages) && $totalPages > 1): ?>
        <div class="card-footer bg-white border-top py-3">
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <?php if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link rounded-pill px-3" href="<?= Helper::buildQuery(['page' => $currentPage - 1]) ?>">Trước</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php 
                    $start = max(1, $currentPage - 2);
                    $end = min($totalPages, $currentPage + 2);
                    for ($i = $start; $i <= $end; $i++): 
                    ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <a class="page-link rounded-circle mx-1" href="<?= Helper::buildQuery(['page' => $i]) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link rounded-pill px-3" href="<?= Helper::buildQuery(['page' => $currentPage + 1]) ?>">Sau</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
.bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1); }
.bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1); }
.bg-info-subtle { background-color: rgba(13, 202, 240, 0.1); }
.bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1); }

.table thead th {
    font-size: 11px;
    letter-spacing: 0.5px;
}

.page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

@keyframes pulse-red {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}
.pulse-badge {
    animation: pulse-red 2s infinite;
}
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý Đơn hàng';
require_once __DIR__ . '/../layouts/seller.php';
?>
