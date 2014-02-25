<?php
require_once(WCF_DIR.'lib/data/cronjobs/Cronjob.class.php');

/**
 * Marks threads as done.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.cronjob
 * @category 	Burning Board
 */
class ThreadMarkAsDoneCronjob implements Cronjob {
	/**
	 * @see Cronjob::execute()
	 */
	public function execute($data) {
		if (MODULE_THREAD_MARKING_AS_DONE == 1 && THREAD_MARKING_AS_DONE_CYCLE > 0) {
			// get board ids
			$boardIDArray = array();
			require_once(WBB_DIR.'lib/data/board/Board.class.php');
			$boards = WCF::getCache()->get('board', 'boards');
			foreach ($boards as $board) {
				if ($board->enableMarkingAsDone == 1) {
					$boardIDArray[] = $board->boardID;
				}
			}
			unset($boards);
			
			// mark as done
			if (count($boardIDArray)) {
				$sql = "UPDATE	wbb".WBB_N."_thread
					SET	isDone = 1
					WHERE	boardID IN (".implode(',', $boardIDArray).")
						AND lastPostTime < ".(TIME_NOW - THREAD_MARKING_AS_DONE_CYCLE * 86400);
				WCF::getDB()->registerShutdownUpdate($sql);
			}
		}
	}
}
?>