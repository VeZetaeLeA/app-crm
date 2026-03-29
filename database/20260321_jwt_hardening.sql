-- Migration: JWT Refresh Token Hardening
-- AddsRevocation and Rotation tracking

ALTER TABLE `jwt_refresh_tokens` 
ADD COLUMN `replaced_by_token` VARCHAR(255) NULL AFTER `token`,
ADD COLUMN `revoked_at` DATETIME NULL AFTER `expires_at`,
ADD COLUMN `revoked_by_ip` VARCHAR(45) NULL AFTER `revoked_at`;

-- Index for performance on searches
CREATE INDEX `idx_refresh_token_lookup` ON `jwt_refresh_tokens` (`token`);
