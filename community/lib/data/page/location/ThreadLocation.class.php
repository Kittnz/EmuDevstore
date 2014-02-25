<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');

// wcf imports
require_once(WCF_DIR.'lib/data/page/location/Location.class.php');

/**
 * ThreadLocation is an implementation of Location for the thread page.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.page.location
 * @category 	Burning Board
 */
class ThreadLocation implements Location {
	public $cachedThreadIDs = array();
	public $threads = null;
	
	/**
	 * @see Location::cache()
	 */
	public function cache($location, $requestURI, $requestMethod, $match) {
		$this->cachedThreadIDs[] = $match[1];
	}
	
	/**
	 * @see Location::get()
	 */
	public function get($location, $requestURI, $requestMethod, $match) {
		if ($this->threads == null) {
			$this->readThreads();
		}
		
		$threadID = $match[1];
		if (!isset($this->threads[$threadID])) {
			return '';
		}
		
		return WCF::getLanguage()->get($location['locationName'], array('$thread' => (!empty($this->threads[$threadID]['prefix']) ? WCF::getLanguage()->get(StringUtil::encodeHTML($this->threads[$threadID]['prefix'])).' ' : '').'<a href="index.php?page=Thread&amp;threadID='.$threadID.SID_ARG_2ND.'">'.StringUtil::encodeHTML($this->threads[$threadID]['topic']).'</a>'));
	}
	
	/**
	 * Gets threads.
	 */
	protected function readThreads() {
		$this->threads = array();
		
		if (!count($this->cachedThreadIDs)) {
			return;
		}
		
		// get accessible boards
		$boardIDs = Board::getAccessibleBoards();
		if (empty($boardIDs)) return;
		
		$sql = "SELECT	threadID, topic, prefix
			FROM	wbb".WBB_N."_thread
			WHERE	threadID IN (".implode(',', $this->cachedThreadIDs).")
				AND boardID IN (".$boardIDs.")
				AND isDeleted = 0
				AND isDisabled = 0";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->threads[$row['threadID']] = $row;
		}
	}
}
?>