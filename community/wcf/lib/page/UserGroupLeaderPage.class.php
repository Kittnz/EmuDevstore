<?php
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');

/**
 * Shows the user group leader page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.group
 * @subpackage	page
 * @category 	Community Framework (commercial)
 */
class UserGroupLeaderPage extends SortablePage {
	public $templateName = 'userGroupLeader';
	public $itemsPerPage = 50;
	public $defaultSortField = 'applicationTime';
	public $defaultSortOrder = 'DESC';
	public $groups = array();
	public $applications = array();
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readGroups();
		if (!count($this->groups)) {
			throw new PermissionDeniedException();
		}
		$this->readApplications();
		WCF::getSession()->unregister('outstandingGroupApplications');
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'groups' => $this->groups,
			'applications' => $this->applications,
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// check permission
		if (!WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		if (MODULE_MODERATED_USER_GROUP != 1) {
			throw new IllegalLinkException();
		}
		
		// set active tab
		UserCPMenu::getInstance()->setActiveMenuItem('wcf.user.usercp.menu.link.userGroups.leader');
		
		parent::show();
	}
	
	/**
	 * Gets a list of all by the active user leaded groups.
	 */
	protected function readGroups() {
		$sql = "SELECT		usergroup.*, (
						SELECT	COUNT(*)
						FROM	wcf".WCF_N."_user_to_groups
						WHERE	groupID = usergroup.groupID
					) AS members
			FROM 		wcf".WCF_N."_group usergroup
			WHERE		groupID IN (
						SELECT	groupID
						FROM	wcf".WCF_N."_group_leader
						WHERE	leaderUserID = ".WCF::getUser()->userID."
							OR leaderGroupID IN (".implode(',', WCF::getUser()->getGroupIDs()).")
					)
			ORDER BY 	groupName";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->groups[] = $row;
		}
	}
	
	/**
	 * Gets a list of all applications.
	 */
	protected function readApplications() {
		if ($this->items) {
			$sql = "SELECT		usergroup.*, application.*, user.username
				FROM 		wcf".WCF_N."_group_application application
				LEFT JOIN 	wcf".WCF_N."_group usergroup
				ON 		(usergroup.groupID = application.groupID)
				LEFT JOIN 	wcf".WCF_N."_user user
				ON 		(user.userID = application.userID)
				WHERE 		application.groupID IN (
							SELECT	groupID
							FROM	wcf".WCF_N."_group_leader
							WHERE	leaderUserID = ".WCF::getUser()->userID."
								OR leaderGroupID IN (".implode(',', WCF::getUser()->getGroupIDs()).")
						)
				ORDER BY	".$this->sortField." ".$this->sortOrder;
			$result = WCF::getDB()->sendQuery($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->applications[] = $row;
			}
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		$sql = "SELECT		COUNT(*) AS count
			FROM 		wcf".WCF_N."_group_application
			WHERE 		groupID IN (
						SELECT	groupID
						FROM	wcf".WCF_N."_group_leader
						WHERE	leaderUserID = ".WCF::getUser()->userID."
							OR leaderGroupID IN (".implode(',', WCF::getUser()->getGroupIDs()).")
					)";
		$result = WCF::getDB()->getFirstRow($sql);
		return $result['count'];
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'username':
			case 'groupName':
			case 'applicationTime':
			case 'applicationStatus':break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
}
?>