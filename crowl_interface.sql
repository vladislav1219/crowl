-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le :  ven. 11 déc. 2020 à 10:13
-- Version du serveur :  10.3.27-MariaDB-0+deb10u1
-- Version de PHP :  7.3.19-1~deb10u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `crowl_interface`
--

-- --------------------------------------------------------

--
-- Structure de la table `configuration`
--

CREATE TABLE `configuration` (
  `id` int(11) NOT NULL,
  `crawl_id` int(11) DEFAULT NULL,
  `projectName` varchar(255) DEFAULT NULL,
  `startUrl` varchar(255) DEFAULT NULL,
  `userAgent` varchar(255) DEFAULT NULL,
  `acceptLanguage` varchar(255) DEFAULT NULL,
  `mimeTypes` varchar(255) DEFAULT NULL,
  `amountInputDelay` int(11) DEFAULT NULL,
  `amountInputThreads` int(11) DEFAULT NULL,
  `amountInputDepth` int(11) DEFAULT NULL,
  `exclusions` text DEFAULT NULL,
  `obeyRobots` varchar(4) DEFAULT NULL,
  `storeLinks` varchar(4) DEFAULT NULL,
  `storePageContent` varchar(4) DEFAULT NULL,
  `outputMysql` varchar(4) DEFAULT NULL,
  `outputCsv` varchar(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `configuration`
--

INSERT INTO `configuration` (`id`, `crawl_id`, `projectName`, `startUrl`, `userAgent`, `acceptLanguage`, `mimeTypes`, `amountInputDelay`, `amountInputThreads`, `amountInputDepth`, `exclusions`, `obeyRobots`, `storeLinks`, `storePageContent`, `outputMysql`, `outputCsv`) VALUES
(2, 127, 'tester', 'http://example.com', 'Crowl (+https://www.crowl.tech/)', 'en', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', 1, 5, 5, 'testit\r\ntestit2', 'on', 'on', NULL, 'on', NULL),
(3, 128, 'caroom_static', 'https://www.caroom.fr', 'Crowl (+https://www.crowl.tech/)', 'en', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', 1, 5, 5, '/offres/\r\nreplytocom', 'on', 'on', NULL, 'on', NULL),
(4, 129, 'test', 'http://example.com', 'Crowl (+https://www.crowl.tech/)', 'en', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', 1, 5, 5, '', 'on', 'on', NULL, 'on', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `crawls`
--

CREATE TABLE `crawls` (
  `id` int(255) NOT NULL,
  `name` varchar(2500) NOT NULL DEFAULT 'Crawl',
  `domain` varchar(4096) DEFAULT NULL,
  `nameid` varchar(1000) DEFAULT NULL,
  `start_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `state` varchar(255) NOT NULL DEFAULT 'running',
  `db` tinyint(255) NOT NULL DEFAULT 0,
  `config` varchar(2555) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `crawls`
--

INSERT INTO `crawls` (`id`, `name`, `domain`, `nameid`, `start_time`, `state`, `db`, `config`) VALUES
(116, 'Crawl #1', 'clubgtevolution.fr', 'Crawl #1_116', '2020-11-15 14:07:58', 'finished', 1, '\n[PROJECT]\nPROJECT_NAME = Crawl #1\nSTART_URL = http://clubgtevolution.fr/\n[CRAWLER]\nUSER_AGENT = Crowl (+https://www.crowl.tech/)\nROBOTS_TXT_OBEY = True\nEXCLUSION_PATTERN = \nDOWNLOAD_DELAY = 0.5\nCONCURRENT_REQUESTS = 5\nMIME_TYPES = text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\nACCEPT_LANGUAGE = en\n\n[EXTRACTION]\nLINKS = True\nCONTENT = False\nDEPTH = 5\n\n[OUTPUT]\n\ncrowl.CrowlMySQLPipeline = 200\n\n[MYSQL]\nMYSQL_HOST = localhost\nMYSQL_PORT = 3306\nMYSQL_USER = Syard\nMYSQL_PASSWORD = Badorpopopopo*4\n            '),
(119, 'fyore_goodazeazeaz eaze', 'demarseilleetdailleurs.com', 'fyore_good_119', '2020-11-15 16:33:56', 'finished', 1, '\n[PROJECT]\nPROJECT_NAME = fyore_good\nSTART_URL = https://www.demarseilleetdailleurs.com/fr\n[CRAWLER]\nUSER_AGENT = Crowl (+https://www.crowl.tech/)\nROBOTS_TXT_OBEY = True\nEXCLUSION_PATTERN = \nDOWNLOAD_DELAY = 0.5\nCONCURRENT_REQUESTS = 5\nMIME_TYPES = text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\nACCEPT_LANGUAGE = en\n\n[EXTRACTION]\nLINKS = True\nCONTENT = False\nDEPTH = 5\n\n[OUTPUT]\n\ncrowl.CrowlMySQLPipeline = 200\n\n[MYSQL]\nMYSQL_HOST = localhost\nMYSQL_PORT = 3306\nMYSQL_USER = Syard\nMYSQL_PASSWORD = Badorpopopopo*4\n            '),
(120, 'Elise', 'unistra.fr', 'Elise_120', '2020-11-16 17:52:26', 'finished', 1, '\n[PROJECT]\nPROJECT_NAME = Elise\nSTART_URL = https://ethnologie.unistra.fr/\n[CRAWLER]\nUSER_AGENT = Crowl (+https://www.crowl.tech/)\nROBOTS_TXT_OBEY = True\nEXCLUSION_PATTERN = \nDOWNLOAD_DELAY = 0.5\nCONCURRENT_REQUESTS = 5\nMIME_TYPES = text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\nACCEPT_LANGUAGE = en\n\n[EXTRACTION]\nLINKS = True\nCONTENT = False\nDEPTH = 10\n\n[OUTPUT]\n\ncrowl.CrowlMySQLPipeline = 200\n\n[MYSQL]\nMYSQL_HOST = localhost\nMYSQL_PORT = 3306\nMYSQL_USER = Syard\nMYSQL_PASSWORD = Badorpopopopo*4\n            '),
(128, 'caroom_static', 'caroom.fr', 'caroom_static_128', '2020-11-23 15:13:37', 'finished', 1, '\n[PROJECT]\nPROJECT_NAME = caroom_static\nSTART_URL = https://www.caroom.fr\n[CRAWLER]\nUSER_AGENT = Crowl (+https://www.crowl.tech/)\nROBOTS_TXT_OBEY = True\nEXCLUSION_PATTERN = /offres/\n    replytocom\nDOWNLOAD_DELAY = 0.5\nCONCURRENT_REQUESTS = 5\nMIME_TYPES = text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\nACCEPT_LANGUAGE = en\n\n[EXTRACTION]\nLINKS = True\nCONTENT = False\nDEPTH = 5\n\n[OUTPUT]\n\ncrowl.CrowlMySQLPipeline = 200\n\n[MYSQL]\nMYSQL_HOST = localhost\nMYSQL_PORT = 3306\nMYSQL_USER = Syard\nMYSQL_PASSWORD = Badorpopopopo*4\n            ');

-- --------------------------------------------------------

--
-- Structure de la table `crawl_stats`
--

CREATE TABLE `crawl_stats` (
  `stats_id` int(11) NOT NULL,
  `crawl_id` int(11) NOT NULL,
  `enqueued` int(50) NOT NULL,
  `dequeued` int(50) NOT NULL,
  `response_bytes` int(50) NOT NULL,
  `response_received_count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `crawl_stats`
--

INSERT INTO `crawl_stats` (`stats_id`, `crawl_id`, `enqueued`, `dequeued`, `response_bytes`, `response_received_count`) VALUES
(55, 106, 1, 1, 1001, 1),
(56, 107, 1, 1, 1001, 1),
(57, 108, 1, 1, 1007, 1),
(58, 109, 1, 1, 984, 1),
(59, 110, 1, 1, 1001, 1),
(60, 111, 1, 1, 990, 1),
(61, 112, 1, 1, 1001, 1),
(62, 113, 1, 1, 1001, 1),
(63, 114, 1, 1, 1001, 1),
(64, 115, 1, 1, 1001, 1),
(65, 116, 8, 8, 42352, 8),
(68, 119, 514, 514, 23750699, 514),
(69, 120, 536, 536, 6112931, 534),
(74, 128, 6208, 6208, 72501011, 5917);

-- --------------------------------------------------------

--
-- Structure de la table `lastId`
--

CREATE TABLE `lastId` (
  `lastId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `lastId`
--

INSERT INTO `lastId` (`lastId`) VALUES
(129);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `configuration`
--
ALTER TABLE `configuration`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `crawls`
--
ALTER TABLE `crawls`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `crawl_stats`
--
ALTER TABLE `crawl_stats`
  ADD PRIMARY KEY (`stats_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `configuration`
--
ALTER TABLE `configuration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `crawls`
--
ALTER TABLE `crawls`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT pour la table `crawl_stats`
--
ALTER TABLE `crawl_stats`
  MODIFY `stats_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
