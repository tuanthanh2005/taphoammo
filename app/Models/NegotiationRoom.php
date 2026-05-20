<?php
// app/Models/NegotiationRoom.php

class NegotiationRoom extends Model
{
    protected $table = 'negotiation_rooms';

    public function createRoom($adminId, $buyerId, $sellerId, $title, $topic = '', $disputeId = null)
    {
        $now = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, [
            'admin_id' => $adminId,
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId,
            'title' => $title,
            'topic' => $topic,
            'dispute_id' => $disputeId,
            'status' => 'open',
            'last_message' => 'Phòng đàm phán đã được tạo',
            'last_message_at' => $now,
            'unread_buyer' => 1,
            'unread_seller' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function findWithDetails($id)
    {
        return $this->db->fetchOne(
            "SELECT r.*,
                    a.name AS admin_name, a.username AS admin_username, a.avatar AS admin_avatar,
                    b.name AS buyer_name, b.username AS buyer_username, b.avatar AS buyer_avatar, b.email AS buyer_email,
                    s.name AS seller_name, s.username AS seller_username, s.avatar AS seller_avatar, s.email AS seller_email,
                    s.telegram_chat_id AS seller_telegram, b.telegram_chat_id AS buyer_telegram
             FROM {$this->table} r
             LEFT JOIN users a ON r.admin_id = a.id
             LEFT JOIN users b ON r.buyer_id = b.id
             LEFT JOIN users s ON r.seller_id = s.id
             WHERE r.id = ?",
            [$id]
        );
    }

    public function getRoomsForAdmin($adminId = null, $status = null)
    {
        $where = [];
        $params = [];
        if ($status) {
            $where[] = 'r.status = ?';
            $params[] = $status;
        }
        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        return $this->db->fetchAll(
            "SELECT r.*,
                    b.name AS buyer_name, b.username AS buyer_username,
                    s.name AS seller_name, s.username AS seller_username,
                    a.name AS admin_name
             FROM {$this->table} r
             LEFT JOIN users b ON r.buyer_id = b.id
             LEFT JOIN users s ON r.seller_id = s.id
             LEFT JOIN users a ON r.admin_id = a.id
             {$whereSql}
             ORDER BY r.updated_at DESC",
            $params
        );
    }

    public function getRoomsForUser($userId, $role = 'any')
    {
        $sql = "SELECT r.*,
                    b.name AS buyer_name, b.username AS buyer_username, b.avatar AS buyer_avatar,
                    s.name AS seller_name, s.username AS seller_username, s.avatar AS seller_avatar,
                    a.name AS admin_name, a.avatar AS admin_avatar
             FROM {$this->table} r
             LEFT JOIN users b ON r.buyer_id = b.id
             LEFT JOIN users s ON r.seller_id = s.id
             LEFT JOIN users a ON r.admin_id = a.id
             WHERE (r.buyer_id = ? OR r.seller_id = ?)
             ORDER BY r.updated_at DESC";
        return $this->db->fetchAll($sql, [$userId, $userId]);
    }

    public function getMessages($roomId)
    {
        return $this->db->fetchAll(
            "SELECT m.*, u.name AS sender_name, u.username AS sender_username, u.avatar AS sender_avatar
             FROM negotiation_messages m
             LEFT JOIN users u ON m.sender_id = u.id
             WHERE m.room_id = ?
             ORDER BY m.created_at ASC",
            [$roomId]
        );
    }

    public function addMessage($roomId, $senderId, $senderRole, $message, $attachment = null, $isSystem = 0)
    {
        $now = date('Y-m-d H:i:s');
        $msgId = $this->db->insert('negotiation_messages', [
            'room_id' => $roomId,
            'sender_id' => $senderId,
            'sender_role' => $senderRole,
            'message' => $message,
            'attachment' => $attachment,
            'is_system' => $isSystem,
            'created_at' => $now,
        ]);

        // Update room last message + unread counters for the others
        $room = $this->find($roomId);
        if ($room) {
            $updates = ['last_message = ?', 'last_message_at = NOW()', 'updated_at = NOW()'];
            $params = [mb_strimwidth($message, 0, 200, '...')];

            if ($senderRole !== 'admin') {
                $updates[] = 'unread_admin = unread_admin + 1';
            }
            if ($senderRole !== 'buyer') {
                $updates[] = 'unread_buyer = unread_buyer + 1';
            }
            if ($senderRole !== 'seller') {
                $updates[] = 'unread_seller = unread_seller + 1';
            }

            $params[] = $roomId;
            $this->db->query(
                "UPDATE {$this->table} SET " . implode(', ', $updates) . " WHERE id = ?",
                $params
            );
        }

        return $msgId;
    }

    public function resetUnread($roomId, $role)
    {
        $field = match ($role) {
            'admin' => 'unread_admin',
            'buyer' => 'unread_buyer',
            'seller' => 'unread_seller',
            default => null,
        };
        if (!$field) return false;
        return $this->db->query("UPDATE {$this->table} SET {$field} = 0 WHERE id = ?", [$roomId]);
    }

    public function setStatus($roomId, $status)
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if (in_array($status, ['resolved', 'closed'])) {
            $data['closed_at'] = date('Y-m-d H:i:s');
        }
        return $this->update($roomId, $data);
    }

    public function getOpenRoomCountForUser($userId)
    {
        $row = $this->db->fetchOne(
            "SELECT COUNT(*) AS c FROM {$this->table}
             WHERE status = 'open' AND (buyer_id = ? OR seller_id = ?)",
            [$userId, $userId]
        );
        return (int)($row['c'] ?? 0);
    }

    public function getTotalUnreadForUser($userId)
    {
        $row = $this->db->fetchOne(
            "SELECT
                SUM(CASE WHEN buyer_id = ? THEN unread_buyer ELSE 0 END) +
                SUM(CASE WHEN seller_id = ? THEN unread_seller ELSE 0 END) AS total
             FROM {$this->table}
             WHERE status = 'open' AND (buyer_id = ? OR seller_id = ?)",
            [$userId, $userId, $userId, $userId]
        );
        return (int)($row['total'] ?? 0);
    }
}
