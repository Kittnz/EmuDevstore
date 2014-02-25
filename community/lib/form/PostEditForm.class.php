<?php
// wbb imports
require_once(WBB_DIR.'lib/data/thread/ThreadEditor.class.php');
require_once(WBB_DIR.'lib/data/post/PostEditor.class.php');
require_once(WBB_DIR.'lib/data/post/PostAction.class.php');
require_once(WBB_DIR.'lib/data/board/BoardEditor.class.php');
require_once(WBB_DIR.'lib/form/ThreadAddForm.class.php');

/**
 * Shows the edit post form.
 *
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	form
 * @category 	Burning Board
 */
class PostEditForm extends ThreadAddForm {
	// system
	public $templateName = 'postEdit';
	public $minCharLength = POST_MIN_CHAR_LENGTH;
	public $minWordCount = POST_MIN_WORD_COUNT;
	public $isModerator = false;
	public $isAuthor = false;
	public $canEditPost = false;
	public $canDeletePost = false;
	
	/**
	 * post id
	 * 
	 * @var	integer
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
	public $thread;
	
	/**
	 * list of posts
	 * 
	 * @var	PostEditPostList
	 */
	public $postList = null;
	
	/**
	 * list of attachments
	 * 
	 * @var	array
	 */
	public $attachments = array();
	
	// parameters
	public $hideEditNote = 1;
	public $deleteReason = '';
	public $editReason = '';
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		MessageForm::readParameters();
		
		if (isset($_REQUEST['postID'])) $this->postID = intval($_REQUEST['postID']);
		
		$this->post = new PostEditor($this->postID);
		$this->thread = new ThreadEditor($this->post->threadID);
		if (!$this->thread->threadID) {
			throw new IllegalLinkException();
		}
		$this->board = new BoardEditor($this->thread->boardID);
		
		$this->thread->enter($this->board);
		
		// check permissions (TODO: maybe we can use post->canEditPost() here)
		$this->isModerator = $this->board->getModeratorPermission('canEditPost') || $this->board->getModeratorPermission('canDeletePost');
		$this->isAuthor = $this->post->userID && $this->post->userID == WCF::getUser()->userID;
		
		$this->canEditPost = $this->board->getModeratorPermission('canEditPost') || $this->isAuthor && $this->board->getPermission('canEditOwnPost');
		$this->canDeletePost = $this->board->getModeratorPermission('canDeletePost') || $this->isAuthor && $this->board->getPermission('canDeleteOwnPost');

		if ((!$this->canEditPost && !$this->canDeletePost) || (!$this->isModerator && ($this->board->isClosed || $this->thread->isClosed || $this->post->isClosed))) {
			throw new PermissionDeniedException();
		}

