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
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=255 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (3,7,'CARMINA VALLEJO','carmina25','Super Admin','Created a new account','::1','2026-07-15 16:28:56'),(4,8,'CARMINA VALLEJO','carmina2519','Admin','Created a new account','::1','2026-07-15 16:31:48'),(5,9,'CARMINA VALLEJO','carmina251911','Viewer','Created a new account','::1','2026-07-15 16:32:14'),(6,10,'CARMINA VALLEJO','carmina2519111','Viewer','Created a new account','::1','2026-07-15 16:38:26'),(7,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt','::1','2026-07-15 16:38:36'),(8,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt','::1','2026-07-15 16:39:36'),(9,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt','::1','2026-07-15 16:39:44'),(10,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt','::1','2026-07-15 16:40:07'),(11,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt','::1','2026-07-15 16:40:18'),(12,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt','::1','2026-07-15 16:40:44'),(13,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt','::1','2026-07-15 16:41:12'),(14,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-15 16:54:03'),(15,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-15 16:54:14'),(16,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-15 16:54:18'),(17,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-15 16:54:34'),(18,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-15 16:54:39'),(19,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-15 23:20:55'),(20,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-15 23:21:00'),(21,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 12:44:23'),(22,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 12:45:50'),(23,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 12:47:02'),(24,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 12:47:20'),(25,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 12:49:38'),(26,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 13:02:31'),(27,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 13:09:50'),(28,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 13:10:05'),(29,8,'CARMINA VALLEJO','carmina2519','Admin','Logged in successfully','::1','2026-07-16 13:56:16'),(30,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 13:56:56'),(31,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-16 14:04:06'),(32,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 01:48:48'),(33,7,'CARMINA VALLEJO','carmina25','Super Admin','Reset password for carmina251911','::1','2026-07-17 01:49:59'),(34,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 01:50:20'),(35,9,'CARMINA VALLEJO','carmina251911','Viewer','Logged in successfully','::1','2026-07-17 01:50:25'),(36,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 01:50:35'),(37,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 01:59:40'),(38,7,'CARMINA VALLEJO','carmina25','Super Admin','Activated user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 02:14:13'),(39,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 02:14:16'),(40,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz12511@gmail.com)','::1','2026-07-17 02:29:33'),(41,7,'CARMINA VALLEJO','carmina25','Super Admin','Activated user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 02:30:12'),(42,7,'CARMINA VALLEJO','carmina25','Super Admin','Activated user account: CARMINA VALLEJO (carriz12511@gmail.com)','::1','2026-07-17 02:30:14'),(43,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 02:34:50'),(44,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 04:00:37'),(45,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 04:24:31'),(46,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 05:23:48'),(47,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 06:34:30'),(48,7,'CARMINA VALLEJO','carmina25','Super Admin','Reset password for carmina2519','::1','2026-07-17 06:35:20'),(49,7,'CARMINA VALLEJO','carmina25','Super Admin','Reset password for carmina25','::1','2026-07-17 06:38:23'),(50,7,'CARMINA VALLEJO','carmina25','Super Admin','Created new user: CARMINA VALLEJO (carriz12511w1@gmail.com) - Role: Admin','::1','2026-07-17 06:41:22'),(51,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 08:34:21'),(52,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-16_15-53-31.sql','::1','2026-07-17 08:54:47'),(53,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-16_15-53-39.sql','::1','2026-07-17 08:54:50'),(54,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-16_15-53-58.sql','::1','2026-07-17 08:54:55'),(55,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:08'),(56,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:11'),(57,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:13'),(58,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:15'),(59,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:17'),(60,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:20'),(61,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:22'),(62,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:28'),(63,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 09:02:47'),(64,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:09:11'),(65,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:09:17'),(66,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:09:20'),(67,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:09:22'),(68,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:09:25'),(69,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked)','::1','2026-07-17 09:09:29'),(70,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked)','::1','2026-07-17 09:09:36'),(71,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked)','::1','2026-07-17 09:09:41'),(72,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked)','::1','2026-07-17 09:09:43'),(73,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked)','::1','2026-07-17 09:10:14'),(74,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-17 09:10:29'),(75,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 09:10:44'),(76,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:12:31'),(77,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:12:36'),(78,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:12:39'),(79,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:12:41'),(80,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:12:48'),(81,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (account locked)','::1','2026-07-17 09:12:52'),(82,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 09:13:08'),(83,9,'CARMINA VALLEJO','carmina251911','Viewer','','::1','2026-07-17 10:03:40'),(84,9,'CARMINA VALLEJO','carmina251911','Viewer','','::1','2026-07-17 10:03:42'),(85,9,'CARMINA VALLEJO','carmina251911','Viewer','','::1','2026-07-17 10:03:44'),(86,9,'CARMINA VALLEJO','carmina251911','Viewer','','::1','2026-07-17 10:03:48'),(87,9,'CARMINA VALLEJO','carmina251911','Viewer','','::1','2026-07-17 10:03:50'),(88,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (account locked)','::1','2026-07-17 10:03:53'),(89,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 10:05:26'),(90,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:06:25'),(91,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:06:28'),(92,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:06:30'),(93,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:08:22'),(94,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:08:25'),(95,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:08:28'),(96,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:09:43'),(97,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:09:46'),(98,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:09:48'),(99,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:10:52'),(100,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:10:54'),(101,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:10:57'),(102,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked)','::1','2026-07-17 10:11:10'),(103,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:12:02'),(104,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:13:10'),(105,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:13:12'),(106,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:14:14'),(107,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:14:19'),(108,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:14:22'),(109,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:15:40'),(110,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:15:53'),(111,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:15:55'),(112,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:17:37'),(113,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:17:40'),(114,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:17:44'),(115,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:19:06'),(116,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:19:08'),(117,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:19:10'),(118,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:28:54'),(119,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:28:57'),(120,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:28:59'),(121,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:30:03'),(122,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:30:14'),(123,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:34:27'),(124,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 10:35:00'),(125,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:35:32'),(126,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:35:37'),(127,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:35:40'),(128,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:38:00'),(129,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:38:04'),(130,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account permanently locked)','::1','2026-07-17 10:38:07'),(131,7,'CARMINA VALLEJO','carmina25','Super Admin','Activated user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 10:41:45'),(132,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 10:41:53'),(133,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account permanently locked)','::1','2026-07-17 10:42:13'),(134,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz12511w1@gmail.com)','::1','2026-07-17 10:42:28'),(135,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Failed login attempt (incorrect password)','::1','2026-07-17 10:42:40'),(136,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Logged in successfully','::1','2026-07-17 10:42:46'),(137,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account permanently locked)','::1','2026-07-17 10:46:40'),(138,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 10:46:54'),(139,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Logged in successfully','::1','2026-07-17 10:47:50'),(140,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Failed login attempt (account disabled)','::1','2026-07-17 10:51:00'),(141,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Failed login attempt (account disabled)','::1','2026-07-17 10:54:20'),(142,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Failed login attempt (account disabled)','::1','2026-07-17 10:54:54'),(143,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 10:55:30'),(144,7,'CARMINA VALLEJO','carmina25','Super Admin','Activated user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 10:55:41'),(145,7,'CARMINA VALLEJO','carmina25','Super Admin','Activated user account: CARMINA VALLEJO (carriz12511w1@gmail.com)','::1','2026-07-17 10:55:52'),(146,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Logged in successfully','::1','2026-07-17 10:56:00'),(147,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 10:56:14'),(148,7,'CARMINA VALLEJO','carmina25','Super Admin','Unlocked account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 10:56:37'),(149,10,'CARMINA VALLEJO','carmina2519111','Viewer','Logged in successfully','::1','2026-07-17 10:56:51'),(150,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 10:57:17'),(151,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 11:46:44'),(152,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 11:48:35'),(153,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 11:53:39'),(154,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 11:54:06'),(155,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted Database Backup','::1','2026-07-17 12:01:03'),(156,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted Database Backup','::1','2026-07-17 12:01:09'),(157,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted Database Backup','::1','2026-07-17 12:01:16'),(158,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 12:33:29'),(159,7,'CARMINA VALLEJO','carmina25','Super Admin','Restored database from backup: backup_2026-07-17_14-41-19.sql','::1','2026-07-17 12:42:18'),(160,7,'CARMINA VALLEJO','carmina25','Super Admin','Restored database from backup: backup_2026-07-17_14-42-29.sql','::1','2026-07-17 12:42:42'),(161,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 12:42:47'),(162,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 12:42:52'),(163,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted Database Backup','::1','2026-07-17 12:43:46'),(164,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted Database Backup','::1','2026-07-17 12:45:35'),(165,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 12:46:15'),(166,7,'CARMINA VALLEJO','carmina25','Super Admin','Restored database from backup: backup_2026-07-17_14-46-22.sql','::1','2026-07-17 12:48:02'),(167,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 12:48:40'),(168,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 12:48:43'),(169,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account permanently locked)','::1','2026-07-17 12:48:45'),(170,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz12511w1@gmail.com)','::1','2026-07-17 12:48:58'),(171,7,'CARMINA VALLEJO','carmina25','Super Admin','Reset password for carmina2519111','::1','2026-07-17 12:50:41'),(172,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 13:05:08'),(173,7,'CARMINA VALLEJO','carmina25','Super Admin','Updated user: CARMINA VALLEJO (carriz12511w1@gmail.com)','::1','2026-07-17 13:10:51'),(174,7,'CARMINA VALLEJO','carmina25','Super Admin','Activated user account: CARMINA VALLEJO (carriz12511w1@gmail.com)','::1','2026-07-17 13:34:33'),(175,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz12511w1@gmail.com)','::1','2026-07-17 13:34:36'),(176,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 13:47:57'),(177,7,'CARMINA VALLEJO','carmina25','Super Admin','Restored database from backup: backup_2026-07-17_15-49-00.sql','::1','2026-07-18 15:13:13'),(178,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-19 00:43:09'),(179,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (incorrect password)','::1','2026-07-19 01:01:56'),(180,9,'CARMINA VALLEJO','carmina251911','Viewer','Logged in successfully','::1','2026-07-19 01:02:01'),(181,8,'CARMINA VALLEJO','carmina2519','Admin','Failed login attempt (incorrect password)','::1','2026-07-19 01:03:10'),(182,8,'CARMINA VALLEJO','carmina2519','Admin','Logged in successfully','::1','2026-07-19 01:03:15'),(183,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-19 01:03:31'),(184,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-19 01:03:37'),(185,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-19 01:03:43'),(186,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-19 02:08:07'),(187,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-19 04:47:50'),(188,7,'CARMINA VALLEJO','carmina25','Super Admin','Restored database from backup: backup_2026-07-19_07-05-15.sql','::1','2026-07-21 00:56:23'),(189,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted Database Backup','::1','2026-07-21 00:56:28'),(190,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted Database Backup','::1','2026-07-21 00:56:45'),(191,7,'CARMINA VALLEJO','carmina25','Super Admin','Soft-deleted user: carmina251911 (ID: 9)','::1','2026-07-21 01:11:28'),(192,7,'CARMINA VALLEJO','carmina25','Super Admin','Soft-deleted user: carmina.vallejo (ID: 11)','::1','2026-07-21 01:13:32'),(193,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-17_14-46-15.sql','::1','2026-07-21 01:23:02'),(194,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-17_15-05-08.sql','::1','2026-07-21 01:23:05'),(195,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-17_15-47-57.sql','::1','2026-07-21 01:23:09'),(196,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 01:23:12'),(197,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 01:28:35'),(198,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 01:28:41'),(199,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-21_03-23-11.sql','::1','2026-07-21 01:28:46'),(200,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-21_03-28-35.sql','::1','2026-07-21 01:28:49'),(201,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 01:32:47'),(202,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-21_03-28-41.sql','::1','2026-07-21 01:33:58'),(203,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-21_03-32-46.sql','::1','2026-07-21 01:34:00'),(204,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 01:34:04'),(205,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 01:44:43'),(206,7,'CARMINA VALLEJO','carmina25','Super Admin','Restored database using uploaded backup: backup_2026-07-21_03-51-08.sql','::1','2026-07-21 01:52:04'),(207,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 01:54:27'),(208,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 01:55:44'),(209,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-21_03-54-26.sql','::1','2026-07-21 01:55:51'),(210,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-21_03-44-42.sql','::1','2026-07-21 01:55:54'),(211,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-21_03-34-03.sql','::1','2026-07-21 01:55:57'),(212,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz1251@gmail.com)','::1','2026-07-21 01:57:23'),(213,7,'CARMINA VALLEJO','carmina25','Super Admin','Created new user: CRIZTIEL VALLEJO (criz123@gmail.com) - Role: Admin','::1','2026-07-21 02:02:43'),(214,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-21 02:03:12'),(215,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 02:08:41'),(216,7,'CARMINA VALLEJO','carmina25','Super Admin','Reset password for criztiel.vallejo','::1','2026-07-21 02:10:00'),(217,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-21 02:10:14'),(218,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Failed login attempt (incorrect password)','::1','2026-07-21 05:22:50'),(219,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-21 05:22:57'),(220,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-21 05:23:09'),(221,7,'CARMINA VALLEJO','carmina25','Super Admin','Restored database using uploaded backup: backup_2026-07-21_13-26-12.sql','::1','2026-07-21 05:29:21'),(222,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-21 05:32:55'),(223,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-21 05:33:13'),(224,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-21 05:33:43'),(225,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-21 05:34:49'),(226,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-21 05:38:27'),(227,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Failed login attempt (incorrect password)','::1','2026-07-21 10:50:15'),(228,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-21 10:50:19'),(229,7,'CARMINA VALLEJO','carmina25','Super Admin','Created new user: riz VALLEJO (carriz12522@gmail.com) - Role: Admin','::1','2026-07-21 12:47:37'),(230,7,'CARMINA VALLEJO','carmina25','Super Admin','Soft-deleted user: riz.vallejo (ID: 13)','::1','2026-07-21 13:42:49'),(231,7,'CARMINA VALLEJO','carmina25','Super Admin','Soft-deleted user: carmina2519 (ID: 8)','::1','2026-07-21 14:09:31'),(232,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 14:30:23'),(233,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-21 15:20:26'),(234,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-21 15:58:50'),(235,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-21 15:58:56'),(236,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-21 15:59:27'),(237,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-21 15:59:52'),(238,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-21_09-55-43.sql','::1','2026-07-22 00:20:17'),(239,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-21_10-08-41.sql','::1','2026-07-22 00:20:20'),(240,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-22 00:21:04'),(241,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Processed sale for product ID 1, quantity 6.','::1','2026-07-24 12:05:27'),(242,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-25 23:56:54'),(243,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-26 00:00:09'),(244,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-28 09:45:23'),(245,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-28 09:46:25'),(246,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-28 09:47:46'),(247,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-28 10:08:45'),(248,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Updated product: qew (ID: 4)','::1','2026-07-28 13:55:24'),(249,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted product: qew (ID: 4)','::1','2026-07-28 13:55:31'),(250,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted product: asada (ID: 3)','::1','2026-07-28 13:55:36'),(251,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted product: pe shirt (ID: 1)','::1','2026-07-28 13:55:38'),(252,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Deleted product: pe shirt (ID: 2)','::1','2026-07-28 13:55:41'),(253,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Failed login attempt (incorrect password)','::1','2026-07-28 14:12:51'),(254,12,'CRIZTIEL VALLEJO','criztiel.vallejo','Admin','Logged in successfully','::1','2026-07-28 14:12:56');
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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_history`
--

LOCK TABLES `backup_history` WRITE;
/*!40000 ALTER TABLE `backup_history` DISABLE KEYS */;
INSERT INTO `backup_history` VALUES (22,'Database Backup - Jul 21, 2026 10:30 PM','Database','backup_2026-07-21_22-30-23.sql','C:\\xampp\\htdocs\\InventoryManagementSystem\\process\\superadmin/../../backups/backup_2026-07-21_22-30-23.sql','48.47 KB',7,'Super Admin','2026-07-21 22:30:23','Completed','Backup created successfully'),(23,'Database Backup - Jul 21, 2026 11:20 PM','Database','backup_2026-07-21_23-20-25.sql','C:\\xampp\\htdocs\\InventoryManagementSystem\\process\\superadmin/../../backups/backup_2026-07-21_23-20-25.sql','48.86 KB',7,'Super Admin','2026-07-21 23:20:26','Completed','Backup created successfully'),(24,'Database Backup - Jul 28, 2026 05:46 PM','Database','backup_2026-07-28_17-46-24.sql','C:\\xampp\\htdocs\\InventoryManagementSystem\\process\\superadmin/../../backups/backup_2026-07-28_17-46-24.sql','49.74 KB',7,'Super Admin','2026-07-28 17:46:25','Completed','Backup created successfully'),(25,'Database Backup - Jul 28, 2026 06:08 PM','Database','backup_2026-07-28_18-08-44.sql','C:\\xampp\\htdocs\\InventoryManagementSystem\\process\\superadmin/../../backups/backup_2026-07-28_18-08-44.sql','50.23 KB',7,'Super Admin','2026-07-28 18:08:45','Completed','Backup created successfully');
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
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `received_by` (`received_by`),
  CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `deliveries_ibfk_2` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deliveries`
