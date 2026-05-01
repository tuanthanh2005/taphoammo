<?php ob_start(); ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-danger fw-bold"><i class="fas fa-user-slash me-2"></i> Khách hàng có dấu hiệu Spam</h5>
        <form action="" method="GET" class="d-flex" style="width: 300px;">
            <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Tìm tên, email..." value="<?= e($search) ?>">
            <button type="submit" class="btn btn-sm btn-primary">Tìm</button>
        </form>
    </div>
    <div class="card-body">
        <div class="alert alert-warning small">
            <i class="fas fa-info-circle me-2"></i> Danh sách này hiển thị những khách hàng có từ <b>3 lần nạp tiền bị từ chối</b> trở lên. Đây là dấu hiệu của việc cố tình spam yêu cầu nạp tiền giả mạo.
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th class="text-center">Tổng cảnh báo</th>
                        <th>Loại vi phạm</th>
                        <th>Lần cuối vi phạm</th>
                        <th>Trạng thái hiện tại</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Không có khách hàng nào nằm trong danh sách spam.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td>
                                    <div class="fw-bold"><?= e($user['name']) ?></div>
                                    <div class="small text-muted"><?= e($user['email']) ?></div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger fs-6"><?= $user['total_alerts'] ?></span>
                                </td>
                                <td>
                                    <?php 
                                    $types = explode(',', $user['alert_types']);
                                    foreach ($types as $type) {
                                        $label = $type === 'deposit_rejected' ? 'Nạp lỗi' : ($type === 'rate_limit_exceeded' ? 'Spam Request' : $type);
                                        $color = $type === 'deposit_rejected' ? 'warning' : 'danger';
                                        echo "<span class='badge bg-{$color} me-1 small'>{$label}</span>";
                                    }
                                    ?>
                                </td>
                                <td class="small text-muted">
                                    <?= date('d/m/Y H:i', strtotime($user['last_alert_at'])) ?>
                                </td>
                                <td>
                                    <?php if ($user['status'] === 'active'): ?>
                                        <span class="badge bg-success">Đang hoạt động</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Bị khóa</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <form action="<?= url('/admin/users/' . $user['id'] . '/toggle-status') ?>" method="POST" class="d-inline" onsubmit="return confirm('<?= $user['status'] === 'active' ? 'Bạn có chắc chắn muốn KHÓA tài khoản này?' : 'Mở khóa tài khoản này?' ?>')">
                                        <?= csrf_field() ?>
                                        <?php if ($user['status'] === 'active'): ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-lock me-1"></i> Khóa tài khoản
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-unlock me-1"></i> Mở khóa
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                    <a href="<?= url('/admin/deposits?user_id=' . $user['id']) ?>" class="btn btn-sm btn-outline-primary ms-1" title="Xem lịch sử nạp">
                                        <i class="fas fa-history"></i>
                                    </a>
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
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Khách hàng spam';
require_once __DIR__ . '/../layouts/admin.php';
?>
