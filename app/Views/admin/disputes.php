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

<style>
:root {
    --admin-primary: #4e73df;
    --admin-success: #1cc88a;
    --admin-warning: #f6c23e;
    --admin-danger: #e74a3b;
    --glass-bg: rgba(255, 255, 255, 0.95);
}

.dispute-modal .modal-content {
    border: none;
    border-radius: 20px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
}

.dispute-header {
    background: linear-gradient(135deg, #f8f9fc 0%, #edf2f7 100%);
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e3e6f0;
}

.evidence-card {
    background: #fff;
    border-radius: 15px;
    border: 1px solid #e3e6f0;
    transition: all 0.3s ease;
}

.evidence-card:hover {
    box-shadow: 0 10px 20px rgba(0,0,0,0.05);
}

.timeline-modern {
    position: relative;
    padding-left: 30px;
}

.timeline-modern::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 5px;
    bottom: 5px;
    width: 2px;
    background: #e3e6f0;
}

.timeline-modern-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-modern-dot {
    position: absolute;
    left: -30px;
    top: 5px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #fff;
    border: 3px solid var(--admin-primary);
    z-index: 1;
}

.decision-sidebar {
    background: #f8f9fc;
    border-left: 1px solid #e3e6f0;
}

.evidence-img-thumb {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 10px;
    cursor: zoom-in;
    transition: transform 0.2s;
}

.evidence-img-thumb:hover {
    transform: scale(1.05);
}

