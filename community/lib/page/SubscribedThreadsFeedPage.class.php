<?php
// wbb imports
require_once(WBB_DIR.'lib/page/ThreadsFeedPage.class.php');

/**
 * Prints a list of subscribed threads as a rss or an atom feed.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
class SubscribedThreadsFeedPage extends ThreadsFeedPage {
	/**
	 * list of threads
	 * 
	 * @var	array<FeedThread>
	 */
	public $threads = array();
	
	/**
	 * Gets the threads for the feed.
	 */
	protected function readThreads() {
		// accessible boards
		$accessibleBoardIDArray = Board::getAccessibleBoardIDArray(array('canViewBoard', 'canEnterBoard', 'canReadThread'));
		
		// get threads
		if (count($accessibleBoardIDArray)) {
			$sql = "SELECT		post.*, thread.*
				FROM		wbb".WBB_N."_thread_subscription subscription
				LEFT JOIN	wbb".WBB_N."_thread thread
				ON		(thread.threadID = subscription.threadID)
				LEFT JOIN	wbb".WBB_N."_post post
				ON		(post.postID = thread.firstPostID)
				WHERE		subscription.userID = ".WCF::getUser()->userID."
						AND thread.boardID IN (".implode(',', $accessibleBoardIDArray).")
						AND thread.isDeleted = 0
						AND thread.isDisabled = 0
						AND thread.movedThreadID = 0
						AND thread.time > ".($this->hours ? (TIME_NOW - $this->hours * 3600) : (TIME_NOW - 30 * 86400))."
				ORDER BY	thread.time DESC";
			$result = WCF::getDB()->sendQuery($sql, $this->limit);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->threads[] = new FeedThread($row);
			}
		}
	}
}
?>