--

LOCK TABLES `deliveries` WRITE;
/*!40000 ALTER TABLE `deliveries` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_items`
--

LOCK TABLES `delivery_items` WRITE;
/*!40000 ALTER TABLE `delivery_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `delivery_items` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_transactions`
--

LOCK TABLES `inventory_transactions` WRITE;
/*!40000 ALTER TABLE `inventory_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_transactions` ENABLE KEYS */;
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
  KEY `fk_product_category` (`category_id`),
  KEY `fk_product_supplier` (`supplier_id`),
  KEY `fk_product_updatedby` (`updated_by`),
  CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `fk_product_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `fk_product_updatedby` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
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
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `processed_by` int(11) DEFAULT NULL,
  `return_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`),
  KEY `product_id` (`product_id`),
  KEY `processed_by` (`processed_by`),
  CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
  CONSTRAINT `returns_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `returns_ibfk_3` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `returns`
--

LOCK TABLES `returns` WRITE;
/*!40000 ALTER TABLE `returns` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_items`
--

LOCK TABLES `sale_items` WRITE;
/*!40000 ALTER TABLE `sale_items` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (7,'CARMINA','VALLEJO','carriz125@gmail.com','','carmina25','$2y$10$rug2ew9Vm0iqEM/JPNHoGemwiF.F8lHFi08wH1rcV1eCRpENS5Fja','Super Admin','$2y$10$VPTZRyRUTQScEXmsvluorOFkQqEPPEGswLTAF44il4dVtvPWF0X.S','DQ-VV-EM',0,NULL,'2026-07-15 16:28:56','2026-07-21 15:59:27','Active',0),(8,'Deleted','User','deleted_8@deleted.local',NULL,'deleted_8','$2y$10$aHJdc4dAG3ibW8658/DqN.EHeHkKowe.tBvv3T8wMZVeL5XMry78C','Admin',NULL,NULL,0,NULL,'2026-07-15 16:31:48','2026-07-21 14:09:31','Deleted',0),(9,'Deleted','User','deleted_9@deleted.local',NULL,'deleted_9','$2y$10$/eIg/YHtvdSYdiKH.XO1LOUpuQaXjBFgBsM20Nk2jj.NCW2AZSVj6','Viewer',NULL,NULL,0,NULL,'2026-07-15 16:32:14','2026-07-21 01:11:28','Deleted',0),(10,'CARMINA','VALLEJO','carriz125111@gmail.com','','carmina2519111','$2y$10$dbN1DzSdmygsK.lcFrm5/ezswSbYTJnF7cAXcAIaMrPDVockwcfMm','Viewer',NULL,NULL,3,NULL,'2026-07-15 16:38:26','2026-07-17 12:50:41','Active',1),(11,'Deleted','User','deleted_11@deleted.local',NULL,'deleted_11','$2y$10$L7326CbonX4j3HhxM.xlbO8PeZUYkgtiOzTD4H7alibY0SeWIXNIq','Admin',NULL,NULL,0,NULL,'2026-07-17 06:41:22','2026-07-21 01:13:32','Deleted',0),(12,'CRIZTIEL','VALLEJO','criz123@gmail.com','09061111111','criztiel.vallejo','$2y$10$6I6aG5ANNKJopYGQQOA3L.Vy5rWxIYuEyYHkm9YqNBr8.X7WPZSwy','Admin',NULL,NULL,0,NULL,'2026-07-21 02:02:43','2026-07-28 14:12:56','Active',0),(13,'Deleted','User','deleted_13@deleted.local',NULL,'deleted_13','$2y$10$b6p9h5MgjJMCArCR/Fx2suYto6LNl1reamzIe9c8vL9yOqGraoYIy','Admin',NULL,NULL,0,NULL,'2026-07-21 12:47:37','2026-07-21 13:42:49','Deleted',0);
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

-- Dump completed on 2026-07-28 22:39:16
