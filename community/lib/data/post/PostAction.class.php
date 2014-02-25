<?php
// wbb imports
require_once(WBB_DIR.'lib/data/post/PostEditor.class.php');
require_once(WBB_DIR.'lib/data/thread/ThreadEditor.class.php');
require_once(WBB_DIR.'lib/data/thread/ThreadAction.class.php');

/**
 * Executes moderation actions on posts.
 * 
 * @author 	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.post
 * @category 	Burning Board
 */
class PostAction {
	/**
	 * post editor object
	 * 
	 * @var PostEditor
	 */
	protected $post = null;
	
	/**
	 * board editor object
	 * 
	 * @var BoardEditor
	 */
	protected $board = null;
	
	/**
	 * thread editor object
	 * 
	 * @var ThreadEditor
	 */
	protected $thread = null;
	
	protected $topic = '';
	protected $url = '';
	protected $postIDs = null;
	protected $reason = '';
	protected $postID = 0;
	
	/**
	 * Creates a new PostAction object.
	 * 
	 * @param	BoardEditor	$board
	 * @param	ThreadEditor	$thread
	 * @param	PostEditor	$post
	 */
	public function __construct($board = null, $thread = null, $post = null, $postID = 0, $topic = '', $forwardURL = '', $reason = '') {
		$this->board = $board;
		$this->thread = $thread;
		$this->post = $post;
		$this->topic = $topic;
		$this->url = $forwardURL;
		$this->postID = $postID;
		if (empty($this->url) && $this->thread) $this->url = 'index.php?page=Thread&threadID='.$this->thread->threadID.SID_ARG_2ND_NOT_ENCODED;
		$this->reason = $reason;
		
		// get marked posts from session
		$this->getMarkedPosts();
	}
	
	/**
	 * Gets marked posts from session.
	 */
	public function getMarkedPosts() {
		$sessionVars = WCF::getSession()->getVars();
		if (isset($sessionVars['markedPosts'])) {
			$this->postIDs = implode(',', $sessionVars['markedPosts']);	
		}
	}
	
	/**
	 * Changes the topic of the selected post.
	 * 
	 * @param	string		$topic
	 */
	public function setTopic($topic) {
		$this->topic = $topic;
		$this->changeTopic();
	}
	
	/**
	 * Changes the topic of the selected post.
	 */
	public function changeTopic() {
		if (!$this->board->getModeratorPermission('canEditPost')) {
			return;
		}
		
		if ($this->post != null) {
			$this->post->setSubject($this->topic);
			
			if (!empty($this->topic) && $this->thread != null && $this->thread->firstPostID == $this->post->postID) {
				$this->thread->setTopic($this->topic, false);
			}
		}
	}
	
