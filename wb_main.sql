-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 23, 2026 at 01:37 PM
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
-- Database: `wb_main`
--

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `bill_id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `total_room_cost` decimal(10,2) DEFAULT 0.00,
  `total_orders_cost` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) DEFAULT 0.00,
  `is_paid` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`bill_id`, `rental_id`, `total_room_cost`, `total_orders_cost`, `grand_total`, `is_paid`, `created_at`) VALUES
(1, 1, 75.00, 0.00, 75.00, 1, '2026-01-31 10:30:13'),
(2, 2, 75.00, 0.00, 75.00, 1, '2026-01-31 10:30:28'),
(3, 3, 150.00, 0.00, 150.00, 1, '2026-01-31 10:34:14'),
(4, 4, 150.00, 0.00, 150.00, 1, '2026-01-31 22:16:49'),
(5, 5, 150.00, 180.00, 330.00, 1, '2026-01-31 22:57:29'),
(6, 6, 150.00, 0.00, 150.00, 1, '2026-02-01 04:17:15'),
(7, 7, 450.00, 0.00, 450.00, 1, '2026-02-01 04:17:28'),
(8, 8, 825.00, 70.00, 895.00, 1, '2026-02-01 04:27:41'),
(9, 9, 450.00, 0.00, 450.00, 1, '2026-02-01 05:06:40'),
(10, 10, 300.00, 0.00, 300.00, 1, '2026-02-01 05:10:03'),
(11, 11, 150.00, 590.00, 740.00, 1, '2026-02-01 06:37:17'),
(12, 12, 150.00, 0.00, 150.00, 1, '2026-02-01 06:57:30'),
(13, 13, 150.00, 0.00, 150.00, 1, '2026-02-01 15:28:52'),
(14, 14, 150.00, 0.00, 150.00, 1, '2026-02-01 15:29:01'),
(15, 15, 75.00, 0.00, 75.00, 1, '2026-02-01 15:29:02'),
(16, 16, 150.00, 0.00, 150.00, 1, '2026-02-01 15:41:23'),
(17, 17, 150.00, 0.00, 150.00, 1, '2026-02-01 15:46:55'),
(18, 18, 150.00, 0.00, 150.00, 1, '2026-02-01 15:52:49'),
(19, 19, 150.00, 0.00, 150.00, 1, '2026-02-01 15:54:37'),
(20, 20, 150.00, 0.00, 150.00, 1, '2026-02-01 16:05:27'),
(21, 21, 150.00, 0.00, 150.00, 1, '2026-02-01 16:06:35'),
(22, 22, 150.00, 0.00, 150.00, 1, '2026-02-01 23:19:53'),
(23, 23, 225.00, 110.00, 335.00, 1, '2026-02-01 23:25:10'),
(24, 24, 150.00, 0.00, 150.00, 1, '2026-02-02 00:30:06'),
(25, 25, 150.00, 0.00, 150.00, 1, '2026-02-02 00:31:36'),
(26, 26, 150.00, 0.00, 150.00, 1, '2026-02-02 00:35:44'),
(27, 27, 150.00, 0.00, 150.00, 1, '2026-02-02 00:38:20'),
(28, 28, 150.00, 0.00, 150.00, 1, '2026-02-02 00:42:16'),
(29, 29, 225.00, 60.00, 285.00, 1, '2026-02-02 03:42:10'),
(30, 30, 150.00, 0.00, 150.00, 1, '2026-02-02 03:48:07'),
(31, 31, 150.00, 0.00, 150.00, 1, '2026-02-02 03:51:12'),
(32, 32, 150.00, 0.00, 150.00, 1, '2026-02-02 03:54:39'),
(33, 33, 75.00, 0.00, 75.00, 1, '2026-02-02 10:47:25'),
(34, 34, 75.00, 285.00, 360.00, 1, '2026-02-02 11:10:58'),
(35, 35, 150.00, 0.00, 150.00, 1, '2026-02-02 11:58:59'),
(36, 36, 150.00, 0.00, 150.00, 1, '2026-02-02 12:16:40'),
(37, 37, 150.00, 0.00, 150.00, 1, '2026-02-02 12:17:37'),
(38, 38, 150.00, 0.00, 150.00, 1, '2026-02-04 06:15:29'),
(39, 39, 150.00, 0.00, 150.00, 1, '2026-02-09 00:15:24'),
(40, 40, 150.00, 0.00, 150.00, 1, '2026-02-09 00:21:10'),
(41, 41, 150.00, 0.00, 150.00, 1, '2026-02-09 00:24:51'),
(42, 42, 150.00, 0.00, 150.00, 1, '2026-02-09 01:04:14'),
(43, 43, 200.00, 0.00, 200.00, 1, '2026-02-09 01:19:15'),
(44, 44, 150.00, 0.00, 150.00, 1, '2026-02-09 10:29:51'),
(45, 45, 150.00, 0.00, 150.00, 1, '2026-02-09 10:30:33'),
(46, 46, 150.00, 0.00, 150.00, 1, '2026-02-09 11:33:47'),
(47, 47, 75.00, 0.00, 75.00, 1, '2026-02-18 05:14:09'),
(48, 48, 200.00, 0.00, 200.00, 1, '2026-02-18 05:21:57'),
(49, 49, 150.00, 0.00, 150.00, 1, '2026-02-18 05:22:32'),
(50, 50, 150.00, 0.00, 150.00, 1, '2026-02-18 05:22:59'),
(51, 51, 75.00, 0.00, 75.00, 1, '2026-02-22 14:21:48'),
(52, 52, 150.00, 0.00, 150.00, 1, '2026-02-22 14:31:00'),
(53, 53, 150.00, 0.00, 150.00, 1, '2026-02-22 14:46:16'),
(54, 54, 150.00, 0.00, 150.00, 1, '2026-02-22 22:43:18'),
(55, 55, 150.00, 0.00, 150.00, 0, '2026-02-23 03:40:05'),
(56, 56, 150.00, 0.00, 150.00, 1, '2026-02-23 07:39:11');

-- --------------------------------------------------------

--
-- Table structure for table `cleaning_logs`
--

CREATE TABLE `cleaning_logs` (
  `cleaning_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `cleaned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cleaning_logs`
