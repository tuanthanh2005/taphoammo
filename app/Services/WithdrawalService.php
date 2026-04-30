<?php
// app/Services/WithdrawalService.php

class WithdrawalService {
    private $db;
    private $walletService;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->walletService = new WalletService();
    }
    
    public function requestWithdrawal($userId, $amount, $method, $accountInfo) {
        try {
            $this->db->beginTransaction();
            
            // Lấy cấu hình từ database
            $minAmountSetting = $this->db->fetchOne("SELECT value FROM settings WHERE key_name = 'min_withdraw_amount'");
            $feePercentSetting = $this->db->fetchOne("SELECT value FROM settings WHERE key_name = 'seller_withdraw_fee_percent'");
            $minBalanceSetting = $this->db->fetchOne("SELECT setting_value FROM system_settings WHERE setting_key = 'seller_minimum_balance'");
            
            $minAmount = $minAmountSetting ? (int)$minAmountSetting['value'] : 50000;
            $feePercent = $feePercentSetting ? (float)$feePercentSetting['value'] : 5;
            $minBalance = $minBalanceSetting ? (float)$minBalanceSetting['setting_value'] : 500000;
            
            // Validate amount
            if ($amount < $minAmount) {
                throw new Exception('Số tiền rút tối thiểu là ' . number_format($minAmount) . 'đ');
            }
            
            // Check wallet balance
            $wallet = $this->walletService->getWallet($userId);
            if ($wallet['balance'] < $amount) {
                throw new Exception('Số dư không đủ');
            }
            
            // Calculate fee
            $feeAmount = $amount * ($feePercent / 100);
            $receiveAmount = $amount - $feeAmount;
            
            // Deduct money from wallet
            $this->walletService->deductMoney($userId, $amount, 'withdrawal', 'withdrawal', null, 
                'Yêu cầu rút tiền ' . number_format($amount) . 'đ');
            
            // Create withdrawal request
            $withdrawalId = $this->db->insert('withdrawals', [
                'user_id' => $userId,
                'amount' => $amount,
                'fee_percent' => $feePercent,
                'fee_amount' => $feeAmount,
                'receive_amount' => $receiveAmount,
                'method' => $method,
                'account_info' => $accountInfo,
                'status' => 'pending'
            ]);
            
            // Update transaction reference
            $this->db->query(
                "UPDATE transactions SET reference_id = ? WHERE user_id = ? AND type = 'withdrawal' AND reference_id IS NULL ORDER BY id DESC LIMIT 1",
                [$withdrawalId, $userId]
            );
            
            $this->db->commit();
            
            return ['success' => true, 'withdrawal_id' => $withdrawalId];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function approveWithdrawal($withdrawalId, $adminId) {
        try {
            $this->db->beginTransaction();
            
            $withdrawal = $this->db->fetchOne("SELECT * FROM withdrawals WHERE id = ?", [$withdrawalId]);
            
            if (!$withdrawal) {
                throw new Exception('Không tìm thấy yêu cầu rút tiền');
            }
            
            if ($withdrawal['status'] !== 'pending') {
                throw new Exception('Yêu cầu rút tiền đã được xử lý');
            }
            
            // Update withdrawal status
            $this->db->update('withdrawals', [
                'status' => 'approved',
                'processed_by' => $adminId,
                'processed_at' => date('Y-m-d H:i:s')
            ], 'id = :id', ['id' => $withdrawalId]);
            
            // Add withdrawal fee to admin wallet
            $this->walletService->addMoney(1, $withdrawal['fee_amount'], 'withdrawal_fee', 'withdrawal', $withdrawalId,
                'Phí rút tiền từ yêu cầu #' . $withdrawalId);
            
            // Update wallet total_withdrawn
            $this->db->query(
                "UPDATE wallets SET total_withdrawn = total_withdrawn + ? WHERE user_id = ?",
                [$withdrawal['amount'], $withdrawal['user_id']]
            );
            
            $this->db->commit();
            
            return ['success' => true];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function rejectWithdrawal($withdrawalId, $adminId, $reason) {
        try {
            $this->db->beginTransaction();
            
            $withdrawal = $this->db->fetchOne("SELECT * FROM withdrawals WHERE id = ?", [$withdrawalId]);
            
            if (!$withdrawal) {
                throw new Exception('Không tìm thấy yêu cầu rút tiền');
            }
            
            if ($withdrawal['status'] !== 'pending') {
                throw new Exception('Yêu cầu rút tiền đã được xử lý');
            }
            
            // Update withdrawal status
            $this->db->update('withdrawals', [
                'status' => 'rejected',
                'admin_note' => $reason,
                'processed_by' => $adminId,
                'processed_at' => date('Y-m-d H:i:s')
            ], 'id = :id', ['id' => $withdrawalId]);
            
            // Refund money to user wallet
            $this->walletService->addMoney($withdrawal['user_id'], $withdrawal['amount'], 'refund', 'withdrawal', $withdrawalId,
                'Hoàn tiền từ yêu cầu rút tiền bị từ chối #' . $withdrawalId);
            
            $this->db->commit();
            
            return ['success' => true];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
