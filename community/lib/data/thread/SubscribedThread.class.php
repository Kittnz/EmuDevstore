<?php
// wbb imports
require_once(WBB_DIR.'lib/data/thread/ViewableThread.class.php');

/**
 * Extends ViewableThread for subscribed threads display.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.thread
 * @category 	Burning Board
 */
class SubscribedThread extends ViewableThread {
	/**
	 * Returns true, if this thread is marked.
	 */
	public function isMarked() {
		$sessionVars = WCF::getSession()->getVars();
		if (isset($sessionVars['markedSubscribedThreads'])) {
			if (in_array($this->threadID, $sessionVars['markedSubscribedThreads'])) return 1;
		}
		
		return 0;
	}
	
	/**
	 * Returns the marked threads.
	 * 
	 * @return	array
	 */
	public static function getMarkedThreads() {
		$sessionVars = WCF::getSession()->getVars();
		if (isset($sessionVars['markedSubscribedThreads']) && is_array($sessionVars['markedSubscribedThreads'])) {
			return $sessionVars['markedSubscribedThreads'];
		}
		
		return array();
	}
	
	/**
	 * Unsubscribes all marked threads.
	 */
	public static function unsubscribeMarked() {
		$markedThreads = self::getMarkedThreads();
		$ids = implode(',', $markedThreads);
		if (!empty($ids)) {
			$sql = "DELETE FROM	wbb".WBB_N."_thread_subscription
				WHERE		userID = ".WCF::getUser()->userID."
						AND threadID IN (".$ids.")";
			WCF::getDB()->sendQuery($sql);
			WCF::getSession()->unregister('hasSubscriptions');
		}
	}
	
	/**
	 * Unsubscribes all subscribed threads.
	 */
	public static function unsubscribeAll() {
		$sql = "DELETE FROM	wbb".WBB_N."_thread_subscription
			WHERE		userID = ".WCF::getUser()->userID;
		WCF::getDB()->sendQuery($sql);
		WCF::getSession()->unregister('hasSubscriptions');
	}
	
	/**
	 * Marks this thread.
	 */
	public function mark() {
		$markedThreads = self::getMarkedThreads();
		if (!in_array($this->threadID, $markedThreads)) {
			array_push($markedThreads, $this->threadID);
			WCF::getSession()->register('markedSubscribedThreads', $markedThreads);
		}
	}
	
	/**
	 * Unmarks all marked threads.
	 */
	public static function unmarkAll() {
		WCF::getSession()->unregister('markedSubscribedThreads');
	}
	
	/**
	 * Unmarks this thread.
	 */
	public function unmark() {
		$markedThreads = self::getMarkedThreads();
		if (in_array($this->threadID, $markedThreads)) {
			$key = array_search($this->threadID, $markedThreads);
			
			unset($markedThreads[$key]);
			if (count($markedThreads) == 0) {
				self::unmarkAll();
			} 
			else {
				WCF::getSession()->register('markedSubscribedThreads', $markedThreads);
			}
		}
	}
	
	/**
	 * Removes inaccessible boards and threads from user subscriptions.
	 */
	public static function clearSubscriptions() {
		$boardIDs = Board::getAccessibleBoards();
		
		// clear board subscriptions
		$sql = "DELETE FROM	wbb".WBB_N."_board_subscription
			WHERE		userID = ".WCF::getUser()->userID."
					".(!empty($boardIDs) ? "AND boardID NOT IN (".$boardIDs.")" : "");
		WCF::getDB()->sendQuery($sql);
		
		// clear thread subscriptions
		$sql = "DELETE FROM	wbb".WBB_N."_thread_subscription
			WHERE		userID = ".WCF::getUser()->userID."
					".(!empty($boardIDs) ? "AND threadID IN (
						SELECT	threadID
						FROM	wbb".WBB_N."_thread
						WHERE	boardID NOT IN (".$boardIDs.")
					)" : "");
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Returns the number of unread subscribed threads.
	 * 
	 * @return	integer
	 */
	public static function getUnreadCount() {
		$unreadCount = 0;
		$sql = "SELECT 		boardID, thread.threadID, thread.lastPostTime, thread_visit.lastVisitTime
			FROM 		wbb".WBB_N."_thread_subscription subscription
			LEFT JOIN	wbb".WBB_N."_thread thread
			ON		(thread.threadID = subscription.threadID)
			LEFT JOIN 	wbb".WBB_N."_thread_visit thread_visit
			ON 		(thread_visit.threadID = thread.threadID AND thread_visit.userID = ".WCF::getUser()->userID.")
			WHERE 		subscription.userID = ".WCF::getUser()->userID."
					AND thread.lastPostTime > ". WCF::getUser()->getLastMarkAllAsReadTime();
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($row['lastPostTime'] > $row['lastVisitTime'] && $row['lastPostTime'] > WCF::getUser()->getBoardVisitTime($row['boardID'])) {
				$unreadCount++;
			}
		}
		
		return $unreadCount;
	}
}
?>