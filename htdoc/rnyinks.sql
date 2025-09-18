-- MySQL dump 10.13  Distrib 8.0.43, for Linux (x86_64)
--
-- Host: localhost    Database: rnyinks
-- ------------------------------------------------------
-- Server version	8.0.43-0ubuntu0.24.04.1

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
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Pending','Received','Shipped','Delivered','Cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_items`
--

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
INSERT INTO `cart_items` VALUES (4,3,16,'Faber-Castell Loom',45.00,2,'images/faber_looom.jpg','Pending','2025-08-25 02:34:35','2025-08-25 05:43:29'),(18,3,15,'Kaweco Sport Classic',28.00,1,'images/kaweco_sport.jpg','Pending','2025-08-25 05:43:27','2025-08-25 05:43:27'),(20,3,61,'Faber-Castell Loom',45.00,1,'images/faber_looom.jpg','Pending','2025-08-25 05:43:28','2025-08-25 05:43:28'),(28,3,83,'Diamine Ancient Copper',15.00,1,'images/diamine_ancientcopper.jpg','Pending','2025-08-25 05:43:41','2025-08-25 05:43:41'),(29,3,99,'Clairefontaine Dotted Notebook',12.00,1,'images/clairefontaine_dotted.jpg','Pending','2025-08-25 05:43:45','2025-08-25 05:43:45'),(30,6,61,'Faber-Castell Loom',45.00,1,'images/faber_looom.jpg','Pending','2025-08-25 05:46:37','2025-08-25 05:46:37');
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collections`
--

