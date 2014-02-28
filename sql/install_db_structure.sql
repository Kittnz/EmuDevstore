-- Dumping structure for table emudevstore2.categories
DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `has_childs` tinyint(4) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `short_title` varchar(255) NOT NULL,
  `subtitle` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;


-- Dumping structure for table emudevstore2.ci_sessions
DROP TABLE IF EXISTS `ci_sessions`;
CREATE TABLE IF NOT EXISTS `ci_sessions` (
  `session_id` varchar(50) NOT NULL,
  `ip_address` varchar(100) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `last_activity` bigint(20) NOT NULL,
  `user_data` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- Dumping structure for table emudevstore2.customers
DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `real_name` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `website` varchar(50) DEFAULT NULL,
  `password_sha1` varchar(50) DEFAULT NULL,
  `rank` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;


-- Dumping structure for table emudevstore2.daily_income
DROP TABLE IF EXISTS `daily_income`;
CREATE TABLE IF NOT EXISTS `daily_income` (
  `day` varchar(50) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- Dumping structure for table emudevstore2.orders
DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `customer_id` varchar(50) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- Dumping structure for table emudevstore2.paypal_logs
DROP TABLE IF EXISTS `paypal_logs`;
CREATE TABLE IF NOT EXISTS `paypal_logs` (
  `user_id` int(11) DEFAULT '0',
  `product_id` int(11) DEFAULT NULL,
  `payment_status` varchar(255) DEFAULT NULL,
  `payment_amount` varchar(255) DEFAULT NULL,
  `payment_currency` varchar(255) DEFAULT NULL,
  `txn_id` int(11) DEFAULT NULL,
  `receiver_email` varchar(255) DEFAULT NULL,
  `payer_email` varchar(255) DEFAULT NULL,
  `products_name` varchar(255) DEFAULT NULL,
  `validated` tinyint(4) DEFAULT '0',
  `total` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- Dumping structure for table emudevstore2.products
DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `screenshot` varchar(255) DEFAULT NULL,
  `download` varchar(500) DEFAULT NULL,
  `category_id` int(11) DEFAULT '1',
  `description` varchar(10000) DEFAULT NULL,
  `paypal_email` varchar(50) DEFAULT NULL,
  `is_unique` tinyint(4) DEFAULT '0',
  `type` tinyint(4) DEFAULT '1',
  `subtitle` varchar(255) DEFAULT NULL,
  `validated` tinyint(4) NOT NULL DEFAULT '0',
  `downloads` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
