<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/session/UserSession.class.php');
	require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');
}

// define default values (can be overwritten by individual options)
if (!defined('SESSION_VALIDATE_USER_AGENT')) define('SESSION_VALIDATE_USER_AGENT', true);
if (!defined('SESSION_VALIDATE_IP_ADDRESS')) define('SESSION_VALIDATE_IP_ADDRESS', 0);

/**
 * A session holds all information about his user and his temporary session variables.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.session
 * @category 	Community Framework
 */
class Session extends DatabaseObject {
	// constants
	/**
	 * Classname used for user sessions.
	 * 
	 * @var string
	 */
	protected $userSessionClassName = 'UserSession';
	
	/**
	 * Classname used for guest sessions.
	 * 
	 * @var string
	 */
	protected $guestSessionClassName = 'UserSession';
	
	/**
	 * Name of the session database table.
	 * 
	 * @param string
	 */
	protected $sessionTable = 'acp_session';
	
	/**
	 * User session object.
	 * 
	 * @var User
	 */
	protected $user;
	
	/**
	 * Registered session variables.
	 * 
	 * @var array
	 */
	protected $sessionVariables = array();
	
	/**
	 * Language id of this session.
	 * 
	 * @var integer
	 */
	protected $languageID = 0;
	
	/**
	 * Selected visible languages.
	 * 
	 * @var array<integer>
	 */
	protected $visibleLanguageIDArray = null;
	
	// session states
	protected $userDataChanged = false;
	protected $userDataReset = false;
	protected $sessionVariableChanged = false;
	protected $doNotUpdate = false;
	protected $updateSQL = '';
	
	/**
	 * Reads the session data from the session table,
	 * inits a user via the handleresult function
	 * and defines SID's for links.
	 * 
	 * @param	string		$sessionID
	 * @param 	array		$row
	 */
	public function __construct($sessionID, $row = null) {
		// horizon update workaround
		if (!defined('ENABLE_SESSION_DATA_CACHE')) {
			define('ENABLE_SESSION_DATA_CACHE', 0);
		}
		
		if ($row === null) {
			if (!ENABLE_SESSION_DATA_CACHE || get_class(WCF::getCache()->getCacheSource()) != 'MemcacheCacheSource') {
				try {
					$sql = "SELECT 		session_data.*, session.* 
						FROM 		wcf".WCF_N."_".$this->sessionTable." session
						LEFT JOIN	wcf".WCF_N."_".$this->sessionTable."_data session_data
						ON		(session_data.sessionID = session.sessionID)
						WHERE 		session.sessionID = '".escapeString($sessionID)."'";
					$row = WCF::getDB()->getFirstRow($sql);
				}
				catch (DatabaseException $e) {
					// horizon update workaround
					$sql = "SELECT 		session.* 
						FROM 		wcf".WCF_N."_".$this->sessionTable." session
						WHERE 		session.sessionID = '".escapeString($sessionID)."'";
					$row = WCF::getDB()->getFirstRow($sql);
				}
			}
			else {
				$sql = "SELECT 		session.* 
					FROM 		wcf".WCF_N."_".$this->sessionTable." session
					WHERE 		session.sessionID = '".escapeString($sessionID)."'";
				$row = WCF::getDB()->getFirstRow($sql);
			} 
		}
			
		parent::__construct($row);
		if (!$this->isCorrupt()) $this->defineConstants();
	}
	
	/**
	 * Initialises the session.
	 */
	public function init() {
		// handle language id
		if ($this->user->userID) $this->languageID = $this->user->languageID;
		else if (($languageID = $this->getVar('languageID')) !== null) $this->languageID = $languageID;
		
		// init user session
		$this->user->init();
	}
	
