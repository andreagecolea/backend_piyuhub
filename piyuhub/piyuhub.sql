-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2024 at 04:30 AM
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
-- Database: `piyuhub`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `comment_text` text DEFAULT NULL,
  `fname` varchar(100) NOT NULL,
  `lname` varchar(100) NOT NULL,
  `comment_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments_college`
--

CREATE TABLE `comments_college` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `comment_text` text DEFAULT NULL,
  `fname` varchar(100) NOT NULL,
  `lname` varchar(100) NOT NULL,
  `comment_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments_lost`
--

CREATE TABLE `comments_lost` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `comment_text` text DEFAULT NULL,
  `fname` varchar(100) NOT NULL,
  `lname` varchar(100) NOT NULL,
  `comment_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `recipient_id`, `message`, `created_at`, `is_read`) VALUES
(67, 9, 1, 'hi', '2024-10-18 12:03:32', 1),
(68, 1, 9, 'hello', '2024-10-18 12:04:52', 1),
(69, 9, 1, 'This is a sample message.', '2024-10-18 12:14:21', 0);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `post_id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(45, 21, 9, 'New announcement by Andrea Gecolea', 0, '2024-10-08 07:22:34'),
(49, 21, 1, 'New announcement by Andrea Gecolea', 0, '2024-10-08 07:22:52');

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

