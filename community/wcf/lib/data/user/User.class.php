<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');
	require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
}

/**
 * User class defines all functions to "get" the information (data) of a user. It is a reading class only.
 *
 * This class provides all necessary functions to "read" all possible userdata. 
 * This includes required data and optional data. To set this userdata read 
 * the documentation of UserEditor.class.php which extends User.class.php
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user
 * @category 	Community Framework
 */
class User extends DatabaseObject {
	protected $sqlJoins = '';
	protected $sqlSelects = '';
	protected $sqlGroupBy = '';
	protected $groupIDs = null;
	protected static $userOptions = null;
	
	/**
	 * Gets the main data of the passed user (id, name or whole datablock) 
	 * and pass it over to the "protected function initUser()".
	 * You can also create an emtpy user object e.g. to search for users.
	 *
	 * @param 	string 		$userID
	 * @param 	array 		$row
	 * @param 	string 		$username
	 * @param 	string 		$email
	 */
	public function __construct($userID, $row = null, $username = null, $email = null) {
		// set sql join to user_data table
		$this->sqlSelects .= 'user_option.*,'; 
		$this->sqlJoins .= "LEFT JOIN wcf".WCF_N."_user_option_value user_option ON (user_option.userID = user.userID)";
		
		// execute sql statement
		$sqlCondition = '';
		if ($userID !== null) {
			$sqlCondition = "user.userID = ".$userID;
		}
		else if ($username !== null) {
			$sqlCondition = "user.username = '".escapeString($username)."'";
		}
		else if ($email !== null) {
			$sqlCondition = "user.email = '".escapeString($email)."'";
		}
		
		if (!empty($sqlCondition)) {
			$sql = "SELECT 	".$this->sqlSelects."
					user.*
				FROM 	wcf".WCF_N."_user user
					".$this->sqlJoins."
				WHERE 	".$sqlCondition.
					$this->sqlGroupBy;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		// handle result set
		parent::__construct($row);
	}
	
	/**
	 * @see DatabaseObject::handleData()
	 */
	protected function handleData($data) {
		parent::handleData($data);
		if (!$this->userID) $this->data['userID'] = 0;
	}
	
	/**
	 * Returns true, if the given password is the correct password for this user.
	 *
	 * @param 	string		$password
	 * @return 	boolean 	password correct
	 */		
	public function checkPassword($password) {
		return ($this->password == StringUtil::getDoubleSaltedHash($password, $this->salt));
	}
	
	/**
	 * Returns true, if the given password hash from a cookie is the correct password for this user.
	 *
	 * @param 	string		$passwordHash
	 * @return 	boolean 	password correct
	 */
	public function checkCookiePassword($passwordHash) {
		return ($this->password == StringUtil::encrypt($this->salt . $passwordHash));
	}
	
	/**
	 * Returns an array with the all the groups in which the actual user is a member.
	 *
	 * @return 	array 		$groupIDs
	 */
	public function getGroupIDs() {
		if ($this->groupIDs === null) {
			$this->groupIDs = array();
			
			if (!$this->userID) {
				// user is a guest
				// use default guest group
				$this->groupIDs[] = Group::getGroupIdByType(Group::GUESTS);
			}
			else {
				$sql = "SELECT 		groupID 
					FROM 		wcf".WCF_N."_user_to_groups
					WHERE 		userID = ".$this->userID."
					ORDER BY	groupID";
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					$this->groupIDs[] = $row['groupID'];
				}
			}
		}
		return $this->groupIDs;
	}
	
	/**
	 * Returns the salt of the user password.
	 * 
	 * @return	string		password salt
	 */
	public function getSalt() {
		return $this->salt;	
	}
	
	/**
	 * alias of the getUsername() function
	 * @see User::getUsername()
	 */
	public function __toString() {
		return $this->username;
	}
	
	/**
	 * Returns a UserEditor object to edit this user.
	 * 
	 * @return	UserEditor
	 */
	public function getEditor() {
		require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
		return new UserEditor($this->userID);
	}
	
	/**
	 * Returns the value of the user option with the given name.
	 * 
	 * @param	string		$name		user option name
	 * @return	mixed				user option value
	 */
	public function getUserOption($name) {
		$optionID = self::getUserOptionID($name);
		if ($optionID === null) {
			return null;
		}
		
		if (!isset($this->data['userOption'.$optionID])) return null;
		return $this->data['userOption'.$optionID];
	}
	
	/**
	 * @see DatabaseObject::__get()
	 */
	public function __get($name) {
		$value = parent::__get($name);
		if ($value === null) $value = $this->getUserOption($name);
		return $value;
	}
	
	/**
	 * Gets the user with the highest registration date timestamp from database and returns a new user object with his data.
	 *
	 * @return 	 User
	 */
	public static function getNewest() {
		$sql = "SELECT 		*
			FROM 		wcf".WCF_N."_user
			ORDER BY 	registrationDate DESC";
		$result = WCF::getDB()->getFirstRow($sql);
		return new User(null, $result);
	}
	
	/**
	 * Gets all user options from cache.
	 */
	protected static function getUserOptionCache() {
		$cacheName = 'user-option-'.PACKAGE_ID;
		WCF::getCache()->addResource($cacheName, WCF_DIR.'cache/cache.'.$cacheName.'.php', WCF_DIR.'lib/system/cache/CacheBuilderOption.class.php');
		self::$userOptions = WCF::getCache()->get($cacheName, 'options');
	}
	
	/**
	 * Returns the id of a user option.
	 * 
	 * @param	string		$name
	 * @return	integer		id
	 */
	public static function getUserOptionID($name) {
		// get user option cache if necessary
		if (self::$userOptions === null) {
			self::getUserOptionCache();
		}
		
		if (!isset(self::$userOptions[$name])) {
			return null;
		}
		
		return self::$userOptions[$name]['optionID'];
	}
	
	/**
	 * Returns a list of users.
	 * 
	 * @param 	string		$userIDs
	 * @return 	array		users
	 */
	public static function getUsers($userIDs) {
		$users = array();
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_user
			WHERE		userID IN (".$userIDs.")
			ORDER BY	username";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$users[] = new User(null, $row);
		}
		
		return $users;
	}
}
?>