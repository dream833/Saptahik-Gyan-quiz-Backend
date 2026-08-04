-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 27, 2026 at 01:35 PM
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
-- Database: `quiz`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin') DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'admin', 'abc@gmail.com', '123', 'admin', 'active', '2026-07-06 15:23:59');

-- --------------------------------------------------------

--
-- Table structure for table `all_mock_tests`
--

CREATE TABLE `all_mock_tests` (
  `id` int(11) NOT NULL,
  `set_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct_answer` enum('A','B','C','D') NOT NULL,
  `explanation` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `all_mock_tests`
--

INSERT INTO `all_mock_tests` (`id`, `set_id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `explanation`, `created_at`) VALUES
(2, 1, 'heloo', 'a', 's', 'a', 's', 'C', '', '2026-07-27 07:46:36'),
(9, 2, 'sf', 'f', 'f', 'f', 'f', 'B', '', '2026-07-27 08:14:18'),
(10, 4, 'dad', 'a', 'd', 'tr', 'd', 'C', '', '2026-07-27 08:54:38');

-- --------------------------------------------------------

--
-- Table structure for table `chapters`
--

CREATE TABLE `chapters` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `chapter_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chapters`
--

INSERT INTO `chapters` (`id`, `subject_id`, `chapter_name`, `created_at`) VALUES
(1, 3, 'Motion and Force', '2026-07-09 16:51:19'),
(2, 3, 'law', '2026-07-09 16:53:46');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `class_name`) VALUES
(1, '8'),
(2, 'Class XII');

-- --------------------------------------------------------

--
-- Table structure for table `exam_categories`
--

CREATE TABLE `exam_categories` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_categories`
--

INSERT INTO `exam_categories` (`id`, `title`, `description`, `created_at`) VALUES
(1, 'CLass 12', 'heloo', '2026-07-27 10:46:15');

-- --------------------------------------------------------

--
-- Table structure for table `mock_tests`
--

CREATE TABLE `mock_tests` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `test_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `test_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `duration_minutes` int(11) DEFAULT 30,
  `total_questions` int(11) DEFAULT 0,
  `total_marks` int(11) DEFAULT 0,
  `is_daily` tinyint(1) DEFAULT 1,
  `status` enum('scheduled','live','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mock_tests`
--

INSERT INTO `mock_tests` (`id`, `class_id`, `subject_id`, `test_name`, `description`, `test_date`, `start_time`, `end_time`, `duration_minutes`, `total_questions`, `total_marks`, `is_daily`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 'Physics Daily Mock 02', 'Chapter 2 Practice', '2026-07-11', '18:00:00', '18:30:00', 30, 1, 0, 1, 'scheduled', '2026-07-07 16:52:01', '2026-07-07 19:08:54'),
(9, 1, 3, 'Air', 'heloooo', '2026-07-27', '00:00:00', '23:59:00', 20, 1, 0, 1, 'scheduled', '2026-07-27 07:43:51', '2026-07-27 07:44:03'),
(10, 1, 3, 'okk', 'gfd', '2026-07-30', '00:00:00', '23:59:00', 2000, 0, 0, 1, 'scheduled', '2026-07-27 07:44:17', '2026-07-27 07:44:17');

-- --------------------------------------------------------

--
-- Table structure for table `mock_test_questions`
--

CREATE TABLE `mock_test_questions` (
  `id` int(11) NOT NULL,
  `mock_test_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `question` text NOT NULL,
  `option_a` text NOT NULL,
  `option_b` text NOT NULL,
  `option_c` text NOT NULL,
  `option_d` text NOT NULL,
  `correct_answer` enum('A','B','C','D') NOT NULL,
  `explanation` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mock_test_questions`
--

INSERT INTO `mock_test_questions` (`id`, `mock_test_id`, `created_at`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `explanation`) VALUES
(2, 1, '2026-07-07 19:08:54', 'What is Force ?', 'Energy', 'Push or Pull', 'Mass', 'Speed', 'B', 'Force is a push or pull.'),
(5, 9, '2026-07-27 07:44:03', 'Asas', 'a', 's', 'as', 'd', 'C', '');

-- --------------------------------------------------------

--
-- Table structure for table `previous_year_questions`
--

CREATE TABLE `previous_year_questions` (
  `id` int(11) NOT NULL,
  `exam_category_id` int(11) NOT NULL,
  `year` year(4) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `pdf_file` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `previous_year_questions`
--

INSERT INTO `previous_year_questions` (`id`, `exam_category_id`, `year`, `subject_id`, `pdf_file`, `created_at`) VALUES
(1, 1, '2024', 3, 'pyq_1_2024_3_1785149226.pdf', '2026-07-27 10:47:06');

-- --------------------------------------------------------

--
-- Table structure for table `sets`
--

CREATE TABLE `sets` (
  `id` int(11) NOT NULL,
  `chapter_id` int(11) NOT NULL,
  `set_name` varchar(255) NOT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sets`
--

INSERT INTO `sets` (`id`, `chapter_id`, `set_name`, `duration_minutes`, `created_at`) VALUES
(1, 1, 'Set-1', 25, '2026-07-09 18:35:04'),
(2, 1, 'Set-2', NULL, '2026-07-09 18:47:36'),
(3, 1, 'Set-2.2', 20, '2026-07-09 18:52:16'),
(4, 1, '2.3', 30, '2026-07-27 08:00:41');

-- --------------------------------------------------------

--
-- Table structure for table `solution_questions`
--

CREATE TABLE `solution_questions` (
  `id` int(11) NOT NULL,
  `chapter_id` int(11) NOT NULL,
  `question_type_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `solution_questions`
--

INSERT INTO `solution_questions` (`id`, `chapter_id`, `question_type_id`, `question`, `answer`, `created_at`) VALUES
(2, 1, 1, 'What is Force?', 'Force is a push or pull.', '2026-07-10 07:15:06');

-- --------------------------------------------------------

--
-- Table structure for table `solution_question_types`
--

CREATE TABLE `solution_question_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `solution_question_types`
--

INSERT INTO `solution_question_types` (`id`, `type_name`, `created_at`) VALUES
(1, 'Very Short', '2026-07-10 07:11:12'),
(2, 'Explanatory', '2026-07-10 07:11:12'),
(3, 'Essay-Type', '2026-07-10 07:11:12');

-- --------------------------------------------------------

--
-- Table structure for table `solution_suggestions`
--

CREATE TABLE `solution_suggestions` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `answer` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `solution_suggestions`
--

INSERT INTO `solution_suggestions` (`id`, `subject_id`, `title`, `description`, `answer`, `created_at`) VALUES
(1, 3, 'ffd', 'dfdfd', 'dfdf', '2026-07-27 10:46:02');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `class_id`, `subject_name`) VALUES
(1, 2, 'Advanced Physics'),
(2, 2, 'Geo'),
(3, 1, 'Physics'),
(4, 2, 'MAth'),
(5, 2, 'History');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mobile` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','blocked') DEFAULT 'active',
  `otp` varchar(10) DEFAULT NULL,
  `otp_expire_at` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `address` varchar(255) DEFAULT NULL,
  `class_grade` varchar(50) DEFAULT NULL,
  `about_me` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `mobile`, `password`, `class_id`, `profile_image`, `status`, `otp`, `otp_expire_at`, `last_login`, `created_at`, `updated_at`, `address`, `class_grade`, `about_me`) VALUES
(1, 'Rahul Das', 'rahul@gmail.com', '9876543210', '$2y$10$lJ/nB8J9bKxe8n/Zi1KcEengDtVxZXZdU9Kqkhkylg0VP/lBO5utG', NULL, NULL, 'active', NULL, NULL, '2026-06-24 15:21:07', '2026-06-24 09:48:04', '2026-06-24 09:51:07', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_answers`
--

CREATE TABLE `user_answers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_answer` enum('A','B','C','D') DEFAULT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_mock_results`
--

CREATE TABLE `user_mock_results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `total_questions` int(11) DEFAULT 0,
  `attempted_questions` int(11) NOT NULL DEFAULT 0,
  `correct_answers` int(11) DEFAULT 0,
  `wrong_answers` int(11) DEFAULT 0,
  `score` int(11) DEFAULT 0,
  `percentage` decimal(5,2) DEFAULT 0.00,
  `time_taken` int(11) NOT NULL DEFAULT 0,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_mock_results`
--

INSERT INTO `user_mock_results` (`id`, `user_id`, `test_id`, `total_questions`, `attempted_questions`, `correct_answers`, `wrong_answers`, `score`, `percentage`, `time_taken`, `submitted_at`) VALUES
(2, 1, 5, 0, 0, 0, 0, 0, 0.00, 0, '2026-06-24 15:48:17');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `type` enum('test','solution','custom') DEFAULT 'custom',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `device_tokens`
--

CREATE TABLE `device_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `token` varchar(255) NOT NULL,
  `platform` enum('android','ios','web') DEFAULT 'android',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for table `device_tokens`
--
ALTER TABLE `device_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `all_mock_tests`
--
ALTER TABLE `all_mock_tests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_allmock_set` (`set_id`);

--
-- Indexes for table `chapters`
--
ALTER TABLE `chapters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exam_categories`
--
ALTER TABLE `exam_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mock_tests`
--
ALTER TABLE `mock_tests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `mock_test_questions`
--
ALTER TABLE `mock_test_questions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `previous_year_questions`
--
ALTER TABLE `previous_year_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pyq_exam_category` (`exam_category_id`),
  ADD KEY `fk_pyq_subject` (`subject_id`);

--
-- Indexes for table `sets`
--
ALTER TABLE `sets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chapter_id` (`chapter_id`);

--
-- Indexes for table `solution_questions`
--
ALTER TABLE `solution_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_solution_chapter` (`chapter_id`),
  ADD KEY `fk_solution_question_type` (`question_type_id`);

--
-- Indexes for table `solution_question_types`
--
ALTER TABLE `solution_question_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `solution_suggestions`
--
ALTER TABLE `solution_suggestions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_solution_suggestion_subject` (`subject_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mobile` (`mobile`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_answers`
--
ALTER TABLE `user_answers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_mock_results`
--
ALTER TABLE `user_mock_results`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `all_mock_tests`
--
ALTER TABLE `all_mock_tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `chapters`
--
ALTER TABLE `chapters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `exam_categories`
--
ALTER TABLE `exam_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mock_tests`
--
ALTER TABLE `mock_tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `mock_test_questions`
--
ALTER TABLE `mock_test_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `previous_year_questions`
--
ALTER TABLE `previous_year_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sets`
--
ALTER TABLE `sets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `solution_questions`
--
ALTER TABLE `solution_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `solution_question_types`
--
ALTER TABLE `solution_question_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `solution_suggestions`
--
ALTER TABLE `solution_suggestions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_answers`
--
ALTER TABLE `user_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_mock_results`
--
ALTER TABLE `user_mock_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device_tokens`
--
ALTER TABLE `device_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `all_mock_tests`
--
ALTER TABLE `all_mock_tests`
  ADD CONSTRAINT `fk_allmock_set` FOREIGN KEY (`set_id`) REFERENCES `sets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chapters`
--
ALTER TABLE `chapters`
  ADD CONSTRAINT `chapters_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mock_tests`
--
ALTER TABLE `mock_tests`
  ADD CONSTRAINT `mock_tests_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `mock_tests_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

--
-- Constraints for table `previous_year_questions`
--
ALTER TABLE `previous_year_questions`
  ADD CONSTRAINT `fk_pyq_exam_category` FOREIGN KEY (`exam_category_id`) REFERENCES `exam_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pyq_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sets`
--
ALTER TABLE `sets`
  ADD CONSTRAINT `sets_ibfk_1` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `solution_questions`
--
ALTER TABLE `solution_questions`
  ADD CONSTRAINT `fk_solution_chapter` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_solution_question_type` FOREIGN KEY (`question_type_id`) REFERENCES `solution_question_types` (`id`);

--
-- Constraints for table `solution_suggestions`
--
ALTER TABLE `solution_suggestions`
  ADD CONSTRAINT `fk_solution_suggestion_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
