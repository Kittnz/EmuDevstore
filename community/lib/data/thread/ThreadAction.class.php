<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/BoardEditor.class.php');
require_once(WBB_DIR.'lib/data/thread/ThreadEditor.class.php');
require_once(WBB_DIR.'lib/data/post/PostEditor.class.php');

/**
 * Executes moderation actions on threads.
 * 
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.thread
 * @category 	Burning Board
 */
class ThreadAction {
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
	
	
	protected $boardID = 0;
	protected $threadID = 0;
	protected $topic = '';
	protected $prefix = '';
	protected $url = '';
	protected $threadIDs = null;
	protected $postIDs = null;
	protected $reason = '';
	
	/**
	 * Creates a new ThreadAction object.
	 * 
	 * @param	BoardEditor	$board
	 * @param	ThreadEditor	$thread
	 * @param	PostEditor	$post
	 */
	public function __construct($board = null, $thread = null, $post = null, $threadID = 0, $topic = '', $prefix = '', $forwardURL = '', $reason = '') {
		$this->board = $board;
		$this->thread = $thread;
		$this->post = $post;
		if ($threadID != 0) $this->threadID = $threadID;
		else if ($thread) $this->threadID = $thread->threadID;
		if ($board) $this->boardID = $board->boardID;
		$this->topic = $topic;
		$this->prefix = $prefix;
		$this->url = $forwardURL;
		if (empty($this->url)) $this->url = 'index.php?page=Board&boardID='.$this->boardID.SID_ARG_2ND_NOT_ENCODED;
		$this->reason = $reason;
		
		// get marked threads from session
		$this->getMarkedThreads();
	}
	
	/**
	 * Gets marked threads and posts from session.
	 */
	public function getMarkedThreads() {
		$sessionVars = WCF::getSession()->getVars();
		if (isset($sessionVars['markedThreads'])) {
			$this->threadIDs = implode(',', $sessionVars['markedThreads']);	
		}
		
		if (isset($sessionVars['markedPosts'])) {
			$this->postIDs	= implode(',', $sessionVars['markedPosts']);	
		}
	}
	
	/**
	 * Changes the topic of the selected thread.
	 */
	public function changeTopic() {
		if (!$this->board->getModeratorPermission('canEditPost')) {
			return;
		}
		
		if (!empty($this->topic) && $this->thread != null) {
			$this->thread->setTopic($this->topic);
			
			if ($this->thread->lastPostTime == $this->board->getLastPostTime()) {
				WCF::getCache()->clearResource('boardData', true);
			}
		}
	}
	
	/**
	 * Changes the prefix of the selected thread.
	 */
	public function changePrefix() {
		if (!$this->board->getModeratorPermission('canEditPost')) {
			return;
		}
		
		$prefixOptions = $this->board->getPrefixOptions();
		if ((empty($this->prefix) && !$this->board->prefixRequired) || isset($prefixOptions[$this->prefix])) {
			$this->thread->setPrefix($this->prefix);
			
			if ($this->thread->lastPostTime == $this->board->getLastPostTime()) {
				WCF::getCache()->clearResource('boardData', true);
			}
		}
	}
	
	/**
	 * Marks the selected thread.
	 */
	public function mark() {
		if ($this->thread != null) {
			$this->thread->mark();
		}
		else if (is_array($this->threadID)) {
			foreach ($this->threadID as $threadID) {
				$thread = new ThreadEditor($threadID);
				$thread->enter();
				$thread->mark();
			}
		}
	}
	
	/**
	 * Unmarks the selected thread.
	 */
	public function unmark() {
		if ($this->thread != null) {
			$this->thread->unmark();
		}
		else if (is_array($this->threadID)) {
			foreach ($this->threadID as $threadID) {
				$thread = new ThreadEditor($threadID);
				$thread->enter();
				$thread->unmark();
			}
		}
	}
	
	/**
	 * Trashes the selected thread.
	 */
	public function trash() {
		if (!THREAD_ENABLE_RECYCLE_BIN || !$this->board->getModeratorPermission('canDeleteThread')) {
			return;
		}
		
		if ($this->thread != null && !$this->thread->isDeleted) {
			$this->thread->trash(true, $this->reason);
			$this->removeThread();
		}
		
		if (strpos($this->url, 'page=Thread') !== false) HeaderUtil::redirect('index.php?page=Board&boardID='.$this->thread->boardID.SID_ARG_2ND_NOT_ENCODED);
		else HeaderUtil::redirect($this->url);
		exit;
	}
	
