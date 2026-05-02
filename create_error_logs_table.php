<?php
// create_error_logs_table.php
require_once __DIR__ . '/app/Core/Database.php';

try {
    $db = Database::getInstance();
    $sql = "CREATE TABLE IF NOT EXISTS `error_logs` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) DEFAULT NULL,
      `error_message` text NOT NULL,
      `error_code` varchar(50) DEFAULT NULL,
      `file` varchar(255) DEFAULT NULL,
      `line` int(11) DEFAULT NULL,
      `trace` longtext,
      `url` varchar(255) DEFAULT NULL,
      `method` varchar(10) DEFAULT NULL,
      `ip_address` varchar(45) DEFAULT NULL,
      `user_agent` text,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`),
      KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $db->query($sql);
    echo "Table 'error_logs' created successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
