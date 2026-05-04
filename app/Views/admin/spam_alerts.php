<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-shield-alt me-2 text-danger"></i>Cảnh báo Spam Request
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted small fw-bold">
                                    <th class="ps-4">Thời gian</th>
                                    <th>Người dùng / IP</th>
                                    <th>Loại vi phạm</th>
                                    <th>Chi tiết</th>
                                    <th>Tốc độ</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end pe-4">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($alerts)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fas fa-check-circle fa-2x mb-3 opacity-25"></i><br>
                                            Chưa có cảnh báo nào cần xử lý
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($alerts as $alert): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <small class="text-muted d-block"><?= date('d/m/Y', strtotime($alert['created_at'])) ?></small>
                                                <strong class="text-dark"><?= date('H:i:s', strtotime($alert['created_at'])) ?></strong>
                                            </td>
                                            <td>
                                                <?php if ($alert['user_id']): ?>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary-subtle text-primary rounded-circle me-2 d-flex align-items-center justify-content-center fw-bold" style="width:32px;height:32px;background:rgba(99,102,241,0.1);">
                                                            <?= strtoupper(substr($alert['user_name'], 0, 1)) ?>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold small"><?= e($alert['user_name']) ?></div>
                                                            <small class="text-muted" style="font-size:0.75rem;"><?= e($alert['user_email']) ?></small>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2" style="font-size:0.7rem;">Khách</span>
                                                <?php endif; ?>
                                                <div class="mt-1 small text-muted">
                                                    <code class="text-primary" style="font-size:0.75rem;"><?= e($alert['ip_address']) ?></code>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning-subtle text-warning px-2 py-1 rounded-pill" style="font-size:0.7rem;background:rgba(255,193,7,0.1);">
                                                    <?= e($alert['type']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="small text-muted" style="max-width: 200px;"><?= e($alert['description']) ?></div>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-danger"><?= number_format($alert['request_count']) ?></span>
                                                <small class="text-muted x-small">req/min</small>
                                            </td>
                                            <td>
                                                <?php if ($alert['banned_until'] && strtotime($alert['banned_until']) > time()): ?>
                                                    <span class="badge bg-danger rounded-pill">Đang bị khóa</span>
                                                    <div class="text-danger mt-1" style="font-size:0.65rem;">
                                                        Mở lúc: <?= date('H:i d/m', strtotime($alert['banned_until'])) ?>
                                                    </div>
                                                <?php elseif ($alert['is_resolved']): ?>
                                                    <span class="badge bg-success-subtle text-success rounded-pill">Đã xử lý</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info-subtle text-info rounded-pill">Mới</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <?php if (!$alert['is_resolved']): ?>
                                                        <form action="<?= url('/admin/spam-alerts/resolve/' . $alert['id']) ?>" method="POST">
                                                            <?= csrf_field() ?>
                                                            <button type="submit" class="btn btn-sm btn-light rounded-circle" title="Đánh dấu đã xử lý">
                                                                <i class="fas fa-check text-success"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($alert['user_id']): ?>
                                                        <button type="button" class="btn btn-sm btn-light rounded-circle" 
                                                                onclick="openBanModal(<?= $alert['user_id'] ?>, '<?= e($alert['user_name']) ?>')"
                                                                title="Khóa tạm thời">
                                                            <i class="fas fa-user-slash text-danger"></i>
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
                        <div class="card-footer bg-white border-0 py-3">
                            <nav>
                                <ul class="pagination pagination-sm justify-content-center mb-0">
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                            <a class="page-link rounded-circle mx-1" href="?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ban Modal -->
<div class="modal fade" id="banModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger"><i class="fas fa-user-slash me-2"></i>Khóa tạm thời người dùng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('/admin/users/temp-ban') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" id="banUserId">
                <div class="modal-body p-4">
                    <p class="text-muted">Bạn đang thực hiện khóa người dùng <strong id="banUserName" class="text-dark"></strong>. Họ sẽ bị chặn truy cập trong thời gian quy định.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Thời gian khóa</label>
                        <select name="duration" class="form-select rounded-pill">
                            <option value="5">5 phút</option>
                            <option value="10" selected>10 phút (Mặc định)</option>
                            <option value="30">30 phút</option>
                            <option value="60">1 giờ</option>
                            <option value="180">3 giờ</option>
                            <option value="1440">24 giờ (1 ngày)</option>
                            <option value="10080">7 ngày</option>
                            <option value="43200">30 ngày</option>
                        </select>
                    </div>
                    <div class="p-3 bg-light rounded-4 small text-muted">
                        <i class="fas fa-info-circle me-1"></i> Sau khi hết thời gian khóa, người dùng sẽ tự động được mở quyền truy cập hệ thống.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
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

<?php
$content = ob_get_clean();
$pageTitle = 'Cảnh báo Spam Request';
require_once __DIR__ . '/../layouts/admin.php';
?>
