<?php
// wbb imports
require_once(WBB_DIR.'lib/acp/action/UpdateCounterAction.class.php');

/**
 * Updates the threads.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.action
 * @category 	Burning Board
 */
class UpdateThreadsAction extends UpdateCounterAction {
	public $action = 'UpdateThreads';
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// count thread
		$sql = "SELECT	COUNT(*) AS count
			FROM	wbb".WBB_N."_thread";
		$row = WCF::getDB()->getFirstRow($sql);
		$count = $row['count'];
		
		// get threads
		$counter = 0;
		$sql = "SELECT		*
			FROM		wbb".WBB_N."_thread
			ORDER BY	threadID";
		$result = WCF::getDB()->sendQuery($sql, $this->limit, ($this->limit * $this->loop));
		while ($row = WCF::getDB()->fetchArray($result)) {
			$counter++;
			if ($row['movedThreadID']) continue;
			
			// get last post information
			$sql = "SELECT		postID, time, userID, username
				FROM		wbb".WBB_N."_post
				WHERE		threadID = ".$row['threadID']."
						AND isDeleted = 0
						AND isDisabled = 0
				ORDER BY	time DESC";
			$lastPostRow = WCF::getDB()->getFirstRow($sql);
			if (empty($lastPostRow['postID'])) {
				$lastPostRow = array(
					'time' => $row['lastPostTime'],
					'userID' => $row['lastPosterID'],
					'username' => $row['lastPoster']
				);
			}
			
			// get first post information
			$sql = "SELECT		postID, time, userID, username
				FROM		wbb".WBB_N."_post
				WHERE		threadID = ".$row['threadID']."
						AND isDeleted = 0
						AND isDisabled = 0
				ORDER BY	time";
			$firstPostRow = WCF::getDB()->getFirstRow($sql);
			if (empty($firstPostRow['postID'])) {
				$firstPostRow = array(
					'postID' => $row['firstPostID'],
					'time' => $row['time'],
					'userID' => $row['userID'],
					'username' => $row['username']
				);
			}
			
			// get stats
			$sql = "SELECT	COUNT(*) AS posts,
					SUM(attachments) AS attachments,
					SUM(IF(pollID <> 0, 1, 0)) AS polls
				FROM	wbb".WBB_N."_post
				WHERE	threadID = ".$row['threadID']."
					AND isDeleted = 0
					AND isDisabled = 0";
			$statsRow = WCF::getDB()->getFirstRow($sql);
			if (empty($statsRow['posts'])) $statsRow['posts'] = 1;
			if (empty($statsRow['attachments'])) $statsRow['attachments'] = 0;
			if (empty($statsRow['polls'])) $statsRow['polls'] = 0;
			
			// update thread
			$sql = "UPDATE	wbb".WBB_N."_thread thread
				SET	lastPostTime = ".$lastPostRow['time'].",
					lastPosterID = ".$lastPostRow['userID'].",
					lastPoster = '".escapeString($lastPostRow['username'])."',
					replies = ".($statsRow['posts'] - 1).",
					attachments = ".$statsRow['attachments'].",
					polls = ".$statsRow['polls'].",
					firstPostID = ".$firstPostRow['postID'].",
					time = ".$firstPostRow['time'].",
					userID = ".$firstPostRow['userID'].",
					username = '".escapeString($firstPostRow['username'])."'
				WHERE	thread.threadID = ".$row['threadID'];
			WCF::getDB()->sendQuery($sql);
		}
		
		if (!$counter) {
			$this->calcProgress();
			$this->finish();
		}
		
		$this->executed();
		$this->calcProgress(($this->limit * $this->loop), $count);
		$this->nextLoop();
	}
}
?>