<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Contains business logic related to handling of package update servers.
 *
 * @author	Siegfried Schweizer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.update
 * @category 	Community Framework
 */
class UpdateServer extends DatabaseObject {
	/**
	 * Creates a new UpdateServer object.
	 * 
	 * @param	integer		$packageUpdateServerID
	 * @param	array		$row
	 */
	public function __construct($packageUpdateServerID, $row = null) {
		if ($row === null) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_package_update_server
				WHERE	packageUpdateServerID = ".$packageUpdateServerID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}

	/**
	 * Returns all active update package servers sorted by hostname.
	 * 
	 * @param	array		$packageUpdateServerIDs
	 * @return	array		$servers
	 */
	public static function getActiveUpdateServers($packageUpdateServerIDs = array()) {
		$servers = array();
		$sql = "SELECT		* 
			FROM		wcf".WCF_N."_package_update_server
			WHERE		".(count($packageUpdateServerIDs) ? "packageUpdateServerID IN (".implode(',', $packageUpdateServerIDs).") AND" : "")."
					statusUpdate = 1 
			ORDER BY	server ASC";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$servers[$row['packageUpdateServerID']] = $row;
		}
		return $servers;
	}
	
	/**
	 * Validates a server url.
	 *
	 * @param	string		$serverURL
	 * @return	boolean		validates
	 */
	public static function isValidServerURL($serverURL) {
		if (trim($serverURL)) {
			if (!$parsedURL = @parse_url($serverURL))
				return false;
			if (!isset($parsedURL['scheme']) || $parsedURL['scheme'] != 'http')
				return false;
			if (!isset($parsedURL['host']))
				return false;
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Gets stored auth data of this update server.
	 *
	 * @return	array		$authData
	 */
	public function getAuthData() {
		$authData = array();
		// database data
		if ($this->htUsername != '' && $this->htPassword != '') {
			$authData = array(
				'authType' => 'Basic',
				'htUsername' => $this->htUsername,
				'htPassword' => $this->htPassword
			);
		}
		
		// session data
		$packageUpdateAuthData = WCF::getSession()->getVar('packageUpdateAuthData');
		if ($packageUpdateAuthData !== null && isset($packageUpdateAuthData[$this->packageUpdateServerID])) {
			$authData = $packageUpdateAuthData[$this->packageUpdateServerID];
		}
		
		return $authData;
	}
}
?>