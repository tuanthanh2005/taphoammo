<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold"><i class="fas fa-handshake me-2 text-primary"></i>Phòng đàm phán</h4>
        <div class="text-muted small">Tạo phòng để admin, khách và seller cùng trao đổi giải quyết khiếu nại.</div>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createNegotiationModal">
        <i class="fas fa-plus me-1"></i> Tạo phòng mới
    </button>
</div>

<div class="btn-group mb-3" role="group">
    <a href="<?= url('/admin/negotiations') ?>" class="btn btn-sm <?= !$currentStatus ? 'btn-primary' : 'btn-outline-primary' ?>">Tất cả</a>
    <a href="<?= url('/admin/negotiations?status=open') ?>" class="btn btn-sm <?= $currentStatus === 'open' ? 'btn-primary' : 'btn-outline-primary' ?>">Đang mở</a>
    <a href="<?= url('/admin/negotiations?status=resolved') ?>" class="btn btn-sm <?= $currentStatus === 'resolved' ? 'btn-success' : 'btn-outline-success' ?>">Đã giải quyết</a>
    <a href="<?= url('/admin/negotiations?status=closed') ?>" class="btn btn-sm <?= $currentStatus === 'closed' ? 'btn-secondary' : 'btn-outline-secondary' ?>">Đã đóng</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Tiêu đề</th>
                        <th>Khách</th>
                        <th>Seller</th>
                        <th>Trạng thái</th>
                        <th>Cập nhật</th>
                        <th class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rooms)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-inbox d-block fs-3 mb-2 opacity-50"></i>Chưa có phòng đàm phán nào</td></tr>
                    <?php else: ?>
                        <?php foreach ($rooms as $r): ?>
                            <tr>
                                <td class="fw-bold">#<?= $r['id'] ?></td>
                                <td>
                                    <div class="fw-semibold"><?= e($r['title']) ?></div>
                                    <div class="small text-muted"><?= e(mb_strimwidth($r['last_message'] ?? '', 0, 60, '…')) ?></div>
                                </td>
                                <td><span class="badge bg-light text-dark"><i class="fas fa-user me-1"></i><?= e($r['buyer_name'] ?? $r['buyer_username']) ?></span></td>
                                <td><span class="badge bg-success-subtle text-success"><i class="fas fa-store me-1"></i><?= e($r['seller_name'] ?? $r['seller_username']) ?></span></td>
                                <td>
                                    <?php
                                    $statusMap = [
                                        'open' => ['bg-primary', 'Đang mở'],
                                        'resolved' => ['bg-success', 'Đã giải quyết'],
                                        'closed' => ['bg-secondary', 'Đã đóng'],
                                    ];
                                    [$cls, $label] = $statusMap[$r['status']] ?? ['bg-light text-dark', $r['status']];
                                    ?>
                                    <span class="badge <?= $cls ?>"><?= $label ?></span>
                                </td>
                                <td class="small text-muted"><?= date('d/m H:i', strtotime($r['updated_at'])) ?></td>
                                <td class="text-end">
                                    <a href="<?= url('/admin/negotiations/' . $r['id']) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Mở
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Create -->
<div class="modal fade" id="createNegotiationModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= url('/admin/negotiations/create') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-handshake me-2"></i> Tạo phòng đàm phán</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tiêu đề <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required placeholder="VD: Khiếu nại đơn #1234 - Sản phẩm không hoạt động">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mô tả vấn đề</label>
                        <textarea name="topic" class="form-control" rows="3" placeholder="Tóm tắt vấn đề cần giải quyết..."></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Khách hàng <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" id="searchBuyer" class="form-control" placeholder="Tên/email/username..." autocomplete="off">
                                <input type="hidden" name="buyer_id" id="buyerId" required>
                                <div id="buyerResults" class="user-results d-none"></div>
                                <div id="buyerSelected" class="mt-2"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Seller <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" id="searchSeller" class="form-control" placeholder="Tên shop/email..." autocomplete="off">
                                <input type="hidden" name="seller_id" id="sellerId" required>
                                <div id="sellerResults" class="user-results d-none"></div>
                                <div id="sellerSelected" class="mt-2"></div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0 small">
                        <i class="fas fa-info-circle me-1"></i>
                        Sau khi tạo, hệ thống sẽ gửi thông báo qua bot chat, Telegram và notification cho cả khách và seller.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tạo phòng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .user-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        max-height: 280px;
        overflow-y: auto;
        z-index: 10;
        margin-top: 4px;
    }
    .user-result-item { padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 10px; }
    .user-result-item:hover { background: #f8fafc; }
    .user-result-item:last-child { border-bottom: 0; }
    .user-avatar-xs { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; }
    .selected-chip { display: inline-flex; align-items: center; gap: 8px; background: #eef2ff; color: #4f46e5; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 0.85rem; }
    .selected-chip .remove { cursor: pointer; opacity: 0.7; }
    .selected-chip .remove:hover { opacity: 1; }
</style>

<script>
(function() {
    function setupSearch(inputId, resultsId, hiddenId, selectedId, role) {
        const input = document.getElementById(inputId);
        const results = document.getElementById(resultsId);
        const hidden = document.getElementById(hiddenId);
        const selected = document.getElementById(selectedId);
        let timer = null;

        input.addEventListener('input', function() {
            clearTimeout(timer);
            const q = this.value.trim();
            if (q.length < 1) { results.classList.add('d-none'); return; }
            timer = setTimeout(async () => {
                try {
                    const r = await fetch('<?= url('/api/admin/users/search') ?>?q=' + encodeURIComponent(q) + '&role=' + role);
                    const d = await r.json();
                    if (!d.success || !d.users.length) {
                        results.innerHTML = '<div class="user-result-item text-muted">Không tìm thấy</div>';
                    } else {
                        results.innerHTML = d.users.map(u => {
                            const initial = (u.name || u.username || '?').charAt(0).toUpperCase();
                            return `<div class="user-result-item" data-id="${u.id}" data-name="${(u.name || u.username || '').replace(/"/g, '&quot;')}">
                                <div class="user-avatar-xs">${initial}</div>
                                <div>
                                    <div class="fw-semibold">${u.name || u.username}</div>
                                    <div class="small text-muted">${u.email || ''} · ${u.role}</div>
                                </div>
                            </div>`;
                        }).join('');
                    }
                    results.classList.remove('d-none');
                } catch (e) {}
            }, 250);
        });

        results.addEventListener('click', function(e) {
            const item = e.target.closest('.user-result-item');
            if (!item || !item.dataset.id) return;
            hidden.value = item.dataset.id;
            input.value = '';
            results.classList.add('d-none');
            selected.innerHTML = `<span class="selected-chip"><i class="fas fa-user-check"></i>${item.dataset.name} (#${item.dataset.id})<span class="remove" onclick="this.closest('.selected-chip').remove();document.getElementById('${hiddenId}').value='';"><i class="fas fa-times"></i></span></span>`;
        });

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !results.contains(e.target)) {
                results.classList.add('d-none');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        setupSearch('searchBuyer', 'buyerResults', 'buyerId', 'buyerSelected', 'buyer');
        setupSearch('searchSeller', 'sellerResults', 'sellerId', 'sellerSelected', 'seller');
    });
})();
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/admin.php';
?>
