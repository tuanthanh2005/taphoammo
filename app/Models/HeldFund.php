<?php
// app/Models/HeldFund.php

class HeldFund extends Model {
    protected $table = 'held_funds';

    public function getHeldFundsBySeller($sellerId) {
        return $this->db->fetchAll(
            "SELECT hf.*, o.order_number, o.total_amount as order_total
             FROM {$this->table} hf
             JOIN orders o ON o.id = hf.order_id
             WHERE hf.seller_id = ? AND hf.status = 'holding'
             ORDER BY hf.hold_until ASC",
            [$sellerId]
        );
    }

    public function getTotalHeldBySeller($sellerId) {
        $result = $this->db->fetchOne(
            "SELECT SUM(amount) as total
             FROM {$this->table}
             WHERE seller_id = ? AND status = 'holding'",
            [$sellerId]
        );
        return $result['total'] ?? 0;
    }

    public function getExpiredHolds() {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table}
             WHERE status = 'holding' AND hold_until <= NOW()
             ORDER BY hold_until ASC"
        );
    }

    public function release($id) {
        return $this->update($id, [
            'status' => 'released',
            'released_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function refund($id, $reason = '') {
        return $this->update($id, [
            'status' => 'refunded',
            'released_at' => date('Y-m-d H:i:s'),
            'reason' => $reason
        ]);
    }
}
