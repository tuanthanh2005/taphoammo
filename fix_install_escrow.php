<?php
/**
 * Script sửa lỗi cài đặt escrow system
 */

require_once __DIR__ . '/config/database.php';

try {
    $config = require __DIR__ . '/config/database.php';
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "=== SỬA LỖI CÀI ĐẶT ESCROW SYSTEM ===\n\n";

    // 1. Thêm cột vào products
    echo "1. Cập nhật bảng products...\n";
    try {
        $pdo->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS deposit_required DECIMAL(15,2) DEFAULT 0");
        $pdo->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS deposit_paid DECIMAL(15,2) DEFAULT 0");
        $pdo->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS deposit_status ENUM('pending', 'paid', 'released') DEFAULT 'pending'");
        echo "   ✓ Đã cập nhật bảng products\n";
    } catch (Exception $e) {
        echo "   ⚠ " . $e->getMessage() . "\n";
    }

    // 2. Tạo bảng seller_deposits
    echo "\n2. Tạo bảng seller_deposits...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS seller_deposits (
          id int(11) NOT NULL AUTO_INCREMENT,
          seller_id int(11) NOT NULL,
          product_id int(11) NOT NULL,
          stock_quantity int(11) NOT NULL,
          product_value decimal(15,2) NOT NULL,
          deposit_amount decimal(15,2) NOT NULL,
          deposit_percentage decimal(5,2) DEFAULT 10.00,
          status enum('pending','paid','released','refunded') DEFAULT 'pending',
          paid_at datetime DEFAULT NULL,
          released_at datetime DEFAULT NULL,
          notes text,
          created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (id),
          KEY seller_id (seller_id),
          KEY product_id (product_id),
          KEY status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "   ✓ Đã tạo bảng seller_deposits\n";

    // 3. Tạo bảng held_funds
    echo "\n3. Tạo bảng held_funds...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS held_funds (
          id int(11) NOT NULL AUTO_INCREMENT,
          order_id int(11) NOT NULL,
          seller_id int(11) NOT NULL,
          amount decimal(15,2) NOT NULL,
          hold_until datetime NOT NULL,
          status enum('holding','released','refunded') DEFAULT 'holding',
          released_at datetime DEFAULT NULL,
          reason varchar(255) DEFAULT NULL,
          created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (id),
          KEY order_id (order_id),
          KEY seller_id (seller_id),
          KEY status (status),
          KEY hold_until (hold_until)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "   ✓ Đã tạo bảng held_funds\n";

    // 4. Thêm cột vào wallets
    echo "\n4. Cập nhật bảng wallets...\n";
    try {
        $pdo->exec("ALTER TABLE wallets ADD COLUMN IF NOT EXISTS held_balance DECIMAL(15,2) DEFAULT 0");
        $pdo->exec("ALTER TABLE wallets ADD COLUMN IF NOT EXISTS deposit_balance DECIMAL(15,2) DEFAULT 0");
        echo "   ✓ Đã cập nhật bảng wallets\n";
    } catch (Exception $e) {
        echo "   ⚠ " . $e->getMessage() . "\n";
    }

    // 5. Thêm cột vào transactions
    echo "\n5. Cập nhật bảng transactions...\n";
    try {
        $pdo->exec("ALTER TABLE transactions ADD COLUMN IF NOT EXISTS transaction_type ENUM('deposit','withdrawal','purchase','sale','refund','deposit_payment','deposit_release','fund_hold','fund_release') DEFAULT 'purchase'");
        $pdo->exec("ALTER TABLE transactions ADD COLUMN IF NOT EXISTS related_id INT(11) DEFAULT NULL");
        echo "   ✓ Đã cập nhật bảng transactions\n";
    } catch (Exception $e) {
        echo "   ⚠ " . $e->getMessage() . "\n";
    }

    // 6. Tạo bảng system_settings (QUAN TRỌNG!)
    echo "\n6. Tạo bảng system_settings...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS system_settings (
          id int(11) NOT NULL AUTO_INCREMENT,
          setting_key varchar(100) NOT NULL,
          setting_value text NOT NULL,
          description text,
          updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (id),
          UNIQUE KEY setting_key (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "   ✓ Đã tạo bảng system_settings\n";

    // 7. Insert cấu hình mặc định
    echo "\n7. Thêm cấu hình mặc định...\n";
    $pdo->exec("
        INSERT INTO system_settings (setting_key, setting_value, description) VALUES
        ('deposit_percentage', '10', 'Phần trăm tiền cọc seller phải trả khi nhập stock (%)'),
        ('hold_days', '7', 'Số ngày giữ tiền sau khi khách mua hàng'),
        ('min_deposit_amount', '50000', 'Số tiền cọc tối thiểu (VNĐ)'),
        ('enable_escrow', '1', 'Bật/tắt hệ thống escrow (1=bật, 0=tắt)')
        ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)
    ");
    echo "   ✓ Đã thêm cấu hình\n";

    echo "\n✅ CÀI ĐẶT HOÀN TẤT!\n\n";
    echo "Cấu hình hiện tại:\n";
    
    $settings = $pdo->query("SELECT * FROM system_settings")->fetchAll();
    foreach ($settings as $setting) {
        echo "  • {$setting['setting_key']}: {$setting['setting_value']}\n";
    }
    
    echo "\nHệ thống escrow đã sẵn sàng!\n";
    
} catch (PDOException $e) {
    echo "\n❌ LỖI: " . $e->getMessage() . "\n";
    exit(1);
}
