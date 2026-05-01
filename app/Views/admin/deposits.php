<?php ob_start(); ?>

<div class="container-fluid py-4">
    <!-- Stats Header -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-warning text-dark">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-white bg-opacity-50 rounded-circle p-2 me-3">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                        <h6 class="mb-0 fw-bold">Yêu cầu chờ duyệt</h6>
                    </div>
                    <h2 class="mb-0 fw-bold"><?= number_format($stats['pending_count'] ?? 0) ?></h2>
                    <div class="small mt-1 opacity-75">Tổng tiền: <?= money($stats['pending_amount'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-success text-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                            <i class="fas fa-check-double fa-lg"></i>
                        </div>
                        <h6 class="mb-0 fw-bold text-white-50">Nạp thành công hôm nay</h6>
                    </div>
                    <h2 class="mb-0 fw-bold"><?= money($stats['approved_today'] ?? 0) ?></h2>
                    <div class="small mt-1 text-white-50">Cập nhật lúc: <?= date('H:i') ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-primary text-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                            <i class="fas fa-shield-alt fa-lg"></i>
                        </div>
                        <h6 class="mb-0 fw-bold text-white-50">Tuân thủ pháp lý</h6>
                    </div>
                    <p class="small mb-0 opacity-75">Tất cả giao dịch đều được ghi lại với đầy đủ mã chuyển khoản và đối soát ngân hàng.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Deposit List -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-university me-2 text-primary"></i> Đối soát nạp tiền ngân hàng</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-uppercase small fw-bold">
                            <th class="ps-4 py-3">Giao dịch</th>
                            <th>Người nạp</th>
                            <th>Số tiền</th>
                            <th>Nội dung chuyển khoản</th>
                            <th>Tài khoản nhận</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($depositRequests)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="fas fa-receipt fa-3x mb-3 text-muted opacity-25"></i>
                                        <p class="text-muted">Chưa có yêu cầu nạp tiền nào được ghi nhận.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($depositRequests as $item): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">#<?= (int)$item['id'] ?></div>
                                        <div class="small text-muted"><?= date('d/m/Y H:i', strtotime($item['created_at'])) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= e($item['user_name']) ?></div>
                                        <div class="small text-muted" style="font-size: 11px;"><?= e($item['user_email']) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-success fs-5"><?= money($item['amount']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-3 py-2 font-monospace" style="font-size: 13px; letter-spacing: 1px;">
                                            <?= e($item['transfer_code']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold small text-dark"><?= e($item['bank_name']) ?></div>
                                        <div class="text-muted small"><?= e($item['account_number']) ?></div>
                                    </td>
                                    <td>
                                        <?php if ($item['status'] === 'pending'): ?>
                                            <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill px-3 py-2">Đang chờ</span>
                                        <?php elseif ($item['status'] === 'approved'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success rounded-pill px-3 py-2">Thành công</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger rounded-pill px-3 py-2">Từ chối</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <?php if ($item['status'] === 'pending'): ?>
                                            <div class="d-flex justify-content-end gap-2">
                                                <form action="<?= url('/admin/deposits/approve/' . $item['id']) ?>" method="POST">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="admin_note" value="Duyệt nạp tiền tự động">
                                                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-bold" onclick="return confirm('Xác nhận đã nhận được tiền và muốn duyệt yêu cầu này?')">
                                                        DUYỆT
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#rejectDepositModal<?= $item['id'] ?>">
                                                    TỪ CHỐI
                                                </button>
                                            </div>

                                            <!-- Reject Modal -->
                                            <div class="modal fade" id="rejectDepositModal<?= $item['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content border-0 shadow-lg rounded-4">
                                                        <form action="<?= url('/admin/deposits/reject/' . $item['id']) ?>" method="POST">
                                                            <?= csrf_field() ?>
                                                            <div class="modal-header border-0 pt-4 px-4">
                                                                <h5 class="modal-title fw-bold">Từ chối nạp tiền</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body p-4">
                                                                <p class="text-muted small">Hãy nêu rõ lý do tại sao không chấp nhận yêu cầu này (Ví dụ: Sai nội dung, chưa nhận được tiền...).</p>
                                                                <textarea name="reason" class="form-control rounded-4 bg-light border-0" rows="3" required placeholder="Lý do từ chối..."></textarea>
                                                            </div>
                                                            <div class="modal-footer border-0 pb-4 px-4">
                                                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                                                                <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">XÁC NHẬN TỪ CHỐI</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="small text-muted italic">
                                                <i class="fas fa-user-check me-1"></i> Đã xử lý
                                                <?php if(!empty($item['admin_note'])): ?>
                                                    <div class="mt-1 x-small bg-light p-1 rounded border-start border-3 border-danger">
                                                        <?= e($item['admin_note']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
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
.bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1); }
.bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
.bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1); }
.font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
.italic { font-style: italic; }
.x-small { font-size: 10px; }
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý nạp tiền';
require_once __DIR__ . '/../layouts/admin.php';
?>
