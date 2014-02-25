<?php
require_once(WCF_DIR.'lib/data/attachment/AttachmentList.class.php');

/**
 * Represents a list of attachments.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.attachment
 * @subpackage	data.attachment
 * @category 	Community Framework (commercial)
 */
class UserAttachmentList extends AttachmentList {
	/**
	 * Creates a new UserAttachmentList object.
	 */
	public function __construct($userID) {
		if (!empty($this->sqlConditions)) $this->sqlConditions .= ' AND ';
		$this->sqlConditions .= "attachment.userID = ".$userID;
		
		// get package ids
		$packageIDArray = array();
		$sql = "SELECT	packageID
			FROM	wcf".WCF_N."_attachment_container_type
			WHERE	packageID IN (SELECT dependency FROM wcf".WCF_N."_package_dependency WHERE packageID = ".PACKAGE_ID.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$packageIDArray[] = $row['packageID'];
		}
		$this->sqlConditions .= " AND attachment.packageID IN (".(count($packageIDArray) ? implode(',', $packageIDArray) : 0).")";
	}
}
?>