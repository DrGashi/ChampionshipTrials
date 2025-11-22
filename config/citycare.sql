-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 22, 2025 at 04:37 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `citycare`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `xp_earned` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `action`, `description`, `xp_earned`, `created_at`) VALUES
(1, 3, 'report_submitted', 'Submitted report: Broken Sidewalk', 50, '2025-11-22 15:25:55'),
(2, 2, 'report_submitted', 'Submitted report: Streetlight outage', 50, '2025-11-22 15:37:37');

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

CREATE TABLE `badges` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `badge_type` varchar(50) NOT NULL,
  `requirement_value` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `badges`
--

INSERT INTO `badges` (`id`, `name`, `description`, `icon`, `badge_type`, `requirement_value`, `created_at`) VALUES
(1, 'First Reporter', 'Submit your first report', '🌟', 'report_count', 1, '2025-11-22 15:26:56'),
(2, 'Active Citizen', 'Submit 5 reports', '⭐', 'report_count', 5, '2025-11-22 15:26:56'),
(3, 'Community Champion', 'Submit 10 reports', '🏅', 'report_count', 10, '2025-11-22 15:26:56'),
(4, 'Dedicated Guardian', 'Submit 25 reports', '🥈', 'report_count', 25, '2025-11-22 15:26:56'),
(5, 'Elite Protector', 'Submit 50 reports', '🥇', 'report_count', 50, '2025-11-22 15:26:56'),
(6, 'Legendary Defender', 'Submit 100 reports', '🏆', 'report_count', 100, '2025-11-22 15:26:56'),
(7, 'Consistent Reporter', 'Report issues 3 days in a row', '🔥', 'streak', 3, '2025-11-22 15:26:56'),
(8, 'Week Warrior', 'Report issues 7 days in a row', '💪', 'streak', 7, '2025-11-22 15:26:56'),
(9, 'Unstoppable', 'Report issues 14 days in a row', '⚡', 'streak', 14, '2025-11-22 15:26:56'),
(10, 'Marathon Reporter', 'Report issues 30 days in a row', '🎯', 'streak', 30, '2025-11-22 15:26:56'),
(11, 'Road Warrior', 'Submit 10 pothole reports', '🛣️', 'category_pothole', 10, '2025-11-22 15:26:56'),
(12, 'Light Keeper', 'Submit 10 street light reports', '💡', 'category_streetlight', 10, '2025-11-22 15:26:56'),
(13, 'Clean Sweep', 'Submit 10 trash overflow reports', '🧹', 'category_trash', 10, '2025-11-22 15:26:56'),
(14, 'Night Owl', 'Submit a report between 10 PM and 5 AM', '🦉', 'time_night', 1, '2025-11-22 15:26:56'),
(15, 'Early Bird', 'Submit a report between 5 AM and 7 AM', '🐦', 'time_morning', 1, '2025-11-22 15:26:56'),
(16, 'Urgent Response', 'Submit 5 urgent priority reports', '🚨', 'priority_urgent', 5, '2025-11-22 15:26:56'),
(17, 'Rising Star', 'Reach Level 5', '✨', 'level', 5, '2025-11-22 15:26:56'),
(18, 'Veteran Reporter', 'Reach Level 10', '🌠', 'level', 10, '2025-11-22 15:26:56'),
(19, 'Master Guardian', 'Reach Level 20', '👑', 'level', 20, '2025-11-22 15:26:56'),
(20, 'Legend', 'Reach Level 30', '💎', 'level', 30, '2025-11-22 15:26:56');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `icon`, `color`) VALUES
(1, 'Pothole', '🕳️', '#e74c3c'),
(2, 'Road Work', '🚧', '#f39c12'),
(3, 'Trash Overflow', '🗑️', '#27ae60'),
(4, 'Street Light', '💡', '#3498db'),
(5, 'Graffiti', '🎨', '#9b59b6'),
(6, 'Water Leak', '💧', '#1abc9c'),
(7, 'Broken Sidewalk', '🚶', '#34495e'),
(8, 'Other', '📋', '#95a5a6');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `location_address` varchar(255) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','in_progress','resolved','rejected') DEFAULT 'pending',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `user_id`, `category_id`, `title`, `description`, `location_address`, `latitude`, `longitude`, `image_path`, `status`, `priority`, `admin_notes`, `created_at`, `updated_at`, `resolved_at`) VALUES
(1, 3, 2, 'Road Works', 'The road is blocked due road works ahead', 'Lat: 42.655890, Lng: 21.159684', 42.65589000, 21.15968400, 'uploads/6921b9e75f2d4.png', 'in_progress', 'low', '', '2025-11-22 13:25:59', '2025-11-22 13:59:01', NULL),
(2, 3, 6, 'The road is full of water', 'I have the seat full of water', 'Lat: 42.658422, Lng: 21.157015', 42.65842191, 21.15701526, 'uploads/download.png', 'resolved', 'low', '', '2025-11-22 14:09:43', '2025-11-22 14:13:48', NULL),
(3, 3, 1, 'Pothole', 'There is a pothole in the middle of the street', 'Lat: 42.656196, Lng: 21.156380', 42.65619607, 21.15637958, 'uploads/6921c4e5a43ba.png', 'pending', 'medium', NULL, '2025-11-22 14:12:53', '2025-11-22 14:12:53', NULL),
(4, 3, 7, 'Broken Sidewalk', 'The sidewalk is broken', 'Lakrishte, Pristina', 42.65340400, 21.14846700, 'uploads/6921d603c7fac.png', 'pending', 'low', NULL, '2025-11-22 15:25:55', '2025-11-22 15:25:55', NULL),
(5, 2, 4, 'Streetlight outage', 'The streetlight is out', 'Sheshi Xhorxh Bush', 42.65932200, 21.16023700, 'uploads/6921d8c19c603_1763825857.png', 'pending', 'medium', NULL, '2025-11-22 15:37:37', '2025-11-22 15:37:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `report_streak` int(11) DEFAULT 0,
  `last_report_date` date DEFAULT NULL,
  `xp` int(11) DEFAULT 0,
  `level` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `is_admin`, `created_at`, `report_streak`, `last_report_date`, `xp`, `level`) VALUES
