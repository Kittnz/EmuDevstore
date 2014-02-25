<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');

// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');

/**
 * Shows the group to boards permissions list.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.form
 * @category 	Burning Board
 */
class GroupPermissionsEditForm extends ACPForm {
	// system
	public $templateName = 'permissionsEdit';
	public $activeMenuItem = 'wcf.acp.menu.link.group';
	public $neededPermissions = 'admin.board.canEditPermissions';
	
	/**
	 * user group id
	 * 
	 * @var	integer
	 */
	public $groupID = 0;
	
	/**
	 * user group editor object
	 * 
	 * @var	GroupEditor
	 */
	public $group = null;
	
	/**
	 * list of available user groups
	 * 
	 * @var	array
	 */
	public $groups = array();
	
	/**
	 * existing board structure
	 * 
	 * @var	array
	 */
	public $boardStructure = null;
	
	/**
	 * list of existing boards
	 * 
	 * @var	array
	 */
	public $boards = null;
	
	/**
	 * structured board list
	 * 
	 * @var	array
	 */
	public $boardList = array();
	
	/**
	 * list of board permissions
	 * 
	 * @var	array
	 */
	public $boardPermissions = array();
	
	/**
	 * list of available permissions
	 * 
	 * @var	array
	 */
	public $permissionSettings = null;
	
	/**
	 * name of selected permission
	 * 
	 * @var	string
	 */
	public $permissionName = '';
	
	/**
	 * list of global permissions
	 * 
	 * @var	array
	 */
	public $globalPermissions = array();
	
	/**
	 * list of active board permissions
	 * 
	 * @var	array
	 */
	public $activeBoardPermissions = array();
	
	/**
	 * list of closed categories
	 * 
	 * @var	array
	 */
	public $closedCategories = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get group
		if (isset($_REQUEST['groupID'])) {
			$this->groupID = intval($_REQUEST['groupID']);
			require_once(WCF_DIR.'lib/data/user/group/GroupEditor.class.php');
			$this->group = new GroupEditor($this->groupID);
			if (!$this->group->groupID) {
				throw new IllegalLinkException();
			}
			if (!$this->group->isAccessible()) {
				throw new PermissionDeniedException();
			}
		}
		
		// active permission
		if (isset($_REQUEST['permissionName'])) $this->permissionName = $_REQUEST['permissionName'];
		
