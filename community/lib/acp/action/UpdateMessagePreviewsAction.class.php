<?php
// wbb imports
require_once(WBB_DIR.'lib/acp/action/UpdateCounterAction.class.php');
require_once(WBB_DIR.'lib/data/post/PostEditor.class.php');

/**
 * Updates the message previews.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.action
 * @category 	Burning Board
 */
class UpdateMessagePreviewsAction extends UpdateCounterAction {
	public $action = 'UpdateMessagePreviews';
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// add cache resources
		WCF::getCache()->addResource('bbcodes', WCF_DIR.'cache/cache.bbcodes.php', WCF_DIR.'lib/system/cache/CacheBuilderBBCodes.class.php');
		WCF::getCache()->addResource('smileys', WCF_DIR.'cache/cache.smileys.php', WCF_DIR.'lib/system/cache/CacheBuilderSmileys.class.php');
		
		// count threads
		$sql = "SELECT	COUNT(*) AS count
			FROM	wbb".WBB_N."_thread";
		$row = WCF::getDB()->getFirstRow($sql);
		$count = $row['count'];
		
		// get thread ids
		$threadIDs = '';
		$sql = "SELECT		threadID
			FROM		wbb".WBB_N."_thread
			ORDER BY	threadID";
		$result = WCF::getDB()->sendQuery($sql, $this->limit, ($this->limit * $this->loop));
		while ($row = WCF::getDB()->fetchArray($result)) {
			$threadIDs .= ','.$row['threadID'];
		}
		
		if (empty($threadIDs)) {
			$this->calcProgress();
			$this->finish();
		}
		
		// get data
		$sql = "SELECT		thread.threadID,
					post.postID, post.message, post.enableSmilies, post.enableHtml, post.enableBBCodes
			FROM		wbb".WBB_N."_thread thread
			LEFT JOIN	wbb".WBB_N."_post post
			ON		(post.postID = thread.firstPostID)
			WHERE		thread.threadID IN (0".$threadIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($row['postID']) {
				PostEditor::updateFirstPostPreview($row['threadID'], $row['postID'], $row['message'], $row);
			}
		}
		$this->executed();
		
		$this->calcProgress(($this->limit * $this->loop), $count);
		$this->nextLoop();
	}
}
?>