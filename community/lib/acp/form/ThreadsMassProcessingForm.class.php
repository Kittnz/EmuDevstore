<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');

// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/system/database/ConditionBuilder.class.php');
require_once(WCF_DIR.'lib/system/language/Language.class.php');
require_once(WCF_DIR.'lib/page/util/InlineCalendar.class.php');

/**
 * Shows the threads mass processing form.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.form
 * @category 	Burning Board
 */
class ThreadsMassProcessingForm extends ACPForm {
	// system
	public $templateName = 'threadsMassProcessing';
	public $activeMenuItem = 'wbb.acp.menu.link.content.threadsAndPosts.massProcessing';
	public $neededPermissions = 'admin.board.canEditBoard';
	
	/**
	 * list of available boards
	 * 
	 * @var	array
	 */
	public $boardOptions = array();
	
	/**
	 * list of available languages
	 * 
	 * @var	array
	 */
	public $languages = array();
	
	/**
	 * list of available actions
	 * 
	 * @var	array
	 */
	public $availableActions = array('move', 'trash', 'delete', 'restore', 'disable', 'enable', 'close', 'open', 'deleteSubscriptions', 'deleteLinks', 'changeLanguage', 'changePrefix');
	
	/**
	 * number of affected threads
	 * 
	 * @var	integer
	 */
	public $affectedThreads = 0;
	
	/**
	 * condition builder object
	 * 
	 * @var	ConditionBuilder
	 */
	public $conditions;
	
	// form parameters
	public $timeAfterDay = 0;
	public $timeAfterMonth = 0;
	public $timeAfterYear = '';
	public $timeBeforeDay = 0;
	public $timeBeforeMonth = 0;
	public $timeBeforeYear = '';
	public $lastPostTimeAfterDay = 0;
	public $lastPostTimeAfterMonth = 0;
	public $lastPostTimeAfterYear = '';
	public $lastPostTimeBeforeDay = 0;
	public $lastPostTimeBeforeMonth = 0;
	public $lastPostTimeBeforeYear = '';
	public $repliesMoreThan = '', $repliesLessThan = '';
	public $createdBy = '', $postsBy = '';
	public $deleted = 0, $notDeleted = 0, $disabled = 0, $notDisabled = 0, $closed = 0, $open = 0;
	public $redirect = 0, $notRedirect = 0, $announcement = 0, $sticky = 0, $normal = 0;
	public $prefix = '';
	public $boardIDs = array();
	public $moveTo = 0;
	public $languageIDs = array();
	public $newLanguageID = 0;
	public $newPrefix = '';
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		// time
		if (isset($_POST['timeAfterDay'])) $this->timeAfterDay = intval($_POST['timeAfterDay']);
		if (isset($_POST['timeAfterMonth'])) $this->timeAfterMonth = intval($_POST['timeAfterMonth']);
		if (!empty($_POST['timeAfterYear'])) $this->timeAfterYear = intval($_POST['timeAfterYear']);
		if (isset($_POST['timeBeforeDay'])) $this->timeBeforeDay = intval($_POST['timeBeforeDay']);
		if (isset($_POST['timeBeforeMonth'])) $this->timeBeforeMonth = intval($_POST['timeBeforeMonth']);
		if (!empty($_POST['timeBeforeYear'])) $this->timeBeforeYear = intval($_POST['timeBeforeYear']);
		
		// last post time
		if (isset($_POST['lastPostTimeAfterDay'])) $this->lastPostTimeAfterDay = intval($_POST['lastPostTimeAfterDay']);
		if (isset($_POST['lastPostTimeAfterMonth'])) $this->lastPostTimeAfterMonth = intval($_POST['lastPostTimeAfterMonth']);
		if (!empty($_POST['lastPostTimeAfterYear'])) $this->lastPostTimeAfterYear = intval($_POST['lastPostTimeAfterYear']);
		if (isset($_POST['lastPostTimeBeforeDay'])) $this->lastPostTimeBeforeDay = intval($_POST['lastPostTimeBeforeDay']);
		if (isset($_POST['lastPostTimeBeforeMonth'])) $this->lastPostTimeBeforeMonth = intval($_POST['lastPostTimeBeforeMonth']);
		if (!empty($_POST['lastPostTimeBeforeYear'])) $this->lastPostTimeBeforeYear = intval($_POST['lastPostTimeBeforeYear']);
		
		// replies
		if (isset($_POST['repliesMoreThan']) && $_POST['repliesMoreThan'] !== '') $this->repliesMoreThan = intval($_POST['repliesMoreThan']);
		if (isset($_POST['repliesLessThan']) && $_POST['repliesLessThan'] !== '') $this->repliesLessThan = intval($_POST['repliesLessThan']);
		
