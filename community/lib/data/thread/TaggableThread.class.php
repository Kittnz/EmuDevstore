<?php
// wbb imports
require_once(WBB_DIR.'lib/data/thread/TaggedThread.class.php');
require_once(WBB_DIR.'lib/data/board/Board.class.php');

// wcf imports
require_once(WCF_DIR.'lib/data/tag/AbstractTaggableObject.class.php');

/**
 * An implementation of Taggable to support the tagging of threads.
 *
 * @author 	Arian Glander
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.thread
 * @category 	Burning Board
 */
class TaggableThread extends AbstractTaggableObject {
	/**
	 * @see Taggable::getObjectsByIDs()
	 */
	public function getObjectsByIDs($objectIDs, $taggedObjects) {
		$sql = "SELECT		*
			FROM		wbb".WBB_N."_thread
			WHERE		threadID IN (" . implode(",", $objectIDs) . ")
					AND isDeleted = 0
					AND isDisabled = 0";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$row['taggable'] = $this;
			$taggedObjects[] = new TaggedThread(null, $row);
		}
		return $taggedObjects;
	}
	
	/**
	 * @see Taggable::countObjectsByTagID()
	 */
	public function countObjectsByTagID($tagID) {
		$accessibleBoardIDArray = Board::getAccessibleBoardIDArray();
		if (count($accessibleBoardIDArray) == 0) return 0;
		
		$sql = "SELECT		COUNT(*) AS count
			FROM		wcf".WCF_N."_tag_to_object tag_to_object
			LEFT JOIN	wbb".WBB_N."_thread thread
			ON		(thread.threadID = tag_to_object.objectID)
			WHERE 		tag_to_object.tagID = ".$tagID."
					AND tag_to_object.taggableID = ".$this->getTaggableID()."
					AND thread.boardID IN (".implode(',', $accessibleBoardIDArray).")
					AND thread.isDeleted = 0
					AND thread.isDisabled = 0";
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * @see Taggable::getObjectsByTagID()
	 */
	public function getObjectsByTagID($tagID, $limit = 0, $offset = 0) {
		$accessibleBoardIDArray = Board::getAccessibleBoardIDArray();
		if (count($accessibleBoardIDArray) == 0) return array();
		
		$sqlThreadVisitSelect = $sqlThreadVisitJoin = $sqlSubscriptionSelect = $sqlSubscriptionJoin = $sqlOwnPostsSelect = $sqlOwnPostsJoin = '';
		if (WCF::getUser()->userID != 0) {
			$sqlThreadVisitSelect = ', thread_visit.lastVisitTime';
			$sqlThreadVisitJoin = " LEFT JOIN 	wbb".WBB_N."_thread_visit thread_visit 
						ON 		(thread_visit.threadID = thread.threadID
								AND thread_visit.userID = ".WCF::getUser()->userID.")";
			$sqlSubscriptionSelect = ', IF(thread_subscription.userID IS NOT NULL, 1, 0) AS subscribed';
			$sqlSubscriptionJoin = " LEFT JOIN 	wbb".WBB_N."_thread_subscription thread_subscription 
						ON 		(thread_subscription.userID = ".WCF::getUser()->userID."
								AND thread_subscription.threadID = thread.threadID)";
			
			if (BOARD_THREADS_ENABLE_OWN_POSTS) {
				$sqlOwnPostsSelect = "DISTINCT post.userID AS ownPosts,";
				$sqlOwnPostsJoin = "	LEFT JOIN	wbb".WBB_N."_post post
							ON 		(post.threadID = thread.threadID
									AND post.userID = ".WCF::getUser()->userID.")";
			}
		}
		
		$threads = array();
		$sql = "SELECT		".$sqlOwnPostsSelect."
					thread.*,
					board.boardID, board.title
					".$sqlThreadVisitSelect."
					".$sqlSubscriptionSelect."
			FROM		wcf".WCF_N."_tag_to_object tag_to_object
			LEFT JOIN	wbb".WBB_N."_thread thread
			ON		(thread.threadID = tag_to_object.objectID)
			LEFT JOIN 	wbb".WBB_N."_board board
			ON 		(board.boardID = thread.boardID)
			".$sqlOwnPostsJoin."
			".$sqlThreadVisitJoin."
			".$sqlSubscriptionJoin."
			WHERE		tag_to_object.tagID = ".$tagID."
					AND tag_to_object.taggableID = ".$this->getTaggableID()."
					AND thread.boardID IN (".implode(',', $accessibleBoardIDArray).")
					AND thread.isDeleted = 0
					AND thread.isDisabled = 0
			ORDER BY	thread.lastPostTime DESC";
		$result = WCF::getDB()->sendQuery($sql, $limit, $offset);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$row['taggable'] = $this;
			$threads[] = new TaggedThread(null, $row);
		}
		return $threads;
	}

	/**
	 * @see Taggable::getIDFieldName()
	 */
	public function getIDFieldName() {
		return 'threadID';
	}
	
	/**
	 * @see Taggable::getResultTemplateName()
	 */
	public function getResultTemplateName() {
		return 'taggedThreads';
	}
	
	/**
	 * @see Taggable::getSmallSymbol()
	 */
	public function getSmallSymbol() {
		return StyleManager::getStyle()->getIconPath('threadS.png');
	}

	/**
	 * @see Taggable::getMediumSymbol()
	 */
	public function getMediumSymbol() {
		return StyleManager::getStyle()->getIconPath('threadM.png');
	}
	
	/**
	 * @see Taggable::getLargeSymbol()
	 */
	public function getLargeSymbol() {
		return StyleManager::getStyle()->getIconPath('threadL.png');
	}
}
?>