DROP TABLE IF EXISTS `collections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `collections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` decimal(10,2) DEFAULT '0.00',
  `stock` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_brand` (`brand`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collections`
--

LOCK TABLES `collections` WRITE;
/*!40000 ALTER TABLE `collections` DISABLE KEYS */;
INSERT INTO `collections` VALUES (1,'Calvin Klein Collection','Modern and minimalist fragrances','../assets/ck-collection.jpg','Calvin Klein',1,'2025-08-24 12:34:07','2025-08-24 12:34:07',0.00,0),(2,'Chanel Classics','Timeless and elegant French perfumes','../assets/chanel-collection.jpg','Chanel',1,'2025-08-24 12:34:07','2025-08-24 12:34:07',0.00,0),(3,'Seasonal Favorites','Fragrances for every season','../assets/seasonal-collection.jpg','Various',1,'2025-08-24 12:34:07','2025-08-24 12:34:07',0.00,0),(4,'Calvin Klein Collection','Modern and minimalist fragrances','../assets/ck-collection.jpg','Calvin Klein',1,'2025-08-24 12:35:35','2025-08-24 12:35:35',0.00,0),(5,'Chanel Classics','Timeless and elegant French perfumes','../assets/chanel-collection.jpg','Chanel',1,'2025-08-24 12:35:35','2025-08-24 12:35:35',0.00,0),(6,'Seasonal Favorites','Fragrances for every season','../assets/seasonal-collection.jpg','Various',1,'2025-08-24 12:35:35','2025-08-24 12:35:35',0.00,0);
/*!40000 ALTER TABLE `collections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `season_id` int DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `is_persistent` tinyint(1) NOT NULL DEFAULT '0',
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `idx_user_season` (`user_id`,`season_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=213 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (5,9,13,NULL,'Lamy Safari is now 20.00% off!','collections.php?view_product=13',0,1,'2025-08-31 01:15:48'),(10,9,13,NULL,'Lamy Safari is now 20.00% off!','collections.php?view_product=13',0,1,'2025-08-31 01:20:53'),(11,9,13,NULL,'Lamy Safari is now 20.00% off!','collections.php?view_product=13',0,1,'2025-08-31 01:20:57'),(12,9,13,NULL,'Lamy Safari is now 20.00% off!','collections.php?view_product=13',0,1,'2025-08-31 01:21:00'),(13,9,13,NULL,'Lamy Safari is now 20.00% off!','collections.php?view_product=13',0,1,'2025-08-31 01:21:33'),(22,9,14,12,'Platinum Century 3776 is now 10.00% off!','collections.php?view_product=14',0,1,'2025-08-31 01:24:48'),(23,9,15,12,'Kaweco Sport Classic is now 10.00% off!','collections.php?view_product=15',0,1,'2025-08-31 01:24:48'),(24,9,16,12,'Faber-Castell Loom is now 10.00% off!','collections.php?view_product=16',0,1,'2025-08-31 01:24:48'),(25,9,21,12,'Aurora Optima is now 10.00% off!','collections.php?view_product=21',0,1,'2025-08-31 01:24:48'),(26,9,12,14,'Pilot Custom 74 is now 20.00% off!','collections.php?view_product=12',0,1,'2025-08-31 01:24:48'),(27,9,13,14,'Lamy Safari is now 20.00% off!','collections.php?view_product=13',0,1,'2025-08-31 01:24:48'),(117,9,13,11,'Lamy Safari is now 14.00% off!','collections.php?view_product=13',0,1,'2025-08-31 01:54:16'),(118,9,14,11,'Platinum Century 3776 is now 14.00% off!','collections.php?view_product=14',0,1,'2025-08-31 01:54:16'),(121,9,21,11,'Aurora Optima is now 14.00% off!','collections.php?view_product=21',0,1,'2025-08-31 01:54:16'),(194,9,12,15,'Pilot Custom 74 is now 21.00% off!','collections.php?view_product=12',0,1,'2025-08-31 01:54:27'),(195,9,13,15,'Lamy Safari is now 21.00% off!','collections.php?view_product=13',0,1,'2025-08-31 01:54:27'),(196,9,14,15,'Platinum Century 3776 is now 21.00% off!','collections.php?view_product=14',0,1,'2025-08-31 01:54:27'),(197,9,15,15,'Kaweco Sport Classic is now 21.00% off!','collections.php?view_product=15',0,1,'2025-08-31 01:54:27'),(198,9,16,15,'Faber-Castell Loom is now 21.00% off!','collections.php?view_product=16',0,1,'2025-08-31 01:54:27'),(199,9,21,15,'Aurora Optima is now 21.00% off!','collections.php?view_product=21',0,1,'2025-08-31 01:54:27'),(200,9,12,16,'Pilot Custom 74 is now 25.00% off!','collections.php?view_product=12',0,1,'2025-08-31 02:32:34'),(201,9,13,16,'Lamy Safari is now 25.00% off!','collections.php?view_product=13',0,1,'2025-08-31 02:32:34'),(202,9,14,16,'Platinum Century 3776 is now 25.00% off!','collections.php?view_product=14',0,1,'2025-08-31 02:32:34'),(203,9,15,16,'Kaweco Sport Classic is now 25.00% off!','collections.php?view_product=15',0,1,'2025-08-31 02:32:34'),(204,9,16,16,'Faber-Castell Loom is now 25.00% off!','collections.php?view_product=16',0,1,'2025-08-31 02:32:34'),(205,9,21,16,'Aurora Optima is now 25.00% off!','collections.php?view_product=21',0,1,'2025-08-31 02:32:34'),(206,9,13,17,'Lamy Safari is now 25.00% off!','collections.php?view_product=13',0,1,'2025-08-31 02:45:46'),(207,9,13,18,'Lamy Safari is now 25.00% off!','collections.php?view_product=13',0,1,'2025-08-31 02:55:44'),(208,9,13,19,'Lamy Safari is now 25.00% off!','collections.php?view_product=13',0,1,'2025-08-31 03:03:34'),(209,9,13,20,'Lamy Safari is now 10.00% off!','collections.php?view_product=13',0,1,'2025-09-01 03:14:54'),(210,9,26,NULL,'Your Order #9 is being packed for delivery.','track_order.php?id=9',1,1,'2025-09-01 04:26:04'),(211,9,55,NULL,'Your Order #10 is being packed for delivery.','track_order.php?id=10',1,1,'2025-09-01 05:11:57'),(212,9,56,NULL,'Your Order #11 is being packed for delivery.','track_order.php?id=11',1,1,'2025-09-01 05:13:39');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_applied` decimal(10,2) NOT NULL DEFAULT '0.00',
  `quantity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,61,'Faber-Castell Loom',45.00,0.00,1),(2,1,16,'Faber-Castell Loom',45.00,0.00,1),(3,2,16,'Faber-Castell Loom',45.00,0.00,1),(4,3,61,'Faber-Castell Loom',45.00,0.00,1),(5,4,16,'Faber-Castell Loom',45.00,0.00,1),(6,5,61,'Faber-Castell Loom',45.00,0.00,1),(7,6,61,'Faber-Castell Loom',45.00,0.00,1),(8,6,16,'Faber-Castell Loom',45.00,0.00,1),(9,6,60,'Kaweco Sport Classic',28.00,0.00,1),(10,6,15,'Kaweco Sport Classic',28.00,0.00,1),(11,6,58,'Lamy Safari',35.00,0.00,1),(12,6,13,'Lamy Safari',35.00,0.00,1),(13,6,83,'Diamine Ancient Copper',15.00,0.00,1),(14,6,38,'Diamine Ancient Copper',15.00,0.00,1),(15,6,84,'KWZ Honey',20.00,0.00,1),(16,6,39,'KWZ Honey',20.00,0.00,1),(17,6,99,'Clairefontaine Dotted Notebook',12.00,0.00,1),(18,6,54,'Clairefontaine Dotted Notebook',12.00,0.00,1),(19,6,56,'Dingbats Wildlife Dotted',28.00,0.00,1),(20,6,101,'Dingbats Wildlife Dotted',28.00,0.00,1),(21,7,15,'Kaweco Sport Classic',28.00,0.00,1),(22,7,61,'Faber-Castell Loom',45.00,0.00,1),(23,8,63,'Pelikan M200',160.00,0.00,1),(24,8,61,'Faber-Castell Loom',45.00,0.00,1),(25,8,15,'Kaweco Sport Classic',28.00,0.00,1),(26,8,58,'Lamy Safari',35.00,0.00,1),(27,8,13,'Lamy Safari',35.00,0.00,1),(28,8,57,'Pilot Custom 74',120.00,0.00,1),(29,8,14,'Platinum Century 3776',150.00,0.00,1);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `shipping_cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `verification_code` varchar(8) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `delivery_status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'pending_verification',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `address` text COLLATE utf8mb4_general_ci NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,5,90.00,0.00,'pending',NULL,'pending_verification','2025-08-25 05:20:50','asdasda','cod'),(2,5,45.00,0.00,'pending',NULL,'pending_verification','2025-08-25 05:23:58','2131231','cod'),(3,5,45.00,0.00,'pending',NULL,'pending_verification','2025-08-25 05:26:39','asdas','cod'),(4,5,45.00,0.00,'pending',NULL,'pending_verification','2025-08-25 05:29:41','adsasda','cod'),(5,5,45.00,0.00,'pending',NULL,'pending_verification','2025-08-25 05:34:22','1231','cod'),(6,6,366.00,0.00,'pending',NULL,'pending_verification','2025-08-25 05:45:42','Andromeda galaxy','cod'),(7,7,73.00,0.00,'pending',NULL,'pending_verification','2025-08-25 08:19:13','Mistubushi, Masbate','card'),(8,5,573.00,0.00,'pending',NULL,'pending_verification','2025-08-28 03:16:41','xZxZZ','cod'),(9,9,112.50,5.00,'Processing','M1tOQgaD','packing','2025-09-01 04:26:04','743 N. Dogwood Ave.\r\nWest Lafayette, IN 47906','0'),(10,9,45.00,20.00,'Processing','h3X8kf07','packing','2025-09-01 05:11:57','743 N. Dogwood Ave.\r\nWest Lafayette, IN 47906','0'),(11,9,33.00,5.00,'Processing','8xIBFsp6','packing','2025-09-01 05:13:39','743 N. Dogwood Ave.\r\nWest Lafayette, IN 47906','0');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_seasons`
--

DROP TABLE IF EXISTS `product_seasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_seasons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `discount_percentage` decimal(5,2) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_seasons_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_seasons`
--

LOCK TABLES `product_seasons` WRITE;
/*!40000 ALTER TABLE `product_seasons` DISABLE KEYS */;
INSERT INTO `product_seasons` VALUES (1,12,15.00,'2025-08-30 15:51:00','2025-08-30 16:11:00'),(2,27,20.00,'2025-08-30 15:51:00','2025-08-30 16:11:00'),(3,42,10.00,'2025-08-30 15:51:00','2025-08-30 16:11:00'),(4,19,25.00,'2025-08-30 16:11:00','2025-08-30 16:31:00'),(5,33,10.00,'2025-08-30 16:11:00','2025-08-30 16:31:00'),(6,53,15.00,'2025-08-30 16:11:00','2025-08-30 16:31:00'),(7,24,30.00,'2025-08-30 16:31:00','2025-08-30 16:51:00'),(8,38,15.00,'2025-08-30 16:31:00','2025-08-30 16:51:00'),(9,48,10.00,'2025-08-30 16:31:00','2025-08-30 16:51:00'),(10,22,10.00,'2025-08-31 07:11:00','2025-08-31 07:35:00'),(11,26,14.00,'2025-08-31 07:11:00','2025-09-07 00:00:00'),(12,29,10.00,'2025-08-31 07:11:00','2025-09-07 00:00:00'),(13,13,20.00,'2025-08-31 07:45:00','2025-08-31 07:55:59'),(14,13,20.00,'2025-08-31 08:00:29','2025-08-31 07:55:59'),(15,13,21.00,'2025-08-31 09:53:29','2025-08-31 07:55:59'),(16,13,25.00,'2025-08-31 10:32:29','2025-08-31 07:55:59'),(17,13,25.00,'2025-08-31 10:45:29','2025-08-31 07:55:59'),(18,13,25.00,'2025-08-31 10:55:29','2025-08-31 07:55:59'),(19,13,25.00,'2025-08-31 10:55:29','2025-08-31 07:55:59'),(20,13,10.00,'2025-09-01 11:14:29','2025-09-07 00:00:00');
/*!40000 ALTER TABLE `product_seasons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock_quantity` int DEFAULT '0',
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subcategory` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('men','women','unisex') COLLATE utf8mb4_unicode_ci DEFAULT 'unisex',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_category` (`category`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (12,'Pilot Custom 74','Classic cartridge/converter fountain pen.',120.00,'images/pilot_custom74.jpg',15,'Fountain Pens','Cartridge_Converter','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(13,'Lamy Safari','Durable everyday fountain pen.',35.00,'images/lamy_safari.jpg',30,'Fountain Pens','Cartridge_Converter','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(14,'Platinum Century 3776','Premium Japanese cartridge/converter pen.',150.00,'images/platinum_3776.jpg',12,'Fountain Pens','Cartridge_Converter','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(15,'Kaweco Sport Classic','Compact German-made fountain pen.',28.00,'images/kaweco_sport.jpg',25,'Fountain Pens','Cartridge_Converter','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(16,'Faber-Castell Loom','Sleek everyday writer.',45.00,'images/faber_looom.jpg',20,'Fountain Pens','Cartridge_Converter','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(17,'TWSBI Eco','Affordable piston filler fountain pen.',32.00,'images/twsbi_eco.jpg',25,'Fountain Pens','Piston_Filler','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(18,'Pelikan M200','High-quality German piston filler.',160.00,'images/pelikan_m200.jpg',10,'Fountain Pens','Piston_Filler','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(19,'Montblanc 146','Luxury piston filler pen.',750.00,'images/montblanc_146.jpg',5,'Fountain Pens','Piston_Filler','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(20,'Lamy 2000','Modern Bauhaus classic piston filler.',200.00,'images/lamy2000.jpg',8,'Fountain Pens','Piston_Filler','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(21,'Aurora Optima','Italian handcrafted piston filler.',480.00,'images/aurora_optima.jpg',6,'Fountain Pens','Piston_Filler','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(22,'Pilot Custom 823','Vacuum-filling premium fountain pen.',320.00,'images/pilot_custom823.jpg',8,'Fountain Pens','Vacuum_Filler','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(23,'TWSBI Vac700R','Affordable vacuum filler.',80.00,'images/twsbi_vac700r.jpg',20,'Fountain Pens','Vacuum_Filler','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(24,'Visconti Homo Sapiens','Italian luxury vacuum filler pen.',780.00,'images/visconti_hs.jpg',4,'Fountain Pens','Vacuum_Filler','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(25,'Conid Bulkfiller Regular','Unique Belgian bulkfiller system.',650.00,'images/conid_bulkfiller.jpg',3,'Fountain Pens','Vacuum_Filler','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(26,'Wing Sung 699','Budget-friendly vacuum filler pen.',25.00,'images/wingsung_699.jpg',50,'Fountain Pens','Vacuum_Filler','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(27,'Diamine Shimmering Seas','Blue ink with gold shimmer.',18.00,'images/diamine_shimmering_seas.jpg',25,'Ink','Shimmering','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(28,'J. Herbin 1670 Emerald of Chivor','Green ink with shimmer and sheen.',25.00,'images/jherbin_emerald.jpg',20,'Ink','Shimmering','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(29,'Diamine Golden Sands','Golden shimmering fountain pen ink.',18.00,'images/diamine_golden_sands.jpg',25,'Ink','Shimmering','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(30,'Robert Oster Heart of Gold','Warm golden shimmer ink.',20.00,'images/ro_heartgold.jpg',15,'Ink','Shimmering','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(31,'De Atramentis Pearlescent Blue','Pearlescent shimmering ink.',22.00,'images/deatramentis_blue.jpg',18,'Ink','Shimmering','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(32,'Robert Oster Fire & Ice','Blue ink with strong red sheen.',17.00,'images/robertoster_fireice.jpg',30,'Ink','Sheening','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(33,'Diamine Majestic Blue','Deep blue ink with heavy red sheen.',15.00,'images/diamine_majesticblue.jpg',40,'Ink','Sheening','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(34,'Pilot Iroshizuku Yama-budo','Wine-colored ink with sheen.',28.00,'images/iroshizuku_yamabudo.jpg',18,'Ink','Sheening','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(35,'Sailor Studio 123','Unique gray-purple ink with sheen.',25.00,'images/sailor123.jpg',20,'Ink','Sheening','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(36,'Organics Studio Nitrogen','Extreme sheening blue ink.',22.00,'images/os_nitrogen.jpg',12,'Ink','Sheening','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(37,'Sailor Manyo Haha','Multi-toned shading ink.',22.00,'images/sailor_haha.jpg',15,'Ink','Shading','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(38,'Diamine Ancient Copper','Rich copper with strong shading.',15.00,'images/diamine_ancientcopper.jpg',30,'Ink','Shading','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(39,'KWZ Honey','Unique honey-colored shading ink.',20.00,'images/kwz_honey.jpg',12,'Ink','Shading','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(40,'Noodler’s Apache Sunset','Famous orange shading ink.',16.00,'images/noodlers_apache.jpg',25,'Ink','Shading','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(41,'Pelikan Edelstein Olivine','Olive green with shading.',28.00,'images/pelikan_olivine.jpg',14,'Ink','Shading','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(42,'Rhodia A5 Lined Notebook','Premium smooth lined paper.',12.00,'images/rhodia_a5_lined.jpg',50,'Paper','Lined','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(43,'Leuchtturm1917 A5 Lined','High-quality lined journal.',20.00,'images/leuchtturm_lined.jpg',40,'Paper','Lined','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(44,'Clairefontaine A5 Lined','Smooth lined writing paper.',10.00,'images/clairefontaine_lined.jpg',60,'Paper','Lined','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(45,'Apica Premium C.D. Notebook','Japanese quality lined paper.',14.00,'images/apica_lined.jpg',30,'Paper','Lined','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(46,'Maruman Mnemosyne Lined','Professional lined notepad.',15.00,'images/mnemosyne_lined.jpg',25,'Paper','Lined','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(47,'Clairefontaine Grid Notebook','Smooth grid-ruled paper.',10.00,'images/clairefontaine_grid.jpg',60,'Paper','Grid','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(48,'Rhodia A4 Grid Pad','Classic grid-ruled writing pad.',9.00,'images/rhodia_grid.jpg',70,'Paper','Grid','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(49,'Leuchtturm1917 Grid Journal','Grid notebook for organization.',22.00,'images/leuchtturm_grid.jpg',35,'Paper','Grid','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(50,'Apica Grid Notebook','Japanese smooth grid paper.',11.00,'images/apica_grid.jpg',40,'Paper','Grid','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(51,'Maruman Grid Notepad','Grid ruled pad from Japan.',13.00,'images/maruman_grid.jpg',28,'Paper','Grid','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(52,'Rhodia DotPad A5','Dotted notepad for versatility.',9.00,'images/rhodia_dotpad.jpg',70,'Paper','Dotted','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(53,'Leuchtturm1917 Dotted Journal','Dotted bullet journal.',22.00,'images/leuchtturm_dotted.jpg',35,'Paper','Dotted','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(54,'Clairefontaine Dotted Notebook','Smooth dotted French paper.',12.00,'images/clairefontaine_dotted.jpg',40,'Paper','Dotted','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(55,'Scribbles That Matter Dotted','Bullet journaling dotted notebook.',25.00,'images/stm_dotted.jpg',30,'Paper','Dotted','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(56,'Dingbats Wildlife Dotted','Eco-friendly dotted journal.',28.00,'images/dingbats_dotted.jpg',20,'Paper','Dotted','unisex',1,'2025-08-25 02:29:24','2025-08-25 02:29:24'),(57,'Pilot Custom 74','Classic cartridge/converter fountain pen.',120.00,'images/pilot_custom74.jpg',15,'Fountain Pens','Cartridge_Converter','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(58,'Lamy Safari','Durable everyday fountain pen.',35.00,'images/lamy_safari.jpg',30,'Fountain Pens','Cartridge_Converter','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(59,'Platinum Century 3776','Premium Japanese cartridge/converter pen.',150.00,'images/platinum_3776.jpg',12,'Fountain Pens','Cartridge_Converter','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(60,'Kaweco Sport Classic','Compact German-made fountain pen.',28.00,'images/kaweco_sport.jpg',25,'Fountain Pens','Cartridge_Converter','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(61,'Faber-Castell Loom','Sleek everyday writer.',45.00,'images/faber_looom.jpg',20,'Fountain Pens','Cartridge_Converter','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(62,'TWSBI Eco','Affordable piston filler fountain pen.',32.00,'images/twsbi_eco.jpg',25,'Fountain Pens','Piston_Filler','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(63,'Pelikan M200','High-quality German piston filler.',160.00,'images/pelikan_m200.jpg',10,'Fountain Pens','Piston_Filler','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(64,'Montblanc 146','Luxury piston filler pen.',750.00,'images/montblanc_146.jpg',5,'Fountain Pens','Piston_Filler','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(65,'Lamy 2000','Modern Bauhaus classic piston filler.',200.00,'images/lamy2000.jpg',8,'Fountain Pens','Piston_Filler','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(66,'Aurora Optima','Italian handcrafted piston filler.',480.00,'images/aurora_optima.jpg',6,'Fountain Pens','Piston_Filler','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(67,'Pilot Custom 823','Vacuum-filling premium fountain pen.',320.00,'images/pilot_custom823.jpg',8,'Fountain Pens','Vacuum_Filler','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(68,'TWSBI Vac700R','Affordable vacuum filler.',80.00,'images/twsbi_vac700r.jpg',20,'Fountain Pens','Vacuum_Filler','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(69,'Visconti Homo Sapiens','Italian luxury vacuum filler pen.',780.00,'images/visconti_hs.jpg',4,'Fountain Pens','Vacuum_Filler','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(70,'Conid Bulkfiller Regular','Unique Belgian bulkfiller system.',650.00,'images/conid_bulkfiller.jpg',3,'Fountain Pens','Vacuum_Filler','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(71,'Wing Sung 699','Budget-friendly vacuum filler pen.',25.00,'images/wingsung_699.jpg',50,'Fountain Pens','Vacuum_Filler','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(72,'Diamine Shimmering Seas','Blue ink with gold shimmer.',18.00,'images/diamine_shimmering_seas.jpg',25,'Ink','Shimmering','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(73,'J. Herbin 1670 Emerald of Chivor','Green ink with shimmer and sheen.',25.00,'images/jherbin_emerald.jpg',20,'Ink','Shimmering','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(74,'Diamine Golden Sands','Golden shimmering fountain pen ink.',18.00,'images/diamine_golden_sands.jpg',25,'Ink','Shimmering','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(75,'Robert Oster Heart of Gold','Warm golden shimmer ink.',20.00,'images/ro_heartgold.jpg',15,'Ink','Shimmering','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(76,'De Atramentis Pearlescent Blue','Pearlescent shimmering ink.',22.00,'images/deatramentis_blue.jpg',18,'Ink','Shimmering','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(77,'Robert Oster Fire & Ice','Blue ink with strong red sheen.',17.00,'images/robertoster_fireice.jpg',30,'Ink','Sheening','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(78,'Diamine Majestic Blue','Deep blue ink with heavy red sheen.',15.00,'images/diamine_majesticblue.jpg',40,'Ink','Sheening','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(79,'Pilot Iroshizuku Yama-budo','Wine-colored ink with sheen.',28.00,'images/iroshizuku_yamabudo.jpg',18,'Ink','Sheening','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(80,'Sailor Studio 123','Unique gray-purple ink with sheen.',25.00,'images/sailor123.jpg',20,'Ink','Sheening','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(81,'Organics Studio Nitrogen','Extreme sheening blue ink.',22.00,'images/os_nitrogen.jpg',12,'Ink','Sheening','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(82,'Sailor Manyo Haha','Multi-toned shading ink.',22.00,'images/sailor_haha.jpg',15,'Ink','Shading','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(83,'Diamine Ancient Copper','Rich copper with strong shading.',15.00,'images/diamine_ancientcopper.jpg',30,'Ink','Shading','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(84,'KWZ Honey','Unique honey-colored shading ink.',20.00,'images/kwz_honey.jpg',12,'Ink','Shading','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(85,'Noodler’s Apache Sunset','Famous orange shading ink.',16.00,'images/noodlers_apache.jpg',25,'Ink','Shading','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(86,'Pelikan Edelstein Olivine','Olive green with shading.',28.00,'images/pelikan_olivine.jpg',14,'Ink','Shading','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(87,'Rhodia A5 Lined Notebook','Premium smooth lined paper.',12.00,'images/rhodia_a5_lined.jpg',50,'Paper','Lined','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(88,'Leuchtturm1917 A5 Lined','High-quality lined journal.',20.00,'images/leuchtturm_lined.jpg',40,'Paper','Lined','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(89,'Clairefontaine A5 Lined','Smooth lined writing paper.',10.00,'images/clairefontaine_lined.jpg',60,'Paper','Lined','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(90,'Apica Premium C.D. Notebook','Japanese quality lined paper.',14.00,'images/apica_lined.jpg',30,'Paper','Lined','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(91,'Maruman Mnemosyne Lined','Professional lined notepad.',15.00,'images/mnemosyne_lined.jpg',25,'Paper','Lined','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(92,'Clairefontaine Grid Notebook','Smooth grid-ruled paper.',10.00,'images/clairefontaine_grid.jpg',60,'Paper','Grid','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(93,'Rhodia A4 Grid Pad','Classic grid-ruled writing pad.',9.00,'images/rhodia_grid.jpg',70,'Paper','Grid','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(94,'Leuchtturm1917 Grid Journal','Grid notebook for organization.',22.00,'images/leuchtturm_grid.jpg',35,'Paper','Grid','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(95,'Apica Grid Notebook','Japanese smooth grid paper.',11.00,'images/apica_grid.jpg',40,'Paper','Grid','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(96,'Maruman Grid Notepad','Grid ruled pad from Japan.',13.00,'images/maruman_grid.jpg',28,'Paper','Grid','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(97,'Rhodia DotPad A5','Dotted notepad for versatility.',9.00,'images/rhodia_dotpad.jpg',70,'Paper','Dotted','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(98,'Leuchtturm1917 Dotted Journal','Dotted bullet journal.',22.00,'images/leuchtturm_dotted.jpg',35,'Paper','Dotted','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(99,'Clairefontaine Dotted Notebook','Smooth dotted French paper.',12.00,'images/clairefontaine_dotted.jpg',40,'Paper','Dotted','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(100,'Scribbles That Matter Dotted','Bullet journaling dotted notebook.',25.00,'images/stm_dotted.jpg',30,'Paper','Dotted','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26'),(101,'Dingbats Wildlife Dotted','Eco-friendly dotted journal.',28.00,'images/dingbats_dotted.jpg',20,'Paper','Dotted','unisex',1,'2025-08-25 02:29:26','2025-08-25 02:29:26');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seasons`
--

DROP TABLE IF EXISTS `seasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seasons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `season_name` enum('spring','summer','autumn','winter') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` int NOT NULL,
  `featured` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_season` (`season_name`),
  KEY `idx_name` (`name`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_featured` (`featured`),
  CONSTRAINT `seasons_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seasons`
--

LOCK TABLES `seasons` WRITE;
/*!40000 ALTER TABLE `seasons` DISABLE KEYS */;
/*!40000 ALTER TABLE `seasons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `firstname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','admin','superadmin','product_admin','order_admin','support') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `profile_picture_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `billing_provider` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_customer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_card_brand` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_card_last_four` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin','User','admin@fragrancefusion.com','test','admin',NULL,'2025-08-19 13:21:54','2025-08-19 13:59:23',NULL,NULL,NULL,NULL,NULL,NULL),(2,'John','Doe','john@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','user',NULL,'2025-08-19 13:22:09','2025-08-19 13:22:09',NULL,NULL,NULL,NULL,NULL,NULL),(3,'James','Rabena','jamesrabena23@gmail.com','$2y$10$JrKuT805zcne7FOPhyJ7fOeafg0dNWSYMWdvVRLACY4TvabFCY24m','user',NULL,'2025-08-19 14:18:53','2025-08-19 14:18:53',NULL,NULL,NULL,NULL,NULL,NULL),(4,'JamesADMIN','RabenaADMIN','jamesrabena23ADMIN@gmail.com','$2y$10$d96gWH3EFZAXHqqkyhsUjOg1Qb7QX4PaxOWm63sgjrGGoE5ZZCjVm','superadmin',NULL,'2025-08-19 14:25:09','2025-08-25 02:51:36',NULL,NULL,NULL,NULL,NULL,NULL),(5,'James','Rabena','PRO_AD@gmail.com','$2y$10$Aj7NMj/aUm4marn2Ky7bW.9ujb1z3ehf/EV0OPyeD.8sDKgeEWjre','product_admin',NULL,'2025-08-25 02:46:29','2025-08-25 02:51:00',NULL,NULL,NULL,NULL,NULL,NULL),(6,'Pascual Bernard','Benauro','pasbenauro05@gmail.com','$2y$10$dk8q0zcvsdBn2s59Kx5oUuC2GQQs0MTol2/V2sq5hSxjF/APlqtyu','user',NULL,'2025-08-25 05:40:10','2025-08-25 05:40:10',NULL,NULL,NULL,NULL,NULL,NULL),(7,'Tester','Rabena','testerrabena@gmail.com','$2y$10$RC3.cmLhAEWeMA4/mlLYm.8J6BECNpYVx.oCZzIWqVuYAhW6PimVy','user',NULL,'2025-08-25 08:16:28','2025-08-25 08:16:28',NULL,NULL,NULL,NULL,NULL,NULL),(8,'Pam','Ayaton','pamayaton@gmail.com','$2y$10$F3vUTEPwfWAv4ce.QoDQ5uH6KAjcnRbYTofSKuUaIMryDsQyP5B5m','user',NULL,'2025-08-25 08:44:26','2025-08-25 08:44:26',NULL,NULL,NULL,NULL,NULL,NULL),(9,'Fernando','Alonso','FA14@gmail.com','$2y$10$8hG7EriWC7FlfEbKO6XymObVgL4eJOCetes211PKgbK21nl8XJ4tS','user','uploads/profile_pictures/9.jpg','2025-08-29 20:24:51','2025-08-30 07:13:53','09785856795','743 N. Dogwood Ave.\r\nWest Lafayette, IN 47906','GCash','09785856795','Mobile',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wishlist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product_unique` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlist`
--

LOCK TABLES `wishlist` WRITE;
/*!40000 ALTER TABLE `wishlist` DISABLE KEYS */;
INSERT INTO `wishlist` VALUES (13,9,16,'2025-08-30 21:57:34'),(14,9,15,'2025-08-30 21:57:35'),(16,9,12,'2025-08-30 21:57:50'),(17,9,14,'2025-08-30 21:57:51'),(19,9,13,'2025-08-30 22:45:13'),(20,9,21,'2025-08-30 22:45:23');
/*!40000 ALTER TABLE `wishlist` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-01 13:31:44
