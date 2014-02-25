<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');

// wcf imports
require_once(WCF_DIR.'lib/form/AbstractForm.class.php');

/**
 * Shows a list of all boards and offers the possibility to ignore some of them.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	form
 * @category 	Burning Board
 */
class BoardIgnoreForm extends AbstractForm {
	// system
	public $templateName = 'boardIgnore';
	
	/**
	 * list of board structure
	 * 
	 * @var	array
	 */
	public $boardStructure = null;
	
	/**
	 * list of boards
	 * 
	 * @var	array
	 */
	public $boards = null;
	
	/**
	 * list of boards
	 * 
	 * @var	array
	 */
	public $boardList = array();
	
	/**
	 * list of ignored boards
	 * 
	 * @var	array<integer>
	 */
	public $unignoredBoardIDArray = array();
	
	/**
	 * @see Form::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get boards from cache
		$this->boards = WCF::getCache()->get('board', 'boards');
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['unignoredBoardIDArray']) && is_array($_POST['unignoredBoardIDArray'])) $this->unignoredBoardIDArray = ArrayUtil::toIntegerArray($_POST['unignoredBoardIDArray']);
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// delete ignored boards
		$sql = "DELETE FROM	wbb".WBB_N."_board_ignored_by_user
			WHERE		userID = ".WCF::getUser()->userID;
		WCF::getDB()->sendQuery($sql);
		
		// fix not ignorable boards
		foreach ($this->boards as $board) {
			if (!$board->ignorable) {
				if (!in_array($board->boardID, $this->unignoredBoardIDArray)) {
					$this->unignoredBoardIDArray[] = $board->boardID;
				}
				$parentBoards = $board->getParentBoards();
				foreach ($parentBoards as $parentBoard) {
					if (!in_array($parentBoard->boardID, $this->unignoredBoardIDArray)) {
						$this->unignoredBoardIDArray[] = $parentBoard->boardID;
					}
				}
			}
		}
		
		// save ignored boards
		foreach ($this->boards as $board) {
			if (!in_array($board->boardID, $this->unignoredBoardIDArray) && $board->getPermission('canViewBoard')) {
				$sql = "INSERT INTO	wbb".WBB_N."_board_ignored_by_user
							(userID, boardID)
					VALUES		(".WCF::getUser()->userID.", ".$board->boardID.")";
				WCF::getDB()->sendQuery($sql);
			}
		}
		
		WCF::getSession()->resetUserData();
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->renderBoards();
		
		// default values
		if (!count($_POST)) {
			$ignoredBoardIDArray = array();
			$sql = "SELECT	boardID
				FROM	wbb".WBB_N."_board_ignored_by_user
				WHERE	userID = ".WCF::getUser()->userID;
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$ignoredBoardIDArray[] = $row['boardID'];
			}
			
			foreach ($this->boards as $board) {
				if (!in_array($board->boardID, $ignoredBoardIDArray) && $board->getPermission('canViewBoard')) {
					$this->unignoredBoardIDArray[] = $board->boardID;
				}
			}
		}
	}
	
	/**
	 * Renders the ordered list of all boards.
	 */
	protected function renderBoards() {
		// get board structure from cache		
		$this->boardStructure = WCF::getCache()->get('board', 'boardStructure');

		$this->clearBoardList();
		$this->makeBoardList();
	}
	
	/**
	 * Removes invisible boards from board list.
	 * 
	 * @param	integer		$parentID
	 */
	protected function clearBoardList($parentID = 0) {
		if (!isset($this->boardStructure[$parentID])) return;
		
		// remove invisible boards
		foreach ($this->boardStructure[$parentID] as $key => $boardID) {
			$board = $this->boards[$boardID];
			if (!$board->getPermission('canViewBoard') || $board->isInvisible) {
				unset($this->boardStructure[$parentID][$key]);
				continue;
			}
			
			$this->clearBoardList($boardID);
		}
		
		if (!count($this->boardStructure[$parentID])) {
			unset($this->boardStructure[$parentID]);
		}
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
		
		$i = 0;
		foreach ($this->boardStructure[$parentID] as $boardID) {
			$board = $this->boards[$boardID];
			
			// boardlist depth on index
			$childrenOpenParents = $openParents + 1;
			$hasChildren = isset($this->boardStructure[$boardID]);
			$last = $i == count($this->boardStructure[$parentID]) - 1;
			if ($hasChildren && !$last) $childrenOpenParents = 1;
			$this->boardList[] = array('depth' => $depth, 'hasChildren' => $hasChildren, 'openParents' => ((!$hasChildren && $last) ? ($openParents) : (0)), 'board' => $board, 'parentID' => $parentID);
			
			// make next level of the board list
			$this->makeBoardList($boardID, $depth + 1, $childrenOpenParents);
			
			$i++;
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'boards' => $this->boardList,
			'unignoredBoardIDArray' => $this->unignoredBoardIDArray
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (!WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		// set active tab
		require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');
		UserCPMenu::getInstance()->setActiveMenuItem('wcf.user.usercp.menu.link.management.ignoredBoards');
		
		parent::show();
	}
}
?>