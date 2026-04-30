<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-box"></i> Chi tiết đơn hàng #<?= e($order['order_code']) ?></h2>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Thông tin đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Mã đơn hàng:</strong> <?= e($order['order_code']) ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Ngày đặt:</strong> <?= Helper::formatDate($order['created_at']) ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Trạng thái thanh toán:</strong>
                            <?php if ($order['payment_status'] === 'paid'): ?>
                                <span class="badge bg-success">Đã thanh toán</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Chờ thanh toán</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Trạng thái đơn:</strong>
                            <?php if ($order['order_status'] === 'completed'): ?>
                                <span class="badge bg-success">Hoàn thành</span>
                            <?php else: ?>
                                <span class="badge bg-info">Đang xử lý</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Sản phẩm đã mua</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($order['items'] as $item): ?>
                    <div class="border-bottom pb-3 mb-3">
                        <div class="row">
                            <div class="col-md-8">
                                <h6><?= e($item['product_name']) ?></h6>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-user"></i> Seller: <?= e($item['seller_name']) ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Giá:</strong> <?= money($item['price']) ?> x <?= $item['quantity'] ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <strong class="text-success"><?= money($item['subtotal']) ?></strong>
                            </div>
                        </div>
                        
                        <?php if ($order['payment_status'] === 'paid' && !empty($item['stocks'])): ?>
                        <div class="alert alert-success mt-3">
                            <strong><i class="fas fa-key"></i> Nội dung sản phẩm:</strong>
                            <div class="mt-2">
                                <?php foreach ($item['stocks'] as $stock): ?>
                                <div class="bg-white p-2 rounded mb-2 text-dark">
                                    <code><?= e($stock['content']) ?></code>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php
                        $statusMap = [
                            'processing' => ['warning',  '⏳ Đang xử lý'],
                            'delivered'  => ['success',  '✅ Đã giao hàng'],
                            'issue'      => ['danger',   '⚠️ Có vấn đề - Liên hệ seller'],
                            'refunded'   => ['secondary','↩️ Đã hoàn tiền'],
                        ];
                        $itemStatus = $item['item_status'] ?? 'processing';
                        [$sColor, $sLabel] = $statusMap[$itemStatus] ?? ['info', $itemStatus];
                        ?>
                        <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                            <span class="fw-bold small">Trạng thái từ seller:</span>
                            <span class="badge bg-<?= $sColor ?> px-3 py-2"><?= $sLabel ?></span>
                            <?php if ($item['status_updated_at']): ?>
                            <small class="text-muted">· <?= date('d/m/Y H:i', strtotime($item['status_updated_at'])) ?></small>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($item['seller_note'])): ?>
                        <div class="alert alert-info mt-2 py-2 mb-0">
                            <i class="fas fa-comment-dots"></i> <strong>Ghi chú từ seller:</strong>
                            <?= nl2br(e($item['seller_note'])) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Tổng đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tạm tính:</span>
                        <strong><?= money($order['total_amount']) ?></strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Tổng cộng:</strong>
                        <strong class="text-success fs-4"><?= money($order['total_amount']) ?></strong>
                    </div>
                </div>
            </div>
            
            <a href="<?= url('/user/orders') ?>" class="btn btn-outline-secondary w-100 mt-3">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
