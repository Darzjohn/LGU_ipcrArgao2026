-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2025 at 02:26 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `elguhrms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `access_logs`
--

CREATE TABLE `access_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `page_accessed` varchar(255) NOT NULL,
  `access_type` enum('unauthorized','login_success','login_failed') NOT NULL DEFAULT 'unauthorized',
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assessments`
--

CREATE TABLE `assessments` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `tax_year` int(4) NOT NULL,
  `year` smallint(6) NOT NULL,
  `assessed_value` decimal(18,2) NOT NULL,
  `basic_tax_rate` decimal(6,4) DEFAULT 0.0100,
  `basic_tax` decimal(18,2) DEFAULT NULL,
  `sef_rate` decimal(6,4) DEFAULT 0.1000,
  `sef_tax` decimal(18,2) DEFAULT NULL,
  `adjustments` decimal(18,2) DEFAULT 0.00,
  `status` enum('draft','finalized') DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `penalties` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessments`
--

INSERT INTO `assessments` (`id`, `property_id`, `tax_year`, `year`, `assessed_value`, `basic_tax_rate`, `basic_tax`, `sef_rate`, `sef_tax`, `adjustments`, `status`, `created_by`, `created_at`, `discount`, `penalties`) VALUES
(55, 8, 2022, 0, 300000.00, 0.0100, 3000.00, 0.1000, 3000.00, 0.00, 'finalized', NULL, '2025-09-26 06:37:57', 0.00, 0.00),
(56, 8, 2023, 0, 300000.00, 0.0100, 3000.00, 0.1000, 3000.00, 0.00, 'finalized', NULL, '2025-09-26 06:38:04', 0.00, 0.00),
(57, 8, 2024, 0, 300000.00, 0.0100, 3000.00, 0.1000, 3000.00, 0.00, 'finalized', NULL, '2025-09-26 06:38:10', 0.00, 0.00),
(58, 8, 2025, 0, 300000.00, 0.0100, 3000.00, 0.1000, 3000.00, 0.00, 'finalized', NULL, '2025-09-26 06:38:19', 0.00, 0.00),
(59, 7, 2020, 0, 200000.00, 0.0100, 2000.00, 0.1000, 2000.00, 0.00, 'finalized', NULL, '2025-09-26 06:38:25', 0.00, 0.00),
(60, 7, 2021, 0, 200000.00, 0.0100, 2000.00, 0.1000, 2000.00, 0.00, 'finalized', NULL, '2025-09-26 06:38:37', 0.00, 0.00),
(61, 7, 2022, 0, 200000.00, 0.0100, 2000.00, 0.1000, 2000.00, 0.00, 'finalized', NULL, '2025-09-26 06:38:47', 0.00, 0.00),
(62, 7, 2023, 0, 200000.00, 0.0100, 2000.00, 0.1000, 2000.00, 0.00, 'finalized', NULL, '2025-09-26 06:38:55', 0.00, 0.00),
(63, 7, 2024, 0, 200000.00, 0.0100, 2000.00, 0.1000, 2000.00, 0.00, 'finalized', NULL, '2025-09-26 06:39:02', 0.00, 0.00),
(64, 7, 2025, 0, 200000.00, 0.0100, 2000.00, 0.1000, 2000.00, 0.00, 'finalized', NULL, '2025-09-26 06:39:18', 0.00, 0.00),
(65, 6, 2020, 0, 100000.00, 0.0100, 1000.00, 0.1000, 1000.00, 0.00, 'finalized', NULL, '2025-09-26 06:39:26', 0.00, 0.00),
(66, 6, 2021, 0, 100000.00, 0.0100, 1000.00, 0.1000, 1000.00, 0.00, 'finalized', NULL, '2025-09-26 06:39:30', 0.00, 0.00),
(67, 6, 2022, 0, 100000.00, 0.0100, 1000.00, 0.1000, 1000.00, 0.00, 'finalized', NULL, '2025-09-26 06:39:36', 0.00, 0.00),
(68, 6, 2023, 0, 100000.00, 0.0100, 1000.00, 0.1000, 1000.00, 0.00, 'finalized', NULL, '2025-09-26 06:39:48', 0.00, 0.00),
(69, 6, 2024, 0, 100000.00, 0.0100, 1000.00, 0.1000, 1000.00, 0.00, 'finalized', NULL, '2025-09-26 06:39:59', 0.00, 0.00),
(70, 6, 2025, 0, 100000.00, 0.0100, 1000.00, 0.1000, 1000.00, 0.00, 'finalized', NULL, '2025-09-26 06:40:13', 0.00, 0.00),
(72, 9, 2020, 0, 400000.00, 0.0100, 4000.00, 0.1000, 4000.00, 0.00, 'finalized', NULL, '2025-09-30 06:07:26', 0.00, 0.00),
(73, 9, 2021, 0, 400000.00, 0.0100, 4000.00, 0.1000, 4000.00, 0.00, 'finalized', NULL, '2025-09-30 06:07:32', 0.00, 0.00),
(74, 9, 2022, 0, 400000.00, 0.0100, 4000.00, 0.1000, 4000.00, 0.00, 'finalized', NULL, '2025-09-30 06:07:38', 0.00, 0.00),
(75, 9, 2023, 0, 400000.00, 0.0100, 4000.00, 0.1000, 4000.00, 0.00, 'finalized', NULL, '2025-09-30 06:07:43', 0.00, 0.00),
(76, 9, 2024, 0, 400000.00, 0.0100, 4000.00, 0.1000, 4000.00, 0.00, 'finalized', NULL, '2025-09-30 06:07:48', 0.00, 0.00),
(77, 9, 2025, 0, 400000.00, 0.0100, 4000.00, 0.1000, 4000.00, 0.00, 'finalized', NULL, '2025-09-30 06:07:59', 0.00, 0.00),
(78, 8, 2026, 0, 300000.00, 0.0100, 3000.00, 0.1000, 3000.00, 0.00, 'finalized', NULL, '2025-10-22 02:48:58', 0.00, 0.00),
(80, 11, 2020, 2020, 300000.00, 0.0100, 3000.00, 0.1000, 3000.00, 0.00, 'finalized', NULL, '2025-10-27 02:21:39', 0.00, 0.00),
(81, 11, 2021, 2021, 300000.00, 0.0100, 3000.00, 0.1000, 3000.00, 0.00, 'finalized', NULL, '2025-10-27 02:21:52', 0.00, 0.00),
(82, 11, 2022, 2022, 300000.00, 0.0100, 3000.00, 0.1000, 3000.00, 0.00, 'finalized', NULL, '2025-10-27 02:22:00', 0.00, 0.00),
(83, 11, 2023, 2023, 300000.00, 0.0100, 3000.00, 0.1000, 3000.00, 0.00, 'finalized', NULL, '2025-10-27 02:22:07', 0.00, 0.00),
(84, 11, 2024, 2024, 300000.00, 0.0100, 3000.00, 0.1000, 3000.00, 0.00, 'finalized', NULL, '2025-10-27 02:22:16', 0.00, 0.00),
(86, 11, 2025, 2025, 300000.00, 0.0100, 3000.00, 0.1000, 3000.00, 0.00, 'finalized', NULL, '2025-10-27 02:45:27', 0.00, 0.00),
(87, 11, 2026, 2026, 300000.00, 0.0100, 3000.00, 0.1000, 3000.00, 0.00, 'finalized', NULL, '2025-10-27 03:00:59', 0.00, 0.00),
(88, 8, 2021, 2021, 300000.00, 0.0100, 3000.00, 0.1000, 3000.00, 0.00, 'finalized', NULL, '2025-10-27 03:21:39', 0.00, 0.00),
(89, 8, 2020, 2020, 300000.00, 0.0100, 3000.00, 0.1000, 3000.00, 0.00, 'finalized', NULL, '2025-10-27 03:28:35', 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_accounts`
--

CREATE TABLE `bank_accounts` (
  `id` int(11) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `beginning_balance` decimal(14,2) DEFAULT 0.00,
  `created_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bank_accounts`
--

INSERT INTO `bank_accounts` (`id`, `account_name`, `account_number`, `bank_name`, `beginning_balance`, `created_by`, `created_at`) VALUES
(1, 'MUNICIPALITY OF ARGAO (DBP)', '111-111-111-111', 'DEVELOPMENT BANK OF THE PHILIPPINES (DBP)', 50000000.00, 'MARIA LUISA C. MAGALLANES', '2025-11-18 01:04:55'),
(2, 'MUNICIPALITY OF ARGAO (LBP)', '222-222-222-222', 'LANDBANK OF THE PHILIPPINES (LBP)', 50000000.00, 'MARIA LUISA C. MAGALLANES', '2025-11-18 01:05:32');

-- --------------------------------------------------------

--
-- Table structure for table `bank_reconciliation`
--

CREATE TABLE `bank_reconciliation` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `book_balance` decimal(15,2) NOT NULL,
  `bank_statement_balance` decimal(15,2) NOT NULL,
  `outstanding_checks` decimal(15,2) NOT NULL DEFAULT 0.00,
  `deposits_in_transit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `service_charge` decimal(15,2) NOT NULL DEFAULT 0.00,
  `bank_adjustments` decimal(15,2) NOT NULL DEFAULT 0.00,
  `reconciled_balance` decimal(15,2) NOT NULL,
  `remarks` text DEFAULT NULL,
  `prepared_by` varchar(100) DEFAULT NULL,
  `prepared_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_transactions`
--

CREATE TABLE `bank_transactions` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `transaction_type` enum('deposit','withdrawal','transfer') NOT NULL,
  `reference_no` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `transfer_to_account_id` int(11) DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `created_by` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `checks_issued`
--

CREATE TABLE `checks_issued` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `fund_source_id` int(11) DEFAULT NULL,
  `serial_no` varchar(100) DEFAULT NULL,
  `dv_payroll_no` varchar(100) DEFAULT NULL,
  `cafoa_no` varchar(100) DEFAULT NULL,
  `sub_allotment` varchar(150) DEFAULT NULL,
  `nature_of_payment` text DEFAULT NULL,
  `gross_amount` decimal(15,2) DEFAULT 0.00,
  `check_no` varchar(100) NOT NULL,
  `payee` varchar(255) NOT NULL,
  `issue_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `remarks` text DEFAULT NULL,
  `cleared` enum('yes','no') DEFAULT 'no',
  `created_by` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `checks_issued`
--

INSERT INTO `checks_issued` (`id`, `account_id`, `fund_source_id`, `serial_no`, `dv_payroll_no`, `cafoa_no`, `sub_allotment`, `nature_of_payment`, `gross_amount`, `check_no`, `payee`, `issue_date`, `amount`, `remarks`, `cleared`, `created_by`, `created_at`) VALUES
(5, 1, 1, '1111111111', '25-0001', '', '', 'FOR SALARARIES OF JOB ORDER', 500000.00, '1111111111', 'JUAN DELA CRUZ', '2025-11-18', 500000.00, '', 'no', 'MARIA LUISA C. MAGALLANES', '2025-11-18 13:12:41'),
(6, 1, 1, '222222222', '25-0002', '', '', 'FOR SALARIES OF CASUAL EMPLOYEES', 350000.00, '222222222', 'JUAN DELA CRUZ', '2025-11-18', 350000.00, '', 'no', 'MARIA LUISA C. MAGALLANES', '2025-11-18 13:14:22');

-- --------------------------------------------------------

--
-- Table structure for table `collections`
--

CREATE TABLE `collections` (
  `id` int(11) NOT NULL,
  `or_no` varchar(50) DEFAULT NULL,
  `previous_or_no` varchar(50) DEFAULT NULL,
  `previous_date_paid` date DEFAULT NULL,
  `previous_year` int(11) DEFAULT NULL,
  `assessment_id` int(11) DEFAULT NULL,
  `tax_year` int(11) DEFAULT NULL,
  `rptsp_no` varchar(100) DEFAULT NULL,
  `td_no` varchar(100) DEFAULT NULL,
  `lot_no` varchar(100) DEFAULT NULL,
  `owner_name` varchar(255) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `classification` varchar(100) DEFAULT NULL,
  `assessed_value` decimal(12,2) DEFAULT NULL,
  `basic_tax` decimal(12,2) DEFAULT NULL,
  `sef_tax` decimal(12,2) DEFAULT NULL,
  `tax_due` decimal(12,2) DEFAULT NULL,
  `adjustments` decimal(12,2) DEFAULT NULL,
  `discount` decimal(12,2) DEFAULT NULL,
  `penalty` decimal(12,2) DEFAULT NULL,
  `total_due` decimal(12,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Paid',
  `processed_by` varchar(100) DEFAULT NULL,
  `assessed_date` datetime DEFAULT NULL,
  `owner` varchar(255) DEFAULT NULL,
  `payor_name` varchar(255) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `total_amount_paid` decimal(12,2) DEFAULT NULL,
  `total_cash_amount` decimal(12,2) DEFAULT NULL,
  `payment_mode` varchar(50) DEFAULT 'Cash',
  `check_number` varchar(50) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `check_date` date DEFAULT NULL,
  `check_amount` decimal(12,2) DEFAULT NULL,
  `treasurer` varchar(100) DEFAULT NULL,
  `collecting_officer` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `collections`
--

INSERT INTO `collections` (`id`, `or_no`, `previous_or_no`, `previous_date_paid`, `previous_year`, `assessment_id`, `tax_year`, `rptsp_no`, `td_no`, `lot_no`, `owner_name`, `barangay`, `location`, `classification`, `assessed_value`, `basic_tax`, `sef_tax`, `tax_due`, `adjustments`, `discount`, `penalty`, `total_due`, `payment_date`, `status`, `processed_by`, `assessed_date`, `owner`, `payor_name`, `year`, `owner_id`, `amount`, `total_amount_paid`, `total_cash_amount`, `payment_mode`, `check_number`, `bank_name`, `check_date`, `check_amount`, `treasurer`, `collecting_officer`) VALUES
(1, '2025-111111111', '2019-111111111', '2019-01-01', 2019, 209, 2020, 'RPTSP-8-170', '02-00001', '02-00001', 'SILVIA FLORES', 'LANGTAD', 'LANGTAD, ARGAO, CEBU', 'LAND', 300000.00, 3000.00, 3000.00, NULL, 0.00, 0.00, 4320.00, 10320.00, '2025-11-06', 'Paid', 'CASHIER NUMBER1', NULL, 'SILVIA FLORES', 'SILVIA FLORES', 2020, NULL, 10320.00, 10320.00, 0.00, 'check', '123456789', 'METROBANK', '2025-11-06', 62040.00, NULL, NULL),
(2, '2025-111111111', '2019-111111111', '2019-01-01', 2019, 210, 2021, 'RPTSP-8-171', '02-00001', '02-00001', 'SILVIA FLORES', 'LANGTAD', 'LANGTAD, ARGAO, CEBU', 'LAND', 300000.00, 3000.00, 3000.00, NULL, 0.00, 0.00, 4320.00, 10320.00, '2025-11-06', 'Paid', 'CASHIER NUMBER1', NULL, 'SILVIA FLORES', 'SILVIA FLORES', 2021, NULL, 10320.00, 10320.00, 0.00, 'check', '123456789', 'METROBANK', '2025-11-06', 62040.00, NULL, NULL),
(3, '2025-111111111', '2019-111111111', '2019-01-01', 2019, 211, 2022, 'RPTSP-8-172', '02-00001', '02-00001', 'SILVIA FLORES', 'LANGTAD', 'LANGTAD, ARGAO, CEBU', 'LAND', 300000.00, 3000.00, 3000.00, NULL, 0.00, 0.00, 4320.00, 10320.00, '2025-11-06', 'Paid', 'CASHIER NUMBER1', NULL, 'SILVIA FLORES', 'SILVIA FLORES', 2022, NULL, 10320.00, 10320.00, 0.00, 'check', '123456789', 'METROBANK', '2025-11-06', 62040.00, NULL, NULL),
(4, '2025-111111111', '2019-111111111', '2019-01-01', 2019, 212, 2023, 'RPTSP-8-173', '02-00001', '02-00001', 'SILVIA FLORES', 'LANGTAD', 'LANGTAD, ARGAO, CEBU', 'LAND', 300000.00, 3000.00, 3000.00, NULL, 0.00, 0.00, 4200.00, 10200.00, '2025-11-06', 'Paid', 'CASHIER NUMBER1', NULL, 'SILVIA FLORES', 'SILVIA FLORES', 2023, NULL, 10200.00, 10200.00, 0.00, 'check', '123456789', 'METROBANK', '2025-11-06', 62040.00, NULL, NULL),
(5, '2025-111111111', '2019-111111111', '2019-01-01', 2019, 213, 2024, 'RPTSP-8-174', '02-00001', '02-00001', 'SILVIA FLORES', 'LANGTAD', 'LANGTAD, ARGAO, CEBU', 'LAND', 300000.00, 3000.00, 3000.00, NULL, 0.00, 0.00, 2760.00, 8760.00, '2025-11-06', 'Paid', 'CASHIER NUMBER1', NULL, 'SILVIA FLORES', 'SILVIA FLORES', 2024, NULL, 8760.00, 8760.00, 0.00, 'check', '123456789', 'METROBANK', '2025-11-06', 62040.00, NULL, NULL),
(6, '2025-111111111', '2019-111111111', '2019-01-01', 2019, 214, 2025, 'RPTSP-8-175', '02-00001', '02-00001', 'SILVIA FLORES', 'LANGTAD', 'LANGTAD, ARGAO, CEBU', 'LAND', 300000.00, 3000.00, 3000.00, NULL, 0.00, 0.00, 1320.00, 7320.00, '2025-11-06', 'Paid', 'CASHIER NUMBER1', NULL, 'SILVIA FLORES', 'SILVIA FLORES', 2025, NULL, 7320.00, 7320.00, 0.00, 'check', '123456789', 'METROBANK', '2025-11-06', 62040.00, NULL, NULL),
(7, '2025-111111111', '2019-111111111', '2019-01-01', 2019, 215, 2026, 'RPTSP-8-176', '02-00001', '02-00001', 'SILVIA FLORES', 'LANGTAD', 'LANGTAD, ARGAO, CEBU', 'LAND', 300000.00, 3000.00, 3000.00, NULL, 0.00, 1200.00, 0.00, 4800.00, '2025-11-06', 'Paid', 'CASHIER NUMBER1', NULL, 'SILVIA FLORES', 'SILVIA FLORES', 2026, NULL, 4800.00, 4800.00, 0.00, 'check', '123456789', 'METROBANK', '2025-11-06', 62040.00, NULL, NULL),
(8, '2025-222222222', '2019-222222222', '2019-01-01', 2019, 202, 2020, 'RPTSP-11-177', '02-00002', '02-00001', 'SHEILA SARAGENA', 'LANGTAD', 'LANGTAD, ARGAO, CEBU', 'LAND', 300000.00, 3000.00, 3000.00, NULL, 0.00, 0.00, 4320.00, 10320.00, '2025-11-06', 'Paid', 'CASHIER NUMBER1', NULL, 'SHEILA SARAGENA', 'SHIELA SARAGENA', 2020, NULL, 10320.00, 10320.00, 62040.00, 'cash', '0', '', NULL, NULL, NULL, NULL),
(9, '2025-222222222', '2019-222222222', '2019-01-01', 2019, 203, 2021, 'RPTSP-11-178', '02-00002', '02-00001', 'SHEILA SARAGENA', 'LANGTAD', 'LANGTAD, ARGAO, CEBU', 'LAND', 300000.00, 3000.00, 3000.00, NULL, 0.00, 0.00, 4320.00, 10320.00, '2025-11-06', 'Paid', 'CASHIER NUMBER1', NULL, 'SHEILA SARAGENA', 'SHIELA SARAGENA', 2021, NULL, 10320.00, 10320.00, 62040.00, 'cash', '0', '', NULL, NULL, NULL, NULL),
(10, '2025-222222222', '2019-222222222', '2019-01-01', 2019, 204, 2022, 'RPTSP-11-179', '02-00002', '02-00001', 'SHEILA SARAGENA', 'LANGTAD', 'LANGTAD, ARGAO, CEBU', 'LAND', 300000.00, 3000.00, 3000.00, NULL, 0.00, 0.00, 4320.00, 10320.00, '2025-11-06', 'Paid', 'CASHIER NUMBER1', NULL, 'SHEILA SARAGENA', 'SHIELA SARAGENA', 2022, NULL, 10320.00, 10320.00, 62040.00, 'cash', '0', '', NULL, NULL, NULL, NULL),
(11, '2025-222222222', '2019-222222222', '2019-01-01', 2019, 205, 2023, 'RPTSP-11-180', '02-00002', '02-00001', 'SHEILA SARAGENA', 'LANGTAD', 'LANGTAD, ARGAO, CEBU', 'LAND', 300000.00, 3000.00, 3000.00, NULL, 0.00, 0.00, 4200.00, 10200.00, '2025-11-06', 'Paid', 'CASHIER NUMBER1', NULL, 'SHEILA SARAGENA', 'SHIELA SARAGENA', 2023, NULL, 10200.00, 10200.00, 62040.00, 'cash', '0', '', NULL, NULL, NULL, NULL),
(12, '2025-222222222', '2019-222222222', '2019-01-01', 2019, 206, 2024, 'RPTSP-11-181', '02-00002', '02-00001', 'SHEILA SARAGENA', 'LANGTAD', 'LANGTAD, ARGAO, CEBU', 'LAND', 300000.00, 3000.00, 3000.00, NULL, 0.00, 0.00, 2760.00, 8760.00, '2025-11-06', 'Paid', 'CASHIER NUMBER1', NULL, 'SHEILA SARAGENA', 'SHIELA SARAGENA', 2024, NULL, 8760.00, 8760.00, 62040.00, 'cash', '0', '', NULL, NULL, NULL, NULL),
(13, '2025-222222222', '2019-222222222', '2019-01-01', 2019, 207, 2025, 'RPTSP-11-182', '02-00002', '02-00001', 'SHEILA SARAGENA', 'LANGTAD', 'LANGTAD, ARGAO, CEBU', 'LAND', 300000.00, 3000.00, 3000.00, NULL, 0.00, 0.00, 1320.00, 7320.00, '2025-11-06', 'Paid', 'CASHIER NUMBER1', NULL, 'SHEILA SARAGENA', 'SHIELA SARAGENA', 2025, NULL, 7320.00, 7320.00, 62040.00, 'cash', '0', '', NULL, NULL, NULL, NULL),
(14, '2025-222222222', '2019-222222222', '2019-01-01', 2019, 208, 2026, 'RPTSP-11-183', '02-00002', '02-00001', 'SHEILA SARAGENA', 'LANGTAD', 'LANGTAD, ARGAO, CEBU', 'LAND', 300000.00, 3000.00, 3000.00, NULL, 0.00, 1200.00, 0.00, 4800.00, '2025-11-06', 'Paid', 'CASHIER NUMBER1', NULL, 'SHEILA SARAGENA', 'SHIELA SARAGENA', 2026, NULL, 4800.00, 4800.00, 62040.00, 'cash', '0', '', NULL, NULL, NULL, NULL),
(21, '2025-333333333', '2019-333333333', '2019-01-01', 2019, 190, 2020, 'RPTSP-9-190', '43-00002', '043-00002', 'ALBOR, CRIS', 'TULIC', 'TULIC, ARGAO, CEBU', 'LAND', 400000.00, 4000.00, 4000.00, NULL, 0.00, 0.00, 5760.00, 13760.00, '2025-11-09', 'Paid', 'MARIA LUISA C. MAGALLANES', NULL, 'ALBOR, CRIS', 'CRIS ALBOR', 2020, NULL, 13760.00, 13760.00, 76320.00, 'cash', '0', '', NULL, NULL, NULL, NULL),
(22, '2025-333333333', '2019-333333333', '2019-01-01', 2019, 191, 2021, 'RPTSP-9-191', '43-00002', '043-00002', 'ALBOR, CRIS', 'TULIC', 'TULIC, ARGAO, CEBU', 'LAND', 400000.00, 4000.00, 4000.00, NULL, 0.00, 0.00, 5760.00, 13760.00, '2025-11-09', 'Paid', 'MARIA LUISA C. MAGALLANES', NULL, 'ALBOR, CRIS', 'CRIS ALBOR', 2021, NULL, 13760.00, 13760.00, 76320.00, 'cash', '0', '', NULL, NULL, NULL, NULL),
(23, '2025-333333333', '2019-333333333', '2019-01-01', 2019, 192, 2022, 'RPTSP-9-192', '43-00002', '043-00002', 'ALBOR, CRIS', 'TULIC', 'TULIC, ARGAO, CEBU', 'LAND', 400000.00, 4000.00, 4000.00, NULL, 0.00, 0.00, 5760.00, 13760.00, '2025-11-09', 'Paid', 'MARIA LUISA C. MAGALLANES', NULL, 'ALBOR, CRIS', 'CRIS ALBOR', 2022, NULL, 13760.00, 13760.00, 76320.00, 'cash', '0', '', NULL, NULL, NULL, NULL),
(24, '2025-333333333', '2019-333333333', '2019-01-01', 2019, 193, 2023, 'RPTSP-9-193', '43-00002', '043-00002', 'ALBOR, CRIS', 'TULIC', 'TULIC, ARGAO, CEBU', 'LAND', 400000.00, 4000.00, 4000.00, NULL, 0.00, 0.00, 5600.00, 13600.00, '2025-11-09', 'Paid', 'MARIA LUISA C. MAGALLANES', NULL, 'ALBOR, CRIS', 'CRIS ALBOR', 2023, NULL, 13600.00, 13600.00, 76320.00, 'cash', '0', '', NULL, NULL, NULL, NULL),
(25, '2025-333333333', '2019-333333333', '2019-01-01', 2019, 194, 2024, 'RPTSP-9-194', '43-00002', '043-00002', 'ALBOR, CRIS', 'TULIC', 'TULIC, ARGAO, CEBU', 'LAND', 400000.00, 4000.00, 4000.00, NULL, 0.00, 0.00, 3680.00, 11680.00, '2025-11-09', 'Paid', 'MARIA LUISA C. MAGALLANES', NULL, 'ALBOR, CRIS', 'CRIS ALBOR', 2024, NULL, 11680.00, 11680.00, 76320.00, 'cash', '0', '', NULL, NULL, NULL, NULL),
(26, '2025-333333333', '2019-333333333', '2019-01-01', 2019, 195, 2025, 'RPTSP-9-195', '43-00002', '043-00002', 'ALBOR, CRIS', 'TULIC', 'TULIC, ARGAO, CEBU', 'LAND', 400000.00, 4000.00, 4000.00, NULL, 0.00, 0.00, 1760.00, 9760.00, '2025-11-09', 'Paid', 'MARIA LUISA C. MAGALLANES', NULL, 'ALBOR, CRIS', 'CRIS ALBOR', 2025, NULL, 9760.00, 9760.00, 76320.00, 'cash', '0', '', NULL, NULL, NULL, NULL),
(27, '2025-444444444', '2019-444444444', '2019-01-01', 2019, 184, 2020, 'RPTSP-7-196', '43-00001', '043-00001', 'ALBERCA, JUAN', 'TULIC', 'TULIC, ARGAO, CEBU', 'LAND', 200000.00, 2000.00, 2000.00, NULL, 0.00, 0.00, 2880.00, 6880.00, '2025-11-09', 'Paid', 'MARIA LUISA C. MAGALLANES', NULL, 'ALBERCA, JUAN', 'JUAN ALBERCA', 2020, NULL, 6880.00, 6880.00, 38160.00, 'check', '13131311313', 'LANDBANK', '2025-11-07', 38160.00, NULL, NULL),
(28, '2025-444444444', '2019-444444444', '2019-01-01', 2019, 185, 2021, 'RPTSP-7-197', '43-00001', '043-00001', 'ALBERCA, JUAN', 'TULIC', 'TULIC, ARGAO, CEBU', 'LAND', 200000.00, 2000.00, 2000.00, NULL, 0.00, 0.00, 2880.00, 6880.00, '2025-11-09', 'Paid', 'MARIA LUISA C. MAGALLANES', NULL, 'ALBERCA, JUAN', 'JUAN ALBERCA', 2021, NULL, 6880.00, 6880.00, 38160.00, 'check', '13131311313', 'LANDBANK', '2025-11-07', 38160.00, NULL, NULL),
(29, '2025-444444444', '2019-444444444', '2019-01-01', 2019, 186, 2022, 'RPTSP-7-198', '43-00001', '043-00001', 'ALBERCA, JUAN', 'TULIC', 'TULIC, ARGAO, CEBU', 'LAND', 200000.00, 2000.00, 2000.00, NULL, 0.00, 0.00, 2880.00, 6880.00, '2025-11-09', 'Paid', 'MARIA LUISA C. MAGALLANES', NULL, 'ALBERCA, JUAN', 'JUAN ALBERCA', 2022, NULL, 6880.00, 6880.00, 38160.00, 'check', '13131311313', 'LANDBANK', '2025-11-07', 38160.00, NULL, NULL),
(30, '2025-444444444', '2019-444444444', '2019-01-01', 2019, 187, 2023, 'RPTSP-7-199', '43-00001', '043-00001', 'ALBERCA, JUAN', 'TULIC', 'TULIC, ARGAO, CEBU', 'LAND', 200000.00, 2000.00, 2000.00, NULL, 0.00, 0.00, 2800.00, 6800.00, '2025-11-09', 'Paid', 'MARIA LUISA C. MAGALLANES', NULL, 'ALBERCA, JUAN', 'JUAN ALBERCA', 2023, NULL, 6800.00, 6800.00, 38160.00, 'check', '13131311313', 'LANDBANK', '2025-11-07', 38160.00, NULL, NULL),
(31, '2025-444444444', '2019-444444444', '2019-01-01', 2019, 188, 2024, 'RPTSP-7-200', '43-00001', '043-00001', 'ALBERCA, JUAN', 'TULIC', 'TULIC, ARGAO, CEBU', 'LAND', 200000.00, 2000.00, 2000.00, NULL, 0.00, 0.00, 1840.00, 5840.00, '2025-11-09', 'Paid', 'MARIA LUISA C. MAGALLANES', NULL, 'ALBERCA, JUAN', 'JUAN ALBERCA', 2024, NULL, 5840.00, 5840.00, 38160.00, 'check', '13131311313', 'LANDBANK', '2025-11-07', 38160.00, NULL, NULL),
(32, '2025-444444444', '2019-444444444', '2019-01-01', 2019, 189, 2025, 'RPTSP-7-201', '43-00001', '043-00001', 'ALBERCA, JUAN', 'TULIC', 'TULIC, ARGAO, CEBU', 'LAND', 200000.00, 2000.00, 2000.00, NULL, 0.00, 0.00, 880.00, 4880.00, '2025-11-09', 'Paid', 'MARIA LUISA C. MAGALLANES', NULL, 'ALBERCA, JUAN', 'JUAN ALBERCA', 2025, NULL, 4880.00, 4880.00, 38160.00, 'check', '13131311313', 'LANDBANK', '2025-11-07', 38160.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ctc_corporation`
--

CREATE TABLE `ctc_corporation` (
  `id` int(11) NOT NULL,
  `ctccorp_no` varchar(100) NOT NULL,
  `year` int(11) NOT NULL,
  `place_of_issue` varchar(255) NOT NULL,
  `date_issued` date NOT NULL,
  `company_fullname` varchar(255) NOT NULL,
  `business_address` varchar(255) NOT NULL,
  `kind_of_org` enum('Corporation','Association','Partnership') NOT NULL,
  `nature_of_business` varchar(255) DEFAULT NULL,
  `incorporation_address` varchar(255) DEFAULT NULL,
  `date_reg` date DEFAULT NULL,
  `rpt_assessedvalue` decimal(15,2) DEFAULT 0.00,
  `gross_receipts` decimal(15,2) DEFAULT 0.00,
  `basic_tax` decimal(15,2) DEFAULT 500.00,
  `additional_tax` decimal(15,2) DEFAULT 0.00,
  `rpt_tax_due` decimal(15,2) DEFAULT 0.00,
  `gr_tax_due` decimal(15,2) DEFAULT 0.00,
  `surcharge` decimal(15,2) DEFAULT 0.00,
  `total_due` decimal(15,2) DEFAULT 0.00,
  `position_authorizedsig` varchar(255) DEFAULT NULL,
  `treasurer` varchar(255) DEFAULT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_by` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ctc_corporation`
--

INSERT INTO `ctc_corporation` (`id`, `ctccorp_no`, `year`, `place_of_issue`, `date_issued`, `company_fullname`, `business_address`, `kind_of_org`, `nature_of_business`, `incorporation_address`, `date_reg`, `rpt_assessedvalue`, `gross_receipts`, `basic_tax`, `additional_tax`, `rpt_tax_due`, `gr_tax_due`, `surcharge`, `total_due`, `position_authorizedsig`, `treasurer`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(1, '00091645', 2025, 'ARGAO, CEBU', '2025-11-07', 'JUAN THE CORP', 'ARGAO, CEBU', 'Corporation', 'GENERAL MERCHANDISE AND CONSTRUCTION SUPPLY', 'CEBU CITY', '2025-11-03', 100000000000.00, 100000000000.00, 500.00, 0.00, 40000000.00, 40000000.00, 17600110.00, 97600610.00, 'JUAN DELA CRUZ', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-07 11:18:06', 'MARIA LUISA C. MAGALLANES', '2025-11-08 23:02:43'),
(2, '00091646', 2025, 'ARGAO, CEBU', '2025-11-08', 'GEORGE THE CORP', 'ARGAO, CEBU', 'Corporation', 'SAND, GRAVEL AND AGREGATES', 'CEBU CITY', '2025-11-03', 10000000.00, 10000000.00, 500.00, 0.00, 4000.00, 4000.00, 1870.00, 10370.00, '', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-08 23:33:40', 'MARIA LUISA C. MAGALLANES', '2025-11-12 17:24:33'),
(15, '00091647', 2025, 'ARGAO, CEBU', '2025-11-13', 'THE LOGOD COMPANY', 'ARGAO, CEBU', 'Corporation', 'LOGOD', 'CEBU CITY', '2025-11-03', 1000000.00, 1000000.00, 500.00, 0.00, 400.00, 400.00, 286.00, 1586.00, '', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-13 11:29:40', NULL, NULL),
(16, '00091648', 2025, 'ARGAO, CEBU', '2025-11-13', 'THE LOGOD COMPANY II', 'ARGAO, CEBU', 'Corporation', 'LOGOD', 'CEBU CITY', '2025-11-03', 2000000.00, 2000000.00, 500.00, 0.00, 800.00, 800.00, 462.00, 2562.00, '', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-13 11:36:26', NULL, NULL),
(17, '00080001', 2025, 'ARGAO, CEBU', '2025-11-17', 'SHEILA UKAY2X', 'POBLACION, ARGAO, CEBU', 'Corporation', 'UKAY2X BUSINESS', 'POBLACION, ARGAO, CEBU', '2025-11-03', 1000000.00, 800000.00, 500.00, 0.00, 400.00, 320.00, 268.40, 1488.40, '', 'CASHIER NUMBER1', 'CASHIER NUMBER1', '2025-11-17 15:33:02', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ctc_individual`
--

CREATE TABLE `ctc_individual` (
  `id` int(11) NOT NULL,
  `ctc_no` varchar(50) NOT NULL,
  `year` int(4) NOT NULL,
  `date_issued` date NOT NULL,
  `place_of_issue` varchar(150) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `citizenship` varchar(100) NOT NULL,
  `place_of_birth` varchar(150) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `profession` varchar(150) DEFAULT NULL,
  `civil_status` varchar(50) DEFAULT NULL,
  `sex` enum('Male','Female') NOT NULL DEFAULT 'Male',
  `gross_receipts` decimal(15,2) DEFAULT 0.00,
  `salaries` decimal(15,2) DEFAULT 0.00,
  `real_property_income` decimal(15,2) DEFAULT 0.00,
  `gr_tax_due` decimal(15,2) DEFAULT 0.00,
  `sal_tax_due` decimal(15,2) DEFAULT 0.00,
  `rpt_tax_due` decimal(15,2) DEFAULT 0.00,
  `surcharge` decimal(15,2) DEFAULT 0.00,
  `basic_tax` decimal(15,2) DEFAULT 0.00,
  `additional_tax` decimal(15,2) DEFAULT 0.00,
  `total_due` decimal(15,2) DEFAULT 0.00,
  `treasurer` varchar(150) DEFAULT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_by` varchar(150) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ctc_individual`
--

INSERT INTO `ctc_individual` (`id`, `ctc_no`, `year`, `date_issued`, `place_of_issue`, `surname`, `firstname`, `middlename`, `address`, `citizenship`, `place_of_birth`, `date_of_birth`, `weight`, `height`, `profession`, `civil_status`, `sex`, `gross_receipts`, `salaries`, `real_property_income`, `gr_tax_due`, `sal_tax_due`, `rpt_tax_due`, `surcharge`, `basic_tax`, `additional_tax`, `total_due`, `treasurer`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(1, '12664938', 2025, '2025-11-07', 'ARGAO, CEBU', 'DELA CRUZ', 'JUAN', 'GOMEZ', 'ARGAO, CEBU', 'FILIPINO', 'CEBU CITY', '1981-01-02', 80.00, 174.00, 'Engineer', 'Single', 'Male', 100000000000.00, 100000000000.00, 100000000000.00, 100000000.00, 100000000.00, 100000000.00, 66000001.10, 5.00, 0.00, 366000006.10, 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-07 08:15:32', 'MARIA LUISA C. MAGALLANES', '2025-11-09 21:13:08'),
(2, '11111111', 2025, '2025-11-08', 'ARGAO, CEBU', 'DELA CRUZ', 'GEORGE', 'GOMEZ', 'ARGAO, CEBU', 'FILIPINO', 'CEBU CITY', '1985-05-05', 100.00, 167.00, 'SEAMAN', 'Single', 'Male', 1000000.00, 1000000.00, 1000000.00, 1000.00, 1000.00, 1000.00, 661.10, 5.00, 0.00, 3666.10, 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-08 23:28:42', 'MARIA LUISA C. MAGALLANES', '2025-11-11 13:49:13'),
(5, '11111112', 2025, '2025-11-12', 'ARGAO, CEBU', 'QUIBOLOY', 'MARK', 'FLORES', 'POBLACION, ARGAO, CEBU', 'FILIPINO', 'CEBU CITY', '1985-02-02', 75.00, 170.00, 'TEACHER', 'Single', 'Male', 100000.00, 1200000.00, 10000000.00, 100.00, 1200.00, 10000.00, 2487.10, 5.00, 0.00, 13792.10, 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-12 16:03:17', 'MARIA LUISA C. MAGALLANES', '2025-11-12 16:08:02'),
(6, '1231231231', 2025, '2025-11-17', 'ARGAO, CEBU', 'GOMEZ', 'ANGELO', 'PUGOY', 'ARGAO, CEBU', 'FILIPINO', 'CEBU CITY', '1981-01-02', 75.00, 167.00, 'ENGINEER', 'Married', 'Male', 1000000.00, 100000.00, 1000000.00, 1000.00, 100.00, 1000.00, 463.10, 5.00, 0.00, 2568.10, 'CASHIER NUMBER1', 'CASHIER NUMBER1', '2025-11-17 15:19:24', NULL, '2025-11-17 15:19:24'),
(7, '1231231232', 2025, '2025-11-17', 'ARGAO, CEBU', 'GOMEZ', 'ANGELA', 'PUGOY', 'Poblacion, Argao, Cebu', 'FILIPINO', 'CEBU CITY', '1990-05-05', 55.00, 164.00, 'TEACHER', 'Single', 'Female', 100000.00, 100000.00, 100000.00, 100.00, 100.00, 100.00, 67.10, 5.00, 0.00, 372.10, 'CASHIER NUMBER1', 'CASHIER NUMBER1', '2025-11-17 15:27:34', NULL, '2025-11-17 15:27:34');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`) VALUES
(1, 'MAYOR\'S OFFICE'),
(2, 'BPLO OFFICE'),
(3, 'ASSESSOR\'S OFFICE'),
(4, 'IT OFFICE');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) UNSIGNED NOT NULL,
  `emp_idno` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `department_id` int(11) UNSIGNED DEFAULT NULL,
  `position_id` int(11) UNSIGNED DEFAULT NULL,
  `employment_status_id` int(11) DEFAULT NULL,
  `sss_no` varchar(50) DEFAULT NULL,
  `gsis_no` varchar(50) DEFAULT NULL,
  `tin_no` varchar(50) DEFAULT NULL,
  `pagibig_no` varchar(50) DEFAULT NULL,
  `phic_no` varchar(50) DEFAULT NULL,
  `emergency_contact_person` varchar(150) DEFAULT NULL,
  `emergency_contact_no` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `surname` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `name_extension` varchar(20) DEFAULT NULL,
  `place_of_birth` varchar(100) DEFAULT NULL,
  `citizenship` varchar(255) DEFAULT NULL,
  `sex` enum('Male','Female') DEFAULT NULL,
  `civil_status` enum('Single','Married','Widowed','Separated','Other') DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `UMID_IdNo` varchar(50) DEFAULT NULL,
  `PhilSys_IdNo` varchar(50) DEFAULT NULL,
  `Agency_EmployeeNo` varchar(50) DEFAULT NULL,
  `ra_house_block_lotno` varchar(255) DEFAULT NULL,
  `ra_street` varchar(255) DEFAULT NULL,
  `ra_subdivisionvillage` varchar(255) DEFAULT NULL,
  `ra_barangay` varchar(255) DEFAULT NULL,
  `ra_citymunicipality` varchar(255) DEFAULT NULL,
  `ra_province` varchar(255) DEFAULT NULL,
  `pa_house_block_lotno` varchar(255) DEFAULT NULL,
  `pa_street` varchar(255) DEFAULT NULL,
  `pa_subdivisionvillage` varchar(255) DEFAULT NULL,
  `pa_barangay` varchar(255) DEFAULT NULL,
  `pa_citymunicipality` varchar(255) DEFAULT NULL,
  `pa_province` varchar(255) DEFAULT NULL,
  `telephon_no` varchar(50) DEFAULT NULL,
  `mobile_no` varchar(50) DEFAULT NULL,
  `email_address` varchar(100) DEFAULT NULL,
  `name` varchar(255) GENERATED ALWAYS AS (concat_ws(' ',`first_name`,`middle_name`,`surname`,`name_extension`)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `emp_idno`, `photo`, `email`, `dob`, `blood_type`, `department_id`, `position_id`, `employment_status_id`, `sss_no`, `gsis_no`, `tin_no`, `pagibig_no`, `phic_no`, `emergency_contact_person`, `emergency_contact_no`, `created_at`, `updated_at`, `surname`, `first_name`, `middle_name`, `name_extension`, `place_of_birth`, `citizenship`, `sex`, `civil_status`, `height`, `weight`, `UMID_IdNo`, `PhilSys_IdNo`, `Agency_EmployeeNo`, `ra_house_block_lotno`, `ra_street`, `ra_subdivisionvillage`, `ra_barangay`, `ra_citymunicipality`, `ra_province`, `pa_house_block_lotno`, `pa_street`, `pa_subdivisionvillage`, `pa_barangay`, `pa_citymunicipality`, `pa_province`, `telephon_no`, `mobile_no`, `email_address`) VALUES
(1, '2025-0580', 'EMP_1765425380.png', 'charl2on381@gmail.com', '1981-01-02', 'O+', 2, 2, 1, '222-222-222-222', '333-333-333-333', '444-444-444-444', '555-555-555-555', '666-666-666-666', 'WILMA ALBISO TEO', '09955787565', '2025-12-11 03:56:20', '2025-12-11 04:08:16', 'TEO', 'CHARLTON', 'HOCAMIS', '', 'CEBU CITY', 'FILIPINO', 'Male', 'Married', 174.00, 85.00, '777-777-777-777', '888-888-888-888', '111-111-111-111', '123', 'STO. TOMAS STREET', 'TEO VILLAGE', 'TALAGA', 'ARGAO', 'CEBU', '123', 'STO. TOMAS STREET', 'TEO VILLAGE', 'TALAGA', 'ARGAO', 'CEBU', '123456789', '09914331455', 'charmiexheena@gmail.com'),
(2, '2025-0581', 'EMP_1765434084.png', 'realylanawan@gmail.com', '1985-12-12', 'O+', 4, 4, 1, '001001001', '002002002', '003003003', '004004004', '005005005', 'JUVELYN LANAWAN', '09955787565', '2025-12-11 06:21:24', '2025-12-11 08:13:26', 'LANAWAN', 'REALY', 'MANILA', '', 'ARGAO', 'FILIPINO', 'Male', 'Married', 165.00, 75.00, '006006006', '007007007', '111-111-111-111', '123', 'ST', 'VILLAGE', 'CANBUNUA', 'ARGAO', 'CEBU', '123', 'ST', 'VILLAGE', 'CANBUNUA', 'ARGAO', 'CEBU', '147258369', '09914331455', 'realy.lanawan@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `employment_status`
--

CREATE TABLE `employment_status` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employment_status`
--

INSERT INTO `employment_status` (`id`, `name`, `created_at`) VALUES
(1, 'PERMANENT', '2025-12-11 03:15:16'),
(2, 'JOB ORDER', '2025-12-11 03:15:16'),
(3, 'CASUAL', '2025-12-11 03:15:16'),
(4, 'CONTRACTUAL', '2025-12-11 03:15:16'),
(5, 'ELECTED', '2025-12-11 03:15:16'),
(6, 'CO-TERMINUS', '2025-12-11 03:15:16'),
(7, 'TEMPORARY', '2025-12-11 03:19:33'),
(8, 'PROJECT BASED', '2025-12-11 03:19:42'),
(9, 'PROBITIONARY', '2025-12-11 03:19:53');

-- --------------------------------------------------------

--
-- Table structure for table `form51`
--

CREATE TABLE `form51` (
  `id` int(11) NOT NULL,
  `or_no` varchar(50) NOT NULL,
  `date_issued` date NOT NULL,
  `payor_name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `payment_mode` enum('cash','check') DEFAULT 'cash',
  `total_cash_paid` decimal(12,2) DEFAULT 0.00,
  `check_number` varchar(100) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `check_date` date DEFAULT NULL,
  `check_amount` decimal(12,2) DEFAULT 0.00,
  `treasurer` varchar(100) DEFAULT NULL,
  `grand_total` decimal(12,2) DEFAULT 0.00,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form51`
--

INSERT INTO `form51` (`id`, `or_no`, `date_issued`, `payor_name`, `address`, `payment_mode`, `total_cash_paid`, `check_number`, `bank_name`, `check_date`, `check_amount`, `treasurer`, `grand_total`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(4, '9191736', '2025-11-11', 'SHIELA SARAGENA', 'ARGAO, CEBU', 'cash', 115.00, '', '', NULL, 0.00, 'MARIA LUISA C. MAGALLANES', 115.00, 'MARIA LUISA C. MAGALLANES', '2025-11-11 03:05:07', 'MARIA LUISA C. MAGALLANES', '2025-11-12 05:31:57'),
(5, '9191737', '2025-11-11', 'REALY LANAWAN - REALY LANAWAN - REALY LANAWAN - REALY LANAWAN', 'CANBANUA, ARGAO, CEBU', 'cash', 1651.41, '', '', NULL, 0.00, 'MARIA LUISA C. MAGALLANES', 1651.41, 'MARIA LUISA C. MAGALLANES', '2025-11-11 03:19:54', 'MARIA LUISA C. MAGALLANES', '2025-11-12 03:45:42'),
(9, '9191802', '2025-11-13', 'CRIS ALBOR', 'CANBANUA, ARGAO, CEBU', 'cash', 530.00, '', '', NULL, 0.00, 'MARIA LUISA C. MAGALLANES', 530.00, 'MARIA LUISA C. MAGALLANES', '2025-11-13 05:43:51', NULL, NULL),
(10, '8080123', '2025-11-17', 'JOHNY BRAVO', 'ARGAO, CEBU', 'cash', 135.00, '', '', NULL, 0.00, 'CASHIER NUMBER1', 135.00, 'CASHIER NUMBER1', '2025-11-17 07:46:06', NULL, NULL),
(11, '8080124', '2025-11-17', 'CARLO UNAGAN', 'ARGAO, CEBU', 'cash', 235.00, '', '', NULL, 0.00, 'CASHIER NUMBER1', 235.00, 'CASHIER NUMBER1', '2025-11-17 08:03:53', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `form51_items`
--

CREATE TABLE `form51_items` (
  `id` int(11) NOT NULL,
  `form51_id` int(11) NOT NULL,
  `ngas_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form51_items`
--

INSERT INTO `form51_items` (`id`, `form51_id`, `ngas_id`, `amount`) VALUES
(66, 5, 8, 30.00),
(67, 5, 13, 250.50),
(68, 5, 12, 250.00),
(69, 5, 11, 250.00),
(70, 5, 56, 85.50),
(71, 5, 58, 85.12),
(72, 5, 57, 150.00),
(73, 5, 59, 150.13),
(74, 5, 60, 150.00),
(75, 5, 13, 250.16),
(76, 4, 8, 30.00),
(77, 4, 56, 85.00),
(104, 9, 8, 30.00),
(105, 9, 13, 250.00),
(106, 9, 12, 250.00),
(107, 10, 8, 30.00),
(108, 10, 11, 105.00),
(109, 11, 8, 30.00),
(110, 11, 13, 205.00);

-- --------------------------------------------------------

--
-- Table structure for table `form58`
--

CREATE TABLE `form58` (
  `id` int(11) NOT NULL,
  `or_no` varchar(50) NOT NULL,
  `date_paid` date NOT NULL,
  `payor_name` varchar(255) NOT NULL,
  `city_or_municipality` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `name_of_deceased` varchar(255) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `age` int(3) DEFAULT NULL,
  `sex` enum('Male','Female') DEFAULT NULL,
  `date_of_death` date DEFAULT NULL,
  `case_of_death` varchar(255) DEFAULT NULL,
  `name_of_cemetery` varchar(255) DEFAULT NULL,
  `infectious_or_noninfectious` varchar(50) DEFAULT NULL,
  `embalmed_or_notembalmed` varchar(50) DEFAULT NULL,
  `disposition_of_remains` varchar(255) DEFAULT NULL,
  `amount_of_fee` decimal(12,2) DEFAULT 0.00,
  `payment_date` date DEFAULT NULL,
  `amount_received` decimal(12,2) DEFAULT 0.00,
  `treasurer` varchar(255) DEFAULT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_by` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form58`
--

INSERT INTO `form58` (`id`, `or_no`, `date_paid`, `payor_name`, `city_or_municipality`, `province`, `name_of_deceased`, `nationality`, `age`, `sex`, `date_of_death`, `case_of_death`, `name_of_cemetery`, `infectious_or_noninfectious`, `embalmed_or_notembalmed`, `disposition_of_remains`, `amount_of_fee`, `payment_date`, `amount_received`, `treasurer`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(2, '0386475', '2025-11-11', 'JUAN DELA CRUZ', 'Argao', 'Cebu', 'JIJI DELA CRUZ', 'FILIPINO', 78, 'Female', '2025-11-07', 'HEART ATTACK', 'ARGAO CEMETERY', 'Non', 'Embalmed', 'xxxxxxx', 50.00, '2025-11-11', 50.00, 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 13:01:23', 'MARIA LUISA C. MAGALLANES', '2025-11-12 12:23:05'),
(3, '10000001', '2025-11-17', 'MISTER BEAN', 'ARGAO', 'CEBU', 'MISTER WOMAN', 'FILIPINO', 78, 'Male', '2025-11-03', 'HEART ATTACK', 'ARGAO CEMETERY', 'Non', 'Embalmed', '', 50.00, '2025-11-17', 50.00, 'CASHIER NUMBER1', 'CASHIER NUMBER1', '2025-11-17 16:46:14', 'CASHIER NUMBER1', '2025-11-17 16:50:20');

-- --------------------------------------------------------

--
-- Table structure for table `fund_source`
--

CREATE TABLE `fund_source` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fund_source`
--

INSERT INTO `fund_source` (`id`, `name`, `code`, `status`, `created_by`, `created_at`) VALUES
(1, 'GENERAL FUND', '101', 1, 'MARIA LUISA C. MAGALLANES', '2025-11-18 09:57:11'),
(2, 'TRUST FUND', '202', 1, 'MARIA LUISA C. MAGALLANES', '2025-11-18 09:57:33'),
(3, 'SPECIAL EDUCATION FUND (SEF)', '303', 1, 'MARIA LUISA C. MAGALLANES', '2025-11-18 09:58:00');

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `role` enum('admin','assessor','assessment_clerk','treasurer','cashier','viewer') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `login_time` datetime DEFAULT current_timestamp(),
  `logout_time` datetime DEFAULT NULL,
  `status` enum('success','failed') DEFAULT 'success'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `assessment_id` int(11) NOT NULL,
  `tax_bill_id` int(11) NOT NULL,
  `rptsp_no` varchar(50) NOT NULL,
  `action` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `assessment_id`, `tax_bill_id`, `rptsp_no`, `action`, `created_at`) VALUES
(1, NULL, 77, 93, 'RPTSP-9-93', 'Finalized assessment & generated RPTSP', '2025-09-30 06:27:02'),
(2, NULL, 76, 94, 'RPTSP-9-94', 'Finalized assessment & generated RPTSP', '2025-09-30 06:27:03'),
(3, NULL, 75, 95, 'RPTSP-9-95', 'Finalized assessment & generated RPTSP', '2025-09-30 06:27:05'),
(4, NULL, 74, 96, 'RPTSP-9-96', 'Finalized assessment & generated RPTSP', '2025-09-30 06:27:06'),
(5, NULL, 73, 97, 'RPTSP-9-97', 'Finalized assessment & generated RPTSP', '2025-09-30 06:27:07'),
(6, NULL, 72, 98, 'RPTSP-9-98', 'Finalized assessment & generated RPTSP', '2025-09-30 06:27:09'),
(7, NULL, 70, 99, 'RPTSP-6-99', 'Finalized assessment & generated RPTSP', '2025-09-30 06:27:10'),
(8, NULL, 69, 100, 'RPTSP-6-100', 'Finalized assessment & generated RPTSP', '2025-09-30 06:27:11'),
(9, NULL, 68, 101, 'RPTSP-6-101', 'Finalized assessment & generated RPTSP', '2025-09-30 06:27:13'),
(10, NULL, 67, 102, 'RPTSP-6-102', 'Finalized assessment & generated RPTSP', '2025-09-30 06:27:14'),
(11, NULL, 66, 103, 'RPTSP-6-103', 'Finalized assessment & generated RPTSP', '2025-09-30 06:27:18'),
(12, NULL, 65, 104, 'RPTSP-6-104', 'Finalized assessment & generated RPTSP', '2025-09-30 06:27:23'),
(13, NULL, 63, 105, 'RPTSP-7-105', 'Finalized assessment & generated RPTSP', '2025-09-30 06:27:27'),
(14, NULL, 64, 106, 'RPTSP-7-106', 'Finalized assessment & generated RPTSP', '2025-09-30 06:27:52'),
(15, NULL, 61, 107, 'RPTSP-7-107', 'Finalized assessment & generated RPTSP', '2025-09-30 06:27:58'),
(16, NULL, 60, 108, 'RPTSP-7-108', 'Finalized assessment & generated RPTSP', '2025-09-30 06:28:03'),
(17, NULL, 62, 109, 'RPTSP-7-109', 'Finalized assessment & generated RPTSP', '2025-09-30 06:28:23'),
(18, NULL, 59, 110, 'RPTSP-7-110', 'Finalized assessment & generated RPTSP', '2025-09-30 06:28:26'),
(19, NULL, 58, 111, 'RPTSP-8-111', 'Finalized assessment & generated RPTSP', '2025-09-30 06:28:30'),
(20, NULL, 57, 112, 'RPTSP-8-112', 'Finalized assessment & generated RPTSP', '2025-09-30 06:28:33'),
(21, NULL, 56, 113, 'RPTSP-8-113', 'Finalized assessment & generated RPTSP', '2025-09-30 06:28:55'),
(22, NULL, 55, 114, 'RPTSP-8-114', 'Finalized assessment & generated RPTSP', '2025-09-30 06:28:58'),
(23, NULL, 77, 115, 'RPTSP-9-115', 'Finalized assessment & generated RPTSP', '2025-09-30 06:38:02'),
(24, NULL, 78, 116, 'RPTSP-8-116', 'Finalized assessment & generated RPTSP', '2025-10-22 02:49:04'),
(25, 10, 80, 117, 'RPTSP-11-117', 'Finalized assessment & generated RPTSP', '2025-10-27 02:38:43'),
(26, 10, 81, 118, 'RPTSP-11-118', 'Finalized assessment & generated RPTSP', '2025-10-27 02:38:47'),
(27, 10, 82, 119, 'RPTSP-11-119', 'Finalized assessment & generated RPTSP', '2025-10-27 02:38:51'),
(28, 10, 83, 120, 'RPTSP-11-120', 'Finalized assessment & generated RPTSP', '2025-10-27 02:38:52'),
(29, 10, 84, 121, 'RPTSP-11-121', 'Finalized assessment & generated RPTSP', '2025-10-27 02:38:53'),
(30, 10, 85, 122, 'RPTSP-11-122', 'Finalized assessment & generated RPTSP', '2025-10-27 02:38:54'),
(31, 10, 86, 123, 'RPTSP-11-123', 'Finalized assessment & generated RPTSP', '2025-10-27 02:45:33'),
(32, 10, 87, 124, 'RPTSP-11-124', 'Finalized assessment & generated RPTSP', '2025-10-27 03:01:01'),
(33, 10, 87, 125, 'RPTSP-11-125', 'Finalized assessment & generated RPTSP', '2025-10-27 03:11:43'),
(34, 10, 86, 126, 'RPTSP-11-126', 'Finalized assessment & generated RPTSP', '2025-10-27 03:11:44'),
(35, 10, 84, 127, 'RPTSP-11-127', 'Finalized assessment & generated RPTSP', '2025-10-27 03:11:44'),
(36, 10, 83, 128, 'RPTSP-11-128', 'Finalized assessment & generated RPTSP', '2025-10-27 03:11:45'),
(37, 10, 82, 129, 'RPTSP-11-129', 'Finalized assessment & generated RPTSP', '2025-10-27 03:11:46'),
(38, 10, 75, 130, 'RPTSP-9-130', 'Finalized assessment & generated RPTSP', '2025-10-27 03:12:20'),
(39, 10, 87, 131, 'RPTSP-11-131', 'Finalized assessment & generated RPTSP', '2025-10-27 03:12:29'),
(40, 10, 86, 132, 'RPTSP-11-132', 'Finalized assessment & generated RPTSP', '2025-10-27 03:12:31'),
(41, 10, 84, 133, 'RPTSP-11-133', 'Finalized assessment & generated RPTSP', '2025-10-27 03:12:32'),
(42, 10, 83, 134, 'RPTSP-11-134', 'Finalized assessment & generated RPTSP', '2025-10-27 03:12:33'),
(43, 10, 82, 135, 'RPTSP-11-135', 'Finalized assessment & generated RPTSP', '2025-10-27 03:12:34'),
(44, 10, 81, 136, 'RPTSP-11-136', 'Finalized assessment & generated RPTSP', '2025-10-27 03:12:37'),
(45, 10, 80, 137, 'RPTSP-11-137', 'Finalized assessment & generated RPTSP', '2025-10-27 03:12:38'),
(46, 10, 78, 138, 'RPTSP-8-138', 'Finalized assessment & generated RPTSP', '2025-10-27 03:12:39'),
(47, 10, 77, 139, 'RPTSP-9-139', 'Finalized assessment & generated RPTSP', '2025-10-27 03:12:40'),
(48, 10, 76, 140, 'RPTSP-9-140', 'Finalized assessment & generated RPTSP', '2025-10-27 03:12:41'),
(49, 10, 74, 141, 'RPTSP-9-141', 'Finalized assessment & generated RPTSP', '2025-10-27 03:12:51'),
(50, 10, 65, 142, 'RPTSP-6-142', 'Finalized assessment & generated RPTSP', '2025-10-27 03:12:55'),
(51, 10, 73, 143, 'RPTSP-9-143', 'Finalized assessment & generated RPTSP', '2025-10-27 03:12:57'),
(52, 10, 72, 144, 'RPTSP-9-144', 'Finalized assessment & generated RPTSP', '2025-10-27 03:12:59'),
(53, 10, 66, 145, 'RPTSP-6-145', 'Finalized assessment & generated RPTSP', '2025-10-27 03:13:04'),
(54, 10, 70, 146, 'RPTSP-6-146', 'Finalized assessment & generated RPTSP', '2025-10-27 03:13:22'),
(55, 10, 69, 147, 'RPTSP-6-147', 'Finalized assessment & generated RPTSP', '2025-10-27 03:13:26'),
(56, 10, 67, 148, 'RPTSP-6-148', 'Finalized assessment & generated RPTSP', '2025-10-27 03:13:29'),
(57, 10, 68, 149, 'RPTSP-6-149', 'Finalized assessment & generated RPTSP', '2025-10-27 03:13:32'),
(58, 10, 64, 150, 'RPTSP-7-150', 'Finalized assessment & generated RPTSP', '2025-10-27 03:13:42'),
(59, 10, 63, 151, 'RPTSP-7-151', 'Finalized assessment & generated RPTSP', '2025-10-27 03:13:45'),
(60, 10, 62, 152, 'RPTSP-7-152', 'Finalized assessment & generated RPTSP', '2025-10-27 03:13:48'),
(61, 10, 61, 153, 'RPTSP-7-153', 'Finalized assessment & generated RPTSP', '2025-10-27 03:13:51'),
(62, 10, 55, 154, 'RPTSP-8-154', 'Finalized assessment & generated RPTSP', '2025-10-27 03:13:54'),
(63, 10, 60, 155, 'RPTSP-7-155', 'Finalized assessment & generated RPTSP', '2025-10-27 03:13:57'),
(64, 10, 56, 156, 'RPTSP-8-156', 'Finalized assessment & generated RPTSP', '2025-10-27 03:13:59'),
(65, 10, 59, 157, 'RPTSP-7-157', 'Finalized assessment & generated RPTSP', '2025-10-27 03:14:02'),
(66, 10, 57, 158, 'RPTSP-8-158', 'Finalized assessment & generated RPTSP', '2025-10-27 03:14:05'),
(67, 10, 58, 159, 'RPTSP-8-159', 'Finalized assessment & generated RPTSP', '2025-10-27 03:14:08'),
(68, 10, 77, 160, 'RPTSP-9-160', 'Finalized assessment & generated RPTSP', '2025-10-27 03:16:17'),
(69, 10, 76, 161, 'RPTSP-9-161', 'Finalized assessment & generated RPTSP', '2025-10-27 03:16:19'),
(70, 10, 64, 162, 'RPTSP-7-162', 'Finalized assessment & generated RPTSP', '2025-10-27 03:20:03'),
(71, 10, 63, 163, 'RPTSP-7-163', 'Finalized assessment & generated RPTSP', '2025-10-27 03:20:09'),
(72, 10, 62, 164, 'RPTSP-7-164', 'Finalized assessment & generated RPTSP', '2025-10-27 03:20:14'),
(73, 10, 61, 165, 'RPTSP-7-165', 'Finalized assessment & generated RPTSP', '2025-10-27 03:20:19'),
(74, 10, 59, 166, 'RPTSP-7-166', 'Finalized assessment & generated RPTSP', '2025-10-27 03:20:24'),
(75, 10, 60, 167, 'RPTSP-7-167', 'Finalized assessment & generated RPTSP', '2025-10-27 03:20:30'),
(76, 10, 72, 168, 'RPTSP-9-168', 'Finalized assessment & generated RPTSP', '2025-10-27 03:20:39'),
(77, 10, 64, 169, 'RPTSP-7-169', 'Finalized assessment & generated RPTSP', '2025-10-27 03:20:55'),
(78, 10, 89, 170, 'RPTSP-8-170', 'Finalized assessment & generated RPTSP', '2025-10-27 03:28:42'),
(79, 10, 88, 171, 'RPTSP-8-171', 'Finalized assessment & generated RPTSP', '2025-10-27 03:28:51'),
(80, 10, 55, 172, 'RPTSP-8-172', 'Finalized assessment & generated RPTSP', '2025-10-27 03:28:58'),
(81, 10, 56, 173, 'RPTSP-8-173', 'Finalized assessment & generated RPTSP', '2025-10-27 03:29:05'),
(82, 10, 57, 174, 'RPTSP-8-174', 'Finalized assessment & generated RPTSP', '2025-10-27 03:29:11'),
(83, 10, 58, 175, 'RPTSP-8-175', 'Finalized assessment & generated RPTSP', '2025-10-27 03:29:21'),
(84, 10, 78, 176, 'RPTSP-8-176', 'Finalized assessment & generated RPTSP', '2025-10-27 03:29:26'),
(85, 10, 80, 177, 'RPTSP-11-177', 'Finalized assessment & generated RPTSP', '2025-10-27 03:29:38'),
(86, 10, 81, 178, 'RPTSP-11-178', 'Finalized assessment & generated RPTSP', '2025-10-27 03:29:44'),
(87, 10, 82, 179, 'RPTSP-11-179', 'Finalized assessment & generated RPTSP', '2025-10-27 03:29:49'),
(88, 10, 83, 180, 'RPTSP-11-180', 'Finalized assessment & generated RPTSP', '2025-10-27 03:29:56'),
(89, 10, 84, 181, 'RPTSP-11-181', 'Finalized assessment & generated RPTSP', '2025-10-27 03:30:04'),
(90, 10, 86, 182, 'RPTSP-11-182', 'Finalized assessment & generated RPTSP', '2025-10-27 03:30:08'),
(91, 10, 87, 183, 'RPTSP-11-183', 'Finalized assessment & generated RPTSP', '2025-10-27 03:30:15'),
(92, 10, 65, 184, 'RPTSP-6-184', 'Finalized assessment & generated RPTSP', '2025-10-27 03:30:24'),
(93, 10, 66, 185, 'RPTSP-6-185', 'Finalized assessment & generated RPTSP', '2025-10-27 03:30:28'),
(94, 10, 67, 186, 'RPTSP-6-186', 'Finalized assessment & generated RPTSP', '2025-10-27 03:30:34'),
(95, 10, 68, 187, 'RPTSP-6-187', 'Finalized assessment & generated RPTSP', '2025-10-27 03:30:39'),
(96, 10, 69, 188, 'RPTSP-6-188', 'Finalized assessment & generated RPTSP', '2025-10-27 03:30:43'),
(97, 10, 70, 189, 'RPTSP-6-189', 'Finalized assessment & generated RPTSP', '2025-10-27 03:30:47'),
(98, 10, 72, 190, 'RPTSP-9-190', 'Finalized assessment & generated RPTSP', '2025-10-27 03:30:51'),
(99, 10, 73, 191, 'RPTSP-9-191', 'Finalized assessment & generated RPTSP', '2025-10-27 03:30:56'),
(100, 10, 74, 192, 'RPTSP-9-192', 'Finalized assessment & generated RPTSP', '2025-10-27 03:31:01'),
(101, 10, 75, 193, 'RPTSP-9-193', 'Finalized assessment & generated RPTSP', '2025-10-27 03:31:04'),
(102, 10, 76, 194, 'RPTSP-9-194', 'Finalized assessment & generated RPTSP', '2025-10-27 03:31:09'),
(103, 10, 77, 195, 'RPTSP-9-195', 'Finalized assessment & generated RPTSP', '2025-10-27 03:31:14'),
(104, 10, 59, 196, 'RPTSP-7-196', 'Finalized assessment & generated RPTSP', '2025-10-27 03:31:24'),
(105, 10, 60, 197, 'RPTSP-7-197', 'Finalized assessment & generated RPTSP', '2025-10-27 03:31:28'),
(106, 10, 61, 198, 'RPTSP-7-198', 'Finalized assessment & generated RPTSP', '2025-10-27 03:31:32'),
(107, 10, 62, 199, 'RPTSP-7-199', 'Finalized assessment & generated RPTSP', '2025-10-27 03:31:37'),
(108, 10, 63, 200, 'RPTSP-7-200', 'Finalized assessment & generated RPTSP', '2025-10-27 03:31:41'),
(109, 10, 64, 201, 'RPTSP-7-201', 'Finalized assessment & generated RPTSP', '2025-10-27 03:31:45');

-- --------------------------------------------------------

--
-- Table structure for table `municipalities`
--

CREATE TABLE `municipalities` (
  `id` int(11) NOT NULL,
  `province_id` int(11) DEFAULT NULL,
  `municipality_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `municipalities`
--

INSERT INTO `municipalities` (`id`, `province_id`, `municipality_name`) VALUES
(1, 1, 'Argao');

-- --------------------------------------------------------

--
-- Table structure for table `ngas_settings`
--

CREATE TABLE `ngas_settings` (
  `id` int(11) NOT NULL,
  `ngas_code` varchar(50) NOT NULL,
  `nature_of_collection` varchar(255) NOT NULL,
  `set_fix_amount` decimal(12,2) DEFAULT 0.00,
  `status` enum('enable','disable') DEFAULT 'enable',
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ngas_settings`
--

INSERT INTO `ngas_settings` (`id`, `ngas_code`, `nature_of_collection`, `set_fix_amount`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, '000-53', 'Cancel OR- Cow', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:16:49', '2025-11-11 08:17:03'),
(2, '000-54', 'Cancel OR - Marriage Licenses', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:17:20', '2025-11-11 08:19:04'),
(3, '000-56', 'Cancel OR - RPT', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:19:36', '2025-11-11 08:19:52'),
(4, '000-907', 'Cancel OR - Cedula Corp', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:20:38', '2025-11-11 08:21:35'),
(5, '148-0', 'GENERAL-Refund of Cash Advance', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:20:51', '2025-11-11 08:21:31'),
(6, '148-1', 'GENERAL-Refund of Petty Cash', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:21:03', '2025-11-11 08:21:28'),
(7, '412-0', 'Due to BIR', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:21:21', '2025-11-11 08:21:25'),
(8, '412-1', 'Doc Stamps', 30.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:21:53', '2025-11-11 08:22:11'),
(9, '416', 'DUE TO NSO(PSA)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:22:08', '2025-11-11 08:22:15'),
(10, '416-0', 'Fire Code Fee', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:22:31', '2025-11-11 08:22:51'),
(11, '416-1', 'NSO Cenomar', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:22:47', '2025-11-11 08:22:54'),
(12, '416-3', 'NSO Marriage', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:23:08', '2025-11-11 08:23:19'),
(13, '416-4', 'NSO Birth', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:24:24', '2025-11-11 08:24:27'),
(14, '416-5', 'NSO Death', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:24:42', '2025-11-11 08:24:45'),
(15, '416-6', 'Fire Code Fee Business', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:24:59', '2025-11-11 08:25:02'),
(16, '416-7', 'Fire Clearance National', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:25:21', '2025-11-11 08:25:23'),
(17, '416-8', 'Fire Code Fee Bldg. (Nat\'l. 25%)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:25:43', '2025-11-11 08:25:46'),
(18, '418-0', 'Due to LGUs', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:25:58', '2025-11-11 08:26:22'),
(19, '418-1', 'Due to LGU Province (Prof. Fee)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:26:37', '2025-11-11 08:26:39'),
(20, '439-0', 'Incentives to Illegal Apprehension Fishing', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:26:51', '2025-11-11 08:27:28'),
(21, '439-1', 'Share from ADEMCO-Mahayahay Beach', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:27:49', '2025-11-11 08:27:52'),
(22, '439-1A', 'Fines and Penalties(Bantay Dagat)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:28:03', '2025-11-11 08:28:10'),
(23, '564-0', 'Professional Fee', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:28:21', '2025-11-11 08:28:26'),
(24, '581-0', 'Amusement Tax', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:28:37', '2025-11-11 08:28:40'),
(25, '582-0', 'Business Permit Fee', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:28:52', '2025-11-11 08:28:56'),
(26, '583-0', 'Community Tax', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:29:09', '2025-11-11 08:29:12'),
(27, '584-0', 'Franchise Tax', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:29:21', '2025-11-11 08:29:25'),
(28, '585-0', 'Occupation Tax', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:29:40', '2025-11-11 08:29:43'),
(29, '586-0', 'Printing and Publication Tax', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:29:54', '2025-11-11 08:29:57'),
(30, '587-0', 'Property Transfer Tax', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:30:09', '2025-11-11 08:30:13'),
(31, '588-0', 'Real Property Tax', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:30:27', '2025-11-11 08:30:30'),
(32, '589-0', 'Real Property Tax on Idle Lands', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:30:48', '2025-11-11 08:30:51'),
(33, '590-0', 'Special Assessment Tax', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:31:12', '2025-11-11 08:31:15'),
(34, '591-0', 'Special Education Tax', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:31:26', '2025-11-11 08:31:30'),
(35, '592-0', 'Tax on Delivery Trucks and Vans', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:31:40', '2025-11-11 08:31:44'),
(36, '593-0', 'Tax on Sand, Gravel and Other Quarry Products', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:31:53', '2025-11-11 08:31:57'),
(37, '598-0', 'Other Local Taxes', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:32:12', '2025-11-11 08:32:15'),
(38, '599-0', 'Fines and Penalties - Local Taxes', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:32:25', '2025-11-11 08:32:28'),
(39, '601-0', 'Fees on Weights and Measures', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:32:40', '2025-11-11 08:32:43'),
(40, '602-0', 'Fishery Rental Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:36:37', '2025-11-11 08:36:46'),
(41, '603-0', 'Franchising and Licensing Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:44:24', '2025-11-11 08:44:29'),
(42, '604-0', 'Motor Vehicles - Franchise', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:44:55', '2025-11-11 08:45:00'),
(43, '604-1', 'Motor Vehicles - Regulatory Fee', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:45:37', '2025-11-11 08:45:41'),
(44, '604-2', 'Motor Vehicles - Driver\'s Permit', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:46:04', '2025-11-11 08:46:08'),
(45, '604-3', 'Motor Vehicles - Recovery Cost of Plate', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:46:33', '2025-11-11 08:46:38'),
(46, '604-4', 'Motor Vehicles - Supervision Fee', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:47:01', '2025-11-11 08:47:06'),
(47, '604-5', 'Motor Vehicles - Filliing Fee', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:47:32', '2025-11-11 08:47:36'),
(48, '605-0', 'Permit Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:48:04', '2025-11-11 08:48:08'),
(49, '605-1', 'Mayor\'s Permit', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:48:31', '2025-11-11 08:48:35'),
(50, '605-2', 'Building Permit', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:48:55', '2025-11-11 08:48:59'),
(51, '605-2A', 'Electrical Permit', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:49:21', '2025-11-11 08:49:25'),
(52, '605-3', 'Burial', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:49:46', '2025-11-11 08:49:51'),
(53, '605-4', 'Health & Sanitary Permit', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:50:50', '2025-11-11 08:50:54'),
(54, '605-5', 'Game & Fowl Permit', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:51:16', '2025-11-11 08:51:20'),
(55, '606-0', 'Registration Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:51:43', '2025-11-11 08:51:48'),
(56, '606-1', 'Civil Reg. Fees - Birth Certificate', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:52:09', '2025-11-11 08:52:14'),
(57, '606-10', 'Civil Reg. Fees - BREQS Processing', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:52:35', '2025-11-11 08:52:40'),
(58, '606-11', 'Civil Reg. Fees - Certified True Copies', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:53:02', '2025-11-11 08:53:06'),
(59, '606-12', 'Civil Reg. Fees - Delayed Registration', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:53:29', '2025-11-11 08:53:33'),
(60, '606-13', 'Civil Reg. Fees - Negative Birth', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:53:53', '2025-11-11 08:53:57'),
(61, '606-14', 'Civil Reg. Fees - Subscription', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:54:20', '2025-11-12 10:34:48'),
(62, '606-15', 'Civil Reg. Fees - Certificate of Registration', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:54:45', '2025-11-11 08:54:49'),
(63, '606-16', 'Civil Reg. Fees - Certificate of Finality', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:55:14', '2025-11-11 08:55:17'),
(64, '606-17', 'Civil Reg. Fees - Court Decree', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:55:46', '2025-11-11 08:55:51'),
(65, '606-18', 'Civil Reg. Fees - Endorsement Fee', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:56:12', '2025-11-11 08:56:16'),
(66, '606-19', 'Civil Reg. Fees - Legal Instrument', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:56:35', '2025-11-11 08:56:39'),
(67, '606-2', 'Cattle Registration (Credentials)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:57:00', '2025-11-11 08:57:03'),
(68, '606-20', 'Civil Reg. Fees - Embalming Permit', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:57:23', '2025-11-11 08:57:27'),
(69, '606-21', 'Civil Reg. Fees - Legitimation', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:57:48', '2025-11-11 08:57:52'),
(70, '606-22', 'Civil Reg. Fees - Adoption', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:58:12', '2025-11-11 08:58:15'),
(71, '606-23', 'Civil Reg. Fees - Annulment', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:58:38', '2025-11-11 08:58:41'),
(72, '606-3', 'Civil Reg. Fees - Marriage Certificate', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:59:11', '2025-11-11 08:59:14'),
(73, '606-4', 'Civil Reg. Fees - App. For Marriage License', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:59:34', '2025-11-11 08:59:37'),
(74, '606-5', 'Civil Reg. Fees - Marriage License', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 08:59:58', '2025-11-11 09:00:03'),
(75, '606-6', 'Civil Reg. Fees - App. For Change of Name', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:00:23', '2025-11-11 09:00:27'),
(76, '606-7', 'Civil Reg. Fees - Petition for Correction of Clerical Error', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:00:54', '2025-11-11 09:00:59'),
(77, '606-8', 'Civil Reg. Fees - Solemnization', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:01:19', '2025-11-11 09:01:23'),
(78, '606-9', 'Civil Reg. Fees - Death Certificate', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:01:48', '2025-11-11 09:01:52'),
(79, '608-0', 'Other Permits and Licenses', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:02:10', '2025-11-11 09:02:15'),
(80, '609-0', 'Fines and Penalties - Traffic Violation', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:02:35', '2025-11-11 09:02:38'),
(81, '611-0', 'Affiliation Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:02:58', '2025-11-11 09:03:12'),
(82, '612-0', 'Athletic and Cultural Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:03:25', '2025-11-11 09:03:28'),
(83, '613-1', 'Zoning Clearance', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:03:48', '2025-11-11 09:03:52'),
(84, '613-2', 'Locational Clearance', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:04:13', '2025-11-11 09:04:18'),
(85, '613-3', ' Secretary\'s Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:04:42', '2025-11-11 09:04:47'),
(86, '613-4', 'Police Clearance', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:05:09', '2025-11-11 09:05:12'),
(87, '613-4A', 'Police Report', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:05:33', '2025-11-11 09:05:37'),
(88, '613-5', 'Clearance and Certification', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:05:56', '2025-11-11 09:05:59'),
(89, '613-6', 'Fire Clearance', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:06:27', '2025-11-11 09:06:31'),
(90, '613-7', 'Certified True Copy - RPT', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:06:52', '2025-11-11 09:06:56'),
(91, '613-7A', 'Certified Photocopy of Lot/Map', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:07:15', '2025-11-11 09:07:19'),
(92, '613-7B', 'Certified Copy of TMCR', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:07:37', '2025-11-11 09:07:41'),
(93, '613-8', 'Annotation Fee', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:08:11', '2025-11-11 09:08:21'),
(94, '614-0', 'Comprehensive Examination Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:08:40', '2025-11-11 09:08:44'),
(95, '615-0', 'Diploma and Graduation Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:09:03', '2025-11-11 09:09:08'),
(96, '616-0', 'Garbage Fee', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:09:27', '2025-11-11 09:09:32'),
(97, '617-0', 'Inspection Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:09:53', '2025-11-11 09:09:58'),
(98, '617-1', 'Coded Bin Inspection Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:10:16', '2025-11-11 09:10:19'),
(99, '618-0', 'Library Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:10:40', '2025-11-11 09:10:45'),
(100, '619-0', 'Medical, Dental and Labatory Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:11:05', '2025-11-11 09:11:09'),
(101, '619-18', 'Exhumation Permit', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:11:29', '2025-11-11 09:11:32'),
(102, '619-21', 'Health Certificate', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:11:52', '2025-11-11 09:11:56'),
(103, '619-22', 'Dental Certificate', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:12:16', '2025-11-11 09:12:21'),
(104, '619-23', 'Medical Certificate', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:12:39', '2025-11-11 09:12:44'),
(105, '619-32', 'Add Health and Sanitary', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:13:04', '2025-11-11 09:13:07'),
(106, '619-33', 'S/E', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:13:27', '2025-11-11 09:13:30'),
(107, '619-4', 'U/A', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:13:49', '2025-11-11 09:13:53'),
(108, '620-0', 'Passport and Visa Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:14:12', '2025-11-11 09:14:16'),
(109, '621-0', 'Processing Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:14:34', '2025-11-11 09:14:38'),
(110, '622-0', 'Seminar Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:15:01', '2025-11-11 09:15:05'),
(111, '623-0', 'Toll and Terminal Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:15:24', '2025-11-11 09:15:27'),
(112, '623-0A', 'Multicab Terminal Cash Tickets', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:15:46', '2025-11-11 09:15:50'),
(113, '623-0B', 'Habal-habal Terminal Cash Tickets', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:16:12', '2025-11-11 09:16:15'),
(114, '624-0', 'Transcript of Record Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:17:18', '2025-11-11 09:17:22'),
(115, '628-0', 'Other Service Income', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:17:42', '2025-11-11 09:17:46'),
(116, '628-1', 'Artificial Insemination Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:18:04', '2025-11-11 09:18:08'),
(117, '629-0', 'Fines and Penalties - Service Income', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:18:29', '2025-11-11 09:18:32'),
(118, '631-0', 'Hospital Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:19:20', '2025-11-11 09:19:24'),
(119, '632-0', 'Income from Canteen Operations', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:19:42', '2025-11-11 09:19:46'),
(120, '633-0', 'Income from Cemetery Operations', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:20:06', '2025-11-11 09:20:10'),
(121, '634-0', 'Income from Communication Facilities', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:20:33', '2025-11-11 09:20:36'),
(122, '635-0', 'Income from Dormitory Operations', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:20:57', '2025-11-11 09:21:00'),
(123, '636-0', 'Income from Markets', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:21:16', '2025-11-11 09:21:19'),
(124, '636-1', 'Market License', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:21:36', '2025-11-11 09:21:40'),
(125, '636-2', 'Market Rental', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:22:02', '2025-11-11 09:22:05'),
(126, '636-3', 'Cash Tickets', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:22:22', '2025-11-11 09:22:27'),
(127, '636-4', 'Income from CR', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:22:43', '2025-11-11 09:22:46'),
(128, '636-5', 'Stall Market', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:23:04', '2025-11-11 09:23:07'),
(129, '637-0', 'Income from Slaughterhouse', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:23:29', '2025-11-11 09:23:33'),
(130, '638-0', 'Income from Transportation Systems', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:23:49', '2025-11-11 09:23:52'),
(131, '639-0', 'Income from Waterworks System', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:24:12', '2025-11-11 09:24:15'),
(132, '640-0', 'Landing and Parking Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:24:39', '2025-11-11 09:24:42'),
(133, '641-0', 'Printing and Publication Income', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:24:57', '2025-11-11 09:25:01'),
(134, '642-0', 'Rent', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:25:18', '2025-11-11 09:25:21'),
(135, '643-0', 'Sales Revenue', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:25:37', '2025-11-11 09:25:41'),
(136, '644-0', 'Tuition Fees', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:25:56', '2025-11-11 09:25:59'),
(137, '648-0', 'Other Business Income', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:26:15', '2025-11-11 09:26:18'),
(138, '648-A1', 'Space Rental PDMSCC', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:26:32', '2025-11-11 09:26:35'),
(139, '648-A2', 'Sale of Property Rights PDMSCC', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:26:51', '2025-11-11 09:26:55'),
(140, '648-A3', 'Equipment Rental PDMSCC', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:27:11', '2025-11-11 09:27:15'),
(141, '648-A4', 'Gate Proceeds', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:27:34', '2025-11-11 09:27:37'),
(142, '648-A5', 'Cash Tickets (PDMSCC-Gate Proceeds)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:27:53', '2025-11-11 09:27:56'),
(143, '648-B1', 'Cash Tickets (ANP-Gate Proceeds)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:28:12', '2025-11-11 09:28:15'),
(144, '648-C1', 'Stall Rental Boulevard', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:28:31', '2025-11-11 09:28:35'),
(145, '648-C2', 'Water and Electricity Boulevard', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:28:49', '2025-11-11 09:28:52'),
(146, '648-D1', 'Rent Income Training Center', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:29:09', '2025-11-11 09:29:12'),
(147, '648-D2', 'Room Rental (Mini Hotel)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:29:27', '2025-11-11 09:29:30'),
(148, '649-0', 'Fines and Penalties - Business Income', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:29:47', '2025-11-11 09:29:50'),
(149, '651-0', 'Subsidy Income from National Government', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:30:06', '2025-11-11 09:30:09'),
(150, '652-0', 'Subsidy from other National Government Agencies', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:30:25', '2025-11-11 09:30:28'),
(151, '653-0', 'Subsidy from Central Office', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:30:42', '2025-11-11 09:30:45'),
(152, '654-0', 'Subsidy from Regional Office', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:31:01', '2025-11-11 09:31:05'),
(153, '655-0', 'Subsidy from Regional Office/Staff Bureau', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:31:20', '2025-11-11 09:31:23'),
(154, '656-0', 'Subsidy from Other LGUs', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:31:40', '2025-11-11 09:31:43'),
(155, '657-0', 'Subsidy from Other Funds', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:31:59', '2025-11-11 09:32:03'),
(156, '661-0', 'Dividend Income', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:32:35', '2025-11-11 09:32:41'),
(157, '662-0', 'Income from Grants and Donations', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:33:31', '2025-11-11 09:33:35'),
(158, '663-0', 'Insurance Income', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:33:51', '2025-11-11 09:33:55'),
(159, '664-0', 'Interest Income', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:34:12', '2025-11-11 09:34:15'),
(160, '665-0', 'Internal Revenue Allotment', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:34:31', '2025-11-11 09:34:35'),
(161, '666-0', 'Sale of Confiscated/Abandoned/Seized Goods and Properties', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:34:50', '2025-11-11 09:34:54'),
(162, '667-0', 'Share from Economic Zones', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:35:10', '2025-11-11 09:35:13'),
(163, '668-0', 'Share from Expanded VAT (EVAT)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:36:02', '2025-11-11 09:36:06'),
(164, '669-0', 'Share from National Wealth', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:36:21', '2025-11-11 09:36:24'),
(165, '670-0', 'Sharefrom PAGCOR/PCSO', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:36:46', '2025-11-11 09:36:50'),
(166, '671-0', 'Share  from Tobacco Excise Tax', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:37:08', '2025-11-11 09:37:11'),
(167, '678-0', 'Miscellaneous Income', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:37:27', '2025-11-11 09:37:31'),
(168, '679-0', 'Other Fines and Penalties', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:37:48', '2025-11-11 09:37:52'),
(169, '680-1', 'Brgy. Bus Clearance(Poblacion)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:38:10', '2025-11-11 09:38:14'),
(170, '680-10', 'Brgy. Bus Clearance(Bulasa)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:38:31', '2025-11-11 09:38:35'),
(171, '680-11', 'Brgy. Bus Clearance(Butong)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:41:00', '2025-11-11 09:41:03'),
(172, '680-12', 'Brgy. Bus Clearance(Calagasan)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:41:19', '2025-11-11 09:41:23'),
(173, '680-13', 'Brgy. Bus Clearance(Canbantug)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:41:39', '2025-11-11 09:41:43'),
(174, '680-14', 'Brgy. Bus Clearance(Canbanua)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:42:00', '2025-11-11 09:42:05'),
(175, '680-15', 'Brgy. Bus Clearance(Cansuje)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:42:22', '2025-11-11 09:42:25'),
(176, '680-16', 'Brgy. Bus Clearance(Capio-an)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:42:40', '2025-11-11 09:42:43'),
(177, '680-17', 'Brgy. Bus Clearance(Casay)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:42:58', '2025-11-11 09:43:01'),
(178, '680-18', 'Brgy. Bus Clearance(Catang)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:43:16', '2025-11-11 09:43:19'),
(179, '680-19', 'Brgy. Bus Clearance(Colawin)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:43:37', '2025-11-11 09:43:40'),
(180, '680-2', 'Brgy. Bus Clearance(Alambijud)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:43:55', '2025-11-11 09:43:59'),
(181, '680-20', 'Brgy. Bus Clearance(Conalum)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:44:14', '2025-11-11 09:44:18'),
(182, '680-21', 'Brgy. Bus Clearance(Guiwanon)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:44:32', '2025-11-11 09:44:35'),
(183, '680-22', 'Brgy. Bus Clearance(Gutlang)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:44:50', '2025-11-11 09:44:53'),
(184, '680-23', 'Brgy. Bus Clearance(Jampang)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:45:09', '2025-11-11 09:45:12'),
(185, '680-24', 'Brgy. Bus Clearance(Jomgao)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:45:26', '2025-11-11 09:45:29'),
(186, '680-25', 'Brgy. Bus Clearance(Lamacan)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:45:46', '2025-11-11 09:45:49'),
(187, '680-26', 'Brgy. Bus Clearance(Langtad)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:46:07', '2025-11-11 09:46:10'),
(188, '680-27', 'Brgy. Bus Clearance(Langub)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:46:24', '2025-11-11 09:46:27'),
(189, '680-28', 'Brgy. Bus Clearance(Lapay)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:46:42', '2025-11-11 09:46:45'),
(190, '680-29', 'Brgy. Bus Clearance(Lengigon)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:47:03', '2025-11-11 09:47:07'),
(191, '680-3', 'Brgy. Bus Clearance(Anajao)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:47:21', '2025-11-11 09:47:24'),
(192, '680-30', 'Brgy. Bus Clearance(Linut-od)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:47:38', '2025-11-11 09:47:42'),
(193, '680-31', 'Brgy. Bus Clearance(Mabasa)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:47:56', '2025-11-11 09:48:00'),
(194, '680-32', 'Brgy. Bus Clearance(Mandilikit)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:48:14', '2025-11-11 09:48:17'),
(195, '680-33', 'Brgy. Bus Clearance(Mompeller)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:48:32', '2025-11-11 09:48:35'),
(196, '680-34', 'Brgy. Bus Clearance(Panadtaran)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:49:11', '2025-11-11 09:49:14'),
(197, '680-35', 'Brgy. Bus Clearance(Sua)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:49:30', '2025-11-11 09:49:32'),
(198, '680-36', 'Brgy. Bus Clearance(Sumaguan)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:49:47', '2025-11-11 09:49:50'),
(199, '680-37', 'Brgy. Bus Clearance(Tabayag)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:50:03', '2025-11-11 09:50:06'),
(200, '680-38', 'Brgy. Bus Clearance(Talaga)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:50:22', '2025-11-11 09:50:25'),
(201, '680-39', 'Brgy. Bus Clearance(Talaytay)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:50:43', '2025-11-11 09:50:47'),
(202, '680-4', 'Brgy. Bus Clearance(Apo)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:51:04', '2025-11-11 09:51:07'),
(203, '680-40', 'Brgy. Bus Clearance(Taloot)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:51:20', '2025-11-11 09:51:24'),
(204, '680-41', 'Brgy. Bus Clearance(Tiguib)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:51:39', '2025-11-11 09:51:42'),
(205, '680-42', 'Brgy. Bus Clearance(Tulang)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:51:56', '2025-11-11 09:51:59'),
(206, '680-43', 'Brgy. Bus Clearance(Tulic)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:52:14', '2025-11-11 09:52:18'),
(207, '680-44', 'Brgy. Bus Clearance(Ubaub)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:52:32', '2025-11-11 09:52:35'),
(208, '680-45', 'Brgy. Bus Clearance(Usmad)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:52:49', '2025-11-11 09:52:54'),
(209, '680-5', 'Brgy. Bus Clearance(Balaas)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:53:07', '2025-11-11 09:53:10'),
(210, '680-6', 'Brgy. Bus Clearance(Balisong)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:53:23', '2025-11-11 09:53:26'),
(211, '680-7', 'Brgy. Bus Clearance(Binlod)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:53:42', '2025-11-11 09:53:46'),
(212, '680-8', 'Brgy. Bus Clearance(Bogo)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:53:59', '2025-11-11 09:54:03'),
(213, '680-9', 'Brgy. Bus Clearance(Bug-ot)', 0.00, 'enable', 'MARIA LUISA C. MAGALLANES', 'MARIA LUISA C. MAGALLANES', '2025-11-11 09:54:17', '2025-11-11 09:54:20');

-- --------------------------------------------------------

--
-- Table structure for table `officials_list`
--

CREATE TABLE `officials_list` (
  `id` int(11) NOT NULL,
  `position` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `office` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `footer_role` enum('Prepared by','Reviewed by','Approved by','None') DEFAULT 'None'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `officials_list`
--

INSERT INTO `officials_list` (`id`, `position`, `name`, `office`, `is_active`, `footer_role`) VALUES
(1, 'Municipal Treasurer', 'Atty. Roberto Reyes', 'Treasurer\'s Office', 1, 'None'),
(2, 'Municipal Assessor', 'Engr. Maria Santos', 'Assessor\'s Office', 1, 'None'),
(3, 'Records Officer / Encoder', 'Juan Dela Cruz', 'Records Office', 1, 'None'),
(4, 'Municipal Mayor', 'Hon. Ana Lopez', 'Mayor\'s Office', 1, 'None');

-- --------------------------------------------------------

--
-- Table structure for table `owners`
--

CREATE TABLE `owners` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tin` varchar(50) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owners`
--

INSERT INTO `owners` (`id`, `name`, `username`, `email`, `password`, `address`, `tin`, `contact_no`, `created_at`) VALUES
(6, 'DELA CRUZ, JUAN', NULL, '', NULL, 'POBLACION, ARGAO, CEBU', NULL, '', '2025-09-18 02:01:57'),
(7, 'ALBERCA, JUAN', NULL, NULL, NULL, 'TULIC, ARGAO, CEBU', NULL, NULL, '2025-09-18 02:02:22'),
(8, 'SILVIA FLORES', NULL, NULL, NULL, 'LANGTAD, ARGAO, CEBU', NULL, 'NONE', '2025-09-24 03:46:48'),
(9, 'ALBOR, CRIS', NULL, NULL, NULL, 'TULIC, ARGAO, CEBU', NULL, NULL, '2025-09-30 06:06:42'),
(12, 'ABEAR , CYNTHIA', NULL, NULL, NULL, 'CANBANUA, ARGAO, CEBU', NULL, NULL, '2025-09-30 07:51:16'),
(13, 'SHEILA SARAGENA', NULL, NULL, NULL, 'LANGTAD, ARGAO, CEBU', NULL, NULL, '2025-10-24 08:29:09');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `payment_batch_id` bigint(20) NOT NULL,
  `bill_id` int(11) DEFAULT NULL,
  `or_no` varchar(50) NOT NULL,
  `payor_name` varchar(255) NOT NULL,
  `date_paid` date NOT NULL,
  `status` enum('Paid','Unpaid') DEFAULT 'Paid',
  `property_id` int(11) NOT NULL,
  `tax_bill_id` int(11) NOT NULL,
  `tax_year` year(4) NOT NULL,
  `basic_tax` decimal(12,2) NOT NULL,
  `sef_tax` decimal(12,2) NOT NULL,
  `discount` decimal(12,2) DEFAULT 0.00,
  `penalty` decimal(12,2) DEFAULT 0.00,
  `total_paid` decimal(12,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_amount` decimal(18,2) NOT NULL,
  `payment_type` enum('cash','bank','gcash','paymaya','cheque','online') DEFAULT 'cash',
  `official_receipt_no` varchar(100) DEFAULT NULL,
  `handled_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  `prev_receipt_no` varchar(50) DEFAULT NULL,
  `prev_date_paid` date DEFAULT NULL,
  `prev_year` varchar(10) DEFAULT NULL,
  `ownership_no` varchar(50) DEFAULT NULL,
  `or_number` varchar(50) DEFAULT NULL,
  `prev_or_no` varchar(50) DEFAULT NULL,
  `prev_paid` decimal(12,2) DEFAULT 0.00,
  `basic` decimal(12,2) DEFAULT 0.00,
  `sef` decimal(12,2) DEFAULT 0.00,
  `adjustments` decimal(12,2) DEFAULT 0.00,
  `adjust` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments_backup`
--

CREATE TABLE `payments_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `bill_id` int(11) DEFAULT NULL,
  `or_no` varchar(50) NOT NULL,
  `payor_name` varchar(255) NOT NULL,
  `date_paid` date NOT NULL,
  `property_id` int(11) NOT NULL,
  `tax_bill_id` int(11) NOT NULL,
  `tax_year` year(4) NOT NULL,
  `basic_tax` decimal(12,2) NOT NULL,
  `sef_tax` decimal(12,2) NOT NULL,
  `discount` decimal(12,2) DEFAULT 0.00,
  `penalty` decimal(12,2) DEFAULT 0.00,
  `total_paid` decimal(12,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_amount` decimal(18,2) NOT NULL,
  `payment_type` enum('cash','bank','gcash','paymaya','cheque','online') DEFAULT 'cash',
  `official_receipt_no` varchar(100) DEFAULT NULL,
  `handled_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  `prev_receipt_no` varchar(50) DEFAULT NULL,
  `prev_date_paid` date DEFAULT NULL,
  `prev_year` varchar(10) DEFAULT NULL,
  `ownership_no` varchar(50) DEFAULT NULL,
  `or_number` varchar(50) DEFAULT NULL,
  `prev_or_no` varchar(50) DEFAULT NULL,
  `prev_paid` decimal(12,2) DEFAULT 0.00,
  `basic` decimal(12,2) DEFAULT 0.00,
  `sef` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments_list`
--

CREATE TABLE `payments_list` (
  `id` int(11) NOT NULL,
  `or_no` varchar(50) DEFAULT NULL,
  `previous_or_no` varchar(50) DEFAULT NULL,
  `previous_date_paid` date DEFAULT NULL,
  `previous_year` int(11) DEFAULT NULL,
  `assessment_id` int(11) DEFAULT NULL,
  `tax_year` int(11) DEFAULT NULL,
  `rptsp_no` varchar(100) DEFAULT NULL,
  `td_no` varchar(100) DEFAULT NULL,
  `lot_no` varchar(100) DEFAULT NULL,
  `owner_name` varchar(255) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `classification` varchar(100) DEFAULT NULL,
  `assessed_value` decimal(12,2) DEFAULT NULL,
  `basic_tax` decimal(12,2) DEFAULT NULL,
  `sef_tax` decimal(12,2) DEFAULT NULL,
  `tax_due` decimal(12,2) DEFAULT NULL,
  `adjustments` decimal(12,2) DEFAULT NULL,
  `discount` decimal(12,2) DEFAULT NULL,
  `penalty` decimal(12,2) DEFAULT NULL,
  `total_due` decimal(12,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `processed_by` varchar(100) DEFAULT NULL,
  `assessed_date` datetime DEFAULT NULL,
  `owner` varchar(255) DEFAULT NULL,
  `payor_name` varchar(255) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `total_amount_paid` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_audit`
--

CREATE TABLE `payment_audit` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `action` enum('DELETE','RESTORE') NOT NULL,
  `user` varchar(100) NOT NULL,
  `action_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `property_id` int(11) DEFAULT NULL,
  `tax_year` year(4) DEFAULT NULL,
  `total_due` decimal(12,2) DEFAULT NULL,
  `or_no` varchar(50) DEFAULT NULL,
  `payor_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `discount` decimal(12,2) DEFAULT 0.00,
  `penalty` decimal(12,2) DEFAULT 0.00,
  `date_paid` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_items`
--

CREATE TABLE `payment_items` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `tax_bill_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_logs`
--

CREATE TABLE `payment_logs` (
  `id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `user` varchar(100) NOT NULL,
  `action_at` datetime NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `name`) VALUES
(1, 'JOB ORDER'),
(2, 'LICENSING OFFICER III'),
(3, 'LAOO I'),
(4, 'ADMIN AIDE I');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `td_no` varchar(50) DEFAULT NULL,
  `lot_no` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `classification` varchar(100) DEFAULT NULL,
  `assessed_value` decimal(18,2) DEFAULT 0.00,
  `effective_year` int(4) DEFAULT NULL,
  `revision_year` int(4) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `td_no`, `lot_no`, `location`, `barangay`, `classification`, `assessed_value`, `effective_year`, `revision_year`, `owner_id`, `notes`, `created_at`, `updated_at`) VALUES
(6, '01-00001', '00001', 'POBLACION, ARGAO, CEBU', 'POBLACION', 'LAND', 100000.00, 2020, 2020, 6, NULL, '2025-09-18 02:03:34', '2025-09-18 02:03:34'),
(7, '43-00001', '043-00001', 'TULIC, ARGAO, CEBU', 'TULIC', 'LAND', 200000.00, 2020, 2020, 7, NULL, '2025-09-18 02:26:40', '2025-09-18 02:26:40'),
(8, '02-00001', '02-00001', 'LANGTAD, ARGAO, CEBU', 'LANGTAD', 'LAND', 300000.00, 2020, 2022, 8, NULL, '2025-09-24 03:48:01', '2025-10-27 03:27:29'),
(9, '43-00002', '043-00002', 'TULIC, ARGAO, CEBU', 'TULIC', 'LAND', 400000.00, 2020, 2020, 9, NULL, '2025-09-30 06:07:08', '2025-09-30 06:07:08'),
(11, '02-00002', '02-00001', 'LANGTAD, ARGAO, CEBU', 'LANGTAD', 'LAND', 300000.00, 2020, NULL, 13, NULL, '2025-10-27 01:28:22', '2025-10-27 01:28:22');

-- --------------------------------------------------------

--
-- Table structure for table `property_history`
--

CREATE TABLE `property_history` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `tax_year` int(11) NOT NULL,
  `assessed_value` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `provinces`
--

CREATE TABLE `provinces` (
  `id` int(11) NOT NULL,
  `province_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `provinces`
--

INSERT INTO `provinces` (`id`, `province_name`) VALUES
(1, 'Cebu');

-- --------------------------------------------------------

--
-- Table structure for table `receipt_logs`
--

CREATE TABLE `receipt_logs` (
  `id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `printed_at` datetime DEFAULT current_timestamp(),
  `ip_address` varchar(50) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `remittance`
--

CREATE TABLE `remittance` (
  `id` int(11) UNSIGNED NOT NULL,
  `form_no` varchar(50) NOT NULL,
  `or_no` varchar(50) NOT NULL,
  `total_paid` decimal(12,2) NOT NULL DEFAULT 0.00,
  `date_paid` date NOT NULL,
  `created_by` varchar(255) NOT NULL,
  `remittance_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `remittance`
--

INSERT INTO `remittance` (`id`, `form_no`, `or_no`, `total_paid`, `date_paid`, `created_by`, `remittance_date`, `created_at`, `updated_at`) VALUES
(6, '16', '11111112', 13792.10, '2025-11-12', 'MARIA LUISA C. MAGALLANES', '2025-11-13', '2025-11-13 17:29:19', NULL),
(7, '907', '00091647', 1586.00, '2025-11-13', 'MARIA LUISA C. MAGALLANES', '2025-11-13', '2025-11-13 17:29:39', NULL),
(8, '907', '00091648', 2562.00, '2025-11-13', 'MARIA LUISA C. MAGALLANES', '2025-11-13', '2025-11-13 17:29:39', NULL),
(9, '51', '9191736', 115.00, '2025-11-11', 'MARIA LUISA C. MAGALLANES', '2025-11-13', '2025-11-13 17:29:57', NULL),
(10, '51', '9191737', 1651.41, '2025-11-11', 'MARIA LUISA C. MAGALLANES', '2025-11-13', '2025-11-13 17:29:57', NULL),
(11, '58', '0386475', 50.00, '2025-11-11', 'MARIA LUISA C. MAGALLANES', '2025-11-14', '2025-11-14 16:42:07', NULL),
(12, '907', '00080001', 1488.40, '2025-11-17', 'CASHIER NUMBER1', '2025-11-17', '2025-11-17 16:32:58', NULL),
(13, '16', '1231231231', 2568.10, '2025-11-17', 'CASHIER NUMBER1', '2025-11-17', '2025-11-17 16:33:05', NULL),
(14, '16', '1231231232', 372.10, '2025-11-17', 'CASHIER NUMBER1', '2025-11-17', '2025-11-17 16:33:05', NULL),
(15, '58', '10000001', 50.00, '2025-11-17', 'CASHIER NUMBER1', '2025-11-17', '2025-11-17 16:51:56', NULL),
(16, '51', '8080123', 135.00, '2025-11-17', 'CASHIER NUMBER1', '2025-11-17', '2025-11-17 17:03:12', NULL),
(17, '51', '8080124', 235.00, '2025-11-17', 'CASHIER NUMBER1', '2025-11-17', '2025-11-17 17:03:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `report_sequence`
--

CREATE TABLE `report_sequence` (
  `id` int(11) NOT NULL,
  `report_no` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_sequence`
--

INSERT INTO `report_sequence` (`id`, `report_no`, `created_at`) VALUES
(1, '2025-11-0001', '2025-11-18 04:48:54'),
(2, '2025-11-0002', '2025-11-18 04:51:55'),
(3, '2025-11-0003', '2025-11-18 05:13:23');

-- --------------------------------------------------------

--
-- Table structure for table `service_records`
--

CREATE TABLE `service_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `emp_idno` varchar(50) NOT NULL,
  `recfrom` date NOT NULL,
  `recto` date DEFAULT NULL,
  `position` int(10) UNSIGNED DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `assignment` int(10) UNSIGNED DEFAULT NULL,
  `lawop` varchar(255) DEFAULT NULL,
  `separation_cause` varchar(255) DEFAULT NULL,
  `separation_date` date DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `salary` decimal(15,2) DEFAULT NULL,
  `salary_grade` varchar(50) DEFAULT NULL,
  `step_increment` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_records`
--

INSERT INTO `service_records` (`id`, `emp_idno`, `recfrom`, `recto`, `position`, `status`, `assignment`, `lawop`, `separation_cause`, `separation_date`, `remarks`, `salary`, `salary_grade`, `step_increment`, `created_at`, `updated_at`) VALUES
(1, '2025-0580', '2007-02-16', '2007-06-30', 3, 1, 3, NULL, NULL, NULL, '10% Salary Increase', 107448.00, 'SG 11', 'STEP 1', '2025-12-11 05:25:36', '2025-12-11 05:25:36'),
(2, '2025-0580', '2007-07-01', '2008-06-30', 3, 1, 3, NULL, NULL, NULL, '10% Salary Increase', 118212.00, 'SG 11', 'STEP 1', '2025-12-11 05:27:09', '2025-12-11 05:27:09');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) DEFAULT NULL,
  `setting_value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES
(1, 'municipal_logo', 'uploads/logo.png'),
(2, 'municipality_name', 'Sample Municipality'),
(3, 'province_name', 'Sample Province'),
(4, 'prepared_by', 'Maria Dela Cruz - Revenue Clerk'),
(5, 'reviewed_by', 'Juan Reyes - Municipal Accountant'),
(6, 'approved_by', 'Engr. Roberto Santos - Municipal Treasurer');

-- --------------------------------------------------------

--
-- Table structure for table `signatories`
--

CREATE TABLE `signatories` (
  `id` int(11) NOT NULL,
  `position` varchar(100) NOT NULL,
  `name` varchar(150) NOT NULL,
  `title` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `signatories`
--

INSERT INTO `signatories` (`id`, `position`, `name`, `title`) VALUES
(1, 'assessor', 'ARNEL D. ALBOFERA', 'Municipal Assessor'),
(2, 'treasurer', 'MARIA LUISA C. MAGALLANES', 'Municipal Treasurer'),
(3, 'mayor', 'ALLAN M. SESALDO', 'Municipal Mayor'),
(4, 'treasurer verifier', 'BLAISE A. REMONDE', 'Treasury Account Verifier Officer'),
(5, 'human resource', 'JO ANN M. VILLAFUERTE', 'Administrative Officer V');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `system_name` varchar(255) DEFAULT 'Real Property Tax Monitoring System (RPTMS)',
  `municipality` varchar(255) DEFAULT 'Argao, Cebu',
  `logo` varchar(255) DEFAULT 'logo.png',
  `background` varchar(255) DEFAULT 'background.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `system_name`, `municipality`, `logo`, `background`) VALUES
(1, 'eLGU Human Resource Management System', 'Municipality of Argao', 'logo.png', 'background.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tax_bills`
--

CREATE TABLE `tax_bills` (
  `id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `tax_year` int(11) DEFAULT NULL,
  `assessed_value` decimal(15,2) NOT NULL,
  `basic_tax_rate` decimal(5,4) NOT NULL DEFAULT 0.0100,
  `basic_tax` decimal(15,2) NOT NULL,
  `sef_tax` decimal(15,2) NOT NULL,
  `tax_due` decimal(12,2) DEFAULT NULL,
  `adjustments` decimal(15,2) NOT NULL DEFAULT 0.00,
  `rptsp_no` varchar(50) NOT NULL,
  `bill_no` varchar(50) DEFAULT NULL,
  `issuance_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `total_amount` decimal(18,2) DEFAULT NULL,
  `paid_amount` decimal(18,2) DEFAULT 0.00,
  `pdf_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('unpaid','paid') DEFAULT 'unpaid',
  `property_id` int(11) NOT NULL,
  `is_paid` tinyint(1) DEFAULT 0,
  `paid_at` datetime DEFAULT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `date_paid` datetime DEFAULT NULL,
  `or_no` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tax_bills`
--

INSERT INTO `tax_bills` (`id`, `assessment_id`, `tax_year`, `assessed_value`, `basic_tax_rate`, `basic_tax`, `sef_tax`, `tax_due`, `adjustments`, `rptsp_no`, `bill_no`, `issuance_date`, `due_date`, `total_amount`, `paid_amount`, `pdf_path`, `created_at`, `updated_at`, `issued_at`, `status`, `property_id`, `is_paid`, `paid_at`, `payment_id`, `date_paid`, `or_no`) VALUES
(170, 89, 2020, 300000.00, 0.0100, 3000.00, 3000.00, NULL, 0.00, 'RPTSP-8-170', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:28:42', '2025-11-06 03:54:30', '2025-10-27 03:28:42', 'paid', 8, 0, NULL, NULL, NULL, NULL),
(171, 88, 2021, 300000.00, 0.0100, 3000.00, 3000.00, NULL, 0.00, 'RPTSP-8-171', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:28:51', '2025-11-06 03:54:30', '2025-10-27 03:28:51', 'paid', 8, 0, NULL, NULL, NULL, NULL),
(172, 55, 2022, 300000.00, 0.0100, 3000.00, 3000.00, NULL, 0.00, 'RPTSP-8-172', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:28:58', '2025-11-06 03:54:30', '2025-10-27 03:28:58', 'paid', 8, 0, NULL, NULL, NULL, NULL),
(173, 56, 2023, 300000.00, 0.0100, 3000.00, 3000.00, NULL, 0.00, 'RPTSP-8-173', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:29:05', '2025-11-06 03:54:30', '2025-10-27 03:29:05', 'paid', 8, 0, NULL, NULL, NULL, NULL),
(174, 57, 2024, 300000.00, 0.0100, 3000.00, 3000.00, NULL, 0.00, 'RPTSP-8-174', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:29:11', '2025-11-06 03:54:30', '2025-10-27 03:29:11', 'paid', 8, 0, NULL, NULL, NULL, NULL),
(175, 58, 2025, 300000.00, 0.0100, 3000.00, 3000.00, NULL, 0.00, 'RPTSP-8-175', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:29:21', '2025-11-06 03:54:30', '2025-10-27 03:29:21', 'paid', 8, 0, NULL, NULL, NULL, NULL),
(176, 78, 2026, 300000.00, 0.0100, 3000.00, 3000.00, NULL, 0.00, 'RPTSP-8-176', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:29:26', '2025-11-06 03:54:30', '2025-10-27 03:29:26', 'paid', 8, 0, NULL, NULL, NULL, NULL),
(177, 80, 2020, 300000.00, 0.0100, 3000.00, 3000.00, NULL, 0.00, 'RPTSP-11-177', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:29:38', '2025-11-06 04:02:39', '2025-10-27 03:29:38', 'paid', 11, 0, NULL, NULL, NULL, NULL),
(178, 81, 2021, 300000.00, 0.0100, 3000.00, 3000.00, NULL, 0.00, 'RPTSP-11-178', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:29:44', '2025-11-06 04:02:39', '2025-10-27 03:29:44', 'paid', 11, 0, NULL, NULL, NULL, NULL),
(179, 82, 2022, 300000.00, 0.0100, 3000.00, 3000.00, NULL, 0.00, 'RPTSP-11-179', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:29:49', '2025-11-06 04:02:39', '2025-10-27 03:29:49', 'paid', 11, 0, NULL, NULL, NULL, NULL),
(180, 83, 2023, 300000.00, 0.0100, 3000.00, 3000.00, NULL, 0.00, 'RPTSP-11-180', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:29:56', '2025-11-06 04:02:39', '2025-10-27 03:29:56', 'paid', 11, 0, NULL, NULL, NULL, NULL),
(181, 84, 2024, 300000.00, 0.0100, 3000.00, 3000.00, NULL, 0.00, 'RPTSP-11-181', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:30:04', '2025-11-06 04:02:39', '2025-10-27 03:30:04', 'paid', 11, 0, NULL, NULL, NULL, NULL),
(182, 86, 2025, 300000.00, 0.0100, 3000.00, 3000.00, NULL, 0.00, 'RPTSP-11-182', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:30:08', '2025-11-06 04:02:39', '2025-10-27 03:30:08', 'paid', 11, 0, NULL, NULL, NULL, NULL),
(183, 87, 2026, 300000.00, 0.0100, 3000.00, 3000.00, NULL, 0.00, 'RPTSP-11-183', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:30:15', '2025-11-06 04:02:39', '2025-10-27 03:30:15', 'paid', 11, 0, NULL, NULL, NULL, NULL),
(184, 65, 2020, 100000.00, 0.0100, 1000.00, 1000.00, NULL, 0.00, 'RPTSP-6-184', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:30:24', '2025-11-08 13:52:08', '2025-10-27 03:30:24', 'paid', 6, 0, NULL, NULL, NULL, NULL),
(185, 66, 2021, 100000.00, 0.0100, 1000.00, 1000.00, NULL, 0.00, 'RPTSP-6-185', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:30:28', '2025-11-08 13:52:08', '2025-10-27 03:30:28', 'paid', 6, 0, NULL, NULL, NULL, NULL),
(186, 67, 2022, 100000.00, 0.0100, 1000.00, 1000.00, NULL, 0.00, 'RPTSP-6-186', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:30:34', '2025-11-08 13:52:08', '2025-10-27 03:30:34', 'paid', 6, 0, NULL, NULL, NULL, NULL),
(187, 68, 2023, 100000.00, 0.0100, 1000.00, 1000.00, NULL, 0.00, 'RPTSP-6-187', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:30:39', '2025-11-08 13:52:08', '2025-10-27 03:30:39', 'paid', 6, 0, NULL, NULL, NULL, NULL),
(188, 69, 2024, 100000.00, 0.0100, 1000.00, 1000.00, NULL, 0.00, 'RPTSP-6-188', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:30:43', '2025-11-08 13:52:08', '2025-10-27 03:30:43', 'paid', 6, 0, NULL, NULL, NULL, NULL),
(189, 70, 2025, 100000.00, 0.0100, 1000.00, 1000.00, NULL, 0.00, 'RPTSP-6-189', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:30:47', '2025-11-08 13:52:08', '2025-10-27 03:30:47', 'paid', 6, 0, NULL, NULL, NULL, NULL),
(190, 72, 2020, 400000.00, 0.0100, 4000.00, 4000.00, NULL, 0.00, 'RPTSP-9-190', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:30:51', '2025-11-09 12:10:34', '2025-10-27 03:30:51', 'paid', 9, 0, NULL, NULL, NULL, NULL),
(191, 73, 2021, 400000.00, 0.0100, 4000.00, 4000.00, NULL, 0.00, 'RPTSP-9-191', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:30:56', '2025-11-09 12:10:34', '2025-10-27 03:30:56', 'paid', 9, 0, NULL, NULL, NULL, NULL),
(192, 74, 2022, 400000.00, 0.0100, 4000.00, 4000.00, NULL, 0.00, 'RPTSP-9-192', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:31:01', '2025-11-09 12:10:34', '2025-10-27 03:31:01', 'paid', 9, 0, NULL, NULL, NULL, NULL),
(193, 75, 2023, 400000.00, 0.0100, 4000.00, 4000.00, NULL, 0.00, 'RPTSP-9-193', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:31:04', '2025-11-09 12:10:34', '2025-10-27 03:31:04', 'paid', 9, 0, NULL, NULL, NULL, NULL),
(194, 76, 2024, 400000.00, 0.0100, 4000.00, 4000.00, NULL, 0.00, 'RPTSP-9-194', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:31:09', '2025-11-09 12:10:34', '2025-10-27 03:31:09', 'paid', 9, 0, NULL, NULL, NULL, NULL),
(195, 77, 2025, 400000.00, 0.0100, 4000.00, 4000.00, NULL, 0.00, 'RPTSP-9-195', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:31:14', '2025-11-09 12:10:34', '2025-10-27 03:31:14', 'paid', 9, 0, NULL, NULL, NULL, NULL),
(196, 59, 2020, 200000.00, 0.0100, 2000.00, 2000.00, NULL, 0.00, 'RPTSP-7-196', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:31:24', '2025-11-09 12:27:15', '2025-10-27 03:31:24', 'paid', 7, 0, NULL, NULL, NULL, NULL),
(197, 60, 2021, 200000.00, 0.0100, 2000.00, 2000.00, NULL, 0.00, 'RPTSP-7-197', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:31:28', '2025-11-09 12:27:15', '2025-10-27 03:31:28', 'paid', 7, 0, NULL, NULL, NULL, NULL),
(198, 61, 2022, 200000.00, 0.0100, 2000.00, 2000.00, NULL, 0.00, 'RPTSP-7-198', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:31:32', '2025-11-09 12:27:15', '2025-10-27 03:31:32', 'paid', 7, 0, NULL, NULL, NULL, NULL),
(199, 62, 2023, 200000.00, 0.0100, 2000.00, 2000.00, NULL, 0.00, 'RPTSP-7-199', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:31:37', '2025-11-09 12:27:15', '2025-10-27 03:31:37', 'paid', 7, 0, NULL, NULL, NULL, NULL),
(200, 63, 2024, 200000.00, 0.0100, 2000.00, 2000.00, NULL, 0.00, 'RPTSP-7-200', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:31:41', '2025-11-09 12:27:15', '2025-10-27 03:31:41', 'paid', 7, 0, NULL, NULL, NULL, NULL),
(201, 64, 2025, 200000.00, 0.0100, 2000.00, 2000.00, NULL, 0.00, 'RPTSP-7-201', NULL, NULL, NULL, NULL, 0.00, NULL, '2025-10-27 03:31:45', '2025-11-09 12:27:15', '2025-10-27 03:31:45', 'paid', 7, 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transfers`
--

CREATE TABLE `transfers` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `from_owner_id` int(11) DEFAULT NULL,
  `to_owner_id` int(11) DEFAULT NULL,
  `transfer_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `emp_idno` varchar(50) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('admin','assessor','assessment_clerk','treasurer','cashier','viewer','hr','hr_staff','employee') NOT NULL DEFAULT 'viewer',
  `email` varchar(100) DEFAULT NULL,
  `contact_no` varchar(30) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `failed_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `emp_idno`, `username`, `password`, `name`, `role`, `email`, `contact_no`, `status`, `last_login`, `created_at`, `updated_at`, `failed_attempts`, `locked_until`) VALUES
(8, NULL, 'admin', '$2y$10$CP4zftCJ01r1AwWfdMoYX.niWUOz/f5ktCkuaI/P2WP7YDOYKlqpq', 'System Administrator', 'admin', 'admin@gmail.com', '88888888888', 'active', NULL, '2025-10-23 07:34:41', '2025-10-24 07:31:19', 0, NULL),
(10, NULL, 'assessor', '$2y$10$bY9p5V2QZLwdQSmLMQmTrecJnQ/4UoJba6pPQEKL8mhL4DfDhbcc2', 'Municipal Assessor', 'assessor', 'assessor@gmail.com', '77777777777', 'active', NULL, '2025-10-23 07:47:26', '2025-10-24 07:30:23', 0, NULL),
(11, NULL, 'assessmentclerk01', '$2y$10$7.wusR4YAM4FSewO9BW3SecHiPLfal6P03wT9pecDF5FzLuWfy.Ie', 'Assessment Clerk 01', 'assessment_clerk', 'assessmentclerk01@gmail.com', '66666666666', 'active', NULL, '2025-10-23 07:47:26', '2025-10-24 07:30:06', 0, NULL),
(12, NULL, 'treasurer', '$2y$10$5sRLg.rZyvYUbYqXWokaEu3NbZLP7Ea.CsGROb.tO5yXgQbtFdVFK', 'MARIA LUISA C. MAGALLANES', 'treasurer', 'treasurer@gmail.com', '55555555555', 'active', NULL, '2025-10-23 07:47:27', '2025-11-07 09:11:46', 0, NULL),
(13, NULL, 'cashier02', '$2y$10$B14vQX3o9PPatRKX2ROXKeqLMeDtgkkAqmM69U4/w682KGWOKm4QC', 'CASHIER NUMBER2', 'cashier', 'cashier02@gmail.com', '44444444444', 'active', NULL, '2025-10-23 07:47:27', '2025-11-06 01:16:44', 0, NULL),
(14, NULL, 'viewer', '$2y$10$hpLo/2f3Cxw./uJWJrC.tO7JKiii1eilTjSxZcVwOtNeaQSGzOycO', 'Public Viewer', 'viewer', 'viewer@gmail.com', '33333333333', 'active', NULL, '2025-10-23 07:47:27', '2025-10-24 07:29:04', 0, NULL),
(15, NULL, 'assessorstaff01', '$2y$10$sKNqcNeTEplUl41yYlConO5jz/878wot1GCcucyIA/JGPNT.9.c3y', 'Assessor Staff', 'assessment_clerk', 'assessorstaff01@gmail.com', '22222222222', 'active', NULL, '2025-10-24 07:14:09', '2025-10-24 07:28:27', 0, NULL),
(16, NULL, 'cashier01', '$2y$10$q4PIE6caQpuQJFeGP8TaYukVKB.wMl25wrSmgCruIhV/1UGGotJDe', 'CASHIER NUMBER1', 'cashier', 'cashier01@gmail.com', '11111111111', 'active', NULL, '2025-10-24 07:15:35', '2025-11-06 01:16:37', 0, NULL),
(18, NULL, 'cashier03', '$2y$10$ii7Od2NKUPOXA4qBYGv1hOqR.8CWumtaqVr1IqkFU0LjGl.9qwkxe', 'CASHIER NUMBER3', 'treasurer', 'cashier03@gmail.com', '99999999999', 'active', NULL, '2025-10-27 06:53:04', '2025-11-06 01:16:28', 0, NULL),
(19, NULL, 'itadmin', '$2y$10$IEdoD8YlX02ZZMLEdRUTFuZHnyuXERU87jUias.TqlDqO.LBhdvP2', 'IT ADMNISTRATOR', 'admin', 'charl2on381@gmail.com', '09914331455', 'active', NULL, '2025-12-09 02:00:45', '2025-12-09 02:00:45', 0, NULL),
(20, NULL, 'humanresource', '$2y$10$QgWxn9RUprtVhTFIgH2uQOzpSqYFgin8M3oEc36qnx0UTGH.Tnz5e', 'HR HR', 'hr', 'humanresource@gmail.com', '147258369', 'active', NULL, '2025-12-09 02:12:05', '2025-12-09 02:12:05', 0, NULL),
(21, NULL, 'employee', '$2y$10$j6Q4zlXynyvzNaNUAZlGZee8/n.dcnL.ZACuC0fCjr.G.VcJKXnDe', 'Employee Employee', 'employee', 'employee@gmail.com', '159357', 'active', NULL, '2025-12-09 02:23:30', '2025-12-09 02:23:30', 0, NULL),
(22, '2025-0001', 'juandelacruz', '$2y$10$ywu0KJDMYpVxT8g6J3mS9.HajpbQ8niz8uLDqhKlRd720M5GhZmn.', 'JUAN DELA CRUZ', 'employee', 'juandelacruz@gmail.com', '147258369', 'active', NULL, '2025-12-09 04:14:58', '2025-12-09 04:14:58', 0, NULL),
(23, '', 'hrstaff', '$2y$10$NeZ8yxdAlBsk1quZL0DV7eLu64F10FE7HcEb1ibonIymQStzFKK/y', 'HR Staff', 'hr_staff', 'hrstaff@gmail.com', '3652147', 'active', NULL, '2025-12-09 05:36:07', '2025-12-09 05:36:07', 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access_logs`
--
ALTER TABLE `access_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bank_reconciliation`
--
ALTER TABLE `bank_reconciliation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `bank_transactions`
--
ALTER TABLE `bank_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `transfer_to_account_id` (`transfer_to_account_id`);

--
-- Indexes for table `checks_issued`
--
ALTER TABLE `checks_issued`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `collections`
--
ALTER TABLE `collections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ctc_corporation`
--
ALTER TABLE `ctc_corporation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ctc_individual`
--
ALTER TABLE `ctc_individual`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ctc_no` (`ctc_no`),
  ADD KEY `year` (`year`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_emp_idno` (`emp_idno`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `fk_employment_status` (`employment_status_id`);

--
-- Indexes for table `employment_status`
--
ALTER TABLE `employment_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `form51`
--
ALTER TABLE `form51`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `form51_items`
--
ALTER TABLE `form51_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `form51_id` (`form51_id`),
  ADD KEY `ngas_id` (`ngas_id`);

--
-- Indexes for table `form58`
--
ALTER TABLE `form58`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `or_no_unique` (`or_no`);

--
-- Indexes for table `fund_source`
--
ALTER TABLE `fund_source`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `municipalities`
--
ALTER TABLE `municipalities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `province_id` (`province_id`);

--
-- Indexes for table `ngas_settings`
--
ALTER TABLE `ngas_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `officials_list`
--
ALTER TABLE `officials_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `owners`
--
ALTER TABLE `owners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`),
  ADD UNIQUE KEY `email_3` (`email`),
  ADD UNIQUE KEY `email_4` (`email`),
  ADD UNIQUE KEY `email_5` (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tax_bill_id` (`tax_bill_id`);

--
-- Indexes for table `payments_list`
--
ALTER TABLE `payments_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_audit`
--
ALTER TABLE `payment_audit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `payment_items`
--
ALTER TABLE `payment_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `tax_bill_id` (`tax_bill_id`);

--
-- Indexes for table `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `td_no` (`td_no`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `property_history`
--
ALTER TABLE `property_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `provinces`
--
ALTER TABLE `provinces`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `receipt_logs`
--
ALTER TABLE `receipt_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bill_id` (`bill_id`);

--
-- Indexes for table `remittance`
--
ALTER TABLE `remittance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date_paid` (`date_paid`),
  ADD KEY `idx_remittance_date` (`remittance_date`);

--
-- Indexes for table `report_sequence`
--
ALTER TABLE `report_sequence`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_records`
--
ALTER TABLE `service_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_emp` (`emp_idno`),
  ADD KEY `fk_position` (`position`),
  ADD KEY `fk_status` (`status`),
  ADD KEY `fk_assignment` (`assignment`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `signatories`
--
ALTER TABLE `signatories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tax_bills`
--
ALTER TABLE `tax_bills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rptsp_no` (`rptsp_no`),
  ADD UNIQUE KEY `bill_no` (`bill_no`),
  ADD KEY `assessment_id` (`assessment_id`);

--
-- Indexes for table `transfers`
--
ALTER TABLE `transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `access_logs`
--
ALTER TABLE `access_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assessments`
--
ALTER TABLE `assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bank_reconciliation`
--
ALTER TABLE `bank_reconciliation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bank_transactions`
--
ALTER TABLE `bank_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `checks_issued`
--
ALTER TABLE `checks_issued`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `collections`
--
ALTER TABLE `collections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `ctc_corporation`
--
ALTER TABLE `ctc_corporation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `ctc_individual`
--
ALTER TABLE `ctc_individual`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employment_status`
--
ALTER TABLE `employment_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `form51`
--
ALTER TABLE `form51`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `form51_items`
--
ALTER TABLE `form51_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `form58`
--
ALTER TABLE `form58`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fund_source`
--
ALTER TABLE `fund_source`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `municipalities`
--
ALTER TABLE `municipalities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ngas_settings`
--
ALTER TABLE `ngas_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=214;

--
-- AUTO_INCREMENT for table `officials_list`
--
ALTER TABLE `officials_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `owners`
--
ALTER TABLE `owners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT for table `payments_list`
--
ALTER TABLE `payments_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=216;

--
-- AUTO_INCREMENT for table `payment_audit`
--
ALTER TABLE `payment_audit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_items`
--
ALTER TABLE `payment_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_logs`
--
ALTER TABLE `payment_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `property_history`
--
ALTER TABLE `property_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `provinces`
--
ALTER TABLE `provinces`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `receipt_logs`
--
ALTER TABLE `receipt_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `remittance`
--
ALTER TABLE `remittance`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `report_sequence`
--
ALTER TABLE `report_sequence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `service_records`
--
ALTER TABLE `service_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `signatories`
--
ALTER TABLE `signatories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tax_bills`
--
ALTER TABLE `tax_bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202;

--
-- AUTO_INCREMENT for table `transfers`
--
ALTER TABLE `transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `access_logs`
--
ALTER TABLE `access_logs`
  ADD CONSTRAINT `access_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `assessments`
--
ALTER TABLE `assessments`
  ADD CONSTRAINT `assessments_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bank_reconciliation`
--
ALTER TABLE `bank_reconciliation`
  ADD CONSTRAINT `bank_reconciliation_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `bank_accounts` (`id`);

--
-- Constraints for table `bank_transactions`
--
ALTER TABLE `bank_transactions`
  ADD CONSTRAINT `bank_transactions_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `bank_accounts` (`id`),
  ADD CONSTRAINT `bank_transactions_ibfk_2` FOREIGN KEY (`transfer_to_account_id`) REFERENCES `bank_accounts` (`id`);

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employment_status` FOREIGN KEY (`employment_status_id`) REFERENCES `employment_status` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `form51_items`
--
ALTER TABLE `form51_items`
  ADD CONSTRAINT `form51_items_ibfk_1` FOREIGN KEY (`form51_id`) REFERENCES `form51` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `form51_items_ibfk_2` FOREIGN KEY (`ngas_id`) REFERENCES `ngas_settings` (`id`);

--
-- Constraints for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD CONSTRAINT `login_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `municipalities`
--
ALTER TABLE `municipalities`
  ADD CONSTRAINT `municipalities_ibfk_1` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`tax_bill_id`) REFERENCES `tax_bills` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_audit`
--
ALTER TABLE `payment_audit`
  ADD CONSTRAINT `payment_audit_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`);

--
-- Constraints for table `payment_items`
--
ALTER TABLE `payment_items`
  ADD CONSTRAINT `payment_items_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_items_ibfk_2` FOREIGN KEY (`tax_bill_id`) REFERENCES `tax_bills` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `property_history`
--
ALTER TABLE `property_history`
  ADD CONSTRAINT `property_history_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `receipt_logs`
--
ALTER TABLE `receipt_logs`
  ADD CONSTRAINT `receipt_logs_ibfk_1` FOREIGN KEY (`bill_id`) REFERENCES `tax_bills` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_records`
--
ALTER TABLE `service_records`
  ADD CONSTRAINT `fk_assignment` FOREIGN KEY (`assignment`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_emp` FOREIGN KEY (`emp_idno`) REFERENCES `employees` (`emp_idno`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_position` FOREIGN KEY (`position`) REFERENCES `positions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_status` FOREIGN KEY (`status`) REFERENCES `employment_status` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tax_bills`
--
ALTER TABLE `tax_bills`
  ADD CONSTRAINT `tax_bills_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transfers`
--
ALTER TABLE `transfers`
  ADD CONSTRAINT `transfers_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
