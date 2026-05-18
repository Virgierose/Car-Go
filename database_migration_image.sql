-- =====================================================
-- Migration: Add image column to tbl_car
-- =====================================================

-- Add image column to store car image paths
ALTER TABLE tbl_car ADD COLUMN image VARCHAR(255) NULL DEFAULT NULL AFTER status;

-- Optional: Create directory for car images
-- You may need to manually create this directory:
-- /xampp/htdocs/CarGo/assets/img/cars/

-- Verify the changes
-- DESCRIBE tbl_car;
