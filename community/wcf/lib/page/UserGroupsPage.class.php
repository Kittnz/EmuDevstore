<?php
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');

/**
 * Shows the list of available user groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.group
 * @subpackage	page
 * @category 	Community Framework (commercial)
 */
class UserGroupsPage extends AbstractPage {
	public $templateName = 'userGroups';
	public $memberships = array();
	public $openGroups = array();
	public $applications = array();
	public $groupLeaders = array();
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readMemberships();
		$this->readOpenGroups();
		$this->readApplications();
		$this->readGroupLeaders();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'memberships' => $this->memberships,
			'openGroups' => $this->openGroups,
			'applications' => $this->applications,
			'groupLeaders' => $this->groupLeaders
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
		UserCPMenu::getInstance()->setActiveMenuItem('wcf.user.usercp.menu.link.userGroups.overview');
		
		parent::show();
	}
	
	/**
	 * Gets a list of all memberships of the active user.
	 */
	protected function readMemberships() {
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_group
			WHERE		groupID IN (".implode(',', WCF::getUser()->getGroupIDs()).")
					AND groupType > 3
			ORDER BY	groupName";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->memberships[] = $row;
		}
	}
	
	/**
	 * Gets a list of all available groups.
	 */
	protected function readOpenGroups() {
		$sql = "SELECT		*
			FROM 		wcf".WCF_N."_group
			WHERE		groupID NOT IN (".implode(',', WCF::getUser()->getGroupIDs()).") 		
					AND groupType BETWEEN 5 AND 7
					AND groupID NOT IN (
						SELECT	groupID
						FROM	wcf".WCF_N."_group_application
						WHERE	userID = ".WCF::getUser()->userID."
					)
			ORDER BY 	groupName";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->openGroups[] = $row;
		}
	}
	
	/**
	 * Gets a list of all applications.
	 */
	protected function readApplications() {
		$sql = "SELECT		usergroup.*, application.*
			FROM 		wcf".WCF_N."_group_application application
			LEFT JOIN 	wcf".WCF_N."_group usergroup
			ON 		(usergroup.groupID = application.groupID)
			WHERE 		application.userID = ".WCF::getUser()->userID."
					AND application.groupID NOT IN (".implode(',', WCF::getUser()->getGroupIDs()).")
			ORDER BY	groupName ASC";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->applications[] = $row;
		}
	}
	
	/**
	 * Gets a list of all group leaders.
	 */
	protected function readGroupLeaders() {
		$sql = "SELECT		leader.*,
					user_table.username, usergroup.groupName,
					CASE WHEN user_table.username IS NOT NULL THEN user_table.username ELSE usergroup.groupName END AS name
			FROM 		wcf".WCF_N."_group_leader leader
			LEFT JOIN 	wcf".WCF_N."_user user_table
			ON		(user_table.userID = leader.leaderUserID)
			LEFT JOIN 	wcf".WCF_N."_group usergroup
			ON		(usergroup.groupID = leader.leaderGroupID)
			ORDER BY 	name";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!isset($this->groupLeaders[$row['groupID']])) $this->groupLeaders[$row['groupID']] = array();
			if ($row['leaderUserID']) $this->groupLeaders[$row['groupID']][] = new User(null, $row);
			else $this->groupLeaders[$row['groupID']][] = new Group(null, $row);
		}
	}
}
?>