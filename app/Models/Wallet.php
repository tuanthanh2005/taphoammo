<?php
// app/Models/Wallet.php

class Wallet extends Model {
    protected $table = 'wallets';
    
    public function getByUserId($userId) {
        return $this->db->fetchOne("SELECT * FROM {$this->table} WHERE user_id = ?", [$userId]);
    }
    
    public function updateBalance($userId, $amount, $type = 'add') {
        $wallet = $this->getByUserId($userId);
        
        if (!$wallet) {
            // Create wallet if not exists
            $this->create(['user_id' => $userId, 'balance' => 0]);
            $wallet = $this->getByUserId($userId);
        }
        
        $newBalance = $wallet['balance'];
        
        if ($type === 'add') {
            $newBalance += $amount;
        } else {
            $newBalance -= $amount;
        }
        
        $this->db->update(
            $this->table,
            ['balance' => $newBalance],
            'user_id = :user_id',
            ['user_id' => $userId]
        );
        
        return $newBalance;
    }
    
    public function addTransaction($userId, $type, $amount, $referenceType = null, $referenceId = null, $description = '') {
        $wallet = $this->getByUserId($userId);
        $balanceBefore = $wallet['balance'] ?? 0;
        
        // Calculate balance after based on transaction type
        $balanceAfter = $balanceBefore;
        if (in_array($type, ['deposit', 'sale_income', 'refund', 'affiliate_commission'])) {
            $balanceAfter += $amount;
        } else {
            $balanceAfter -= $amount;
        }
        
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
    }
    
    public function getTransactions($userId, $limit = 50) {
        return $this->db->fetchAll(
            "SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT {$limit}",
            [$userId]
        );
    }
}
