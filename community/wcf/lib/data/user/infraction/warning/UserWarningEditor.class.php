<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/warning/UserWarning.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/warning/Warning.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/suspension/UserSuspensionEditor.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/suspension/SuspensionEditor.class.php');

/**
 * Provides functions to add, edit and delete user warnings.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	data.user.infraction.warning
 * @category 	Community Framework (commercial)
 */
class UserWarningEditor extends UserWarning {
	/**
	 * Deletes this user warning.
	 */
	public function delete() {
		$sql = "DELETE FROM	wcf".WCF_N."_user_infraction_warning_to_user
			WHERE		userWarningID = ".$this->userWarningID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Creates a new user warning.
	 *
	 * @param	integer		$userID
	 * @param	integer		$judgeID
	 * @param	integer		$warningID
	 * @param	integer		$objectID
	 * @param	string		$objectType
	 * @param	string		$title
	 * @param	integer		$points
	 * @param	integer		$expires
	 * @param	string		$reason
	 * @param	integer		$packageID
	 * @return	UserWarningEditor
	 */
	public static function create($userID, $judgeID, $warningID, $objectID = 0, $objectType = '', $title = '', $points = 0, $expires = 0, $reason = '', $package = PACKAGE_ID) {
		// get data
		if ($warningID) {
			$warning = new Warning($warningID);
			$title = $warning->title;
			$points = $warning->points;
			$expires = ($warning->expires ? TIME_NOW + $warning->expires : 0);
		}
		
		// save
		$sql = "INSERT INTO	wcf".WCF_N."_user_infraction_warning_to_user
					(packageID, objectID, objectType, userID, judgeID, warningID, time, title, points, expires, reason)
			VALUES		(".$package.", ".$objectID.", '".escapeString($objectType)."', ".$userID.", ".$judgeID.", ".$warningID.", ".TIME_NOW.", '".escapeString($title)."', ".$points.", ".$expires.", '".escapeString($reason)."')";
		WCF::getDB()->sendQuery($sql);
		$userWarningID = WCF::getDB()->getInsertID("wcf".WCF_N."_user_infraction_warning_to_user", 'userWarningID');
		
		// extend existing warnings
		if ($expires != 0) {
			$sql = "UPDATE	wcf".WCF_N."_user_infraction_warning_to_user
				SET	expires = ".$expires."
				WHERE	userID = ".$userID."
					AND expires >= ".TIME_NOW."
					AND expires < ".($expires);
			WCF::getDB()->sendQuery($sql);
		}
		
		// get object
		return new UserWarningEditor($userWarningID);
	}
	
	/**
	 * Updates this warning.
	 *
	 * @param	integer		$warningID
	 * @param	string		$title
	 * @param	integer		$points
	 * @param	integer		$expires
	 * @param	string		$reason
	 */
	public function update($warningID, $title = '', $points = 0, $expires = 0, $reason = '') {
		// get data
		if ($warningID) {
			$warning = new Warning($warningID);
			$title = $warning->title;
			$points = $warning->points;
			if ($warningID != $this->warningID) {
				$expires = TIME_NOW + $warning->expires;
			}
			else {
				$expires = $this->expires;
			}
		}
		
		// update
		$sql = "UPDATE 	wcf".WCF_N."_user_infraction_warning_to_user
			SET	warningID = ".$warningID.",
				title = '".escapeString($title)."',
				points = ".$points.",
				expires = ".$expires.",
				reason = '".escapeString($reason)."'
			WHERE	userWarningID = ".$this->userWarningID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Checks the warnings of a user and applies suspensions.
	 * 
	 * @param	integer		$userID
	 */
	public static function checkWarnings($userID) {
		// get suspensions
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_user_infraction_suspension
			WHERE		packageID IN (
						SELECT	dependency
						FROM	wcf".WCF_N."_package_dependency
						WHERE	packageID = ".PACKAGE_ID."
					)
					AND points <= IFNULL((
						SELECT	SUM(points)
						FROM	wcf".WCF_N."_user_infraction_warning_to_user
						WHERE	userID = ".$userID."
							AND (
								expires >= ".TIME_NOW."
								OR expires = 0
							)
					), 0)
					AND suspensionID NOT IN (
						SELECT	suspensionID
						FROM	wcf".WCF_N."_user_infraction_suspension_to_user
						WHERE	userID = ".$userID."
							AND (
								expires >= ".TIME_NOW."
								OR expires = 0
							)
					)
			ORDER BY 	points DESC";
		$result = WCF::getDB()->sendQuery($sql);
		if (WCF::getDB()->countRows($result) > 0) {
			$user = new UserEditor($userID);
			while ($row = WCF::getDB()->fetchArray($result)) {
				// get suspension
				$suspension = new Suspension(null, $row);
				
				// create user suspension
				$userSuspension = UserSuspensionEditor::create($userID, $suspension->suspensionID, ($suspension->expires != 0 ? TIME_NOW + $suspension->expires : 0));
				
				// get suspension type
				$suspensionTypeObject = Suspension::getSuspensionTypeObject($suspension->suspensionType);
				
				// apply suspension
				$suspensionTypeObject->apply($user, $userSuspension, $suspension);
			}
			// reset session
			Session::resetSessions($userID);
		}
	}
}
?>