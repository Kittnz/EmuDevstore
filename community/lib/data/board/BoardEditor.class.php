<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');

/**
 * BoardEditor provides functions to edit the data of a board.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.board
 * @category 	Burning Board
 */
class BoardEditor extends Board {
	protected $lastPostTime = null; 
	
	/**
	 * Creates a new BoardEditor object.
	 * @see Board::__construct()
	 */
	public function __construct($boardID, $row = null, $cacheObject = null, $useCache = true) {
		if ($useCache) parent::__construct($boardID, $row, $cacheObject);
		else {
			$sql = "SELECT	*
				FROM	wbb".WBB_N."_board
				WHERE	boardID = ".$boardID;
			$row = WCF::getDB()->getFirstRow($sql);
			parent::__construct(null, $row);
		}
	}
	
	/**
	 * Increases the thread count of this board.
	 * 
	 * @param	integer		$threads
	 * @param	integer		$posts
	 */
	public function addThreads($threads = 1, $posts = 1) {
		$sql = "UPDATE	wbb".WBB_N."_board
			SET	threads = threads + ".$threads.",
				posts = posts + ".$posts."
			WHERE 	boardID = ".$this->boardID;
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Increases the post count of this board.
	 * 
	 * @param	integer		$posts
	 */
	public function addPosts($posts = 1) {
		$sql = "UPDATE	wbb".WBB_N."_board
			SET	posts = posts + ".$posts."
			WHERE 	boardID = ".$this->boardID;
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Sets the last post of this board.
	 * 
	 * @param 	 Thread		$thread		thread of the lastest post
	 */
	public function setLastPost($thread) {
		$sql = "REPLACE INTO	wbb".WBB_N."_board_last_post
					(boardID, languageID, threadID) 
			VALUES 		(".$this->boardID.", ".$thread->languageID.", ".$thread->threadID.")";
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Sets the last post of this board for the given language ids.
	 * 
	 * @param 	string		$languageIDs
	 */
	public function setLastPosts($languageIDs = '') {
		if ($languageIDs === '') {
			// get all language ids
			$sql = "SELECT	DISTINCT languageID
				FROM	wbb".WBB_N."_thread
				WHERE	boardID = ".$this->boardID."
					AND isDeleted = 0
					AND isDisabled = 0
					AND movedThreadID = 0";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!empty($languageIDs)) $languageIDs .= ',';
				$languageIDs .= $row['languageID'];
			}
		}
		
		if ($languageIDs !== '') {
			$languages = explode(',', $languageIDs);
			foreach ($languages as $languageID) {
				$sql = "SELECT		threadID
					FROM 		wbb".WBB_N."_thread
					WHERE 		boardID = ".$this->boardID."
							AND isDeleted = 0
							AND isDisabled = 0
							AND movedThreadID = 0
							AND languageID = ".$languageID."
					ORDER BY 	lastPostTime DESC";
				$row = WCF::getDB()->getFirstRow($sql);
				if (!empty($row['threadID'])) {
					$sql = "REPLACE INTO	wbb".WBB_N."_board_last_post
								(boardID, languageID, threadID) 
						VALUES 		(".$this->boardID.", ".$languageID.", ".$row['threadID'].")";
					WCF::getDB()->registerShutdownUpdate($sql);
				}
			}
		}
		else {
			$sql = "DELETE FROM	wbb".WBB_N."_board_last_post
				WHERE		boardID = ".$this->boardID;
			WCF::getDB()->registerShutdownUpdate($sql);
		}
	}
	
	/**
	 * Returns the last post time of this board.
	 * 
	 * @return	integer
	 */
	public function getLastPostTime($languageID = null) {
		if ($this->lastPostTime == null) {
			$this->lastPostTime = 0;
			$sql = "SELECT 		thread.lastPostTime
				FROM 		wbb".WBB_N."_board_last_post last_post
				LEFT JOIN 	wbb".WBB_N."_thread thread
				ON 		(thread.threadID = last_post.threadID)
				WHERE 		last_post.boardID = ".$this->boardID.
						($languageID != null ? " AND last_post.languageID = ".$languageID : "");
			$row = WCF::getDB()->getFirstRow($sql);
			if (isset($row['lastPostTime'])) $this->lastPostTime = $row['lastPostTime'];
		}
		
		return $this->lastPostTime;
	}
	
	/**
	 * Updates the thread and post counter for this board.
	 */
	public function refresh() {
		$this->refreshAll($this->boardID);
	}
	
	/**
	 * Updates the thread and post counter for the given boards.
	 * 
	 * @param	string		$boardIDs
	 */
	public static function refreshAll($boardIDs) {
		if (empty($boardIDs)) return;
		
		$sql = "UPDATE	wbb".WBB_N."_board board
			SET	threads = (
					SELECT	COUNT(*)
					FROM	wbb".WBB_N."_thread
					WHERE	boardID = board.boardID
						AND isDeleted = 0
						AND isDisabled = 0
						AND movedThreadID = 0
				),
				posts = (
					SELECT	COUNT(*) + IFNULL(SUM(replies), 0)
					FROM	wbb".WBB_N."_thread
					WHERE	boardID = board.boardID
						AND isDeleted = 0
						AND isDisabled = 0
						AND movedThreadID = 0
				)
			WHERE	boardID IN (".$boardIDs.")";
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Deletes the data of boards.
	 */
	public static function deleteData($boardIDs) {
		$sql = "DELETE FROM	wbb".WBB_N."_board_closed_category_to_user
			WHERE		boardID IN (".$boardIDs.")";
		WCF::getDB()->sendQuery($sql);
		$sql = "DELETE FROM	wbb".WBB_N."_board_closed_category_to_admin
			WHERE		boardID IN (".$boardIDs.")";
		WCF::getDB()->sendQuery($sql);
		$sql = "DELETE FROM	wbb".WBB_N."_board_ignored_by_user
			WHERE		boardID IN (".$boardIDs.")";
		WCF::getDB()->sendQuery($sql);
		
		$sql = "DELETE FROM	wbb".WBB_N."_board_last_post
			WHERE		boardID IN (".$boardIDs.")";
		WCF::getDB()->sendQuery($sql);
		
		$sql = "DELETE FROM	wbb".WBB_N."_board_moderator
			WHERE		boardID IN (".$boardIDs.")";
		WCF::getDB()->sendQuery($sql);
		
		$sql = "DELETE FROM	wbb".WBB_N."_board_subscription
			WHERE		boardID IN (".$boardIDs.")";
		WCF::getDB()->sendQuery($sql);
		
		$sql = "DELETE FROM	wbb".WBB_N."_board_to_group
			WHERE		boardID IN (".$boardIDs.")";
		WCF::getDB()->sendQuery($sql);
		
		$sql = "DELETE FROM	wbb".WBB_N."_board_to_user
			WHERE		boardID IN (".$boardIDs.")";
		WCF::getDB()->sendQuery($sql);
		
		$sql = "DELETE FROM	wbb".WBB_N."_board_visit
			WHERE		boardID IN (".$boardIDs.")";
		WCF::getDB()->sendQuery($sql);
		
		$sql = "DELETE FROM	wbb".WBB_N."_board
			WHERE		boardID IN (".$boardIDs.")";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Deletes this board.
	 */
	public function delete() {
		// empty board
		// get alle thread ids
		$threadIDs = '';
		$sql = "SELECT	threadID
			FROM	wbb".WBB_N."_thread
			WHERE	boardID = ".$this->boardID;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($threadIDs)) $threadIDs .= ',';
			$threadIDs .= $row['threadID'];
		}
		if (!empty($threadIDs)) {
			// delete threads
			require_once(WBB_DIR.'lib/data/thread/ThreadEditor.class.php');
			ThreadEditor::deleteAllCompletely($threadIDs);
		}
		
		$this->removePositions();
		
		// update sub boards
		$sql = "UPDATE	wbb".WBB_N."_board
			SET	parentID = ".$this->parentID."
			WHERE	parentID = ".$this->boardID;
		WCF::getDB()->sendQuery($sql);
		
		$sql = "UPDATE	wbb".WBB_N."_board_structure
			SET	parentID = ".$this->parentID."
			WHERE	parentID = ".$this->boardID;
		WCF::getDB()->sendQuery($sql);
		
		// delete board
		self::deleteData($this->boardID);
	}
	
	/**
	 * Removes a board from all positions in board tree.
	 */
	public function removePositions() {
		// unshift boards
		$sql = "SELECT 	parentID, position
			FROM	wbb".WBB_N."_board_structure
			WHERE	boardID = ".$this->boardID;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$sql = "UPDATE	wbb".WBB_N."_board_structure
				SET	position = position - 1
				WHERE 	parentID = ".$row['parentID']."
					AND position > ".$row['position'];
			WCF::getDB()->sendQuery($sql);
		}
		
		// delete board
		$sql = "DELETE FROM	wbb".WBB_N."_board_structure
			WHERE		boardID = ".$this->boardID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Adds a board to a specific position in the board tree.
	 * 
	 * @param	integer		$parentID
	 * @param	integer		$position
	 */
	public function addPosition($parentID, $position = null) {
		// shift boards
		if ($position !== null) {
			$sql = "UPDATE	wbb".WBB_N."_board_structure
				SET	position = position + 1
				WHERE 	parentID = ".$parentID."
					AND position >= ".$position;
			WCF::getDB()->sendQuery($sql);
		}
		
		// get final position
		$sql = "SELECT 	IFNULL(MAX(position), 0) + 1 AS position
			FROM	wbb".WBB_N."_board_structure
			WHERE	parentID = ".$parentID."
				".($position ? "AND position <= ".$position : '');
		$row = WCF::getDB()->getFirstRow($sql);
		$position = $row['position'];
		
		// save position
		$sql = "INSERT INTO	wbb".WBB_N."_board_structure
					(parentID, boardID, position)
			VALUES		(".$parentID.", ".$this->boardID.", ".$position.")";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Updates the data of a board.
	 */
	public function update($parentID = null, $title = null, $description = null, $boardType = null, $image = null, $imageNew = null, $imageShowAsBackground = null, $imageBackgroundRepeat = null, $externalURL = null, $prefixes = null, $prefixMode = null, $prefixRequired = null, $styleID = null, $enforceStyle = null, $daysPrune = null, $sortField = null, $sortOrder = null, $postSortOrder = null, $isClosed = null, $countUserPosts = null, $isInvisible = null, $showSubBoards = null, $allowDescriptionHtml = null, $enableRating = null, $threadsPerPage = null, $postsPerPage = null, $searchable = null, $searchableForSimilarThreads = null, $ignorable = null, $enableMarkingAsDone = null, $additionalFields = array()) {
		$fields = array();
		if ($parentID !== null) $fields['parentID'] = $parentID;
		if ($title !== null) $fields['title'] = $title;
		if ($description !== null) $fields['description'] = $description;
		if ($boardType !== null) $fields['boardType'] = $boardType;
		if ($image !== null) $fields['image'] = $image;
		if ($externalURL !== null) $fields['externalURL'] = $externalURL;
		if ($prefixes !== null) $fields['prefixes'] = $prefixes;
		if ($prefixMode !== null) $fields['prefixMode'] = $prefixMode;
		if ($prefixRequired !== null) $fields['prefixRequired'] = $prefixRequired;
		if ($styleID !== null) $fields['styleID'] = $styleID;
		if ($enforceStyle !== null) $fields['enforceStyle'] = $enforceStyle;
		if ($daysPrune !== null) $fields['daysPrune'] = $daysPrune;
		if ($sortField !== null) $fields['sortField'] = $sortField;
		if ($sortOrder !== null) $fields['sortOrder'] = $sortOrder;
		if ($postSortOrder !== null) $fields['postSortOrder'] = $postSortOrder;
		if ($isClosed !== null) $fields['isClosed'] = $isClosed;
		if ($countUserPosts !== null) $fields['countUserPosts'] = $countUserPosts;
		if ($isInvisible !== null) $fields['isInvisible'] = $isInvisible;
		if ($showSubBoards !== null) $fields['showSubBoards'] = $showSubBoards;
		if ($allowDescriptionHtml !== null) $fields['allowDescriptionHtml'] = $allowDescriptionHtml;
		if ($enableRating !== null) $fields['enableRating'] = $enableRating;
		if ($threadsPerPage !== null) $fields['threadsPerPage'] = $threadsPerPage;
		if ($postsPerPage !== null) $fields['postsPerPage'] = $postsPerPage;
		if ($imageNew !== null) $fields['imageNew'] = $imageNew;
		if ($imageShowAsBackground !== null) $fields['imageShowAsBackground'] = $imageShowAsBackground;
		if ($imageBackgroundRepeat !== null) $fields['imageBackgroundRepeat'] = $imageBackgroundRepeat;
		if ($searchable !== null) $fields['searchable'] = $searchable;
		if ($searchableForSimilarThreads !== null) $fields['searchableForSimilarThreads'] = $searchableForSimilarThreads;
		if ($ignorable !== null) $fields['ignorable'] = $ignorable;
		if ($enableMarkingAsDone !== null) $fields['enableMarkingAsDone'] = $enableMarkingAsDone;
		
		$this->updateData(array_merge($fields, $additionalFields));
	}
	
	/**
	 * Updates the data of a board.
	 *
	 * @param 	array		$fields
	 */
	public function updateData($fields = array()) { 
		$updates = '';
		foreach ($fields as $key => $value) {
			if (!empty($updates)) $updates .= ',';
			$updates .= $key.'=';
			if (is_int($value)) $updates .= $value;
			else $updates .= "'".escapeString($value)."'";
		}
		
		if (!empty($updates)) {
			$sql = "UPDATE	wbb".WBB_N."_board
				SET	".$updates."
				WHERE	boardID = ".$this->boardID;
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * Creates a new board.
	 * 
	 * @return	BoardEditor
	 */
	public static function create($parentID, $position, $title, $description = '', $boardType = 0, $image = '', $imageNew = '', $imageShowAsBackground = 1, $imageBackgroundRepeat = 'no', $externalURL = '', $time = TIME_NOW, $prefixes = '', $prefixMode = 0, $prefixRequired = 0, $styleID = 0, $enforceStyle = 0, $daysPrune = 0, $sortField = '', $sortOrder = '', $postSortOrder = '', $isClosed = 0, $countUserPosts = 1, $isInvisible = 0, $showSubBoards = 1, $allowDescriptionHtml = 0, $enableRating = -1, $threadsPerPage = 0, $postsPerPage = 0, $searchable = 1, $searchableForSimilarThreads = 1, $ignorable = 1, $enableMarkingAsDone = 0, $additionalFields = array()) {
		// save data
		$boardID = self::insert($title, array_merge($additionalFields, array(
			'parentID' => $parentID,
			'description' => $description,
			'boardType' => $boardType,
			'image' => $image,
			'externalURL' => $externalURL,
			'time' => $time,
			'prefixes' => $prefixes,
			'prefixMode' => $prefixMode,
			'prefixRequired' => $prefixRequired,
			'styleID' => $styleID,
			'enforceStyle' => $enforceStyle,
			'daysPrune' => $daysPrune,
			'sortField' => $sortField,
			'sortOrder' => $sortOrder,
			'postSortOrder' => $postSortOrder,
			'isClosed' => $isClosed,
			'countUserPosts' => $countUserPosts,
			'isInvisible' => $isInvisible,
			'showSubBoards' => $showSubBoards,
			'allowDescriptionHtml' => $allowDescriptionHtml,
			'enableRating' => $enableRating,
			'threadsPerPage' => $threadsPerPage,
			'postsPerPage' => $postsPerPage,
			'imageNew' => $imageNew,
			'imageShowAsBackground' => $imageShowAsBackground,
			'imageBackgroundRepeat' => $imageBackgroundRepeat,
			'searchable' => $searchable,
			'searchableForSimilarThreads' => $searchableForSimilarThreads,
			'ignorable' => $ignorable,
			'enableMarkingAsDone' => $enableMarkingAsDone
		)));
		
		// get board
		$board = new BoardEditor($boardID, null, null, false);
		
		// save position
		$board->addPosition($parentID, $position);
		
		// return new board
		return $board;
	}
	
	/**
	 * Creates the board row in database table.
	 *
	 * @param 	string 		$title
	 * @param 	array		$additionalFields
	 * @return	integer		new board id
	 */
	public static function insert($title, $additionalFields = array()) { 
		$keys = $values = '';
		foreach ($additionalFields as $key => $value) {
			$keys .= ','.$key;
			if (is_int($value)) $values .= ",".$value;
			else $values .= ",'".escapeString($value)."'";
		}
		
		$sql = "INSERT INTO	wbb".WBB_N."_board
					(title
					".$keys.")
			VALUES		('".escapeString($title)."'
					".$values.")";
		WCF::getDB()->sendQuery($sql);
		return WCF::getDB()->getInsertID();
	}
	
	/**
	 * Updates the position of a board directly.
	 *
	 * @param	integer		$boardID
	 * @param	integer		$parentID
	 * @param	integer		$position
	 */
	public static function updatePosition($boardID, $parentID, $position) {
		$sql = "UPDATE	wbb".WBB_N."_board
			SET	parentID = ".$parentID."
			WHERE 	boardID = ".$boardID;
		WCF::getDB()->sendQuery($sql);
		
		$sql = "REPLACE INTO	wbb".WBB_N."_board_structure
					(boardID, parentID, position)
			VALUES		(".$boardID.", ".$parentID.", ".$position.")";
		WCF::getDB()->sendQuery($sql);
	}
}
?>