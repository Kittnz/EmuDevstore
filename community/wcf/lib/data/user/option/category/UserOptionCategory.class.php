<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a user option category.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option.category
 * @category 	Community Framework
 */
class UserOptionCategory extends DatabaseObject {
	/**
	 * Creates a new UserOptionCategory object.
	 *
	 * @param	integer		$categoryID
	 * @param	array<mixed>	$row
	 */
	public function __construct($categoryID, $row = null) {
		if ($categoryID !== null) {
			$sql = "SELECT	option_category.*,
					(SELECT COUNT(DISTINCT optionName) FROM wcf".WCF_N."_user_option WHERE categoryName = option_category.categoryName) AS options
				FROM	wcf".WCF_N."_user_option_category option_category
				WHERE	option_category.categoryID = ".$categoryID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Returns the title of this category.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return $this->categoryName;
	}
}
?>