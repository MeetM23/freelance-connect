-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 03, 2025 at 08:44 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `freelance_connect`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `profile_image`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@freelanceconnect.com', '$2y$10$Xgt2gVR/HTKEQk6CDXi04.RnWC99WTiG22morGtrWSqwXJHySWYWu', 'Admin', 'User', NULL, 1, '2025-08-03 18:12:05', '2025-08-03 12:08:04', '2025-08-03 18:12:05');

-- --------------------------------------------------------

--
-- Table structure for table `admin_login_history`
--

CREATE TABLE `admin_login_history` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_login_history`
--

INSERT INTO `admin_login_history` (`id`, `admin_id`, `login_time`, `ip_address`, `user_agent`) VALUES
(1, 1, '2025-08-03 12:19:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'),
(2, 1, '2025-08-03 18:12:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `icon`, `created_at`) VALUES
(1, 'Web Development', 'Custom websites, web apps, and e-commerce solutions', 'fas fa-code', '2025-07-28 18:58:10'),
(2, 'Mobile Development', 'iOS and Android apps with modern UI/UX design', 'fas fa-mobile-alt', '2025-07-28 18:58:10'),
(3, 'Design & Creative', 'Logo design, branding, and creative visual content', 'fas fa-palette', '2025-07-28 18:58:10'),
(4, 'Digital Marketing', 'SEO, social media marketing, and content strategy', 'fas fa-chart-line', '2025-07-28 18:58:10'),
(5, 'Writing & Translation', 'Content writing, copywriting, and translation services', 'fas fa-pen-fancy', '2025-07-28 18:58:10'),
(6, 'Video & Animation', 'Video editing, motion graphics, and animation', 'fas fa-video', '2025-07-28 18:58:10');

-- --------------------------------------------------------

--
-- Table structure for table `deals`
--

CREATE TABLE `deals` (
  `id` int(11) NOT NULL,
  `proposal_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `freelancer_id` int(11) NOT NULL,
  `status` enum('ongoing','completed','cancelled') DEFAULT 'ongoing',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deals`
--

INSERT INTO `deals` (`id`, `proposal_id`, `client_id`, `freelancer_id`, `status`, `created_at`, `updated_at`) VALUES
(3, 3, 5, 6, 'completed', '2025-08-03 13:01:38', '2025-08-03 13:20:41'),
(4, 5, 5, 4, 'completed', '2025-08-03 18:05:12', '2025-08-03 18:09:35');

-- --------------------------------------------------------

--
-- Table structure for table `deal_files`
--

CREATE TABLE `deal_files` (
  `id` int(11) NOT NULL,
  `deal_id` int(11) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deal_files`
--

INSERT INTO `deal_files` (`id`, `deal_id`, `uploaded_by`, `file_name`, `file_path`, `uploaded_at`) VALUES
(1, 3, 6, 'forms.docx', 'uploads/deals/project_3_6_1754227154.docx', '2025-08-03 13:19:14'),
(2, 3, 6, 'Documentation.docx', 'uploads/deals/project_3_6_1754227168.docx', '2025-08-03 13:19:28'),
(5, 4, 4, '20250630_2323_Rajmani Fashion Sale_simple_compose_01jz0zga5yegn810qc120kwrzp.png', 'uploads/deals/project_4_4_1754244544.png', '2025-08-03 18:09:04');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `budget_min` decimal(10,2) DEFAULT NULL,
  `budget_max` decimal(10,2) DEFAULT NULL,
  `project_type` enum('fixed','hourly') NOT NULL,
  `skills_required` text DEFAULT NULL,
  `status` enum('open','in_progress','completed','cancelled') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `admin_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `client_id`, `category_id`, `title`, `description`, `budget_min`, `budget_max`, `project_type`, `skills_required`, `status`, `created_at`, `updated_at`, `admin_notes`) VALUES
(10, 5, 2, 'Game Development', 'Create a game for kids \r\n-- First, make sure the admins table exists\r\nCREATE TABLE IF NOT EXISTS admins (\r\n    id INT AUTO_INCREMENT PRIMARY KEY,\r\n    username VARCHAR(50) UNIQUE NOT NULL,\r\n    email VARCHAR(100) UNIQUE NOT NULL,\r\n    password_hash VARCHAR(255) NOT NULL,\r\n    first_name VARCHAR(50) NOT NULL,\r\n    last_name VARCHAR(50) NOT NULL,\r\n    profile_image VARCHAR(255),\r\n    is_active BOOLEAN DEFAULT TRUE,\r\n    last_login TIMESTAMP NULL,\r\n    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\r\n    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP\r\n);', 1000.00, 1000.00, '', 'HTML, CSS, JAVASCRIPT', 'open', '2025-08-03 12:26:08', '2025-08-03 12:26:08', NULL),
(11, 5, 4, 'Application Development', 'Be specific about what you need. A clear title helps freelancers understand your project.Be specific about what you need. A clear title helps freelancers understand your project.Be specific about what you need. A clear title helps freelancers understand your project.Be specific about what you need. A clear title helps freelancers understand your project.', 800.00, 800.00, '', 'html', 'open', '2025-08-03 18:01:04', '2025-08-03 18:01:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `proposals`
--

CREATE TABLE `proposals` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `freelancer_id` int(11) NOT NULL,
  `cover_letter` text NOT NULL,
  `bid_amount` decimal(10,2) NOT NULL,
  `delivery_time` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proposals`
