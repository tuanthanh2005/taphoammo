<?php
/**
 * Simple Database Migration Script
 */

// Load .env
define('ROOT_PATH', __DIR__);
if (file_exists(ROOT_PATH . '/.env')) {
    $lines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value, '"');
        }
    }
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'mmo_marketplace';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';

try {
    // 1. Connect to MySQL (without DB)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "--- Bắt đầu quá trình import database ---\n";

    // 2. Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "1. Đã tạo/kiểm tra database: $dbname\n";

    // 3. Connect to the database
    $pdo->exec("USE `$dbname`");

    // 4. Read SQL file
    $sqlFile = ROOT_PATH . '/database.sql';
    if (!file_exists($sqlFile)) {
        die("Lỗi: Không tìm thấy file database.sql tại " . $sqlFile);
    }

    $sql = file_get_contents($sqlFile);

    // 5. Execute SQL
    // Note: This works for the provided database.sql which has standard formatting
    $pdo->exec($sql);

    echo "2. Đã import dữ liệu từ file database.sql Thành công!\n";
    echo "--- Hoàn tất! Bây giờ bạn có thể đăng nhập vào trang admin! ---\n";

} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
