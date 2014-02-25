<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Updates notification option of user subscriptions.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.event.listener
 * @category 	Burning Board
 */
class UserEditFormEmailNotificationListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (isset($eventObj->activeOptions['enableEmailNotification']) && $eventObj->user->enableEmailNotification != $eventObj->activeOptions['enableEmailNotification']['optionValue']) {
			$sql = "UPDATE	wbb".WBB_N."_board_subscription
				SET	enableNotification = ".$eventObj->activeOptions['enableEmailNotification']['optionValue']."
				WHERE	userID = ".$eventObj->user->userID;
			WCF::getDB()->registerShutdownUpdate($sql);
			
			$sql = "UPDATE	wbb".WBB_N."_thread_subscription
				SET	enableNotification = ".$eventObj->activeOptions['enableEmailNotification']['optionValue']."
				WHERE	userID = ".$eventObj->user->userID;
			WCF::getDB()->registerShutdownUpdate($sql);
		}
	}
}
?>