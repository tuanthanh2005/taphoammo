<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">Quan ly nguoi ban</h2>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form action="<?= url('/admin/sellers') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Tìm tên hoặc email seller..." value="<?= e($search ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                </div>
                <?php if (!empty($search)): ?>
                    <div class="col-md-1">
                        <a href="<?= url('/admin/sellers') ?>" class="btn btn-outline-secondary w-100"><i class="fas fa-times"></i></a>
                    </div>
                <?php endif; ?>
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
                            <th>Seller</th>
                            <th>San pham</th>
                            <th>Don hang</th>
                            <th>Dang xu ly</th>
                            <th>So du vi</th>
                            <th>Gioi han</th>
                            <th>Trang thai</th>
                            <th>Hanh dong</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sellers)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">Không có dữ liệu</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sellers as $seller): ?>
                                <?php $openOrderItems = (int)($seller['open_order_items'] ?? 0); ?>
                                <tr>
                                    <td>#<?= $seller['id'] ?></td>
                                    <td>
                                        <div>
                                            <h6 class="mb-0"><?= e($seller['name']) ?></h6>
                                            <small class="text-muted"><?= e($seller['email']) ?></small>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-info rounded-pill"><?= $seller['total_products'] ?></span></td>
                                    <td><span class="badge bg-secondary rounded-pill"><?= $seller['total_orders'] ?></span></td>
                                    <td>
                                        <?php if ($openOrderItems > 0): ?>
                                            <span class="badge bg-warning text-dark rounded-pill"><?= $openOrderItems ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success rounded-pill">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-success"><?= money($seller['balance'] ?? 0) ?></td>
                                    <td>
                                        <form action="<?= url('/admin/sellers/update-limit/' . $seller['id']) ?>" method="POST" class="d-flex align-items-center" style="max-width: 150px;">
                                            <?= csrf_field() ?>
                                            <input type="number" name="max_products" class="form-control form-control-sm me-1" value="<?= $seller['max_products'] ?? 10 ?>" min="1" style="width: 70px;">
                                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i></button>
                                        </form>
                                    </td>
                                    <td>
                                        <?php if ($seller['status'] === 'active'): ?>
                                            <span class="badge bg-success">Hoat dong</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Bi khoa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($seller['id'] !== $_SESSION['user_id']): ?>
                                            <?php if ($seller['status'] === 'active'): ?>
                                                <form action="<?= url('/admin/sellers/toggle-status/' . $seller['id']) ?>" method="POST" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Khoa" onclick="return confirm('Chi khoa seller khi da xu ly xong het don hang. Tiep tuc?')"><i class="fas fa-lock"></i></button>
                                                </form>
                                                <?php if ($openOrderItems > 0): ?>
                                                    <form action="<?= url('/admin/sellers/refund-and-ban/' . $seller['id']) ?>" method="POST" class="d-inline">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="admin_note" value="Admin hoan tien toan bo don dang xu ly truoc khi khoa seller.">
                                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Hoan tien va khoa" onclick="return confirm('Seller nay con <?= $openOrderItems ?> don/san pham dang xu ly. He thong se hoan tien tat ca roi khoa tai khoan. Tiep tuc?')"><i class="fas fa-undo"></i></button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <form action="<?= url('/admin/sellers/toggle-status/' . $seller['id']) ?>" method="POST" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Mo khoa" onclick="return confirm('Ban co chac chan muon mo khoa tai khoan nay?')"><i class="fas fa-unlock"></i></button>
                                                </form>
                                            <?php endif; ?>
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

<?php
$content = ob_get_clean();
$pageTitle = 'Quan ly nguoi ban';
require_once __DIR__ . '/../layouts/admin.php';
?>