	/**
	 * Deletes the selected thread.
	 */
	public function delete() {
		if ($this->thread == null) {
			throw new IllegalLinkException();
		}
		
		$this->board->checkModeratorPermission('canDeleteThreadCompletely');
		
		$this->thread->unmark();
		$this->thread->delete();
		if (!$this->thread->isDeleted || !THREAD_ENABLE_RECYCLE_BIN) {
			$this->removeThread();
		}
		
		if (strpos($this->url, 'page=Thread') !== false) HeaderUtil::redirect('index.php?page=Board&boardID='.$this->thread->boardID.SID_ARG_2ND_NOT_ENCODED);
		else HeaderUtil::redirect($this->url);
		exit;
	}
	
	/**
	 * Recovers the selected thread.
	 */
	public function recover() {
		if (!$this->board->getModeratorPermission('canDeleteThreadCompletely')) {
			return;
		}
		
		if ($this->thread != null && $this->thread->isDeleted) {
			$this->thread->restore();
			$this->thread->refresh();
			$this->addThread();
		}
	}
	
	/**
	 * Disables the selected thread.
	 */
	public function disable() {
		if (!$this->board->getModeratorPermission('canEnableThread')) {
			return;
		}
		
		if ($this->thread != null && !$this->thread->isDisabled) {
			$this->thread->disable();
			$this->removeThread();
		}
	}
	
	/**
	 * Enables the selected thread.
	 */
	public function enable() {
		if (!$this->board->getModeratorPermission('canEnableThread')) {
			return;
		}
		
		if ($this->thread != null && $this->thread->isDisabled) {
			$this->thread->enable();
			$this->thread->refresh();
			$this->addThread();
		}
	}
	
	/**
	 * Closes the selected thread.
	 */
	public function close() {
		if (!$this->board->getModeratorPermission('canCloseThread')) {
			return;
		}
		
		if ($this->thread != null && !$this->thread->isClosed) {
			$this->thread->close();
		}
	}
	
	/**
	 * Closes all marked threads.
	 */
	public function closeAll() {
		list($boards, $boardIDs) = ThreadEditor::getBoards($this->threadIDs);
		
		// check permissions
		foreach ($boards as $board) {
			$board->checkModeratorPermission('canCloseThread');
		}
		
		ThreadEditor::closeAll($this->threadIDs);
		ThreadEditor::unmarkAll();
		
		HeaderUtil::redirect($this->url);
		exit;
	}
	
	/**
	 * Opens the selected thread.
	 */
	public function open() {
		if (!$this->board->getModeratorPermission('canCloseThread')) {
			return;
		}
		
		if ($this->thread != null && $this->thread->isClosed) {
			$this->thread->open();
		}
	}
	
	/**
	 * Unmarks all marked threads.
	 */
	public static function unmarkAll() {
		ThreadEditor::unmarkAll();
	}
	
