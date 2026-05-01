<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">Quan ly nguoi dung</h2>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Tim ten, email..." value="<?= e($_GET['search'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select">
                        <option value="">Tat ca vai tro</option>
                        <option value="user" <?= (isset($_GET['role']) && $_GET['role'] === 'user') ? 'selected' : '' ?>>User</option>
                        <option value="seller" <?= (isset($_GET['role']) && $_GET['role'] === 'seller') ? 'selected' : '' ?>>Seller</option>
                        <option value="affiliate" <?= (isset($_GET['role']) && $_GET['role'] === 'affiliate') ? 'selected' : '' ?>>Affiliate</option>
                        <option value="admin" <?= (isset($_GET['role']) && $_GET['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Loc</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nguoi dung</th>
                            <th>Vai tro</th>
                            <th>So du</th>
                            <th>Trang thai</th>
                            <th>Ngay tham gia</th>
                            <th>Hanh dong</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">Khong co du lieu</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>#<?= $user['id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="ms-3">
                                                <h6 class="mb-0"><?= e($user['name']) ?></h6>
                                                <small class="text-muted"><?= e($user['email']) ?></small>
                                                <?php if ($user['is_seller_requested']): ?>
                                                    <div class="mt-1"><span class="badge bg-warning text-dark" style="font-size: 0.65rem;">Cho duyet Seller</span></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <form action="<?= url('/admin/users/update-role/' . $user['id']) ?>" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                                <option value="seller" <?= $user['role'] === 'seller' ? 'selected' : '' ?>>Seller</option>
                                                <option value="affiliate" <?= $user['role'] === 'affiliate' ? 'selected' : '' ?>>Affiliate</option>
                                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="fw-bold text-primary"><?= money($user['wallet_balance'] ?? 0) ?></td>
                                    <td>
                                        <?php if ($user['status'] === 'active'): ?>
                                            <span class="badge bg-success">Hoat dong</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Bi khoa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                                <form action="<?= url('/admin/users/toggle-status/' . $user['id']) ?>" method="POST" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <?php if ($user['status'] === 'active'): ?>
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Khoa" onclick="return confirm('Ban co chac chan muon khoa tai khoan nay?')"><i class="fas fa-lock"></i></button>
                                                    <?php else: ?>
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Mo khoa" onclick="return confirm('Ban co chac chan muon mo khoa tai khoan nay?')"><i class="fas fa-unlock"></i></button>
                                                    <?php endif; ?>
                                                </form>
                                            <?php endif; ?>

                                            <?php if (empty($user['google_id'])): ?>
                                                <button type="button" class="btn btn-sm btn-outline-primary ms-1" title="Reset Password"
                                                        onclick="resetPassword(<?= $user['id'] ?>, '<?= e($user['name']) ?>')">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                            <?php endif; ?>

                                            <?php if ($user['is_seller_requested']): ?>
                                                <div class="d-flex gap-1 ms-1">
                                                    <form action="<?= url('/admin/users/approve-seller') ?>" method="POST" class="d-inline">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-success" title="Duyet lam Seller" onclick="return confirm('Xac nhan duyet nguoi dung nay lam Nha ban hang?')">
                                                            <i class="fas fa-check"></i> Duyet
                                                        </button>
                                                    </form>
                                                    <form action="<?= url('/admin/users/reject-seller') ?>" method="POST" class="d-inline">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Tu choi yeu cau" onclick="return confirm('Ban co chac chan muon tu choi yeu cau nay?')">
                                                            <i class="fas fa-times"></i> Tu choi
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (!empty($users) && count($users) == 50): ?>
        <div class="card-footer bg-white border-top-0">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-end mb-0">
                    <?php if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $currentPage - 1 ?>">Truoc</a>
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

<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="resetPasswordForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Reset mat khau: <span id="resetUserName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Mat khau moi</label>
                        <input type="text" name="password" class="form-control" required minlength="6" placeholder="Nhap mat khau moi">
                        <small class="text-muted">Mat khau toi thieu 6 ky tu.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huy</button>
                    <button type="submit" class="btn btn-primary">Xac nhan Reset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetPassword(userId, userName) {
    const modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    document.getElementById('resetUserName').textContent = userName;
    document.getElementById('resetPasswordForm').action = '<?= url('/admin/users/reset-password/') ?>' + userId;
    modal.show();
}
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Quan ly nguoi dung';
require_once __DIR__ . '/../layouts/admin.php';
?>
