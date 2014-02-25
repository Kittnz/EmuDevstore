<?php
// wbb imports
require_once(WBB_DIR.'lib/data/user/AbstractWBBUserSession.class.php');

/**
 * Represents a guest session in the forum.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.user
 * @category 	Burning Board
 */
class WBBGuestSession extends AbstractWBBUserSession {
	protected $boardVisits = null;
	protected $threadVisits = null;
	protected $closedCategories = null;
	protected $lastVisitTime = null;
	
	/**
	 * Initialises the user session.
	 */
	public function init() {
		parent::init();
		
		$this->boardVisits = $this->threadVisits = $this->closedCategories = $this->lastVisitTime = null;
	}
	
	/**
	 * Sets the global board last visit timestamp.
	 */
	public function setLastVisitTime($timestamp) {
		$this->lastVisitTime = $timestamp;
		// cookie
		HeaderUtil::setCookie('boardLastVisitTime', $this->lastVisitTime, TIME_NOW + 365 * 24 * 3600);
		// session
		SessionFactory::getActiveSession()->register('boardLastVisitTime', $this->lastVisitTime);
	}
	
	/**
	 * Returns the last visit time of this user.
	 * 
	 * @return	integer
	 */
	public function getLastVisitTime() {
		if ($this->lastVisitTime === null) {
			$this->lastVisitTime = 0;
			if (isset($_COOKIE[COOKIE_PREFIX.'boardLastVisitTime'])) {
				$this->lastVisitTime = intval($_COOKIE[COOKIE_PREFIX.'boardLastVisitTime']);
			}
			else {
				$this->lastVisitTime = intval(SessionFactory::getActiveSession()->getVar('boardLastVisitTime'));
			}
			
			if ($this->lastVisitTime < TIME_NOW - 3600 * 24 * 365) {
				$this->lastVisitTime = TIME_NOW - VISIT_TIME_FRAME;
			}
		}
		
		return $this->lastVisitTime;
	}
	
	/**
	 * Gets the board visits of this guest from session variables.
	 */
	protected function getBoardVisits() {
		if ($this->boardVisits === null) {
			$this->boardVisits = WCF::getSession()->getVar('boardVisits');
			if ($this->boardVisits === false) $this->boardVisits = array();
		}
	}
	
	/**
	 * Returns the board visit of this guest for the board with the given board id.
	 *
	 * @return	integer		board visit of this guest for the board with the given board id
	 */
	public function getBoardVisitTime($boardID) {
		$this->getBoardVisits();
		$boardVisitTime = 0;
		
		if (isset($this->boardVisits[$boardID])) return $boardVisitTime = $this->boardVisits[$boardID];
		if ($boardVisitTime < $this->getLastVisitTime()) {
			$boardVisitTime = $this->getLastVisitTime();
		}
		
		return $boardVisitTime;
	}
	
	/**
	 * Sets the board visit of this guest for the board with the given board id.
	 *
	 * @param	integer		$boardID
	 */
	public function setBoardVisitTime($boardID) {
		$this->getBoardVisits();
		
		$this->boardVisits[$boardID] = TIME_NOW;
		WCF::getSession()->register('boardVisits', $this->boardVisits);
	}
	
	/**
	 * Gets the thread visits of this guest from session variables.
	 */
	protected function getThreadVisits() {
		if ($this->threadVisits === null) {
			$this->threadVisits = WCF::getSession()->getVar('threadVisits');
			if ($this->threadVisits === false) $this->threadVisits = array();
		}
	}
	
	/**
	 * Returns the thread visit of this guest for the thread with the given thread id.
	 *
	 * @return	integer		thread visit of this guest for the thread with the given thread id
	 */
	public function getThreadVisitTime($threadID) {
		$this->getThreadVisits();
		$threadVisitTime = 0;
		
		if (isset($this->threadVisits[$threadID])) return $threadVisitTime = $this->threadVisits[$threadID];
		if ($threadVisitTime < $this->getLastVisitTime()) {
			$threadVisitTime = $this->getLastVisitTime();
		}
		
		return $threadVisitTime;
	}
	
	/**
	 * Sets the thread visit of this guest for the thread with the given thread id.
	 *
	 * @param	integer		$threadID
	 */
	public function setThreadVisitTime($threadID, $timestamp = TIME_NOW) {
		$this->getThreadVisits();
		
		$this->threadVisits[$threadID] = $timestamp;
		WCF::getSession()->register('threadVisits', $this->threadVisits);
	}
	
	/**
	 * Gets the closed categories of this guest from session variables.
	 */
	protected function getClosedCategories() {
		if ($this->closedCategories === null) {
			$this->closedCategories = WCF::getSession()->getVar('closedCategories');
			if ($this->closedCategories === null) $this->closedCategories = array();
		}
	}
	
	/**
	 * Returns true, if the category with the given board id is closed by this guest.
	 *
	 * @param	integer		$boardID
	 * @return	boolean
	 */
	public function isClosedCategory($boardID) {
		$this->getClosedCategories();
		
		if (!isset($this->closedCategories[$boardID])) return 0;
		return $this->closedCategories[$boardID];
	}
	
	/**
	 * Closes the category with the given board id for this guest.
	 *
	 * @param	integer		$boardID
	 * @param	integer		$close		1 closes the category
	 *						-1 opens the category
	 */
	public function closeCategory($boardID, $close = 1) {
		$this->getClosedCategories();
		
		require_once(WBB_DIR.'lib/data/board/Board.class.php');
		$board = Board::getBoard($boardID);
		if (!$board->isCategory()) {
			throw new IllegalLinkException();
		}
		
		$this->closedCategories[$boardID] = $close;
		WCF::getSession()->register('closedCategories', $this->closedCategories);
	}
	
	/**
	 * Does nothing.
	 */
	public function isIgnoredBoard($boardID) {
		return 0;
	}
	
	/**
	 * Returns the last mark all as read timestamp.
	 * 
	 * @return	integer
	 */
	public function getLastMarkAllAsReadTime() {
		return $this->getLastVisitTime();
	}
	
	/**
	 * Sets the last mark all as read timestamp.
	 */
	public function setLastMarkAllAsReadTime($timestamp) {
		$this->setLastVisitTime($timestamp);
	}
}
?>