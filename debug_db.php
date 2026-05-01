<?php
define('ROOT_PATH', __DIR__);
require_once 'app/Core/Database.php';
$db = Database::getInstance();
echo "Logs structure:\n";
print_r($db->fetchAll("DESCRIBE logs"));
echo "\nDeposit requests rejected stats:\n";
print_r($db->fetchAll("SELECT user_id, COUNT(*) as count FROM deposit_requests WHERE status = 'rejected' GROUP BY user_id"));
