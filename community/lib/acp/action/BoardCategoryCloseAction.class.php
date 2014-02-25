<?php
// wbb imports
require_once(WBB_DIR.'lib/acp/action/AbstractBoardAction.class.php');

/**
 * Closes a category.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.action
 * @category 	Burning Board
 */
class BoardCategoryCloseAction extends AbstractBoardAction {
	/**
	 * closing status
	 *
	 * @var integer
	 */
	public $close = 0;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['close'])) $this->close = intval($_REQUEST['close']);
		if (!$this->board->isCategory()) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.board.canEditBoard', 'admin.board.canDeleteBoard', 'admin.board.canEditPermissions', 'admin.board.canEditModerators'));
		
		if ($this->close == 1) {
			$sql = "INSERT IGNORE INTO	wbb".WBB_N."_board_closed_category_to_admin
							(userID, boardID)
				VALUES			(".WCF::getUser()->userID.", ".$this->boardID.")";
			WCF::getDB()->sendQuery($sql);
		}
		else {
			$sql = "DELETE FROM	wbb".WBB_N."_board_closed_category_to_admin
				WHERE		userID = ".WCF::getUser()->userID."
						AND boardID = ".$this->boardID;
			WCF::getDB()->sendQuery($sql);
		}
		
		$this->executed();
	}
}
?>