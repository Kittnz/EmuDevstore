<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/session/Session.class.php');
	require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');
}

/**
 * SessionFactory returns and creates the session for the active user.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.session
 * @category 	Community Framework
 */
class SessionFactory {
	/**
	 * Classname used for user sessions.
	 * 
	 * @var string
	 */
	protected $userClassName = 'UserSession';
	
	/**
	 * Classname used for session objects.
	 * 
	 * @var string
	 */
	protected $sessionClassName = 'Session';
	
	/**
	 * Static pointer to active user session.
	 * 
	 * @var Session
	 */
	protected static $activeSession = null;
	
	/**
	 * ID of this session.
	 * 
	 * @var string
	 */
	public $sessionID = '';
	
	/**
	 * Active session.
	 * 
	 * @var Session
	 */
	public $session = null;
	
	/**
	 * Returns the object of the active session.
	 * Tries to find an existing session. 
	 * Otherwise creates a new session.
	 * 
	 * @return 	 Session 		$session
	 */
	public function get() {
		// get session id
		$this->sessionID = $this->readSessionID();
		$this->session = null;

		// get existing session
		if (!empty($this->sessionID)) {
			$this->session = $this->getExistingSession($this->sessionID);
		}
	
		// create new session
		if ($this->session == null) {
			$this->session = $this->create();
		}
		
		self::$activeSession = $this->session;
		
		// call shouldInit event
		if (!defined('NO_IMPORTS')) EventHandler::fireAction($this, 'shouldInit');
		
		// init session
		$this->session->init();

		// call didInit event
		if (!defined('NO_IMPORTS')) EventHandler::fireAction($this, 'didInit');
		
		return $this->session;
	}
	
	/**
	 * Gets an existing session from database with given sessionID.
	 * Returns null, if no session with the given sessionID does exist.
	 * 
	 * @param 	string		$sessionID
	 * @return 	Session 	$session
	 */
	protected function getExistingSession($sessionID) {
		$session = new $this->sessionClassName($sessionID);
		if (!$session->isCorrupt()) return $session;
		return null;
	}
	
	/**
	 * Creates a new session.
	 * 
	 * Generates a new session hash, inserts the new session into database
	 * and returns the object of the created session. 
	 * 
	 * @return 	 Session 	$session
	 */
	public function create() {
		// create new session hash
		$sessionID = StringUtil::getRandomID();
		
		// get user automatically
		if (!defined('NO_IMPORTS')) require_once(WCF_DIR.'lib/system/auth/UserAuth.class.php');
		$user = UserAuth::getInstance()->loginAutomatically();
		
		// create user
		if ($user === null) {
			// no valid user found
			// create guest user
			$user = new $this->userClassName();
		}
		
		// update user session
		$user->update();
		
		// insert session into database
		$requestMethod = (!empty($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '');
		$sql = "INSERT INTO 	wcf".WCF_N."_acp_session
					(sessionID, packageID, userID, ipAddress, userAgent, lastActivityTime, requestURI, requestMethod)
			VALUES 		('".$sessionID."',
					".PACKAGE_ID.",
					".$user->userID.",
					'".escapeString(UserUtil::getIpAddress())."',
					'".escapeString(UserUtil::getUserAgent())."',
					".TIME_NOW.",
					'".escapeString(UserUtil::getRequestURI())."',
					'".escapeString($requestMethod)."')";
		WCF::getDB()->sendQuery($sql);
		
		// save user data
		$serializedUserData = '';
		if (ENABLE_SESSION_DATA_CACHE && get_class(WCF::getCache()->getCacheSource()) == 'MemcacheCacheSource') {
			require_once(WCF_DIR.'lib/system/cache/source/MemcacheAdapter.class.php');
			MemcacheAdapter::getInstance()->getMemcache()->set('acp_session_userdata_'.$sessionID, $user);
		}
		else {
			$serializedUserData = serialize($user);
			try {
				$sql = "INSERT INTO 	wcf".WCF_N."_acp_session_data
							(sessionID, userData)
					VALUES 		('".$sessionID."',
							'".escapeString($serializedUserData)."')";
				WCF::getDB()->sendQuery($sql);
			}
			catch (DatabaseException $e) {
				// horizon update workaround
				$sql = "UPDATE 	wcf".WCF_N."_acp_session
					SET	userData = '".escapeString($serializedUserData)."'
					WHERE	sessionID = '".$sessionID."'";
				WCF::getDB()->sendQuery($sql);
			}
		}
		
		// return new session object
		return new $this->sessionClassName(null, array(
			'sessionID' => $sessionID,
			'packageID' => PACKAGE_ID,
			'ipAddress' => UserUtil::getIpAddress(),
			'userAgent' =>  UserUtil::getUserAgent(),
			'lastActivityTime' => TIME_NOW,
			'requestURI' => UserUtil::getRequestURI(),
			'requestMethod' => $requestMethod,
			'userData' => $serializedUserData,
			'sessionVariables' => '',
			'userID' => $user->userID,
			'isNew' => true
		));
	}
	
	/**
	 * Gets the sessionID from request (GET/POST).
	 * Returns an empty string, if no sessionID was given.
	 * 
	 * @return 	string 		$sessionID
	 */
	protected function readSessionID() {
		$sessionID = '';
		// get sessionID from request
		// do not use session id from cookie
		// other applications maybe sets cookies with same name
		//if (isset($_REQUEST['sessionID'])) $sessionID = $_REQUEST['sessionID'];
		if (isset($_GET['s'])) $sessionID = $_GET['s'];
		else if (isset($_POST['s'])) $sessionID = $_POST['s'];
		
		return $sessionID;
	}
	
	/**
	 * Returns the active session.
	 * 
	 * @return	Session
	 */
	public static function getActiveSession() {
		return self::$activeSession;
	}
}
?>