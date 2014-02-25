<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObjectList.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/Avatar.class.php');

/**
 * Represents a list of avatars.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.user.avatar
 * @category 	Community Framework
 */
class AvatarList extends DatabaseObjectList {
	/**
	 * list of avatars
	 * 
	 * @var array<Avatar>
	 */
	public $avatars = array();

	/**
	 * @see DatabaseObjectList::countObjects()
	 */
	public function countObjects() {
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_avatar avatar
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : '');
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * @see DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		$sql = "SELECT		".(!empty($this->sqlSelects) ? $this->sqlSelects.',' : '')."
					avatar.*, avatar_category.title AS avatarCategoryTitle,
					user_table.username, user_table.disableAvatar, usergroup.groupName
			FROM		wcf".WCF_N."_avatar avatar
			LEFT JOIN	wcf".WCF_N."_avatar_category avatar_category
			ON		(avatar_category.avatarCategoryID = avatar.avatarCategoryID)
			LEFT JOIN	wcf".WCF_N."_group usergroup
			ON		(usergroup.groupID = avatar.groupID)
			LEFT JOIN	wcf".WCF_N."_user user_table
			ON		(user_table.userID = avatar.userID)
			".$this->sqlJoins."
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : '')."
			".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
		$result = WCF::getDB()->sendQuery($sql, $this->sqlLimit, $this->sqlOffset);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->avatars[] = new Avatar(null, $row);
		}
	}
	
	/**
	 * @see DatabaseObjectList::getObjects()
	 */
	public function getObjects() {
		return $this->avatars;
	}
}
?>