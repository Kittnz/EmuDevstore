<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/BoardEditor.class.php');

// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Provides default implementations for board actions.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.action
 * @category 	Burning Board
 */
class AbstractBoardAction extends AbstractAction {
	/**
	 * board id
	 *
	 * @var integer
	 */
	public $boardID = 0;
	
	/**
	 * board editor object
	 *
	 * @var BoardEditor
	 */
	public $board = null;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['boardID'])) $this->boardID = intval($_REQUEST['boardID']);
		$this->board = new BoardEditor($this->boardID);
		if (!$this->board->boardID) {
			throw new IllegalLinkException();
		}
	}
}
?>