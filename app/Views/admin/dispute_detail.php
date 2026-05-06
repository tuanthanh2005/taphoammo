<?php ob_start(); ?>

<div class="container-fluid py-4">
    <!-- Breadcrumb & Header -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('/admin/dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= url('/admin/disputes') ?>" class="text-decoration-none">Khiếu nại</a></li>
            <li class="breadcrumb-item active" aria-current="page">#<?= e($d['dispute_code']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Left Column: Evidence & Timeline -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white p-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-primary-soft text-primary mb-2">#<?= e($d['dispute_code']) ?></span>
                            <h4 class="fw-bold mb-0 text-dark">Chi tiết Khiếu nại</h4>
                            <div class="small text-muted mt-1">
                                <i class="far fa-clock me-1"></i> <?= date('d/m/Y H:i', strtotime($d['created_at'])) ?> 
                                <span class="mx-2">|</span> 
                                <i class="fas fa-shopping-bag me-1"></i> Đơn hàng: <a href="<?= url('/admin/orders/' . $d['order_id']) ?>" target="_blank" class="text-primary fw-bold text-decoration-none">#<?= e($d['order_code']) ?></a>
                            </div>
                        </div>
                        <div class="text-end">
                            <?php
                            $sConfig = [
                                'open' => ['bg' => 'bg-danger-soft', 'text' => 'text-danger', 'label' => 'Mới mở'],
                                'under_review' => ['bg' => 'bg-warning-soft', 'text' => 'text-warning', 'label' => 'Đang xem xét'],
                                'resolved_refund' => ['bg' => 'bg-success-soft', 'text' => 'text-success', 'label' => 'Hoàn tiền'],
                                'resolved_partial' => ['bg' => 'bg-info-soft', 'text' => 'text-info', 'label' => 'Hoàn 1 phần'],
                                'resolved_rejected' => ['bg' => 'bg-secondary-soft', 'text' => 'text-secondary', 'label' => 'Từ chối'],
                                'closed' => ['bg' => 'bg-dark-soft', 'text' => 'text-dark', 'label' => 'Đã đóng']
                            ][$d['status']] ?? ['bg' => 'bg-light', 'text' => 'text-dark', 'label' => $d['status']];
                            ?>
                            <span class="badge rounded-pill px-4 py-2 <?= $sConfig['bg'] ?> <?= $sConfig['text'] ?>" style="font-size: 0.9rem;">
                                <?= $sConfig['label'] ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 p-lg-5">
                    <!-- Quick Info Cards -->
                    <div class="row g-4 mb-5">
                        <div class="col-md-4">
                            <div class="p-4 border-0 rounded-4 bg-light h-100 shadow-sm transition-hover">
                                <div class="small text-muted mb-2 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Lý do tranh chấp</div>
                                <div class="fw-bold text-dark fs-5"><?= [
                                    'not_received' => 'Chưa nhận được hàng',
                                    'wrong_item' => 'Sai sản phẩm',
                                    'not_working' => 'Sản phẩm lỗi/hỏng',
                                    'scam' => 'Lừa đảo',
                                    'other' => 'Khác'
                                ][$d['reason']] ?? 'Khác' ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-4 border-0 rounded-4 bg-danger-soft h-100 shadow-sm transition-hover">
                                <div class="small text-danger mb-2 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Số tiền tranh chấp</div>
                                <div class="fw-bold fs-5 text-danger"><?= money($d['amount']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-4 border-0 rounded-4 bg-primary-soft h-100 shadow-sm transition-hover">
                                <div class="small text-primary mb-2 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Tổng đơn hàng</div>
                                <div class="fw-bold fs-5 text-primary"><?= money($d['order_total']) ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Involved Parties -->
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light rounded-4 p-3 h-100">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-lg bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="fas fa-user fa-lg"></i>
                                    </div>
                                    <div>
                                        <div class="small text-muted">Người mua</div>
                                        <div class="fw-bold text-dark"><?= e($d['user_name']) ?> (@<?= e($d['user_username']) ?>)</div>
                                        <div class="small text-muted"><?= e($d['user_email']) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 bg-light rounded-4 p-3 h-100">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-lg bg-warning rounded-circle text-white d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="fas fa-store fa-lg"></i>
                                    </div>
                                    <div>
                                        <div class="small text-muted">Người bán</div>
                                        <div class="fw-bold text-dark"><?= e($d['seller_name']) ?> (@<?= e($d['seller_username']) ?>)</div>
                                        <div class="small text-muted"><?= e($d['seller_email']) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Evidence Section -->
                    <div class="evidence-section">
                        <!-- Buyer Evidence -->
                        <div class="evidence-card p-4 mb-4 shadow-sm border-0">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <h5 class="fw-bold mb-0">Bằng chứng từ Người mua</h5>
                            </div>
                            <div class="p-4 bg-light rounded-4 mb-4 text-dark border-start border-4 border-primary shadow-inner" style="line-height: 1.8;">
                                <?= nl2br(e($d['description'])) ?>
                            </div>
                            
                            <?php if(!empty($d['evidence_images'])): ?>
                                <?php $images = json_decode($d['evidence_images'], true); ?>
                                <?php if(is_array($images) && count($images) > 0): ?>
                                    <div class="d-flex gap-3 flex-wrap">
                                        <?php foreach($images as $img): ?>
                                            <a href="<?= asset($img) ?>" target="_blank" class="evidence-img-link">
                                                <img src="<?= asset($img) ?>" class="evidence-img-thumb border-0" alt="Evidence">
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Seller Response -->
                        <div class="evidence-card p-4 mb-4 shadow-sm border-0">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px;">
                                    <i class="fas fa-store"></i>
                                </div>
                                <h5 class="fw-bold mb-0">Giải trình từ Người bán</h5>
                            </div>
                            <?php 
                            $sellerImages = json_decode($d['seller_evidence_images'] ?? '[]', true) ?: [];
                            if (!empty($d['seller_responded_at']) || !empty($d['seller_response']) || !empty($sellerImages)): 
                            ?>
                                <div class="p-4 bg-success-soft rounded-4 mb-4 text-dark border-start border-4 border-success" style="line-height: 1.8;">
                                    <?= nl2br(e($d['seller_response'] ?? 'Người bán chỉ cung cấp thêm hình ảnh bằng chứng.')) ?>
                                    <div class="mt-3 pt-3 border-top border-success border-opacity-10 small text-muted">
                                        <i class="far fa-clock me-1"></i> Phản hồi lúc: <?= date('H:i d/m/Y', strtotime($d['seller_responded_at'])) ?>
                                    </div>
                                </div>
                                <?php if (!empty($sellerImages)): ?>
                                    <div class="d-flex gap-3 flex-wrap">
                                        <?php foreach($sellerImages as $img): ?>
                                            <a href="<?= asset($img) ?>" target="_blank" class="evidence-img-link">
                                                <img src="<?= asset($img) ?>" class="evidence-img-thumb border-0" alt="Seller Evidence">
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert bg-warning-soft text-warning mb-0 border-0 rounded-4 p-4 d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle fa-2x me-3 opacity-50"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">Chưa có phản hồi</h6>
                                        <p class="small mb-0">Người bán hiện vẫn chưa gửi bất kỳ giải trình hoặc bằng chứng đối chất nào.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="mt-5 pt-4 border-top">
                        <h5 class="fw-bold mb-5"><i class="fas fa-history me-2 text-primary"></i> Lịch sử đối thoại & Hoạt động</h5>
                        <div class="timeline-modern">
                            <?php if (empty($events)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-stream fa-3x mb-3 opacity-10"></i>
                                    <p>Chưa có hoạt động nào được ghi lại.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($events as $e): ?>
                                    <div class="timeline-modern-item">
                                        <div class="timeline-modern-dot" style="border-color: <?= $e['actor_role'] === 'admin' ? '#4e73df' : ($e['actor_role'] === 'seller' ? '#f6c23e' : '#36b9cc') ?>"></div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="small fw-bold text-dark">
                                                <?= e($e['actor_name'] ?: ($e['actor_role'] === 'buyer' ? 'Người mua' : 'Người bán')) ?>
                                                <span class="badge rounded-pill bg-light text-muted border ms-2 px-2 py-1" style="font-size: 0.6rem;"><?= strtoupper($e['actor_role']) ?></span>
                                            </span>
                                            <span class="text-muted" style="font-size: 0.75rem;"><?= date('H:i d/m/Y', strtotime($e['created_at'])) ?></span>
                                        </div>
                                        <div class="p-4 rounded-4 bg-white border-0 shadow-sm small transition-hover">
                                            <div class="text-muted fw-bold mb-2 text-uppercase letter-spacing-1" style="font-size: 0.6rem;"><?= str_replace('_', ' ', $e['event_type']) ?></div>
                                            <div class="text-dark fs-6" style="line-height: 1.6;"><?= nl2br(e($e['message'])) ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Admin Actions -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 2rem; z-index: 1020;">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white p-4 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="bg-danger-soft rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                <i class="fas fa-gavel text-danger"></i>
                            </div>
                            <h5 class="fw-bold mb-0 text-dark">Quyết định Admin</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <?php if (in_array($d['status'], ['open', 'under_review'])): ?>
                            <form action="<?= url('/admin/disputes/resolve/' . $d['id']) ?>" method="POST">
                                <?= csrf_field() ?>
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted mb-2">1. Phán quyết cuối cùng</label>
                                    <select class="form-select rounded-4 p-3 shadow-none border-0 bg-light fw-bold" name="decision" required onchange="toggleRefundInputs(this, <?= $d['id'] ?>)">
                                        <option value="">-- Chọn quyết định --</option>
                                        <option value="refund">Chấp nhận (Hoàn tiền Buyer)</option>
                                        <option value="reject">Từ chối (Seller thắng)</option>
                                    </select>
                                </div>
                                
                                <div id="refundInputs<?= $d['id'] ?>" class="d-none animate-fade-in">
                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-success mb-2">2. Số tiền hoàn lại</label>
                                        <div class="input-group input-group-lg">
                                            <input type="number" class="form-control rounded-start-4 border-0 bg-light fw-bold text-success" name="refund_amount" value="<?= $d['amount'] ?>" max="<?= $d['amount'] ?>" min="0">
                                            <span class="input-group-text bg-light border-0 text-muted small px-3">VNĐ</span>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-danger mb-2">3. Mức phạt Seller (nếu có)</label>
                                        <div class="input-group input-group-lg">
                                            <input type="number" class="form-control rounded-start-4 border-0 bg-light text-danger fw-bold" name="penalty_amount" value="0" min="0">
                                            <span class="input-group-text bg-light border-0 text-muted small px-3">VNĐ</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted mb-2">Ghi chú công khai (Gửi cho cả 2 bên)</label>
                                    <textarea class="form-control rounded-4 bg-light border-0 p-3" name="admin_note" rows="6" required placeholder="Lý do chi tiết cho quyết định này..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-lg border-0 bg-gradient-primary mb-3" onclick="return confirm('XÁC NHẬN THỰC THI PHÁN QUYẾT?')">
                                    THỰC THI PHÁN QUYẾT
                                </button>
                                <p class="text-center text-muted small px-2">Lưu ý: Quyết định sau khi thực thi sẽ không thể hoàn tác.</p>
                            </form>
                        <?php else: ?>
                            <!-- Resolved Status Display -->
                            <div class="text-center mb-4 py-4 bg-success-soft rounded-4 border-bottom border-white border-4">
                                <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 80px; height: 80px;">
                                    <i class="fas fa-check-circle fa-3x text-success"></i>
                                </div>
                                <h4 class="fw-bold mb-1 text-success">Vụ việc đã đóng</h4>
                                <div class="small text-muted">Xử lý lúc: <?= date('H:i d/m/Y', strtotime($d['resolved_at'] ?? $d['status_updated_at'] ?? 'now')) ?></div>
                            </div>

                            <div class="space-y-4">
                                <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded-4">
                                    <span class="text-muted small fw-bold text-uppercase">Kết quả</span>
                                    <?php
                                    $finalStatus = [
                                        'resolved_refund' => ['text' => 'Hoàn tiền', 'class' => 'bg-success'],
                                        'resolved_partial' => ['text' => 'Hoàn 1 phần', 'class' => 'bg-info'],
                                        'resolved_rejected' => ['text' => 'Từ chối', 'class' => 'bg-danger'],
                                    ][$d['status']] ?? ['text' => $d['status'], 'class' => 'bg-secondary'];
                                    ?>
                                    <span class="badge rounded-pill <?= $finalStatus['class'] ?> px-3 py-2"><?= $finalStatus['text'] ?></span>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3 px-3">
                                    <span class="text-muted small fw-bold text-uppercase">Số tiền hoàn</span>
                                    <span class="fw-bold text-success fs-4"><?= money($d['refund_amount']) ?></span>
                                </div>

                                <?php if($d['penalty_amount'] > 0): ?>
                                <div class="d-flex justify-content-between align-items-center mb-4 px-3">
                                    <span class="text-danger small fw-bold text-uppercase">Mức phạt Seller</span>
                                    <span class="fw-bold text-danger fs-4"><?= money($d['penalty_amount']) ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="mt-4 pt-4 border-top">
                                    <div class="small fw-bold text-muted mb-3 text-uppercase letter-spacing-1" style="font-size: 0.65rem;">Ghi chú phán quyết:</div>
                                    <div class="p-4 bg-light rounded-4 text-dark small shadow-inner" style="line-height: 1.8;">
                                        <?= nl2br(e($d['admin_note'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-4">Lối tắt nhanh</h6>
                        <div class="d-grid gap-3">
                            <a href="<?= url('/admin/orders/' . $d['order_id']) ?>" target="_blank" class="btn btn-light rounded-pill py-2 border-0 shadow-sm text-dark d-flex align-items-center justify-content-center">
                                <i class="fas fa-receipt me-2 text-muted"></i> Xem đơn hàng gốc
                            </a>
                            <a href="<?= url('/admin/products?search=' . urlencode($d['seller_name'])) ?>" target="_blank" class="btn btn-light rounded-pill py-2 border-0 shadow-sm text-dark d-flex align-items-center justify-content-center">
                                <i class="fas fa-store me-2 text-muted"></i> Gian hàng Seller
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-primary-soft { background-color: rgba(78, 115, 223, 0.08); color: #4e73df; }
.bg-success-soft { background-color: rgba(28, 200, 138, 0.08); color: #1cc88a; }
.bg-warning-soft { background-color: rgba(246, 194, 62, 0.08); color: #f6c23e; }
.bg-danger-soft { background-color: rgba(231, 74, 59, 0.08); color: #e74a3b; }
.bg-info-soft { background-color: rgba(54, 185, 204, 0.08); color: #36b9cc; }
.bg-dark-soft { background-color: rgba(33, 37, 41, 0.08); color: #2d3748; }

.shadow-inner {
    box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05);
}

.transition-hover {
    transition: all 0.3s ease;
}
.transition-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.timeline-modern {
    position: relative;
    padding-left: 35px;
}

.timeline-modern::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 5px;
    bottom: 5px;
    width: 2px;
    background: linear-gradient(to bottom, #e3e6f0, #f8f9fc);
}

.timeline-modern-item {
    position: relative;
    margin-bottom: 2.5rem;
}

.timeline-modern-dot {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    border: 4px solid #4e73df;
    z-index: 1;
    box-shadow: 0 0 0 4px rgba(78, 115, 223, 0.1);
}

.evidence-img-thumb {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 16px;
    cursor: zoom-in;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 4px solid #fff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.evidence-img-thumb:hover {
    transform: scale(1.1) rotate(3deg);
    box-shadow: 0 12px 25px rgba(0,0,0,0.15);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
}

.evidence-img-link {
    display: block;
    border-radius: 16px;
    overflow: hidden;
    position: relative;
}

.evidence-img-link::after {
    content: '\f00e';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(78, 115, 223, 0.4);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.evidence-img-link:hover::after {
    opacity: 1;
}

.letter-spacing-1 {
    letter-spacing: 0.1em;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "\f105";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    font-size: 0.8rem;
    color: #cbd5e0;
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
$pageTitle = 'Xử lý khiếu nại #' . $d['dispute_code'];
require_once __DIR__ . '/../layouts/admin.php';
?>
