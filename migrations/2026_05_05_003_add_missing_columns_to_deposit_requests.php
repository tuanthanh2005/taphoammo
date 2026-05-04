<?php
/**
 * Migration: Add missing columns to deposit_requests table
 * Date: 2026-05-05
 */

require_once __DIR__ . '/../app/Core/Database.php';
$config = require __DIR__ . '/../config/database.php';

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $columns = $pdo->query("SHOW COLUMNS FROM deposit_requests")->fetchAll(PDO::FETCH_COLUMN);
    
    $missingColumns = [
        'note'           => "TEXT DEFAULT NULL AFTER `amount` ",
        'transfer_code'  => "VARCHAR(50) DEFAULT NULL AFTER `note` ",
        'bank_code'      => "VARCHAR(50) DEFAULT NULL AFTER `transfer_code` ",
        'bank_name'      => "VARCHAR(255) DEFAULT NULL AFTER `bank_code` ",
        'account_name'   => "VARCHAR(255) DEFAULT NULL AFTER `bank_name` ",
        'account_number' => "VARCHAR(50) DEFAULT NULL AFTER `account_name` "
    ];

    foreach ($missingColumns as $column => $definition) {
        if (!in_array($column, $columns)) {
            $pdo->exec("ALTER TABLE `deposit_requests` ADD COLUMN `$column` $definition");
            echo "   [OK] Đã thêm cột $column vào bảng deposit_requests\n";
        }
    }

} catch (Exception $e) {
    echo "   [ERROR] " . $e->getMessage() . "\n";
    throw $e;
}
