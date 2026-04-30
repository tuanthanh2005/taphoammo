<?php 
ob_start();
?>

<div class="mb-3">
    <form method="GET" class="row g-3">
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Tất cả trạng thái</option>
                <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                <option value="approved" <?= ($_GET['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                <option value="rejected" <?= ($_GET['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Từ chối</option>
                <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Đang bán</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Lọc</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-box"></i> Quản lý sản phẩm</h5>
    </div>
    <div class="card-body">
        <?php if (empty($products)): ?>
            <p class="text-muted">Chưa có sản phẩm nào</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sản phẩm</th>
                        <th>Seller</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Đã bán</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= $product['id'] ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?= asset($product['thumbnail'] ?? 'images/no-image.png') ?>" width="50" class="me-2">
                                <div>
                                    <strong><?= e($product['name']) ?></strong><br>
                                    <small class="text-muted"><?= e($product['category_name']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?= e($product['seller_name']) ?></td>
                        <td><?= money($product['price']) ?></td>
                        <td><span class="badge bg-info"><?= $product['stock_quantity'] ?></span></td>
                        <td><?= $product['total_sold'] ?></td>
                        <td>
                            <?php
                            $statusBadges = [
                                'pending' => '<span class="badge bg-warning">Chờ duyệt</span>',
                                'approved' => '<span class="badge bg-success">Đã duyệt</span>',
                                'rejected' => '<span class="badge bg-danger">Từ chối</span>',
                                'active' => '<span class="badge bg-success">Đang bán</span>',
                                'hidden' => '<span class="badge bg-secondary">Ẩn</span>'
                            ];
                            echo $statusBadges[$product['status']] ?? $product['status'];
                            ?>
                        </td>
                        <td>
                            <?php if ($product['status'] === 'pending'): ?>
                                <form action="<?= url('/admin/products/approve/' . $product['id']) ?>" method="POST" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Duyệt sản phẩm này?')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <form action="<?= url('/admin/products/reject/' . $product['id']) ?>" method="POST" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Từ chối sản phẩm này?')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">Đã xử lý</span>
                            <?php endif; ?>
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
$pageTitle = 'Quản lý sản phẩm';
require_once __DIR__ . '/../layouts/admin.php';
?>
