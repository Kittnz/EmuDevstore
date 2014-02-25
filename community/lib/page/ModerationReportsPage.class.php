<?php
require_once(WBB_DIR.'lib/page/ModerationPostsPage.class.php');
require_once(WBB_DIR.'lib/data/post/ReportsPostList.class.php');

/**
 * Shows the reported posts.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
class ModerationReportsPage extends ModerationPostsPage {
	public $templateName = 'moderationReports';
	public $action = 'reports';
	public $pageName = 'ModerationReports';
	public $neededPermissions = 'mod.board.canEditPost';
	
	/**
	 * Creates a new ModerationReportsPage object.
	 */
	public function __construct() {
		if ($this->postList === null) $this->postList = new ReportsPostList();
		parent::__construct();
	}
}
?>