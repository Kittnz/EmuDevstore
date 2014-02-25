<?php
require_once(WBB_DIR.'lib/data/thread/Thread.class.php');
require_once(WBB_DIR.'lib/data/board/Board.class.php');
require_once(WBB_DIR.'lib/data/post/PostList.class.php');

/**
 * Shows a list of dependent posts.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.post
 * @category 	Burning Board
 */
class DependentPostList extends PostList {
	public $board;
	public $sqlConditionVisible = '';
	
	/**
	 * Creates a new DependentPostList object.
	 * 
	 * @param	Thread		$thread
	 * @param	Board		$board
	 */
	public function __construct(Thread $thread, Board $board) {
		$this->thread = $thread;
		$this->board = $board;
		$this->canViewAttachmentPreview = ($this->board->getPermission('canViewAttachmentPreview') || $this->board->getPermission('canDownloadAttachment'));
		
		parent::__construct();
	}
	
	/**
	 * @see PostList::initDefaultSQL();
	 */
	protected function initDefaultSQL() {
		parent::initDefaultSQL();

		// default sql conditions
		$this->sqlConditions = "threadID = ".$this->thread->threadID;
		if (!$this->board->getModeratorPermission('canReadDeletedPost') && !THREAD_ENABLE_DELETED_POST_NOTE) {
			$this->sqlConditionVisible .= ' AND isDeleted = 0';
		}
		if (!$this->board->getModeratorPermission('canEnablePost')) {
			$this->sqlConditionVisible .= ' AND isDisabled = 0';
		}
		$this->sqlConditions .= $this->sqlConditionVisible;
	}
}
?>