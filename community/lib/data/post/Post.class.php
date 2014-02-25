<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/Message.class.php');

/**
 * Represents a post in the forum.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.post
 * @category 	Burning Board
 */
class Post extends Message {
	/**
	 * Creates a new post object.
	 *
	 * If id is set, the function reads the post data from database.
	 * Otherwise it uses the given resultset.
	 *
	 * @param 	integer 	$postID		id of a post
	 * @param 	array 		$row		resultset with post data form database
	 */
	public function __construct($postID, $row = null) {
		if ($postID !== null) {
			$sql = "SELECT	*
				FROM 	wbb".WBB_N."_post
				WHERE 	postID = ".$postID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		parent::__construct($row);
		$this->messageID = $row['postID'];
	}

	/**
	 * Returns true, if this post is marked in the active session.
	 */
	public function isMarked() {
		$sessionVars = WCF::getSession()->getVars();
		if (isset($sessionVars['markedPosts'])) {
			if (in_array($this->postID, $sessionVars['markedPosts'])) return 1;
		}
		
		return 0;
	}
	
	/**
	 * Returns the number of quotes of this post.
	 * 
	 * @return	integer
	 */
	public function isQuoted() {
		require_once(WCF_DIR.'lib/data/message/multiQuote/MultiQuoteManager.class.php');
		return MultiQuoteManager::getQuoteCount($this->postID, 'post');
	}
	
	/**
	 * Returns true, if the active user can edit or delete this post.
	 * 
	 * @param	Board		$board
	 * @param	Thread		$thread
	 * @return	boolean
	 */
	public function canEditPost($board, $thread) {
		$isModerator = $board->getModeratorPermission('canEditPost') || $board->getModeratorPermission('canDeletePost');
		$isAuthor = $this->userID && $this->userID == WCF::getUser()->userID;
		
		$canEditPost = $board->getModeratorPermission('canEditPost') || $isAuthor && $board->getPermission('canEditOwnPost');
		$canDeletePost = $board->getModeratorPermission('canDeletePost') || $isAuthor && $board->getPermission('canDeleteOwnPost');

		if ((!$canEditPost && !$canDeletePost) || (!$isModerator && ($board->isClosed || $thread->isClosed || $this->isClosed))) {
			return false;
		}

		// check post edit timeout 
		if (!$isModerator && WCF::getUser()->getPermission('user.board.postEditTimeout') != -1 && TIME_NOW - $this->time > WCF::getUser()->getPermission('user.board.postEditTimeout') * 60) {
			return false;
		}
		
		return true;
	}
}
?>