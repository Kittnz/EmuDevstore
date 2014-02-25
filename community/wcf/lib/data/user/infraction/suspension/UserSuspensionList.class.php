<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObjectList.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/suspension/UserSuspension.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/suspension/Suspension.class.php');

/**
 * Represents a list of user suspensions.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	data.user.infraction.suspension
 * @category 	Community Framework (commercial)
 */
class UserSuspensionList extends DatabaseObjectList {
	/**
	 * list of user suspensions
	 * 
	 * @var array<UserSuspension>
	 */
	public $userSuspensions = array();

	/**
	 * @see DatabaseObjectList::countObjects()
	 */
	public function countObjects() {
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user_infraction_suspension_to_user user_suspension
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : '');
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * @see DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		// get suspensions
		$sql = "SELECT		".(!empty($this->sqlSelects) ? $this->sqlSelects.',' : '')."
					user_table.username,
					suspension.*, user_suspension.*
			FROM		wcf".WCF_N."_user_infraction_suspension_to_user user_suspension
			LEFT JOIN	wcf".WCF_N."_user_infraction_suspension suspension
			ON		(suspension.suspensionID = user_suspension.suspensionID)
			LEFT JOIN	wcf".WCF_N."_user user_table
			ON		(user_table.userID = user_suspension.userID)
			".$this->sqlJoins."
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : '')."
			".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
		$result = WCF::getDB()->sendQuery($sql, $this->sqlLimit, $this->sqlOffset);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->userSuspensions[] = new UserSuspension(null, $row);
		}
	}
	
	/**
	 * @see DatabaseObjectList::getObjects()
	 */
	public function getObjects() {
		return $this->userSuspensions;
	}
}
?>