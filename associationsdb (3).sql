-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 04, 2025 at 07:19 AM
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
-- Database: `associationsdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `additional_social_status`
--

CREATE TABLE `additional_social_status` (
  `individual_id` int(11) DEFAULT NULL,
  `status` enum('Patient','Poor','Disabled','Orphan') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `additional_social_status`
--

INSERT INTO `additional_social_status` (`individual_id`, `status`) VALUES
(6, 'Orphan'),
(5, 'Poor');

-- --------------------------------------------------------

--
-- Table structure for table `associations`
--

CREATE TABLE `associations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `activity_type` varchar(100) DEFAULT NULL,
  `responsible_person` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `families`
--

CREATE TABLE `families` (
  `id` int(11) NOT NULL,
  `family_name` varchar(100) NOT NULL,
  `father_id` int(11) DEFAULT NULL,
  `mother_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `families`
--

INSERT INTO `families` (`id`, `family_name`, `father_id`, `mother_id`) VALUES
(1, 'Baouchi', 1, 2),
(2, 'dabouz', 1, 4);

-- --------------------------------------------------------

--
-- Table structure for table `family_relations`
--

CREATE TABLE `family_relations` (
  `id` int(11) NOT NULL,
  `individual1_id` int(11) DEFAULT NULL,
  `individual2_id` int(11) DEFAULT NULL,
  `relationship` enum('Father','Son','Daughter') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_relations`
--

INSERT INTO `family_relations` (`id`, `individual1_id`, `individual2_id`, `relationship`) VALUES
(3, 1, 3, 'Father'),
(5, 1, 4, 'Father'),
(7, 3, 5, 'Father'),
(9, 3, 6, 'Father');

-- --------------------------------------------------------

--
-- Table structure for table `individuals`
--

CREATE TABLE `individuals` (
  `id` int(11) NOT NULL,
  `status` enum('active','pending','blocked') NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `residence` varchar(100) DEFAULT NULL,
  `marital_status` enum('Single','Married','Divorced','Widowed') DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `national_id` varchar(20) DEFAULT NULL,
  `social_status_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `individuals`
--

INSERT INTO `individuals` (`id`, `status`, `first_name`, `last_name`, `birth_date`, `gender`, `residence`, `marital_status`, `phone`, `email`, `address`, `national_id`, `social_status_id`) VALUES
(1, 'active', 'Ahmed', 'Baouchi', '1950-03-10', 'Male', 'Algiers', 'Married', '0661000001', 'ahmed@example.com', '123 Main St', '100000001', 1),
(3, 'active', 'Mohamed', 'Baouchi', '1980-06-20', 'Male', 'Algiers', 'Married', '0661000003', 'mohamed@example.com', '456 Oak St', '100000003', 2),
(4, 'pending', 'Amina', 'Baouchi', '1985-05-10', 'Female', 'Algiers', 'Married', '0661000004', 'amina@example.com', '789 Elm St', '100000004', 4),
(5, 'active', 'Youssef', 'Baouchi', '2005-09-25', 'Male', 'Algiers', 'Single', '0661000005', 'youssef@example.com', '123 Main St', '100000005', 5),
(6, 'active', 'Salma', 'Baouchi', '2010-01-12', 'Female', 'Algiers', 'Single', '0661000006', 'salma@example.com', '123 Main St', '100000006', 6),
(7, 'active', 'yacine', 'baouchi', '2005-04-01', 'Male', 'berriane', 'Single', NULL, 'yacine@mail.com', 'cheikh amer', '12345678', 6);

-- --------------------------------------------------------

--
-- Table structure for table `memberships`
--

CREATE TABLE `memberships` (
  `id` int(11) NOT NULL,
  `individual_id` int(11) DEFAULT NULL,
  `association_id` int(11) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `join_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `social_status_info`
--

CREATE TABLE `social_status_info` (
  `id` int(11) NOT NULL,
  `individual_id` int(11) NOT NULL,
  `occupation` enum('student','worker','retired','pupil') NOT NULL,
  `occupation_type` varchar(100) DEFAULT NULL,
  `occupation_place` varchar(100) DEFAULT NULL,
  `study_year` varchar(50) DEFAULT NULL,
  `speciality` varchar(100) DEFAULT NULL,
  `school` varchar(100) DEFAULT NULL,
  `quran` enum('خاتم','مستظهر','غير خاتم') NOT NULL,
  `retirement_info` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `social_status_info`
--

INSERT INTO `social_status_info` (`id`, `individual_id`, `occupation`, `occupation_type`, `occupation_place`, `study_year`, `speciality`, `school`, `quran`, `retirement_info`) VALUES
(1, 1, 'retired', NULL, NULL, NULL, NULL, NULL, 'خاتم', 'Retired as a government officer'),
(2, 3, 'retired', NULL, NULL, NULL, NULL, NULL, 'خاتم', 'Retired as a teacher'),
(3, 3, 'worker', 'Engineer', 'Algerian Company', NULL, 'Software Engineering', NULL, 'غير خاتم', NULL),
(4, 4, 'worker', 'Teacher', 'High School', NULL, 'Mathematics', NULL, 'مستظهر', NULL),
(5, 5, 'student', NULL, NULL, '2nd Year High School', NULL, 'Algerian High School', 'مستظهر', NULL),
(6, 6, 'pupil', NULL, NULL, 'Primary School', NULL, 'Algerian Primary School', 'غير خاتم', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `additional_social_status`
--
ALTER TABLE `additional_social_status`
  ADD KEY `individual_id` (`individual_id`);

--
-- Indexes for table `associations`
--
ALTER TABLE `associations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `families`
--
ALTER TABLE `families`
  ADD PRIMARY KEY (`id`),
  ADD KEY `father_id` (`father_id`),
  ADD KEY `mother_id` (`mother_id`);

--
-- Indexes for table `family_relations`
--
ALTER TABLE `family_relations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `individual1_id` (`individual1_id`),
  ADD KEY `individual2_id` (`individual2_id`);

--
-- Indexes for table `individuals`
--
ALTER TABLE `individuals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `national_id` (`national_id`),
  ADD KEY `individuals_ibfk_1` (`social_status_id`);

--
-- Indexes for table `memberships`
--
ALTER TABLE `memberships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `individual_id` (`individual_id`),
  ADD KEY `association_id` (`association_id`);

--
-- Indexes for table `social_status_info`
--
ALTER TABLE `social_status_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `social_status_info_ibfk_1` (`individual_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `associations`
--
ALTER TABLE `associations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `families`
--
ALTER TABLE `families`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `family_relations`
--
ALTER TABLE `family_relations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `individuals`
--
ALTER TABLE `individuals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `memberships`
--
ALTER TABLE `memberships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `social_status_info`
--
ALTER TABLE `social_status_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `additional_social_status`
--
ALTER TABLE `additional_social_status`
  ADD CONSTRAINT `additional_social_status_ibfk_1` FOREIGN KEY (`individual_id`) REFERENCES `individuals` (`id`);

--
-- Constraints for table `families`
--
ALTER TABLE `families`
  ADD CONSTRAINT `families_ibfk_1` FOREIGN KEY (`father_id`) REFERENCES `individuals` (`id`),
  ADD CONSTRAINT `families_ibfk_2` FOREIGN KEY (`mother_id`) REFERENCES `individuals` (`id`);

--
-- Constraints for table `family_relations`
--
ALTER TABLE `family_relations`
  ADD CONSTRAINT `family_relations_ibfk_1` FOREIGN KEY (`individual1_id`) REFERENCES `individuals` (`id`),
  ADD CONSTRAINT `family_relations_ibfk_2` FOREIGN KEY (`individual2_id`) REFERENCES `individuals` (`id`);

--
-- Constraints for table `individuals`
--
ALTER TABLE `individuals`
  ADD CONSTRAINT `individuals_ibfk_1` FOREIGN KEY (`social_status_id`) REFERENCES `social_status_info` (`id`);

--
-- Constraints for table `memberships`
--
ALTER TABLE `memberships`
  ADD CONSTRAINT `memberships_ibfk_1` FOREIGN KEY (`individual_id`) REFERENCES `individuals` (`id`),
  ADD CONSTRAINT `memberships_ibfk_2` FOREIGN KEY (`association_id`) REFERENCES `associations` (`id`);

--
-- Constraints for table `social_status_info`
--
ALTER TABLE `social_status_info`
  ADD CONSTRAINT `social_status_info_ibfk_1` FOREIGN KEY (`individual_id`) REFERENCES `individuals` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
