<?php
require 'config/database.php';
$c = require 'config/database.php';
$p = new PDO("mysql:host={$c['host']};dbname={$c['database']}", $c['username'], $c['password']);

$cols = $p->query("SHOW COLUMNS FROM deposit_requests")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo $col['Field'] . " | " . $col['Type'] . " | Null:" . $col['Null'] . " | Default:" . $col['Default'] . "\n";
}
