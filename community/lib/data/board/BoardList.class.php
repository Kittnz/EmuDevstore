<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');
require_once(WBB_DIR.'lib/data/user/WBBUser.class.php');

// wcf imports
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');

/**
 * Shows the list of boards on the start page.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.board
 * @category 	Burning Board
 */
class BoardList {
	public $maxDepth = 2;
	protected $boardID = 0;
	protected $boardStructure;
	protected $boards;
	protected $boardList = array();
	protected $lastPosts = array();
	protected $subBoards = array();
	protected $newPosts = array();
	protected $unreadThreadsCount = array();
	protected $lastPostTimes = array();
	protected $inheritHiddenBoards = array();
	protected $boardUsersOnline = array();
	protected $boardStats = array();
	protected $visibleSQL = '';
	
	/**
	 * Creates a new BoardListPage object.
	 *	
	 * The boardID determines, which subboards are rendered.
	 * 0 means, that all boards are rendered.
	 *
	 * @param 	integer		$boardID		id of the parent board.
	 */
	public function __construct($boardID = 0) {
		$this->boardID = $boardID;
	}
	
	/**
	 * Handles the request for hiding a board.
	 */
	public function readParameters() {
		if (isset($_REQUEST['closeCategory'])) {
			WCF::getUser()->closeCategory(intval($_REQUEST['closeCategory']), 1);
		}
		if (isset($_REQUEST['openCategory'])) {
			WCF::getUser()->closeCategory(intval($_REQUEST['openCategory']), -1);
		}
	}
	
