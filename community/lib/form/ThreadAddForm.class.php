<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');
require_once(WBB_DIR.'lib/data/board/BoardEditor.class.php');
require_once(WBB_DIR.'lib/data/post/PostEditor.class.php');
require_once(WBB_DIR.'lib/data/thread/ThreadEditor.class.php');

// wcf imports
require_once(WCF_DIR.'lib/data/attachment/MessageAttachmentListEditor.class.php');
require_once(WCF_DIR.'lib/data/message/poll/PollEditor.class.php');
require_once(WCF_DIR.'lib/form/MessageForm.class.php');
require_once(WCF_DIR.'lib/system/language/Language.class.php');

/**
 * Shows the new thread form.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	form
 * @category 	Burning Board
 */
class ThreadAddForm extends MessageForm {
	// system
	public $templateName = 'threadAdd';
	public $useCaptcha = POST_ADD_USE_CAPTCHA;
	public $minCharLength = THREAD_MIN_CHAR_LENGTH;
	public $minWordCount = THREAD_MIN_WORD_COUNT;
	
	/**
	 * board id
	 * 
	 * @var	integer
	 */
	public $boardID = 0;
	
	/**
	 * board editor object
	 * 
	 * @var	BoardEditor
	 */
	public $board = null;
	
	/**
	 * attachment list editor object
	 * 
	 * @var	MessageAttachmentListEditor
	 */
	public $attachmentListEditor = null;
	
	/**
	 * poll editor object
	 * 
	 * @var	PollEditor
	 */
	public $pollEditor = null;
	
	/**
	 * thread editor object
	 * 
	 * @var	ThreadEditor
	 */
	public $newThread = null;
	
	/**
	 * list of available languages
	 * 
	 * @var	array
	 */
	public $availableLanguages = array();
	
	/**
	 * list of available boards
	 * 
	 * @var	array
	 */
	public $boardOptions = array();

	// form parameters
	public $username = '';
	public $prefix = '';
	public $subscription = 0;
	public $closeThread = 0;
	public $disableThread = 0;
	public $isImportant = 0;
	public $preview, $send;
	public $boardIDs = array();
	public $languageID = 0;
	public $tags = '';
	protected $userInterfaceLanguageID = null;

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get board
		if (isset($_REQUEST['boardID'])) $this->boardID = intval($_REQUEST['boardID']);
		$this->board = new BoardEditor($this->boardID);
		$this->board->enter();
		
		// check permissions
		if (!$this->board->canStartThread()) {
			throw new PermissionDeniedException();
		}
		
