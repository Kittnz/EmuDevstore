<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');

// wcf imports
require_once(WCF_DIR.'lib/data/page/location/Location.class.php');

/**
 * BoardLocation is an implementation of Location for the board page.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.page.location
 * @category 	Burning Board
 */
class BoardLocation implements Location {
	public $boards = null;
	
	/**
	 * @see Location::cache()
	 */
	public function cache($location, $requestURI, $requestMethod, $match) {}
	
	/**
	 * @see Location::get()
	 */
	public function get($location, $requestURI, $requestMethod, $match) {
		if ($this->boards == null) {
			$this->readBoards();
		}
		
		$boardID = $match[1];
		if (!isset($this->boards[$boardID]) || !$this->boards[$boardID]->getPermission()) {
			return '';
		}
		
		return WCF::getLanguage()->get($location['locationName'], array('$board' => '<a href="index.php?page=Board&amp;boardID='.$this->boards[$boardID]->boardID.SID_ARG_2ND.'">'.WCF::getLanguage()->get(StringUtil::encodeHTML($this->boards[$boardID]->title)).'</a>'));
	}
	
	/**
	 * Gets boards from cache.
	 */
	protected function readBoards() {
		$this->boards = WCF::getCache()->get('board', 'boards');
	}
}
?>