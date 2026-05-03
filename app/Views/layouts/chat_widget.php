<?php if (Auth::check()): ?>
<!-- Floating Chat Bubble -->
<button onclick="toggleInboxWidget()" class="floating-chat-bubble shadow-lg" id="globalChatBubble" title="Chat với chúng tôi">
    <i class="fas fa-comment-dots"></i>
    <?php $unreadCount = (new Conversation())->getTotalUnread(Auth::id()); ?>
    <span class="unread-dot <?= $unreadCount > 0 ? '' : 'd-none' ?>" id="bubbleUnreadDot"></span>
</button>

<style>
.floating-chat-bubble {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    border: none;
    cursor: pointer;
    z-index: 9998;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.floating-chat-bubble:hover { transform: scale(1.1) rotate(5deg); }
.unread-dot {
    position: absolute;
    top: 0;
    right: 0;
    width: 14px;
    height: 14px;
    background: #ef4444;
    border: 2px solid white;
    border-radius: 50%;
}

#inboxWidget { 
    position: fixed; 
    bottom: 100px; 
    right: 30px; 
    width: 750px; 
    max-width: calc(100vw - 40px); 
    height: 600px; 
    background: #fff; 
    border-radius: 20px; 
    box-shadow: 0 15px 50px rgba(0,0,0,0.15); 
    display: none; 
    z-index: 9999; 
    overflow: hidden; 
    flex-direction: row; 
    border: 1px solid rgba(0,0,0,0.05);
    backdrop-filter: blur(10px);
}
#inboxWidget.open { display: flex; animation: iw-bounceIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
@keyframes iw-bounceIn {
    0% { opacity: 0; transform: scale(0.7) translateY(100px); transform-origin: bottom right; }
    100% { opacity: 1; transform: scale(1) translateY(0); transform-origin: bottom right; }
}
#inboxConvList { width: 280px; flex-shrink: 0; border-right: 1px solid #edf2f7; display: flex; flex-direction: column; background: #fbfcfe; }
#inboxConvListBody { flex: 1; overflow-y: auto; }
.inbox-conv-item { display: flex; align-items: center; gap: 12px; padding: 14px 18px; cursor: pointer; border-bottom: 1px solid #f1f5f9; transition: all 0.2s; }
.inbox-conv-item:hover { background: #f1f4ff; }
.inbox-conv-item.active { background: #eef2ff; border-left: 4px solid #6366f1; }
.inbox-conv-avatar { width: 42px; height: 42px; border-radius: 14px; background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff; font-weight: 700; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 12px rgba(99,102,241,0.2); }
.inbox-conv-name { font-size: 0.88rem; font-weight: 700; color: #1e293b; margin-bottom: 2px; }
.inbox-conv-preview { font-size: 0.78rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px; }
.inbox-conv-time { font-size: 0.68rem; color: #94a3b8; margin-left: auto; white-space: nowrap; }
#inboxChatPanel { flex: 1; display: flex; flex-direction: column; min-width: 0; background: #fff; }
#inboxChatMessages { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 12px; background: #f8fafc; }
#inboxChatEmpty { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #94a3b8; font-size: 0.9rem; text-align: center; }
#inboxChatHeader { display: none; padding: 14px 20px; align-items: center; gap: 12px; background: linear-gradient(135deg,#6366f1,#8b5cf6); color: white; }
#inboxChatHeader.visible { display: flex; }

.article-media { margin: 15px 0; }
.article-media-frame { border-radius: 12px; overflow: hidden; border: 1px solid #eee; }
.article-media-frame img { width: 100%; height: auto; display: block; }

@media (max-width: 768px) {
    .floating-chat-bubble { bottom: 85px; right: 20px; width: 50px; height: 50px; font-size: 1.2rem; }
    #inboxWidget { 
        bottom: 0; 
        right: 0; 
        width: 100vw; 
        height: 100vh; 
        max-width: none; 
        border-radius: 0; 
    }
    #inboxConvList { width: 100%; }
    #inboxChatPanel { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 100; display: none; }
    #inboxChatPanel.mobile-show { display: flex; }
}
</style>

<div id="inboxWidget">
    <div id="inboxConvList">
        <div class="px-3 py-3 d-flex align-items-center justify-content-between" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);min-height:60px;">
            <span class="text-white fw-bold" style="font-size:1.1rem;"><i class="fas fa-inbox me-2"></i>Tin nhắn</span>
            <button onclick="toggleInboxWidget()" class="btn p-0 text-white opacity-75" style="font-size:1.5rem;background:none;border:none;line-height:1;">&times;</button>
        </div>
        <div class="px-3 py-2 border-bottom bg-white">
            <button onclick="_iwOpenChat(<?= Helper::getSystemUserId() ?>, '<?= e(Helper::getSystemDisplayName()) ?>', 'npc')" class="btn btn-sm btn-primary w-100 rounded-pill shadow-sm py-2" style="font-size:0.82rem; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border:none; font-weight:600;">
                <i class="fas fa-robot me-2"></i> Chat với <?= e(Helper::getSystemDisplayName()) ?>
            </button>
        </div>
        <div id="inboxConvListBody">
            <div class="text-center text-muted py-5" style="font-size:0.9rem;">
                <div class="spinner-border spinner-border-sm mb-2" role="status"></div><br>Đang tải...
            </div>
        </div>
    </div>
    <div id="inboxChatPanel">
        <div id="inboxChatHeader" class="px-3 py-2 align-items-center gap-2" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);min-height:60px;">
            <button class="btn p-0 text-white me-2 d-md-none" onclick="document.getElementById('inboxChatPanel').classList.remove('mobile-show')">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div id="inboxChatAvatar" class="rounded-circle bg-white d-flex align-items-center justify-content-center fw-bold" style="width:38px;height:38px;color:#6366f1;font-size:1rem;flex-shrink:0;"></div>
            <div>
                <div id="inboxChatName" class="text-white fw-bold" style="font-size:0.95rem;line-height:1.2;"></div>
                <div id="inboxChatStatus" style="font-size:0.75rem;color:rgba(255,255,255,0.9);"></div>
            </div>
            <button onclick="toggleInboxWidget()" class="btn p-0 text-white opacity-75 ms-auto d-none d-md-block" style="font-size:1.5rem;background:none;border:none;line-height:1;">&times;</button>
        </div>
        <div id="inboxChatMessages">
            <div id="inboxChatEmpty">
                <i class="far fa-comment-dots" style="font-size:3.5rem;opacity:0.1;margin-bottom:15px;"></i>
                Chọn cuộc trò chuyện để xem tin nhắn
            </div>
        </div>
        <div id="inboxChatInput" style="display:none;padding:15px 20px;border-top:1px solid #edf2f7;background:#fff;position:relative;">
            <div id="inboxImagePreviewContainer" class="d-none mb-3">
                <div class="d-flex align-items-center bg-white border rounded-4 p-2 shadow-sm" style="max-width:320px;gap:12px;border: 1px solid #e2e8f0 !important;">
                    <div class="flex-shrink-0" style="width:56px;height:56px;border-radius:10px;overflow:hidden;background:#f8fafc;">
                        <img id="inboxImagePreview" src="" style="width:100%;height:100%;object-fit:cover;">
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div id="inboxAttachFileName" class="fw-bold text-dark text-truncate" style="font-size:0.85rem;"></div>
                        <div id="inboxAttachFileSize" class="text-muted" style="font-size:0.75rem;"></div>
                    </div>
                    <button class="btn btn-danger rounded-circle p-0 d-flex align-items-center justify-content-center flex-shrink-0" style="width:24px;height:24px;border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.15);" onclick="_iwRemoveAttachment()" title="Xóa ảnh">
                        <i class="fas fa-times" style="font-size:10px;"></i>
                    </button>
                </div>
            </div>
            <div class="d-flex align-items-end gap-2">
                <input type="file" id="inboxAttachmentInput" accept="image/*" data-skip-default-preview="true" class="d-none" onchange="_iwPreviewAttachment(this)">
                <button class="btn btn-light rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" onclick="document.getElementById('inboxAttachmentInput').click()" title="Đính kèm ảnh" style="width:42px;height:42px;border:1.5px solid #e2e8f0;background:#f8fafc;">
                    <i class="fas fa-paperclip text-secondary" style="font-size:1rem;"></i>
                </button>
                <div class="flex-grow-1">
                    <textarea id="inboxMsgInput" class="form-control" placeholder="Nhập tin nhắn..." style="resize:none;min-height:42px;max-height:120px;border-radius:21px;padding:9px 18px;border:1.5px solid #e2e8f0;box-shadow:none;font-size:0.9rem;line-height:1.5;transition:all 0.2s;background:#f8fafc;" rows="1" oninput="this.style.height='';this.style.height=Math.min(this.scrollHeight,120)+'px'" onfocus="this.style.borderColor='#8b5cf6';this.style.background='#fff'" onblur="this.style.borderColor='#e2e8f0';this.style.background='#f8fafc'"></textarea>
                </div>
                <button id="inboxSendBtn" class="btn rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px;background:#8b5cf6;border:none;color:#fff;box-shadow:0 4px 12px rgba(139,92,246,0.3);transition:all 0.2s;">
                    <i class="fas fa-paper-plane" style="font-size:0.9rem;transform:rotate(-10deg);"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    let _iw = { 
        open: false, 
        activeOtherId: null, 
        activeConvId: null, 
        pollTimer: null, 
        attachment: null,
        lastUnreadCount: <?= (new Conversation())->getTotalUnread(Auth::id()) ?>,
        lastNpcMessageId: null,
        activeLastMessageId: null,
        bellSound: new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3')
    };

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
            attachHtml = `<div class="mb-2"><img src="<?= asset('') ?>${msg.attachment}" class="img-fluid rounded-3 border shadow-sm" style="max-height: 180px; cursor:pointer;" onclick="window.open(this.src)"></div>`;
        }
        let body = '';
        if (msg.message && msg.message !== '[Tệp đính kèm]') {
            body = `<div style="font-size:0.9rem;white-space:pre-wrap;word-break:break-word;line-height:1.5;">${msg.message}</div>`;
        } else if (msg.message === '[Tệp đính kèm]' && !msg.attachment) {
            body = `<div style="font-size:0.9rem;font-style:italic;opacity:0.8;">[Tệp đính kèm]</div>`;
        }
        const t = new Date(msg.created_at.replace(' ', 'T')).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
        const s = me ? 'background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border-radius:18px 18px 4px 18px;' : 'background:#fff;color:#1e293b;border-radius:18px 18px 18px 4px;box-shadow:0 2px 5px rgba(0,0,0,0.05);border:1px solid #edf2f7;';
        return `<div class="d-flex ${me ? 'justify-content-end' : 'justify-content-start'} animate__animated animate__fadeInUp animate__faster"><div style="max-width:85%;padding:10px 16px;${s}">${attachHtml}${body}<div style="font-size:0.65rem;opacity:0.6;text-align:${me ? 'right' : 'left'};margin-top:4px;">${t}</div></div></div>`;
    }

    window.toggleInboxWidget = function() {
        _iw.open = !_iw.open;
        const widget = document.getElementById('inboxWidget');
        widget.classList.toggle('open', _iw.open);
        if (_iw.open) {
            _iwLoadConvList();
            document.getElementById('bubbleUnreadDot').classList.add('d-none');
            const badges = document.querySelectorAll('#globalUnreadBadge, #inboxUnreadBadge');
            badges.forEach(b => b.classList.add('d-none'));
        } else if (_iw.pollTimer) {
            clearInterval(_iw.pollTimer);
        }
    };

    function _iwStripTags(html) {
        if (!html) return '';
        return html.replace(/<[^>]*>?/gm, '');
    }

    async function _iwLoadConvList() {
        const body = document.getElementById('inboxConvListBody');
        try {
            const r = await fetch('<?= url('/api/chat/list') ?>');
            const d = await r.json();
            if (!d.success || !d.conversations.length) {
                body.innerHTML = '<div class="text-center text-muted py-5" style="font-size:0.85rem;"><i class="far fa-comment-dots" style="font-size:2rem;opacity:0.1;display:block;margin-bottom:10px;"></i>Chưa có cuộc trò chuyện</div>';
                return;
            }
            body.innerHTML = d.conversations.map(c => {
                const isBuyer = c.buyer_id == <?= Auth::id() ?>;
                const otherId = isBuyer ? c.seller_id : c.buyer_id;
                const otherName = c.other_name || c.other_username || '?';
                const initials = otherName.charAt(0).toUpperCase();
                const lastMsg = c.last_message || 'Bắt đầu trò chuyện';
                const previewRaw = _iwStripTags(lastMsg);
                const preview = previewRaw.substring(0, 35) + (previewRaw.length > 35 ? '…' : '');
                const time = c.updated_at ? _iwTimeAgo(c.updated_at) : '';
                const unread = isBuyer ? (c.unread_count_buyer || 0) : (c.unread_count_seller || 0);
                const isActive = (_iw.activeConvId == c.id) || (!_iw.activeConvId && _iw.activeOtherId == otherId) ? 'active' : '';
                const convIdParam = typeof c.id === 'string' ? `'${c.id}'` : c.id;
                return `<div class="inbox-conv-item ${isActive}" onclick="_iwOpenChat(${otherId},'${otherName.replace(/'/g, "\\'")}',${convIdParam})" data-conv-id="${c.id}" data-other-id="${otherId}">
                    <div class="inbox-conv-avatar">${initials}</div>
                    <div style="min-width:0;flex:1;">
                        <div class="inbox-conv-name d-flex justify-content-between align-items-center">
                            <span>${otherName}</span>
                            ${unread > 0 ? `<span class="badge bg-danger rounded-pill" style="font-size:0.6rem;padding: 3px 6px;">${unread}</span>` : ''}
                        </div>
                        <div class="inbox-conv-preview">${preview || '<em style="opacity:0.5">Chưa có tin nhắn</em>'}</div>
                    </div>
                    <div class="inbox-conv-time">${time}</div>
                </div>`;
            }).join('');
        } catch (e) {
            body.innerHTML = '<div class="text-center text-danger py-4" style="font-size:0.85rem;">Lỗi kết nối</div>';
        }
    }

    window._iwOpenChat = async function(otherUserId, otherUserName, convId = null) {
        _iw.activeOtherId = otherUserId;
        _iw.activeConvId = convId;
        
        const widget = document.getElementById('inboxWidget');
        if (widget && !widget.classList.contains('open')) {
            toggleInboxWidget();
        }
        
        document.querySelectorAll('.inbox-conv-item').forEach(el => {
            el.classList.toggle('active', (convId && el.dataset.convId == convId) || (!convId && el.dataset.otherId == otherUserId));
        });
        
        if (window.innerWidth <= 768) {
            document.getElementById('inboxChatPanel').classList.add('mobile-show');
        }

        document.getElementById('inboxChatHeader').classList.add('visible');
        document.getElementById('inboxChatAvatar').textContent = otherUserName.charAt(0).toUpperCase();
        document.getElementById('inboxChatName').textContent = otherUserName;
        document.getElementById('inboxChatStatus').innerHTML = '<span style="opacity:0.6;">Đang tải...</span>';
        document.getElementById('inboxChatInput').style.display = 'block';
        document.getElementById('inboxChatMessages').innerHTML = '<div style="flex:1;display:flex;align-items:center;justify-content:center;"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></div>';
        
        _iw.activeLastMessageId = null; // Reset để không kêu khi vừa mở
        await _iwLoadMessages();
        if (_iw.pollTimer) clearInterval(_iw.pollTimer);
        _iw.pollTimer = setInterval(_iwLoadMessages, 3000);
    };

    async function _iwLoadMessages() {
        if (!_iw.activeConvId && !_iw.activeOtherId) return;
        const el = document.getElementById('inboxChatMessages');
        try {
            let url = '';
            if (_iw.activeConvId && _iw.activeConvId !== 'npc') {
                url = `<?= url('/api/chat/conversation') ?>?id=${_iw.activeConvId}`;
            } else if (_iw.activeConvId === 'npc') {
                url = `<?= url('/api/chat/conversation') ?>?id=npc`;
            } else {
                url = `<?= url('/api/chat/messages') ?>?seller_id=${_iw.activeOtherId}`;
            }

            const r = await fetch(url);
            const d = await r.json();
            if (!d.success) return;

            if (!_iw.activeConvId && d.messages && d.messages.length > 0) {
                _iw.activeConvId = d.messages[0].conversation_id;
            } else if (d.conversation && d.conversation.id) {
                 _iw.activeConvId = d.conversation.id;
            }

            const bot = el.scrollHeight - el.scrollTop <= el.clientHeight + 80;
            const st = document.getElementById('inboxChatStatus');
            if (st) {
                if (d.is_online) {
                    st.innerHTML = '<span style="width:8px;height:8px;border-radius:50%;background:#4ade80;display:inline-block;margin-right:6px;box-shadow: 0 0 8px rgba(74,222,128,0.5);"></span>Đang trực tuyến';
                } else if (d.last_active_at) {
                    st.innerHTML = 'Hoạt động ' + _iwTimeAgo(d.last_active_at) + ' trước';
                } else {
                    st.innerHTML = 'Ngoại tuyến';
                }
            }
            const msgs = d.messages.map(m => _iwRenderMsg(m, d.current_user_id)).join('');
            
            // Âm thanh khi có tin nhắn mới trong cửa sổ đang mở
            if (d.messages.length > 0) {
                const lastMsg = d.messages[d.messages.length - 1];
                if (_iw.activeLastMessageId && lastMsg.id > _iw.activeLastMessageId && lastMsg.sender_id != d.current_user_id) {
                    _iw.bellSound.play().catch(e => {});
                }
                _iw.activeLastMessageId = lastMsg.id;
            }

            const empty = document.getElementById('inboxChatEmpty');
            if (empty) empty.style.display = 'none';
            el.innerHTML = msgs || '<div class="text-center text-muted" style="margin-top:60px;font-size:0.85rem;"><i class="far fa-comment-dots" style="font-size:2rem;opacity:0.1;display:block;margin-bottom:10px;"></i>Hãy bắt đầu cuộc trò chuyện!</div>';
            if (bot) el.scrollTop = el.scrollHeight;
        } catch (e) {}
    }

    async function _iwSend() {
        const inp = document.getElementById('inboxMsgInput');
        const btn = document.getElementById('inboxSendBtn');
        const message = inp.value.trim();
        if ((!message && !_iw.attachment) || (!_iw.activeConvId && !_iw.activeOtherId)) return;
        const ob = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size:0.8rem;"></i>';
        try {
            const fd = new FormData();
            if (_iw.activeConvId) {
                fd.append('conversation_id', _iw.activeConvId);
            } else {
                fd.append('seller_id', _iw.activeOtherId);
            }
            fd.append('message', message);
            if (_iw.attachment) fd.append('attachment', _iw.attachment);
            fd.append('csrf_token', '<?= csrf_token() ?>');
            const r = await fetch('<?= url('/api/chat/send') ?>', { method:'POST', body:fd });
            const d = await r.json();
            if (d.success) {
                window._iwRemoveAttachment();
                inp.value = '';
                inp.style.height = '42px';
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

    async function checkNotifications() {
        try {
            const response = await fetch('<?= url('/api/notifications/check') ?>');
            const data = await response.json();
            
            if (data.success) {
                const bubbleDot = document.getElementById('bubbleUnreadDot');
                const badges = document.querySelectorAll('#globalUnreadBadge, #inboxUnreadBadge');
                
                if (data.unread_count > 0 && !_iw.open) {
                    if (bubbleDot) bubbleDot.classList.remove('d-none');
                    badges.forEach(badge => {
                        badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                        badge.classList.remove('d-none');
                    });
                } else {
                    if (bubbleDot) bubbleDot.classList.add('d-none');
                    badges.forEach(badge => badge.classList.add('d-none'));
                }

                let shouldPlay = false;
                if (data.has_new_npc_message && data.last_npc_message_id !== _iw.lastNpcMessageId) {
                    shouldPlay = true;
                    _iw.lastNpcMessageId = data.last_npc_message_id;
                } else if (data.unread_count > _iw.lastUnreadCount) {
                    shouldPlay = true;
                }

                if (shouldPlay) {
                    _iw.bellSound.play().catch(e => console.log('Audio blocked'));
                    if (!_iw.open) _iwLoadConvList(); 
                }
                
                _iw.lastUnreadCount = data.unread_count;
            }
        } catch (error) {}
    }

    document.addEventListener('DOMContentLoaded', function() {
        const sendBtn = document.getElementById('inboxSendBtn');
        if (sendBtn) sendBtn.addEventListener('click', _iwSend);
        
        const msgInp = document.getElementById('inboxMsgInput');
        if (msgInp) {
            msgInp.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    _iwSend();
                }
            });
        }
        
        document.addEventListener('click', function(e) {
            const w = document.getElementById('inboxWidget');
            const b = document.getElementById('globalChatBubble');
            if (_iw.open && w && !w.contains(e.target) && b && !b.contains(e.target)) {
                toggleInboxWidget();
            }
        });

        setInterval(checkNotifications, 10000);
    });
})();
</script>
<?php endif; ?>