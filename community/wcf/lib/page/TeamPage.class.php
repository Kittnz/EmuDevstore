<?php
require_once(WCF_DIR.'lib/page/MembersListPage.class.php');

/**
 * Shows a list of all team members.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.page.user.membersList.team
 * @subpackage	page
 * @category 	Community Framework (commercial)
 */
class TeamPage extends MembersListPage {
	public $templateName = 'team';
	public $groupedMembers = array();
	
	/**
	 * @see MembersListPage::getSearchResult()
	 */
	protected function getSearchResult() {}
	
	/**
	 * @see MembersListPage::countItems()
	 */
	public function countItems() {
		return MultipleLinkPage::countItems();
	}
	
	/**
	 * @see MembersListPage::getMembers()
	 */
	protected function readMembers() {
		$sql = "SELECT		".$this->sqlSelects."
					".(in_array('language', $this->activeFields) ? "language.languageCode," : '')."
					avatar.*, user.*, rank.*,
					usergroup.groupID, usergroup.groupName,
					".(!WCF::getUser()->getPermission('admin.general.canViewInvisible') ? "IF(userOption".User::getUserOptionID('invisible')." = 1, 0, lastActivityTime) AS lastActivityTimeSortField" : 'lastActivityTime AS lastActivityTimeSortField').",
					".(TEAM_SHOW_GROUP_LEADERS ? "group_leader.leaderUserID" : 0)." AS isGroupLeader
 			FROM 		wcf".WCF_N."_group usergroup
 			LEFT JOIN 	wcf".WCF_N."_user_to_groups user_to_groups 
			ON		(user_to_groups.groupID = usergroup.groupID)
			LEFT JOIN 	".$this->userTable." user 
			ON		(user.userID = user_to_groups.userID)
			".$this->sqlJoins."
 			LEFT JOIN	wcf".WCF_N."_avatar avatar
			ON		(avatar.avatarID = ".$this->userTableAlias.".avatarID)
			LEFT JOIN 	wcf".WCF_N."_user_rank rank
			ON		(rank.rankID = ".$this->userTableAlias.".rankID)
			".(in_array('language', $this->activeFields) ? "
			LEFT JOIN	wcf".WCF_N."_language language
			ON		(language.languageID = ".$this->userTableAlias.".languageID)
			" : '')."
			".(TEAM_SHOW_GROUP_LEADERS ? "
			LEFT JOIN 	wcf".WCF_N."_group_leader group_leader
			ON		(group_leader.leaderUserID = ".$this->userTableAlias.".userID AND group_leader.groupID = usergroup.groupID)
			" : '')."
			WHERE 		usergroup.showOnTeamPage = 1
			ORDER BY 	usergroup.teamPagePosition,
					".($this->sortField != 'lastActivity' ? 'user.' : '').$this->realSortField." ".$this->sortOrder;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!$row['userID']) continue;
			$member = $this->getMember($row);
			$userID = $member['user']->userID;
			if (TEAM_SHOW_MULTIPLE_MEMBERSHIPS || !isset($this->members[$userID])) {
				if (!isset($this->groupedMembers[$row['groupID']])) {
					$this->groupedMembers[$row['groupID']] = array('members' => array(), 'leaders' => array(), 'groupName' => $row['groupName']);
				}
			
				if (!isset($this->members[$userID])) {
					$this->members[$userID] = $member;
				}
				$this->groupedMembers[$row['groupID']][($row['isGroupLeader'] ? 'leaders' : 'members')][] =& $this->members[$userID];
			}
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		SortablePage::assignVariables();
		
		// show page
		WCF::getTPL()->assign(array(
			'members' => $this->groupedMembers,
			'fields' => $this->activeFields,
			'header' => $this->headers,
			'hasFriends' => self::hasFriends()
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (MODULE_TEAM_LIST != 1) {
			throw new IllegalLinkException();
		}
		
		parent::show();
	}
}
?>