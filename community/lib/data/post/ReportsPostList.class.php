<?php
require_once(WBB_DIR.'lib/data/post/ModerationPostList.class.php');

/**
 * ReportsPostList displays a list of reported posts. 
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.post
 * @category 	Burning Board
 */
class ReportsPostList extends ModerationPostList {
	/**
	 * @see PostList::countPosts()
	 */
	public function countPosts() {
		$boardIDs = Board::getModeratedBoards('canEditPost');
		$boardIDs2 = Board::getModeratedBoards('canReadDeletedPost');
		
		if (!empty($boardIDs)) {
			$sql = "SELECT		COUNT(*) AS count
				FROM		wbb".WBB_N."_post_report report
				LEFT JOIN	wbb".WBB_N."_post post
				ON		(post.postID = report.postID)
				LEFT JOIN	wbb".WBB_N."_thread thread
				ON		(thread.threadID = post.threadID)
				WHERE		thread.boardID IN (".$boardIDs.")
						AND (post.isDeleted = 0".(!empty($boardIDs2) ? " OR thread.boardID IN (".$boardIDs2.")" : '').")";
			$row = WCF::getDB()->getFirstRow($sql);
			return $row['count'];
		}
		
		return 0;
	}
	
	/**
	 * @see PostList::readPostIDs()
	 */
	protected function readPostIDs() {
		$boardIDs = Board::getModeratedBoards('canEditPost');
		$boardIDs2 = Board::getModeratedBoards('canReadDeletedPost');
		
		if (!empty($boardIDs)) {
			$sql = "SELECT		report.postID
				FROM		wbb".WBB_N."_post_report report
				LEFT JOIN	wbb".WBB_N."_post post
				ON		(post.postID = report.postID)
				LEFT JOIN	wbb".WBB_N."_thread thread
				ON		(thread.threadID = post.threadID)
				WHERE		thread.boardID IN (".$boardIDs.")
						AND (post.isDeleted = 0".(!empty($boardIDs2) ? " OR thread.boardID IN (".$boardIDs2.")" : '').")
				ORDER BY	report.reportTime DESC";
			$result = WCF::getDB()->sendQuery($sql, $this->limit, $this->offset);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!empty($this->postIDs)) $this->postIDs .= ',';
				$this->postIDs .= $row['postID'];
			}
		}
	}
	
	/**
	 * @see PostList::buildQuery()
	 */
	protected function buildQuery() {
		return $sql = "SELECT		report.reportID, report.userID AS reporterID, report.report, report.reportTime,
						post.*, thread.topic, thread.prefix, thread.boardID, board.title, user.username AS reporter
				FROM		wbb".WBB_N."_post_report report
				LEFT JOIN	wbb".WBB_N."_post post
				ON		(post.postID = report.postID)
				LEFT JOIN	wbb".WBB_N."_thread thread
				ON		(thread.threadID = post.threadID)
				LEFT JOIN	wbb".WBB_N."_board board
				ON		(board.boardID = thread.boardID)
				LEFT JOIN	wcf".WCF_N."_user user
				ON		(user.userID = report.userID)
				WHERE		report.postID IN (".$this->postIDs.")
				ORDER BY	report.reportTime DESC, reportID";
	}
}
?>