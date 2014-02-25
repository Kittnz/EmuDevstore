<?php
// wcf imports
require_once(WCF_DIR.'lib/data/cronjobs/Cronjob.class.php');

/**
 * Deletes old entries from session log.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	system.cronjob
 * @category 	Community Framework
 */
class CleanUpSessionLogCronjob implements Cronjob {
	/**
	 * @see Cronjob::execute()
	 */
	public function execute($data) {
		// delete access log
		$sql = "DELETE FROM	wcf".WCF_N."_acp_session_access_log
			WHERE		sessionLogID IN (
						SELECT	sessionLogID
						FROM	wcf".WCF_N."_acp_session_log
						WHERE	lastActivityTime < ".(TIME_NOW - (86400 * 30))."
					)";
		WCF::getDB()->sendQuery($sql);
		
		// delete session log
		$sql = "DELETE FROM	wcf".WCF_N."_acp_session_log
			WHERE		lastActivityTime < ".(TIME_NOW - (86400 * 30));
		WCF::getDB()->sendQuery($sql);
	}
}
?>