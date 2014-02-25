<?php
// wbb imports
require_once(WBB_DIR.'lib/acp/action/UpdateCounterAction.class.php');
require_once(WBB_DIR.'lib/data/thread/ThreadEditor.class.php');

/**
 * Updates the similar threads.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.action
 * @category 	Burning Board
 */
class UpdateSimilarThreadsAction extends UpdateCounterAction {
	public $action = 'UpdateSimilarThreads';
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// count threads
		$sql = "SELECT	COUNT(*) AS count
			FROM	wbb".WBB_N."_thread";
		$row = WCF::getDB()->getFirstRow($sql);
		$count = $row['count'];
		
		// get thread ids
		$threadIDs = '';
		$sql = "SELECT		threadID, topic
			FROM		wbb".WBB_N."_thread
			ORDER BY	threadID";
		$result = WCF::getDB()->sendQuery($sql, $this->limit, ($this->limit * $this->loop));
		if (!WCF::getDB()->countRows($result)) {
			$this->calcProgress();
			$this->finish();
		}
		while ($row = WCF::getDB()->fetchArray($result)) {
			// delete old entries
			$sql = "DELETE FROM	wbb".WBB_N."_thread_similar
				WHERE		threadID = ".$row['threadID'];
			WCF::getDB()->sendQuery($sql);
			
			// update entries
			ThreadEditor::updateSimilarThreads($row['threadID'], $row['topic']);
		}
		$this->executed();
		
		$this->calcProgress(($this->limit * $this->loop), $count);
		$this->nextLoop();
	}
}
?>