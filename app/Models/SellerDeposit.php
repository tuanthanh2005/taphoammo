<?php
// app/Models/SellerDeposit.php

class SellerDeposit extends Model {
    protected $table = 'seller_deposits';

    public function getDepositsBySeller($sellerId) {
        return $this->db->fetchAll(
            "SELECT sd.*, p.name as product_name, p.slug as product_slug
             FROM {$this->table} sd
             JOIN products p ON p.id = sd.product_id
             WHERE sd.seller_id = ?
             ORDER BY sd.created_at DESC",
            [$sellerId]
        );
    }

    public function getTotalDepositBySeller($sellerId) {
        $result = $this->db->fetchOne(
            "SELECT SUM(deposit_amount) as total
             FROM {$this->table}
             WHERE seller_id = ? AND status = 'paid'",
            [$sellerId]
        );
        return $result['total'] ?? 0;
    }

    public function getPendingDeposits() {
        return $this->db->fetchAll(
            "SELECT sd.*, p.name as product_name, u.username as seller_name
             FROM {$this->table} sd
             JOIN products p ON p.id = sd.product_id
             JOIN users u ON u.id = sd.seller_id
             WHERE sd.status = 'pending'
             ORDER BY sd.created_at DESC"
        );
    }

    public function markAsPaid($id) {
        return $this->update($id, [
            'status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function release($id) {
        return $this->update($id, [
            'status' => 'released',
            'released_at' => date('Y-m-d H:i:s')
        ]);
    }
}
