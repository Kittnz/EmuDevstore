<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/option/category/UserOptionCategory.class.php');

/**
 * Provides functions to add, edit and delete user option categories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option.category
 * @category 	Community Framework
 */
class UserOptionCategoryEditor extends UserOptionCategory {
	/**
	 * Creates a new category.
	 * 
	 * @param 	string		$name
	 * @param 	string		$parentName
	 * @param 	string		$smallIcon
	 * @param 	string		$mediumIcon
	 * @param	integer		$showOrder
	 * @param	integer		$packageID
	 * @return	UserOptionCategoryEditor
	 */
	public static function create($name, $parentName, $smallIcon = '', $mediumIcon = '', $showOrder = 0, $packageID = PACKAGE_ID) {
		$sql = "INSERT INTO	wcf".WCF_N."_user_option_category
					(categoryName, packageID, categoryIconS, categoryIconM, parentCategoryName, showOrder)
			VALUES		('".escapeString($name)."', ".$packageID.", '".escapeString($smallIcon)."', '".escapeString($mediumIcon)."','".escapeString($parentName)."', ".$showOrder.")";
		WCF::getDB()->sendQuery($sql);
		
		$categoryID = WCF::getDB()->getInsertID("wcf".WCF_N."_user_option_category", 'categoryID');
		return new UserOptionCategoryEditor($categoryID);
	}
	
	/**
	 * Updates this category.
	 * 
	 * @param 	string		$name
	 * @param 	string		$parentName
	 * @param 	string		$smallIcon
	 * @param 	string		$mediumIcon
	 * @param	integer		$showOrder
	 */
	public function update($name, $parentName, $smallIcon = '', $mediumIcon = '', $showOrder = 0) {
		$sql = "UPDATE	wcf".WCF_N."_user_option_category
			SET	categoryName = '".escapeString($name)."',
				parentCategoryName = '".escapeString($parentName)."',
				categoryIconS = '".escapeString($smallIcon)."',
				categoryIconM = '".escapeString($mediumIcon)."',
				showOrder = ".$showOrder."
			WHERE	categoryID = ".$this->categoryID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Deletes this category.
	 */
	public function delete() {
		$sql = "DELETE FROM	wcf".WCF_N."_user_option_category
			WHERE		categoryID = ".$this->categoryID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>