-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2026 at 12:04 PM
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
-- Database: `cargo_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `mfa_otps`
--

CREATE TABLE `mfa_otps` (
  `otp_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `otp_expiry` datetime NOT NULL,
  `otp_used` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_admin`
--

CREATE TABLE `tbl_admin` (
  `admin_id` int(11) NOT NULL,
  `admin_fname` varchar(100) NOT NULL,
  `admin_lname` varchar(100) NOT NULL,
  `admin_email` varchar(150) NOT NULL,
  `admin_pass` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_booking`
--

CREATE TABLE `tbl_booking` (
  `booking_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `pickup_date` date NOT NULL,
  `return_date` date NOT NULL,
  `day_estimated` int(11) NOT NULL DEFAULT 1,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bkng_status` enum('pending','confirmed','ongoing','completed','cancelled') NOT NULL DEFAULT 'pending',
  `booking_dateAdded` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_car`
--

CREATE TABLE `tbl_car` (
  `car_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `plate_number` varchar(20) NOT NULL,
  `color` varchar(50) NOT NULL,
  `year` year(4) NOT NULL,
  `transmission` varchar(20) DEFAULT 'Automatic',
  `fuel_type` varchar(20) DEFAULT 'Gasoline',
  `seats` int(11) DEFAULT 5,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('available','booked','maintenance') NOT NULL DEFAULT 'available',
  `car_dateAdded` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_car`
--

INSERT INTO `tbl_car` (`car_id`, `model_id`, `plate_number`, `color`, `year`, `transmission`, `fuel_type`, `seats`, `image`, `status`, `car_dateAdded`) VALUES
(1, 2, 'ABC-1234', 'Silver Metallic', '2025', 'Automatic', 'Gasoline', 5, 'assets/img/cars/car_1778031228_c348e470.png', 'available', '2026-05-06 09:36:02'),
(2, 3, 'XYZ-5678', 'Premium Opal', '2025', 'Automatic', 'Gasoline', 5, 'assets/img/cars/car_1779087131_8e573fc1.png', 'available', '2026-05-18 14:52:11'),
(3, 4, 'LMN-9012', 'Graphite Gray', '2026', 'Automatic', 'Gasoline', 5, 'assets/img/cars/car_1779087185_2f1a1a2e.png', 'available', '2026-05-18 14:53:05'),
(4, 5, 'JKL-3456', 'Jungle Green', '2025', 'Automatic', 'Gasoline', 5, 'assets/img/cars/car_1779087251_b951e328.png', 'available', '2026-05-18 14:54:11'),
(5, 6, 'QRS-7890', 'Vivid Red', '2025', 'Automatic', 'Gasoline', 5, 'assets/img/cars/car_1779087319_a448f6ed.png', 'available', '2026-05-18 14:55:19'),
(6, 7, 'TUV-1122', 'Magnetic Silver', '2026', 'Automatic', 'Gasoline', 5, 'assets/img/cars/car_1779087425_c2e9c424.png', 'available', '2026-05-18 14:57:05'),
(7, 8, 'WXY-3344', 'Panther Black', '2025', 'Automatic', 'Gasoline', 5, 'assets/img/cars/car_1779087482_11d9a64f.png', 'available', '2026-05-18 14:58:02'),
(8, 9, 'BCD-5566', 'Stealth Gray', '2025', 'Automatic', 'Gasoline', 5, 'assets/img/cars/car_1779087556_9822ccb0.png', 'available', '2026-05-18 14:59:16'),
(9, 10, 'EFG-7788', 'Soul Red Crystal', '2025', 'Automatic', 'Gasoline', 5, 'assets/img/cars/car_1779087606_9526a145.png', 'available', '2026-05-18 15:00:06'),
(10, 11, 'HIJ-9900', 'Arctic White', '2026', 'Automatic', 'Gasoline', 5, 'assets/img/cars/car_1779087671_f1dccf45.png', 'available', '2026-05-18 15:01:11');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_car_model`
--

CREATE TABLE `tbl_car_model` (
  `model_id` int(11) NOT NULL,
  `model_name` varchar(100) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `car_model_dateAdded` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_car_model`
--

INSERT INTO `tbl_car_model` (`model_id`, `model_name`, `brand`, `type`, `price`, `car_model_dateAdded`) VALUES
(1, 'Vios', 'Toyota', 'Sedan', 1000.00, '2026-05-06 09:05:52'),
(2, 'Vios XLE', 'Toyota', 'Sedan', 1000.00, '2026-05-06 09:33:48'),
(3, 'BR-V VX', 'Honda', 'Sedan', 0.00, '2026-05-18 14:52:11'),
(4, 'Triton GLX', 'Mitsubishi', 'Sedan', 0.00, '2026-05-18 14:53:05'),
(5, 'Jimny GLX', 'Suzuki', 'Sedan', 0.00, '2026-05-18 14:54:11'),
(6, 'Sonet SX', 'Kia', 'Sedan', 0.00, '2026-05-18 14:55:19'),
(7, 'Stargazer X', 'Hyundai', 'Sedan', 0.00, '2026-05-18 14:57:05'),
(8, 'Territory Titanium', 'Ford', 'Sedan', 0.00, '2026-05-18 14:58:02'),
(9, 'Navara PRO-4X', 'Nissan', 'Sedan', 0.00, '2026-05-18 14:59:16'),
(10, 'CX-5 Sport', 'Mazda', 'Sedan', 0.00, '2026-05-18 15:00:06'),
(11, 'Sealion 6', 'BYD', 'Sedan', 0.00, '2026-05-18 15:01:11');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_client`
--

CREATE TABLE `tbl_client` (
  `client_id` int(11) NOT NULL,
  `clnt_fname` varchar(100) NOT NULL,
  `clnt_lname` varchar(100) NOT NULL,
  `clnt_mname` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `clnt_phone_number` varchar(20) NOT NULL,
  `adress` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `mfa_secret` varchar(255) DEFAULT NULL,
  `mfa_otp_code` varchar(10) DEFAULT NULL,
  `mfa_otp_expires` datetime DEFAULT NULL,
  `client_dateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `role` enum('client','admin') NOT NULL DEFAULT 'client',
  `is_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_client`
--

INSERT INTO `tbl_client` (`client_id`, `clnt_fname`, `clnt_lname`, `clnt_mname`, `email`, `clnt_phone_number`, `adress`, `password`, `mfa_secret`, `mfa_otp_code`, `mfa_otp_expires`, `client_dateAdded`, `role`, `is_admin`) VALUES
(1, 'Sha', 'para', 'maravill', 'sharmparadero@gmail.com', '09457566561', 'bacolod', '$2y$10$93ZH36sLFVgNM1Nc6QYep.fueF.mdw/2W1Cd/EurZKlz35M9YeNU6', NULL, NULL, NULL, '2026-04-24 08:37:28', 'client', 0),
(2, 'Admin', 'User', '', 'admin@cargo.com', '09000000000', 'Admin Office', '$2y$10$1ma7HBz2jBf.Ok/k75CkIuEUy7rfPn7C15RgDXyNBYU4RDzxy/xEW', 'V3F7LFDMLWRU72BP4TZPKYC74MUVN635', NULL, NULL, '2026-04-24 14:57:03', 'admin', 1),
(3, 'Nurmie', 'Paradero', 'Maravilla', 'nurmieparadero@gmail.com', '09457566561', 'bacolod', '$2y$10$XODa7pQB32wpgtorxYgi.uWngI3KlSFdx2UmvRjtIEbuZaOTdcWGm', NULL, NULL, NULL, '2026-04-24 11:08:36', 'client', 0),
(4, 'Nhuriel', 'Paradero', 'Maravilla', 'nhur@gmail.com', '09457566561', 'bacolod', '$2y$10$X05RWHpWZ5wIYLtGoZKKZupdkhYUBoikKrdFwu32Ff.DXzaqscNii', NULL, NULL, NULL, '2026-04-24 12:53:42', 'client', 0),
(5, 'Maria', 'Paradero', 'Maravilla', 'ysel@gmail.com', '09457566561', 'bacolod', '$2y$10$956MHqRxxSQk793SDidv5.fNGNfOgi7hRlhBGyG/meT.UhTPRc3wG', 'DZIV4QLHHZ3OWQSSB4FQRWQITITLVHM4', '003190', '2026-05-05 04:02:57', '2026-05-04 11:58:53', 'client', 0),
(6, 'Joanah', 'Gamboa', 'Salcedo', 'joanah@gmail.com', '09457566561', 'Pulupandan', '$2y$10$ZtwdxWme14DkrfULpUTOeOkpbc3.q.J1VoohF6D7tF/cm.3J364r.', NULL, NULL, NULL, '2026-05-04 17:18:16', 'client', 0),
(7, 'Ernesto', 'Brillantes', 'III', 'ern@gmail.com', '09457566561', 'Bacolod', '$2y$10$1jOOfyj1hdP15V1NhXnAduO52ahQiVu3hCMxBvfqaFFVbWcNdJgmK', 'CIQKB4QSWRPUTCQJFF5KPWTVUSEUDZ34', '242732', '2026-05-06 02:37:45', '2026-05-06 02:21:03', 'client', 0),
(8, 'Norbing', 'Paradero', 'Bayer', 'norbs@gmail.com', '09457566561', 'Bacolod', '$2y$10$UaiiQrdKvZXtt9ibjNfuv.Z2JpWZEjrXT1z1snDPL8nTxypcM78Tq', 'BDQIUCEKO7WSKXAFF54UP4T5QBSFI4FR', '713255', '2026-05-18 09:23:29', '2026-05-18 09:11:10', 'client', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_contact`
--

CREATE TABLE `tbl_contact` (
  `contact_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `contact_dateAdded` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_contact`
--

INSERT INTO `tbl_contact` (`contact_id`, `name`, `email`, `subject`, `message`, `is_read`, `contact_dateAdded`) VALUES
(1, 'hatdog', 'sharmparadero@gmail.com', 'Booking Inquiry', 'kjdjfva', 1, '2026-04-24 19:38:48');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_driver`
--

CREATE TABLE `tbl_driver` (
  `driver_id` int(11) NOT NULL,
  `drvr_fname` varchar(100) NOT NULL,
  `drvr_lname` varchar(100) NOT NULL,
  `drvr_mname` varchar(100) DEFAULT NULL,
  `drvr_license_no` varchar(50) NOT NULL,
  `drvr_phone_number` varchar(20) NOT NULL,
  `rate_per_day` decimal(10,2) NOT NULL DEFAULT 0.00,
  `driver_status` enum('available','on_trip','inactive') NOT NULL DEFAULT 'available',
  `driver_dateAdded` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_payment`
--

CREATE TABLE `tbl_payment` (
  `payment_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_date` date DEFAULT NULL,
  `method` enum('cash','gcash','credit_card','bank_transfer') NOT NULL DEFAULT 'cash',
  `payment_status` enum('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
  `payment_dateAdded` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `mfa_otps`
--
ALTER TABLE `mfa_otps`
  ADD PRIMARY KEY (`otp_id`),
  ADD KEY `fk_otp_client` (`client_id`);

--
-- Indexes for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `admin_email` (`admin_email`);

--
-- Indexes for table `tbl_booking`
--
ALTER TABLE `tbl_booking`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `fk_booking_client` (`client_id`),
  ADD KEY `fk_booking_car` (`car_id`),
  ADD KEY `fk_booking_driver` (`driver_id`);

--
-- Indexes for table `tbl_car`
--
ALTER TABLE `tbl_car`
  ADD PRIMARY KEY (`car_id`),
  ADD UNIQUE KEY `uq_plate_number` (`plate_number`),
  ADD KEY `fk_car_model` (`model_id`);

--
-- Indexes for table `tbl_car_model`
--
ALTER TABLE `tbl_car_model`
  ADD PRIMARY KEY (`model_id`);

--
-- Indexes for table `tbl_client`
--
ALTER TABLE `tbl_client`
  ADD PRIMARY KEY (`client_id`),
  ADD UNIQUE KEY `uq_client_email` (`email`);

--
-- Indexes for table `tbl_contact`
--
ALTER TABLE `tbl_contact`
  ADD PRIMARY KEY (`contact_id`);

--
-- Indexes for table `tbl_driver`
--
ALTER TABLE `tbl_driver`
  ADD PRIMARY KEY (`driver_id`),
  ADD UNIQUE KEY `uq_license` (`drvr_license_no`);

--
-- Indexes for table `tbl_payment`
--
ALTER TABLE `tbl_payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `uq_booking_payment` (`booking_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mfa_otps`
--
ALTER TABLE `mfa_otps`
  MODIFY `otp_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_booking`
--
ALTER TABLE `tbl_booking`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_car`
--
ALTER TABLE `tbl_car`
  MODIFY `car_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_car_model`
--
ALTER TABLE `tbl_car_model`
  MODIFY `model_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tbl_client`
--
ALTER TABLE `tbl_client`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_contact`
--
ALTER TABLE `tbl_contact`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_driver`
--
ALTER TABLE `tbl_driver`
  MODIFY `driver_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_payment`
--
ALTER TABLE `tbl_payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `mfa_otps`
--
ALTER TABLE `mfa_otps`
  ADD CONSTRAINT `fk_otp_client` FOREIGN KEY (`client_id`) REFERENCES `tbl_client` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_booking`
--
ALTER TABLE `tbl_booking`
  ADD CONSTRAINT `fk_booking_car` FOREIGN KEY (`car_id`) REFERENCES `tbl_car` (`car_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_booking_client` FOREIGN KEY (`client_id`) REFERENCES `tbl_client` (`client_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_booking_driver` FOREIGN KEY (`driver_id`) REFERENCES `tbl_driver` (`driver_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tbl_car`
--
ALTER TABLE `tbl_car`
  ADD CONSTRAINT `fk_car_model` FOREIGN KEY (`model_id`) REFERENCES `tbl_car_model` (`model_id`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_payment`
--
ALTER TABLE `tbl_payment`
  ADD CONSTRAINT `fk_payment_booking` FOREIGN KEY (`booking_id`) REFERENCES `tbl_booking` (`booking_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
