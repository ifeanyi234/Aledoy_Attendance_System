-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 06, 2026 at 07:23 PM
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
-- Database: `aledoy_attendance_register_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `staff_id` varchar(50) NOT NULL,
  `log_date` date NOT NULL,
  `log_time` time NOT NULL,
  `status` enum('check-in','check-out') NOT NULL DEFAULT 'check-in'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `staff_id`, `log_date`, `log_time`, `status`) VALUES
(1, 'ALS-2026-017', '2026-06-23', '13:47:26', 'check-in'),
(4, 'ALS-2026-017', '2026-06-23', '14:04:00', 'check-out'),
(5, 'ALS-2026-67', '2026-06-23', '14:21:47', 'check-in'),
(12, 'ALS-2026-017', '2026-06-24', '14:14:13', 'check-out'),
(13, 'ALS-2026-67', '2026-06-29', '10:32:03', 'check-in'),
(14, 'ALS-2026-017', '2026-06-30', '11:13:52', 'check-in'),
(16, 'ALS-2026-017', '2026-06-30', '12:08:56', 'check-out'),
(17, 'ALS-2026-019', '2026-07-03', '13:06:40', 'check-in'),
(18, 'ALS-2026-004', '2026-07-03', '13:22:39', 'check-in'),
(19, 'ALS-2026-004', '2026-07-03', '14:36:08', 'check-out'),
(20, 'ALS-2026-019', '2026-07-03', '14:36:46', 'check-out');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_name`) VALUES
(1, 'AWS Cloud Computing'),
(2, 'Data Analysis'),
(3, 'Product Design'),
(4, 'Web Development (Frontend)'),
(5, 'Web Development (Backend using PHP)'),
(6, 'Web Development (Python)'),
(7, 'DevOps Engineering'),
(8, 'Cybersecurity'),
(9, 'Kiddies'),
(10, 'Wordpress'),
(11, 'Accounting, Decision-Making & Financial Communication'),
(12, 'Mastering Negotiating & Dispute Resolutions'),
(13, 'Advanced Budgeting and Cost Management');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(10) UNSIGNED NOT NULL,
  `staff_id` varchar(50) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL DEFAULT 'NULL',
  `staff_type` enum('main','academy','part-time') NOT NULL,
  `course` varchar(100) NOT NULL DEFAULT 'NULL',
  `passport_image` varchar(255) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `staff_id`, `firstname`, `lastname`, `email`, `phone`, `staff_type`, `course`, `passport_image`, `qr_code`, `date_created`) VALUES
(4, 'ALS-2026-017', 'Ifeanyi', 'Ezeh', 'EI71283@gmail.com', '+2347043277337', 'part-time', 'Web Development (Backend using PHP)', 'uploads/passports/ALS-2026-017_1783013359.png', 'qrcodes/ALS-2026-017.png', '2026-06-25 00:00:00'),
(5, 'ALS-2026-067', 'Chima', 'Oko', 'chima@oko.com', '+2348067676767', 'academy', 'Data Analysis', NULL, NULL, '2026-06-29 00:00:00'),
(6, 'ALS-2026-021', 'Ifeanyi', 'Chukwu', 'ifeanyi@company.com', '+448012345678', 'part-time', 'Web Development (Python)', NULL, NULL, '2026-06-29 12:09:28'),
(7, 'ALS-2026-072', 'Blessing', 'Amadi', 'blessing.a@company.com', '+18023456789', 'main', 'Data Science', NULL, NULL, '2026-06-29 12:09:28'),
(8, 'ALS-2026-003', 'John', 'Okon', 'john.okon@company.com', '+2348034567890', 'part-time', 'Cybersecurity', NULL, NULL, '2026-06-29 12:09:28'),
(9, 'ALS-2026-013', 'Sarah', 'Alabi', 'sarah.alabi@company.com', '+2348045678901', 'academy', 'Product Design', NULL, NULL, '2026-06-29 12:09:28'),
(10, 'ALS-2026-001', 'David', 'Musa', 'david.musa@company.com', '+448056789012', 'main', 'Web Development (Backend using PHP)', NULL, NULL, '2026-06-29 12:09:28'),
(11, 'ALS-2026-087', 'Michael', 'james', 'michaeljames@gmail.com', '+2347034786546', 'main', 'Wordpress', '', 'qrcodes/ALS-2026-087.png', '2026-06-29 00:00:00'),
(12, 'ALS-2026-019', 'johnbosco', 'Nwaoga', 'Nwaogajohnbosco@yahoo.com', '+2349163277337', 'academy', 'Data Analysis', 'uploads/passports/ALS-2026-019.jpg', 'qrcodes/ALS-2026-019.png', '2026-07-02 00:00:00'),
(13, 'ALS-2026-005', 'Segun', 'Moses', 'segunmoses@gmail.com', '+2348031234567', 'main', 'Product Design', 'uploads/passports/ALS-2026-005.png', 'qrcodes/ALS-2026-005.png', '2026-07-02 00:00:00'),
(14, 'ALS-2026-004', 'Lu', 'Abikoye', 'oabike@yahoo.com', '+2348161177834', 'main', 'AWS Cloud Computing', 'uploads/passports/ALS-2026-004_1783075897.png', 'qrcodes/ALS-2026-004.png', '2026-07-02 00:00:00'),
(15, 'ALS-2026-002', 'Values', 'Agu', 'Aguvalues@gmail.com', '', 'academy', 'Web Development (Frontend)', 'uploads/passports/ALS-2026-002.png', 'qrcodes/ALS-2026-002.png', '2026-07-03 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` tinyint(1) NOT NULL DEFAULT 0,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `role`, `password`, `created_at`) VALUES
(1, 'Administrator', NULL, 1, '@Admin2026', '2026-06-17 15:53:26'),
(2, 'John Doe', 'Johndoe@email.com', 0, '$2y$10$/pADoxEHLFQ4q.gtJyDhqOXov08ckZSODxUbdbNNqdJL18ZtWElP.', '2026-06-29 12:37:54'),
(3, 'segunmoses', 'segunmoses@gmail.com', 0, '$2y$10$ePtlzgvL0rqM7KLsUGkkI.l3zgRk5M/3C.hXfJu4MDRHIrGP4RQia', '2026-06-29 17:06:42'),
(4, 'luabikoye', 'luabikoye@gmail.com', 1, '@Luabikoye2026', '2026-06-30 10:52:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_id` (`staff_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
