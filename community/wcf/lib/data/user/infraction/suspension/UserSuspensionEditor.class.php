<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/infraction/suspension/UserSuspension.class.php');

/**
 * Provides functions to add, edit and delete user suspensions.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	data.user.infraction.suspension
 * @category 	Community Framework (commercial)
 */
class UserSuspensionEditor extends UserSuspension {
	/**
	 * Creates a new user suspension.
	 *
	 * @param	integer		$userID
	 * @param	integer		$suspensionID
	 * @param	integer		$expires
	 * @param	integer		$time
	 * @param 	integer		$packageID
	 * @return	UserSuspensionEditor
	 */
	public static function create($userID, $suspensionID, $expires, $time = TIME_NOW, $packageID = PACKAGE_ID) {
		$sql = "INSERT INTO	wcf".WCF_N."_user_infraction_suspension_to_user
					(packageID, userID, suspensionID, time, expires)
			VALUES		(".$packageID.", ".$userID.", ".$suspensionID.", ".$time.", ".$expires.")";
		WCF::getDB()->sendQuery($sql);
		
		$userSuspensionID = WCF::getDB()->getInsertID("wcf".WCF_N."_user_infraction_suspension_to_user", 'userSuspensionID');
		return new UserSuspensionEditor($userSuspensionID);
	}
	
	/**
	 * Updates this suspension.
	 *
	 * @param	integer		$userID
	 * @param	integer		$suspensionID
	 * @param	integer		$expires
	 */
	public function update($userID, $suspensionID, $expires) {
		$sql = "UPDATE	wcf".WCF_N."_user_infraction_suspension_to_user
			SET	userID = ".$userID.",
				suspensionID = ".$suspensionID.",
				expires = ".$expires."
			WHERE	userSuspensionID = ".$this->userSuspensionID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Deletes this suspension.
	 */
	public function delete() {
		$sql = "DELETE FROM	wcf".WCF_N."_user_infraction_suspension_to_user
			WHERE		userSuspensionID = ".$this->userSuspensionID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>