--

INSERT INTO `cleaning_logs` (`cleaning_id`, `room_id`, `staff_id`, `cleaned_at`) VALUES
(1, 1, 3, '2026-01-31 15:57:20'),
(2, 2, 3, '2026-01-31 15:57:29'),
(3, 5, 3, '2026-01-31 23:26:52'),
(4, 10, 3, '2026-01-31 23:26:59'),
(5, 9, 3, '2026-01-31 23:27:01'),
(6, 3, 3, '2026-01-31 23:27:03'),
(7, 2, 3, '2026-01-31 23:27:05'),
(8, 1, 3, '2026-01-31 23:27:07'),
(9, 4, 3, '2026-01-31 23:27:09'),
(10, 5, 3, '2026-01-31 23:27:10'),
(11, 9, 3, '2026-01-31 23:27:12'),
(12, 10, 3, '2026-01-31 23:27:13'),
(13, 2, 3, '2026-02-01 06:36:45'),
(14, 1, 3, '2026-02-01 06:36:46'),
(15, 3, 3, '2026-02-01 06:36:47'),
(16, 4, 3, '2026-02-01 06:36:47'),
(17, 5, 3, '2026-02-01 06:36:49'),
(18, 1, 3, '2026-02-01 15:47:54'),
(19, 2, 3, '2026-02-01 15:47:56'),
(20, 3, 3, '2026-02-01 15:47:57'),
(21, 4, 3, '2026-02-01 15:47:59'),
(22, 5, 3, '2026-02-01 15:48:00'),
(23, 7, 3, '2026-02-01 15:48:02'),
(24, 6, 3, '2026-02-01 15:48:03'),
(25, 1, 3, '2026-02-01 23:20:50'),
(26, 2, 3, '2026-02-01 23:20:52'),
(27, 3, 3, '2026-02-01 23:20:53'),
(28, 4, 3, '2026-02-01 23:20:54'),
(29, 5, 3, '2026-02-01 23:20:55'),
(30, 1, 3, '2026-02-02 00:44:35'),
(31, 2, 3, '2026-02-02 03:44:26'),
(32, 3, 3, '2026-02-02 03:44:34'),
(33, 4, 3, '2026-02-02 03:44:37'),
(34, 5, 3, '2026-02-02 03:44:41'),
(35, 6, 3, '2026-02-02 03:44:43'),
(36, 3, 3, '2026-02-02 03:51:49'),
(37, 2, 3, '2026-02-02 03:51:53'),
(38, 1, 3, '2026-02-02 03:51:56'),
(39, 1, 3, '2026-02-02 03:52:45'),
(40, 1, 3, '2026-02-02 03:52:51'),
(41, 1, 3, '2026-02-02 11:10:34'),
(42, 2, 3, '2026-02-02 11:10:36'),
(43, 5, 3, '2026-02-02 11:28:43'),
(44, 5, 3, '2026-02-02 11:28:52'),
(45, 1, 3, '2026-02-02 12:13:24'),
(46, 1, 3, '2026-02-09 10:29:31'),
(47, 2, 3, '2026-02-09 10:29:34'),
(48, 3, 3, '2026-02-09 10:29:36'),
(49, 4, 3, '2026-02-09 10:29:38'),
(50, 1, 3, '2026-02-18 05:19:04'),
(51, 1, 4, '2026-02-22 14:01:35'),
(52, 1, 4, '2026-02-22 14:01:40'),
(53, 2, 4, '2026-02-22 14:01:42'),
(54, 3, 4, '2026-02-22 14:01:43'),
(55, 16, 4, '2026-02-22 14:02:19'),
(56, 15, 4, '2026-02-22 14:30:58'),
(57, 15, 4, '2026-02-22 14:46:14'),
(58, 15, 4, '2026-02-22 22:43:15'),
(59, 15, 4, '2026-02-23 03:43:24'),
(60, 1, 4, '2026-02-23 03:43:26'),
(61, 4, 4, '2026-02-23 03:43:28'),
(62, 5, 4, '2026-02-23 03:43:30'),
(63, 8, 4, '2026-02-23 07:39:27');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `ordered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('NEW','PREPARING','READY','READY_TO_DELIVER','DELIVERING','DELIVERED') NOT NULL DEFAULT 'NEW',
  `amount_tendered` decimal(10,2) DEFAULT NULL,
  `change_amount` decimal(10,2) DEFAULT NULL,
  `prepared_by` int(11) DEFAULT NULL,
  `prepared_at` datetime DEFAULT NULL,
  `assigned_staff_id` int(11) DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `rental_id`, `ordered_at`, `status`, `amount_tendered`, `change_amount`, `prepared_by`, `prepared_at`, `assigned_staff_id`, `assigned_at`) VALUES
(1, 5, '2026-01-31 23:03:16', 'DELIVERED', NULL, NULL, NULL, '2026-02-02 08:06:14', 3, '2026-02-02 07:57:39'),
(2, 5, '2026-01-31 23:03:26', 'DELIVERED', NULL, NULL, NULL, '2026-02-02 07:52:43', 3, '2026-02-02 07:52:30'),
(3, 8, '2026-02-01 04:52:00', 'DELIVERED', NULL, NULL, NULL, '2026-02-02 07:45:09', 3, '2026-02-02 07:45:05'),
(4, 11, '2026-02-01 06:37:37', 'DELIVERED', NULL, NULL, NULL, '2026-02-02 07:45:01', 3, '2026-02-02 07:41:38'),
(5, 11, '2026-02-01 06:40:11', 'DELIVERED', NULL, NULL, NULL, '2026-02-02 08:06:42', 3, '2026-02-02 08:06:36'),
(6, 11, '2026-02-01 06:56:30', 'DELIVERED', NULL, NULL, NULL, '2026-02-02 07:54:33', 3, '2026-02-02 07:54:06'),
(7, 23, '2026-02-01 23:25:34', 'DELIVERED', NULL, NULL, NULL, '2026-02-02 08:06:53', 3, '2026-02-02 08:06:47'),
(8, 29, '2026-02-02 03:42:55', 'DELIVERED', NULL, NULL, NULL, '2026-02-02 11:44:09', 3, '2026-02-02 11:43:45'),
(9, 34, '2026-02-02 11:17:25', 'DELIVERED', NULL, NULL, NULL, '2026-02-02 19:38:00', 3, '2026-02-02 19:37:54'),
(10, 34, '2026-02-02 11:38:31', 'DELIVERED', NULL, NULL, NULL, '2026-02-02 19:44:13', 3, '2026-02-02 19:43:47'),
(11, 34, '2026-02-02 11:44:33', 'DELIVERED', NULL, NULL, NULL, '2026-02-02 20:59:44', 3, '2026-02-02 20:59:35'),
(12, 34, '2026-02-02 11:48:02', 'DELIVERED', NULL, NULL, NULL, '2026-02-02 20:59:32', 3, '2026-02-02 20:59:25');

-- --------------------------------------------------------

--
-- Table structure for table `order_audit`
--

CREATE TABLE `order_audit` (
  `audit_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `action` varchar(64) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `meta` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_audit`
--

INSERT INTO `order_audit` (`audit_id`, `order_id`, `action`, `user_id`, `role_id`, `meta`, `created_at`) VALUES
(1, 1, 'CREATED', 6, 4, '{\"order_total\":110,\"items_count\":3}', '2026-02-01 07:03:16'),
(2, 2, 'CREATED', 6, 4, '{\"order_total\":70,\"items_count\":1}', '2026-02-01 07:03:26'),
(3, 3, 'CREATED', 8, 4, '{\"order_total\":70,\"items_count\":2}', '2026-02-01 12:52:00'),
(4, 4, 'CREATED', 6, 4, '{\"order_total\":180,\"items_count\":3}', '2026-02-01 14:37:37'),
(5, 5, 'CREATED', 6, 4, '{\"order_total\":180,\"items_count\":3}', '2026-02-01 14:40:11'),
(6, 6, 'CREATED', 6, 4, '{\"order_total\":230,\"items_count\":4}', '2026-02-01 14:56:30'),
(7, 7, 'CREATED', 6, 4, '{\"order_total\":110,\"items_count\":2}', '2026-02-02 07:25:34'),
(8, 4, 'CLAIMED', 3, 2, '{\"action\":\"CLAIM\",\"assigned_to\":3}', '2026-02-02 07:41:38'),
(9, 4, 'STATUS_CHANGE', 3, 2, '{\"from\":\"PREPARING\",\"to\":\"READY\"}', '2026-02-02 07:42:23'),
(10, 4, 'STATUS_CHANGE', 3, 2, '{\"from\":\"READY\",\"to\":\"DELIVERED\"}', '2026-02-02 07:45:01'),
(11, 3, 'CLAIMED', 3, 2, '{\"action\":\"CLAIM\",\"assigned_to\":3}', '2026-02-02 07:45:05'),
(12, 3, 'STATUS_CHANGE', 3, 2, '{\"from\":\"PREPARING\",\"to\":\"READY\"}', '2026-02-02 07:45:07'),
(13, 3, 'STATUS_CHANGE', 3, 2, '{\"from\":\"READY\",\"to\":\"DELIVERED\"}', '2026-02-02 07:45:09'),
(14, 2, 'CLAIMED', 3, 2, '{\"action\":\"CLAIM\",\"assigned_to\":3}', '2026-02-02 07:52:30'),
(15, 2, 'STATUS_CHANGE', 3, 2, '{\"from\":\"PREPARING\",\"to\":\"READY\"}', '2026-02-02 07:52:35'),
(16, 2, 'STATUS_CHANGE', 3, 2, '{\"from\":\"READY\",\"to\":\"DELIVERED\"}', '2026-02-02 07:52:43'),
(17, 6, 'CLAIMED', 3, 2, '{\"action\":\"CLAIM\",\"assigned_to\":3}', '2026-02-02 07:54:06'),
(18, 6, 'STATUS_CHANGE', 3, 2, '{\"from\":\"PREPARING\",\"to\":\"READY\"}', '2026-02-02 07:54:09'),
(19, 6, 'STATUS_CHANGE', 3, 2, '{\"from\":\"READY\",\"to\":\"DELIVERED\"}', '2026-02-02 07:54:33'),
(20, 1, 'CLAIMED', 3, 2, '{\"action\":\"CLAIM\",\"assigned_to\":3}', '2026-02-02 07:57:39'),
(21, 1, 'STATUS_CHANGE', 3, 2, '{\"from\":\"PREPARING\",\"to\":\"READY\"}', '2026-02-02 08:06:08'),
(22, 1, 'STATUS_CHANGE', 3, 2, '{\"from\":\"READY\",\"to\":\"DELIVERED\"}', '2026-02-02 08:06:14'),
(23, 5, 'CLAIMED', 3, 2, '{\"action\":\"CLAIM\",\"assigned_to\":3}', '2026-02-02 08:06:36'),
(24, 5, 'STATUS_CHANGE', 3, 2, '{\"from\":\"PREPARING\",\"to\":\"READY\"}', '2026-02-02 08:06:38'),
(25, 5, 'STATUS_CHANGE', 3, 2, '{\"from\":\"READY\",\"to\":\"DELIVERED\"}', '2026-02-02 08:06:42'),
(26, 7, 'CLAIMED', 3, 2, '{\"action\":\"CLAIM\",\"assigned_to\":3}', '2026-02-02 08:06:47'),
(27, 7, 'STATUS_CHANGE', 3, 2, '{\"from\":\"PREPARING\",\"to\":\"READY\"}', '2026-02-02 08:06:50'),
(28, 7, 'STATUS_CHANGE', 3, 2, '{\"from\":\"READY\",\"to\":\"DELIVERED\"}', '2026-02-02 08:06:53'),
(29, 8, 'CREATED', 6, 4, '{\"order_total\":60,\"items_count\":1}', '2026-02-02 11:42:55'),
(30, 8, 'CLAIMED', 3, 2, '{\"action\":\"CLAIM\",\"assigned_to\":3}', '2026-02-02 11:43:45'),
(31, 8, 'STATUS_CHANGE', 3, 2, '{\"from\":\"PREPARING\",\"to\":\"READY\"}', '2026-02-02 11:44:01'),
(32, 8, 'STATUS_CHANGE', 3, 2, '{\"from\":\"READY\",\"to\":\"DELIVERED\"}', '2026-02-02 11:44:09'),
(33, 9, 'CREATED', 6, 4, '{\"order_total\":20,\"items_count\":1}', '2026-02-02 19:17:25'),
(34, 9, 'CLAIMED', 3, 2, '{\"action\":\"CLAIM\",\"assigned_to\":3}', '2026-02-02 19:37:54'),
(35, 9, 'STATUS_CHANGE', 3, 2, '{\"from\":\"PREPARING\",\"to\":\"READY\"}', '2026-02-02 19:37:57'),
(36, 9, 'STATUS_CHANGE', 3, 2, '{\"from\":\"READY\",\"to\":\"DELIVERED\"}', '2026-02-02 19:38:00'),
(37, 10, 'CREATED', 6, 4, '{\"order_total\":130,\"items_count\":2}', '2026-02-02 19:38:31'),
(38, 10, 'CLAIMED', 3, 2, '{\"action\":\"CLAIM\",\"assigned_to\":3}', '2026-02-02 19:43:47'),
(39, 10, 'STATUS_CHANGE', 3, 2, '{\"from\":\"PREPARING\",\"to\":\"READY\"}', '2026-02-02 19:44:05'),
(40, 10, 'STATUS_CHANGE', 3, 2, '{\"from\":\"READY\",\"to\":\"DELIVERED\"}', '2026-02-02 19:44:13'),
(41, 11, 'CREATED', 6, 4, '{\"order_total\":100,\"items_count\":1}', '2026-02-02 19:44:33'),
(42, 12, 'CREATED', 6, 4, '{\"order_total\":35,\"items_count\":1}', '2026-02-02 19:48:02'),
(43, 12, 'CLAIMED', 3, 2, '{\"action\":\"CLAIM\",\"assigned_to\":3}', '2026-02-02 20:59:25'),
(44, 12, 'STATUS_CHANGE', 3, 2, '{\"from\":\"PREPARING\",\"to\":\"READY\"}', '2026-02-02 20:59:28'),
(45, 12, 'STATUS_CHANGE', 3, 2, '{\"from\":\"READY\",\"to\":\"DELIVERED\"}', '2026-02-02 20:59:32'),
(46, 11, 'CLAIMED', 3, 2, '{\"action\":\"CLAIM\",\"assigned_to\":3}', '2026-02-02 20:59:35'),
(47, 11, 'STATUS_CHANGE', 3, 2, '{\"from\":\"PREPARING\",\"to\":\"READY\"}', '2026-02-02 20:59:39'),
(48, 11, 'STATUS_CHANGE', 3, 2, '{\"from\":\"READY\",\"to\":\"DELIVERED\"}', '2026-02-02 20:59:44');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 1, 35.00),
(2, 1, 2, 1, 35.00),
(3, 1, 4, 2, 20.00),
(4, 2, 1, 2, 35.00),
(5, 3, 1, 1, 35.00),
(6, 3, 2, 1, 35.00),
(7, 4, 1, 2, 35.00),
(8, 4, 2, 2, 35.00),
(9, 4, 4, 2, 20.00),
(10, 5, 1, 2, 35.00),
(11, 5, 2, 2, 35.00),
(12, 5, 4, 2, 20.00),
(13, 6, 1, 2, 35.00),
(14, 6, 2, 2, 35.00),
(15, 6, 3, 2, 25.00),
(16, 6, 4, 2, 20.00),
(17, 7, 1, 2, 35.00),
(18, 7, 4, 2, 20.00),
(19, 8, 4, 3, 20.00),
(20, 9, 4, 1, 20.00),
(21, 10, 3, 2, 25.00),
(22, 10, 5, 2, 40.00),
(23, 11, 4, 5, 20.00),
(24, 12, 1, 1, 35.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `bill_id`, `amount_paid`, `payment_method`, `paid_at`) VALUES
(1, 1, 75.00, 'GCASH', '2026-01-31 10:30:19'),
(2, 3, 150.00, 'CASH', '2026-01-31 10:38:58'),
(3, 2, 75.00, 'CASH', '2026-01-31 16:07:15'),
(4, 5, 330.00, 'CASH', '2026-01-31 23:04:52'),
(5, 4, 150.00, 'CASH', '2026-01-31 23:04:56'),
(6, 6, 150.00, 'GCASH', '2026-02-01 04:17:22'),
(7, 7, 450.00, 'GCASH', '2026-02-01 04:17:34'),
(8, 8, 895.00, 'GCASH', '2026-02-01 05:19:26'),
(9, 9, 450.00, 'GCASH', '2026-02-01 05:20:54'),
(10, 10, 300.00, 'CASH', '2026-02-01 06:29:55'),
(11, 17, 150.00, 'CASH', '2026-02-01 15:47:02'),
(12, 11, 740.00, 'CASH', '2026-02-01 15:47:11'),
(13, 12, 150.00, 'CASH', '2026-02-01 15:47:16'),
(14, 13, 150.00, 'CASH', '2026-02-01 15:47:20'),
(15, 14, 150.00, 'CASH', '2026-02-01 15:47:25'),
(16, 15, 75.00, 'CASH', '2026-02-01 15:47:28'),
(17, 16, 150.00, 'CASH', '2026-02-01 15:47:32'),
(18, 20, 150.00, 'GCASH', '2026-02-01 16:05:34'),
(19, 21, 150.00, 'CASH', '2026-02-01 23:19:57'),
(20, 19, 150.00, 'CASH', '2026-02-01 23:20:01'),
(21, 18, 150.00, 'CASH', '2026-02-01 23:20:06'),
(22, 22, 150.00, 'CASH', '2026-02-01 23:20:09'),
(23, 23, 335.00, 'CASH', '2026-02-02 00:43:29'),
(24, 24, 150.00, 'CASH', '2026-02-02 02:45:01'),
(25, 25, 150.00, 'CASH', '2026-02-02 02:45:07'),
(26, 26, 150.00, 'CASH', '2026-02-02 02:45:14'),
(27, 27, 150.00, 'CASH', '2026-02-02 03:41:07'),
(28, 28, 150.00, 'CASH', '2026-02-02 03:41:14'),
(29, 29, 285.00, 'CASH', '2026-02-02 03:45:30'),
(30, 30, 150.00, 'CASH', '2026-02-02 03:51:26'),
(31, 31, 150.00, 'CASH', '2026-02-02 03:51:31'),
(32, 32, 150.00, 'CASH', '2026-02-02 10:14:41'),
(33, 33, 75.00, 'CASH', '2026-02-02 11:06:32'),
(34, 35, 150.00, 'CASH', '2026-02-02 12:12:34'),
(35, 34, 360.00, 'CASH', '2026-02-02 12:12:40'),
(36, 36, 150.00, 'CASH', '2026-02-02 12:16:46'),
(37, 37, 150.00, 'CASH', '2026-02-02 12:35:43'),
(38, 38, 150.00, 'GCASH', '2026-02-08 23:51:07'),
(39, 39, 150.00, 'CASH', '2026-02-09 00:21:06'),
(40, 40, 150.00, 'CASH', '2026-02-09 00:24:48'),
(41, 41, 150.00, 'CASH', '2026-02-09 01:04:10'),
(42, 42, 150.00, 'CASH', '2026-02-09 01:19:12'),
(43, 44, 150.00, 'GCASH', '2026-02-12 07:37:17'),
(44, 46, 150.00, 'CASH', '2026-02-18 04:52:10'),
(45, 45, 150.00, 'CASH', '2026-02-18 04:52:19'),
(46, 43, 200.00, 'CASH', '2026-02-18 04:52:30'),
(47, 47, 75.00, 'GCASH', '2026-02-18 05:17:49'),
(48, 48, 200.00, 'CASH', '2026-02-18 05:22:09'),
(49, 49, 150.00, 'CASH', '2026-02-18 05:22:41'),
(50, 51, 80.00, 'CASH', '2026-02-22 14:23:57'),
(51, 52, 1000.00, 'CASH', '2026-02-22 14:32:03'),
(52, 53, 150.00, 'CASH', '2026-02-22 22:42:54'),
(53, 54, 21313.00, 'GCASH', '2026-02-22 22:43:44'),
(54, 50, 150.00, 'CASH', '2026-02-23 03:41:55'),
(55, 56, 150.00, 'CASH', '2026-02-23 07:49:40');

-- --------------------------------------------------------

--
-- Table structure for table `payment_requests`
--

CREATE TABLE `payment_requests` (
  `request_id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `status` enum('PENDING','ASSIGNED_TO_STAFF','COLLECTED','CANCELLED') DEFAULT 'PENDING',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_staff_id` int(11) DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `collected_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `Category` varchar(122) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `price`, `stock_quantity`, `is_active`, `Category`) VALUES
