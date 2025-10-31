-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 31, 2025 at 07:42 AM
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
(1, 'Free Circumcition', 'The barangay is currently having a free session of circumcition located at Court Gym.', 'uploads/announcements/ann_1760843109_7fc2e60a40dc.png', 'published', '2025-10-19 05:05:09', 1),
(2, 'free', 'wasdadasd', 'uploads/announcements/ann_1760843724_3d706fb5a998.png', 'published', '2025-10-19 05:15:24', 1),
(3, 'ads', 'wasdasda', 'uploads/announcements/ann_1760843987_f9cc4a13e246.png', 'published', '2025-10-19 05:19:47', 1),
(4, 'adsad', 'asdasdad', 'uploads/announcements/ann_1760844044_d02946de7b2c.png', 'published', '2025-10-19 05:20:44', 1),
(5, 'wasd', 'asdadas', 'uploads/announcements/ann_1760844541_b31f8abae430.png', 'published', '2025-10-19 05:29:01', 1);

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
(2, 2, 'CMP-6901A04944DE8', 'Garbage Problem', 'aray ko', '2025-10-29', 'cavite', 'Resolved', '2025-10-29 13:04:09');

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
(9, 1, 'Ivan', 'clark', 'Yonzon', 'Prefer not to say', 12, 'purok gemelina', 'yonzonivanclark@gmail.com', 'Barangay Certificate', 'wadsdadasdadadsasdasd', 'pending', '2025-10-19 08:40:17');

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
(2, 'dae', 'Yonzon', '2025-10-29', 'Female', 'legacykrung@gmail.com', '$2y$10$fy9T9hxBlMdryMgdNWXwCOi6sKQi9mpwzOXLmdSi/I2rgMrvXp8ay', 'resident', 'active');

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
  MODIFY `ann_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_complaints`
--
ALTER TABLE `tbl_complaints`
  MODIFY `c_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_requests`
--
ALTER TABLE `tbl_requests`
  MODIFY `r_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
