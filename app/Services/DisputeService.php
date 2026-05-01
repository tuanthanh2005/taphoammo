<?php
// app/Services/DisputeService.php

class DisputeService {
    private $db;
    private $walletService;
    private $escrowService;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->walletService = new WalletService();
        $this->escrowService = new EscrowService();
    }

    public function createDispute($userId, $orderId, $reason, $description, $evidenceImages = [], $orderItemId = null) {
        try {
            $this->db->beginTransaction();

            $order = $this->db->fetchOne("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$orderId, $userId]);
            if (!$order) {
                throw new Exception("Don hang khong ton tai hoac khong thuoc ve ban.");
            }

            $params = [$orderId];
            $sql = "SELECT oi.*, p.name as product_name, p.product_type, COALESCE(p.warranty_days, 0) as warranty_days, p.warranty_note
                    FROM order_items oi
                    LEFT JOIN products p ON p.id = oi.product_id
                    WHERE oi.order_id = ?";
            if ($orderItemId) {
                $sql .= " AND oi.id = ?";
                $params[] = $orderItemId;
            }
            $sql .= " LIMIT 1";

            $orderItem = $this->db->fetchOne($sql, $params);
            if (!$orderItem) {
                throw new Exception("San pham trong don hang khong hop le.");
            }

            $sellerId = $orderItem['seller_id'];
            $disputeAmount = $orderItem['subtotal'];
            $warrantyDays = max(0, (int)($orderItem['warranty_days'] ?? 0));
            $minimumDisputeHours = Helper::getMinimumDisputeHours();
            $protectionSeconds = max($minimumDisputeHours * 3600, $warrantyDays * 86400);

            if ($warrantyDays < 0) {
                 throw new Exception("Thoi gian bao hanh khong hop le.");
            }

            $protectionStart = $this->getDisputeProtectionStartAt($orderItem, $order['created_at']);
            if (time() - strtotime($protectionStart) > $protectionSeconds) {
                $windowText = $warrantyDays > 0
                    ? "{$warrantyDays} ngay"
                    : "{$minimumDisputeHours} gio";
                throw new Exception("Thoi han khieu nai ({$windowText}) cho san pham nay da het.");
            }

            $active = $this->db->fetchOne(
                "SELECT id FROM disputes WHERE order_item_id = ? AND status IN ('open', 'under_review')",
                [$orderItem['id']]
            );
            if ($active) {
                throw new Exception("San pham nay dang co khieu nai duoc xu ly. Vui long cho ket qua.");
            }

            $disputeCount = $this->db->fetchOne(
                "SELECT COUNT(*) as total FROM disputes WHERE order_item_id = ?",
                [$orderItem['id']]
            )['total'];

            if ($disputeCount >= 2) {
                throw new Exception("Ban da su dung het 2 luot khieu nai cho san pham nay. Khong the tao them.");
            }

            $disputeModel = new Dispute();
            $evidenceJson = empty($evidenceImages) ? null : json_encode($evidenceImages);

            $disputeId = $disputeModel->createDispute([
                'order_id' => $orderId,
                'order_item_id' => $orderItem['id'],
                'user_id' => $userId,
                'seller_id' => $sellerId,
                'amount' => $disputeAmount,
                'reason' => $reason,
                'description' => $description,
                'evidence_images' => $evidenceJson,
                'status' => 'open'
            ]);

            $this->logDisputeEvent($disputeId, $userId, 'buyer', 'opened', $description);

            $this->db->update(
                'order_items',
                ['item_status' => 'disputed', 'status_updated_at' => date('Y-m-d H:i:s')],
                'id = :id',
                ['id' => $orderItem['id']]
            );

            $this->db->query(
                "UPDATE held_funds
                 SET hold_until = '2099-12-31 23:59:59', status = 'disputed'
                 WHERE seller_id = ?
                   AND ((order_item_id = ?)
                    OR (order_item_id IS NULL AND order_id = ?))
                   AND status IN ('holding', 'disputed')",
                [$sellerId, $orderItem['id'], $orderId]
            );

            $this->notifyNewDispute($disputeId, $order, $orderItem, $sellerId, $reason, $description);

            $this->db->commit();
            return ['success' => true, 'dispute_id' => $disputeId];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function sellerRespond($sellerId, $disputeId, $response, $evidenceImages = []) {
        try {
            $this->db->beginTransaction();

            $dispute = $this->db->fetchOne(
                "SELECT * FROM disputes WHERE id = ? AND seller_id = ?",
                [$disputeId, $sellerId]
            );
            if (!$dispute) {
                throw new Exception("Khieu nai khong ton tai hoac khong thuoc ve ban.");
            }

            if (!in_array($dispute['status'], ['open', 'under_review'], true)) {
                throw new Exception("Khieu nai nay da co ket qua, khong the phan hoi them.");
            }

            $response = trim((string)$response);
            if ($response === '' && empty($evidenceImages)) {
                throw new Exception("Vui long nhap noi dung phan hoi hoac tai len bang chung.");
            }

            $sellerEvidenceJson = empty($evidenceImages) ? null : json_encode(array_values($evidenceImages));
            $now = date('Y-m-d H:i:s');
            $eventType = empty($dispute['seller_responded_at']) ? 'responded' : 'response_updated';

            $this->db->update('disputes', [
                'status' => 'under_review',
                'seller_response' => $response,
                'seller_evidence_images' => $sellerEvidenceJson,
                'seller_responded_at' => $now
            ], 'id = :id', ['id' => $disputeId]);

            $this->logDisputeEvent(
                $disputeId,
                $sellerId,
                'seller',
                $eventType,
                $response !== '' ? $response : 'Seller da cap nhat bang chung.'
            );

            $this->notifySellerResponse($dispute, $response);

            $this->db->commit();
            return ['success' => true, 'message' => 'Da gui phan hoi khiếu nại thanh cong.'];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function sellerRefundBuyer($sellerId, $orderId, $orderItemId, $note = '') {
        try {
            $this->db->beginTransaction();

            $orderItem = $this->db->fetchOne(
                "SELECT oi.*, p.product_type FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.id = ? AND oi.seller_id = ?",
                [$orderItemId, $sellerId]
            );
            if (!$orderItem) {
                throw new Exception("San pham khong thuoc ve ban.");
            }
            if ($orderItem['item_status'] === 'refunded') {
                throw new Exception("San pham nay da duoc hoan tien.");
            }

            $order = $this->db->fetchOne("SELECT * FROM orders WHERE id = ?", [$orderId]);
            $buyerId = $order['user_id'];
            $refundAmountToBuyer = $orderItem['subtotal'];
            $sellerAmountToDeduct = $orderItem['seller_amount'];
            $adminFeeAmount = $orderItem['admin_fee_amount'];
            $systemUserId = Helper::getSystemUserId();

            $sellerRecovered = $this->recoverSellerFunds(
                $sellerId,
                $orderId,
                $orderItemId,
                $sellerAmountToDeduct,
                'refund',
                'order_item',
                $orderItemId,
                "Hoan tien don hang #{$order['order_code']}"
            );
            $sellerShortfall = max(0, $sellerAmountToDeduct - $sellerRecovered);

            $this->walletService->forceDeductMoney(
                $systemUserId,
                $adminFeeAmount + $sellerShortfall,
                'refund',
                'order_item',
                $orderItemId,
                "Hoan lai phi va bu thieu khi hoan tien don #{$order['order_code']}"
            );
            $this->walletService->addMoney($buyerId, $refundAmountToBuyer, 'refund', 'order_item', $orderItemId, "Hoan tien tu don hang #{$order['order_code']}");
            
            // Thông báo hệ thống qua Chat (NPC Admin)
            Helper::sendSystemMessage($buyerId, "💰 <b>Hoàn tiền thành công!</b>\nBạn đã nhận được khoản hoàn trả <b>" . money($refundAmountToBuyer) . "</b> cho đơn hàng <b>#{$order['order_code']}</b>. Tiền đã được cộng vào ví của bạn.");

            $this->db->update('order_items', [
                'item_status' => 'refunded',
                'seller_note' => $note,
                'status_updated_at' => date('Y-m-d H:i:s')
            ], 'id = :id', ['id' => $orderItemId]);

            $reusableTypes = ['file', 'link', 'service'];
            if (in_array($orderItem['product_type'], $reusableTypes, true)) {
                $stockStatus = 'available';
                $orderIdValue = null;
                $soldAtValue = null;
            } else {
                $stockStatus = 'invalidated';
                $orderIdValue = $orderId;
                $soldAtValue = date('Y-m-d H:i:s');
            }

            $this->db->query(
                "UPDATE product_stocks SET status = ?, order_id = ?, sold_at = ? WHERE order_id = ? AND product_id = ?",
                [$stockStatus, $orderIdValue, $soldAtValue, $orderId, $orderItem['product_id']]
            );
            $productModel = new Product();
            $productModel->updateStock($orderItem['product_id']);

            $this->db->query(
                "UPDATE disputes SET status = 'resolved_refund', admin_note = 'Seller tu hoan tien', resolved_at = NOW() WHERE order_item_id = ? AND status IN ('open','under_review')",
                [$orderItemId]
            );

            $this->db->commit();
            return ['success' => true, 'message' => 'Hoan tien thanh cong.'];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function resolveDispute($adminId, $disputeId, $decision, $refundAmount = 0, $penaltyAmount = 0, $adminNote = '') {
        try {
            $this->db->beginTransaction();

            $dispute = $this->db->fetchOne("SELECT * FROM disputes WHERE id = ?", [$disputeId]);
            if (!$dispute || !in_array($dispute['status'], ['open', 'under_review'])) {
                throw new Exception("Khieu nai khong ton tai hoac da duoc xu ly.");
            }

            $orderId = $dispute['order_id'];
            $sellerId = $dispute['seller_id'];
            $buyerId = $dispute['user_id'];
            $order = $this->db->fetchOne("SELECT * FROM orders WHERE id = ?", [$orderId]);
            $systemUserId = Helper::getSystemUserId();

            if ($decision === 'refund') {
                $orderItem = $this->db->fetchOne("SELECT * FROM order_items WHERE id = ?", [$dispute['order_item_id']]);

                if ($orderItem['item_status'] !== 'refunded') {
                    $refundToBuyer = $refundAmount > 0 ? $refundAmount : $orderItem['subtotal'];
                    $ratio = min(1, $refundToBuyer / $orderItem['subtotal']);
                    $sellerDeduct = $orderItem['seller_amount'] * $ratio;
                    $adminFeeDeduct = $orderItem['admin_fee_amount'] * $ratio;

                    $sellerRecovered = $this->recoverSellerFunds(
                $sellerId,
                $orderId,
                $dispute['order_item_id'],
                $sellerDeduct,
                'refund',
                'dispute',
                        $disputeId,
                        "Admin tru tien khieu nai #{$dispute['dispute_code']}"
                    );
                    $sellerShortfall = max(0, $sellerDeduct - $sellerRecovered);

                    $this->walletService->forceDeductMoney(
                        $systemUserId,
                        $adminFeeDeduct + $sellerShortfall,
                        'refund',
                        'dispute',
                        $disputeId,
                        "Hoan phi va bu thieu khieu nai #{$dispute['dispute_code']}"
                    );
                    $this->walletService->addMoney($buyerId, $refundToBuyer, 'refund', 'dispute', $disputeId, "Hoan tien tu khieu nai #{$dispute['dispute_code']}");
                    
                    // Thông báo hệ thống qua Chat (NPC Admin)
                    Helper::sendSystemMessage($buyerId, "⚖️ <b>Kết quả khiếu nại: Hoàn tiền</b>\nAdmin đã phán quyết hoàn trả <b>" . money($refundToBuyer) . "</b> cho đơn hàng <b>#{$order['order_code']}</b> sau khi xem xét khiếu nại của bạn.");

                    if ($penaltyAmount > 0) {
                        $penaltyRecovered = $this->recoverSellerFunds(
                            $sellerId,
                            $orderId,
                            $dispute['order_item_id'],
                            $penaltyAmount,
                            'penalty',
                            'dispute',
                            $disputeId,
                            "Phat vi pham don hang #{$order['order_code']}"
                        );

                        if ($penaltyRecovered > 0) {
                            $this->walletService->addMoney($systemUserId, $penaltyRecovered, 'admin_fee', 'dispute', $disputeId, "Thu tien phat seller {$sellerId}");
                        }
                    }

                    $this->db->update('order_items', ['item_status' => 'refunded', 'status_updated_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $dispute['order_item_id']]);
                }

                $status = ($refundAmount > 0 && $refundAmount < $dispute['amount']) ? 'resolved_partial' : 'resolved_refund';
            } else if ($decision === 'reject') {
                $heldFunds = $this->db->fetchAll(
                    "SELECT * FROM held_funds
                     WHERE seller_id = ?
                       AND ((order_item_id = ?)
                        OR (order_item_id IS NULL AND order_id = ?))
                       AND status = 'disputed'",
                    [$sellerId, $dispute['order_item_id'], $orderId]
                );
                foreach ($heldFunds as $heldFund) {
                    $this->db->query("UPDATE held_funds SET status = 'holding', hold_until = NOW() WHERE id = ?", [$heldFund['id']]);
                    $this->escrowService->releaseFunds($heldFund['id']);
                }

                $this->db->update('order_items', ['item_status' => 'delivered', 'status_updated_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $dispute['order_item_id']]);
                $status = 'resolved_rejected';
                $refundAmount = 0;
                $penaltyAmount = 0;
            } else {
                throw new Exception("Quyet dinh khong hop le.");
            }

            $this->db->update('disputes', [
                'status' => $status,
                'admin_id' => $adminId,
                'admin_note' => $adminNote,
                'refund_amount' => $refundAmount,
                'penalty_amount' => $penaltyAmount,
                'resolved_at' => date('Y-m-d H:i:s')
            ], 'id = :id', ['id' => $disputeId]);

            $this->logDisputeEvent($disputeId, $adminId, 'admin', $status, $adminNote);

            $this->notifyResolvedDispute($dispute, $buyerId, $sellerId, $decision, $adminNote);

            $this->db->commit();
            return ['success' => true, 'message' => 'Da xu ly khieu nai thanh cong.'];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function recoverSellerFunds($sellerId, $orderId, $orderItemId, $targetAmount, $transactionType, $referenceType, $referenceId, $description) {
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
                "UPDATE held_funds SET amount = ?, status = ?, released_at = ? WHERE id = ?",
                [$newAmount, $newStatus, $newAmount > 0 ? null : date('Y-m-d H:i:s'), $heldFund['id']]
            );
            $this->db->insert('transactions', [
                'user_id' => $sellerId,
                'type' => $transactionType,
                'amount' => -$usable,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => "Tru tien dang giu: {$description}"
            ]);

            $remaining -= $usable;
            $recovered += $usable;
        }

        if ($remaining > 0) {
            $balanceRecovered = $this->walletService->deductUpTo(
                $sellerId,
                $remaining,
                $transactionType,
                $referenceType,
                $referenceId,
                $description
            );
            $remaining -= $balanceRecovered;
            $recovered += $balanceRecovered;
        }

        if ($remaining > 0) {
            $depositRecovered = $this->recoverDepositBalance($sellerId, $remaining, $transactionType, $referenceType, $referenceId, $description);
            $remaining -= $depositRecovered;
            $recovered += $depositRecovered;
        }

        return $recovered;
    }

    private function recoverDepositBalance($sellerId, $targetAmount, $transactionType, $referenceType, $referenceId, $description) {
        $wallet = $this->walletService->getWallet($sellerId);
        $available = max(0, (float)($wallet['deposit_balance'] ?? 0));
        $recover = min($available, (float)$targetAmount);

        if ($recover <= 0) {
            return 0;
        }

        $this->db->query(
            "UPDATE wallets SET deposit_balance = GREATEST(0, deposit_balance - ?) WHERE user_id = ?",
            [$recover, $sellerId]
        );
        $this->db->insert('transactions', [
            'user_id' => $sellerId,
            'type' => $transactionType,
            'amount' => -$recover,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => "Tru tien coc: {$description}"
        ]);

        return $recover;
    }

    private function getDisputeProtectionStartAt(array $orderItem, $fallbackDate) {
        $eligibleStatuses = ['delivered', 'disputed', 'issue', 'refunded', 'released'];
        if (in_array($orderItem['item_status'] ?? '', $eligibleStatuses, true) && !empty($orderItem['status_updated_at'])) {
            return $orderItem['status_updated_at'];
        }

        return $fallbackDate;
    }

    private function notifyNewDispute($disputeId, $order, $orderItem, $sellerId, $reason, $description) {
        try {
            $userModel = new User();
            $seller = $userModel->find($sellerId);

            $msg = "⚠️ <b>KHIEU NAI MOI</b>\n";
            $msg .= "Ma KN: #" . Helper::telegramEscape($disputeId) . "\n";
            $msg .= "Don hang: #" . Helper::telegramEscape($order['order_code']) . "\n";
            $msg .= "San pham: " . Helper::telegramEscape($orderItem['product_name'] ?? $orderItem['product_id']) . "\n";
            $msg .= "Ly do: " . Helper::telegramEscape($reason) . "\n";
            $msg .= "Mo ta: " . Helper::telegramEscape($description) . "\n";
            $msg .= "Vui long kiem tra va xu ly.";

            $adminChatId = $_ENV['TELEGRAM_ADMIN_CHAT_ID'] ?? null;
            if (!$adminChatId) {
                $row = $this->db->fetchOne("SELECT value FROM settings WHERE key_name = 'telegram_chat_id'");
                $adminChatId = $row['value'] ?? null;
            }

            if ($adminChatId) {
                Helper::sendTelegramMessage($adminChatId, "⚖️ <b>ADMIN</b> - " . $msg);
            }

            if (!empty($seller['telegram_chat_id'])) {
                Helper::sendTelegramMessage($seller['telegram_chat_id'], "🛒 <b>SELLER</b> - " . $msg);
            }
        } catch (Exception $e) {
            error_log("Dispute Notify Error: " . $e->getMessage());
        }
    }

    private function notifySellerResponse(array $dispute, $response) {
        try {
            $userModel = new User();
            $buyer = $userModel->find($dispute['user_id']);
            $seller = $userModel->find($dispute['seller_id']);

            $preview = trim((string)$response);
            if ($preview === '') {
                $preview = 'Seller da gui them bang chung cho khiếu nại nay.';
            } elseif (mb_strlen($preview) > 180) {
                $preview = mb_substr($preview, 0, 180) . '...';
            }

            Helper::sendSystemMessage(
                (int)$dispute['user_id'],
                "📩 <b>Seller đã phản hồi khiếu nại</b>\nMã khiếu nại <b>#{$dispute['dispute_code']}</b> đã có phản hồi từ người bán. Admin sẽ xem xét cả hai phía trước khi ra quyết định cuối cùng."
            );

            $adminChatId = $_ENV['TELEGRAM_ADMIN_CHAT_ID'] ?? null;
            if (!$adminChatId) {
                $row = $this->db->fetchOne("SELECT value FROM settings WHERE key_name = 'telegram_chat_id'");
                $adminChatId = $row['value'] ?? null;
            }

            $msg = "📩 <b>SELLER PHAN HOI KHIẾU NẠI</b>\n";
            $msg .= "Ma KN: #" . Helper::telegramEscape($dispute['dispute_code']) . "\n";
            $msg .= "Seller: " . Helper::telegramEscape($seller['name'] ?? ('#' . $dispute['seller_id'])) . "\n";
            $msg .= "Noi dung: " . Helper::telegramEscape($preview);

            if ($adminChatId) {
                Helper::sendTelegramMessage($adminChatId, "⚖️ <b>ADMIN</b> - " . $msg);
            }
            if ($buyer && !empty($buyer['telegram_chat_id'])) {
                Helper::sendTelegramMessage($buyer['telegram_chat_id'], "🛒 <b>BUYER</b> - " . $msg);
            }
        } catch (Exception $e) {
            error_log("Dispute seller response notify failed: " . $e->getMessage());
        }
    }

    private function logDisputeEvent($disputeId, $actorId, $actorRole, $eventType, $message = '') {
        try {
            $this->db->insert('dispute_events', [
                'dispute_id' => $disputeId,
                'actor_id' => $actorId,
                'actor_role' => $actorRole,
                'event_type' => $eventType,
                'message' => $message
            ]);
        } catch (Exception $e) {
            error_log("Dispute event log failed: " . $e->getMessage());
        }
    }

    private function notifyResolvedDispute($dispute, $buyerId, $sellerId, $decision, $adminNote) {
        try {
            $userModel = new User();
            $buyer = $userModel->find($buyerId);
            $seller = $userModel->find($sellerId);

            $decisionText = ($decision === 'refund') ? "PHE DUYET HOAN TIEN" : "TU CHOI - SELLER DUNG";
            $msg = "⚖️ <b>KET QUA KHIEU NAI</b>\n";
            $msg .= "Ma KN: #" . Helper::telegramEscape($dispute['dispute_code']) . "\n";
            $msg .= "Phan quyet: <b>" . Helper::telegramEscape($decisionText) . "</b>\n";
            $msg .= "Ghi chu: " . Helper::telegramEscape($adminNote) . "\n";

            if ($buyer && !empty($buyer['telegram_chat_id'])) {
                Helper::sendTelegramMessage($buyer['telegram_chat_id'], $msg);
            }
            if ($seller && !empty($seller['telegram_chat_id'])) {
                Helper::sendTelegramMessage($seller['telegram_chat_id'], $msg);
            }
        } catch (Exception $e) {
            error_log("Dispute Resolve Notify Error: " . $e->getMessage());
        }
    }
}
