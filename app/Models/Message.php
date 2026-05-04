<?php
// app/Models/Message.php

class Message extends Model {
    protected $table = 'messages';
    private static $hasAttachmentColumn = null;

    public function createChatMessage($data) {
        if (!$this->hasAttachmentColumn()) {
            if (!empty($data['attachment'])) {
                throw new Exception("Database is missing messages.attachment. Run php update_chat_schema.php or add_chat_attachment_column.php.");
            }

            unset($data['attachment']);
        }

        return $this->create($data);
    }

    private function hasAttachmentColumn() {
        if (self::$hasAttachmentColumn !== null) {
            return self::$hasAttachmentColumn;
        }

        $column = $this->db->fetchOne("SHOW COLUMNS FROM {$this->table} LIKE 'attachment'");
        self::$hasAttachmentColumn = !empty($column);

        return self::$hasAttachmentColumn;
    }

    public function getMessagesForConversation($conversationId) {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE conversation_id = ? ORDER BY created_at ASC",
            [$conversationId]
        );
    }

    public function markAsRead($conversationId, $userId) {
        return $this->db->query(
            "UPDATE {$this->table} SET is_read = 1 WHERE conversation_id = ? AND sender_id != ?",
            [$conversationId, $userId]
        );
    }
}
