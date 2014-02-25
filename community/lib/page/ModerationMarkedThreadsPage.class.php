<?php
require_once(WBB_DIR.'lib/page/ModerationThreadsPage.class.php');

/**
 * Shows the marked threads.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
class ModerationMarkedThreadsPage extends ModerationThreadsPage {
	public $action = 'markedThreads';
	public $pageName = 'ModerationMarkedThreads';
	
	/**
	 * Creates a new ModerationMarkedThreadsPage object.
	 */
	public function __construct() {
		if ($markedThreads = WCF::getSession()->getVar('markedThreads')) {
			$this->sqlConditions = 'thread.threadID IN ('.implode(',', $markedThreads).') AND movedThreadID = 0';
		}
		parent::__construct();
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		if (empty($this->sqlConditions)) return 0;
		return parent::countItems();
	}
}
?>