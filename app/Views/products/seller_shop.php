<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="seller-shop-header py-5 text-white mb-5" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
    <div class="container py-4 text-center">
        <div class="seller-avatar-large mx-auto mb-3 bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 100px; height: 100px; font-size: 40px;">
            <?php if (!empty($seller['avatar'])): ?>
                <img src="<?= asset($seller['avatar']) ?>" alt="<?= e($seller['name']) ?>" class="w-100 h-100 rounded-circle" style="object-fit: cover;">
            <?php else: ?>
                <?= strtoupper(substr($seller['name'], 0, 1)) ?>
            <?php endif; ?>
        </div>
        <h2 class="display-5 fw-bold mb-1">Cửa hàng của <?= e($seller['name']) ?></h2>
        <p class="mb-0 opacity-75">
            <i class="fas fa-check-circle text-success bg-white rounded-circle p-1"></i> Người bán chuyên nghiệp
        </p>
    </div>
</div>

<div class="container mb-5 pb-5">
    <div class="d-flex justify-content-between align-items-end mb-4 border-bottom pb-3">
        <h3 class="fw-bold section-title mb-0"><i class="fas fa-box-open text-primary me-2"></i> Sản phẩm đang bán</h3>
        <span class="text-muted"><?= count($products) ?> sản phẩm</span>
    </div>

    <?php if (empty($products)): ?>
        <div class="text-center py-5 bg-white rounded-4 shadow-sm border">
            <div class="mb-3 text-muted">
                <i class="fas fa-box-open fa-4x opacity-50"></i>
            </div>
            <h4 class="fw-bold text-dark">Chưa có sản phẩm nào</h4>
            <p class="text-secondary mb-0">Người bán này hiện tại chưa đăng bán sản phẩm nào.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 col-6">
                    <div class="product-card card h-100 border-0 shadow-sm">
                        <div class="position-relative overflow-hidden">
                            <img src="<?= asset($product['thumbnail'] ?? 'images/no-image.png') ?>" class="card-img-top product-img" alt="<?= e($product['name']) ?>">
                            
                            <?php if ($product['is_featured']): ?>
                                <div class="product-badges">
                                    <span class="badge bg-danger">HOT</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="product-action-overlay d-flex align-items-center justify-content-center">
                                <a href="<?= url('/product/' . $product['slug']) ?>" class="btn btn-light btn-sm rounded-pill px-3 fw-bold text-primary">
                                    <i class="fas fa-shopping-cart"></i> Mua
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <h6 class="card-title product-title mb-2">
                                <a href="<?= url('/product/' . $product['slug']) ?>" class="text-dark text-decoration-none stretched-link">
                                    <?= e(Helper::truncate($product['name'], 50)) ?>
                                </a>
                            </h6>
                            <div class="mt-auto border-top pt-2">
                                <div class="product-price mb-1">
                                    <?php if ($product['sale_price']): ?>
                                        <span class="text-danger fw-bold d-block"><?= money($product['sale_price']) ?></span>
                                        <small class="text-muted text-decoration-line-through" style="font-size: 11px;"><?= money($product['price']) ?></small>
                                    <?php else: ?>
                                        <span class="text-primary fw-bold d-block"><?= money($product['price']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="stock-badge badge bg-light text-dark border w-100 text-start mt-1" style="font-size: 11px;">
                                    <i class="fas fa-box text-muted"></i> Kho: <?= $product['stock_quantity'] ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($products) == 20): // Simplified pagination check ?>
            <div class="text-center mt-5">
                <a href="?page=<?= $currentPage + 1 ?>" class="btn btn-outline-primary rounded-pill px-4">
                    Tải thêm sản phẩm <i class="fas fa-chevron-down ms-1"></i>
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
    /* Reuse Product Cards Style from Home */
    .section-title {
        position: relative;
        font-size: 1.5rem;
    }
    .product-card {
        transition: all 0.3s ease;
        border-radius: 8px;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
    }
    .product-img {
        height: 180px;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .product-card:hover .product-img {
        transform: scale(1.05);
    }
    .product-title {
        line-height: 1.4;
        height: 2.8em;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        font-size: 0.95rem;
    }
    .product-title a:hover {
        color: #8b5cf6 !important;
    }
    .product-badges {
        position: absolute;
        top: 8px;
        left: 8px;
        z-index: 2;
    }
    .product-action-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.3);
        opacity: 0;
        transition: all 0.3s ease;
        z-index: 3;
    }
    .product-card:hover .product-action-overlay {
        opacity: 1;
    }
    /* Floating chat bubble */
    #shopChatBubble {
        position: fixed;
        bottom: 28px;
        right: 28px;
        z-index: 1050;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg,#6366f1,#8b5cf6);
        box-shadow: 0 4px 20px rgba(99,102,241,0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: none;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    #shopChatBubble:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 28px rgba(99,102,241,0.55);
    }
    #shopChatBubble .chat-badge {
        position: absolute;
        top: -2px;
        right: -2px;
        width: 14px;
        height: 14px;
        background: #4ade80;
        border-radius: 50%;
        border: 2px solid #fff;
    }