--

INSERT INTO `proposals` (`id`, `project_id`, `freelancer_id`, `cover_letter`, `bid_amount`, `delivery_time`, `status`, `created_at`, `admin_notes`) VALUES
(3, 10, 6, 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', 500.00, 6, 'accepted', '2025-08-03 12:59:22', NULL),
(5, 11, 4, 'Be specific about what you need. A clear title helps freelancers understand your project.Be specific about what you need. A clear title helps freelancers understand your project.Be specific about what you need. A clear title helps freelancers understand your project.Be specific about what you need. A clear title helps freelancers understand your project.', 300.00, 5, 'accepted', '2025-08-03 18:04:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `user_type` enum('freelancer','client') NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','suspended','banned') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `user_type`, `profile_image`, `bio`, `skills`, `hourly_rate`, `location`, `created_at`, `updated_at`, `status`) VALUES
(4, 'meet', 'meet@gmail.com', '$2y$10$dEQZuH00BK40GTYaa8ooP.E8k1gGm2N10YZmqaqHyVdRF.Rp93XsW', 'Meet', 'Modasiya', 'freelancer', 'uploads/profiles/profile_4_1754244139.png', 'Hello, I am future billionaire', 'HTML, CSS, JavaScript', 5.00, 'Gujarat', '2025-07-28 18:58:39', '2025-08-03 18:02:19', 'active'),
(5, 'haider', 'haider@gmail.com', '$2y$10$JAmvIoEgoezlyshGIhawgeyfI3A1mvv2MN1Tm5qhjQQOUjU3PdDxe', 'Haider', 'Limbdi', 'client', 'uploads/profiles/profile_5_1754243571.png', 'Hello, I\'m Web Developer', NULL, NULL, 'Gujrata', '2025-07-28 19:00:17', '2025-08-03 17:52:51', 'active'),
(6, 'parish', 'parish@gmail.com', '$2y$10$wbdgIKcjLdtiPeVoSyrpe.563S3juyeFkSrRVxAjiOxlGR1PWG4OS', 'Parish', '', 'freelancer', NULL, NULL, NULL, NULL, NULL, '2025-08-03 12:57:14', '2025-08-03 18:23:25', 'banned');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_login_history`
--
ALTER TABLE `admin_login_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deals`
--
ALTER TABLE `deals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proposal_id` (`proposal_id`),
  ADD KEY `idx_deals_client_id` (`client_id`),
  ADD KEY `idx_deals_freelancer_id` (`freelancer_id`),
  ADD KEY `idx_deals_status` (`status`);

--
-- Indexes for table `deal_files`
--
ALTER TABLE `deal_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_deal_files_deal_id` (`deal_id`),
  ADD KEY `idx_deal_files_uploaded_by` (`uploaded_by`),
  ADD KEY `idx_deal_files_uploaded_at` (`uploaded_at`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `proposals`
--
ALTER TABLE `proposals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proposals_status` (`status`),
  ADD KEY `idx_proposals_project_id` (`project_id`),
  ADD KEY `idx_proposals_freelancer_id` (`freelancer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_login_history`
--
ALTER TABLE `admin_login_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `deals`
--
ALTER TABLE `deals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `deal_files`
--
ALTER TABLE `deal_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `proposals`
--
ALTER TABLE `proposals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_login_history`
--
ALTER TABLE `admin_login_history`
  ADD CONSTRAINT `admin_login_history_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `deals`
--
ALTER TABLE `deals`
  ADD CONSTRAINT `deals_ibfk_1` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deals_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deals_ibfk_3` FOREIGN KEY (`freelancer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `deal_files`
--
ALTER TABLE `deal_files`
  ADD CONSTRAINT `deal_files_ibfk_1` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_files_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `proposals`
--
ALTER TABLE `proposals`
  ADD CONSTRAINT `proposals_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `proposals_ibfk_2` FOREIGN KEY (`freelancer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
