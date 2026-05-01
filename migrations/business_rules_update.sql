-- Business rules update: warranty-based disputes, safer stock/refund status, escrow disputed state.

ALTER TABLE `products`
    ADD COLUMN IF NOT EXISTS `warranty_days` INT(11) NOT NULL DEFAULT 0 AFTER `product_type`,
    ADD COLUMN IF NOT EXISTS `warranty_note` TEXT DEFAULT NULL AFTER `warranty_days`;

ALTER TABLE `product_stocks`
    MODIFY COLUMN `status` ENUM('available','sold','refunded','invalidated') NOT NULL DEFAULT 'available';

ALTER TABLE `order_items`
    MODIFY COLUMN `item_status` ENUM('processing','delivered','disputed','issue','refunded','released') NOT NULL DEFAULT 'processing';

ALTER TABLE `held_funds`
    MODIFY COLUMN `status` ENUM('holding','disputed','released','refunded') DEFAULT 'holding',
    ADD COLUMN IF NOT EXISTS `order_item_id` INT(11) DEFAULT NULL AFTER `order_id`;

ALTER TABLE `seller_deposits`
    ADD COLUMN IF NOT EXISTS `released_quantity` INT(11) NOT NULL DEFAULT 0 AFTER `stock_quantity`,
    ADD COLUMN IF NOT EXISTS `released_deposit_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `deposit_amount`;

ALTER TABLE `disputes`
    ADD COLUMN IF NOT EXISTS `seller_response` TEXT DEFAULT NULL AFTER `description`,
    ADD COLUMN IF NOT EXISTS `seller_evidence_images` LONGTEXT DEFAULT NULL AFTER `evidence_images`,
    ADD COLUMN IF NOT EXISTS `seller_responded_at` DATETIME DEFAULT NULL AFTER `seller_evidence_images`;

ALTER TABLE `transactions`
    MODIFY COLUMN `type` VARCHAR(50) NOT NULL DEFAULT 'deposit';

ALTER TABLE `users`
    MODIFY COLUMN `status` ENUM('active','banned','pending','suspended') NOT NULL DEFAULT 'active';

CREATE TABLE IF NOT EXISTS `dispute_events` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `dispute_id` INT(11) NOT NULL,
    `actor_id` INT(11) DEFAULT NULL,
    `actor_role` VARCHAR(50) DEFAULT NULL,
    `event_type` VARCHAR(50) NOT NULL,
    `message` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `dispute_id` (`dispute_id`),
    KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('deposit_percentage', '30', 'Default seller stock deposit percentage. Increase for risky sellers/products, decrease for trusted sellers.'),
('dispute_seller_response_hours', '24', 'Seller response SLA for active disputes.'),
('dispute_admin_resolution_hours', '48', 'Admin resolution SLA after seller response window.')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
