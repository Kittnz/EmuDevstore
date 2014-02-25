<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/infraction/suspension/Suspension.class.php');

/**
 * Provides functions to add, edit and delete suspensions.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	data.user.infraction.suspension
 * @category 	Community Framework (commercial)
 */
class SuspensionEditor extends Suspension {
	/**
	 * Creates a new suspension.
	 * 
	 * @param 	string		$title
	 * @param	integer		$points
	 * @param	string		$suspensionType
	 * @param	array		$suspensionData
	 * @param	integer		$expires
	 * @param	integer		$packageID
	 * @return	SuspensionEditor
	 */
	public static function create($title, $points, $suspensionType, $suspensionData = array(), $expires = 0, $packageID = PACKAGE_ID) {
		$sql = "INSERT INTO	wcf".WCF_N."_user_infraction_suspension
					(title, points, expires, suspensionType, suspensionData, packageID)
			VALUES		('".escapeString($title)."', ".$points.", ".$expires.", '".escapeString($suspensionType)."', '".escapeString(serialize($suspensionData))."', ".$packageID.")";
		WCF::getDB()->sendQuery($sql);
		
		$suspensionID = WCF::getDB()->getInsertID("wcf".WCF_N."_user_infraction_suspension", 'suspensionID');
		return new SuspensionEditor($suspensionID);
	}
	
	/**
	 * Updates this suspension.
	 * 
	 * @param 	string		$title
	 * @param	integer		$points
	 * @param	string		$suspensionType
	 * @param	array		$suspensionData
	 * @param	integer		$expires
	 */
	public function update($title, $points, $suspensionType, $suspensionData = array(), $expires = 0) {
		$sql = "UPDATE	wcf".WCF_N."_user_infraction_suspension
			SET	title = '".escapeString($title)."',
				points = ".$points.",
				expires = ".$expires.",
				suspensionType = '".escapeString($suspensionType)."',
				suspensionData = '".escapeString(serialize($suspensionData))."'
			WHERE	suspensionID = ".$this->suspensionID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Deletes this suspension.
	 */
	public function delete() {
		$sql = "DELETE FROM	wcf".WCF_N."_user_infraction_suspension
			WHERE		suspensionID = ".$this->suspensionID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>