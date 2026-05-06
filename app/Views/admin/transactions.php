<?php ob_start(); ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="fw-bold mb-1 text-dark"><i class="fas fa-exchange-alt text-primary me-2"></i> Lịch sử giao dịch hệ thống</h4>
            <p class="text-muted small mb-0">Theo dõi biến động số dư của tất cả người dùng trên sàn.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="<?= url('/admin/transactions') ?>" method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label small fw-bold">Tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Tên khách, email hoặc mô tả..." value="<?= e($currentSearch ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Loại giao dịch</label>
                    <select name="type" class="form-select">
                        <option value="all" <?= ($currentType ?? 'all') === 'all' ? 'selected' : '' ?>>-- Tất cả loại --</option>
                        <option value="deposit" <?= ($currentType ?? '') === 'deposit' ? 'selected' : '' ?>>Nạp tiền</option>
                        <option value="purchase" <?= ($currentType ?? '') === 'purchase' ? 'selected' : '' ?>>Mua hàng</option>
                        <option value="sale_income" <?= ($currentType ?? '') === 'sale_income' ? 'selected' : '' ?>>Bán hàng (Thu nhập)</option>
                        <option value="withdrawal" <?= ($currentType ?? '') === 'withdrawal' ? 'selected' : '' ?>>Rút tiền</option>
                        <option value="admin_fee" <?= ($currentType ?? '') === 'admin_fee' ? 'selected' : '' ?>>Phí nền tảng</option>
                        <option value="refund" <?= ($currentType ?? '') === 'refund' ? 'selected' : '' ?>>Hoàn tiền</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Lọc dữ liệu</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="<?= url('/admin/transactions') ?>" class="btn btn-light w-100 rounded-pill">Xóa lọc</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Transaction Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-uppercase small fw-bold text-muted">
                            <th class="ps-4 py-3">Mã GD</th>
                            <th>Người dùng</th>
                            <th>Loại</th>
                            <th>Biến động</th>
                            <th>Số dư sau</th>
                            <th>Mô tả</th>
                            <th class="pe-4">Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="fas fa-history fa-3x mb-3 text-muted opacity-25"></i>
                                        <p class="text-muted">Không tìm thấy giao dịch nào phù hợp.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $tx): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">#<?= $tx['id'] ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= e($tx['user_name']) ?></div>
                                        <div class="text-muted" style="font-size: 11px;"><?= e($tx['user_email']) ?></div>
                                    </td>
                                    <td>
                                        <?php
                                        $typeConfig = [
                                            'deposit' => ['bg' => 'bg-success-subtle', 'text' => 'text-success', 'label' => 'Nạp tiền'],
                                            'purchase' => ['bg' => 'bg-danger-subtle', 'text' => 'text-danger', 'label' => 'Mua hàng'],
                                            'sale_income' => ['bg' => 'bg-primary-subtle', 'text' => 'text-primary', 'label' => 'Bán hàng'],
                                            'admin_fee' => ['bg' => 'bg-warning-subtle', 'text' => 'text-warning', 'label' => 'Phí Admin'],
                                            'withdrawal' => ['bg' => 'bg-info-subtle', 'text' => 'text-info', 'label' => 'Rút tiền'],
                                            'withdrawal_fee' => ['bg' => 'bg-secondary-subtle', 'text' => 'text-secondary', 'label' => 'Phí rút'],
                                            'refund' => ['bg' => 'bg-purple-subtle', 'text' => 'text-purple', 'label' => 'Hoàn tiền'],
                                            'affiliate_commission' => ['bg' => 'bg-orange-subtle', 'text' => 'text-orange', 'label' => 'Hoa hồng']
                                        ];
                                        $cfg = $typeConfig[$tx['type']] ?? ['bg' => 'bg-light', 'text' => 'text-dark', 'label' => $tx['type']];
                                        ?>
                                        <span class="badge rounded-pill <?= $cfg['bg'] ?> <?= $cfg['text'] ?> px-3 py-2 fw-normal" style="font-size: 11px;">
                                            <?= $cfg['label'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold <?= $tx['amount'] < 0 ? 'text-danger' : 'text-success' ?>" style="font-size: 15px;">
                                            <?= $tx['amount'] < 0 ? '' : '+' ?> <?= money($tx['amount']) ?>
                                        </div>
                                        <div class="text-muted" style="font-size: 10px;">Từ: <?= money($tx['balance_before']) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= money($tx['balance_after']) ?></div>
                                    </td>
                                    <td>
                                        <div class="text-muted small" style="max-width: 300px;"><?= e($tx['description']) ?></div>
                                    </td>
                                    <td class="pe-4">
                                        <div class="text-dark small fw-semibold"><?= date('d/m/Y', strtotime($tx['created_at'])) ?></div>
                                        <div class="text-muted" style="font-size: 10px;"><?= date('H:i:s', strtotime($tx['created_at'])) ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($totalPages > 1): ?>
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
.bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1); }
.bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1); }
.bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1); }
.bg-info-subtle { background-color: rgba(13, 202, 240, 0.1); }
.bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1); }
.bg-purple-subtle { background-color: rgba(111, 66, 193, 0.1); }
.bg-orange-subtle { background-color: rgba(253, 126, 20, 0.1); }

.text-purple { color: #6f42c1; }
.text-orange { color: #fd7e14; }

.pagination .page-link {
    border: none;
    color: #64748b;
}
.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    color: #fff;
}
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Lịch sử giao dịch';
require_once __DIR__ . '/../layouts/admin.php';
?>
