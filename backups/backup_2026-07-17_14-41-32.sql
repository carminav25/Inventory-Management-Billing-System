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
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (3,7,'CARMINA VALLEJO','carmina25','Super Admin','Created a new account','::1','2026-07-15 16:28:56'),(4,8,'CARMINA VALLEJO','carmina2519','Admin','Created a new account','::1','2026-07-15 16:31:48'),(5,9,'CARMINA VALLEJO','carmina251911','Viewer','Created a new account','::1','2026-07-15 16:32:14'),(6,10,'CARMINA VALLEJO','carmina2519111','Viewer','Created a new account','::1','2026-07-15 16:38:26'),(7,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt','::1','2026-07-15 16:38:36'),(8,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt','::1','2026-07-15 16:39:36'),(9,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt','::1','2026-07-15 16:39:44'),(10,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt','::1','2026-07-15 16:40:07'),(11,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt','::1','2026-07-15 16:40:18'),(12,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt','::1','2026-07-15 16:40:44'),(13,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt','::1','2026-07-15 16:41:12'),(14,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-15 16:54:03'),(15,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-15 16:54:14'),(16,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-15 16:54:18'),(17,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-15 16:54:34'),(18,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-15 16:54:39'),(19,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-15 23:20:55'),(20,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-15 23:21:00'),(21,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 12:44:23'),(22,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 12:45:50'),(23,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 12:47:02'),(24,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 12:47:20'),(25,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 12:49:38'),(26,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 13:02:31'),(27,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 13:09:50'),(28,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 13:10:05'),(29,8,'CARMINA VALLEJO','carmina2519','Admin','Logged in successfully','::1','2026-07-16 13:56:16'),(30,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-16 13:56:56'),(31,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-16 14:04:06'),(32,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 01:48:48'),(33,7,'CARMINA VALLEJO','carmina25','Super Admin','Reset password for carmina251911','::1','2026-07-17 01:49:59'),(34,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 01:50:20'),(35,9,'CARMINA VALLEJO','carmina251911','Viewer','Logged in successfully','::1','2026-07-17 01:50:25'),(36,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 01:50:35'),(37,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 01:59:40'),(38,7,'CARMINA VALLEJO','carmina25','Super Admin','Activated user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 02:14:13'),(39,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 02:14:16'),(40,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz12511@gmail.com)','::1','2026-07-17 02:29:33'),(41,7,'CARMINA VALLEJO','carmina25','Super Admin','Activated user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 02:30:12'),(42,7,'CARMINA VALLEJO','carmina25','Super Admin','Activated user account: CARMINA VALLEJO (carriz12511@gmail.com)','::1','2026-07-17 02:30:14'),(43,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 02:34:50'),(44,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 04:00:37'),(45,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 04:24:31'),(46,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 05:23:48'),(47,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 06:34:30'),(48,7,'CARMINA VALLEJO','carmina25','Super Admin','Reset password for carmina2519','::1','2026-07-17 06:35:20'),(49,7,'CARMINA VALLEJO','carmina25','Super Admin','Reset password for carmina25','::1','2026-07-17 06:38:23'),(50,7,'CARMINA VALLEJO','carmina25','Super Admin','Created new user: CARMINA VALLEJO (carriz12511w1@gmail.com) - Role: Admin','::1','2026-07-17 06:41:22'),(51,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 08:34:21'),(52,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-16_15-53-31.sql','::1','2026-07-17 08:54:47'),(53,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-16_15-53-39.sql','::1','2026-07-17 08:54:50'),(54,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted database backup: backup_2026-07-16_15-53-58.sql','::1','2026-07-17 08:54:55'),(55,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:08'),(56,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:11'),(57,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:13'),(58,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:15'),(59,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:17'),(60,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:20'),(61,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:22'),(62,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:02:28'),(63,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 09:02:47'),(64,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:09:11'),(65,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:09:17'),(66,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:09:20'),(67,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:09:22'),(68,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:09:25'),(69,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked)','::1','2026-07-17 09:09:29'),(70,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked)','::1','2026-07-17 09:09:36'),(71,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked)','::1','2026-07-17 09:09:41'),(72,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked)','::1','2026-07-17 09:09:43'),(73,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked)','::1','2026-07-17 09:10:14'),(74,7,'CARMINA VALLEJO','carmina25','Super Admin','Failed login attempt (incorrect password)','::1','2026-07-17 09:10:29'),(75,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 09:10:44'),(76,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:12:31'),(77,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:12:36'),(78,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:12:39'),(79,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:12:41'),(80,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 09:12:48'),(81,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (account locked)','::1','2026-07-17 09:12:52'),(82,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 09:13:08'),(83,9,'CARMINA VALLEJO','carmina251911','Viewer','','::1','2026-07-17 10:03:40'),(84,9,'CARMINA VALLEJO','carmina251911','Viewer','','::1','2026-07-17 10:03:42'),(85,9,'CARMINA VALLEJO','carmina251911','Viewer','','::1','2026-07-17 10:03:44'),(86,9,'CARMINA VALLEJO','carmina251911','Viewer','','::1','2026-07-17 10:03:48'),(87,9,'CARMINA VALLEJO','carmina251911','Viewer','','::1','2026-07-17 10:03:50'),(88,9,'CARMINA VALLEJO','carmina251911','Viewer','Failed login attempt (account locked)','::1','2026-07-17 10:03:53'),(89,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 10:05:26'),(90,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:06:25'),(91,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:06:28'),(92,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:06:30'),(93,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:08:22'),(94,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:08:25'),(95,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:08:28'),(96,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:09:43'),(97,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:09:46'),(98,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:09:48'),(99,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:10:52'),(100,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:10:54'),(101,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:10:57'),(102,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked)','::1','2026-07-17 10:11:10'),(103,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:12:02'),(104,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:13:10'),(105,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:13:12'),(106,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:14:14'),(107,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:14:19'),(108,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:14:22'),(109,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:15:40'),(110,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:15:53'),(111,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:15:55'),(112,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:17:37'),(113,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:17:40'),(114,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:17:44'),(115,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:19:06'),(116,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:19:08'),(117,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:19:10'),(118,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:28:54'),(119,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:28:57'),(120,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:28:59'),(121,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:30:03'),(122,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:30:14'),(123,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:34:27'),(124,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 10:35:00'),(125,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:35:32'),(126,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:35:37'),(127,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account locked for 1 min)','::1','2026-07-17 10:35:40'),(128,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:38:00'),(129,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (incorrect password)','::1','2026-07-17 10:38:04'),(130,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account permanently locked)','::1','2026-07-17 10:38:07'),(131,7,'CARMINA VALLEJO','carmina25','Super Admin','Activated user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 10:41:45'),(132,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 10:41:53'),(133,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account permanently locked)','::1','2026-07-17 10:42:13'),(134,7,'CARMINA VALLEJO','carmina25','Super Admin','Disabled user account: CARMINA VALLEJO (carriz12511w1@gmail.com)','::1','2026-07-17 10:42:28'),(135,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Failed login attempt (incorrect password)','::1','2026-07-17 10:42:40'),(136,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Logged in successfully','::1','2026-07-17 10:42:46'),(137,10,'CARMINA VALLEJO','carmina2519111','Viewer','Failed login attempt (account permanently locked)','::1','2026-07-17 10:46:40'),(138,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 10:46:54'),(139,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Logged in successfully','::1','2026-07-17 10:47:50'),(140,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Failed login attempt (account disabled)','::1','2026-07-17 10:51:00'),(141,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Failed login attempt (account disabled)','::1','2026-07-17 10:54:20'),(142,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Failed login attempt (account disabled)','::1','2026-07-17 10:54:54'),(143,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 10:55:30'),(144,7,'CARMINA VALLEJO','carmina25','Super Admin','Activated user account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 10:55:41'),(145,7,'CARMINA VALLEJO','carmina25','Super Admin','Activated user account: CARMINA VALLEJO (carriz12511w1@gmail.com)','::1','2026-07-17 10:55:52'),(146,11,'CARMINA VALLEJO','carmina.vallejo','Admin','Logged in successfully','::1','2026-07-17 10:56:00'),(147,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 10:56:14'),(148,7,'CARMINA VALLEJO','carmina25','Super Admin','Unlocked account: CARMINA VALLEJO (carriz125111@gmail.com)','::1','2026-07-17 10:56:37'),(149,10,'CARMINA VALLEJO','carmina2519111','Viewer','Logged in successfully','::1','2026-07-17 10:56:51'),(150,7,'CARMINA VALLEJO','carmina25','Super Admin','Logged in successfully','::1','2026-07-17 10:57:17'),(151,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 11:46:44'),(152,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 11:48:35'),(153,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 11:53:39'),(154,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 11:54:06'),(155,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted Database Backup','::1','2026-07-17 12:01:03'),(156,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted Database Backup','::1','2026-07-17 12:01:09'),(157,7,'CARMINA VALLEJO','carmina25','Super Admin','Deleted Database Backup','::1','2026-07-17 12:01:16'),(158,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 12:33:29'),(159,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 12:41:19'),(160,7,'CARMINA VALLEJO','carmina25','Super Admin','Created Database Backup','::1','2026-07-17 12:41:26');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_history`
--

LOCK TABLES `backup_history` WRITE;
/*!40000 ALTER TABLE `backup_history` DISABLE KEYS */;
INSERT INTO `backup_history` VALUES (4,'Manual Database Backup','Database','backup_2026-07-17_13-54-06.sql','C:\\xampp\\htdocs\\InventoryManagementSystem\\pages\\superadmin/../../backups/backup_2026-07-17_13-54-06.sql','24.46 KB',7,'Super Admin','2026-07-17 19:54:06','Completed','Backup created successfully'),(5,'Database Backup - Jul 17, 2026 02:33 PM','Database','backup_2026-07-17_14-33-29.sql','C:\\xampp\\htdocs\\InventoryManagementSystem\\pages\\superadmin/../../backups/backup_2026-07-17_14-33-29.sql','24.32 KB',7,'Super Admin','2026-07-17 20:33:29','Completed','Backup created successfully'),(6,'Database Backup - Jul 17, 2026 02:41 PM','Database','backup_2026-07-17_14-41-19.sql','C:\\xampp\\htdocs\\InventoryManagementSystem\\pages\\superadmin/../../backups/backup_2026-07-17_14-41-19.sql','24.71 KB',7,'Super Admin','2026-07-17 20:41:19','Completed','Backup created successfully'),(7,'Database Backup - Jul 17, 2026 02:41 PM','Database','backup_2026-07-17_14-41-26.sql','C:\\xampp\\htdocs\\InventoryManagementSystem\\pages\\superadmin/../../backups/backup_2026-07-17_14-41-26.sql','25.1 KB',7,'Super Admin','2026-07-17 20:41:26','Completed','Backup created successfully');
/*!40000 ALTER TABLE `backup_history` ENABLE KEYS */;
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
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `is_permanently_locked` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (7,'CARMINA','VALLEJO','carriz125@gmail.com','','carmina25','$2y$10$rug2ew9Vm0iqEM/JPNHoGemwiF.F8lHFi08wH1rcV1eCRpENS5Fja','Super Admin','$2y$10$VPTZRyRUTQScEXmsvluorOFkQqEPPEGswLTAF44il4dVtvPWF0X.S','DQ-VV-EM',0,NULL,'2026-07-15 16:28:56','2026-07-17 09:10:44','Active',0),(8,'CARMINA','VALLEJO','carriz1251@gmail.com','','carmina2519','$2y$10$aHJdc4dAG3ibW8658/DqN.EHeHkKowe.tBvv3T8wMZVeL5XMry78C','Admin',NULL,NULL,0,NULL,'2026-07-15 16:31:48','2026-07-17 06:35:20','Active',0),(9,'CARMINA','VALLEJO','carriz12511@gmail.com','','carmina251911','$2y$10$/eIg/YHtvdSYdiKH.XO1LOUpuQaXjBFgBsM20Nk2jj.NCW2AZSVj6','Viewer',NULL,NULL,5,'2026-07-17 12:18:50','2026-07-15 16:32:14','2026-07-17 10:03:50','Active',0),(10,'CARMINA','VALLEJO','carriz125111@gmail.com','','carmina2519111','$2y$10$MUM.BEqzEjd45okFn9Narepg31gsS7O6w0v3.q8C6PB2txjUphY3O','Viewer',NULL,NULL,0,NULL,'2026-07-15 16:38:26','2026-07-17 10:56:37','Active',0),(11,'CARMINA','VALLEJO','carriz12511w1@gmail.com','','carmina.vallejo','$2y$10$L7326CbonX4j3HhxM.xlbO8PeZUYkgtiOzTD4H7alibY0SeWIXNIq','Admin',NULL,NULL,0,NULL,'2026-07-17 06:41:22','2026-07-17 10:55:52','Active',0);
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

-- Dump completed on 2026-07-17 20:41:32
