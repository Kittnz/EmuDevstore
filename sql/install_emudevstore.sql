-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.5.32 - MySQL Community Server (GPL)
-- Server OS:                    Win32
-- HeidiSQL Version:             8.1.0.4545
-- --------------------------------------------------------

-- Dumping structure for table emudevstore.categories
DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- Dumping structure for table emudevstore.ci_sessions
DROP TABLE IF EXISTS `ci_sessions`;
CREATE TABLE IF NOT EXISTS `ci_sessions` (
  `session_id` int(11) NOT NULL,
  `ip_address` varchar(100) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `last_activity` bigint(20) NOT NULL,
  `user_data` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- Dumping structure for table emudevstore.customers
DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `real_name` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `website` varchar(50) DEFAULT NULL,
  `password_sha1` varchar(50) DEFAULT NULL,
  `rank` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;


-- Dumping structure for table emudevstore.daily_income
DROP TABLE IF EXISTS `daily_income`;
CREATE TABLE IF NOT EXISTS `daily_income` (
  `day` varchar(50) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- Dumping structure for table emudevstore.orders
DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `customer_id` varchar(50) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- Dumping structure for table emudevstore.paypal_logs
DROP TABLE IF EXISTS `paypal_logs`;
CREATE TABLE IF NOT EXISTS `paypal_logs` (
  `user_id` int(11) DEFAULT '0',
  `product_id` int(11) DEFAULT NULL,
  `products_name` varchar(255) DEFAULT NULL,
  `validated` tinyint(4) DEFAULT '0',
  `total` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- Dumping structure for table emudevstore.products
DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `thumbnail` varchar(50) DEFAULT NULL,
  `screenshot` varchar(50) DEFAULT NULL,
  `download` varchar(50) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` varchar(50) DEFAULT NULL,
  `paypal_email` varchar(50) DEFAULT NULL,
  `validated` tinyint(4) NOT NULL DEFAULT '0',
  `downloads` int(11) NOT NULL DEFAULT '0',
  `is_unique` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

