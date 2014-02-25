<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');

// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');

/**
 * Shows the board page.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
class BoardPage extends SortablePage {
	// default values
	public $defaultSortField = BOARD_DEFAULT_SORT_FIELD;
	public $defaultSortOrder = BOARD_DEFAULT_SORT_ORDER;
	public $defaultDaysPrune = BOARD_DEFAULT_DAYS_PRUNE;
	public $itemsPerPage = BOARD_THREADS_PER_PAGE;
	
	// board data
	public $boardID = 0;
	public $board;
	public $enableRating = THREAD_ENABLE_RATING;
	
	// parameters
	public $prefix = '';
	public $daysPrune;
	public $status = '';
	public $languageID = 0;
	
	// system
	public $templateName = 'board';
	public $boardList = null;
	public $threadList = null;
	public $markedPosts = 0, $markedThreads = 0;
	public $topThreadsStatus = 1, $normalThreadsStatus = 1;
	public $boardModerators = array();
	public $tags = array();
	
	/**
	 * tag id
	 *
	 * @var integer
	 */
	public $tagID = 0;
	
	/**
	 * tag object
	 *
	 * @var Tag
	 */
	public $tag = null;
	
	/**
	 * Reads the given parameters.
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get board id
		if (isset($_REQUEST['boardID'])) $this->boardID = intval($_REQUEST['boardID']);
		else if (isset($_REQUEST['boardid'])) $this->boardID = intval($_REQUEST['boardid']); // wbb2 style
		if (isset($_REQUEST['prefix'])) $this->prefix = $_REQUEST['prefix'];
		if (isset($_REQUEST['status'])) $this->status = $_REQUEST['status'];
		if (isset($_REQUEST['languageID'])) $this->languageID = intval($_REQUEST['languageID']);
		if (isset($_REQUEST['tagID'])) $this->tagID = intval($_REQUEST['tagID']);
		
		// get board
		$this->board = new Board($this->boardID);
		
		// threads per page
		if ($this->board->threadsPerPage) $this->itemsPerPage = $this->board->threadsPerPage;
		if (WCF::getUser()->threadsPerPage) $this->itemsPerPage = WCF::getUser()->threadsPerPage;
		
		// enter board
		$this->board->enter();
		
		// redirect to external url if given
		if ($this->board->isExternalLink()) {
			if (!WCF::getSession()->spiderID) {
				// count redirects
				$sql = "UPDATE	wbb".WBB_N."_board
					SET	clicks = clicks + 1
					WHERE	boardID = ".$this->boardID;
				WCF::getDB()->registerShutdownUpdate($sql);
				
				// reset cache
				WCF::getCache()->clearResource('boardData');
			}
			
			// do redirect
			HeaderUtil::redirect($this->board->externalURL, false);
			exit;
		}
		
		// get sorting values
		if ($this->board->sortField) $this->defaultSortField = $this->board->sortField;
		if ($this->board->sortOrder) $this->defaultSortOrder = $this->board->sortOrder;
		if ($this->board->daysPrune) $this->defaultDaysPrune = $this->board->daysPrune;
		if (WCF::getUser()->threadDaysPrune) $this->defaultDaysPrune = WCF::getUser()->threadDaysPrune;
		
		// thread rating
		if ($this->board->enableRating != -1) $this->enableRating = $this->board->enableRating;
		
		// days prune
		if (isset($_REQUEST['daysPrune'])) $this->daysPrune = intval($_REQUEST['daysPrune']);
		if ($this->daysPrune < 1) $this->daysPrune = $this->defaultDaysPrune;
		
		// status filter
		if (!empty($this->status)) {
			switch ($this->status) {
				case 'read':
				case 'unread': 
				case 'open':
				case 'closed': 
				case 'deleted': 
				case 'hidden': 
				case 'done':
				case 'undone': break;
				default: $this->status = '';
			}
		}
		
		if ($this->board->isBoard()) {
			if (MODULE_TAGGING && $this->tagID) {
				require_once(WCF_DIR.'lib/data/tag/TagEngine.class.php');
				$this->tag = TagEngine::getInstance()->getTagByID($this->tagID);
				if ($this->tag === null) {
					throw new IllegalLinkException();
				}
				require_once(WBB_DIR.'lib/data/thread/TaggedBoardThreadList.class.php');
				$this->threadList = new TaggedBoardThreadList($this->tagID, $this->board, $this->daysPrune, $this->prefix, $this->status, $this->languageID);
			}
			else {
				require_once(WBB_DIR.'lib/data/thread/BoardThreadList.class.php');
				$this->threadList = new BoardThreadList($this->board, $this->daysPrune, $this->prefix, $this->status, $this->languageID);
			}
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// generate list of subboards
		$this->renderBoards();
		
		// get threads
		if ($this->threadList != null) {
			$this->threadList->limit = $this->itemsPerPage;
			$this->threadList->offset = ($this->pageNo - 1) * $this->itemsPerPage;
			$this->threadList->sqlOrderBy = "thread.isAnnouncement DESC, thread.isSticky DESC,
							".($this->sortField != 'ratingResult' ? 'thread.' : '').$this->sortField." ".$this->sortOrder. 
							(($this->sortField == 'ratingResult') ? (", thread.ratings ".$this->sortOrder) : ('')).
							(($this->sortField != 'lastPostTime') ? (", thread.lastPostTime DESC") : (''));
			$this->threadList->readThreads();
		}
		
		// show online list
		if (MODULE_USERS_ONLINE && BOARD_ENABLE_ONLINE_LIST) {
			$this->renderOnlineList();
		}
		
		// show moderators
		if (BOARD_ENABLE_MODERATORS) {
			$this->renderModerators();
		}
		
		// update subscription
		if (WCF::getUser()->userID) {
			WCF::getUser()->updateBoardSubscription($this->boardID);
		}
		
		// get marked posts
		$sessionVars = WCF::getSession()->getVars();
		if (isset($sessionVars['markedPosts'])) {
			$this->markedPosts = count($sessionVars['markedPosts']);
		}
		if (isset($sessionVars['markedThreads'])) {
			$this->markedThreads = count($sessionVars['markedThreads']);
		}
		
		// get list status
		if (WCF::getUser()->userID) {
			$this->topThreadsStatus = intval(WCF::getUser()->topThreadsStatus);
			$this->normalThreadsStatus = intval(WCF::getUser()->normalThreadsStatus);
		}
		else {
			if (WCF::getSession()->getVar('topThreadsStatus') !== null) $this->topThreadsStatus = WCF::getSession()->getVar('topThreadsStatus');
			if (WCF::getSession()->getVar('normalThreadsStatus') !== null) $this->normalThreadsStatus = WCF::getSession()->getVar('normalThreadsStatus');
		}
		
		// tags
		if (MODULE_TAGGING && THREAD_ENABLE_TAGS && BOARD_ENABLE_TAGS && $this->board->isBoard()) {
			$this->readTags();
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'permissions' => $this->board->getModeratorPermissions(),
			'selfLink' => 'index.php?page=Board&boardID=' . $this->boardID . SID_ARG_2ND_NOT_ENCODED,
			'daysPrune' => $this->daysPrune,
			'markedPosts' => $this->markedPosts,
			'markedThreads' => $this->markedThreads,
			'board' => $this->board,
			'boardID' => $this->boardID,
			'prefix' => $this->prefix,
			'boardQuickJumpOptions' => Board::getBoardSelect(),
			'status' => $this->status,
			'boardModerators' => $this->boardModerators,
			'normalThreads' => ($this->threadList != null ? $this->threadList->threads : null),
			'topThreads' => ($this->threadList != null ? $this->threadList->topThreads : null),
			'newTopThreads' => ($this->threadList != null ? $this->threadList->newTopThreads : 0),
			'newNormalThreads' => ($this->threadList != null ? $this->threadList->newThreads : 0),
			'topThreadsStatus' => $this->topThreadsStatus,
			'normalThreadsStatus' => $this->normalThreadsStatus,
			'allowSpidersToIndexThisPage' => true,
			'defaultSortField' => $this->defaultSortField,
			'defaultSortOrder' => $this->defaultSortOrder,
			'defaultDaysPrune' => $this->defaultDaysPrune,
			'languageID' => $this->languageID,
			'contentLanguages' => Language::getContentLanguages(),
			'enableRating' => $this->enableRating,
			'tags' => $this->tags,
			'tagID' => $this->tagID,
			'tag' => $this->tag
		));
		
		if (WCF::getSession()->spiderID) {
			if ($this->threadList != null && $this->threadList->maxLastPostTime) {
				@header('Last-Modified: '.gmdate('D, d M Y H:i:s', $this->threadList->maxLastPostTime).' GMT');
			}
		}
	}
	
	/**
	 * Renders the moderators of this board for template output.
	 */
	protected function renderModerators() {
		$moderators = WCF::getCache()->get('board', 'moderators');
		if (isset($moderators[$this->boardID])) {
			$this->boardModerators = $moderators[$this->boardID];
		}
	}
	
	/**
	 * Wrapper for BoardList->renderBoards()
	 * @see BoardList::renderBoards()
	 */
	protected function renderBoards() {
		if ($this->boardList === null) {
			require_once(WBB_DIR.'lib/data/board/BoardList.class.php');
			$this->boardList = new BoardList($this->boardID);
		}
		$this->boardList->maxDepth = BOARD_BOARD_LIST_DEPTH;
		$this->boardList->renderBoards();
	}
		
	/**
	 * Wrapper for UsersOnlineList->renderOnlineList()
	 * @see UsersOnlineList::renderOnlineList()
	 */
	protected function renderOnlineList() {
		require_once(WCF_DIR.'lib/data/user/usersOnline/UsersOnlineList.class.php');
		$usersOnlineList = new UsersOnlineList('session.boardID = '.$this->boardID);
		$usersOnlineList->renderOnlineList();
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		if ($this->threadList == null) return 0;
		return $this->threadList->countThreads();
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'prefix':
			case 'topic':
			case 'username':
			case 'time':
			case 'views':
			case 'replies':
			case 'lastPostTime':
			case 'attachments':
			case 'polls': break;
			case 'ratingResult': if ($this->enableRating) break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * Reads the tags of this board.
	 */
	protected function readTags() {
		// include files
		require_once(WBB_DIR.'lib/data/board/BoardTagCloud.class.php');
		
		// get tags
		$tagCloud = new BoardTagCloud($this->boardID, WCF::getSession()->getVisibleLanguageIDArray());
		$this->tags = $tagCloud->getTags();
	}
}
?>