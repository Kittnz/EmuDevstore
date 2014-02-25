<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/Message.class.php');

/**
 * Represents a recipient of a private message.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm
 * @category 	Community Framework (commercial)
 */
class PMRecipient extends DatabaseObject {
	/**
	 * Creates a new PMRecipient object.
	 * 
	 * @param	integer		$pmID
	 * @param	integer		$recipientID
	 * @param	array		$row
	 */
	public function __construct($pmID, $recipientID, $row = null) {
		if ($pmID !== null && $recipientID !== null) {
			$sql = "SELECT		*
				FROM		wcf".WCF_N."_pm_to_user
				WHERE		pmID = ".$pmID."
						AND recipientID = ".$recipientID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Returns the name of this recipient.
	 *
	 * @return	string
	 */
	public function __toString() {
		return $this->recipient;
	}
}
?>