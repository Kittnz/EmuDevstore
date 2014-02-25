<?php
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a board in the forum.
 * A board is a container for threads and other board.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.board
 * @category 	Burning Board
 */
class Board extends DatabaseObject {
	protected $parentBoards = null;
	protected $clicks = null;
	protected $threads = null;
	protected $posts = null;
	protected $postsPerDay = null;
	
	protected static $boards = null;
	protected static $boardSelect;
	protected static $boardStructure = null;
	
	/**
	 * Defines that a board acts as a container for threads.
	 */
	const TYPE_BOARD = 0;
	
	/**
	 * Defines that a board acts as a category.
	 */
	const TYPE_CATEGORY = 1;
	
	/**
	 * Defines that a board acts as an external link.
	 */
	const TYPE_LINK = 2;
	
	/**
	 * Prefix modes.
	 */
	const PREFIX_MODE_OFF = 0;
	const PREFIX_MODE_GLOBAL = 1;
	const PREFIX_MODE_BOARD = 2;
	const PREFIX_MODE_COMBINATION = 3;
	
	/**
	 * Creates a new Board object.
	 *
	 * If id is set, the function reads the board data from database.
	 * Otherwise it uses the given resultset.
	 * 
	 * @param 	integer		$boardID		id of a board 
	 * @param 	array		$row			resultset with board data form database
	 * @param 	Board 		$cacheObject		object with board data form database
	 */
	public function __construct($boardID, $row = null, $cacheObject = null) {
		if ($boardID !== null) $cacheObject = self::getBoard($boardID);
		if ($row != null) parent::__construct($row);
		if ($cacheObject != null) parent::__construct($cacheObject->data);
	}
	
	/**
	 * Returns true if this board is no category and no external link.
	 *
	 * @return	boolean
	 */
	public function isBoard() {
		return $this->boardType == self::TYPE_BOARD;
	}
	
	/**
	 * Returns true if this board is a category.
	 *
	 * @return	boolean
	 */
	public function isCategory() {
		return $this->boardType == self::TYPE_CATEGORY;
	}
	
	/**
	 * Returns true if this board is an external link.
	 *
	 * @return	boolean
	 */
	public function isExternalLink() {
		return $this->boardType == self::TYPE_LINK;
	}
	
	/**
	 * Returns a list of the parent boards of this board.
	 * 
	 * @return	array
	 */
	public function getParentBoards() {
		if ($this->parentBoards === null) {
			$this->parentBoards = array();
			$boards = WCF::getCache()->get('board', 'boards');
			
			$parentBoard = $this;
			while ($parentBoard->parentID != 0) {
				$parentBoard = $boards[$parentBoard->parentID];
				array_unshift($this->parentBoards, $parentBoard);
			}
		}
		
		return $this->parentBoards;
	}
	