	/**
	 * Defines the constants that are used in each link of the WCF.
	 */
	protected function defineConstants() {
		if (!defined('SID_ARG_1ST')) define('SID_ARG_1ST', '?s='.$this->sessionID);
		if (!defined('SID_ARG_2ND')) define('SID_ARG_2ND', '&amp;s='.$this->sessionID);
		if (!defined('SID_ARG_2ND_NOT_ENCODED')) define('SID_ARG_2ND_NOT_ENCODED', '&s='.$this->sessionID);
		if (!defined('SID')) define('SID', $this->sessionID);
		if (!defined('SID_INPUT_TAG')) define('SID_INPUT_TAG', '<input type="hidden" name="s" value="'.$this->sessionID.'" />');
		
		// security token
		if (!defined('SECURITY_TOKEN')) define('SECURITY_TOKEN', $this->getSecurityToken());
		if (!defined('SECURITY_TOKEN_INPUT_TAG')) define('SECURITY_TOKEN_INPUT_TAG', '<input type="hidden" name="t" value="'.$this->getSecurityToken().'" />');
	}
	
	/**
	 * Handles the given resultset. Stores database data in this session object.
	 *
	 * @param 	array 		$row
	 */
	protected function handleData($data) {
		parent::handleData($data);
		
		if ($this->sessionID) {
			// validate session
			if (!$this->validate()) {
				$this->data['sessionID'] = false;
				return;
			}
			
			$this->data['lastRequestURI'] = $this->requestURI;
			$this->data['lastRequestMethod'] = $this->requestMethod;
			$this->data['ipAddress'] = UserUtil::getIpAddress();
			$this->data['userAgent'] = UserUtil::getUserAgent();
			$this->data['requestURI'] = UserUtil::getRequestURI();
			$this->data['requestMethod'] = (!empty($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '');
			
			// handle data
			if (ENABLE_SESSION_DATA_CACHE && get_class(WCF::getCache()->getCacheSource()) == 'MemcacheCacheSource') {
				require_once(WCF_DIR.'lib/system/cache/source/MemcacheAdapter.class.php');
				// get user data
				$this->user = MemcacheAdapter::getInstance()->getMemcache()->get($this->sessionTable.'_userdata_'.$this->sessionID);
				
				// get session variables
				$this->sessionVariables = MemcacheAdapter::getInstance()->getMemcache()->get($this->sessionTable.'_variables_'.$this->sessionID);
				if (!is_array($this->sessionVariables)) {
					$this->sessionVariables = array();
				}
				
				// package changed; reset user data
				if ($this->data['packageID'] != PACKAGE_ID) {
					$this->user = null;
				}
			}
			else {
				// package changed; reset user data
				if ($this->data['packageID'] != PACKAGE_ID) {
					$this->data['userData'] = '';
				}
				//$this->data['packageID'] = PACKAGE_ID;
		
				// unserialize the variables of this session
				@$this->sessionVariables = unserialize($data['sessionVariables']);
				if (!is_array($this->sessionVariables)) {
					$this->sessionVariables = array();
				}
					
				// unserialize the user object of this session
				@$this->user = unserialize($this->userData);
				unset($this->data['userData']);
			}

			// check whether the user object is valid
			if (!is_object($this->user) || ($this->userID != 0 && !($this->user instanceof $this->userSessionClassName)) || ($this->userID == 0 && !($this->user instanceof $this->guestSessionClassName))) {
				// create a new user object
				$this->createUser($this->userID != 0 ? $this->userID : null);
			}
			
			// generate security token
			$this->initSecurityToken();
		}
	}
	
	/**
	 * Creates a security token.
	 */
	protected function initSecurityToken() {
		if ($this->getVar('__SECURITY_TOKEN') === null) {
			$this->register('__SECURITY_TOKEN', StringUtil::getRandomID());
		}
	}
	
	/**
	 * Returns the security token.
	 * 
	 * @return	string
	 */
	public function getSecurityToken() {
		return $this->getVar('__SECURITY_TOKEN');
	}
	
	/**
	 * Validates the given security token. Returns false, if the given token is invalid.
	 * 
	 * @param	string		$token
	 * @return	boolean
	 */
	public function checkSecurityToken($token) {
		return ($this->getVar('__SECURITY_TOKEN') === $token);
	}
	
	/**
	 * Validates the ip address or the user agent of this session.
	 * 
	 * @return 	boolean
	 */
	protected function validate() {
		if ((SESSION_VALIDATE_USER_AGENT && $this->userAgent != UserUtil::getUserAgent())) {
			return false;
		}
		if (SESSION_VALIDATE_IP_ADDRESS > 0) {
			if (SESSION_VALIDATE_IP_ADDRESS == 4) {
				if ($this->ipAddress != UserUtil::getIpAddress()) {
					return false;
				}
			}
			else {
				// skip validation for IPv6
				if (strpos($this->ipAddress, '.') !== false) {
					// validate blocks
					$oldIpAddressBlocks = explode('.', $this->ipAddress);
					$newIpAddressBlocks = explode('.', UserUtil::getIpAddress());
					
					for ($i = 0; $i < SESSION_VALIDATE_IP_ADDRESS; $i++) {
						if (!isset($oldIpAddressBlocks[$i]) || !isset($newIpAddressBlocks[$i]) || $oldIpAddressBlocks[$i] != $newIpAddressBlocks[$i]) {
							return false;
						}
					}
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Registers a variable as a session variable.
	 *
	 * @param 	string 		$key 		name of the session variable
	 * @param 	string 		$data 		value of the session variable
	 */
	public function register($key, $data) {
		$this->sessionVariables[$key] = $data;
		$this->sessionVariableChanged = true;
	}
	
	/**
	 * Unsets a session variable.
	 *
	 * @param 	string 		$key
	 */
	public function unregister($key) {
		unset($this->sessionVariables[$key]);
		$this->sessionVariableChanged = true;
	}
	
	/**
	 * Returns all registered session variables.
	 *
	 * @return 	array 		$sessionVariables
	 */
	public function getVars() {
		return $this->sessionVariables;
	}
	
	/**
	 * Returns the session variable with name $name.
	 *
	 * @param 	string		$name
	 * @return	mixed 		$sessionVariable
	 */
	public function getVar($name) {
		if (isset($this->sessionVariables[$name])) return $this->sessionVariables[$name];
		return null;
	}
	
	/**
	 * Returns true, if this session is corrupt.
	 *
	 * @return 	boolean 	$isCorrupt
	 */
	public function isCorrupt() {
		if (!$this->sessionID) return true;
		return false;
	}
	
	/**
	 * Returns the user object of this session.
	 *
	 * @return 	 User 	$user
	 */
	public function getUser() {
		return $this->user;
	}
	
	/**
	 * Stores a new user object in this session.
	 * e.g. a user was guest because not logged in, 
	 * after the loggin his old session is used to store his full data.
	 *
	 * @param 	 User 		$user
	 */
	public function changeUser(User $user) {
		if ($user->userID != 0) {
			// user is no guest
			// delete all other sessions of this user
			// delete data
			if (ENABLE_SESSION_DATA_CACHE && get_class(WCF::getCache()->getCacheSource()) == 'MemcacheCacheSource') {
				require_once(WCF_DIR.'lib/system/cache/source/MemcacheAdapter.class.php');
				$sql = "SELECT	sessionID
					FROM	wcf".WCF_N."_".$this->sessionTable." 
					WHERE 	sessionID <> '".$this->sessionID."'
						AND userID = ".$user->userID;
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					MemcacheAdapter::getInstance()->getMemcache()->delete($this->sessionTable.'_userdata_'.$row['sessionID']);
					MemcacheAdapter::getInstance()->getMemcache()->delete($this->sessionTable.'_variables_'.$row['sessionID']);
				}
				
				// delete session
				$sql = "DELETE FROM 	wcf".WCF_N."_".$this->sessionTable." 
					WHERE 		sessionID <> '".$this->sessionID."'
							AND userID = ".$user->userID;
				WCF::getDB()->sendQuery($sql);
			}
			else {
				try {
					$sql = "DELETE		session,
								session_data
						FROM		wcf".WCF_N."_".$this->sessionTable." session 
						LEFT JOIN	wcf".WCF_N."_".$this->sessionTable."_data session_data USING (sessionID)
						WHERE		session.userID = ".$user->userID."
								AND session.sessionID <> '".$this->sessionID."'";
					WCF::getDB()->sendQuery($sql);
				}
				catch (DatabaseException $e) {
					// horizon update workaround
					$sql = "DELETE FROM 	wcf".WCF_N."_".$this->sessionTable." 
						WHERE 		sessionID <> '".$this->sessionID."'
								AND userID = ".$user->userID;
					WCF::getDB()->sendQuery($sql);
				}
			}
		}
		
		// update session
		$this->user = $user;
		$sql = "UPDATE	wcf".WCF_N."_".$this->sessionTable."
			SET 	userID = ".$user->userID.",
				username = '".escapeString($user->username)."'
			WHERE 	sessionID = '".$this->sessionID."'";
		WCF::getDB()->sendQuery($sql);
		
		// save user data
		if (ENABLE_SESSION_DATA_CACHE && get_class(WCF::getCache()->getCacheSource()) == 'MemcacheCacheSource') {
			require_once(WCF_DIR.'lib/system/cache/source/MemcacheAdapter.class.php');
			MemcacheAdapter::getInstance()->getMemcache()->set($this->sessionTable.'_userdata_'.$this->sessionID, $user);
		}
		else {
			try {
				$sql = "UPDATE	wcf".WCF_N."_".$this->sessionTable."_data
					SET 	sessionVariables = '',
						userData = '".escapeString(serialize($user))."'
					WHERE 	sessionID = '".$this->sessionID."'";
				WCF::getDB()->sendQuery($sql);
			}
			catch (DatabaseException $e) {
				// horizon update workaround
				$sql = "UPDATE	wcf".WCF_N."_".$this->sessionTable."
					SET 	sessionVariables = '',
						userData = '".escapeString(serialize($user))."'
					WHERE 	sessionID = '".$this->sessionID."'";
				WCF::getDB()->sendQuery($sql);
			}
		}
	}
	
	/**
	 * Updates the session table on the end of a script (register_shutdown_function).
	 */
	public function update() {
		if ($this->doNotUpdate) return;
		
		// update session
		$sql = "UPDATE 	wcf".WCF_N."_".$this->sessionTable." 
			SET 	ipAddress = '".escapeString($this->ipAddress)."',
				userAgent = '".escapeString($this->userAgent)."',
				requestURI = '".escapeString($this->requestURI)."',
				requestMethod = '".escapeString($this->requestMethod)."',
				lastActivityTime = ".TIME_NOW.",
				packageID = ".PACKAGE_ID."
				".$this->updateSQL."
			WHERE 	sessionID = '".$this->sessionID."'";
		WCF::getDB()->sendQuery($sql);
		
		// update sesion data
		if (ENABLE_SESSION_DATA_CACHE && get_class(WCF::getCache()->getCacheSource()) == 'MemcacheCacheSource') {
			require_once(WCF_DIR.'lib/system/cache/source/MemcacheAdapter.class.php');
			// save updated session variables
			if ($this->sessionVariableChanged) {
				MemcacheAdapter::getInstance()->getMemcache()->set($this->sessionTable.'_variables_'.$this->sessionID, $this->sessionVariables);
			}
			
			// save updates user data
			if ($this->userDataChanged) {
				MemcacheAdapter::getInstance()->getMemcache()->set($this->sessionTable.'_userdata_'.$this->sessionID, $this->user);
			}
			
			// reset update data
			if ($this->userDataReset) {
				MemcacheAdapter::getInstance()->getMemcache()->delete($this->sessionTable.'_userdata_'.$this->sessionID);
			}
		}
		else {
			$sessionVariablesSQL = $userDataSQL = '';
			// save updated session variables
			if ($this->sessionVariableChanged) {
				$sessionVariablesSQL = "sessionVariables = '".escapeString(serialize($this->sessionVariables))."'";
			}
			
			// save updates user data
			if ($this->userDataChanged) {
				$userDataSQL = "userData = '".escapeString(serialize($this->user))."'";
			}
			
			// reset update data
			if ($this->userDataReset) {
				$userDataSQL = "userData = ''";
			}
			
			if (!empty($sessionVariablesSQL) || !empty($userDataSQL)) {
				try {
					$sql = "UPDATE 	wcf".WCF_N."_".$this->sessionTable."_data
						SET 	".$sessionVariablesSQL."
							".((!empty($sessionVariablesSQL) && !empty($userDataSQL)) ? ',' : '').$userDataSQL."
						WHERE 	sessionID = '".$this->sessionID."'";
					WCF::getDB()->sendQuery($sql);
					if (!WCF::getDB()->getAffectedRows()) {
						$sql = "INSERT IGNORE INTO 	wcf".WCF_N."_".$this->sessionTable."_data
							 			(sessionID, userData, sessionVariables)
							VALUES			('".$this->sessionID."', '".escapeString(serialize($this->user))."', '".escapeString(serialize($this->sessionVariables))."')";
						WCF::getDB()->sendQuery($sql);
					}
				}
				catch (DatabaseException $e) {
					// horizon update workaround
					$sql = "UPDATE 	wcf".WCF_N."_".$this->sessionTable."
						SET 	".$sessionVariablesSQL."
							".((!empty($sessionVariablesSQL) && !empty($userDataSQL)) ? ',' : '').$userDataSQL."
						WHERE 	sessionID = '".$this->sessionID."'";
					WCF::getDB()->sendQuery($sql);
				}
			}
		}
	}
	
	/**
	 * Creates a new user session for the active session.
	 *
	 * @param 	integer 	$userID
	 */
	protected function createUser($userID) {
		// create a user and add group data from cache to user_data.
		// if cache for this users groups does not exist: build it.
		$this->user = $userID ? new $this->userSessionClassName($userID) : new $this->guestSessionClassName();
		$this->user->update();
		$this->userDataChanged = true;
		$this->userDataReset = false;
	}
	
	/**
	 * Resets the current user data. 
	 * The data will be reloaded on the next page.
	 */
	public function resetUserData() {
		$this->userDataChanged = false;
		$this->userDataReset = true; 
	}
	
	/**
	 * Updates the current user data immediately. 
	 */
	public function updateUserData() {
		$this->createUser($this->userID);
	}
	
	/**
	 * Disables the update of this session.
	 * 
	 * @param 	boolean		$disable
	 */
	public function disableUpdate($disable = true) {
		$this->doNotUpdate = $disable;
	}
	
	/**
	 * Returns the active language id.
	 * 
	 * @return	integer
	 */
	public function getLanguageID() {
		return $this->languageID;
	}
	
	/**
	 * Sets the active language id.
	 * 
	 * @param 	integer		$newLanguageID
	 */
	public function setLanguageID($newLanguageID) {
		$this->languageID = $newLanguageID;
		$this->register('languageID', $newLanguageID);
	}
	
	/**
	 * Sets the request URI.
	 * 
	 * @param 	string		$newRequestURI
	 */
	public function setRequestURI($newRequestURI) {
		$this->data['requestURI'] = $newRequestURI;
	}
	
	/**
	 * Returns ids of visible languages.
	 * @deprecated
	 * @return	string
	 */
	public function getVisibleLanguages() {
		return implode(',', $this->getVisibleLanguageIDArray());
	}
	
	/**
	 * Returns ids of visible languages.
	 *
	 * @return	array<integer>
	 */
	public function getVisibleLanguageIDArray() {
		if ($this->visibleLanguageIDArray === null) {
			$this->visibleLanguageIDArray = array();
			if (!$this->spiderID && count(Language::getAvailableContentLanguages(PACKAGE_ID)) != 0) {
				$this->visibleLanguageIDArray[] = 0;
				if ($this->user->languageIDs) {
					$this->visibleLanguageIDArray = array_merge($this->visibleLanguageIDArray, explode(',', $this->user->languageIDs));
				}
				else {
					if (WCF::getLanguage()->isContentLanguage()) {
						$this->visibleLanguageIDArray[] = WCF::getLanguage()->getLanguageID();
					}
					else {
						$this->visibleLanguageIDArray[] = Language::getDefaultLanguageID();
					}
				}
			}
		}
		
		return $this->visibleLanguageIDArray;
	}
	
	/**
	 * Deletes this session.
	 */
	public function delete() {
		if (ENABLE_SESSION_DATA_CACHE && get_class(WCF::getCache()->getCacheSource()) == 'MemcacheCacheSource') {
			require_once(WCF_DIR.'lib/system/cache/source/MemcacheAdapter.class.php');
			MemcacheAdapter::getInstance()->getMemcache()->delete($this->sessionTable.'_userdata_'.$this->sessionID);
			MemcacheAdapter::getInstance()->getMemcache()->delete($this->sessionTable.'_variables_'.$this->sessionID);
			
			$sql = "DELETE FROM	wcf".WCF_N."_".$this->sessionTable."
				WHERE 		sessionID = '".$this->sessionID."'";
			WCF::getDB()->sendQuery($sql);
		}
		else {
			try {
				$sql = "DELETE 		session,
							session_data
					FROM		wcf".WCF_N."_".$this->sessionTable."
					LEFT JOIN	wcf".WCF_N."_".$this->sessionTable."_data USING (sessionID)
					WHERE 		session.sessionID = '".$this->sessionID."'";
				WCF::getDB()->sendQuery($sql);
			}
			catch (DatabaseException $e) {
				// horizon update workaround
				$sql = "DELETE FROM	wcf".WCF_N."_".$this->sessionTable."
					WHERE 		sessionID = '".$this->sessionID."'";
				WCF::getDB()->sendQuery($sql);
			}
		}
		
		// disable update
		$this->disableUpdate();
	}
	
	/**
	 * Resets active sessions of the given users.
	 * 
	 * @param	array<integer>	$userIDArray
	 * @param	boolean		$userSession
	 * @param	boolean		$adminSession
	 */
	public static function resetSessions($userIDArray = array(), $userSession = true, $adminSession = true) {
		if (!is_array($userIDArray)) {
			$userIDArray = array($userIDArray);
		}
		
		// user sessions
		if ($userSession) {
			if (ENABLE_SESSION_DATA_CACHE && get_class(WCF::getCache()->getCacheSource()) == 'MemcacheCacheSource') {
				require_once(WCF_DIR.'lib/system/cache/source/MemcacheAdapter.class.php');
				$sql = "SELECT	sessionID
					FROM	wcf".WCF_N."_session
					".(count($userIDArray) ? "WHERE userID IN (".implode(',', $userIDArray).")" : "");
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					MemcacheAdapter::getInstance()->getMemcache()->delete('session_userdata_'.$row['sessionID']);
				}
			}
			else {
				try {
					if (count($userIDArray)) {
						$sql = "UPDATE		wcf".WCF_N."_session session
							LEFT JOIN	wcf".WCF_N."_session_data session_data USING (sessionID)
							SET		session_data.userData = ''
							WHERE		session.userID IN (".implode(',', $userIDArray).")";
					}
					else {
						$sql = "UPDATE		wcf".WCF_N."_session_data
							SET		userData = ''";
					}
					WCF::getDB()->sendQuery($sql);
				}
				catch (DatabaseException $e) {
					// horizon update workaround
					$sql = "UPDATE	wcf".WCF_N."_session
						SET	userData = ''
						".(count($userIDArray) ? "WHERE userID IN (".implode(',', $userIDArray).")" : "");
					WCF::getDB()->sendQuery($sql);
				}
			}
		}
		
		// admin sessions
		if ($adminSession) {
			if (ENABLE_SESSION_DATA_CACHE && get_class(WCF::getCache()->getCacheSource()) == 'MemcacheCacheSource') {
				require_once(WCF_DIR.'lib/system/cache/source/MemcacheAdapter.class.php');
				$sql = "SELECT	sessionID
					FROM	wcf".WCF_N."_acp_session
					".(count($userIDArray) ? "WHERE userID IN (".implode(',', $userIDArray).")" : "");
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					MemcacheAdapter::getInstance()->getMemcache()->delete('acp_session_userdata_'.$row['sessionID']);
				}
			}
			else {
				try {
					if (count($userIDArray)) {
						$sql = "UPDATE		wcf".WCF_N."_acp_session session
							LEFT JOIN	wcf".WCF_N."_acp_session_data session_data USING (sessionID)
							SET		session_data.userData = ''
							WHERE		session.userID IN (".implode(',', $userIDArray).")";
					}
					else {
						$sql = "UPDATE		wcf".WCF_N."_acp_session_data
							SET		userData = ''";
					}
					WCF::getDB()->sendQuery($sql);
				}
				catch (DatabaseException $e) {
					// horizon update workaround
					$sql = "UPDATE	wcf".WCF_N."_acp_session
						SET	userData = ''
						".(count($userIDArray) ? "WHERE userID IN (".implode(',', $userIDArray).")" : "");
					WCF::getDB()->sendQuery($sql);
				}
			}
		}
	}
	
	/**
	 * Deletes active sessions of the given users.
	 * 
	 * @param	array<integer>	$userIDArray
	 * @param	boolean		$userSession
	 * @param	boolean		$adminSession
	 */
	public static function deleteSessions($userIDArray = array(), $userSession = true, $adminSession = true) {
		if (!is_array($userIDArray)) {
			$userIDArray = array($userIDArray);
		}
		
		// user sessions
		if ($userSession) {
			if (ENABLE_SESSION_DATA_CACHE && get_class(WCF::getCache()->getCacheSource()) == 'MemcacheCacheSource') {
				require_once(WCF_DIR.'lib/system/cache/source/MemcacheAdapter.class.php');
				$sql = "SELECT	sessionID
					FROM	wcf".WCF_N."_session
					".(count($userIDArray) ? "WHERE userID IN (".implode(',', $userIDArray).")" : "");
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					MemcacheAdapter::getInstance()->getMemcache()->delete('session_userdata_'.$row['sessionID']);
					MemcacheAdapter::getInstance()->getMemcache()->delete('session_variables_'.$row['sessionID']);
				}
				
				$sql = "DELETE FROM	wcf".WCF_N."_session
					".(count($userIDArray) ? "WHERE userID IN (".implode(',', $userIDArray).")" : "");
				WCF::getDB()->sendQuery($sql);
			}
			else {
				try {
					$sql = "DELETE		session,
								session_data
						FROM		wcf".WCF_N."_session session 
						LEFT JOIN	wcf".WCF_N."_session_data session_data USING (sessionID)
						".(count($userIDArray) ? "WHERE	session.userID IN (".implode(',', $userIDArray).")" : '');
					WCF::getDB()->sendQuery($sql);
				}
				catch (DatabaseException $e) {
					// horizon update workaround
					$sql = "DELETE FROM	wcf".WCF_N."_session
					".(count($userIDArray) ? "WHERE userID IN (".implode(',', $userIDArray).")" : "");
					WCF::getDB()->sendQuery($sql);
				}
			}
		}
		
		// admin sessions
		if ($adminSession) {
			if (ENABLE_SESSION_DATA_CACHE && get_class(WCF::getCache()->getCacheSource()) == 'MemcacheCacheSource') {
				require_once(WCF_DIR.'lib/system/cache/source/MemcacheAdapter.class.php');
				$sql = "SELECT	sessionID
					FROM	wcf".WCF_N."_acp_session
					".(count($userIDArray) ? "WHERE userID IN (".implode(',', $userIDArray).")" : "");
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					MemcacheAdapter::getInstance()->getMemcache()->delete('acp_session_userdata_'.$row['sessionID']);
					MemcacheAdapter::getInstance()->getMemcache()->delete('acp_session_variables_'.$row['sessionID']);
				}
				
				$sql = "DELETE FROM	wcf".WCF_N."_acp_session
					".(count($userIDArray) ? "WHERE userID IN (".implode(',', $userIDArray).")" : "");
				WCF::getDB()->sendQuery($sql);
			}
			else {
				try {
					$sql = "DELETE		session,
								session_data
						FROM		wcf".WCF_N."_acp_session session 
						LEFT JOIN	wcf".WCF_N."_acp_session_data session_data USING (sessionID)
						".(count($userIDArray) ? "WHERE	session.userID IN (".implode(',', $userIDArray).")" : '');
					WCF::getDB()->sendQuery($sql);
				}
				catch (DatabaseException $e) {
					// horizon update workaround
					$sql = "DELETE FROM	wcf".WCF_N."_acp_session
						".(count($userIDArray) ? "WHERE userID IN (".implode(',', $userIDArray).")" : "");
					WCF::getDB()->sendQuery($sql);
				}
			}
		}
	}
	