	/**
	 * Gets the post time of the last unread post for each board.
	 */
	protected function getLastPostTimes() {
		$sql = "SELECT 		boardID, thread.threadID, thread.lastPostTime
					".((WCF::getUser()->userID) ? (", thread_visit.lastVisitTime") : (", 0 AS lastVisitTime"))."
			FROM 		wbb".WBB_N."_thread thread
			".((WCF::getUser()->userID) ? ("
			LEFT JOIN 	wbb".WBB_N."_thread_visit thread_visit
			ON 		(thread_visit.threadID = thread.threadID AND thread_visit.userID = ".WCF::getUser()->userID.")
			") : (""))."
			WHERE 		thread.lastPostTime > ". WCF::getUser()->getLastMarkAllAsReadTime()."
					AND isDeleted = 0
					AND isDisabled = 0
					AND movedThreadID = 0"
					.((count(WCF::getSession()->getVisibleLanguageIDArray()) && (BOARD_THREADS_ENABLE_LANGUAGE_FILTER_FOR_GUESTS == 1 || WCF::getUser()->userID != 0)) ? " AND thread.languageID IN (".implode(',', WCF::getSession()->getVisibleLanguageIDArray()).")" : "");
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (WCF::getUser()->userID) $lastVisitTime = $row['lastVisitTime'];
			else $lastVisitTime = WCF::getUser()->getThreadVisitTime($row['threadID']);
			
			if ($row['lastPostTime'] > $lastVisitTime) {
				// count unread threads
				if ($row['lastPostTime'] > WCF::getUser()->getBoardVisitTime($row['boardID'])) {
					if (!isset($this->unreadThreadsCount[$row['boardID']])) $this->unreadThreadsCount[$row['boardID']] = 0;
					$this->unreadThreadsCount[$row['boardID']]++;
				}
				
				// save last post time
				if (!isset($this->lastPostTimes[$row['boardID']]) || $row['lastPostTime'] > $this->lastPostTimes[$row['boardID']]) {
					$this->lastPostTimes[$row['boardID']] = $row['lastPostTime'];
				}
			}
		}
	}

	/**
	 * Gets users online in the boards on the board list.
	 */
	protected function getBoardUsersOnline() {
		require_once(WBB_DIR.'lib/data/board/BoardListUsersOnline.class.php');
		$usersOnline = new BoardListUsersOnline();
		$this->boardUsersOnline = $usersOnline->getBoardUsersOnline();
	}
	
	/**
	 * Renders the list of boards on the index page or the list of subboards on a board page.
	 */
	public function renderBoards() {
		// get board structure from cache		
		$this->boardStructure = WCF::getCache()->get('board', 'boardStructure');
		
		if (!isset($this->boardStructure[$this->boardID])) {
			// the board with the given board id has no children
			WCF::getTPL()->assign('boards', array());
			return;
		}
		
		$this->readParameters();
		$this->getLastPostTimes();
		if (BOARD_LIST_ENABLE_ONLINE_LIST) {
			$this->getBoardUsersOnline();
		}
		
		// get boards from cache
		$this->boards = WCF::getCache()->get('board', 'boards');
				
		// show newest posts on index
		if (BOARD_LIST_ENABLE_LAST_POST) {
			$lastPosts = WCF::getCache()->get('boardData', 'lastPosts');
			
			if (is_array($lastPosts)) {
				$visibleLanguages = false;
				if (count(WCF::getSession()->getVisibleLanguageIDArray()) && (BOARD_THREADS_ENABLE_LANGUAGE_FILTER_FOR_GUESTS == 1 || WCF::getUser()->userID != 0)) {
					$visibleLanguages = WCF::getSession()->getVisibleLanguageIDArray();
				}
				
				foreach ($lastPosts as $boardID => $languages) {
					foreach ($languages as $languageID => $row) {
						if (!$languageID || !$visibleLanguages || in_array($languageID, $visibleLanguages)) {
							$this->lastPosts[$row['boardID']] = new DatabaseObject($row);
							continue 2;
						}
					}
				}
			}
		}
		// stats
		if (BOARD_LIST_ENABLE_STATS) {
			$this->boardStats = WCF::getCache()->get('boardData', 'counts');
		}
		
		$this->clearBoardList($this->boardID);
		$this->makeBoardList($this->boardID, $this->boardID);
		WCF::getTPL()->assign('boards', $this->boardList);
		WCF::getTPL()->assign('newPosts', $this->newPosts);
		WCF::getTPL()->assign('unreadThreadsCount', $this->unreadThreadsCount);
		
		// show newest posts on index
		if (BOARD_LIST_ENABLE_LAST_POST) {
			WCF::getTPL()->assign('lastPosts', $this->lastPosts);
		}
		// show subboards on index
		if (BOARD_LIST_ENABLE_SUB_BOARDS) {
			WCF::getTPL()->assign('subBoards', $this->subBoards);
		}
		// show users in board
		if (BOARD_LIST_ENABLE_ONLINE_LIST) {
			WCF::getTPL()->assign('boardUsersOnline', $this->boardUsersOnline);
		}
		// moderators
		if (BOARD_LIST_ENABLE_MODERATORS) {
			WCF::getTPL()->assign('moderators', WCF::getCache()->get('board', 'moderators'));
		}
		// stats
		if (BOARD_LIST_ENABLE_STATS) {
			WCF::getTPL()->assign('boardStats', $this->boardStats);
		}
	}
	
	/**
	 * Compares subboards for subboard sorting.
	 * 
	 * @param	Board		$boardA
	 * @param	Board		$boardB
	 * @return	integer
	 */
	protected static function compareSubBoards($boardA, $boardB) {
		return strcoll($boardA->title, $boardB->title);
	}
	
	/**
	 * Removes invisible boards from board list.
	 * 
	 * @param	integer		parentID		render the subboards of the board with the given id
	 */
	protected function clearBoardList($parentID = 0) {
		if (!isset($this->boardStructure[$parentID])) return;
		
		// remove invisible boards
		foreach ($this->boardStructure[$parentID] as $key => $boardID) {
			$board = $this->boards[$boardID];
			if (WCF::getUser()->isIgnoredBoard($boardID) || !$board->getPermission() || $board->isInvisible) {
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
	 * @param	integer		$parentID		render the subboards of the board with the given id
	 * @param	integer		$subBoardFrom		helping variable for displaying the invisible subboards as a link under the parent board
	 * @param	integer		$depth			the depth of the current level
	 * @param	integer		$openParents		helping variable for rendering the html list in the boardlist template
	 * @param	integer		$parentClosed		determines whether a parent category is collapsed
	 * @param	boolean		$showSubBoards
	 */
	protected function makeBoardList($parentID = 0, $subBoardsFrom = 0, $depth = 1, $openParents = 0, $parentClosed = 0, $showSubBoards = true) {
		if (!isset($this->boardStructure[$parentID])) return;
		
		$i = 0;
		$count = count($this->boardStructure[$parentID]);
		foreach ($this->boardStructure[$parentID] as $boardID) {
			$board = $this->boards[$boardID];
			if (!isset($this->lastPostTimes[$boardID])) {
				$this->lastPostTimes[$boardID] = 0;
			}
			
			// boardlist depth on index
			$updateNewPosts = 0;
			$updateBoardInfo = 1;
			$childrenOpenParents = $openParents + 1;
			$newSubBoardsFrom = $subBoardsFrom;
			if ($parentClosed == 0 && (WCF::getUser()->isClosedCategory($parentID) == -1 || $depth <= $this->maxDepth) && $subBoardsFrom == $parentID) {
				$updateBoardInfo = 0;
				$open = WCF::getUser()->isClosedCategory($boardID) == -1 || ($depth + 1 <= $this->maxDepth && WCF::getUser()->isClosedCategory($boardID) != 1);
				$hasChildren = isset($this->boardStructure[$boardID]) && $open;
				$last = ($i == ($count - 1));
				if ($hasChildren && !$last) $childrenOpenParents = 1;
				$this->boardList[] = array('open' => $open, 'depth' => $depth, 'hasChildren' => $hasChildren, 'openParents' => ((!$hasChildren && $last) ? ($openParents) : (0)), 'board' => $board);
				$newSubBoardsFrom = $boardID;
			}
			// show subboards on index
			else if ($parentClosed == 0 && BOARD_LIST_ENABLE_SUB_BOARDS && $showSubBoards) {
				$this->subBoards[$subBoardsFrom][$boardID] = $board;			
			}
			// board is invisible; show new posts in parent board
			else {
				$updateNewPosts = 1;
			}
			
			// make next level of the board list
			$this->makeBoardList($boardID, $newSubBoardsFrom, $depth + 1, $childrenOpenParents, $parentClosed || WCF::getUser()->isClosedCategory($boardID) == 1, $showSubBoards && $board->showSubBoards);
			
			// user can not enter board; unset last post
			if (!$board->getPermission('canEnterBoard') && isset($this->lastPosts[$boardID])) {
				unset($this->lastPosts[$boardID]);
			}
			
			// show newest posts on index
			if ($updateBoardInfo && $parentID != 0 && BOARD_LIST_ENABLE_LAST_POST) {
				if (isset($this->lastPosts[$boardID])) {
					if (!isset($this->lastPosts[$parentID]) || $this->lastPosts[$boardID]->lastPostTime > $this->lastPosts[$parentID]->lastPostTime) {
						$this->lastPosts[$parentID] = $this->lastPosts[$boardID];
					}
				}
			}
			
			// update parent stats
			if ($updateBoardInfo && $parentID != 0 && BOARD_LIST_ENABLE_STATS) {
				if (isset($this->boardStats[$parentID]) && isset($this->boardStats[$boardID])) {
					$this->boardStats[$parentID]['threads'] += $this->boardStats[$boardID]['threads'];
					$this->boardStats[$parentID]['posts'] += $this->boardStats[$boardID]['posts'];
				}
			}
			
			// update user online to parent board
			if ($updateBoardInfo && isset($this->boardUsersOnline[$boardID])) {
				if (isset($this->boardUsersOnline[$boardID]['users'])) {
					if (!isset($this->boardUsersOnline[$parentID]['users'])) {
						$this->boardUsersOnline[$parentID]['users'] = $this->boardUsersOnline[$boardID]['users'];
					}
					else {
						$this->boardUsersOnline[$parentID]['users'] = array_merge($this->boardUsersOnline[$parentID]['users'], $this->boardUsersOnline[$boardID]['users']);
					}
				}
				
				if (isset($this->boardUsersOnline[$boardID]['guests'])) {
					if (!isset($this->boardUsersOnline[$parentID]['guests'])) {
						$this->boardUsersOnline[$parentID]['guests'] = $this->boardUsersOnline[$boardID]['guests'];
					}
					else {
						$this->boardUsersOnline[$parentID]['guests'] += $this->boardUsersOnline[$boardID]['guests'];
					}
				}
			}
			
			// board has unread posts
			if ($this->lastPostTimes[$boardID] > WCF::getUser()->getBoardVisitTime($boardID)) {
				$this->newPosts[$boardID] = true;
				if ($updateNewPosts) {
					// update unread thread count
					if (isset($this->unreadThreadsCount[$boardID])) {
						if (!isset($this->unreadThreadsCount[$parentID])) $this->unreadThreadsCount[$parentID] = 0;
						$this->unreadThreadsCount[$parentID] += $this->unreadThreadsCount[$boardID];
					}
				
					// update last post time
					if (!isset($this->lastPostTimes[$parentID]) || $this->lastPostTimes[$parentID] < $this->lastPostTimes[$boardID]) {
						$this->lastPostTimes[$parentID] = $this->lastPostTimes[$boardID];
					}
				}
			}
			else {
				$this->newPosts[$boardID] = false;
			}
			
			$i++;
		}
	}
}
?>