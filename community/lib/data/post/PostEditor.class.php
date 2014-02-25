<?php
// wbb imports
require_once(WBB_DIR.'lib/data/post/Post.class.php');

/**
 * PostEditor provides functions to create and edit the data of a post.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.post
 * @category 	Burning Board
 */
class PostEditor extends Post {
	/**
	 * Updates the data of this post.
	 * 
	 * @param	string				$subject		new subject of this post
	 * @param	string				$message		new text of this post
	 * @param	array				$options		new options of this post
	 * @param	AttachmentsEditor		$attachments		
	 * @param	PollEditor			$poll
	 */
	public function update($subject, $message, $options, $attachments = null, $poll = null, $additionalData = array()) {
		$updateSubject = $updateText = '';
		$attachmentsAmount = $attachments != null ? count($attachments->getAttachments($this->postID)) : 0;
		
		// save subject
		if ($subject != $this->subject) {
			$updateSubject = "subject = '".escapeString($subject)."',";
		}
		
		// save message
		$updateText = "message = '".escapeString($message)."',";
		
		// assign attachments
		if ($attachments != null) {
			require_once(WCF_DIR.'lib/data/message/bbcode/AttachmentBBCode.class.php');
			AttachmentBBCode::setAttachments($attachments->getSortedAttachments());
		}
		
		// update post cache
		require_once(WCF_DIR.'lib/data/message/bbcode/MessageParser.class.php');
		$parser = MessageParser::getInstance();
		$parser->setOutputType('text/html');
		$sql = "UPDATE	wbb".WBB_N."_post_cache
			SET	messageCache = '".escapeString($parser->parse($message, $options['enableSmilies'], $options['enableHtml'], $options['enableBBCodes'], false))."'
			WHERE	postID = ".$this->postID;
		WCF::getDB()->registerShutdownUpdate($sql);
		
		$additionalSql = '';
		foreach ($additionalData as $key => $value) {
			$additionalSql .= ','.$key."='".escapeString($value)."'";
		}
		
		// save post in database
		$sql = "UPDATE 	wbb".WBB_N."_post
			SET	$updateSubject
				$updateText
				attachments = ".$attachmentsAmount.",
				".($poll != null ? "pollID = ".intval($poll->pollID)."," : '')."
				enableSmilies = ".$options['enableSmilies'].",
				enableHtml = ".$options['enableHtml'].",
				enableBBCodes = ".$options['enableBBCodes'].",
				showSignature = ".$options['showSignature']."
				".$additionalSql."
			WHERE 	postID = ".$this->postID;
		WCF::getDB()->sendQuery($sql);
		
		// update attachments
		if ($attachments != null) {
			$attachments->findEmbeddedAttachments($message);
		}
		// update poll
		if ($poll != null) {
			$poll->updateMessageID($this->postID);
		}
		
		// update first post preview
		$this->updateFirstPostPreview($this->threadID, $this->postID, $message, $options);
		
		// refresh thread data
		require_once(WBB_DIR.'lib/data/thread/ThreadEditor.class.php');
		ThreadEditor::refreshAll($this->threadID, false);
	}
	
