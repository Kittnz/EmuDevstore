<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/BoardEditor.class.php');
require_once(WBB_DIR.'lib/data/post/PostEditor.class.php');
require_once(WBB_DIR.'lib/data/thread/Thread.class.php');

/**
 * ThreadEditor provides functions to create and edit the data of a thread.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.thread
 * @category 	Burning Board
 */
class ThreadEditor extends Thread {
	/**
	 * Assigns an announcement to the given boards.
	 * 
	 * @param	array		$boardIDs
	 */
	public function assignBoards($boardIDs) {
		if (!in_array($this->boardID, $boardIDs)) {
			$boardIDs[] = $this->boardID;
		}
		
		$boardIDs = array_unique($boardIDs);
	
		$inserts = '';
		foreach ($boardIDs as $boardID) {
			if (!empty($inserts)) $inserts .= ',';
			$inserts .= '('.$boardID.', '.$this->threadID.')';
		}
	
		// insert new boards
		$sql = "INSERT IGNORE INTO 	wbb".WBB_N."_thread_announcement
						(boardID, threadID)
			VALUES			".$inserts;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Removes assigned boards.
	 */
	public function removeAssignedBoards() {
		$sql = "DELETE FROM 	wbb".WBB_N."_thread_announcement
			WHERE		threadID = ".$this->threadID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Returns the list of assigned boards.
	 * 
	 * @return	array		list of board ids
	 */
	public function getAssignedBoards() {
		$boardIDs = array();
		$sql = "SELECT	boardID
			FROM	wbb".WBB_N."_thread_announcement
			WHERE	threadID = ".$this->threadID;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$boardIDs[] = $row['boardID'];
		}
		
		return $boardIDs;
	}
	
	/**
	 * Adds a new post to this thread.
	 *
	 * @param	Post		$post		the new post
	 * @param	integer		$closedThread	true (1), if thread ought to be closed
	 */
	public function addPost($post, $closeThread = 0) {
		$this->data['lastPoster'] = $post->username;
		$this->data['lastPostTime'] = $post->time;
		$this->data['lastPosterID'] = $post->userID;
		$this->data['replies']++;
		$this->data['attachments'] += $post->attachments;
		$this->data['polls'] += $post->pollID ? 1 : 0;
		
		$sql = "UPDATE 	wbb".WBB_N."_thread
			SET	".(($closeThread) ? ("isClosed = 1,") : (""))."
				replies = replies + 1,
				attachments = attachments + ".$post->attachments.",
				polls = polls + ".($post->pollID ? 1 : 0).",
				lastPostTime = ".$this->lastPostTime.",
				lastPosterID = ".$this->lastPosterID.",
				lastPoster = '".escapeString($this->lastPoster)."'
			WHERE 	threadID = ".$this->threadID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Updates the type of subscription on this thread for the active user.
	 *
	 * @param	integer		$subscription		new type of subscription on this thread for the active user
	 */
	public function setSubscription($subscription) {
		if (WCF::getUser()->userID && $this->subscribed != $subscription) {
			if (!$subscription) {
				// delete notification
				$sql = "DELETE FROM 	wbb".WBB_N."_thread_subscription
					WHERE		userID = ".WCF::getUser()->userID."
							AND threadID = ".$this->threadID;
				WCF::getDB()->sendQuery($sql);
			}
			else {
				// add new notification
				$sql = "INSERT INTO 	wbb".WBB_N."_thread_subscription
							(userID, threadID, enableNotification)
					VALUES		(".WCF::getUser()->userID.", ".$this->threadID.", ".WCF::getUser()->enableEmailNotification.")";
				WCF::getDB()->sendQuery($sql);
			}
		}
	}
	
	/**
	 * Sets the topic of this thread.
	 * 
	 * @param	string		$topic		new topic for this thread
	 */
	public function setTopic($topic, $updateFirstPost = true) {
		if ($topic == $this->topic) return;
		
		$this->topic = $topic;
		$sql = "UPDATE 	wbb".WBB_N."_thread
			SET	topic = '".escapeString($topic)."'
			WHERE 	threadID = ".$this->threadID;
		WCF::getDB()->registerShutdownUpdate($sql);
		
		// update the subject of the first post in this thread
		if ($updateFirstPost && $this->firstPostID) {
			$sql = "UPDATE 	wbb".WBB_N."_post
				SET	subject = '".escapeString($topic)."'
				WHERE 	postID = ".$this->firstPostID;
			WCF::getDB()->registerShutdownUpdate($sql);
		}
	}
	
	/**
	 * Sets the prefix of this thread.
	 * 
	 * @param	string		$prefix
	 */
	public function setPrefix($prefix) {
		if ($prefix == $this->prefix) return;
		
		$this->prefix = $prefix;
		$sql = "UPDATE 	wbb".WBB_N."_thread
			SET	prefix = '".escapeString($prefix)."'
			WHERE 	threadID = ".$this->threadID;
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Returns true, if this thread contains posts.
	 * 
	 * @return	boolean		true, if this thread contains posts
	 */
	public function hasPosts() {
		$sql = "SELECT 	COUNT(*) AS count
			FROM 	wbb".WBB_N."_post
			WHERE 	threadID = ".$this->threadID;
		$result = WCF::getDB()->getFirstRow($sql);
		return $result['count'];
	}
	
	/**
	 * Sets the last post of this thread.
	 * 
	 * @param	Post	$post
	 */
	public function setLastPost($post = null) {
		self::__setLastPost($this->threadID, $post);
	}
	
	/**
	 * Marks this thread.
	 */
	public function mark() {
		$markedThreads = self::getMarkedThreads();
		if ($markedThreads == null || !is_array($markedThreads)) { 
			$markedThreads = array($this->threadID);
			WCF::getSession()->register('markedThreads', $markedThreads);
		}
		else {
			if (!in_array($this->threadID, $markedThreads)) {
				array_push($markedThreads, $this->threadID);
				WCF::getSession()->register('markedThreads', $markedThreads);
			}
		}
	}
	
	/**
	 * Unmarks this thread.
	 */
	public function unmark() {
		$markedThreads = self::getMarkedThreads();
		if (is_array($markedThreads) && in_array($this->threadID, $markedThreads)) {
			$key = array_search($this->threadID, $markedThreads);
			
			unset($markedThreads[$key]);
			if (count($markedThreads) == 0) {
				self::unmarkAll();
			} 
			else {
				WCF::getSession()->register('markedThreads', $markedThreads);
			}
		}
	}
	
	/**
	 * Moves this thread into the recycle bin.
	 */
	public function trash($trashPosts = true, $reason = '') {
		self::trashAll($this->threadID, $trashPosts, $reason);
	}
	
	/**
	 * Deletes this thread completely.
	 */
	public function delete($deletePosts = true, $updateUserStats = true) {
		self::deleteAllCompletely($this->threadID, $deletePosts, $updateUserStats);
	}
	
	/**
	 * Restores this deleted thread.
	 */
	public function restore($restorePosts = true) {
		self::restoreAll($this->threadID, $restorePosts);
	}
	
	/**
	 * Disables this thread.
	 */
	public function disable($disablePosts = true) {
		self::disableAll($this->threadID, $disablePosts);
	}
	
	/**
	 * Enables this thread.
	 */
	public function enable($enablePosts = true) {
		self::enableAll($this->threadID, $enablePosts);
	}
	
	/**
	 * Closes this thread.
	 */
	public function close() {
		self::closeAll($this->threadID);
	}
	
	/**
	 * Closes the threads with given ids.
	 * 
	 * @param	string		$threadIDs
	 */
	public static function closeAll($threadIDs) {
		if (empty($threadIDs)) return;
		
		$sql = "UPDATE 	wbb".WBB_N."_thread
			SET	isClosed = 1
			WHERE 	threadID IN (".$threadIDs.")";
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Opens this thread.
	 */
	public function open() {
		$sql = "UPDATE 	wbb".WBB_N."_thread
			SET	isClosed = 0
			WHERE 	threadID = ".$this->threadID;
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Returns the post ids of this thread.
	 */
	public function getPostIDs() {
		return self::getAllPostIDs($this->threadID);
	}
	
	/**
	 * Copies the threads with the given thread ids and merges the copies with this thread.
	 */
	public function copyAndMerge($threadIDs) {
		if (empty($threadIDs)) return;
		
		// remove user stats
		self::updateUserStats($this->threadID, 'delete');
		PostEditor::updateUserStats(self::getAllPostIDs($this->threadID), 'delete');
		
		// copy posts
		PostEditor::copyAll(self::getAllPostIDs($threadIDs), $this->threadID, null, $this->boardID, false);
		
		// re-add user stats
		$this->refresh();
		self::updateUserStats($this->threadID, 'enable');
		PostEditor::updateUserStats(self::getAllPostIDs($this->threadID), 'enable');
	}
	
	/**
	 * Merges the threads with the given thread ids with this thread.
	 */
	public function merge($threadIDs) {
		if (empty($threadIDs)) return;
		
		$threadIDArray = explode(',', $threadIDs);
		if (in_array($this->threadID, $threadIDArray)) {
			unset($threadIDArray[array_search($this->threadID, $threadIDArray)]);
			$threadIDs = implode(',', $threadIDArray);
			if (empty($threadIDs)) return;
		}
		
		// add views
		$sql = "SELECT	SUM(views) AS views
			FROM	wbb".WBB_N."_thread
			WHERE	threadID IN (".$threadIDs.")";
		$row = WCF::getDB()->getFirstRow($sql);
		if ($row['views']) {
			$sql = "UPDATE	wbb".WBB_N."_thread
				SET	views = views + ".$row['views']."
				WHERE	threadID = ".$this->threadID;
			WCF::getDB()->sendQuery($sql);
		}
		
		// update tags
		if (MODULE_TAGGING) {
			require_once(WCF_DIR.'lib/data/tag/TagEngine.class.php');
			$taggable = TagEngine::getInstance()->getTaggable('com.woltlab.wbb.thread');
			$sql = "UPDATE IGNORE	wcf".WCF_N."_tag_to_object
				SET		objectID = ".$this->threadID."
				WHERE		taggableID = ".$taggable->getTaggableID()."
						AND languageID = ".$this->languageID."
						AND objectID IN (".$threadIDs.")";
			WCF::getDB()->sendQuery($sql);
			$sql = "DELETE FROM	wcf".WCF_N."_tag_to_object
				WHERE		taggableID = ".$taggable->getTaggableID()."
						AND objectID IN (".$threadIDs.")";
			WCF::getDB()->sendQuery($sql);
		}
		
		// remove user stats
		$postIDs = self::getAllPostIDs($threadIDs);
		self::updateUserStats($threadIDs.','.$this->threadID, 'delete');
		PostEditor::updateUserStats($postIDs.','.self::getAllPostIDs($this->threadID), 'delete');
		
		// move posts
		PostEditor::moveAll($postIDs, $this->threadID, $this->boardID, false);
		
		// re-add user stats
		$this->refresh();
		self::updateUserStats($this->threadID, 'enable');
		PostEditor::updateUserStats(self::getAllPostIDs($this->threadID), 'enable');
		
		// delete threads
		self::deleteAllCompletely($threadIDs, false, false);
	}
	
	/**
	 * Refreshes the last post, replies, amount of attachments and amount of polls of this thread.
	 */
	public function refresh($refreshLastPost = true) {
		self::refreshAll($this->threadID, $refreshLastPost);
	}
	
	/**
	 * Creates a link of this thread.
	 */
	public function createLink() {
		$sql = "INSERT INTO	wbb".WBB_N."_thread
					(boardID, languageID, prefix, topic, time, userID, username, lastPostTime, 
					lastPosterID, lastPoster, replies, views, ratings, rating, attachments,
					polls, isAnnouncement, isSticky, isDisabled, everEnabled, isClosed, isDeleted,
					movedThreadID, movedTime, deleteTime, deletedBy, deletedByID, deleteReason)
			VALUES		(".$this->boardID.",
					".$this->languageID.",
					'".escapeString($this->prefix)."',
					'".escapeString($this->topic)."',
					".$this->time.",
					".$this->userID.",
					'".escapeString($this->username)."',
					".$this->lastPostTime.",
					".$this->lastPosterID.",
					'".escapeString($this->lastPoster)."',
					".$this->replies.",
					".$this->views.",
					".$this->ratings.",
					".$this->rating.",
					".$this->attachments.",
					".$this->polls.",
					".$this->isAnnouncement.",
					".$this->isSticky.",
					".$this->isDisabled.",
					".$this->everEnabled.",
					".$this->isClosed.",
					".$this->isDeleted.",
					".$this->threadID.",
					".TIME_NOW.",
					".$this->deleteTime.",
					'".escapeString($this->deletedBy)."',
					".$this->deletedByID.",
					'".escapeString($this->deleteReason)."')";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Copies the sql data of this thread.
	 */
	public function copy($boardID) {
		return self::insert($this->topic, $boardID, array(
			'languageID' => $this->languageID,
			'userID' => $this->userID,
			'username' => $this->username,
			'prefix' => $this->prefix,
			'time' => $this->time,
			'lastPostTime' => $this->lastPostTime,
			'lastPosterID' => $this->lastPosterID,
			'lastPoster' => $this->lastPoster,
			'replies' => $this->replies,
			'views' => $this->views,
			'ratings' => $this->ratings,
			'rating' => $this->rating,
			'attachments' => $this->attachments,
			'polls' => $this->polls,
			'isSticky' => $this->isSticky,
			'isAnnouncement' => $this->isAnnouncement,
			'isClosed' => $this->isClosed,
			'isDisabled' => $this->isDisabled,
			'everEnabled' => $this->everEnabled,
			'isDeleted' => $this->isDeleted
		));
	}
	
	/**
	 * Checks whether this thread is empty, thrashed or hidden. 
	 */
	public function checkVisibility($reason = '') {
		self::checkVisibilityAll($this->threadID, $reason);
	}
	
	/**
	 * Creates a new thread with the given data in the database.
	 * Returns a ThreadEditor object of the new thread.
	 * 
	 * @param	integer				$boardID							
	 * @param	string				$subject		subject of the new thread
	 * @param	string				$text			text of the first post in the new thread
	 * @param	integer				$authorID		user id of the author of the new thread
	 * @param	string				$author			username of the author of the new thread
	 * @param	integer				$sticky			true (1), if it is a sticky thread
	 * @param	integer				$isClosed		true (1), if it is a closed thread					
	 * @param	array				$options		options of the new thread
	 * @param	integer				$subscription		type of notifation on the new thread for the active user					
	 * @param	AttachmentsEditor		$attachmentsEditor
	 * @param	PollEditor			$pollEditor
	 * 
	 * @return	ThreadEditor						the new thread		
	 */
	public static function create($boardID, $languageID, $prefix, $subject, $text, $userID, $username, $sticky = 0, $announcement = 0, $closed = 0, $options = array(), $subscription = 0, $attachments = null, $poll = null, $disabled = 0) {
		$attachmentsAmount = $attachments != null ? count($attachments->getAttachments()) : 0;
		$polls = ($poll != null && $poll->pollID) ? 1 : 0;
		
		// insert thread
		$threadID = self::insert($subject, $boardID, array(
			'languageID' => $languageID,
			'userID' => $userID,
			'username' => $username,
			'prefix' => $prefix,
			'time' => TIME_NOW,
			'lastPostTime' => TIME_NOW,
			'lastPosterID' => $userID,
			'lastPoster' => $username,
			'attachments' => $attachmentsAmount,
			'polls' => $polls,
			'isSticky' => $sticky,
			'isAnnouncement' => $announcement,
			'isClosed' => $closed,
			'isDisabled' => $disabled,
			'everEnabled' => ($disabled ? 0 : 1)
		));
		
		// create post
		$post = PostEditor::create($threadID, $subject, $text, $userID, $username, $options, $attachments, $poll, null, $disabled, true);
		
		// update first post id
		$sql = "UPDATE	wbb".WBB_N."_thread
			SET	firstPostID = ".$post->postID."
			WHERE	threadID = ".$threadID;
		WCF::getDB()->sendQuery($sql);
		
		// update first post preview
		PostEditor::updateFirstPostPreview($threadID, $post->postID, $text, $options);
		
		// get thread object
		$thread = new ThreadEditor($threadID);
		
		// update subscription
		$thread->setSubscription($subscription);
		
		// get similar threads
		self::updateSimilarThreads($threadID, $subject, $boardID);
		
		return $thread;
	}
	
	/**
	 * Creates the thread row in database table.
	 *
	 * @param 	string 		$topic
	 * @param	integer		$boardID
	 * @param 	array		$additionalFields
	 * @return	integer		new thread id
	 */
	public static function insert($topic, $boardID, $additionalFields = array()) { 
		$keys = $values = '';
		foreach ($additionalFields as $key => $value) {
			$keys .= ','.$key;
			$values .= ",'".escapeString($value)."'";
		}
		
		$sql = "INSERT INTO	wbb".WBB_N."_thread
					(boardID, topic
					".$keys.")
			VALUES		(".$boardID.", '".escapeString($topic)."'
					".$values.")";
		WCF::getDB()->sendQuery($sql);
		return WCF::getDB()->getInsertID();
	}
	
	/**
	 * Updates similar threads.
	 * 
	 * @param	integer		$threadID
	 * @param	string		$subject
	 * @param	integer		$boardID
	 */
	public static function updateSimilarThreads($threadID, $subject, $boardID = 0) {
		if (THREAD_ENABLE_SIMILAR_THREADS) {
			// get board ids
			$notSearchableBoardIDArray = array();
			$boards = WCF::getCache()->get('board', 'boards');
			foreach ($boards as $board) if (!$board->searchableForSimilarThreads) $notSearchableBoardIDArray[] = $board->boardID;

			// get similar posts
			$matches = array();
			$sql = "SELECT		post.postID,
						MATCH (post.subject, post.message) AGAINST ('".escapeString($subject)."')
						+ (5 / (1 + POW(LN(1 + (".TIME_NOW." - post.time) / 2592000), 2)))
						".($boardID != 0 ? "+ IF(thread.boardID=".$boardID.",2,0)" : "")." AS relevance
				FROM		wbb".WBB_N."_post post
				LEFT JOIN	wbb".WBB_N."_thread thread USING (threadID)
				WHERE		MATCH (post.subject, post.message) AGAINST ('".escapeString($subject)."' IN BOOLEAN MODE)
						AND (post.threadID <> ".$threadID.")
						".((count($notSearchableBoardIDArray) > 0) ? ' AND thread.boardID NOT IN ('.implode(',', $notSearchableBoardIDArray).')' : '')."
				GROUP BY	post.postID
				ORDER BY	relevance DESC";
			$result = WCF::getDB()->sendQuery($sql, 5);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$matches[] = $row['postID'];
			}
			
			// save matches
			if (count($matches)) {
				$sql = "INSERT IGNORE INTO	wbb".WBB_N."_thread_similar
								(threadID, similarThreadID)
					SELECT			".$threadID.", threadID
					FROM			wbb".WBB_N."_post
					WHERE			postID IN (".implode(',', $matches).")";
				WCF::getDB()->registerShutdownUpdate($sql);
			}
		}
	}
	
	/**
	 * Unmarks all marked threads.
	 */
	public static function unmarkAll() {
		WCF::getSession()->unregister('markedThreads');
	}
	
	/**
	 * Returns the currently marked threads. 
	 */
	public static function getMarkedThreads() {
		$sessionVars = WCF::getSession()->getVars();
		if (isset($sessionVars['markedThreads'])) {
			return $sessionVars['markedThreads'];
		}
		return null;
	}
	
	/**
	 * Deletes the threads with the given thread ids.
	 */
	public static function deleteAll($threadIDs, $deletePosts = true, $reason = '') {
		if (empty($threadIDs)) return;
		
		$trashIDs = '';
		$deleteIDs = '';
		if (THREAD_ENABLE_RECYCLE_BIN) {
			// recylce bin enabled
			// first of all we check which threads are already in recylce bin
			$sql = "SELECT 	threadID, isDeleted
				FROM 	wbb".WBB_N."_thread
				WHERE 	threadID IN (".$threadIDs.")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if ($row['isDeleted']) {
					// thread in recylce bin
					// delete completely
					if (!empty($deleteIDs)) $deleteIDs .= ',';
					$deleteIDs .= $row['threadID'];
				}
				else {
					// move thread to recylce bin
					if (!empty($trashIDs)) $trashIDs .= ',';
					$trashIDs .= $row['threadID'];
				}
			}
		}
		else {
			// no recylce bin
			// delete all threads completely
			$deleteIDs = $threadIDs;
		}
		
		self::trashAll($trashIDs, $deletePosts, $reason);
		self::deleteAllCompletely($deleteIDs, $deletePosts);
	}
	
	/**
	 * Moves the threads with the given thread ids into the recycle bin.
	 */
	public static function trashAll($threadIDs, $trashPosts = true, $reason = '') {
		if (empty($threadIDs)) return;
		
		// trash thread
		$sql = "UPDATE 	wbb".WBB_N."_thread
			SET	isDeleted = 1,
				deleteTime = ".TIME_NOW.",
				deletedBy = '".escapeString(WCF::getUser()->username)."',
				deletedByID = ".WCF::getUser()->userID.",
				deleteReason = '".escapeString($reason)."',
				isDisabled = 0
			WHERE 	threadID IN (".$threadIDs.")";
		WCF::getDB()->sendQuery($sql);
		
		// trash post	
		if ($trashPosts) {
			PostEditor::trashAll(self::getAllPostIDs($threadIDs), $reason);
		}
	}
	
	/**
	 * Deletes the threads with the given thread ids completely.
	 */
	public static function deleteAllCompletely($threadIDs, $deletePosts = true, $updateUserStats = true) {
		if (empty($threadIDs)) return;
		
		// update user posts & activity points
		if ($updateUserStats) {
			self::updateUserStats($threadIDs, 'delete');
		}
		
		// delete posts
		if ($deletePosts) {
			PostEditor::deleteAllCompletely(self::getAllPostIDs($threadIDs), true, true, $updateUserStats);
		}
		
		// delete threads
		self::deleteData($threadIDs);
	}
	
	/**
	 * Deletes the sql data of the threads with the given thread ids.
	 */
	protected static function deleteData($threadIDs) {
		// delete thread
		$sql = "DELETE FROM	wbb".WBB_N."_thread
			WHERE 		threadID IN (".$threadIDs.")
					OR movedThreadID IN (".$threadIDs.")";
		WCF::getDB()->sendQuery($sql);
		
		// delete ratingd
		$sql = "DELETE FROM	wbb".WBB_N."_thread_rating
			WHERE 		threadID IN (".$threadIDs.")";
		WCF::getDB()->registerShutdownUpdate($sql);
		
		// delete subscriptions
		$sql = "DELETE FROM 	wbb".WBB_N."_thread_subscription
			WHERE 		threadID IN (".$threadIDs.")";
		WCF::getDB()->registerShutdownUpdate($sql);
		
		// delete thread visits
		$sql = "DELETE FROM	wbb".WBB_N."_thread_visit
			WHERE 		threadID IN (".$threadIDs.")";
		WCF::getDB()->registerShutdownUpdate($sql);
		
		// delete announcements
		$sql = "DELETE FROM	wbb".WBB_N."_thread_announcement
			WHERE 		threadID IN (".$threadIDs.")";
		WCF::getDB()->registerShutdownUpdate($sql);
		
		// delete similar threads
		$sql = "DELETE FROM	wbb".WBB_N."_thread_similar
			WHERE 		threadID IN (".$threadIDs.")
					OR similarThreadID IN (".$threadIDs.")";
		WCF::getDB()->registerShutdownUpdate($sql);
		
		// delete tags
		if (MODULE_TAGGING) {
			require_once(WCF_DIR.'lib/data/tag/TagEngine.class.php');
			$taggable = TagEngine::getInstance()->getTaggable('com.woltlab.wbb.thread');
			
			$sql = "DELETE FROM	wcf".WCF_N."_tag_to_object
				WHERE 		taggableID = ".$taggable->getTaggableID()."
						AND objectID IN (".$threadIDs.")";
			WCF::getDB()->registerShutdownUpdate($sql);
		}
	}
	
	/**
	 * Restores the threads with the given thread ids.
	 */
	public static function restoreAll($threadIDs, $restorePosts = true) {
		if (empty($threadIDs)) return;
		
		// restore thread
		$sql = "UPDATE 	wbb".WBB_N."_thread
			SET	isDeleted = 0
			WHERE 	threadID IN (".$threadIDs.")";
		WCF::getDB()->sendQuery($sql);
		
		// restore post	
		if ($restorePosts) {
			PostEditor::restoreAll(self::getAllPostIDs($threadIDs));
		}
	}
	
	/**
	 * Returns the ids of the posts with the given thread ids.
	 */
	public static function getAllPostIDs($threadIDs) {
		if (empty($threadIDs)) return;
		
		$postIDs = '';
		$sql = "SELECT	postID
			FROM 	wbb".WBB_N."_post
			WHERE 	threadID IN (".$threadIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($postIDs)) $postIDs .= ',';
			$postIDs .= $row['postID'];
		}
		
		return $postIDs;
	}
	
	/**
	 * Returns the boards of the threads with the given thread ids.
	 * 
	 * @param	string		$threadIDs
	 * @return	array
	 */
	public static function getBoards($threadIDs) {
		if (empty($threadIDs)) return array(array(), '', 'boards' => array(), 'boardIDs' => '');
		
		$boards = array();
		$boardIDs = '';
		$sql = "SELECT 	DISTINCT boardID
			FROM 	wbb".WBB_N."_thread
			WHERE 	threadID IN (".$threadIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($boardIDs)) $boardIDs .= ',';
			$boardIDs .= $row['boardID'];
			$boards[$row['boardID']] = new BoardEditor($row['boardID']);
		}
		
		return array($boards, $boardIDs, 'boards' => $boards, 'boardIDs' => $boardIDs);
	}
	
	/**
	 * Moves all threads with the given ids into the board with the given board id.
	 */
	public static function moveAll($threadIDs, $newBoardID) {
		if (empty($threadIDs)) return;
		
		// remove thread links
		$sql = "DELETE FROM	wbb".WBB_N."_thread
			WHERE		boardID = ".$newBoardID."
					AND movedThreadID IN (".$threadIDs.")";
		WCF::getDB()->sendQuery($sql);
		
		// update user posts & activity points (threads)
		self::updateUserStats($threadIDs, 'move', $newBoardID);
		
		// get post ids
		$postIDs = '';
		$sql = "SELECT	postID
			FROM	wbb".WBB_N."_post
			WHERE	threadID IN (".$threadIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($postIDs)) $postIDs .= ',';
			$postIDs .= $row['postID'];
		}
		
		// update user posts & activity points (posts)
		PostEditor::updateUserStats($postIDs, 'move', $newBoardID);
		
		// move threads
		$sql = "UPDATE 	wbb".WBB_N."_thread
			SET	boardID = ".$newBoardID."
			WHERE 	threadID IN (".$threadIDs.")
				AND boardID <> ".$newBoardID;
		WCF::getDB()->sendQuery($sql);
		
		// check prefixes
		self::checkPrefixes($threadIDs, $newBoardID);
	}
	
	/**
	 * Creates a link for all threads with the given ids.
	 */
	public static function createLinks($threadIDs, $boardID) {
		if (empty($threadIDs)) return;
		
		$sql = "SELECT	*
			FROM 	wbb".WBB_N."_thread
			WHERE 	threadID IN (".$threadIDs.")
				AND boardID <> ".$boardID;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$thread = new ThreadEditor(null, $row);
			$thread->createLink();
		}
	}
	
	/**
	 * Copies all SQL data of the threads with the given thread ids. 
	 */
	public static function copyAll($threadIDs, $boardID) {
		if (empty($threadIDs)) return;
		
		// copy 'thread' data
		$mapping = array();
		$sql = "SELECT	*
			FROM 	wbb".WBB_N."_thread
			WHERE 	threadID IN (".$threadIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$thread = new ThreadEditor(null, $row);
			$mapping[$thread->threadID] = $thread->copy($boardID);
		}
		
		// copy 'thread_announcement' data
		$sql = "SELECT	*
			FROM 	wbb".WBB_N."_thread_announcement
			WHERE 	threadID IN (".$threadIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$sql = "INSERT INTO	wbb".WBB_N."_thread_announcement
						(boardID, threadID)
				VALUES 		(".$row['boardID'].", ".$mapping[$row['threadID']].")";
			WCF::getDB()->registerShutdownUpdate($sql);
		}
		
		// copy 'thread_rating' data
		$sql = "SELECT	*
			FROM 	wbb".WBB_N."_thread_rating
			WHERE 	threadID IN (".$threadIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$sql = "INSERT INTO	wbb".WBB_N."_thread_rating
						(threadID, rating, userID, ipAddress)
				VALUES		(".$mapping[$row['threadID']].", ".$row['rating'].",
						".$row['userID'].", '".escapeString($row['ipAddress'])."')";
			WCF::getDB()->registerShutdownUpdate($sql);
		}
		
		// copy 'thread_subscription' data
		$sql = "SELECT	*
			FROM 	wbb".WBB_N."_thread_subscription
			WHERE 	threadID IN (".$threadIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$sql = "INSERT INTO 	wbb".WBB_N."_thread_subscription
						(userID, threadID, enableNotification, emails)
				VALUES		(".$row['userID'].", ".$mapping[$row['threadID']].",
						".$row['enableNotification'].", ".$row['emails'].")";
			WCF::getDB()->registerShutdownUpdate($sql);
		}
		
		// copy 'thread_visit' data
		$sql = "SELECT 	*
			FROM 	wbb".WBB_N."_thread_visit
			WHERE 	threadID IN (".$threadIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$sql = "INSERT INTO 	wbb".WBB_N."_thread_visit
						(threadID, userID, lastVisitTime)
				VALUES		(".$mapping[$row['threadID']].", ".$row['userID'].", ".$row['lastVisitTime'].")";
			WCF::getDB()->registerShutdownUpdate($sql);
		}
		
		// update user posts & activity points
		self::updateUserStats($threadIDs, 'copy', $boardID);
		
		// copy posts (and polls, attachments)
		PostEditor::copyAll(self::getAllPostIds($threadIDs), null, $mapping, $boardID);
		
		// check prefixes
		self::checkPrefixes(implode(',', $mapping), $boardID);
	}
	
	/**
	 * Checks whether the threads with the given thread ids are empty, thrashed or hidden. 
	 */
	public static function checkVisibilityAll($threadIDs, $reason = '') {
		if (empty($threadIDs)) return;
		
		$emptyThreads = '';
		$trashedThreads = '';
		$hiddenThreads = '';
		$enabledThreads = '';
		$restoresThreads = '';
		$sql = "SELECT		COUNT(post.postID) AS posts,
					SUM(post.isDeleted) AS deletedPosts,
					SUM(post.isDisabled) AS hiddenPosts,
					thread.threadID, thread.isDeleted, thread.isDisabled
			FROM 		wbb".WBB_N."_thread thread
			LEFT JOIN 	wbb".WBB_N."_post post
			ON 		(post.threadID = thread.threadID)
			WHERE 		thread.threadID IN (".$threadIDs.")
			GROUP BY 	thread.threadID";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$row['deletedPosts'] = intval($row['deletedPosts']);
			$row['hiddenPosts'] = intval($row['hiddenPosts']);
			
			// thread has no posts
			// delete thread
			if ($row['posts'] == 0) {
				if (!empty($emptyThreads)) $emptyThreads .= ',';
				$emptyThreads .= $row['threadID'];
			}
			
			// all posts of this thread are into the recylce bin
			// move thread also into the recylce bin  
			else if ($row['posts'] == $row['deletedPosts']) {
				if (!empty($trashedThreads)) $trashedThreads .= ',';
				$trashedThreads .= $row['threadID'];
			}
			
			// all posts of this thread are hidden
			// hide thread also
			else if ($row['posts'] == $row['hiddenPosts'] || $row['posts'] == $row['hiddenPosts'] + $row['deletedPosts']) {
				if (!empty($hiddenThreads)) $hiddenThreads .= ',';
				$hiddenThreads .= $row['threadID'];
			}
			
			// thread is deleted, but no posts are deleted
			// restore thread
			else if (intval($row['deletedPosts']) == 0 && $row['isDeleted'] == 1) {
				if (!empty($restoresThreads)) $restoresThreads .= ',';
				$restoresThreads .= $row['threadID'];
			}
			
			// thread is hidden, but no posts are hidden
			// enable thread
			else if (intval($row['hiddenPosts']) == 0 && $row['isDisabled'] == 1) {
				if (!empty($enabledThreads)) $enabledThreads .= ',';
				$enabledThreads .= $row['threadID'];
			}
		}
		
		self::deleteAllCompletely($emptyThreads, false, false);
		self::trashAll($trashedThreads, false, $reason);
		self::disableAll($hiddenThreads, false);
		self::restoreAll($restoresThreads, false);
		self::enableAll($enabledThreads, false);
	}
	
	/**
	 * Disables the threads with the given thread ids.
	 */
	public static function disableAll($threadIDs, $disablePosts = true) {
		if (empty($threadIDs)) return;
		
		// disable thread
		$sql = "UPDATE 	wbb".WBB_N."_thread
			SET	isDeleted = 0,
				isDisabled = 1
			WHERE 	threadID IN (".$threadIDs.")";
		WCF::getDB()->sendQuery($sql);
		
		// disable post	
		if ($disablePosts) {
			PostEditor::disableAll(self::getAllPostIDs($threadIDs));
		}
	}
	
	/**
	 * Enables the threads with the given thread ids.
	 */
	public static function enableAll($threadIDs, $enablePosts = true) {
		if (empty($threadIDs)) return;
		
		// send notifications
		$statThreadIDs = '';
		$sql = "SELECT	*
			FROM	wbb".WBB_N."_thread
			WHERE	threadID IN (".$threadIDs.")
				AND isDisabled = 1
				AND everEnabled = 0";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($statThreadIDs)) $statThreadIDs .= ',';
			$statThreadIDs .= $row['threadID'];
			
			// send notifications
			$thread = new ThreadEditor(null, $row);
			$thread->sendNotification();
		}
		
		// update user posts & activity points
		self::updateUserStats($statThreadIDs, 'enable');
		
		// enable thread
		$sql = "UPDATE 	wbb".WBB_N."_thread
			SET	isDisabled = 0,
				everEnabled = 1
			WHERE 	threadID IN (".$threadIDs.")";
		WCF::getDB()->registerShutdownUpdate($sql);
		
		// enable post	
		if ($enablePosts) {
			PostEditor::enableAll(self::getAllPostIDs($threadIDs));
		}
	}
	
	/**
	 * Refreshes the last post, replies, amount of attachments and amount of polls of this thread.
	 */
	public static function refreshAll($threadIDs, $refreshLastPost = true, $refreshFirstPostID = true) {
		if (empty($threadIDs)) return;
		
		$sql = "UPDATE 	wbb".WBB_N."_thread thread
			SET	replies = IF(thread.isDeleted = 0 AND thread.isDisabled = 0,
					(
						SELECT 	COUNT(*)
						FROM 	wbb".WBB_N."_post
						WHERE 	threadID = thread.threadID
							AND isDeleted = 0
							AND isDisabled = 0
					) - 1, replies),
				attachments = IFNULL((
					SELECT 	SUM(attachments)
					FROM 	wbb".WBB_N."_post
					WHERE 	threadID = thread.threadID
						AND isDeleted = 0
						AND isDisabled = 0
				), 0),
				polls = (
					SELECT 	COUNT(*)
					FROM 	wbb".WBB_N."_post
					WHERE 	threadID = thread.threadID
						AND isDeleted = 0
						AND isDisabled = 0
						AND pollID <> 0
				)
			WHERE 	threadID IN (".$threadIDs.")";
		WCF::getDB()->sendQuery($sql);
		
		if ($refreshLastPost) {
			self::setLastPostAll($threadIDs);
		}
		
		if ($refreshFirstPostID) {
			self::refreshFirstPostIDAll($threadIDs);
		}
	}
	
	/**
	 * Sets the last post of the threads with the given thread ids.
	 */
	public static function setLastPostAll($threadIDs) {
		if (empty($threadIDs)) return;
		
		$threads = explode(',', $threadIDs);
		foreach ($threads as $threadID) {
			self::__setLastPost($threadID);
		}
	}
	
	/**
	 * Sets the last post of the thread with the given thread id.
	 */
	protected static function __setLastPost($threadID, $post = null) {
		if ($post != null) {
			$result = array('time' => $post->time, 'userID' => $post->userID, 'username' => $post->username);
		}
		else {
			$sql = "SELECT		time, userID, username
				FROM 		wbb".WBB_N."_post
				WHERE 		threadID = ".$threadID."
						AND isDeleted = 0
						AND isDisabled = 0
				ORDER BY 	time DESC";
			$result = WCF::getDB()->getFirstRow($sql);
		}
		
		if ($result['time']) {
			$sql = "UPDATE 	wbb".WBB_N."_thread
				SET	lastPostTime = ".intval($result['time']).",
					lastPosterID = ".intval($result['userID']).",
					lastPoster = '".escapeString($result['username'])."'
				WHERE 	threadID = ".$threadID;
		}
		else {
			$sql = "UPDATE 	wbb".WBB_N."_thread
				SET	lastPostTime = time,
					lastPosterID = userID,
					lastPoster = username
				WHERE 	threadID = ".$threadID;
		}
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Creates a new thread.
	 */
	public static function createFromPosts($postIDs, $boardID) {
		// get post
		$sql = "SELECT 		post.*, thread.languageID
			FROM 		wbb".WBB_N."_post post
			LEFT JOIN	wbb".WBB_N."_thread thread
			ON		(thread.threadID = post.threadID)
			WHERE 		post.postID IN (".$postIDs.")
			ORDER BY 	post.time ASC";
		$row = WCF::getDB()->getFirstRow($sql);
		$post = new Post(null, $row);
		
		$sql = "INSERT INTO 	wbb".WBB_N."_thread
					(boardID, topic, firstPostID, time, userID, username, languageID)
			VALUES		(".$boardID.",
					'".escapeString($post->subject ? $post->subject : substr($post->message, 0, 255))."',
					".$post->postID.",
					".$post->time.",
					".$post->userID.",
					'".escapeString($post->username)."',
					".intval($row['languageID']).")";
		WCF::getDB()->sendQuery($sql);
		$threadID = WCF::getDB()->getInsertID();
		
		// update user posts & activity points
		self::updateUserStats($threadID, 'copy', $boardID);
		
		// update first post preview
		PostEditor::updateFirstPostPreview($threadID, $post->postID, $post->message, array('enableSmilies' => $post->enableSmilies, 'enableHtml' => $post->enableHtml, 'enableBBCodes' => $post->enableBBCodes));
		
		return new ThreadEditor($threadID);	
	}
	
	/**
	 * Sticks this thread.
	 */
	public function stick() {
		$sql = "UPDATE	wbb".WBB_N."_thread
			SET 	isSticky = 1,
				isAnnouncement = 0
			WHERE	threadID = ".$this->threadID."
				AND isSticky = 0";
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Unsticks this thread.
	 */
	public function unstick() {
		$sql = "UPDATE	wbb".WBB_N."_thread
			SET 	isSticky = 0
			WHERE	threadID = ".$this->threadID."
				AND isSticky = 1";
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Sets the status of this thread.
	 */
	public function setStatus($sticky, $announcement) {
		$sql = "UPDATE	wbb".WBB_N."_thread
			SET 	isSticky = ".$sticky.",
				isAnnouncement = ".$announcement."
			WHERE	threadID = ".$this->threadID;
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Updates thread data.
	 */
	public function update($threadData) {
		$updateSql = '';
		foreach ($threadData as $key => $value) {
			if (!empty($updateSql)) $updateSql .= ',';
			$updateSql .= $key."='".escapeString($value)."'";
		}
		
		if (!empty($updateSql)) {
			$sql = "UPDATE 	wbb".WBB_N."_thread
				SET	".$updateSql."
				WHERE	threadID = ".$this->threadID;
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * Sends the email notification.
	 */
	public function sendNotification($post = null, $attachmentList = null) {
		$sql = "SELECT		user.*
			FROM		wbb".WBB_N."_board_subscription subscription
			LEFT JOIN	wcf".WCF_N."_user user
			ON		(user.userID = subscription.userID)
			WHERE		subscription.boardID = ".$this->boardID."
					AND subscription.enableNotification = 1
					AND subscription.emails = 0
					AND subscription.userID <> ".$this->userID."
					AND user.userID IS NOT NULL";
		$result = WCF::getDB()->sendQuery($sql);
		if (WCF::getDB()->countRows($result)) {
			// get first post
			if ($post === null) {
				require_once(WBB_DIR.'lib/data/post/Post.class.php');
				$post = new Post($this->firstPostID);
			}
			
			// get attachments
			if ($attachmentList === null) {
				require_once(WCF_DIR.'lib/data/attachment/MessageAttachmentList.class.php');
				$attachmentList = new MessageAttachmentList($this->firstPostID);
				$attachmentList->readObjects();
			}
			
			// set attachments
			require_once(WCF_DIR.'lib/data/message/bbcode/AttachmentBBCode.class.php');
			AttachmentBBCode::setAttachments($attachmentList->getSortedAttachments());
			
			// parse text
			require_once(WCF_DIR.'lib/data/message/bbcode/MessageParser.class.php');
			$parser = MessageParser::getInstance();
			$parser->setOutputType('text/plain');
			$parsedText = $parser->parse($post->message, $post->enableSmilies, $post->enableHtml, $post->enableBBCodes, false);
			// truncate message
			if (!POST_NOTIFICATION_SEND_FULL_MESSAGE && StringUtil::length($parsedText) > 500) $parsedText = StringUtil::substring($parsedText, 0, 500) . '...';
			
			// send notifications
			$languages = array();
			$languages[WCF::getLanguage()->getLanguageID()] = WCF::getLanguage();
			$languages[0] = WCF::getLanguage();
			require_once(WCF_DIR.'lib/data/mail/Mail.class.php');
			require_once(WCF_DIR.'lib/data/user/User.class.php');
			require_once(WBB_DIR.'lib/data/board/Board.class.php');
			$board = Board::getBoard($this->boardID);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$recipient = new User(null, $row);
				
				// get language
				if (!isset($languages[$recipient->languageID])) {
					$languages[$recipient->languageID] = new Language($recipient->languageID);	
				}
				
				// enable language
				$languages[$recipient->languageID]->setLocale();
				
				// send mail
				$data = array(
					'PAGE_TITLE' => $languages[$recipient->languageID]->get(PAGE_TITLE),
					'PAGE_URL' => PAGE_URL,
					'$recipient' => $recipient->username,
					'$author' => $this->username,
					'$boardTitle' => $languages[$recipient->languageID]->get($board->title),
					'$topic' => $this->topic,
					'$threadID' => $this->threadID,
					'$text' => $parsedText);
				$mail = new Mail(	array($recipient->username => $recipient->email),
							$languages[$recipient->languageID]->get('wbb.threadAdd.notification.subject', array('$title' => $languages[$recipient->languageID]->get($board->title))),
							$languages[$recipient->languageID]->get('wbb.threadAdd.notification.mail', $data));
				$mail->send();
			}
			
			// enable user language
			WCF::getLanguage()->setLocale();
			
			// update notification count
			$sql = "UPDATE	wbb".WBB_N."_board_subscription
				SET 	emails = emails + 1
				WHERE	boardID = ".$this->boardID."
					AND enableNotification = 1
					AND emails = 0";
			WCF::getDB()->registerShutdownUpdate($sql);
		}
	}
	
	/**
	 * Refreshes the first post ids of given threads.
	 * 
	 * @param	string		$threadIDs
	 */
	public static function refreshFirstPostIDAll($threadIDs) {
		$threadIDsArray = explode(',', $threadIDs);
		foreach ($threadIDsArray as $threadID) {
			// get post
			$sql = "SELECT 		postID, threadID, time, userID, username
				FROM 		wbb".WBB_N."_post post
				WHERE 		threadID = ".$threadID."
				ORDER BY 	time ASC";
			$row = WCF::getDB()->getFirstRow($sql);
			if (!empty($row['postID'])) {
				$sql = "UPDATE	wbb".WBB_N."_thread
					SET	firstPostID = ".$row['postID'].",
						time = ".$row['time'].",
						userID = ".$row['userID'].",
						username = '".escapeString($row['username'])."'
					WHERE	threadID = ".$threadID;
				WCF::getDB()->sendQuery($sql);
			}
		}
	}
	
	/**
	 * Checks if prefixes of given threads match available prefixes.
	 * Removes unavailable prefixes.
	 * 
	 * @param	string		$threadIDs
	 * @param 	string		$boardID
	 * @return 	integer		affected rows
	 */
	public static function checkPrefixes($threadIDs, $boardID) {
		if (empty($threadIDs)) return;
		
		// get board
		$board = Board::getBoard($boardID);
		
		// get valid prefixes
		$prefixes = implode("','", array_map('escapeString', $board->getPrefixes()));
		
		// update threads
		$sql = "UPDATE	wbb".WBB_N."_thread
			SET	prefix = ''
			WHERE	threadID IN (".$threadIDs.")
				".($prefixes ? "AND prefix NOT IN ('".$prefixes."')" : '');
		WCF::getDB()->sendQuery($sql);
		return WCF::getDB()->getAffectedRows();
	}
	
	/**
	 * Updates the user stats (user posts, activity points & user rank).
	 * 
	 * @param	string		$threadIDs		changed threads
	 * @param 	string		$mode			(enable|copy|move|delete)
	 * @param 	integer		$destinationBoardID
	 */
	public static function updateUserStats($threadIDs, $mode, $destinationBoardID = 0) {
		if (empty($threadIDs)) return;
		
		// get destination board
		$destinationBoard = null;
		if ($destinationBoardID) $destinationBoard = Board::getBoard($destinationBoardID);
		if ($mode == 'copy' && !$destinationBoard->countUserPosts) return;
		
		// update user posts, activity points
		$userPosts = array();
		$userActivityPoints = array();
		$sql = "SELECT	boardID, userID
			FROM	wbb".WBB_N."_thread
			WHERE	threadID IN (".$threadIDs.")
				".($mode != 'enable' ? "AND everEnabled = 1" : '')."
				AND userID <> 0";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$board = Board::getBoard($row['boardID']);
			
			switch ($mode) {
				case 'enable':
					if ($board->countUserPosts) {
						// posts
						if (!isset($userPosts[$row['userID']])) $userPosts[$row['userID']] = 0;
						$userPosts[$row['userID']]++;
						// activity points
						if (!isset($userActivityPoints[$row['userID']])) $userActivityPoints[$row['userID']] = 0;
						$userActivityPoints[$row['userID']] += ACTIVITY_POINTS_PER_THREAD;
					}
					break;
				case 'copy':
					if ($destinationBoard->countUserPosts) {
						// posts
						if (!isset($userPosts[$row['userID']])) $userPosts[$row['userID']] = 0;
						$userPosts[$row['userID']]++;
						// activity points
						if (!isset($userActivityPoints[$row['userID']])) $userActivityPoints[$row['userID']] = 0;
						$userActivityPoints[$row['userID']] += ACTIVITY_POINTS_PER_THREAD;
					}
					break;
				case 'move':
					if ($board->countUserPosts != $destinationBoard->countUserPosts) {
						// posts
						if (!isset($userPosts[$row['userID']])) $userPosts[$row['userID']] = 0;
						$userPosts[$row['userID']] += ($board->countUserPosts ? -1 : 1);
						// activity points
						if (!isset($userActivityPoints[$row['userID']])) $userActivityPoints[$row['userID']] = 0;
						$userActivityPoints[$row['userID']] += ($board->countUserPosts ? ACTIVITY_POINTS_PER_THREAD * -1 : ACTIVITY_POINTS_PER_THREAD);
					}
					break;
				case 'delete':
					if ($board->countUserPosts) {
						// posts
						if (!isset($userPosts[$row['userID']])) $userPosts[$row['userID']] = 0;
						$userPosts[$row['userID']]--;
						// activity points
						if (!isset($userActivityPoints[$row['userID']])) $userActivityPoints[$row['userID']] = 0;
						$userActivityPoints[$row['userID']] -= ACTIVITY_POINTS_PER_THREAD;
					}
					break;
			}
		}
		
		// save posts
		if (count($userPosts)) {
			require_once(WBB_DIR.'lib/data/user/WBBUser.class.php');
			foreach ($userPosts as $userID => $posts) {
				WBBUser::updateUserPosts($userID, $posts);
			}
		}
		
		// save activity points
		if (count($userActivityPoints)) {
			require_once(WCF_DIR.'lib/data/user/rank/UserRank.class.php');
			foreach ($userActivityPoints as $userID => $points) {
				UserRank::updateActivityPoints($points, $userID);
			}
		}
	}
	
	/**
	 * Updates the tags of this thread.
	 * 
	 * @param	array		$tags
	 */
	public function updateTags($tagArray) {
		// include files
		require_once(WCF_DIR.'lib/data/tag/TagEngine.class.php');
		require_once(WBB_DIR.'lib/data/thread/TaggedThread.class.php');
		
		// save tags
		$tagged = new TaggedThread(null, array(
			'threadID' => $this->threadID,
			'taggable' => TagEngine::getInstance()->getTaggable('com.woltlab.wbb.thread')
		));

		// delete old tags
		TagEngine::getInstance()->deleteObjectTags($tagged, array($this->languageID));
		
		// save new tags
		if (count($tagArray) > 0) TagEngine::getInstance()->addTags($tagArray, $tagged, $this->languageID);
	}
	
	/**
	 * Marks this thread as done.
	 */
	public function markAsDone() {
		$sql = "UPDATE	wbb".WBB_N."_thread
			SET	isDone = 1
			WHERE	threadID = ".$this->threadID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Marks this thread as undone.
	 */
	public function markAsUndone() {
		$sql = "UPDATE	wbb".WBB_N."_thread
			SET	isDone = 0
			WHERE	threadID = ".$this->threadID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>