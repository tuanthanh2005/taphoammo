<?php 
ob_start();
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Quản lý rút tiền</h5>
    </div>
    <div class="card-body">
        <?php if (empty($withdrawals)): ?>
            <p class="text-muted">Chưa có yêu cầu rút tiền nào</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Seller</th>
                        <th>Số tiền</th>
                        <th>Phí (<?= $withdrawals[0]['fee_percent'] ?>%)</th>
                        <th>Thực nhận</th>
                        <th>Phương thức</th>
                        <th>Ngày tạo</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($withdrawals as $w): ?>
                    <tr>
                        <td><?= $w['id'] ?></td>
                        <td>
                            <strong><?= e($w['user_name']) ?></strong><br>
                            <small class="text-muted"><?= e($w['user_email']) ?></small>
                        </td>
                        <td><?= money($w['amount']) ?></td>
                        <td class="text-danger">-<?= money($w['fee_amount']) ?></td>
                        <td class="fw-bold text-success"><?= money($w['receive_amount']) ?></td>
                        <td><?= e($w['method']) ?></td>
                        <td><?= Helper::formatDate($w['created_at']) ?></td>
                        <td>
                            <?php if ($w['status'] === 'pending'): ?>
                                <span class="badge bg-warning">Chờ duyệt</span>
                            <?php elseif ($w['status'] === 'approved'): ?>
                                <span class="badge bg-success">Đã duyệt</span>
                            <?php elseif ($w['status'] === 'rejected'): ?>
                                <span class="badge bg-danger">Từ chối</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($w['status'] === 'pending'): ?>
                                <form action="<?= url('/admin/withdrawals/approve/' . $w['id']) ?>" method="POST" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Duyệt yêu cầu này?')">
                                        <i class="fas fa-check"></i> Duyệt
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $w['id'] ?>">
                                    <i class="fas fa-times"></i> Từ chối
                                </button>
                                
                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal<?= $w['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="<?= url('/admin/withdrawals/reject/' . $w['id']) ?>" method="POST">
                                                <?= csrf_field() ?>
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Từ chối yêu cầu rút tiền</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Lý do từ chối</label>
                                                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-danger">Xác nhận từ chối</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">Đã xử lý</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="9" class="bg-light">
                            <small>
                                <strong>Thông tin nhận tiền:</strong><br>
                                <?= nl2br(e($w['account_info'])) ?>
                                <?php if ($w['admin_note']): ?>
                                    <br><strong class="text-danger">Ghi chú admin:</strong> <?= e($w['admin_note']) ?>
                                <?php endif; ?>
                            </small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý rút tiền';
require_once __DIR__ . '/../layouts/admin.php';
?>
