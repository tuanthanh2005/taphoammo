<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">Quản lý người dùng</h2>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Tìm tên, email..." value="<?= e($_GET['search'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select">
                        <option value="">Tất cả vai trò</option>
                        <option value="user" <?= (isset($_GET['role']) && $_GET['role'] === 'user') ? 'selected' : '' ?>>User</option>
                        <option value="seller" <?= (isset($_GET['role']) && $_GET['role'] === 'seller') ? 'selected' : '' ?>>Seller</option>
                        <option value="affiliate" <?= (isset($_GET['role']) && $_GET['role'] === 'affiliate') ? 'selected' : '' ?>>Affiliate</option>
                        <option value="admin" <?= (isset($_GET['role']) && $_GET['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Lọc</button>
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
                            <th>Người dùng</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Ngày tham gia</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">Không có dữ liệu</td>
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
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $roleColors = [
                                            'admin' => 'danger',
                                            'seller' => 'info',
                                            'affiliate' => 'warning',
                                            'user' => 'secondary'
                                        ];
                                        $color = $roleColors[$user['role']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $color ?> text-uppercase"><?= $user['role'] ?></span>
                                    </td>
                                    <td>
                                        <?php if ($user['status'] === 'active'): ?>
                                            <span class="badge bg-success">Hoạt động</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Bị khóa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                        <form action="<?= url('/admin/users/toggle-status/' . $user['id']) ?>" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <?php if ($user['status'] === 'active'): ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Khóa" onclick="return confirm('Bạn có chắc chắn muốn khóa tài khoản này?')"><i class="fas fa-lock"></i></button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Mở khóa" onclick="return confirm('Bạn có chắc chắn muốn mở khóa tài khoản này?')"><i class="fas fa-unlock"></i></button>
                                            <?php endif; ?>
                                        </form>
                                        <?php endif; ?>
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
$pageTitle = 'Quản lý người dùng';
require_once __DIR__ . '/../layouts/admin.php';
?>
