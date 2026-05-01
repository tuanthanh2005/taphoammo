<?php
// app/Models/Favorite.php

class Favorite extends Model {
    protected $table = 'user_favorites';

    public function toggle($userId, $productId) {
        $db = Database::getInstance();
        $exists = $db->fetchOne("SELECT id FROM {$this->table} WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
        
        if ($exists) {
            $db->query("DELETE FROM {$this->table} WHERE id = ?", [$exists['id']]);
            return ['status' => 'removed'];
        } else {
            $this->create([
                'user_id' => $userId,
                'product_id' => $productId
            ]);
            return ['status' => 'added'];
        }
    }

    public function isFavorite($userId, $productId) {
        $db = Database::getInstance();
        return (bool)$db->fetchOne("SELECT id FROM {$this->table} WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
    }

    public function getUserFavorites($userId, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT p.*, c.name as category_name, u.name as seller_name, u.username as seller_username
             FROM {$this->table} f
             JOIN products p ON f.product_id = p.id
             LEFT JOIN categories c ON p.category_id = c.id
             LEFT JOIN users u ON p.seller_id = u.id
             WHERE f.user_id = ? AND p.status IN ('active', 'approved')
             ORDER BY f.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            [$userId]
        );
    }
}
