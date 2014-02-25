<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/BoardEditor.class.php');

// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/user/User.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
require_once(WCF_DIR.'lib/system/session/Session.class.php');
require_once(WCF_DIR.'lib/system/style/StyleManager.class.php');

/**
 * Shows the board add form.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.form
 * @category 	Burning Board
 */
class BoardAddForm extends ACPForm {
	// system
	public $templateName = 'boardAdd';
	public $activeMenuItem = 'wbb.acp.menu.link.content.board.add';
	public $neededPermissions = 'admin.board.canAddBoard';
	public $activeTabMenuItem = 'data';
	
	/**
	 * board editor object
	 * 
	 * @var	BoardEditor
	 */
	public $board;
	
	/**
	 * list of available permisions
	 * 
	 * @var	array
	 */
	public $permissionSettings = array();
	
	/**
	 * list of available moderator permisions
	 * 
	 * @var	array
	 */
	public $moderatorSettings = array();
	
	/**
	 * list of available parent boards
	 * 
	 * @var	array
	 */
	public $boardOptions = array();
	
	/**
	 * list of available styles
	 * 
	 * @var	array
	 */
	public $availableStyles = array();
	
	/**
	 * list of additional fields
	 * 
	 * @var	array
	 */
	public $additionalFields = array();
	
	// parameters
	public $boardType = 0;
	public $parentID = 0;
	public $position = '';
	public $title = '';
	public $description = '';
	public $allowDescriptionHtml = 0;
	public $image = '';
	public $imageNew = '';
	public $imageShowAsBackground = 1;
	public $imageBackgroundRepeat = 'no';
	public $externalURL = '';
	public $prefixes = '';
	public $prefixRequired = 0;
	public $styleID = 0;
	public $enforceStyle = 0;
	public $daysPrune = 0;
	public $sortField = '';
	public $sortOrder = '';
	public $postSortOrder = '';
	public $closed = 0;
	public $countUserPosts = 1;
	public $invisible = 0;
	public $showSubBoards = 1;
	public $permissions = array();
	public $moderators = array();
	public $enableRating = -1;
	public $threadsPerPage = 0;
	public $postsPerPage = 0;
	public $prefixMode = 0;
	public $searchable = 1;
	public $searchableForSimilarThreads = 1;
	public $ignorable = 1;
	public $enableMarkingAsDone = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->readModeratorSettings();
		$this->readPermissionSettings();
		if (isset($_REQUEST['parentID'])) $this->parentID = intval($_REQUEST['parentID']);
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->prefixRequired = $this->enforceStyle = $this->closed = $this->imageShowAsBackground = 0;
		$this->countUserPosts = $this->invisible = $this->showSubBoards = $this->allowDescriptionHtml = 0;
		$this->searchable = $this->searchableForSimilarThreads = $this->ignorable = 0;
		
