<?php
// app/Models/Product.php

class Product extends Model {
    protected $table = 'products';
    
    public function getAll($filters = [], $page = 1, $perPage = 20) {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['category_id'])) {
            if (is_array($filters['category_id'])) {
                $ids = array_values(array_filter($filters['category_id'], static fn($v) => $v !== '' && $v !== null));
                if (!empty($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $where[] = "p.category_id IN ({$placeholders})";
                    foreach ($ids as $id) {
                        $params[] = $id;
                    }
                }
            } else {
                $where[] = 'p.category_id = ?';
                $params[] = $filters['category_id'];
            }
        }

        if (!empty($filters['seller_id'])) {
            $where[] = 'p.seller_id = ?';
            $params[] = $filters['seller_id'];
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] !== 'all') {
                $where[] = 'p.status = ?';
                $params[] = $filters['status'];
            } else {
                $where[] = "p.status != 'deleted'";
            }
        } else {
            $where[] = "p.status IN ('active', 'approved')";
        }

        if (!empty($filters['search'])) {
            $raw = trim($filters['search']);
            $whereSearch = [];

            if (is_numeric($raw)) {
                $whereSearch[] = "p.id = ?";
                $params[] = (int)$raw;
            }

            $whereSearch[] = "p.name LIKE ?";
            $params[] = '%' . $raw . '%';

            $whereSearch[] = "p.description LIKE ?";
            $params[] = '%' . $raw . '%';

            $whereSearch[] = "u.name LIKE ?";
            $params[] = '%' . $raw . '%';

            $whereSearch[] = "u.username LIKE ?";
            $params[] = '%' . $raw . '%';

            $compact = preg_replace('/\s+/u', '', mb_strtolower($raw, 'UTF-8'));
            if (!empty($compact)) {
                $whereSearch[] = "REPLACE(LOWER(p.name), ' ', '') LIKE ?";
                $params[] = '%' . $compact . '%';

                $whereSearch[] = "REPLACE(LOWER(p.description), ' ', '') LIKE ?";
                $params[] = '%' . $compact . '%';

                $whereSearch[] = "REPLACE(LOWER(u.name), ' ', '') LIKE ?";
                $params[] = '%' . $compact . '%';

                $whereSearch[] = "REPLACE(LOWER(u.username), ' ', '') LIKE ?";
                $params[] = '%' . $compact . '%';
            }

            $tokens = preg_split('/\s+/u', mb_strtolower($raw, 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY);
            $tokens = array_values(array_unique($tokens));

            if (!empty($tokens)) {
                $tokenGroups = [];
                foreach ($tokens as $token) {
                    if ($token === '') {
                        continue;
                    }
                    $tokenGroups[] = "(LOWER(p.name) LIKE ? OR LOWER(p.description) LIKE ? OR LOWER(u.name) LIKE ? OR LOWER(u.username) LIKE ?)";
                    $params[] = '%' . $token . '%';
                    $params[] = '%' . $token . '%';
                    $params[] = '%' . $token . '%';
                    $params[] = '%' . $token . '%';
                }

                if (!empty($tokenGroups)) {
                    $whereSearch[] = '(' . implode(' OR ', $tokenGroups) . ')';
                }
            }

            $where[] = '(' . implode(' OR ', $whereSearch) . ')';
        }

        if (!empty($filters['is_featured'])) {
            $where[] = 'p.is_featured = ?';
            $params[] = $filters['is_featured'];
        }

        if (!empty($filters['on_sale'])) {
            $where[] = 'p.sale_price > 0 AND p.sale_price < p.price';
        }

        $orderBy = 'p.created_at DESC';
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'hot':
                    $orderBy = 'p.total_sold DESC, p.created_at DESC';
                    break;
                case 'top_seller':
                    $where[] = "u.role = 'seller' AND u.status = 'active'";
                    $orderBy = 'u.created_at ASC, p.total_sold DESC'; // Simplified top seller logic
                    break;
                case 'price_asc':
                    $orderBy = 'p.price ASC';
                    break;
                case 'price_desc':
                    $orderBy = 'p.price DESC';
                    break;
            }
        }

        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT p.*, u.name as seller_name, u.username as seller_username, u.status as seller_status, c.name as category_name 
                FROM {$this->table} p
                LEFT JOIN users u ON p.seller_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE {$whereClause}
                ORDER BY {$orderBy}
                LIMIT {$perPage} OFFSET {$offset}";

        $products = $this->db->fetchAll($sql, $params);

        if (empty($products)) {
            return $products;
        }

        $maxSold = 0;
        foreach ($products as $p) {
            $sold = (int)($p['total_sold'] ?? 0);
            if ($sold > $maxSold) {
                $maxSold = $sold;
            }
        }

        $firstPostedId = null;
        foreach ($products as $p) {
            $pid = (int)($p['id'] ?? 0);
            if ($firstPostedId === null || ($pid > 0 && $pid < $firstPostedId)) {
                $firstPostedId = $pid;
            }
        }

        $sponsoredIdsStr = $this->db->fetchOne("SELECT value FROM settings WHERE key_name = 'sponsored_product_ids'")['value'] ?? '';
        $goldenIds = [];
        if (!empty(trim($sponsoredIdsStr))) {
            $goldenIds = array_map('intval', explode(',', $sponsoredIdsStr));
            $goldenIds = array_filter($goldenIds);
        }

        foreach ($products as &$p) {
            $pid = (int)($p['id'] ?? 0);
            $sold = (int)($p['total_sold'] ?? 0);

            $isMostSold = ($maxSold > 0 && $sold === $maxSold);
            $isGoldenPosition = in_array($pid, $goldenIds, true);
            $isFirstPosted = ($firstPostedId !== null && $pid === $firstPostedId);

            $p['show_crown'] = ($isMostSold || $isGoldenPosition || $isFirstPosted) ? 1 : 0;
            $p['is_golden_position'] = $isGoldenPosition ? 1 : 0;
            $p['is_most_sold'] = $isMostSold ? 1 : 0;
            $p['is_first_posted'] = $isFirstPosted ? 1 : 0;
        }
        unset($p);

        return $products;
    }
    
    public function findBySlug($slug) {
        $sql = "SELECT p.*, 
                    u.name as seller_name, 
                    u.username as seller_username, 
                    u.status as seller_status,
                    c.name as category_name, 
                    c.slug as category_slug 
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

        // Sync variants stock
        $variants = $this->db->fetchAll("SELECT id FROM product_variants WHERE product_id = ?", [$productId]);
        foreach ($variants as $v) {
            $vCount = (int)$this->db->fetchOne(
                "SELECT COUNT(*) as total FROM product_stocks WHERE variant_id = ? AND status = 'available'",
                [$v['id']]
            )['total'];
            $this->db->query("UPDATE product_variants SET stock_quantity = ? WHERE id = ?", [$vCount, $v['id']]);
        }
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
