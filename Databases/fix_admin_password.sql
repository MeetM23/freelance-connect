-- Fix Admin Password Script
-- Run this if you already have the admins table but can't login

-- Update the admin password to 'admin123'
UPDATE admins 
SET password_hash = '$2y$10$kouTJKmjFQahYaS4XXb/WeExrwsfeA/p7B5qKR9tcZf3Q1EL7ygyq' 
WHERE email = 'admin@freelanceconnect.com';

-- If no admin exists, create one
INSERT IGNORE INTO admins (username, email, password_hash, first_name, last_name) 
VALUES ('admin', 'admin@freelanceconnect.com', '$2y$10$kouTJKmjFQahYaS4XXb/WeExrwsfeA/p7B5qKR9tcZf3Q1EL7ygyq', 'Admin', 'User'); 