		if (isset($_POST['createdBy'])) $this->createdBy = StringUtil::trim($_POST['createdBy']);
		if (isset($_POST['postsBy'])) $this->postsBy = StringUtil::trim($_POST['postsBy']);
		if (isset($_POST['prefix'])) $this->prefix = StringUtil::trim($_POST['prefix']);
		if (isset($_POST['boardIDs']) && is_array($_POST['boardIDs'])) $this->boardIDs = ArrayUtil::toIntegerArray($_POST['boardIDs']);
				
		if (isset($_POST['deleted'])) $this->deleted = intval($_POST['deleted']);
		if (isset($_POST['notDeleted'])) $this->notDeleted = intval($_POST['notDeleted']);
		if (isset($_POST['disabled'])) $this->disabled = intval($_POST['disabled']);
		if (isset($_POST['notDisabled'])) $this->notDisabled = intval($_POST['notDisabled']);
		if (isset($_POST['closed'])) $this->closed = intval($_POST['closed']);
		if (isset($_POST['open'])) $this->open = intval($_POST['open']);
		if (isset($_POST['redirect'])) $this->redirect = intval($_POST['redirect']);
		if (isset($_POST['notRedirect'])) $this->notRedirect = intval($_POST['notRedirect']);
		if (isset($_POST['announcement'])) $this->announcement = intval($_POST['announcement']);
		if (isset($_POST['sticky'])) $this->sticky = intval($_POST['sticky']);
		if (isset($_POST['normal'])) $this->normal = intval($_POST['normal']);
		
		if (isset($_POST['moveTo'])) $this->moveTo = intval($_POST['moveTo']);
		if (isset($_POST['newPrefix'])) $this->newPrefix = StringUtil::trim($_POST['newPrefix']);
		
