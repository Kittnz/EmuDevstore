<?php
// wcf imports
require_once(WCF_DIR.'lib/data/attachment/AttachmentList.class.php');

/**
 * Represents a list of attachments.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.attachment
 * @subpackage	data.attachment
 * @category 	Community Framework (commercial)
 */
class AdminAttachmentList extends AttachmentList {
	/**
	 * Creates a new AdminAttachmentList object.
	 */
	public function __construct() {
		if (!empty($this->sqlConditions)) $this->sqlConditions .= ' AND ';
		
		// get package ids
		$packageIDArray = array();
		$sql = "SELECT	packageID
			FROM	wcf".WCF_N."_attachment_container_type
			WHERE	packageID IN (SELECT dependency FROM wcf".WCF_N."_package_dependency WHERE packageID = ".PACKAGE_ID.")
				AND isPrivate = 0";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$packageIDArray[] = $row['packageID'];
		}
		$this->sqlConditions .= "attachment.packageID IN (".(count($packageIDArray) ? implode(',', $packageIDArray) : 0).")";
		
		if (!empty($this->sqlSelects)) $this->sqlSelects .= ',';
		$this->sqlSelects .= 'user_table.username';
		if (!empty($this->sqlJoins)) $this->sqlJoins .= ' ';
		$this->sqlJoins .= "LEFT JOIN wcf".WCF_N."_user user_table ON (user_table.userID = attachment.userID)";
	}
}
?>