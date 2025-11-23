-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2025 at 04:45 PM
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
(2, 2, 'report_submitted', 'Submitted report: Streetlight outage', 50, '2025-11-22 15:37:37'),
(3, 4, 'report_submitted', 'Submitted report: Street Light Flickering', 50, '2025-11-23 10:04:13'),
(4, 4, 'report_submitted', 'Submitted report: Graffiti on the wall', 50, '2025-11-23 10:07:22'),
(5, 4, 'report_submitted', 'Submitted report: Trash Everywhere', 50, '2025-11-23 10:09:07'),
(6, 4, 'report_submitted', 'Submitted report: Road Blocked', 50, '2025-11-23 10:11:02'),
(7, 4, 'report_submitted', 'Submitted report: Road Work', 50, '2025-11-23 10:13:18'),
(8, 4, 'report_submitted', 'Submitted report: Pothole', 50, '2025-11-23 12:44:32'),
(9, 4, 'report_submitted', 'Submitted report: Trash Everywhere', 50, '2025-11-23 14:30:42'),
(10, 4, 'report_submitted', 'Submitted report: Road Work', 50, '2025-11-23 14:32:59');

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
(8, 'Other', '📋', '#95a5a6'),
(9, 'Downed Tree', '🌲', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `clothing_rewards`
--

CREATE TABLE `clothing_rewards` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(10) NOT NULL,
  `level_required` int(11) NOT NULL,
  `rarity` enum('common','rare','epic','legendary') DEFAULT 'common',
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clothing_rewards`
--

INSERT INTO `clothing_rewards` (`id`, `name`, `description`, `icon`, `level_required`, `rarity`, `image_url`, `created_at`) VALUES
(1, 'Rookie Cap', 'A simple cap for new citizens', '🧢', 1, 'common', NULL, '2025-11-23 09:49:08'),
(2, 'Citizen T-Shirt', 'Show your civic pride', '👕', 2, 'common', NULL, '2025-11-23 09:49:08'),
(3, 'Reporter Vest', 'For dedicated reporters', '🦺', 5, 'rare', NULL, '2025-11-23 09:49:08'),
(4, 'Hero Hoodie', 'Keep warm while helping your city', '🧥', 10, 'rare', NULL, '2025-11-23 09:49:08'),
(5, 'Legend Jacket', 'Only the elite wear this', '🧥', 15, 'epic', NULL, '2025-11-23 09:49:08'),
(6, 'Champion Sunglasses', 'Look cool while making a difference', '🕶️', 20, 'epic', NULL, '2025-11-23 09:49:08'),
(7, 'Elite Sneakers', 'Walk the path of excellence', '👟', 25, 'legendary', NULL, '2025-11-23 09:49:08'),
(8, 'Master\'s Crown', 'The ultimate achievement', '👑', 30, 'legendary', NULL, '2025-11-23 09:49:08');

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
(5, 2, 4, 'Streetlight outage', 'The streetlight is out', 'Sheshi Xhorxh Bush', 42.65932200, 21.16023700, 'uploads/6921d8c19c603_1763825857.png', 'resolved', 'medium', '', '2025-11-22 15:37:37', '2025-11-23 10:05:21', NULL),
(6, 4, 4, 'Street Light Flickering', 'The street light has started flickering', 'Aktash, Prishtina', 42.65406700, 21.16756700, 'uploads/6922dc1d65cfc_1763892253.jpg', 'rejected', 'medium', '', '2025-11-23 10:04:13', '2025-11-23 12:42:19', NULL),
(7, 4, 5, 'Graffiti on the wall', 'Someone has sprayed graffiti all over the wall', 'Rruga B, Pristina', 42.65147900, 21.17472100, 'uploads/6922dcdab5957_1763892442.jpg', 'resolved', 'low', '', '2025-11-23 10:07:22', '2025-11-23 10:14:18', NULL),
(8, 4, 3, 'Trash Everywhere', 'There is so much trash the it is overflowing', 'Kalabria, Pristina', 42.64351300, 21.14391000, 'uploads/6922dd43159e3_1763892547.jpg', 'in_progress', 'high', '', '2025-11-23 10:09:07', '2025-11-23 10:13:57', NULL),
(9, 4, 8, 'Road Blocked', 'The road is blocked by the cars parked', 'Dardania, Pristina', 42.64931700, 21.15472200, 'uploads/6922ddb63a17c_1763892662.png', 'pending', 'urgent', '', '2025-11-23 10:11:02', '2025-11-23 12:53:46', NULL),
(10, 4, 2, 'Road Work', 'The road work has started but it is blocking everything', 'Rruga A, Pristina', 42.64270400, 21.16798300, 'uploads/6922de3eb2091_1763892798.png', 'in_progress', 'medium', '', '2025-11-23 10:13:18', '2025-11-23 12:54:49', NULL),
(11, 4, 1, 'Pothole', 'The road is damaged and a pothole has been uncovered', 'Ulpiana, Pristina', 42.65137300, 21.16305600, 'uploads/692301b09050e_1763901872.jpg', 'pending', 'medium', NULL, '2025-11-23 12:44:32', '2025-11-23 12:44:32', NULL),
(12, 4, 3, 'Trash Everywhere', 'There is trash everywhere because of the overflow', 'Mahalla e Muhaxhereve, Pristina', 42.65888000, 21.16649700, 'uploads/69231a926657f_1763908242.jpg', 'pending', 'high', NULL, '2025-11-23 14:30:42', '2025-11-23 14:30:42', NULL),
(13, 4, 2, 'Road Work', 'Road work ahead', 'Lakrishte, Pristina', 42.65334100, 21.14604200, 'uploads/69231b1bd075d_1763908379.jpg', 'pending', 'medium', NULL, '2025-11-23 14:32:59', '2025-11-23 14:32:59', NULL);

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
(3, 'gerti', 'gerticalaj@gmail.com', '$2y$10$w.dfqmZeehOLOeCmdPFCHOfLt.pK/LEdTJu3ZeGvCyR7mrNIdWW6m', 'gerti', '+38344123456', 0, '2025-11-22 13:16:18', 1, '2025-11-22', 200, 2),
(4, 'deoni', 'deonbeka@gmail.com', '$2y$10$08cJDooEaW/7.x8ZYq5TveJftwdQ/9/IWgUqifRVDdXofwRhj0U5u', 'Deon', '+38344123456', 0, '2025-11-22 15:29:43', 1, '2025-11-23', 350, 2);

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
(1, 2, 1, '2025-11-22 15:37:37'),
(2, 3, 1, '2025-11-23 09:40:34'),
(3, 4, 1, '2025-11-23 10:04:13'),
(5, 4, 2, '2025-11-23 12:32:54');

-- --------------------------------------------------------

--
-- Table structure for table `user_claimed_rewards`
--

CREATE TABLE `user_claimed_rewards` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reward_id` int(11) NOT NULL,
  `claimed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_claimed_rewards`
--

INSERT INTO `user_claimed_rewards` (`id`, `user_id`, `reward_id`, `claimed_at`) VALUES
(1, 3, 1, '2025-11-23 10:00:32'),
(2, 3, 2, '2025-11-23 10:00:34'),
(3, 4, 1, '2025-11-23 10:29:39');

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
-- Indexes for table `clothing_rewards`
--
ALTER TABLE `clothing_rewards`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `user_claimed_rewards`
--
ALTER TABLE `user_claimed_rewards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_reward` (`user_id`,`reward_id`),
  ADD KEY `reward_id` (`reward_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `clothing_rewards`
--
ALTER TABLE `clothing_rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_badges`
--
ALTER TABLE `user_badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_claimed_rewards`
--
ALTER TABLE `user_claimed_rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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

--
-- Constraints for table `user_claimed_rewards`
--
ALTER TABLE `user_claimed_rewards`
  ADD CONSTRAINT `user_claimed_rewards_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_claimed_rewards_ibfk_2` FOREIGN KEY (`reward_id`) REFERENCES `clothing_rewards` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
