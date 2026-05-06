<?php ob_start(); ?>

<div class="container-fluid py-3">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="fw-bold mb-1"><i class="fas fa-gavel text-primary me-2"></i> Trung tâm Giải quyết Khiếu nại</h4>
            <p class="text-muted small mb-0">Xử lý các tranh chấp giữa Người mua và Người bán trên sàn.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex flex-wrap justify-content-md-end gap-3 align-items-center">
            <form action="<?= url('/admin/disputes') ?>" method="GET" class="d-flex gap-2">
                <?php if (!empty($currentStatus)): ?>
                    <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
                <?php endif; ?>
                <div class="input-group input-group-sm" style="max-width: 250px;">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Tìm mã khiếu nại, đơn, tên..." value="<?= e($search ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-sm btn-primary px-3 rounded-pill">Tìm</button>
            </form>

            <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                <a class="btn btn-white btn-sm <?= empty($currentStatus) ? 'active' : '' ?> px-3" href="<?= url('/admin/disputes') ?>">Tất cả</a>
                <?php 
                $statusMap = [
                    'open' => ['text' => 'Mới mở', 'class' => 'bg-danger'],
                    'under_review' => ['text' => 'Đang xem xét', 'class' => 'bg-warning'],
                    'resolved_refund' => ['text' => 'Hoàn tiền', 'class' => 'bg-success'],
                    'resolved_partial' => ['text' => 'Hoàn 1 phần', 'class' => 'bg-info'],
                    'resolved_rejected' => ['text' => 'Từ chối', 'class' => 'bg-secondary'],
                ];
                foreach ($statusMap as $k => $v): 
                    $count = 0;
                    foreach ($counts as $c) {
                        if ($c['status'] == $k) $count = $c['total'];
                    }
                ?>
                <a class="btn btn-white btn-sm <?= $currentStatus === $k ? 'active' : '' ?> px-3 border" href="<?= url('/admin/disputes?status=' . $k) ?>">
                    <?= $v['text'] ?> <?= $count > 0 ? "<span class='badge {$v['class']} ms-1'>$count</span>" : '' ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Disputes Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-uppercase small fw-bold">
                            <th class="ps-4 py-3">Mã khiếu nại</th>
                            <th>Đơn hàng</th>
                            <th>Các bên liên quan</th>
                            <th>Lý do & Số tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($disputes)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="fas fa-check-circle fa-3x mb-3 text-muted opacity-25"></i>
                                        <p class="text-muted">Không tìm thấy khiếu nại nào cần xử lý.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($disputes as $d): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">#<?= e($d['dispute_code']) ?></div>
                                </td>
                                <td>
                                    <a href="<?= url('/admin/orders/' . $d['order_id']) ?>" class="text-decoration-none fw-semibold">
                                        <i class="fas fa-shopping-cart small me-1"></i> #<?= e($d['order_code']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <div class="small">
                                            <span class="badge bg-primary-subtle text-primary fw-normal">Mua:</span> 
                                            <strong><?= e($d['user_name']) ?></strong> (@<?= e($d['user_username']) ?>)
                                        </div>
                                        <div class="small">
                                            <span class="badge bg-warning-subtle text-warning fw-normal">Bán:</span> 
                                            <strong><?= e($d['seller_name']) ?></strong> (@<?= e($d['seller_username']) ?>)
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $reasonMap = [
                                        'not_received' => 'Chưa nhận được hàng',
                                        'wrong_item' => 'Sai sản phẩm',
                                        'not_working' => 'Sản phẩm lỗi/hỏng',
                                        'scam' => 'Lừa đảo',
                                        'other' => 'Khác'
                                    ];
                                    ?>
                                    <div class="fw-bold"><?= $reasonMap[$d['reason']] ?? 'Khác' ?></div>
                                    <div class="text-danger small fw-bold"><?= money($d['amount']) ?></div>
                                </td>
                                <td>
                                    <?php
                                    $sConfig = [
                                        'open' => ['bg' => 'bg-danger-subtle', 'text' => 'text-danger', 'border' => 'border-danger', 'label' => 'Mới mở'],
                                        'under_review' => ['bg' => 'bg-warning-subtle', 'text' => 'text-warning', 'border' => 'border-warning', 'label' => 'Đang xem xét'],
                                        'resolved_refund' => ['bg' => 'bg-success-subtle', 'text' => 'text-success', 'border' => 'border-success', 'label' => 'Hoàn tiền'],
                                        'resolved_partial' => ['bg' => 'bg-info-subtle', 'text' => 'text-info', 'border' => 'border-info', 'label' => 'Hoàn 1 phần'],
                                        'resolved_rejected' => ['bg' => 'bg-secondary-subtle', 'text' => 'text-secondary', 'border' => 'border-secondary', 'label' => 'Từ chối'],
                                        'closed' => ['bg' => 'bg-dark-subtle', 'text' => 'text-dark', 'border' => 'border-dark', 'label' => 'Đã đóng']
                                    ][$d['status']] ?? ['bg' => 'bg-light', 'text' => 'text-dark', 'border' => 'border-secondary', 'label' => $d['status']];
                                    ?>
                                    <span class="badge rounded-pill border px-3 py-2 <?= $sConfig['bg'] ?> <?= $sConfig['text'] ?> <?= $sConfig['border'] ?>">
                                        <?= $sConfig['label'] ?>
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    <?= date('d/m/Y', strtotime($d['created_at'])) ?><br>
                                    <span style="font-size: 10px;"><?= date('H:i', strtotime($d['created_at'])) ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="<?= url('/admin/disputes/' . $d['id']) ?>" class="btn btn-primary rounded-pill btn-sm px-4 shadow-sm">
                                        Xử lý ngay
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1); }
.bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1); }
.bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1); }
.bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
.bg-info-subtle { background-color: rgba(13, 202, 240, 0.1); }
.bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1); }
.bg-dark-subtle { background-color: rgba(33, 37, 41, 0.1); }

.btn-white { background: #fff; color: #444; }
.btn-white:hover { background: #f8fafc; color: #000; }
.btn-white.active { background: #0d6efd; color: #fff; border-color: #0d6efd; }
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý khiếu nại';
require_once __DIR__ . '/../layouts/admin.php';
?>
