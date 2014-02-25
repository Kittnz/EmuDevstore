<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/infraction/warning/Warning.class.php');

/**
 * Provides functions to add, edit and delete warnings.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	data.user.infraction.warning
 * @category 	Community Framework (commercial)
 */
class WarningEditor extends Warning {
	/**
	 * Creates a new warning.
	 * 
	 * @param 	string		$title
	 * @param	integer		$points
	 * @param	integer		$expires
	 * @param	integer		$packageID
	 * @return	WarningEditor
	 */
	public static function create($title, $points = 0, $expires = 0, $packageID = PACKAGE_ID) {
		$sql = "INSERT INTO	wcf".WCF_N."_user_infraction_warning
					(title, points, expires, packageID)
			VALUES		('".escapeString($title)."', ".$points.", ".$expires.", ".$packageID.")";
		WCF::getDB()->sendQuery($sql);
		
		$warningID = WCF::getDB()->getInsertID("wcf".WCF_N."_user_infraction_warning", 'warningID');
		return new WarningEditor($warningID);
	}
	
	/**
	 * Updates this warning.
	 * 
	 * @param 	string		$title
	 * @param	integer		$points
	 * @param	integer		$expires
	 */
	public function update($title, $points = 0, $expires = 0) {
		$sql = "UPDATE	wcf".WCF_N."_user_infraction_warning
			SET	title = '".escapeString($title)."',
				points = ".$points.",
				expires = ".$expires."
			WHERE	warningID = ".$this->warningID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Deletes this warning.
	 */
	public function delete() {
		$sql = "DELETE FROM	wcf".WCF_N."_user_infraction_warning
			WHERE		warningID = ".$this->warningID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>