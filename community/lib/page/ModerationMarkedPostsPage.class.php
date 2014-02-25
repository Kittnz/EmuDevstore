<?php
require_once(WBB_DIR.'lib/page/ModerationPostsPage.class.php');

/**
 * Shows the marked posts.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
class ModerationMarkedPostsPage extends ModerationPostsPage {
	public $action = 'markedPosts';
	public $pageName = 'ModerationMarkedPosts';
	
	/**
	 * Creates a new ModerationMarkedPostsPage object.
	 */
	public function __construct() {
		if ($markedPosts = WCF::getSession()->getVar('markedPosts')) {
			$this->sqlConditions = 'post.postID IN ('.implode(',', $markedPosts).')';
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