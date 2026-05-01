<?php
if (Auth::check()) {
    Auth::updateLastActive();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $meta_description ?? 'AI CỦA TÔI - Nền tảng mua bán sản phẩm số uy tín #1 Việt Nam. Chuyên cung cấp tài khoản, key phần mềm, khóa học với giá tốt nhất.' ?>">
    <meta name="keywords" content="<?= $meta_keywords ?? 'mmo, sản phẩm số, tài khoản game, key phần mềm, khóa học online, marketplace' ?>">
    <meta name="author" content="AI CỦA TÔI">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= url($_SERVER['REQUEST_URI'] ?? '/') ?>">
    <meta property="og:title" content="<?= $title ?? 'AI CỦA TÔI' ?>">
    <meta property="og:description" content="<?= $meta_description ?? 'AI CỦA TÔI - Nền tảng mua bán sản phẩm số uy tín #1 Việt Nam.' ?>">
    <meta property="og:image" content="<?= $og_image ?? asset('images/default-og.jpg') ?>">
    <title><?= $title ?? 'AI CỦA TÔI' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-success" href="<?= url('/') ?>">
                <i class="fas fa-store"></i> AI CỦA TÔI
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <?php
                    $menuModel = new Menu();
                    $menuTree = $menuModel->getTree();
                    foreach ($menuTree as $menu):
                        if (empty($menu['children'])):
                    ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url($menu['url']) ?>">
                                <?php if ($menu['icon']): ?><i class="<?= e($menu['icon']) ?>"></i><?php endif; ?>
                                <?= e($menu['title']) ?>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="<?= url($menu['url']) ?>" id="menuDropdown<?= $menu['id'] ?>" role="button" data-bs-toggle="dropdown">
                                <?php if ($menu['icon']): ?><i class="<?= e($menu['icon']) ?>"></i><?php endif; ?>
                                <?= e($menu['title']) ?>
                            </a>
                            <ul class="dropdown-menu border-0 shadow-sm">
                                <?php foreach ($menu['children'] as $child): ?>
                                <li><a class="dropdown-item py-2" href="<?= url($child['url']) ?>">
                                    <?php
                                    $iconClass = !empty($child['icon']) ? $child['icon'] : 'fas fa-chevron-right';
                                    if (strpos($iconClass, ' ') === false) {
                                        $iconClass = 'fas ' . $iconClass;
                                        if (strpos($iconClass, 'fa-bitcoin') !== false) {
                                            $iconClass = 'fab fa-bitcoin';
                                        }
                                    }
                                    ?>
                                    <i class="<?= $iconClass ?> text-success" style="width: 20px;"></i> <?= e($child['title']) ?>
                                </a></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </ul>

                <ul class="navbar-nav align-items-center">
                    <?php if (Auth::check()): ?>
                        <?php
                        $headerWalletService = new WalletService();
                        $headerWallet = $headerWalletService->getWallet(Auth::id());
                        $headerWalletBalance = $headerWallet['balance'] ?? 0;
                        ?>
                        <li class="nav-item me-2 position-relative">
                            <?php if (Auth::isSeller() || Auth::isAdmin()): ?>
                            <a class="btn btn-outline-secondary rounded-pill p-2 d-flex align-items-center justify-content-center position-relative" href="<?= Auth::isSeller() ? url('/seller/chat') : url('/messages') ?>" style="width: 40px; height: 40px;">
                                <i class="fas fa-comment-dots"></i>
                                <?php $unreadCount = (new Conversation())->getTotalUnread(Auth::id()); ?>
                                <span id="globalUnreadBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger <?= $unreadCount > 0 ? '' : 'd-none' ?>" style="font-size: 0.6rem;">
                                    <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
                                </span>
                            </a>
                            <?php else: ?>
                            <?php $unreadCount = (new Conversation())->getTotalUnread(Auth::id()); ?>
                            <button id="inboxToggleBtn" onclick="toggleInboxWidget()" class="btn btn-outline-secondary rounded-pill p-2 d-flex align-items-center justify-content-center position-relative" style="width: 40px; height: 40px; border: none;">
                                <i class="fas fa-comment-dots"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger <?= $unreadCount > 0 ? '' : 'd-none' ?>" style="font-size: 0.6rem;" id="inboxUnreadBadge">
                                    <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
                                </span>
                            </button>
                            <?php endif; ?>
                        </li>

                        <li class="nav-item me-2">
                            <a class="btn btn-success rounded-pill px-3 py-2 fw-bold d-flex align-items-center gap-2" href="<?= url('/user/wallet') ?>" style="font-size: 0.95rem;">
                                <i class="fas fa-wallet"></i>
                                <span><?= compact_money($headerWalletBalance) ?></span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle fs-3 text-secondary"></i>
                                <span><?= e(Auth::user()['name']) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if (Auth::isAdmin()): ?>
                                    <li><a class="dropdown-item" href="<?= url('/admin/dashboard') ?>"><i class="fas fa-tachometer-alt"></i> Admin</a></li>
                                <?php endif; ?>
                                <?php if (Auth::isSeller()): ?>
                                    <li><a class="dropdown-item" href="<?= url('/seller/dashboard') ?>"><i class="fas fa-store"></i> Seller</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?= url('/user/dashboard') ?>"><i class="fas fa-home"></i> Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?= url('/user/orders') ?>"><i class="fas fa-box text-success"></i> Đơn hàng</a></li>
                                <li><a class="dropdown-item" href="<?= url('/user/favorites') ?>"><i class="fas fa-heart text-danger"></i> Sản phẩm yêu thích</a></li>
                                <li><a class="dropdown-item" href="<?= url('/user/disputes') ?>"><i class="fas fa-balance-scale text-danger"></i> Khiếu nại</a></li>
                                <li><a class="dropdown-item" href="<?= url('/user/wallet') ?>"><i class="fas fa-wallet text-info"></i> Ví tiền</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= url('/logout') ?>"><i class="fas fa-sign-out-alt text-secondary"></i> Đăng xuất</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/login') ?>"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-success text-white ms-2" href="<?= url('/register') ?>">Đăng ký</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (Session::hasFlash('success')): ?>
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: '<?= e(Session::getFlash('success')) ?>',
                    timer: 3000,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true
                });
            });
        </script>
    <?php endif; ?>

    <?php if (Session::hasFlash('error')): ?>
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: '<?= e(Session::getFlash('error')) ?>',
                    confirmButtonColor: '#d33'
                });
            });
        </script>
    <?php endif; ?>

