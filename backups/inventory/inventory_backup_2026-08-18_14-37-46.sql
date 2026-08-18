-- ======================================================
-- ISU INVENTORY MANAGEMENT & BILLING SYSTEM
-- INVENTORY BACKUP FILE
-- Version: 1.0
-- Generated: 2026-08-18 14:37:46
-- ======================================================

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
  CONSTRAINT `fk_product_updatedby` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (11,'PROD-000001','PE Shirt','PE',NULL,'XS','0','ABC Company',111.00,NULL,111.00,0.00,109,12321.00,11,'Available','assets/uploads/products/6a68e6892d3d5_front_pe shirt.png','assets/qrcodes/PROD-000001.png','2026-07-28 17:27:37','2026-08-10 15:01:31',NULL),(12,'PROD-000012','PE Shirt','PE',NULL,'Small','0','ABC Company',11.00,NULL,111.00,0.00,12,121.00,11,'Available','assets/uploads/products/6a68e6c86e6a7_front_pe shirt.png','assets/qrcodes/PROD-000012.png','2026-07-28 17:28:40','2026-08-02 13:25:38',NULL),(13,'PROD-000013','PE Shirt','PE',NULL,'Medium','0','ABC Company',150.00,NULL,250.00,0.00,18,3000.00,5,'Available','assets/uploads/products/6a694d0cee767_front_pe shirt.png','assets/qrcodes/PROD-000013.png','2026-07-29 00:45:00','2026-08-02 12:23:50',NULL),(14,'PROD-000014','PE Shirt','PE',NULL,'Large','0','ABC Company',150.00,NULL,250.00,0.00,18,3000.00,5,'Available','assets/uploads/products/6a694d38bf138_front_pe shirt.png','assets/qrcodes/PROD-000014.png','2026-07-29 00:45:44','2026-08-14 13:38:05',NULL),(15,'PROD-000015','PE Shirt','PE',NULL,'XL','0','ABC Company',150.00,NULL,250.00,0.00,19,3000.00,5,'Available','assets/uploads/products/6a694d8da6703_front_pe shirt.png','assets/qrcodes/PROD-000015.png','2026-07-29 00:47:09','2026-08-10 10:47:30',NULL),(16,'PROD-000016','PE Shirt','PE',NULL,'XXL','0','ABC Company',150.00,NULL,250.00,0.00,20,3000.00,5,'Available','assets/uploads/products/6a694da8a8f2d_front_pe shirt.png','assets/qrcodes/PROD-000016.png','2026-07-29 00:47:36','2026-07-29 00:47:36',NULL),(17,'PROD-000017','PE Shirt','PE',NULL,'XXXL','0','ABC Company',150.00,NULL,250.00,0.00,20,3000.00,5,'Available','assets/uploads/products/6a694dbdc606e_front_pe shirt.png','assets/qrcodes/PROD-000017.png','2026-07-29 00:47:57','2026-07-29 00:47:57',NULL),(18,'PROD-000018','PE Shirt','PE',NULL,'Free Size','0','ABC Company',150.00,NULL,250.00,0.00,20,3000.00,5,'Available','assets/uploads/products/6a694dcf4cf49_front_pe shirt.png','assets/qrcodes/PROD-000018.png','2026-07-29 00:48:15','2026-07-29 00:48:15',NULL),(19,'PROD-000019','PE Pants','PE',NULL,'XS','0','ABC Company',100.00,NULL,200.00,0.00,22,2000.00,5,'Available','assets/uploads/products/6a694e00ef116_front_pe_pants.png','assets/qrcodes/PROD-000019.png','2026-07-29 00:49:04','2026-07-30 14:54:17',NULL),(20,'PROD-000020','PE Pants','PE',NULL,'Small','0','ABC Company',100.00,NULL,200.00,0.00,23,2000.00,5,'Available','assets/uploads/products/6a694e0e6e3c7_front_pe_pants.png','assets/qrcodes/PROD-000020.png','2026-07-29 00:49:18','2026-08-04 06:47:02',NULL),(21,'PROD-000021','PE Pants','PE',NULL,'Medium','0','ABC Company',100.00,NULL,200.00,0.00,20,2000.00,5,'Available','assets/uploads/products/6a694e1ae1367_front_pe_pants.png','assets/qrcodes/PROD-000021.png','2026-07-29 00:49:30','2026-07-29 00:49:30',NULL),(22,'PROD-000022','PE Pants','PE',NULL,'Large','0','ABC Company',100.00,NULL,200.00,0.00,18,2000.00,5,'Available','assets/uploads/products/6a694e2aa109f_front_pe_pants.png','assets/qrcodes/PROD-000022.png','2026-07-29 00:49:46','2026-08-02 13:11:36',NULL),(23,'PROD-000023','PE Pants','PE',NULL,'XL','0','ABC Company',100.00,NULL,200.00,0.00,16,2000.00,5,'Available','assets/uploads/products/6a694e3ba0a4a_front_pe_pants.png','assets/qrcodes/PROD-000023.png','2026-07-29 00:50:03','2026-08-11 09:25:42',NULL),(24,'PROD-000024','PE Pants','PE',NULL,'XXL','0','ABC Company',100.00,NULL,200.00,0.00,19,2000.00,5,'Available','assets/uploads/products/6a694e47aaf3f_front_pe_pants.png','assets/qrcodes/PROD-000024.png','2026-07-29 00:50:15','2026-08-07 08:41:51',NULL),(25,'PROD-000025','PE Pants','PE',NULL,'XXXL','0','ABC Company',100.00,NULL,200.00,0.00,22,2000.00,5,'Available','assets/uploads/products/6a694e5223381_front_pe_pants.png','assets/qrcodes/PROD-000025.png','2026-07-29 00:50:26','2026-08-14 13:35:09',NULL),(26,'PROD-000026','PE Pants','PE',NULL,'Free Size','0','ABC Company',100.00,NULL,200.00,0.00,13,2000.00,5,'Available','assets/uploads/products/6a694e652bcce_front_pe_pants.png','assets/qrcodes/PROD-000026.png','2026-07-29 00:50:45','2026-08-04 06:45:32',NULL),(27,'PROD-000027','Set Uniform for Women','School Uniform',NULL,'XS','0','ABC Company',750.00,NULL,1200.00,0.00,21,15000.00,5,'Available','assets/uploads/products/6a694ed083d8f_front_new_compuniform.png','assets/qrcodes/PROD-000027.png','2026-07-29 00:52:32','2026-07-30 14:54:17',NULL),(28,'PROD-000028','Set Uniform for Women','School Uniform',NULL,'Small','0','ABC Company',750.00,NULL,1200.00,0.00,21,15000.00,5,'Available','assets/uploads/products/6a694fb4a24ca_front_new_compuniform.png','assets/qrcodes/PROD-000028.png','2026-07-29 00:56:20','2026-07-30 15:24:02',NULL),(29,'PROD-000029','Set Uniform for Women','School Uniform',NULL,'Medium','0','ABC Company',750.00,NULL,1200.00,0.00,20,15000.00,5,'Available','assets/uploads/products/6a694fbf49801_front_new_compuniform.png','assets/qrcodes/PROD-000029.png','2026-07-29 00:56:31','2026-07-29 00:56:31',NULL),(30,'PROD-000030','Set Uniform for Women','School Uniform',NULL,'Large','0','ABC Company',750.00,NULL,1200.00,0.00,20,15000.00,5,'Available','assets/uploads/products/6a694fdcef036_front_new_compuniform.png','assets/qrcodes/PROD-000030.png','2026-07-29 00:57:00','2026-07-29 00:57:00',NULL),(31,'PROD-000031','Set Uniform for Women','School Uniform',NULL,'XL','0','ABC Company',750.00,NULL,1200.00,0.00,22,15000.00,5,'Available','assets/uploads/products/6a694fe4ec1e7_front_new_compuniform.png','assets/qrcodes/PROD-000031.png','2026-07-29 00:57:08','2026-08-02 14:10:40',NULL),(32,'PROD-000032','Set Uniform for Women','School Uniform',NULL,'XXL','0','ABC Company',750.00,NULL,1200.00,0.00,22,15000.00,5,'Available','assets/uploads/products/6a694fef97d12_front_new_compuniform.png','assets/qrcodes/PROD-000032.png','2026-07-29 00:57:19','2026-08-02 14:11:21',NULL),(33,'PROD-000033','Set Uniform for Women','School Uniform',NULL,'XXXL','0','ABC Company',750.00,NULL,1200.00,0.00,8,15000.00,5,'Available','assets/uploads/products/6a694ffea40ef_front_new_compuniform.png','assets/qrcodes/PROD-000033.png','2026-07-29 00:57:34','2026-08-04 08:00:07',NULL),(34,'PROD-000034','Set Uniform for Women','School Uniform',NULL,'Free Size','0','ABC Company',750.00,NULL,1200.00,0.00,8,15000.00,5,'Available','assets/uploads/products/6a69500e5710e_front_new_compuniform.png','assets/qrcodes/PROD-000034.png','2026-07-29 00:57:50','2026-08-02 14:10:40',NULL),(35,'PROD-000035','ID Lace','Accessories',NULL,'Free Size','0','ABC',50.00,NULL,100.00,0.00,10,2500.00,20,'Available','assets/uploads/products/6a6b298e7041a_front_id_lace.png','assets/qrcodes/PROD-000035.png','2026-07-30 10:38:06','2026-08-14 13:41:56',NULL),(36,'PROD-000036','Neck Lace','Accessories',NULL,'Free Size','0','ABC Trading',15.00,NULL,30.00,0.00,54,750.00,20,'Available','assets/uploads/products/6a6b2a387c790_front_necklace_g.png','assets/qrcodes/PROD-000036.png','2026-07-30 10:40:56','2026-08-06 03:19:29',NULL),(37,'PROD-000037','Neck Tie','Accessories',NULL,'Free Size','0','ABC Trading',15.00,NULL,30.00,0.00,49,750.00,20,'Available','assets/uploads/products/6a6b2a6570d8e_front_necktie_g.png','assets/qrcodes/PROD-000037.png','2026-07-30 10:41:41','2026-08-11 09:25:16',NULL),(38,'PROD-000038','ISU Logo','Accessories',NULL,'Free Size','0','ABC Trading',15.00,NULL,30.00,0.00,65,750.00,20,'Available','assets/uploads/products/6a6b2aa861b28_front_logo_.png','assets/qrcodes/PROD-000038.png','2026-07-30 10:42:48','2026-08-11 09:18:49',NULL),(40,'PROD-000039','Old Newform','Uniform',NULL,'Small','0','ISU Supplier',450.00,NULL,750.00,0.00,7,4500.00,10,'Available','assets/uploads/products/6a6b70b80b204_front_com_uniform.png','assets/qrcodes/PROD-000039.png','2026-07-30 15:41:44','2026-08-04 08:00:39',NULL),(41,'PROD-000041','Old Newform','Uniform',NULL,'XS','0','ABC Trading',500.00,NULL,1000.00,0.00,0,0.00,0,'Available','assets/uploads/products/6a6b70eb0734d_front_com_uniform.png','assets/qrcodes/PROD-000041.png','2026-07-30 15:42:35','2026-08-04 06:46:02',NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'ABC Trading','Juan Dela Cruz','09123456789','abc@gmail.com','Cauayan City','Active','2026-07-30 10:30:51'),(2,'ISU Supplier','Pedro Santos','09987654321','isu@gmail.com','Santiago City','Active','2026-07-30 10:30:51'),(3,'Uniform Depot','Maria Cruz','09111222333','uniform@gmail.com','Isabela','Inactive','2026-07-30 10:30:51'),(4,'ABC COMPANY','MAY CRUZ','09999999999','carriz12511@gmail.com','SAN FERMIN DACANAY','Active','2026-07-30 17:02:32'),(5,'JC','John','09111111111','carriz12511@sample.com','Dalupang','Active','2026-08-04 07:56:23'),(6,'ABC','MARK LACE','09111222333','carriz12511@gmail.com','Dalupang','Active','2026-08-07 08:36:24'),(11,'abcv','CARMINA VALLEJO','09999999999','carriz12511@sample.com','Dalupang','Active','2026-08-11 06:13:05');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
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
  CONSTRAINT `deliveries_ibfk_2` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deliveries`
--

LOCK TABLES `deliveries` WRITE;
/*!40000 ALTER TABLE `deliveries` DISABLE KEYS */;
INSERT INTO `deliveries` VALUES (2,1,'DEL-20260730-6355','2026-07-30 00:00:00',NULL,'Completed','','DR-26-0125'),(3,1,'DEL-20260730-9377','2026-07-30 00:00:00',NULL,'Completed','','DR-26-0125'),(4,1,'DEL-20260730-5614','2026-07-30 00:00:00',NULL,'Completed','',''),(5,1,'DEL-20260730-9076','2026-07-30 00:00:00',NULL,'Completed','','DR-26-0126'),(6,1,'DEL-20260731-1692','2026-07-31 00:00:00',NULL,'Completed','','DR-26-0127'),(7,4,'DEL-20260731-7797','2026-07-31 00:00:00',NULL,'Completed','',''),(8,4,'DEL-20260802-3749','2026-08-02 00:00:00',NULL,'Completed','logo','DR-26-0125'),(9,NULL,'DEL-20260802-6306','2026-08-02 00:00:00',NULL,'Completed','Received delivery','DR-26-0125'),(10,4,'DEL-20260802-6400','2026-08-02 00:00:00',NULL,'Completed','Received delivery','DR-26-0125'),(11,1,'DEL-20260802-9998','2026-08-02 00:00:00',NULL,'Completed','Received delivery','DR-26-0126'),(12,4,'DEL-20260802-8920','2026-08-02 00:00:00',NULL,'Completed','Received delivery','DR-26-0128'),(13,4,'DEL-20260802-5616','2026-08-02 00:00:00',NULL,'Completed','Received delivery','DR-26-0125'),(14,1,'DEL-20260802-3830','2026-08-02 00:00:00',NULL,'Completed','Received delivery','DR-26-0129'),(15,4,'DEL-20260804-4608','2026-08-04 14:46:00',NULL,'Completed','','DR-26-0134'),(16,1,'DEL-20260806-1055','2026-08-06 11:19:00',NULL,'Completed','','DR-26-0128'),(17,6,'DEL-20260807-7028','2026-08-07 16:37:00',NULL,'Completed','','DR-26-0127'),(18,4,'DEL-20260807-2467','2026-08-07 16:41:00',NULL,'Completed','','DR-26-0130'),(19,1,'DEL-20260811-1109','2026-08-11 17:17:00',2,'Completed','','DR-26-0125'),(20,4,'DEL-20260814-5185','2026-08-14 21:34:00',2,'Completed','','ds-21321'),(21,6,'DEL-20260814-1995','2026-08-14 21:41:00',2,'Completed','','ds-21321');
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
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_items`
--

