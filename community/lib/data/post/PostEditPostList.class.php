<?php
require_once(WBB_DIR.'lib/data/post/PostAddPostList.class.php');
require_once(WBB_DIR.'lib/data/post/Post.class.php');

/**
 * Shows the lists of posts in the post edit form.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.post
 * @category 	Burning Board
 */
class PostEditPostList extends PostAddPostList {
	public $post;
	
	/**
	 * Creates a new PostEditPostList object.
	 * @see	ThreadPostList::__construct()
	 */
	public function __construct(Post $post, Thread $thread, Board $board) {
		$this->post = $post;
		parent::__construct($thread, $board);
	}
	
	/**
	 * @see ThreadPostList::initDefaultSQL()
	 */
	protected function initDefaultSQL() {
		parent::initDefaultSQL();
		$this->sqlConditions .= ' AND time < '.$this->post->time;
	}
}
?>