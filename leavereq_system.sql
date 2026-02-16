-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 10, 2026 at 07:13 AM
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
-- Database: `leavereq_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `leave_balances`
--

CREATE TABLE `leave_balances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `leave_type_id` bigint(20) UNSIGNED NOT NULL,
  `total_credits` int(11) NOT NULL,
  `used_credits` int(11) NOT NULL DEFAULT 0,
  `pending_credits` int(11) NOT NULL DEFAULT 0,
  `year` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_balances`
--

INSERT INTO `leave_balances` (`id`, `user_id`, `leave_type_id`, `total_credits`, `used_credits`, `pending_credits`, `year`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 20, 0, 0, 2026, '2026-02-09 17:38:00', '2026-02-09 17:38:00'),
(2, 1, 2, 15, 0, 0, 2026, '2026-02-09 17:38:00', '2026-02-09 17:38:00'),
(3, 1, 3, 4, 0, 0, 2026, '2026-02-09 17:38:00', '2026-02-09 17:38:00'),
(4, 1, 4, 7, 0, 0, 2026, '2026-02-09 17:38:00', '2026-02-09 17:38:00'),
(5, 1, 5, 30, 0, 0, 2026, '2026-02-09 17:38:00', '2026-02-09 17:38:00'),
(6, 1, 6, 5, 0, 0, 2026, '2026-02-09 17:38:00', '2026-02-09 17:38:00'),
(7, 3, 1, 20, 3, 0, 2026, '2026-02-09 18:29:47', '2026-02-09 18:30:43'),
(8, 3, 2, 15, 2, 0, 2026, '2026-02-09 18:29:47', '2026-02-09 21:40:15'),
(9, 3, 3, 4, 0, 0, 2026, '2026-02-09 18:29:47', '2026-02-09 18:30:49'),
(10, 3, 4, 7, 0, 0, 2026, '2026-02-09 18:29:47', '2026-02-09 18:29:47'),
(11, 3, 5, 30, 0, 0, 2026, '2026-02-09 18:29:47', '2026-02-09 18:29:47'),
(12, 3, 6, 5, 0, 0, 2026, '2026-02-09 18:29:47', '2026-02-09 18:29:47'),
(13, 4, 1, 20, 0, 0, 2026, '2026-02-09 19:21:21', '2026-02-09 22:05:58'),
(14, 4, 2, 15, 0, 0, 2026, '2026-02-09 19:21:21', '2026-02-09 19:21:21'),
(15, 4, 3, 4, 0, 2, 2026, '2026-02-09 19:21:21', '2026-02-09 19:22:05'),
(16, 4, 4, 7, 0, 0, 2026, '2026-02-09 19:21:21', '2026-02-09 19:21:21'),
(17, 4, 5, 30, 0, 0, 2026, '2026-02-09 19:21:21', '2026-02-09 19:21:21'),
(18, 4, 6, 5, 0, 0, 2026, '2026-02-09 19:21:21', '2026-02-09 19:21:21');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `leave_type_id` bigint(20) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `admin_remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `user_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `admin_remarks`, `created_at`, `updated_at`) VALUES
(1, 3, 1, '2026-02-11', '2026-02-13', 3, 'im tired boss', 'approved', 'rest', '2026-02-09 18:30:05', '2026-02-09 18:30:43'),
(2, 3, 3, '2026-02-25', '2026-02-26', 2, 'meeting', 'rejected', 'sorry no', '2026-02-09 18:30:22', '2026-02-09 18:30:49'),
(3, 4, 1, '2026-02-11', '2026-02-13', 3, 'school', 'rejected', 'no school', '2026-02-09 19:21:40', '2026-02-09 19:42:35'),
(4, 4, 3, '2026-02-24', '2026-02-25', 2, 'birthday', 'pending', NULL, '2026-02-09 19:22:05', '2026-02-09 19:22:05'),
(5, 4, 1, '2026-03-17', '2026-03-23', 5, 'outing', 'cancelled', NULL, '2026-02-09 19:22:28', '2026-02-09 22:05:58'),
(6, 3, 2, '2026-02-19', '2026-02-20', 2, 'asa', 'approved', 'ok', '2026-02-09 19:23:22', '2026-02-09 21:40:15');

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `max_days` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`id`, `name`, `code`, `max_days`, `created_at`, `updated_at`) VALUES
(1, 'Vacation Leave', 'vacation', 20, '2026-02-09 17:10:04', '2026-02-09 17:10:04'),
(2, 'Sick Leave', 'sick', 15, '2026-02-09 17:10:04', '2026-02-09 17:10:04'),
(3, 'Emergency Leave', 'emergency', 4, '2026-02-09 17:10:04', '2026-02-09 17:10:04'),
(4, 'Paternity Leave', 'paternity', 7, '2026-02-09 17:10:04', '2026-02-09 17:10:04'),
(5, 'Parental Leave', 'parental', 30, '2026-02-09 17:10:04', '2026-02-09 17:10:04'),
(6, 'Service Incentive Leave', 'service_incentive', 5, '2026-02-09 17:10:04', '2026-02-09 17:10:04');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '2026_02_05_003056_create_leave_types_table', 1),
(3, '2026_02_05_003239_create_leave_balances_table', 1),
(4, '2026_02_05_003308_create_leave_requests_table', 1),
(5, '2026_02_06_022232_add_is_admin_to_users_table', 1),
(6, '2026_02_09_010551_add_admin_remarks_to_leave_requests', 1),
(7, '2026_02_09_035222_add_is_approved_to_users_table', 1),
(8, '2026_02_10_001426_add_status_to_users_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('NXJKT4RlVRIoJgSI8ALITQAqVvXMlJnMtZ9QPL23', 4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVXZOYnhZQ1hUbWpkTkttaHYzaFdLQnVpSEtrVjNuSVpiRDU3Nno2WCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NDt9', 1770703558);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `admin_remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `status`, `is_approved`, `remember_token`, `created_at`, `updated_at`, `is_admin`, `admin_remarks`) VALUES
(1, 'Admin Name', 'admin@example.com', NULL, '$2y$12$1jVFB8kZcwC/VfczCBO4wegk3Ttu/KfwZ2Lp6zAX3B5pmIGTmhKoi', 'approved', 0, NULL, '2026-02-09 17:11:30', '2026-02-09 17:13:12', 1, NULL),
(2, 'test', 'test@example.com', NULL, '$2y$12$bltuJPeznOynMXfiG82NUu3l0WqMPMQYgzRJCzkfgZapX78T6iQtW', 'rejected', 0, NULL, '2026-02-09 17:23:38', '2026-02-09 18:00:57', 0, NULL),
(3, 'Juan Dela Cruz', 'juan@example.com', NULL, '$2y$12$4Nns/P.FeWoSu6DcovmWI.L4JTJsCy2ENDHDD/LPd19ey.0ryGHLS', 'approved', 0, NULL, '2026-02-09 18:29:09', '2026-02-09 18:29:37', 0, NULL),
(4, 'Justin Bryan', 'justin@example.com', NULL, '$2y$12$jw.Q0Ah37O9xNHbiiWERkeTW3PWQej4Rhzzc2aq0JaoKEKF.ItrPm', 'approved', 0, NULL, '2026-02-09 19:17:36', '2026-02-09 19:20:03', 0, NULL),
(5, 'Bryan Justin', 'bryan@example.com', NULL, '$2y$12$oFWIv5p/sB73Y5MF.td/4ui3j0xENfCwn63DiVUhy6O51aUDbtDau', 'rejected', 0, NULL, '2026-02-09 19:18:51', '2026-02-09 19:20:07', 0, NULL),
(6, 'Maria Santos', 'maria@example.com', NULL, '$2y$12$B7G5ZvNjPyupo72jWrjsi.7g2DqzdHdGQxuFNXmp0ntosjySmUEmq', 'pending', 0, NULL, '2026-02-09 19:20:34', '2026-02-09 19:20:34', 0, NULL),
(7, 'corpuz', 'corpuz@example.com', NULL, '$2y$12$.8Zl48tFL/4/ONRXW0rplu4EGPSPeiCqxVH7sT0odzaDK.kHuuJjW', 'approved', 0, NULL, '2026-02-09 19:53:34', '2026-02-09 19:54:12', 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `leave_balances`
--
ALTER TABLE `leave_balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `leave_balances_user_id_leave_type_id_year_unique` (`user_id`,`leave_type_id`,`year`),
  ADD KEY `leave_balances_leave_type_id_foreign` (`leave_type_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_requests_user_id_foreign` (`user_id`),
  ADD KEY `leave_requests_leave_type_id_foreign` (`leave_type_id`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `leave_types_code_unique` (`code`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `leave_balances`
--
ALTER TABLE `leave_balances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `leave_balances`
--
ALTER TABLE `leave_balances`
  ADD CONSTRAINT `leave_balances_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_balances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
