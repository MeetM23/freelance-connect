-- Freelance Connect Database Structure
-- Create database
CREATE DATABASE IF NOT EXISTS freelance_connect;
USE freelance_connect;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    user_type ENUM('freelancer', 'client') NOT NULL,
    profile_image VARCHAR(255),
    bio TEXT,
    skills TEXT,
    hourly_rate DECIMAL(10,2),
    location VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Projects table
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    budget_min DECIMAL(10,2),
    budget_max DECIMAL(10,2),
    project_type ENUM('fixed', 'hourly') NOT NULL,
    skills_required TEXT,
    status ENUM('open', 'in_progress', 'completed', 'cancelled') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Proposals table
CREATE TABLE proposals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    freelancer_id INT NOT NULL,
    cover_letter TEXT NOT NULL,
    bid_amount DECIMAL(10,2) NOT NULL,
    delivery_time INT NOT NULL, -- in days
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (freelancer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample categories
INSERT INTO categories (name, description, icon) VALUES
('Web Development', 'Custom websites, web apps, and e-commerce solutions', 'fas fa-code'),
('Mobile Development', 'iOS and Android apps with modern UI/UX design', 'fas fa-mobile-alt'),
('Design & Creative', 'Logo design, branding, and creative visual content', 'fas fa-palette'),
('Digital Marketing', 'SEO, social media marketing, and content strategy', 'fas fa-chart-line'),
('Writing & Translation', 'Content writing, copywriting, and translation services', 'fas fa-pen-fancy'),
('Video & Animation', 'Video editing, motion graphics, and animation', 'fas fa-video');

-- Insert sample users (password: password123)
INSERT INTO users (username, email, password_hash, first_name, last_name, user_type, bio, skills, hourly_rate, location) VALUES
('john_developer', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Developer', 'freelancer', 'Experienced web developer with 5+ years of experience', 'PHP, JavaScript, React, Node.js', 45.00, 'New York, USA'),
('sarah_designer', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Designer', 'freelancer', 'Creative designer specializing in UI/UX', 'Figma, Adobe Creative Suite, Sketch', 35.00, 'London, UK'),
('mike_client', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike', 'Client', 'client', 'Startup founder looking for talented developers', '', NULL, 'San Francisco, USA');

-- Insert sample projects
INSERT INTO projects (client_id, category_id, title, description, budget_min, budget_max, project_type, skills_required, status) VALUES
(3, 1, 'E-commerce Website Development', 'Need a modern e-commerce website with payment integration and admin panel', 2000.00, 5000.00, 'fixed', 'PHP, MySQL, JavaScript, Payment APIs', 'open'),
(3, 2, 'Mobile App for Food Delivery', 'Looking for a React Native developer to build a food delivery app', 3000.00, 8000.00, 'fixed', 'React Native, Firebase, Google Maps API', 'open'),
(3, 3, 'Company Logo and Branding', 'Need a professional logo design and complete branding package', 500.00, 1500.00, 'fixed', 'Adobe Illustrator, Photoshop, Branding', 'open'); 