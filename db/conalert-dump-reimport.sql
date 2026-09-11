-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: conalert
-- ------------------------------------------------------
-- Server version	8.4.3

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
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `status` enum('active','revoked') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'Admin User','admin@conalert.org','$2y$10$vOqrtCTOpNlaYh3BVCYEKuuME4C/iNT8VnCgsEpVLzNbxidd3jqxK','admin','active','2026-09-08 18:55:32',NULL,'2026-09-08 19:06:40');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `case_events`
--

DROP TABLE IF EXISTS `case_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `case_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `case_id` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `event_type` varchar(50) NOT NULL,
  `detail` text,
  `actor` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `case_id` (`case_id`),
  CONSTRAINT `case_events_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `case_events`
--

LOCK TABLES `case_events` WRITE;
/*!40000 ALTER TABLE `case_events` DISABLE KEYS */;
INSERT INTO `case_events` VALUES (1,2,'2026-09-08 18:56:05','submitted','Public submission received (sample data)',NULL),(2,3,'2026-09-08 18:56:05','submitted','Public submission received (sample data)',NULL),(3,4,'2026-09-08 18:56:05','submitted','Public submission received (sample data)',NULL),(4,5,'2026-09-08 18:56:20','submitted','Public submission received',NULL),(5,2,'2026-09-08 18:56:41','status_change','new -> published','Admin User <admin@conalert.org>'),(6,2,'2026-09-08 18:56:41','summary_edited','Public summary updated','Admin User <admin@conalert.org>'),(7,2,'2026-09-08 18:56:41','note_added','Admin notes updated','Admin User <admin@conalert.org>'),(8,6,'2026-09-08 19:04:57','submitted','Public submission received',NULL);
/*!40000 ALTER TABLE `case_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cases`
--

DROP TABLE IF EXISTS `cases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cases` (
  `id` int NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('new','in_review','verified','published','resolved','rejected') NOT NULL DEFAULT 'new',
  `priority` enum('low','normal','high') NOT NULL DEFAULT 'normal',
  `full_name` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `country` varchar(100) DEFAULT NULL,
  `platform_name` varchar(200) NOT NULL,
  `platform_type` enum('casino','exchange','other') NOT NULL,
  `amount_usd` decimal(14,2) DEFAULT NULL,
  `currency_lost` varchar(20) DEFAULT NULL,
  `incident_date` varchar(20) DEFAULT NULL,
  `description` text NOT NULL,
  `evidence_links` text,
  `consent_to_publish` tinyint(1) NOT NULL DEFAULT '0',
  `public_summary` text,
  `admin_notes` text,
  `honeypot_tripped` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_cases_status` (`status`),
  KEY `idx_cases_platform` (`platform_name`),
  KEY `idx_cases_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cases`
--

LOCK TABLES `cases` WRITE;
/*!40000 ALTER TABLE `cases` DISABLE KEYS */;
INSERT INTO `cases` VALUES (1,'2026-09-08 18:55:32','2026-09-08 18:55:32','published','normal','Example Reporter (illustrative)','example@example.org','Not specified','bets.io','casino',14000.00,'USDT','2026-01-01','SAMPLE / ILLUSTRATIVE CASE — replace with a real, evidence-backed submission before relying on this. Reporter states that a withdrawal request for approximately $14,000 was frozen by the platform, that their account was subsequently restricted, and that support did not provide a resolution after repeated contact. Include exact dates, ticket numbers, and screenshots for a real case.','https://example.org/replace-with-real-screenshot-link\nhttps://example.org/replace-with-transaction-hash-link',1,'A reporter says a withdrawal of roughly $14,000 from bets.io was frozen and their account restricted, with no resolution after repeated support contact. This is an example entry seeded with this software — it has not been independently verified. Real cases should only be published after the evidence checklist in this file has been followed and the platform has been given a chance to respond.','Seed/demo data. Do not treat as a verified real case. Before publishing anything about a real company: verify evidence, request comment from the company, and get the wording checked.',0),(2,'2026-09-08 18:56:05','2026-09-08 18:56:41','published','normal','Ama Boateng','ama.boateng@example.com','Ghana','CryptoSpin','casino',2500.00,'USDT','2026-02-15','I deposited 2500 USDT on CryptoSpin on Jan 10, won some bets, then tried to withdraw on Feb 15. The withdrawal has been stuck on \"pending\" for 3 weeks. Support ticket #CS-88291 keeps saying \"under review\" with no timeline.','https://imgur.com/example-amascreenshot\nhttps://etherscan.io/tx/0xabc123',1,'A reporter states that a withdrawal of about $2500 from CryptoSpin has been stuck as pending since Feb 15 (ticket CS-88291) with repeated \'under review\' responses and no timeline provided. Evidence includes screenshots and a transaction hash.','Sample publish - verified screenshots placeholder',0),(3,'2026-09-08 18:56:05','2026-09-08 18:56:05','new','normal','James Okafor','j.okafor@example.com','Nigeria','BitVault','exchange',8200.50,'BTC','2026-03-02','BitVault froze my BTC withdrawal of approx 0.12 BTC (~$8200) on Mar 2 citing \"risk review\". KYC was already verified. Support chat transcript saved, ticket BV-44912. No response after 10 days.','https://imgur.com/example-james\nhttps://blockstream.info/tx/abc456',1,NULL,NULL,0),(4,'2026-09-08 18:56:05','2026-09-08 18:56:05','new','normal','Linda Park','linda.park@example.com','Kenya','PaxPay','other',1150.00,'USD','2026-01-28','PaxPay held my $1150 withdrawal after I sold gift cards. They asked for extra ID which I provided, then account was restricted. Ticket PP-7721.','https://imgur.com/example-linda',0,NULL,NULL,0),(5,'2026-09-08 18:56:20','2026-09-08 18:56:20','new','normal','Kwame Asare','kwame.test@example.com','Ghana','StakeCrypt','casino',4320.00,'USDT','2026-03-10','Deposited 4320 USDT, met wagering requirements, withdrawal blocked on Mar 10. Support ticket ST-9912, no resolution after 2 weeks. Evidence screenshots saved.','https://imgur.com/kwame-evidence1\nhttps://tronscan.org/#/transaction/abc789',1,NULL,NULL,0),(6,'2026-09-08 19:04:57','2026-09-08 19:04:57','new','normal','Michael Boafo','mikeboafo30@gmail.com','Ghana','bet.io','casino',1400.00,'USD','2026-09-08','HERE IS WHAT HAPPENED','LINK HERE',1,NULL,NULL,0);
/*!40000 ALTER TABLE `cases` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-08 19:32:18
