<?php

$c = require 'config/database.php';
$pdo = new PDO(
    "mysql:host={$c['host']};dbname={$c['database']};charset={$c['charset']}",
    $c['username'],
    $c['password'],
    $c['options'] ?? []
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function columnExists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = ?
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
        LIMIT 1
    ");
    $stmt->execute([$GLOBALS['c']['database'], $table, $column]);
    return (bool) $stmt->fetchColumn();
}

echo "Applying business rules update...\n";

if (!columnExists($pdo, 'products', 'warranty_days')) {
    $pdo->exec("ALTER TABLE products ADD COLUMN warranty_days INT(11) NOT NULL DEFAULT 0 AFTER product_type");
    echo "Added products.warranty_days\n";
}

if (!columnExists($pdo, 'products', 'warranty_note')) {
    $pdo->exec("ALTER TABLE products ADD COLUMN warranty_note TEXT DEFAULT NULL AFTER warranty_days");
    echo "Added products.warranty_note\n";
}

$pdo->exec("ALTER TABLE product_stocks MODIFY COLUMN status ENUM('available','sold','refunded','invalidated') NOT NULL DEFAULT 'available'");
if (!columnExists($pdo, 'order_items', 'item_status')) {
    $pdo->exec("ALTER TABLE order_items ADD COLUMN item_status ENUM('processing','delivered','disputed','issue','refunded','released') NOT NULL DEFAULT 'processing' AFTER seller_amount");
} else {
    $pdo->exec("ALTER TABLE order_items MODIFY COLUMN item_status ENUM('processing','delivered','disputed','issue','refunded','released') NOT NULL DEFAULT 'processing'");
}
if (!columnExists($pdo, 'order_items', 'seller_note')) {
    $pdo->exec("ALTER TABLE order_items ADD COLUMN seller_note TEXT DEFAULT NULL AFTER item_status");
}
if (!columnExists($pdo, 'order_items', 'note')) {
    $pdo->exec("ALTER TABLE order_items ADD COLUMN note TEXT DEFAULT NULL AFTER seller_note");
}
if (!columnExists($pdo, 'order_items', 'status_updated_at')) {
    $pdo->exec("ALTER TABLE order_items ADD COLUMN status_updated_at DATETIME DEFAULT NULL AFTER note");
}
$pdo->exec("ALTER TABLE held_funds MODIFY COLUMN status ENUM('holding','disputed','released','refunded') DEFAULT 'holding'");
$pdo->exec("ALTER TABLE transactions MODIFY COLUMN type VARCHAR(50) NOT NULL DEFAULT 'deposit'");
$pdo->exec("ALTER TABLE users MODIFY COLUMN status ENUM('active','banned','pending','suspended') NOT NULL DEFAULT 'active'");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS dispute_events (
        id INT(11) NOT NULL AUTO_INCREMENT,
        dispute_id INT(11) NOT NULL,
        actor_id INT(11) DEFAULT NULL,
        actor_role VARCHAR(50) DEFAULT NULL,
        event_type VARCHAR(50) NOT NULL,
        message TEXT DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY dispute_id (dispute_id),
        KEY created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$settings = [
    ['deposit_percentage', '30', 'Default seller stock deposit percentage'],
    ['dispute_seller_response_hours', '24', 'Seller response SLA for active disputes'],
    ['dispute_admin_resolution_hours', '48', 'Admin resolution SLA after seller response window'],
];

$stmt = $pdo->prepare("
    INSERT INTO system_settings (setting_key, setting_value, description)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), description = VALUES(description)
");

foreach ($settings as $setting) {
    $stmt->execute($setting);
}

echo "Business rules update applied.\n";