		if (isset($_POST['boardType'])) $this->boardType = intval($_POST['boardType']);
		if (!empty($_POST['position'])) $this->position = intval($_POST['position']);
		if (isset($_POST['title'])) $this->title = StringUtil::trim($_POST['title']);
		if (isset($_POST['description'])) $this->description = StringUtil::trim($_POST['description']);
		if (isset($_POST['allowDescriptionHtml'])) $this->allowDescriptionHtml = intval($_POST['allowDescriptionHtml']);
		if (isset($_POST['image'])) $this->image = StringUtil::trim($_POST['image']);
		if (isset($_POST['externalURL'])) $this->externalURL = StringUtil::trim($_POST['externalURL']);
		if (isset($_POST['prefixes'])) $this->prefixes = StringUtil::trim($_POST['prefixes']);
		if (isset($_POST['prefixRequired'])) $this->prefixRequired = intval($_POST['prefixRequired']);
		if (isset($_POST['styleID'])) $this->styleID = intval($_POST['styleID']);
		if (isset($_POST['enforceStyle'])) $this->enforceStyle = intval($_POST['enforceStyle']);
		if (isset($_POST['daysPrune'])) $this->daysPrune = intval($_POST['daysPrune']);
		if (isset($_POST['sortField'])) $this->sortField = $_POST['sortField'];
		if (isset($_POST['sortOrder'])) $this->sortOrder = $_POST['sortOrder'];
		if (isset($_POST['postSortOrder'])) $this->postSortOrder = $_POST['postSortOrder'];
		if (isset($_POST['closed'])) $this->closed = intval($_POST['closed']);
		if (isset($_POST['countUserPosts'])) $this->countUserPosts = intval($_POST['countUserPosts']);
		if (isset($_POST['invisible'])) $this->invisible = intval($_POST['invisible']);
		if (isset($_POST['showSubBoards'])) $this->showSubBoards = intval($_POST['showSubBoards']);
		if (isset($_POST['activeTabMenuItem'])) $this->activeTabMenuItem = $_POST['activeTabMenuItem'];
		if (isset($_POST['enableRating'])) $this->enableRating = intval($_POST['enableRating']);
		if (isset($_POST['threadsPerPage'])) $this->threadsPerPage = intval($_POST['threadsPerPage']);
		if (isset($_POST['postsPerPage'])) $this->postsPerPage = intval($_POST['postsPerPage']);
		if (isset($_POST['prefixMode'])) $this->prefixMode = intval($_POST['prefixMode']);
		if (isset($_POST['imageNew'])) $this->imageNew = StringUtil::trim($_POST['imageNew']);
		if (isset($_POST['imageShowAsBackground'])) $this->imageShowAsBackground = intval($_POST['imageShowAsBackground']);
		if (isset($_POST['imageBackgroundRepeat'])) $this->imageBackgroundRepeat = $_POST['imageBackgroundRepeat'];
		if (isset($_POST['searchable'])) $this->searchable = intval($_POST['searchable']);
		if (isset($_POST['searchableForSimilarThreads'])) $this->searchableForSimilarThreads = intval($_POST['searchableForSimilarThreads']);
		if (isset($_POST['ignorable'])) $this->ignorable = intval($_POST['ignorable']);
		if (isset($_POST['enableMarkingAsDone'])) $this->enableMarkingAsDone = intval($_POST['enableMarkingAsDone']);
		
