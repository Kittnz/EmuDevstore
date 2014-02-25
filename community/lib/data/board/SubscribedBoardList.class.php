<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');

/**
 * Shows the list of subscribed boards.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.board
 * @category 	Burning Board
 */
class SubscribedBoardList {
	public $boards = array();
	public $unreadThreadsCount = array();
	public $lastPosts = array();
	
	/**
	 * Gets unread threads of subscribed boards.
	 */
	protected function readUnreadThreads() {
		$sql = "SELECT 		boardID, thread.threadID, thread.lastPostTime, thread_visit.lastVisitTime
			FROM 		wbb".WBB_N."_thread thread
			LEFT JOIN 	wbb".WBB_N."_thread_visit thread_visit
			ON 		(thread_visit.threadID = thread.threadID AND thread_visit.userID = ".WCF::getUser()->userID.")
			WHERE 		thread.lastPostTime > ". WCF::getUser()->getLastMarkAllAsReadTime()."
					AND isDeleted = 0
					AND isDisabled = 0
					AND movedThreadID = 0"
					.(count(WCF::getSession()->getVisibleLanguageIDArray()) ? " AND thread.languageID IN (".implode(',', WCF::getSession()->getVisibleLanguageIDArray()).")" : "")."
					AND boardID IN (
						SELECT	boardID
						FROM	wbb".WBB_N."_board_subscription
						WHERE	userID = ".WCF::getUser()->userID."
					)";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($row['lastPostTime'] > $row['lastVisitTime'] && $row['lastPostTime'] > WCF::getUser()->getBoardVisitTime($row['boardID'])) {
				if (!isset($this->unreadThreadsCount[$row['boardID']])) $this->unreadThreadsCount[$row['boardID']] = 0;
				$this->unreadThreadsCount[$row['boardID']]++;
			}
		}
	}
	
	/**
	 * Gets subscribed boards.
	 */
	protected function readBoards() {
		$sql = "SELECT		board.*
			FROM		wbb".WBB_N."_board_subscription subscription
			LEFT JOIN	wbb".WBB_N."_board board
			ON		(board.boardID = subscription.boardID)
			WHERE		subscription.userID = ".WCF::getUser()->userID."
			ORDER BY	board.title";
		$this->boards = WCF::getDB()->getResultList($sql);
	}

	/**
	 * Renders the list of boards.
	 */
	public function renderBoards() {
		// get unread threads
		$this->readUnreadThreads();
		
		// get boards
		$this->readBoards();

		// assign data
		WCF::getTPL()->assign('boards', $this->boards);
		WCF::getTPL()->assign('unreadThreadsCount', $this->unreadThreadsCount);
		
		// show newest posts
		if (BOARD_LIST_ENABLE_LAST_POST) {
			$lastPosts = WCF::getCache()->get('boardData', 'lastPosts');
			
			if (is_array($lastPosts)) {
				$visibleLanguages = false;
				if (count(WCF::getSession()->getVisibleLanguageIDArray())) {
					$visibleLanguages = WCF::getSession()->getVisibleLanguageIDArray();
				}
				
				foreach ($lastPosts as $boardID => $languages) {
					foreach ($languages as $languageID => $row) {
						if (!$languageID || !$visibleLanguages || in_array($languageID, $visibleLanguages)) {
							$this->lastPosts[$row['boardID']] = new DatabaseObject($row);
							continue 2;
						}
					}
				}
			}
			
			WCF::getTPL()->assign('lastPosts', $this->lastPosts);
		}
		
		// stats
		if (BOARD_LIST_ENABLE_STATS) {
			WCF::getTPL()->assign('boardStats', WCF::getCache()->get('boardData', 'counts'));
		}
	}
}
?>