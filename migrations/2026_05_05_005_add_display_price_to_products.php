<?php
// migrations/2026_05_05_005_add_display_price_to_products.php

return new class {
    public function up($db) {
        $check = $db->fetchAll("SHOW COLUMNS FROM products LIKE 'display_price'");
        if (empty($check)) {
            $db->query("ALTER TABLE products ADD COLUMN display_price VARCHAR(255) NULL AFTER sale_price");
        }
    }

    public function down($db) {
        $db->query("ALTER TABLE products DROP COLUMN display_price");
    }
};
