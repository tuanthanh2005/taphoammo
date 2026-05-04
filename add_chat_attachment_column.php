<?php
/**
 * Add the missing messages.attachment column used by chat uploads.
 * Safe to run more than once.
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
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

    $table = $pdo->query("SHOW TABLES LIKE 'messages'")->fetch();
    if (!$table) {
        echo "Table messages does not exist. Run php update_chat_schema.php first.\n";
        exit(1);
    }

    $column = $pdo->query("SHOW COLUMNS FROM messages LIKE 'attachment'")->fetch();
    if ($column) {
        echo "Column messages.attachment already exists.\n";
        exit(0);
    }

    $pdo->exec("ALTER TABLE messages ADD COLUMN `attachment` varchar(255) DEFAULT NULL AFTER `message`");
    echo "Added messages.attachment successfully.\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
