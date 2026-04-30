<?php
// app/Models/Category.php

class Category extends Model {
    protected $table = 'categories';
    
    public function getActive() {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY display_order ASC, name ASC"
        );
    }
    
    public function findBySlug($slug) {
        return $this->db->fetchOne("SELECT * FROM {$this->table} WHERE slug = ?", [$slug]);
    }
    
    public function getProductCount($categoryId) {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as total FROM products WHERE category_id = ? AND status IN ('active', 'approved')",
            [$categoryId]
        );
        return $result['total'] ?? 0;
    }
}