	/**
	 * Updates the message of this post.
	 * 
	 * @param	string				$message		new text of this post
	 */
	public function updateMessage($message, $additionalData = array()) {
		$additionalSql = '';
		foreach ($additionalData as $key => $value) {
			$additionalSql .= ','.$key."='".escapeString($value)."'";
		}

		// save post in database
		$sql = "UPDATE 	wbb".WBB_N."_post
			SET	message = '".escapeString($message)."'
				".$additionalSql."
			WHERE 	postID = ".$this->postID;
		WCF::getDB()->sendQuery($sql);
		
		// update post cache
		if ($this->attachments) {
			// get attachments
			require_once(WCF_DIR.'lib/data/attachment/MessageAttachmentList.class.php');
			$attachmentList = new MessageAttachmentList(array($this->postID));
			$attachmentList->readObjects();
			// assign attachments
			require_once(WCF_DIR.'lib/data/message/bbcode/AttachmentBBCode.class.php');
			AttachmentBBCode::setAttachments($attachmentList->getSortedAttachments());
		}

		// update post cache
		require_once(WCF_DIR.'lib/data/message/bbcode/MessageParser.class.php');
		MessageParser::getInstance()->setOutputType('text/html');
		$sql = "UPDATE	wbb".WBB_N."_post_cache
			SET	messageCache = '".escapeString(MessageParser::getInstance()->parse($message, $this->enableSmilies, $this->enableHtml, $this->enableBBCodes, false))."'
			WHERE	postID = ".$this->postID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Updates the first post preview.
	 * 
	 * @param	integer		$threadID
	 * @param	integer		$postID
	 * @param	string		$message
	 * @param	array		$options
	 */
	public static function updateFirstPostPreview($threadID, $postID, $message, $options) {
		if (!BOARD_THREADS_ENABLE_MESSAGE_PREVIEW) {
			return;
		}
		
		require_once(WCF_DIR.'lib/data/message/bbcode/MessageParser.class.php');
		$parser = MessageParser::getInstance();
		$parser->setOutputType('text/plain');
		$message = StringUtil::stripHTML($message);
		$parsedMessage = $parser->parse($message, $options['enableSmilies'], $options['enableHtml'], $options['enableBBCodes'], false);
		
		if (StringUtil::length($parsedMessage) > 500) {
			$parsedMessage = StringUtil::substring($parsedMessage, 0, 497) . '...';
		}
			
		$sql = "UPDATE	wbb".WBB_N."_thread
			SET	firstPostPreview = '".escapeString($parsedMessage)."'
			WHERE	threadID = ".$threadID."
				AND firstPostID = ".$postID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Sets the subject of this message.
	 * 
	 * @param	string		$subject	new subject for this message
	 */
	public function setSubject($subject) {
		$sql = "UPDATE 	wbb".WBB_N."_post SET
				subject = '".escapeString($subject)."'
			WHERE 	postID = ".$this->postID;
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Marks this post.
	 */
	public function mark() {
		$markedPosts = self::getMarkedPosts();
		if ($markedPosts == null || !is_array($markedPosts)) { 
			$markedPosts = array($this->postID);
			WCF::getSession()->register('markedPosts', $markedPosts);
		}
		else {
			if (!in_array($this->postID, $markedPosts)) {
				array_push($markedPosts, $this->postID);
				WCF::getSession()->register('markedPosts', $markedPosts);
			}
		}
	}
	
	/**
	 * Unmarks this post.
	 */
	public function unmark() {
		$markedPosts = self::getMarkedPosts();
		if (is_array($markedPosts) && in_array($this->postID, $markedPosts)) {
			$key = array_search($this->postID, $markedPosts);
			
			unset($markedPosts[$key]);
			if (count($markedPosts) == 0) {
				self::unmarkAll();
			}
			else {
				WCF::getSession()->register('markedPosts', $markedPosts);
			}
		}
	}
	
	/**
	 * Moves this post in the recycle bin.
	 */
	public function trash($reason = '') {
		self::trashAll($this->postID, $reason);
	}
	
	/**
	 * Deletes this post completely.
	 * 
	 * Deletes the attachments and the poll of this post and
	 * the sql data in tables post, post_cache and post_report.
	 */
	public function delete($updateUserStats = true) {
		self::deleteAllCompletely($this->postID, $this->attachments > 0, $this->pollID != 0, $updateUserStats);
	}
	
	/**
	 * Restores this deleted post.
	 */
	public function restore() {
		self::restoreAll($this->postID);
	}
	
	/**
	 * Disables this post.
	 */
	public function disable() {
		self::disableAll($this->postID);
	}
	
	/**
	 * Enables this post.
	 */
	public function enable() {
		self::enableAll($this->postID);
	}
	
	/**
	 * Closes this post.
	 */
	public function close() {
		$sql = "UPDATE 	wbb".WBB_N."_post
			SET	isClosed = 1
			WHERE 	postID = ".$this->postID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Opens this post.
	 */
	public function open() {
		$sql = "UPDATE 	wbb".WBB_N."_post
			SET	isClosed = 0
			WHERE 	postID = ".$this->postID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Copies the sql data of this post.
	 */
	public function copy($threadID) {
		return self::insert($this->subject, $this->message, $threadID, array(
			'userID' => $this->userID,
			'username' => $this->username,
			'time' => $this->time,
			'isDeleted' => $this->isDeleted,
			'deleteTime' => $this->deleteTime,
			'deletedBy' => $this->deletedBy,
			'deletedByID' => $this->deletedByID,
			'deleteReason' => $this->deleteReason,
			'isDisabled' => $this->isDisabled,
			'everEnabled' => $this->everEnabled,
			'isClosed' => $this->isClosed,
			'editor' => $this->editor,
			'editorID' => $this->editorID,
			'lastEditTime' => $this->lastEditTime,
			'editCount' => $this->editCount,
			'attachments' => $this->attachments,
			'pollID' => $this->pollID,
			'enableSmilies' => $this->enableSmilies,
			'enableHtml' => $this->enableHtml,
			'enableBBCodes' => $this->enableBBCodes,
			'showSignature' => $this->showSignature,
			'ipAddress' => $this->ipAddress
		));
	}
	
	/**
	 * Creates a new post with the given data in the database.
	 * Returns a PostEditor object of the new post.
	 * 
	 * @param	integer				$threadID							
	 * @param	string				$subject		subject of the new post
	 * @param	string				$text			text of the new post
	 * @param	integer				$userID			user id of the author of the new post
	 * @param	string				$username		username of the author of the new post
	 * @param	array				$options		options of the new post
	 * @param	AttachmentsEditor		$attachmentsEditor
	 * @param	PollEditor			$pollEditor
	 * 
	 * @return	PostEditor						the new post		
	 */
	public static function create($threadID, $subject, $message, $userID, $username, $options, $attachments = null, $poll = null, $ipAddress = null, $disabled = 0, $firstPost = false) {
		if ($ipAddress == null) $ipAddress = WCF::getSession()->ipAddress;
		$hash = StringUtil::getHash(($firstPost ? '' : $threadID) . $subject . $message . $userID . $username);
		$attachmentsAmount = $attachments != null ? count($attachments->getAttachments()) : 0;
		
		// insert post
		$postID = self::insert($subject, $message, $threadID, array(
			'userID' => $userID,
			'username' => $username,
			'time' => TIME_NOW,
			'attachments' => $attachmentsAmount,
			'enableSmilies' => $options['enableSmilies'],
			'enableHtml' => $options['enableHtml'],
			'enableBBCodes' => $options['enableBBCodes'],
			'showSignature' => $options['showSignature'],
			'pollID' => ($poll != null ? intval($poll->pollID) : 0),
			'ipAddress' => $ipAddress,
			'isDisabled' => $disabled,
			'everEnabled' => ($disabled ? 0 : 1)
		));
		
		// save hash
		$sql = "INSERT INTO	wbb".WBB_N."_post_hash
					(postID, messageHash, time)
			VALUES		(".$postID.", '".$hash."', ".TIME_NOW.")";
		WCF::getDB()->sendQuery($sql);
		
		// get post
		$post = new PostEditor($postID);
		
		// assign attachments
		if ($attachments != null) {
			require_once(WCF_DIR.'lib/data/message/bbcode/AttachmentBBCode.class.php');
			AttachmentBBCode::setAttachments($attachments->getSortedAttachments());
		}

		// create post cache
		require_once(WCF_DIR.'lib/data/message/bbcode/MessageParser.class.php');
		$parser = MessageParser::getInstance();
		$parser->setOutputType('text/html');
		$sql = "INSERT INTO 	wbb".WBB_N."_post_cache
					(postID, threadID, messageCache)
			VALUES		(".$postID.",
					".$threadID.",
					'".escapeString($parser->parse($post->message, $post->enableSmilies, $post->enableHtml, $post->enableBBCodes, false))."')";
		WCF::getDB()->sendQuery($sql);
		
		// update attachments & poll
		if ($attachments != null) {
			$attachments->updateContainerID($postID);
			$attachments->findEmbeddedAttachments($message);
		}
		if ($poll != null) {
			$poll->updateMessageID($postID);
		}
		
		// save last post
		if (PROFILE_SHOW_LAST_POSTS && $userID != 0) {
			$sql = "INSERT INTO	wbb".WBB_N."_user_last_post
						(userID, postID, time)
				VALUES		(".$userID.", ".$postID.", ".TIME_NOW.")";
			WCF::getDB()->registerShutdownUpdate($sql);
		}
		
		return $post;
	}
	
	/**
	 * Creates the post row in database table.
	 *
	 * @param 	string 		$subject
	 * @param 	string		$message
	 * @param	integer		$threadID
	 * @param 	array		$additionalFields
	 * @return	integer		new post id
	 */
	public static function insert($subject, $message, $threadID, $additionalFields = array()){ 
		$keys = $values = '';
		foreach ($additionalFields as $key => $value) {
			$keys .= ','.$key;
			$values .= ",'".escapeString($value)."'";
		}
		
		$sql = "INSERT INTO	wbb".WBB_N."_post
					(threadID, subject, message
					".$keys.")
			VALUES		(".$threadID.", '".escapeString($subject)."', '".escapeString($message)."'
					".$values.")";
		WCF::getDB()->sendQuery($sql);
		return WCF::getDB()->getInsertID();
	}
	
	/**
	 * Checks whether a post with the given data already exists in the database.
	 * 
	 * @param	string		$subject
	 * @param	string		$message
	 * @param	integer		$authorID
	 * @param	string		$author
	 * @param	integer		$threadID
	 * 
	 * @return	boolean		true, if a post with the given data already exists in the database
	 */
	public static function test($subject, $message, $authorID, $author, $threadID = 0) {
		$hash = StringUtil::getHash(($threadID ? $threadID : '') . $subject . $message . $authorID . $author);
		$sql = "SELECT		postID
			FROM 		wbb".WBB_N."_post_hash
			WHERE 		messageHash = '".$hash."'";
		$row = WCF::getDB()->getFirstRow($sql);
		if (!empty($row['postID'])) return $row['postID'];
		return false;
	}
	
	/**
	 * Creates the preview of a post with the given data.
	 * 
	 * @param	string		$subject
	 * @param	string		$text
	 * 
	 * @return	string		the preview of a post 
	 */
	public static function createPreview($subject, $message, $enableSmilies = 1, $enableHtml = 0, $enableBBCodes = 1) {
		$row = array(
			'postID' => 0,
			'subject' => $subject,
			'message' => $message,
			'enableSmilies' => $enableSmilies,
			'enableHtml' => $enableHtml,
			'enableBBCodes' => $enableBBCodes,
			'messagePreview' => true
		);

		require_once(WBB_DIR.'lib/data/post/ViewablePost.class.php');
		$post = new ViewablePost(null, $row);
		return $post->getFormattedMessage();
	}
	
	/**
	 * Returns the marked posts.
	 * 
	 * @return	array		marked posts
	 */
	public static function getMarkedPosts() {
		$sessionVars = WCF::getSession()->getVars();
		if (isset($sessionVars['markedPosts'])) {
			return $sessionVars['markedPosts'];
		}
		return null;
	}
	
	/**
	 * Unmarks all marked posts.
	 */
	public static function unmarkAll() {
		WCF::getSession()->unregister('markedPosts');
	}
	
	/**
	 * Restores all posts with the given ids.
	 */
	public static function restoreAll($postIDs) {
		$sql = "UPDATE 	wbb".WBB_N."_post
			SET	isDeleted = 0
			WHERE 	postID IN (".$postIDs.")";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Enables all posts with the given ids.
	 */
	public static function enableAll($postIDs) {
		// send notifications
		require_once(WBB_DIR.'lib/data/board/Board.class.php');
		$statPostIDs = '';
		$sql = "SELECT		post.*, thread.boardID
			FROM		wbb".WBB_N."_post post
			LEFT JOIN	wbb".WBB_N."_thread thread
			ON		(thread.threadID = post.threadID)
			WHERE		post.postID IN (".$postIDs.")
					AND post.isDisabled = 1
					AND post.everEnabled = 0
					AND post.postID <> thread.firstPostID";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($statPostIDs)) $statPostIDs .= ',';
			$statPostIDs .= $row['postID'];
			
			// send notifications
			$post = new PostEditor(null, $row);
			$post->sendNotification();
		}
		
		// update user posts & activity points
		self::updateUserStats($statPostIDs, 'enable');
		
		// enable posts
		$sql = "UPDATE 	wbb".WBB_N."_post
			SET	isDisabled = 0,
				everEnabled = 1
			WHERE 	postID IN (".$postIDs.")";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Disables all posts with the given ids.
	 */
	public static function disableAll($postIDs) {
		$sql = "UPDATE 	wbb".WBB_N."_post
			SET	isDisabled = 1,
				isDeleted = 0
			WHERE 	postID IN (".$postIDs.")";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Deletes the posts with the given post ids.
	 */
	public static function deleteAll($postIDs, $updateUserStats = true, $reason = '') {
		if (empty($postIDs)) return;
		
		$trashIDs = '';
		$deleteIDs = '';
		if (THREAD_ENABLE_RECYCLE_BIN) {
			// recylce bin enabled
			// first of all we check which posts are already in recylce bin
			$sql = "SELECT 	postID, isDeleted
				FROM 	wbb".WBB_N."_post
				WHERE 	postID IN (".$postIDs.")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if ($row['isDeleted']) {
					// post in recylce bin
					// delete completely
					if (!empty($deleteIDs)) $deleteIDs .= ',';
					$deleteIDs .= $row['postID'];
				}
				else {
					// move post to recylce bin
					if (!empty($trashIDs)) $trashIDs .= ',';
					$trashIDs .= $row['postID'];
				}
			}
		}
		else {
			// no recylce bin
			// delete all threads completely
			$deleteIDs = $postIDs;
		}
		
		self::trashAll($trashIDs, $reason);
		self::deleteAllCompletely($deleteIDs, true, true, $updateUserStats);
		
		// reset first post id 
		self::resetFirstPostID($postIDs);
	}
	
	/**
	 * Moves the posts with the given post ids into the recycle bin.
	 */
	public static function trashAll($postIDs, $reason = '') {
		if (empty($postIDs)) return;
		
		$sql = "UPDATE 	wbb".WBB_N."_post
			SET	isDeleted = 1,
				deleteTime = ".TIME_NOW.",
				deletedBy = '".escapeString(WCF::getUser()->username)."',
				deletedByID = ".WCF::getUser()->userID.",
				deleteReason = '".escapeString($reason)."',
				isDisabled = 0
			WHERE 	postID IN (".$postIDs.")";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Deletes all posts with the given post ids.
	 */
	public static function deleteAllCompletely($postIDs, $deleteAttachments = true, $deletePolls = true, $updateUserStats = true) {
		if (empty($postIDs)) return;
		
		// delete attachments
		if ($deleteAttachments) {
			require_once(WCF_DIR.'lib/data/attachment/MessageAttachmentListEditor.class.php');
			$attachment = new MessageAttachmentListEditor(explode(',', $postIDs));
			$attachment->deleteAll();
		}
		
		// delete polls
		if ($deletePolls) {
			require_once(WCF_DIR.'lib/data/message/poll/PollEditor.class.php');
			PollEditor::deleteAll($postIDs);
		}
		
		// update user posts & activity points
		if ($updateUserStats) {
			self::updateUserStats($postIDs, 'delete');
		}
		
		// delete sql data
		self::deleteData($postIDs);
	}
	
	/**
	 * Deletes the sql data of the posts with the given post ids.
	 */
	protected static function deleteData($postIDs) {
		// delete post, post_cache, post_hash and post_report
		$sql = "DELETE FROM 	wbb".WBB_N."_post
			WHERE 		postID IN (".$postIDs.")";
		WCF::getDB()->sendQuery($sql);
		
		$sql = "DELETE FROM	wbb".WBB_N."_post_cache
			WHERE 		postID IN (".$postIDs.")";
		WCF::getDB()->registerShutdownUpdate($sql);
		
		$sql = "DELETE FROM	wbb".WBB_N."_post_hash
			WHERE 		postID IN (".$postIDs.")";
		WCF::getDB()->registerShutdownUpdate($sql);
		
		$sql = "DELETE FROM 	wbb".WBB_N."_post_report
			WHERE 		postID IN (".$postIDs.")";
		WCF::getDB()->registerShutdownUpdate($sql);
		
		// delete last posts
		$sql = "DELETE FROM 	wbb".WBB_N."_user_last_post
			WHERE 		postID IN (".$postIDs.")";
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Copies all SQL data of the posts with the given posts ids. 
	 */
	public static function copyAll($postIDs, $threadID, $threadMapping = null, $boardID = 0, $updateUserStats = true) {
		if (empty($postIDs)) return;
		
		// copy 'post' data
		$postMapping = array();
		$sql = "SELECT	*
			FROM 	wbb".WBB_N."_post
			WHERE 	postID IN (".$postIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$post = new PostEditor(null, $row);
			$postMapping[$post->postID] = $post->copy($threadID ? $threadID : $threadMapping[$row['threadID']]);
		}
		
		// refresh first post ids
		require_once(WBB_DIR.'lib/data/thread/ThreadEditor.class.php');
		ThreadEditor::refreshFirstPostIDAll(($threadID ? $threadID : implode(',', $threadMapping)));
		
		// update user posts and activity points
		if ($updateUserStats) {
			self::updateUserStats(implode(',', $postMapping), 'copy', $boardID);
		}
		
		// copy 'post_cache' data
		$sql = "SELECT	*
			FROM 	wbb".WBB_N."_post_cache
			WHERE 	postID IN (".$postIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$sql = "INSERT INTO 	wbb".WBB_N."_post_cache
						(postID, threadID, messageCache)
				VALUES		(".$postMapping[$row['postID']].",
						".($threadID ? $threadID : $threadMapping[$row['threadID']]).",
						'".escapeString($row['messageCache'])."')";
			WCF::getDB()->sendQuery($sql);
		}
		
		// copy 'post_report' data
		$sql = "SELECT 	*
			FROM 	wbb".WBB_N."_post_report
			WHERE 	postID IN (".$postIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$sql = "INSERT INTO 	wbb".WBB_N."_post_report
						(postID, userID, report, reportTime)
				VALUES		(".$postMapping[$row['postID']].",
						".$row['userID'].",
						'".escapeString($row['report'])."',
						".$row['reportTime'].")";
			WCF::getDB()->sendQuery($sql);
		}
		
		// copy polls
		require_once(WCF_DIR.'lib/data/message/poll/PollEditor.class.php');
		$pollMapping = PollEditor::copyAll($postIDs, $postMapping);
		if (is_array($pollMapping)) {
			foreach ($pollMapping as $oldPollID => $newPollID) {
				$sql = "UPDATE		wbb".WBB_N."_post
					SET 		pollID = ".$newPollID."
					WHERE 		pollID = ".$oldPollID."
							AND postID NOT IN (SELECT messageID FROM wcf".WCF_N."_poll WHERE pollID = ".$oldPollID.")";
				WCF::getDB()->sendQuery($sql);
			}
		}
		
		// copy attachments
		require_once(WCF_DIR.'lib/data/message/attachment/AttachmentsEditor.class.php');
		$attachment = new AttachmentsEditor($postIDs);
		$attachmentMapping = $attachment->copyAll($postMapping);
		
		// update inline attachments
		if (count($attachmentMapping) > 0) {
			$sql = "SELECT	postID, message
				FROM	wbb".WBB_N."_post
				WHERE	postID IN (".implode(',', array_keys($attachmentMapping)).")
					AND message LIKE '%[attach%'";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$messageChanged = false;
				foreach ($attachmentMapping[$row['postID']] as $oldAttachmentID => $newAttachmentID) {
					$row['message'] = StringUtil::replaceIgnoreCase('[attach='.$oldAttachmentID.']', '[attach='.$newAttachmentID.']', $row['message']);
					$row['message'] = StringUtil::replaceIgnoreCase('[attach]'.$oldAttachmentID.'[/attach]', '[attach]'.$newAttachmentID.'[/attach]', $row['message']);
					$messageChanged = true;
				}
				
				if ($messageChanged) {
					// update message
					$sql = "UPDATE	wbb".WBB_N."_post
						SET	message = '".escapeString($row['message'])."'
						WHERE	postID = ".$row['postID'];
					WCF::getDB()->sendQuery($sql);
					
					// delete post cache
					$sql = "DELETE FROM	wbb".WBB_N."_post_cache
						WHERE		postID = ".$row['postID'];
					WCF::getDB()->sendQuery($sql);
				}
			}
		}
	}
	
	/**
	 * Moves all posts with the given ids into the thread with the given thread id.
	 */
	public static function moveAll($postIDs, $threadID, $boardID, $updateUserStats = true) {
		if (empty($postIDs)) return;
		
		// update user posts & activity points
		if ($updateUserStats) {
			self::updateUserStats($postIDs, 'move', $boardID);
		}
		
		$sql = "UPDATE 	wbb".WBB_N."_post
			SET	threadID = ".$threadID."
			WHERE 	postID IN (".$postIDs.")
				AND threadID <> ".$threadID;
		WCF::getDB()->sendQuery($sql);
		
		// update post cache
		$sql = "UPDATE 	wbb".WBB_N."_post_cache
			SET	threadID = ".$threadID."
			WHERE 	postID IN (".$postIDs.")
				AND threadID <> ".$threadID;
		WCF::getDB()->sendQuery($sql);
		
		// reset first post id 
		self::resetFirstPostID($postIDs);
	}
	
	/**
	 * Resets first post id.
	 * 
	 * @param	string		$postIDs
	 */
	public static function resetFirstPostID($postIDs) {
		$sql = "UPDATE 	wbb".WBB_N."_thread
			SET	firstPostID = 0
			WHERE 	firstPostID IN (".$postIDs.")";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Returns the thread ids of the posts with the given post ids.
	 */
	public static function getThreadIDs($postIDs) {
		if (empty($postIDs)) return '';
		
		$threadIDs = '';
		$sql = "SELECT 	DISTINCT threadID
			FROM 	wbb".WBB_N."_post
			WHERE 	postID IN (".$postIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($threadIDs)) $threadIDs .= ',';
			$threadIDs .= $row['threadID'];
		}
		
		return $threadIDs;
	}
	
	/**
	 * Returns a list of ip addresses used by a user.
	 * 
	 * @param	integer		$userID
	 * @param	string		$username
	 * @param	string		$notIpAddress
	 * @return	array
	 */
	public static function getIpAddressByAuthor($userID, $username = '', $notIpAddress = '', $limit = 10) {
		$sql = "SELECT		DISTINCT ipAddress
			FROM 		wbb".WBB_N."_post
			WHERE 		userID = ".$userID."
					AND ipAddress <> ''".
			(!empty($username) ? " AND username = '".escapeString($username)."'" : '').
			(!empty($notIpAddress) ? " AND ipAddress <> '".escapeString($notIpAddress)."'" : '')."
			ORDER BY	time DESC";
		$result = WCF::getDB()->sendQuery($sql, $limit);
		$ipAddresses = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$ipAddresses[] = $row["ipAddress"];
		}
		
		return $ipAddresses;
	}
	
	/**
	 * Returns a list of users which have used the given ip address.
	 * 
	 * @param	string		$ipAddress
	 * @param	integer		$notUserID
	 * @param	string		$notUsername
	 * @return	array
	 */
	public static function getAuthorByIpAddress($ipAddress, $notUserID = 0, $notUsername = '', $limit = 10) {
		$sql = "SELECT		DISTINCT username
			FROM 		wbb".WBB_N."_post
			WHERE 		ipAddress = '".escapeString($ipAddress)."'".
			($notUserID ? " AND userID <> ".$notUserID : '').
			(!empty($notUsername) ? " AND username <> '".escapeString($notUsername)."'" : '')."
			ORDER BY	time DESC";
		$result = WCF::getDB()->sendQuery($sql, $limit);
		$users = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$users[] = $row["username"];
		}
		
		return $users;
	}
	
	/**
	 * Deletes the data of a post report.
	 */
	public static function removeReportData($postIDs) {
		if (empty($postIDs)) return;
		
		$sql = "DELETE FROM	wbb".WBB_N."_post_report
			WHERE		postID IN (".$postIDs.")";
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Sends the email notification.
	 */
	public function sendNotification($thread = null, $board = null, $attachmentList = null) {
		// get thread
		if ($thread === null) {
			require_once(WBB_DIR.'lib/data/thread/Thread.class.php');
			$thread = new Thread($this->threadID);
		}
		
		// get board
		if ($board === null) {
			require_once(WBB_DIR.'lib/data/board/Board.class.php');
			$board = Board::getBoard($thread->boardID);
		}
		
		$sql = "	(SELECT		user.*
				FROM		wbb".WBB_N."_thread_subscription subscription
				LEFT JOIN	wcf".WCF_N."_user user
				ON		(user.userID = subscription.userID)
				WHERE		subscription.threadID = ".$this->threadID."
						AND subscription.enableNotification = 1
						AND subscription.emails = 0
						AND subscription.userID <> ".$this->userID."
						AND user.userID IS NOT NULL)
			UNION
				(SELECT		user.*
				FROM		wbb".WBB_N."_board_subscription subscription
				LEFT JOIN	wcf".WCF_N."_user user
				ON		(user.userID = subscription.userID)
				WHERE		subscription.boardID = ".$board->boardID."
						AND subscription.enableNotification = 1
						AND subscription.emails = 0
						AND subscription.userID <> ".$this->userID."
						AND user.userID IS NOT NULL)";
		$result = WCF::getDB()->sendQuery($sql);
		if (WCF::getDB()->countRows($result)) {
			// get attachments
			if ($attachmentList === null) {
				require_once(WCF_DIR.'lib/data/attachment/MessageAttachmentList.class.php');
				$attachmentList = new MessageAttachmentList($this->postID);
				$attachmentList->readObjects();
			}
			
			// set attachments
			require_once(WCF_DIR.'lib/data/message/bbcode/AttachmentBBCode.class.php');
			AttachmentBBCode::setAttachments($attachmentList->getSortedAttachments());
			
			// get parsed text
			require_once(WCF_DIR.'lib/data/message/bbcode/MessageParser.class.php');
			$parser = MessageParser::getInstance();
			$parser->setOutputType('text/plain');
			$parsedText = $parser->parse($this->message, $this->enableSmilies, $this->enableHtml, $this->enableBBCodes, false);
			// truncate message
			if (!POST_NOTIFICATION_SEND_FULL_MESSAGE && StringUtil::length($parsedText) > 500) $parsedText = StringUtil::substring($parsedText, 0, 500) . '...';
			
			// send notifications
			$languages = array(0 => WCF::getLanguage(), WCF::getLanguage()->getLanguageID() => WCF::getLanguage());
			require_once(WCF_DIR.'lib/data/mail/Mail.class.php');
			require_once(WCF_DIR.'lib/data/user/User.class.php');
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
					'$topic' => $thread->topic,
					'$postID' => $this->postID,
					'$text' => $parsedText);
				$mail = new Mail(	array($recipient->username => $recipient->email),
							$languages[$recipient->languageID]->get('wbb.postAdd.notification.subject', array('$topic' => $thread->topic)),
							$languages[$recipient->languageID]->get('wbb.postAdd.notification.mail', $data));
				$mail->send();
			}
			
			// enable user language
			WCF::getLanguage()->setLocale();
			
			// update notification count
			$sql = "UPDATE	wbb".WBB_N."_thread_subscription
				SET 	emails = emails + 1
				WHERE	threadID = ".$this->threadID."
					AND enableNotification = 1
					AND emails = 0";
			WCF::getDB()->registerShutdownUpdate($sql);
			
			$sql = "UPDATE	wbb".WBB_N."_board_subscription
				SET 	emails = emails + 1
				WHERE	boardID = ".$board->boardID."
					AND enableNotification = 1
					AND emails = 0";
			WCF::getDB()->registerShutdownUpdate($sql);
		}
	}
	
	/**
	 * Updates the user stats (user posts, activity points & user rank).
	 * 
	 * @param	string		$postIDs		changed threads
	 * @param 	string		$mode			(enable|copy|move|delete)
	 * @param 	integer		$destinationBoardID
	 */
	public static function updateUserStats($postIDs, $mode, $destinationBoardID = 0) {
		if (empty($postIDs)) return;
		require_once(WBB_DIR.'lib/data/board/Board.class.php');
		
		// get destination board
		$destinationBoard = null;
		if ($destinationBoardID) $destinationBoard = Board::getBoard($destinationBoardID);
		if ($mode == 'copy' && !$destinationBoard->countUserPosts) return;
		
		// update user posts, activity points
		$userPosts = array();
		$userActivityPoints = array();
		$sql = "SELECT		post.userID, thread.boardID
			FROM		wbb".WBB_N."_post post
			LEFT JOIN	wbb".WBB_N."_thread thread
			ON		(thread.threadID = post.threadID)
			WHERE		post.postID IN (".$postIDs.")
					".($mode != 'enable' ? "AND post.everEnabled = 1" : '')."
					AND post.userID <> 0
					AND post.postID <> thread.firstPostID";
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
						$userActivityPoints[$row['userID']] += ACTIVITY_POINTS_PER_POST;
					}
					break;
				case 'copy':
					if ($destinationBoard->countUserPosts) {
						// posts
						if (!isset($userPosts[$row['userID']])) $userPosts[$row['userID']] = 0;
						$userPosts[$row['userID']]++;
						// activity points
						if (!isset($userActivityPoints[$row['userID']])) $userActivityPoints[$row['userID']] = 0;
						$userActivityPoints[$row['userID']] += ACTIVITY_POINTS_PER_POST;
					}
					break;
				case 'move':
					if ($board->countUserPosts != $destinationBoard->countUserPosts) {
						// posts
						if (!isset($userPosts[$row['userID']])) $userPosts[$row['userID']] = 0;
						$userPosts[$row['userID']] += ($board->countUserPosts ? -1 : 1);
						// activity points
						if (!isset($userActivityPoints[$row['userID']])) $userActivityPoints[$row['userID']] = 0;
						$userActivityPoints[$row['userID']] += ($board->countUserPosts ? ACTIVITY_POINTS_PER_POST * -1 : ACTIVITY_POINTS_PER_POST);
					}
					break;
				case 'delete':
					if ($board->countUserPosts) {
						// posts
						if (!isset($userPosts[$row['userID']])) $userPosts[$row['userID']] = 0;
						$userPosts[$row['userID']]--;
						// activity points
						if (!isset($userActivityPoints[$row['userID']])) $userActivityPoints[$row['userID']] = 0;
						$userActivityPoints[$row['userID']] -= ACTIVITY_POINTS_PER_POST;
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
	 * Merges posts.
	 */
	public static function mergeAll($postIDs, $postID) {
		if (empty($postIDs)) return;
		
		// get messages
		$message = '';
		$sql = "SELECT		message
			FROM		wbb".WBB_N."_post
			WHERE		postID IN (".$postIDs.")
			ORDER BY 	time";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$message .= "\n".$row['message'];
		}
		
		// update attachments
		$sql = "UPDATE	wcf".WCF_N."_attachment
			SET	containerID = ".$postID."
			WHERE	packageID = ".PACKAGE_ID."
				AND containerID IN (".$postIDs.")
				AND containerType = 'post'";
		WCF::getDB()->sendQuery($sql);
		
		// update message and recount attachments
		$sql = "UPDATE 	wbb".WBB_N."_post
			SET	message = CONCAT(message, '".escapeString($message)."'),
				attachments = (
					SELECT	COUNT(*)
					FROM	wcf".WCF_N."_attachment
					WHERE	packageID = ".PACKAGE_ID."
						AND containerID = ".$postID."
						AND containerType = 'post'
				)
			WHERE 	postID = ".$postID;
		WCF::getDB()->sendQuery($sql);
		
		// clear post cache
		$sql = "DELETE FROM 	wbb".WBB_N."_post_cache
			WHERE 		postID = ".$postID;
		WCF::getDB()->sendQuery($sql);
		
		// delete posts
		self::deleteAllCompletely($postIDs, true, true, false);
		
		// reset first post id 
		self::resetFirstPostID($postIDs);
	}
}
?>