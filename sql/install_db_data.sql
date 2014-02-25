-- --------------------------------------------------------
-- INSTALL DATA
-- --------------------------------------------------------

-- Dumping data for table emudevstore.categories: ~8 rows (approximately)
INSERT INTO `categories` (`id`, `parent_id`, `has_childs`, `title`, `short_title`, `subtitle`, `image`) VALUES
(1, 0, 1, 'Website', 'Web applications', '', ''),
(2, 1, 1, 'Designs & PSDs', '', 'Webtemplates, Logos, Full Websites, PSDs', 'psdtt.png'),
(3, 1, 1, 'Web Tools', '', 'Single Scripts, Single Modules', 'webtools.png'),
(4, 1, 1, 'Fusion CMS', '', 'Themes, Modules, Fixes', 'fusiontt.png'),
(5, 1, 1, 'Web-WoW CMS', '', 'Full Websites, Themes, Scripts', 'webwow.png'),
(6, 1, 1, 'Woltlab Burning Board', '', 'Themes, Plugins, Scripts', 'wbbtt.png'),
(7, 4, 1, 'Themes', '', 'Fusion CMS Themes', 'fcms_themes.jpg'),
(8, 4, 1, 'Modules', '', 'Fusion CMS Modules', 'fcms_modules.jpg');

-- Dumping data for table emudevstore.customers
INSERT INTO `customers` (`customer_id`, `real_name`, `email`, `website`, `password_sha1`, `rank`) VALUES
(1, 'administrator', 'admin@yahoo.com', '', 'd033e22ae348aeb5660fc2140aec35850c4da997', 2);

-- Dumping data for table emudevstore.daily_income
INSERT INTO `daily_income` (`day`, `amount`) VALUES
('2014-02-25', 0);

-- Dumping data for table emudevstore.products
INSERT INTO `products` (`id`, `product_id`, `author_id`, `category_id`, `name`, `subtitle`, `price`, `screenshot`, `thumbnail`, `download`, `description`, `paypal_email`, `validated`, `downloads`, `is_unique`) VALUES
(1, 1, 1, 2, 'test product name', NULL, 10, '', NULL, NULL, 'any test description', 'paypal@domain.com', 1, 0, NULL);
