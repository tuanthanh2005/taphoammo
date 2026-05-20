<?php
// Migration: Create negotiation_rooms and negotiation_messages tables

// Load env
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

require_once __DIR__ . '/../app/Core/Database.php';

$db = Database::getInstance();

echo "Creating negotiation_rooms table...\n";
$db->query("
    CREATE TABLE IF NOT EXISTS negotiation_rooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        topic TEXT NULL,
        admin_id INT NOT NULL,
        buyer_id INT NOT NULL,
        seller_id INT NOT NULL,
        dispute_id INT NULL,
        status ENUM('open','resolved','closed') NOT NULL DEFAULT 'open',
        last_message TEXT NULL,
        last_message_at DATETIME NULL,
        unread_admin INT NOT NULL DEFAULT 0,
        unread_buyer INT NOT NULL DEFAULT 0,
        unread_seller INT NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        closed_at DATETIME NULL,
        INDEX idx_buyer (buyer_id),
        INDEX idx_seller (seller_id),
        INDEX idx_admin (admin_id),
        INDEX idx_dispute (dispute_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "Creating negotiation_messages table...\n";
$db->query("
    CREATE TABLE IF NOT EXISTS negotiation_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        room_id INT NOT NULL,
        sender_id INT NOT NULL,
        sender_role ENUM('admin','buyer','seller','system') NOT NULL DEFAULT 'system',
        message TEXT NOT NULL,
        attachment VARCHAR(500) NULL,
        is_system TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_room (room_id),
        INDEX idx_sender (sender_id),
        FOREIGN KEY (room_id) REFERENCES negotiation_rooms(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "Done!\n";
