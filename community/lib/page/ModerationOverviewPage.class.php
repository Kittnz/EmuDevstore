<?php
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');
require_once(WBB_DIR.'lib/data/board/Board.class.php');

/**
 * Shows the moderation control panel start page.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
class ModerationOverviewPage extends AbstractPage {
	public $templateName = 'moderationOverview';
	public $deletedThreads = 0, $hiddenThreads = 0, $markedThreads = 0, $deletedPosts = 0, $hiddenPosts = 0, $markedPosts = 0, $reports = 0;
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->countDeletedThreads();
		$this->countHiddenThreads();
		$this->countMarkedThreads();
		$this->countDeletedPosts();
		$this->countHiddenPosts();
		$this->countMarkedPosts();
		$this->countReports();
		WCF::getSession()->unregister('outstandingModerations');
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'deletedThreads' => $this->deletedThreads,
			'hiddenThreads' => $this->hiddenThreads,
			'markedThreads' => $this->markedThreads,
			'deletedPosts' => $this->deletedPosts,
			'hiddenPosts' => $this->hiddenPosts,
			'markedPosts' => $this->markedPosts,
			'reports' => $this->reports
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// active default tab
		UserCPMenu::getInstance()->setActiveMenuItem('wcf.user.usercp.menu.link.modcp.overview');
		
		// check permission
		WCF::getUser()->checkPermission(array('mod.board.canReadDeletedThread', 'mod.board.canEnableThread', 'mod.board.canReadDeletedPost', 'mod.board.canEnablePost'));
		
		parent::show();
	}
	
	/**
	 * Counts the deleted threads.
	 */
	protected function countDeletedThreads() {
		$boardIDs = Board::getModeratedBoards('canReadDeletedThread');
		
		if (!empty($boardIDs)) {
			$sql = "SELECT	COUNT(*) AS count
				FROM	wbb".WBB_N."_thread
				WHERE	isDeleted = 1
					AND boardID IN (".$boardIDs.")";
			$row = WCF::getDB()->getFirstRow($sql);
			$this->deletedThreads = $row['count'];
		}
	}
	
	/**
	 * Counts the disabled threads.
	 */
	protected function countHiddenThreads() {
		$boardIDs = Board::getModeratedBoards('canEnableThread');
		
		if (!empty($boardIDs)) {
			$sql = "SELECT	COUNT(*) AS count
				FROM	wbb".WBB_N."_thread
				WHERE	isDisabled = 1
					AND boardID IN (".$boardIDs.")
					AND movedThreadID = 0";
			$row = WCF::getDB()->getFirstRow($sql);
			$this->hiddenThreads = $row['count'];
		}
	}
	
	/**
	 * Counts the marked threads.
	 */
	protected function countMarkedThreads() {
		$this->markedThreads = (($markedThreads = WCF::getSession()->getVar('markedThreads')) ? count($markedThreads) : 0);
	}
	
	/**
	 * Counts the deleted posts.
	 */
	protected function countDeletedPosts() {
		$boardIDs = Board::getModeratedBoards('canReadDeletedPost');
		
		if (!empty($boardIDs)) {
			$sql = "SELECT		COUNT(*) AS count
				FROM		wbb".WBB_N."_post post
				LEFT JOIN	wbb".WBB_N."_thread thread
				ON		(thread.threadID = post.threadID)
				WHERE		post.isDeleted = 1
						AND thread.boardID IN (".$boardIDs.")";
			$row = WCF::getDB()->getFirstRow($sql);
			$this->deletedPosts = $row['count'];
		}
	}
	
	/**
	 * Counts the disabled posts.
	 */
	protected function countHiddenPosts() {
		$boardIDs = Board::getModeratedBoards('canEnablePost');
		
		if (!empty($boardIDs)) {
			$sql = "SELECT		COUNT(*) AS count
				FROM		wbb".WBB_N."_post post
				LEFT JOIN	wbb".WBB_N."_thread thread
				ON		(thread.threadID = post.threadID)
				WHERE		post.isDisabled = 1
						AND thread.boardID IN (".$boardIDs.")";
			$row = WCF::getDB()->getFirstRow($sql);
			$this->hiddenPosts = $row['count'];
		}
	}
	
	/**
	 * Counts the marked posts.
	 */
	protected function countMarkedPosts() {
		$this->markedPosts = (($markedPosts = WCF::getSession()->getVar('markedPosts')) ? count($markedPosts) : 0);
	}
	
	/**
	 * Counts the reported posts.
	 */
	protected function countReports() {
		$boardIDs = Board::getModeratedBoards('canEditPost');
		$boardIDs2 = Board::getModeratedBoards('canReadDeletedPost');
		
		if (!empty($boardIDs)) {
			$sql = "SELECT		COUNT(*) AS count
				FROM		wbb".WBB_N."_post_report report
				LEFT JOIN	wbb".WBB_N."_post post
				ON		(post.postID = report.postID)
				LEFT JOIN	wbb".WBB_N."_thread thread
				ON		(thread.threadID = post.threadID)
				WHERE		thread.boardID IN (".$boardIDs.")
						AND (post.isDeleted = 0".(!empty($boardIDs2) ? " OR thread.boardID IN (".$boardIDs2.")" : '').")";
			$row = WCF::getDB()->getFirstRow($sql);
			$this->reports = $row['count'];
		}
	}
}
?>