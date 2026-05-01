<?php

define('ROOT_PATH', __DIR__);

if (file_exists(ROOT_PATH . '/.env')) {
    $lines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}

require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/config/database.php';

$db = Database::getInstance();

$db->query("
CREATE TABLE IF NOT EXISTS menus (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  url varchar(255) DEFAULT '#',
  icon varchar(100) DEFAULT NULL,
  parent_id int(11) DEFAULT NULL,
  display_order int(11) DEFAULT 0,
  status enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (id),
  FOREIGN KEY (parent_id) REFERENCES menus(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$db->query('DELETE FROM menus');

$db->insert('menus', ['id' => 1, 'title' => 'San pham', 'url' => '#', 'display_order' => 1]);
$db->insert('menus', ['id' => 2, 'title' => 'Dich vu', 'url' => '#', 'display_order' => 2]);
$db->insert('menus', ['id' => 3, 'title' => 'Ho tro', 'url' => '/support', 'display_order' => 3]);
$db->insert('menus', ['id' => 4, 'title' => 'Cong cu', 'url' => '#', 'display_order' => 4]);
$db->insert('menus', ['id' => 5, 'title' => 'FAQs', 'url' => '/faqs', 'display_order' => 5]);

$db->insert('menus', ['title' => 'Email Marketing', 'url' => '/category/email-marketing', 'icon' => 'fas fa-envelope', 'parent_id' => 1, 'display_order' => 1]);
$db->insert('menus', ['title' => 'Phan mem', 'url' => '/category/phan-mem', 'icon' => 'fas fa-laptop-code', 'parent_id' => 1, 'display_order' => 2]);
$db->insert('menus', ['title' => 'Tang tuong tac', 'url' => '/category/tang-tuong-tac', 'icon' => 'fas fa-chart-line', 'parent_id' => 2, 'display_order' => 1]);
$db->insert('menus', ['title' => 'Blockchain', 'url' => '/category/blockchain', 'icon' => 'fab fa-bitcoin', 'parent_id' => 2, 'display_order' => 2]);

echo "Menu table created successfully\n";
