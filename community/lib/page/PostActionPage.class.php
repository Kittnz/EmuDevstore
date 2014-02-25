<?php
// wbb imports
require_once(WBB_DIR.'lib/data/post/PostAction.class.php');

// wcf imports
require_once(WCF_DIR.'lib/page/AbstractSecurePage.class.php');

/**
 * Starts the execution moderation actions on posts.
 * 
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
class PostActionPage extends AbstractSecurePage {
	public $postID = 0;
	public $threadID = 0;
	public $boardID = 0;
	public $post, $thread, $board;
	public $topic = '';
	public $url = '';
	public static $validFunctions = array('changeTopic', 'mark', 'unmark', 'trash', 'delete', 'recover', 'disable', 'enable', 'close', 'open', 'unmarkAll', 'deleteAll', 'recoverAll', 'copy', 'move', 'removeReport', 'removeReports', 'merge');
	public $reason = '';
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['postID'])) $this->postID = ArrayUtil::toIntegerArray($_REQUEST['postID']); 
		if (isset($_REQUEST['threadID'])) $this->threadID = intval($_REQUEST['threadID']);
		if (isset($_REQUEST['reason'])) {
			$this->reason = StringUtil::trim($_REQUEST['reason']);
			if (CHARSET != 'UTF-8') $this->reason = StringUtil::convertEncoding('UTF-8', CHARSET, $this->reason);
		}
		if (isset($_REQUEST['boardID'])) $this->boardID = intval($_REQUEST['boardID']);
		if (isset($_REQUEST['topic'])) {
			$this->topic = StringUtil::trim($_REQUEST['topic']);
			if (CHARSET != 'UTF-8') $this->topic = StringUtil::convertEncoding('UTF-8', CHARSET, $this->topic);
		}
		if (isset($_REQUEST['url'])) $this->url = $_REQUEST['url'];
		
		// check permissions
		if (!is_array($this->postID) && $this->postID != 0) {
			require_once(WBB_DIR.'lib/data/post/PostEditor.class.php');
			$this->post = new PostEditor($this->postID);
			if (!$this->post->postID) {
				throw new IllegalLinkException();
			}
			$this->threadID = $this->post->threadID;
		}
		if ($this->threadID != 0) {
			require_once(WBB_DIR.'lib/data/thread/ThreadEditor.class.php');
			$this->thread = new ThreadEditor($this->threadID);
			$this->boardID = $this->thread->boardID;
			$this->thread->enter();
		}
		if ($this->boardID != 0) {
			require_once(WBB_DIR.'lib/data/board/BoardEditor.class.php');
			$this->board = new BoardEditor($this->boardID);
			if ($this->thread != null) {
				$this->thread->enter();
			}
		}
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		parent::show();
		
		if (in_array($this->action, self::$validFunctions)) {
			$postAction = new PostAction($this->board, $this->thread, $this->post, $this->postID, $this->topic, $this->url, $this->reason);
			$postAction->{$this->action}();
		}
	}
}
?>