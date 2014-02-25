<?php
// wbb imports
require_once(WBB_DIR.'lib/data/post/DependentPostList.class.php');

/**
 * Shows the lists of posts in the post add form.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.post
 * @category 	Burning Board
 */
class PostAddPostList extends DependentPostList {
	public $sqlOrderBy = 'post.time DESC';
	public $limit = REPLY_SHOW_POSTS_MAX;
	
	/**
	 * Creates a new PostAddPostList object.
	 * @see	ThreadPostList::__construct()
	 */
	public function __construct(Thread $thread, Board $board) {
		parent::__construct($thread, $board);
		$this->readPosts();
	}
	
	/**
	 * @see ThreadPostList::initDefaultSQL()
	 */
	protected function initDefaultSQL() {
		parent::initDefaultSQL();
		
		if (!$this->board->getModeratorPermission('canReadDeletedPost') && THREAD_ENABLE_DELETED_POST_NOTE) {
			$this->sqlConditionVisible .= ' AND isDeleted = 0';
			$this->sqlConditions .= ' AND isDeleted = 0';
		}
		
		// default selects / joins
		$this->sqlSelects = "user.*, IFNULL(user.username, post.username) AS username,";
		$this->sqlJoins = "	LEFT JOIN 	wcf".WCF_N."_user user
					ON 		(user.userID = post.userID)";
		$this->sqlSelects .= 'avatar.avatarID, avatar.avatarExtension, avatar.width, avatar.height,';
		$this->sqlJoins .= ' LEFT JOIN wcf'.WCF_N.'_avatar avatar ON (avatar.avatarID = user.avatarID) ';
	}
}
?>