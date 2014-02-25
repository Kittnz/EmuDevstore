<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');

/**
 * Handles the online marking during user registration.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.page.user.usersOnline
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class RegisterFormOnlineMarkingListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if ($eventName == 'validate') {
			$eventObj->additionalFields['userOnlineGroupID'] = Group::getGroupIdByType(Group::USERS);
		}
	}
}
?>