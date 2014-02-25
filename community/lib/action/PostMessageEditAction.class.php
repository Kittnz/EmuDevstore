<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');

// wbb imports
require_once(WBB_DIR.'lib/data/post/PostEditor.class.php');
require_once(WBB_DIR.'lib/data/post/PostList.class.php');
require_once(WBB_DIR.'lib/data/thread/ThreadEditor.class.php');
require_once(WBB_DIR.'lib/data/board/BoardEditor.class.php');

/**
 * Saves the message of a post.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	action
 * @category 	Burning Board
 */
class PostMessageEditAction extends AbstractSecureAction {
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
	 * board object
	 *
	 * @var	BoardEditor
	 */
	public $board = null;
	
	/**
	 * new message
	 *
	 * @var	string
	 */
	public $text = '';
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		try {
			// get post
			if (isset($_REQUEST['postID'])) $this->postID = intval($_REQUEST['postID']);
			$this->post = new PostEditor($this->postID);
			if (!$this->post->postID) {
				throw new IllegalLinkException();
			}
			// get thread
			$this->thread = new ThreadEditor($this->post->threadID);
			$this->board = new BoardEditor($this->thread->boardID);
			$this->thread->enter($this->board);

			// check permissions
			$isModerator = $this->board->getModeratorPermission('canEditPost') || $this->board->getModeratorPermission('canDeletePost');
			$isAuthor = $this->post->userID && $this->post->userID == WCF::getUser()->userID;
			$canEditPost = $this->board->getModeratorPermission('canEditPost') || $isAuthor && $this->board->getPermission('canEditOwnPost');
			if (!$canEditPost || (!$isModerator && ($this->board->isClosed || $this->thread->isClosed || $this->post->isClosed))) {
				throw new PermissionDeniedException();
			}
	
			// check post edit timeout 
			if (!$isModerator && WCF::getUser()->getPermission('user.board.postEditTimeout') != -1 && TIME_NOW - $this->post->time > WCF::getUser()->getPermission('user.board.postEditTimeout') * 60) {
				throw new NamedUserException(WCF::getLanguage()->get('wbb.postEdit.error.timeout', array('$timeout' => WCF::getUser()->getPermission('user.board.postEditTimeout'))));
			}
			
			// get message
			if (isset($_POST['text'])) {
				$this->text = StringUtil::trim($_POST['text']);
				if (CHARSET != 'UTF-8') {
					$this->text = StringUtil::convertEncoding('UTF-8', CHARSET, $this->text);
				}
				if (empty($this->text)) {
					throw new IllegalLinkException();
				}
			}
		}
		catch (UserException $e) {
			@header('HTTP/1.0 403 Forbidden');
			echo $e->getMessage();
			exit;
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// add edit note
		$postData = array();
		if (!$this->board->getPermission('canHideEditNote') && (WCF::getUser()->userID != $this->post->userID || $this->post->time <= TIME_NOW - POST_EDIT_HIDE_EDIT_NOTE_PERIOD * 60)) {
			$postData['editor'] = WCF::getUser()->username;
			$postData['editorID'] = WCF::getUser()->userID;
			$postData['lastEditTime'] = TIME_NOW;
			$postData['editCount'] = $this->post->editCount + 1;
			$postData['editReason'] = '';
		}
		
		// update message
		$this->post->updateMessage($this->text, $postData);
		if ($this->thread->firstPostID == $this->post->postID) {
			// update first post preview
			$this->post->updateFirstPostPreview($this->post->threadID, $this->post->postID, $this->text, array(
				'enableSmilies' => $this->post->enableSmilies,
				'enableHtml' => $this->post->enableHtml,
				'enableBBCodes' => $this->post->enableBBCodes
			));
		}
		$this->executed();
		
		// get new formatted message and return it
		$postList = new PostList();
		$postList->sqlConditions = 'post.postID = '.$this->postID;
		$postList->readPosts();
		$post = reset($postList->posts);
		HeaderUtil::sendHeaders();
		echo $post->getFormattedMessage();
	}
}
?>