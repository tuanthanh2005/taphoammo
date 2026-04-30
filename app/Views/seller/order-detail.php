<?php ob_start(); ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <a href="<?= url('/seller/orders') ?>" class="btn btn-outline-secondary btn-sm me-2">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <h2 class="d-inline-block mb-0">Chi tiết đơn hàng #<?= e($order['order_code']) ?></h2>
        </div>
        <span class="badge bg-success fs-6">Đã thanh toán</span>
    </div>
</div>

<div class="row g-4">
    <!-- Thông tin đơn hàng -->
    <div class="col-lg-8">

        <!-- Thông tin sản phẩm -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">
                <i class="fas fa-box text-primary"></i> Thông tin sản phẩm
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="<?= asset($order['thumbnail'] ?? 'images/no-image.png') ?>"
                         style="width:60px;height:60px;object-fit:cover;border-radius:8px;">
                    <div>
                        <div class="fw-bold fs-5"><?= e($order['product_name']) ?></div>
                        <div class="text-muted small">Loại: <?= e($order['product_type'] ?? 'key') ?></div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-sm-4">
                        <div class="bg-light rounded p-3 text-center">
                            <div class="text-muted small">Số lượng</div>
                            <div class="fw-bold fs-5"><?= $order['quantity'] ?></div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="bg-light rounded p-3 text-center">
                            <div class="text-muted small">Đơn giá</div>
                            <div class="fw-bold fs-5 text-primary"><?= money($order['price']) ?></div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="bg-success bg-opacity-10 rounded p-3 text-center">
                            <div class="text-muted small">Bạn nhận được</div>
                            <div class="fw-bold fs-5 text-success"><?= money($order['seller_amount']) ?></div>
                        </div>
                    </div>
                </div>
                <?php if (!empty($order['note'])): ?>
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="fas fa-comment-dots"></i> <strong>Ghi chú của khách:</strong>
                    <div class="mt-1"><?= nl2br(e($order['note'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mã hàng đã giao -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="fas fa-key text-warning"></i> Mã hàng đã giao cho khách (<?= count($stocks) ?> mã)</span>
                <?php if (!empty($stocks)): ?>
                <button class="btn btn-sm btn-outline-secondary" onclick="copyAllKeys()">
                    <i class="fas fa-copy"></i> Copy tất cả
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($stocks)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-exclamation-circle fs-3 d-block mb-2"></i>
                        Chưa có mã hàng nào được giao
                    </div>
                <?php else: ?>
                    <div id="allKeys" class="d-none">
                        <?php foreach ($stocks as $s): ?><?= $s['content'] ?>
<?php endforeach; ?>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Mã hàng / Key / Account</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stocks as $i => $stock): ?>
                                <tr>
                                    <td class="text-muted"><?= $i + 1 ?></td>
                                    <td>
                                        <code class="text-dark fw-bold"><?= e($stock['content']) ?></code>
                                    </td>
                                    <td><span class="badge bg-secondary">Đã giao</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Sidebar: Thông tin khách hàng & đơn hàng -->
    <div class="col-lg-4">

        <!-- Thông tin khách hàng -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">
                <i class="fas fa-user text-info"></i> Thông tin khách hàng
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold"
                         style="width:45px;height:45px;flex-shrink:0;">
                        <?= mb_strtoupper(mb_substr($order['buyer_name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div>
                        <div class="fw-bold"><?= e($order['buyer_name']) ?></div>
                        <div class="text-muted small">@<?= e($order['buyer_username']) ?></div>
                    </div>
                </div>
                <div class="small text-muted">
                    <i class="fas fa-envelope me-1"></i> <?= e($order['buyer_email']) ?>
                </div>
                <hr>
                <a href="<?= url('/seller/chat') ?>" class="btn btn-outline-primary btn-sm w-100">
                    <i class="fas fa-comment-dots"></i> Nhắn tin với khách
                </a>
            </div>
        </div>

        <!-- Cập nhật trạng thái -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">
                <i class="fas fa-edit text-warning"></i> Cập nhật trạng thái
            </div>
            <div class="card-body">
                <?php
                $statusMap = [
                    'processing' => ['warning',  'Đang xử lý'],
                    'delivered'  => ['success',  'Đã giao hàng'],
                    'issue'      => ['danger',   'Có vấn đề'],
                    'refunded'   => ['secondary','Đã hoàn tiền'],
                ];
                $cur = $order['item_status'] ?? 'processing';
                [$color, $label] = $statusMap[$cur];
                ?>
                <div class="mb-3 text-center">
                    <span class="badge bg-<?= $color ?> fs-6 px-3 py-2"><?= $label ?></span>
                    <?php if ($order['status_updated_at']): ?>
                    <div class="text-muted small mt-1">Cập nhật lúc: <?= date('d/m/Y H:i', strtotime($order['status_updated_at'])) ?></div>
                    <?php endif; ?>
                </div>

                <form action="<?= url('/seller/orders/' . $order['order_id'] . '/status') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Trạng thái mới</label>
                        <select name="item_status" class="form-select">
                            <option value="processing" <?= $cur=='processing'?'selected':'' ?>>⏳ Đang xử lý</option>
                            <option value="delivered"  <?= $cur=='delivered' ?'selected':'' ?>>✅ Đã giao hàng</option>
                            <option value="issue"      <?= $cur=='issue'     ?'selected':'' ?>>⚠️ Có vấn đề</option>
                            <option value="refunded"   <?= $cur=='refunded'  ?'selected':'' ?>>↩️ Đã hoàn tiền</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ghi chú cho khách <small class="text-muted">(tùy chọn)</small></label>
                        <textarea name="seller_note" class="form-control" rows="2"
                            placeholder="Ví dụ: Key đã được gửi, vui lòng kiểm tra..."><?= e($order['seller_note'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> Lưu trạng thái
                    </button>
                </form>
            </div>
        </div>
            <div class="card-header bg-white fw-bold">
                <i class="fas fa-receipt text-success"></i> Thông tin đơn hàng
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted">Mã đơn</td>
                        <td class="fw-bold">#<?= e($order['order_code']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Ngày đặt</td>
                        <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tổng đơn</td>
                        <td class="fw-bold"><?= money($order['subtotal']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Phí nền tảng</td>
                        <td class="text-danger">-<?= money($order['admin_fee_amount']) ?> (<?= $order['admin_fee_percent'] ?>%)</td>
                    </tr>
                    <tr class="table-success">
                        <td class="fw-bold">Bạn nhận</td>
                        <td class="fw-bold text-success"><?= money($order['seller_amount']) ?></td>
                    </tr>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
function copyAllKeys() {
    const text = document.getElementById('allKeys').innerText.trim();
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Đã copy!',
            text: 'Đã copy tất cả mã hàng vào clipboard',
            timer: 1500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    });
}
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Chi tiết đơn hàng';
require_once __DIR__ . '/../layouts/seller.php';
?>
