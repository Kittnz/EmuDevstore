

-- Dumping data for table emudevstore2.categories: ~8 rows (approximately)
INSERT INTO `categories` (`id`, `parent_id`, `has_childs`, `title`, `name`, `short_title`, `subtitle`, `image`) VALUES
(1, 0, 1, 'Download', '', '', '', ''),
(2, 1, 0, 'Designs & PSDs', '', '', 'Webtemplates, Logos, Full Websites, PSDs', 'psdtt.png'),
(3, 1, 0, 'Web Tools', '', '', 'Single Scripts, Single Modules', 'webtools.png'),
(4, 1, 0, 'Fusion CMS', '', '', 'Themes, Modules, Fixes', 'fusiontt.png'),
(5, 1, 0, 'Web-WoW CMS', '', '', 'Full Websites, Themes, Scripts', 'webwow.png'),
(6, 1, 0, 'Woltlab Burning Board', '', '', 'Themes, Plugins, Scripts', 'wbbtt.png'),
(7, 4, 0, 'Themes', '', '', 'Fusion CMS Themes', 'fcms_themes.jpg'),
(8, 4, 0, 'Modules', '', '', 'Fusion CMS Modules', 'fcms_modules.jpg');

	
-- Dumping data for table emudevstore2.customers: ~1 rows (approximately)
INSERT INTO `customers` (`customer_id`, `real_name`, `email`, `website`, `password_sha1`, `rank`) VALUES
(1, 'admin', 'admin@yahoo.com', '', 'd033e22ae348aeb5660fc2140aec35850c4da997', 2);