</style>

<?php if (!isset($seller['id']) || !Auth::check() || Auth::id() != $seller['id']): ?>
<!-- Floating Chat Bubble -->
<button id="shopChatBubble" onclick="openShopChat()" title="Nhắn tin với cửa hàng">
    <i class="fas fa-comment-dots text-white" style="font-size:1.4rem;"></i>
    <span class="chat-badge"></span>
</button>

<!-- Chat Modal -->
<div class="modal fade" id="shopChatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content border-0" style="border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.15);">
            <!-- Header -->
            <div class="px-4 py-3 d-flex align-items-center justify-content-between" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-white d-flex align-items-center justify-content-center fw-bold" style="width:38px;height:38px;color:#6366f1;font-size:1rem;flex-shrink:0;">
                        <?= strtoupper(substr($seller['name'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="fw-semibold text-white" style="font-size:0.95rem;line-height:1.2;"><?= e($seller['name']) ?></div>
                        <div id="shopChatStatus" class="d-flex align-items-center gap-1" style="font-size:0.7rem;opacity:0.85;color:#fff;">
                            <span style="width:6px;height:6px;border-radius:50%;background:#4ade80;display:inline-block;"></span> Đang trực tuyến
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <!-- Messages -->
            <div class="modal-body p-0" style="background:#f8fafc;">
                <div id="shopChatHistory" class="px-3 py-3" style="height:400px;overflow-y:auto;display:flex;flex-direction:column;gap:8px;">
                    <div class="text-center text-muted py-5">
                        <div class="spinner-border spinner-border-sm mb-2" role="status"></div>
                        <div class="small">Đang tải...</div>
                    </div>
                </div>
            </div>
            <!-- Footer -->
            <div class="px-3 py-3 bg-white" style="border-top:1px solid #e9ecef;">
                <div class="d-flex align-items-end gap-2 rounded-3 px-3 py-2" style="background:#f1f5f9;">
                    <textarea id="shopChatInput" class="form-control border-0 bg-transparent flex-grow-1 p-0" placeholder="Nhập tin nhắn..." style="box-shadow:none;resize:none;min-height:22px;max-height:140px;font-size:0.875rem;line-height:1.5;" rows="1" oninput="this.style.height='';this.style.height=Math.min(this.scrollHeight,140)+'px'"></textarea>
                    <button id="shopChatSendBtn" class="btn rounded-circle d-flex align-items-center justify-content-center p-0 mb-1" style="width:32px;height:32px;flex-shrink:0;background:linear-gradient(135deg,#6366f1,#8b5cf6);border:none;color:#fff;">
                        <i class="fas fa-paper-plane" style="font-size:0.75rem;"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const _shopSellerId = <?= (int)$seller['id'] ?>;
    let _shopChatInterval = null;
    let _shopModal = null;

    function _shopTimeAgo(d) {
        if (!d) return '';
        const s = Math.floor((new Date() - new Date(d.replace(' ','T'))) / 1000);
        if (s < 60) return 'vài giây trước';
        if (s < 3600) return Math.floor(s/60) + ' phút trước';
        if (s < 86400) return Math.floor(s/3600) + ' giờ trước';
        return Math.floor(s/86400) + ' ngày trước';
    }

    function _shopRenderMsg(msg, uid) {
        const me = msg.sender_id == uid;
        const body = msg.message ? `<div style="font-size:0.875rem;white-space:pre-wrap;word-break:break-word;line-height:1.5;">${msg.message}</div>` : '';
        const t = new Date(msg.created_at.replace(' ','T')).toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});
        const s = me ? 'background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border-radius:18px 18px 4px 18px;' : 'background:#fff;color:#1e293b;border-radius:18px 18px 18px 4px;box-shadow:0 1px 4px rgba(0,0,0,0.08);';
        return `<div class="d-flex ${me?'justify-content-end':'justify-content-start'}"><div style="max-width:75%;padding:8px 14px;${s}">${body}<div style="font-size:0.6rem;opacity:0.6;text-align:${me?'right':'left'};margin-top:2px;">${t}</div></div></div>`;
    }

    function openShopChat() {
        <?php if (!Auth::check()): ?>
        Swal.fire({icon:'info',title:'Đăng nhập',text:'Vui lòng đăng nhập để nhắn tin',confirmButtonText:'Đăng nhập',showCancelButton:true})
            .then(r => { if (r.isConfirmed) window.location.href='<?= url('/login') ?>'; });
        return;
        <?php endif; ?>
        if (!_shopModal) _shopModal = new bootstrap.Modal(document.getElementById('shopChatModal'));
        _shopModal.show();
        _shopLoadMessages();
        if (_shopChatInterval) clearInterval(_shopChatInterval);
        _shopChatInterval = setInterval(_shopLoadMessages, 3000);
    }

    async function _shopLoadMessages() {
        const el = document.getElementById('shopChatHistory');
        try {
            const r = await fetch(`<?= url('/api/chat/messages') ?>?seller_id=${_shopSellerId}`);
            if (!r.ok) throw new Error();
            const d = await r.json();
            if (!d.success) { el.innerHTML = `<div class="text-center text-danger small my-5">${d.message||'Lỗi'}</div>`; return; }
            const bot = el.scrollHeight - el.scrollTop <= el.clientHeight + 60;
            const st = document.getElementById('shopChatStatus');
            if (st) {
                const dot = d.is_online ? '#4ade80' : '#9ca3af';
                const txt = d.is_online ? 'Đang trực tuyến' : (d.last_active_at ? 'Hoạt động ' + _shopTimeAgo(d.last_active_at) : 'Ngoại tuyến');
                st.innerHTML = `<span style="width:6px;height:6px;border-radius:50%;background:${dot};display:inline-block;"></span> ${txt}`;
            }
            el.innerHTML = d.messages.length === 0
                ? '<div class="text-center text-muted small" style="margin-top:80px;"><i class="far fa-comment-dots" style="font-size:2rem;opacity:0.2;display:block;margin-bottom:8px;"></i>Hãy bắt đầu cuộc trò chuyện!</div>'
                : d.messages.map(m => _shopRenderMsg(m, d.current_user_id)).join('');
            if (bot) el.scrollTop = el.scrollHeight;
        } catch(e) {
            if (el.querySelector('.spinner-border')) el.innerHTML = '<div class="text-center text-danger small my-5">Lỗi kết nối</div>';
        }
    }

    async function _shopSendMessage() {
        const inp = document.getElementById('shopChatInput');
        const btn = document.getElementById('shopChatSendBtn');
        if (!inp.value.trim()) return;
        const ob = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size:0.7rem;"></i>';
        try {
            const fd = new FormData();
            fd.append('seller_id', _shopSellerId);
            fd.append('message', inp.value.trim());
            fd.append('csrf_token', '<?= csrf_token() ?>');
            const r = await fetch('<?= url('/api/chat/send') ?>', {method:'POST',body:fd});
            const d = await r.json();
            if (d.success) {
                inp.value = ''; inp.style.height = '22px';
                if (d.messages && d.current_user_id) {
                    const el = document.getElementById('shopChatHistory');
                    el.innerHTML = d.messages.map(m => _shopRenderMsg(m, d.current_user_id)).join('');
                    el.scrollTop = el.scrollHeight;
                }
            } else {
                Swal.fire({icon:'error',title:'Lỗi',text:d.message||'Không thể gửi',toast:true,position:'top-end',showConfirmButton:false,timer:3000});
            }
        } catch(e) {
            Swal.fire({icon:'error',title:'Lỗi',text:'Lỗi kết nối',toast:true,position:'top-end',showConfirmButton:false,timer:3000});
        } finally {
            btn.disabled = false; btn.innerHTML = ob;
            inp.focus();
        }
    }

    document.getElementById('shopChatSendBtn').addEventListener('click', _shopSendMessage);
    document.getElementById('shopChatInput').addEventListener('keydown', e => {
        if (e.key==='Enter' && !e.shiftKey) { e.preventDefault(); _shopSendMessage(); }
    });
    document.getElementById('shopChatModal').addEventListener('hidden.bs.modal', () => {
        if (_shopChatInterval) clearInterval(_shopChatInterval);
        _shopChatInterval = null;
    });
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
