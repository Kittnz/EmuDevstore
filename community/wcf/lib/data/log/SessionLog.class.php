<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a session log entry.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.log
 * @category 	Community Framework
 */
class SessionLog extends DatabaseObject {
	/**
	 * Creates a new SessionLog object.
	 *
	 * @param	integer		$sessionLogID
	 * @param	array<mixed>	$row
	 */
	public function __construct($sessionLogID, $row = null) {
		if ($sessionLogID !== null) {
			$sql = "SELECT		acp_session_log.*, user_table.username, acp_session.sessionID AS active
				FROM		wcf".WCF_N."_acp_session_log acp_session_log
				LEFT JOIN	wcf".WCF_N."_acp_session acp_session
				ON		(acp_session.sessionID = acp_session_log.sessionID)
				LEFT JOIN	wcf".WCF_N."_user user_table
				ON		(user_table.userID = acp_session_log.userID)
				WHERE		acp_session_log.sessionLogID = ".$sessionLogID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Returns true, if this session is active.
	 *
	 * @return	boolean
	 */
	public function isActive() {
		if ($this->active && $this->lastActivityTime > TIME_NOW - SESSION_TIMEOUT) {
			return 1;
		}
		
		return 0;
	}
	
	/**
	 * Returns true, if this session is the active user session.
	 *
	 * @return	boolean
	 */
	public function isActiveUserSession() {
		if ($this->isActive() && $this->sessionID == WCF::getSession()->sessionID) {
			return 1;
		}
		
		return 0;
	}
}
?>