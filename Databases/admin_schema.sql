-- Admin Panel Database Schema for Freelance Connect
-- Add this to your existing freelance_connect database

-- Admins table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    profile_image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admin login history table
CREATE TABLE admin_login_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin123)
-- This hash is generated using password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO admins (username, email, password_hash, first_name, last_name) VALUES
('admin', 'admin@freelanceconnect.com', '$2y$10$kouTJKmjFQahYaS4XXb/WeExrwsfeA/p7B5qKR9tcZf3Q1EL7ygyq', 'Admin', 'User');

-- Add status field to users table if not exists
ALTER TABLE users ADD COLUMN IF NOT EXISTS status ENUM('active', 'suspended', 'banned') DEFAULT 'active';

-- Add admin_notes field to projects table
ALTER TABLE projects ADD COLUMN IF NOT EXISTS admin_notes TEXT;

-- Add admin_notes field to proposals table  
ALTER TABLE proposals ADD COLUMN IF NOT EXISTS admin_notes TEXT; 