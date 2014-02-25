<?php
// delete members.css
@unlink(WCF_DIR.'style/members.css');
$sql = "DELETE FROM	wcf".WCF_N."_package_installation_file_log
	WHERE		filename = 'style/members.css'";
WCF::getDB()->sendQuery($sql);

// delete this file
@unlink(__FILE__);
?>