<?php
// app/Services/EscrowService.php

class EscrowService {
    private $db;
    private $walletService;
    private $depositModel;
    private $heldFundModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->walletService = new WalletService();
        $this->depositModel = new SellerDeposit();
        $this->heldFundModel = new HeldFund();
    }

    /**
     * Tính tiền cọc = 100% giá trị stock (số lượng × giá)
     * Ví dụ: 5 con × 100k = 500k → trừ 500k từ ví seller
     */
    public function calculateDepositRequired($productPrice, $quantity) {
        return $productPrice * $quantity; // 100% giá trị
    }

    /**
     * Tạo và thanh toán tiền cọc khi seller nhập stock
     * Trả về ['success', 'message', 'deposit_amount']
     */
    public function processStockDeposit($sellerId, $productId, $quantity, $productPrice) {
        $depositAmount = $this->calculateDepositRequired($productPrice, $quantity);
        $totalValue    = $depositAmount; // 100%

        // Kiểm tra số dư
        $wallet = $this->walletService->getWallet($sellerId);
        if (($wallet['balance'] ?? 0) < $depositAmount) {
            $needed = $depositAmount - ($wallet['balance'] ?? 0);
            return [
                'success' => false,
                'message' => "Số dư không đủ! Cần " . money($depositAmount) . " để nhập {$quantity} sản phẩm (100% giá trị). Vui lòng nạp thêm " . money($needed) . "."
            ];
        }

        try {
            $this->db->beginTransaction();

            // Tạo bản ghi deposit
            $depositId = $this->depositModel->create([
                'seller_id'          => $sellerId,
                'product_id'         => $productId,
                'stock_quantity'     => $quantity,
                'product_value'      => $totalValue,
                'deposit_amount'     => $depositAmount,
                'deposit_percentage' => 100,
                'status'             => 'paid',
                'paid_at'            => date('Y-m-d H:i:s')
            ]);

            // Trừ tiền từ balance → deposit_balance
            $this->walletService->deductMoney(
                $sellerId,
                $depositAmount,
                'withdrawal',
                'deposit',
                $depositId,
                "Tiền cọc nhập {$quantity} stock sản phẩm #{$productId}"
            );

            // Cộng vào deposit_balance
            $this->db->query(
                "UPDATE wallets SET deposit_balance = deposit_balance + ? WHERE user_id = ?",
                [$depositAmount, $sellerId]
            );

            $this->db->commit();

            return [
                'success'        => true,
                'deposit_amount' => $depositAmount,
                'message'        => 'Đã trừ tiền cọc thành công'
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    /**
     * Khi khách mua hàng: tiền seller nhận vào held_balance (giữ 7 ngày)
     * Đồng thời hoàn lại tiền cọc tương ứng từ deposit_balance → balance
     */
    public function holdFundsFromOrder($orderId, $sellerId, $amount, $productId = null, $quantity = 1) {
        $holdDays  = $this->getHoldDays();
        $holdUntil = date('Y-m-d H:i:s', strtotime("+{$holdDays} days"));

        try {
            $this->db->beginTransaction();

            // Tạo held_fund record
            $heldFundId = $this->heldFundModel->create([
                'order_id'   => $orderId,
                'seller_id'  => $sellerId,
                'amount'     => $amount,
                'hold_until' => $holdUntil,
                'status'     => 'holding'
            ]);

            // Cộng vào held_balance
            $this->db->query(
                "UPDATE wallets SET held_balance = held_balance + ? WHERE user_id = ?",
                [$amount, $sellerId]
            );

            // Hoàn lại tiền cọc tương ứng (deposit_balance → balance)
            // Tìm deposit gần nhất của sản phẩm này
            if ($productId) {
                $depositPerUnit = 0;
                $deposit = $this->db->fetchOne(
                    "SELECT deposit_amount, stock_quantity FROM seller_deposits
                     WHERE seller_id = ? AND product_id = ? AND status = 'paid'
                     ORDER BY created_at DESC LIMIT 1",
                    [$sellerId, $productId]
                );
                if ($deposit && $deposit['stock_quantity'] > 0) {
                    $depositPerUnit = $deposit['deposit_amount'] / $deposit['stock_quantity'];
                    $refundDeposit  = $depositPerUnit * $quantity;

                    // Trừ deposit_balance, cộng balance
                    $this->db->query(
                        "UPDATE wallets SET deposit_balance = GREATEST(0, deposit_balance - ?), balance = balance + ? WHERE user_id = ?",
                        [$refundDeposit, $refundDeposit, $sellerId]
                    );
                }
            }

            // Ghi log transaction
            $this->db->insert('transactions', [
                'user_id'          => $sellerId,
                'type'             => 'sale_income',
                'amount'           => $amount,
                'description'      => "Tiền từ đơn hàng #{$orderId} (giữ {$holdDays} ngày, tự động chuyển vào số dư sau {$holdDays} ngày)",
                'transaction_type' => 'fund_hold',
                'related_id'       => $heldFundId
            ]);

            $this->db->commit();

            return ['success' => true, 'held_fund_id' => $heldFundId];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Release tiền sau 7 ngày: held_balance → balance
     */
    public function releaseFunds($heldFundId) {
        $heldFund = $this->heldFundModel->find($heldFundId);

        if (!$heldFund || $heldFund['status'] != 'holding') {
            return ['success' => false, 'message' => 'Không tìm thấy tiền đang giữ'];
        }

        try {
            $this->db->beginTransaction();

            // held_balance → balance (100%, seller rút được trừ 5% phí rút)
            $this->db->query(
                "UPDATE wallets SET held_balance = GREATEST(0, held_balance - ?), balance = balance + ? WHERE user_id = ?",
                [$heldFund['amount'], $heldFund['amount'], $heldFund['seller_id']]
            );

            $this->heldFundModel->release($heldFundId);

            $this->db->insert('transactions', [
                'user_id'          => $heldFund['seller_id'],
                'type'             => 'sale_income',
                'amount'           => $heldFund['amount'],
                'description'      => "Đã release tiền từ đơn hàng #{$heldFund['order_id']} (hết thời gian giữ)",
                'transaction_type' => 'fund_release',
                'related_id'       => $heldFundId
            ]);

            $this->db->commit();

            return ['success' => true, 'message' => 'Đã release tiền thành công'];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Auto-release tất cả tiền đã hết hạn giữ (chạy bằng cron)
     */
    public function autoReleaseExpiredFunds() {
        $expiredFunds = $this->heldFundModel->getExpiredHolds();
        $released = 0;

        foreach ($expiredFunds as $fund) {
            $result = $this->releaseFunds($fund['id']);
            if ($result['success']) {
                $released++;
            }
        }

        return ['success' => true, 'released_count' => $released];
    }

    private function getHoldDays() {
        $setting = $this->db->fetchOne("SELECT setting_value FROM system_settings WHERE setting_key = 'hold_days'");
        return $setting ? (int)$setting['setting_value'] : 7;
    }

    public function isEscrowEnabled() {
        $setting = $this->db->fetchOne("SELECT setting_value FROM system_settings WHERE setting_key = 'enable_escrow'");
        return $setting && $setting['setting_value'] == '1';
    }
}
