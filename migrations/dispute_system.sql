-- Migration: Dispute & Refund System
-- Run this on the database

-- 1. Add missing columns to order_items
ALTER TABLE `order_items`
    ADD COLUMN `item_status` ENUM('processing','delivered','disputed','issue','refunded','released') NOT NULL DEFAULT 'processing' AFTER `seller_amount`,
    ADD COLUMN `seller_note` TEXT DEFAULT NULL AFTER `item_status`,
    ADD COLUMN `note` TEXT DEFAULT NULL AFTER `seller_note`,
    ADD COLUMN `status_updated_at` DATETIME DEFAULT NULL AFTER `note`;

-- 2. Create disputes table
CREATE TABLE IF NOT EXISTS `disputes` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `dispute_code` VARCHAR(50) NOT NULL,
    `order_id` INT(11) NOT NULL,
    `order_item_id` INT(11) DEFAULT NULL,
    `user_id` INT(11) NOT NULL COMMENT 'Buyer who filed',
    `seller_id` INT(11) NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Amount in dispute',
    `reason` ENUM('not_received','wrong_item','not_working','scam','other') NOT NULL DEFAULT 'other',
    `description` TEXT NOT NULL,
    `evidence_images` TEXT DEFAULT NULL COMMENT 'JSON array of image paths',
    `status` ENUM('open','under_review','resolved_refund','resolved_partial','resolved_rejected','closed') NOT NULL DEFAULT 'open',
    `admin_id` INT(11) DEFAULT NULL COMMENT 'Admin who resolved',
    `admin_note` TEXT DEFAULT NULL,
    `refund_amount` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Amount refunded to buyer',
    `penalty_amount` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Extra penalty on seller',
    `resolved_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `dispute_code` (`dispute_code`),
    KEY `order_id` (`order_id`),
    KEY `user_id` (`user_id`),
    KEY `seller_id` (`seller_id`),
    KEY `status` (`status`),
    KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Add dispute/penalty types to transactions (ALTER ENUM)
ALTER TABLE `transactions`
    MODIFY COLUMN `type` VARCHAR(50) NOT NULL DEFAULT 'deposit';
