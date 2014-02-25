<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents an avatar category.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.user.avatar.category
 * @category 	Community Framework
 */
class AvatarCategory extends DatabaseObject {
	/**
	 * Creates a new AvatarCategory object.
	 * 
	 * @param	integer		$avatarCategoryID
	 * @param	array<mixed>	$row
	 */
	public function __construct($avatarCategoryID, $row = null) {
		if ($avatarCategoryID !== null) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_avatar_category
				WHERE	avatarCategoryID = ".$avatarCategoryID;
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
		return $this->title;
	}
	
	/**
	 * Returns a list of avatar categories.
	 * 
	 * @return	array<AvatarCategory>
	 */
	public static function getAvatarCategories() {
		$categories = array();
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_avatar_category
			ORDER BY	title";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$row['title'] = WCF::getLanguage()->get($row['title']);
			$categories[$row['avatarCategoryID']] = new AvatarCategory(null, $row);
		}
		
		// sort
		self::sort($categories, 'title');
		
		return $categories;
	}
}
?>