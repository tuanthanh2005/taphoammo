<?php
require 'config/database.php';
$c = require 'config/database.php';
$p = new PDO("mysql:host={$c['host']};dbname={$c['database']}", $c['username'], $c['password']);

$cols = $p->query("SHOW COLUMNS FROM order_items")->fetchAll(PDO::FETCH_COLUMN);

if (!in_array('item_status', $cols)) {
    $p->exec("ALTER TABLE order_items ADD COLUMN item_status ENUM('processing','delivered','issue','refunded') DEFAULT 'processing' AFTER seller_amount");
    echo "✓ Added item_status column\n";
}
if (!in_array('seller_note', $cols)) {
    $p->exec("ALTER TABLE order_items ADD COLUMN seller_note TEXT DEFAULT NULL AFTER item_status");
    echo "✓ Added seller_note column\n";
}
if (!in_array('status_updated_at', $cols)) {
    $p->exec("ALTER TABLE order_items ADD COLUMN status_updated_at DATETIME DEFAULT NULL AFTER seller_note");
    echo "✓ Added status_updated_at column\n";
}

// Update existing orders to delivered if they have stocks
$p->exec("UPDATE order_items oi 
          INNER JOIN (SELECT DISTINCT order_id FROM product_stocks WHERE status='sold') ps ON ps.order_id = oi.order_id
          SET oi.item_status = 'delivered' WHERE oi.item_status = 'processing'");

echo "✓ Updated existing orders status\n";
echo "Done!\n";
