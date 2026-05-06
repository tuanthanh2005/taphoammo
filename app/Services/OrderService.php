<?php
// app/Services/OrderService.php

class OrderService {
    private $db;
    private $walletService;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->walletService = new WalletService();
    }

    public function createOrder($userId, $cartItems) {
        ob_start();

        try {
            $this->db->beginTransaction();

            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
            }

            $wallet = $this->walletService->getWallet($userId);
            if ($wallet['balance'] < $totalAmount) {
                throw new Exception('Số dư ví không đủ');
            }

            $orderCode = 'ORD' . date('Ymd') . rand(1000, 9999);
            $orderId = $this->db->insert('orders', [
                'order_code' => $orderCode,
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'payment_method' => 'wallet',
                'payment_status' => 'paid',
                'order_status' => 'processing'
            ]);

            $config = require __DIR__ . '/../../config/payment.php';
            $adminFeePercent = $config['admin_order_fee_percent'];
            $systemUserId = Helper::getSystemUserId();

            $isManualOrder = false;
            foreach ($cartItems as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $adminFeeAmount = $subtotal * ($adminFeePercent / 100);
                $sellerAmount = $subtotal - $adminFeeAmount;

                $variantId = $item['variant_id'] ?? null;
                $stocks = $this->db->fetchAll(
                    "SELECT * FROM product_stocks WHERE product_id = ? AND (variant_id = ? OR (? IS NULL AND variant_id IS NULL)) AND status = 'available' LIMIT ?",
                    [$item['product_id'], $variantId, $variantId, $item['quantity']]
                );

                $deliveredStocks = [];
                
                if (count($stocks) < $item['quantity']) {
                    // Check for static_content fallback
                    if ($variantId) {
                        $variant = $this->db->fetchOne("SELECT static_content FROM product_variants WHERE id = ?", [$variantId]);
                        $staticContent = $variant['static_content'] ?? null;
                    } else {
                        $productData = $this->db->fetchOne("SELECT static_content FROM products WHERE id = ?", [$item['product_id']]);
                        $staticContent = $productData['static_content'] ?? null;
                    }

                    if (!empty($staticContent)) {
                        // Use static content to fill the gap or replace entirely
                        $needed = (int)$item['quantity'];
                        // First use what we have in stock
                        foreach ($stocks as $s) {
                            $deliveredStocks[] = [
                                'id' => $s['id'],
                                'content' => $s['content'],
                                'is_new' => false
                            ];
                        }
                        
                        // Then fill the rest with static content
                        $remaining = $needed - count($stocks);
                        for ($i = 0; $i < $remaining; $i++) {
                            $newStockId = $this->db->insert('product_stocks', [
                                'product_id' => $item['product_id'],
                                'variant_id' => $variantId,
                                'seller_id'  => $item['seller_id'],
                                'content'    => $staticContent,
                                'status'     => 'sold',
                                'order_id'   => $orderId,
                                'sold_at'    => date('Y-m-d H:i:s')
                            ]);
                            $deliveredStocks[] = [
                                'id' => $newStockId,
                                'content' => $staticContent,
                                'is_new' => true
                            ];
                        }
                    } else {
                        throw new Exception('Sản phẩm ' . $item['name'] . ' không đủ hàng');
                    }
                } else {
                    foreach ($stocks as $s) {
                        $deliveredStocks[] = [
                            'id' => $s['id'],
                            'content' => $s['content'],
                            'is_new' => false
                        ];
                    }
                }

                $hasManualStock = false;
                foreach ($deliveredStocks as $ds) {
                    if ($ds['is_new']) continue; // Already marked as sold during creation

                    $updateData = [
                        'status' => 'sold',
                        'order_id' => $orderId,
                        'sold_at' => date('Y-m-d H:i:s')
                    ];

                    $parsedStock = Helper::parseStockContent($ds['content']);
                    if (($parsedStock['type'] ?? 'text') === 'manual_delivery') {
                        $hasManualStock = true;
                        $isManualOrder = true;
                        $updateData['content'] = Helper::encodeStockContent('manual_delivery', [
                            'order_code' => $orderCode,
                            'message' => 'San pham ban giao thu cong. Vui long lien he Nguoi ban qua muc Chat.'
                        ]);
                    }

                    $this->db->update('product_stocks', $updateData, 'id = :id', ['id' => $ds['id']]);
                }

                $orderItemId = $this->db->insert('order_items', [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'variant_name' => $item['variant_name'] ?? null,
                    'seller_id' => $item['seller_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $subtotal,
                    'admin_fee_percent' => $adminFeePercent,
                    'admin_fee_amount' => $adminFeeAmount,
                    'seller_amount' => $sellerAmount,
                    'item_status' => $hasManualStock ? 'processing' : 'delivered',
                    'status_updated_at' => date('Y-m-d H:i:s'),
                    'note' => $item['note'] ?? null
                ]);

                $productModel = new Product();
                $productModel->updateStock($item['product_id']);

                $this->db->query(
                    "UPDATE products SET total_sold = total_sold + ? WHERE id = ?",
                    [$item['quantity'], $item['product_id']]
                );

                $escrowService = new EscrowService();
                if ($escrowService->isEscrowEnabled()) {
                    $holdResult = $escrowService->holdFundsFromOrder(
                        $orderId,
                        $orderItemId,
                        $item['seller_id'],
                        $sellerAmount,
                        $item['product_id'],
                        $item['quantity']
                    );

                    if (!$holdResult['success']) {
                        throw new Exception('Lỗi khi giữ tiền: ' . $holdResult['message']);
                    }
                } else {
                    $this->walletService->addMoney(
                        $item['seller_id'],
                        $sellerAmount,
                        'sale_income',
                        'order',
                        $orderId,
                        'Doanh thu từ đơn hàng #' . $orderCode
                    );
                }

                $this->walletService->addMoney(
                    $systemUserId,
                    $adminFeeAmount,
                    'admin_fee',
                    'order',
                    $orderId,
                    'Phí quản lý từ đơn hàng #' . $orderCode
                );
            }

            $this->walletService->deductMoney(
                $userId,
                $totalAmount,
                'purchase',
                'order',
                $orderId,
                'Thanh toán đơn hàng #' . $orderCode
            );

            if ($isManualOrder) {
                $this->db->update('orders', ['order_status' => 'processing'], 'id = :id', ['id' => $orderId]);
                Helper::sendSystemMessage($userId, "✅ <b>Đặt hàng thành công!</b>\nĐơn hàng <b>#{$orderCode}</b> đang được người bán xử lý và bàn giao thủ công. Vui lòng liên hệ người bán qua mục 'Chat' để nhận hàng.");
            } else {
                $this->db->update('orders', ['order_status' => 'completed'], 'id = :id', ['id' => $orderId]);
                Helper::sendSystemMessage($userId, "✅ <b>Đặt hàng thành công!</b>\nĐơn hàng <b>#{$orderCode}</b> đã hoàn tất. Bạn có thể xem nội dung sản phẩm ngay tại mục 'Đơn hàng đã mua'.");
            }
            $this->db->commit();

            try {
                $userModel = new User();
                foreach ($cartItems as $item) {
                    $seller = $userModel->find($item['seller_id']);
                    $buyer = $userModel->find($userId);
                    
                    $subtotal = $item['price'] * $item['quantity'];
                    $chatMsg = "🛒 <b>ĐƠN HÀNG MỚI</b>\n";
                    $chatMsg .= "Mã đơn: #{$orderCode}\n";
                    $chatMsg .= "Khách hàng: " . ($buyer['name'] ?? 'Khách') . "\n";
                    $chatMsg .= "Sản phẩm: {$item['name']}\n";
                    if (!empty($item['variant_name'])) {
                        $chatMsg .= "Gói: {$item['variant_name']}\n";
                    }
                    $chatMsg .= "Số lượng: {$item['quantity']}\n";
                    $chatMsg .= "Tổng tiền: " . money($subtotal) . "\n";
                    if (!empty($item['note'])) {
                        $chatMsg .= "Ghi chú: " . $item['note'] . "\n";
                    }
                    $chatMsg .= "\n<i>Người bán vui lòng kiểm tra và bàn giao hàng sớm nhất có thể.</i>";
                    
                    // Gửi tin nhắn vào Chat hệ thống
                    Helper::sendChatMessage($userId, $item['seller_id'], $chatMsg);

                    // Gửi Telegram nếu có
                    if (!empty($seller['telegram_chat_id'])) {
                        $tgMsg = "🛒 <b>ĐƠN HÀNG MỚI</b>\n";
                        $tgMsg .= "Mã đơn: " . Helper::telegramEscape($orderCode) . "\n";
                        $tgMsg .= "Sản phẩm: " . Helper::telegramEscape($item['name']) . "\n";
                        if (!empty($item['variant_name'])) {
                            $tgMsg .= "Gói: " . Helper::telegramEscape($item['variant_name']) . "\n";
                        }
                        $tgMsg .= "Số lượng: " . Helper::telegramEscape($item['quantity']) . "\n";
                        $tgMsg .= "Tổng tiền: " . money($subtotal) . "\n";
                        if (!empty($item['note'])) {
                            $tgMsg .= "Ghi chú của khách: " . Helper::telegramEscape($item['note']) . "\n";
                        }
                        $tgMsg .= "Vui lòng kiểm tra trên hệ thống.";
                        Helper::sendTelegramMessage($seller['telegram_chat_id'], $tgMsg);
                    }
                }
            } catch (Exception $e) {
                error_log("Order Notification Error: " . $e->getMessage());
            }

            ob_end_clean();
            return ['success' => true, 'order_id' => $orderId, 'order_code' => $orderCode];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            $output = ob_get_clean();
            if (!empty($output)) {
                error_log('OrderService output: ' . $output);
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getOrderDetails($orderId, $userId) {
        $order = $this->db->fetchOne(
            "SELECT * FROM orders WHERE id = ? AND user_id = ?",
            [$orderId, $userId]
        );

        if (!$order) {
            return null;
        }

        $items = $this->db->fetchAll(
            "SELECT oi.*, p.name as product_name, p.thumbnail, p.product_type,
                    COALESCE(p.warranty_days, 0) as warranty_days, p.warranty_note,
                    u.name as seller_name, u.username as seller_username,
                    oi.item_status, oi.seller_note, oi.status_updated_at
             FROM order_items oi
             LEFT JOIN products p ON oi.product_id = p.id
             LEFT JOIN users u ON oi.seller_id = u.id
             WHERE oi.order_id = ?",
            [$orderId]
        );

        $stocks = $this->db->fetchAll(
            "SELECT product_id, content
             FROM product_stocks
             WHERE order_id = ?",
            [$orderId]
        );
        $stocksByProduct = [];
        foreach ($stocks as $stock) {
            $stocksByProduct[$stock['product_id']][] = $stock;
        }

        foreach ($items as &$item) {
            $item['stocks'] = $stocksByProduct[$item['product_id']] ?? [];
        }

        $order['items'] = $items;
        return $order;
    }
}
