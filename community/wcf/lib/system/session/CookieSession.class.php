<?php
// wcf imports
require_once(WCF_DIR.'lib/system/session/Session.class.php');

/**
 * If the user has enabled cookies, a CookieSession transfers the session id in a browser cookie.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.session
 * @category 	Community Framework
 */
class CookieSession extends Session {
	protected $sessionTable = 'session';
	
	/**
	 * Indicates if the client supports cookies.
	 *
	 * @var boolean
	 */
	protected $useCookies = true;
	
	/**
	 * Sets the username of this session.
	 * 
	 * @param 	string		$username
	 */
	public function setUsername($username) {
		$sql = "UPDATE	wcf".WCF_N."_".$this->sessionTable."
			SET	username = '".escapeString($username)."'
			WHERE	sessionID = '".$this->sessionID."'";
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Defines the constants that are used in each link of WCF.
	 * If cookies are enabled the constants are empty.
	 */
	protected function defineConstants() {
		// check cookies
		$this->handleCookie();
		
		if (!$this->useCookies && !$this->spiderID) {
			parent::defineConstants();
		}
		else {		
			if (!defined('SID_ARG_1ST')) define('SID_ARG_1ST', '');
			if (!defined('SID_ARG_2ND')) define('SID_ARG_2ND', '');
			if (!defined('SID_ARG_2ND_NOT_ENCODED')) define('SID_ARG_2ND_NOT_ENCODED', '');
			if (!defined('SID')) define('SID', '');
			if (!defined('SID_INPUT_TAG')) define('SID_INPUT_TAG', '');
			
			// security token
			if (!defined('SECURITY_TOKEN')) define('SECURITY_TOKEN', $this->getSecurityToken());
			if (!defined('SECURITY_TOKEN_INPUT_TAG')) define('SECURITY_TOKEN_INPUT_TAG', '<input type="hidden" name="t" value="'.$this->getSecurityToken().'" />');
		}
	}
	
	/**
	 * Examines whether cookies are enabled.
	 */
	protected function handleCookie() {
		if (isset($_COOKIE[COOKIE_PREFIX.'cookieHash'])) {
			if ($_COOKIE[COOKIE_PREFIX.'cookieHash'] != $this->sessionID) {
				$this->useCookies = false;
			}
		}
		else {
			$this->useCookies = false;
		}
		
		if (!$this->useCookies) {
			HeaderUtil::setCookie('cookieHash', $this->sessionID);
		}
	}
}
?>