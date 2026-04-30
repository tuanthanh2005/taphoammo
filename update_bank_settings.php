<?php
require 'config/database.php';
$c = require 'config/database.php';
$p = new PDO("mysql:host={$c['host']};dbname={$c['database']}", $c['username'], $c['password']);

echo "Updating bank settings...\n";

$settings = [
    'deposit_bank_code'    => 'mb',
    'deposit_bank_name'    => 'MB Bank',
    'deposit_account_name' => 'TRAN THANH TUAN',
    'deposit_account_number' => '0783704196',
];

foreach ($settings as $key => $value) {
    $exists = $p->prepare("SELECT id FROM settings WHERE key_name = ?");
    $exists->execute([$key]);
    
    if ($exists->fetch()) {
        $p->prepare("UPDATE settings SET value = ? WHERE key_name = ?")->execute([$value, $key]);
        echo "✓ Updated: $key = $value\n";
    } else {
        $p->prepare("INSERT INTO settings (key_name, value) VALUES (?, ?)")->execute([$key, $value]);
        echo "✓ Inserted: $key = $value\n";
    }
}

echo "\nDone!\n";
