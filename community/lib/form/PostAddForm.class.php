<?php
// wbb imports
require_once(WBB_DIR.'lib/data/thread/ThreadEditor.class.php');
require_once(WBB_DIR.'lib/data/post/PostEditor.class.php');
require_once(WBB_DIR.'lib/data/board/BoardEditor.class.php');
require_once(WBB_DIR.'lib/data/post/PostAddPostList.class.php');
require_once(WBB_DIR.'lib/form/ThreadAddForm.class.php');

/**
 * Shows the thread reply form.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	form
 * @category 	Burning Board
 */
class PostAddForm extends ThreadAddForm {
	// system
	public $templateName = 'postAdd';
	public $minCharLength = POST_MIN_CHAR_LENGTH;
	public $minWordCount = POST_MIN_WORD_COUNT;
	
	/**
	 * thread id
	 * 
	 * @var	integer
	 */
	public $threadID = 0;
	
	/**
	 * post id
	 * 
	 * @var	integer
	 */
	public $postID = 0;
	
	/**
	 * thread editor object
	 * 
	 * @var	ThreadEditor
	 */
	public $thread = null;
	
	/**
	 * post editor object
	 * 
	 * @var	PostEditor
	 */
	public $newPost = null;
	
	/**
	 * list of posts
	 * 
	 * @var	PostAddPostList
	 */
	public $postList = null;
	
	/**
	 * list of attachments
	 * 
	 * @var	array
	 */
	public $attachments = array();
	
	// parameters
	public $action = '';
	public $oldThreadWarning = 0;
	public $disablePost = 0;
	public $markAsDone = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		MessageForm::readParameters();
		
		if (isset($_REQUEST['threadID'])) $this->threadID = intval($_REQUEST['threadID']);
		if (isset($_REQUEST['postID'])) $this->postID = intval($_REQUEST['postID']);
		if (isset($_REQUEST['action'])) $this->action = $_REQUEST['action'];
		
		// get thread
		$this->thread = new ThreadEditor($this->threadID, null, $this->postID);
		$this->threadID = $this->thread->threadID;
		
		// get board
		$this->board = new BoardEditor($this->thread->boardID);
		
		// check permissions
		$this->thread->enter($this->board);
		if (!$this->thread->canReplyThread($this->board)) {
			throw new PermissionDeniedException();
		}
		
		// check double posts
		if (WCF::getUser()->getPermission('user.board.doublePostLock') != 0 && WCF::getUser()->userID && WCF::getUser()->userID == $this->thread->lastPosterID) {
			if (WCF::getUser()->getPermission('user.board.doublePostLock') == -1) {
				throw new NamedUserException(WCF::getLanguage()->get('wbb.postAdd.error.doublePostLock'));
			}
			else if ($this->thread->lastPostTime >= TIME_NOW - WCF::getUser()->getPermission('user.board.doublePostLock') * 60) {
				throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wbb.postAdd.error.doublePostLockTime', array('timeout' => WCF::getUser()->getPermission('user.board.doublePostLock'))));
			}
		}
		
