<?php
// wbb imports
require_once(WBB_DIR.'lib/acp/action/UpdateCounterAction.class.php');
require_once(WBB_DIR.'lib/data/board/BoardEditor.class.php');

/**
 * Updates the installation date timestamp and board creation timestamp.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.action
 * @category 	Burning Board
 */
class UpdateInstallationDateAction extends UpdateCounterAction {
	public $action = 'UpdateInstallationDate';
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// set installation date
		$sql = "UPDATE	wcf".WCF_N."_option
			SET	optionValue = IFNULL((
					SELECT	MIN(time)
					FROM	wbb".WBB_N."_thread
					WHERE	time > 0
				), optionValue)
			WHERE	optionName = 'install_date'
				AND packageID = ".PACKAGE_ID;
		WCF::getDB()->sendQuery($sql);
		
		// delete options file
		@unlink(WBB_DIR.'options.inc.php');
		
		// update boards
		$sql = "UPDATE	wbb".WBB_N."_board board
			SET	time = IFNULL((
					SELECT	MIN(time)
					FROM	wbb".WBB_N."_thread
					WHERE	boardID = board.boardID
						AND time > 0
				), ".TIME_NOW.")";
		WCF::getDB()->sendQuery($sql);
		
		// clear board cache
		WCF::getCache()->clear(WBB_DIR.'cache', 'cache.board.php');
			
		$this->executed();
		
		$this->calcProgress();
		$this->finish();
	}
}
?>