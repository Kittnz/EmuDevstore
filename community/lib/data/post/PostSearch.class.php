<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/search/AbstractSearchableMessageType.class.php');

/**
 * An implementation of SearchableMessageType for searching in forum posts.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.post
 * @category 	Burning Board
 */
class PostSearch extends AbstractSearchableMessageType {
	public $messageCache = array();
	public $boardIDs = array();
	public $threadID = 0;
	public $findAttachments = 0;
	public $findPolls = 0;
	public $findThreads = SEARCH_FIND_THREADS;
	public $findUserThreads = 0;
	public $threadTableJoin = false;
	
	public $boards = array();
	public $boardStructure = array();
	public $selectedBoards = array();
	
	/**
	 * Caches the data of the messages with the given ids.
	 */
	public function cacheMessageData($messageIDs, $additionalData = null) {
		if ($additionalData !== null && isset($additionalData['findThreads']) && $additionalData['findThreads'] == 1) {
			WCF::getTPL()->assign('findThreads', 1);
			
			$sqlThreadVisitSelect = $sqlThreadVisitJoin = $sqlSubscriptionSelect = $sqlSubscriptionJoin = $sqlOwnPostsSelect = $sqlOwnPostsJoin = '';
			if (WCF::getUser()->userID != 0) {
				$sqlThreadVisitSelect = ', thread_visit.lastVisitTime';
				$sqlThreadVisitJoin = " LEFT JOIN 	wbb".WBB_N."_thread_visit thread_visit 
							ON 		(thread_visit.threadID = thread.threadID
									AND thread_visit.userID = ".WCF::getUser()->userID.")";
				$sqlSubscriptionSelect = ', IF(thread_subscription.userID IS NOT NULL, 1, 0) AS subscribed';
				$sqlSubscriptionJoin = " LEFT JOIN 	wbb".WBB_N."_thread_subscription thread_subscription 
							ON 		(thread_subscription.userID = ".WCF::getUser()->userID."
									AND thread_subscription.threadID = thread.threadID)";
				
				if (BOARD_THREADS_ENABLE_OWN_POSTS) {
					$sqlOwnPostsSelect = "DISTINCT post.userID AS ownPosts,";
					$sqlOwnPostsJoin = "	LEFT JOIN	wbb".WBB_N."_post post
								ON 		(post.threadID = thread.threadID
										AND post.userID = ".WCF::getUser()->userID.")";
				}
			}
			
			$sql = "SELECT		".$sqlOwnPostsSelect."
						thread.*,
						board.boardID, board.title, board.enableMarkingAsDone
						".$sqlThreadVisitSelect."
						".$sqlSubscriptionSelect."
				FROM		wbb".WBB_N."_thread thread
				LEFT JOIN 	wbb".WBB_N."_board board
				ON 		(board.boardID = thread.boardID)
				".$sqlOwnPostsJoin."
				".$sqlThreadVisitJoin."
				".$sqlSubscriptionJoin."
				WHERE		thread.threadID IN (".$messageIDs.")";
			$result = WCF::getDB()->sendQuery($sql);
			require_once(WBB_DIR.'lib/data/thread/ThreadSearchResult.class.php');
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->messageCache[$row['threadID']] = array('type' => 'post', 'message' => new ThreadSearchResult(null, $row));
			}
		}
		else {
			$sql = "SELECT		post.*,
						thread.topic, thread.prefix,
						board.boardID, board.title
				FROM		wbb".WBB_N."_post post
				LEFT JOIN 	wbb".WBB_N."_thread thread
				ON 		(thread.threadID = post.threadID)
				LEFT JOIN 	wbb".WBB_N."_board board
				ON 		(board.boardID = thread.boardID)
				WHERE		post.postID IN (".$messageIDs.")";
			$result = WCF::getDB()->sendQuery($sql);
			require_once(WBB_DIR.'lib/data/post/PostSearchResult.class.php');
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->messageCache[$row['postID']] = array('type' => 'post', 'message' => new PostSearchResult(null, $row));
			}
		}
	}
	
	/**
	 * @see SearchableMessageType::getMessageData()
	 */
	public function getMessageData($messageID, $additionalData = null) {
		if (isset($this->messageCache[$messageID])) return $this->messageCache[$messageID];
		return null;
	}
	
	/**
	 * Shows post specific form elements in the global search form.
	 */
	public function show($form = null) {
		// get unsearchable boards
		require_once(WBB_DIR.'lib/data/board/Board.class.php');
		$boards = WCF::getCache()->get('board', 'boards');
		$unsearchableBoardIDArray = array();
		foreach ($boards as $board) {
			if (!$board->searchable) $unsearchableBoardIDArray[] = $board->boardID;
		}
		
		// get existing values
		if ($form !== null && isset($form->searchData['additionalData']['post'])) {
			$this->boardIDs = $form->searchData['additionalData']['post']['boardIDs'];
			$this->findAttachments = $form->searchData['additionalData']['post']['findAttachments'];
			$this->findPolls = $form->searchData['additionalData']['post']['findPolls'];
		}
		
		WCF::getTPL()->assign(array(
			'boardOptions' => Board::getBoardSelect(array('canViewBoard', 'canEnterBoard', 'canReadThread'), true, false, $unsearchableBoardIDArray),
			'boardIDs' => $this->boardIDs,
			'threadID' => $this->threadID,
			'selectAllBoards' => count($this->boardIDs) == 0 || $this->boardIDs[0] == '*',
			'findAttachments' => $this->findAttachments,
			'findPolls' => $this->findPolls,
			'findThreads' => $this->findThreads,
			'findUserThreads' => $this->findUserThreads
		));
	}
	
	/**
	 * Reads the given form parameters.
	 */
	protected function readFormParameters($form = null) {
		// get existing values
		if ($form !== null && isset($form->searchData['additionalData']['post'])) {
			$this->boardIDs = $form->searchData['additionalData']['post']['boardIDs'];
			$this->findAttachments = $form->searchData['additionalData']['post']['findAttachments'];
			$this->findPolls = $form->searchData['additionalData']['post']['findPolls'];
			//$this->findThreads = $form->searchData['additionalData']['post']['findThreads'];
			$this->findUserThreads = $form->searchData['additionalData']['post']['findUserThreads'];
			$this->threadID = $form->searchData['additionalData']['post']['threadID'];
		}
		
		// get new values
		if (isset($_POST['boardIDs']) && is_array($_POST['boardIDs'])) {
			$this->boardIDs = ArrayUtil::toIntegerArray($_POST['boardIDs']);
		}
		
		if (isset($_POST['findAttachments'])) {
			$this->findAttachments = intval($_POST['findAttachments']);
		}
		
		if (isset($_POST['findPolls'])) {
			$this->findPolls = intval($_POST['findPolls']);
		}
		
		if (isset($_POST['findThreads'])) {
			$this->findThreads = intval($_POST['findThreads']);
		}
		
		if (isset($_REQUEST['findUserThreads'])) {
			$this->findUserThreads = intval($_REQUEST['findUserThreads']);
			if ($this->findUserThreads) $this->findThreads = 1;
		}
		
		if (isset($_POST['threadID'])) {
			$this->threadID = intval($_POST['threadID']);
		}
	}
	
	private function includeSubBoards($boardID) {
		if (isset($this->boardStructure[$boardID])) {
			foreach ($this->boardStructure[$boardID] as $childBoardID) {
				if (!isset($this->selectedBoards[$childBoardID])) {
					$this->selectedBoards[$childBoardID] = $this->boards[$childBoardID];
					
					// include children
					$this->includeSubBoards($childBoardID);
				}
			}
		}
	}
	
	/**
	 * Returns the conditions for a search in the table of this search type.
	 */
	public function getConditions($form = null) {
		$this->readFormParameters($form);
		
		$boardIDs = $this->boardIDs;
		if (count($boardIDs) && $boardIDs[0] == '*') $boardIDs = array();
		
		// remove empty elements
		foreach ($boardIDs as $key => $boardID) {
			if ($boardID == '-') unset($boardIDs[$key]);
		}
		
		// get all boards from cache
		require_once(WBB_DIR.'lib/data/board/Board.class.php');
		$this->boards = WCF::getCache()->get('board', 'boards');
		$this->boardStructure = WCF::getCache()->get('board', 'boardStructure');
		$this->selectedBoards = array();
		
		// check whether the selected board does exist
		foreach ($boardIDs as $boardID) {
			if (!isset($this->boards[$boardID]) || !$this->boards[$boardID]->searchable) {
				throw new UserInputException('boardIDs', 'notValid');
			}
			
			if (!isset($this->selectedBoards[$boardID])) {
				$this->selectedBoards[$boardID] = $this->boards[$boardID];
				
				// include children
				$this->includeSubBoards($boardID);
			}
		}
		if (count($this->selectedBoards) == 0) $this->selectedBoards = $this->boards;
		
		// check permission of the active user
		foreach ($this->selectedBoards as $board) {
			if (WCF::getUser()->isIgnoredBoard($board->boardID) || !$board->getPermission() || !$board->getPermission('canEnterBoard') || !$board->getPermission('canReadThread') || !$board->searchable) {
				unset($this->selectedBoards[$board->boardID]);
			}
		}
		
		if (count($this->selectedBoards) == 0) {
			throw new PermissionDeniedException();
		}
		
		// build board id list
		$selectedBoardIDs = '';
		if (count($this->selectedBoards) != count($this->boards)) {
			foreach ($this->selectedBoards as $board) {
				if (!empty($selectedBoardIDs)) $selectedBoardIDs .= ',';
				$selectedBoardIDs .= $board->boardID;
			}
		}
		
		// build final condition
		require_once(WCF_DIR.'lib/system/database/ConditionBuilder.class.php');
		$condition = new ConditionBuilder(false);
		
		// board ids
		if (!empty($selectedBoardIDs)) {
			$this->threadTableJoin = true;
			$condition->add('thread.threadID = messageTable.threadID');
			$condition->add('thread.boardID IN ('.$selectedBoardIDs.')');
		}
		else if ($this->findThreads || count(WCF::getSession()->getVisibleLanguageIDArray()) || $this->threadTableJoin) {
			$condition->add('thread.threadID = messageTable.threadID');
		}
		
		// find user threads
		if ($this->findUserThreads && $form !== null && ($userIDs = $form->getUserIDs())) {
			$condition->add('thread.userID IN ('.implode(',', $userIDs).')');
		}
		
		// thread id
		if ($this->threadID != 0) {
			$condition->add('messageTable.threadID = '.$this->threadID);
		}
		$condition->add('messageTable.isDeleted = 0');
		$condition->add('messageTable.isDisabled = 0');
		// find attachments
		if ($this->findAttachments) $condition->add('messageTable.attachments > 0');
		// find polls
		if ($this->findPolls) $condition->add('messageTable.pollID > 0');
		// language
		if (count(WCF::getSession()->getVisibleLanguageIDArray())) $condition->add('thread.languageID IN ('.implode(',', WCF::getSession()->getVisibleLanguageIDArray()).')');
		
		// return sql condition
		return '('.$condition->get().')'.($this->findThreads ? '/* findThreads */' : '').($this->findUserThreads ? '/* findUserThreads */' : '');
	}
	
	/**
	 * @see SearchableMessageType::getJoins()
	 */
	public function getJoins() {
		return (($this->threadTableJoin || $this->findThreads || count(WCF::getSession()->getVisibleLanguageIDArray())) ? ", wbb".WBB_N."_thread thread" : '');
	}
	
	/**
	 * Returns the database table name for this search type.
	 */
	public function getTableName() {
		return 'wbb'.WBB_N.'_post';
	}
	
	/**
	 * Returns the message id field name for this search type.
	 */
	public function getIDFieldName() {
		return ($this->findThreads ? 'thread.threadID' : 'postID');
	}
	
	/**
	 * @see SearchableMessageType::getAdditionalData()
	 */
	public function getAdditionalData() {
		return array(
			'findThreads' => $this->findThreads,
			'findAttachments' => $this->findAttachments,
			'findPolls' => $this->findPolls,
			'findUserThreads' => $this->findUserThreads,
			'boardIDs' => $this->boardIDs,
			'threadID' => $this->threadID
		);
	}
	
	/**
	 * @see SearchableMessageType::getFormTemplateName()
	 */
	public function getFormTemplateName() {
		return 'searchPost';
	}
	
	/**
	 * @see SearchableMessageType::getResultTemplateName()
	 */
	public function getResultTemplateName() {
		return 'searchResultPost';
	}
}
?>