	/**
	 * Deletes all marked threads.
	 */
	public function deleteAll() {
		if (!empty($this->threadIDs)) {
			list($boards, $boardIDs) = ThreadEditor::getBoards($this->threadIDs);
			
			// check permissions
			$sql = "SELECT 	threadID, isDeleted, boardID
				FROM 	wbb".WBB_N."_thread
				WHERE 	threadID IN (".$this->threadIDs.")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if ($row['isDeleted'] || !THREAD_ENABLE_RECYCLE_BIN) {
					$boards[$row['boardID']]->checkModeratorPermission('canDeleteThreadCompletely');
				}
				else {
					$boards[$row['boardID']]->checkModeratorPermission('canDeleteThread');
				}
			}
			
			ThreadEditor::deleteAll($this->threadIDs, true, $this->reason);
			ThreadEditor::unmarkAll();
			
			// refresh counts
			BoardEditor::refreshAll($boardIDs);
			
			// set last post
			foreach ($boards as $board) {
				$board->setLastPosts();
			}
			
			self::resetCache();
		}
		HeaderUtil::redirect($this->url);
		exit;
	}
	
	/**
	 * Recovers all marked threads.
	 */
	public function recoverAll() {
		if (!empty($this->threadIDs)) {
			list($boards, $boardIDs) = ThreadEditor::getBoards($this->threadIDs);
			
			// check permissions
			$sql = "SELECT 	boardID
				FROM 	wbb".WBB_N."_thread
				WHERE 	threadID IN (".$this->threadIDs.")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$boards[$row['boardID']]->checkModeratorPermission('canDeleteThreadCompletely');
			
			}
			
			ThreadEditor::restoreAll($this->threadIDs);
			ThreadEditor::unmarkAll();
			
			// refresh counts
			BoardEditor::refreshAll($boardIDs);
			
			// set last post
			foreach ($boards as $board) {
				$board->setLastPosts();
			}
			
			self::resetCache();
		}
		HeaderUtil::redirect($this->url);
		exit;
	}
	
	/**
	 * Moves the marked threads.
	 */
	public function move() {
		if ($this->board == null) {
			throw new IllegalLinkException();
		}
		
		$this->board->checkModeratorPermission('canMoveThread');
		
		list($boards, $boardIDs) = ThreadEditor::getBoards($this->threadIDs);
		
		// check permissions
		foreach ($boards as $board) {
			$board->checkModeratorPermission('canMoveThread');
		}
		
		ThreadEditor::moveAll($this->threadIDs, $this->boardID);
		ThreadEditor::unmarkAll();
		
		// refresh counts
		BoardEditor::refreshAll($boardIDs.','.$this->board->boardID);
		
		// set last post
		$this->board->setLastPosts();
		foreach ($boards as $board) {
			$board->setLastPosts();
		}
		
		self::resetCache();
		HeaderUtil::redirect($this->url);
		exit;
	}
	
	/**
	 * Moves the marked threads with link.
	 */
	public function moveWithLink() {
		if ($this->board == null) {
			throw new IllegalLinkException();
		}
		
		$this->board->checkModeratorPermission('canMoveThread');
		
		list($boards, $boardIDs) = ThreadEditor::getBoards($this->threadIDs);
		
		// check permissions
		foreach ($boards as $board) {
			$board->checkModeratorPermission('canMoveThread');
		}
		
		ThreadEditor::createLinks($this->threadIDs, $this->boardID);
		ThreadEditor::moveAll($this->threadIDs, $this->boardID);
		ThreadEditor::unmarkAll();
		
		// refresh counts
		BoardEditor::refreshAll($boardIDs.','.$this->board->boardID);
				
		// set last post
		$this->board->setLastPosts();
		foreach ($boards as $board) {
			$board->setLastPosts();
		}
		
		self::resetCache();
		HeaderUtil::redirect($this->url);
		exit;
	}
	
	/**
	 * Copies the marked threads.
	 */
	public function copy() {
		if ($this->board == null) {
			throw new IllegalLinkException();
		}
		
		$this->board->checkModeratorPermission('canCopyThread');
				
		ThreadEditor::copyAll($this->threadIDs, $this->boardID);
		ThreadEditor::unmarkAll();
		
		// set last post
		$this->board->refresh();
		$this->board->setLastPosts();
		self::resetCache();
		
		HeaderUtil::redirect($this->url);
		exit;
	}
	
	/**
	 * Merges the marked threads.
	 */
	public function merge() {
		if ($this->thread == null) {
			throw new IllegalLinkException();
		}
		
		$this->board->checkModeratorPermission('canMergeThread');
		
		list($boards, $boardIDs) = ThreadEditor::getBoards($this->threadIDs);
		
		// check permissions
		foreach ($boards as $board) {
			$board->checkModeratorPermission('canMergeThread');
		}
		
		$this->thread->merge($this->threadIDs);
		ThreadEditor::unmarkAll();
		
		// refresh counts
		BoardEditor::refreshAll($boardIDs.','.$this->board->boardID);
		
		// set last post
		$this->board->setLastPosts();
		foreach ($boards as $board) {
			$board->setLastPosts();
		}
		
		self::resetCache();
		HeaderUtil::redirect('index.php?page=Thread&threadID='.$this->thread->threadID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * Copies and merges the marked threads.
	 */
	public function copyAndMerge() {
		if ($this->thread == null) {
			throw new IllegalLinkException();
		}
		
		$this->board->checkModeratorPermission('canMergeThread');
		
		$this->thread->copyAndMerge($this->threadIDs);
		ThreadEditor::unmarkAll();
		
		// set last post
		$this->board->refresh();
		$this->board->setLastPosts();
		self::resetCache();
		
		HeaderUtil::redirect('index.php?page=Thread&threadID='.$this->thread->threadID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * Moves and inserts the marked posts in a new thread.
	 */
	public function moveAndInsert() {
		if ($this->board == null) {
			throw new IllegalLinkException();
		}
		
		// check permission
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
		ThreadEditor::updateUserStats($threadIDs, 'delete');
		PostEditor::updateUserStats(ThreadEditor::getAllPostIDs($threadIDs), 'delete');
		
		// create new thread
		$thread = ThreadEditor::createFromPosts($this->postIDs, $this->board->boardID);
		
		// move posts
		PostEditor::moveAll($this->postIDs, $thread->threadID, $thread->boardID);
		PostEditor::unmarkAll();
		
		// check threads
		ThreadEditor::checkVisibilityAll($threadIDs.','.$thread->threadID);
		
		// refresh
		ThreadEditor::refreshAll($threadIDs.','.$thread->threadID);
		
		// re-add user stats
		ThreadEditor::updateUserStats($threadIDs, 'enable');
		PostEditor::updateUserStats($this->postIDs, 'enable');
		PostEditor::updateUserStats(ThreadEditor::getAllPostIDs($threadIDs), 'enable');
		
		// refresh counts
		BoardEditor::refreshAll($boardIDs.','.$this->board->boardID);
		
		// set last post
		$this->board->setLastPosts();
		foreach ($boards as $board) {
			$board->setLastPosts();
		}
		
		self::resetCache();
		HeaderUtil::redirect($this->url);
		exit;
	}
	
	/**
	 * Copies and inserts the marked posts in a new thread.
	 */
	public function copyAndInsert() {
		if ($this->board == null) {
			throw new IllegalLinkException();
		}
		
		$this->board->checkModeratorPermission('canCopyPost');
		
		// create new thread
		$thread = ThreadEditor::createFromPosts($this->postIDs, $this->board->boardID);
	
		// move posts
		PostEditor::copyAll($this->postIDs, $thread->threadID, null, $this->board->boardID);
		PostEditor::unmarkAll();
		
		// check thread
		$thread->checkVisibility();
		
		// refresh
		$thread->refresh();
		
		// set last post
		$this->board->refresh();
		$this->board->setLastPosts();
		self::resetCache();
		
		HeaderUtil::redirect($this->url);
		exit;
	}
	
	/**
	 * Sticks a thread.
	 */
	public function stick() {
		// check permission
		$this->board->checkModeratorPermission('canPinThread');
		if ($this->thread == null) throw new IllegalLinkException();
		
		$this->thread->stick();
		HeaderUtil::redirect($this->url);
		exit;
	}
	
	/**
	 * Unsticks a thread.
	 */
	public function unstick() {
		// check permission
		$this->board->checkModeratorPermission('canPinThread');
		if ($this->thread == null) throw new IllegalLinkException();
		
		$this->thread->unstick();
		HeaderUtil::redirect($this->url);
		exit;
	}
	
	/**
	 * Marks a thread as done.
	 */
	public function markAsDone() {
		// check permission
		$this->board->checkModeratorPermission('canMarkAsDoneThread');
		if ($this->thread == null) throw new IllegalLinkException();
		
		$this->thread->markAsDone();
		exit;
	}
	
	/**
	 * Marks a thread as undone.
	 */
	public function markAsUndone() {
		// check permission
		$this->board->checkModeratorPermission('canMarkAsDoneThread');
		if ($this->thread == null) throw new IllegalLinkException();
		
		$this->thread->markAsUndone();
		exit;
	}
	
	/**
	 * Adds a thread.
	 */	
	public function addThread() {
		self::resetCache();
		
		// refresh board last post
		$this->board->refresh();
		if ($this->thread->lastPostTime > $this->board->getLastPostTime($this->thread->languageID)) {
			$this->board->setLastPost($this->thread);
		}
	}
	
	/**
	 * Removes a thread.
	 */
	public function removeThread() {
		self::resetCache();
		
		// refresh board last post
		$this->board->refresh();
		if ($this->thread->lastPostTime >= $this->board->getLastPostTime($this->thread->languageID)) {
			$this->board->setLastPosts();
		}
	}
	
	/**
	 * Resets the relavant cache resources.
	 */
	public static function resetCache() {
		// reset stat cache
		WCF::getCache()->clearResource('stat');
		// reset board data cache
		WCF::getCache()->clearResource('boardData', true);
	}
}
?>