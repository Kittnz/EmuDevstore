<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a session access log entry.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.log
 * @category 	Community Framework
 */
class SessionAccessLog extends DatabaseObject {
	/**
	 * Creates a new SessionAccessLog object.
	 *
	 * @param	integer		$sessionAccessLogID
	 * @param	array<mixed>	$row
	 */
	public function __construct($sessionAccessLogID, $row = null) {
		if ($sessionAccessLogID !== null) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_acp_session_access_log
				WHERE	sessionAccessLogID = ".$sessionAccessLogID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Returns true, if the URI of this log entry is protected.
	 *
	 * @return 	boolean
	 */
	public function hasProtectedURI() {
		if ($this->requestMethod != 'GET' || !preg_match('/(\?|&)(page|form)=/', $this->requestURI)) {
			return true;
		}
		
		return false;
	}
}
?>