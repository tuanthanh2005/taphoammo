<?php ob_start(); ?>
<?php $responseHours = Helper::getDisputeSellerResponseHours(); ?>

<div class="row align-items-center mb-4 gy-3">
    <div class="col-12 col-md-auto me-auto text-center text-md-start">
        <h2 class="mb-1 fw-bold">Khiếu nại từ khách hàng</h2>
        <p class="text-muted small mb-0">Phản hồi rõ ràng và gửi bằng chứng để admin xem xét.</p>
    </div>
    <div class="col-12 col-md-auto text-center">
        <a href="<?= url('/seller/orders') ?>" class="btn btn-outline-primary btn-sm px-3">
            <i class="fas fa-shopping-cart me-1"></i> Quản lý đơn hàng
        </a>
    </div>
</div>

<div class="alert alert-warning border-0 shadow-sm mb-4">
    <div class="d-flex align-items-start">
        <i class="fas fa-shield-alt fa-lg me-3 mt-1"></i>
        <div>
            <h6 class="alert-heading fw-bold mb-1">Quy tắc xử lý khiếu nại</h6>
            <p class="mb-1 small">Buyer mở khiếu nại không đồng nghĩa seller thua. Tiền chỉ bị giữ tạm, admin sẽ xem bằng chứng của cả hai phía rồi mới quyết định.</p>
            <p class="mb-0 small">Nên phản hồi trong khoảng <?= (int)$responseHours ?> giờ để tránh thiếu dữ liệu khi admin xem xét.</p>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="min-width: 120px;">Khiếu nại / Đơn</th>
                        <th style="min-width: 140px;">Sản phẩm / Người mua</th>
                        <th style="min-width: 90px;">Số tiền</th>
                        <th style="min-width: 100px;">Trạng thái</th>
                        <th style="min-width: 100px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($disputes)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-balance-scale fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">Chưa có khách hàng nào khiếu nại.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($disputes as $d): ?>
                    <?php
                    $reasonMap = [
                        'not_received' => 'Chưa nhận được hàng',
                        'wrong_item' => 'Sai sản phẩm',
                        'not_working' => 'Sản phẩm lỗi/không hoạt động',
                        'scam' => 'Lừa đảo',
                        'other' => 'Lý do khác'
                    ];
                    $statusMap = [
                        'open' => ['danger', 'Mới mở'],
                        'under_review' => ['warning', 'Đang xem xét'],
                        'resolved_refund' => ['success', 'Đã hoàn tiền'],
                        'resolved_partial' => ['info', 'Hoàn một phần'],
                        'resolved_rejected' => ['secondary', 'Seller đúng'],
                        'closed' => ['dark', 'Đã đóng']
                    ];
                    [$color, $label] = $statusMap[$d['status']] ?? ['primary', $d['status']];
                    $buyerImages = json_decode($d['evidence_images'] ?? '[]', true) ?: [];
                    $sellerImages = json_decode($d['seller_evidence_images'] ?? '[]', true) ?: [];
                    $canRespond = in_array($d['status'], ['open', 'under_review'], true);
                    ?>
                    <tr>
                        <td class="ps-3">
                            <div class="fw-bold text-primary">#<?= e($d['dispute_code']) ?></div>
                            <div class="small">
                                <a href="<?= url('/seller/orders/' . $d['order_id']) ?>" class="text-decoration-none text-muted">
                                    Đơn: #<?= e($d['order_code']) ?>
                                </a>
                            </div>
                            <div class="text-muted" style="font-size: 0.65rem;"><?= date('d/m/y H:i', strtotime($d['created_at'])) ?></div>
                        </td>
                        <td>
                            <div class="fw-semibold text-truncate" style="max-width: 130px;"><?= e($d['product_name'] ?? 'Sản phẩm') ?></div>
                            <div class="small text-muted" style="font-size: 0.7rem;">
                                <i class="fas fa-user me-1"></i><?= e($d['user_name']) ?> (@<?= e($d['user_username']) ?>)
                            </div>
                        </td>
                        <td class="fw-bold text-danger" style="font-size: 0.85rem;"><?= money($d['amount']) ?></td>
                        <td>
                            <span class="badge bg-<?= $color ?> d-block mb-1 py-1" style="font-size: 0.65rem;"><?= $label ?></span>
                            <?php if (!empty($d['seller_responded_at'])): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-1" style="font-size: 0.6rem;">Đã phản hồi</span>
                            <?php else: ?>
                                <span class="badge bg-light text-dark border px-1" style="font-size: 0.6rem;">Chưa phản hồi</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm <?= $canRespond ? 'btn-primary' : 'btn-outline-secondary' ?> w-100 py-1" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#disputeSellerModal<?= $d['id'] ?>">
                                <?= $canRespond ? 'Phản hồi' : 'Chi tiết' ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php foreach ($disputes as $d): ?>
