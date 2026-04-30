<?php
// app/Models/Product.php

class Product extends Model {
    protected $table = 'products';
    
    public function getAll($filters = [], $page = 1, $perPage = 20) {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['category_id'])) {
            if (is_array($filters['category_id'])) {
                $placeholders = [];
                foreach ($filters['category_id'] as $k => $v) {
                    $key = 'cat_in_' . $k;
                    $placeholders[] = ':' . $key;
                    $params[$key] = $v;
                }
                $where[] = "p.category_id IN (" . implode(',', $placeholders) . ")";
            } else {
                $where[] = 'p.category_id = :category_id';
                $params['category_id'] = $filters['category_id'];
            }
        }
        
        if (!empty($filters['seller_id'])) {
            $where[] = 'p.seller_id = :seller_id';
            $params['seller_id'] = $filters['seller_id'];
        }
        
        if (!empty($filters['status'])) {
            if ($filters['status'] !== 'all') {
                $where[] = 'p.status = :status';
                $params['status'] = $filters['status'];
            }
        } else {
            $where[] = "p.status IN ('active', 'approved')";
        }
        
        if (!empty($filters['search'])) {
            $raw = trim($filters['search']);
            // Build tokens: full keyword + individual words + bigrams from words
            $tokens = [];
            $tokens[] = $raw; // full phrase
            $words = preg_split('/\s+/', $raw);
            foreach ($words as $w) {
                if (strlen($w) >= 2) {
                    $tokens[] = $w;
                    // bigrams: "chatgpt" → "ch","ha","at","tg","gp","pt"
                    for ($i = 0; $i < strlen($w) - 1; $i++) {
                        $tokens[] = substr($w, $i, 2);
                    }
                }
            }
            $tokens = array_unique(array_filter($tokens));

            // Build OR conditions, one per token
            $likeConditions = [];
            foreach ($tokens as $i => $token) {
                $k1 = 'stok_n' . $i;
                $k2 = 'stok_d' . $i;
                $likeConditions[] = "(p.name LIKE :$k1 OR p.description LIKE :$k2)";
                $params[$k1] = '%' . $token . '%';
                $params[$k2] = '%' . $token . '%';
            }
            $where[] = '(' . implode(' OR ', $likeConditions) . ')';
        }

        
        if (!empty($filters['is_featured'])) {
            $where[] = 'p.is_featured = :is_featured';
            $params['is_featured'] = $filters['is_featured'];
        }
        
        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT p.*, u.name as seller_name, c.name as category_name 
                FROM {$this->table} p
                LEFT JOIN users u ON p.seller_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE {$whereClause}
                ORDER BY p.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function findBySlug($slug) {
        $sql = "SELECT p.*, u.name as seller_name, u.username as seller_username, c.name as category_name, c.slug as category_slug 
                FROM {$this->table} p
                LEFT JOIN users u ON p.seller_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.slug = ?";
        return $this->db->fetchOne($sql, [$slug]);
    }
    
    public function getStockCount($productId) {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as total FROM product_stocks WHERE product_id = ? AND status = 'available'",
            [$productId]
        );
        return $result['total'] ?? 0;
    }
    
    public function updateStock($productId) {
        $count = $this->getStockCount($productId);
        $this->update($productId, ['stock_quantity' => $count]);
    }
    
    public function getSponsored($limit = 3) {
        $sponsoredIdsStr = $this->db->fetchOne("SELECT value FROM settings WHERE key_name = 'sponsored_product_ids'")['value'] ?? '';
        $sponsoredProducts = [];
        
        if (!empty(trim($sponsoredIdsStr))) {
            $ids = array_map('intval', explode(',', $sponsoredIdsStr));
            $ids = array_filter($ids);
            if (!empty($ids)) {
                $ids = array_slice($ids, 0, $limit);
                $placeholders = str_repeat('?,', count($ids) - 1) . '?';
                $sql = "SELECT p.*, u.name as seller_name 
                        FROM {$this->table} p 
                        LEFT JOIN users u ON p.seller_id = u.id 
                        WHERE p.id IN ($placeholders) AND p.status IN ('active', 'approved')";
                $fetchedProducts = $this->db->fetchAll($sql, $ids);
                
                // Keep order
                foreach ($ids as $id) {
                    foreach ($fetchedProducts as $p) {
                        if ((int)$p['id'] === (int)$id) {
                            $sponsoredProducts[] = $p;
                            break;
                        }
                    }
                }
            }
        }
        
        // If not enough sponsored, fill with featured
        if (count($sponsoredProducts) < $limit) {
            $needed = $limit - count($sponsoredProducts);
            $excludeIds = array_column($sponsoredProducts, 'id');
            $whereExclude = !empty($excludeIds) ? "AND p.id NOT IN (" . implode(',', array_fill(0, count($excludeIds), '?')) . ")" : "";
            
            $sql = "SELECT p.*, u.name as seller_name 
                    FROM {$this->table} p 
                    LEFT JOIN users u ON p.seller_id = u.id 
                    WHERE p.is_featured = 1 AND p.status IN ('active', 'approved') $whereExclude
                    LIMIT $needed";
            $featured = $this->db->fetchAll($sql, $excludeIds);
            $sponsoredProducts = array_merge($sponsoredProducts, $featured);
        }
        
        return $sponsoredProducts;
    }
}
