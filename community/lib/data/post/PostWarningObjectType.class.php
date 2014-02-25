<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/infraction/warning/object/WarningObjectType.class.php');

// wbb imports
require_once(WBB_DIR.'lib/data/post/PostWarningObject.class.php');
require_once(WBB_DIR.'lib/data/board/Board.class.php');

/**
 * 
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.post
 * @category 	Burning Board
 */
class PostWarningObjectType implements WarningObjectType {
	/**
	 * @see WarningObjectType::getObjectByID()
	 */
	public function getObjectByID($objectID) {
		if (is_array($objectID)) {
			$posts = array();
			$sql = "SELECT		post.*, thread.topic, thread.boardID
				FROM 		wbb".WBB_N."_post post
				LEFT JOIN	wbb".WBB_N."_thread thread
				ON		(thread.threadID = post.threadID)
				WHERE 		postID IN (".implode(',', $objectID).")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$posts[$row['postID']] = new PostWarningObject(null, $row);
			}
			
			return (count($posts) > 0 ? $posts : null); 
		}
		else {
			// get object
			$post = new PostWarningObject($objectID);
			if (!$post->postID) return null;
			
			// check permissions
			if (!class_exists('WBBACP')) { // ignore permission in acp
				$board = Board::getBoard($post->boardID);
				if (!$board->getPermission('canViewBoard') || !$board->getPermission('canEnterBoard')) return null;
			}
			
			// return object
			return $post;
		}
	}
}
?>