<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/update/UpdateServer.class.php');

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
class UpdateServerEditor extends UpdateServer {
	/**
	 * Creates a new update server.
	 *
	 * @param	string		$server
	 * @param	string		$htUsername
	 * @param	string		$htPassword
	 * @return	integer		$packageUpdateServerID
	 */
	public static function create($server, $htUsername = '', $htPassword = '') {
		$sql = "INSERT INTO	wcf".WCF_N."_package_update_server
					(server, htUsername, htPassword)
			VALUES		('".escapeString($server)."', '".escapeString($htUsername)."', '".escapeString($htPassword)."')";
		WCF::getDB()->sendQuery($sql);
		
		return WCF::getDB()->getInsertID();
	}
	
	/**
	 * Updates this update server.
	 *
	 * @param	string		$server
	 * @param	string		$htUsername
	 * @param	string		$htPassword
	 */
	public function update($server, $htUsername = '', $htPassword = '') {
		$sql = "UPDATE	wcf".WCF_N."_package_update_server
			SET	server = '".escapeString($server)."',
				htUsername = '".escapeString($htUsername)."',
				htPassword = '".escapeString($htPassword)."'
			WHERE	packageUpdateServerID = ".$this->packageUpdateServerID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Updates the status of this update server.
	 *
	 * @param	integer		$timestamp
	 * @param	string		$status
	 * @param	string		$errorText
	 */
	public function updateStatus($timestamp, $status = 'online', $errorText = '') {
		$sql = "UPDATE	wcf".WCF_N."_package_update_server
			SET	timestamp = ".$timestamp.",
				status = '".escapeString($status)."',
				errorText = '".escapeString($errorText)."'
			WHERE	packageUpdateServerID = ".$this->packageUpdateServerID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Enables/Disables an update server.
	 *
	 * @param	boolean		$enable
	 */
	public function enable($enable) {
		$sql = "UPDATE	wcf".WCF_N."_package_update_server
			SET	statusUpdate = ".intval($enable)."
			WHERE	packageUpdateServerID = ".$this->packageUpdateServerID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Deletes this update server.
	 */
	public function delete() {
		// delete packages, versions ... etc.
		$sql = "DELETE FROM	wcf".WCF_N."_package_update_requirement
			WHERE		packageUpdateVersionID IN (
						SELECT	packageUpdateVersionID
						FROM	wcf".WCF_N."_package_update_version
						WHERE	packageUpdateID IN (
							SELECT	packageUpdateID
							FROM	wcf".WCF_N."_package_update
							WHERE	packageUpdateServerID = ".$this->packageUpdateServerID."
						)
					)";
		WCF::getDB()->sendQuery($sql);
		$sql = "DELETE FROM	wcf".WCF_N."_package_update_fromversion
			WHERE		packageUpdateVersionID IN (
						SELECT	packageUpdateVersionID
						FROM	wcf".WCF_N."_package_update_version
						WHERE	packageUpdateID IN (
							SELECT	packageUpdateID
							FROM	wcf".WCF_N."_package_update
							WHERE	packageUpdateServerID = ".$this->packageUpdateServerID."
						)
					)";
		WCF::getDB()->sendQuery($sql);
		$sql = "DELETE FROM	wcf".WCF_N."_package_update_version
			WHERE		packageUpdateID IN (
						SELECT	packageUpdateID
						FROM	wcf".WCF_N."_package_update
						WHERE	packageUpdateServerID = ".$this->packageUpdateServerID."
					)";
		WCF::getDB()->sendQuery($sql);
		$sql = "DELETE FROM	wcf".WCF_N."_package_update
			WHERE		packageUpdateServerID = ".$this->packageUpdateServerID."";
		WCF::getDB()->sendQuery($sql);
		
		// delete server
		$sql = "DELETE FROM	wcf".WCF_N."_package_update_server 
			WHERE		packageUpdateServerID = ".$this->packageUpdateServerID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>