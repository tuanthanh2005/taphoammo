<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <a href="<?= url('/admin/negotiations') ?>" class="text-muted text-decoration-none small mb-1 d-inline-block">
            <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
        </a>
        <h4 class="mb-0 fw-bold">🏛️ <?= e($room['title']) ?> <span class="badge bg-light text-dark ms-2">#<?= $room['id'] ?></span></h4>
    </div>
    <div class="d-flex gap-2">
        <?php if ($room['status'] === 'open'): ?>
            <form method="POST" action="<?= url('/admin/negotiations/' . $room['id'] . '/status') ?>" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="resolved">
                <button class="btn btn-success btn-sm"><i class="fas fa-check me-1"></i> Đã giải quyết</button>
            </form>
            <form method="POST" action="<?= url('/admin/negotiations/' . $room['id'] . '/status') ?>" class="d-inline" onsubmit="return confirm('Đóng phòng đàm phán này?')">
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="closed">
                <button class="btn btn-secondary btn-sm"><i class="fas fa-lock me-1"></i> Đóng phòng</button>
            </form>
        <?php else: ?>
            <span class="badge bg-<?= $room['status'] === 'resolved' ? 'success' : 'secondary' ?> fs-6">
                <?= $room['status'] === 'resolved' ? '✅ Đã giải quyết' : '🔒 Đã đóng' ?>
            </span>
            <form method="POST" action="<?= url('/admin/negotiations/' . $room['id'] . '/status') ?>" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="open">
                <button class="btn btn-outline-primary btn-sm"><i class="fas fa-unlock me-1"></i> Mở lại</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="text-muted small text-uppercase mb-2">Thành viên</div>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="user-avatar-md bg-primary"><i class="fas fa-user-shield"></i></div>
                    <div>
                        <div class="fw-bold"><?= e($room['admin_name']) ?></div>
                        <div class="small text-muted">Admin</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="user-avatar-md bg-info"><?= mb_strtoupper(mb_substr($room['buyer_name'] ?? 'U', 0, 1)) ?></div>
                    <div>
                        <div class="fw-bold"><?= e($room['buyer_name']) ?></div>
                        <div class="small text-muted">Khách · <?= e($room['buyer_email']) ?></div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="user-avatar-md bg-success"><?= mb_strtoupper(mb_substr($room['seller_name'] ?? 'S', 0, 1)) ?></div>
                    <div>
                        <div class="fw-bold"><?= e($room['seller_name']) ?></div>
                        <div class="small text-muted">Seller · <?= e($room['seller_email']) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($room['topic'])): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small text-uppercase mb-2">Mô tả vấn đề</div>
                <div><?= nl2br(e($room['topic'])) ?></div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="height: 70vh; display: flex; flex-direction: column;">
            <div class="card-header bg-white border-0 pt-3">
                <strong>💬 Cuộc trao đổi</strong>
                <span class="badge bg-light text-dark ms-2"><?= count($messages) ?> tin</span>
            </div>
            <div id="negChatBox" class="flex-grow-1 overflow-auto p-3" style="background:#f8fafc;"></div>
            <?php if ($room['status'] === 'open'): ?>
            <div class="border-top p-3 bg-white">
                <form id="negSendForm" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="d-flex gap-2 align-items-end">
                        <input type="file" id="negAttach" name="attachment" accept="image/*" class="d-none">
                        <button type="button" class="btn btn-light" onclick="document.getElementById('negAttach').click()" title="Đính kèm">
                            <i class="fas fa-paperclip"></i>
                        </button>
                        <textarea name="message" id="negMessage" class="form-control" rows="1" placeholder="Tin nhắn của admin..." style="resize:none;"></textarea>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
                    </div>
                    <div id="negAttachPreview" class="small text-muted mt-2"></div>
                </form>
            </div>
            <?php else: ?>
            <div class="border-top p-3 text-center text-muted small bg-light">🔒 Phòng đã đóng. Mở lại để tiếp tục trao đổi.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .user-avatar-md { width: 40px; height: 40px; border-radius: 50%; color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .neg-msg { display: flex; gap: 10px; margin-bottom: 14px; max-width: 80%; }
    .neg-msg .av { width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff; font-size: 0.85rem; }
    .neg-msg .bubble { background: #fff; padding: 10px 14px; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
    .neg-msg.me { margin-left: auto; flex-direction: row-reverse; }
    .neg-msg.me .bubble { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; }
    .neg-msg.system { max-width: 100%; justify-content: center; }
    .neg-msg.system .bubble { background: #fef3c7; color: #92400e; border: 1px dashed #fcd34d; font-size: 0.85rem; text-align: center; }
    .neg-msg .meta { font-size: 0.7rem; opacity: 0.6; margin-top: 4px; }
    .neg-msg .name { font-weight: 700; font-size: 0.78rem; margin-bottom: 2px; }
    .role-tag { display: inline-block; padding: 1px 6px; border-radius: 4px; font-size: 0.62rem; font-weight: 700; margin-left: 4px; }
    .role-admin { background: #fee2e2; color: #b91c1c; }
    .role-seller { background: #dcfce7; color: #166534; }
    .role-buyer { background: #dbeafe; color: #1e40af; }
</style>

<script>
(function() {
    const ROOM_ID = <?= (int)$room['id'] ?>;
    const API_GET = '<?= url('/api/negotiation/' . $room['id'] . '/messages') ?>';
    const API_SEND = '<?= url('/api/negotiation/' . $room['id'] . '/send') ?>';
    const ASSET_BASE = '<?= asset('') ?>';
    const box = document.getElementById('negChatBox');

    function escape(s) { return (s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]); }
    function avatarColor(role) { return role === 'admin' ? '#dc2626' : (role === 'seller' ? '#16a34a' : (role === 'buyer' ? '#2563eb' : '#6b7280')); }
    function roleLabel(role) { return role === 'admin' ? 'Admin' : (role === 'seller' ? 'Seller' : (role === 'buyer' ? 'Khách' : 'Hệ thống')); }

    function render(messages, currentUserId) {
        if (!messages.length) { box.innerHTML = '<div class="text-center text-muted py-5">Chưa có tin nhắn. Hãy bắt đầu trao đổi.</div>'; return; }
        box.innerHTML = messages.map(m => {
            if (m.is_system == 1 || m.sender_role === 'system') {
                return `<div class="neg-msg system"><div class="bubble">${escape(m.message).replace(/\n/g, '<br>')}</div></div>`;
            }
            const me = m.sender_id == currentUserId;
            const initial = (m.sender_name || '?').charAt(0).toUpperCase();
            let attach = '';
            if (m.attachment) attach = `<div class="mt-2"><img src="${ASSET_BASE}${m.attachment}" style="max-width:240px;border-radius:8px;cursor:pointer;" onclick="window.open(this.src)"></div>`;
            const time = new Date(m.created_at.replace(' ', 'T')).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
            return `<div class="neg-msg ${me ? 'me' : ''}">
                <div class="av" style="background:${avatarColor(m.sender_role)}">${initial}</div>
                <div>
                    <div class="name">${escape(m.sender_name || '')}<span class="role-tag role-${m.sender_role}">${roleLabel(m.sender_role)}</span></div>
                    <div class="bubble">${escape(m.message).replace(/\n/g, '<br>')}${attach}<div class="meta">${time}</div></div>
                </div>
            </div>`;
        }).join('');
        box.scrollTop = box.scrollHeight;
    }

    async function load() {
        try {
            const r = await fetch(API_GET);
            const d = await r.json();
            if (d.success) render(d.messages, d.current_user_id);
        } catch (e) {}
    }

    const form = document.getElementById('negSendForm');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const msg = document.getElementById('negMessage').value.trim();
            const file = document.getElementById('negAttach').files[0];
            if (!msg && !file) return;
            const fd = new FormData(form);
            try {
                const r = await fetch(API_SEND, { method: 'POST', body: fd });
                const d = await r.json();
                if (d.success) {
                    document.getElementById('negMessage').value = '';
                    document.getElementById('negAttach').value = '';
                    document.getElementById('negAttachPreview').textContent = '';
                    render(d.messages, d.current_user_id);
                } else {
                    alert(d.message || 'Lỗi gửi');
                }
            } catch (e) { alert('Lỗi kết nối'); }
        });

        document.getElementById('negAttach').addEventListener('change', function() {
            document.getElementById('negAttachPreview').textContent = this.files[0] ? '📎 ' + this.files[0].name : '';
        });
    }

    load();
    setInterval(load, 4000);
})();
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/admin.php';
?>
