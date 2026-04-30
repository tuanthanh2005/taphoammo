<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">Quản lý người bán (Sellers)</h2>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Seller</th>
                            <th>Sản phẩm</th>
                            <th>Đơn hàng</th>
                            <th>Số dư ví</th>
                            <th>Tổng thu nhập</th>
                            <th>Đã rút</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sellers)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">Không có dữ liệu</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sellers as $seller): ?>
                                <tr>
                                    <td>#<?= $seller['id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="mb-0"><?= e($seller['name']) ?></h6>
                                                <small class="text-muted"><?= e($seller['email']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-info rounded-pill"><?= $seller['total_products'] ?></span></td>
                                    <td><span class="badge bg-secondary rounded-pill"><?= $seller['total_orders'] ?></span></td>
                                    <td class="fw-bold text-success"><?= money($seller['balance'] ?? 0) ?></td>
                                    <td class="text-primary"><?= money($seller['total_earned'] ?? 0) ?></td>
                                    <td class="text-warning"><?= money($seller['total_withdrawn'] ?? 0) ?></td>
                                    <td>
                                        <?php if ($seller['status'] === 'active'): ?>
                                            <span class="badge bg-success">Hoạt động</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Bị khóa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($seller['id'] !== $_SESSION['user_id']): ?>
                                        <form action="<?= url('/admin/users/toggle-status/' . $seller['id']) ?>" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <?php if ($seller['status'] === 'active'): ?>
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
        <?php if (!empty($sellers) && count($sellers) == 50): ?>
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
$pageTitle = 'Quản lý người bán';
require_once __DIR__ . '/../layouts/admin.php';
?>