		$this->readPermissionSettings();
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['boardPermissions']) && is_array($_POST['boardPermissions'])) $this->boardPermissions = $_POST['boardPermissions'];
	}
	
	/**
	 * Validates the given permissions.
	 */
	public function validatePermissions() {
		$validPermissions = array_flip($this->permissionSettings);
		
		foreach ($this->boardPermissions as $boardID => $permissions) {
			foreach ($permissions as $key => $value) {
				if (!isset($validPermissions[$key])) {
					unset($this->boardPermissions[$boardID][$key]);
				}
				
				if (($value != -1 && $value != 0 && $value =! 1)) {
					throw new UserInputException();
				}
			}
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		$this->validatePermissions();
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		$inserts = $fields = '';
		foreach ($this->permissionSettings as $name) {
			$fields .= ', '.$name;
		}
		
		foreach ($this->boardPermissions as $boardID => $permissions) {
			$noDefaultValue = false;
			foreach ($permissions as $value) {
				if ($value != -1) $noDefaultValue = true;
			}
			if (!$noDefaultValue) continue;
			
			if (!empty($inserts)) $inserts .= ',';
			$inserts .= '('.intval($boardID).', '.$this->groupID;
			foreach ($this->permissionSettings as $name) {
				$inserts .= ', '.(isset($permissions[$name]) ? $permissions[$name] : -1);
			}
			$inserts .= ')';
		}
		
		// delete old entries
		$sql = "DELETE FROM	wbb".WBB_N."_board_to_group
			WHERE		groupID = ".$this->groupID;
		WCF::getDB()->sendQuery($sql);
			
		if (!empty($inserts)) {
			$sql = "INSERT IGNORE INTO	wbb".WBB_N."_board_to_group
							(boardID, groupID".$fields.")
				VALUES			".$inserts;
			WCF::getDB()->sendQuery($sql);
		}
		
		// reset permissions cache
		WCF::getCache()->clear(WBB_DIR . 'cache/', 'cache.boardPermissions-*', true);
		// reset sessions
		Session::resetSessions(array(), true, false);
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readClosedCategories();
		$this->groups = Group::getAllGroups();
		$this->readBoardPermissions();
		$this->loadGlobalPermissions();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$this->renderBoards();
		WCF::getTPL()->assign(array(
			'boardStructure' => $this->boardStructure,
			'boards' => $this->boardList,
			'groupID' => $this->groupID,
			'group' => $this->group,
			'globalPermissions' => $this->globalPermissions,
			'boardPermissions' => $this->activeBoardPermissions,
			'type' => 'group',
			'permissionName' => $this->permissionName,
			'groups' => $this->groups,
			'availablePermissions' => $this->permissionSettings
		));
	}
	
	/**
	 * Gets the list of closed categories.
	 */
	protected function readClosedCategories() {
		$sql = "SELECT	boardID
			FROM	wbb".WBB_N."_board_closed_category_to_admin
			WHERE	userID = ".WCF::getUser()->userID;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->closedCategories[$row['boardID']] = $row['boardID'];
		}
	}
	
	/**
	 * Gets a list of board permissions.
	 */
	protected function readBoardPermissions() {
		$sql = "SELECT		*
			FROM		wbb".WBB_N."_board_to_group
			WHERE		groupID = ".$this->groupID."
			ORDER BY	boardID";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$boardID = $row['boardID'];
			unset($row['boardID'], $row['groupID']);
			$this->activeBoardPermissions[$boardID] = $row;
		}
	}
	
	/**
	 * Renders the ordered list of all boards.
	 */
	protected function renderBoards() {
		// get board structure from cache		
		$this->boardStructure = WCF::getCache()->get('board', 'boardStructure');
		// get boards from cache
		$this->boards = WCF::getCache()->get('board', 'boards');
				
		$this->makeBoardList();
	}
	
	/**
	 * Renders one level of the board structure.
	 *
	 * @param	integer		parentID		render the subboards of the board with the given id
	 * @param	integer		depth			the depth of the current level
	 * @param	integer		openParents		helping variable for rendering the html list in the boardlist template
	 */
	protected function makeBoardList($parentID = 0, $depth = 1, $openParents = 0) {
		if (!isset($this->boardStructure[$parentID])) return;
		
		$i = 0; $children = count($this->boardStructure[$parentID]);
		foreach ($this->boardStructure[$parentID] as $boardID) {
			$board = $this->boards[$boardID];
			
			// boardlist depth on index
			$childrenOpenParents = $openParents + 1;
			$hasChildren = isset($this->boardStructure[$boardID]);
			$last = $i == count($this->boardStructure[$parentID]) - 1;
			if ($hasChildren && !$last) $childrenOpenParents = 1;
			$this->boardList[] = array('depth' => $depth, 'hasChildren' => $hasChildren, 'openParents' => ((!$hasChildren && $last) ? ($openParents) : (0)), 'board' => $board, 'parentID' => $parentID, 'position' => $i+1, 'maxPosition' => $children, 'open' => (!isset($this->closedCategories[$boardID]) ? 1 : 0));
			
			// make next level of the board list
			$this->makeBoardList($boardID, $depth + 1, $childrenOpenParents);
			
			$i++;
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
	 * Gets a list of all global permissions.
	 */
	protected function loadGlobalPermissions() {
		$this->globalPermissions = array(
			'canViewBoard' => $this->group->getGroupOption('user.board.canViewBoard'),
			'canEnterBoard' => $this->group->getGroupOption('user.board.canEnterBoard'),
			'canReadThread' => $this->group->getGroupOption('user.board.canReadThread'),
			'canReadOwnThread' => $this->group->getGroupOption('user.board.canReadOwnThread'),
			'canStartThread' => $this->group->getGroupOption('user.board.canStartThread'),
			'canReplyThread' => $this->group->getGroupOption('user.board.canReplyThread'),
			'canReplyOwnThread' => $this->group->getGroupOption('user.board.canReplyOwnThread'),
			'canStartThreadWithoutModeration' => $this->group->getGroupOption('user.board.canStartThreadWithoutModeration'),
			'canReplyThreadWithoutModeration' => $this->group->getGroupOption('user.board.canReplyThreadWithoutModeration'),
			'canRateThread' => $this->group->getGroupOption('user.board.canRateThread'),
			'canUsePrefix' => $this->group->getGroupOption('user.board.canUsePrefix'),
			'canDeleteOwnPost' => $this->group->getGroupOption('user.board.canDeleteOwnPost'),
			'canEditOwnPost' => $this->group->getGroupOption('user.board.canEditOwnPost')
		);
		
		if (MODULE_THREAD_MARKING_AS_DONE) {
			$this->globalPermissions['canMarkAsDoneOwnThread'] = $this->group->getGroupOption('user.board.canMarkAsDoneOwnThread');
		}
		
		if (MODULE_TAGGING) {
			$this->globalPermissions['canSetTags'] = $this->group->getGroupOption('user.board.canSetTags');
		}
		
		if (MODULE_ATTACHMENT) {
			$this->globalPermissions['canUploadAttachment'] = $this->group->getGroupOption('user.board.canUploadAttachment');
			$this->globalPermissions['canDownloadAttachment'] = $this->group->getGroupOption('user.board.canDownloadAttachment');
			$this->globalPermissions['canViewAttachmentPreview'] = $this->group->getGroupOption('user.board.canViewAttachmentPreview');
		}
		
		if (MODULE_POLL) {
			$this->globalPermissions['canStartPoll'] = $this->group->getGroupOption('user.board.canStartPoll');
			$this->globalPermissions['canVotePoll'] = $this->group->getGroupOption('user.board.canVotePoll');
		}
	}
}
?>