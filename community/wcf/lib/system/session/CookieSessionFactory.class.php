<?php
// wcf imports
require_once(WCF_DIR.'lib/system/session/CookieSession.class.php');
require_once(WCF_DIR.'lib/system/session/SessionFactory.class.php');
require_once(WCF_DIR.'lib/data/user/User.class.php');

/**
 * CookieSessionFactory extends SessionFactory by cookie support.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.session
 * @category 	Community Framework
 */
class CookieSessionFactory extends SessionFactory {
	protected $sessionClassName = 'CookieSession';
	
	/**
	 * Classname used for guest sessions.
	 * 
	 * @var string
	 */
	protected $guestClassName = 'UserSession';
	
	/**
	 * @see SessionFactory::create()
	 */
	public function create() {
		// get spider information
		$spider = $this->isSpider(UserUtil::getUserAgent());
		if ($spider) {
			if (($session = $this->getExistingSpiderSession($spider['spiderID'])) !== null) {
				if (!$session->isCorrupt()) {
					return $session;
				}
			}
		}
		
		// create new session hash
		$sessionID = StringUtil::getRandomID();
		
		// check cookies for userID & password
		require_once(WCF_DIR.'lib/system/auth/UserAuth.class.php');
		$user = UserAuth::getInstance()->loginAutomatically(true, $this->userClassName);
		
		if ($user === null) {
			// no valid user found
			// create guest user
			$user = new $this->guestClassName();
		}
		
		// update user session
		$user->update();
		
		if ($user->userID != 0) {
			// user is no guest
			// delete all other sessions of this user
			Session::deleteSessions($user->userID, true, false);
		}
		$requestMethod = (!empty($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '');
		// insert session into database
		$sql = "INSERT INTO 	wcf".WCF_N."_session
					(sessionID, packageID, userID, ipAddress, userAgent,
					lastActivityTime, requestURI, requestMethod,
					username".($spider ? ", spiderID" : "").")
			VALUES		('".$sessionID."',
					".PACKAGE_ID.",
					".$user->userID.",
					'".escapeString(UserUtil::getIpAddress())."',
					'".escapeString(UserUtil::getUserAgent())."',
					".TIME_NOW.",
					'".escapeString(UserUtil::getRequestURI())."',
					'".escapeString($requestMethod)."',
					'".($spider ? escapeString($spider['spiderName']) : escapeString($user->username))."'
					".($spider ? ", ".$spider['spiderID'] : "").")";
		WCF::getDB()->sendQuery($sql);
		
		// save user data
		$serializedUserData = '';
		if (ENABLE_SESSION_DATA_CACHE && get_class(WCF::getCache()->getCacheSource()) == 'MemcacheCacheSource') {
			require_once(WCF_DIR.'lib/system/cache/source/MemcacheAdapter.class.php');
			MemcacheAdapter::getInstance()->getMemcache()->set('session_userdata_-'.$sessionID, $user);
		}
		else {
			$serializedUserData = serialize($user);
			try {
				$sql = "INSERT INTO 	wcf".WCF_N."_session_data
							(sessionID, userData)
					VALUES 		('".$sessionID."',
							'".escapeString($serializedUserData)."')";
				WCF::getDB()->sendQuery($sql);
			}
			catch (DatabaseException $e) {
				// horizon update workaround
				$sql = "UPDATE 	wcf".WCF_N."_session
					SET	userData = '".escapeString($serializedUserData)."'
					WHERE	sessionID = '".$sessionID."'";
				WCF::getDB()->sendQuery($sql);
			}
		}
		
		// return new session object
		return new $this->sessionClassName(null, array(
			'sessionID' => $sessionID,
			'packageID' => PACKAGE_ID,
			'userID' => $user->userID,
			'ipAddress' => UserUtil::getIpAddress(),
			'userAgent' =>  UserUtil::getUserAgent(),
			'lastActivityTime' => TIME_NOW,
			'requestURI' => UserUtil::getRequestURI(),
			'requestMethod' => $requestMethod,
			'userData' => $serializedUserData,
			'sessionVariables' => '',
			'username' => ($spider ? $spider['spiderName'] : $user->username),
			'spiderID' => ($spider ? $spider['spiderID'] : 0),
			'isNew' => true
		));
	}
	
	/**
	 * Returns spider information, if the given user agent identifies a spider.
	 * Otherwise returns false.
	 * 
	 * @param 	string		$userAgent
	 * @return	mixed
	 */
	protected static function isSpider($userAgent) {
		$spiderList = WCF::getCache()->get('spiders');
		$userAgent = strtolower($userAgent);
		
		foreach ($spiderList as $spider) {
			if (strpos($userAgent, $spider['spiderIdentifier']) !== false) {
				return $spider;
			}
		}
	
		return false;
	}
	
	/**
	 * @see SessionFactory::readSessionID()
	 */
	protected function readSessionID() {
		$sessionID = parent::readSessionID();
		
		// get sessionID from cookie
		if (empty($sessionID) && isset($_COOKIE[COOKIE_PREFIX.'cookieHash'])) {
			$sessionID = $_COOKIE[COOKIE_PREFIX . 'cookieHash'];
		}
		
		return $sessionID;
	}
	
	/**
	 * Finds an existing session of a search spider.
	 * 
	 * @param 	integer		$spiderID
	 * @return	CookieSession
	 */
	protected function getExistingSpiderSession($spiderID) {
		if (!ENABLE_SESSION_DATA_CACHE || get_class(WCF::getCache()->getCacheSource()) != 'MemcacheCacheSource') {
			$sql = "SELECT 		session_data.*, session.* 
				FROM 		wcf".WCF_N."_session session
				LEFT JOIN	wcf".WCF_N."_session_data session_data
				ON		(session_data.sessionID = session.sessionID)
				WHERE 		session.spiderID = '".escapeString($spiderID)."'
						AND session.userID = 0";
		}
		else {
			$sql = "SELECT 		* 
				FROM 		wcf".WCF_N."_session
				WHERE 		spiderID = '".escapeString($spiderID)."'
						AND userID = 0";
		}
		$row = WCF::getDB()->getFirstRow($sql);
		if (!empty($row['sessionID'])) {
			// fix session validation
			$row['ipAddress'] = UserUtil::getIpAddress();
			$row['userAgent'] = UserUtil::getUserAgent();
			
			// return session object
			return new $this->sessionClassName(null, $row);
		}
		return null;
	}
}
?>