<?php
require_once(WBB_DIR.'lib/page/ModerationThreadsPage.class.php');

/**
 * Shows the disabled threads.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
class ModerationHiddenThreadsPage extends ModerationThreadsPage {
	protected $sqlConditions = 'thread.isDisabled = 1 AND thread.movedThreadID = 0';
	public $action = 'hiddenThreads';
	public $pageName = 'ModerationHiddenThreads';
	public $neededPermissions = 'mod.board.canEnableThread';
	
	/**
	 * Creates a new ModerationHiddenThreadsPage object.
	 */
	public function __construct() {
		$boardIDs = Board::getModeratedBoards('canEnableThread');
		$this->sqlConditions .= ' AND thread.boardID IN ('.(!empty($boardIDs) ? $boardIDs : 0).')';
		parent::__construct();
	}
}
?>