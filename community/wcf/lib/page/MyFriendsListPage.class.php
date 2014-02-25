<?php
// wcf imports
require_once(WCF_DIR.'lib/page/MembersListPage.class.php');

/**
 * Shows a list of all friends of the active user.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	page
 * @category 	Community Framework
 */
class MyFriendsListPage extends MembersListPage {
	// system
	public $templateName = 'myFriendsList';
	
	/**
	 * user id
	 * 
	 * @var	integer
	 */
	public $userID = 0;
	
	/**
	 * Creates a new MyFriendsListPage object.
	 */
	public function __construct() {
		$this->userID = WCF::getUser()->userID;
		parent::__construct();
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		SortablePage::countItems();
		if (!$this->userID) return 0;
		
		// count members
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user_whitelist
			WHERE	userID = ".$this->userID."
				AND confirmed = 1";
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * Gets user ids for active page.
	 * 
	 * @return	string
	 */
	protected function getUserIDs() {
		$userIDs = '';
		$sql = "SELECT		user.userID,
					".(!WCF::getUser()->getPermission('admin.general.canViewInvisible') ? "IF(userOption".User::getUserOptionID('invisible')." = 1, 0, lastActivityTime) AS lastActivityTimeSortField" : 'lastActivityTime AS lastActivityTimeSortField')."
			FROM		wcf".WCF_N."_user_whitelist whitelist
			LEFT JOIN	".$this->userTable." user
			ON		(user.userID = whitelist.whiteUserID)
			".$this->sqlJoins."
			WHERE		whitelist.userID = ".$this->userID."
					AND whitelist.confirmed = 1
					AND user.userID IS NOT NULL
			ORDER BY	".($this->sortField != 'lastActivity' ? 'user.' : '').$this->realSortField." ".$this->sortOrder.
			($this->realSortField != 'username' ? ', user.userID' : '');
		$result = WCF::getDB()->sendQuery($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($userIDs)) $userIDs .= ',';
			$userIDs .= $row['userID'];
		}
		
		return $userIDs;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($this->members)) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see MembersListPage::getSearchResult()
	 */
	protected function getSearchResult() {}
}
?>