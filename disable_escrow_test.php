<?php
require 'config/database.php';
$c = require 'config/database.php';
$p = new PDO("mysql:host={$c['host']};dbname={$c['database']}", $c['username'], $c['password']);

// Tắt escrow tạm thời để test checkout
$p->exec("UPDATE system_settings SET setting_value = '0' WHERE setting_key = 'enable_escrow'");
echo "Escrow disabled for testing!\n";
echo "Run this to re-enable: UPDATE system_settings SET setting_value = '1' WHERE setting_key = 'enable_escrow'\n";
