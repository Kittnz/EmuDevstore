<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');

/**
 * Removes the user groups tabs from the usercp menu
 * if the user groups functions are disabled.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.group
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class UserCPMenuModeratedGroupsListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_MODERATED_USER_GROUP == 1) {
			// check available groups 
			$availableGroups = (Group::getGroupsByType(array(5, 6, 7)) ? true : false);
			
			// active user is a group leader?
			$isGroupLeader = null;//WCF::getSession()->getVar('isGroupLeader');
			if ($isGroupLeader === null) {
				$sql = "SELECT	COUNT(*) AS count
					FROM	wcf".WCF_N."_group_leader leader, wcf".WCF_N."_group usergroup
					WHERE	(leader.leaderUserID = ".WCF::getUser()->userID."
						OR leader.leaderGroupID IN (".implode(',', WCF::getUser()->getGroupIDs())."))
						AND leader.groupID = usergroup.groupID";
				$row = WCF::getDB()->getFirstRow($sql);
				$isGroupLeader = ($row['count'] ? true : false);
				
				// save status
				WCF::getSession()->register('isGroupLeader', $isGroupLeader);
			}
			
			// fix usercp menu
			if (!$availableGroups || !$isGroupLeader) {
				if (!$availableGroups && !$isGroupLeader) {
					// remove user groups tab
					if (isset($eventObj->menuItems[''])) {
						foreach ($eventObj->menuItems[''] as $key => $tab) {
							if ($tab['menuItem'] == 'wcf.user.usercp.menu.link.userGroups') {
								unset($eventObj->menuItems[''][$key]);
								break;
							}
						}
					}
				}
				else if ($availableGroups) {
					// remove group leader subtab
					foreach ($eventObj->menuItems['wcf.user.usercp.menu.link.userGroups'] as $key => $tab) {
						if ($tab['menuItem'] == 'wcf.user.usercp.menu.link.userGroups.leader') {
							unset($eventObj->menuItems['wcf.user.usercp.menu.link.userGroups'][$key]);
							break;
						}
					}
				}
				else {
					// remove user groups subtab
					foreach ($eventObj->menuItems['wcf.user.usercp.menu.link.userGroups'] as $key => $tab) {
						if ($tab['menuItem'] == 'wcf.user.usercp.menu.link.userGroups.overview') {
							unset($eventObj->menuItems['wcf.user.usercp.menu.link.userGroups'][$key]);
							break;
						}
					}
					
					// map user groups tab to group leader subtab
					foreach ($eventObj->menuItems[''] as $key => $tab) {
						if ($tab['menuItem'] == 'wcf.user.usercp.menu.link.userGroups') {
							$eventObj->menuItems[''][$key]['menuItemLink'] = 'index.php?page=UserGroupLeader'.SID_ARG_2ND_NOT_ENCODED;
							break;
						}
					}
				}
			}
		}
	}
}
?>