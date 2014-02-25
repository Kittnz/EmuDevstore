<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');

// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Shows a list of all boards.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.page
 * @category 	Burning Board
 */
class BoardListPage extends AbstractPage {
	// system
	public $templateName = 'boardList';
	
	/**
	 * board structure
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
	 * structured list of boards
	 * 
	 * @var	array
	 */
	public $boardList = array();
	
	/**
	 * board id
	 * 
	 * @var	integer
	 */
	public $deletedBoardID = 0;
	
	/**
	 * closed categories
	 * 
	 * @var	array
	 */
	public $closedCategories = array();
		
	/**
	 * If the list was sorted successfully
	 * @var boolean
	 */
	public $successfulSorting = false;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['successfulSorting'])) $this->successfulSorting = true;
		if (isset($_REQUEST['deletedBoardID'])) $this->deletedBoardID = intval($_REQUEST['deletedBoardID']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readClosedCategories();
		$this->renderBoards();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'boards' => $this->boardList,
			'deletedBoardID' => $this->deletedBoardID,
			'successfulSorting' => $this->successfulSorting
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wbb.acp.menu.link.content.board.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.board.canEditBoard', 'admin.board.canDeleteBoard', 'admin.board.canEditPermissions', 'admin.board.canEditModerators'));
		
		parent::show();
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
}
?>