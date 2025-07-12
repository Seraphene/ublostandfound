-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: localhost    Database: ub_lost_found
-- ------------------------------------------------------
-- Server version	8.0.42

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `reportitem`
--

DROP TABLE IF EXISTS `reportitem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reportitem` (
  `ReportID` int NOT NULL AUTO_INCREMENT,
  `ItemName` varchar(200) NOT NULL,
  `Description` text,
  `ItemClassID` int NOT NULL,
  `ReportStatusID` int NOT NULL,
  `PhotoURL` varchar(255) DEFAULT NULL,
  `LostLocation` varchar(200) DEFAULT NULL,
  `DateOfLoss` date NOT NULL,
  `StudentNo` varchar(20) NOT NULL,
  `ContactInfo` text,
  `Reward` decimal(10,2) DEFAULT '0.00',
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `StatusConfirmed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`ReportID`),
  KEY `idx_reportitem_class` (`ItemClassID`),
  KEY `idx_reportitem_status` (`ReportStatusID`),
  KEY `idx_reportitem_studentno` (`StudentNo`),
  CONSTRAINT `reportitem_ibfk_1` FOREIGN KEY (`ItemClassID`) REFERENCES `itemclass` (`ItemClassID`),
  CONSTRAINT `reportitem_ibfk_2` FOREIGN KEY (`ReportStatusID`) REFERENCES `reportstatus` (`ReportStatusID`),
  CONSTRAINT `reportitem_ibfk_3` FOREIGN KEY (`StudentNo`) REFERENCES `student` (`StudentNo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reportitem`
--

LOCK TABLES `reportitem` WRITE;
/*!40000 ALTER TABLE `reportitem` DISABLE KEYS */;
INSERT INTO `reportitem` VALUES (3,'sada','dasdwa',4,1,'assets/uploads/lost_68713f0c586208.22932784.jpg','wadwa','2025-07-02','123123',NULL,0.00,'2025-07-11 16:42:52','2025-07-11 16:45:47',1),(4,'f','fdsfds',5,1,'assets/uploads/lost_687146062633f5.62246804.jpg','fsdf','2025-07-10','123123',NULL,0.00,'2025-07-11 17:12:38','2025-07-11 17:12:46',1);
/*!40000 ALTER TABLE `reportitem` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-07-12  8:18:30
