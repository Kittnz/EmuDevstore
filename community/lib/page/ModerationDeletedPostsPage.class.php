<?php
require_once(WBB_DIR.'lib/page/ModerationPostsPage.class.php');

/**
 * Shows the deleted posts (recycle bin).
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
class ModerationDeletedPostsPage extends ModerationPostsPage {
	protected $sqlConditions = 'post.isDeleted = 1';
	public $action = 'deletedPosts';
	public $pageName = 'ModerationDeletedPosts';
	public $neededPermissions = 'mod.board.canReadDeletedPost';
	
	/**
	 * Creates a new ModerationDeletedPostsPage object.
	 */
	public function __construct() {
		$boardIDs = Board::getModeratedBoards('canReadDeletedPost');
		$this->sqlConditions .= ' AND thread.boardID IN ('.(!empty($boardIDs) ? $boardIDs : 0).')';
		parent::__construct();
	}
}
?>