<?php if (Auth::check() && !Auth::isSeller() && !Auth::isAdmin()): ?>
<style>
#inboxWidget { position: fixed; bottom: 24px; right: 24px; width: 720px; max-width: calc(100vw - 32px); height: 500px; background: #fff; border-radius: 16px; box-shadow: 0 8px 40px rgba(0,0,0,0.18); display: none; z-index: 9999; overflow: hidden; flex-direction: row; }
#inboxWidget.open { display: flex; }
#inboxConvList { width: 260px; flex-shrink: 0; border-right: 1px solid #e9ecef; display: flex; flex-direction: column; background: #fafafa; }
#inboxConvListBody { flex: 1; overflow-y: auto; }
.inbox-conv-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f1f5f9; transition: background 0.15s; }
.inbox-conv-item:hover, .inbox-conv-item.active { background: #eff0ff; }
.inbox-conv-avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff; font-weight: 700; font-size: 1rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.inbox-conv-name { font-size: 0.82rem; font-weight: 600; color: #1e293b; }
.inbox-conv-preview { font-size: 0.72rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px; }
.inbox-conv-time { font-size: 0.65rem; color: #94a3b8; margin-left: auto; white-space: nowrap; }
#inboxChatPanel { flex: 1; display: flex; flex-direction: column; min-width: 0; }
#inboxChatMessages { flex: 1; overflow-y: auto; padding: 12px; display: flex; flex-direction: column; gap: 8px; background: #f8fafc; }
#inboxChatEmpty { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #94a3b8; font-size: 0.85rem; }
#inboxChatHeader { display: none; }
#inboxChatHeader.visible { display: flex; }
</style>

<div id="inboxWidget">
    <div id="inboxConvList">
        <div class="px-3 py-2 d-flex align-items-center justify-content-between" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);min-height:52px;">
            <span class="text-white fw-semibold" style="font-size:0.9rem;"><i class="fas fa-inbox me-2"></i>Hộp thư</span>
            <button onclick="toggleInboxWidget()" class="btn p-0 text-white opacity-75" style="font-size:1.1rem;background:none;border:none;">&times;</button>
        </div>
        <div id="inboxConvListBody">
            <div class="text-center text-muted py-4" style="font-size:0.8rem;">
                <div class="spinner-border spinner-border-sm mb-2" role="status"></div><br>Đang tải...
            </div>
        </div>
    </div>
    <div id="inboxChatPanel">
        <div id="inboxChatHeader" class="px-3 py-2 align-items-center gap-2" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);min-height:52px;">
            <div id="inboxChatAvatar" class="rounded-circle bg-white d-flex align-items-center justify-content-center fw-bold" style="width:32px;height:32px;color:#6366f1;font-size:0.9rem;flex-shrink:0;"></div>
            <div>
                <div id="inboxChatName" class="text-white fw-semibold" style="font-size:0.85rem;line-height:1.2;"></div>
                <div id="inboxChatStatus" style="font-size:0.65rem;color:rgba(255,255,255,0.8);"></div>
            </div>
        </div>
        <div id="inboxChatMessages">
            <div id="inboxChatEmpty">
                <i class="far fa-comment-dots" style="font-size:2.5rem;opacity:0.15;margin-bottom:8px;"></i>
                Chọn cuộc trò chuyện để xem tin nhắn
            </div>
        </div>
        <div id="inboxChatInput" style="display:none;padding:10px 12px;border-top:1px solid #e9ecef;background:#fff;position:relative;">
            <div id="inboxImagePreviewContainer" class="d-none mb-2">
                <div class="d-flex align-items-center bg-white border rounded-3 p-2 shadow-sm" style="max-width:300px;gap:8px;">
                    <div class="flex-shrink-0" style="width:48px;height:48px;border-radius:6px;overflow:hidden;background:#f0f0f0;">
                        <img id="inboxImagePreview" src="" style="width:100%;height:100%;object-fit:cover;">
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div id="inboxAttachFileName" class="fw-semibold text-dark text-truncate" style="font-size:0.78rem;"></div>
                        <div id="inboxAttachFileSize" class="text-muted" style="font-size:0.7rem;"></div>
                    </div>
                    <button class="btn btn-danger rounded-circle p-0 d-flex align-items-center justify-content-center flex-shrink-0" style="width:22px;height:22px;border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,0.2);" onclick="_iwRemoveAttachment()" title="Xóa ảnh">
                        <i class="fas fa-times" style="font-size:8px;"></i>
                    </button>
                </div>
            </div>
            <div class="d-flex align-items-end gap-2">
                <input type="file" id="inboxAttachmentInput" accept="image/*" data-skip-default-preview="true" class="d-none" onchange="_iwPreviewAttachment(this)">
                <button class="btn btn-light rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" onclick="document.getElementById('inboxAttachmentInput').click()" title="Đính kèm ảnh" style="width:36px;height:36px;border:1.5px solid #dee2e6;flex-shrink:0;">
                    <i class="fas fa-paperclip text-secondary" style="font-size:0.8rem;"></i>
                </button>
                <div class="flex-grow-1">
                    <textarea id="inboxMsgInput" class="form-control" placeholder="Nhập tin nhắn..." style="resize:none;min-height:36px;max-height:100px;border-radius:18px;padding:7px 14px;border:1.5px solid #dee2e6;box-shadow:none;font-size:0.82rem;line-height:1.5;transition:border-color 0.2s;" rows="1" oninput="this.style.height='';this.style.height=Math.min(this.scrollHeight,100)+'px'" onfocus="this.style.borderColor='#198754'" onblur="this.style.borderColor='#dee2e6'"></textarea>
                </div>
                <button id="inboxSendBtn" class="btn rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:38px;height:38px;background:#198754;border:none;color:#fff;box-shadow:0 2px 8px rgba(25,135,84,0.35);transition:background 0.2s;" onmouseover="this.style.background='#157347'" onmouseout="this.style.background='#198754'">
                    <i class="fas fa-paper-plane" style="font-size:0.75rem;transform:rotate(-10deg);"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    let _iw = { open: false, activeSellerId: null, activeConvId: null, pollTimer: null, attachment: null };

    function _iwFormatSize(b) {
        if (b < 1024) return b + ' B';
        if (b < 1048576) return (b / 1024).toFixed(2) + ' KB';
        return (b / 1048576).toFixed(2) + ' MB';
    }

    window._iwPreviewAttachment = function(input) {
        if (input.files && input.files[0]) {
            _iw.attachment = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('inboxImagePreview').src = e.target.result;
                document.getElementById('inboxAttachFileName').textContent = _iw.attachment.name;
                document.getElementById('inboxAttachFileSize').textContent = _iwFormatSize(_iw.attachment.size);
                document.getElementById('inboxImagePreviewContainer').classList.remove('d-none');
            };
            reader.readAsDataURL(_iw.attachment);
        }
    };

    window._iwRemoveAttachment = function() {
        _iw.attachment = null;
        document.getElementById('inboxAttachmentInput').value = '';
        document.getElementById('inboxImagePreviewContainer').classList.add('d-none');
        document.getElementById('inboxImagePreview').src = '';
        document.getElementById('inboxAttachFileName').textContent = '';
        document.getElementById('inboxAttachFileSize').textContent = '';
    };

    function _iwTimeAgo(d) {
        if (!d) return '';
        const s = Math.floor((new Date() - new Date(d.replace(' ', 'T'))) / 1000);
        if (s < 60) return 'vừa xong';
        if (s < 3600) return Math.floor(s / 60) + ' phút';
        if (s < 86400) return Math.floor(s / 3600) + ' giờ';
        return Math.floor(s / 86400) + ' ngày';
    }

    function _iwRenderMsg(msg, uid) {
        const me = msg.sender_id == uid;
        let attachHtml = '';
        if (msg.attachment) {
            attachHtml = `<div class="mb-1"><img src="<?= asset('') ?>${msg.attachment}" class="img-fluid rounded border shadow-sm" style="max-height: 120px; cursor:pointer;" onclick="window.open(this.src)"></div>`;
        }
        let body = '';
        if (msg.message && msg.message !== '[Tệp đính kèm]') {
            body = `<div style="font-size:0.82rem;white-space:pre-wrap;word-break:break-word;line-height:1.5;">${msg.message}</div>`;
        } else if (msg.message === '[Tệp đính kèm]' && !msg.attachment) {
            body = `<div style="font-size:0.82rem;font-style:italic;opacity:0.8;">[Tệp đính kèm]</div>`;
        }
        const t = new Date(msg.created_at.replace(' ', 'T')).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
        const s = me ? 'background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border-radius:14px 14px 3px 14px;' : 'background:#fff;color:#1e293b;border-radius:14px 14px 14px 3px;box-shadow:0 1px 3px rgba(0,0,0,0.08);';
        return `<div class="d-flex ${me ? 'justify-content-end' : 'justify-content-start'}"><div style="max-width:85%;padding:6px 12px;${s}">${attachHtml}${body}<div style="font-size:0.55rem;opacity:0.6;text-align:${me ? 'right' : 'left'};margin-top:2px;">${t}</div></div></div>`;
    }

    window.toggleInboxWidget = function() {
        _iw.open = !_iw.open;
        document.getElementById('inboxWidget').classList.toggle('open', _iw.open);
        if (_iw.open) {
            _iwLoadConvList();
        } else if (_iw.pollTimer) {
            clearInterval(_iw.pollTimer);
        }
    };

    async function _iwLoadConvList() {
        const body = document.getElementById('inboxConvListBody');
        try {
            const r = await fetch('<?= url('/api/chat/list') ?>');
            const d = await r.json();
            if (!d.success || !d.conversations.length) {
                body.innerHTML = '<div class="text-center text-muted py-5" style="font-size:0.78rem;"><i class="far fa-comment-dots" style="font-size:1.5rem;opacity:0.2;display:block;margin-bottom:6px;"></i>Chưa có cuộc trò chuyện</div>';
                return;
            }
            body.innerHTML = d.conversations.map(c => {
                const isBuyer = c.buyer_id == <?= Auth::id() ?>;
                const otherId = isBuyer ? c.seller_id : c.buyer_id;
                const otherName = c.other_name || c.other_username || '?';
                const initials = otherName.charAt(0).toUpperCase();
                const preview = (c.last_message || '').substring(0, 35) + ((c.last_message || '').length > 35 ? '…' : '');
                const time = c.updated_at ? _iwTimeAgo(c.updated_at) + ' trước' : '';
                const unread = isBuyer ? (c.unread_count_buyer || 0) : (c.unread_count_seller || 0);
                const isActive = _iw.activeSellerId == otherId ? 'active' : '';
                const convIdParam = typeof c.id === 'string' ? `'${c.id}'` : c.id;
                return `<div class="inbox-conv-item ${isActive}" onclick="_iwOpenChat(${otherId},'${otherName.replace(/'/g, "\\'")}',${convIdParam})" data-seller-id="${otherId}">
                    <div class="inbox-conv-avatar">${initials}</div>
                    <div style="min-width:0;flex:1;">
                        <div class="inbox-conv-name">${otherName}${unread > 0 ? `<span class="badge bg-danger ms-1" style="font-size:0.55rem;">${unread}</span>` : ''}</div>
                        <div class="inbox-conv-preview">${preview || '<em style="opacity:0.5">Chưa có tin nhắn</em>'}</div>
                    </div>
                    <div class="inbox-conv-time">${time}</div>
                </div>`;
            }).join('');
        } catch (e) {
            body.innerHTML = '<div class="text-center text-danger py-4" style="font-size:0.78rem;">Lỗi kết nối</div>';
        }
    }

    window._iwOpenChat = async function(sellerId, sellerName, convId) {
        _iw.activeSellerId = sellerId;
        _iw.activeConvId = convId;
        document.querySelectorAll('.inbox-conv-item').forEach(el => {
            el.classList.toggle('active', el.dataset.sellerId == sellerId);
        });
        document.getElementById('inboxChatHeader').classList.add('visible');
        document.getElementById('inboxChatAvatar').textContent = sellerName.charAt(0).toUpperCase();
        document.getElementById('inboxChatName').textContent = sellerName;
        document.getElementById('inboxChatStatus').innerHTML = '<span style="opacity:0.6;">Đang tải...</span>';
        document.getElementById('inboxChatInput').style.display = 'block';
        document.getElementById('inboxChatMessages').innerHTML = '<div style="flex:1;display:flex;align-items:center;justify-content:center;"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></div>';
        await _iwLoadMessages();
        if (_iw.pollTimer) clearInterval(_iw.pollTimer);
        _iw.pollTimer = setInterval(_iwLoadMessages, 3000);
    };

    async function _iwLoadMessages() {
        if (!_iw.activeSellerId) return;
        const el = document.getElementById('inboxChatMessages');
        try {
            const r = await fetch(`<?= url('/api/chat/messages') ?>?seller_id=${_iw.activeSellerId}`);
            const d = await r.json();
            if (!d.success) return;
            const bot = el.scrollHeight - el.scrollTop <= el.clientHeight + 60;
            const st = document.getElementById('inboxChatStatus');
            if (st) {
                if (d.is_online) {
                    st.innerHTML = '<span style="width:6px;height:6px;border-radius:50%;background:#4ade80;display:inline-block;margin-right:4px;"></span>Đang trực tuyến';
                } else if (d.last_active_at) {
                    st.innerHTML = '<span style="width:6px;height:6px;border-radius:50%;background:#9ca3af;display:inline-block;margin-right:4px;"></span>Hoạt động ' + _iwTimeAgo(d.last_active_at) + ' trước';
                } else {
                    st.innerHTML = '<span style="width:6px;height:6px;border-radius:50%;background:#9ca3af;display:inline-block;margin-right:4px;"></span>Ngoại tuyến';
                }
            }
            const msgs = d.messages.map(m => _iwRenderMsg(m, d.current_user_id)).join('');
            const empty = document.getElementById('inboxChatEmpty');
            if (empty) empty.style.display = 'none';
            el.innerHTML = msgs || '<div class="text-center text-muted" style="margin-top:60px;font-size:0.78rem;"><i class="far fa-comment-dots" style="font-size:1.5rem;opacity:0.15;display:block;margin-bottom:6px;"></i>Hãy bắt đầu cuộc trò chuyện!</div>';
            if (bot) el.scrollTop = el.scrollHeight;
        } catch (e) {}
    }

    async function _iwSend() {
        const inp = document.getElementById('inboxMsgInput');
        const btn = document.getElementById('inboxSendBtn');
        const message = inp.value.trim();
        if ((!message && !_iw.attachment) || !_iw.activeSellerId) return;
        const ob = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size:0.55rem;"></i>';
        try {
            const fd = new FormData();
            fd.append('seller_id', _iw.activeSellerId);
            fd.append('message', message);
            if (_iw.attachment) fd.append('attachment', _iw.attachment);
            fd.append('csrf_token', '<?= csrf_token() ?>');
            const r = await fetch('<?= url('/api/chat/send') ?>', { method:'POST', body:fd });
            const d = await r.json();
            if (d.success) {
                window._iwRemoveAttachment();
                inp.value = '';
                inp.style.height = '20px';
                if (d.messages) {
                    const el = document.getElementById('inboxChatMessages');
                    el.innerHTML = d.messages.map(m => _iwRenderMsg(m, d.current_user_id)).join('');
                    el.scrollTop = el.scrollHeight;
                }
                _iwLoadConvList();
            }
        } catch (e) {
        } finally {
            btn.disabled = false;
            btn.innerHTML = ob;
            inp.focus();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('inboxSendBtn').addEventListener('click', _iwSend);
        document.getElementById('inboxMsgInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                _iwSend();
            }
        });
        document.addEventListener('click', function(e) {
            const w = document.getElementById('inboxWidget');
            const btn = document.getElementById('inboxToggleBtn');
            if (_iw.open && !w.contains(e.target) && btn && !btn.contains(e.target)) {
                toggleInboxWidget();
            }
        });
    });
})();
</script>
<?php endif; ?>

