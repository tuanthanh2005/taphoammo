<?php
// app/Services/WalletService.php

class WalletService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getWallet($userId) {
        $wallet = $this->db->fetchOne("SELECT * FROM wallets WHERE user_id = ?", [$userId]);
        
        if (!$wallet) {
            // Create wallet if not exists
            $this->db->insert('wallets', ['user_id' => $userId, 'balance' => 0]);
            $wallet = $this->getWallet($userId);
        }
        
        return $wallet;
    }
    
    public function addMoney($userId, $amount, $type, $referenceType = null, $referenceId = null, $description = '') {
        $wallet = $this->getWallet($userId);
        $balanceBefore = $wallet['balance'];
        $balanceAfter = $balanceBefore + $amount;
        
        // Update wallet balance
        $this->db->update('wallets', [
            'balance' => $balanceAfter,
            'total_earned' => $wallet['total_earned'] + $amount
        ], 'user_id = :user_id', ['user_id' => $userId]);
        
        // Create transaction record
        $this->db->insert('transactions', [
            'user_id' => $userId,
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description
        ]);
        
        return $balanceAfter;
    }
    
    public function deductMoney($userId, $amount, $type, $referenceType = null, $referenceId = null, $description = '') {
        $wallet = $this->getWallet($userId);
        $balanceBefore = $wallet['balance'];
        
        if ($balanceBefore < $amount) {
            throw new Exception('Số dư không đủ');
        }
        
        $balanceAfter = $balanceBefore - $amount;
        
        // Update wallet balance
        $this->db->update('wallets', [
            'balance' => $balanceAfter
        ], 'user_id = :user_id', ['user_id' => $userId]);
        
        // Create transaction record
        $this->db->insert('transactions', [
            'user_id' => $userId,
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description
        ]);
        
        return $balanceAfter;
    }

    public function forceDeductMoney($userId, $amount, $type, $referenceType = null, $referenceId = null, $description = '') {
        $wallet = $this->getWallet($userId);
        $balanceBefore = (float)$wallet['balance'];
        $balanceAfter = $balanceBefore - (float)$amount;

        $this->db->update('wallets', [
            'balance' => $balanceAfter
        ], 'user_id = :user_id', ['user_id' => $userId]);

        $this->db->insert('transactions', [
            'user_id' => $userId,
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description
        ]);

        return $balanceAfter;
    }

    public function deductUpTo($userId, $amount, $type, $referenceType = null, $referenceId = null, $description = '') {
        $wallet = $this->getWallet($userId);
        $recover = min((float)$amount, max(0, (float)$wallet['balance']));

        if ($recover > 0) {
            $this->deductMoney($userId, $recover, $type, $referenceType, $referenceId, $description);
        }

        return $recover;
    }
    
    public function getTransactions($userId, $limit = 50) {
        return $this->db->fetchAll(
            "SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    public function getCombinedHistory($userId, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $sql = "
            (SELECT id, type, amount, balance_after, description, created_at, 'transaction' as source_table 
             FROM transactions WHERE user_id = ?)
            UNION
            (SELECT id, status as type, amount, NULL as balance_after, 
             CASE 
                WHEN status = 'rejected' THEN 'Nạp tiền thất bại' 
                ELSE 'Đang chờ xử lý' 
             END as description, 
             created_at, 'deposit_request' as source_table 
             FROM deposit_requests WHERE user_id = ? AND status != 'approved')
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ";
        return $this->db->fetchAll($sql, [$userId, $userId, (int)$perPage, (int)$offset]);
    }

    public function getCombinedHistoryCount($userId) {
        $sql = "
            SELECT (
                (SELECT COUNT(*) FROM transactions WHERE user_id = ?) + 
                (SELECT COUNT(*) FROM deposit_requests WHERE user_id = ? AND status != 'approved')
            ) as total
        ";
        return (int)$this->db->fetchOne($sql, [$userId, $userId])['total'];
    }
}
