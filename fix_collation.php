<?php
/**
 * Script để sửa lỗi collation trong database
 */

require_once __DIR__ . '/config/database.php';

try {
    $config = require __DIR__ . '/config/database.php';
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "Đang sửa lỗi collation...\n\n";

    // Fix conversations table
    echo "1. Sửa bảng conversations...\n";
    $pdo->exec("ALTER TABLE conversations CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "   ✓ Đã sửa bảng conversations\n\n";

    // Fix messages table
    echo "2. Sửa bảng messages...\n";
    $pdo->exec("ALTER TABLE messages CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "   ✓ Đã sửa bảng messages\n\n";

    // Fix users table
    echo "3. Sửa bảng users...\n";
    $pdo->exec("ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "   ✓ Đã sửa bảng users\n\n";

    // Fix all other tables
    $tables = ['products', 'categories', 'orders', 'order_items', 'reviews', 'wallets', 'transactions', 'withdrawals', 'deposit_requests'];
    
    echo "4. Sửa các bảng khác...\n";
    foreach ($tables as $table) {
        try {
            $pdo->exec("ALTER TABLE {$table} CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "   ✓ Đã sửa bảng {$table}\n";
        } catch (Exception $e) {
            echo "   ⚠ Bỏ qua bảng {$table} (có thể không tồn tại)\n";
        }
    }

    echo "\n✅ ĐÃ SỬA XONG LỖI COLLATION!\n";
    echo "Vui lòng refresh trang và thử lại.\n";
    
} catch (PDOException $e) {
    echo "\n❌ LỖI: " . $e->getMessage() . "\n";
    exit(1);
}
