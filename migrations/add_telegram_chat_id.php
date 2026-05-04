<?php
/**
 * Migration: Add telegram_chat_id column to users table
 * Date: 2026-05-05
 */

$config = require_once __DIR__ . '/../config/database.php';

try {
    $db = new PDO(
        "mysql:host=" . $config['host'] . ";dbname=" . $config['database'],
        $config['username'],
        $config['password'],
        $config['options'] ?? []
    );
    
    // Check if column exists
    $stmt = $db->prepare("
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = 'users' 
        AND COLUMN_NAME = 'telegram_chat_id'
        AND TABLE_SCHEMA = ?
    ");
    $stmt->execute([$config['database']]);
    
    if ($stmt->rowCount() === 0) {
        // Column doesn't exist, so add it
        $db->exec("
            ALTER TABLE users 
            ADD COLUMN telegram_chat_id VARCHAR(255) DEFAULT NULL 
            AFTER phone
        ");
        echo "✓ Successfully added 'telegram_chat_id' column to users table\n";
    } else {
        echo "✓ Column 'telegram_chat_id' already exists\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
