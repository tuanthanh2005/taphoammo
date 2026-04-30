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
        // Bắt tất cả output để tránh HTML lẫn vào JSON
        ob_start();
        
        try {
            $this->db->beginTransaction();
            
            // Calculate total
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
            }
            
            // Check wallet balance
            $wallet = $this->walletService->getWallet($userId);
            if ($wallet['balance'] < $totalAmount) {
                throw new Exception('Số dư ví không đủ');
            }
            
            // Create order
            $orderModel = new Order();
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
            
            // Process each item
            foreach ($cartItems as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $adminFeeAmount = $subtotal * ($adminFeePercent / 100);
                $sellerAmount = $subtotal - $adminFeeAmount;
                
                // Create order item
                $this->db->insert('order_items', [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'seller_id' => $item['seller_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $subtotal,
                    'admin_fee_percent' => $adminFeePercent,
                    'admin_fee_amount' => $adminFeeAmount,
                    'seller_amount' => $sellerAmount,
                    'note' => $item['note'] ?? null
                ]);
                
                // Get stock items
                $stocks = $this->db->fetchAll(
                    "SELECT * FROM product_stocks WHERE product_id = ? AND status = 'available' LIMIT ?",
                    [$item['product_id'], $item['quantity']]
                );
                
                if (count($stocks) < $item['quantity']) {
                    throw new Exception('Sản phẩm ' . $item['name'] . ' không đủ hàng');
                }
                
                // Mark stocks as sold
                foreach ($stocks as $stock) {
                    $this->db->update('product_stocks', [
                        'status' => 'sold',
                        'order_id' => $orderId,
                        'sold_at' => date('Y-m-d H:i:s')
                    ], 'id = :id', ['id' => $stock['id']]);
                }
                
                // Update product stock quantity
                $productModel = new Product();
                $productModel->updateStock($item['product_id']);
                
                // Update product total_sold
                $this->db->query(
                    "UPDATE products SET total_sold = total_sold + ? WHERE id = ?",
                    [$item['quantity'], $item['product_id']]
                );
                
                // Add money to seller wallet (with hold if escrow enabled)
                $escrowService = new EscrowService();
                $escrowEnabled = $escrowService->isEscrowEnabled();
                
                if ($escrowEnabled) {
                    // Hold funds, đồng thời hoàn tiền cọc tương ứng
                    $holdResult = $escrowService->holdFundsFromOrder(
                        $orderId,
                        $item['seller_id'],
                        $sellerAmount,
                        $item['product_id'],
                        $item['quantity']
                    );
                    
                    if (!$holdResult['success']) {
                        throw new Exception('Lỗi khi giữ tiền: ' . $holdResult['message']);
                    }
                } else {
                    $this->walletService->addMoney($item['seller_id'], $sellerAmount, 'sale_income', 'order', $orderId, 
                        'Doanh thu từ đơn hàng #' . $orderCode);
                }
                
            // Add admin fee to admin wallet (user_id = 1)
                $this->walletService->addMoney(1, $adminFeeAmount, 'admin_fee', 'order', $orderId, 
                    'Phí quản lý từ đơn hàng #' . $orderCode);
            }
            
            // Deduct money from buyer wallet
            $this->walletService->deductMoney($userId, $totalAmount, 'purchase', 'order', $orderId, 
                'Thanh toán đơn hàng #' . $orderCode);
            
            $this->db->commit();
            
            // Send Telegram notifications to sellers
            try {
                $userModel = new User();
                foreach ($cartItems as $item) {
                    $seller = $userModel->find($item['seller_id']);
                    if (!empty($seller['telegram_chat_id'])) {
                        $subtotal = $item['price'] * $item['quantity'];
                        $msg = "🛒 <b>ĐƠN HÀNG MỚI</b>\n";
                        $msg .= "Mã đơn: {$orderCode}\n";
                        $msg .= "Sản phẩm: {$item['name']}\n";
                        $msg .= "Số lượng: {$item['quantity']}\n";
                        $msg .= "Tổng tiền: " . money($subtotal) . "\n";
                        if (!empty($item['note'])) {
                            $msg .= "Ghi chú của khách: {$item['note']}\n";
                        }
                        $msg .= "Vui lòng kiểm tra trên hệ thống.";
                        Helper::sendTelegramMessage($seller['telegram_chat_id'], $msg);
                    }
                }
            } catch (Exception $e) {
                error_log("Telegram Notify Error: " . $e->getMessage());
            }
            
            ob_end_clean();
            return ['success' => true, 'order_id' => $orderId, 'order_code' => $orderCode];
            
        } catch (Exception $e) {
            $this->db->rollback();
            $output = ob_get_clean();
            // Log PHP warnings/errors nếu có
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
        
        // Get order items with stock content
        $items = $this->db->fetchAll(
            "SELECT oi.*, p.name as product_name, p.thumbnail, p.product_type,
                    u.name as seller_name, u.username as seller_username,
                    oi.item_status, oi.seller_note, oi.status_updated_at
             FROM order_items oi
             LEFT JOIN products p ON oi.product_id = p.id
             LEFT JOIN users u ON oi.seller_id = u.id
             WHERE oi.order_id = ?",
            [$orderId]
        );
        
        // Get stock content for each item
        foreach ($items as &$item) {
            $stocks = $this->db->fetchAll(
                "SELECT content FROM product_stocks WHERE order_id = ? AND product_id = ?",
                [$orderId, $item['product_id']]
            );
            $item['stocks'] = $stocks;
        }
        
        $order['items'] = $items;
        
        return $order;
    }
}
