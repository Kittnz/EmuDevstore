<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');

// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a thread in the forum.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.thread
 * @category 	Burning Board
 */
class Thread extends DatabaseObject {
	protected $post;
	protected $board = null;
	
	/**
	 * Creates a new thread object.
	 *
	 * If id is set, the function reads the thread data from database.
	 * Otherwise it uses the given resultset.
	 * 
	 * @param 	integer 	$threadID	id of a thread
	 * @param 	array 		$row		resultset with thread data form database
	 * @param	integer		$postID		id of a post in the requested thread
	 */
	public function __construct($threadID, $row = null, $postID = null) {
		if ($postID !== null && $postID !== 0) {
			require_once(WBB_DIR.'lib/data/post/Post.class.php');
			$this->post = new Post($postID);
			if ($this->post->threadID) {
				$threadID = $this->post->threadID;
			}
		}
		
		if ($threadID !== null) {
			// select thread and thread subscription, visit and rating 
			$sql = "SELECT		thread.*,
						thread_rating.rating AS userRating 
						".(WCF::getUser()->userID ? ', IF(subscription.userID IS NOT NULL, 1, 0) AS subscribed, enableNotification, emails, thread_visit.lastVisitTime' : '')."
				FROM 		wbb".WBB_N."_thread thread
				".((WCF::getUser()->userID) ? ("
				LEFT JOIN 	wbb".WBB_N."_thread_subscription subscription
				ON 		(subscription.userID = ".WCF::getUser()->userID."
						AND subscription.threadID = ".$threadID.")
				LEFT JOIN 	wbb".WBB_N."_thread_visit thread_visit 
				ON 		(thread_visit.threadID = thread.threadID
						AND thread_visit.userID = ".WCF::getUser()->userID.")") : (""))."
				LEFT JOIN 	wbb".WBB_N."_thread_rating thread_rating
				ON 		(thread_rating.threadID = thread.threadID
						AND ".(WCF::getUser()->userID ? "thread_rating.userID = ".WCF::getUser()->userID : "thread_rating.ipAddress = '".escapeString(WCF::getSession()->ipAddress)."'").")
				WHERE 		thread.threadID = ".$threadID;
			$row = WCF::getDB()->getFirstRow($sql);
		}

		parent::__construct($row);
	}

	/**
	 * Returns the result of the rating of this thread.
	 * 
	 * @return	mixed		result of the rating of this thread
	 */
	public function getRating() {
		if ($this->ratings > 0 && $this->ratings >= THREAD_MIN_RATINGS) {
			return $this->rating / $this->ratings;
		}
		return false;
	}
	
	/**
	 * Returns true, if this thread is marked.
	 */
	public function isMarked() {
		$sessionVars = WCF::getSession()->getVars();
		if (isset($sessionVars['markedThreads'])) {
			if (in_array($this->threadID, $sessionVars['markedThreads'])) return 1;
		}
		
		return 0;
	}
	
	/**
	 * Returns the requested post, if post id was given at creation of the Thread object.
	 * 
	 * @return	boolean		requested post, if post id was given at creation of the Thread object
	 */
	public function getPost() {
		return $this->post;
	}
	
	/**
	 * Enters the active user to this thread.
	 */
	public function enter($board = null, $refreshSession = true) {
		if (!$this->threadID || $this->movedThreadID) {
			throw new IllegalLinkException();
		}
		
		if ($board == null || $board->boardID != $this->boardID) {
			$board = Board::getBoard($this->boardID);
		}
		
		$board->enter();
		
		// check permissions
		if ((!$board->getPermission('canReadThread') && (!$board->getPermission('canReadOwnThread') || !$this->userID || $this->userID != WCF::getUser()->userID)) || ($this->isDeleted && !$board->getModeratorPermission('canReadDeletedThread')) || ($this->isDisabled && !$board->getModeratorPermission('canEnableThread'))) {
			throw new PermissionDeniedException();
		}
		
		// refresh session
		if ($refreshSession) {
			WCF::getSession()->setThreadID($this->threadID);
		}
			
		// save board
		$this->board = $board;
	}
	
	/**
	 * Returns true, if the active user can reply this thread.
	 */
	public function canReplyThread($board = null) {
		if ($board == null || $board->boardID != $this->boardID) {
			if ($this->board !== null) $board = $this->board;
			else $board = Board::getBoard($this->boardID);
		}
		return (!$board->isClosed && (($this->isClosed && $board->getModeratorPermission('canReplyClosedThread')) 
			|| (!$this->isClosed && ($board->getPermission('canReplyThread') || ($this->userID && $this->userID == WCF::getUser()->userID && $board->getPermission('canReplyOwnThread'))))));
	}
	
	/**
	 * Subscribes the active user to this thread.
	 */
	public function subscribe() {
		if (!$this->subscribed) {
			$sql = "INSERT INTO	wbb".WBB_N."_thread_subscription
						(userID, threadID, enableNotification)
				VALUES 		(".WCF::getUser()->userID.", ".$this->threadID.", ".WCF::getUser()->enableEmailNotification.")";
			WCF::getDB()->registerShutdownUpdate($sql);
			$this->data['subscribed'] = 1;
			WCF::getSession()->unregister('hasSubscriptions');
		}
	}
	
	/**
	 * Unsubscribes the active user to this thread.
	 */
	public function unsubscribe() {
		if ($this->subscribed) {
			$sql = "DELETE FROM 	wbb".WBB_N."_thread_subscription
				WHERE 		userID = ".WCF::getUser()->userID."
						AND threadID = ".$this->threadID;
			WCF::getDB()->registerShutdownUpdate($sql);
			$this->data['subscribed'] = 0;
			WCF::getSession()->unregister('hasSubscriptions');
		}
	}
	
	/**
	 * Updates the subscription of this thread for the active user.
	 */
	public function updateSubscription() {
		if ($this->emails > 0) {
			$sql = "UPDATE 	wbb".WBB_N."_thread_subscription
				SET 	emails = 0
				WHERE 	userID = " . WCF::getUser()->userID . "
					AND threadID = ". $this->threadID;
			WCF::getDB()->registerShutdownUpdate($sql);
		}
	}
	
	/**
	 * Returns the tags of this thread.
	 * 
	 * @return	array
	 */
	public function getTags($languageIDArray) {
		// include files
		require_once(WCF_DIR.'lib/data/tag/TagEngine.class.php');
		require_once(WBB_DIR.'lib/data/thread/TaggedThread.class.php');
		
		// get tags
		return TagEngine::getInstance()->getTagsByTaggedObject(new TaggedThread(null, array(
			'threadID' => $this->threadID,
			'taggable' => TagEngine::getInstance()->getTaggable('com.woltlab.wbb.thread')
		)), $languageIDArray);
	}
	
	/**
	 * Returns true, if this thread is new for the active user.
	 *
	 * @return	boolean		true, if this thread is new for the active user
	 */
	public function isNew() {
		if (!$this->movedThreadID && $this->lastPostTime > $this->lastVisitTime) {
			return true;	
		}
		
		return false;
	}
}
?>