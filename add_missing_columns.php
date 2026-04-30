<?php
require_once 'config/database.php';
$config = require 'config/database.php';
$pdo = new PDO("mysql:host={$config['host']};dbname={$config['database']}", $config['username'], $config['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Checking and adding missing columns...\n\n";

// Check products table
$columns = $pdo->query('SHOW COLUMNS FROM products')->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('deposit_required', $columns)) {
    $pdo->exec('ALTER TABLE products ADD COLUMN deposit_required DECIMAL(15,2) DEFAULT 0');
    echo "✓ Added deposit_required to products\n";
}
if (!in_array('deposit_paid', $columns)) {
    $pdo->exec('ALTER TABLE products ADD COLUMN deposit_paid DECIMAL(15,2) DEFAULT 0');
    echo "✓ Added deposit_paid to products\n";
}
if (!in_array('deposit_status', $columns)) {
    $pdo->exec("ALTER TABLE products ADD COLUMN deposit_status ENUM('pending', 'paid', 'released') DEFAULT 'pending'");
    echo "✓ Added deposit_status to products\n";
}

// Check wallets table
$columns = $pdo->query('SHOW COLUMNS FROM wallets')->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('held_balance', $columns)) {
    $pdo->exec('ALTER TABLE wallets ADD COLUMN held_balance DECIMAL(15,2) DEFAULT 0');
    echo "✓ Added held_balance to wallets\n";
}
if (!in_array('deposit_balance', $columns)) {
    $pdo->exec('ALTER TABLE wallets ADD COLUMN deposit_balance DECIMAL(15,2) DEFAULT 0');
    echo "✓ Added deposit_balance to wallets\n";
}

// Check transactions table
$columns = $pdo->query('SHOW COLUMNS FROM transactions')->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('transaction_type', $columns)) {
    $pdo->exec('ALTER TABLE transactions ADD COLUMN transaction_type ENUM("deposit","withdrawal","purchase","sale","refund","deposit_payment","deposit_release","fund_hold","fund_release") DEFAULT "purchase"');
    echo "✓ Added transaction_type to transactions\n";
}
if (!in_array('related_id', $columns)) {
    $pdo->exec('ALTER TABLE transactions ADD COLUMN related_id INT(11) DEFAULT NULL');
    echo "✓ Added related_id to transactions\n";
}

echo "\n✅ All columns added successfully!\n";