		// language
		if (isset($_POST['languageIDs']) && is_array($_POST['languageIDs'])) $this->languageIDs = ArrayUtil::toIntegerArray($_POST['languageIDs']);
		if (isset($_POST['newLanguageID'])) $this->newLanguageID = intval($_POST['newLanguageID']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();

		// action
		if (!in_array($this->action, $this->availableActions)) {
			throw new UserInputException('action');
		}
		
		// move to
		if ($this->action == 'move') {
			try {
				$board = Board::getBoard($this->moveTo);
			}
			catch (IllegalLinkException $e) {
				throw new UserInputException('moveTo');
			}
			
			if (!$board->isBoard()) {
				throw new UserInputException('moveTo');
			}
		}
		
		// validate new language
		if ($this->action == 'changeLanguage') {
			require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');
			$language = new LanguageEditor($this->newLanguageID);
			if (!$language->getLanguageID()) {
				throw new UserInputException('newLanguageID');
			}
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		// build conditions
		$this->conditions = new ConditionBuilder();
		
		parent::save();
		
		// time
		if ($this->timeAfterDay && $this->timeAfterMonth && $this->timeAfterYear) {
			$time = @gmmktime(0, 0, 0, $this->timeAfterMonth, $this->timeAfterDay, $this->timeAfterYear);
			if ($time !== false && $time !== -1) $this->conditions->add("time > ".$time);
		}
		if ($this->timeBeforeDay && $this->timeBeforeMonth && $this->timeBeforeYear) {
			$time = @gmmktime(0, 0, 0, $this->timeBeforeMonth, $this->timeBeforeDay, $this->timeBeforeYear);
			if ($time !== false && $time !== -1) $this->conditions->add("time < ".$time);
		}
		
		// last post time
		if ($this->lastPostTimeAfterDay && $this->lastPostTimeAfterMonth && $this->lastPostTimeAfterYear) {
			$time = @gmmktime(0, 0, 0, $this->lastPostTimeAfterMonth, $this->lastPostTimeAfterDay, $this->lastPostTimeAfterYear);
			if ($time !== false && $time !== -1) $this->conditions->add("lastPostTime > ".$time);
		}
		if ($this->lastPostTimeBeforeDay && $this->lastPostTimeBeforeMonth && $this->lastPostTimeBeforeYear) {
			$time = @gmmktime(0, 0, 0, $this->lastPostTimeBeforeMonth, $this->lastPostTimeBeforeDay, $this->lastPostTimeBeforeYear);
			if ($time !== false && $time !== -1) $this->conditions->add("lastPostTime < ".$time);
		}
		
		// replies
		if ($this->repliesMoreThan !== '') $this->conditions->add('replies > '.$this->repliesMoreThan);
		if ($this->repliesLessThan !== '') $this->conditions->add('replies < '.$this->repliesLessThan);
		
		// username
		if ($this->createdBy != '') {
			$users = preg_split('/\s*,\s*/', $this->createdBy, -1, PREG_SPLIT_NO_EMPTY);
			$users = array_map('escapeString', $users);
			$this->conditions->add("username IN ('".implode("','", $users)."')");
		}
		if ($this->postsBy != '') {
			$users = preg_split('/\s*,\s*/', $this->postsBy, -1, PREG_SPLIT_NO_EMPTY);
			$users = array_map('escapeString', $users);
			$this->conditions->add("threadID IN (SELECT DISTINCT threadID FROM wbb".WBB_N."_post WHERE username IN ('".implode("','", $users)."'))");
		}
		
		// prefix
		if ($this->prefix != '') $this->conditions->add("prefix = '".escapeString($this->prefix)."'");
		
		// boardIDs
		if (count($this->boardIDs)) $this->conditions->add("boardID IN (".implode(',', $this->boardIDs).")");
		
		// language ids
		if (count($this->languageIDs)) $this->conditions->add("languageID IN (".implode(',', $this->languageIDs).")");
		
		if ($this->deleted) $this->conditions->add("isDeleted = 1");
		if ($this->notDeleted) $this->conditions->add("isDeleted = 0");
		if ($this->disabled) $this->conditions->add("isDisabled = 1");
		if ($this->notDisabled) $this->conditions->add("isDisabled = 0");
		if ($this->closed) $this->conditions->add("isClosed = 1");
		if ($this->open) $this->conditions->add("isClosed = 0");
		if ($this->redirect) $this->conditions->add("movedThreadID <> 0");
		if ($this->notRedirect) $this->conditions->add("movedThreadID = 0");
		if ($this->announcement) $this->conditions->add("isAnnouncement = 1");
		if ($this->sticky) $this->conditions->add("isSticky = 1");
		if ($this->normal) $this->conditions->add("isAnnouncement = 0 AND isSticky = 0");
		
		// execute action
		$conditions = $this->conditions->get();
		switch ($this->action) {
			case 'move':
				$sql = "UPDATE	wbb".WBB_N."_thread
					SET	boardID = ".$this->moveTo."
					".$conditions;
				WCF::getDB()->sendQuery($sql);
				$this->affectedThreads = WCF::getDB()->getAffectedRows();
				break;
				
			case 'delete':
				$threadIDs = '';
				$sql = "SELECT	threadID
					FROM	wbb".WBB_N."_thread
					".$conditions;
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					if (!empty($threadIDs)) $threadIDs .= ',';
					$threadIDs .= $row['threadID'];
					$this->affectedThreads++;
				}
				
				require_once(WBB_DIR.'lib/data/thread/ThreadEditor.class.php');
				ThreadEditor::deleteAllCompletely($threadIDs);
				break;
			
			case 'trash':	
			case 'restore':
				$threadIDs = '';
				$sql = "SELECT	threadID
					FROM	wbb".WBB_N."_thread
					".$conditions;
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					if (!empty($threadIDs)) $threadIDs .= ',';
					$threadIDs .= $row['threadID'];
				}

				if (!empty($threadIDs)) {
					// trash/restore posts
					$sql = "UPDATE	wbb".WBB_N."_post
						SET	isDeleted = ".($this->action == 'trash' ? 1 : 0)."
							".($this->action == 'trash' ? ",deleteTime = ".TIME_NOW.", deletedBy = '".escapeString(WCF::getUser()->username)."', deletedByID = ".WCF::getUser()->userID : '')."
						WHERE	threadID IN (".$threadIDs.")";
					WCF::getDB()->sendQuery($sql);
	
					// trash/restore threads
					$sql = "UPDATE	wbb".WBB_N."_thread
						SET	isDeleted = ".($this->action == 'trash' ? 1 : 0)."
							".($this->action == 'trash' ? ",deleteTime = ".TIME_NOW.", deletedBy = '".escapeString(WCF::getUser()->username)."', deletedByID = ".WCF::getUser()->userID : '')."
						WHERE	threadID IN (".$threadIDs.")";
					WCF::getDB()->sendQuery($sql);
					$this->affectedThreads = WCF::getDB()->getAffectedRows();
				}
				break;
			
			case 'disable':
			case 'enable':
				$sql = "UPDATE	wbb".WBB_N."_thread
					SET	isDisabled = ".($this->action == 'disable' ? 1 : 0)."
					".$conditions;
				WCF::getDB()->sendQuery($sql);
				$this->affectedThreads = WCF::getDB()->getAffectedRows();
				break;
			
			case 'close':
			case 'open':
				$sql = "UPDATE	wbb".WBB_N."_thread
					SET	isClosed = ".($this->action == 'close' ? 1 : 0)."
					".$conditions;
				WCF::getDB()->sendQuery($sql);
				$this->affectedThreads = WCF::getDB()->getAffectedRows();
				break;
				
			case 'deleteSubscriptions':
				$sql = "DELETE FROM	wbb".WBB_N."_thread_subscription
					WHERE		threadID IN (
								SELECT	threadID
								FROM	wbb".WBB_N."_thread
								".$conditions."	
							)";
				WCF::getDB()->sendQuery($sql);
				$this->affectedThreads = WCF::getDB()->getAffectedRows();
				break;
				
			case 'changeLanguage':
				$sql = "UPDATE	wbb".WBB_N."_thread
					SET	languageID = ".$this->newLanguageID."
					".$conditions;
				WCF::getDB()->sendQuery($sql);
				$this->affectedThreads = WCF::getDB()->getAffectedRows();
				break;
				
			case 'changePrefix':
				$sql = "UPDATE	wbb".WBB_N."_thread
					SET	prefix = '".escapeString($this->newPrefix)."'
					".$conditions;
				WCF::getDB()->sendQuery($sql);
				$this->affectedThreads = WCF::getDB()->getAffectedRows();
				break;
				
			case 'deleteLinks':
				$threadIDs = '';
				$sql = "SELECT	threadID
					FROM	wbb".WBB_N."_thread
					".$conditions;
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					if (!empty($threadIDs)) $threadIDs .= ',';
					$threadIDs .= $row['threadID'];
					$this->affectedThreads++;
				}
				