CREATE TABLE `post` (
  `id` int(11) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `student_name` varchar(200) NOT NULL,
  `post_text` text NOT NULL,
  `post_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post`
--

INSERT INTO `post` (`id`, `student_id`, `student_name`, `post_text`, `post_image`, `created_at`) VALUES
(23, 'CCS-01', 'Andrea Gecolea', 'This is a sample post', NULL, '2024-10-08 07:27:46'),
(27, 'CCS-01', 'Andrea Gecolea', 'hi', NULL, '2024-12-03 02:07:02');

-- --------------------------------------------------------

--
-- Table structure for table `post_college`
--

CREATE TABLE `post_college` (
  `id` int(11) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `student_name` varchar(200) NOT NULL,
  `post_text` text NOT NULL,
  `post_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_college`
--

INSERT INTO `post_college` (`id`, `student_id`, `student_name`, `post_text`, `post_image`, `created_at`) VALUES
(21, 'CCS-01', 'Andrea Gecolea', 'This is an announcement posted by the Developer (Super Admin)', NULL, '2024-10-08 07:22:34');

-- --------------------------------------------------------

--
-- Table structure for table `post_images`
--

CREATE TABLE `post_images` (
  `id` int(11) NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_images`
--

INSERT INTO `post_images` (`id`, `post_id`, `image_path`) VALUES
(4, 23, 'Upload/posts/6704def2a016f_ashim-d-silva-WeYamle9fDM-unsplash.jpg'),
(6, 27, 'Upload/posts/674e67c68bd42_1338179.png');

-- --------------------------------------------------------

--
-- Table structure for table `post_images_college`
--

CREATE TABLE `post_images_college` (
  `id` int(11) NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_images_lost`
--

CREATE TABLE `post_images_lost` (
  `id` int(11) NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_lost`
--

CREATE TABLE `post_lost` (
  `id` int(11) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `student_name` varchar(200) NOT NULL,
  `post_text` text NOT NULL,
  `post_image` varchar(255) DEFAULT NULL,
  `status` enum('LOST','FOUND') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_lost`
--

INSERT INTO `post_lost` (`id`, `student_id`, `student_name`, `post_text`, `post_image`, `status`, `created_at`) VALUES
(15, 'CCS-01', 'Andrea Gecolea', 'found', NULL, 'FOUND', '2024-10-14 06:23:22');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `id_pic` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT 'Upload/logo/profile.png',
  `fname` varchar(100) NOT NULL,
  `lname` varchar(100) NOT NULL,
  `id_no` varchar(9) NOT NULL CHECK (`id_no` regexp '^[0-9]{4}-[0-9]{4}$'),
  `college` enum('CCS','CTE','CFND','CAS','CBAA','CHMT','CCJE','COF','ADMIN') NOT NULL,
  `email` varchar(255) NOT NULL,
  `status` enum('pending','approved','blocked') DEFAULT 'pending',
  `position` enum('Student','SSC','Org','Representative','Admin','Developer') DEFAULT 'Student',
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token_hash` varchar(255) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `last_attempt_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `student_id`, `id_pic`, `profile_picture`, `fname`, `lname`, `id_no`, `college`, `email`, `status`, `position`, `password`, `created_at`, `reset_token_hash`, `reset_token_expires_at`, `login_attempts`, `last_attempt_time`) VALUES
(1, 'OTHERS-01', 'Upload/id/OTHERS-01_Piyulogo.png', 'Upload/profile/CCS-1111-1111-67022352e4ad8-piyuLogo.png', 'PiyuHub', '', '1111-1111', 'ADMIN', 'info.piyuhub@gmail.com', 'approved', 'Developer', '$2y$10$t1cbY6ZKY3KeNufdd7g.aeSItLYe3yk3jQyVaPRMyqWw4S9ixfnxG', '2024-10-06 04:31:15', NULL, NULL, 0, '2024-10-06 08:52:11'),
(9, 'CCS-01', 'Upload/id/CCS-01_PICTURE_REGULAR_2X2 - Copy.JPG', 'Upload/profile/CCS-0422-0637-670d3295ad5e3-geco.png', 'Andrea', 'Gecolea', '0422-0637', 'CCS', 'andreagecolea18@gmail.com', 'approved', 'Developer', '$2y$10$pdgTg8RMFjU.c2bKy.OlzOHaWjtliBB24CHQe9nbMJCmblfE5JzeO', '2024-10-06 05:54:36', '$2y$10$TzE62RXjEkfUd0fSvZR7qOrcOalQjEqBUuON.J3.qYFqXsY6KAqO.', '2024-10-09 09:14:39', 0, '2024-10-14 15:02:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `comments_college`
--
ALTER TABLE `comments_college`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `comments_lost`
--
ALTER TABLE `comments_lost`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `recipient_id` (`recipient_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `post_college`
--
ALTER TABLE `post_college`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `post_images`
--
ALTER TABLE `post_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `post_images_college`
--
ALTER TABLE `post_images_college`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `post_images_lost`
--
ALTER TABLE `post_images_lost`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `post_lost`
--
ALTER TABLE `post_lost`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `comments_college`
--
ALTER TABLE `comments_college`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `comments_lost`
--
ALTER TABLE `comments_lost`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `post`
--
ALTER TABLE `post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `post_college`
--
ALTER TABLE `post_college`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `post_images`
--
ALTER TABLE `post_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `post_images_college`
--
ALTER TABLE `post_images_college`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `post_images_lost`
--
ALTER TABLE `post_images_lost`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `post_lost`
--
ALTER TABLE `post_lost`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `comments_college`
--
ALTER TABLE `comments_college`
  ADD CONSTRAINT `comments_college_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `post_college` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_college_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `comments_lost`
--
ALTER TABLE `comments_lost`
  ADD CONSTRAINT `comments_lost_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `post_lost` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_lost_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `post_college` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post`
--
ALTER TABLE `post`
  ADD CONSTRAINT `post_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `post_college`
--
ALTER TABLE `post_college`
  ADD CONSTRAINT `post_college_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `post_images`
--
ALTER TABLE `post_images`
  ADD CONSTRAINT `post_images_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_images_college`
--
ALTER TABLE `post_images_college`
  ADD CONSTRAINT `post_images_college_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `post_college` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_images_lost`
--
ALTER TABLE `post_images_lost`
  ADD CONSTRAINT `post_images_lost_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `post_lost` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_lost`
--
ALTER TABLE `post_lost`
  ADD CONSTRAINT `post_lost_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`student_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
