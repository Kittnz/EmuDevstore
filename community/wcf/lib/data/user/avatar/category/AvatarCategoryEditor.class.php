<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/avatar/category/AvatarCategory.class.php');

/**
 * Provides functions to create and edit the data of an avatar category.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.user.avatar.category
 * @category 	Community Framework
 */
class AvatarCategoryEditor extends AvatarCategory {
	/**
	 * Deletes this avatar category.
	 */
	public function delete() {
		// delete database entry
		$sql = "DELETE FROM	wcf".WCF_N."_avatar_category
			WHERE		avatarCategoryID = ".$this->avatarCategoryID;
		WCF::getDB()->sendQuery($sql);
		
		// update avatars
		$sql = "UPDATE	wcf".WCF_N."_avatar
			SET	avatarCategoryID = 0
			WHERE	avatarCategoryID = ".$this->avatarCategoryID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Creates a new avatar category.
	 * 
	 * @param	string		$title
	 * @param	string		$showOrder
	 * @param	integer		$groupID
	 * @param	integer		$neededPoints
	 * @return	AvatarCategoryEditor
	 */
	public static function create($title, $showOrder, $groupID = 0, $neededPoints = 0) {
		$sql = "INSERT INTO	wcf".WCF_N."_avatar_category
					(title, showOrder, groupID, neededPoints)
			VALUES		('".escapeString($title)."', ".$showOrder.", ".$groupID.", ".$neededPoints.")";
		WCF::getDB()->sendQuery($sql);
		$avatarCategoryID = WCF::getDB()->getInsertID("wcf".WCF_N."_avatar_category", 'avatarCategoryID');
		
		return new AvatarCategoryEditor($avatarCategoryID);
	}
	
	/**
	 * Updates this avatar category.
	 * 
	 * @param	string		$title
	 * @param	string		$showOrder
	 * @param	integer		$groupID
	 * @param	integer		$neededPoints
	 */
	public function update($title, $showOrder, $groupID = 0, $neededPoints = 0) {
		$sql = "UPDATE	wcf".WCF_N."_avatar_category
			SET	title = '".escapeString($title)."',
				showOrder = ".$showOrder.",
				groupID = ".$groupID.",
				neededPoints = ".$neededPoints."
			WHERE	avatarCategoryID = ".$this->avatarCategoryID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>