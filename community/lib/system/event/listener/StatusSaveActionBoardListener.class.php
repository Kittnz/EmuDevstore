<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Listen the status save action to close/open board in board list.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.event.listener
 * @category 	Burning Board
 */
class StatusSaveActionBoardListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (preg_match('/^category(\d+)$/', $eventObj->name, $match)) {
			WCF::getUser()->closeCategory($match[1], ($eventObj->status ? -1 : 1));
			exit;
		}
	}
}
?>