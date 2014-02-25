<?php
// wcf imports
require_once(WCF_DIR.'lib/page/MultipleLinkPage.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/Avatar.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/category/AvatarCategory.class.php');

/**
 * Shows a list of all accessible avatars.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	page
 * @category 	Community Framework
 */
class AvatarListPage extends MultipleLinkPage {
	public $sqlConditions = '';
	public $avatarID = 0;
	public $availableAvatars = array();
	public $availableAvatarCategories = array();
	public $avatarCategoryID = 0;
	public $avatarCategory = null;
	public $hasDefaultAvatars = 0;
	
	/**
	 * Creates a new AvatarListPage object.
	 */
	public function __construct($avatarID) {
		$this->avatarID = $avatarID;
		$this->sqlConditions = "userID = 0 AND groupID IN (0,".implode(',', WCF::getUser()->getGroupIDs()).") AND neededPoints <= ".intval(WCF::getUser()->activityPoints);
		parent::__construct();
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();

		// count number of avatars
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_avatar
			WHERE	".$this->sqlConditions;
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// category id
		if (isset($_REQUEST['avatarCategoryID'])) {
			$this->avatarCategoryID = intval($_REQUEST['avatarCategoryID']);
		}
		else if ($this->avatarID > 0) {
			$avatar = new Avatar($this->avatarID);
			$this->avatarCategoryID = $avatar->avatarCategoryID;
		}
		if ($this->avatarCategoryID) {
			$this->avatarCategory = new AvatarCategory($this->avatarCategoryID);
			if (($this->avatarCategory->groupID && !in_array($this->avatarCategory->groupID, WCF::getUser()->getGroupIDs())) || $this->avatarCategory->neededPoints > WCF::getUser()->activityPoints) {
				$this->avatarCategory = null;
				$this->avatarCategoryID = 0;
			}
		}
		
		$this->sqlConditions = "avatarCategoryID = ".$this->avatarCategoryID." AND ".$this->sqlConditions;
		
		// find page of the selected avatar
		if ($this->pageNo == 0 && $this->avatarID > 0) {
			$sql = "SELECT	COUNT(*) AS count
				FROM	wcf".WCF_N."_avatar
				WHERE	(".$this->sqlConditions.")
					AND (
						avatarName < (SELECT avatarName FROM wcf".WCF_N."_avatar WHERE avatarID = ".$this->avatarID.")
						OR (
							avatarName = (SELECT avatarName FROM wcf".WCF_N."_avatar WHERE avatarID = ".$this->avatarID.")
							AND avatarID < ".$this->avatarID."
						)
					)";
			$row = WCF::getDB()->getFirstRow($sql);
			$position = $row['count'] + 1;
			$this->pageNo = ceil($position / $this->itemsPerPage);
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if ($this->avatarCategoryID) {
			$sql = "SELECT	COUNT(*) AS count
				FROM	wcf".WCF_N."_avatar
				WHERE	avatarCategoryID = 0 AND userID = 0 AND groupID IN (0,".implode(',', WCF::getUser()->getGroupIDs()).") AND neededPoints <= ".intval(WCF::getUser()->activityPoints);
			$row = WCF::getDB()->getFirstRow($sql);
			$this->hasDefaultAvatars = $row['count'];
		}
		else {
			$this->hasDefaultAvatars = $this->items;
		}
		
		$this->readAvailableAvatars();
		$this->readAvailableCategories();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'avatars' => $this->availableAvatars,
			'avatarCategories' => $this->availableAvatarCategories,
			'avatarCategoryID' => $this->avatarCategoryID,
			'avatarCategory' => $this->avatarCategory,
			'hasDefaultAvatars' => $this->hasDefaultAvatars
		));
	}
	
	/**
	 * Gets a list of available avatars.
	 */
	protected function readAvailableAvatars() {
		// get avatars
		if ($this->items > 0) {
			$sql = "SELECT		*
				FROM		wcf".WCF_N."_avatar
				WHERE		".$this->sqlConditions."
				ORDER BY	avatarName, avatarID";
			$result = WCF::getDB()->sendQuery($sql, $this->itemsPerPage, (($this->pageNo - 1) * $this->itemsPerPage));
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->availableAvatars[] = new Avatar(null, $row);
			}
		}
	}
	
	/**
	 * Gets a list of available categories.
	 */
	protected function readAvailableCategories() {
		$sql = "SELECT		avatar_category.*
			FROM		wcf".WCF_N."_avatar_category avatar_category
			WHERE		avatar_category.groupID IN (0,".implode(',', WCF::getUser()->getGroupIDs()).") AND avatar_category.neededPoints <= ".intval(WCF::getUser()->activityPoints)."
			ORDER BY	avatar_category.title";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$row['title'] = WCF::getLanguage()->get($row['title']);
			$this->availableAvatarCategories[$row['avatarCategoryID']] = new AvatarCategory(null, $row);
		}
		
		// sort
		AvatarCategory::sort($this->availableAvatarCategories, 'showOrder');
	}
}
?>