(1, 'admin', 'admin@citycare.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', NULL, 1, '2025-11-22 13:01:20', 0, NULL, 0, 1),
(2, 'dren', 'drengashi@gmail.com', '$2y$10$Td0Khwp7h5O8n2nWWQx0KOW3pmd2WamRMsbMnZYlYkS9LG3HNsTty', 'Dren', '+38344123456', 1, '2025-11-22 13:06:19', 1, '2025-11-22', 50, 1),
(3, 'gerti', 'gerticalaj@gmail.com', '$2y$10$w.dfqmZeehOLOeCmdPFCHOfLt.pK/LEdTJu3ZeGvCyR7mrNIdWW6m', 'gerti', '+38344123456', 0, '2025-11-22 13:16:18', 1, '2025-11-22', 50, 1),
(4, 'deoni', 'deonbeka@gmail.com', '$2y$10$08cJDooEaW/7.x8ZYq5TveJftwdQ/9/IWgUqifRVDdXofwRhj0U5u', 'Deon', '+38344123456', 0, '2025-11-22 15:29:43', 0, NULL, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_badges`
--

CREATE TABLE `user_badges` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `earned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_badges`
--

INSERT INTO `user_badges` (`id`, `user_id`, `badge_id`, `earned_at`) VALUES
(1, 2, 1, '2025-11-22 15:37:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_badges`
--
ALTER TABLE `user_badges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_badge` (`user_id`,`badge_id`),
  ADD KEY `badge_id` (`badge_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_badges`
--
ALTER TABLE `user_badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `user_badges`
--
ALTER TABLE `user_badges`
  ADD CONSTRAINT `user_badges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_badges_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
