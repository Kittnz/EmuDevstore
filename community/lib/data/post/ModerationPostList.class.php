<?php
require_once(WBB_DIR.'lib/data/post/PostList.class.php');

/**
 * ModerationPostList displays a list of posts for moderative options. 
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.post
 * @category 	Burning Board
 */
class ModerationPostList extends PostList {
	public $sqlOrderBy = 'post.time DESC';
	
	/**
	 * @see PostList::initDefaultSQL();
	 */
	protected function initDefaultSQL() {
		parent::initDefaultSQL();
		
		$this->sqlSelects .= "thread.topic, thread.prefix, thread.boardID, board.title,";
		$this->sqlJoins .= "	LEFT JOIN wbb".WBB_N."_thread thread ON (thread.threadID = post.threadID)
					LEFT JOIN wbb".WBB_N."_board board ON (board.boardID = thread.boardID)";
		$this->sqlConditionJoins .= "LEFT JOIN wbb".WBB_N."_thread thread ON (thread.threadID = post.threadID)";
	}
}
?>