<?php
define('ROOT_PATH', __DIR__);
require_once 'app/Core/Database.php';
$db = Database::getInstance();

$sql = "CREATE TABLE IF NOT EXISTS spam_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$db->query($sql);
echo "Table spam_alerts created/verified.\n";
