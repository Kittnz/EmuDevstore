<?php
// delete obsolete files
@unlink(WCF_DIR.'acp/style/acp.css');
@unlink(WCF_DIR.'acp/style/buttons.css');
@unlink(WCF_DIR.'acp/style/containers.css');
@unlink(WCF_DIR.'acp/style/forms.css');
@unlink(WCF_DIR.'acp/style/global.css');
@unlink(WCF_DIR.'acp/style/header.css');
@unlink(WCF_DIR.'acp/style/ie6Fix.css');
@unlink(WCF_DIR.'acp/style/ie7Fix.css');
@unlink(WCF_DIR.'acp/style/inlineCalendar.css');
@unlink(WCF_DIR.'acp/style/messages.css');
@unlink(WCF_DIR.'acp/style/pageMenu.css');
@unlink(WCF_DIR.'acp/style/pageNavigation.css');
@unlink(WCF_DIR.'acp/style/setupStyle.css');
@unlink(WCF_DIR.'acp/style/setupWindowStyle.css');
@unlink(WCF_DIR.'acp/style/tabbedMenus.css');
@unlink(WCF_DIR.'acp/style/tables.css');

// update acp style file
StyleUtil::updateStyleFile();

// init package install and update timestamps
$sql = "UPDATE	wcf".WCF_N."_package
	SET	installDate = packageDate
	WHERE	installDate = 0";
WCF::getDB()->sendQuery($sql);
$sql = "UPDATE	wcf".WCF_N."_package
	SET	updateDate = packageDate
	WHERE	updateDate = 0";
WCF::getDB()->sendQuery($sql);

// remove obsolete package com.woltlab.wcf.system.template.pack
// get wcf id
$wcfPackageID = $this->installation->getPackageID();
// get id of template pack package
$sql = "SELECT packageID FROM wcf".WCF_N."_package WHERE package = 'com.woltlab.wcf.system.template.pack'";
$row = WCF::getDB()->getFirstRow($sql);
if (!empty($row['packageID'])) {
	$tplPackageID = $row['packageID'];
	// modify table
	$sql = "ALTER TABLE wcf".WCF_N."_template_pack ADD parentTemplatePackID INT(10) NOT NULL DEFAULT 0";
	try {
		WCF::getDB()->sendQuery($sql);
	}
	catch (DatabaseException $e) {}
	
	// kick package
	WCF::getDB()->sendQuery("DELETE FROM wcf".WCF_N."_package WHERE packageID = ".$tplPackageID);
	WCF::getDB()->sendQuery("DELETE FROM wcf".WCF_N."_package_dependency WHERE packageID = ".$tplPackageID." OR dependency = ".$tplPackageID);
	WCF::getDB()->sendQuery("DELETE FROM wcf".WCF_N."_package_installation_file_log WHERE packageID = ".$tplPackageID);
	WCF::getDB()->sendQuery("UPDATE wcf".WCF_N."_package_installation_sql_log SET packageID = ".$wcfPackageID." WHERE packageID = ".$tplPackageID);
	WCF::getDB()->sendQuery("DELETE FROM wcf".WCF_N."_package_requirement WHERE packageID = ".$tplPackageID." OR requirement = ".$tplPackageID);
	WCF::getDB()->sendQuery("DELETE FROM wcf".WCF_N."_package_requirement_map WHERE packageID = ".$tplPackageID." OR requirement = ".$tplPackageID);
}
else {
	// add new table
	WCF::getDB()->sendQuery("DROP TABLE IF EXISTS wcf".WCF_N."_template_pack");
	WCF::getDB()->sendQuery("CREATE TABLE wcf".WCF_N."_template_pack (
		templatePackID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		parentTemplatePackID INT(10) NOT NULL DEFAULT 0,
		templatePackName VARCHAR(255) NOT NULL DEFAULT '',
		templatePackFolderName VARCHAR(255) NOT NULL DEFAULT ''
	) ENGINE=MyISAM DEFAULT CHARSET=".WCF::getDB()->getCharset());
	
	// modify table
	try {
		WCF::getDB()->sendQuery("ALTER TABLE wcf".WCF_N."_template ADD templatePackID INT(10) NOT NULL DEFAULT 0");
	}
	catch (DatabaseException $e) {}
	try {
		WCF::getDB()->sendQuery("ALTER TABLE wcf".WCF_N."_template ADD obsolete TINYINT(1) NOT NULL DEFAULT 0");
	}
	catch (DatabaseException $e) {}
	try {
		WCF::getDB()->sendQuery("ALTER TABLE wcf".WCF_N."_template ADD KEY (packageID, templatePackID, templateName)");
	}
	catch (DatabaseException $e) {}
	
	// log new table
	try {
		$sql = "INSERT INTO	wcf".WCF_N."_package_installation_sql_log
					(packageID, sqlTable)
			VALUES		(".$wcfPackageID.", 'wcf".WCF_N."_template_pack')";
		WCF::getDB()->sendQuery($sql);
	}
	catch (DatabaseException $e) {}
}

