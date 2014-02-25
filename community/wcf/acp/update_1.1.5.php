<?php
// delete obsolete update servers
$sql = "DELETE FROM wcf".WCF_N."_package_update_server WHERE server LIKE '%community.woltlab.com%'";
WCF::getDB()->sendQuery($sql);

// add new update servers (plugin store)
$sql = "SELECT COUNT(*) AS count FROM wcf".WCF_N."_package_update_server WHERE server LIKE '%store.woltlab.com/tempest%'";
$row = WCF::getDB()->getFirstRow($sql);
if (!$row['count']) {
	$sql = "INSERT INTO 	wcf".WCF_N."_package_update_server
				(server, status, statusUpdate, errorText, updatesFile, timestamp, htUsername, htPassword)
		VALUES 		('http://store.woltlab.com/tempest/', 'online', 1, NULL, 0, 1168257450, '', '')";
	WCF::getDB()->sendQuery($sql);
}
$sql = "SELECT COUNT(*) AS count FROM wcf".WCF_N."_package_update_server WHERE server LIKE '%store.woltlab.com'";
$row = WCF::getDB()->getFirstRow($sql);
if (!$row['count']) {
	$sql = "INSERT INTO 	wcf".WCF_N."_package_update_server
				(server, status, statusUpdate, errorText, updatesFile, timestamp, htUsername, htPassword)
		VALUES 		('http://store.woltlab.com', 'online', 1, NULL, 0, 1168257450, '', '')";
	WCF::getDB()->sendQuery($sql);
}
?>