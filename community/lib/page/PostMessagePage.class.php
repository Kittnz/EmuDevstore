<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

// wbb imports
require_once(WBB_DIR.'lib/data/post/Post.class.php');
require_once(WBB_DIR.'lib/data/thread/Thread.class.php');

/**
 * Gets the message of a post.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
class PostMessagePage extends AbstractPage {
	/**
	 * post id
	 *
	 * @var	int
	 */
	public $postID = 0;

	/**
	 * post editor object
	 *
	 * @var	PostEditor
	 */
	public $post = null;
	
	/**
	 * thread editor object
	 *
	 * @var	ThreadEditor
	 */
	public $thread = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get post
		if (isset($_REQUEST['postID'])) $this->postID = intval($_REQUEST['postID']);
		$this->post = new Post($this->postID);
		if (!$this->post->postID) {
			throw new IllegalLinkException();
		}
		// get thread
		$this->thread = new Thread($this->post->threadID);
		$this->thread->enter();
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		parent::show();
		
		HeaderUtil::sendHeaders();
		echo $this->post->message;
	}
}
?>