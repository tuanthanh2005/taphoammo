<?php
/**
 * Simple Database Migration Script - Fixed for Hosting
 */

define('ROOT_PATH', __DIR__);

// Load .env một cách chắc chắn hơn
if (file_exists(ROOT_PATH . '/.env')) {
    $envFile = file_get_contents(ROOT_PATH . '/.env');
    $lines = explode("\n", str_replace("\r", "", $envFile));
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0)
            continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            $_ENV[$key] = $value;
        }
    }
}

// Lấy thông tin từ ENV hoặc báo lỗi nếu thiếu
$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? '';
$username = $_ENV['DB_USER'] ?? '';
$password = $_ENV['DB_PASS'] ?? '';

if (empty($dbname) || empty($username)) {
    die("Lỗi: Không tìm thấy thông tin Database trong file .env!\n");
}

try {
    echo "--- Bắt đầu quá trình import database ---\n";
    echo "Đang kết nối tới Database: $dbname...\n";

    // 1. Connect trực tiếp tới Database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "1. Đã kết nối thành công!\n";

    // 2. Đọc file SQL
    $sqlFile = ROOT_PATH . '/database.sql';
    if (!file_exists($sqlFile)) {
        die("Lỗi: Không tìm thấy file database.sql tại " . $sqlFile . "\n");
    }

    $sql = file_get_contents($sqlFile);

    // 3. Thực thi SQL
    echo "2. Đang import dữ liệu (vui lòng đợi)...\n";
    $pdo->exec($sql);

    echo "3. Đã import dữ liệu Thành công!\n";
    echo "--- HOÀN TẤT! ---\n";

} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage() . "\n");
}
