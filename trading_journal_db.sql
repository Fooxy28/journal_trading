-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 02, 2026 at 07:29 AM
-- Server version: 12.3.1-MariaDB-log
-- PHP Version: 8.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `trading_journal_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `account_name`, `description`, `created_at`) VALUES
(1, 'Demo 1', NULL, '2026-03-02 07:12:16');

-- --------------------------------------------------------

--
-- Table structure for table `balances`
--

CREATE TABLE `balances` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `balance_amount` decimal(15,2) DEFAULT 0.00,
  `last_updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `balances`
--

INSERT INTO `balances` (`id`, `account_id`, `balance_amount`, `last_updated`) VALUES
(1, 1, 8022.00, '2026-03-02 07:27:45');

-- --------------------------------------------------------

--
-- Table structure for table `balance_logs`
--

CREATE TABLE `balance_logs` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `type` enum('deposit','withdraw','adjustment','trade') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `balance_logs`
--

INSERT INTO `balance_logs` (`id`, `account_id`, `type`, `amount`, `note`, `created_at`) VALUES
(1, 1, 'trade', 12.00, 'Trade ID: 1', '2026-03-02 07:15:05'),
(2, 1, 'trade', 10.00, 'Trade ID: 2', '2026-03-02 07:20:27'),
(3, 1, 'deposit', 1000.00, '-', '2026-03-02 07:26:10');

-- --------------------------------------------------------

--
-- Table structure for table `pairs`
--

