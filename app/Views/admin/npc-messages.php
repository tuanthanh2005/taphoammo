<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-7">
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fas fa-robot text-warning me-2"></i> Tin nhắn với NPC
            </h4>
            <p class="text-muted small mb-0">Xem các cuộc trò chuyện giữa người dùng và bot hệ thống.</p>
        </div>
        <div class="col-md-5">
            <form action="<?= url('/admin/npc-messages') ?>" method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Tìm tên, username, email..." value="<?= e($search ?? '') ?>">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-search"></i>
                </button>
                <?php if (!empty($search)): ?>
                    <a href="<?= url('/admin/npc-messages') ?>" class="btn btn-light px-3">Xóa</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm npc-panel">
                <div class="card-header bg-white py-3">
                    <div class="fw-bold">Người đang nhắn với NPC</div>
                    <div class="text-muted small"><?= count($conversations ?? []) ?> cuộc trò chuyện</div>
                </div>
                <div class="list-group list-group-flush npc-list">
                    <?php if (empty($conversations)): ?>
                        <div class="text-center text-muted py-5 px-3">
                            <i class="fas fa-comments fa-2x mb-3 opacity-25"></i>
                            <div>Chưa có cuộc trò chuyện NPC nào.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($conversations as $conversation): ?>
                            <?php
                                $active = !empty($selectedConversation) && (int)$selectedConversation['id'] === (int)$conversation['id'];
                                $lastAt = $conversation['last_message_at'] ?? $conversation['updated_at'] ?? $conversation['created_at'] ?? null;
                            ?>
                            <a class="list-group-item list-group-item-action <?= $active ? 'active' : '' ?>"
                               href="<?= url('/admin/npc-messages?conversation_id=' . $conversation['id'] . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="npc-avatar"><?= strtoupper(substr($conversation['user_name'] ?: $conversation['username'], 0, 1)) ?></div>
                                    <div class="flex-grow-1 min-width-0">
                                        <div class="d-flex justify-content-between gap-2">
                                            <div class="fw-bold text-truncate"><?= e($conversation['user_name'] ?: $conversation['username']) ?></div>
                                            <?php if ($lastAt): ?>
                                                <small class="<?= $active ? 'text-white-50' : 'text-muted' ?> text-nowrap"><?= date('d/m H:i', strtotime($lastAt)) ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="<?= $active ? 'text-white-50' : 'text-muted' ?> small text-truncate"><?= e($conversation['user_email']) ?></div>
                                        <div class="<?= $active ? 'text-white-50' : 'text-muted' ?> small text-truncate mt-1">
                                            <?= e($conversation['last_message'] ?: 'Chưa có tin nhắn') ?>
                                        </div>
                                        <div class="d-flex gap-2 mt-2">
                                            <span class="badge <?= $active ? 'bg-light text-primary' : 'bg-primary-subtle text-primary' ?>">
                                                <?= (int)$conversation['total_messages'] ?> tin
                                            </span>
                                            <span class="badge <?= $active ? 'bg-light text-dark' : 'bg-light text-muted' ?>">
                                                <?= e($conversation['user_role']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm npc-panel">
                <?php if (empty($selectedConversation)): ?>
                    <div class="d-flex align-items-center justify-content-center text-center text-muted npc-empty">
                        <div>
                            <i class="far fa-comment-dots fa-3x mb-3 opacity-25"></i>
                            <div>Chọn một cuộc trò chuyện để xem tin nhắn.</div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="fw-bold"><?= e($selectedConversation['user_name'] ?: $selectedConversation['username']) ?></div>
                                <div class="text-muted small">
                                    #<?= (int)$selectedConversation['user_id'] ?> · <?= e($selectedConversation['user_email']) ?>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-secondary-subtle text-secondary"><?= e($selectedConversation['user_status']) ?></span>
                                <div class="text-muted small mt-1">
                                    User: <?= (int)$selectedConversation['user_messages'] ?> · NPC: <?= (int)$selectedConversation['npc_messages'] ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body npc-thread">
                        <?php if (empty($messages)): ?>
                            <div class="text-center text-muted py-5">Cuộc trò chuyện chưa có tin nhắn.</div>
                        <?php else: ?>
                            <?php foreach ($messages as $message): ?>
                                <?php $fromNpc = (int)$message['sender_id'] === (int)$systemUserId; ?>
                                <div class="d-flex mb-3 <?= $fromNpc ? 'justify-content-end' : 'justify-content-start' ?>">
                                    <div class="npc-message <?= $fromNpc ? 'npc-message-bot' : 'npc-message-user' ?>">
                                        <div class="small fw-bold mb-1">
                                            <?= $fromNpc ? 'NPC hệ thống' : e($message['sender_name'] ?: 'Người dùng') ?>
                                        </div>
                                        <?php if (!empty($message['attachment'])): ?>
                                            <div class="mb-2">
                                                <a href="<?= asset($message['attachment']) ?>" target="_blank">
                                                    <img src="<?= asset($message['attachment']) ?>" class="img-fluid rounded border" style="max-height: 180px;" alt="Attachment">
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($message['message']) && $message['message'] !== '[Tệp đính kèm]'): ?>
                                            <div class="message-text"><?= nl2br(e($message['message'])) ?></div>
                                        <?php endif; ?>
                                        <div class="small mt-2 <?= $fromNpc ? 'text-white-50' : 'text-muted' ?>">
                                            <?= date('d/m/Y H:i:s', strtotime($message['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.npc-panel {
    border-radius: 8px;
    overflow: hidden;
    min-height: 640px;
}
.npc-list {
    max-height: 580px;
    overflow-y: auto;
}
.npc-list .list-group-item {
    border-left: 0;
    border-right: 0;
    padding: 16px;
}
.npc-avatar {
    width: 42px;
    height: 42px;
    border-radius: 8px;
    background: #eef2ff;
    color: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    flex: 0 0 auto;
}
.list-group-item.active .npc-avatar {
    background: rgba(255,255,255,0.2);
    color: #fff;
}
.npc-thread {
    height: 580px;
    overflow-y: auto;
    background: #f8fafc;
}
.npc-empty {
    min-height: 640px;
}
.npc-message {
    max-width: min(620px, 82%);
    border-radius: 8px;
    padding: 12px 14px;
    word-break: break-word;
}
.npc-message-user {
    background: #fff;
    border: 1px solid #e5e7eb;
}
.npc-message-bot {
    background: #4f46e5;
    color: #fff;
}
.message-text {
    white-space: normal;
    line-height: 1.5;
}
.bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1); }
.bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1); }
.min-width-0 { min-width: 0; }
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Tin nhắn NPC';
require_once __DIR__ . '/../layouts/admin.php';
?>
