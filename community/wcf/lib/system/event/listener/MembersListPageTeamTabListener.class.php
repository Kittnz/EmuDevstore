<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Shows the team tab on members list page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.page.user.membersList.team
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class MembersListPageTeamTabListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_TEAM_LIST) {
			WCF::getCache()->addResource('teamCount', WCF_DIR.'cache/cache.teamCount.php', WCF_DIR.'lib/system/cache/CacheBuilderTeamCount.class.php', 0, 1800);
			if (WCF::getCache()->get('teamCount') > 0) {
				WCF::getTPL()->append('additionalTabs', '<li><a href="index.php?page=Team'.SID_ARG_2ND.'"><img src="'.StyleManager::getStyle()->getIconPath('teamM.png').'" alt="" /> <span>'.WCF::getLanguage()->get('wcf.user.team.title').'</span></a></li>');
			}
		}
	}
}
?>