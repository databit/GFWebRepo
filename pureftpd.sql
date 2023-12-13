-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Creato il: Dic 10, 2023 alle 14:40
-- Versione del server: 5.7.40
-- Versione PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pureftpd`
--
CREATE DATABASE IF NOT EXISTS `pureftpd` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `pureftpd`;

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `password` varchar(106) NOT NULL DEFAULT '',
  `uid` varchar(11) NOT NULL DEFAULT '-1',
  `gid` varchar(11) NOT NULL DEFAULT '-1',
  `dir` varchar(128) NOT NULL DEFAULT '',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `ul_bandwidth` smallint(6) NOT NULL DEFAULT '0',
  `dl_bandwidth` smallint(6) NOT NULL DEFAULT '0',
  `comment` tinytext NOT NULL,
  `ipaccess` varchar(15) NOT NULL DEFAULT '*',
  `quota_size` smallint(6) NOT NULL DEFAULT '0',
  `quota_files` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user`),
  ADD UNIQUE KEY `user` (`user`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

CREATE USER 'pureftpd'@'localhost' IDENTIFIED WITH mysql_native_password BY 'O2t11[A8yZj#';
GRANT ALL PRIVILEGES ON *.* TO 'pureftpd'@'localhost' REQUIRE NONE WITH GRANT OPTION MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0; 
FLUSH PRIVILEGES;
INSERT INTO `users` (`user`, `name`, `email`, `status`, `is_admin`, `password`, `uid`, `gid`, `dir`, `ul_bandwidth`, `dl_bandwidth`, `comment`, `ipaccess`, `quota_size`, `quota_files`) VALUES ('admin', 'Amministratore', 'admin@loacalhost.org', '1', '1', '$1$OG4RNwvn$HKMVueFdNd9g2aarmiGK51','2001','2001','/var/www/html/users/','0','0','','*','0','0');
FLUSH PRIVILEGES;