	/**
	 * Deletes the expired sessions.
	 * 
	 * @param	integer		$timestamp
	 * @param	boolean		$userSession
	 * @param	boolean		$adminSession
	 */
	public static function deleteExpiredSessions($timestamp, $userSession = true, $adminSession = true) {
		// user sessions
		if ($userSession) {
			if (ENABLE_SESSION_DATA_CACHE && get_class(WCF::getCache()->getCacheSource()) == 'MemcacheCacheSource') {
				require_once(WCF_DIR.'lib/system/cache/source/MemcacheAdapter.class.php');
				$sql = "SELECT	sessionID
					FROM	wcf".WCF_N."_session
					WHERE	lastActivityTime < ".$timestamp;
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					MemcacheAdapter::getInstance()->getMemcache()->delete('session_userdata_'.$row['sessionID']);
					MemcacheAdapter::getInstance()->getMemcache()->delete('session_variables_'.$row['sessionID']);
				}
				
				$sql = "DELETE FROM	wcf".WCF_N."_session
					WHERE		lastActivityTime < ".$timestamp;
				WCF::getDB()->sendQuery($sql);
			}
			else {
				try {
					$sql = "DELETE		session,
								session_data
						FROM		wcf".WCF_N."_session session 
						LEFT JOIN	wcf".WCF_N."_session_data session_data USING (sessionID)
						WHERE		session.lastActivityTime  < ".$timestamp;
					WCF::getDB()->sendQuery($sql);
				}
				catch (DatabaseException $e) {
					// horizon update workaround
					$sql = "DELETE FROM	wcf".WCF_N."_session
						WHERE		lastActivityTime < ".$timestamp;
					WCF::getDB()->sendQuery($sql);
				}
			}
		}
		
