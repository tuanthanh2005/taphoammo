<?php 
ob_start();
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-qrcode"></i> Quản lý yêu cầu nạp tiền</h5>
    </div>
    <div class="card-body">
        <?php if (empty($depositRequests)): ?>
            <p class="text-muted mb-0">Chưa có yêu cầu nạp tiền nào</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Người dùng</th>
                            <th>Số tiền</th>
                            <th>Nội dung CK</th>
                            <th>Ngân hàng</th>
                            <th>Ngày tạo</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($depositRequests as $item): ?>
                            <tr>
                                <td>#<?= (int)$item['id'] ?></td>
                                <td>
                                    <strong><?= e($item['user_name']) ?></strong><br>
                                    <small class="text-muted"><?= e($item['user_email']) ?></small>
                                </td>
                                <td class="fw-bold text-success"><?= money($item['amount']) ?></td>
                                <td><code><?= e($item['transfer_code']) ?></code></td>
                                <td>
                                    <strong><?= e($item['bank_name']) ?></strong><br>
                                    <small class="text-muted"><?= e($item['account_number']) ?></small>
                                </td>
                                <td><?= Helper::formatDate($item['created_at']) ?></td>
                                <td>
                                    <?php if ($item['status'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                    <?php elseif ($item['status'] === 'approved'): ?>
                                        <span class="badge bg-success">Đã duyệt</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Từ chối</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($item['status'] === 'pending'): ?>
                                        <form action="<?= url('/admin/deposits/approve/' . $item['id']) ?>" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="admin_note" value="Đã duyệt nạp tiền">
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Duyệt yêu cầu nạp tiền này?')">
                                                <i class="fas fa-check"></i> Duyệt
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectDepositModal<?= $item['id'] ?>">
                                            <i class="fas fa-times"></i> Từ chối
                                        </button>

                                        <div class="modal fade" id="rejectDepositModal<?= $item['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="<?= url('/admin/deposits/reject/' . $item['id']) ?>" method="POST">
                                                        <?= csrf_field() ?>
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Từ chối yêu cầu nạp tiền</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <label class="form-label">Lý do từ chối</label>
                                                            <textarea name="reason" class="form-control" rows="3" required></textarea>
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
                            <?php if (!empty($item['admin_note'])): ?>
                                <tr>
                                    <td colspan="8" class="bg-light">
                                        <small><strong>Ghi chú admin:</strong> <?= e($item['admin_note']) ?></small>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Yêu cầu nạp tiền';
require_once __DIR__ . '/../layouts/admin.php';
?>