		// check post edit timeout 
		if (!$this->isModerator && WCF::getUser()->getPermission('user.board.postEditTimeout') != -1 && TIME_NOW - $this->post->time > WCF::getUser()->getPermission('user.board.postEditTimeout') * 60) {
			throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wbb.postEdit.error.timeout', array('timeout' => WCF::getUser()->getPermission('user.board.postEditTimeout'))));
		}
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->hideEditNote = 0;
		if (isset($_POST['hideEditNote']) && $this->board->getPermission('canHideEditNote')) {
			$this->hideEditNote = intval($_POST['hideEditNote']);
		}
		if (isset($_POST['deleteReason'])) $this->deleteReason = StringUtil::trim($_POST['deleteReason']);
		if (isset($_POST['editReason'])) $this->editReason = StringUtil::trim($_POST['editReason']);
	}
	
	/**
	 * @see Form::submit()
	 */
	public function submit() {
		parent::submit();
		
		try {
			if (isset($_POST['deletePost'])) {
				if (!$this->canDeletePost()) {
					throw new PermissionDeniedException();
				}
				
				if (isset($_POST['sure'])) {
					$postAction = new PostAction($this->board, $this->thread, $this->post, 0, '', '', $this->deleteReason);
					
					if ($this->post->isDeleted) {
						$postAction->delete();
					}
					else {
						$postAction->trash(true);
						$thread = new ThreadEditor($this->thread->threadID);
						if ($thread->isDeleted) HeaderUtil::redirect('index.php?page=Board&boardID='.$this->thread->boardID.SID_ARG_2ND_NOT_ENCODED);
						else HeaderUtil::redirect('index.php?page=Thread&threadID='.$this->thread->threadID.SID_ARG_2ND_NOT_ENCODED);
						exit;
					}
				}
				else {
					throw new UserInputException('sure');
				}
			}
		}
		catch (UserInputException $e) {
			$this->errorField = $e->getField();
			$this->errorType = $e->getType();
		}
	}
	
	/**
	 * Does nothing.
	 */
	protected function validateSubject() {}
	
	/**
	 * @see ThreadAddForm::validatePrefix()
	 */
	protected function validatePrefix() {
		if ($this->thread->firstPostID == $this->post->postID) {
			parent::validatePrefix();
		}
	}
	
	/**
	 * @see ThreadAddForm::validateLanguage()
	 */
	protected function validateLanguage() {
		if ($this->thread->firstPostID == $this->post->postID) {
			parent::validateLanguage();
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		if (!$this->canEditPost()) {
			throw new PermissionDeniedException();
		}

		// set the language temporarily to the thread language
		if ($this->thread->languageID && $this->thread->languageID != WCF::getLanguage()->getLanguageID()) {
			$this->setLanguage($this->thread->languageID);
		}
		
		MessageForm::save();
		
		// update subscription
		$this->thread->setSubscription($this->subscription);
		
		// save poll
		if ($this->showPoll) {
			$this->pollEditor->save();
		}
		
		// add edit note
		$postData = array();
		if (!$this->hideEditNote && (WCF::getUser()->userID != $this->post->userID || $this->post->time <= TIME_NOW - POST_EDIT_HIDE_EDIT_NOTE_PERIOD * 60)) {
			$postData['editor'] = WCF::getUser()->username;
			$postData['editorID'] = WCF::getUser()->userID;
			$postData['lastEditTime'] = TIME_NOW;
			$postData['editCount'] = $this->post->editCount + 1;
			$postData['editReason'] = $this->editReason;
		}
		else if (!empty($this->editReason)) {
			$postData['editReason'] = $this->editReason;
		}
		
		// update database entry
		$this->post->update($this->subject, $this->text, $this->getOptions(), $this->attachmentListEditor, $this->pollEditor, $postData);
		
		// reset language
		if ($this->userInterfaceLanguageID !== null) {
			$this->setLanguage($this->userInterfaceLanguageID, true);
		}
		
		$threadData = array();
		if ($this->thread->firstPostID == $this->post->postID) {
			// update thread topic
			if (!empty($this->subject)) $threadData['topic'] = $this->subject;
			
			// update thread prefix
			if ($this->board->getPermission('canUsePrefix')) {
				$threadData['prefix'] = $this->prefix;
			}
			
			// save tags
			if (MODULE_TAGGING && THREAD_ENABLE_TAGS && $this->board->getPermission('canSetTags')) {
				$this->thread->updateTags(TaggingUtil::splitString($this->tags));
			}
			
			// announcement
			if ($this->board->getModeratorPermission('canStartAnnouncement')) {
				if ($this->thread->isAnnouncement) {
					$this->thread->removeAssignedBoards();
				}
				if ($this->isImportant == 2) {
					$this->thread->assignBoards($this->boardIDs);
				}
				$threadData['isAnnouncement'] = intval($this->isImportant == 2);
			}
				
			// pin thread
			if ($this->board->getModeratorPermission('canPinThread')) {
				$threadData['isSticky'] = intval($this->isImportant == 1);
			}
			
			// set language
			if ($this->languageID != $this->thread->languageID) {
				$threadData['languageID'] = $this->languageID;
			}
		}
		
		// close / open thread
		if ($this->board->getModeratorPermission('canCloseThread')) {
			if (!$this->thread->isClosed && $this->closeThread) $threadData['isClosed'] = 1;
			else if ($this->thread->isClosed && !$this->closeThread) $threadData['isClosed'] = 0;
		}

		// update thread
		$this->thread->update($threadData);
		
		// update last posts
		if ($this->thread->firstPostID == $this->post->postID && $this->languageID != $this->thread->languageID) {
			$this->board->setLastPosts();
			WCF::getCache()->clearResource('boardData');
		}
		$this->saved();
		
		// forward to post
		$url = 'index.php?page=Thread&postID='.$this->postID. SID_ARG_2ND_NOT_ENCODED . '#post'.$this->postID;
		HeaderUtil::redirect($url);
		exit;
	}
	
	/**
	 * @see MessageForm::saveOptions()
	 */
	protected function saveOptions() {
		if (WCF::getUser()->userID) {
			$options = array();
			
			// wysiwyg
			$options['wysiwygEditorMode'] = $this->wysiwygEditorMode;
			$options['wysiwygEditorHeight'] = $this->wysiwygEditorHeight;
			
			// options
			if (WCF::getUser()->getPermission('user.'.$this->permissionType.'.canUseBBCodes')) {
				$options[$this->permissionType.'ParseURL'] = $this->parseURL;
			}
			
			$editor = WCF::getUser()->getEditor();
			$editor->updateOptions($options);
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();

		if (!count($_POST)) {
			$this->closeThread = $this->thread->isClosed;
			$this->text = $this->post->message;
			$this->subject = $this->post->subject;
			$this->subscription = $this->thread->subscribed;
			$this->prefix = $this->thread->prefix;
			if ($this->thread->isSticky) $this->isImportant = 1;
			if ($this->thread->isAnnouncement) $this->isImportant = 2;
			$this->boardIDs = $this->thread->getAssignedBoards();
			$this->languageID = $this->thread->languageID;
			$this->editReason = $this->post->editReason;
			
			$this->enableSmilies =  $this->post->enableSmilies;
			$this->enableHtml = $this->post->enableHtml;
			$this->enableBBCodes = $this->post->enableBBCodes;
			$this->showSignature = $this->post->showSignature;
			
			// tags
			if (THREAD_ENABLE_TAGS && $this->thread->firstPostID == $this->postID) {
				$this->tags = TaggingUtil::buildString($this->thread->getTags(array($this->languageID)));
			}
		}
			
		// get post list
		if ($this->thread->firstPostID != $this->postID) {
			try {
				require_once(WBB_DIR.'lib/data/post/PostEditPostList.class.php');
				$this->postList = new PostEditPostList($this->post, $this->thread, $this->board);
				$this->attachments = $this->postList->attachments;
				if (count($this->attachments) > 0) {
					require_once(WCF_DIR.'lib/data/attachment/MessageAttachmentList.class.php');
					MessageAttachmentList::removeEmbeddedAttachments($this->attachments);
				}
			}
			catch (SystemException $e) {}
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'postID' =>  $this->postID,
			'thread' => $this->thread,
			'form' => $this,
			'post' => $this->post,
			'hideEditNote' => $this->hideEditNote,
			'posts' => ($this->postList ? $this->postList->posts : array()),
			'postAttachments' => $this->attachments,
			'deleteReason' => $this->deleteReason,
			'editReason' => $this->editReason
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if ($this->thread->firstPostID != $this->post->postID && !WCF::getUser()->getPermission('user.board.canStartPollInReply')) {
			$this->showPoll = false;
		}
		
		$this->attachmentListEditor = new MessageAttachmentListEditor(array($this->postID), 'post', PACKAGE_ID, WCF::getUser()->getPermission('user.board.maxAttachmentSize'), WCF::getUser()->getPermission('user.board.allowedAttachmentExtensions'), WCF::getUser()->getPermission('user.board.maxAttachmentCount'));
		$this->pollEditor = new PollEditor($this->post->pollID, 0, 'post', WCF::getUser()->getPermission('user.board.canStartPublicPoll'));
		
		parent::show();
	}
	
	/**
	 * Returns true, if the active user can edit this post.
	 */
	public function canEditPost() {
		return $this->canEditPost;
	}
	
	/**
	 * Returns true, if the active user can delete this post.
	 */
	public function canDeletePost() {
		return $this->canDeletePost;
	}
	
	/**
	 * @see ThreadAddForm::getAvailableLanguages()
	 */
	protected function getAvailableLanguages() {
		$visibleLanguages = explode(',', WCF::getUser()->languageIDs);
		$availableLanguages = Language::getAvailableContentLanguages(PACKAGE_ID);
		foreach ($availableLanguages as $key => $language) {
			if (!in_array($language['languageID'], $visibleLanguages) && !$this->board->getModeratorPermission('canEditPost')) {
				unset($availableLanguages[$key]);
			}
		}
		
		return $availableLanguages;
	}
}
?>