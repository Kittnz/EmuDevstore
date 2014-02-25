<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/BoardEditor.class.php');

// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Sorts the structure of boards.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.action
 * @category 	Burning Board
 */
class BoardSortAction extends AbstractAction {
	/**
	 * new positions
	 *
	 * @var array
	 */
	public $positions = array();
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['boardListPositions']) && is_array($_POST['boardListPositions'])) $this->positions = ArrayUtil::toIntegerArray($_POST['boardListPositions']);
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.board.canEditBoard');

		// delete old positions
		$sql = "TRUNCATE wbb".WBB_N."_board_structure";
		WCF::getDB()->sendQuery($sql);
		
		// update postions
		foreach ($this->positions as $boardID => $data) {
			foreach ($data as $parentID => $position) {
				BoardEditor::updatePosition(intval($boardID), intval($parentID), $position);
			}
		}
		
		// insert default values
		$sql = "INSERT IGNORE INTO	wbb".WBB_N."_board_structure
						(parentID, boardID)
			SELECT			parentID, boardID
			FROM			wbb".WBB_N."_board";
		WCF::getDB()->sendQuery($sql);
		
		// reset cache
		WCF::getCache()->clearResource('board');
		$this->executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=BoardList&successfulSorting=1&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>