				if (!empty($threadIDs)) {
					$sql = "DELETE FROM	wbb".WBB_N."_thread
						WHERE		movedThreadID IN (".$threadIDs.")";
					WCF::getDB()->sendQuery($sql);
					$this->affectedThreads = WCF::getDB()->getAffectedRows();
				}
				break;
		}
		$this->saved();
		
		WCF::getTPL()->assign('affectedThreads', $this->affectedThreads);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// boards
		$this->boardOptions = Board::getBoardSelect(array(), true, true);
		
		// language
		$this->languages = Language::getAvailableContentLanguages();
		foreach ($this->languages as $languageID => $language) {
			$this->languages[$languageID] = WCF::getLanguage()->get('wcf.global.language.'.$language['languageCode']);
		}
		StringUtil::sort($this->languages);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		InlineCalendar::assignVariables();
		WCF::getTPL()->assign(array(
			'timeAfterDay' => $this->timeAfterDay,
			'timeAfterMonth' => $this->timeAfterMonth,
			'timeAfterYear' => $this->timeAfterYear,
			'timeBeforeDay' => $this->timeBeforeDay,
			'timeBeforeMonth' => $this->timeBeforeMonth,
			'timeBeforeYear' => $this->timeBeforeYear,
			'lastPostTimeAfterDay' => $this->lastPostTimeAfterDay,
			'lastPostTimeAfterMonth' => $this->lastPostTimeAfterMonth,
			'lastPostTimeAfterYear' => $this->lastPostTimeAfterYear,
			'lastPostTimeBeforeDay' => $this->lastPostTimeBeforeDay,
			'lastPostTimeBeforeMonth' => $this->lastPostTimeBeforeMonth,
			'lastPostTimeBeforeYear' => $this->lastPostTimeBeforeYear,
			'repliesMoreThan' => $this->repliesMoreThan,
			'repliesLessThan' => $this->repliesLessThan,
			'createdBy' => $this->createdBy,
			'postsBy' => $this->postsBy,
			'deleted' => $this->deleted,
			'notDeleted' => $this->notDeleted,
			'disabled' => $this->disabled,
			'notDisabled' => $this->notDisabled,
			'closed' => $this->closed,
			'open' => $this->open,
			'redirect' => $this->redirect,
			'notRedirect' => $this->notRedirect,
			'announcement' => $this->announcement,
			'sticky' => $this->sticky,
			'normal' => $this->normal,
			'prefix' => $this->prefix,
			'boardIDs' => $this->boardIDs,
			'boardOptions' => $this->boardOptions,
			'moveTo' => $this->moveTo,
			'languages' => $this->languages,
			'languageIDs' => $this->languageIDs,
			'newLanguageID' => $this->newLanguageID,
			'newPrefix' => $this->newPrefix
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
?>