<?php
require 'config/database.php';
$c = require 'config/database.php';
$p = new PDO("mysql:host={$c['host']};dbname={$c['database']}", $c['username'], $c['password']);
$r = $p->query('SHOW COLUMNS FROM transactions WHERE Field = "type"')->fetch(PDO::FETCH_ASSOC);
echo "Current type column: " . $r['Type'] . "\n";
