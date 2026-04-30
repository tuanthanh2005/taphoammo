<?php
require 'app/Core/Database.php'; 
require 'config/database.php'; 
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
$db->insert('menus', ['id'=>1, 'title'=>'Sản phẩm', 'url'=>'#', 'display_order'=>1]);
$db->insert('menus', ['id'=>2, 'title'=>'Dịch vụ', 'url'=>'#', 'display_order'=>2]);
$db->insert('menus', ['id'=>3, 'title'=>'Hỗ trợ', 'url'=>'/support', 'display_order'=>3]);
$db->insert('menus', ['id'=>4, 'title'=>'Công cụ', 'url'=>'#', 'display_order'=>4]);
$db->insert('menus', ['id'=>5, 'title'=>'FAQs', 'url'=>'/faqs', 'display_order'=>5]);

// Children of Sản phẩm
$db->insert('menus', ['title'=>'Email Marketing', 'url'=>'/category/email-marketing', 'icon'=>'fas fa-envelope', 'parent_id'=>1, 'display_order'=>1]);
$db->insert('menus', ['title'=>'Phần mềm', 'url'=>'/category/phan-mem', 'icon'=>'fas fa-laptop-code', 'parent_id'=>1, 'display_order'=>2]);

// Children of Dịch vụ
$db->insert('menus', ['title'=>'Tăng tương tác', 'url'=>'/category/tang-tuong-tac', 'icon'=>'fas fa-chart-line', 'parent_id'=>2, 'display_order'=>1]);
$db->insert('menus', ['title'=>'Blockchain', 'url'=>'/category/blockchain', 'icon'=>'fab fa-bitcoin', 'parent_id'=>2, 'display_order'=>2]);

echo 'Menu table created';
