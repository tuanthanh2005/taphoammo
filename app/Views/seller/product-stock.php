<?php 
ob_start();
$isBalanceLow = false; // Không còn bắt buộc minimum balance
?>

<?php if ($isBalanceLow): ?>
<div class="alert alert-danger">
    <h5><i class="fas fa-exclamation-triangle"></i> Không thể nhập stock!</h5>
    <p class="mb-2">Tài khoản của bạn chưa đủ <?= money($minBalance) ?> tối thiểu.</p>
    <p class="mb-2"><strong>Số dư hiện tại:</strong> <?= money($currentBalance) ?></p>
    <p class="mb-3"><strong>Cần nạp thêm:</strong> <span class="text-danger fw-bold"><?= money($minBalance - $currentBalance) ?></span></p>
    <a href="<?= url('/seller/wallet') ?>" class="btn btn-warning">
        <i class="fas fa-wallet"></i> Nạp tiền ngay
    </a>
</div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5><?= e($product['name']) ?></h5>
                <p class="text-muted mb-0">
                    Tồn kho: <strong class="text-success"><?= $product['stock_quantity'] ?></strong> |
                    Đã bán: <strong><?= $product['total_sold'] ?></strong>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-upload"></i> Nhập kho hàng</h5>
            </div>
            <div class="card-body">
                <form action="<?= url('/seller/products/stock/import') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Nhập key/account (mỗi dòng 1 mã)</label>
                        <textarea name="stock_content" class="form-control" rows="10" required placeholder="key1&#10;key2&#10;key3&#10;..."></textarea>
                        <small class="text-muted">Mỗi dòng là một key/account riêng biệt</small>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100" <?= $isBalanceLow ? 'disabled' : '' ?>>
                        <i class="fas fa-upload"></i> Nhập kho
                    </button>
                    <?php if ($isBalanceLow): ?>
                    <small class="text-danger d-block mt-2">
                        <i class="fas fa-lock"></i> Cần nạp đủ <?= money($minBalance) ?> để nhập kho
                    </small>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list"></i> Danh sách kho hàng</h5>
            </div>
            <div class="card-body">
                <?php if (empty($stocks)): ?>
                    <p class="text-muted">Chưa có kho hàng. Vui lòng nhập kho.</p>
                <?php else: ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nội dung</th>
                                <th>Trạng thái</th>
                                <th>Ngày nhập</th>
                                <th>Ngày bán</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stocks as $stock): ?>
                            <tr>
                                <td><?= $stock['id'] ?></td>
                                <td>
                                    <?php if ($stock['status'] === 'available'): ?>
                                        <code><?= e($stock['content']) ?></code>
                                    <?php else: ?>
                                        <span class="text-muted">***ĐÃ BÁN***</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($stock['status'] === 'available'): ?>
                                        <span class="badge bg-success">Còn hàng</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Đã bán</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= Helper::formatDate($stock['created_at']) ?></td>
                                <td>
                                    <?php if ($stock['sold_at']): ?>
                                        <?= Helper::formatDate($stock['sold_at']) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="<?= url('/seller/products') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại danh sách sản phẩm
    </a>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý kho hàng';
require_once __DIR__ . '/../layouts/seller.php';
?>
