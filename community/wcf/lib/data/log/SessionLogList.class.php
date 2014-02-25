<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObjectList.class.php');
require_once(WCF_DIR.'lib/data/log/SessionLog.class.php');

/**
 * Represents a list of session log entries.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.log
 * @category 	Community Framework
 */
class SessionLogList extends DatabaseObjectList {
	/**
	 * list of session log entries.
	 * 
	 * @var array<SessionLog>
	 */
	public $sessionLogs = array();

	/**
	 * @see DatabaseObjectList::countObjects()
	 */
	public function countObjects() {
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_acp_session_log
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : '');
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * @see DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		$sql = "SELECT		".(!empty($this->sqlSelects) ? $this->sqlSelects.',' : '')."
					session_log.*, user_table.username, acp_session.sessionID AS active,
					(SELECT COUNT(*) FROM wcf".WCF_N."_acp_session_access_log WHERE sessionLogID = session_log.sessionLogID) AS accesses
			FROM		wcf".WCF_N."_acp_session_log session_log
			LEFT JOIN	wcf".WCF_N."_user user_table
			ON		(user_table.userID = session_log.userID)
			LEFT JOIN	wcf".WCF_N."_acp_session acp_session
			ON		(acp_session.sessionID = session_log.sessionID)
			".$this->sqlJoins."
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : '')."
			".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
		$result = WCF::getDB()->sendQuery($sql, $this->sqlLimit, $this->sqlOffset);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->sessionLogs[] = new SessionLog(null, $row);
		}
	}
	
	/**
	 * @see DatabaseObjectList::getObjects()
	 */
	public function getObjects() {
		return $this->sessionLogs;
	}
}
?>