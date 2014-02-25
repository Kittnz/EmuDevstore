<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObjectList.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/warning/UserWarning.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/warning/Warning.class.php');

/**
 * Represents a list of user warnings.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	data.user.infraction.warning
 * @category 	Community Framework (commercial)
 */
class UserWarningList extends DatabaseObjectList {
	/**
	 * list of user warnings
	 * 
	 * @var array<UserWarning>
	 */
	public $userWarnings = array();

	/**
	 * @see DatabaseObjectList::countObjects()
	 */
	public function countObjects() {
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user_infraction_warning_to_user user_warning
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : '');
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * @see DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		// get ids
		$userWarningIDArray = $objectIDArray = $objects = array();
		$sql = "SELECT		user_warning.userWarningID,
					user_warning.objectID, user_warning.objectType
			FROM		wcf".WCF_N."_user_infraction_warning_to_user user_warning
			LEFT JOIN	wcf".WCF_N."_user_infraction_warning warning
			ON		(warning.warningID = user_warning.warningID)
			LEFT JOIN	wcf".WCF_N."_user user_table
			ON		(user_table.userID = user_warning.userID)
			LEFT JOIN	wcf".WCF_N."_user judge
			ON		(judge.userID = user_warning.judgeID)
			".$this->sqlJoins."
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : '')."
			".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
		$result = WCF::getDB()->sendQuery($sql, $this->sqlLimit, $this->sqlOffset);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$userWarningIDArray[] = $row['userWarningID'];
			if ($row['objectID'] != 0 && $row['objectType'] != '') {
				if (!isset($objectIDArray[$row['objectType']])) $objectIDArray[$row['objectType']] = array();
				$objectIDArray[$row['objectType']][] = $row['objectID'];
			}
		}
		
		if (count($userWarningIDArray)) {
			// get warning objects
			foreach ($objectIDArray as $objectType => $idArray) {
				if (($result = Warning::getWarningObjectByID($objectType, $idArray)) !== null) {
					$objects[$objectType] = $result;
				}
			}
			
			// get warnings
			$sql = "SELECT		".(!empty($this->sqlSelects) ? $this->sqlSelects.',' : '')."
						user_table.username, judge.username AS judgeUsername,
						warning.*, user_warning.*
				FROM		wcf".WCF_N."_user_infraction_warning_to_user user_warning
				LEFT JOIN	wcf".WCF_N."_user_infraction_warning warning
				ON		(warning.warningID = user_warning.warningID)
				LEFT JOIN	wcf".WCF_N."_user user_table
				ON		(user_table.userID = user_warning.userID)
				LEFT JOIN	wcf".WCF_N."_user judge
				ON		(judge.userID = user_warning.judgeID)
				".$this->sqlJoins."
				WHERE		user_warning.userWarningID IN (".implode(',', $userWarningIDArray).")
				".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (isset($objects[$row['objectType']]) && isset($objects[$row['objectType']][$row['objectID']])) {
					$row['object'] = $objects[$row['objectType']][$row['objectID']];
				}
				$this->userWarnings[] = new UserWarning(null, $row);
			}
		}
	}
	
	/**
	 * @see DatabaseObjectList::getObjects()
	 */
	public function getObjects() {
		return $this->userWarnings;
	}
}
?>