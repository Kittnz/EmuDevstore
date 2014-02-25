<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');

// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');
require_once(WBB_DIR.'lib/data/post/PostEditor.class.php');

/**
 * Empties the recycle bin of posts.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	action
 * @category 	Burning Board
 */
class EmptyPostRecycleBinAction extends AbstractSecureAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permissions
		$boardIDs = Board::getModeratedBoards('canDeletePostCompletely');
		if (empty($boardIDs)) {
			throw new PermissionDeniedException();
		}
		
		// delete posts
		$postIDArray = array();
		$sql = "SELECT	postID
			FROM	wbb".WBB_N."_post
			WHERE	isDeleted = 1
				AND threadID IN (
					SELECT	threadID
					FROM	wbb".WBB_N."_thread
					WHERE	boardID IN (".$boardIDs.")
						AND isDeleted = 0
				)";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$postIDArray[] = $row['postID'];
		}
		if (count($postIDArray)) {
			PostEditor::deleteAllCompletely(implode(',', $postIDArray));
		}
		$this->executed();
		
		// forward
		HeaderUtil::redirect('index.php?page=ModerationDeletedPosts'.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>