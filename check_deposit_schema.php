<?php
// check_deposit_schema.php
require_once __DIR__ . '/app/Core/Database.php';

try {
    $db = Database::getInstance();
    $columns = $db->fetchAll("DESCRIBE deposit_requests");
    foreach ($columns as $col) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
