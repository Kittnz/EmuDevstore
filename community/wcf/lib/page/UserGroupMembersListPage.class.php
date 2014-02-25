<?php
require_once(WCF_DIR.'lib/page/MultipleLinkPage.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');

/**
 * Shows a list of user group members.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.group
 * @subpackage	page
 * @category 	Community Framework (commercial)
 */
class UserGroupMembersListPage extends MultipleLinkPage {
	public $itemsPerPage = 52;
	public $groupID;
	public $members = array();
	
	/**
	 * Creates a new UserGroupMembersListPage object.
	 * 
	 * @param	integer 	$groupID
	 */
	public function __construct($groupID) {
		$this->groupID = $groupID;
		parent::__construct();
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readMembers();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'members' => $this->members
		));
	}
	
	/**
	 * Gets a list of all group members.
	 */
	protected function readMembers() {
		if ($this->items) {
			$sql = "SELECT		userID, username
				FROM 		wcf".WCF_N."_user
				WHERE		userID IN (
							SELECT	userID
							FROM	wcf".WCF_N."_user_to_groups
							WHERE	groupID = ".$this->groupID."
						)
				ORDER BY	username";
			$result = WCF::getDB()->sendQuery($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->members[] = $row;
			}
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		$sql = "SELECT		COUNT(*) AS count
			FROM 		wcf".WCF_N."_user
			WHERE		userID IN (
						SELECT	userID
						FROM	wcf".WCF_N."_user_to_groups
						WHERE	groupID = ".$this->groupID."
					)";
		$result = WCF::getDB()->getFirstRow($sql);
		return $result['count'];
	}
}
?>