<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Handles the boardID and threadID parameters on the users online page.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.event.listener
 * @category 	Burning Board
 */
class UsersOnlinePageParameterListener implements EventListener {
	protected $boardID = 0;
	protected $threadID = 0;
	
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if ($eventName == 'readParameters') {
			// read parameters
			if (isset($_REQUEST['boardID'])) $this->boardID = intval($_REQUEST['boardID']);
			if (isset($_REQUEST['threadID'])) $this->threadID = intval($_REQUEST['threadID']);
			
			// append sql condition
			if ($this->threadID) {
				$eventObj->usersOnlineSortedList->sqlConditions .= " AND session.threadID = ".$this->threadID." ";
			}
			else if ($this->boardID) {
				$eventObj->usersOnlineSortedList->sqlConditions .= " AND session.boardID = ".$this->boardID." ";
			}
		}
		else if ($eventName == 'assignVariables') {
			if ($this->threadID) {
				WCF::getTPL()->append('additionalParameters', '&amp;threadID='.$this->threadID);
			}
			else if ($this->boardID) {
				WCF::getTPL()->append('additionalParameters', '&amp;boardID='.$this->boardID);
			}
		}
	}
}
?>