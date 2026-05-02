<?php
// check_settings.php
require_once __DIR__ . '/app/Core/Database.php';

try {
    $db = Database::getInstance();
    $rows = $db->fetchAll("SELECT * FROM settings WHERE key_name LIKE 'deposit_%' OR key_name LIKE 'telegram_%' OR key_name LIKE 'wallet_%'");
    foreach ($rows as $row) {
        echo $row['key_name'] . ": " . $row['value'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
