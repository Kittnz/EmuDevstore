<?php
require_once(WCF_DIR.'lib/data/cronjobs/Cronjob.class.php');

/**
 * Cronjob empties the recycle bin for threads and posts.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.cronjob
 * @category 	Burning Board
 */
class EmptyRecycleBinCronjob implements Cronjob {
	/**
	 * @see Cronjob::execute()
	 */
	public function execute($data) {
		if (THREAD_ENABLE_RECYCLE_BIN && THREAD_EMPTY_RECYCLE_BIN_CYCLE > 0) {
			// delete threads first
			$sql = "SELECT	threadID
				FROM	wbb".WBB_N."_thread
				WHERE	isDeleted = 1
					AND deleteTime < ".(TIME_NOW - THREAD_EMPTY_RECYCLE_BIN_CYCLE * 86400);
			$result = WCF::getDB()->sendQuery($sql);
			if (WCF::getDB()->countRows($result) > 0) {
				require_once(WBB_DIR.'lib/data/thread/ThreadEditor.class.php');
				$threadIDs = '';
				while ($row = WCF::getDB()->fetchArray($result)) {
					if (!empty($threadIDs)) $threadIDs .= ',';
					$threadIDs .= $row['threadID'];
				}
				
				ThreadEditor::deleteAllCompletely($threadIDs);
			}
		
			// delete posts
			$sql = "SELECT	postID
				FROM	wbb".WBB_N."_post
				WHERE	isDeleted = 1
					AND deleteTime < ".(TIME_NOW - THREAD_EMPTY_RECYCLE_BIN_CYCLE * 86400);
			$result = WCF::getDB()->sendQuery($sql);
			if (WCF::getDB()->countRows($result) > 0) {
				require_once(WBB_DIR.'lib/data/post/PostEditor.class.php');
				$postIDs = '';
				while ($row = WCF::getDB()->fetchArray($result)) {
					if (!empty($postIDs)) $postIDs .= ',';
					$postIDs .= $row['postID'];
				}
				
				PostEditor::deleteAllCompletely($postIDs);
			}
		}
		if (THREAD_DELETE_LINK_CYCLE > 0) {
			$sql = "DELETE FROM	wbb".WBB_N."_thread
				WHERE		movedTime > 0
						AND movedTime < ".(TIME_NOW - THREAD_DELETE_LINK_CYCLE * 86400);
			WCF::getDB()->sendQuery($sql);
		}
	}
}
?>