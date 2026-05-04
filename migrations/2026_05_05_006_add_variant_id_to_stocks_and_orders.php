<?php

return new class {
    public function up($db) {
        // Helper to check column existence
        $checkCol = function($table, $column) use ($db) {
            $cols = $db->fetchAll("SHOW COLUMNS FROM {$table} LIKE '{$column}'");
            return !empty($cols);
        };

        // Add variant_id to product_stocks
        if (!$checkCol('product_stocks', 'variant_id')) {
            $db->query("ALTER TABLE product_stocks ADD COLUMN variant_id INT NULL AFTER product_id");
            echo "Added variant_id to product_stocks.\n";
        }
        
        // Add variant_id and variant_name to order_items
        if (!$checkCol('order_items', 'variant_id')) {
            $db->query("ALTER TABLE order_items ADD COLUMN variant_id INT NULL AFTER product_id");
            echo "Added variant_id to order_items.\n";
        }
        if (!$checkCol('order_items', 'variant_name')) {
            $db->query("ALTER TABLE order_items ADD COLUMN variant_name VARCHAR(255) NULL AFTER variant_id");
            echo "Added variant_name to order_items.\n";
        }
    }

    public function down($db) {
        $db->query("ALTER TABLE product_stocks DROP COLUMN variant_id");
        $db->query("ALTER TABLE order_items DROP COLUMN variant_id");
        $db->query("ALTER TABLE order_items DROP COLUMN variant_name");
    }
};
