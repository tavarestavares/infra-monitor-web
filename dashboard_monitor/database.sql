-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 19/09/2025 às 14:38
-- Versão do servidor: 10.11.13-MariaDB-0ubuntu0.24.04.1
-- Versão do PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `monitor_dashboard`
--
CREATE DATABASE IF NOT EXISTS monitor_dashboard;
USE monitor_dashboard;

--
-- Estrutura para tabela `alerts`
--

DROP TABLE IF EXISTS `alerts`;
CREATE TABLE `alerts` (
  `id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `service_type` enum('internal','external') DEFAULT NULL,
  `alert_type` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `resolved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `check_history`
--

DROP TABLE IF EXISTS `check_history`;
CREATE TABLE `check_history` (
  `id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `service_type` enum('internal','external') DEFAULT NULL,
  `status` enum('online','offline') DEFAULT NULL,
  `response_time` int(11) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `checked_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `external_services`
--

DROP TABLE IF EXISTS `external_services`;
CREATE TABLE `external_services` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `ip_address` varchar(15) DEFAULT NULL,
  `port` int(11) DEFAULT 80,
  `service_type` varchar(50) DEFAULT NULL,
  `check_interval` int(11) DEFAULT 60,
  `status` enum('online','offline','pending') DEFAULT 'pending',
  `last_check` timestamp NULL DEFAULT NULL,
  `response_time` int(11) DEFAULT 0,
  `ssl_expiry` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `internal_services`
--

DROP TABLE IF EXISTS `internal_services`;
CREATE TABLE `internal_services` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `ip_address` varchar(15) NOT NULL,
  `port` int(11) DEFAULT 80,
  `service_type` varchar(50) DEFAULT NULL,
  `check_interval` int(11) DEFAULT 60,
  `status` enum('online','offline','pending') DEFAULT 'pending',
  `last_check` timestamp NULL DEFAULT NULL,
  `response_time` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `check_history`
--
ALTER TABLE `check_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service` (`service_id`,`service_type`),
  ADD KEY `idx_checked` (`checked_at`);

--
-- Índices de tabela `external_services`
--
ALTER TABLE `external_services`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `internal_services`
--
ALTER TABLE `internal_services`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `check_history`
--
ALTER TABLE `check_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `external_services`
--
ALTER TABLE `external_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `internal_services`
--
ALTER TABLE `internal_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
