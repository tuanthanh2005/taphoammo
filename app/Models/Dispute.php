<?php
// app/Models/Dispute.php

class Dispute extends Model {
    protected $table = 'disputes';

    public function createDispute($data) {
        $data['dispute_code'] = 'DSP' . date('Ymd') . rand(1000, 9999);
        return $this->create($data);
    }

    public function getByOrder($orderId) {
        return $this->db->fetchAll(
            "SELECT d.*, u.name as user_name, u.username as user_username,
                    s.name as seller_name, s.username as seller_username
             FROM {$this->table} d
             LEFT JOIN users u ON d.user_id = u.id
             LEFT JOIN users s ON d.seller_id = s.id
             WHERE d.order_id = ?
             ORDER BY d.created_at DESC",
            [$orderId]
        );
    }

    public function getByUser($userId, $limit = 50) {
        return $this->db->fetchAll(
            "SELECT d.*, o.order_code,
                    p.name as product_name,
                    s.name as seller_name, s.username as seller_username
             FROM {$this->table} d
             LEFT JOIN orders o ON d.order_id = o.id
             LEFT JOIN order_items oi ON d.order_item_id = oi.id
             LEFT JOIN products p ON oi.product_id = p.id
             LEFT JOIN users s ON d.seller_id = s.id
             WHERE d.user_id = ?
             ORDER BY d.created_at DESC
             LIMIT ?",
            [$userId, $limit]
        );
    }

    public function getBySeller($sellerId, $limit = 50) {
        return $this->db->fetchAll(
            "SELECT d.*, o.order_code,
                    p.name as product_name,
                    u.name as user_name, u.username as user_username
             FROM {$this->table} d
             LEFT JOIN orders o ON d.order_id = o.id
             LEFT JOIN order_items oi ON d.order_item_id = oi.id
             LEFT JOIN products p ON oi.product_id = p.id
             LEFT JOIN users u ON d.user_id = u.id
             WHERE d.seller_id = ?
             ORDER BY d.created_at DESC
             LIMIT ?",
            [$sellerId, $limit]
        );
    }

    public function getAllDisputes($page = 1, $perPage = 50, $status = '', $search = '') {
        $offset = ($page - 1) * $perPage;
        $where = '1=1';
        $params = [];

        if (!empty($status)) {
            $where .= ' AND d.status = ?';
            $params[] = $status;
        }

        if (!empty($search)) {
            $where .= ' AND (d.dispute_code LIKE ? OR o.order_code LIKE ? OR u.name LIKE ? OR s.name LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        return $this->db->fetchAll(
            "SELECT d.*, o.order_code, o.total_amount as order_total,
                    p.name as product_name,
                    u.name as user_name, u.username as user_username, u.email as user_email,
                    s.name as seller_name, s.username as seller_username, s.email as seller_email
             FROM {$this->table} d
             LEFT JOIN orders o ON d.order_id = o.id
             LEFT JOIN order_items oi ON d.order_item_id = oi.id
             LEFT JOIN products p ON oi.product_id = p.id
             LEFT JOIN users u ON d.user_id = u.id
             LEFT JOIN users s ON d.seller_id = s.id
             WHERE {$where}
             ORDER BY FIELD(d.status, 'open', 'under_review') DESC, d.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
    }

    public function countByStatus() {
        return $this->db->fetchAll(
            "SELECT status, COUNT(*) as total FROM {$this->table} GROUP BY status"
        );
    }

    public function getEvents($disputeId) {
        return $this->db->fetchAll(
            "SELECT de.*, u.name as actor_name 
             FROM dispute_events de
             LEFT JOIN users u ON de.actor_id = u.id
             WHERE de.dispute_id = ?
             ORDER BY de.created_at ASC",
            [$disputeId]
        );
    }
}
