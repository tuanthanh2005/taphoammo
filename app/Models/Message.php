<?php
// app/Models/Message.php

class Message extends Model {
    protected $table = 'messages';

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
