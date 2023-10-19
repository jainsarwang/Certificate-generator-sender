-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2023 at 03:11 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `gdsc`
--
CREATE DATABASE IF NOT EXISTS `certificae_generator_sender` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `certificae_generator_sender`;

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE IF NOT EXISTS `certificates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `template_id` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  `generated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `send_at` datetime DEFAULT NULL,
  `error` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE IF NOT EXISTS `templates` (
  `id` varchar(30) NOT NULL,
  `event` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `html_file` varchar(255) NOT NULL,
  `text_file` varchar(255) NOT NULL,
  `certificate_file` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `templates`
--

INSERT INTO `templates` (`id`, `event`, `subject`, `html_file`, `text_file`, `certificate_file`) VALUES
('64f5b0471a948', 'React & Node JS workshop', 'Certification for React and Node JS workshop from GDSC UECU', 'reactNodeJSEmail.php', 'reactNodeJSEmail.txt', 'reactNodeJSCert-participation.png'),
('64f61391957b0', 'React workshop', 'Certification for React workshop from GDSC UECU', 'reactNodeJSEmail.php', 'reactNodeJSEmail.txt', 'reactNodeJSCert-participation.png');
COMMIT;
