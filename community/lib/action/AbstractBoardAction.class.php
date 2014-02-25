<?php
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');
require_once(WBB_DIR.'lib/data/board/Board.class.php');

/**
 * Abstract board action.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	action
 * @category 	Burning Board
 */
class AbstractBoardAction extends AbstractSecureAction {
	public $boardID = 0;
	public $board = null;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get board
		if (isset($_REQUEST['boardID'])) $this->boardID = intval($_REQUEST['boardID']);
		$this->board = new Board($this->boardID);
		$this->board->enter();
	}
	
	/**
	 * @see AbstractAction::executed()
	 */
	protected function executed() {
		parent::executed();
		
		if (empty($_REQUEST['ajax'])) HeaderUtil::redirect('index.php?page=Board&boardID='.$this->boardID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>