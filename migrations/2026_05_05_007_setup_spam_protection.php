<?php
// migrations/2026_05_05_007_setup_spam_protection.php

require_once __DIR__ . '/../app/Core/Database.php';

$db = Database::getInstance();

// 1. Add banned_until to users table
try {
    $db->query("ALTER TABLE users ADD COLUMN banned_until TIMESTAMP NULL DEFAULT NULL AFTER status");
    echo "Added banned_until column to users table.\n";
} catch (Exception $e) {
    echo "Column banned_until already exists or error: " . $e->getMessage() . "\n";
}

// 2. Update spam_alerts table
try {
    // Check if table exists
    $db->query("CREATE TABLE IF NOT EXISTS spam_alerts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        ip_address VARCHAR(45) NOT NULL,
        type VARCHAR(50) NOT NULL,
        description TEXT,
        request_count INT DEFAULT 0,
        is_resolved TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    
    // Add columns if they don't exist
    $columns = $db->fetchAll("SHOW COLUMNS FROM spam_alerts");
    $columnNames = array_column($columns, 'Field');
    
    if (!in_array('ip_address', $columnNames)) {
        $db->query("ALTER TABLE spam_alerts ADD COLUMN ip_address VARCHAR(45) NOT NULL AFTER user_id");
    }
    if (!in_array('request_count', $columnNames)) {
        $db->query("ALTER TABLE spam_alerts ADD COLUMN request_count INT DEFAULT 0 AFTER description");
    }
    if (!in_array('is_resolved', $columnNames)) {
        $db->query("ALTER TABLE spam_alerts ADD COLUMN is_resolved TINYINT(1) DEFAULT 0 AFTER request_count");
    }
    
    echo "Updated/Created spam_alerts table.\n";
} catch (Exception $e) {
    echo "Error updating spam_alerts table: " . $e->getMessage() . "\n";
}
