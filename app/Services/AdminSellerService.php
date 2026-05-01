<?php
// app/Services/AdminSellerService.php

class AdminSellerService {
    private $db;
    private $walletService;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->walletService = new WalletService();
    }

    public function getSellerSummary($sellerId) {
        $seller = $this->db->fetchOne(
            "SELECT id, role, status, name, email FROM users WHERE id = ? AND role = 'seller'",
            [$sellerId]
        );

        if (!$seller) {
            return null;
        }

        $summary = $this->db->fetchOne(
            "SELECT
                COUNT(*) AS total_items,
                SUM(CASE WHEN item_status NOT IN ('delivered', 'refunded', 'released') THEN 1 ELSE 0 END) AS open_items,
                SUM(CASE WHEN item_status = 'disputed' THEN 1 ELSE 0 END) AS disputed_items
             FROM order_items
             WHERE seller_id = ?",
            [$sellerId]
        );

        $seller['total_items'] = (int)($summary['total_items'] ?? 0);
        $seller['open_items'] = (int)($summary['open_items'] ?? 0);
        $seller['disputed_items'] = (int)($summary['disputed_items'] ?? 0);

        return $seller;
    }

    public function banSellerIfSettled($adminId, $sellerId) {
        try {
            $summary = $this->getSellerSummary($sellerId);
            if (!$summary) {
                throw new Exception('Seller khong ton tai.');
            }

            if ($summary['open_items'] > 0) {
                throw new Exception('Người bán vẫn còn đơn hàng đang xử lý. Hãy bấm "Hoàn tiền tất cả rồi khóa" để đóng toàn bộ đơn mở trước khi khóa tài khoản.');
            }

            $this->db->update('users', ['status' => 'banned'], 'id = :id', ['id' => $sellerId]);
            Auth::logAction('admin_ban_seller', "Admin #{$adminId} khoa seller #{$sellerId}");

            return ['success' => true, 'message' => 'Đã khóa tài khoản người bán.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function restoreSeller($adminId, $sellerId) {
        try {
            $summary = $this->getSellerSummary($sellerId);
            if (!$summary) {
                throw new Exception('Seller khong ton tai.');
            }

            $this->db->update('users', ['status' => 'active'], 'id = :id', ['id' => $sellerId]);
            Auth::logAction('admin_restore_seller', "Admin #{$adminId} mo khoa seller #{$sellerId}");

            return ['success' => true, 'message' => 'Đã mở khóa tài khoản người bán.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function refundOpenOrdersAndBanSeller($adminId, $sellerId, $note = '') {
        try {
            $summary = $this->getSellerSummary($sellerId);
            if (!$summary) {
                throw new Exception('Seller khong ton tai.');
            }

            $openItems = $this->db->fetchAll(
                "SELECT oi.*, o.order_code, o.user_id AS buyer_id, p.product_type
                 FROM order_items oi
                 INNER JOIN orders o ON o.id = oi.order_id
                 LEFT JOIN products p ON p.id = oi.product_id
                 WHERE oi.seller_id = ?
                   AND oi.item_status NOT IN ('delivered', 'refunded', 'released')
                 ORDER BY oi.order_id ASC, oi.id ASC",
                [$sellerId]
            );

            $this->db->beginTransaction();

            foreach ($openItems as $item) {
                $this->forceRefundOrderItem($adminId, $sellerId, $item, $note);
                $this->syncOrderStatus($item['order_id']);
            }

            $this->db->update('users', ['status' => 'banned'], 'id = :id', ['id' => $sellerId]);
            Auth::logAction('admin_force_refund_ban_seller', "Admin #{$adminId} hoan tien va khoa seller #{$sellerId}");

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Đã hoàn tiền tất cả đơn đang xử lý và khóa tài khoản người bán.',
                'refunded_items' => count($openItems),
            ];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function forceRefundOrderItem($adminId, $sellerId, array $item, $note = '') {
        $refundAmountToBuyer = (float)$item['subtotal'];
        $sellerAmountToRecover = (float)$item['seller_amount'];
        $adminFeeAmount = (float)$item['admin_fee_amount'];
        $systemUserId = Helper::getSystemUserId();

        $sellerRecovered = $this->recoverFromHeldFunds($sellerId, $item['order_id'], $item['id'], $sellerAmountToRecover);
        $remainingSellerRecover = max(0, $sellerAmountToRecover - $sellerRecovered);

        if ($remainingSellerRecover > 0) {
            $sellerBalanceRecovered = $this->recoverFromWalletBalance(
                $sellerId,
                $remainingSellerRecover,
                'refund',
                'order_item',
                $item['id'],
                "Admin thu hồi tiền người bán khi đóng tài khoản, đơn #{$item['order_code']}"
            );
            $remainingSellerRecover = max(0, $remainingSellerRecover - $sellerBalanceRecovered);
        }

        $platformCoverage = $adminFeeAmount + $remainingSellerRecover;
        if ($platformCoverage > 0) {
            $this->forceDeductMoney(
                $systemUserId,
                $platformCoverage,
                'refund',
                'order_item',
                $item['id'],
                "Admin ứng tiền đóng người bán #{$sellerId} cho đơn #{$item['order_code']}"
            );
        }

        $this->walletService->addMoney(
            $item['buyer_id'],
            $refundAmountToBuyer,
            'refund',
            'order_item',
            $item['id'],
            "Hoàn tiền do admin đóng tài khoản người bán, đơn #{$item['order_code']}"
        );

        $finalNote = trim($note);
        if ($finalNote === '') {
            $finalNote = 'Admin hoàn tiền và khóa tài khoản người bán.';
        }

        $this->db->update('order_items', [
            'item_status' => 'refunded',
            'seller_note' => $finalNote,
            'status_updated_at' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $item['id']]);

        $this->restoreOrInvalidateStock($item);

            $this->db->query(
                "UPDATE disputes
                 SET status = 'resolved_refund',
                 admin_id = ?,
                 admin_note = ?,
                 refund_amount = amount,
                 resolved_at = NOW()
             WHERE order_item_id = ?
               AND status IN ('open', 'under_review')",
            [$adminId, $finalNote, $item['id']]
        );
    }

    private function recoverFromHeldFunds($sellerId, $orderId, $orderItemId, $targetAmount) {
        $remaining = (float)$targetAmount;
        $recovered = 0.0;

        $heldFunds = $this->db->fetchAll(
            "SELECT * FROM held_funds
             WHERE seller_id = ?
               AND ((order_item_id = ?)
                OR (order_item_id IS NULL AND order_id = ?))
               AND status IN ('holding', 'disputed')
               AND amount > 0
             ORDER BY CASE WHEN order_item_id = ? THEN 0 ELSE 1 END ASC, id ASC",
            [$sellerId, $orderItemId, $orderId, $orderItemId]
        );

        foreach ($heldFunds as $heldFund) {
            if ($remaining <= 0) {
                break;
            }

            $usable = min($remaining, (float)$heldFund['amount']);
            if ($usable <= 0) {
                continue;
            }

            $newAmount = (float)$heldFund['amount'] - $usable;
            $newStatus = $newAmount > 0 ? $heldFund['status'] : 'refunded';

            $this->db->query(
                "UPDATE wallets SET held_balance = GREATEST(0, held_balance - ?) WHERE user_id = ?",
                [$usable, $sellerId]
            );

            $this->db->query(
                "UPDATE held_funds
                 SET amount = ?, status = ?, released_at = ?
                 WHERE id = ?",
                [$newAmount, $newStatus, $newAmount > 0 ? null : date('Y-m-d H:i:s'), $heldFund['id']]
            );

            $this->db->insert('transactions', [
                'user_id' => $sellerId,
                'type' => 'refund',
                'amount' => -$usable,
                'reference_type' => 'held_fund',
                'reference_id' => $heldFund['id'],
                'description' => "Trừ tiền đang giữ khi admin đóng tài khoản người bán, đơn #{$orderId}"
            ]);

            $remaining -= $usable;
            $recovered += $usable;
        }

        return $recovered;
    }

    private function recoverFromWalletBalance($userId, $targetAmount, $type, $referenceType, $referenceId, $description) {
        $wallet = $this->walletService->getWallet($userId);
        $available = max(0, (float)($wallet['balance'] ?? 0));
        $recover = min($available, (float)$targetAmount);

        if ($recover > 0) {
            $this->walletService->deductMoney($userId, $recover, $type, $referenceType, $referenceId, $description);
        }

        return $recover;
    }

    private function forceDeductMoney($userId, $amount, $type, $referenceType = null, $referenceId = null, $description = '') {
        $wallet = $this->walletService->getWallet($userId);
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
    }

    private function restoreOrInvalidateStock(array $item) {
        $reusableTypes = ['file', 'link', 'service'];
        if (in_array($item['product_type'], $reusableTypes, true)) {
            $stockStatus = 'available';
            $orderIdValue = null;
            $soldAtValue = null;
        } else {
            $stockStatus = 'invalidated';
            $orderIdValue = $item['order_id'];
            $soldAtValue = date('Y-m-d H:i:s');
        }

        $this->db->query(
            "UPDATE product_stocks
             SET status = ?, order_id = ?, sold_at = ?
             WHERE order_id = ? AND product_id = ?",
            [$stockStatus, $orderIdValue, $soldAtValue, $item['order_id'], $item['product_id']]
        );

        $productModel = new Product();
        $productModel->updateStock($item['product_id']);
    }

    private function syncOrderStatus($orderId) {
        $remainingItems = $this->db->fetchOne(
            "SELECT COUNT(*) AS count
             FROM order_items
             WHERE order_id = ?
               AND item_status NOT IN ('delivered', 'refunded', 'released')",
            [$orderId]
        );

        if ((int)($remainingItems['count'] ?? 0) === 0) {
            $this->db->update('orders', ['order_status' => 'completed'], 'id = :id', ['id' => $orderId]);
        }
    }
}
