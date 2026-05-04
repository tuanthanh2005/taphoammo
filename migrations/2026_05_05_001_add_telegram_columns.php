<?php
/**
 * Migration: Add telegram_chat_id to users table
 * Date: 2026-05-05
 */

require_once __DIR__ . '/../app/Core/Database.php';
$config = require __DIR__ . '/../config/database.php';

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $columns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('telegram_chat_id', $columns)) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `telegram_chat_id` VARCHAR(50) DEFAULT NULL AFTER `updated_at` ");
        echo "   [OK] Đã thêm cột telegram_chat_id\n";
    }

    if (!in_array('max_products', $columns)) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `max_products` INT DEFAULT 10 AFTER `telegram_chat_id` ");
        echo "   [OK] Đã thêm cột max_products\n";
    }

} catch (Exception $e) {
    echo "   [ERROR] " . $e->getMessage() . "\n";
    throw $e;
}