		$this->messageTable = "wbb".WBB_N."_post";
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['disablePost']) && $this->board->getModeratorPermission('canEnablePost')) {
			$this->disablePost = intval($_POST['disablePost']);
		}
		if (isset($_POST['markAsDone']) && MODULE_THREAD_MARKING_AS_DONE && $this->board->enableMarkingAsDone && !$this->thread->isDone && ($this->board->getModeratorPermission('canMarkAsDoneThread') || (WCF::getUser()->userID && WCF::getUser()->userID == $this->thread->userID && $this->board->getPermission('canMarkAsDoneOwnThread')))) {
			$this->markAsDone = intval($_POST['markAsDone']);
		}
	}
	
	/**
	 * Does nothing.
	 */
	protected function validateSubject() {}
	
	/**
	 * Does nothing.
	 */
	protected function validatePrefix() {}
	
	/**
	 * Does nothing.
	 */
	protected function validateLanguage() {}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		// set the language temporarily to the thread language
		if ($this->thread->languageID && $this->thread->languageID != WCF::getLanguage()->getLanguageID()) {
			$this->setLanguage($this->thread->languageID);
		}
		
		MessageForm::save();
		
		if ($this->thread->isDisabled) {
			$this->disablePost = 1;
		}
		
		// search for double posts
		if ($postID = PostEditor::test($this->subject, $this->text, WCF::getUser()->userID, $this->username, $this->threadID)) {
			HeaderUtil::redirect('index.php?page=Thread&postID=' . $postID . SID_ARG_2ND_NOT_ENCODED . '#post' . $postID);
			exit;
		}
		
		// save poll
		if ($this->showPoll) {
			$this->pollEditor->save();
		}
		
		// save post in database
		$this->newPost = PostEditor::create($this->thread->threadID, $this->subject, $this->text, WCF::getUser()->userID, $this->username, $this->getOptions(), $this->attachmentListEditor, $this->pollEditor, null, intval(($this->disablePost || !$this->board->getPermission('canReplyThreadWithoutModeration'))));
		
		// reset language
		if ($this->userInterfaceLanguageID !== null) {
			$this->setLanguage($this->userInterfaceLanguageID, true);
		}
		
		// remove quotes
		$sessionVars = WCF::getSession()->getVars();
		if (isset($sessionVars['quotes'][$this->threadID])) {
			unset($sessionVars['quotes'][$this->threadID]);
			WCF::getSession()->register('quotes', $sessionVars['quotes']);
		}
		
		if (!$this->disablePost && $this->board->getPermission('canReplyThreadWithoutModeration')) {
			// refresh thread
			$this->thread->addPost($this->newPost, $this->closeThread);
			
			// update subscription
			$this->thread->setSubscription($this->subscription);
			
			// update user posts
			if (WCF::getUser()->userID && $this->board->countUserPosts) {
				WBBUser::updateUserPosts(WCF::getUser()->userID, 1);
				if (ACTIVITY_POINTS_PER_POST) {
					require_once(WCF_DIR.'lib/data/user/rank/UserRank.class.php');
					UserRank::updateActivityPoints(ACTIVITY_POINTS_PER_POST);
				}
			}
			
			// refresh counter and last post
			$this->board->addPosts();
			$this->board->setLastPost($this->thread);
			
			// close / open thread
			if (!$this->thread->isClosed && $this->closeThread) $this->thread->close();
			else if ($this->thread->isClosed && !$this->closeThread) $this->thread->open();
			
			// mark as done
			if ($this->markAsDone == 1) $this->thread->markAsDone();
			// mark as undone
			else if (MODULE_THREAD_MARKING_AS_DONE && $this->board->enableMarkingAsDone && $this->thread->isDone && WCF::getUser()->userID && WCF::getUser()->userID == $this->thread->userID) {
				$this->thread->markAsUndone();
			}
			
			// reset stat cache
			WCF::getCache()->clearResource('stat');
			WCF::getCache()->clearResource('boardData');
			
			// send notifications
			$this->newPost->sendNotification($this->thread, $this->board, $this->attachmentListEditor);
			$this->saved();
			
			// forward to post
			$url = 'index.php?page=Thread&postID='.$this->newPost->postID. SID_ARG_2ND_NOT_ENCODED . '#post'.$this->newPost->postID;
			HeaderUtil::redirect($url);
		}
		else {
			$this->saved();
			if ($this->disablePost) {
				HeaderUtil::redirect('index.php?page=Thread&postID='.$this->newPost->postID. SID_ARG_2ND_NOT_ENCODED . '#post'.$this->newPost->postID);
			}
			else {
				WCF::getTPL()->assign(array(
					'url' => 'index.php?page=Thread&threadID='.$this->threadID.SID_ARG_2ND_NOT_ENCODED,
					'message' => WCF::getLanguage()->get('wbb.postAdd.moderation.redirect'),
					'wait' => 5
				));
				WCF::getTPL()->display('redirect');
			}
		}
		exit;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get post list
		$this->postList = new PostAddPostList($this->thread, $this->board);
		
		// old thread warning
		if (REPLY_OLD_THREAD_WARNING && $this->thread->lastPostTime > 0) {
			$this->oldThreadWarning = intval(floor((TIME_NOW - $this->thread->lastPostTime) / 86400));
			if ($this->oldThreadWarning < REPLY_OLD_THREAD_WARNING) {
				$this->oldThreadWarning = 0;
			}
		}
		
		$this->attachments = $this->postList->attachments;
		if (count($this->attachments) > 0) {
			require_once(WCF_DIR.'lib/data/attachment/MessageAttachmentList.class.php');
			MessageAttachmentList::removeEmbeddedAttachments($this->attachments);
		}
		
		// default values
		if (!count($_POST)) {
			$this->closeThread = $this->thread->isClosed;
			$this->subscription = $this->thread->subscribed;
			if (!$this->subscription && WCF::getUser()->enableSubscription) {
				$this->subscription = 1;
			}
			
			// single quote
			if ($this->action == 'quote') {
				$post = $this->thread->getPost();
				if ($post) {
					$this->text = "[quote='".StringUtil::replace("'", "\'", $post->username)."',index.php?page=Thread&postID=".$post->postID."#post".$post->postID."]".$post->message."[/quote]";
					if ($post->subject) {
						$this->subject = WCF::getLanguage()->get('wbb.postAdd.quote.subject', array('$subject' => $post->subject));
					}
				}
			}
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
			'posts' => $this->postList->posts,
			'items' => $this->postList->countPosts(),
			'postAttachments' => $this->attachments,
			'oldThreadWarning' => $this->oldThreadWarning,
			'disablePost' => $this->disablePost,
			'markAsDone' => $this->markAsDone,
			'insertQuotes' => (!count($_POST) && empty($this->text) ? 1 : 0)
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (!WCF::getUser()->getPermission('user.board.canStartPollInReply')) {
			$this->showPoll = false;
		}
		
		// show form
		parent::show();
	}
}
?>