		// permissions
		if (isset($_POST['permission']) && is_array($_POST['permission'])) $this->permissions = $_POST['permission'];
		if (isset($_POST['moderator']) && is_array($_POST['moderator'])) $this->moderators = $_POST['moderator'];
	}
	
	/**
	 * Validates the given permissions.
	 */
	public function validatePermissions($permissions, $validSettings) {
		foreach ($permissions as $permission) {
			// type
			if (!isset($permission['type']) || ($permission['type'] != 'user' && $permission['type'] != 'group')) {
				throw new UserInputException();
			}
			
			// id
			if (!isset($permission['id'])) {
				throw new UserInputException();
			}
			if ($permission['type'] == 'user') {
				$user = new User(intval($permission['id']));
				if (!$user->userID) throw new UserInputException();
			}
			else {
				$group = new Group(intval($permission['id']));
				if (!$group->groupID) throw new UserInputException();
			}
			
			// settings
			if (!isset($permission['settings']) || !is_array($permission['settings'])) {
				throw new UserInputException();
			}
			// find invalid settings
			foreach ($permission['settings'] as $key => $value) {
				if (!isset($validSettings[$key]) || ($value != -1 && $value != 0 && $value =! 1)) {
					throw new UserInputException();
				}
			}
			// find missing settings
			foreach ($validSettings as $key => $value) {
				if (!isset($permission['settings'][$key])) {
					throw new UserInputException();
				}
			}
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		// validate permissions
		$this->validatePermissions($this->permissions, array_flip($this->permissionSettings));
		$this->validatePermissions($this->moderators, array_flip($this->moderatorSettings));
		
		parent::validate();
		
		// board type
		if ($this->boardType < 0 || $this->boardType > 2) {
			throw new UserInputException('boardType', 'invalid');
		}
		
		// parent id
		$this->validateParentID();
		
		// position
		/*if (!$this->position) {
			throw new UserInputException('position');
		}*/
		
		// title
		if (empty($this->title)) {
			throw new UserInputException('title');
		}
		
		// external url
		if ($this->boardType == 2 && empty($this->externalURL)) {
			throw new UserInputException('externalURL');
		}
		
		// prefix
		if ($this->boardType == 0 && $this->prefixRequired && $this->prefixMode == 2 && empty($this->prefixes)) {
			throw new UserInputException('prefixes');
		}
		
		// sortField
		switch ($this->sortField) {
			case '': case 'prefix': case 'topic': case 'attachments': case 'polls': case 'username': 
			case 'time': case 'ratingResult': case 'replies': case 'views': case 'lastPostTime': break;
			default: throw new UserInputException('sortField', 'invalid');
		}
		
		// sortOrder
		switch ($this->sortOrder) {
			case '': case 'ASC': case 'DESC': break;
			default: throw new UserInputException('sortOrder', 'invalid');
		}
		
		// postSortOrder
		switch ($this->postSortOrder) {
			case '': case 'ASC': case 'DESC': break;
			default: throw new UserInputException('postSortOrder', 'invalid');
		}
	}
	
	/**
	 * Validates the given parent id.
	 */
	protected function validateParentID() {
		if ($this->parentID) {
			try {
				Board::getBoard($this->parentID);
			}
			catch (IllegalLinkException $e) {
				throw new UserInputException('parentID', 'invalid');
			}
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save board
		if (WCF::getUser()->getPermission('admin.board.canAddBoard')) {
			$this->board = BoardEditor::create($this->parentID, ($this->position ? $this->position : null), $this->title, $this->description,
			$this->boardType, $this->image, $this->imageNew, $this->imageShowAsBackground, $this->imageBackgroundRepeat, $this->externalURL, TIME_NOW, $this->prefixes, $this->prefixMode,
			$this->prefixRequired, $this->styleID, $this->enforceStyle, $this->daysPrune,
			$this->sortField, $this->sortOrder, $this->postSortOrder, $this->closed, $this->countUserPosts,
			$this->invisible, $this->showSubBoards, $this->allowDescriptionHtml, $this->enableRating,
			$this->threadsPerPage, $this->postsPerPage, $this->searchable, $this->searchableForSimilarThreads, $this->ignorable,
			$this->enableMarkingAsDone,
			$this->additionalFields);
		}
		
		// save permissions
		if (WCF::getUser()->getPermission('admin.board.canEditPermissions')) {
			$this->savePermissions();
		}
		
		// save moderators
		if (WCF::getUser()->getPermission('admin.board.canEditModerators')) {
			$this->saveModerators();
		}
		
		// reset cache
		$this->resetCache();
		$this->saved();
		
		// reset values
		$this->boardType = $this->parentID = $this->prefixRequired = $this->styleID = $this->threadsPerPage = $this->postsPerPage = 0;
		$this->enforceStyle = $this->daysPrune = $this->closed = $this->invisible = $this->allowDescriptionHtml = $this->prefixMode = 0;
		$this->enableMarkingAsDone = 0;
		$this->countUserPosts = $this->showSubBoards = $this->imageShowAsBackground = $this->searchable = $this->searchableForSimilarThreads = $this->ignorable = 1;
		$this->position = $this->title = $this->description = $this->image = $this->imageNew = $this->externalURL = $this->prefixes = $this->sortField = $this->sortOrder = $this->postSortOrder = '';
		$this->permissions = $this->moderators = array();
		$this->enableRating = -1;
		$this->imageBackgroundRepeat = 'no-repeat';
		
		// show success message
		WCF::getTPL()->assign(array(
			'board' => $this->board,
			'success' => true
		));
	}
	
	/**
	 * Resets the board cache after changes.
	 */
	protected function resetCache() {
		Board::resetCache();
		
		// reset sessions
		Session::resetSessions(array(), true, false);
	}
	
	/**
	 * Saves user and group permissions.
	 */
	public function savePermissions() {
		// create inserts
		$userInserts = $groupInserts = '';
		foreach ($this->permissions as $key => $permission) {
			// skip default values
			$noDefaultValue = false;
			foreach ($permission['settings'] as $value) {
				if ($value != -1) $noDefaultValue = true;
			}
			if (!$noDefaultValue) {
				unset($this->permissions[$key]);
				continue;
			}
			
			if ($permission['type'] == 'user') {
				if (!empty($userInserts)) $userInserts .= ',';
				$userInserts .= '('.$this->board->boardID.',
						 '.intval($permission['id']).',
						 '.(implode(', ', ArrayUtil::toIntegerArray($permission['settings']))).')';
			
			}
			else {
				if (!empty($groupInserts)) $groupInserts .= ',';
				$groupInserts .= '('.$this->board->boardID.',
						 '.intval($permission['id']).',
						 '.(implode(', ', ArrayUtil::toIntegerArray($permission['settings']))).')';
			}
		}
	
		if (!empty($userInserts)) {
			$sql = "INSERT INTO	wbb".WBB_N."_board_to_user
						(boardID, userID, ".implode(', ', $this->permissionSettings).")
				VALUES		".$userInserts;
			WCF::getDB()->sendQuery($sql);
		}
		
		if (!empty($groupInserts)) {
			$sql = "INSERT INTO	wbb".WBB_N."_board_to_group
						(boardID, groupID, ".implode(', ', $this->permissionSettings).")
				VALUES		".$groupInserts;
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * Saves moderators.
	 */
	public function saveModerators() {
		// create inserts
		$inserts = '';
		foreach ($this->moderators as $moderator) {
			if (!empty($inserts)) $inserts .= ',';
			$inserts .= '	('.$this->board->boardID.',
					'.($moderator['type'] == 'user' ? intval($moderator['id']) : 0).',
					'.($moderator['type'] == 'group' ? intval($moderator['id']) : 0).',
					'.(implode(', ', ArrayUtil::toIntegerArray($moderator['settings']))).')';
		}
	
		if (!empty($inserts)) {
			$sql = "INSERT INTO	wbb".WBB_N."_board_moderator
						(boardID, userID, groupID, ".implode(', ', $this->moderatorSettings).")
				VALUES		".$inserts;
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readBoardOptions();
		$this->availableStyles = StyleManager::getAvailableStyles();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'boardType' => $this->boardType,
			'parentID' => $this->parentID,
			'position' => $this->position,
			'title' => $this->title,
			'description' => $this->description,
			'allowDescriptionHtml' => $this->allowDescriptionHtml,
			'image' => $this->image,
			'externalURL' => $this->externalURL,
			'prefixes' => $this->prefixes,
			'prefixRequired' => $this->prefixRequired,
			'styleID' => $this->styleID,
			'enforceStyle' => $this->enforceStyle,
			'daysPrune' => $this->daysPrune,
			'sortField' => $this->sortField,
			'sortOrder' => $this->sortOrder,
			'postSortOrder' => $this->postSortOrder,
			'closed' => $this->closed,
			'countUserPosts' => $this->countUserPosts,
			'invisible' => $this->invisible,
			'showSubBoards' => $this->showSubBoards,
			'boardOptions' => $this->boardOptions,
			'permissions' => $this->permissions,
			'moderators' => $this->moderators,
			'moderatorSettings' => $this->moderatorSettings,
			'permissionSettings' => $this->permissionSettings,
			'action' => 'add',
			'availableStyles' => $this->availableStyles,
			'activeTabMenuItem' => $this->activeTabMenuItem,
			'enableRating' => $this->enableRating,
			'threadsPerPage' => $this->threadsPerPage,
			'postsPerPage' => $this->postsPerPage,
			'prefixMode' => $this->prefixMode,
			'imageNew' => $this->imageNew,
			'imageShowAsBackground' => $this->imageShowAsBackground,
			'imageBackgroundRepeat' => $this->imageBackgroundRepeat,
			'searchable' => $this->searchable,
			'searchableForSimilarThreads' => $this->searchableForSimilarThreads,
			'ignorable' => $this->ignorable,
			'enableMarkingAsDone' => $this->enableMarkingAsDone
		));
	}
	
	/**
	 * Gets available moderator settings.
	 */
	protected function readModeratorSettings() {
		$sql = "SHOW COLUMNS FROM wbb".WBB_N."_board_moderator";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($row['Field'] != 'boardID' && $row['Field'] != 'userID' && $row['Field'] != 'groupID') {
				// check modules
				switch ($row['Field']) {
					case 'canMarkAsDoneThread': 
						if (!MODULE_THREAD_MARKING_AS_DONE) continue 2;
						break;
				}
				
				$this->moderatorSettings[] = $row['Field'];
			}
		}
	}
	
	/**
	 * Gets available permission settings.
	 */
	protected function readPermissionSettings() {
		$sql = "SHOW COLUMNS FROM wbb".WBB_N."_board_to_group";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($row['Field'] != 'boardID' && $row['Field'] != 'groupID') {
				// check modules
				switch ($row['Field']) {
					case 'canMarkAsDoneOwnThread': 
						if (!MODULE_THREAD_MARKING_AS_DONE) continue 2;
						break;
					
					case 'canSetTags':
						if (!MODULE_TAGGING) continue 2;
						break;
					
					case 'canUploadAttachment':
					case 'canDownloadAttachment':
					case 'canViewAttachmentPreview': 
						if (!MODULE_ATTACHMENT) continue 2;
						break;
					
					case 'canStartPoll':
					case 'canVotePoll': 
						if (!MODULE_POLL) continue 2;
						break;
				}
				
				$this->permissionSettings[] = $row['Field'];
			}
		}
	}
	
	/**
	 * Gets a list of available parent boards.
	 */
	protected function readBoardOptions() {
		$this->boardOptions = Board::getBoardSelect(array(), true, true);
	}
}
?>