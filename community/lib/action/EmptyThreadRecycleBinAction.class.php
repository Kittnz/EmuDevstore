<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');

// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');
require_once(WBB_DIR.'lib/data/thread/ThreadEditor.class.php');

/**
 * Empties the recycle bin of threads.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	action
 * @category 	Burning Board
 */
class EmptyThreadRecycleBinAction extends AbstractSecureAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permissions
		$boardIDs = Board::getModeratedBoards('canDeleteThreadCompletely');
		if (empty($boardIDs)) {
			throw new PermissionDeniedException();
		}
		
		// delete threads
		$threadIDArray = array();
		$sql = "SELECT	threadID
			FROM	wbb".WBB_N."_thread
			WHERE	isDeleted = 1
				AND boardID IN (".$boardIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$threadIDArray[] = $row['threadID'];
		}
		if (count($threadIDArray)) {
			ThreadEditor::deleteAllCompletely(implode(',', $threadIDArray));
		}
		$this->executed();
		
		// forward
		HeaderUtil::redirect('index.php?page=ModerationDeletedThreads'.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>