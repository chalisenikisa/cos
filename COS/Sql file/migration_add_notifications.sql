-- Migration: Add notifications table and stock_quantity to menu_items
-- Run this SQL to update the database

-- Add stock_quantity column to menu_items table
ALTER TABLE menu_items ADD COLUMN stock_quantity INT DEFAULT 100 AFTER is_available;

-- Add is_low_stock column for tracking notification status
ALTER TABLE menu_items ADD COLUMN is_low_stock TINYINT(1) DEFAULT 0 AFTER stock_quantity;

-- Create notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL DEFAULT 'low_stock',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    item_id INT DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_is_read (is_read)
);

-- Insert initial low stock notifications for items with low stock (if any)
-- This will be handled by the application logic
