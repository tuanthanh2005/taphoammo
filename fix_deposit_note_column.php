<?php
require 'config/database.php';
$c = require 'config/database.php';
$p = new PDO("mysql:host={$c['host']};dbname={$c['database']}", $c['username'], $c['password']);

$cols = $p->query("SHOW COLUMNS FROM deposit_requests")->fetchAll(PDO::FETCH_COLUMN);

if (!in_array('note', $cols)) {
    $p->exec("ALTER TABLE deposit_requests ADD COLUMN note TEXT DEFAULT NULL AFTER amount");
    echo "✓ Added note column\n";
} else {
    echo "✓ note column already exists\n";
}
echo "Done!\n";
