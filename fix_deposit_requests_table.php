<?php
require 'config/database.php';
$c = require 'config/database.php';
$p = new PDO("mysql:host={$c['host']};dbname={$c['database']}", $c['username'], $c['password']);
$p->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Checking deposit_requests table...\n";

// Check if table exists
$tables = $p->query("SHOW TABLES LIKE 'deposit_requests'")->fetchAll();

if (empty($tables)) {
    echo "Creating deposit_requests table...\n";
    $p->exec("
        CREATE TABLE deposit_requests (
            id INT(11) NOT NULL AUTO_INCREMENT,
            user_id INT(11) NOT NULL,
            deposit_code VARCHAR(50) NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            note TEXT,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            proof_image VARCHAR(255) DEFAULT NULL,
            admin_note TEXT,
            processed_by INT(11) DEFAULT NULL,
            processed_at DATETIME DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY deposit_code (deposit_code),
            KEY user_id (user_id),
            KEY status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Table created\n";
} else {
    echo "Table exists, checking columns...\n";
    
    // Check if deposit_code column exists
    $columns = $p->query("SHOW COLUMNS FROM deposit_requests")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('deposit_code', $columns)) {
        echo "Adding deposit_code column...\n";
        $p->exec("ALTER TABLE deposit_requests ADD COLUMN deposit_code VARCHAR(50) NOT NULL AFTER user_id");
        $p->exec("ALTER TABLE deposit_requests ADD UNIQUE KEY deposit_code (deposit_code)");
        echo "✓ Added deposit_code column\n";
    } else {
        echo "✓ deposit_code column already exists\n";
    }
}

echo "\nDone!\n";
