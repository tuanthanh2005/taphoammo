<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar: Danh sách cuộc trò chuyện -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; height: 600px;">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <h5 class="fw-bold mb-0">Tin nhắn</h5>
                </div>
                <div class="card-body p-0 overflow-y-auto" id="conversationList">
                    <?php if (empty($conversations)): ?>
                        <div class="text-center py-5">
                            <i class="far fa-comments fs-1 text-muted mb-3 d-block"></i>
                            <p class="text-muted small">Chưa có cuộc trò chuyện nào</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($conversations as $conv): ?>
                            <div class="conversation-item p-3 d-flex align-items-center border-bottom cursor-pointer transition-all"
                                onclick="loadConversation(<?= $conv['id'] ?>, '<?= e($conv['other_username']) ?>')"
                                data-id="<?= $conv['id'] ?>">
                                <div class="position-relative me-3">
                                    <div class="rounded-circle bg-primary-soft text-primary d-flex align-items-center justify-content-center fw-bold"
                                        style="width: 45px; height: 45px; flex-shrink: 0;">
                                        <?= mb_strtoupper(mb_substr($conv['other_username'], 0, 1)) ?>
                                    </div>
                                    <?php
                                    $isOnline = $conv['other_last_active'] && (time() - strtotime($conv['other_last_active']) < 300);
                                    if ($isOnline):
                                        ?>
                                        <span
                                            class="position-absolute bottom-0 end-0 bg-success border border-white border-2 rounded-circle"
                                            style="width: 12px; height: 12px;"></span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0 text-dark"><?= e($conv['other_username']) ?></h6>
                                        <small class="text-muted"
                                            style="font-size: 0.65rem;"><?= date('H:i', strtotime($conv['updated_at'])) ?></small>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <p class="text-muted small mb-0 text-truncate me-2">
                                            <?= e($conv['last_message'] ?? 'Bắt đầu trò chuyện') ?></p>
                                        <?php
                                        $isBuyer = ($conv['buyer_id'] == Auth::id());
                                        $unread = $isBuyer ? $conv['unread_count_buyer'] : $conv['unread_count_seller'];
                                        if ($unread > 0):
                                            ?>
                                            <span class="badge bg-danger rounded-pill"
                                                style="font-size: 0.6rem;"><?= $unread ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Main: Cửa sổ chat -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm d-flex flex-column" style="border-radius: 20px; height: 600px;">
                <div id="chatWindowHeader" class="card-header bg-white border-bottom py-3 px-4 d-none">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold me-3"
                            style="width: 40px; height: 40px;" id="activeUserAvatar">
                            ?
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0" id="activeUserName">Chọn một cuộc trò chuyện</h6>
                            <small class="text-success" style="font-size: 0.7rem;">Trực tuyến</small>
                        </div>
                    </div>
                </div>

                <div class="card-body bg-light p-0 position-relative flex-grow-1 overflow-hidden">
                    <!-- Empty State -->
                    <div id="chatEmptyState"
                        class="position-absolute top-50 start-50 translate-middle text-center w-100">
                        <i class="fas fa-comment-dots fs-1 text-muted opacity-25 mb-3"></i>
                        <h6 class="text-muted">Chọn một cuộc trò chuyện để bắt đầu nhắn tin</h6>
                    </div>

                    <!-- Message History -->
                    <div id="chatMessages" class="p-4 d-none"
                        style="height: 100%; overflow-y: auto; display: flex; flex-direction: column; gap: 12px;">
                        <!-- Messages load here -->
                    </div>
                </div>

                <div id="chatInputArea" class="card-footer bg-white border-0 p-3 d-none">
                    <div class="d-flex align-items-end bg-light rounded-4 p-2">
                        <textarea id="sellerMessageInput" class="form-control border-0 bg-transparent flex-grow-1"
                            placeholder="Nhập tin nhắn..."
                            style="box-shadow: none; resize: none; min-height: 40px; max-height: 140px;"
                            rows="1"
                            oninput="this.style.height = ''; this.style.height = Math.min(this.scrollHeight, 140) + 'px'"></textarea>
                        <button class="btn btn-primary rounded-circle ms-2 d-flex align-items-center justify-content-center mb-1"
                            id="sendSellerMessageBtn" style="width: 40px; height: 40px; flex-shrink:0;">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .conversation-item {
        cursor: pointer;
    }

    .conversation-item:hover {
        background-color: #f8fafc;
    }

    .conversation-item.active {
        background-color: #eff6ff;
        border-right: 3px solid #0d6efd;
    }

    .bg-primary-soft {
        background-color: #eff6ff;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .transition-all {
        transition: all 0.2s ease;
    }
