<?php
// wbb imports
require_once(WBB_DIR.'lib/data/user/WBBUserSession.class.php');
require_once(WBB_DIR.'lib/data/user/WBBGuestSession.class.php');

// wcf imports
require_once(WCF_DIR.'lib/system/session/CookieSession.class.php');
require_once(WCF_DIR.'lib/data/user/User.class.php');

/**
 * WBBSession extends the CookieSession class with forum specific functions.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.session
 * @category 	Burning Board
 */
class WBBSession extends CookieSession {
	protected $userSessionClassName = 'WBBUserSession';
	protected $guestSessionClassName = 'WBBGuestSession';
	protected $boardID = 0;
	protected $threadID = 0;
	protected $styleID = 0;
	
	/**
	 * Initialises the session.
	 */
	public function init() {
		parent::init();
		
		// handle style id
		if ($this->user->userID) $this->styleID = $this->user->styleID;
		if (($styleID = $this->getVar('styleID')) !== null) $this->styleID = $styleID;
		
		if ($this->userID) {
			// user
			// update board / thread visits
			if ($this->user->boardLastActivityTime > $this->user->boardLastVisitTime && $this->user->boardLastActivityTime < TIME_NOW - SESSION_TIMEOUT) {
				$this->user->setLastVisitTime($this->user->boardLastActivityTime);
				
				// remove unnecessary board and thread visits
				$sql = "DELETE FROM	wbb".WBB_N."_thread_visit
					WHERE		userID = ".$this->userID."
							AND lastVisitTime <= ".($this->user->boardLastMarkAllAsReadTime);
				WCF::getDB()->registerShutdownUpdate($sql);
				
				$sql = "DELETE FROM	wbb".WBB_N."_board_visit
					WHERE		userID = ".$this->userID."
							AND lastVisitTime <= ".($this->user->boardLastMarkAllAsReadTime);
				WCF::getDB()->registerShutdownUpdate($sql);
				
				// reset user data
				$this->resetUserData();
			}
			
			// update global last activity time
			if ($this->lastActivityTime < TIME_NOW - USER_ONLINE_TIMEOUT + 299) {
				WBBUserSession::updateLastActivityTime($this->userID);
			}
		}
		else {
			// guest
			$boardLastActivityTime = 0;
			$boardLastVisitTime = $this->user->getLastVisitTime();
			if (isset($_COOKIE[COOKIE_PREFIX.'boardLastActivityTime'])) {
				$boardLastActivityTime = intval($_COOKIE[COOKIE_PREFIX.'boardLastActivityTime']);
			}
			
			if ($boardLastActivityTime != 0 && $boardLastActivityTime < $boardLastVisitTime && $boardLastActivityTime < TIME_NOW - SESSION_TIMEOUT) {
				$this->user->setLastVisitTime($boardLastActivityTime);
				$this->resetUserData();
			}
			
			HeaderUtil::setCookie('boardLastActivityTime', TIME_NOW, TIME_NOW + 365 * 24 * 3600);
		}
	}
	
	/**
	 * @see CookieSession::update()
	 */
	public function update() {
		$this->updateSQL .= ", boardID = ".$this->boardID.", threadID = ".$this->threadID;
		 
		parent::update();
	}
	
	/**
	 * Sets the current board id for this session.
	 *
	 * @param	integer		$boardID
	 */
	public function setBoardID($boardID) {
		$this->boardID = $boardID;
	}
	
	/**
	 * Sets the current thread id for this session.
	 *
	 * @param	integer		$threadID
	 */
	public function setThreadID($threadID) {
		$this->threadID = $threadID;
	}
	
	/**
	 * Sets the active style id.
	 * 
	 * @param 	integer		$newStyleID
	 */
	public function setStyleID($newStyleID) {
		$this->styleID = $newStyleID;
		if ($newStyleID > 0) $this->register('styleID', $newStyleID);
		else $this->unregister('styleID');
	}
	
	/**
	 * Returns the active style id.
	 * 
	 * @return	integer
	 */
	public function getStyleID() {
		return $this->styleID;
	}
}
?>