<?php if (Auth::check()): ?>
<script>
(function() {
    // Tiếng chuông thông báo
    const bellSound = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
    let lastUnreadCount = <?= (new Conversation())->getTotalUnread(Auth::id()) ?>;
    let lastNpcMessageId = null;

    async function checkNotifications() {
        try {
            const response = await fetch('<?= url('/api/notifications/check') ?>');
            const data = await response.json();
            
            if (data.success) {
                // Cập nhật badge trên menu
                const badges = document.querySelectorAll('#globalUnreadBadge, #inboxUnreadBadge');
                badges.forEach(badge => {
                    if (data.unread_count > 0) {
                        badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                        badge.classList.remove('d-none');
                    } else {
                        badge.classList.add('d-none');
                    }
                });

                // Phát tiếng chuông nếu có tin nhắn NPC mới HOẶC tổng số tin nhắn chưa đọc tăng lên
                let shouldPlay = false;
                if (data.has_new_npc_message && data.last_npc_message_id !== lastNpcMessageId) {
                    shouldPlay = true;
                    lastNpcMessageId = data.last_npc_message_id;
                } else if (data.unread_count > lastUnreadCount) {
                    shouldPlay = true;
                }

                if (shouldPlay) {
                    bellSound.play().catch(e => {
                        // Trình duyệt có thể chặn autoplay nếu user chưa tương tác
                        console.log('Thông báo: Có tin nhắn mới nhưng tiếng chuông bị trình duyệt chặn.');
                    });
                }
                
                lastUnreadCount = data.unread_count;
            }
        } catch (error) {
            // Lỗi nhẹ, không cần báo user
        }
    }

    // Kiểm tra mỗi 10 giây
    setInterval(checkNotifications, 10000);
})();
</script>
<?php endif; ?>
