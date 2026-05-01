-- Hệ thống đặt cọc cho seller
-- Chạy SQL này để thêm các bảng và cột mới

-- Run this on the currently selected database.

-- Thêm cột vào bảng products để theo dõi deposit
ALTER TABLE products 
ADD COLUMN IF NOT EXISTS `deposit_required` DECIMAL(15,2) DEFAULT 0 COMMENT 'Số tiền cọc cần thiết cho stock này',
ADD COLUMN IF NOT EXISTS `deposit_paid` DECIMAL(15,2) DEFAULT 0 COMMENT 'Số tiền cọc đã trả',
ADD COLUMN IF NOT EXISTS `deposit_status` ENUM('pending', 'paid', 'released') DEFAULT 'pending' COMMENT 'Trạng thái tiền cọc';

-- Bảng theo dõi tiền cọc của seller
CREATE TABLE IF NOT EXISTS `seller_deposits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `stock_quantity` int(11) NOT NULL COMMENT 'Số lượng stock nhập',
  `product_value` decimal(15,2) NOT NULL COMMENT 'Tổng giá trị stock',
  `deposit_amount` decimal(15,2) NOT NULL COMMENT 'Số tiền cọc phải trả',
  `deposit_percentage` decimal(5,2) DEFAULT 30.00 COMMENT 'Phần trăm cọc mặc định',
  `status` enum('pending','paid','released','refunded') DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `released_at` datetime DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `seller_id` (`seller_id`),
  KEY `product_id` (`product_id`),
  KEY `status` (`status`),
  CONSTRAINT `fk_deposit_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_deposit_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng theo dõi tiền bị hold (giữ) từ đơn hàng
CREATE TABLE IF NOT EXISTS `held_funds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL COMMENT 'Số tiền bị giữ',
  `hold_until` datetime NOT NULL COMMENT 'Giữ đến ngày',
  `status` enum('holding','disputed','released','refunded') DEFAULT 'holding',
  `released_at` datetime DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `seller_id` (`seller_id`),
  KEY `status` (`status`),
  KEY `hold_until` (`hold_until`),
  CONSTRAINT `fk_held_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_held_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm cột vào bảng wallets để theo dõi tiền bị hold
ALTER TABLE wallets
ADD COLUMN IF NOT EXISTS `held_balance` DECIMAL(15,2) DEFAULT 0 COMMENT 'Số tiền đang bị giữ',
ADD COLUMN IF NOT EXISTS `deposit_balance` DECIMAL(15,2) DEFAULT 0 COMMENT 'Số tiền cọc đã đặt';

-- Thêm cột vào bảng transactions để phân loại
ALTER TABLE transactions
ADD COLUMN IF NOT EXISTS `transaction_type` ENUM('deposit','withdrawal','purchase','sale','refund','deposit_payment','deposit_release','fund_hold','fund_release') DEFAULT 'purchase',
ADD COLUMN IF NOT EXISTS `related_id` INT(11) DEFAULT NULL COMMENT 'ID liên quan (order_id, deposit_id, etc)';

-- Bảng cấu hình hệ thống
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `description` text,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm các cấu hình mặc định
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('deposit_percentage', '30', 'Phần trăm tiền cọc seller phải trả khi nhập stock (%)'),
('hold_days', '7', 'Số ngày giữ tiền sau khi khách mua hàng'),
('min_deposit_amount', '50000', 'Số tiền cọc tối thiểu (VNĐ)'),
('enable_escrow', '1', 'Bật/tắt hệ thống escrow (1=bật, 0=tắt)')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- Index để tối ưu query (chỉ tạo nếu cột đã tồn tại)
-- CREATE INDEX IF NOT EXISTS idx_wallet_held ON wallets(held_balance);
-- CREATE INDEX IF NOT EXISTS idx_wallet_deposit ON wallets(deposit_balance);
-- CREATE INDEX IF NOT EXISTS idx_transaction_type ON transactions(transaction_type);
-- CREATE INDEX IF NOT EXISTS idx_transaction_related ON transactions(related_id);