	/**
	 * Marks the selected post.
	 */
	public function mark() {
		if ($this->post != null) {
			$this->post->mark();
		}
		else if (is_array($this->postID)) {
			$threadIDs = PostEditor::getThreadIDs(implode(',', $this->postID));
			if (!empty($threadIDs)) {
				// check permissions
				$sql = "SELECT	*
					FROM	wbb".WBB_N."_thread
					WHERE	threadID IN (".$threadIDs.")";
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					$thread = new ThreadEditor(null, $row);
					$thread->enter();
				}
				
				foreach ($this->postID as $postID) {
					$post = new PostEditor($postID);
					$post->mark();
				}
			}
		}
	}
	
	/**
	 * Unmarks the selected post.
	 */
	public function unmark() {
		if ($this->post != null) {
			$this->post->unmark();
		}
		else if (is_array($this->postID)) {
			$threadIDs = PostEditor::getThreadIDs(implode(',', $this->postID));
			if (!empty($threadIDs)) {
				// check permissions
				$sql = "SELECT	*
					FROM	wbb".WBB_N."_thread
					WHERE	threadID IN (".$threadIDs.")";
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					$thread = new ThreadEditor(null, $row);
					$thread->enter();
				}
				
				foreach ($this->postID as $postID) {
					$post = new PostEditor($postID);
					$post->unmark();
				}
			}
		}
	}
	
	/**
	 * Trashes the selected post.
	 */
	public function trash($ignorePermission = false) {
		if (!THREAD_ENABLE_RECYCLE_BIN || (!$ignorePermission && !$this->board->getModeratorPermission('canDeletePost'))) {
			return;
		}
		
		if ($this->post != null && !$this->post->isDeleted) {
			$this->post->trash($this->reason);
			$this->thread->checkVisibility($this->reason);
			$this->removePost();
		}
	}
	
	/**
	 * Deletes the selected post.
	 */
	public function delete() {
		if ($this->post == null) {
			throw new IllegalLinkException();
		}
		
		// check permission
		$this->board->checkModeratorPermission('canDeletePostCompletely');
		
		// remove user stats
		ThreadEditor::updateUserStats($this->thread->threadID, 'delete');
		PostEditor::updateUserStats(ThreadEditor::getAllPostIDs($this->thread->threadID), 'delete');
		
		$this->post->unmark();
		$this->post->delete(false);
	
		if ($this->thread->hasPosts()) {
			// delete only post
			$this->thread->checkVisibility();
			if (!$this->post->isDeleted || !THREAD_ENABLE_RECYCLE_BIN) {
				$this->removePost();
			}
			else {
				ThreadEditor::refreshFirstPostIDAll($this->thread->threadID);
			}
			
			// re-add user stats
			ThreadEditor::updateUserStats($this->thread->threadID, 'enable');
			PostEditor::updateUserStats(ThreadEditor::getAllPostIDs($this->thread->threadID), 'enable');
		
			// forward
			HeaderUtil::redirect($this->url);
			exit;
		}
		else {
			// delete complete thread
			$this->thread->delete(false, false);
			if (!$this->post->isDeleted || !THREAD_ENABLE_RECYCLE_BIN) {
				$this->board->refresh();
				if ($this->post->time >= $this->board->getLastPostTime($this->thread->languageID)) {
					$this->board->setLastPosts();
				}
				
				// reset cache
				ThreadAction::resetCache();
			}
			
			HeaderUtil::redirect('index.php?page=Board&boardID='.$this->board->boardID.SID_ARG_2ND_NOT_ENCODED);
			exit;
		}
	}
	
	/**
	 * Restores the selected post.
	 */
	public function recover() {
		if (!$this->board->getModeratorPermission('canDeletePostCompletely')) {
			return;
		}
		
		if ($this->post != null && $this->post->isDeleted) {
			$this->post->restore();
			$this->thread->checkVisibility();
			$this->addPost();
		}
	}
	
	/**
	 * Disables the selected post.
	 */
	public function disable() {
		if (!$this->board->getModeratorPermission('canEnablePost')) {
			return;
		}
		
		if ($this->post != null && !$this->post->isDisabled) {
			$this->post->disable();
			$this->thread->checkVisibility();
			$this->removePost();
		}
	}
	
	/**
	 * Enables the selected post.
	 */
	public function enable() {
		if (!$this->board->getModeratorPermission('canEnablePost')) {
			return;
		}
		
		if ($this->post != null && $this->post->isDisabled) {
			$this->post->enable();
			$this->thread->checkVisibility();
			$this->addPost();
		}
	}
	
	/**
	 * Closes the selected post.
	 */
	public function close() {
		if (!$this->board->getModeratorPermission('canClosePost')) {
			return;
		}
		
		if ($this->post != null && !$this->post->isClosed) {
			$this->post->close();
		}
	}
	
	/**
	 * Opens the selected post.
	 */
	public function open() {
		if (!$this->board->getModeratorPermission('canClosePost')) {
			return;
		}
		
		if ($this->post != null && $this->post->isClosed) {
			$this->post->open();
		}
	}
	
	/**
	 * Unmarks all marked posts.
	 */
	public static function unmarkAll() {
		PostEditor::unmarkAll();
	}
	
	/**
	 * Deletes all marked posts.
	 */
	public function deleteAll() {
		if (!empty($this->postIDs)) {
			// get threadids 
			$threadIDs = PostEditor::getThreadIDs($this->postIDs);
			
			// get boards
			list($boards, $boardIDs) = ThreadEditor::getBoards($threadIDs);
			
			// check permissions
			foreach ($boards as $board) {
				$board->checkModeratorPermission('canDeletePost');
			}
			
			// get thread ids of deleted posts
			$threadIDs2 = '';
			$sql = "SELECT 	DISTINCT threadID
				FROM 	wbb".WBB_N."_post
				WHERE 	postID IN (".$this->postIDs.")
					AND isDeleted = 1";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!empty($threadIDs2)) $threadIDs2 .= ',';
				$threadIDs2 .= $row['threadID'];
			}
			
			// get boards of deleted posts
			list($boards2, $boardIDs2) = ThreadEditor::getBoards($threadIDs2);
			
			// check permissions (delete completely)
			foreach ($boards2 as $board2) {
				$board2->checkModeratorPermission('canDeletePostCompletely');
			}
			
			// remove user stats
			ThreadEditor::updateUserStats($threadIDs, 'delete');
			PostEditor::updateUserStats(ThreadEditor::getAllPostIDs($threadIDs), 'delete');
			
			// delete posts
			PostEditor::deleteAll($this->postIDs, false, $this->reason);
			PostEditor::unmarkAll();
			
			// handle threads (check for empty, deleted and hidden threads)
			ThreadEditor::checkVisibilityAll($threadIDs);
			
			// refresh last post, replies, attachments, polls in threads
			ThreadEditor::refreshAll($threadIDs);
			
			// re-add user stats
			ThreadEditor::updateUserStats($threadIDs, 'enable');
			PostEditor::updateUserStats(ThreadEditor::getAllPostIDs($threadIDs), 'enable');
			
			// refresh counts
			BoardEditor::refreshAll($boardIDs);
			
			// refresh last post in boards
			foreach ($boards as $board) {
				$board->setLastPosts();
			}
			
			// reset cache
			ThreadAction::resetCache();
		}
		
		// check whether the enable exists and forward
		if ($this->thread != null && $this->thread->hasPosts()) {
			HeaderUtil::redirect($this->url);
		}
		else if ($this->board != null) {
			HeaderUtil::redirect('index.php?page=Board&boardID='.$this->board->boardID.SID_ARG_2ND_NOT_ENCODED);
		}
		else {
			HeaderUtil::redirect('index.php'.SID_ARG_1ST);
		}
		exit;
	}
	
	/**
	 * Recovers all marked posts.
	 */
	public function recoverAll() {
		// get threadids 
		$threadIDs = PostEditor::getThreadIDs($this->postIDs);
		
		// get boards
		list($boards, $boardIDs) = ThreadEditor::getBoards($threadIDs);
		
		// check permissions
		foreach ($boards as $board) {
			$board->checkModeratorPermission('canDeletePostCompletely');
		}
		
		// recover posts
		PostEditor::restoreAll($this->postIDs);
		PostEditor::unmarkAll();
		
		// handle threads (check for empty, deleted and hidden threads)
		ThreadEditor::checkVisibilityAll($threadIDs);
		
		// refresh last post, replies, attachments, polls in threads
		ThreadEditor::refreshAll($threadIDs);
		
		// refresh counts
		BoardEditor::refreshAll($boardIDs);
		
		// refresh last post in boards
		foreach ($boards as $board) {
			$board->setLastPosts();
		}
		
		// reset cache
		ThreadAction::resetCache();
		
		// forward
		HeaderUtil::redirect($this->url);
		exit;
	}
	
	/**
	 * Copies the marked posts.
	 */
	public function copy() {
		if ($this->thread == null) {
			throw new IllegalLinkException();
		}
		
		$this->board->checkModeratorPermission('canCopyPost');
		
		// get threadids 
		$threadIDs = PostEditor::getThreadIDs($this->postIDs);
		
		// remove user stats
		ThreadEditor::updateUserStats($this->thread->threadID, 'delete');
		PostEditor::updateUserStats(ThreadEditor::getAllPostIDs($this->thread->threadID), 'delete');
		
		// copy posts
		PostEditor::copyAll($this->postIDs, $this->thread->threadID, null, $this->thread->boardID, false);
		PostEditor::unmarkAll();
		
		// refresh thread
		$this->thread->refresh();
		
		// re-add user stats
		ThreadEditor::updateUserStats($this->thread->threadID, 'enable');
		PostEditor::updateUserStats(ThreadEditor::getAllPostIDs($this->thread->threadID), 'enable');
		
		// refresh counts
		$this->board->refresh();
		
		// set last post in board
		$this->board->setLastPosts();
		
		// reset cache
		ThreadAction::resetCache();
		
		HeaderUtil::redirect('index.php?page=Thread&threadID='.$this->thread->threadID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * Moves the marked posts.
	 */
	public function move() {
		if ($this->thread == null) {
			throw new IllegalLinkException();
		}
		
		$this->board->checkModeratorPermission('canMovePost');
		
		// get threadids 
		$threadIDs = PostEditor::getThreadIDs($this->postIDs);
		
		// get boards
		list($boards, $boardIDs) = ThreadEditor::getBoards($threadIDs);
		
		// check permissions
		foreach ($boards as $board) {
			$board->checkModeratorPermission('canMovePost');
		}
		
		// remove user stats
		ThreadEditor::updateUserStats($threadIDs.','.$this->thread->threadID, 'delete');
		PostEditor::updateUserStats(ThreadEditor::getAllPostIDs($threadIDs.','.$this->thread->threadID), 'delete');
		
		// move posts
		PostEditor::moveAll($this->postIDs, $this->thread->threadID, $this->thread->boardID, false);
		PostEditor::unmarkAll();
		
		// handle threads (check for empty, deleted and hidden threads)
		ThreadEditor::checkVisibilityAll($threadIDs);
	
		// refresh last post, replies, attachments, polls in threads
		ThreadEditor::refreshAll($threadIDs.','.$this->thread->threadID);
		
		// re-add user stats
		ThreadEditor::updateUserStats($threadIDs.','.$this->thread->threadID, 'enable');
		PostEditor::updateUserStats(ThreadEditor::getAllPostIDs($threadIDs.','.$this->thread->threadID), 'enable');
		
		// refresh counts
		BoardEditor::refreshAll($boardIDs.','.$this->board->boardID);
		
		// refresh last post in boards
		$this->board->setLastPosts();
		foreach ($boards as $board) {
			$board->setLastPosts();
		}
		
		// reset cache
		ThreadAction::resetCache();
		
		HeaderUtil::redirect('index.php?page=Thread&threadID='.$this->thread->threadID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * Adds a post.
	 */
	public function addPost() {
		// reset cache
		ThreadAction::resetCache();
		
		// refresh thread
		$this->thread->refresh(false);
		if ($this->post->time > $this->thread->lastPostTime) {
			$this->thread->setLastPost($this->post);
		}
		
		// refresh board
		$this->board->refresh();
		if ($this->post->time > $this->board->getLastPostTime($this->thread->languageID)) {
			$this->board->setLastPosts();
		}
	}
	
	/**
	 * Removes a post.
	 */
	public function removePost() {
		// reset cache
		ThreadAction::resetCache();
		
		// refresh thread
		$this->thread->refresh(false);
		if ($this->post->time >= $this->thread->lastPostTime) {
			$this->thread->setLastPost();
		}
		
		// refresh board
		$this->board->refresh();
		if ($this->post->time >= $this->board->getLastPostTime($this->thread->languageID)) {
			$this->board->setLastPosts();
		}
	}
	
	/**
	 * Deletes the report of a post.
	 */
	public function removeReport() {
		if ($this->post == null) {
			throw new IllegalLinkException();
		}
		
		$this->board->checkModeratorPermission('canEditPost');
		
		PostEditor::removeReportData($this->post->postID);
		
		HeaderUtil::redirect($this->url);
		exit;
	}
	
	/**
	 * Deletes reports of marked posts.
	 */
	public function removeReports() {
		// get threadids 
		$threadIDs = PostEditor::getThreadIDs($this->postIDs);
		
		// get boards
		list($boards, $boardIDs) = ThreadEditor::getBoards($threadIDs);
		
		// check permissions
		foreach ($boards as $board) {
			$board->checkModeratorPermission('canEditPost');
		}
		
		PostEditor::removeReportData($this->postIDs);
		self::unmarkAll();
		
		HeaderUtil::redirect($this->url);
		exit;
	}
	
	/**
	 * Merges posts.
	 */
	public function merge() {
		if ($this->post === null || empty($this->postIDs)) {
			throw new IllegalLinkException();
		}
		
		// remove target post from source
		$postIDArray = explode(',', $this->postIDs);
		if (($key = array_search($this->post->postID, $postIDArray)) !== false) {
			unset($postIDArray[$key]);
			$this->postIDs = implode(',', $postIDArray);
		}
		
		// get thread ids
		$threadIDs = PostEditor::getThreadIDs($this->postIDs);
		
		// get boards
		list($boards, $boardIDs) = ThreadEditor::getBoards($threadIDs);
		
		// check permissions
		$this->board->checkModeratorPermission('canMergePost');
		foreach ($boards as $board) {
			$board->checkModeratorPermission('canMergePost');
		}
		
		// remove user stats
		ThreadEditor::updateUserStats($threadIDs, 'delete');
		PostEditor::updateUserStats(ThreadEditor::getAllPostIDs($threadIDs), 'delete');
		
		// merge posts
		PostEditor::mergeAll($this->postIDs, $this->post->postID);
		PostEditor::unmarkAll();
		
		// handle threads (check for empty, deleted and hidden threads)
		ThreadEditor::checkVisibilityAll($threadIDs);
	
		// refresh last post, replies, attachments, polls in threads
		ThreadEditor::refreshAll($threadIDs);
		
		// re-add user stats
		ThreadEditor::updateUserStats($threadIDs, 'enable');
		PostEditor::updateUserStats(ThreadEditor::getAllPostIDs($threadIDs), 'enable');
		
		// refresh counts
		BoardEditor::refreshAll($boardIDs);
		
		// refresh last post in boards
		$this->board->setLastPosts();
		foreach ($boards as $board) {
			$board->setLastPosts();
		}
		
		HeaderUtil::redirect($this->url);
		exit;
	}
}
?>