LOCK TABLES `delivery_items` WRITE;
/*!40000 ALTER TABLE `delivery_items` DISABLE KEYS */;
INSERT INTO `delivery_items` VALUES (1,2,12,1,11.00),(2,3,38,10,15.00),(3,4,38,1,15.00),(4,4,35,1,50.00),(5,4,36,1,15.00),(6,4,19,2,100.00),(7,4,27,1,750.00),(8,5,20,1,10.00),(9,5,28,1,800.00),(10,5,12,1,11.00),(11,5,37,1,15.00),(12,5,36,1,15.00),(13,5,38,1,15.00),(14,5,35,1,50.00),(15,6,36,1,15.00),(16,7,35,5,50.00),(17,8,38,1,15.00),(18,9,32,1,750.00),(19,10,32,1,750.00),(20,11,38,1,15.00),(21,11,41,1,500.00),(22,12,32,2,750.00),(23,12,31,1,750.00),(24,12,33,3,750.00),(25,13,32,1,750.00),(26,13,31,2,750.00),(27,13,33,1,750.00),(28,14,38,1,15.00),(29,15,20,2,100.00),(30,16,36,1,15.00),(31,17,35,1,50.00),(32,18,25,2,100.00),(33,18,24,2,100.00),(34,18,23,1,100.00),(35,19,38,1,15.00),(36,20,25,1,100.00),(37,21,35,10,50.00);
/*!40000 ALTER TABLE `delivery_items` ENABLE KEYS */;
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
  CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
