<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');
require_once(WBB_DIR.'lib/data/post/ModerationPostList.class.php');

// wcf imports
require_once(WCF_DIR.'lib/page/MultipleLinkPage.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');

/**
 * Provides default implementations for the pages of post moderation.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
abstract class ModerationPostsPage extends MultipleLinkPage {
	public $templateName = 'moderationPosts';
	public $neededPermissions = '';
	public $pageName = '';
	public $markedPosts = 0;
	public $itemsPerPage = THREAD_POSTS_PER_PAGE;
	
	public $postList = null;
	protected $sqlConditions = '';
	
	/**
	 * Creates a new ModerationPostsPage object.
	 */
	public function __construct() {
		if ($this->postList === null) $this->postList = new ModerationPostList();
		$this->postList->sqlConditions .= $this->sqlConditions;
		parent::__construct();
	}
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (WCF::getUser()->postsPerPage) $this->itemsPerPage = WCF::getUser()->postsPerPage;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get posts
		if ($this->items) {
			$this->postList->offset = ($this->pageNo - 1) * $this->itemsPerPage;
			$this->postList->limit = $this->itemsPerPage;
			$this->postList->readPosts();
		}
		
		// get marked posts
		$sessionVars = WCF::getSession()->getVars();
		if (isset($sessionVars['markedPosts'])) {
			$this->markedPosts = count($sessionVars['markedPosts']);
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		// assign variables
		WCF::getTPL()->assign(array(
			'posts' => $this->postList->posts,
			'attachments' => $this->postList->attachments,
			'markedThreads' => 0,
			'markedPosts' => $this->markedPosts,
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
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		return $this->postList->countPosts();
	}
}
?>