		// admin sessions
		if ($adminSession) {
			if (ENABLE_SESSION_DATA_CACHE && get_class(WCF::getCache()->getCacheSource()) == 'MemcacheCacheSource') {
				require_once(WCF_DIR.'lib/system/cache/source/MemcacheAdapter.class.php');
				$sql = "SELECT	sessionID
					FROM	wcf".WCF_N."_acp_session
					WHERE	lastActivityTime < ".$timestamp;
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					MemcacheAdapter::getInstance()->getMemcache()->delete('acp_session_userdata_'.$row['sessionID']);
					MemcacheAdapter::getInstance()->getMemcache()->delete('acp_session_variables_'.$row['sessionID']);
				}
				
				$sql = "DELETE FROM	wcf".WCF_N."_acp_session
					WHERE		lastActivityTime < ".$timestamp;
				WCF::getDB()->sendQuery($sql);
			}
			else {
				try {
					$sql = "DELETE		session,
								session_data
						FROM		wcf".WCF_N."_acp_session session 
						LEFT JOIN	wcf".WCF_N."_acp_session_data session_data USING (sessionID)
						WHERE		session.lastActivityTime  < ".$timestamp;
					WCF::getDB()->sendQuery($sql);
				}
				catch (DatabaseException $e) {
					// horizon update workaround
					$sql = "DELETE FROM	wcf".WCF_N."_acp_session
						WHERE		lastActivityTime < ".$timestamp;
					WCF::getDB()->sendQuery($sql);
				}
			}
		}
		
		self::deleteOrphanedSessionData($userSession, $adminSession);
	}
	
	/**
	 * Deletes orphaned session data.
	 * 
	 * @param	boolean		$userSession
	 * @param	boolean		$adminSession
	 */
	public static function deleteOrphanedSessionData($userSession = true, $adminSession = true) {
		if ($userSession) {
			if (!ENABLE_SESSION_DATA_CACHE || get_class(WCF::getCache()->getCacheSource()) == 'DiskCacheSource') {
				try {
					$sql = "DELETE FROM	wcf".WCF_N."_session_data 
						WHERE		sessionID NOT IN (
									SELECT	sessionID
									FROM	wcf".WCF_N."_session
								)";
					WCF::getDB()->sendQuery($sql);
				}
				catch (DatabaseException $e) {
					// horizon update workaround
				}
			}
		}
		
		if ($adminSession) {
			if (!ENABLE_SESSION_DATA_CACHE || get_class(WCF::getCache()->getCacheSource()) == 'DiskCacheSource') {
				try {
					$sql = "DELETE FROM	wcf".WCF_N."_acp_session_data 
						WHERE		sessionID NOT IN (
									SELECT	sessionID
									FROM	wcf".WCF_N."_acp_session
								)";
					WCF::getDB()->sendQuery($sql);
				}
				catch (DatabaseException $e) {
					// horizon update workaround
				}
			}
		}
	} 
}
?>