		$this->messageTable = "wbb".WBB_N."_post";
	}

	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) 		$this->username 	= StringUtil::trim($_POST['username']);
		if (isset($_POST['prefix']) && $this->board->getPermission('canUsePrefix')) {
			$this->prefix = $_POST['prefix'];
		}
		if (isset($_POST['preview']))		$this->preview		= (boolean) $_POST['preview'];
		if (isset($_POST['send']))		$this->send		= (boolean) $_POST['send'];
		if (isset($_POST['boardIDs']))		$this->boardIDs		= ArrayUtil::toIntegerArray($_POST['boardIDs']);
		if (isset($_POST['languageID']))	$this->languageID	= intval($_POST['languageID']);
		if (isset($_POST['tags'])) 		$this->tags 		= StringUtil::trim($_POST['tags']);
		
		$this->subscription = $this->closeThread = $this->isImportant = 0;
		// subscription
		if (isset($_POST['subscription'])) $this->subscription = intval($_POST['subscription']);
		
		// close thread
		if (isset($_POST['closeThread']) && $this->board->getModeratorPermission('canCloseThread')) {
			$this->closeThread = intval($_POST['closeThread']);
		}
		// disable thread
		if (isset($_POST['disableThread']) && $this->board->getModeratorPermission('canEnableThread')) {
			$this->disableThread = intval($_POST['disableThread']);
		}
		
		// thread status
		if (isset($_POST['isImportant'])) $this->isImportant = intval($_POST['isImportant']);
		if ($this->isImportant < 0 || $this->isImportant > 2) $this->isImportant = 0;
		if ($this->isImportant == 1 && !$this->board->getModeratorPermission('canPinThread')) $this->isImportant = 0;
		if ($this->isImportant == 2 && !$this->board->getModeratorPermission('canStartAnnouncement')) $this->isImportant = 0;
	}
	
	/**
	 * @see Form::submit()
	 */
	public function submit() {
		// call submit event
		EventHandler::fireAction($this, 'submit');
		
		$this->readFormParameters();
		
		try {
			// attachment handling
			if ($this->showAttachments) {
				$this->attachmentListEditor->handleRequest();
			}
			
			// poll handling
			if ($this->showPoll) {
				$this->pollEditor->readParams();
			}
				
			// preview
			if ($this->preview) {
				require_once(WCF_DIR.'lib/data/message/bbcode/AttachmentBBCode.class.php');
				AttachmentBBCode::setAttachments($this->attachmentListEditor->getSortedAttachments());
				WCF::getTPL()->assign('preview', PostEditor::createPreview($this->subject, $this->text, $this->enableSmilies, $this->enableHtml, $this->enableBBCodes));
			}
			// send message or save as draft
			if ($this->send) {
				$this->validate();
				// no errors
				$this->save();
			}
		}
		catch (UserInputException $e) {
			$this->errorField = $e->getField();
			$this->errorType = $e->getType();
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		// prefix
		$this->validatePrefix();
		
		// subject, text, captcha
		parent::validate();
		
		// username
		$this->validateUsername();
		
		// poll
		if ($this->showPoll) $this->pollEditor->checkParams();
		
		// language
		$this->validateLanguage();
	}
	
	/**
	 * Validates message text.
	 */
	protected function validateText() {
		parent::validateText();
		
		// check text length
		if ($this->minCharLength > 0 && StringUtil::length($this->text) < $this->minCharLength) {
			throw new UserInputException('text', 'tooShort');
		}
		
		// check word count
		if ($this->minWordCount > 0 && count(preg_split('/[\W]+/', $this->text, -1, PREG_SPLIT_NO_EMPTY)) < $this->minWordCount) {
			throw new UserInputException('text', 'tooShort');
		}
	}
	
	/**
	 * Validates the language.
	 */
	protected function validateLanguage() {
		// language
		$availableLanguages = Language::getAvailableContentLanguages(PACKAGE_ID);
		if (count($availableLanguages) > 0) {
			if (!isset($availableLanguages[$this->languageID])) {
				$this->languageID = WCF::getLanguage()->getLanguageID();
				if (!isset($availableLanguages[$this->languageID])) {
					$languageIDs = array_keys($availableLanguages);
					$this->languageID = array_shift($languageIDs);
				}
			}
		}
		else {
			$this->languageID = 0;
		}
	}
	
	/**
	 * Validates the username.
	 */
	protected function validateUsername() {
		// only for guests
		if (WCF::getUser()->userID == 0) {
			// username
			if (empty($this->username)) {
				throw new UserInputException('username');
			}
			if (!UserUtil::isValidUsername($this->username)) {
				throw new UserInputException('username', 'notValid');
			}
			if (!UserUtil::isAvailableUsername($this->username)) {
				throw new UserInputException('username', 'notAvailable');
			}
			
			WCF::getSession()->setUsername($this->username);
		}
		else {
			$this->username = WCF::getUser()->username;
		}
	}
	
	/**
	 * Validates the given prefix.
	 */
	protected function validatePrefix() {
		if ($this->board->prefixRequired && empty($this->prefix)) {
			throw new UserInputException('prefix');
		}
		
		$prefixes = $this->board->getPrefixOptions();
		if (!empty($this->prefix) && !isset($prefixes[$this->prefix])) {
			throw new UserInputException('prefix', 'invalid');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		// set the language temporarily to the thread language
		if ($this->languageID && $this->languageID != WCF::getLanguage()->getLanguageID()) {
			$this->setLanguage($this->languageID);
		}
		
		parent::save();
		
		// search for double posts
		if ($postID = PostEditor::test($this->subject, $this->text, WCF::getUser()->userID, $this->username)) {
			HeaderUtil::redirect('index.php?page=Thread&postID=' . $postID . SID_ARG_2ND_NOT_ENCODED . '#post' . $postID);
			exit;
		}
		
		// save poll
		if ($this->showPoll) {
			$this->pollEditor->save();
		}
		
		// save thread in database
		$this->newThread = ThreadEditor::create($this->board->boardID, $this->languageID, $this->prefix, $this->subject, $this->text, WCF::getUser()->userID, $this->username, intval($this->isImportant == 1), intval($this->isImportant == 2), $this->closeThread, $this->getOptions(), $this->subscription, $this->attachmentListEditor, $this->pollEditor, intval(($this->disableThread || !$this->board->getPermission('canStartThreadWithoutModeration'))));
		if ($this->isImportant == 2) {
			$this->newThread->assignBoards($this->boardIDs);
		}
		
		// save tags
		if (MODULE_TAGGING && THREAD_ENABLE_TAGS && $this->board->getPermission('canSetTags')) {
			$tagArray = TaggingUtil::splitString($this->tags);
			if (count($tagArray)) $this->newThread->updateTags($tagArray);
		}
		
		// reset language
		if ($this->userInterfaceLanguageID !== null) {
			$this->setLanguage($this->userInterfaceLanguageID, true);
		}
		
		if (!$this->disableThread && $this->board->getPermission('canStartThreadWithoutModeration')) {
			// update user posts
			if (WCF::getUser()->userID && $this->board->countUserPosts) {
				require_once(WBB_DIR.'lib/data/user/WBBUser.class.php');
				WBBUser::updateUserPosts(WCF::getUser()->userID, 1);
				if (ACTIVITY_POINTS_PER_THREAD) {
					require_once(WCF_DIR.'lib/data/user/rank/UserRank.class.php');
					UserRank::updateActivityPoints(ACTIVITY_POINTS_PER_THREAD);
				}
			}
			
			// refresh counter and last post
			$this->board->addThreads();
			$this->board->setLastPost($this->newThread);
			
			// reset stat cache
			WCF::getCache()->clearResource('stat');
			WCF::getCache()->clearResource('boardData');
			
			// send notifications
			$this->newThread->sendNotification(new Post(null, array('postID' => $this->newThread->firstPostID, 'message' => $this->text, 'enableSmilies' => $this->enableSmilies, 'enableHtml' => $this->enableHtml, 'enableBBCodes' => $this->enableBBCodes)), $this->attachmentListEditor);
			$this->saved();
			
			// forward to post
			HeaderUtil::redirect('index.php?page=Thread&threadID=' . $this->newThread->threadID . SID_ARG_2ND_NOT_ENCODED);
		}
		else {
			$this->saved();
			if ($this->disableThread) {
				// forward to post
				HeaderUtil::redirect('index.php?page=Thread&threadID=' . $this->newThread->threadID . SID_ARG_2ND_NOT_ENCODED);
			}
			else {
				WCF::getTPL()->assign(array(
					'url' => 'index.php?page=Board&boardID='.$this->boardID.SID_ARG_2ND_NOT_ENCODED,
					'message' => WCF::getLanguage()->get('wbb.threadAdd.moderation.redirect'),
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
		
		if (!count($_POST)) {
			// default values
			$this->username = WCF::getSession()->username;
			if (!$this->subscription && WCF::getUser()->enableSubscription) {
				$this->subscription = 1;
			}
		}
		
		$this->boardOptions = Board::getBoardSelect(array('canViewBoard'), true, true);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'username' => $this->username,
			'isImportant' => $this->isImportant,
			'board' => $this->board,
			'boardID' => $this->board->boardID,
			'subscription' => $this->subscription,
			'postID' => 0,
			'closeThread' => $this->closeThread,
			'prefix' => $this->prefix,
			'boardOptions' => $this->boardOptions,
			'boardIDs' => $this->boardIDs,
			'languageID' => $this->languageID,
			'availableLanguages' => $this->availableLanguages,
			'tags' => $this->tags,
			'disableThread' => $this->disableThread,
			'minCharLength' => $this->minCharLength,
			'minWordCount' => $this->minWordCount
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		$this->loadAvailableLanguages();
		
		// get max text length
		$this->maxTextLength = WCF::getUser()->getPermission('user.board.maxPostLength');
		
		if (MODULE_POLL != 1 || !$this->board->getPermission('canStartPoll')) {
			$this->showPoll = false;
		}
		
		if (MODULE_ATTACHMENT != 1 || !$this->board->getPermission('canUploadAttachment')) {
			$this->showAttachments = false;
		}
		
		// get attachments editor
		if ($this->attachmentListEditor == null) {
			$this->attachmentListEditor = new MessageAttachmentListEditor(array(), 'post', PACKAGE_ID, WCF::getUser()->getPermission('user.board.maxAttachmentSize'), WCF::getUser()->getPermission('user.board.allowedAttachmentExtensions'), WCF::getUser()->getPermission('user.board.maxAttachmentCount'));
		}
		
		// get poll editor
		if ($this->pollEditor == null) $this->pollEditor = new PollEditor(0, 0, 'post', WCF::getUser()->getPermission('user.board.canStartPublicPoll'));
		
		// show form
		parent::show();
	}
	
	/**
	 * Gets the available content languages.
	 */
	protected function loadAvailableLanguages() {
		if ($this->languageID == 0) $this->languageID = WCF::getLanguage()->getLanguageID();
		$this->availableLanguages = $this->getAvailableLanguages();
		
		if (!isset($this->availableLanguages[$this->languageID]) && count($this->availableLanguages) > 0) {
			$languageIDs = array_keys($this->availableLanguages);
			$this->languageID = array_shift($languageIDs);
		}
	}
	
	/**
	 * Returns a list of available languages.
	 *
	 * @return	array
	 */
	protected function getAvailableLanguages() {
		$visibleLanguages = explode(',', WCF::getUser()->languageIDs);
		$availableLanguages = Language::getAvailableContentLanguages(PACKAGE_ID);
		foreach ($availableLanguages as $key => $language) {
			if (!in_array($language['languageID'], $visibleLanguages)) {
				unset($availableLanguages[$key]);
			}
		}
		
		return $availableLanguages;
	}
	
	/**
	 * Sets the language object and the template languageID to the thread language
	 * and back to the users interface language
	 * 
	 * @param	integer		$languageID
	 */
	protected function setLanguage($languageID, $reset = false) {
		if (!$reset) {
			$this->userInterfaceLanguageID = WCF::getLanguage()->getLanguageID();
		}
		
		WCF::setLanguage($languageID);
		WCF::getTPL()->setLanguageID($languageID);
	}
}
?>