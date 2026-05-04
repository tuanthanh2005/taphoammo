<?php
/**
 * Migration: Create product_variants table
 * Date: 2026-05-05
 */

require_once __DIR__ . '/../app/Core/Database.php';
$config = require __DIR__ . '/../config/database.php';

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE TABLE IF NOT EXISTS `product_variants` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `product_id` INT NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `price` DECIMAL(15,2) NOT NULL,
        `sale_price` DECIMAL(15,2) DEFAULT NULL,
        `stock_quantity` INT DEFAULT 0,
        `warranty_days` INT DEFAULT 0,
        `require_note` TINYINT(1) DEFAULT 0,
        `status` ENUM('active', 'hidden') DEFAULT 'active',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    echo "   [OK] Đã tạo bảng product_variants thành công\n";

} catch (Exception $e) {
    echo "   [ERROR] " . $e->getMessage() . "\n";
    throw $e;
}
