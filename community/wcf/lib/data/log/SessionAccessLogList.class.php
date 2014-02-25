<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObjectList.class.php');
require_once(WCF_DIR.'lib/data/log/SessionAccessLog.class.php');

/**
 * Represents a list of access logs.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.log
 * @category 	Community Framework
 */
class SessionAccessLogList extends DatabaseObjectList {
	/**
	 * list of session access log entries.
	 * 
	 * @var array<SessionAccessLog>
	 */
	public $sessionAccessLogs = array();

	/**
	 * @see DatabaseObjectList::countObjects()
	 */
	public function countObjects() {
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_acp_session_access_log
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : '');
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * @see DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		$sql = "SELECT		".(!empty($this->sqlSelects) ? $this->sqlSelects.',' : '')."
					session_access_log.*,
					CASE WHEN package.instanceName <> '' THEN package.instanceName ELSE package.packageName END AS packageName
			FROM		wcf".WCF_N."_acp_session_access_log session_access_log
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = session_access_log.packageID)
			".$this->sqlJoins."
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : '')."
			".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
		$result = WCF::getDB()->sendQuery($sql, $this->sqlLimit, $this->sqlOffset);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->sessionAccessLogs[] = new SessionAccessLog(null, $row);
		}
	}
	
	/**
	 * @see DatabaseObjectList::getObjects()
	 */
	public function getObjects() {
		return $this->sessionAccessLogs;
	}
}
?>