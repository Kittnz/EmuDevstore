<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a failed user login.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.security.login
 * @subpackage	data.user.login
 * @category 	Community Framework (commercial)
 */
class FailedLogin extends DatabaseObject {
	/**
	 * Creates a new FailedLogin object.
	 *
	 * @param	integer		$failedLoginID
	 * @param	array<mixed>	$row
	 */
	public function __construct($failedLoginID, $row = null) {
		if ($failedLoginID !== null) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_user_failed_login
				WHERE	failedLoginID = ".$failedLoginID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Returns the number of failed logins.
	 * 
	 * @param	string		$ipAddress
	 * @param	integer		$time
	 * @return	integer
	 */
	public static function countFailedLogins($ipAddress = null, $time = null) {
		// take default values
		if ($ipAddress === null) $ipAddress = WCF::getSession()->ipAddress;
		if ($time === null) $time = (TIME_NOW - FAILED_LOGIN_TIME_FRAME);
		
		// get number of failed logins
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user_failed_login
			WHERE	ipAddress = '".escapeString($ipAddress)."'
				AND time > ".$time;
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
}
?>