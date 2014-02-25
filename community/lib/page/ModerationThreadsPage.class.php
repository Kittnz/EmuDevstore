<?php
// wbb imports
require_once(WBB_DIR.'lib/data/thread/ThreadList.class.php');
require_once(WBB_DIR.'lib/data/board/Board.class.php');

// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');

/**
 * Provides default implementations for the pages of thread moderation.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
abstract class ModerationThreadsPage extends SortablePage {
	public $defaultSortField = BOARD_DEFAULT_SORT_FIELD;
	public $defaultSortOrder = BOARD_DEFAULT_SORT_ORDER;
	public $templateName = 'moderationThreads';
	public $neededPermissions = '';
	public $pageName = '';
	public $itemsPerPage = BOARD_THREADS_PER_PAGE;
	public $markedThreads = 0;
	
	public $threadList;
	protected $sqlConditions = '';
	
	/**
	 * Creates a new ModerationThreadsPage object.
	 */
	public function __construct() {
		if ($this->threadList === null) $this->threadList = new ThreadList();
		$this->threadList->sqlConditions .= $this->sqlConditions;
		parent::__construct();
	}
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (WCF::getUser()->threadsPerPage) $this->itemsPerPage = WCF::getUser()->threadsPerPage;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get data
		if ($this->items) {
			$this->threadList->limit = $this->itemsPerPage;
			$this->threadList->offset = ($this->pageNo - 1) * $this->itemsPerPage;
			$this->threadList->sqlOrderBy = $this->sortField." ".$this->sortOrder . (($this->sortField != 'lastPostTime') ? (", lastPostTime DESC") : (''));
			$this->threadList->readThreads();
		}
		
		// get marked threads
		$sessionVars = WCF::getSession()->getVars();
		if (isset($sessionVars['markedThreads'])) {
			$this->markedThreads = count($sessionVars['markedThreads']);
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'threads' => $this->threadList->threads,
			'markedThreads' => $this->markedThreads,
			'markedPosts' => 0,
			'permissions' => Board::getGlobalModeratorPermissions(),
			'page' => $this->pageName
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// active default tab
		UserCPMenu::getInstance()->setActiveMenuItem('wcf.user.usercp.menu.link.modcp.'.$this->action);
		
		// check permission
		if (!empty($this->neededPermissions)) WCF::getUser()->checkPermission($this->neededPermissions);
		
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