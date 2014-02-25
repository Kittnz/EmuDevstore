<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');
require_once(WBB_DIR.'lib/data/thread/ThreadEditor.class.php');
require_once(WBB_DIR.'lib/data/post/PostEditor.class.php');
				
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/system/database/ConditionBuilder.class.php');
require_once(WCF_DIR.'lib/system/language/Language.class.php');
require_once(WCF_DIR.'lib/page/util/InlineCalendar.class.php');

/**
 * Shows the posts mass processing form.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.form
 * @category 	Burning Board
 */
class PostsMassProcessingForm extends ACPForm {
	// system
	public $templateName = 'postsMassProcessing';
	public $activeMenuItem = 'wbb.acp.menu.link.content.threadsAndPosts.postsMassProcessing';
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
	public $availableActions = array('trash', 'delete', 'restore', 'disable', 'enable', 'close', 'open');
	
	/**
	 * number of affected posts
	 * 
	 * @var	integer
	 */
	public $affectedPosts = 0;
	
	/**
	 * condition builder object
	 * 
	 * @var	ConditionBuilder
	 */
	public $conditions = null;
	
	// form parameters
	public $timeAfterDay = 0;
	public $timeAfterMonth = 0;
	public $timeAfterYear = '';
	public $timeBeforeDay = 0;
	public $timeBeforeMonth = 0;
	public $timeBeforeYear = '';
	public $createdBy = '';
	public $deleted = 0, $notDeleted = 0, $disabled = 0, $notDisabled = 0, $closed = 0, $open = 0;
	public $boardIDs = array();
	
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
		
		if (isset($_POST['createdBy'])) $this->createdBy = StringUtil::trim($_POST['createdBy']);
		if (isset($_POST['boardIDs']) && is_array($_POST['boardIDs'])) $this->boardIDs = ArrayUtil::toIntegerArray($_POST['boardIDs']);
				
		if (isset($_POST['deleted'])) $this->deleted = intval($_POST['deleted']);
		if (isset($_POST['notDeleted'])) $this->notDeleted = intval($_POST['notDeleted']);
		if (isset($_POST['disabled'])) $this->disabled = intval($_POST['disabled']);
		if (isset($_POST['notDisabled'])) $this->notDisabled = intval($_POST['notDisabled']);
		if (isset($_POST['closed'])) $this->closed = intval($_POST['closed']);
		if (isset($_POST['open'])) $this->open = intval($_POST['open']);
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
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		// build conditions
		$this->conditions = new ConditionBuilder();
		
		parent::save();
		
		// boardIDs
		if (count($this->boardIDs)) $this->conditions->add("threadID IN (SELECT threadID FROM wbb".WBB_N."_thread WHERE boardID IN (".implode(',', $this->boardIDs)."))");
		
		// time
		if ($this->timeAfterDay && $this->timeAfterMonth && $this->timeAfterYear) {
			$time = @gmmktime(0, 0, 0, $this->timeAfterMonth, $this->timeAfterDay, $this->timeAfterYear);
			if ($time !== false && $time !== -1) $this->conditions->add("time > ".$time);
		}
		if ($this->timeBeforeDay && $this->timeBeforeMonth && $this->timeBeforeYear) {
			$time = @gmmktime(0, 0, 0, $this->timeBeforeMonth, $this->timeBeforeDay, $this->timeBeforeYear);
			if ($time !== false && $time !== -1) $this->conditions->add("time < ".$time);
		}
		
		// username
		if ($this->createdBy != '') {
			$users = preg_split('/\s*,\s*/', $this->createdBy, -1, PREG_SPLIT_NO_EMPTY);
			$users = array_map('escapeString', $users);
			$this->conditions->add("username IN ('".implode("','", $users)."')");
		}
		
		// status
		if ($this->deleted) $this->conditions->add("isDeleted = 1");
		if ($this->notDeleted) $this->conditions->add("isDeleted = 0");
		if ($this->disabled) $this->conditions->add("isDisabled = 1");
		if ($this->notDisabled) $this->conditions->add("isDisabled = 0");
		if ($this->closed) $this->conditions->add("isClosed = 1");
		if ($this->open) $this->conditions->add("isClosed = 0");
		
		// execute action
		$conditions = $this->conditions->get();
		switch ($this->action) {
			case 'delete':
				$postIDs = '';
				$sql = "SELECT	postID
					FROM	wbb".WBB_N."_post
					".$conditions;
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					if (!empty($postIDs)) $postIDs .= ',';
					$postIDs .= $row['postID'];
					$this->affectedPosts++;
				}
				
				// get thread ids
				$threadIDs = PostEditor::getThreadIDs($postIDs);
				
				// delete posts
				PostEditor::deleteAllCompletely($postIDs);
				
				// check threads
				ThreadEditor::checkVisibilityAll($threadIDs);
				break;
			
			case 'trash':	
			case 'restore':
				$sql = "UPDATE	wbb".WBB_N."_post
					SET	isDeleted = ".($this->action == 'trash' ? 1 : 0)."
						".($this->action == 'trash' ? ",deleteTime = ".TIME_NOW.", deletedBy = '".escapeString(WCF::getUser()->username)."', deletedByID = ".WCF::getUser()->userID : '')."
					".$conditions;
				WCF::getDB()->sendQuery($sql);
				$this->affectedPosts = WCF::getDB()->getAffectedRows();
				break;
			
			case 'disable':
			case 'enable':
				$sql = "UPDATE	wbb".WBB_N."_post
					SET	isDisabled = ".($this->action == 'disable' ? 1 : 0)."
					".$conditions;
				WCF::getDB()->sendQuery($sql);
				$this->affectedPosts = WCF::getDB()->getAffectedRows();
				break;
			
			case 'close':
			case 'open':
				$sql = "UPDATE	wbb".WBB_N."_post
					SET	isClosed = ".($this->action == 'close' ? 1 : 0)."
					".$conditions;
				WCF::getDB()->sendQuery($sql);
				$this->affectedPosts = WCF::getDB()->getAffectedRows();
				break;
		}
		$this->saved();
		
		WCF::getTPL()->assign('affectedPosts', $this->affectedPosts);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// boards
		$this->boardOptions = Board::getBoardSelect(array(), true, true);
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
			'createdBy' => $this->createdBy,
			'deleted' => $this->deleted,
			'notDeleted' => $this->notDeleted,
			'disabled' => $this->disabled,
			'notDisabled' => $this->notDisabled,
			'closed' => $this->closed,
			'open' => $this->open,
			'boardIDs' => $this->boardIDs,
			'boardOptions' => $this->boardOptions
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