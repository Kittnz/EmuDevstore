<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a user option.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option
 * @category 	Community Framework
 */
class UserOption extends DatabaseObject {
	/**
	 * Creates a new UserOption object.
	 * 
	 * @param	integer		$optionID
	 * @param	array		$row
	 */
	public function __construct($optionID, $row = null) {
		if ($optionID !== null) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_user_option
				WHERE	optionID = ".$optionID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
}
?>