<?php require_once __DIR__ . '/../layouts/admin.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-shield-alt me-2"></i>Cảnh báo Spam Request
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Thời gian</th>
                                    <th>Người dùng / IP</th>
                                    <th>Loại</th>
                                    <th>Mô tả</th>
                                    <th>Số lượng</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($alerts)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Chưa có cảnh báo nào</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($alerts as $alert): ?>
                                        <tr>
                                            <td>
                                                <small class="text-muted d-block"><?= date('d/m/Y', strtotime($alert['created_at'])) ?></small>
                                                <strong><?= date('H:i:s', strtotime($alert['created_at'])) ?></strong>
                                            </td>
                                            <td>
                                                <?php if ($alert['user_id']): ?>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary-soft text-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width:32px;height:32px;background:#eef2ff;">
                                                            <?= strtoupper(substr($alert['user_name'], 0, 1)) ?>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold"><?= e($alert['user_name']) ?></div>
                                                            <small class="text-muted"><?= e($alert['user_email']) ?></small>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Khách</span>
                                                <?php endif; ?>
                                                <div class="mt-1 small text-muted">
                                                    <i class="fas fa-network-wired me-1"></i><?= e($alert['ip_address']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning-soft text-warning px-2 py-1" style="background:#fff7ed;">
                                                    <?= e($alert['type']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="small" style="max-width: 250px;"><?= e($alert['description']) ?></div>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-danger"><?= number_format($alert['request_count']) ?></span>
                                                <small class="text-muted">req/min</small>
                                            </td>
                                            <td>
                                                <?php if ($alert['banned_until'] && strtotime($alert['banned_until']) > time()): ?>
                                                    <span class="badge bg-danger rounded-pill">Đang bị khóa</span>
                                                    <div class="x-small mt-1 text-danger" style="font-size:0.7rem;">
                                                        Đến: <?= date('H:i d/m', strtotime($alert['banned_until'])) ?>
                                                    </div>
                                                <?php elseif ($alert['is_resolved']): ?>
                                                    <span class="badge bg-success rounded-pill">Đã xử lý</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info rounded-pill">Mới</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <?php if (!$alert['is_resolved']): ?>
                                                        <form action="<?= url('/admin/spam-alerts/resolve/' . $alert['id']) ?>" method="POST" class="d-inline">
                                                            <?= csrf_field() ?>
                                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Đánh dấu đã xử lý">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($alert['user_id']): ?>
                                                        <button type="button" class="btn btn-sm btn-danger" 
                                                                onclick="openBanModal(<?= $alert['user_id'] ?>, '<?= e($alert['user_name']) ?>')"
                                                                title="Khóa tạm thời">
                                                            <i class="fas fa-user-slash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ban Modal -->
<div class="modal fade" id="banModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title"><i class="fas fa-user-slash me-2"></i>Khóa tạm thời người dùng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('/admin/users/temp-ban') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" id="banUserId">
                <div class="modal-body p-4">
                    <p>Bạn đang thực hiện khóa tạm thời người dùng <strong id="banUserName"></strong> do có hành vi spam.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Thời gian khóa (phút)</label>
                        <select name="duration" class="form-select">
                            <option value="5">5 phút</option>
                            <option value="10" selected>10 phút (Mặc định)</option>
                            <option value="30">30 phút</option>
                            <option value="60">1 giờ</option>
                            <option value="180">3 giờ</option>
                            <option value="1440">24 giờ</option>
                            <option value="10080">7 ngày</option>
                            <option value="43200">30 ngày</option>
                        </select>
                        <div class="form-text mt-2 text-muted">
                            <i class="fas fa-info-circle me-1"></i>Người dùng sẽ không thể truy cập hệ thống cho đến khi hết thời gian khóa.
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Xác nhận khóa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openBanModal(userId, userName) {
    document.getElementById('banUserId').value = userId;
    document.getElementById('banUserName').textContent = userName;
    new bootstrap.Modal(document.getElementById('banModal')).show();
}
</script>
