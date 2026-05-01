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

    public function calculateDepositRequired($productPrice, $quantity, $sellerId = null, $productId = null) {
        $percent = $this->getDepositPercent($sellerId, $productId);
        return (float)$productPrice * (int)$quantity * ($percent / 100);
    }

    public function processStockDeposit($sellerId, $productId, $quantity, $productPrice) {
        $depositPercent = $this->getDepositPercent($sellerId, $productId);
        $depositAmount = $this->calculateDepositRequired($productPrice, $quantity, $sellerId, $productId);
        $totalValue = (float)$productPrice * (int)$quantity;

        $wallet = $this->walletService->getWallet($sellerId);
        if (($wallet['balance'] ?? 0) < $depositAmount) {
            $needed = $depositAmount - ($wallet['balance'] ?? 0);
            return [
                'success' => false,
                'message' => "So du khong du! Can " . money($depositAmount) . " de nhap {$quantity} san pham ({$depositPercent}% gia tri). Vui long nap them " . money($needed) . "."
            ];
        }

        $startedTransaction = false;
        try {
            $startedTransaction = !$this->db->inTransaction();
            if ($startedTransaction) {
                $this->db->beginTransaction();
            }

            $depositId = $this->depositModel->create([
                'seller_id' => $sellerId,
                'product_id' => $productId,
                'stock_quantity' => $quantity,
                'product_value' => $totalValue,
                'deposit_amount' => $depositAmount,
                'deposit_percentage' => $depositPercent,
                'status' => 'paid',
                'paid_at' => date('Y-m-d H:i:s')
            ]);

            $this->walletService->deductMoney(
                $sellerId,
                $depositAmount,
                'withdrawal',
                'deposit',
                $depositId,
                "Tien coc nhap {$quantity} stock san pham #{$productId}"
            );

            $this->db->query(
                "UPDATE wallets SET deposit_balance = deposit_balance + ? WHERE user_id = ?",
                [$depositAmount, $sellerId]
            );

            if ($startedTransaction) {
                $this->db->commit();
            }

            return [
                'success' => true,
                'deposit_amount' => $depositAmount,
                'message' => 'Da tru tien coc thanh cong'
            ];

        } catch (Exception $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'message' => 'Loi: ' . $e->getMessage()];
        }
    }

    public function holdFundsFromOrder($orderId, $orderItemId, $sellerId, $amount, $productId = null, $quantity = 1) {
        $holdDays = $this->getHoldDays($productId);

        $startedTransaction = false;
        try {
            $startedTransaction = !$this->db->inTransaction();
            if ($startedTransaction) {
                $this->db->beginTransaction();
            }

            $this->releaseDepositForSale($sellerId, $productId, $quantity);

            if ($holdDays <= 0) {
                $this->walletService->addMoney(
                    $sellerId,
                    $amount,
                    'sale_income',
                    'order_item',
                    $orderItemId,
                    "Tien tu don hang #{$orderId} (khong giu do san pham khong bao hanh)"
                );

                if ($startedTransaction) {
                    $this->db->commit();
                }

                return ['success' => true, 'held_fund_id' => null];
            }

            $holdUntil = date('Y-m-d H:i:s', strtotime("+{$holdDays} days"));
            $heldFundId = $this->heldFundModel->create([
                'order_id' => $orderId,
                'order_item_id' => $orderItemId,
                'seller_id' => $sellerId,
                'amount' => $amount,
                'hold_until' => $holdUntil,
                'status' => 'holding'
            ]);

            $this->db->query(
                "UPDATE wallets SET held_balance = held_balance + ? WHERE user_id = ?",
                [$amount, $sellerId]
            );

            $this->db->insert('transactions', [
                'user_id' => $sellerId,
                'type' => 'sale_income',
                'amount' => $amount,
                'description' => "Tien tu don hang #{$orderId} (giu {$holdDays} ngay theo bao hanh san pham)",
                'transaction_type' => 'fund_hold',
                'related_id' => $heldFundId
            ]);

            if ($startedTransaction) {
                $this->db->commit();
            }

            return ['success' => true, 'held_fund_id' => $heldFundId];

        } catch (Exception $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function releaseFunds($heldFundId) {
        $heldFund = $this->heldFundModel->find($heldFundId);

        if (!$heldFund || $heldFund['status'] != 'holding') {
            return ['success' => false, 'message' => 'Khong tim thay tien dang giu'];
        }

        $startedTransaction = false;
        try {
            $startedTransaction = !$this->db->inTransaction();
            if ($startedTransaction) {
                $this->db->beginTransaction();
            }

            $this->db->query(
                "UPDATE wallets SET held_balance = GREATEST(0, held_balance - ?), balance = balance + ? WHERE user_id = ?",
                [$heldFund['amount'], $heldFund['amount'], $heldFund['seller_id']]
            );

            $this->heldFundModel->release($heldFundId);

            $this->db->insert('transactions', [
                'user_id' => $heldFund['seller_id'],
                'type' => 'sale_income',
                'amount' => $heldFund['amount'],
                'description' => "Da release tien tu don hang #{$heldFund['order_id']}",
                'transaction_type' => 'fund_release',
                'related_id' => $heldFundId
            ]);

            if ($startedTransaction) {
                $this->db->commit();
            }

            return ['success' => true, 'message' => 'Da release tien thanh cong'];

        } catch (Exception $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

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

    private function getDepositPercent($sellerId = null, $productId = null) {
        $setting = $this->db->fetchOne("SELECT setting_value FROM system_settings WHERE setting_key = 'deposit_percentage'");
        $percent = $setting ? (float)$setting['setting_value'] : 30;
        return max(0, min(100, $percent));
    }

    private function getHoldDays($productId = null) {
        if ($productId) {
            $product = $this->db->fetchOne("SELECT warranty_days FROM products WHERE id = ?", [$productId]);
            if ($product && array_key_exists('warranty_days', $product)) {
                $warrantyDays = max(0, (int)$product['warranty_days']);
                $minimumHoldDays = (int)ceil(Helper::getMinimumDisputeHours() / 24);
                return max($minimumHoldDays, $warrantyDays);
            }
        }

        $setting = $this->db->fetchOne("SELECT setting_value FROM system_settings WHERE setting_key = 'hold_days'");
        return $setting ? (int)$setting['setting_value'] : 7;
    }

    public function isEscrowEnabled() {
        $setting = $this->db->fetchOne("SELECT setting_value FROM system_settings WHERE setting_key = 'enable_escrow'");
        return $setting && $setting['setting_value'] == '1';
    }

    private function releaseDepositForSale($sellerId, $productId, $quantity) {
        if (!$productId || $quantity <= 0) {
            return;
        }

        $remainingQuantity = (int)$quantity;
        $deposits = $this->db->fetchAll(
            "SELECT id, stock_quantity, released_quantity, deposit_amount, released_deposit_amount
             FROM seller_deposits
             WHERE seller_id = ? AND product_id = ? AND status IN ('paid', 'released')
             ORDER BY created_at ASC, id ASC",
            [$sellerId, $productId]
        );

        foreach ($deposits as $deposit) {
            if ($remainingQuantity <= 0) {
                break;
            }

            $stockQuantity = max(0, (int)($deposit['stock_quantity'] ?? 0));
            $releasedQuantity = max(0, (int)($deposit['released_quantity'] ?? 0));
            $availableQuantity = max(0, $stockQuantity - $releasedQuantity);
            if ($availableQuantity <= 0) {
                continue;
            }

            $useQuantity = min($remainingQuantity, $availableQuantity);
            $depositAmount = (float)($deposit['deposit_amount'] ?? 0);
            $releasedAmount = (float)($deposit['released_deposit_amount'] ?? 0);
            $perUnitDeposit = $stockQuantity > 0 ? ($depositAmount / $stockQuantity) : 0;
            $wallet = $this->walletService->getWallet($sellerId);
            $walletDepositBalance = max(0, (float)($wallet['deposit_balance'] ?? 0));
            $releaseAmount = min($depositAmount - $releasedAmount, $perUnitDeposit * $useQuantity, $walletDepositBalance);

            if ($releaseAmount > 0) {
                $newReleasedQuantity = $releasedQuantity + $useQuantity;
                $newReleasedAmount = $releasedAmount + $releaseAmount;
                $newStatus = $newReleasedQuantity >= $stockQuantity ? 'released' : 'paid';

                $this->db->query(
                    "UPDATE seller_deposits
                     SET released_quantity = ?, released_deposit_amount = ?, status = ?, released_at = ?
                     WHERE id = ?",
                    [
                        $newReleasedQuantity,
                        $newReleasedAmount,
                        $newStatus,
                        $newStatus === 'released' ? date('Y-m-d H:i:s') : null,
                        $deposit['id']
                    ]
                );
                $this->db->query(
                    "UPDATE wallets
                     SET deposit_balance = GREATEST(0, deposit_balance - ?), balance = balance + ?
                     WHERE user_id = ?",
                    [$releaseAmount, $releaseAmount, $sellerId]
                );
            }

            $remainingQuantity -= $useQuantity;
        }
    }
}
