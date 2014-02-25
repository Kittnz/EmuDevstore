<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Shows the amout of posts on pm view page.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.event.listener
 * @category 	Burning Board
 */
class PMViewPagePostsListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MESSAGE_SIDEBAR_ENABLE_USER_POSTS) {
			if ($eventName == 'readData') {
				$eventObj->pmList->sqlSelects .= 'wbb_user.posts';
				$eventObj->pmList->sqlJoins .= ' LEFT JOIN wbb'.WBB_N.'_user wbb_user
							ON (wbb_user.userID = pm.userID) ';
			}
		}
	}
}
?>