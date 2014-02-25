<?php
require_once(WBB_DIR.'lib/page/ModerationThreadsPage.class.php');

/**
 * Shows the deleted threads (recycle bin).
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
class ModerationDeletedThreadsPage extends ModerationThreadsPage {
	protected $sqlConditions = 'thread.isDeleted = 1 AND thread.movedThreadID = 0';
	public $action = 'deletedThreads';
	public $pageName = 'ModerationDeletedThreads';
	public $neededPermissions = 'mod.board.canReadDeletedThread';
	
	/**
	 * Creates a new ModerationDeletedThreadsPage object.
	 */
	public function __construct() {
		$boardIDs = Board::getModeratedBoards('canReadDeletedThread');
		$this->sqlConditions .= ' AND thread.boardID IN ('.(!empty($boardIDs) ? $boardIDs : 0).')';
		parent::__construct();
	}
}
?>