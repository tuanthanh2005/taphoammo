<?php
// app/Models/Conversation.php

class Conversation extends Model {
    protected $table = 'conversations';

    public function findOrCreate($buyerId, $sellerId) {
        $conversation = $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE buyer_id = ? AND seller_id = ?",
            [$buyerId, $sellerId]
        );

        if (!$conversation) {
            $id = $this->create([
                'buyer_id' => $buyerId,
                'seller_id' => $sellerId
            ]);
            return $this->find($id);
        }

        return $conversation;
    }

    public function getConversationsForUser($userId) {
        return $this->db->fetchAll(
            "SELECT c.*, 
                    u.username as other_username, 
                    u.avatar as other_avatar,
                    u.last_active_at as other_last_active
             FROM {$this->table} c
             JOIN users u ON (u.id = c.buyer_id OR u.id = c.seller_id) AND u.id != ?
             WHERE c.buyer_id = ? OR c.seller_id = ?
             ORDER BY c.updated_at DESC",
            [$userId, $userId, $userId]
        );
    }

    public function updateLastMessage($id, $message, $isBuyer) {
        $update = [
            'last_message' => $message,
            'last_message_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($isBuyer) {
            $this->db->query("UPDATE {$this->table} SET unread_count_seller = unread_count_seller + 1 WHERE id = ?", [$id]);
        } else {
            $this->db->query("UPDATE {$this->table} SET unread_count_buyer = unread_count_buyer + 1 WHERE id = ?", [$id]);
        }

        return $this->update($id, $update);
    }

    public function resetUnreadCount($id, $isBuyer) {
        if ($isBuyer) {
            return $this->db->query("UPDATE {$this->table} SET unread_count_buyer = 0 WHERE id = ?", [$id]);
        } else {
            return $this->db->query("UPDATE {$this->table} SET unread_count_seller = 0 WHERE id = ?", [$id]);
        }
    }

    public function getTotalUnread($userId) {
        $result = $this->db->fetchOne(
            "SELECT 
                SUM(CASE WHEN buyer_id = ? THEN unread_count_buyer ELSE 0 END) +
                SUM(CASE WHEN seller_id = ? THEN unread_count_seller ELSE 0 END) as total
             FROM {$this->table}
             WHERE buyer_id = ? OR seller_id = ?",
            [$userId, $userId, $userId, $userId]
        );
        return $result['total'] ?? 0;
    }
}