.bg-primary-soft { background-color: rgba(78, 115, 223, 0.1); color: #4e73df; }
.bg-success-soft { background-color: rgba(28, 200, 138, 0.1); color: #1cc88a; }
.bg-warning-soft { background-color: rgba(246, 194, 62, 0.1); color: #f6c23e; }
.bg-danger-soft { background-color: rgba(231, 74, 59, 0.1); color: #e74a3b; }

.modal-body-scroll {
    max-height: calc(90vh - 150px);
    overflow-y: auto;
    scrollbar-width: thin;
}
</style>

<?php foreach ($disputes as $d): ?>
<!-- Dispute Detail Modal -->
<div class="modal fade dispute-modal" id="disputeModal<?= $d['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="dispute-header d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-primary-soft mb-2">#<?= e($d['dispute_code']) ?></span>
                    <h4 class="fw-bold mb-0 text-dark">Chi tiết Khiếu nại</h4>
                    <div class="small text-muted mt-1">
                        <i class="far fa-clock me-1"></i> <?= date('d/m/Y H:i', strtotime($d['created_at'])) ?> 
                        <span class="mx-2">|</span> 
                        <i class="fas fa-shopping-bag me-1"></i> Đơn hàng: <strong>#<?= e($d['order_code']) ?></strong>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <!-- Left: Content -->
                    <div class="col-lg-8 modal-body-scroll">
                        <div class="p-4 p-lg-5">
                            <!-- Quick Summary -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-4 bg-light">
                                        <div class="small text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.7rem;">Lý do tranh chấp</div>
                                        <div class="fw-bold text-dark fs-5"><?= $reasonMap[$d['reason']] ?? 'Khác' ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-4 bg-danger-soft">
                                        <div class="small text-danger mb-1 text-uppercase fw-bold" style="font-size: 0.7rem;">Số tiền tranh chấp</div>
                                        <div class="fw-bold fs-5"><?= money($d['amount']) ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Buyer Evidence -->
                            <div class="evidence-card p-4 mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0">Bằng chứng từ Người mua</h6>
                                </div>
                                <div class="p-3 bg-light rounded-3 mb-3 text-dark border-start border-4 border-primary">
                                    <?= nl2br(e($d['description'])) ?>
                                </div>
                                
                                <?php if(!empty($d['evidence_images'])): ?>
                                    <?php $images = json_decode($d['evidence_images'], true); ?>
                                    <?php if(is_array($images) && count($images) > 0): ?>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <?php foreach($images as $img): ?>
                                                <a href="<?= asset($img) ?>" target="_blank">
                                                    <img src="<?= asset($img) ?>" class="evidence-img-thumb border" alt="Evidence">
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Seller Response -->
                            <div class="evidence-card p-4 mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                        <i class="fas fa-store"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0">Giải trình từ Người bán</h6>
                                </div>
                                <?php 
                                $sellerImages = json_decode($d['seller_evidence_images'] ?? '[]', true) ?: [];
                                if (!empty($d['seller_responded_at']) || !empty($d['seller_response']) || !empty($sellerImages)): 
                                ?>
                                    <div class="p-3 bg-success-soft rounded-3 mb-3 text-dark border-start border-4 border-success">
                                        <?= nl2br(e($d['seller_response'] ?? 'Người bán chỉ cung cấp thêm hình ảnh bằng chứng.')) ?>
                                        <div class="mt-2 small text-muted">Phản hồi lúc: <?= date('H:i d/m/Y', strtotime($d['seller_responded_at'])) ?></div>
                                    </div>
                                    <?php if (!empty($sellerImages)): ?>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <?php foreach($sellerImages as $img): ?>
                                                <a href="<?= asset($img) ?>" target="_blank">
                                                    <img src="<?= asset($img) ?>" class="evidence-img-thumb border" alt="Seller Evidence">
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="alert alert-warning mb-0 border-0 rounded-3">
                                        <i class="fas fa-exclamation-triangle me-2"></i> Người bán chưa gửi phản hồi giải trình.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Modern Timeline -->
                            <h6 class="fw-bold mb-4 mt-5"><i class="fas fa-history me-2"></i> Lịch sử đối thoại & Hoạt động</h6>
                            <div class="timeline-modern">
                                <?php 
                                $dEvents = $events[$d['id']] ?? []; 
                                if (empty($dEvents)):
                                ?>
                                    <p class="text-muted small italic ms-2">Chưa có hoạt động nào được ghi lại.</p>
                                <?php else: ?>
                                    <?php foreach ($dEvents as $e): ?>
                                        <div class="timeline-modern-item">
                                            <div class="timeline-modern-dot" style="border-color: <?= $e['actor_role'] === 'admin' ? '#4e73df' : ($e['actor_role'] === 'seller' ? '#f6c23e' : '#36b9cc') ?>"></div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="small fw-bold">
                                                    <?= e($e['actor_name'] ?: ($e['actor_role'] === 'buyer' ? 'Người mua' : 'Người bán')) ?>
                                                    <span class="badge rounded-pill bg-light text-dark border ms-1" style="font-size: 0.6rem;"><?= strtoupper($e['actor_role']) ?></span>
                                                </span>
                                                <span class="text-muted" style="font-size: 0.7rem;"><?= date('H:i d/m/Y', strtotime($e['created_at'])) ?></span>
                                            </div>
                                            <div class="p-3 rounded-4 bg-white border shadow-sm small">
                                                <div class="text-muted fw-bold mb-1 text-uppercase" style="font-size: 0.6rem;"><?= str_replace('_', ' ', $e['event_type']) ?></div>
                                                <div class="text-dark"><?= nl2br(e($e['message'])) ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Actions -->
                    <div class="col-lg-4 decision-sidebar p-4 p-lg-5">
                        <div class="sticky-top" style="top: 0;">
                            <h5 class="fw-bold mb-4 text-dark"><i class="fas fa-gavel me-2 text-danger"></i> Quyết định Admin</h5>
                            
                            <?php if (in_array($d['status'], ['open', 'under_review'])): ?>
                                <div class="p-3 bg-white border rounded-4 mb-4 shadow-sm">
                                    <form action="<?= url('/admin/disputes/resolve/' . $d['id']) ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <div class="mb-4">
                                            <label class="form-label small fw-bold">1. Phán quyết cuối cùng</label>
                                            <select class="form-select rounded-3 p-3 shadow-none border-2" name="decision" required onchange="toggleRefundInputs(this, <?= $d['id'] ?>)">
                                                <option value="">-- Chọn quyết định --</option>
                                                <option value="refund">Chấp nhận (Hoàn tiền cho Buyer)</option>
                                                <option value="reject">Từ chối (Seller đúng, trả tiền Seller)</option>
                                            </select>
                                        </div>
                                        
                                        <div id="refundInputs<?= $d['id'] ?>" class="d-none animate-fade-in">
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold text-success">2. Số tiền hoàn lại</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control rounded-start-3 p-3" name="refund_amount" value="<?= $d['amount'] ?>" max="<?= $d['amount'] ?>" min="0">
                                                    <span class="input-group-text bg-white">VNĐ</span>
                                                </div>
                                            </div>
                                            <div class="mb-4">
                                                <label class="form-label small fw-bold text-danger">3. Mức phạt Seller (nếu có)</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control rounded-start-3 p-3 border-danger" name="penalty_amount" value="0" min="0">
                                                    <span class="input-group-text bg-danger text-white border-danger">VNĐ</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label small fw-bold">Ghi chú xử lý (Gửi cho các bên)</label>
                                            <textarea class="form-control rounded-4 bg-light border-0" name="admin_note" rows="4" required placeholder="Nêu rõ lý do quyết định..."></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-lg" onclick="return confirm('XÁC NHẬN THỰC THI PHÁN QUYẾT?')">
                                            THỰC THI PHÁN QUYẾT
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <!-- Resolved Info -->
                                <div class="card border-0 bg-white rounded-4 shadow-sm mb-4">
                                    <div class="card-body p-4">
                                        <div class="text-center mb-4">
                                            <div class="bg-success-soft rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                                                <i class="fas fa-check-double fa-2x"></i>
                                            </div>
                                            <h5 class="fw-bold mb-0">Đã Phán Quyết</h5>
                                        </div>
                                        <hr class="opacity-10">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Kết quả:</span>
                                            <span class="fw-bold text-dark"><?= $statusMap[$d['status']]['text'] ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Đã hoàn:</span>
                                            <span class="fw-bold text-success"><?= money($d['refund_amount']) ?></span>
                                        </div>
                                        <?php if($d['penalty_amount'] > 0): ?>
                                        <div class="d-flex justify-content-between mb-4">
                                            <span class="text-muted text-danger">Mức phạt:</span>
                                            <span class="fw-bold text-danger"><?= money($d['penalty_amount']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="p-3 bg-light rounded-4 small">
                                            <div class="fw-bold text-muted mb-1">Ghi chú từ Admin:</div>
                                            <?= nl2br(e($d['admin_note'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="d-grid gap-2">
                                <a href="<?= url('/admin/orders/' . $d['order_id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill border-0 bg-light text-dark py-2">
                                    <i class="fas fa-receipt me-2"></i> Xem đơn hàng gốc
                                </a>
                                <a href="<?= url('/admin/products?search=' . urlencode($d['seller_name'])) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill border-0 bg-light text-dark py-2">
                                    <i class="fas fa-store me-2"></i> Gian hàng của Seller
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
