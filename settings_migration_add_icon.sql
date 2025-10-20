-- Add icon column to categories
ALTER TABLE categories ADD COLUMN icon VARCHAR(32) NULL AFTER color;