</style>

<script>
    let activeConversationId = null;
    let chatPollInterval = null;

    function timeAgo(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString.replace(' ', 'T'));
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) return 'vài giây trước';
        const diffInMinutes = Math.floor(diffInSeconds / 60);
        if (diffInMinutes < 60) return diffInMinutes + ' phút trước';
        const diffInHours = Math.floor(diffInMinutes / 60);
        if (diffInHours < 24) return diffInHours + ' giờ trước';
        const diffInDays = Math.floor(diffInHours / 24);
        if (diffInDays < 30) return diffInDays + ' ngày trước';
        const diffInMonths = Math.floor(diffInDays / 30);
        if (diffInMonths < 12) return diffInMonths + ' tháng trước';
        const diffInYears = Math.floor(diffInMonths / 12);
        return diffInYears + ' năm trước';
    }

    async function loadConversation(id, username) {
        activeConversationId = id;

        // UI Updates
        document.querySelectorAll('.conversation-item').forEach(el => el.classList.remove('active'));
        document.querySelector(`.conversation-item[data-id="${id}"]`).classList.add('active');

        document.getElementById('chatEmptyState').classList.add('d-none');
        document.getElementById('chatWindowHeader').classList.remove('d-none');
        document.getElementById('chatMessages').classList.remove('d-none');
        document.getElementById('chatInputArea').classList.remove('d-none');

        document.getElementById('activeUserName').innerText = username;
        document.getElementById('activeUserAvatar').innerText = username.charAt(0).toUpperCase();

        await fetchMessages();

        if (chatPollInterval) clearInterval(chatPollInterval);
        chatPollInterval = setInterval(() => {
            fetchMessages();
            fetchConversations(); // Update sidebar too
        }, 3000);
    }

    async function fetchConversations() {
        try {
            const response = await fetch('<?= url('/api/chat/list') ?>'); // Need to create this endpoint
            const data = await response.json();
            if (data.success) {
                const list = document.getElementById('conversationList');
                let html = '';
                if (data.conversations.length === 0) {
                    html = '<div class="text-center py-5"><i class="far fa-comments fs-1 text-muted mb-3 d-block"></i><p class="text-muted small">Chưa có cuộc trò chuyện nào</p></div>';
                } else {
                    data.conversations.forEach(conv => {
                        const isActive = conv.id == activeConversationId;
                        const isBuyer = conv.buyer_id == <?= Auth::id() ?>;
                        const unread = isBuyer ? conv.unread_count_buyer : conv.unread_count_seller;
                        const lastActiveStr = conv.other_last_active ? conv.other_last_active.replace(' ', 'T') : null;
                        const isOnline = lastActiveStr && (new Date() - new Date(lastActiveStr)) < 300000;

                        const updatedAtStr = conv.updated_at ? conv.updated_at.replace(' ', 'T') : new Date().toISOString();

                        html += `
                            <div class="conversation-item p-3 d-flex align-items-center border-bottom cursor-pointer transition-all ${isActive ? 'active' : ''}" 
                                 onclick="loadConversation(${conv.id}, '${conv.other_username.replace(/'/g, "\\'")}')"
                                 data-id="${conv.id}">
                                <div class="position-relative me-3">
                                    <div class="rounded-circle bg-primary-soft text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px; flex-shrink: 0;">
                                        ${conv.other_username.charAt(0).toUpperCase()}
                                    </div>
                                    ${isOnline ? '<span class="position-absolute bottom-0 end-0 bg-success border border-white border-2 rounded-circle" style="width: 12px; height: 12px;"></span>' : ''}
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0 text-dark">${conv.other_username}</h6>
                                        <small class="text-muted" style="font-size: 0.65rem;">${new Date(updatedAtStr).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</small>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <p class="text-muted small mb-0 text-truncate me-2">${conv.last_message || 'Bắt đầu trò chuyện'}</p>
                                        ${unread > 0 ? `<span class="badge bg-danger rounded-pill" style="font-size: 0.6rem;">${unread}</span>` : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }
                list.innerHTML = html;
            }
        } catch (e) {
            console.error('Error fetching conversations', e);
        }
    }

    async function fetchMessages() {
        if (!activeConversationId) return;

        try {
            const response = await fetch(`<?= url('/api/chat/conversation') ?>?id=${activeConversationId}`);
            const data = await response.json();

            if (data.success) {
                renderMessages(data.messages, data.current_user_id, data.is_online, data.last_active_at);
            }
        } catch (e) {
            console.error('Error fetching messages', e);
        }
    }

    function renderMessages(messages, currentUserId, isOnline = null, lastActiveAt = null) {
        const chatArea = document.getElementById('chatMessages');
        const isAtBottom = chatArea.scrollHeight - chatArea.scrollTop <= chatArea.clientHeight + 100;

        let html = '';
        messages.forEach(msg => {
            const isMe = msg.sender_id == currentUserId;
            const msgContent = msg.message ? `<div style="font-size: 0.9rem; white-space: pre-wrap; word-break: break-word;">${msg.message}</div>` : '';
            html += `
                <div class="d-flex ${isMe ? 'justify-content-end' : 'justify-content-start'}">
                    <div class="p-2 px-3 shadow-sm" style="max-width: 75%; border-radius: 18px; ${isMe ? 'background: #8b5cf6; color: #fff; border-bottom-right-radius: 4px;' : 'background: #fff; color: #333; border-bottom-left-radius: 4px;'}">
                        ${msgContent}
                        <div class="text-end mt-1" style="font-size: 0.65rem; opacity: 0.7;">
                            ${new Date(msg.created_at.replace(' ', 'T')).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                        </div>
                    </div>
                </div>
            `;
        });

        chatArea.innerHTML = html;

        // Update online status in header if provided
        if (isOnline !== null) {
            const statusEl = document.querySelector('#chatWindowHeader small');
            if (isOnline) {
                statusEl.innerText = 'Trực tuyến';
                statusEl.className = 'text-success';
            } else {
                let offlineText = 'Ngoại tuyến';
                if (lastActiveAt) {
                    offlineText = 'Hoạt động ' + timeAgo(lastActiveAt);
                }
                statusEl.innerText = offlineText;
                statusEl.className = 'text-muted';
            }
        }

        if (isAtBottom) {
            chatArea.scrollTop = chatArea.scrollHeight;
        }
    }

    async function sendSellerMessage() {
        const input = document.getElementById('sellerMessageInput');
        const btn = document.getElementById('sendSellerMessageBtn');
        const message = input.value.trim();
        if (!message) return;

        input.disabled = true;
        btn.disabled = true;
        const originalValue = input.value;
        input.value = ''; input.style.height = '40px';

        try {
            const formData = new FormData();
            formData.append('conversation_id', activeConversationId);
            formData.append('message', message);
            formData.append('csrf_token', '<?= csrf_token() ?>');

            const response = await fetch('<?= url('/api/chat/send') ?>', { method: 'POST', body: formData });
            const data = await response.json();

            if (data.success) {
                renderMessages(data.messages, data.current_user_id);
                fetchConversations();
                setTimeout(() => { document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight; }, 50);
            } else {
                alert(data.message || 'Không thể gửi tin nhắn');
                input.value = originalValue;
            }
        } catch (e) {
            alert('Lỗi: ' + e.message);
            input.value = originalValue;
        } finally {
            input.disabled = false;
            btn.disabled = false;
            input.focus();
        }
    }

    document.getElementById('sendSellerMessageBtn').addEventListener('click', sendSellerMessage);
    document.getElementById('sellerMessageInput').addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendSellerMessage();
        }
    });
</script>


<?php require_once __DIR__ . '/../layouts/footer.php'; ?>