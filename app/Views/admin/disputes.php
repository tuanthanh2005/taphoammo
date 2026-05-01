<?php ob_start(); ?>

<div class="container-fluid py-3">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="fw-bold mb-1"><i class="fas fa-gavel text-primary me-2"></i> Trung tâm Giải quyết Khiếu nại</h4>
            <p class="text-muted small mb-0">Xử lý các tranh chấp giữa Người mua và Người bán trên sàn.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex flex-wrap justify-content-md-end gap-3 align-items-center">
            <form action="<?= url('/admin/disputes') ?>" method="GET" class="d-flex gap-2">
                <?php if (!empty($currentStatus)): ?>
                    <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
                <?php endif; ?>
                <div class="input-group input-group-sm" style="max-width: 250px;">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Tìm mã khiếu nại, đơn, tên..." value="<?= e($search ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-sm btn-primary px-3 rounded-pill">Tìm</button>
            </form>

            <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                <a class="btn btn-white btn-sm <?= empty($currentStatus) ? 'active' : '' ?> px-3" href="<?= url('/admin/disputes') ?>">Tất cả</a>
                <?php 
                $statusMap = [
                    'open' => ['text' => 'Mới mở', 'class' => 'bg-danger'],
                    'under_review' => ['text' => 'Đang xem xét', 'class' => 'bg-warning'],
                    'resolved_refund' => ['text' => 'Hoàn tiền', 'class' => 'bg-success'],
                    'resolved_partial' => ['text' => 'Hoàn 1 phần', 'class' => 'bg-info'],
                    'resolved_rejected' => ['text' => 'Từ chối', 'class' => 'bg-secondary'],
                ];
                foreach ($statusMap as $k => $v): 
                    $count = 0;
                    foreach ($counts as $c) {
                        if ($c['status'] == $k) $count = $c['total'];
                    }
                ?>
                <a class="btn btn-white btn-sm <?= $currentStatus === $k ? 'active' : '' ?> px-3 border" href="<?= url('/admin/disputes?status=' . $k) ?>">
                    <?= $v['text'] ?> <?= $count > 0 ? "<span class='badge {$v['class']} ms-1'>$count</span>" : '' ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Disputes Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-uppercase small fw-bold">
                            <th class="ps-4 py-3">Mã khiếu nại</th>
                            <th>Đơn hàng</th>
                            <th>Các bên liên quan</th>
                            <th>Lý do & Số tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($disputes)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="fas fa-check-circle fa-3x mb-3 text-muted opacity-25"></i>
                                        <p class="text-muted">Không tìm thấy khiếu nại nào cần xử lý.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($disputes as $d): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">#<?= e($d['dispute_code']) ?></div>
                                </td>
                                <td>
                                    <a href="<?= url('/admin/orders/' . $d['order_id']) ?>" class="text-decoration-none fw-semibold">
                                        <i class="fas fa-shopping-cart small me-1"></i> #<?= e($d['order_code']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <div class="small">
                                            <span class="badge bg-primary-subtle text-primary fw-normal">Mua:</span> 
                                            <strong><?= e($d['user_name']) ?></strong> (@<?= e($d['user_username']) ?>)
                                        </div>
                                        <div class="small">
                                            <span class="badge bg-warning-subtle text-warning fw-normal">Bán:</span> 
                                            <strong><?= e($d['seller_name']) ?></strong> (@<?= e($d['seller_username']) ?>)
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $reasonMap = [
                                        'not_received' => 'Chưa nhận được hàng',
                                        'wrong_item' => 'Sai sản phẩm',
                                        'not_working' => 'Sản phẩm lỗi/hỏng',
                                        'scam' => 'Lừa đảo',
                                        'other' => 'Khác'
                                    ];
                                    ?>
                                    <div class="fw-bold"><?= $reasonMap[$d['reason']] ?? 'Khác' ?></div>
                                    <div class="text-danger small fw-bold"><?= money($d['amount']) ?></div>
                                </td>
                                <td>
                                    <?php
                                    $sConfig = [
                                        'open' => ['bg' => 'bg-danger-subtle', 'text' => 'text-danger', 'border' => 'border-danger', 'label' => 'Mới mở'],
                                        'under_review' => ['bg' => 'bg-warning-subtle', 'text' => 'text-warning', 'border' => 'border-warning', 'label' => 'Đang xem xét'],
                                        'resolved_refund' => ['bg' => 'bg-success-subtle', 'text' => 'text-success', 'border' => 'border-success', 'label' => 'Hoàn tiền'],
                                        'resolved_partial' => ['bg' => 'bg-info-subtle', 'text' => 'text-info', 'border' => 'border-info', 'label' => 'Hoàn 1 phần'],
                                        'resolved_rejected' => ['bg' => 'bg-secondary-subtle', 'text' => 'text-secondary', 'border' => 'border-secondary', 'label' => 'Từ chối'],
                                        'closed' => ['bg' => 'bg-dark-subtle', 'text' => 'text-dark', 'border' => 'border-dark', 'label' => 'Đã đóng']
                                    ][$d['status']] ?? ['bg' => 'bg-light', 'text' => 'text-dark', 'border' => 'border-secondary', 'label' => $d['status']];
                                    ?>
                                    <span class="badge rounded-pill border px-3 py-2 <?= $sConfig['bg'] ?> <?= $sConfig['text'] ?> <?= $sConfig['border'] ?>">
                                        <?= $sConfig['label'] ?>
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    <?= date('d/m/Y', strtotime($d['created_at'])) ?><br>
                                    <span style="font-size: 10px;"><?= date('H:i', strtotime($d['created_at'])) ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <button type="button" class="btn btn-primary rounded-pill btn-sm px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#disputeModal<?= $d['id'] ?>">
                                        Xử lý ngay
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
</div>

<?php foreach ($disputes as $d): ?>
<!-- Dispute Detail Modal -->
<div class="modal fade" id="disputeModal<?= $d['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-light p-4">
                <div>
                    <h5 class="modal-title fw-bold mb-1">Khiếu nại #<?= e($d['dispute_code']) ?></h5>
                    <div class="small text-muted">Đơn hàng: <strong>#<?= e($d['order_code']) ?></strong> | Ngày tạo: <?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <!-- Left Column: Evidence & Timeline -->
                    <div class="col-lg-8 border-end" style="max-height: 70vh; overflow-y: auto;">
                        <div class="p-4">
                            <!-- Dispute Info -->
                            <div class="card border-0 bg-primary-subtle rounded-4 mb-4">
                                <div class="card-body p-3">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                <i class="fas fa-info-circle fa-lg"></i>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="fw-bold text-primary">Lý do khiếu nại: <?= $reasonMap[$d['reason']] ?? 'Khác' ?></div>
                                            <div class="small text-primary opacity-75">Số tiền tranh chấp: <strong><?= money($d['amount']) ?></strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Initial Description -->
                            <div class="mb-5">
                                <h6 class="fw-bold mb-3 border-start border-4 border-primary ps-2">Mô tả từ Người mua</h6>
                                <div class="p-4 bg-light rounded-4 position-relative">
                                    <i class="fas fa-quote-left position-absolute top-0 start-0 opacity-10 mt-2 ms-2 fa-2x"></i>
                                    <div class="text-dark"><?= nl2br(e($d['description'])) ?></div>
                                    
                                    <?php if(!empty($d['evidence_images'])): ?>
                                        <?php $images = json_decode($d['evidence_images'], true); ?>
                                        <?php if(is_array($images) && count($images) > 0): ?>
                                            <div class="mt-4 pt-3 border-top border-white border-opacity-50">
                                                <div class="small fw-bold text-muted mb-3 text-uppercase">Hình ảnh bằng chứng:</div>
                                                <div class="d-flex gap-3 flex-wrap">
                                                    <?php foreach($images as $img): ?>
                                                        <a href="<?= asset($img) ?>" target="_blank" class="evidence-img-container">
                                                            <img src="<?= asset($img) ?>" class="rounded-3 border shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                                                            <div class="overlay"><i class="fas fa-search-plus"></i></div>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mb-5">
                                <h6 class="fw-bold mb-3 border-start border-4 border-success ps-2">Giai trinh tu Seller</h6>
                                <?php $sellerImages = json_decode($d['seller_evidence_images'] ?? '[]', true) ?: []; ?>
                                <?php if (!empty($d['seller_responded_at']) || !empty($d['seller_response']) || !empty($sellerImages)): ?>
                                    <div class="p-4 bg-success-subtle rounded-4 position-relative border border-success-subtle">
                                        <div class="small text-success fw-bold mb-2">
                                            Da phan hoi luc <?= !empty($d['seller_responded_at']) ? date('d/m/Y H:i', strtotime($d['seller_responded_at'])) : 'khong ro' ?>
                                        </div>
                                        <div class="text-dark"><?= nl2br(e($d['seller_response'] ?? 'Seller chi gui them bang chung.')) ?></div>

                                        <?php if (!empty($sellerImages)): ?>
                                            <div class="mt-4 pt-3 border-top border-success-subtle">
                                                <div class="small fw-bold text-muted mb-3 text-uppercase">Bang chung tu seller:</div>
                                                <div class="d-flex gap-3 flex-wrap">
                                                    <?php foreach($sellerImages as $img): ?>
                                                        <a href="<?= asset($img) ?>" target="_blank" class="evidence-img-container">
                                                            <img src="<?= asset($img) ?>" class="rounded-3 border shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                                                            <div class="overlay"><i class="fas fa-search-plus"></i></div>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-light border rounded-4 mb-0">
                                        Seller chua gui phan hoi. SLA hien tai la <?= (int)Helper::getDisputeSellerResponseHours() ?> gio ke tu luc buyer mo khieu nai.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Timeline / Conversation -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-4 border-start border-4 border-warning ps-2">Lịch sử tranh chấp (Timeline)</h6>
                                <div class="timeline-container ps-3">
                                    <?php 
                                    $dEvents = $events[$d['id']] ?? []; 
                                    if (empty($dEvents)):
                                    ?>
                                        <p class="text-muted small italic">Chưa có hoạt động nào được ghi lại.</p>
                                    <?php else: ?>
                                        <?php foreach ($dEvents as $e): ?>
                                            <div class="timeline-item pb-4 position-relative border-start border-dashed ps-4 ms-2">
                                                <div class="timeline-dot bg-<?= $e['actor_role'] === 'admin' ? 'primary' : ($e['actor_role'] === 'seller' ? 'warning' : 'info') ?> position-absolute rounded-circle" style="width: 12px; height: 12px; left: -6px; top: 4px;"></div>
                                                <div class="small fw-bold mb-1 d-flex align-items-center">
                                                    <span class="text-capitalize"><?= $e['actor_name'] ?: ($e['actor_role'] === 'buyer' ? 'Người mua' : 'Người bán') ?></span>
                                                    <span class="badge bg-light text-dark border ms-2" style="font-size: 9px;"><?= strtoupper($e['actor_role']) ?></span>
                                                    <span class="text-muted ms-auto" style="font-size: 10px;"><?= date('H:i d/m/Y', strtotime($e['created_at'])) ?></span>
                                                </div>
                                                <div class="bg-white p-3 rounded-3 border shadow-sm small">
                                                    <div class="fw-semibold mb-1 text-uppercase" style="font-size: 9px;"><?= $e['event_type'] ?></div>
                                                    <div><?= nl2br(e($e['message'])) ?></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Admin Actions -->
                    <div class="col-lg-4 bg-white p-4" style="border-top-right-radius: 1rem; border-bottom-right-radius: 1rem;">
                        <h6 class="fw-bold mb-4 border-start border-4 border-danger ps-2">Quyết định của Admin</h6>
                        
                        <?php if (in_array($d['status'], ['open', 'under_review'])): ?>
                            <div class="alert alert-warning rounded-4 small mb-4 py-2">
                                <i class="fas fa-gavel me-2"></i> Admin hãy đọc kỹ bằng chứng và lịch sử trao đổi trước khi đưa ra phán quyết cuối cùng.
                            </div>

                            <form action="<?= url('/admin/disputes/resolve/' . $d['id']) ?>" method="POST">
                                <?= csrf_field() ?>
                                <div class="mb-4">
                                    <label class="form-label small fw-bold">1. Phán quyết</label>
                                    <select class="form-select border-primary-subtle rounded-3" name="decision" required onchange="toggleRefundInputs(this, <?= $d['id'] ?>)">
                                        <option value="">-- Chọn quyết định --</option>
                                        <option value="refund">Chấp nhận (Hoàn tiền cho Người mua)</option>
                                        <option value="reject">Từ chối (Seller đúng, thanh toán cho Seller)</option>
                                    </select>
                                </div>
                                
                                <div id="refundInputs<?= $d['id'] ?>" class="d-none animate-fade-in">
                                    <div class="mb-4">
                                        <label class="form-label small fw-bold">2. Số tiền hoàn lại</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control rounded-3" name="refund_amount" value="<?= $d['amount'] ?>" max="<?= $d['amount'] ?>" min="1">
                                            <span class="input-group-text bg-light">VNĐ</span>
                                        </div>
                                        <div class="form-text small" style="font-size: 10px;">Có thể hoàn một phần hoặc toàn bộ số tiền (tối đa <?= money($d['amount']) ?>).</div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-danger">3. Phạt Seller (Nếu cần)</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control border-danger-subtle rounded-3" name="penalty_amount" value="0" min="0">
                                            <span class="input-group-text bg-danger-subtle text-danger border-danger-subtle">VNĐ</span>
                                        </div>
                                        <div class="form-text small text-danger" style="font-size: 10px;">Số tiền này sẽ được trừ từ ví Seller và cộng vào doanh thu Admin.</div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-bold">Ghi chú & Lý do xử lý</label>
                                    <textarea class="form-control rounded-4 bg-light border-0" name="admin_note" rows="4" required placeholder="Nêu rõ lý do vì sao đưa ra phán quyết này..."></textarea>
                                    <div class="form-text small italic" style="font-size: 10px;">Nội dung này sẽ được gửi tới Telegram của cả hai bên.</div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow" onclick="return confirm('XÁC NHẬN THI HÀNH PHÁN QUYẾT?\nHành động này không thể hoàn tác, tiền sẽ được điều chuyển ngay lập tức.')">
                                    <i class="fas fa-balance-scale me-2"></i> THI HÀNH PHÁN QUYẾT
                                </button>
                            </form>
                        <?php else: ?>
                            <!-- Resolved Display -->
                            <div class="card border-0 shadow-none bg-light rounded-4">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px;">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <h6 class="mb-0 fw-bold">Đã được giải quyết</h6>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="small text-muted">Kết quả:</label>
                                        <div class="fw-bold"><?= $statusMap[$d['status']]['text'] ?></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="small text-muted">Tiền đã hoàn:</label>
                                        <div class="fw-bold text-success"><?= money($d['refund_amount']) ?></div>
                                    </div>
                                    <?php if($d['penalty_amount'] > 0): ?>
                                    <div class="mb-3">
                                        <label class="small text-muted">Mức phạt Seller:</label>
                                        <div class="fw-bold text-danger"><?= money($d['penalty_amount']) ?></div>
                                    </div>
                                    <?php endif; ?>
                                    <div class="mb-0">
                                        <label class="small text-muted">Ghi chú phán quyết:</label>
                                        <div class="p-3 bg-white rounded-3 border small mt-1">
                                            <?= nl2br(e($d['admin_note'])) ?>
                                        </div>
                                    </div>
                                    <div class="mt-3 small text-muted italic">
                                        Xử lý lúc: <?= date('H:i d/m/Y', strtotime($d['resolved_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mt-5 border-top pt-4">
                            <h6 class="small fw-bold text-muted text-uppercase mb-3">Liên kết nhanh</h6>
                            <div class="d-grid gap-2">
                                <a href="<?= url('/admin/orders/' . $d['order_id']) ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill border-0 bg-primary-subtle text-primary">
                                    <i class="fas fa-external-link-alt me-1"></i> Xem đơn hàng gốc
                                </a>
                                <a href="<?= url('/admin/products?search=' . urlencode($d['seller_name'])) ?>" target="_blank" class="btn btn-outline-warning btn-sm rounded-pill border-0 bg-warning-subtle text-warning">
                                    <i class="fas fa-boxes me-1"></i> Các sp khác của Seller
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<style>
.bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1); }
.bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1); }
.bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1); }
.bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
.bg-info-subtle { background-color: rgba(13, 202, 240, 0.1); }
.bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1); }
.bg-dark-subtle { background-color: rgba(33, 37, 41, 0.1); }

.btn-white { background: #fff; color: #444; }
.btn-white:hover { background: #f8fafc; color: #000; }
.btn-white.active { background: #0d6efd; color: #fff; border-color: #0d6efd; }

.evidence-img-container {
    position: relative;
    display: inline-block;
    transition: transform 0.2s;
}
.evidence-img-container:hover { transform: scale(1.05); }
.evidence-img-container .overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.3);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; opacity: 0; transition: opacity 0.2s;
}
.evidence-img-container:hover .overlay { opacity: 1; }

.timeline-item:last-child { border-start: none !important; }
.timeline-dot { z-index: 2; }

.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
function toggleRefundInputs(selectElement, id) {
    const refundInputs = document.getElementById('refundInputs' + id);
    if (selectElement.value === 'refund') {
        refundInputs.classList.remove('d-none');
    } else {
        refundInputs.classList.add('d-none');
    }
}
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý khiếu nại';
require_once __DIR__ . '/../layouts/admin.php';
?>
