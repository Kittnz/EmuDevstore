<?php
// wbb imports
require_once(WBB_DIR.'lib/data/thread/Thread.class.php');

// wcf imports
require_once(WCF_DIR.'lib/system/style/StyleManager.class.php');

/**
 * Represents a viewable thread in the forum.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.thread
 * @category 	Burning Board
 */
class ViewableThread extends Thread {
	/**
	 * Handles the given resultset.
	 *
	 * @param 	array 		$row		resultset with thread data form database
	 */
	protected function handleData($data) {
		parent::handleData($data);
		
		// handle moved threads
		$this->data['realThreadID'] = $this->threadID;
		if ($this->movedThreadID != 0) $this->data['threadID'] = $this->movedThreadID;
		
		// get last visit time
		if (!$this->lastVisitTime && WCF::getUser()->userID == 0) {
			// user is guest; get thread visit from session
			$this->data['lastVisitTime'] = WCF::getUser()->getThreadVisitTime($this->threadID);
		}
		
		if ($this->lastVisitTime < WCF::getUser()->getBoardVisitTime($this->boardID)) {
			$this->data['lastVisitTime'] = WCF::getUser()->getBoardVisitTime($this->boardID);
		}
	}
	
	/**
	 * Gets the thread rating result for template output.
	 *
	 * @return	string		thread rating result for template output
	 */
	public function getRatingOutput() {
		$rating = $this->getRating();
		if ($rating !== false) $roundedRating = round($rating, 0);
		else $roundedRating = 0;
		$description = '';
		if ($this->ratings > 0) {
			$description = WCF::getLanguage()->get('wbb.board.vote.description', array('$votes' => StringUtil::formatNumeric($this->ratings), '$vote' => StringUtil::formatNumeric($rating)));
		}
		
		return '<img src="'.StyleManager::getStyle()->getIconPath('rating'.$roundedRating.'.png').'" alt="" title="'.$description.'" />';
	}

	/**
	 * Gets the number of pages in this thread.
	 *
	 * @return	integer		number of pages in this thread
	 */
	public function getPages($board = null) {
		// get board
		if ($board == null || $board->boardID != $this->boardID) {
			if ($this->board !== null) $board = $this->board;
			else $board = Board::getBoard($this->boardID);
		}
		
		// get posts per page
		if (WCF::getUser()->postsPerPage) $postsPerPage = WCF::getUser()->postsPerPage;
		else if ($board->postsPerPage) $postsPerPage = $board->postsPerPage;
		else $postsPerPage = THREAD_POSTS_PER_PAGE;
		
		return intval(ceil(($this->replies + 1) / $postsPerPage));
	}
	
	/**
	 * Returns the filename of the thread icon.
	 *
	 * @return	string		filename of the thread icon
	 */
	public function getIconName() {
		// deleted
		if ($this->isDeleted) return 'threadTrash';
		
		$icon = 'thread';
		
		// new
		if ($this->isNew()) $icon .= 'New';
		
		// moved
		if ($this->movedThreadID) {
			$icon .= 'Moved';
			
			// closed
			if ($this->isClosed) $icon .= 'Closed';
			
			return $icon;
		}
		
		// important
		if ($this->isAnnouncement == 1) $icon .= 'Announcement';
		else if ($this->isSticky == 1) $icon .= 'Important';
		
		// closed
		if ($this->isClosed) $icon .= 'Closed';
		
		return $icon;
	}
	
	/**
	 * Returns the flag icon for the thread language.
	 * 
	 * @return	string
	 */
	public function getLanguageIcon() {
		$languageData = Language::getLanguage($this->languageID);
		if ($languageData !== null) {
			return '<img src="'.StyleManager::getStyle()->getIconPath('language'.ucfirst($languageData['languageCode']).'S.png').'" alt="" title="'.WCF::getLanguage()->get('wcf.global.language.'.$languageData['languageCode']).'" />';
		}
		return '';
	}
}
?>