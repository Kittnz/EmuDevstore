<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Creates the session access log.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.event.listener
 * @category 	Community Framework
 */
class SessionAccessLogListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (WCF::getUser()->userID && WCF::getUser()->getPermission('admin.general.canUseAcp') && !defined(get_class($eventObj).'::DO_NOT_LOG')) {
			// try to find existing session log
			$sql = "SELECT	sessionLogID
				FROM	wcf".WCF_N."_acp_session_log
				WHERE	sessionID = '".WCF::getSession()->sessionID."'
					AND lastActivityTime >= ".(TIME_NOW - SESSION_TIMEOUT);
			$row = WCF::getDB()->getFirstRow($sql);
			if (!empty($row['sessionLogID'])) {
				$sessionLogID = $row['sessionLogID'];
				// update session log
				$sql = "UPDATE	wcf".WCF_N."_acp_session_log
					SET	lastActivityTime = ".TIME_NOW."
					WHERE	sessionLogID = ".$sessionLogID;
				WCF::getDB()->registerShutdownUpdate($sql);
			}
			else {
				// create new session log
				$sql = "INSERT INTO	wcf".WCF_N."_acp_session_log
							(sessionID, userID, ipAddress, hostname, userAgent, time, lastActivityTime)
					VALUES		('".WCF::getSession()->sessionID."', ".WCF::getUser()->userID.", '".escapeString(WCF::getSession()->ipAddress)."', '".escapeString(@gethostbyaddr(WCF::getSession()->ipAddress))."', '".escapeString(WCF::getSession()->userAgent)."', ".TIME_NOW.", ".TIME_NOW.")";
				WCF::getDB()->sendQuery($sql);
				$sessionLogID = WCF::getDB()->getInsertID("wcf".WCF_N."_acp_session_log", 'sessionLogID');
			}
			
			// format request uri
			$requestURI = WCF::getSession()->requestURI;
			// remove directories
			$URIComponents = explode('/', $requestURI);
			$requestURI = array_pop($URIComponents);
			// remove session url
			$requestURI = preg_replace('/(?:\?|&)s=[a-f0-9]{40}/', '', $requestURI);
			
			// save access
			$sql = "INSERT INTO	wcf".WCF_N."_acp_session_access_log
						(sessionLogID, packageID, ipAddress, time, requestURI, requestMethod, className)
				VALUES		(".$sessionLogID.", ".PACKAGE_ID.", '".escapeString(WCF::getSession()->ipAddress)."', ".TIME_NOW.", '".escapeString($requestURI)."', '".escapeString(WCF::getSession()->requestMethod)."', '".escapeString(get_class($eventObj))."')";
			WCF::getDB()->registerShutdownUpdate($sql);
		}
	}
}
?>