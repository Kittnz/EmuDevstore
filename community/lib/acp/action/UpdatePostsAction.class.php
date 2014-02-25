<?php
// wbb imports
require_once(WBB_DIR.'lib/acp/action/UpdateCounterAction.class.php');

/**
 * Updates the posts.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.action
 * @category 	Burning Board
 */
class UpdatePostsAction extends UpdateCounterAction {
	public $action = 'UpdatePosts';
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// count posts
		$sql = "SELECT	COUNT(*) AS count
			FROM	wbb".WBB_N."_post";
		$row = WCF::getDB()->getFirstRow($sql);
		$count = $row['count'];
		
		// get postids
		$postIDs = '';
		$sql = "SELECT		postID
			FROM		wbb".WBB_N."_post
			ORDER BY	postID";
		$result = WCF::getDB()->sendQuery($sql, $this->limit, ($this->limit * $this->loop));
		while ($row = WCF::getDB()->fetchArray($result)) {
			$postIDs .= ','.$row['postID'];
		}
		
		if (empty($postIDs)) {
			$this->calcProgress();
			$this->finish();
		}
		
		// update posts
		$sql = "UPDATE	wbb".WBB_N."_post post
			SET	attachments = IFNULL((
					SELECT	COUNT(*)
					FROM	wcf".WCF_N."_attachment attachment
					WHERE	attachment.packageID = ".PACKAGE_ID."
						AND attachment.containerID = post.postID
						AND attachment.containerType = 'post'
				), 0)
			WHERE	post.postID IN (0".$postIDs.")";
		WCF::getDB()->sendQuery($sql);
		$this->executed();
		
		$this->calcProgress(($this->limit * $this->loop), $count);
		$this->nextLoop();
	}
}
?>