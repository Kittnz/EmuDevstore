<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObjectList.class.php');
require_once(WCF_DIR.'lib/data/user/option/category/UserOptionCategory.class.php');

/**
 * Represents an list of user option categories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option.category
 * @category 	Community Framework
 */
class UserOptionCategoryList extends DatabaseObjectList {
	/**
	 * active package id
	 * 
	 * @var	integer
	 */
	protected $packageID = 0;
	
	/**
	 * list of category ids used by active package.
	 * 
	 * @var	array<integer> 
	 */
	protected $categoryIDArray = array();
	
	/**
	 * list of user option categories
	 * 
	 * @var array<UserOptionCategory>
	 */
	public $categories = array();

	/**
	 * Creates a new UserOptionCategoryList object.
	 * 
	 * @param	integer		$packageID
	 */
	public function __construct($packageID = PACKAGE_ID) {
		$this->packageID = $packageID;
		$this->readCategoryIDArray();
	}
	
	/**
	 * Gets category ids used by active package.
	 */
	protected function readCategoryIDArray() {
		$sql = "SELECT		categoryName, categoryID 
			FROM		wcf".WCF_N."_user_option_category option_category,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		option_category.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".$this->packageID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->categoryIDArray[$row['categoryName']] = $row['categoryID'];
		}
	}
	
	/**
	 * @see DatabaseObjectList::countObjects()
	 */
	public function countObjects() {
		if (!count($this->categoryIDArray)) return 0;
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user_option_category option_category
			WHERE	option_category.categoryID IN (".implode(',', $this->categoryIDArray).")
			".(!empty($this->sqlConditions) ? "AND (".$this->sqlConditions.")" : '');
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * @see DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		if (!count($this->categoryIDArray)) return;
		
		$sql = "SELECT		".(!empty($this->sqlSelects) ? $this->sqlSelects.',' : '')."
					option_category.*,
					(SELECT COUNT(DISTINCT optionName) FROM wcf".WCF_N."_user_option WHERE categoryName = option_category.categoryName) AS options
			FROM		wcf".WCF_N."_user_option_category option_category
			".$this->sqlJoins."
			WHERE		option_category.categoryID IN (".implode(',', $this->categoryIDArray).")
			".(!empty($this->sqlConditions) ? "AND ".$this->sqlConditions : '')."
			".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
		$result = WCF::getDB()->sendQuery($sql, $this->sqlLimit, $this->sqlOffset);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->categories[] = new UserOptionCategory(null, $row);
		}
	}
	
	/**
	 * @see DatabaseObjectList::getObjects()
	 */
	public function getObjects() {
		return $this->categories;
	}
}
?>