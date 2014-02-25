<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Applies the user online marking to message sidebar.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.page.user.usersOnline
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class MessageSidebarFactoryUserOnlineMarkingListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_USERS_ONLINE == 1 && MESSAGE_SIDEBAR_ENABLE_USER_ONLINE_MARKING == 1) {
			// get cached groups
			WCF::getCache()->addResource('groups', WCF_DIR.'cache/cache.groups.php', WCF_DIR.'lib/system/cache/CacheBuilderGroups.class.php');
			$groups = WCF::getCache()->get('groups', 'groups');

			foreach ($eventObj->messageSidebars as $id => $sidebar) {
				if ($sidebar->getUser()->userID && isset($groups[$sidebar->getUser()->userOnlineGroupID]) && $groups[$sidebar->getUser()->userOnlineGroupID]['userOnlineMarking'] != '%s') {
					$sidebar->usernameStyle = $groups[$sidebar->getUser()->userOnlineGroupID]['userOnlineMarking'];
				}
			}
		}
	}
}
?>