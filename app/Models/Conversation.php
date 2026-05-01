<?php
// app/Models/Conversation.php

class Conversation extends Model
{
    protected $table = 'conversations';

    public function findOrCreate($buyerId, $sellerId)
    {
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

    public function getConversationsForUser($userId)
    {
        $systemUserId = Helper::getSystemUserId();
        $systemDisplayName = Helper::getSystemDisplayName();
        $conversations = $this->db->fetchAll(
            "SELECT c.*, 
                    u.username as other_username, 
                    u.name as other_name,
                    u.avatar as other_avatar,
                    u.last_active_at as other_last_active
             FROM {$this->table} c
             JOIN users u ON (u.id = c.buyer_id OR u.id = c.seller_id) AND u.id != ?
             WHERE c.buyer_id = ? OR c.seller_id = ?
             ORDER BY c.updated_at DESC",
            [$userId, $userId, $userId]
        );

        foreach ($conversations as &$conversation) {
            $otherUserId = ((int)$conversation['buyer_id'] === (int)$userId)
                ? (int)$conversation['seller_id']
                : (int)$conversation['buyer_id'];

            if ($otherUserId === $systemUserId) {
                $conversation['other_username'] = $systemDisplayName;
                $conversation['other_name'] = $systemDisplayName;
            }
        }
        unset($conversation);

        // Đảm bảo luôn có NPC trong danh sách
        $hasNPC = false;
        foreach ($conversations as $c) {
            if ((int)$c['buyer_id'] === $systemUserId || (int)$c['seller_id'] === $systemUserId) {
                $hasNPC = true;
                break;
            }
        }

        if (!$hasNPC && (int)$userId !== $systemUserId) {
            $npc = $this->db->fetchOne("SELECT username as other_username, name as other_name, avatar as other_avatar FROM users WHERE id = ?", [$systemUserId]);
            if ($npc) {
                array_unshift($conversations, [
                    'id' => 'npc', // ID ảo để nhận diện NPC
                    'buyer_id' => $userId,
                    'seller_id' => $systemUserId,
                    'last_message' => 'Hệ thống NPC sẵn sàng hỗ trợ bạn.',
                    'last_message_at' => date('Y-m-d H:i:s'),
                    'unread_count_buyer' => 0,
                    'unread_count_seller' => 0,
                    'other_username' => $systemDisplayName,
                    'other_name' => $systemDisplayName,
                    'other_avatar' => $npc['other_avatar'],
                    'other_last_active' => date('Y-m-d H:i:s'),
                    'is_npc' => true
                ]);
            }
        }

        return $conversations;
    }

    public function updateLastMessage($id, $message, $isBuyer)
    {
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

    public function resetUnreadCount($id, $isBuyer)
    {
        if ($isBuyer) {
            return $this->db->query("UPDATE {$this->table} SET unread_count_buyer = 0 WHERE id = ?", [$id]);
        } else {
            return $this->db->query("UPDATE {$this->table} SET unread_count_seller = 0 WHERE id = ?", [$id]);
        }
    }

    public function getTotalUnread($userId)
    {
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