	/**
	 * Checks the given board permissions.
	 * Throws a PermissionDeniedException if the active user doesn't have one of the given permissions.
	 * @see		Board::getPermission()
	 * 
	 * @param	mixed		$permissions
	 */
	public function checkPermission($permissions = 'canViewBoard') {
		if (!is_array($permissions)) $permissions = array($permissions);
		
		foreach ($permissions as $permission) {
			if (!$this->getPermission($permission)) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * Checks whether the active user has the permission with the given name on this board.
	 * 
	 * @param	string		$permission	name of the requested permission
	 * @return	boolean
	 */
	public function getPermission($permission = 'canViewBoard') {
		return (boolean) WCF::getUser()->getBoardPermission($permission, $this->boardID);
	}
	
	/**
	 * Checks whether the active user has the moderator permission with the given name on this board.
	 * 
	 * @param	string		$permission	name of the requested permission
	 * @return	boolean
	 */
	public function getModeratorPermission($permission) {
		return (boolean) WCF::getUser()->getBoardModeratorPermission($permission, $this->boardID);
	}
	
	/**
	 * Checks the requested moderator permissions.
	 * Throws a PermissionDeniedException if the active user doesn't have one of the given permissions.
	 * @see 	Board::getModeratorPermission()
	 * 
	 * @param	mixed		$permissions
	 */
	public function checkModeratorPermission($permissions) {
		if (!is_array($permissions)) $permissions = array($permissions);
		
		$result = false;
		foreach ($permissions as $permission) {
			$result = $result || $this->getModeratorPermission($permission);
		}
		
		if (!$result) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Enters the active user to this board.
	 */
	public function enter() {
		// check permissions
		$this->checkPermission(array('canViewBoard', 'canEnterBoard'));
		
		// refresh session
		WCF::getSession()->setBoardID($this->boardID);
		
		// change style if necessary
		require_once(WCF_DIR.'lib/system/style/StyleManager.class.php');
		if ($this->styleID && (!WCF::getSession()->getStyleID() || $this->enforceStyle) && StyleManager::getStyle()->styleID != $this->styleID) {
			StyleManager::changeStyle($this->styleID, true);
		}
	}
	
	/**
	 * Returns true, if the active user can start new threads in this board.
	 * 
	 * @return	boolean
	 */
	public function canStartThread() {
		return ($this->isBoard() && $this->getPermission('canStartThread') && !$this->isClosed);
	}

	/**	
	 * Returns an array with the prefix options of this board.
	 * 
	 * @return	array
	 */
	public function getPrefixOptions() {
		// format prefixes
		$result = self::getPrefixes();
		$prefixes = array();
		foreach ($result as $value) {
			$prefixes[$value] = WCF::getLanguage()->get($value);
		}
		
		return $prefixes;
	}
	
	/**	
	 * Returns an array with the prefixes of this board.
	 * 
	 * @return	array
	 */
	public function getPrefixes() {
		if (!$this->hasPrefixes()) return array();
		
		// get prefixes
		$prefixes = '';
		if (($this->prefixMode == self::PREFIX_MODE_GLOBAL || $this->prefixMode == self::PREFIX_MODE_COMBINATION) && THREAD_DEFAULT_PREFIXES) {
			$prefixes = THREAD_DEFAULT_PREFIXES;
		}
		if (($this->prefixMode == self::PREFIX_MODE_BOARD || $this->prefixMode == self::PREFIX_MODE_COMBINATION) && $this->prefixes) {
			if (!empty($prefixes)) $prefixes .= "\n";
			$prefixes .= $this->prefixes;
		}
		
		return explode("\n", StringUtil::unifyNewlines($prefixes));
	}
	
	/**
	 * Returns true, if this board has any prefixes.
	 * 
	 * @return	boolean
	 */
	public function hasPrefixes() {
		if ((($this->prefixMode == self::PREFIX_MODE_BOARD || $this->prefixMode == self::PREFIX_MODE_COMBINATION) && $this->prefixes) || (($this->prefixMode == self::PREFIX_MODE_GLOBAL || $this->prefixMode == self::PREFIX_MODE_COMBINATION) && THREAD_DEFAULT_PREFIXES)) {
			return 1;
		}
		return 0;
	}
	
	/**
	 * Gets the board with the given board id from cache.
	 * 
	 * @param 	integer		$boardID	id of the requested board
	 * @return	Board
	 */
	public static function getBoard($boardID) {
		if (self::$boards === null) {
			self::$boards = WCF::getCache()->get('board', 'boards');
		}
		
		if (!isset(self::$boards[$boardID])) {
			throw new IllegalLinkException();
		}
		
		return self::$boards[$boardID];
	}
	
	/**
	 * Creates the board select list.
	 * 
	 * @param	array		$permissions		filters boards by given permissions
	 * @param	boolean		$hideLinks		should be true, to hide external link boards
	 * @param	boolean		$showInvisibleBoards	should be true, to display invisible boards
	 * @param	array		$ignore			list of board ids to ignore in result
	 * @return 	array
	 */
	public static function getBoardSelect($permissions = array('canViewBoard'), $hideLinks = false, $showInvisibleBoards = false, $ignore = array()) {
		self::$boardSelect = array();
		
		if (self::$boardStructure === null) self::$boardStructure = WCF::getCache()->get('board', 'boardStructure');
		if (self::$boards === null) self::$boards = WCF::getCache()->get('board', 'boards');
		
		self::makeBoardSelect(0, 0, $permissions, $hideLinks, $showInvisibleBoards, $ignore);
		
		return self::$boardSelect;
	}
	
	/**
	 * Generates the board select list.
	 * 
	 * @param	integer		$parentID		id of the parent board
	 * @param	integer		$depth 			current list depth
	 * @param	array		$permissions		filters boards by given permissions
	 * @param	boolean		$hideLinks		should be true, to hide external link boards
	 * @param	boolean		$showInvisibleBoards	should be true, to display invisible boards
	 * @param	array		$ignore			list of board ids to ignore in result
	 */
	protected static function makeBoardSelect($parentID = 0, $depth = 0, $permissions = array('canViewBoard'), $hideLinks = false, $showInvisibleBoards = false, $ignore = array()) {
		if (!isset(self::$boardStructure[$parentID])) return;
		
		foreach (self::$boardStructure[$parentID] as $boardID) {
			if (!empty($ignore) && in_array($boardID, $ignore)) continue;
			
			$board = self::$boards[$boardID];
			if (!$showInvisibleBoards && ($board->isInvisible || WCF::getUser()->isIgnoredBoard($boardID))) continue;
			
			$result = true;
			foreach ($permissions as $permission) {
				$result = $result && $board->getPermission($permission);
			}
			
			if (!$result) continue;
			if ($hideLinks && $board->isExternalLink()) continue; 
			
			// we must encode html here because the htmloptions plugin doesn't do it
			$title = WCF::getLanguage()->get(StringUtil::encodeHTML($board->title));
			if ($depth > 0) $title = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth). ' ' . $title;
			
			self::$boardSelect[$boardID] = $title;
			self::makeBoardSelect($boardID, $depth + 1, $permissions, $hideLinks, $showInvisibleBoards, $ignore);
		}
	}
	
	/**
	 * Returns the number of clicks.
	 * 
	 * @return	integer
	 */
	public function getClicks() {
		if (!$this->isExternalLink()) {
			return null;
		}
		
		if ($this->clicks === null) {
			// get clicks from cache
			$this->clicks = 0;
			$cache = WCF::getCache()->get('boardData', 'counts');
			if (isset($cache[$this->boardID]['clicks'])) $this->clicks = $cache[$this->boardID]['clicks'];
		}
		
		return $this->clicks;
	}
	
	/**
	 * Returns the number of threads in this board.
	 * 
	 * @return	integer
	 */
	public function getThreads() {
		if (!$this->isBoard()) {
			return null;
		}
		
		if ($this->threads === null) {
			// get threads from cache
			$this->threads = 0;
			$cache = WCF::getCache()->get('boardData', 'counts');
			if (isset($cache[$this->boardID]['threads'])) $this->threads = $cache[$this->boardID]['threads'];
		}
		
		return $this->threads;
	}
	
	/**
	 * Returns the number of posts in this board.
	 * 
	 * @return	integer
	 */
	public function getPosts() {
		if (!$this->isBoard()) {
			return null;
		}
		
		if ($this->posts === null) {
			// get posts from cache
			$this->posts = 0;
			$cache = WCF::getCache()->get('boardData', 'counts');
			if (isset($cache[$this->boardID]['posts'])) $this->posts = $cache[$this->boardID]['posts'];
		}
		
		return $this->posts;
	}
	
	/**
	 * Returns the number of posts per day in this board.
	 * 
	 * @return	float
	 */
	public function getPostsPerDay() {
		if ($this->postsPerDay === null) {
			$this->postsPerDay = 0;
			$days = ceil((TIME_NOW - $this->time) / 86400);
			if ($days <= 0) $days = 1;
			$this->postsPerDay = $this->getPosts() / $days;
		}
		
		return $this->postsPerDay;
	}
	
	/**
	 * Returns the moderator permissions of the active user.
	 * 
	 * @return	array
	 */
	public function getModeratorPermissions() {
		$permissions = array();
		
		// thread permissions
		$permissions['canDeleteThread'] = intval($this->getModeratorPermission('canDeleteThread'));
		$permissions['canReadDeletedThread'] = intval($this->getModeratorPermission('canReadDeletedThread'));
		$permissions['canDeleteThreadCompletely'] = intval($this->getModeratorPermission('canDeleteThreadCompletely'));
		$permissions['canCloseThread'] = intval($this->getModeratorPermission('canCloseThread'));
		$permissions['canEnableThread'] = intval($this->getModeratorPermission('canEnableThread'));
		$permissions['canEditPost'] = intval($this->getModeratorPermission('canEditPost'));
		$permissions['canMoveThread'] = intval($this->getModeratorPermission('canMoveThread'));
		$permissions['canCopyThread'] = intval($this->getModeratorPermission('canCopyThread'));
		$permissions['canPinThread'] = intval($this->getModeratorPermission('canPinThread'));
		$permissions['canMarkAsDoneThread'] = intval($this->getModeratorPermission('canMarkAsDoneThread'));
		$permissions['canMarkThread'] = intval($permissions['canDeleteThread'] || $permissions['canMoveThread'] || $permissions['canCopyThread']);
		$permissions['canHandleThread'] = intval($permissions['canCloseThread'] || $permissions['canEnableThread'] || $permissions['canEditPost'] || $permissions['canMarkAsDoneThread'] || $permissions['canMarkThread']);

		// post permissions
		$permissions['canDeletePost'] = intval($this->getModeratorPermission('canDeletePost'));
		$permissions['canReadDeletedPost'] = intval($this->getModeratorPermission('canReadDeletedPost'));
		$permissions['canDeletePostCompletely'] = intval($this->getModeratorPermission('canDeletePostCompletely'));
		$permissions['canClosePost'] = intval($this->getModeratorPermission('canClosePost'));
		$permissions['canEnablePost'] = intval($this->getModeratorPermission('canEnablePost'));
		$permissions['canMovePost'] = intval($this->getModeratorPermission('canMovePost'));
		$permissions['canCopyPost'] = intval($this->getModeratorPermission('canCopyPost'));
		$permissions['canMergePost'] = intval($this->getModeratorPermission('canMergePost'));
		$permissions['canMarkPost'] = intval($permissions['canDeletePost'] || $permissions['canMovePost'] || $permissions['canCopyPost']);
		$permissions['canHandlePost'] = intval($permissions['canClosePost'] || $permissions['canEnablePost'] || $permissions['canEditPost'] || $permissions['canMarkThread']);

		return $permissions;
	}
	
	/**
	 * Returns the global moderator permissions.
	 * 
	 * @return	array
	 */
	public static function getGlobalModeratorPermissions() {
		$permissions = array();
		
		// thread permissions
		$permissions['canDeleteThread'] = intval(WCF::getUser()->getPermission('mod.board.canDeleteThread'));
		$permissions['canReadDeletedThread'] = intval(WCF::getUser()->getPermission('mod.board.canReadDeletedThread'));
		$permissions['canDeleteThreadCompletely'] = intval(WCF::getUser()->getPermission('mod.board.canDeleteThreadCompletely'));
		$permissions['canCloseThread'] = intval(WCF::getUser()->getPermission('mod.board.canCloseThread'));
		$permissions['canEnableThread'] = intval(WCF::getUser()->getPermission('mod.board.canEnableThread'));
		$permissions['canEditPost'] = intval(WCF::getUser()->getPermission('mod.board.canEditPost'));
		$permissions['canMoveThread'] = intval(WCF::getUser()->getPermission('mod.board.canMoveThread'));
		$permissions['canCopyThread'] = intval(WCF::getUser()->getPermission('mod.board.canCopyThread'));
		$permissions['canPinThread'] = intval(WCF::getUser()->getPermission('mod.board.canPinThread'));
		$permissions['canMarkAsDoneThread'] = intval(WCF::getUser()->getPermission('mod.board.canMarkAsDoneThread'));
		$permissions['canMarkThread'] = intval($permissions['canDeleteThread'] || $permissions['canMoveThread'] || $permissions['canCopyThread']);
		$permissions['canHandleThread'] = intval($permissions['canCloseThread'] || $permissions['canEnableThread'] || $permissions['canEditPost'] || $permissions['canMarkThread'] || $permissions['canMarkAsDoneThread']);

		// post permissions
		$permissions['canDeletePost'] = intval(WCF::getUser()->getPermission('mod.board.canDeletePost'));
		$permissions['canReadDeletedPost'] = intval(WCF::getUser()->getPermission('mod.board.canReadDeletedPost'));
		$permissions['canDeletePostCompletely'] = intval(WCF::getUser()->getPermission('mod.board.canDeletePostCompletely'));
		$permissions['canClosePost'] = intval(WCF::getUser()->getPermission('mod.board.canClosePost'));
		$permissions['canEnablePost'] = intval(WCF::getUser()->getPermission('mod.board.canEnablePost'));
		$permissions['canMovePost'] = intval(WCF::getUser()->getPermission('mod.board.canMovePost'));
		$permissions['canCopyPost'] = intval(WCF::getUser()->getPermission('mod.board.canCopyPost'));
		$permissions['canMergePost'] = intval(WCF::getUser()->getPermission('mod.board.canMergePost'));
		$permissions['canMarkPost'] = intval($permissions['canDeletePost'] || $permissions['canMovePost'] || $permissions['canCopyPost']);
		$permissions['canHandlePost'] = intval($permissions['canClosePost'] || $permissions['canEnablePost'] || $permissions['canEditPost'] || $permissions['canMarkThread']);

		return $permissions;
	}
	
	/**
	 * Returns a list of accessible boards.
	 * 
	 * @param	string		$permission		name of the requested permission
	 * @return	string					comma separated board ids
	 */
	public static function getModeratedBoards($permission) {
		if (self::$boards === null) self::$boards = WCF::getCache()->get('board', 'boards');
		
		$boardIDs = '';
		foreach (self::$boards as $board) {
			if ($board->getModeratorPermission($permission)) {
				if (!empty($boardIDs)) $boardIDs .= ',';
				$boardIDs .= $board->boardID;
			}
		}
		
		return $boardIDs;
	}
	
	/**
	 * Returns a list of accessible boards.
	 * 
	 * @param	array		$permissions		filters boards by given permissions
	 * @return	array<integer>				comma separated board ids
	 */
	public static function getAccessibleBoardIDArray($permissions = array('canViewBoard', 'canEnterBoard')) {
		if (self::$boards === null) self::$boards = WCF::getCache()->get('board', 'boards');
		
		$boardIDArray = array();
		foreach (self::$boards as $board) {
			$result = true;
			foreach ($permissions as $permission) {
				$result = $result && $board->getPermission($permission);
			}
			
			if ($result) {
				$boardIDArray[] = $board->boardID;
			}
		}
		
		return $boardIDArray;
	}
	
	/**
	 * Returns a list of accessible boards.
	 * 
	 * @param	array		$permissions		filters boards by given permissions
	 * @return	string					comma separated board ids
	 */
	public static function getAccessibleBoards($permissions = array('canViewBoard', 'canEnterBoard')) {
		return implode(',', self::getAccessibleBoardIDArray($permissions));
	}
	
	/** 
	 * inherits forum permissions.
	 *
	 * @param 	integer 	$parentID
	 * @param 	array 		$permissions
	 */
	public static function inheritPermissions($parentID = 0, &$permissions) {
		if (self::$boardStructure === null) self::$boardStructure = WCF::getCache()->get('board', 'boardStructure');
		if (self::$boards === null) self::$boards = WCF::getCache()->get('board', 'boards');
		
		if (isset(self::$boardStructure[$parentID]) && is_array(self::$boardStructure[$parentID])) {
			foreach (self::$boardStructure[$parentID] as $boardID) {
				$board = self::$boards[$boardID];
					
				// inherit permissions from parent board
				if ($board->parentID) {
					if (isset($permissions[$board->parentID]) && !isset($permissions[$boardID])) {
						$permissions[$boardID] = $permissions[$board->parentID];
					}
				}
				
				self::inheritPermissions($boardID, $permissions);
			}
		}
	}
	
	/**
	 * Subscribes the active user to this board.
	 */
	public function subscribe() {
		WCF::getUser()->subscribeBoard($this->boardID);
	}
	
	/**
	 * Unsubscribes the active user to this board.
	 */
	public function unsubscribe() {
		WCF::getUser()->unsubscribeBoard($this->boardID);
	}
	
	/**
	 * Marks this board as read for the active user.
	 */
	public function markAsRead() {
		WCF::getUser()->setBoardVisitTime($this->boardID);
	}
	
	/**
	 * Resets the board cache after changes.
	 */
	public static function resetCache() {
		// reset cache
		WCF::getCache()->clearResource('board');
		// reset permissions cache
		WCF::getCache()->clear(WBB_DIR . 'cache/', 'cache.boardPermissions-*', true);
		
		self::$boards = self::$boardStructure = self::$boardSelect = null;
	}
	
	/**
	 * Returns a list of subboards.
	 * 
	 * @param	mixed		$boardID
	 * @return	array<integer>
	 */
	public static function getSubBoardIDArray($boardID) {
		$boardIDArray = (is_array($boardID) ? $boardID : array($boardID));
		$subBoardIDArray = array();
		
		// load cache
		if (self::$boardStructure === null) self::$boardStructure = WCF::getCache()->get('board', 'boardStructure');
		foreach ($boardIDArray as $boardID) {
			$subBoardIDArray = array_merge($subBoardIDArray, self::makeSubBoardIDArray($boardID));
		}
		
		$subBoardIDArray = array_unique($subBoardIDArray);
		return $subBoardIDArray;
	}
	
	/**
	 * Returns a list of subboards.
	 * 
	 * @param	integer		$parentBoardID
	 * @return	array<integer>
	 */
	public static function makeSubBoardIDArray($parentBoardID) {
		if (!isset(self::$boardStructure[$parentBoardID])) {
			return array();
		}
		
		$subBoardIDArray = array();
		foreach (self::$boardStructure[$parentBoardID] as $boardID) {
			$subBoardIDArray = array_merge($subBoardIDArray, self::makeSubBoardIDArray($boardID));
			$subBoardIDArray[] = $boardID;
		}
		
		return $subBoardIDArray;
	}
	
	/**
	 * Returns the filename of the board icon.
	 *
	 * @return	string		filename of the board icon
	 */
	public function getIconName() {
		if ($this->isBoard()) {
			$icon = 'board';
			if ($this->isClosed) $icon .= 'Closed';
		}
		else if ($this->isCategory()) {
			$icon = 'category';
		}
		else {
			$icon = 'boardRedirect';
		}
		
		return $icon;
	}
}
?>