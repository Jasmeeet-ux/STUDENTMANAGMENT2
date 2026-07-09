-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 03, 2026 at 12:17 PM
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
-- Database: `new_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin1234', '$2y$10$GG/0FazjxC3F76NbpbEcDeYXJIG80pBdUHWaq3AM59ZGfmN4rqLi2');

-- --------------------------------------------------------

--
-- Table structure for table `affiliates`
--

CREATE TABLE `affiliates` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_earnings` decimal(10,2) DEFAULT 0.00,
  `pending_payout` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `affiliates`
--

INSERT INTO `affiliates` (`id`, `user_id`, `total_earnings`, `pending_payout`) VALUES
(1, 1, 45750.00, 8250.00),
(2, 4, 14508.20, 1500.00),
(6, 13, 0.00, 0.00),
(10, 17, 0.00, 0.00),
(16, 23, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `assignment_submissions`
--

CREATE TABLE `assignment_submissions` (
  `id` int(11) NOT NULL,
  `reg_no` varchar(50) NOT NULL,
  `topic_name` varchar(255) NOT NULL,
  `score` int(11) DEFAULT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `course_id` int(11) DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `grade` varchar(20) DEFAULT NULL,
  `feedback` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignment_submissions`
--

INSERT INTO `assignment_submissions` (`id`, `reg_no`, `topic_name`, `score`, `is_completed`, `created_at`, `course_id`, `file_path`, `submitted_at`, `grade`, `feedback`) VALUES
(14, 'DMGD-0226-1441', 'add topic', 6, 1, '2026-03-08 15:13:27', 2, 'uploads/student_assignments/sub_dmgd02261441_c8e86357d2455dd8333d89785d4e388d.pdf', '2026-03-08 19:43:48', '', 'its good '),
(18, '1111', 'gjhgmjb', NULL, 1, '2026-03-20 06:26:08', 6, 'uploads/student_assignments/sub_1111_878764ece41963b3c815d83d9a4430bc.docx', '2026-03-20 06:26:08', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `reg_no` varchar(50) NOT NULL,
  `batch_no` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `status` enum('P','A','L') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `reg_no`, `batch_no`, `date`, `status`) VALUES
(26, '123\n               ', '1\n               ', '2026-02-01', 'P'),
(27, '123\n               ', '1\n               ', '2026-02-04', 'A'),
(28, '123\n               ', '1\n               ', '2026-02-07', 'L'),
(29, '123\n               ', '1\n               ', '2026-02-15', 'A'),
(30, '123\n               ', '1\n               ', '2026-02-19', 'P'),
(31, '123\n               ', '1\n               ', '2026-02-21', 'P'),
(32, '123\n               ', '1\n               ', '2026-02-26', 'A'),
(33, '123\n               ', '1\n               ', '2026-02-25', 'L'),
(34, '123\n               ', '1\n               ', '0000-00-00', 'A'),
(35, '123\n               ', '1\n               ', '2026-02-18', 'P'),
(36, '123\n               ', '1\n               ', '2026-02-10', 'P'),
(37, '123\n               ', '1\n               ', '2026-02-02', 'P'),
(38, '123\n               ', '1\n               ', '2026-02-03', 'P'),
(39, '123\n               ', '1\n               ', '2026-02-28', 'A'),
(40, '123\n               ', '1\n               ', '2026-02-27', 'P'),
(41, '123\n               ', '1\n               ', '2026-02-13', 'P'),
(42, '123\n               ', '1\n               ', '2026-02-22', 'A'),
(43, '123\n               ', '1\n               ', '2026-02-16', 'P'),
(44, '123\n               ', '1\n               ', '2026-02-08', 'P'),
(45, '123\n               ', '1\n               ', '2026-02-09', 'P'),
(46, '123\n               ', '1\n               ', '2026-02-17', 'P'),
(47, '123\n               ', '1\n               ', '2026-02-24', 'P'),
(48, '123\n               ', '1\n               ', '2026-02-23', 'A'),
(50, '123\n               ', '1\n               ', '2026-02-05', 'P'),
(51, '123\n               ', '1\n               ', '2026-02-14', 'P'),
(52, '123\n               ', '1\n               ', '2026-02-06', 'P'),
(53, '123\n               ', '1\n               ', '2026-02-12', 'P'),
(54, '123\n               ', '1\n               ', '2026-02-11', 'P'),
(55, '123\n               ', '1\n               ', '2026-02-20', 'P'),
(56, '123', '1', '2026-02-01', 'P'),
(57, '123', '1', '2026-02-02', 'P'),
(58, '123', '1', '2026-02-03', 'A'),
(59, '123', '1', '2026-02-04', 'L'),
(60, '123', '1', '2026-02-05', 'A'),
(61, '123', '1', '2026-02-06', 'P'),
(62, '123', '1', '2026-02-07', 'A'),
(63, '123', '1', '2026-02-08', 'A'),
(64, '123', '1', '2026-02-09', 'P'),
(65, '123', '1', '2026-02-10', 'A'),
(66, '123', '1', '2026-02-11', 'P'),
(67, '123', '1', '2026-02-12', 'A'),
(68, '123', '1', '2026-02-13', 'P'),
(69, '123', '1', '2026-02-14', 'P'),
(70, '123', '1', '2026-02-15', 'A'),
(71, '123', '1', '2026-02-16', 'P'),
(72, '123', '1', '2026-02-17', 'P'),
(73, '123', '1', '2026-02-18', 'P'),
(74, '123', '1', '2026-02-19', 'P'),
(75, '123', '1', '2026-02-20', 'P'),
(76, '123', '1', '2026-02-21', 'P'),
(77, '123', '1', '2026-02-25', 'A'),
(78, '123', '1', '2026-02-24', 'P'),
(79, '123', '1', '2026-02-23', 'P'),
(80, '123', '1', '2026-02-22', 'P'),
(81, '321', '1', '2026-02-01', 'P'),
(82, '321', '1', '2026-02-02', 'P'),
(83, '321', '1', '2026-02-03', 'P'),
(84, '321', '1', '2026-02-04', 'P'),
(85, '321', '1', '2026-02-05', 'A'),
(86, '321', '1', '2026-02-06', 'P'),
(87, '321', '1', '2026-02-07', 'P'),
(88, '321', '1', '2026-02-14', 'L'),
(89, '321', '1', '2026-02-20', 'P'),
(90, '321', '1', '2026-02-12', 'P'),
(91, '321', '1', '2026-02-13', 'P'),
(92, '321', '1', '2026-02-11', 'P'),
(93, '321', '1', '2026-02-10', 'P'),
(94, '321', '1', '2026-02-09', 'P'),
(95, '321', '1', '2026-02-08', 'P'),
(96, '321', '1', '2026-02-15', 'P'),
(97, '321', '1', '2026-02-16', 'P'),
(98, '321', '1', '2026-02-22', 'P'),
(99, '321', '1', '2026-02-23', 'P'),
(100, '321', '1', '2026-02-24', 'P'),
(101, '321', '1', '2026-02-17', 'P'),
(102, '321', '1', '2026-02-25', 'P'),
(103, '321', '1', '2026-02-19', 'P'),
(104, '321', '1', '2026-02-18', 'P'),
(105, '321', '1', '2026-02-26', 'P'),
(106, '321', '1', '2026-02-21', 'L'),
(107, '989', 'Batch 1', '2026-03-01', 'P'),
(108, '989', 'Batch 1', '2026-03-02', 'A'),
(109, '989', 'Batch 1', '2026-03-03', 'P'),
(110, '989', 'Batch 1', '2026-03-04', 'P'),
(111, '989', 'Batch 1', '2026-03-05', 'L'),
(112, '989', 'Batch 1', '2026-03-06', 'A'),
(113, '989', 'Batch 1', '2026-03-07', 'P'),
(114, '989', 'Batch 1', '2026-03-14', 'A'),
(115, '989', 'Batch 1', '2026-03-27', 'L'),
(116, '989', 'Batch 1', '2026-02-25', 'P'),
(117, '989', 'Batch 1', '2026-03-08', 'A'),
(118, '989', 'Batch 1', '2026-03-09', 'A'),
(119, '989', 'Batch 3 Afternoon', '2026-03-10', 'P'),
(120, '989', 'Batch 1', '2026-03-11', 'L'),
(121, '989', 'Batch 1', '2026-03-12', 'P'),
(122, '989', 'Batch 1', '2026-03-13', 'A'),
(123, '123456', 'Batch 1 Morning', '2026-03-08', 'P'),
(124, 'ADMAI003', 'Advanced Digital Marketing with AI (ADMAI)', '2026-03-02', 'P'),
(125, 'ADMAI003', 'Advanced Digital Marketing with AI (ADMAI)', '2026-03-03', 'P'),
(126, 'ADMAI003', 'Advanced Digital Marketing with AI (ADMAI)', '2026-03-04', 'A'),
(127, 'GDPAI003', 'Graphic Design Photo Editing with AI (GDPAI)', '2026-03-01', 'A'),
(128, 'GDPAI003', 'Graphic Design Photo Editing with AI (GDPAI)', '2026-03-02', 'P'),
(129, 'GDPAI003', 'Graphic Design Photo Editing with AI (GDPAI)', '2026-03-03', 'L'),
(130, 'GDPAI003', 'Graphic Design Photo Editing with AI (GDPAI)', '2026-03-04', 'P'),
(131, 'UIUX002', 'UI/UX Design with AI (UIUXAI)', '2026-03-01', 'L'),
(132, 'UIUX002', 'UI/UX Design with AI (UIUXAI)', '2026-03-02', 'P'),
(133, 'UIUX002', 'UI/UX Design with AI (UIUXAI)', '2026-03-03', 'P'),
(134, 'ADAAI003', 'Advanced Data Analytics with AI (ADAAI)', '2026-03-03', 'P'),
(135, 'ADAAI003', 'Advanced Data Analytics with AI (ADAAI)', '2026-03-02', 'A'),
(136, 'ADAAI003', 'Advanced Data Analytics with AI (ADAAI)', '2026-03-01', 'P'),
(137, '9999', '26DM0304', '2026-03-02', 'L'),
(138, '9999', '26DM0304', '2026-03-03', 'A'),
(139, '9999', '26DM0304', '2026-03-04', 'P'),
(140, 'DMGD-0226-1441', '26DM0309', '2026-03-09', 'A'),
(141, 'DMGD-0226-1441', '26DM0309', '2026-03-02', 'P'),
(142, 'DMGD-0226-1441', '26DM0309', '2026-03-03', 'P'),
(143, 'DMGD-0226-1441', '26DM0309', '2026-03-10', 'P'),
(144, 'DMGD-0226-1441', '26DM0309', '2026-03-04', 'L'),
(145, 'DMGD-0226-1441', '26DM0309', '2026-03-11', 'L'),
(146, 'DMGD-0226-1441', '26DM0309', '2026-03-05', 'A'),
(147, 'DMGD-0226-1441', '26DM0309', '2026-03-13', 'P'),
(148, '989', 'Batch 3 Afternoon', '2026-03-17', 'A'),
(149, '989', 'Batch 3 Afternoon', '2026-03-18', 'L'),
(150, '989', 'Batch 3 Afternoon', '2026-03-19', ''),
(151, '989', 'Batch 3 Afternoon', '2026-03-16', 'P'),
(152, '989', 'Batch 3 Afternoon', '2026-03-15', 'L'),
(153, '12345', 'jfkd', '2026-03-18', ''),
(154, '12345', 'jfkd', '2026-03-13', 'P'),
(155, '12345', 'jfkd', '2026-03-05', 'P'),
(156, '12345', 'jfkd', '2026-03-09', ''),
(157, '1111', 'Batch 3 Afternoon', '2026-03-01', 'P'),
(158, '1111', 'Batch 3 Afternoon', '2026-03-02', 'A'),
(159, '1111', 'Batch 3 Afternoon', '2026-03-03', ''),
(160, '1111', 'Batch 3 Afternoon', '2026-03-04', 'L'),
(161, '1111', 'Batch 3 Afternoon', '2026-03-05', 'P'),
(162, '1111', 'Batch 3 Afternoon', '2026-03-06', ''),
(163, '1111', 'Batch 3 Afternoon', '2026-03-07', 'P'),
(164, '1111', 'Batch 3 Afternoon', '2026-03-11', ''),
(165, '1111', 'Batch 3 Afternoon', '2026-03-09', 'P'),
(166, '1111', 'Batch 3 Afternoon', '2026-03-10', ''),
(167, '1111', 'Batch 3 Afternoon', '2026-03-18', 'P'),
(168, '1111', 'Batch 3 Afternoon', '2026-03-13', 'P'),
(169, '1111', 'mo', '2026-03-17', 'P'),
(170, 'sdsd', 'kzmkz', '2026-04-02', 'A'),
(171, 'sdsd', 'kzmkz', '2026-04-01', 'A'),
(172, '434', 'kzmkz', '2026-04-01', 'P'),
(173, '434', 'kzmkz', '2026-04-02', ''),
(174, 'sdsd', 'kzmkz', '2026-04-03', 'P'),
(175, 'sdsd', 'kzmkz', '2026-04-07', 'P'),
(176, 'sdsd', 'kzmkz', '2026-04-15', 'A'),
(177, '434', 'kzmkz', '2026-04-03', 'P');

-- --------------------------------------------------------

--
-- Table structure for table `batches`
--

CREATE TABLE `batches` (
  `id` int(11) NOT NULL,
  `batch_name` varchar(100) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `timing_start` time NOT NULL DEFAULT '00:00:00',
  `timing_end` time NOT NULL DEFAULT '00:00:00',
  `day_type` enum('weekdays','weekends') NOT NULL DEFAULT 'weekdays',
  `duration` enum('6months','1year') NOT NULL DEFAULT '6months'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `batches`
--

INSERT INTO `batches` (`id`, `batch_name`, `course_id`, `created_at`, `timing_start`, `timing_end`, `day_type`, `duration`) VALUES
(29, 'kzmkz', 5, '2026-03-25 04:02:22', '19:00:00', '11:00:00', 'weekdays', ''),
(30, 'sd', 6, '2026-04-02 07:40:59', '08:07:00', '15:11:00', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `class_sessions`
--

CREATE TABLE `class_sessions` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `batch_no` varchar(50) DEFAULT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `topic` varchar(255) NOT NULL,
  `status` enum('scheduled','cancelled') NOT NULL DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `session_type` varchar(50) DEFAULT 'lecture'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_sessions`
--

INSERT INTO `class_sessions` (`id`, `course_id`, `batch_no`, `batch_id`, `date`, `start_time`, `end_time`, `topic`, `status`, `created_at`, `session_type`) VALUES
(584, 2, '26DM0309', 23, '2026-03-20', '09:30:00', '10:30:00', 'tools of ai', 'scheduled', '2026-03-18 14:14:03', 'communication'),
(585, 6, 'mo', 28, '2026-03-17', '13:52:00', '13:52:00', 'jhm', 'scheduled', '2026-03-20 06:27:01', 'lecture');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_name` varchar(300) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `duration` varchar(20) NOT NULL DEFAULT '6months',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_name`, `course_code`, `duration`, `description`, `created_at`) VALUES
(1, 'Advanced Digital Marketing with AI', 'ADMAI', '6months', NULL, '2026-03-05 19:11:12'),
(2, 'Master Digital Marketing with AI & Automation', 'MDMAI', '1year', NULL, '2026-03-05 19:11:12'),
(3, 'Graphic Design - Photo Editing with AI', 'GDPAI', '6months', NULL, '2026-03-05 19:11:12'),
(4, 'Graphic Design - Video Editing with AI', 'GDVAI', '6months', NULL, '2026-03-05 19:11:12'),
(5, 'Graphic Design - Photo & Video Editing with AI', 'GDPVAI', '1year', NULL, '2026-03-05 19:11:12'),
(6, 'Advanced Data Analytics with AI', 'ADAAI', '6months', NULL, '2026-03-05 19:11:12'),
(7, 'Master Data Analytics with Generative AI', 'MDAAI', '1year', NULL, '2026-03-05 19:11:12'),
(8, 'UI/UX Design with AI', 'UIUXAI', '6months', NULL, '2026-03-05 19:11:12'),
(9, 'Advanced Full Stack Web Design & Development with AI', 'AFSDDAI', '6months', NULL, '2026-03-05 19:11:12'),
(10, 'Master Full Stack Web Design & Development with AI', 'MFSDDAI', '1year', NULL, '2026-03-05 19:11:12'),
(11, 'AI Future Leaders Professional Certification', 'AIFLPRO', '6months', NULL, '2026-03-05 19:11:12');

-- --------------------------------------------------------

--
-- Table structure for table `course_modules`
--

CREATE TABLE `course_modules` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `batch_name` varchar(255) NOT NULL,
  `module_name` varchar(255) NOT NULL,
  `sub_heading` varchar(255) DEFAULT NULL,
  `topic_name` varchar(255) NOT NULL,
  `topic_order` int(11) NOT NULL DEFAULT 0,
  `video_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_modules`
--

INSERT INTO `course_modules` (`id`, `course_id`, `batch_name`, `module_name`, `sub_heading`, `topic_name`, `topic_order`, `video_url`, `created_at`) VALUES
(210, 2, '', 'bmnb', NULL, 'tools of ai', 1, '', '2026-03-18 14:23:55'),
(211, 9, '', 'gjbmjn', NULL, 'hnb', 1, '', '2026-03-19 08:36:45'),
(212, 6, '', '123', NULL, 'gjhgmjb', 1, '', '2026-03-20 06:23:36');

-- --------------------------------------------------------

--
-- Table structure for table `course_progress`
--

CREATE TABLE `course_progress` (
  `id` int(11) NOT NULL,
  `reg_no` varchar(50) NOT NULL,
  `topic_name` varchar(200) NOT NULL,
  `module_name` varchar(200) NOT NULL,
  `completed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_progress`
--

INSERT INTO `course_progress` (`id`, `reg_no`, `topic_name`, `module_name`, `completed_at`) VALUES
(1, '123', 'What is Domain', 'Domain & Hosting', '2026-02-26 20:52:17'),
(2, '123', 'Types of Domain', 'Domain & Hosting', '2026-02-26 20:52:21'),
(3, '123', 'What is Hosting', 'Domain & Hosting', '2026-02-26 20:52:23'),
(4, '123', 'Introduction to WordPress', 'WordPress', '2026-02-26 20:52:25'),
(5, '123', 'Themes & Plugins', 'WordPress', '2026-02-26 20:52:27'),
(6, '123', 'Creating Pages & Posts', 'WordPress', '2026-02-26 20:52:28'),
(7, '123', 'On Page SEO', 'SEO', '2026-02-26 20:52:30'),
(8, '123', 'Off Page SEO', 'SEO', '2026-02-26 20:52:34'),
(9, '123', 'Keywords Research', 'SEO', '2026-02-26 20:52:37'),
(10, '123', 'Instagram Marketing', 'Social Media', '2026-02-26 20:52:40'),
(11, '123', 'Facebook Ads', 'Social Media', '2026-02-26 20:52:43'),
(12, '123', 'Content Strategy', 'Social Media', '2026-02-26 21:06:00'),
(13, '123', 'HTML Basics', 'Web Basics', '2026-02-26 21:06:05'),
(14, '123', 'CSS Basics', 'Web Basics', '2026-02-26 21:06:09'),
(15, '123', 'How Websites Work', 'Web Basics', '2026-02-26 21:06:26'),
(16, '321', 'What is Domain', 'Domain & Hosting', '2026-02-26 21:31:54'),
(17, '321', 'Types of Domain', 'Domain & Hosting', '2026-02-26 21:31:57'),
(18, '321', 'What is Hosting', 'Domain & Hosting', '2026-02-26 21:31:59'),
(19, '989', 'What is Domain', 'Domain & Hosting', '2026-02-26 22:43:42'),
(20, '989', 'Types of Domain', 'Domain & Hosting', '2026-02-26 22:44:23'),
(21, '989', 'What is Hosting', 'Domain & Hosting', '2026-02-26 22:51:45'),
(22, '989', 'Introduction to WordPress', 'WordPress', '2026-02-26 22:52:14'),
(23, '989', 'Themes & Plugins', 'WordPress', '2026-02-27 12:25:40'),
(24, '989', 'Keywords Research', 'SEO', '2026-02-28 06:25:36'),
(25, '989', 'Creating Pages & Posts', 'WordPress', '2026-02-28 06:36:35'),
(26, '989', 'On Page SEO', 'SEO', '2026-02-28 06:36:42'),
(27, '989', 'Off Page SEO', 'SEO', '2026-02-28 06:36:44'),
(28, '999', 'What is Domain', 'Domain & Hosting', '2026-03-02 19:20:56'),
(29, '999', 'Types of Domain', 'Domain & Hosting', '2026-03-02 19:21:11'),
(30, '999', 'Instagram Marketing', 'Social Media', '2026-03-02 19:21:41'),
(31, '999', 'What is Hosting', 'Domain & Hosting', '2026-03-02 19:21:50'),
(32, 'DMGD-0226-1441', 'Domain and Hosting Setup', 'Website Development', '2026-03-06 00:14:47'),
(33, 'DMGD-0226-1441', 'Cpanel and Website Server Overview', 'Website Development', '2026-03-06 00:14:56'),
(34, 'DMGD-0226-1441', 'WordPress Installation and Setup', 'Website Development', '2026-03-06 00:15:01'),
(35, 'DMGD-0226-1441', 'Theme Installation and Customization', 'Website Development', '2026-03-06 00:15:05'),
(36, 'DMGD-0226-1441', 'Plugin Installation and Configuration', 'Website Development', '2026-03-06 00:15:07'),
(37, 'DMGD-0226-1441', 'Website Structure and Navigation Setup', 'Website Development', '2026-03-06 00:15:09'),
(38, 'DMGD-0226-1441', 'SEO Fundamentals and Search Engine Basics', 'SEO – Search Engine Optimization', '2026-03-06 12:16:32'),
(39, 'DMGD-0226-1441', 'Keyword Research Fundamentals', 'SEO – Search Engine Optimization', '2026-03-06 12:16:34'),
(40, 'DMGD-0226-1441', 'On Page SEO Optimization', 'SEO – Search Engine Optimization', '2026-03-06 12:16:36'),
(41, 'DMGD-0226-1441', 'Keyword Strategy and Search Intent', 'SEO – Search Engine Optimization', '2026-03-06 12:16:38'),
(42, 'DMGD-0226-1441', 'Content Quality Optimization and AI Content Tools', 'SEO – Search Engine Optimization', '2026-03-06 12:16:39'),
(43, 'DMGD-0226-1441', 'Website Technical SEO Optimization', 'SEO – Search Engine Optimization', '2026-03-06 12:16:41'),
(44, 'DMGD-0226-1441', 'Technical SEO Fundamentals', 'SEO – Search Engine Optimization', '2026-03-06 12:16:45'),
(45, 'DMGD-0226-1441', 'RankMath and All in One SEO Plugin Setup', 'SEO – Search Engine Optimization', '2026-03-06 12:17:01'),
(46, 'DMGD-0226-1441', 'SEO Tools Introduction Ahrefs and Semrush', 'SEO – Search Engine Optimization', '2026-03-06 12:17:07'),
(47, 'DMGD-0226-1441', 'Voice Search SEO Optimization', 'SEO – Search Engine Optimization', '2026-03-06 12:17:09'),
(48, 'DMGD-0226-1441', 'Website Structure Optimization for SEO', 'SEO – Search Engine Optimization', '2026-03-06 12:17:13'),
(49, 'DMGD-0226-1441', 'Backlink Creation Strategy', 'SEO – Search Engine Optimization', '2026-03-06 12:17:15'),
(50, 'DMGD-0226-1441', 'Off Page SEO Fundamentals', 'SEO – Search Engine Optimization', '2026-03-06 12:17:18'),
(51, 'DMGD-0226-1441', 'Competitor Backlink Analysis', 'SEO – Search Engine Optimization', '2026-03-06 12:17:20'),
(52, 'DMGD-0226-1441', 'add topic', 'Learn new ai tools to  manage websites', '2026-03-08 19:32:26'),
(53, 'DMGD-0226-1441', 'lots of things more', 'Learn new ai tools to  manage websites', '2026-03-09 00:05:10'),
(54, 'DMGD-0226-1441', 'cursur', 'Learn new ai tools to  manage websites', '2026-03-09 00:05:14'),
(55, 'DMGD-0226-1441', 'more things like  gpt', 'Learn new ai tools to  manage websites', '2026-03-09 00:05:17'),
(56, 'DMGD-0226-1441', 'ai skills', 'Learn new ai tools to  manage websites', '2026-03-09 00:05:20'),
(57, 'DMGD-0226-1441', 'claude', 'Learn new ai tools to  manage websites', '2026-03-09 00:05:25'),
(58, 'DMGD-0226-1441', 'Google Search Console Setup', 'Google Tools and Analytics', '2026-03-09 00:05:35'),
(59, 'DMGD-0226-1441', 'Google Search Console Practical Implementation', 'Google Tools and Analytics', '2026-03-09 00:05:37'),
(60, 'DMGD-0226-1441', 'Google Analytics 4 Setup', 'Google Tools and Analytics', '2026-03-09 00:05:39'),
(61, 'DMGD-0226-1441', 'Google Analytics 4 Practical Implementation', 'Google Tools and Analytics', '2026-03-09 00:06:44'),
(62, 'DMGD-0226-1441', 'Google Business Profile Setup', 'Google Tools and Analytics', '2026-03-09 00:06:46'),
(63, 'DMGD-0226-1441', 'Google PageSpeed Insights Optimization', 'Google Tools and Analytics', '2026-03-09 00:06:48'),
(64, 'DMGD-0226-1441', 'KPI Tracking and ROI Measurement', 'Google Tools and Analytics', '2026-03-09 00:06:50'),
(65, 'DMGD-0226-1441', 'Content Strategy Fundamentals', 'Content Marketing', '2026-03-09 00:06:52'),
(66, 'DMGD-0226-1441', 'Content Strategy Practical Implementation', 'Content Marketing', '2026-03-09 00:06:54'),
(67, 'DMGD-0226-1441', 'Storytelling and Content Psychology', 'Content Marketing', '2026-03-09 00:06:56'),
(68, 'DMGD-0226-1441', 'Video Creation for Marketing', 'Video Marketing', '2026-03-09 00:17:02'),
(69, 'DMGD-0226-1441', 'tools of ai', 'learn about ai', '2026-03-13 12:12:46'),
(70, '1111', 'gjhgmjb', '123', '2026-03-20 11:55:35');

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `is_preview` tinyint(1) DEFAULT 0,
  `order_no` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `module_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `course_id`, `title`, `video_url`, `is_preview`, `order_no`, `created_at`, `module_id`) VALUES
(1, 1, 'Introduction to Digital Marketing', 'https://example.com/video1', 1, 1, '2025-09-22 11:03:39', NULL),
(2, 1, 'Understanding SEO', 'https://example.com/video2', 0, 2, '2025-09-22 11:03:39', NULL),
(3, 1, 'Content Marketing Strategies', 'https://example.com/video3', 0, 3, '2025-09-22 11:03:39', NULL),
(4, 1, 'Paid Advertising (PPC)', 'https://example.com/video4', 0, 4, '2025-09-22 11:03:39', NULL),
(5, 1, 'Analytics and Reporting', 'https://example.com/video5', 0, 5, '2025-09-22 11:03:39', NULL),
(6, 2, 'Basics of Stock Market', 'https://example.com/video6', 1, 1, '2025-09-22 11:03:39', NULL),
(7, 2, 'Fundamental Analysis', 'https://example.com/video7', 0, 2, '2025-09-22 11:03:39', NULL),
(8, 2, 'Technical Analysis', 'https://example.com/video8', 0, 3, '2025-09-22 11:03:39', NULL),
(9, 2, 'Risk Management', 'https://example.com/video9', 0, 4, '2025-09-22 11:03:39', NULL),
(10, 2, 'Building a Portfolio', 'https://example.com/video10', 0, 5, '2025-09-22 11:03:39', NULL),
(11, 3, 'Introduction to HTML & CSS', 'https://example.com/video11', 1, 1, '2025-09-22 11:03:39', NULL),
(12, 3, 'Responsive Design', 'https://example.com/video12', 0, 2, '2025-09-22 11:03:39', NULL),
(13, 3, 'JavaScript for Designers', 'https://example.com/video13', 0, 3, '2025-09-22 11:03:39', NULL),
(14, 3, 'UI/UX Principles', 'https://example.com/video14', 0, 4, '2025-09-22 11:03:39', NULL),
(15, 3, 'Final Project', 'https://example.com/video15', 0, 5, '2025-09-22 11:03:39', NULL),
(16, 4, 'Facebook & Instagram Marketing', 'https://example.com/video16', 1, 1, '2025-09-22 11:03:39', NULL),
(17, 4, 'LinkedIn for Professionals', 'https://example.com/video17', 0, 2, '2025-09-22 11:03:39', NULL),
(18, 4, 'Content Strategy', 'https://example.com/video18', 0, 3, '2025-09-22 11:03:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mcq_attempts`
--

CREATE TABLE `mcq_attempts` (
  `id` int(11) NOT NULL,
  `reg_no` varchar(50) NOT NULL,
  `course_id` int(11) NOT NULL,
  `topic_name` varchar(300) NOT NULL,
  `score` int(11) DEFAULT 0,
  `total` int(11) DEFAULT 0,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mcq_attempts`
--

INSERT INTO `mcq_attempts` (`id`, `reg_no`, `course_id`, `topic_name`, `score`, `total`, `attempted_at`) VALUES
(1, 'DMGD-0226-1441', 2, 'add topic', 0, 1, '2026-03-08 14:27:16'),
(2, 'DMGD-0226-1441', 2, 'tools of ai', 1, 1, '2026-03-13 06:44:42'),
(3, '1111', 6, 'gjhgmjb', 1, 1, '2026-03-20 06:26:19');

-- --------------------------------------------------------

--
-- Table structure for table `moderation_queue`
--

CREATE TABLE `moderation_queue` (
  `id` int(11) NOT NULL,
  `content_type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `reason` text DEFAULT NULL,
  `submitted_by` int(11) DEFAULT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `status` enum('pending','approved','auto_rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `module_number` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `course_id`, `module_number`, `title`, `duration`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 'adjskdnddddddddddddddddddddddd', '4', '2026-03-24 09:21:28', '2026-03-24 09:21:28');

-- --------------------------------------------------------

--
-- Table structure for table `progress`
--

CREATE TABLE `progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `progress`
--

INSERT INTO `progress` (`id`, `user_id`, `lesson_id`, `completed`, `updated_at`) VALUES
(1, 1, 1, 1, '2025-09-22 11:03:39'),
(2, 1, 2, 1, '2025-09-22 11:03:39'),
(3, 1, 3, 1, '2025-09-22 11:03:39'),
(4, 1, 6, 1, '2025-09-22 11:03:39'),
(5, 1, 7, 1, '2025-09-22 11:03:39'),
(6, 1, 11, 1, '2025-09-22 11:03:39'),
(7, 1, 16, 1, '2025-09-22 11:03:39'),
(8, 1, 17, 1, '2025-09-22 11:03:39'),
(9, 1, 18, 1, '2025-09-22 11:03:39');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `payment_id` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `purchased_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `user_id`, `course_id`, `payment_id`, `amount`, `status`, `purchased_at`) VALUES
(1, 1, 1, 'pay_abc123', 2999.00, 'completed', '2024-05-10 04:30:00'),
(2, 1, 2, 'pay_def456', 4999.00, 'completed', '2024-05-11 05:30:00'),
(3, 1, 3, 'pay_ghi789', 1999.00, 'completed', '2024-05-12 06:30:00'),
(4, 1, 4, 'pay_jkl012', 2499.00, 'completed', '2024-05-13 07:30:00'),
(5, 2, 1, 'pay_mno345', 2999.00, 'completed', '2024-05-15 08:30:00'),
(6, 5, 2, 'pay_pqr678', 4999.00, 'completed', '2024-05-16 09:30:00'),
(17, 4, 2, NULL, 59.00, 'completed', '2025-09-25 08:24:49'),
(18, 4, 2, NULL, 4999.00, 'completed', '2025-09-25 08:34:18'),
(21, 17, 1, NULL, 2999.00, 'completed', '2025-09-25 10:50:17'),
(22, 17, 2, NULL, 4999.00, 'completed', '2025-09-25 10:50:42'),
(27, 23, 1, NULL, 2999.00, 'completed', '2025-10-06 10:42:53');

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` int(11) NOT NULL,
  `referrer_id` int(11) NOT NULL,
  `referred_user_id` int(11) NOT NULL,
  `commission` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `referrals`
--

INSERT INTO `referrals` (`id`, `referrer_id`, `referred_user_id`, `commission`, `created_at`) VALUES
(1, 1, 2, 750.00, '2024-05-15 08:30:00'),
(2, 1, 5, 1250.00, '2024-05-16 09:30:00'),
(4, 4, 13, 0.00, '2025-09-25 07:02:03'),
(14, 4, 23, 0.00, '2025-10-06 10:40:05');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `device_info` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `session_token`, `device_info`, `ip_address`, `created_at`, `expires_at`) VALUES
(4, 25, 'ddd8156f99d684b2cd83a492123574fa2db4324289b9ed99fe46986f8b903e47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '::1', '2026-03-24 09:15:03', '2026-03-25 04:45:03');

-- --------------------------------------------------------

--
-- Table structure for table `student_enrollments`
--

CREATE TABLE `student_enrollments` (
  `id` int(11) NOT NULL,
  `reg_no` varchar(50) NOT NULL,
  `coursename` text NOT NULL,
  `batch_no` varchar(100) NOT NULL,
  `startingdate` date DEFAULT NULL,
  `completeddate` date DEFAULT NULL,
  `addonvalue` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_enrollments`
--

INSERT INTO `student_enrollments` (`id`, `reg_no`, `coursename`, `batch_no`, `startingdate`, `completeddate`, `addonvalue`, `created_at`) VALUES
(1, '1234567889', 'UI/UX Design with AI (UIUXAI)', 'Batch 3 Afternoon', '2026-03-19', '2026-03-26', 'klnm', '2026-03-19 15:41:53'),
(2, '1234567889', 'Graphic Design (Video Editing) with AI (GDVAI)', 'jfkd', '2026-03-19', '2026-12-02', 'mm,m,m', '2026-03-19 15:41:53'),
(3, '999999', 'Graphic Design (Photo & Video Editing) with AI & Generative AI Automation (GDPVAI)', 'Batch 3 Afternoon', '2026-03-19', '2026-03-24', 'dmlcmsd', '2026-03-19 15:46:51'),
(4, '12345', 'Master Full Stack Web Design & Development with AI & AI Automation (MFSDDAI)', 'jfkd', '2026-03-19', '2026-04-10', 'photoshop', '2026-03-19 15:49:39'),
(7, '989', 'Master Data Analytics with Generative AI (MDAAI)', 'Batch 3 Afternoon', '2026-03-19', '2026-03-18', 'dmlcmsd', '2026-03-19 16:03:34'),
(8, '989', 'Graphic Design (Video Editing) with AI (GDVAI)', 'jfkd', '2026-03-20', '2026-03-12', 'mm,m,m', '2026-03-19 16:03:34'),
(10, '1111', 'Master Digital Marketing with AI & Automation (MDMAI)', 'mo', '2026-03-19', '2026-07-02', 'dmlcmsd', '2026-03-20 06:23:09'),
(11, 'sdsd', 'Advanced Digital Marketing with AI (ADMAI)', 'kzmkz', '2026-03-25', '2026-03-13', ',md,', '2026-03-25 04:03:28'),
(12, '434', 'Master Digital Marketing with AI & Automation (MDMAI)', 'kzmkz', '2026-04-02', '2026-03-30', 'photoshop', '2026-04-02 07:41:45');

-- --------------------------------------------------------

--
-- Table structure for table `sub_admins`
--

CREATE TABLE `sub_admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sub_admins`
--

INSERT INTO `sub_admins` (`id`, `name`, `username`, `password`, `batch_id`, `created_at`) VALUES
(8, 'mohan', 'admin1234', '$2y$10$2op8cJtlkyOBkvpGzCkK5eSFaJ.Xj/N69nJlnUxlqdP89sroEBKLW', 0, '2026-03-21 12:15:48'),
(9, 'ff', 'df', '$2y$10$dGAbc3VDLSB5WkNV9ruZG.wC06ABYsBnKtg/J0bhxhc8yGrTGlN2S', 0, '2026-04-02 07:42:29');

-- --------------------------------------------------------

--
-- Table structure for table `sub_admin_batches`
--

CREATE TABLE `sub_admin_batches` (
  `id` int(11) NOT NULL,
  `sub_admin_id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sub_admin_batches`
--

INSERT INTO `sub_admin_batches` (`id`, `sub_admin_id`, `batch_id`, `created_at`) VALUES
(46, 8, 29, '2026-04-02 07:41:08'),
(47, 8, 30, '2026-04-02 07:41:08'),
(48, 9, 29, '2026-04-02 07:42:29'),
(49, 9, 30, '2026-04-02 07:42:29');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `topic_assignments`
--

CREATE TABLE `topic_assignments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `module_name` varchar(300) NOT NULL,
  `topic_name` varchar(300) NOT NULL,
  `title` varchar(300) NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `topic_assignments`
--

INSERT INTO `topic_assignments` (`id`, `course_id`, `module_name`, `topic_name`, `title`, `file_path`, `instructions`, `created_at`) VALUES
(1, 2, 'Website Development', 'Domain and Hosting Setup', 'Domain and Hosting Setup - MCQ Assignment', 'uploads/assignments/Domain_and_Hosting_Setup_MCQ_Assignment.pdf', 'Read all questions carefully. Answer each MCQ and submit your response. This assignment covers the basics of domain names, web hosting, DNS and nameservers.', '2026-03-05 19:45:32'),
(2, 2, 'Learn new ai tools to  manage websites', 'add topic', 'AI TOPIC', 'uploads/assignments/assign_2_c8e86357d2455dd8333d89785d4e388d.pdf', 'SOLVE THIS PROPER AND SUBMIT IT AS SOON AS A POSSIBLE', '2026-03-08 13:39:46'),
(3, 2, 'learn about ai', 'tools of ai', 'Xz', 'uploads/assignments/1773842472_Malli-CV-Jon.pdf', 'X', '2026-03-18 14:01:12'),
(4, 9, 'gjbmjn', 'hnb', 'kk,', 'uploads/assignments/1773909487_Malli-CV-Jon.pdf', 'hkjh', '2026-03-19 08:38:07'),
(5, 6, '123', 'gjhgmjb', 'jnj,mnnm,nkhhjj', 'uploads/assignments/1773987855_Malli-CV-Jon.pdf', 'jhjk', '2026-03-20 06:24:15');

-- --------------------------------------------------------

--
-- Table structure for table `topic_mcq`
--

CREATE TABLE `topic_mcq` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `module_name` varchar(300) NOT NULL,
  `topic_name` varchar(300) NOT NULL,
  `question` text NOT NULL,
  `option_a` varchar(500) NOT NULL,
  `option_b` varchar(500) NOT NULL,
  `option_c` varchar(500) NOT NULL,
  `option_d` varchar(500) NOT NULL,
  `correct_ans` char(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `topic_mcq`
--

INSERT INTO `topic_mcq` (`id`, `course_id`, `module_name`, `topic_name`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_ans`, `created_at`) VALUES
(1, 2, 'Website Development', 'Domain and Hosting Setup', 'What is a domain name?', 'A computer software', 'Website address (like google.com)', 'A hosting plan', 'A coding language', 'B', '2026-03-05 19:45:12'),
(2, 2, 'Website Development', 'Domain and Hosting Setup', 'Which of the following is an example of a domain name?', '192.168.1.1', 'www.amazon.com', 'Windows 10', 'Cpanel', 'B', '2026-03-05 19:45:12'),
(3, 2, 'Website Development', 'Domain and Hosting Setup', 'What is web hosting?', 'A place to design logo', 'A service to store website files online', 'A type of domain', 'A social media platform', 'B', '2026-03-05 19:45:12'),
(4, 2, 'Website Development', 'Domain and Hosting Setup', 'Why do we need hosting for a website?', 'To buy a laptop', 'To store website data and make it live on internet', 'To create social media account', 'To edit photos', 'B', '2026-03-05 19:45:12'),
(5, 2, 'Website Development', 'Domain and Hosting Setup', 'Which one connects domain name with hosting?', 'SEO', 'DNS', 'Canva', 'HTML', 'B', '2026-03-05 19:45:12'),
(6, 2, 'Website Development', 'Domain and Hosting Setup', 'What does DNS stand for?', 'Domain Name System', 'Data Network Server', 'Digital Name Service', 'Domain Network Setup', 'A', '2026-03-05 19:45:12'),
(7, 2, 'Website Development', 'Domain and Hosting Setup', 'Where do we update nameservers?', 'In YouTube', 'In domain provider account', 'In MS Word', 'In Facebook', 'B', '2026-03-05 19:45:12'),
(8, 2, 'Website Development', 'Domain and Hosting Setup', 'Which of the following is a hosting company?', 'Google Chrome', 'Hostinger', 'Instagram', 'MS Excel', 'B', '2026-03-05 19:45:12'),
(9, 2, 'Website Development', 'Domain and Hosting Setup', 'What is shared hosting?', 'One server used by many websites', 'One website per server', 'No server needed', 'Offline hosting', 'A', '2026-03-05 19:45:12'),
(10, 2, 'Website Development', 'Domain and Hosting Setup', 'After connecting domain and hosting, what is the next step?', 'Buy mobile phone', 'Install website (like WordPress)', 'Delete domain', 'Stop hosting', 'B', '2026-03-05 19:45:12'),
(11, 2, 'Learn new ai tools to  manage websites', 'add topic', 'jbadjknd,mn,dc', 'ds,md', ',dm,', 's,dm', 'dlm', 'D', '2026-03-08 14:26:56'),
(12, 2, 'learn about ai', 'tools of ai', 'what is the best use of ai', 'Developing skills', 'Making working life easy', 'A', 'D', 'A', '2026-03-13 06:44:15'),
(13, 2, 'learn about ai', 'tools of ai', 'jnxknx', 'xzx', 'zx', 'xz', 'zX', 'B', '2026-03-18 14:00:39'),
(14, 2, 'bmnb', 'tools of ai', 'nb', 'frs', 'dfs', 'sfd', 'sf', 'C', '2026-03-18 14:24:09'),
(15, 9, 'gjbmjn', 'hnb', 'hgh', 'fgf', 'hmj', 'jhkj', 'jhjm', 'C', '2026-03-19 08:37:06'),
(16, 6, '123', 'gjhgmjb', 'n,nm', 'n,mn', 'jhkjh', 'n,mn', 'kn,', 'B', '2026-03-20 06:23:56');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'INR',
  `gateway` varchar(50) DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `amount`, `currency`, `gateway`, `status`, `created_at`) VALUES
(1, 1, 2999.00, 'INR', 'Stripe', 'completed', '2024-05-10 04:30:00'),
(2, 1, 4999.00, 'INR', 'Stripe', 'completed', '2024-05-11 05:30:00'),
(3, 1, 750.00, 'INR', 'Affiliate Commission', 'completed', '2024-05-15 08:35:00'),
(4, 1, 1250.00, 'INR', 'Affiliate Commission', 'completed', '2024-05-16 09:35:00'),
(5, 1, -15000.00, 'INR', 'Withdrawal', 'completed', '2024-05-20 03:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `reg_no` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','student') DEFAULT 'student',
  `terms_accepted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `reg_no`, `email`, `password`, `role`, `terms_accepted`) VALUES
(62, 'nmnm', 'sdsd', 'dikj@j45254', 'sjdn,snd', 'student', 0),
(63, '434', '434', 'dhhf@gmail.com', '123456', 'student', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_details`
--

CREATE TABLE `user_details` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `phoneno` varchar(10) NOT NULL,
  `whatsapp` varchar(10) NOT NULL,
  `gmail` varchar(255) NOT NULL,
  `address` varchar(500) DEFAULT NULL,
  `qualification` varchar(100) NOT NULL,
  `dob` date NOT NULL,
  `coursename` varchar(255) NOT NULL,
  `startingdate` date NOT NULL,
  `completeddate` date NOT NULL,
  `addonvalue` varchar(100) NOT NULL,
  `parentname` varchar(255) NOT NULL,
  `parentsno` varchar(10) NOT NULL,
  `reg_no` varchar(50) NOT NULL,
  `batch_no` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_details`
--

INSERT INTO `user_details` (`id`, `name`, `gender`, `phoneno`, `whatsapp`, `gmail`, `address`, `qualification`, `dob`, `coursename`, `startingdate`, `completeddate`, `addonvalue`, `parentname`, `parentsno`, `reg_no`, `batch_no`) VALUES
(68, 'nmnm', 'Male', '8509480958', '4859485094', 'dikj@j45254', 'nd,nd,ma', 'Pursuing Graduation (Non-IT)', '2026-03-07', 'Advanced Digital Marketing with AI (ADMAI)', '2026-03-25', '2026-03-13', ',md,', 'dm,am', '9877383939', 'sdsd', 'kzmkz'),
(69, '434', 'Female', '4344444444', '4344444444', 'dhhf@gmail.com', 'adress update  doing it', 'Pursuing Graduation (Non-IT)', '2026-04-03', 'Master Digital Marketing with AI & Automation (MDMAI)', '2026-04-02', '2026-03-30', 'photoshop', '434', '8865574458', '434', 'kzmkz');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `affiliates`
--
ALTER TABLE `affiliates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_reg_topic` (`reg_no`,`topic_name`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_attendance` (`reg_no`,`batch_no`,`date`);

--
-- Indexes for table `batches`
--
ALTER TABLE `batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `class_sessions`
--
ALTER TABLE `class_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_batch_date` (`date`),
  ADD KEY `idx_batch_id` (`batch_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_modules`
--
ALTER TABLE `course_modules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_progress`
--
ALTER TABLE `course_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_progress` (`reg_no`,`topic_name`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lesson_course` (`course_id`);

--
-- Indexes for table `mcq_attempts`
--
ALTER TABLE `mcq_attempts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_attempt` (`reg_no`,`topic_name`);

--
-- Indexes for table `moderation_queue`
--
ALTER TABLE `moderation_queue`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `progress`
--
ALTER TABLE `progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`lesson_id`),
  ADD KEY `lesson_id` (`lesson_id`),
  ADD KEY `idx_progress_user` (`user_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_purchase_user` (`user_id`),
  ADD KEY `idx_purchase_course` (`course_id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `referrer_id` (`referrer_id`),
  ADD KEY `referred_user_id` (`referred_user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `student_enrollments`
--
ALTER TABLE `student_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reg_no` (`reg_no`),
  ADD KEY `idx_batch_no` (`batch_no`);

--
-- Indexes for table `sub_admins`
--
ALTER TABLE `sub_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `sub_admin_batches`
--
ALTER TABLE `sub_admin_batches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_sub_batch` (`sub_admin_id`,`batch_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `topic_assignments`
--
ALTER TABLE `topic_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_topic_assign` (`course_id`,`topic_name`);

--
-- Indexes for table `topic_mcq`
--
ALTER TABLE `topic_mcq`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transaction_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reg_no` (`reg_no`);

--
-- Indexes for table `user_details`
--
ALTER TABLE `user_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reg_no` (`reg_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `affiliates`
--
ALTER TABLE `affiliates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=178;

--
-- AUTO_INCREMENT for table `batches`
--
ALTER TABLE `batches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `class_sessions`
--
ALTER TABLE `class_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=586;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `course_modules`
--
ALTER TABLE `course_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=213;

--
-- AUTO_INCREMENT for table `course_progress`
--
ALTER TABLE `course_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `mcq_attempts`
--
ALTER TABLE `mcq_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `moderation_queue`
--
ALTER TABLE `moderation_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `progress`
--
ALTER TABLE `progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `student_enrollments`
--
ALTER TABLE `student_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sub_admins`
--
ALTER TABLE `sub_admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sub_admin_batches`
--
ALTER TABLE `sub_admin_batches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `topic_assignments`
--
ALTER TABLE `topic_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `topic_mcq`
--
ALTER TABLE `topic_mcq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `user_details`
--
ALTER TABLE `user_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
