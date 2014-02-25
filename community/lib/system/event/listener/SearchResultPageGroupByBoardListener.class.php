<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Groups search results by board.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.event.listener
 * @category 	Burning Board
 */
class SearchResultPageGroupByBoardListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (SEARCH_RESULT_GROUP_BY_BOARD && isset($eventObj->search['types']) && count($eventObj->search['types']) == 1 && $eventObj->search['types'][0] == 'post' && isset($eventObj->additionalData['post']['findThreads']) && $eventObj->additionalData['post']['findThreads'] == 1) {
			require_once(WBB_DIR.'lib/data/board/Board.class.php');

			// group by board id
			$boardToMessages = array();
			foreach ($eventObj->messages as $id => $item) {
				$boardToMessages[$item['message']->boardID][] = $item;
			}
			
			// add board object to first entry
			foreach ($boardToMessages as $boardID => $messages) {
				foreach ($messages as $id => $item) {
					$boardToMessages[$boardID][$id]['board'] = new Board($boardID);
					break;
				}
			}
			
			// merge array
			$messages = array();
			foreach ($boardToMessages as $boardMessages) {
				$messages = array_merge($messages, $boardMessages);
			}
			
			$eventObj->messages = $messages;
		}
	}
}
?>