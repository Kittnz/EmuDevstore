<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches last posts and board clicks.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.cache
 * @category 	Burning Board
 */
class CacheBuilderBoardData implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$data = array('lastPosts' => array(), 'counts' => array());
		
		// counts
		$sql = "SELECT	boardID, clicks, threads, posts
			FROM 	wbb".WBB_N."_board";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$data['counts'][$row['boardID']] = $row;
		}
		
		// last posts
		$sql = "SELECT		thread.prefix, thread.topic, thread.lastPostTime,
					thread.lastPosterID, thread.lastPoster,
					last_post.*
			FROM 		wbb".WBB_N."_board_last_post last_post
			LEFT JOIN	wbb".WBB_N."_thread thread
			ON		(thread.threadID = last_post.threadID)
			ORDER BY	last_post.boardID,
					thread.lastPostTime DESC";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$data['lastPosts'][$row['boardID']][$row['languageID']] = $row;
		}
		
		return $data;
	}
}
?>