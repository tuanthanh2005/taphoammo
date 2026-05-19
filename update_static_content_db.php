<?php
/**
 * Script to automatically add missing static_content columns on production.
 * Copy this file to your hosting root and visit: https://yourdomain.com/update_static_content_db.php
 */

define('ROOT_PATH', __DIR__);

// Load env parameters
if (file_exists(ROOT_PATH . '/.env')) {
    $envFile = file_get_contents(ROOT_PATH . '/.env');
    $lines = explode("\n", str_replace("\r", '', $envFile));
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? '';
$username = $_ENV['DB_USER'] ?? '';
$password = $_ENV['DB_PASS'] ?? '';

if ($dbname === '' || $username === '') {
    die("Lỗi: Không tìm thấy thông tin database trong file .env\n");
}

try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h3>Bắt đầu cập nhật cấu trúc Database...</h3>";

    // 1. Check products table
    $columns = $pdo->query('SHOW COLUMNS FROM products')->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('static_content', $columns)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN static_content TEXT DEFAULT NULL");
        echo "<p style='color: green;'>✓ Đã thêm cột <b>static_content</b> vào bảng <b>products</b> thành công!</p>";
    } else {
        echo "<p style='color: blue;'>- Bảng <b>products</b> đã có sẵn cột <b>static_content</b>.</p>";
    }

    // 2. Check product_variants table
    try {
        $columns = $pdo->query('SHOW COLUMNS FROM product_variants')->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('static_content', $columns)) {
            $pdo->exec('ALTER TABLE product_variants ADD COLUMN static_content TEXT DEFAULT NULL');
            echo "<p style='color: green;'>✓ Đã thêm cột <b>static_content</b> vào bảng <b>product_variants</b> thành công!</p>";
        } else {
            echo "<p style='color: blue;'>- Bảng <b>product_variants</b> đã có sẵn cột <b>static_content</b>.</p>";
        }
        if (!in_array('warranty_days', $columns)) {
            $pdo->exec('ALTER TABLE product_variants ADD COLUMN warranty_days INT DEFAULT 0 AFTER stock_quantity');
            echo "<p style='color: green;'>✓ Đã thêm cột <b>warranty_days</b> vào bảng <b>product_variants</b> thành công!</p>";
        } else {
            echo "<p style='color: blue;'>- Bảng <b>product_variants</b> đã có sẵn cột <b>warranty_days</b>.</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠ Không thể kiểm tra bảng product_variants (có thể bảng này không tồn tại): " . $e->getMessage() . "</p>";
    }

    echo "<h3 style='color: green;'>Hoàn tất! Hãy xóa file này (update_static_content_db.php) khỏi hosting sau khi chạy xong để bảo mật.</h3>";

} catch (PDOException $e) {
    die("<h3 style='color: red;'>Lỗi kết nối database: " . $e->getMessage() . "</h3>");
}
