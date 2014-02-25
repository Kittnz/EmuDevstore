<?php
require_once(WBB_DIR.'lib/page/ModerationPostsPage.class.php');

/**
 * Shows the disabled posts.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
class ModerationHiddenPostsPage extends ModerationPostsPage {
	protected $sqlConditions = 'post.isDisabled = 1';
	public $action = 'hiddenPosts';
	public $pageName = 'ModerationHiddenPosts';
	public $neededPermissions = 'mod.board.canEnablePost';
	
	/**
	 * Creates a new ModerationHiddenPostsPage object.
	 */
	public function __construct() {
		$boardIDs = Board::getModeratedBoards('canEnablePost');
		$this->sqlConditions .= ' AND thread.boardID IN ('.(!empty($boardIDs) ? $boardIDs : 0).')';
		parent::__construct();
	}
}
?>