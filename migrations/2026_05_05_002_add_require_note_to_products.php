<?php
/**
 * Migration: Add require_note and deposit columns to products table
 * Date: 2026-05-05
 */

require_once __DIR__ . '/../app/Core/Database.php';
$config = require __DIR__ . '/../config/database.php';

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $columns = $pdo->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);
    
    // Thêm cột require_note
    if (!in_array('require_note', $columns)) {
        $pdo->exec("ALTER TABLE `products` ADD COLUMN `require_note` TINYINT(1) DEFAULT 0 AFTER `updated_at` ");
        echo "   [OK] Đã thêm cột require_note vào bảng products\n";
    }

    // Thêm các cột về đặt cọc (nếu chưa có)
    if (!in_array('deposit_required', $columns)) {
        $pdo->exec("ALTER TABLE `products` ADD COLUMN `deposit_required` DECIMAL(15,2) DEFAULT 0 AFTER `require_note` ");
        echo "   [OK] Đã thêm cột deposit_required vào bảng products\n";
    }

    if (!in_array('deposit_paid', $columns)) {
        $pdo->exec("ALTER TABLE `products` ADD COLUMN `deposit_paid` DECIMAL(15,2) DEFAULT 0 AFTER `deposit_required` ");
        echo "   [OK] Đã thêm cột deposit_paid vào bảng products\n";
    }

    if (!in_array('deposit_status', $columns)) {
        $pdo->exec("ALTER TABLE `products` ADD COLUMN `deposit_status` ENUM('pending', 'paid', 'released') DEFAULT 'pending' AFTER `deposit_paid` ");
        echo "   [OK] Đã thêm cột deposit_status vào bảng products\n";
    }

} catch (Exception $e) {
    echo "   [ERROR] " . $e->getMessage() . "\n";
    throw $e;
}
