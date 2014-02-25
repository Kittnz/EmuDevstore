<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/auth/UserAuthDefault.class.php');
	require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');
	require_once(WCF_DIR.'lib/data/user/User.class.php');
}

/**
 * All user authentication types should implement the abstract functions of this class.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.auth
 * @category 	Community Framework
 */
abstract class UserAuth {
	/**
	 * active instance
	 *
	 * @var	UserAuth
	 */
	protected static $instance = null;
	
	/**
	 * Returns an instance of the enabled user auth class.
	 * 
	 * @return	UserAuth
	 */
	public static function getInstance() {
		if (self::$instance === null) {
			// call loadInstance event
			if (!defined('NO_IMPORTS')) EventHandler::fireAction('UserAuth', 'loadInstance');
		
			if (self::$instance === null) self::$instance = new UserAuthDefault();
		}
		return self::$instance;
	}
	
	/**
	 * Returns true, if this auth type supports persistent logins.
	 * 
	 * @return	boolean
	 */
	public abstract function supportsPersistentLogins();
	
	/**
	 * Stores the user access data for a persistent login.
	 * 
	 * @param	User		$user
	 * @param 	string		$username
	 * @param	string		$password
	 */
	public abstract function storeAccessData(User $user, $username, $password);
	
	/**
	 * Does an manual user login.
	 * 
	 * @param 	string		$username
	 * @param	string		$password
	 * @param	string		$userClassname		class name of user class
	 * @return	User
	 */
	public abstract function loginManually($username, $password, $userClassname = 'UserSession');
	
	/**
	 * Does an automatic user login.
	 * 
	 * @param	boolean		$persistent		true = persistent login
	 * @param	string		$userClassname		class name of user class
	 * @return	User
	 */
	public abstract function loginAutomatically($persistent = false, $userClassname = 'UserSession');
}
?>