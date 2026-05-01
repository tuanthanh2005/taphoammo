<?php ob_start(); ?>

<div class="container-fluid py-4">
    <!-- Stats Header -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <h4 class="fw-bold mb-1"><i class="fas fa-wallet text-primary me-2"></i> Quản lý rút tiền</h4>
            <p class="text-muted small">Duyệt và quản lý yêu cầu rút tiền của các Seller.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-list-ul me-2 text-primary"></i>Danh sách yêu cầu</h5>
            
            <form action="<?= url('/admin/withdrawals') ?>" method="GET" class="d-flex gap-2">
                <div class="input-group input-group-sm" style="max-width: 300px;">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Tên, email, thông tin..." value="<?= e($search ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-sm btn-primary px-3 rounded-pill fw-bold">Tìm</button>
                <?php if (!empty($search)): ?>
                    <a href="<?= url('/admin/withdrawals') ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-2"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-uppercase small fw-bold">
                            <th class="ps-4 py-3">Seller & Thông tin</th>
                            <th>Số tiền rút</th>
                            <th>Phí dịch vụ</th>
                            <th>Thực nhận</th>
                            <th>Ngày yêu cầu</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($withdrawals)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="fas fa-money-check-alt fa-3x mb-3 text-muted opacity-25"></i>
                                        <p class="text-muted">Chưa có yêu cầu rút tiền nào.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($withdrawals as $w): ?>
                                <tr class="<?= $w['active_disputes'] > 0 ? 'bg-danger-subtle bg-opacity-10' : '' ?>">
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= e($w['user_name']) ?></div>
                                                <div class="small text-muted mb-1"><?= e($w['user_email']) ?></div>
                                                <?php if ($w['active_disputes'] > 0): ?>
                                                    <span class="badge bg-danger rounded-pill" style="font-size: 10px;">
                                                        <i class="fas fa-exclamation-triangle me-1"></i> <?= $w['active_disputes'] ?> khiếu nại chưa xử lý
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="fw-bold text-dark"><?= money($w['amount']) ?></span></td>
                                    <td><span class="text-danger small">-<?= money($w['fee_amount']) ?> (<?= $w['fee_percent'] ?>%)</span></td>
                                    <td><span class="fw-bold text-success fs-5"><?= money($w['receive_amount']) ?></span></td>
                                    <td>
                                        <div class="small text-dark"><?= date('d/m/Y', strtotime($w['created_at'])) ?></div>
                                        <div class="text-muted" style="font-size: 11px;"><?= date('H:i', strtotime($w['created_at'])) ?></div>
                                    </td>
                                    <td>
                                        <?php if ($w['status'] === 'pending'): ?>
                                            <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill px-3 py-2">Chờ duyệt</span>
                                        <?php elseif ($w['status'] === 'approved'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success rounded-pill px-3 py-2">Đã duyệt</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger rounded-pill px-3 py-2">Từ chối</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-light btn-sm rounded-pill border px-3" type="button" data-bs-toggle="collapse" data-bs-target="#info-<?= $w['id'] ?>">
                                            Chi tiết <i class="fas fa-chevron-down ms-1 small"></i>
                                        </button>
                                    </td>
                                </tr>
                                <!-- Info Detail Row -->
                                <tr class="collapse" id="info-<?= $w['id'] ?>">
                                    <td colspan="7" class="bg-light p-0">
                                        <div class="p-4 border-top">
                                            <div class="row g-4">
                                                <div class="col-md-6">
                                                    <h6 class="fw-bold small text-uppercase text-muted mb-3">Thông tin nhận tiền (<?= e($w['method']) ?>)</h6>
                                                    <div class="p-3 bg-white border rounded-4 shadow-sm">
                                                        <?= nl2br(e($w['account_info'])) ?>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="fw-bold small text-uppercase text-muted mb-3">Hành động xử lý</h6>
                                                    <div class="card border-0 shadow-sm rounded-4">
                                                        <div class="card-body p-3">
                                                            <?php if ($w['status'] === 'pending'): ?>
                                                                <?php if ($w['active_disputes'] > 0): ?>
                                                                    <div class="alert alert-danger rounded-4 py-2 small mb-3">
                                                                        <i class="fas fa-exclamation-circle me-2"></i>
                                                                        Seller này đang có <strong><?= $w['active_disputes'] ?></strong> khiếu nại chưa xử lý. Hãy cẩn trọng khi duyệt rút tiền!
                                                                    </div>
                                                                <?php endif; ?>
                                                                
                                                                <div class="d-flex gap-2">
                                                                    <form action="<?= url('/admin/withdrawals/approve/' . $w['id']) ?>" method="POST" class="flex-grow-1">
                                                                        <?= csrf_field() ?>
                                                                        <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold" onclick="return confirm('Xác nhận đã chuyển tiền cho Seller này?')">
                                                                            <i class="fas fa-check-circle me-1"></i> DUYỆT & ĐÃ CHUYỂN
                                                                        </button>
                                                                    </form>
                                                                    <button type="button" class="btn btn-danger rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $w['id'] ?>">
                                                                        TỪ CHỐI
                                                                    </button>
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="p-2 text-center text-muted italic">
                                                                    <i class="fas fa-info-circle me-1"></i> Yêu cầu này đã được xử lý lúc <?= date('H:i d/m/Y', strtotime($w['updated_at'] ?? $w['created_at'])) ?>
                                                                    <?php if ($w['admin_note']): ?>
                                                                        <div class="mt-2 p-2 bg-light rounded text-dark small border-start border-4 border-danger">
                                                                            <strong>Lý do từ chối:</strong> <?= e($w['admin_note']) ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                            
                                                            <div class="mt-3 text-center">
                                                                <a href="<?= url('/admin/disputes?search=' . urlencode($w['user_name'])) ?>" class="small text-decoration-none">
                                                                    <i class="fas fa-history me-1"></i> Xem lịch sử khiếu nại của Seller này
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Reject Modal (Inside Loop for simplicity in this template) -->
                                <div class="modal fade" id="rejectModal<?= $w['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <form action="<?= url('/admin/withdrawals/reject/' . $w['id']) ?>" method="POST">
                                                <?= csrf_field() ?>
                                                <div class="modal-header border-0 pt-4 px-4">
                                                    <h5 class="modal-title fw-bold">Từ chối yêu cầu rút tiền</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <p class="text-muted small">Vui lòng nhập lý do từ chối để Seller nắm rõ thông tin.</p>
                                                    <textarea name="reason" class="form-control rounded-4 border-light-subtle bg-light" rows="4" placeholder="Ví dụ: Đang có khiếu nại chưa xử lý, thông tin ngân hàng sai..." required></textarea>
                                                </div>
                                                <div class="modal-footer border-0 pb-4 px-4">
                                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-modal="dismiss">Hủy</button>
                                                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">XÁC NHẬN TỪ CHỐI</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
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
.bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
.bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1); }
.bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1); }
.table-hover tbody tr:hover { background-color: rgba(0, 0, 0, 0.02); }
.btn-light { background: #f8fafc; border: 1px solid #e2e8f0; }
.btn-light:hover { background: #f1f5f9; }
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý rút tiền';
require_once __DIR__ . '/../layouts/admin.php';
?>
