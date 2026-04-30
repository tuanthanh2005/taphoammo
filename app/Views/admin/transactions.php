<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">Lịch sử giao dịch toàn hệ thống</h2>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Người dùng</th>
                            <th>Loại giao dịch</th>
                            <th>Số tiền</th>
                            <th>Số dư trước</th>
                            <th>Số dư sau</th>
                            <th>Mô tả</th>
                            <th>Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">Không có dữ liệu</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $tx): ?>
                                <tr>
                                    <td>#<?= $tx['id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="mb-0"><?= e($tx['user_name']) ?></h6>
                                                <small class="text-muted"><?= e($tx['user_email']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $typeLabels = [
                                            'deposit' => ['success', 'Nạp tiền'],
                                            'purchase' => ['danger', 'Mua hàng'],
                                            'sale_income' => ['success', 'Bán hàng'],
                                            'admin_fee' => ['danger', 'Phí nền tảng'],
                                            'withdrawal' => ['warning', 'Rút tiền'],
                                            'withdrawal_fee' => ['danger', 'Phí rút tiền'],
                                            'refund' => ['info', 'Hoàn tiền'],
                                            'affiliate_commission' => ['success', 'Hoa hồng']
                                        ];
                                        $label = $typeLabels[$tx['type']] ?? ['secondary', $tx['type']];
                                        ?>
                                        <span class="badge bg-<?= $label[0] ?>"><?= $label[1] ?></span>
                                    </td>
                                    <td>
                                        <?php if (in_array($tx['type'], ['purchase', 'admin_fee', 'withdrawal', 'withdrawal_fee'])): ?>
                                            <span class="text-danger fw-bold">-<?= money($tx['amount']) ?></span>
                                        <?php else: ?>
                                            <span class="text-success fw-bold">+<?= money($tx['amount']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted"><?= money($tx['balance_before']) ?></td>
                                    <td class="fw-bold"><?= money($tx['balance_after']) ?></td>
                                    <td><small class="text-muted"><?= e($tx['description']) ?></small></td>
                                    <td><?= date('d/m/Y H:i:s', strtotime($tx['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (!empty($transactions) && count($transactions) == 100): ?>
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
$pageTitle = 'Lịch sử giao dịch';
require_once __DIR__ . '/../layouts/admin.php';
?>
