<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Resets style id in session after user profile changes.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.event.listener
 * @category 	Burning Board
 */
class UserProfileEditFormStyleListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (isset($eventObj->additionalFields['styleID']) && WCF::getSession()->getStyleID() != 0) {
			// reset session style
			WCF::getSession()->setStyleID(0);
		}
	}
}
?>