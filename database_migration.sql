-- =====================================================
-- Migration: Add missing columns to tbl_car
-- =====================================================

-- Add transmission column
ALTER TABLE tbl_car ADD COLUMN transmission VARCHAR(20) DEFAULT 'Automatic' AFTER year;

-- Add fuel_type column
ALTER TABLE tbl_car ADD COLUMN fuel_type VARCHAR(20) DEFAULT 'Gasoline' AFTER transmission;

-- Add seats column
ALTER TABLE tbl_car ADD COLUMN seats INT DEFAULT 5 AFTER fuel_type;

-- Add image column
ALTER TABLE tbl_car ADD COLUMN image VARCHAR(255) NULL AFTER seats;

-- Verify the changes (this is just for reference, remove when running)
-- DESCRIBE tbl_car;
