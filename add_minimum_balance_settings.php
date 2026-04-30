<?php
require 'config/database.php';
$c = require 'config/database.php';
$p = new PDO("mysql:host={$c['host']};dbname={$c['database']}", $c['username'], $c['password']);

echo "Adding minimum balance settings...\n";

$p->exec("INSERT INTO system_settings (setting_key, setting_value, description) VALUES 
    ('seller_minimum_balance', '500000', 'Số dư tối thiểu seller phải giữ trong tài khoản (VNĐ)') 
    ON DUPLICATE KEY UPDATE setting_value='500000'");

$p->exec("INSERT INTO system_settings (setting_key, setting_value, description) VALUES 
    ('seller_deactivation_hold_days', '7', 'Số ngày chờ khi seller yêu cầu hủy tài khoản') 
    ON DUPLICATE KEY UPDATE setting_value='7'");

echo "✓ Added seller_minimum_balance: 500,000 VNĐ\n";
echo "✓ Added seller_deactivation_hold_days: 7 days\n";
echo "\nDone!\n";
