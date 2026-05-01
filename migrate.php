<?php
/**
 * Simple database migration script for shared hosting.
 */

define('ROOT_PATH', __DIR__);

if (file_exists(ROOT_PATH . '/.env')) {
    $envFile = file_get_contents(ROOT_PATH . '/.env');
    $lines = explode("\n", str_replace("\r", '', $envFile));

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        if (strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? '';
$username = $_ENV['DB_USER'] ?? '';
$password = $_ENV['DB_PASS'] ?? '';

if ($dbname === '' || $username === '') {
    die("Loi: Khong tim thay thong tin database trong file .env\n");
}

try {
    echo "--- Bat dau qua trinh import database ---\n";
    echo "Dang ket noi toi database: {$dbname}...\n";

    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "1. Da ket noi thanh cong!\n";

    $sqlFile = ROOT_PATH . '/database.sql';
    if (!file_exists($sqlFile)) {
        die("Loi: Khong tim thay file database.sql tai {$sqlFile}\n");
    }

    $sql = file_get_contents($sqlFile);

    // Strip hard-coded database directives so the import targets DB_NAME from .env.
    $sql = preg_replace('/^\s*CREATE\s+DATABASE\b.*?;\s*$/mi', '', $sql);
    $sql = preg_replace('/^\s*USE\s+`?.+?`?\s*;\s*$/mi', '', $sql);

    echo "2. Dang import du lieu, vui long doi...\n";

    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $statement) {
        if ($statement === '') {
            continue;
        }

        $pdo->exec($statement);
    }

    echo "3. Da import du lieu thanh cong!\n";
    echo "--- HOAN TAT! ---\n";
} catch (PDOException $e) {
    die("Loi: " . $e->getMessage() . "\n");
}
