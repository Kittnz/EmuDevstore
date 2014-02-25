<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');

// wcf imports
require_once(WCF_DIR.'lib/data/page/location/Location.class.php');

/**
 * PostLocation is an implementation of Location for the thread page.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.page.location
 * @category 	Burning Board
 */
class PostLocation implements Location {
	public $cachedPostIDs = array();
	public $threads = null;
	
	/**
	 * @see Location::cache()
	 */
	public function cache($location, $requestURI, $requestMethod, $match) {
		$this->cachedPostIDs[] = $match[1];
	}
	
	/**
	 * @see Location::get()
	 */
	public function get($location, $requestURI, $requestMethod, $match) {
		if ($this->threads == null) {
			$this->readThreads();
		}
		
		$postID = $match[1];
		if (!isset($this->threads[$postID])) {
			return '';
		}
		
		return WCF::getLanguage()->get($location['locationName'], array('$thread' => (!empty($this->threads[$postID]['prefix']) ? WCF::getLanguage()->get(StringUtil::encodeHTML($this->threads[$postID]['prefix'])).' ' : '').'<a href="index.php?page=Thread&amp;postID='.$postID.SID_ARG_2ND.'#post'.$postID.'">'.StringUtil::encodeHTML($this->threads[$postID]['topic']).'</a>'));
	}
	
	/**
	 * Gets threads.
	 */
	protected function readThreads() {
		$this->threads = array();
		
		if (!count($this->cachedPostIDs)) {
			return;
		}
		
		// get accessible boards
		$boardIDs = Board::getAccessibleBoards();
		if (empty($boardIDs)) return;
		
		$sql = "SELECT		post.postID, thread.topic, thread.prefix
			FROM		wbb".WBB_N."_post post
			LEFT JOIN	wbb".WBB_N."_thread thread
			ON		(thread.threadID = post.threadID)
			WHERE		post.postID IN (".implode(',', $this->cachedPostIDs).")
					AND post.isDeleted = 0
					AND post.isDisabled = 0
					AND thread.boardID IN (".$boardIDs.")
					AND thread.isDeleted = 0
					AND thread.isDisabled = 0";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->threads[$row['postID']] = $row;
		}
	}
}
?>