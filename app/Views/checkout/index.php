<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-credit-card"></i> Thanh toán</h2>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-box"></i> Thông tin đơn hàng</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Tổng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td><?= e($item['name']) ?></td>
                                <td><?= money($item['price']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td class="fw-bold"><?= money($item['subtotal']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                                <td class="fw-bold text-success fs-5"><?= money($total) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-wallet"></i> Phương thức thanh toán</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Hiện tại chỉ hỗ trợ thanh toán bằng ví nội bộ
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Số dư ví hiện tại:</span>
                        <strong class="fs-5 <?= $wallet['balance'] >= $total ? 'text-success' : 'text-danger' ?>">
                            <?= money($wallet['balance']) ?>
                        </strong>
                    </div>
                    
                    <?php if ($wallet['balance'] < $total): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> Số dư không đủ. Vui lòng nạp thêm tiền vào ví.
                        </div>
                    <?php else: ?>
                        <form action="<?= url('/checkout') ?>" method="POST">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-success btn-lg w-100" onclick="return confirm('Xác nhận thanh toán đơn hàng này?')">
                                <i class="fas fa-check-circle"></i> Xác nhận thanh toán
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Tóm tắt đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Số sản phẩm:</span>
                        <strong><?= count($cartItems) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tạm tính:</span>
                        <strong><?= money($total) ?></strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Tổng thanh toán:</strong>
                        <strong class="text-success fs-4"><?= money($total) ?></strong>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-warning mt-3">
                <small>
                    <i class="fas fa-info-circle"></i> 
                    Sau khi thanh toán thành công, bạn sẽ nhận được sản phẩm ngay lập tức trong mục "Đơn hàng của tôi"
                </small>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
