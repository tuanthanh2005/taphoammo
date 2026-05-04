<?php
/**
 * Script để cập nhật schema cho hệ thống chat
 * Chạy file này một lần để cập nhật database
 */

if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}

require_once __DIR__ . '/config/database.php';

try {
    $config = require __DIR__ . '/config/database.php';
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "Đang kết nối database...\n";

    // Check if tables exist
    $tables = $pdo->query("SHOW TABLES LIKE 'conversations'")->fetchAll();
    
    if (empty($tables)) {
        echo "Tạo bảng conversations và messages mới...\n";
        
        // Create conversations table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `conversations` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `buyer_id` int(11) NOT NULL COMMENT 'Người mua',
              `seller_id` int(11) NOT NULL COMMENT 'Người bán',
              `product_id` int(11) DEFAULT NULL COMMENT 'Sản phẩm liên quan',
              `last_message` text,
              `last_message_at` datetime DEFAULT NULL,
              `unread_count_buyer` int(11) DEFAULT 0 COMMENT 'Số tin chưa đọc của buyer',
              `unread_count_seller` int(11) DEFAULT 0 COMMENT 'Số tin chưa đọc của seller',
              `status` enum('active','closed') DEFAULT 'active',
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `buyer_id` (`buyer_id`),
              KEY `seller_id` (`seller_id`),
              KEY `product_id` (`product_id`),
              KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Create messages table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `messages` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `conversation_id` int(11) NOT NULL,
              `sender_id` int(11) NOT NULL COMMENT 'Người gửi',
              `message` text NOT NULL,
              `attachment` varchar(255) DEFAULT NULL,
              `is_read` tinyint(1) DEFAULT 0,
              `read_at` datetime DEFAULT NULL,
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `conversation_id` (`conversation_id`),
              KEY `sender_id` (`sender_id`),
              KEY `is_read` (`is_read`),
              KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Add foreign keys
        $pdo->exec("
            ALTER TABLE `conversations`
              ADD CONSTRAINT `fk_conv_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
              ADD CONSTRAINT `fk_conv_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ");
        
        $pdo->exec("
            ALTER TABLE `messages`
              ADD CONSTRAINT `fk_msg_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
              ADD CONSTRAINT `fk_msg_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ");
        
        echo "✓ Đã tạo bảng conversations và messages\n";
    } else {
        echo "Bảng conversations đã tồn tại, kiểm tra cấu trúc...\n";
        
        // Check if old column names exist and rename them
        $columns = $pdo->query("SHOW COLUMNS FROM conversations")->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('user_id', $columns)) {
            echo "Đổi tên cột user_id thành buyer_id...\n";
            $pdo->exec("ALTER TABLE conversations CHANGE `user_id` `buyer_id` int(11) NOT NULL COMMENT 'Người mua'");
            echo "✓ Đã đổi tên cột user_id\n";
        }
        
        if (in_array('user_unread', $columns)) {
            echo "Đổi tên cột user_unread thành unread_count_buyer...\n";
            $pdo->exec("ALTER TABLE conversations CHANGE `user_unread` `unread_count_buyer` int(11) DEFAULT 0 COMMENT 'Số tin chưa đọc của buyer'");
            echo "✓ Đã đổi tên cột user_unread\n";
        }
        
        if (in_array('seller_unread', $columns)) {
            echo "Đổi tên cột seller_unread thành unread_count_seller...\n";
            $pdo->exec("ALTER TABLE conversations CHANGE `seller_unread` `unread_count_seller` int(11) DEFAULT 0 COMMENT 'Số tin chưa đọc của seller'");
            echo "✓ Đã đổi tên cột seller_unread\n";
        }
        
        // Check messages table
        $msgColumns = $pdo->query("SHOW COLUMNS FROM messages")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('attachment', $msgColumns)) {
            echo "Them cot attachment vao bang messages...\n";
            $pdo->exec("ALTER TABLE messages ADD COLUMN `attachment` varchar(255) DEFAULT NULL AFTER `message`");
            echo "OK: Da them cot attachment\n";
            $msgColumns[] = 'attachment';
        }

        if (in_array('receiver_id', $msgColumns)) {
            echo "Xóa cột receiver_id không cần thiết...\n";
            $pdo->exec("ALTER TABLE messages DROP FOREIGN KEY IF EXISTS fk_msg_receiver");
            $pdo->exec("ALTER TABLE messages DROP COLUMN `receiver_id`");
            echo "✓ Đã xóa cột receiver_id\n";
        }
    }
    
    // Add last_active_at to users table if not exists
    $userColumns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('last_active_at', $userColumns)) {
        echo "Thêm cột last_active_at vào bảng users...\n";
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `last_active_at` DATETIME DEFAULT NULL AFTER `updated_at`");
        echo "✓ Đã thêm cột last_active_at\n";
    }
    
    echo "\n✅ CẬP NHẬT DATABASE THÀNH CÔNG!\n";
    echo "Hệ thống chat đã sẵn sàng sử dụng.\n";
    
} catch (PDOException $e) {
    echo "\n❌ LỖI: " . $e->getMessage() . "\n";
    echo "Vui lòng kiểm tra lại cấu hình database.\n";
    exit(1);
}
