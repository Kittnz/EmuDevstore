<?php
// wbb imports
require_once(WBB_DIR.'lib/acp/action/UpdateCounterAction.class.php');
require_once(WBB_DIR.'lib/data/board/BoardEditor.class.php');

/**
 * Updates the board statistics.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.action
 * @category 	Burning Board
 */
class UpdateBoardsAction extends UpdateCounterAction {
	public $action = 'UpdateBoards';
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// count board
		$sql = "SELECT	COUNT(*) AS count
			FROM	wbb".WBB_N."_board";
		$row = WCF::getDB()->getFirstRow($sql);
		$count = $row['count'];
		
		// get board ids
		$boardIDs = '';
		$sql = "SELECT		boardID
			FROM		wbb".WBB_N."_board
			ORDER BY	boardID";
		$result = WCF::getDB()->sendQuery($sql, $this->limit, ($this->limit * $this->loop));
		while ($row = WCF::getDB()->fetchArray($result)) {
			$boardIDs .= ','.$row['boardID'];
			
			// update last post
			$board = new BoardEditor($row['boardID']);
			$board->setLastPosts();
		}
		
		if (empty($boardIDs)) {
			// clear board cache
			WCF::getCache()->clear(WBB_DIR.'cache', 'cache.boardData.php');
			
			$this->calcProgress();
			$this->finish();
		}
		
		// update boards
		$sql = "UPDATE	wbb".WBB_N."_board board
			SET	threads = (
					SELECT	COUNT(*)
					FROM	wbb".WBB_N."_thread
					WHERE	boardID = board.boardID
						AND isDeleted = 0
						AND isDisabled = 0
				),
				posts = (
					SELECT	IFNULL(SUM(replies), 0) + COUNT(*)
					FROM	wbb".WBB_N."_thread thread
					WHERE	boardID = board.boardID
						AND isDeleted = 0
						AND isDisabled = 0
				)
			WHERE	board.boardID IN (0".$boardIDs.")";
		WCF::getDB()->sendQuery($sql);
		$this->executed();
		
		$this->calcProgress(($this->limit * $this->loop), $count);
		$this->nextLoop();
	}
}
?>