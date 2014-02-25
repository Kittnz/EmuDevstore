<?php
// wbb imports
require_once(WBB_DIR.'lib/data/thread/SubscribedThread.class.php');
require_once(WBB_DIR.'lib/data/thread/SubscribedThreadList.class.php');
require_once(WBB_DIR.'lib/data/board/SubscribedBoardList.class.php');

// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');

/**
 * Shows the subscription page.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
class SubscriptionsListPage extends SortablePage {
	public $defaultSortField = BOARD_DEFAULT_SORT_FIELD;
	public $defaultSortOrder = BOARD_DEFAULT_SORT_ORDER;
	public $defaultDaysPrune = BOARD_DEFAULT_DAYS_PRUNE;
	public $itemsPerPage = BOARD_THREADS_PER_PAGE;
	public $templateName = 'subscriptions';
	public $daysPrune = 0;
	
	public $threadList = null;
	public $boardList = null;
	
	/**
	 * Creates a new ModerationThreadsPage object.
	 */
	public function __construct() {
		SubscribedThread::clearSubscriptions();
		parent::__construct();
	}
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// default values
		if (WCF::getUser()->threadsPerPage) $this->itemsPerPage = WCF::getUser()->threadsPerPage;
		if (WCF::getUser()->threadDaysPrune) $this->defaultDaysPrune = WCF::getUser()->threadDaysPrune;
		
		// days prune
		if (isset($_REQUEST['daysPrune'])) $this->daysPrune = intval($_REQUEST['daysPrune']);
		if ($this->daysPrune < 1) $this->daysPrune = $this->defaultDaysPrune;
		
		// get thread list
		if ($this->threadList === null) $this->threadList = new SubscribedThreadList($this->daysPrune);
		// get board list
		if ($this->boardList === null) $this->boardList = new SubscribedBoardList();
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get threads
		if ($this->items) {
			$this->threadList->limit = $this->itemsPerPage;
			$this->threadList->offset = ($this->pageNo - 1) * $this->itemsPerPage;
			$this->threadList->sqlOrderBy = $this->sortField." ".$this->sortOrder .
							(($this->sortField == 'ratingResult') ? (", ratings ".$this->sortOrder) : ('')).
							(($this->sortField != 'lastPostTime') ? (", lastPostTime DESC") : (''));
			$this->threadList->readThreads();
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		// assign
		$this->boardList->renderBoards();
		WCF::getTPL()->assign(array(
			'threads' => $this->threadList->threads,
			'markedThreads' => count(SubscribedThread::getMarkedThreads()),
			'daysPrune' => $this->daysPrune,
			'allItems' => ($this->daysPrune != 1000 ? $this->threadList->countAllThreads() : $this->items)
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
		UserCPMenu::getInstance()->setActiveMenuItem('wcf.user.usercp.menu.link.management.subscriptions');
		
		parent::show();
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
			case 'lastPostTime': break;
			case 'ratingResult': if (THREAD_ENABLE_RATING) break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		return $this->threadList->countThreads();
	}
}
?>