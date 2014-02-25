-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.5.32 - MySQL Community Server (GPL)
-- Server OS:                    Win32
-- HeidiSQL Version:             8.1.0.4545
-- --------------------------------------------------------


-- Dumping data for table emudevstore.categories: ~8 rows (approximately)
INSERT INTO `categories` (`id`, `parent_id`, `title`, `name`, `short_title`, `subtitle`, `image`) VALUES
(1, 0, 'Website', '', '', '', ''),
(2, 1, 'Designs & PSDs', '', '', 'Webtemplates, Logos, Full Websites, PSDs', 'psdtt.png'),
(3, 1, 'Web Tools', '', '', 'Single Scripts, Single Modules', 'webtools.png'),
(4, 1, 'Fusion CMS', '', '', 'Themes, Modules, Fixes', 'fusiontt.png'),
(5, 1, 'Web-WoW CMS', '', '', 'Full Websites, Themes, Scripts', 'webwow.png'),
(6, 1, 'Woltlab Burning Board', '', '', 'Themes, Plugins, Scripts', 'wbbtt.png'),
(7, 4, 'Themes', '', '', 'Fusion CMS Themes', 'fcms_themes.jpg'),
(8, 4, 'Modules', '', '', 'Fusion CMS Modules', 'fcms_modules.jpg');


-- Dumping data for table emudevstore.customers: ~1 rows (approximately)
INSERT INTO `customers` (`customer_id`, `real_name`, `email`, `website`, `password_sha1`, `rank`) VALUES
(1, 'admin 2', 'admin@yahoo.com', '', 'd033e22ae348aeb5660fc2140aec35850c4da997', 3);

