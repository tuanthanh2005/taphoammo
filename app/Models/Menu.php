<?php
// app/Models/Menu.php

class Menu extends Model {
    protected $table = 'menus';
    
    public function getTree() {
        // Fetch all active menus, ordered by display_order
        $allMenus = $this->db->fetchAll("SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY display_order ASC");
        
        $tree = [];
        $children = [];
        
        foreach ($allMenus as $menu) {
            if (empty($menu['parent_id'])) {
                $menu['children'] = [];
                $tree[$menu['id']] = $menu;
            } else {
                $children[$menu['parent_id']][] = $menu;
            }
        }
        
        // Attach children to parents
        foreach ($children as $parentId => $childItems) {
            if (isset($tree[$parentId])) {
                $tree[$parentId]['children'] = $childItems;
            }
        }
        
        return array_values($tree);
    }
    
    public function getAllMenus() {
        return $this->db->fetchAll("
            SELECT m1.*, m2.title as parent_name 
            FROM {$this->table} m1 
            LEFT JOIN {$this->table} m2 ON m1.parent_id = m2.id 
            ORDER BY m1.parent_id ASC, m1.display_order ASC
        ");
    }
    
    public function getParents() {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE parent_id IS NULL ORDER BY display_order ASC");
    }
}
