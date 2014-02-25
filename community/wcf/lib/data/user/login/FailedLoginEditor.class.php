<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/login/FailedLogin.class.php');

/**
 * Provides functions to add, edit and delete failed logins.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.security.login
 * @subpackage	data.user.login
 * @category 	Community Framework (commercial)
 */
class FailedLoginEditor extends FailedLogin {
	/**
	 * Creates a new failed login.
	 * 
	 * @param 	string		$environment
	 * @param	integer		$userID
	 * @param	username	$username
	 * @param	integer		$time
	 * @param	string		$ipAddress
	 * @param	string		$userAgent
	 * @return	FailedLoginEditor
	 */
	public static function create($environment, $userID, $username, $time, $ipAddress, $userAgent) {
		$sql = "INSERT INTO	wcf".WCF_N."_user_failed_login
					(environment, userID, username, time, ipAddress, userAgent)
			VALUES		('".$environment."', ".$userID.", '".escapeString($username)."', ".$time.", '".escapeString($ipAddress)."', '".escapeString($userAgent)."')";
		WCF::getDB()->sendQuery($sql);
		
		$failedLoginID = WCF::getDB()->getInsertID("wcf".WCF_N."_user_failed_login", 'failedLoginID');
		return new FailedLoginEditor($failedLoginID);
	}
	
	/**
	 * Updates this failed login.
	 * 
	 * @param 	string		$environment
	 * @param	integer		$userID
	 * @param	username	$username
	 * @param	integer		$time
	 * @param	string		$ipAddress
	 * @param	string		$userAgent
	 */
	public function update($environment, $userID, $username, $time, $ipAddress, $userAgent) {
		$sql = "UPDATE	wcf".WCF_N."_user_failed_login
			SET	environment = '".$environment."',
				userID = ".$userID.",
				username = '".escapeString($username)."',
				time = ".$time.",
				ipAddress = '".escapeString($ipAddress)."',
				userAgent = '".escapeString($userAgent)."'
			WHERE	failedLoginID = ".$this->failedLoginID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Deletes this failed login.
	 */
	public function delete() {
		$sql = "DELETE FROM	wcf".WCF_N."_user_failed_login
			WHERE		failedLoginID = ".$this->failedLoginID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>