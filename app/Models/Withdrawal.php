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
    
    public function getUserWithdrawals($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC";
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
    
    public function getAllWithdrawals($page = 1, $perPage = 50) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT w.*, u.name as user_name, u.email as user_email
                FROM {$this->table} w
                LEFT JOIN users u ON w.user_id = u.id
                ORDER BY w.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
        
        return $this->db->fetchAll($sql);
    }
}
