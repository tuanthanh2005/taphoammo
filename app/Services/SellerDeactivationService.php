<?php
// app/Services/SellerDeactivationService.php

class SellerDeactivationService {
    private $db;
    private $walletService;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->walletService = new WalletService();
    }

    /**
     * Seller yêu cầu hủy tài khoản và rút toàn bộ tiền
     */
    public function requestDeactivation($sellerId, $reason = '') {
        try {
            $this->db->beginTransaction();

            // Kiểm tra xem đã có yêu cầu pending chưa
            $existing = $this->db->fetchOne(
                "SELECT * FROM seller_deactivation_requests WHERE seller_id = ? AND status = 'pending'",
                [$sellerId]
            );

            if ($existing) {
                throw new Exception('Bạn đã có yêu cầu hủy tài khoản đang chờ xử lý');
            }

            // Lấy số dư ví
            $wallet = $this->walletService->getWallet($sellerId);
            $totalBalance = $wallet['balance'] + ($wallet['deposit_balance'] ?? 0);

            if ($totalBalance <= 0) {
                throw new Exception('Số dư tài khoản của bạn là 0đ, không cần yêu cầu hủy');
            }

            // Kiểm tra có tiền đang bị hold không
            if (($wallet['held_balance'] ?? 0) > 0) {
                throw new Exception('Bạn còn ' . money($wallet['held_balance']) . ' đang bị giữ. Vui lòng đợi tiền được release trước khi hủy tài khoản.');
            }

            // Lấy cấu hình số ngày chờ
            $holdDaysSetting = $this->db->fetchOne(
                "SELECT setting_value FROM system_settings WHERE setting_key = 'seller_deactivation_hold_days'"
            );
            $holdDays = $holdDaysSetting ? (int)$holdDaysSetting['setting_value'] : 7;

            $holdUntil = date('Y-m-d H:i:s', strtotime("+{$holdDays} days"));

            // Tạo yêu cầu hủy
            $requestId = $this->db->insert('seller_deactivation_requests', [
                'seller_id' => $sellerId,
                'request_date' => date('Y-m-d H:i:s'),
                'hold_until' => $holdUntil,
                'refund_amount' => $totalBalance,
                'status' => 'pending',
                'reason' => $reason
            ]);

            // Đóng băng tài khoản seller (không cho bán hàng mới)
            $this->db->query(
                "UPDATE users SET status = 'suspended' WHERE id = ?",
                [$sellerId]
            );

            $this->db->commit();

            return [
                'success' => true,
                'message' => "Yêu cầu hủy tài khoản đã được gửi. Bạn sẽ nhận lại {$totalBalance}đ sau {$holdDays} ngày (vào ngày " . date('d/m/Y', strtotime($holdUntil)) . ")",
                'hold_until' => $holdUntil,
                'refund_amount' => $totalBalance
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Seller hủy yêu cầu deactivation
     */
    public function cancelRequest($sellerId) {
        try {
            $this->db->beginTransaction();

            $request = $this->db->fetchOne(
                "SELECT * FROM seller_deactivation_requests WHERE seller_id = ? AND status = 'pending'",
                [$sellerId]
            );

            if (!$request) {
                throw new Exception('Không tìm thấy yêu cầu hủy tài khoản');
            }

            // Cập nhật status
            $this->db->update('seller_deactivation_requests', [
                'status' => 'cancelled'
            ], 'id = :id', ['id' => $request['id']]);

            // Mở lại tài khoản
            $this->db->query(
                "UPDATE users SET status = 'active' WHERE id = ?",
                [$sellerId]
            );

            $this->db->commit();

            return ['success' => true, 'message' => 'Đã hủy yêu cầu deactivation'];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Auto-process các yêu cầu đã hết hạn chờ (chạy bằng cron)
     */
    public function processExpiredRequests() {
        $requests = $this->db->fetchAll(
            "SELECT * FROM seller_deactivation_requests 
             WHERE status = 'pending' AND hold_until <= NOW()"
        );

        $processed = 0;

        foreach ($requests as $request) {
            try {
                $this->db->beginTransaction();

                // Lấy số dư hiện tại
                $wallet = $this->walletService->getWallet($request['seller_id']);
                $totalBalance = $wallet['balance'] + ($wallet['deposit_balance'] ?? 0);

                // Tạo withdrawal request tự động
                $withdrawalId = $this->db->insert('withdrawals', [
                    'user_id' => $request['seller_id'],
                    'amount' => $totalBalance,
                    'fee_percent' => 0, // Không tính phí khi hủy tài khoản
                    'fee_amount' => 0,
                    'receive_amount' => $totalBalance,
                    'method' => 'bank_transfer',
                    'account_info' => 'Hoàn tiền từ hủy tài khoản seller',
                    'status' => 'approved',
                    'processed_at' => date('Y-m-d H:i:s')
                ]);

                // Trừ tiền từ ví
                if ($wallet['balance'] > 0) {
                    $this->walletService->deductMoney(
                        $request['seller_id'],
                        $wallet['balance'],
                        'withdrawal',
                        'deactivation',
                        $request['id'],
                        'Hoàn tiền từ hủy tài khoản seller'
                    );
                }

                // Trừ deposit_balance
                if (($wallet['deposit_balance'] ?? 0) > 0) {
                    $this->db->query(
                        "UPDATE wallets SET deposit_balance = 0 WHERE user_id = ?",
                        [$request['seller_id']]
                    );
                }

                // Cập nhật request
                $this->db->update('seller_deactivation_requests', [
                    'status' => 'approved',
                    'processed_at' => date('Y-m-d H:i:s')
                ], 'id = :id', ['id' => $request['id']]);

                // Chuyển user về role 'user'
                $this->db->query(
                    "UPDATE users SET role = 'user', status = 'active' WHERE id = ?",
                    [$request['seller_id']]
                );

                $this->db->commit();
                $processed++;

            } catch (Exception $e) {
                $this->db->rollBack();
                error_log("Error processing deactivation request #{$request['id']}: " . $e->getMessage());
            }
        }

        return ['success' => true, 'processed' => $processed];
    }

    /**
     * Lấy yêu cầu deactivation của seller
     */
    public function getSellerRequest($sellerId) {
        return $this->db->fetchOne(
            "SELECT * FROM seller_deactivation_requests WHERE seller_id = ? AND status = 'pending'",
            [$sellerId]
        );
    }
}