// remove obsolete package com.woltlab.wcf.page.user.membersList
// get package ids
$sql = "SELECT packageID FROM wcf".WCF_N."_package WHERE package = 'com.woltlab.wcf.page.user.membersList'";
$row1 = WCF::getDB()->getFirstRow($sql);
$sql = "SELECT packageID FROM wcf".WCF_N."_package WHERE package = 'com.woltlab.wcf.page.user.profile'";
$row2 = WCF::getDB()->getFirstRow($sql);
if (!empty($row1['packageID']) && !empty($row2['packageID'])) {
	// move components
	WCF::getDB()->sendQuery("UPDATE wcf".WCF_N."_package_installation_file_log SET packageID = ".$row2['packageID']." WHERE packageID = ".$row1['packageID']);
	WCF::getDB()->sendQuery("UPDATE wcf".WCF_N."_template SET packageID = ".$row2['packageID']." WHERE packageID = ".$row1['packageID']);
	WCF::getDB()->sendQuery("UPDATE wcf".WCF_N."_header_menu_item SET packageID = ".$row2['packageID']." WHERE packageID = ".$row1['packageID']);
	WCF::getDB()->sendQuery("UPDATE wcf".WCF_N."_option_category SET packageID = ".$row2['packageID']." WHERE packageID = ".$row1['packageID']);
	WCF::getDB()->sendQuery("UPDATE wcf".WCF_N."_option SET packageID = ".$row2['packageID']." WHERE packageID = ".$row1['packageID']);
	WCF::getDB()->sendQuery("UPDATE wcf".WCF_N."_group_option_category SET packageID = ".$row2['packageID']." WHERE packageID = ".$row1['packageID']);
	WCF::getDB()->sendQuery("UPDATE wcf".WCF_N."_group_option SET packageID = ".$row2['packageID']." WHERE packageID = ".$row1['packageID']);
	WCF::getDB()->sendQuery("UPDATE wcf".WCF_N."_help_item SET packageID = ".$row2['packageID']." WHERE packageID = ".$row1['packageID']);
	WCF::getDB()->sendQuery("UPDATE wcf".WCF_N."_page_location SET packageID = ".$row2['packageID']." WHERE packageID = ".$row1['packageID']);
	WCF::getDB()->sendQuery("UPDATE wcf".WCF_N."_language_item SET packageID = ".$row2['packageID']." WHERE packageID = ".$row1['packageID']);
	
	// kick package
	WCF::getDB()->sendQuery("DELETE FROM wcf".WCF_N."_package WHERE packageID = ".$row1['packageID']);
	WCF::getDB()->sendQuery("DELETE FROM wcf".WCF_N."_package_dependency WHERE packageID = ".$row1['packageID']." OR dependency = ".$row1['packageID']);
	WCF::getDB()->sendQuery("DELETE FROM wcf".WCF_N."_package_requirement WHERE packageID = ".$row1['packageID']." OR requirement = ".$row1['packageID']);
	WCF::getDB()->sendQuery("DELETE FROM wcf".WCF_N."_package_requirement_map WHERE packageID = ".$row1['packageID']." OR requirement = ".$row1['packageID']);
	WCF::getDB()->sendQuery("DELETE FROM wcf".WCF_N."_language_to_packages WHERE packageID = ".$row1['packageID']);
}
?>