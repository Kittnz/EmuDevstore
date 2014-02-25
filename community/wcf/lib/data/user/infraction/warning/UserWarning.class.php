<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a user warning.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	data.user.infraction.warning
 * @category 	Community Framework (commercial)
 */
class UserWarning extends DatabaseObject {
	/**
	 * Creates a new UserWarning object.
	 *
	 * @param	integer		$userWarningID
	 * @param	array<mixed>	$row
	 */
	public function __construct($userWarningID, $row = null) {
		if ($userWarningID !== null) {
			$sql = "SELECT		user_warning.*,
						user_table.username, judge.username AS judgeUsername
				FROM		wcf".WCF_N."_user_infraction_warning_to_user user_warning
				LEFT JOIN	wcf".WCF_N."_user user_table
				ON		(user_table.userID = user_warning.userID)
				LEFT JOIN	wcf".WCF_N."_user judge
				ON		(judge.userID = user_warning.judgeID)
				WHERE		user_warning.userWarningID = ".$userWarningID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
}
?>