<?php
// wbb imports
require_once(WBB_DIR.'lib/acp/form/BoardAddForm.class.php');

/**
 * Shows the board edit form.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.form
 * @category 	Burning Board
 */
class BoardEditForm extends BoardAddForm {
	// system
	public $activeMenuItem = 'wbb.acp.menu.link.content.board';
	public $neededPermissions = array('admin.board.canEditBoard', 'admin.board.canEditPermissions', 'admin.board.canEditModerators');
	
	/**
	 * board id
	 * 
	 * @var	integer
	 */
	public $boardID = 0;
	
	/**
	 * existing board structure
	 * 
	 * @var	array
	 */
	public static $boardStructure;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get board id
		if (isset($_REQUEST['boardID'])) $this->boardID = intval($_REQUEST['boardID']);
		
		// get board
		$this->board = new BoardEditor($this->boardID);
	}
	
	/**
	 * @see BoardAddForm::validateParentID()
	 */
	protected function validateParentID() {
		parent::validateParentID();
		
		if ($this->parentID) {
			if (self::$boardStructure === null) self::$boardStructure = WCF::getCache()->get('board', 'boardStructure');
			if ($this->boardID == $this->parentID || $this->searchChildren($this->boardID, $this->parentID)) {
				throw new UserInputException('parentID', 'invalid');
			}
		}
	}
	
	/**
	 * Searches for a board in the child tree of another board.
	 */
	protected function searchChildren($parentID, $searchedBoardID) {
		if (isset(self::$boardStructure[$parentID])) {
			foreach (self::$boardStructure[$parentID] as $boardID) {
				if ($boardID == $searchedBoardID) return true;
				if ($this->searchChildren($boardID, $searchedBoardID)) return true;
			}
		}
		
		return false;
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// save board
		if (WCF::getUser()->getPermission('admin.board.canEditBoard')) {
			// fix closed categories
			if ($this->board->isCategory() && $this->boardType != Board::TYPE_CATEGORY) {
				$sql = "DELETE FROM	wbb".WBB_N."_board_closed_category_to_user
					WHERE		boardID = ".$this->board->boardID;
				WCF::getDB()->sendQuery($sql);
				$sql = "DELETE FROM	wbb".WBB_N."_board_closed_category_to_admin
					WHERE		boardID = ".$this->board->boardID;
				WCF::getDB()->sendQuery($sql);
			}
			
			// update data
			$this->board->update($this->parentID, $this->title, $this->description,
				$this->boardType, $this->image, $this->imageNew, $this->imageShowAsBackground,
				$this->imageBackgroundRepeat, $this->externalURL, $this->prefixes, $this->prefixMode,
				$this->prefixRequired, $this->styleID, $this->enforceStyle, $this->daysPrune,
				$this->sortField, $this->sortOrder, $this->postSortOrder, $this->closed, $this->countUserPosts,
				$this->invisible, $this->showSubBoards, $this->allowDescriptionHtml, $this->enableRating,
				$this->threadsPerPage, $this->postsPerPage, $this->searchable, $this->searchableForSimilarThreads, $this->ignorable,
				$this->enableMarkingAsDone,
				$this->additionalFields);
			$this->board->removePositions();
			$this->board->addPosition($this->parentID, ($this->position ? $this->position : null));
			
			// fix ignored boards
			if (!$this->ignorable && $this->board->ignorable) {
				$unignorableBoardIDArray = array($this->boardID);
				$parentBoards = $this->board->getParentBoards();
				foreach ($parentBoards as $parentBoard) $unignorableBoardIDArray[] = $parentBoard->boardID;
				$sql = "DELETE FROM	wbb".WBB_N."_board_ignored_by_user
					WHERE		boardID IN (".implode(',', $unignorableBoardIDArray).")";
				WCF::getDB()->sendQuery($sql);
			}
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
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see BoardAddForm::savePermissions()
	 */
	public function savePermissions() {
		// delete old entries
		$sql = "DELETE FROM	wbb".WBB_N."_board_to_user
			WHERE		boardID = ".$this->boardID;
		WCF::getDB()->sendQuery($sql);
		$sql = "DELETE FROM	wbb".WBB_N."_board_to_group
			WHERE		boardID = ".$this->boardID;
		WCF::getDB()->sendQuery($sql);
	
		// save new entries
		parent::savePermissions();
	}
	
	/**
	 * @see BoardAddForm::saveModerators()
	 */
	public function saveModerators() {
		// delete old entries
		$sql = "DELETE FROM	wbb".WBB_N."_board_moderator
			WHERE		boardID = ".$this->boardID;
		WCF::getDB()->sendQuery($sql);
		
		// save new entries
		parent::saveModerators();
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			// default active tab item
			if (!WCF::getUser()->getPermission('admin.board.canEditBoard')) {
				if (WCF::getUser()->getPermission('admin.board.canEditPermissions')) $this->activeTabMenuItem = 'permissions';
				else $this->activeTabMenuItem = 'moderators';
			}
			
			// get values
			$this->boardType = $this->board->boardType;
			$this->parentID = $this->board->parentID;
			$this->title = $this->board->title;
			$this->description = $this->board->description;
			$this->image = $this->board->image;
			$this->externalURL = $this->board->externalURL;
			$this->prefixes = $this->board->prefixes;
			$this->prefixRequired = $this->board->prefixRequired;
			$this->styleID = $this->board->styleID;
			$this->enforceStyle = $this->board->enforceStyle;
			$this->daysPrune = $this->board->daysPrune;
			$this->sortField = $this->board->sortField;
			$this->sortOrder = $this->board->sortOrder;
			$this->postSortOrder = $this->board->postSortOrder;
			$this->closed = $this->board->isClosed;
			$this->countUserPosts = $this->board->countUserPosts;
			$this->invisible = $this->board->isInvisible;
			$this->showSubBoards = $this->board->showSubBoards;
			$this->allowDescriptionHtml = $this->board->allowDescriptionHtml;
			$this->enableRating = $this->board->enableRating;
			$this->threadsPerPage = $this->board->threadsPerPage;
			$this->postsPerPage = $this->board->postsPerPage;
			$this->prefixMode = $this->board->prefixMode;
			$this->imageNew = $this->board->imageNew;
			$this->imageShowAsBackground = $this->board->imageShowAsBackground;
			$this->imageBackgroundRepeat = $this->board->imageBackgroundRepeat;
			$this->searchable = $this->board->searchable;
			$this->searchableForSimilarThreads = $this->board->searchableForSimilarThreads;
			$this->ignorable = $this->board->ignorable;
			$this->enableMarkingAsDone = $this->board->enableMarkingAsDone;
			
			// get position
			$sql = "SELECT	position
				FROM	wbb".WBB_N."_board_structure
				WHERE	boardID = ".$this->boardID;
			$row = WCF::getDB()->getFirstRow($sql);
			if (isset($row['position'])) $this->position = $row['position'];
			
			// get permissions
			$sql = "		(SELECT		user_permission.*, user.userID AS id, 'user' AS type, user.username AS name
						FROM		wbb".WBB_N."_board_to_user user_permission
						LEFT JOIN	wcf".WCF_N."_user user
						ON		(user.userID = user_permission.userID)
						WHERE		boardID = ".$this->boardID.")
				UNION
						(SELECT		group_permission.*, usergroup.groupID AS id, 'group' AS type, usergroup.groupName AS name
						FROM		wbb".WBB_N."_board_to_group group_permission
						LEFT JOIN	wcf".WCF_N."_group usergroup
						ON		(usergroup.groupID = group_permission.groupID)
						WHERE		boardID = ".$this->boardID.")
				ORDER BY	name";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (empty($row['id'])) continue;
				$permission = array('name' => $row['name'], 'type' => $row['type'], 'id' => $row['id']);
				unset($row['name'], $row['userID'], $row['groupID'], $row['boardID'], $row['id'], $row['type']);
				foreach ($row as $key => $value) {
					if (!in_array($key, $this->permissionSettings)) unset($row[$key]);
				}
				$permission['settings'] = $row;
				$this->permissions[] = $permission;
			}
			
			// get moderators
			$sql = "SELECT		moderator.*, IFNULL(user.username, usergroup.groupName) AS name, user.userID, usergroup.groupID
				FROM		wbb".WBB_N."_board_moderator moderator
				LEFT JOIN	wcf".WCF_N."_user user
				ON		(user.userID = moderator.userID)
				LEFT JOIN	wcf".WCF_N."_group usergroup
				ON		(usergroup.groupID = moderator.groupID)
				WHERE		boardID = ".$this->boardID."
				ORDER BY	name";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (empty($row['userID']) && empty($row['groupID'])) continue;
				$moderator = array('name' => $row['name'], 'type' => ($row['userID'] ? 'user' : 'group'), 'id' => ($row['userID'] ? $row['userID'] : $row['groupID']));
				unset($row['name'], $row['userID'], $row['groupID'], $row['boardID']);
				foreach ($row as $key => $value) {
					if (!in_array($key, $this->moderatorSettings)) unset($row[$key]);
				}
				$moderator['settings'] = $row;
				$this->moderators[] = $moderator;
			}
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'boardID' => $this->boardID,
			'board' => $this->board,
			'action' => 'edit',
			'boardQuickJumpOptions' => Board::getBoardSelect(array(), false, true),
		));
	}
	
	/**
	 * @see BoardAddForm::readBoardOptions()
	 */
	protected function readBoardOptions() {
		$this->boardOptions = Board::getBoardSelect(array(), true, true, array($this->boardID));
	}
}
?>