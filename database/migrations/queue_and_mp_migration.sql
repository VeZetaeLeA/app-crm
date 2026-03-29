<?php
// storage/database/migrations/queue_and_mp_migration.sql
?>
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_class` varchar(255) NOT NULL,
  `payload` json NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('pending','processing','failed') NOT NULL DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: For MercadoPago preferences if we want to store them, 
-- but we can just use the MP ID directly on the invoice table.
ALTER TABLE `invoices` ADD COLUMN `mp_preference_id` VARCHAR(255) NULL AFTER `status`;
