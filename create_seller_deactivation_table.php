<?php
require 'config/database.php';
$c = require 'config/database.php';
$p = new PDO("mysql:host={$c['host']};dbname={$c['database']}", $c['username'], $c['password']);

echo "Creating seller_deactivation_requests table...\n";

$p->exec("
    CREATE TABLE IF NOT EXISTS seller_deactivation_requests (
        id INT(11) NOT NULL AUTO_INCREMENT,
        seller_id INT(11) NOT NULL,
        request_date DATETIME NOT NULL,
        hold_until DATETIME NOT NULL,
        refund_amount DECIMAL(15,2) NOT NULL,
        status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
        reason TEXT,
        admin_note TEXT,
        processed_at DATETIME DEFAULT NULL,
        processed_by INT(11) DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY seller_id (seller_id),
        KEY status (status),
        KEY hold_until (hold_until)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "✓ Table created successfully!\n";
