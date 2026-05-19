-- Migration: Add missing car detail fields
-- This script adds engine and daily_rate fields to tbl_car table

ALTER TABLE `tbl_car` ADD COLUMN `engine` VARCHAR(50) DEFAULT NULL AFTER `fuel_type`;
ALTER TABLE `tbl_car` ADD COLUMN `daily_rate` DECIMAL(10, 2) DEFAULT 0.00 AFTER `seats`;

-- Optional: Update existing cars with default values if needed
-- UPDATE `tbl_car` SET `engine` = '2.0L' WHERE `engine` IS NULL;
-- UPDATE `tbl_car` SET `daily_rate` = 0 WHERE `daily_rate` IS NULL;
