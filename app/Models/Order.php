<?php
// app/Models/Order.php

class Order extends Model {
    protected $table = 'orders';
    
    public function createOrder($userId, $items, $totalAmount) {
        $orderCode = 'ORD' . date('Ymd') . rand(1000, 9999);
        
        $orderId = $this->create([
            'order_code' => $orderCode,
            'user_id' => $userId,
            'total_amount' => $totalAmount,
            'payment_method' => 'wallet',
            'payment_status' => 'pending',
            'order_status' => 'pending'
        ]);
        
        return $orderId;
    }
    
    public function getUserOrders($userId, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT {$perPage} OFFSET {$offset}";
        
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    public function getOrderItems($orderId) {
        $sql = "SELECT oi.*, p.name as product_name, p.thumbnail, u.name as seller_name
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                LEFT JOIN users u ON oi.seller_id = u.id
                WHERE oi.order_id = ?";
        
        return $this->db->fetchAll($sql, [$orderId]);
    }
    
    public function getOrderWithItems($orderId) {
        $order = $this->find($orderId);
        if ($order) {
            $order['items'] = $this->getOrderItems($orderId);
        }
        return $order;
    }
}
