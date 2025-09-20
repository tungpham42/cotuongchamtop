-- MySQL dump 10.13  Distrib 8.0.23, for Linux (x86_64)
--
-- Host: localhost    Database: webmaster_toolkit
-- ------------------------------------------------------
-- Server version	8.0.23-0ubuntu0.20.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `wt_alexa`
--

DROP TABLE IF EXISTS `wt_alexa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wt_alexa` (
  `Domain` varchar(255) NOT NULL,
  `Added` timestamp NULL DEFAULT NULL,
  `Modified` timestamp NULL DEFAULT NULL,
  `linksin` int unsigned DEFAULT '0',
  `review_count` int unsigned DEFAULT '0',
  `review_avg` float DEFAULT '0',
  `rank` int unsigned DEFAULT '0',
  `country_code` varchar(2) DEFAULT NULL,
  `country_name` varchar(255) DEFAULT NULL,
  `country_rank` int unsigned DEFAULT '0',
  `speed_time` int unsigned DEFAULT '0',
  `pct` tinyint unsigned DEFAULT '0',
  `data` mediumtext,
  PRIMARY KEY (`Domain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wt_alexa`
--

LOCK TABLES `wt_alexa` WRITE;
/*!40000 ALTER TABLE `wt_alexa` DISABLE KEYS */;
/*!40000 ALTER TABLE `wt_alexa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wt_backlinks`
--

DROP TABLE IF EXISTS `wt_backlinks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wt_backlinks` (
  `Domain` varchar(255) NOT NULL,
  `Added` timestamp NULL DEFAULT NULL,
  `Modified` timestamp NULL DEFAULT NULL,
  `Cnt` bigint unsigned DEFAULT '0',
  PRIMARY KEY (`Domain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wt_backlinks`
--

LOCK TABLES `wt_backlinks` WRITE;
/*!40000 ALTER TABLE `wt_backlinks` DISABLE KEYS */;
/*!40000 ALTER TABLE `wt_backlinks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wt_diagnostic`
--

DROP TABLE IF EXISTS `wt_diagnostic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wt_diagnostic` (
  `Domain` varchar(255) NOT NULL,
  `Added` timestamp NULL DEFAULT NULL,
  `Modified` timestamp NULL DEFAULT NULL,
  `google` enum('safe','warning','caution','untested') NOT NULL,
  `mcafee` enum('safe','warning','caution','untested') NOT NULL,
  `norton` enum('safe','warning','caution','untested') NOT NULL,
  `avg` enum('safe','warning','caution','untested') NOT NULL,
  PRIMARY KEY (`Domain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wt_diagnostic`
--

LOCK TABLES `wt_diagnostic` WRITE;
/*!40000 ALTER TABLE `wt_diagnostic` DISABLE KEYS */;
/*!40000 ALTER TABLE `wt_diagnostic` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wt_location`
--

DROP TABLE IF EXISTS `wt_location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wt_location` (
  `Domain` varchar(255) NOT NULL,
  `Added` timestamp NULL DEFAULT NULL,
  `Modified` timestamp NULL DEFAULT NULL,
  `ip` varchar(64) DEFAULT NULL,
  `country_name` varchar(500) DEFAULT NULL,
  `country_code` varchar(2) DEFAULT NULL,
  `city` varchar(500) DEFAULT NULL,
  `latitude` float DEFAULT '0',
  `longitude` float DEFAULT '0',
  `region_name` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`Domain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wt_location`
--

LOCK TABLES `wt_location` WRITE;
/*!40000 ALTER TABLE `wt_location` DISABLE KEYS */;
/*!40000 ALTER TABLE `wt_location` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wt_search`
--

DROP TABLE IF EXISTS `wt_search`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wt_search` (
  `Domain` varchar(255) NOT NULL,
  `Added` timestamp NULL DEFAULT NULL,
  `Modified` timestamp NULL DEFAULT NULL,
  `google` bigint unsigned DEFAULT '0',
  `yahoo` bigint unsigned DEFAULT '0',
  `yandex` bigint unsigned DEFAULT '0',
  `bing` bigint unsigned DEFAULT '0',
  PRIMARY KEY (`Domain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wt_search`
--

LOCK TABLES `wt_search` WRITE;
/*!40000 ALTER TABLE `wt_search` DISABLE KEYS */;
/*!40000 ALTER TABLE `wt_search` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wt_social`
--

DROP TABLE IF EXISTS `wt_social`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wt_social` (
  `Domain` varchar(255) NOT NULL,
  `Added` timestamp NULL DEFAULT NULL,
  `Modified` timestamp NULL DEFAULT NULL,
  `pinterest` int unsigned DEFAULT '0',
  `share_count` int unsigned DEFAULT '0',
  `like_count` int unsigned DEFAULT '0',
  `comment_count` int unsigned DEFAULT '0',
  `total_count` int unsigned DEFAULT '0',
  `click_count` int unsigned DEFAULT '0',
  `reaction_count` int unsigned DEFAULT '0',
  `comment_plugin_count` int unsigned DEFAULT '0',
  PRIMARY KEY (`Domain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wt_social`
--

LOCK TABLES `wt_social` WRITE;
/*!40000 ALTER TABLE `wt_social` DISABLE KEYS */;
/*!40000 ALTER TABLE `wt_social` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wt_whois`
--

DROP TABLE IF EXISTS `wt_whois`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wt_whois` (
  `Domain` varchar(255) NOT NULL,
  `Added` timestamp NULL DEFAULT NULL,
  `Modified` timestamp NULL DEFAULT NULL,
  `Whois` mediumtext NOT NULL,
  PRIMARY KEY (`Domain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wt_whois`
--

LOCK TABLES `wt_whois` WRITE;
/*!40000 ALTER TABLE `wt_whois` DISABLE KEYS */;
/*!40000 ALTER TABLE `wt_whois` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-06-11  7:47:14
