-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 17, 2025 at 09:17 AM
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
-- Database: `barangay_e-services`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_announcements`
--

CREATE TABLE `tbl_announcements` (
  `ann_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `details` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `status` enum('published','draft') DEFAULT 'draft',
  `created_at` datetime DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_announcements`
--

INSERT INTO `tbl_announcements` (`ann_id`, `title`, `details`, `image_path`, `status`, `created_at`, `created_by`) VALUES
(5, 'wasddasdad', 'adasdadsda', 'uploads/announcements/ann_1763138574_601496856e9d.jpg', 'published', '2025-11-14 17:42:54', 1),
(6, 'Free Circumcision', 'The Barangay Guindaruhan offer free circumcision for the youth.', 'uploads/announcements/ann_1763364222_01423a99eccf.jpg', 'published', '2025-11-17 08:23:42', 1),
(7, 'wdasdada', 'dasdasdadasdasdasdadasds', 'uploads/announcements/ann_1763367073_be20d892d52b.jpg', 'published', '2025-11-17 09:11:13', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_complaints`
--

CREATE TABLE `tbl_complaints` (
  `c_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reference_no` varchar(50) NOT NULL,
  `complaint_type` varchar(100) NOT NULL,
  `details` text NOT NULL,
  `date_of_incident` date NOT NULL,
  `location` varchar(255) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `date_filed` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_complaints`
--

INSERT INTO `tbl_complaints` (`c_id`, `user_id`, `reference_no`, `complaint_type`, `details`, `date_of_incident`, `location`, `status`, `date_filed`) VALUES
(1, 1, 'CMP-68F5D4544A4F1', 'Noise Disturbance', 'Saba kaayo sig videoke diri tunga sa gabie', '2025-10-20', 'purok gemelina', 'Resolved', '2025-10-20 14:19:00'),
(2, 2, 'CMP-6901A04944DE8', 'Garbage Problem', 'aray ko', '2025-10-29', 'cavite', 'Resolved', '2025-10-29 13:04:09'),
(3, 2, 'CMP-69176AA1D2D93', 'Noise Disturbance', 'adasdadsadsad', '2025-11-15', 'purok gemelina', 'Dismissed', '2025-11-15 01:45:05'),
(4, 2, 'CMP-691AA33E6756A', 'Noise Disturbance', 'wasdadadadasdadsadasdasd', '2025-11-17', 'purok gemelina', 'Dismissed', '2025-11-17 12:23:26'),
(5, 2, 'CMP-691ACB596436D', 'Noise Disturbance', 'naa didto ah', '2025-11-17', 'purok gemelina', 'Dismissed', '2025-11-17 15:14:33'),
(6, 2, 'CMP-691AD06344F05', 'Garbage Problem', 'dlkaldasldladlkaldasldladlkaldasldladlkaldasldladlkaldasldladlkaldasldladlkaldasldladlkaldasldladlkaldasldladlkaldasldladlkaldasldla', '2025-11-17', 'purok gemelina', 'Pending', '2025-11-17 15:36:03'),
(7, 2, 'CMP-691AD8EA4BB5A', 'Noise Disturbance', 'wdadadasdwdadadasdwdadadasdwdadadasdwdadadasdwdadadasdwdadadasd', '2025-11-17', 'purok gemelina', 'Pending', '2025-11-17 16:12:26');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_requests`
--

CREATE TABLE `tbl_requests` (
  `r_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `second_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `gender` varchar(50) NOT NULL,
  `age` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `document_type` varchar(255) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `r_status` varchar(255) NOT NULL,
  `date_requested` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_requests`
--

INSERT INTO `tbl_requests` (`r_id`, `id`, `first_name`, `second_name`, `last_name`, `gender`, `age`, `address`, `email`, `document_type`, `purpose`, `r_status`, `date_requested`) VALUES
(2, 1, 'Ivan', 'clark', 'Yonzon', 'Male', 20, 'purok gemelina', 'yonzonivanclark@gmail.com', 'Barangay Indigency', 'wa ra gud', 'denied', '2025-10-13 06:28:27'),
(3, 1, 'Ivan', 'clark', 'Yonzon', 'Male', 20, 'purok gemelina', 'yonzonivanclark@gmail.com', 'Business Permit', 'wa ra gud', 'approved', '2025-10-13 07:05:03'),
(5, 1, 'wdasdasda', 'wasdsada', 'wasdasda', 'Male', 20, 'purok gemelina', 'yonzonivanclark@gmail.com', 'Barangay Certificate', 'wa ra gud', 'denied', '2025-10-16 04:49:07'),
(6, 1, 'Ivan', 'clark', 'Yonzon', 'Male', 20, 'purok gemelina', 'yonzonivanclark@gmail.com', 'Business Permit', 'wasd', 'approved', '2025-10-18 07:07:50'),
(12, 2, 'Ivan', 'clark', 'Yonzon', 'Male', 20, 'purok gemelina', 'legacykrung@gmail.com', 'Barangay Certificate', 'wa ra gud', 'approved', '2025-11-14 16:28:51'),
(15, 2, 'Ivan', '', 'Yonzon', 'Male', 22, 'purok gemelina', 'legacykrung@gmail.com', 'Barangay Certificate', 'awdasdasdasdasdas', 'approved', '2025-11-17 05:21:24'),
(16, 2, 'Ivan', 'clark', 'jeky', 'Male', 22, 'purok gemelina', 'legacykrung@gmail.com', 'Barangay Indigency', 'wa ra gud', 'approved', '2025-11-17 07:38:02'),
(17, 2, 'Ivan', 'clark', '123', 'Male', 22, 'purok gemelina', 'legacykrung@gmail.com', 'Business Permit', 'business', 'approved', '2025-11-17 08:12:52'),
(18, 2, 'Ivan', 'clark', 'wdadasd', 'Male', 22, 'purok gemelina', 'legacykrung@gmail.com', 'Barangay Certificate', 'wadsdadasdadadsasdasd', 'approved', '2025-11-17 08:35:18'),
(19, 2, 'Ivan', 'clark', 'Yonzon', 'Female', 22, 'purok gemelina', 'legacykrung@gmail.com', 'Barangay Certificate', 'wa ra gud', 'pending', '2025-11-17 08:35:34'),
(20, 2, 'Ivan', 'clark', 'Yonzon', 'Male', 22, 'purok gemelina', 'legacykrung@gmail.com', 'Barangay Certificate', 'wa ra gud', 'pending', '2025-11-17 09:11:46'),
(21, 2, 'Ivan', 'clark', 'Yonzon', 'Female', 22, 'purok gemelina', 'legacykrung@gmail.com', 'Business Permit', 'wa ra gud', 'pending', '2025-11-17 09:12:04');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `account_status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`id`, `first_name`, `last_name`, `birth_date`, `gender`, `email`, `password`, `role`, `account_status`) VALUES
(1, 'Ivan', 'Yonzon', '2025-10-01', 'Male', 'yonzonivanclark@gmail.com', '$2y$10$TW4lKIIDK9z3bCyKHpm1suq1McL0fwSnweWJ21pbEWY3FbDljoggC', 'official', 'active'),
(2, 'eheys', 'Yonzon', '2025-10-29', 'Female', 'legacykrung@gmail.com', '$2y$10$fy9T9hxBlMdryMgdNWXwCOi6sKQi9mpwzOXLmdSi/I2rgMrvXp8ay', 'resident', 'active'),
(21, 'Ivan', 'Yonzon', '2025-11-17', 'Male', 'dasdasda@gmail.com', '$2y$10$m43Y8HyqAIQE3TnGhOYDI.5I0CM1PVw0ppolUgdZ49v11tZ5Td/hi', 'user', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_announcements`
--
ALTER TABLE `tbl_announcements`
  ADD PRIMARY KEY (`ann_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `tbl_complaints`
--
ALTER TABLE `tbl_complaints`
  ADD PRIMARY KEY (`c_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_requests`
--
ALTER TABLE `tbl_requests`
  ADD PRIMARY KEY (`r_id`),
  ADD KEY `id` (`id`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_announcements`
--
ALTER TABLE `tbl_announcements`
  MODIFY `ann_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_complaints`
--
ALTER TABLE `tbl_complaints`
  MODIFY `c_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_requests`
--
ALTER TABLE `tbl_requests`
  MODIFY `r_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_announcements`
--
ALTER TABLE `tbl_announcements`
  ADD CONSTRAINT `tbl_announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `tbl_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_complaints`
--
ALTER TABLE `tbl_complaints`
  ADD CONSTRAINT `tbl_complaints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`);

--
-- Constraints for table `tbl_requests`
--
ALTER TABLE `tbl_requests`
  ADD CONSTRAINT `tbl_requests_ibfk_1` FOREIGN KEY (`id`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_requests_ibfk_2` FOREIGN KEY (`email`) REFERENCES `tbl_users` (`email`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
