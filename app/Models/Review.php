<?php
// app/Models/Review.php

class Review extends Model {
    protected $table = 'reviews';

    public function getByProduct($productId) {
        return $this->db->fetchAll(
            "SELECT r.*, u.name as user_name 
             FROM {$this->table} r
             JOIN users u ON r.user_id = u.id
             WHERE r.product_id = ?
             ORDER BY r.created_at DESC",
            [$productId]
        );
    }

    public function getByOrder($orderId) {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE order_id = ?",
            [$orderId]
        );
    }

    public function createReview($data) {
        return $this->create($data);
    }

    public function getAverageRating($productId) {
        $result = $this->db->fetchOne(
            "SELECT AVG(rating) as avg_rating, COUNT(*) as count 
             FROM {$this->table} 
             WHERE product_id = ?",
            [$productId]
        );
        return $result;
    }
}
