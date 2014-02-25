<?php
// wbb imports
require_once(WBB_DIR.'lib/acp/action/AbstractBoardAction.class.php');

/**
 * Deletes a board.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.action
 * @category 	Burning Board
 */
class BoardDeleteAction extends AbstractBoardAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.board.canDeleteBoard');
				
		// delete board
		$this->board->delete();
		
		// reset cache
		WCF::getCache()->clearResource('board');
		$this->executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=BoardList&deletedBoardID='.$this->boardID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>