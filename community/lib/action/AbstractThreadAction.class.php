<?php
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');
require_once(WBB_DIR.'lib/data/board/Board.class.php');
require_once(WBB_DIR.'lib/data/thread/ThreadEditor.class.php');

/**
 * Abstract thread action.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	action
 * @category 	Burning Board
 */
class AbstractThreadAction extends AbstractSecureAction {
	/**
	 * thread id
	 *
	 * @var	integer
	 */
	public $threadID = 0;
	
	/**
	 * thread editor object
	 *
	 * @var	ThreadEditor
	 */
	public $thread = null;
	
	/**
	 * board object
	 *
	 * @var	Board
	 */
	public $board = null;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['threadID'])) $this->threadID = intval($_REQUEST['threadID']);
		// get thread
		$this->thread = new ThreadEditor($this->threadID);
		// get board
		$this->board = Board::getBoard($this->thread->boardID);
		// enter thread
		$this->thread->enter($this->board);
	}
	
	/**
	 * @see AbstractAction::executed()
	 */
	protected function executed() {
		parent::executed();
		
		if (empty($_REQUEST['ajax'])) HeaderUtil::redirect('index.php?page=Thread&threadID='.$this->threadID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>