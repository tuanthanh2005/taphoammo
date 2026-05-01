<?php
// app/Models/Withdrawal.php

class Withdrawal extends Model {
    protected $table = 'withdrawals';
    
    public function createWithdrawal($userId, $amount, $method, $accountInfo) {
        $config = require __DIR__ . '/../../config/payment.php';
        $feePercent = $config['seller_withdraw_fee_percent'];
        
        $feeAmount = $amount * ($feePercent / 100);
        $receiveAmount = $amount - $feeAmount;
        
        return $this->create([
            'user_id' => $userId,
            'amount' => $amount,
            'fee_percent' => $feePercent,
            'fee_amount' => $feeAmount,
            'receive_amount' => $receiveAmount,
            'method' => $method,
            'account_info' => $accountInfo,
            'status' => 'pending'
        ]);
    }
    
    public function getUserWithdrawals($userId, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    public function getPendingWithdrawals() {
        $sql = "SELECT w.*, u.name as user_name, u.email as user_email
                FROM {$this->table} w
                LEFT JOIN users u ON w.user_id = u.id
                WHERE w.status = 'pending'
                ORDER BY w.created_at ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function getAllWithdrawals($page = 1, $perPage = 50, $search = '') {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        if (!empty($search)) {
            $where .= " AND (u.name LIKE ? OR u.email LIKE ? OR w.account_info LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql = "SELECT w.*, u.name as user_name, u.email as user_email,
                       (SELECT COUNT(*) FROM disputes WHERE seller_id = w.user_id AND status IN ('open', 'under_review')) as active_disputes
                FROM {$this->table} w
                LEFT JOIN users u ON w.user_id = u.id
                WHERE {$where}
                ORDER BY w.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
        
        return $this->db->fetchAll($sql, $params);
    }
}