(1, 'Coke 330ml', 35.00, 37, 1, 'Beverages'),
(2, 'Sprite 330ml', 35.00, 42, 1, 'Beverages'),
(3, 'Piattos Cheese', 25.00, 46, 1, 'Snacks'),
(4, 'Bottled Water', 20.00, 31, 1, 'Beverages'),
(5, 'Nova', 40.00, 48, 1, 'Snacks'),
(6, 'Mang Juan', 30.00, 50, 1, 'Snacks'),
(7, 'Cup Noodles', 26.00, 50, 1, 'Noodles'),
(8, 'Winston', 200.00, 0, 1, 'Other'),
(9, 'TANDUAY LIGHT', 150.00, 200, 1, 'Beverages');

-- --------------------------------------------------------

--
-- Table structure for table `rentals`
--

CREATE TABLE `rentals` (
  `rental_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `started_at` datetime NOT NULL,
  `ended_at` datetime DEFAULT NULL,
  `total_minutes` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rentals`
--

INSERT INTO `rentals` (`rental_id`, `room_id`, `started_at`, `ended_at`, `total_minutes`, `is_active`) VALUES
(1, 1, '2026-01-31 18:30:13', '2026-01-31 18:30:19', 30, 0),
(2, 2, '2026-01-31 18:30:28', '2026-02-01 00:07:15', 30, 0),
(3, 3, '2026-01-31 18:34:14', '2026-01-31 18:38:58', 60, 0),
(4, 4, '2026-02-01 06:16:49', '2026-02-01 07:04:56', 60, 0),
(5, 1, '2026-02-01 06:57:29', '2026-02-01 07:04:52', 60, 0),
(6, 1, '2026-02-01 12:17:15', '2026-02-01 12:17:22', 60, 0),
(7, 2, '2026-02-01 12:17:28', '2026-02-01 12:17:34', 180, 0),
(8, 3, '2026-02-01 12:27:41', '2026-02-01 13:19:26', 330, 0),
(9, 4, '2026-02-01 13:06:40', '2026-02-01 13:20:54', 180, 0),
(10, 5, '2026-02-01 13:10:03', '2026-02-01 14:29:55', 120, 0),
(11, 1, '2026-02-01 14:37:17', '2026-02-01 23:47:11', 60, 0),
(12, 2, '2026-02-01 14:57:30', '2026-02-01 23:47:16', 60, 0),
(13, 3, '2026-02-01 23:28:52', '2026-02-01 23:47:20', 60, 0),
(14, 4, '2026-02-01 23:29:01', '2026-02-01 23:47:25', 60, 0),
(15, 5, '2026-02-01 23:29:02', '2026-02-01 23:47:28', 30, 0),
(16, 6, '2026-02-01 23:41:23', '2026-02-01 23:47:32', 60, 0),
(17, 7, '2026-02-01 23:46:55', '2026-02-01 23:47:02', 60, 0),
(18, 1, '2026-02-01 23:52:49', '2026-02-02 07:20:06', 60, 0),
(19, 2, '2026-02-01 23:54:37', '2026-02-02 07:20:01', 60, 0),
(20, 3, '2026-02-02 00:05:27', '2026-02-02 00:05:34', 60, 0),
(21, 4, '2026-02-02 00:06:35', '2026-02-02 07:19:57', 60, 0),
(22, 5, '2026-02-02 07:19:53', '2026-02-02 07:20:09', 60, 0),
(23, 1, '2026-02-02 07:25:10', '2026-02-02 08:43:30', 90, 0),
(24, 2, '2026-02-02 08:30:06', '2026-02-02 10:45:01', 60, 0),
(25, 3, '2026-02-02 08:31:36', '2026-02-02 10:45:07', 60, 0),
(26, 4, '2026-02-02 08:35:44', '2026-02-02 10:45:14', 60, 0),
(27, 5, '2026-02-02 08:38:20', '2026-02-02 11:41:07', 60, 0),
(28, 6, '2026-02-02 08:42:16', '2026-02-02 11:41:14', 60, 0),
(29, 1, '2026-02-02 11:42:10', '2026-02-02 11:45:30', 90, 0),
(30, 2, '2026-02-02 11:48:07', '2026-02-02 11:51:26', 60, 0),
(31, 3, '2026-02-02 11:51:12', '2026-02-02 11:51:31', 60, 0),
(32, 1, '2026-02-02 11:54:39', '2026-02-02 18:14:41', 60, 0),
(33, 2, '2026-02-02 18:47:25', '2026-02-02 19:06:32', 30, 0),
(34, 1, '2026-02-02 19:10:58', '2026-02-02 20:12:41', 30, 0),
(35, 2, '2026-02-02 19:58:59', '2026-02-02 20:12:34', 60, 0),
(36, 1, '2026-02-02 20:16:40', '2026-02-02 20:16:46', 60, 0),
(37, 3, '2026-02-02 20:17:37', '2026-02-02 20:35:43', 60, 0),
(38, 4, '2026-02-04 14:15:29', '2026-02-09 07:51:07', 60, 0),
(39, 5, '2026-02-09 08:15:24', '2026-02-09 08:21:06', 60, 0),
(40, 6, '2026-02-09 08:21:10', '2026-02-09 08:24:48', 60, 0),
(41, 7, '2026-02-09 08:24:51', '2026-02-09 09:04:10', 60, 0),
(42, 8, '2026-02-09 09:04:14', '2026-02-09 09:19:12', 60, 0),
(43, 9, '2026-02-09 09:19:15', '2026-02-18 12:52:30', 60, 0),
(44, 1, '2026-02-09 18:29:51', '2026-02-12 15:37:17', 60, 0),
(45, 2, '2026-02-09 18:30:33', '2026-02-18 12:52:19', 60, 0),
(46, 3, '2026-02-09 19:33:47', '2026-02-18 12:52:10', 60, 0),
(47, 4, '2026-02-18 13:14:09', '2026-02-18 13:17:49', 30, 0),
(48, 10, '2026-02-18 13:21:57', '2026-02-18 13:22:09', 60, 0),
(49, 16, '2026-02-18 13:22:32', '2026-02-18 13:22:41', 60, 0),
(50, 1, '2026-02-18 13:22:59', '2026-02-23 11:41:55', 60, 0),
(51, 15, '2026-02-22 22:21:48', '2026-02-22 22:23:57', 30, 0),
(52, 15, '2026-02-22 22:31:00', '2026-02-22 22:32:03', 60, 0),
(53, 15, '2026-02-22 22:46:16', '2026-02-23 06:42:54', 60, 0),
(54, 15, '2026-02-23 06:43:18', '2026-02-23 06:43:44', 60, 0),
(55, 1, '2026-02-23 11:40:05', NULL, 60, 1),
(56, 15, '2026-02-23 15:39:11', '2026-02-23 15:49:40', 60, 0);

-- --------------------------------------------------------

--
-- Table structure for table `rental_access`
--

CREATE TABLE `rental_access` (
  `access_id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `qr_token` varchar(64) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rental_access`
--

INSERT INTO `rental_access` (`access_id`, `rental_id`, `room_id`, `qr_token`, `otp_code`, `is_used`, `expires_at`, `created_at`) VALUES
(1, 51, 15, 'f0d3326676ff4017a5cf12f6bde30110b3a843ae1dd04dfd74bbb7c4132c787d', '298462', 0, '2026-02-23 22:21:48', '2026-02-22 14:21:48'),
(2, 52, 15, '4ee6c92ca2a5a68c924a55faa5bab3e0a82033fb96bc18be6ea03bb1c157254a', '370846', 0, '2026-02-23 22:31:00', '2026-02-22 14:31:00'),
(3, 53, 15, '787df0a15f8a3789d29c9b16cb2c823ce5ca635ef9e5ee43e05db0b225d70e48', '123264', 0, '2026-02-23 22:46:16', '2026-02-22 14:46:16'),
(4, 54, 15, 'd5203c552bc5ab9ba37e0b7562e71fb32dcdc638c92175e54f39f74fb44d873e', '435469', 0, '2026-02-24 06:43:18', '2026-02-22 22:43:18'),
(5, 55, 1, '368fadb5d6c749ebae82ec4d355f98754645c5a3dbff063c9fa871e4a5f9a70a', '775337', 0, '2026-02-24 11:40:05', '2026-02-23 03:40:05'),
(6, 56, 15, '2031204f07e050d4a91c3c7e66962dfd6193da41b2eb6f0b31c7e4f03e6d2e7d', '762740', 0, '2026-02-24 15:39:11', '2026-02-23 07:39:12');

-- --------------------------------------------------------

--
-- Table structure for table `rental_extensions`
--

CREATE TABLE `rental_extensions` (
  `extension_id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `minutes_added` int(11) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `extended_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rental_extensions`
--

INSERT INTO `rental_extensions` (`extension_id`, `rental_id`, `minutes_added`, `cost`, `extended_at`) VALUES
(1, 8, 30, 75.00, '2026-02-01 05:00:08'),
(2, 8, 30, 75.00, '2026-02-01 05:04:57'),
(3, 8, 180, 450.00, '2026-02-01 05:07:03'),
(4, 8, 30, 75.00, '2026-02-01 05:09:10'),
(5, 10, 30, 75.00, '2026-02-01 05:15:51'),
(6, 10, 30, 75.00, '2026-02-01 05:18:45'),
(7, 23, 30, 75.00, '2026-02-01 23:25:21'),
(8, 29, 30, 75.00, '2026-02-02 03:42:27');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(3, 'CASHIER'),
(4, 'CUSTOMER'),
(1, 'OWNER'),
(2, 'STAFF');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `room_number` int(11) NOT NULL,
  `room_type_id` int(11) NOT NULL,
  `status` enum('AVAILABLE','OCCUPIED','CLEANING') DEFAULT 'AVAILABLE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_number`, `room_type_id`, `status`) VALUES
(1, 1, 1, 'AVAILABLE'),
(2, 2, 1, 'AVAILABLE'),
(3, 3, 1, 'AVAILABLE'),
(4, 4, 1, 'AVAILABLE'),
(5, 5, 1, 'AVAILABLE'),
(6, 6, 1, 'CLEANING'),
(7, 7, 3, 'CLEANING'),
(8, 8, 1, 'AVAILABLE'),
(9, 9, 2, 'CLEANING'),
(10, 10, 2, 'CLEANING'),
(11, 11, 3, 'AVAILABLE'),
(12, 12, 3, 'AVAILABLE'),
(14, 13, 3, 'AVAILABLE'),
(15, 0, 1, 'CLEANING'),
(16, 15, 1, 'AVAILABLE');

-- --------------------------------------------------------

--
-- Table structure for table `room_types`
--

CREATE TABLE `room_types` (
  `room_type_id` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL,
  `price_per_hour` decimal(10,2) NOT NULL,
  `price_per_30min` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_types`
--

INSERT INTO `room_types` (`room_type_id`, `type_name`, `price_per_hour`, `price_per_30min`) VALUES
(1, 'Regular', 149.00, 75.00),
(2, 'Mid-tier (199)', 199.00, 100.00),
(3, 'Premium (300)', 300.00, 150.00);

-- --------------------------------------------------------

--
-- Table structure for table `staff_collections`
--

CREATE TABLE `staff_collections` (
  `collection_id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `amount_collected` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'CASH',
  `collection_status` enum('COLLECTED','PASSED_TO_CASHIER','VERIFIED') DEFAULT 'COLLECTED',
  `collected_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `passed_at` datetime DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `cashier_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `transaction_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `bill_id`, `transaction_date`, `total_amount`, `user_id`) VALUES
(1, 1, '2026-01-31', 75.00, NULL),
(2, 3, '2026-01-31', 150.00, NULL),
(3, 2, '2026-02-01', 75.00, NULL),
(4, 5, '2026-02-01', 330.00, NULL),
(5, 4, '2026-02-01', 150.00, NULL),
(6, 6, '2026-02-01', 150.00, NULL),
(7, 7, '2026-02-01', 450.00, NULL),
(8, 8, '2026-02-01', 895.00, NULL),
(9, 9, '2026-02-01', 450.00, NULL),
(10, 10, '2026-02-01', 300.00, NULL),
(11, 17, '2026-02-01', 150.00, NULL),
(12, 11, '2026-02-01', 740.00, NULL),
(13, 12, '2026-02-01', 150.00, NULL),
(14, 13, '2026-02-01', 150.00, NULL),
(15, 14, '2026-02-01', 150.00, NULL),
(16, 15, '2026-02-01', 75.00, NULL),
(17, 16, '2026-02-01', 150.00, NULL),
(18, 20, '2026-02-02', 150.00, NULL),
(19, 21, '2026-02-02', 150.00, NULL),
(20, 19, '2026-02-02', 150.00, NULL),
(21, 18, '2026-02-02', 150.00, NULL),
(22, 22, '2026-02-02', 150.00, NULL),
(23, 23, '2026-02-02', 335.00, NULL),
(24, 24, '2026-02-02', 150.00, NULL),
(25, 25, '2026-02-02', 150.00, NULL),
(26, 26, '2026-02-02', 150.00, NULL),
(27, 27, '2026-02-02', 150.00, NULL),
(28, 28, '2026-02-02', 150.00, NULL),
(29, 29, '2026-02-02', 285.00, NULL),
(30, 30, '2026-02-02', 150.00, NULL),
(31, 31, '2026-02-02', 150.00, NULL),
(32, 32, '2026-02-02', 150.00, NULL),
(33, 33, '2026-02-02', 75.00, NULL),
(34, 35, '2026-02-02', 150.00, NULL),
(35, 34, '2026-02-02', 360.00, NULL),
(36, 36, '2026-02-02', 150.00, NULL),
(37, 37, '2026-02-02', 150.00, NULL),
(38, 38, '2026-02-09', 150.00, NULL),
(39, 39, '2026-02-09', 150.00, NULL),
(40, 40, '2026-02-09', 150.00, NULL),
(41, 41, '2026-02-09', 150.00, NULL),
(42, 42, '2026-02-09', 150.00, NULL),
(43, 44, '2026-02-12', 150.00, NULL),
(44, 46, '2026-02-18', 150.00, NULL),
(45, 45, '2026-02-18', 150.00, NULL),
(46, 43, '2026-02-18', 200.00, NULL),
(47, 47, '2026-02-18', 75.00, NULL),
(48, 48, '2026-02-18', 200.00, NULL),
(49, 49, '2026-02-18', 150.00, NULL),
(50, 51, '2026-02-22', 80.00, NULL),
(51, 52, '2026-02-22', 1000.00, NULL),
(52, 53, '2026-02-23', 150.00, NULL),
(53, 54, '2026-02-23', 21313.00, NULL),
(54, 50, '2026-02-23', 150.00, NULL),
(55, 56, '2026-02-23', 150.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `display_name` varchar(150) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `otp` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `must_change_password` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `display_name`, `email`, `password`, `role_id`, `room_id`, `is_active`, `otp`, `otp_expiry`, `must_change_password`, `created_at`) VALUES
(2, 'owner', NULL, 'jazdylabajan@gmail.com', '$2y$10$dtozxfIe4kU8AJWGngs8KuFSzeqbrvBx9on5NcNV6OzC9SlRWrYsS', 1, NULL, 1, '', NULL, 0, '2026-01-26 06:23:50'),
(3, 'staff', NULL, NULL, '$2y$10$5OG.P9TuHpwOeMSc2C/j8e9aw69Stciq9RYFv64tuTGzEXEv3c/5q', 2, NULL, 1, NULL, NULL, 0, '2026-01-26 06:23:50'),
(4, 'cashier', NULL, NULL, '$2y$10$l5.HyUPaVcikuETJvMmVy.anHQrhNKkq3Lia32Fam3w8STYngG8/2', 3, NULL, 1, NULL, NULL, 0, '2026-01-26 06:23:50'),
(5, 'customer', NULL, NULL, 'customerpass', 4, 1, 0, NULL, NULL, 0, '2026-01-26 06:23:50'),
(6, 'room1', 'room1', NULL, '$2y$10$RjV/PMPDaADQ6joo.Eu8i.P59GQk0N0K54tQYxYQ.DfsQT68KwVr2', 4, 1, 1, NULL, NULL, 0, '2026-01-31 22:34:45'),
(7, 'room2', '', NULL, '$2y$10$DM3OgpbMBAaDhyllHLUnqub.fLR2czrV9.1lSpxDxuCgE0DC2NMue', 4, 2, 1, NULL, NULL, 0, '2026-01-31 23:30:02'),
(8, 'room3', '', NULL, '$2y$10$IMiWyQ8NeWIkXcFvtRgGLuPdjr/TA/zqWLC7RbnWF4dghLKlKyEAS', 4, 3, 1, NULL, NULL, 0, '2026-01-31 23:30:29'),
(9, 'room4', '', NULL, '$2y$10$wuoQnEecHZtdKILtJNHHfuK5o4c.dyxNQjw/qxVP6tslU.VVvfsye', 4, 4, 1, NULL, NULL, 0, '2026-01-31 23:31:33'),
(10, 'room5', '', NULL, '$2y$10$BRbsiLFs591RZfTBVIiVhuRKoLb3q4epnYj3hSV2UhOsZf7tuV95a', 4, 5, 1, NULL, NULL, 0, '2026-02-01 05:10:16'),
(11, 'Rj', 'Rj Cainglet', NULL, '$2y$10$jO.Wpg38NEzHnoX194OJuOALp0mWzdODli4Aou3GwkPwJxjcd3oja', 3, NULL, 1, NULL, NULL, 0, '2026-02-02 12:11:49'),
(12, 'testing', 'testing', NULL, '$2y$10$NXT7WNBOMagviz8KUgtVBOMyief83xoJcB7ni7hEN6O9v6AfnRju.', 1, NULL, 1, NULL, NULL, 0, '2026-02-09 01:43:17'),
(13, 'customer1', 'sample', NULL, '$2y$10$Pgf2pAFFrZ4tPuLEs06AAO3KC.TfqIjgvYx2Fz.pVRm.dCdV7bX5O', 4, 1, 1, NULL, NULL, 0, '2026-02-09 10:34:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`bill_id`),
  ADD KEY `rental_id` (`rental_id`);

--
-- Indexes for table `cleaning_logs`
--
ALTER TABLE `cleaning_logs`
  ADD PRIMARY KEY (`cleaning_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `rental_id` (`rental_id`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_assigned` (`assigned_staff_id`);

--
-- Indexes for table `order_audit`
--
ALTER TABLE `order_audit`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `bill_id` (`bill_id`);

--
-- Indexes for table `payment_requests`
--
ALTER TABLE `payment_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `rental_id` (`rental_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`rental_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `rental_access`
--
ALTER TABLE `rental_access`
  ADD PRIMARY KEY (`access_id`),
  ADD UNIQUE KEY `qr_token` (`qr_token`),
  ADD UNIQUE KEY `rental_id` (`rental_id`),
  ADD KEY `otp_code` (`otp_code`);

--
-- Indexes for table `rental_extensions`
--
ALTER TABLE `rental_extensions`
  ADD PRIMARY KEY (`extension_id`),
  ADD KEY `rental_id` (`rental_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD UNIQUE KEY `room_number` (`room_number`),
  ADD KEY `room_type_id` (`room_type_id`);

--
-- Indexes for table `room_types`
--
ALTER TABLE `room_types`
  ADD PRIMARY KEY (`room_type_id`);

--
-- Indexes for table `staff_collections`
--
ALTER TABLE `staff_collections`
  ADD PRIMARY KEY (`collection_id`),
  ADD KEY `rental_id` (`rental_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `bill_id` (`bill_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email_unique` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `idx_room_id` (`room_id`),
  ADD KEY `idx_otp` (`otp`),
  ADD KEY `idx_must_change_password` (`must_change_password`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `bill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `cleaning_logs`
--
ALTER TABLE `cleaning_logs`
  MODIFY `cleaning_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_audit`
--
ALTER TABLE `order_audit`
  MODIFY `audit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `payment_requests`
--
ALTER TABLE `payment_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `rental_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `rental_access`
--
ALTER TABLE `rental_access`
  MODIFY `access_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `rental_extensions`
--
ALTER TABLE `rental_extensions`
  MODIFY `extension_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `room_types`
--
ALTER TABLE `room_types`
  MODIFY `room_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `staff_collections`
--
ALTER TABLE `staff_collections`
  MODIFY `collection_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bills`
--
ALTER TABLE `bills`
  ADD CONSTRAINT `bills_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`);

--
-- Constraints for table `cleaning_logs`
--
ALTER TABLE `cleaning_logs`
  ADD CONSTRAINT `cleaning_logs_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`),
  ADD CONSTRAINT `cleaning_logs_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`bill_id`);

--
-- Constraints for table `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`);

--
-- Constraints for table `rental_extensions`
--
ALTER TABLE `rental_extensions`
  ADD CONSTRAINT `rental_extensions_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`);

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`room_type_id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`bill_id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