CREATE TABLE `pairs` (
  `id` int(11) NOT NULL,
  `pair_code` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pairs`
--

INSERT INTO `pairs` (`id`, `pair_code`, `created_at`) VALUES
(1, 'XAUUSD', '2026-03-02 07:07:39'),
(3, 'BTCUSD', '2026-03-02 07:13:14');

-- --------------------------------------------------------

--
-- Table structure for table `strategies`
--

CREATE TABLE `strategies` (
  `id` int(11) NOT NULL,
  `strategy_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `strategies`
--

INSERT INTO `strategies` (`id`, `strategy_name`, `description`, `created_at`) VALUES
(1, 'CRT', NULL, '2026-03-02 07:13:39');

-- --------------------------------------------------------

--
-- Table structure for table `trades`
--

CREATE TABLE `trades` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `trade_date` date NOT NULL,
  `pair_id` int(11) NOT NULL,
  `position_type` enum('buy','sell') NOT NULL,
  `entry_price` decimal(15,5) NOT NULL,
  `exit_price` decimal(15,5) NOT NULL,
  `trading_status` enum('profit','loss') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `net_percent` decimal(8,2) NOT NULL,
  `lot` decimal(10,2) NOT NULL,
  `tp_sl_status` enum('hit','manual') NOT NULL,
  `strategy_id` int(11) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `analysis_link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trades`
--

INSERT INTO `trades` (`id`, `account_id`, `trade_date`, `pair_id`, `position_type`, `entry_price`, `exit_price`, `trading_status`, `amount`, `net_percent`, `lot`, `tp_sl_status`, `strategy_id`, `note`, `analysis_link`, `created_at`) VALUES
(1, 1, '2026-03-02', 1, 'sell', 5167.00000, 5267.00000, 'loss', 12.00, 0.00, 0.01, 'hit', 1, '-', 'https://tradingview.id/', '2026-03-02 07:15:05'),
(2, 1, '2026-03-02', 1, 'buy', 5267.00000, 5300.00000, 'profit', 10.00, 83.33, 0.01, 'hit', 1, '-', '-', '2026-03-02 07:20:27'),
(3, 1, '2026-03-02', 3, 'buy', 6600.00000, 6300.00000, 'loss', 500.00, 48.92, 1.00, 'hit', 1, '-', '-', '2026-03-02 07:27:20'),
(4, 1, '2026-03-02', 3, 'buy', 6600.00000, 6300.00000, 'loss', 500.00, 32.85, 1.00, 'hit', 1, '-', '-', '2026-03-02 07:27:23'),
(5, 1, '2026-03-02', 3, 'buy', 6600.00000, 6300.00000, 'loss', 500.00, 24.73, 1.00, 'hit', 1, '-', '-', '2026-03-02 07:27:25'),
(6, 1, '2026-03-02', 3, 'buy', 6600.00000, 6300.00000, 'loss', 500.00, 19.83, 1.00, 'hit', 1, '-', '-', '2026-03-02 07:27:26'),
(7, 1, '2026-03-02', 3, 'buy', 6600.00000, 6300.00000, 'loss', 500.00, 16.55, 1.00, 'hit', 1, '-', '-', '2026-03-02 07:27:27'),
(8, 1, '2026-03-02', 3, 'buy', 6600.00000, 6300.00000, 'loss', 500.00, 14.20, 1.00, 'hit', 1, '-', '-', '2026-03-02 07:27:29'),
(9, 1, '2026-03-02', 3, 'buy', 6600.00000, 6300.00000, 'loss', 500.00, 12.43, 1.00, 'hit', 1, '-', '-', '2026-03-02 07:27:32'),
(10, 1, '2026-03-02', 3, 'buy', 6600.00000, 6300.00000, 'loss', 500.00, 11.06, 1.00, 'hit', 1, '-', '-', '2026-03-02 07:27:33'),
(11, 1, '2026-03-02', 3, 'buy', 6600.00000, 6300.00000, 'loss', 500.00, 9.96, 1.00, 'hit', 1, '-', '-', '2026-03-02 07:27:36'),
(12, 1, '2026-03-02', 3, 'buy', 6600.00000, 6300.00000, 'loss', 500.00, 9.05, 1.00, 'hit', 1, '-', '-', '2026-03-02 07:27:37'),
(13, 1, '2026-03-02', 3, 'buy', 6600.00000, 6300.00000, 'loss', 500.00, 8.30, 1.00, 'hit', 1, '-', '-', '2026-03-02 07:27:40'),
(14, 1, '2026-03-02', 3, 'buy', 6600.00000, 6300.00000, 'loss', 500.00, 7.67, 1.00, 'hit', 1, '-', '-', '2026-03-02 07:27:41'),
(15, 1, '2026-03-02', 3, 'buy', 6600.00000, 6300.00000, 'loss', 500.00, 7.12, 1.00, 'hit', 1, '-', '-', '2026-03-02 07:27:44'),
(16, 1, '2026-03-02', 3, 'buy', 6600.00000, 6300.00000, 'loss', 500.00, 6.65, 1.00, 'hit', 1, '-', '-', '2026-03-02 07:27:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `balances`
--
ALTER TABLE `balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_id` (`account_id`);

--
-- Indexes for table `balance_logs`
--
ALTER TABLE `balance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `pairs`
--
ALTER TABLE `pairs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pair_code` (`pair_code`);

--
-- Indexes for table `strategies`
--
ALTER TABLE `strategies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `strategy_name` (`strategy_name`);

--
-- Indexes for table `trades`
--
ALTER TABLE `trades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `pair_id` (`pair_id`),
  ADD KEY `strategy_id` (`strategy_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `balances`
--
ALTER TABLE `balances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `balance_logs`
--
ALTER TABLE `balance_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pairs`
--
ALTER TABLE `pairs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `strategies`
--
ALTER TABLE `strategies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `trades`
--
ALTER TABLE `trades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `balances`
--
ALTER TABLE `balances`
  ADD CONSTRAINT `1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `balance_logs`
--
ALTER TABLE `balance_logs`
  ADD CONSTRAINT `1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trades`
--
ALTER TABLE `trades`
  ADD CONSTRAINT `1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `2` FOREIGN KEY (`pair_id`) REFERENCES `pairs` (`id`),
  ADD CONSTRAINT `3` FOREIGN KEY (`strategy_id`) REFERENCES `strategies` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