INSERT INTO `sales` VALUES (1,'INV-20260731-5442','jin',100.00,0.00,100.00,'Cash',NULL,'2026-07-31 00:11:26'),(2,'INV-20260731-1509','',450.00,0.00,450.00,'Cash',NULL,'2026-07-31 00:36:41'),(3,'INV-20260731-1543','lala',100.00,0.00,100.00,'Cash',NULL,'2026-07-31 13:05:13'),(4,'INV-20260731-5600','kikm',100.00,0.00,100.00,'Cash',NULL,'2026-07-31 13:05:59'),(5,'INV-20260731-3046','jane',4900.00,0.00,4900.00,'Cash',NULL,'2026-07-31 13:06:43'),(6,'INV-20260802-7249','jane',750.00,0.00,750.00,'Cash',NULL,'2026-08-02 17:12:21'),(7,'INV-20260802-9179','jane',200.00,0.00,200.00,'Cash',NULL,'2026-08-02 20:23:37'),(8,'INV-20260802-3436','lala',250.00,0.00,250.00,'Cash',NULL,'2026-08-02 20:23:50'),(9,'INV-20260802-7033','jin',200.00,0.00,200.00,'Cash',NULL,'2026-08-02 20:24:06'),(10,'INV-20260802-2633','jin',200.00,0.00,200.00,'Cash',NULL,'2026-08-02 20:24:21'),(11,'INV-20260802-9395','lala',200.00,0.00,200.00,'Cash',NULL,'2026-08-02 20:37:40'),(15,'INV-20260804-1772','lala',1400.00,0.00,1400.00,'Cash',NULL,'2026-08-04 14:45:32'),(16,'INV-20260804-6833','jin',1030.00,0.00,1030.00,'Cash',NULL,'2026-08-04 14:46:02'),(17,'INV-20260804-6904','jane',1200.00,0.00,1200.00,'Cash',NULL,'2026-08-04 15:59:24'),(18,'INV-20260804-7619','jin',1200.00,0.00,1200.00,'Cash',NULL,'2026-08-04 16:00:07'),(19,'INV-20260804-1702','jane',750.00,0.00,750.00,'Cash',NULL,'2026-08-04 16:00:39'),(20,'INV-20260806-1872','kikm',200.00,0.00,200.00,'Cash',NULL,'2026-08-06 11:19:41'),(21,'INV-20260807-5266','lala',30.00,0.00,30.00,'Cash',NULL,'2026-08-07 16:38:37'),(22,'INV-20260810-1946','car',250.00,0.00,250.00,'Cash',2,'2026-08-10 18:47:30'),(23,'INV-20260810-7129','car',100.00,0.00,100.00,'Cash',2,'2026-08-10 18:49:00'),(24,'INV-20260810-9551','vin',111.00,0.00,111.00,'Cash',2,'2026-08-10 23:01:31'),(25,'INV-20260811-6471','jane',30.00,0.00,30.00,'Cash',2,'2026-08-11 17:25:16'),(26,'INV-20260811-4818','jane',200.00,10.00,190.00,'Cash',2,'2026-08-11 17:25:42'),(27,'INV-20260814-7404','car',250.00,0.00,250.00,'Cash',2,'2026-08-14 21:38:05');
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_items`
--

LOCK TABLES `sale_items` WRITE;
/*!40000 ALTER TABLE `sale_items` DISABLE KEYS */;
INSERT INTO `sale_items` VALUES (1,1,35,1,100.00,100.00),(2,2,23,1,200.00,200.00),(3,2,13,1,250.00,250.00),(4,3,35,1,100.00,100.00),(5,4,35,1,100.00,100.00),(6,5,35,49,100.00,4900.00),(7,6,40,1,750.00,750.00),(8,7,22,1,200.00,200.00),(9,8,13,1,250.00,250.00),(10,9,25,1,200.00,200.00),(11,10,24,1,200.00,200.00),(12,11,23,1,200.00,200.00),(16,15,26,7,200.00,1400.00),(17,16,38,1,30.00,30.00),(18,16,41,1,1000.00,1000.00),(19,17,33,1,1200.00,1200.00),(20,18,33,1,1200.00,1200.00),(21,19,40,1,750.00,750.00),(22,20,23,1,200.00,200.00),(23,21,37,1,30.00,30.00),(24,22,15,1,250.00,250.00),(25,23,35,1,100.00,100.00),(26,24,11,1,111.00,111.00),(27,25,37,1,30.00,30.00),(28,26,23,1,200.00,200.00),(29,27,14,1,250.00,250.00);
/*!40000 ALTER TABLE `sale_items` ENABLE KEYS */;
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
  `processed_by` int(11) DEFAULT NULL,
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
  CONSTRAINT `fk_return_user` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `returns`
--

LOCK TABLES `returns` WRITE;
/*!40000 ALTER TABLE `returns` DISABLE KEYS */;
INSERT INTO `returns` VALUES (2,'SRET-20260802-6005',NULL,33,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',NULL,'2026-08-02 00:00:00','2026-08-02 10:45:53','2026-08-02 10:45:53'),(3,'SRET-20260802-8405',NULL,22,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',NULL,'2026-08-02 00:00:00','2026-08-02 13:11:36','2026-08-02 13:11:36'),(4,'SRET-20260802-8387',NULL,41,'ABC Trading','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',NULL,'2026-08-02 00:00:00','2026-08-02 13:11:53','2026-08-02 13:11:53'),(5,'SRET-20260802-6250',NULL,14,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',NULL,'2026-08-02 00:00:00','2026-08-02 13:12:17','2026-08-02 13:12:17'),(10,'SRET-20260802-6148',NULL,12,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',NULL,'2026-08-02 00:00:00','2026-08-02 13:25:38','2026-08-02 13:25:38'),(13,'SRET-20260802-2747',NULL,11,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',NULL,'2026-08-02 00:00:00','2026-08-02 13:34:05','2026-08-02 13:34:05'),(14,'SRET-20260802-2747',NULL,24,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',NULL,'2026-08-02 00:00:00','2026-08-02 13:34:05','2026-08-02 13:34:05'),(15,'SRET-20260802-9225',NULL,34,'ABC Company','',2,'Supplier Delivered Wrong Item','Returned',NULL,'',NULL,'2026-08-02 22:10:00','2026-08-02 14:10:40','2026-08-02 14:10:40'),(16,'SRET-20260802-9225',NULL,33,'ABC Company','',2,'Supplier Delivered Wrong Item','Returned',NULL,'',NULL,'2026-08-02 22:10:00','2026-08-02 14:10:40','2026-08-02 14:10:40'),(17,'SRET-20260802-9225',NULL,32,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',NULL,'2026-08-02 22:10:00','2026-08-02 14:10:40','2026-08-02 14:10:40'),(18,'SRET-20260802-9225',NULL,31,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',NULL,'2026-08-02 22:10:00','2026-08-02 14:10:40','2026-08-02 14:10:40'),(19,'SRET-20260802-6270',NULL,33,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',NULL,'2026-08-02 22:10:00','2026-08-02 14:10:50','2026-08-02 14:10:50'),(20,'SRET-20260802-3477',NULL,35,'ABC','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',NULL,'2026-08-02 22:10:00','2026-08-02 14:11:07','2026-08-02 14:11:07'),(21,'SRET-20260802-8444',NULL,32,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',NULL,'2026-08-02 22:11:00','2026-08-02 14:11:21','2026-08-02 14:11:21'),(22,'SRET-20260802-3912',NULL,40,'ISU Supplier','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',NULL,'2026-08-02 22:11:00','2026-08-02 14:11:27','2026-08-02 14:11:27'),(23,'SRET-20260804-8632',NULL,35,'ABC','',4,'Supplier Delivered Wrong Item','Returned',NULL,'',NULL,'2026-08-04 14:22:00','2026-08-04 06:23:09','2026-08-04 06:23:09'),(24,'SRET-20260806-2808',NULL,24,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',NULL,'2026-08-06 11:19:00','2026-08-06 03:19:54','2026-08-06 03:19:54'),(25,'SRET-20260806-2808',NULL,23,'ABC Company','',1,'Supplier Delivered Wrong Item','Returned',NULL,'',NULL,'2026-08-06 11:19:00','2026-08-06 03:19:54','2026-08-06 03:19:54');
/*!40000 ALTER TABLE `returns` ENABLE KEYS */;
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
  CONSTRAINT `inventory_transactions_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_transactions`
--

LOCK TABLES `inventory_transactions` WRITE;
/*!40000 ALTER TABLE `inventory_transactions` DISABLE KEYS */;
INSERT INTO `inventory_transactions` VALUES (1,12,'Stock In',1,11,12,'0','Received from Delivery',NULL,'2026-07-30 21:06:55'),(2,38,'Stock In',10,50,60,'0','Received from Delivery',NULL,'2026-07-30 22:20:37'),(3,38,'Stock In',1,60,61,'0','Received from Delivery',NULL,'2026-07-30 22:54:17'),(4,35,'Stock In',1,50,51,'0','Received from Delivery',NULL,'2026-07-30 22:54:17'),(5,36,'Stock In',1,50,51,'0','Received from Delivery',NULL,'2026-07-30 22:54:17'),(6,19,'Stock In',2,20,22,'0','Received from Delivery',NULL,'2026-07-30 22:54:17'),(7,27,'Stock In',1,20,21,'0','Received from Delivery',NULL,'2026-07-30 22:54:17'),(8,20,'Stock In',1,20,21,'0','Received from Delivery',NULL,'2026-07-30 23:24:02'),(9,28,'Stock In',1,20,21,'0','Received from Delivery',NULL,'2026-07-30 23:24:02'),(10,12,'Stock In',1,12,13,'0','Received from Delivery',NULL,'2026-07-30 23:24:02'),(11,37,'Stock In',1,50,51,'0','Received from Delivery',NULL,'2026-07-30 23:24:02'),(12,36,'Stock In',1,51,52,'0','Received from Delivery',NULL,'2026-07-30 23:24:02'),(13,38,'Stock In',1,61,62,'0','Received from Delivery',NULL,'2026-07-30 23:24:02'),(14,35,'Stock In',1,51,52,'0','Received from Delivery',NULL,'2026-07-30 23:24:02'),(15,36,'Stock In',1,52,53,'0','Received from Delivery',NULL,'2026-07-31 00:41:38'),(16,35,'Stock In',5,0,5,'0','Received from Delivery',NULL,'2026-07-31 13:10:39'),(17,38,'Stock In',1,62,63,'0','Received from Delivery',NULL,'2026-08-02 00:28:40'),(18,32,'Stock In',1,20,21,'0','Received from Delivery',NULL,'2026-08-02 11:09:20'),(19,32,'Stock In',1,21,22,'0','Received from Delivery',NULL,'2026-08-02 11:15:15'),(20,38,'Stock In',1,63,64,'0','Received from Delivery',NULL,'2026-08-02 11:16:22'),(21,41,'Stock In',1,1,2,'0','Received from Delivery',NULL,'2026-08-02 11:16:22'),(22,32,'Stock In',2,22,24,'0','Received from Delivery',NULL,'2026-08-02 11:17:39'),(23,31,'Stock In',1,20,21,'0','Received from Delivery',NULL,'2026-08-02 11:17:39'),(24,33,'Stock In',3,10,13,'0','Received from Delivery',NULL,'2026-08-02 11:17:39'),(25,32,'Stock In',1,24,25,'0','Received from Delivery',NULL,'2026-08-02 11:42:55'),(26,31,'Stock In',2,21,23,'0','Received from Delivery',NULL,'2026-08-02 11:42:55'),(27,33,'Stock In',1,13,14,'0','Received from Delivery',NULL,'2026-08-02 11:42:55'),(28,38,'Stock In',1,64,65,'0','Received from Delivery',NULL,'2026-08-02 17:02:02');
/*!40000 ALTER TABLE `inventory_transactions` ENABLE KEYS */;
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
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-18 14:37:46
