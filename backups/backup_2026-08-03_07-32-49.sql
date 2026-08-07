-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: inventory_system
-- ------------------------------------------------------
-- Server version	11.8.6-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `username` varchar(100) NOT NULL,
  `role` enum('Super Admin','Admin','Viewer') NOT NULL,
  `action` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `module` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=396 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (3,7,'CARMINA VALLEJO','carmina25','Super Admin','Created a new account','::1','2026-07-15 16:28:56',NULL),(4,8,'CARMINA VALLEJO','carmina2519','Admin','Created a new account','::1','2026-07-15 16:31:48',NULL),(5,9,'CARMINA VALLEJO','carmina251911','Viewer','Created a new account','::1','2026-07-15 16:32:14',NULL),(6,10,'CARMINA VALLEJO','carmina2519111','Viewer','Created a new account','::1','2026-07-15 16:38:26',NULL),(7,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt','::1','2026-07-15 16:38:36',NULL),(8,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt','::1','2026-07-15 16:39:36',NULL),(9,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt','::1','2026-07-15 16:39:44',NULL),(10,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt','::1','2026-07-15 16:40:07',NULL),(11,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt','::1','2026-07-15 16:40:18',NULL),(12,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt','::1','2026-07-15 16:40:44',NULL),(13,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt','::1','2026-07-15 16:41:12',NULL),(14,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-15 16:54:03',NULL),(15,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-15 16:54:14',NULL),(16,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-15 16:54:18',NULL),(17,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-15 16:54:34',NULL),(18,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-15 16:54:39',NULL),(19,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-15 23:20:55',NULL),(20,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-15 23:21:00',NULL),(21,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 12:44:23',NULL),(22,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 12:45:50',NULL),(23,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 12:47:02',NULL),(24,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 12:47:20',NULL),(25,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 12:49:38',NULL),(26,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 13:02:31',NULL),(27,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 13:09:50',NULL),(28,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 13:10:05',NULL),(29,8,'CARMINA VALLEJO','carmina2519','Admin','Logged in successfully','::1','2026-07-16 13:56:16',NULL),(30,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 13:56:56',NULL),(31,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-16 14:04:06',NULL),(32,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 01:48:48',NULL),(33,7,'CARMINA VALLEJO','carmina25','Super Admin','Reset password for carmina251911','::1','2026-07-17 01:49:59',NULL),(34,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 01:50:20',NULL),(35,9,'CARMINA VALLEJO','carmina251911','Viewer','Logged in successfully','::1','2026-07-17 01:50:25',NULL),(36,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 01:50:35',NULL),(37,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 01:59:40',NULL),(38,7,'CARMINA VALLEJO','carmina25','Super Admin','Activated user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 02:14:13',NULL),(39,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 02:14:16',NULL),(40,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz12511@gmail.com)','::1','2026-07-17 02:29:33',NULL),(41,7,'CARMINA VALLEJO','carmina25','Super Admin','Activated user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 02:30:12',NULL),(42,7,'CARMINA VALLEJO','carmina25','Super Admin','Activated user account: CARMINA VALLEJO (carriz12511@gmail.com)','::1','2026-07-17 02:30:14',NULL),(43,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 02:34:50',NULL),(44,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 04:00:37',NULL),(45,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 04:24:31',NULL),(46,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 05:23:48',NULL),(47,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 06:34:30',NULL),(48,7,'CARMINA VALLEJO','carmina25','Super Admin','Reset password for carmina2519','::1','2026-07-17 06:35:20',NULL),(49,7,'CARMINA VALLEJO','carmina25','Super Admin','Reset password for carmina25','::1','2026-07-17 06:38:23',NULL),(50,7,'CARMINA VALLEJO','carmina25','Super Admin','Created new user: CARMINA VALLEJO (carriz12511w1@gmail.com) - Role: Admin','::1','2026-07-17 06:41:22',NULL),(51,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 08:34:21',NULL),(52,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-16_15-53-31.sql','::1','2026-07-17 08:54:47',NULL),(53,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-16_15-53-39.sql','::1','2026-07-17 08:54:50',NULL),(54,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-16_15-53-58.sql','::1','2026-07-17 08:54:55',NULL),(55,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:08',NULL),(56,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:11',NULL),(57,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:13',NULL),(58,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:15',NULL),(59,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:17',NULL),(60,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:20',NULL),(61,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:22',NULL),(62,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:28',NULL),(63,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 09:02:47',NULL),(64,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:09:11',NULL),(65,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:09:17',NULL),(66,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:09:20',NULL),(67,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:09:22',NULL),(68,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:09:25',NULL),(69,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked)','::1','2026-07-17 09:09:29',NULL),(70,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked)','::1','2026-07-17 09:09:36',NULL),(71,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked)','::1','2026-07-17 09:09:41',NULL),(72,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked)','::1','2026-07-17 09:09:43',NULL),(73,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked)','::1','2026-07-17 09:10:14',NULL),(74,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-17 09:10:29',NULL),(75,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 09:10:44',NULL),(76,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:12:31',NULL),(77,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:12:36',NULL),(78,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:12:39',NULL),(79,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:12:41',NULL),(80,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:12:48',NULL),(81,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (account locked)','::1','2026-07-17 09:12:52',NULL),(82,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 09:13:08',NULL),(83,9,'CARMINA VALLEJO','carmina251911','Viewer','','::1','2026-07-17 10:03:40',NULL),(84,9,'CARMINA VALLEJO','carmina251911','Viewer','','::1','2026-07-17 10:03:42',NULL),(85,9,'CARMINA VALLEJO','carmina251911','Viewer','','::1','2026-07-17 10:03:44',NULL),(86,9,'CARMINA VALLEJO','carmina251911','Viewer','','::1','2026-07-17 10:03:48',NULL),(87,9,'CARMINA VALLEJO','carmina251911','Viewer','','::1','2026-07-17 10:03:50',NULL),(88,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (account locked)','::1','2026-07-17 10:03:53',NULL),(89,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 10:05:26',NULL),(90,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:06:25',NULL),(91,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:06:28',NULL),(92,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:06:30',NULL),(93,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:08:22',NULL),(94,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:08:25',NULL),(95,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:08:28',NULL),(96,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:09:43',NULL),(97,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:09:46',NULL),(98,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:09:48',NULL),(99,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:10:52',NULL),(100,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:10:54',NULL),(101,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:10:57',NULL),(102,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked)','::1','2026-07-17 10:11:10',NULL),(103,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:12:02',NULL),(104,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:13:10',NULL),(105,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:13:12',NULL),(106,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:14:14',NULL),(107,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:14:19',NULL),(108,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:14:22',NULL),(109,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:15:40',NULL),(110,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:15:53',NULL),(111,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:15:55',NULL),(112,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:17:37',NULL),(113,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:17:40',NULL),(114,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:17:44',NULL),(115,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:19:06',NULL),(116,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:19:08',NULL),(117,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:19:10',NULL),(118,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:28:54',NULL),(119,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:28:57',NULL),(120,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:28:59',NULL),(121,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:30:03',NULL),(122,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:30:14',NULL),(123,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:34:27',NULL),(124,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 10:35:00',NULL),(125,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:35:32',NULL),(126,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:35:37',NULL),(127,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:35:40',NULL),(128,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:38:00',NULL),(129,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:38:04',NULL),(130,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account permanently locked)','::1','2026-07-17 10:38:07',NULL),(131,7,'CARMINA VALLEJO','carmina25','Super Admin','Activated user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 10:41:45',NULL),(132,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 10:41:53',NULL),(133,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account permanently locked)','::1','2026-07-17 10:42:13',NULL),(134,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz12511w1@gmail.com)','::1','2026-07-17 10:42:28',NULL),(135,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Failed login attempt (incorrect password)','::1','2026-07-17 10:42:40',NULL),(136,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Logged in successfully','::1','2026-07-17 10:42:46',NULL),(137,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account permanently locked)','::1','2026-07-17 10:46:40',NULL),(138,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 10:46:54',NULL),(139,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Logged in successfully','::1','2026-07-17 10:47:50',NULL),(140,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Failed login attempt (account disabled)','::1','2026-07-17 10:51:00',NULL),(141,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Failed login attempt (account disabled)','::1','2026-07-17 10:54:20',NULL),(142,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Failed login attempt (account disabled)','::1','2026-07-17 10:54:54',NULL),(143,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 10:55:30',NULL),(144,7,'CARMINA VALLEJO','carmina25','Super Admin','Activated user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 10:55:41',NULL),(145,7,'CARMINA VALLEJO','carmina25','Super Admin','Activated user account: CARMINA VALLEJO (carriz12511w1@gmail.com)','::1','2026-07-17 10:55:52',NULL),(146,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Logged in successfully','::1','2026-07-17 10:56:00',NULL),(147,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 10:56:14',NULL),(148,7,'CARMINA VALLEJO','carmina25','Super Admin','Unlocked account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 10:56:37',NULL),(149,10,'CARMINA VALLEJO','carmina2519111','Viewer','Logged in successfully','::1','2026-07-17 10:56:51',NULL),(150,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 10:57:17',NULL),(151,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 11:46:44',NULL),(152,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 11:48:35',NULL),(153,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 11:53:39',NULL),(154,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 11:54:06',NULL),(155,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted Database Backup','::1','2026-07-17 12:01:03',NULL),(156,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted Database Backup','::1','2026-07-17 12:01:09',NULL),(157,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted Database Backup','::1','2026-07-17 12:01:16',NULL),(158,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 12:33:29',NULL),(159,7,'CARMINA VALLEJO','carmina25','Super Admin','Restored database from backup: backup_2026-07-17_14-41-19.sql','::1','2026-07-17 12:42:18',NULL),(160,7,'CARMINA VALLEJO','carmina25','Super Admin','Restored database from backup: backup_2026-07-17_14-42-29.sql','::1','2026-07-17 12:42:42',NULL),(161,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 12:42:47',NULL),(162,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 12:42:52',NULL),(163,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted Database Backup','::1','2026-07-17 12:43:46',NULL),(164,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted Database Backup','::1','2026-07-17 12:45:35',NULL),(165,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 12:46:15',NULL),(166,7,'CARMINA VALLEJO','carmina25','Super Admin','Restored database from backup: backup_2026-07-17_14-46-22.sql','::1','2026-07-17 12:48:02',NULL),(167,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 12:48:40',NULL),(168,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 12:48:43',NULL),(169,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account permanently locked)','::1','2026-07-17 12:48:45',NULL),(170,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz12511w1@gmail.com)','::1','2026-07-17 12:48:58',NULL),(171,7,'CARMINA VALLEJO','carmina25','Super Admin','Reset password for carmina2519111','::1','2026-07-17 12:50:41',NULL),(172,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 13:05:08',NULL),(173,7,'CARMINA VALLEJO','carmina25','Super Admin','Updated user: CARMINA VALLEJO (carriz12511w1@gmail.com)','::1','2026-07-17 13:10:51',NULL),(174,7,'CARMINA VALLEJO','carmina25','Super Admin','Activated user account: CARMINA VALLEJO (carriz12511w1@gmail.com)','::1','2026-07-17 13:34:33',NULL),(175,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz12511w1@gmail.com)','::1','2026-07-17 13:34:36',NULL),(176,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 13:47:57',NULL),(177,7,'CARMINA VALLEJO','carmina25','Super Admin','Restored database from backup: backup_2026-07-17_15-49-00.sql','::1','2026-07-18 15:13:13',NULL),(178,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-19 00:43:09',NULL),(179,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (incorrect password)','::1','2026-07-19 01:01:56',NULL),(180,9,'CARMINA VALLEJO','carmina251911','Viewer','Logged in successfully','::1','2026-07-19 01:02:01',NULL),(181,8,'CARMINA VALLEJO','carmina2519','Admin','Failed login attempt (incorrect password)','::1','2026-07-19 01:03:10',NULL),(182,8,'CARMINA VALLEJO','carmina2519','Admin','Logged in successfully','::1','2026-07-19 01:03:15',NULL),(183,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-19 01:03:31',NULL),(184,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-19 01:03:37',NULL),(185,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-19 01:03:43',NULL),(186,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-19 02:08:07',NULL),(187,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-19 04:47:50',NULL),(188,7,'CARMINA VALLEJO','carmina25','Super Admin','Restored database from backup: backup_2026-07-19_07-05-15.sql','::1','2026-07-21 00:56:23',NULL),(189,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted Database Backup','::1','2026-07-21 00:56:28',NULL),(190,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted Database Backup','::1','2026-07-21 00:56:45',NULL),(191,7,'CARMINA VALLEJO','carmina25','Super Admin','Soft-deleted user: carmina251911 (ID: 9)','::1','2026-07-21 01:11:28',NULL),(192,7,'CARMINA VALLEJO','carmina25','Super Admin','Soft-deleted user: carmina.vallejo (ID: 11)','::1','2026-07-21 01:13:32',NULL),(193,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-17_14-46-15.sql','::1','2026-07-21 01:23:02',NULL),(194,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-17_15-05-08.sql','::1','2026-07-21 01:23:05',NULL),(195,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-17_15-47-57.sql','::1','2026-07-21 01:23:09',NULL),(196,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 01:23:12',NULL),(197,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 01:28:35',NULL),(198,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 01:28:41',NULL),(199,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-21_03-23-11.sql','::1','2026-07-21 01:28:46',NULL),(200,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-21_03-28-35.sql','::1','2026-07-21 01:28:49',NULL),(201,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 01:32:47',NULL),(202,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-21_03-28-41.sql','::1','2026-07-21 01:33:58',NULL),(203,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-21_03-32-46.sql','::1','2026-07-21 01:34:00',NULL),(204,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 01:34:04',NULL),(205,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 01:44:43',NULL),(206,7,'CARMINA VALLEJO','carmina25','Super Admin','Restored database using uploaded backup: backup_2026-07-21_03-51-08.sql','::1','2026-07-21 01:52:04',NULL),(207,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 01:54:27',NULL),(208,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 01:55:44',NULL),(209,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-21_03-54-26.sql','::1','2026-07-21 01:55:51',NULL),(210,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-21_03-44-42.sql','::1','2026-07-21 01:55:54',NULL),(211,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-21_03-34-03.sql','::1','2026-07-21 01:55:57',NULL),(212,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz1251@gmail.com)','::1','2026-07-21 01:57:23',NULL),(213,7,'CARMINA VALLEJO','carmina25','Super Admin','Created new user: CRIZTIEL VALLEJO (criz123@gmail.com) - Role: Admin','::1','2026-07-21 02:02:43',NULL),(214,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-21 02:03:12',NULL),(215,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 02:08:41',NULL),(216,7,'CARMINA VALLEJO','carmina25','Super Admin','Reset password for criztiel.vallejo','::1','2026-07-21 02:10:00',NULL),(217,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-21 02:10:14',NULL),(218,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Failed login attempt (incorrect password)','::1','2026-07-21 05:22:50',NULL),(219,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-21 05:22:57',NULL),(220,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-21 05:23:09',NULL),(221,7,'CARMINA VALLEJO','carmina25','Super Admin','Restored database using uploaded backup: backup_2026-07-21_13-26-12.sql','::1','2026-07-21 05:29:21',NULL),(222,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-21 05:32:55',NULL),(223,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-21 05:33:13',NULL),(224,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-21 05:33:43',NULL),(225,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-21 05:34:49',NULL),(226,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-21 05:38:27',NULL),(227,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Failed login attempt (incorrect password)','::1','2026-07-21 10:50:15',NULL),(228,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-21 10:50:19',NULL),(229,7,'CARMINA VALLEJO','carmina25','Super Admin','Created new user: riz VALLEJO (carriz12522@gmail.com) - Role: Admin','::1','2026-07-21 12:47:37',NULL),(230,7,'CARMINA VALLEJO','carmina25','Super Admin','Soft-deleted user: riz.vallejo (ID: 13)','::1','2026-07-21 13:42:49',NULL),(231,7,'CARMINA VALLEJO','carmina25','Super Admin','Soft-deleted user: carmina2519 (ID: 8)','::1','2026-07-21 14:09:31',NULL),(232,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 14:30:23',NULL),(233,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 15:20:26',NULL),(234,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-21 15:58:50',NULL),(235,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-21 15:58:56',NULL),(236,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-21 15:59:27',NULL),(237,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-21 15:59:52',NULL),(238,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-21_09-55-43.sql','::1','2026-07-22 00:20:17',NULL),(239,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-21_10-08-41.sql','::1','2026-07-22 00:20:20',NULL),(240,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-22 00:21:04',NULL),(241,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Processed sale for product ID 1, quantity 6.','::1','2026-07-24 12:05:27',NULL),(242,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-25 23:56:54',NULL),(243,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-26 00:00:09',NULL),(244,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-28 09:45:23',NULL),(245,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-28 09:46:25',NULL),(246,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-28 09:47:46',NULL),(247,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-28 10:08:45',NULL),(248,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Updated product: qew (ID: 4)','::1','2026-07-28 13:55:24',NULL),(249,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted product: qew (ID: 4)','::1','2026-07-28 13:55:31',NULL),(250,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted product: asada (ID: 3)','::1','2026-07-28 13:55:36',NULL),(251,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted product: pe shirt (ID: 1)','::1','2026-07-28 13:55:38',NULL),(252,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted product: pe shirt (ID: 2)','::1','2026-07-28 13:55:41',NULL),(253,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Failed login attempt (incorrect password)','::1','2026-07-28 14:12:51',NULL),(254,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-28 14:12:56',NULL),(255,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-28 14:39:16',NULL),(256,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted product: PE Shirt (ID: 5)','::1','2026-07-28 16:25:08',NULL),(257,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted product: PE Shirt (ID: 6)','::1','2026-07-28 16:31:15',NULL),(258,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted product: 3 (ID: 7)','::1','2026-07-28 16:32:15',NULL),(259,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted product: PE Shirt (ID: 9)','::1','2026-07-28 17:07:11',NULL),(260,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted product: PE Shirt (ID: 10)','::1','2026-07-28 17:27:09',NULL),(261,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted product: PE Shirt (ID: 8)','::1','2026-07-28 17:27:15',NULL),(262,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-29 00:43:25',NULL),(263,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-29 10:38:15',NULL),(264,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-29 10:42:08',NULL),(265,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Adjusted stock of \'Set Uniform for Women\' (ID: 34) from 20 to 10. Approved by carmina25. Reason: miss count','::1','2026-07-29 11:35:47','Product Management'),(266,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Adjusted stock of \'Set Uniform for Women\' (ID: 33) from 20 to 10. Approved by carmina25. Reason: miss count','::1','2026-07-29 11:37:26','Product Management'),(267,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged out','::1','2026-07-30 10:23:07',NULL),(268,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-30 10:23:32',NULL),(269,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-30 10:23:35',NULL),(270,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-30 10:24:05',NULL),(271,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Received Delivery #DEL-20260730-6355 from supplier ID 1.','::1','2026-07-30 13:06:55',NULL),(272,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Received Delivery #DEL-20260730-9377 from supplier ID 1.','::1','2026-07-30 14:20:37',NULL),(273,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-30 14:35:46',NULL),(274,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-30 14:52:26',NULL),(275,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Received Delivery #DEL-20260730-5614 from supplier ID 1.','::1','2026-07-30 14:54:17',NULL),(276,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Received Delivery #DEL-20260730-9076 from supplier ID 1.','::1','2026-07-30 15:24:02',NULL),(277,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-30 15:36:03',NULL),(278,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted product: Old Newform (ID: 39)','::1','2026-07-30 15:39:06',NULL),(279,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Completed Sale #INV-20260731-1509. Total: P450.00','::1','2026-07-30 16:36:41',NULL),(280,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Viewed Delivery #DEL-20260730-9076','::1','2026-07-30 16:37:22',NULL),(281,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Printed Delivery #DEL-20260730-9076','::1','2026-07-30 16:37:29',NULL),(282,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Viewed Delivery #DEL-20260730-9076','::1','2026-07-30 16:37:30',NULL),(283,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Received Delivery #DEL-20260731-1692 from ABC Trading','::1','2026-07-30 16:41:38',NULL),(284,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Created a new inventory backup: inventory_backup_2026-07-31_00-43-56.sql','::1','2026-07-30 16:43:56',NULL),(285,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Added new supplier: ABC COMPANY','::1','2026-07-30 17:02:32',NULL),(286,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Updated supplier details for: ABC COMPANY','::1','2026-07-30 17:03:09',NULL),(287,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Updated supplier details for: ABC COMPANY','::1','2026-07-30 17:03:55',NULL),(288,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Updated supplier details for: ABC COMPANY','::1','2026-07-30 17:05:12',NULL),(289,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Processed return for Neck Tie (1 pcs). Status: Damaged.','::1','2026-07-30 17:23:29',NULL),(290,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Updated supplier details for: Uniform Depot','::1','2026-07-30 17:25:24',NULL),(291,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged return for Old Newform (1 pcs) - Status: Damaged.','::1','2026-07-30 17:51:32',NULL),(292,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged return for Old Newform (1 pcs) - Status: Good.','::1','2026-07-30 17:53:48',NULL),(293,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Created a new inventory backup: inventory_backup_2026-07-31_09-46-33.sql','::1','2026-07-31 01:46:33',NULL),(294,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged return for Old Newform (1 pcs) - Status: Good.','::1','2026-07-31 01:51:46',NULL),(295,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Created a new inventory backup: inventory_backup_2026-07-31_10-09-42.sql','::1','2026-07-31 02:09:42',NULL),(296,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted Backup File: inventory_backup_2026-07-28_20-47-50.sql. Reason: free space','::1','2026-07-31 02:10:24',NULL),(297,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Created Inventory Backup: inventory_backup_2026-07-31_10-19-21.sql','::1','2026-07-31 02:19:22',NULL),(298,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Restored inventory from backup file: inventory_backup_2026-07-31_10-09-42 (1).sql','::1','2026-07-31 02:19:53',NULL),(299,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted Backup File: inventory_backup_2026-07-28_22-02-35.sql. Reason: free space','::1','2026-07-31 02:20:24',NULL),(300,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Created Inventory Backup: inventory_backup_2026-07-31_10-20-32.sql','::1','2026-07-31 02:20:35',NULL),(301,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Restored inventory from backup file: inventory_backup_2026-07-31_10-19-21.sql','::1','2026-07-31 02:21:30',NULL),(302,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted Backup File: inventory_backup_2026-07-30_18-16-05.sql. Reason: free space','::1','2026-07-31 02:23:18',NULL),(303,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted Backup File: inventory_backup_2026-07-30_18-35-28.sql. Reason: free space','::1','2026-07-31 02:23:30',NULL),(304,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted Backup File: inventory_backup_2026-07-30_18-48-55.sql. Reason: free space','::1','2026-07-31 02:23:45',NULL),(305,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted Backup File: inventory_backup_2026-07-30_22-23-51.sql. Reason: free space','::1','2026-07-31 02:24:02',NULL),(306,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted Backup File: inventory_backup_2026-07-31_00-43-56.sql. Reason: free space','::1','2026-07-31 02:24:14',NULL),(307,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted Backup File: inventory_backup_2026-07-31_09-46-33.sql. Reason: free space','::1','2026-07-31 02:24:25',NULL),(308,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted Backup File: inventory_backup_2026-07-31_10-09-42.sql. Reason: free space','::1','2026-07-31 02:24:36',NULL),(309,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted Backup File: inventory_backup_2026-07-31_10-19-21.sql. Reason: free space','::1','2026-07-31 02:24:48',NULL),(310,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-21_22-30-23.sql','::1','2026-07-31 02:28:29',NULL),(311,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-21_23-20-25.sql','::1','2026-07-31 02:28:33',NULL),(312,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-28_17-46-24.sql','::1','2026-07-31 02:28:37',NULL),(313,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-31 02:28:41',NULL),(314,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-28_18-08-44.sql','::1','2026-07-31 02:28:47',NULL),(315,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-31 02:31:44',NULL),(316,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Created Inventory Backup: inventory_backup_2026-07-31_11-18-19.sql','::1','2026-07-31 03:18:20',NULL),(317,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Created Inventory Backup: inventory_backup_2026-07-31_12-57-47.sql','::1','2026-07-31 04:57:47',NULL),(318,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Completed Sale #INV-20260731-1543. Total: P100.00','::1','2026-07-31 05:05:13',NULL),(319,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Completed Sale #INV-20260731-5600. Total: P100.00','::1','2026-07-31 05:05:59',NULL),(320,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Completed Sale #INV-20260731-3046. Total: P4,900.00','::1','2026-07-31 05:06:43',NULL),(321,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Received Delivery #DEL-20260731-7797 from ABC COMPANY','::1','2026-07-31 05:10:39',NULL),(322,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Created Inventory Backup: inventory_backup_2026-07-31_13-25-15.sql','::1','2026-07-31 05:25:16',NULL),(323,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-08-01 13:01:30',NULL),(324,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Created Inventory Backup: inventory_backup_2026-08-01_21-42-38.sql','::1','2026-08-01 13:42:39',NULL),(325,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Created Inventory Backup: inventory_backup_2026-08-02_00-06-00.sql','::1','2026-08-01 16:06:00',NULL),(326,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged out','::1','2026-08-01 16:21:34',NULL),(327,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-08-01 16:21:41',NULL),(328,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Received Delivery #DEL-20260802-3749 from ABC COMPANY','::1','2026-08-01 16:28:40',NULL),(329,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Created Inventory Backup: inventory_backup_2026-08-02_01-06-18.sql','::1','2026-08-01 17:06:19',NULL),(330,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Restored inventory from backup file: inventory_backup_2026-08-02_00-06-00.sql','::1','2026-08-01 17:19:43',NULL),(331,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Restored inventory from backup file: inventory_backup_2026-08-01_21-42-38.sql','::1','2026-08-01 17:20:20',NULL),(332,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged out','::1','2026-08-01 17:20:36',NULL),(333,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-08-01 17:20:44',NULL),(334,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Created Inventory Backup: inventory_backup_2026-08-02_01-22-51.sql','::1','2026-08-01 17:22:51',NULL),(335,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Restored inventory from backup file: inventory_backup_2026-08-02_01-06-18.sql','::1','2026-08-01 17:23:07',NULL),(336,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged out','::1','2026-08-01 17:43:26',NULL),(337,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-08-01 17:43:54',NULL),(338,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-08-02 02:48:11',NULL),(339,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Received Delivery #DEL-20260802-6306','::1','2026-08-02 03:09:20',NULL),(340,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Received Delivery #DEL-20260802-6400 from ABC COMPANY','::1','2026-08-02 03:15:15',NULL),(341,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Received Delivery #DEL-20260802-9998 from ABC Trading','::1','2026-08-02 03:16:22',NULL),(342,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Received Delivery #DEL-20260802-8920 from ABC COMPANY','::1','2026-08-02 03:17:39',NULL),(343,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Received Delivery #DEL-20260802-5616 from ABC COMPANY','::1','2026-08-02 03:42:55',NULL),(344,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Viewed Delivery #DEL-20260802-5616','::1','2026-08-02 03:43:08',NULL),(345,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Printed Delivery #DEL-20260802-9998','::1','2026-08-02 03:43:20',NULL),(346,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Viewed Delivery #DEL-20260802-9998','::1','2026-08-02 03:43:26',NULL),(347,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Printed Delivery #DEL-20260802-5616','::1','2026-08-02 03:57:52',NULL),(348,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Viewed Delivery #DEL-20260802-5616','::1','2026-08-02 03:58:01',NULL),(349,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Viewed Delivery #DEL-20260802-9998','::1','2026-08-02 03:58:07',NULL),(350,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Created Inventory Backup: inventory_backup_2026-08-02_12-25-22.sql','::1','2026-08-02 04:25:22',NULL),(351,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-08-02 04:28:00',NULL),(352,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-08-02 08:13:52',NULL),(353,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-08-02 08:13:58',NULL),(354,7,'CARMINA VALLEJO','carmina25','Super Admin','Created new user: Riz monte (rizmonte@gmail.com) - Role: Admin','::1','2026-08-02 08:15:30',NULL),(355,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-08-02 08:16:00',NULL),(356,7,'CARMINA VALLEJO','carmina25','Super Admin','Updated user: Riz monte (rizmonte@gmail.com)','::1','2026-08-02 08:25:11',NULL),(357,7,'CARMINA VALLEJO','carmina25','Super Admin','Updated user: Riz monte (rizmonte@gmail.com)','::1','2026-08-02 08:25:19',NULL),(358,7,'CARMINA VALLEJO','carmina25','Super Admin','Updated user: Riz monte (rizmonte@gmail.com)','::1','2026-08-02 08:25:30',NULL),(359,7,'CARMINA VALLEJO','carmina25','Super Admin','Reset password for riz.monte','::1','2026-08-02 08:26:07',NULL),(360,14,'Riz monte','riz.monte','Admin','Logged in successfully','::1','2026-08-02 08:26:24',NULL),(361,14,'Riz monte','riz.monte','Admin','Logged out','::1','2026-08-02 08:29:09',NULL),(362,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-08-02 08:29:16',NULL),(363,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Received Delivery #DEL-20260802-3830 from ABC Trading','::1','2026-08-02 09:02:02',NULL),(364,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Completed Sale #INV-20260802-7249. Total: P750.00','::1','2026-08-02 09:12:21',NULL),(365,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Created Inventory Backup: inventory_backup_2026-08-02_17-59-21.sql','::1','2026-08-02 09:59:22',NULL),(366,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged out','::1','2026-08-02 09:59:25',NULL),(367,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-08-02 09:59:31',NULL),(368,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-08-02 09:59:36',NULL),(369,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-08-02 09:59:46',NULL),(370,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Recorded new supplier return slip #SRET-20260802-3857 for supplier: ABC Company','::1','2026-08-02 10:28:01',NULL),(371,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Recorded new supplier return slip #SRET-20260802-6005 for supplier: ABC Company','::1','2026-08-02 10:45:53',NULL),(372,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted supplier return record #1. Reason: wrong','::1','2026-08-02 10:46:15',NULL),(373,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Completed Sale #INV-20260802-9179. Total: P200.00','::1','2026-08-02 12:23:37',NULL),(374,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Completed Sale #INV-20260802-3436. Total: P250.00','::1','2026-08-02 12:23:50',NULL),(375,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Completed Sale #INV-20260802-7033. Total: P200.00','::1','2026-08-02 12:24:06',NULL),(376,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Completed Sale #INV-20260802-2633. Total: P200.00','::1','2026-08-02 12:24:21',NULL),(377,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Completed Sale #INV-20260802-9395. Total: P200.00','::1','2026-08-02 12:37:40',NULL),(378,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Printed Delivery #DEL-20260802-3830','::1','2026-08-02 12:38:05',NULL),(379,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Viewed Delivery #DEL-20260802-3830','::1','2026-08-02 12:38:22',NULL),(380,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Printed Delivery #DEL-20260802-3830','::1','2026-08-02 12:39:09',NULL),(381,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Printed Delivery #DEL-20260802-3830','::1','2026-08-02 13:09:27',NULL),(382,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Viewed Delivery #DEL-20260802-3830','::1','2026-08-02 13:09:38',NULL),(383,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Recorded new supplier return slip #SRET-20260802-8405 for supplier: ABC Company','::1','2026-08-02 13:11:36',NULL),(384,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Recorded new supplier return slip #SRET-20260802-8387 for supplier: ABC Trading','::1','2026-08-02 13:11:53',NULL),(385,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Recorded new supplier return slip #SRET-20260802-6250 for supplier: ABC Company','::1','2026-08-02 13:12:17',NULL),(386,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Recorded new supplier return slip #SRET-20260802-6148 for supplier: ABC Company','::1','2026-08-02 13:25:38',NULL),(387,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Recorded new supplier return slip #SRET-20260802-2747 for supplier: ABC Company','::1','2026-08-02 13:34:05',NULL),(388,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Recorded new supplier return slip #SRET-20260802-9225 for supplier: ABC Company','::1','2026-08-02 14:10:40',NULL),(389,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Recorded new supplier return slip #SRET-20260802-6270 for supplier: ABC Company','::1','2026-08-02 14:10:50',NULL),(390,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Recorded new supplier return slip #SRET-20260802-3477 for supplier: ABC','::1','2026-08-02 14:11:07',NULL),(391,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Recorded new supplier return slip #SRET-20260802-8444 for supplier: ABC Company','::1','2026-08-02 14:11:21',NULL),(392,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Recorded new supplier return slip #SRET-20260802-3912 for supplier: ISU Supplier','::1','2026-08-02 14:11:27',NULL),(393,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Created Inventory Backup: inventory_backup_2026-08-03_07-32-30.sql','::1','2026-08-02 23:32:30',NULL),(394,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged out','::1','2026-08-02 23:32:33',NULL),(395,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-08-02 23:32:44',NULL);
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_trail`
--

DROP TABLE IF EXISTS `audit_trail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_trail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `module` varchar(100) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `audit_trail_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_trail`
--

LOCK TABLES `audit_trail` WRITE;
/*!40000 ALTER TABLE `audit_trail` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_trail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_history`
--

DROP TABLE IF EXISTS `backup_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `backup_name` varchar(255) NOT NULL,
  `backup_type` enum('Database','Full') DEFAULT 'Database',
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` varchar(50) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_role` enum('Super Admin','Admin') DEFAULT NULL,
  `backup_date` datetime DEFAULT current_timestamp(),
  `status` enum('Completed','Failed') DEFAULT 'Completed',
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `backup_history_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_history`
--

LOCK TABLES `backup_history` WRITE;
/*!40000 ALTER TABLE `backup_history` DISABLE KEYS */;
INSERT INTO `backup_history` VALUES (26,'Database Backup - Jul 28, 2026 10:39 PM','Database','backup_2026-07-28_22-39-15.sql','C:\\xampp\\htdocs\\InventoryManagementSystem\\process\\superadmin/../../backups/backup_2026-07-28_22-39-15.sql','50.36 KB',7,'Super Admin','2026-07-28 22:39:16','Completed','Backup created successfully'),(27,'Database Backup - Jul 31, 2026 10:28 AM','Database','backup_2026-07-31_10-28-40.sql','C:\\xampp\\htdocs\\InventoryManagementSystem\\process\\superadmin/../../backups/backup_2026-07-31_10-28-40.sql','71.38 KB',7,'Super Admin','2026-07-31 10:28:41','Completed','Backup created successfully'),(28,'Database Backup - Jul 31, 2026 10:31 AM','Database','backup_2026-07-31_10-31-44.sql','C:\\xampp\\htdocs\\InventoryManagementSystem\\process\\superadmin/../../backups/backup_2026-07-31_10-31-44.sql','71.63 KB',7,'Super Admin','2026-07-31 10:31:44','Completed','Backup created successfully'),(29,'Database Backup - Aug 02, 2026 05:59 PM','Database','backup_2026-08-02_17-59-35.sql','C:\\xampp\\htdocs\\InventoryManagementSystem\\process\\superadmin/../../backups/backup_2026-08-02_17-59-35.sql','86.91 KB',7,'Super Admin','2026-08-02 17:59:36','Completed','Backup created successfully');
/*!40000 ALTER TABLE `backup_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deliveries`
--

DROP TABLE IF EXISTS `deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deliveries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) DEFAULT NULL,
  `delivery_no` varchar(100) DEFAULT NULL,
  `delivery_date` datetime DEFAULT NULL,
  `received_by` int(11) DEFAULT NULL,
  `status` enum('Pending','Completed') DEFAULT 'Completed',
  `remarks` text DEFAULT NULL,
  `dr_number` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `received_by` (`received_by`),
  CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `deliveries_ibfk_2` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deliveries`
--

LOCK TABLES `deliveries` WRITE;
/*!40000 ALTER TABLE `deliveries` DISABLE KEYS */;
INSERT INTO `deliveries` VALUES (2,1,'DEL-20260730-6355','2026-07-30 00:00:00',12,'Completed','','DR-26-0125'),(3,1,'DEL-20260730-9377','2026-07-30 00:00:00',12,'Completed','','DR-26-0125'),(4,1,'DEL-20260730-5614','2026-07-30 00:00:00',12,'Completed','',''),(5,1,'DEL-20260730-9076','2026-07-30 00:00:00',12,'Completed','','DR-26-0126'),(6,1,'DEL-20260731-1692','2026-07-31 00:00:00',12,'Completed','','DR-26-0127'),(7,4,'DEL-20260731-7797','2026-07-31 00:00:00',12,'Completed','',''),(8,4,'DEL-20260802-3749','2026-08-02 00:00:00',12,'Completed','logo','DR-26-0125'),(9,NULL,'DEL-20260802-6306','2026-08-02 00:00:00',12,'Completed','Received delivery','DR-26-0125'),(10,4,'DEL-20260802-6400','2026-08-02 00:00:00',12,'Completed','Received delivery','DR-26-0125'),(11,1,'DEL-20260802-9998','2026-08-02 00:00:00',12,'Completed','Received delivery','DR-26-0126'),(12,4,'DEL-20260802-8920','2026-08-02 00:00:00',12,'Completed','Received delivery','DR-26-0128'),(13,4,'DEL-20260802-5616','2026-08-02 00:00:00',12,'Completed','Received delivery','DR-26-0125'),(14,1,'DEL-20260802-3830','2026-08-02 00:00:00',12,'Completed','Received delivery','DR-26-0129');
/*!40000 ALTER TABLE `deliveries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_items`
--

DROP TABLE IF EXISTS `delivery_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `delivery_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `delivery_id` (`delivery_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `delivery_items_ibfk_1` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`),
  CONSTRAINT `delivery_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_items`
--

LOCK TABLES `delivery_items` WRITE;
/*!40000 ALTER TABLE `delivery_items` DISABLE KEYS */;
INSERT INTO `delivery_items` VALUES (1,2,12,1,11.00),(2,3,38,10,15.00),(3,4,38,1,15.00),(4,4,35,1,50.00),(5,4,36,1,15.00),(6,4,19,2,100.00),(7,4,27,1,750.00),(8,5,20,1,10.00),(9,5,28,1,800.00),(10,5,12,1,11.00),(11,5,37,1,15.00),(12,5,36,1,15.00),(13,5,38,1,15.00),(14,5,35,1,50.00),(15,6,36,1,15.00),(16,7,35,5,50.00),(17,8,38,1,15.00),(18,9,32,1,750.00),(19,10,32,1,750.00),(20,11,38,1,15.00),(21,11,41,1,500.00),(22,12,32,2,750.00),(23,12,31,1,750.00),(24,12,33,3,750.00),(25,13,32,1,750.00),(26,13,31,2,750.00),(27,13,33,1,750.00),(28,14,38,1,15.00);
/*!40000 ALTER TABLE `delivery_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_monthly_reports`
--

DROP TABLE IF EXISTS `inventory_monthly_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_monthly_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `report_month` tinyint(4) NOT NULL,
  `report_year` smallint(6) NOT NULL,
  `beginning_stock` int(11) NOT NULL,
  `inventory_in` int(11) DEFAULT 0,
  `inventory_out` int(11) DEFAULT 0,
  `ending_stock` int(11) NOT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`,`report_month`,`report_year`),
  CONSTRAINT `inventory_monthly_reports_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_monthly_reports`
--

LOCK TABLES `inventory_monthly_reports` WRITE;
/*!40000 ALTER TABLE `inventory_monthly_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_monthly_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_stock_cards`
--

DROP TABLE IF EXISTS `inventory_stock_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_stock_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `transaction_date` datetime NOT NULL,
  `transaction_type` enum('Beginning','Delivery','Sale','Return','Adjustment') NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `stock_in` int(11) DEFAULT 0,
  `stock_out` int(11) DEFAULT 0,
  `running_balance` int(11) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `inventory_stock_cards_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_stock_cards`
--

LOCK TABLES `inventory_stock_cards` WRITE;
/*!40000 ALTER TABLE `inventory_stock_cards` DISABLE KEYS */;
INSERT INTO `inventory_stock_cards` VALUES (1,35,'2026-07-31 13:05:13','Sale','INV-20260731-1543',0,1,50,'Cash Sale',12,'2026-07-31 05:05:13'),(2,35,'2026-07-31 13:05:59','Sale','INV-20260731-5600',0,1,49,'Cash Sale',12,'2026-07-31 05:05:59'),(3,35,'2026-07-31 13:06:43','Sale','INV-20260731-3046',0,49,0,'Cash Sale',12,'2026-07-31 05:06:43'),(4,35,'2026-07-31 13:10:39','Delivery','DEL-20260731-7797',5,0,5,'Supplier Delivery',12,'2026-07-31 05:10:39'),(5,38,'2026-08-02 00:28:40','Delivery','DEL-20260802-3749',1,0,63,'Supplier Delivery',12,'2026-08-01 16:28:40'),(6,32,'2026-08-02 11:09:20','Delivery','DEL-20260802-6306',1,0,21,'Supplier Delivery',12,'2026-08-02 03:09:20'),(7,32,'2026-08-02 11:15:15','Delivery','DEL-20260802-6400',1,0,22,'Supplier Delivery',12,'2026-08-02 03:15:15'),(8,38,'2026-08-02 11:16:22','Delivery','DEL-20260802-9998',1,0,64,'Supplier Delivery',12,'2026-08-02 03:16:22'),(9,41,'2026-08-02 11:16:22','Delivery','DEL-20260802-9998',1,0,2,'Supplier Delivery',12,'2026-08-02 03:16:22'),(10,32,'2026-08-02 11:17:39','Delivery','DEL-20260802-8920',2,0,24,'Supplier Delivery',12,'2026-08-02 03:17:39'),(11,31,'2026-08-02 11:17:39','Delivery','DEL-20260802-8920',1,0,21,'Supplier Delivery',12,'2026-08-02 03:17:39'),(12,33,'2026-08-02 11:17:39','Delivery','DEL-20260802-8920',3,0,13,'Supplier Delivery',12,'2026-08-02 03:17:39'),(13,32,'2026-08-02 11:42:55','Delivery','DEL-20260802-5616',1,0,25,'Supplier Delivery',12,'2026-08-02 03:42:55'),(14,31,'2026-08-02 11:42:55','Delivery','DEL-20260802-5616',2,0,23,'Supplier Delivery',12,'2026-08-02 03:42:55'),(15,33,'2026-08-02 11:42:55','Delivery','DEL-20260802-5616',1,0,14,'Supplier Delivery',12,'2026-08-02 03:42:55'),(16,38,'2026-08-02 17:02:02','Delivery','DEL-20260802-3830',1,0,65,'Supplier Delivery',12,'2026-08-02 09:02:02'),(17,40,'2026-08-02 17:12:21','Sale','INV-20260802-7249',0,1,9,'Cash Sale',12,'2026-08-02 09:12:21'),(18,22,'2026-08-02 20:23:37','Sale','INV-20260802-9179',0,1,19,'Cash Sale',12,'2026-08-02 12:23:37'),(19,13,'2026-08-02 20:23:50','Sale','INV-20260802-3436',0,1,18,'Cash Sale',12,'2026-08-02 12:23:50'),(20,25,'2026-08-02 20:24:06','Sale','INV-20260802-7033',0,1,19,'Cash Sale',12,'2026-08-02 12:24:06'),(21,24,'2026-08-02 20:24:21','Sale','INV-20260802-2633',0,1,19,'Cash Sale',12,'2026-08-02 12:24:21'),(22,23,'2026-08-02 20:37:40','Sale','INV-20260802-9395',0,1,18,'Cash Sale',12,'2026-08-02 12:37:40');
/*!40000 ALTER TABLE `inventory_stock_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_transactions`
--

DROP TABLE IF EXISTS `inventory_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `transaction_type` enum('Stock In','Stock Out','Sale','Return','Adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `previous_stock` int(11) NOT NULL,
  `current_stock` int(11) NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `transaction_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `performed_by` (`performed_by`),
  CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `inventory_transactions_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_transactions`
--

LOCK TABLES `inventory_transactions` WRITE;
/*!40000 ALTER TABLE `inventory_transactions` DISABLE KEYS */;
INSERT INTO `inventory_transactions` VALUES (1,12,'Stock In',1,11,12,'0','Received from Delivery',12,'2026-07-30 21:06:55'),(2,38,'Stock In',10,50,60,'0','Received from Delivery',12,'2026-07-30 22:20:37'),(3,38,'Stock In',1,60,61,'0','Received from Delivery',12,'2026-07-30 22:54:17'),(4,35,'Stock In',1,50,51,'0','Received from Delivery',12,'2026-07-30 22:54:17'),(5,36,'Stock In',1,50,51,'0','Received from Delivery',12,'2026-07-30 22:54:17'),(6,19,'Stock In',2,20,22,'0','Received from Delivery',12,'2026-07-30 22:54:17'),(7,27,'Stock In',1,20,21,'0','Received from Delivery',12,'2026-07-30 22:54:17'),(8,20,'Stock In',1,20,21,'0','Received from Delivery',12,'2026-07-30 23:24:02'),(9,28,'Stock In',1,20,21,'0','Received from Delivery',12,'2026-07-30 23:24:02'),(10,12,'Stock In',1,12,13,'0','Received from Delivery',12,'2026-07-30 23:24:02'),(11,37,'Stock In',1,50,51,'0','Received from Delivery',12,'2026-07-30 23:24:02'),(12,36,'Stock In',1,51,52,'0','Received from Delivery',12,'2026-07-30 23:24:02'),(13,38,'Stock In',1,61,62,'0','Received from Delivery',12,'2026-07-30 23:24:02'),(14,35,'Stock In',1,51,52,'0','Received from Delivery',12,'2026-07-30 23:24:02'),(15,36,'Stock In',1,52,53,'0','Received from Delivery',12,'2026-07-31 00:41:38'),(16,35,'Stock In',5,0,5,'0','Received from Delivery',12,'2026-07-31 13:10:39'),(17,38,'Stock In',1,62,63,'0','Received from Delivery',12,'2026-08-02 00:28:40'),(18,32,'Stock In',1,20,21,'0','Received from Delivery',12,'2026-08-02 11:09:20'),(19,32,'Stock In',1,21,22,'0','Received from Delivery',12,'2026-08-02 11:15:15'),(20,38,'Stock In',1,63,64,'0','Received from Delivery',12,'2026-08-02 11:16:22'),(21,41,'Stock In',1,1,2,'0','Received from Delivery',12,'2026-08-02 11:16:22'),(22,32,'Stock In',2,22,24,'0','Received from Delivery',12,'2026-08-02 11:17:39'),(23,31,'Stock In',1,20,21,'0','Received from Delivery',12,'2026-08-02 11:17:39'),(24,33,'Stock In',3,10,13,'0','Received from Delivery',12,'2026-08-02 11:17:39'),(25,32,'Stock In',1,24,25,'0','Received from Delivery',12,'2026-08-02 11:42:55'),(26,31,'Stock In',2,21,23,'0','Received from Delivery',12,'2026-08-02 11:42:55'),(27,33,'Stock In',1,13,14,'0','Received from Delivery',12,'2026-08-02 11:42:55'),(28,38,'Stock In',1,64,65,'0','Received from Delivery',12,'2026-08-02 17:02:02');
/*!40000 ALTER TABLE `inventory_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_yearly_reports`
--

DROP TABLE IF EXISTS `inventory_yearly_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_yearly_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_year` year(4) DEFAULT NULL,
  `total_beginning` int(11) DEFAULT NULL,
  `total_in` int(11) DEFAULT NULL,
  `total_out` int(11) DEFAULT NULL,
  `total_ending` int(11) DEFAULT NULL,
  `total_inventory_value` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_yearly_reports`
--

LOCK TABLES `inventory_yearly_reports` WRITE;
/*!40000 ALTER TABLE `inventory_yearly_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_yearly_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` enum('Low Stock','Backup','Security','System') DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_code` varchar(50) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_size` varchar(30) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `supplier` varchar(150) DEFAULT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  `current_stock` int(11) DEFAULT 0,
  `inventory_value` decimal(12,2) DEFAULT 0.00,
  `reorder_level` int(11) DEFAULT 10,
  `status` enum('Available','Low Stock','Out of Stock') DEFAULT 'Available',
  `front_image` varchar(255) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_code` (`product_code`),
  UNIQUE KEY `unique_product_size` (`product_name`,`product_size`),
  KEY `fk_product_category` (`category_id`),
  KEY `fk_product_supplier` (`supplier_id`),
  KEY `fk_product_updatedby` (`updated_by`),
  CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `fk_product_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `fk_product_updatedby` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (11,'PROD-000001','PE Shirt','PE',NULL,'XS','0','ABC Company',111.00,NULL,111.00,0.00,110,12321.00,11,'Available','assets/uploads/products/6a68e6892d3d5_front_pe shirt.png','assets/qrcodes/PROD-000001.png','2026-07-28 17:27:37','2026-08-02 13:34:05',NULL),(12,'PROD-000012','PE Shirt','PE',NULL,'Small','0','ABC Company',11.00,NULL,111.00,0.00,12,121.00,11,'Available','assets/uploads/products/6a68e6c86e6a7_front_pe shirt.png','assets/qrcodes/PROD-000012.png','2026-07-28 17:28:40','2026-08-02 13:25:38',NULL),(13,'PROD-000013','PE Shirt','PE',NULL,'Medium','0','ABC Company',150.00,NULL,250.00,0.00,18,3000.00,5,'Available','assets/uploads/products/6a694d0cee767_front_pe shirt.png','assets/qrcodes/PROD-000013.png','2026-07-29 00:45:00','2026-08-02 12:23:50',NULL),(14,'PROD-000014','PE Shirt','PE',NULL,'Large','0','ABC Company',150.00,NULL,250.00,0.00,19,3000.00,5,'Available','assets/uploads/products/6a694d38bf138_front_pe shirt.png','assets/qrcodes/PROD-000014.png','2026-07-29 00:45:44','2026-08-02 13:12:17',NULL),(15,'PROD-000015','PE Shirt','PE',NULL,'XL','0','ABC Company',150.00,NULL,250.00,0.00,20,3000.00,5,'Available','assets/uploads/products/6a694d8da6703_front_pe shirt.png','assets/qrcodes/PROD-000015.png','2026-07-29 00:47:09','2026-07-29 00:47:09',NULL),(16,'PROD-000016','PE Shirt','PE',NULL,'XXL','0','ABC Company',150.00,NULL,250.00,0.00,20,3000.00,5,'Available','assets/uploads/products/6a694da8a8f2d_front_pe shirt.png','assets/qrcodes/PROD-000016.png','2026-07-29 00:47:36','2026-07-29 00:47:36',NULL),(17,'PROD-000017','PE Shirt','PE',NULL,'XXXL','0','ABC Company',150.00,NULL,250.00,0.00,20,3000.00,5,'Available','assets/uploads/products/6a694dbdc606e_front_pe shirt.png','assets/qrcodes/PROD-000017.png','2026-07-29 00:47:57','2026-07-29 00:47:57',NULL),(18,'PROD-000018','PE Shirt','PE',NULL,'Free Size','0','ABC Company',150.00,NULL,250.00,0.00,20,3000.00,5,'Available','assets/uploads/products/6a694dcf4cf49_front_pe shirt.png','assets/qrcodes/PROD-000018.png','2026-07-29 00:48:15','2026-07-29 00:48:15',NULL),(19,'PROD-000019','PE Pants','PE',NULL,'XS','0','ABC Company',100.00,NULL,200.00,0.00,22,2000.00,5,'Available','assets/uploads/products/6a694e00ef116_front_pe_pants.png','assets/qrcodes/PROD-000019.png','2026-07-29 00:49:04','2026-07-30 14:54:17',NULL),(20,'PROD-000020','PE Pants','PE',NULL,'Small','0','ABC Company',100.00,NULL,200.00,0.00,21,2000.00,5,'Available','assets/uploads/products/6a694e0e6e3c7_front_pe_pants.png','assets/qrcodes/PROD-000020.png','2026-07-29 00:49:18','2026-07-30 15:24:02',NULL),(21,'PROD-000021','PE Pants','PE',NULL,'Medium','0','ABC Company',100.00,NULL,200.00,0.00,20,2000.00,5,'Available','assets/uploads/products/6a694e1ae1367_front_pe_pants.png','assets/qrcodes/PROD-000021.png','2026-07-29 00:49:30','2026-07-29 00:49:30',NULL),(22,'PROD-000022','PE Pants','PE',NULL,'Large','0','ABC Company',100.00,NULL,200.00,0.00,18,2000.00,5,'Available','assets/uploads/products/6a694e2aa109f_front_pe_pants.png','assets/qrcodes/PROD-000022.png','2026-07-29 00:49:46','2026-08-02 13:11:36',NULL),(23,'PROD-000023','PE Pants','PE',NULL,'XL','0','ABC Company',100.00,NULL,200.00,0.00,18,2000.00,5,'Available','assets/uploads/products/6a694e3ba0a4a_front_pe_pants.png','assets/qrcodes/PROD-000023.png','2026-07-29 00:50:03','2026-08-02 12:37:40',NULL),(24,'PROD-000024','PE Pants','PE',NULL,'XXL','0','ABC Company',100.00,NULL,200.00,0.00,18,2000.00,5,'Available','assets/uploads/products/6a694e47aaf3f_front_pe_pants.png','assets/qrcodes/PROD-000024.png','2026-07-29 00:50:15','2026-08-02 13:34:05',NULL),(25,'PROD-000025','PE Pants','PE',NULL,'XXXL','0','ABC Company',100.00,NULL,200.00,0.00,19,2000.00,5,'Available','assets/uploads/products/6a694e5223381_front_pe_pants.png','assets/qrcodes/PROD-000025.png','2026-07-29 00:50:26','2026-08-02 12:24:06',NULL),(26,'PROD-000026','PE Pants','PE',NULL,'Free Size','0','ABC Company',100.00,NULL,200.00,0.00,20,2000.00,5,'Available','assets/uploads/products/6a694e652bcce_front_pe_pants.png','assets/qrcodes/PROD-000026.png','2026-07-29 00:50:45','2026-07-29 00:50:45',NULL),(27,'PROD-000027','Set Uniform for Women','School Uniform',NULL,'XS','0','ABC Company',750.00,NULL,1200.00,0.00,21,15000.00,5,'Available','assets/uploads/products/6a694ed083d8f_front_new_compuniform.png','assets/qrcodes/PROD-000027.png','2026-07-29 00:52:32','2026-07-30 14:54:17',NULL),(28,'PROD-000028','Set Uniform for Women','School Uniform',NULL,'Small','0','ABC Company',750.00,NULL,1200.00,0.00,21,15000.00,5,'Available','assets/uploads/products/6a694fb4a24ca_front_new_compuniform.png','assets/qrcodes/PROD-000028.png','2026-07-29 00:56:20','2026-07-30 15:24:02',NULL),(29,'PROD-000029','Set Uniform for Women','School Uniform',NULL,'Medium','0','ABC Company',750.00,NULL,1200.00,0.00,20,15000.00,5,'Available','assets/uploads/products/6a694fbf49801_front_new_compuniform.png','assets/qrcodes/PROD-000029.png','2026-07-29 00:56:31','2026-07-29 00:56:31',NULL),(30,'PROD-000030','Set Uniform for Women','School Uniform',NULL,'Large','0','ABC Company',750.00,NULL,1200.00,0.00,20,15000.00,5,'Available','assets/uploads/products/6a694fdcef036_front_new_compuniform.png','assets/qrcodes/PROD-000030.png','2026-07-29 00:57:00','2026-07-29 00:57:00',NULL),(31,'PROD-000031','Set Uniform for Women','School Uniform',NULL,'XL','0','ABC Company',750.00,NULL,1200.00,0.00,22,15000.00,5,'Available','assets/uploads/products/6a694fe4ec1e7_front_new_compuniform.png','assets/qrcodes/PROD-000031.png','2026-07-29 00:57:08','2026-08-02 14:10:40',NULL),(32,'PROD-000032','Set Uniform for Women','School Uniform',NULL,'XXL','0','ABC Company',750.00,NULL,1200.00,0.00,22,15000.00,5,'Available','assets/uploads/products/6a694fef97d12_front_new_compuniform.png','assets/qrcodes/PROD-000032.png','2026-07-29 00:57:19','2026-08-02 14:11:21',NULL),(33,'PROD-000033','Set Uniform for Women','School Uniform',NULL,'XXXL','0','ABC Company',750.00,NULL,1200.00,0.00,10,15000.00,5,'Available','assets/uploads/products/6a694ffea40ef_front_new_compuniform.png','assets/qrcodes/PROD-000033.png','2026-07-29 00:57:34','2026-08-02 14:10:50',NULL),(34,'PROD-000034','Set Uniform for Women','School Uniform',NULL,'Free Size','0','ABC Company',750.00,NULL,1200.00,0.00,8,15000.00,5,'Available','assets/uploads/products/6a69500e5710e_front_new_compuniform.png','assets/qrcodes/PROD-000034.png','2026-07-29 00:57:50','2026-08-02 14:10:40',NULL),(35,'PROD-000035','ID Lace','Accessories',NULL,'Free Size','0','ABC',50.00,NULL,100.00,0.00,4,2500.00,20,'Available','assets/uploads/products/6a6b298e7041a_front_id_lace.png','assets/qrcodes/PROD-000035.png','2026-07-30 10:38:06','2026-08-02 14:11:07',NULL),(36,'PROD-000036','Neck Lace','Accessories',NULL,'Free Size','0','ABC Trading',15.00,NULL,30.00,0.00,53,750.00,20,'Available','assets/uploads/products/6a6b2a387c790_front_necklace_g.png','assets/qrcodes/PROD-000036.png','2026-07-30 10:40:56','2026-07-30 16:41:38',NULL),(37,'PROD-000037','Neck Tie','Accessories',NULL,'Free Size','0','ABC Trading',15.00,NULL,30.00,0.00,51,750.00,20,'Available','assets/uploads/products/6a6b2a6570d8e_front_necktie_g.png','assets/qrcodes/PROD-000037.png','2026-07-30 10:41:41','2026-07-30 15:24:02',NULL),(38,'PROD-000038','ISU Logo','Accessories',NULL,'Free Size','0','ABC Trading',15.00,NULL,30.00,0.00,65,750.00,20,'Available','assets/uploads/products/6a6b2aa861b28_front_logo_.png','assets/qrcodes/PROD-000038.png','2026-07-30 10:42:48','2026-08-02 09:02:02',NULL),(40,'PROD-000039','Old Newform','Uniform',NULL,'Small','0','ISU Supplier',450.00,NULL,750.00,0.00,8,4500.00,10,'Available','assets/uploads/products/6a6b70b80b204_front_com_uniform.png','assets/qrcodes/PROD-000039.png','2026-07-30 15:41:44','2026-08-02 14:11:27',NULL),(41,'PROD-000041','Old Newform','Uniform',NULL,'XS','0','ABC Trading',500.00,NULL,1000.00,0.00,1,0.00,0,'Available','assets/uploads/products/6a6b70eb0734d_front_com_uniform.png','assets/qrcodes/PROD-000041.png','2026-07-30 15:42:35','2026-08-02 13:11:53',NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `returns`
--

DROP TABLE IF EXISTS `returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `returns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `return_no` varchar(30) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `supplier` varchar(150) NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `reason` enum('Supplier Delivered Wrong Item','Supplier Delivered Damaged Item','Factory Defect','Quality Defect','Incorrect Size','Incorrect Color','Excess Delivered Quantity','Other Supplier Issue') NOT NULL,
  `status` enum('Pending','Approved','Returned','Rejected') NOT NULL DEFAULT 'Pending',
  `image_path` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `processed_by` int(11) NOT NULL,
  `return_date` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_return_no` (`return_no`),
  KEY `idx_product` (`product_id`),
  KEY `idx_supplier` (`supplier`),
  KEY `idx_status` (`status`),
  KEY `idx_date` (`return_date`),
  KEY `fk_return_sale` (`sale_id`),
  KEY `fk_return_user` (`processed_by`),
  CONSTRAINT `fk_return_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_return_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_return_user` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `returns`
--

LOCK TABLES `returns` WRITE;
/*!40000 ALTER TABLE `returns` DISABLE KEYS */;
INSERT INTO `returns` VALUES (2,'SRET-20260802-6005',NULL,33,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',12,'2026-08-02 00:00:00','2026-08-02 10:45:53','2026-08-02 10:45:53'),(3,'SRET-20260802-8405',NULL,22,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',12,'2026-08-02 00:00:00','2026-08-02 13:11:36','2026-08-02 13:11:36'),(4,'SRET-20260802-8387',NULL,41,'ABC Trading','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',12,'2026-08-02 00:00:00','2026-08-02 13:11:53','2026-08-02 13:11:53'),(5,'SRET-20260802-6250',NULL,14,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',12,'2026-08-02 00:00:00','2026-08-02 13:12:17','2026-08-02 13:12:17'),(10,'SRET-20260802-6148',NULL,12,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',12,'2026-08-02 00:00:00','2026-08-02 13:25:38','2026-08-02 13:25:38'),(13,'SRET-20260802-2747',NULL,11,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',12,'2026-08-02 00:00:00','2026-08-02 13:34:05','2026-08-02 13:34:05'),(14,'SRET-20260802-2747',NULL,24,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',12,'2026-08-02 00:00:00','2026-08-02 13:34:05','2026-08-02 13:34:05'),(15,'SRET-20260802-9225',NULL,34,'ABC Company','',2,'Supplier Delivered Wrong Item','Returned',NULL,'',12,'2026-08-02 22:10:00','2026-08-02 14:10:40','2026-08-02 14:10:40'),(16,'SRET-20260802-9225',NULL,33,'ABC Company','',2,'Supplier Delivered Wrong Item','Returned',NULL,'',12,'2026-08-02 22:10:00','2026-08-02 14:10:40','2026-08-02 14:10:40'),(17,'SRET-20260802-9225',NULL,32,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',12,'2026-08-02 22:10:00','2026-08-02 14:10:40','2026-08-02 14:10:40'),(18,'SRET-20260802-9225',NULL,31,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',12,'2026-08-02 22:10:00','2026-08-02 14:10:40','2026-08-02 14:10:40'),(19,'SRET-20260802-6270',NULL,33,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',12,'2026-08-02 22:10:00','2026-08-02 14:10:50','2026-08-02 14:10:50'),(20,'SRET-20260802-3477',NULL,35,'ABC','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',12,'2026-08-02 22:10:00','2026-08-02 14:11:07','2026-08-02 14:11:07'),(21,'SRET-20260802-8444',NULL,32,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',12,'2026-08-02 22:11:00','2026-08-02 14:11:21','2026-08-02 14:11:21'),(22,'SRET-20260802-3912',NULL,40,'ISU Supplier','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',12,'2026-08-02 22:11:00','2026-08-02 14:11:27','2026-08-02 14:11:27');
/*!40000 ALTER TABLE `returns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_items`
--

DROP TABLE IF EXISTS `sale_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
  CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_items`
--

LOCK TABLES `sale_items` WRITE;
/*!40000 ALTER TABLE `sale_items` DISABLE KEYS */;
INSERT INTO `sale_items` VALUES (1,1,35,1,100.00,100.00),(2,2,23,1,200.00,200.00),(3,2,13,1,250.00,250.00),(4,3,35,1,100.00,100.00),(5,4,35,1,100.00,100.00),(6,5,35,49,100.00,4900.00),(7,6,40,1,750.00,750.00),(8,7,22,1,200.00,200.00),(9,8,13,1,250.00,250.00),(10,9,25,1,200.00,200.00),(11,10,24,1,200.00,200.00),(12,11,23,1,200.00,200.00);
/*!40000 ALTER TABLE `sale_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(100) DEFAULT NULL,
  `customer_name` varchar(150) DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT NULL,
  `discount` decimal(12,2) DEFAULT NULL,
  `total` decimal(12,2) DEFAULT NULL,
  `payment_method` enum('Cash','GCash') DEFAULT 'Cash',
  `created_by` int(11) DEFAULT NULL,
  `sale_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_no` (`invoice_no`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
INSERT INTO `sales` VALUES (1,'INV-20260731-5442','jin',100.00,0.00,100.00,'Cash',12,'2026-07-31 00:11:26'),(2,'INV-20260731-1509','',450.00,0.00,450.00,'Cash',12,'2026-07-31 00:36:41'),(3,'INV-20260731-1543','lala',100.00,0.00,100.00,'Cash',12,'2026-07-31 13:05:13'),(4,'INV-20260731-5600','kikm',100.00,0.00,100.00,'Cash',12,'2026-07-31 13:05:59'),(5,'INV-20260731-3046','jane',4900.00,0.00,4900.00,'Cash',12,'2026-07-31 13:06:43'),(6,'INV-20260802-7249','jane',750.00,0.00,750.00,'Cash',12,'2026-08-02 17:12:21'),(7,'INV-20260802-9179','jane',200.00,0.00,200.00,'Cash',12,'2026-08-02 20:23:37'),(8,'INV-20260802-3436','lala',250.00,0.00,250.00,'Cash',12,'2026-08-02 20:23:50'),(9,'INV-20260802-7033','jin',200.00,0.00,200.00,'Cash',12,'2026-08-02 20:24:06'),(10,'INV-20260802-2633','jin',200.00,0.00,200.00,'Cash',12,'2026-08-02 20:24:21'),(11,'INV-20260802-9395','lala',200.00,0.00,200.00,'Cash',12,'2026-08-02 20:37:40');
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_adjustments`
--

DROP TABLE IF EXISTS `stock_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_adjustments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `old_stock` int(11) NOT NULL,
  `new_stock` int(11) NOT NULL,
  `reason` text NOT NULL,
  `requested_by` int(11) NOT NULL,
  `approved_by` int(11) NOT NULL,
  `approved_at` timestamp NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Approved',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_adjustments`
--

LOCK TABLES `stock_adjustments` WRITE;
/*!40000 ALTER TABLE `stock_adjustments` DISABLE KEYS */;
INSERT INTO `stock_adjustments` VALUES (3,34,20,10,'miss count',12,7,'2026-07-29 11:35:47','Approved'),(4,33,20,10,'miss count',12,7,'2026-07-29 11:37:26','Approved');
/*!40000 ALTER TABLE `stock_adjustments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(150) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'ABC Trading','Juan Dela Cruz','09123456789','abc@gmail.com','Cauayan City','Active','2026-07-30 10:30:51'),(2,'ISU Supplier','Pedro Santos','09987654321','isu@gmail.com','Santiago City','Active','2026-07-30 10:30:51'),(3,'Uniform Depot','Maria Cruz','09111222333','uniform@gmail.com','Isabela','Inactive','2026-07-30 10:30:51'),(4,'ABC COMPANY','MAY CRUZ','09999999999','carriz12511@gmail.com','SAN FERMIN DACANAY','Active','2026-07-30 17:02:32');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(100) DEFAULT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_name` (`setting_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Super Admin','Admin','Viewer') NOT NULL,
  `recovery_password` varchar(255) DEFAULT NULL,
  `recovery_code` varchar(255) DEFAULT NULL,
  `failed_attempts` int(11) NOT NULL DEFAULT 0,
  `lock_until` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('Active','Inactive','Deleted') NOT NULL DEFAULT 'Active',
  `is_permanently_locked` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (7,'CARMINA','VALLEJO','carriz125@gmail.com','','carmina25','$2y$10$rug2ew9Vm0iqEM/JPNHoGemwiF.F8lHFi08wH1rcV1eCRpENS5Fja','Super Admin','$2y$10$VPTZRyRUTQScEXmsvluorOFkQqEPPEGswLTAF44il4dVtvPWF0X.S','DQ-VV-EM',0,NULL,'2026-07-15 16:28:56','2026-08-02 08:13:58','Active',0),(8,'Deleted','User','deleted_8@deleted.local',NULL,'deleted_8','$2y$10$aHJdc4dAG3ibW8658/DqN.EHeHkKowe.tBvv3T8wMZVeL5XMry78C','Admin',NULL,NULL,0,NULL,'2026-07-15 16:31:48','2026-07-21 14:09:31','Deleted',0),(9,'Deleted','User','deleted_9@deleted.local',NULL,'deleted_9','$2y$10$/eIg/YHtvdSYdiKH.XO1LOUpuQaXjBFgBsM20Nk2jj.NCW2AZSVj6','Viewer',NULL,NULL,0,NULL,'2026-07-15 16:32:14','2026-07-21 01:11:28','Deleted',0),(10,'CARMINA','VALLEJO','carriz125111@gmail.com','','carmina2519111','$2y$10$dbN1DzSdmygsK.lcFrm5/ezswSbYTJnF7cAXcAIaMrPDVockwcfMm','Viewer',NULL,NULL,3,NULL,'2026-07-15 16:38:26','2026-07-17 12:50:41','Active',1),(11,'Deleted','User','deleted_11@deleted.local',NULL,'deleted_11','$2y$10$L7326CbonX4j3HhxM.xlbO8PeZUYkgtiOzTD4H7alibY0SeWIXNIq','Admin',NULL,NULL,0,NULL,'2026-07-17 06:41:22','2026-07-21 01:13:32','Deleted',0),(12,'CRIZTIEL','VALLEJO','criz123@gmail.com','09061111111','criztiel.vallejo','$2y$10$6I6aG5ANNKJopYGQQOA3L.Vy5rWxIYuEyYHkm9YqNBr8.X7WPZSwy','Admin',NULL,NULL,0,NULL,'2026-07-21 02:02:43','2026-07-28 14:12:56','Active',0),(13,'Deleted','User','deleted_13@deleted.local',NULL,'deleted_13','$2y$10$b6p9h5MgjJMCArCR/Fx2suYto6LNl1reamzIe9c8vL9yOqGraoYIy','Admin',NULL,NULL,0,NULL,'2026-07-21 12:47:37','2026-07-21 13:42:49','Deleted',0),(14,'Riz','monte','rizmonte@gmail.com','','riz.monte','$2y$10$XxyHMV1hYWEKX0H.kT3TkO8WZEg0jIafe3tLUP0.SpRskVOoyA6NC','Admin',NULL,NULL,0,NULL,'2026-08-02 08:15:30','2026-08-02 08:26:07','Active',0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-03  7:32:49
