<?php ob_start(); ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <!-- Header -->
    <div class="row mb-4 gy-3">
        <div class="col-12 col-md-auto me-auto">
            <h4 class="fw-bold mb-1"><i class="fas fa-shopping-basket text-primary me-2"></i> Quản lý Đơn hàng</h4>
            <div class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill small">
                Tổng: <?= count($orders) ?> đơn
            </div>
        </div>
        <div class="col-12 col-md-auto">
            <form action="<?= url('/seller/orders') ?>" method="GET" class="row g-2">
                <div class="col">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Tìm mã đơn, SP..." value="<?= e($search ?? '') ?>">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">Tìm</button>
                    <?php if (!empty($search)): ?>
                        <a href="<?= url('/seller/orders') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-uppercase small fw-bold text-muted">
                            <th class="ps-3 py-3" style="min-width: 130px;">Mã đơn / Ngày</th>
                            <th style="min-width: 150px;">Sản phẩm / Khách</th>
                            <th style="min-width: 90px;">Doanh thu</th>
                            <th style="min-width: 100px;">Trạng thái</th>
                            <th class="pe-3 text-end">Chi tiết</th>
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
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <div class="fw-bold text-dark">#<?= e($order['order_code']) ?></div>
                                            <?php if (!($order['is_read'] ?? 1)): ?>
                                                <span class="badge bg-danger rounded-pill pulse-badge" style="font-size: 0.6rem;">Mới</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.65rem;">
                                            <i class="far fa-clock me-1"></i> <?= date('d/m/y H:i', strtotime($order['created_at'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark text-truncate" style="max-width: 140px; font-size: 0.85rem;"><?= e($order['product_name']) ?></div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">
                                            x<?= $order['quantity'] ?> · <span class="fw-bold text-muted"><?= e($order['buyer_name']) ?></span>
                                        </div>
                                        <?php if (!empty($order['note'])): ?>
                                            <div class="mt-1">
                                                <span class="badge bg-light text-dark fw-normal border text-truncate" style="font-size: 0.65rem; max-width: 140px;">
                                                    <i class="fas fa-comment-dots text-muted me-1"></i> <?= e($order['note']) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-success" style="font-size: 0.85rem;"><?= money($order['seller_amount']) ?></div>
                                        <div class="text-muted" style="font-size: 0.65rem;"><?= $order['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chờ' ?></div>
                                    </td>
                                    <td>
                                        <?php
                                        $statusConfigs = [
                                            'processing' => ['bg' => 'bg-warning-subtle', 'text' => 'text-warning', 'label' => 'Xử lý'],
                                            'delivered' => ['bg' => 'bg-success-subtle', 'text' => 'text-success', 'label' => 'Đã giao'],
                                            'issue' => ['bg' => 'bg-danger-subtle', 'text' => 'text-danger', 'label' => 'Lỗi'],
                                            'refunded' => ['bg' => 'bg-secondary-subtle', 'text' => 'text-secondary', 'label' => 'Hoàn']
                                        ];
                                        $cfg = $statusConfigs[$order['item_status'] ?? 'processing'] ?? ['bg' => 'bg-light', 'text' => 'text-dark', 'label' => $order['item_status']];
                                        ?>
                                        <span class="badge rounded-pill <?= $cfg['bg'] ?> <?= $cfg['text'] ?> px-2 py-1 fw-normal" style="font-size: 0.65rem;">
                                            <?= $cfg['label'] ?>
                                        </span>
                                    </td>
                                    <td class="pe-3 text-end">
                                        <a href="<?= url('/seller/orders/' . $order['order_id']) ?>" class="btn btn-sm btn-primary py-1 px-2" style="font-size: 0.75rem;">
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
