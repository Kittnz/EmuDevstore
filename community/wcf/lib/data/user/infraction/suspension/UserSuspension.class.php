<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a user suspension.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	data.user.infraction.suspension
 * @category 	Community Framework (commercial)
 */
class UserSuspension extends DatabaseObject {
	/**
	 * Creates a new UserSuspension object.
	 *
	 * @param	integer		$userSuspensionID
	 * @param	array<mixed>	$row
	 */
	public function __construct($userSuspensionID, $row = null) {
		if ($userSuspensionID !== null) {
			$sql = "SELECT		suspension.*, user_table.username,
						suspension_to_user.*
				FROM		wcf".WCF_N."_user_infraction_suspension_to_user suspension_to_user
				LEFT JOIN	wcf".WCF_N."_user_infraction_suspension suspension
				ON		(suspension.suspensionID = suspension_to_user.suspensionID)
				LEFT JOIN	wcf".WCF_N."_user user_table
				ON		(user_table.userID = suspension_to_user.userID)
				WHERE		suspension_to_user.userSuspensionID = ".$userSuspensionID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
}
?>