<?php
$buyerImages = json_decode($d['evidence_images'] ?? '[]', true) ?: [];
$sellerImages = json_decode($d['seller_evidence_images'] ?? '[]', true) ?: [];
$canRespond = in_array($d['status'], ['open', 'under_review'], true);
$reasonMap = [
    'not_received' => 'Chưa nhận được hàng',
    'wrong_item' => 'Sai sản phẩm',
    'not_working' => 'Sản phẩm lỗi/không hoạt động',
    'scam' => 'Lừa đảo',
    'other' => 'Lý do khác'
];
?>
<div class="modal fade" id="disputeSellerModal<?= $d['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1">Khiếu nại #<?= e($d['dispute_code']) ?></h5>
                    <div class="small text-muted">Đơn #<?= e($d['order_code']) ?> · <?= e($d['product_name'] ?? 'Sản phẩm') ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <div class="small text-uppercase fw-bold text-muted mb-2">Phía người mua</div>
                    <div class="p-3 rounded-4 bg-light border">
                        <div class="fw-semibold mb-2"><?= e($reasonMap[$d['reason']] ?? 'Khác') ?></div>
                        <div class="small"><?= nl2br(e($d['description'])) ?></div>
                        <?php if (!empty($buyerImages)): ?>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <?php foreach ($buyerImages as $img): ?>
                                    <a href="<?= asset($img) ?>" target="_blank" class="text-decoration-none">
                                        <img src="<?= asset($img) ?>" alt="Buyer evidence" style="width: 88px; height: 88px; object-fit: cover;" class="rounded-3 border">
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($d['seller_responded_at'])): ?>
                <div class="mb-4">
                    <div class="small text-uppercase fw-bold text-muted mb-2">Phản hồi seller hiện tại</div>
                    <div class="p-3 rounded-4 border bg-success-subtle">
                        <div class="small mb-2"><?= nl2br(e($d['seller_response'] ?? '')) ?></div>
                        <div class="small text-muted">Cập nhật lúc <?= date('d/m/Y H:i', strtotime($d['seller_responded_at'])) ?></div>
                        <?php if (!empty($sellerImages)): ?>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <?php foreach ($sellerImages as $img): ?>
                                    <a href="<?= asset($img) ?>" target="_blank" class="text-decoration-none">
                                        <img src="<?= asset($img) ?>" alt="Seller evidence" style="width: 88px; height: 88px; object-fit: cover;" class="rounded-3 border">
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($canRespond): ?>
                <form action="<?= url('/seller/disputes/respond/' . $d['id']) ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nội dung phản hồi của seller</label>
                        <textarea name="seller_response" rows="5" class="form-control" placeholder="Nêu rõ seller đã giao gì, thời điểm nào, bằng chứng ra sao, vì sao buyer đang hiểu sai hoặc cố tình báo cáo."><?= e($d['seller_response'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ảnh bằng chứng</label>
                        <input type="file" name="seller_evidence_images[]" class="form-control" accept="image/*" multiple>
                        <div class="form-text">Có thể bỏ trống nếu bạn chỉ cần cập nhật lời giải trình.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> Gửi phản hồi cho admin
                    </button>
                </form>
                <?php else: ?>
                <div class="alert alert-light border mb-0">
                    Khiếu nại này đã có kết quả cuối cùng. Seller không thể cập nhật thêm phản hồi.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý khiếu nại';
require_once __DIR__ . '/../layouts/seller.php';
?>
