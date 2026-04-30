<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-shopping-cart"></i> Giỏ hàng của bạn</h2>
    
    <?php if (empty($cartItems)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Giỏ hàng trống. <a href="<?= url('/products') ?>">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Giá</th>
                                    <th>Số lượng</th>
                                    <th>Tổng</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= asset($item['thumbnail'] ?? 'images/no-image.png') ?>" width="60" class="me-3">
                                            <div>
                                                <a href="<?= url('/product/' . $item['slug']) ?>"><?= e($item['name']) ?></a>
                                                <br><small class="text-muted">Còn: <?= $item['stock_quantity'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= money($item['price']) ?></td>
                                    <td>
                                        <form action="<?= url('/cart/update') ?>" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock_quantity'] ?>" class="form-control form-control-sm" style="width: 80px;" onchange="this.form.submit()">
                                        </form>
                                    </td>
                                    <td class="fw-bold"><?= money($item['subtotal']) ?></td>
                                    <td>
                                        <form action="<?= url('/cart/remove') ?>" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Xóa sản phẩm này?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Tổng đơn hàng</h5>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tạm tính:</span>
                            <strong><?= money($total) ?></strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Tổng cộng:</strong>
                            <strong class="text-success fs-4"><?= money($total) ?></strong>
                        </div>
                        <a href="<?= url('/checkout') ?>" class="btn btn-success w-100 btn-lg">
                            <i class="fas fa-credit-card"></i> Thanh toán
                        </a>
                        <a href="<?= url('/products') ?>" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
