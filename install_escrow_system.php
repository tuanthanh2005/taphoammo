<?php
/**
 * Script cài đặt hệ thống Escrow/Deposit cho Seller
 */

require_once __DIR__ . '/config/database.php';

try {
    $config = require __DIR__ . '/config/database.php';
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "=== CÀI ĐẶT HỆ THỐNG ESCROW/DEPOSIT ===\n\n";

    // Read and execute SQL file
    $sql = file_get_contents(__DIR__ . '/database_escrow_system.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $count = 0;
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $count++;
        } catch (PDOException $e) {
            // Ignore duplicate column/table errors
            if (strpos($e->getMessage(), 'Duplicate') === false && 
                strpos($e->getMessage(), 'already exists') === false) {
                echo "⚠ Warning: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "✅ Đã thực thi {$count} câu lệnh SQL\n\n";
    
    echo "=== CẤU HÌNH HỆ THỐNG ===\n";
    echo "• Phần trăm cọc: 10% giá trị stock\n";
    echo "• Thời gian giữ tiền: 7 ngày\n";
    echo "• Tiền cọc tối thiểu: 50,000 VNĐ\n\n";
    
    echo "✅ CÀI ĐẶT HOÀN TẤT!\n\n";
    echo "Các tính năng mới:\n";
    echo "1. Seller phải đặt cọc khi nhập stock\n";
    echo "2. Tiền từ đơn hàng bị giữ 7 ngày\n";
    echo "3. Admin có thể quản lý tiền cọc\n";
    echo "4. Tự động release tiền sau thời gian hold\n\n";
    
} catch (PDOException $e) {
    echo "\n❌ LỖI: " . $e->getMessage() . "\n";
    exit(1);
}
