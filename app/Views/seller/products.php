<?php 
ob_start();
?>

<div class="mb-3">
    <a href="<?= url('/seller/products/create') ?>" class="btn btn-success">
        <i class="fas fa-plus"></i> Thêm sản phẩm mới
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-box"></i> Sản phẩm của tôi</h5>
    </div>
    <div class="card-body">
        <?php if (empty($products)): ?>
            <p class="text-muted">Bạn chưa có sản phẩm nào</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
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
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?= asset($product['thumbnail'] ?? 'images/no-image.png') ?>" width="50" class="me-2">
                                <div>
                                    <strong><?= e($product['name']) ?></strong><br>
                                    <small class="text-muted"><?= e($product['category_name']) ?></small>
                                </div>
                            </div>
                        </td>
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
                            <a href="<?= url('/seller/products/stock/' . $product['id']) ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-warehouse"></i> Kho
                            </a>
                            <a href="<?= url('/seller/products/edit/' . $product['id']) ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
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
$pageTitle = 'Sản phẩm của tôi';
require_once __DIR__ . '/../layouts/seller.php';
?>
