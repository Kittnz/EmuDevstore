<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/data/user/User.class.php');
	require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
	require_once(WCF_DIR.'lib/system/session/Session.class.php');
}

/**
 * UserEditor creates, edits or deletes users.
 *
 * @author	Benjamin Kunz
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user
 * @category 	Community Framework
 */
class UserEditor extends User {
	/**
	 * Adds a user to the groups he should be in.
	 *
	 * @param 	mixed 		$groups
	 * @param	boolean		$deleteOldGroups
	 * @param 	boolean 	$addDefaultGroups
	 */
	public function addToGroups($groupIDs, $deleteOldGroups = true, $addDefaultGroups = true) {
		if (!is_array($groupIDs)) {
			$groupIDs = array($groupIDs);
		}
		
		// add default groups
		if ($addDefaultGroups) {
			$groupIDs = array_merge($groupIDs, Group::getGroupIdsByType(array(Group::EVERYONE, Group::USERS)));
		}
		
		// build sql
		$insertSQL = '';
		foreach ($groupIDs as $groupID) {
			if (!empty($insertSQL)) $insertSQL .= ',';
			$insertSQL .= '('.$this->userID.', '.$groupID.')';
		}
		
		// delete old groups
		if ($deleteOldGroups) {
			$sql = "DELETE FROM	wcf".WCF_N."_user_to_groups
				WHERE		userID = ".$this->userID;
			WCF::getDB()->sendQuery($sql);
		}
		
		// insert new groups
		if (!empty($insertSQL)) {
			$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_to_groups 
							(userID, groupID) 
				VALUES 			".$insertSQL;
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * Adds a user to a user group.
	 *
	 * @param 	integer 	$groupID
	 */
	public function addToGroup($groupID) {
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_to_groups 
						(userID, groupID) 
			VALUES 			(".$this->userID.", ".$groupID.")";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Removes a user from a user group.
	 *
	 * @param 	integer 	$groupID
	 */
	public function removeFromGroup($groupID) {
		$sql = "DELETE FROM	wcf".WCF_N."_user_to_groups
			WHERE		userID = ".$this->userID."
					AND groupID = ".$groupID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Saves the visible languages of a user.
	 *
	 * @param 	mixed 		$languageIDs
	 */
	public function addToLanguage($languageIDs) {
		if (!is_array($languageIDs)) {
			$languageIDs = array($languageIDs);
		}
		
		// build sql
		$insertSQL = '';
		foreach ($languageIDs as $languageID) {
			if (!empty($insertSQL)) $insertSQL .= ',';
			$insertSQL .= '('.$this->userID.', '.$languageID.')';
		}
		
		// delete old languages
		$sql = "DELETE FROM	wcf".WCF_N."_user_to_languages
			WHERE		userID = ".$this->userID;
		WCF::getDB()->sendQuery($sql);
		
		// insert new groups
		if (!empty($insertSQL)) {
			$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_to_languages 
							(userID, languageID) 
				VALUES 			".$insertSQL;
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * Creates a new user with all required and filled out additional fields.
	 *
	 * @param 	string 		$username
	 * @param 	string 		$email
	 * @param	string 		$password
	 * @param	mixed		$groupIDs
	 * @param	array		$userOptions
	 * @param	array		$additionalFields
	 * @param 	array		$visibleLanguages
	 * @param 	boolean		$addDefaultGroups
	 * @return 	UserEditor
	 */
	public static function create($username, $email, $password, $groupIDs, $userOptions = array(), $additionalFields = array(), $visibleLanguages = array(), $addDefaultGroups = true) {
		// insert main data
		$salt 		= StringUtil::getRandomID();
		$password 	= StringUtil::getDoubleSaltedHash($password, $salt);
		$userID 	= self::insert($username, $email, $password, $salt, $additionalFields);
		
		// insert user options
		self::insertUserOptions($userID, $userOptions);
		
		// insert groups
		$user = new UserEditor($userID);
		$user->addToGroups($groupIDs, false, $addDefaultGroups);
		$user->addToLanguage($visibleLanguages);
		
		return $user;
	}
	
	/**
	 * Inserts the main user data into the user table. 
	 *
	 * @param 	string 		$username
	 * @param 	string 		$email
	 * @param 	string 		$password
	 * @param 	string 		$salt
	 * @param	array		$additionalFields
	 * @return 	integer		new userID
	 */
	public static function insert($username, $email, $password, $salt, $additionalFields = array()) { 
		$additionalColumnNames = $additionalColumnValues = '';
		if (!isset($additionalFields['registrationDate'])) $additionalFields['registrationDate'] = TIME_NOW;
		foreach ($additionalFields as $key => $value) {
			$additionalColumnNames .= ', '.$key;
			$additionalColumnValues .= ', '.((is_int($value)) ? $value : "'".escapeString($value)."'");
		}
		
		$sql = "INSERT INTO	wcf".WCF_N."_user
					(username, email, password, salt
					".$additionalColumnNames.")
			VALUES		('".escapeString($username)."',
					'".escapeString($email)."',
					'".escapeString($password)."',
					'".escapeString($salt)."'
					".$additionalColumnValues.")";
		WCF::getDB()->sendQuery($sql);
		return WCF::getDB()->getInsertID();
	}
	
	/**
	 * Inserts the additional user data into the user table. 
	 *
	 * @param 	integer		$userID
	 * @param 	array 		$userOptions
	 * @param	boolean		$update
	 */
	protected static function insertUserOptions($userID, $userOptions = array(), $update = false) {
		// get default values from options.
		$defaultValues = array();
		if (!$update) {
			$sql = "SELECT	optionID, defaultValue
				FROM	wcf".WCF_N."_user_option";
			$result = WCF::getDB()->sendQuery($sql);
			
			while ($row = WCF::getDB()->fetchArray($result)) {
				if ($row['defaultValue']) {
					$defaultValues[$row['optionID']] = $row['defaultValue'];
				}	
			}
		}
		
		// build the sql strings. 
		$columnNames = $columnValues = $updateColumns = '';
		foreach ($userOptions as $option) {
			$columnNames .= ', userOption'.$option['optionID'];
			$columnValues .= ", '".escapeString($option['optionValue'])."'";
			
			if (!empty($updateColumns)) $updateColumns .= ',';
			$updateColumns .= 'userOption'.$option['optionID'].' = VALUES(userOption'.$option['optionID'].')';
			
			// the value of this option was send via "activeOptions".
			unset($defaultValues[$option['optionID']]);
		}
		
		// add default values from inactive options.
		foreach ($defaultValues as $optionID => $optionValue) {
			$columnNames .= ', userOption'.$optionID;
			$columnValues .= ", '".escapeString($optionValue)."'";
			
			if (!empty($updateColumns)) $updateColumns .= ',';
			$updateColumns .= 'userOption'.$optionID.' = VALUES(userOption'.$optionID.')';
		}
		
		// insert option values to user record.
		if (!$update || !empty($updateColumns)) {
			$sql = "INSERT INTO			wcf".WCF_N."_user_option_value
								(userID".$columnNames.")
				VALUES 				(".$userID.$columnValues.")
				".(!empty($updateColumns) ? "ON DUPLICATE KEY UPDATE ".$updateColumns : "");
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * Updates this user. 
	 * 
	 * @param	string		$username
	 * @param	string		$email 
	 * @param	string		$password
	 * @param	mixed		$groupIDs
	 * @param	array		$dynamicOptions
	 * @param	array 		$additionalFields
	 * @param 	array		$visibleLanguages
	 */
	public function update($username = '', $email = '', $password = '', $groupIDs = null, $dynamicOptions = null, $additionalFields = array(), $visibleLanguages = null) {
		$this->updateUser($username, $email, $password, $additionalFields);
		if ($groupIDs !== null) $this->addToGroups($groupIDs);
		if ($visibleLanguages !== null) $this->addToLanguage($visibleLanguages);
		if ($dynamicOptions !== null) self::insertUserOptions($this->userID, $dynamicOptions, true);	
	}
	
	/**
	 * Updates additional user fields.
	 * 
	 * @param	array 	$additionalFields
	 */
	public function updateFields($additionalFields) {
		$this->updateUser('', '', '', $additionalFields);
	}
	
	/**
	 * Updates the given user options.
	 * 
	 * @param	array	$options
	 */
	public function updateOptions($options) {
		// get user option cache if necessary
		if (self::$userOptions === null) {
			self::getUserOptionCache();
		}
		
		$dynamicOptions = array();
		foreach ($options as $name => $value) {
			if (isset(self::$userOptions[$name])) {
				$option = self::$userOptions[$name];
				$option['optionValue'] = $value;
				$dynamicOptions[] = $option;
			}
		}
		
		$this->update('', '', '', null, $dynamicOptions);
	}
	
	/**
	 * Updates the static data of this user.
	 *
	 * @param	string		$username
	 * @param	string		$email 
	 * @param	string		$password
	 * @param	array		$additionalFields
	 */
	protected function updateUser($username = '', $email = '', $password = '', $additionalFields = array()) {
		// create new salt
		if (!empty($password)) {
			$salt 		= StringUtil::getRandomID();
			$password	= StringUtil::getDoubleSaltedHash($password, $salt); 
		}
		
		$updateSQL = '';
		if (!empty($username)) {
			$updateSQL = "username = '".escapeString($username)."'";
			$this->username = $username;
		}
		if (!empty($email)) {
			if (!empty($updateSQL)) $updateSQL .= ',';
			$updateSQL .= "email = '".escapeString($email)."'";
			$this->email = $email;
		}
		if (!empty($password)) {
			if (!empty($updateSQL)) $updateSQL .= ',';
			$updateSQL .= "password = '".$password."', salt = '".$salt."'";
			$this->password = $password;
			$this->salt = $salt;
		}
		
		foreach ($additionalFields as $key => $value) {
			if (!empty($updateSQL)) $updateSQL .= ',';
			$updateSQL .= $key.'='.((is_int($value)) ? $value : "'".escapeString($value)."'");
		}
		
		if (!empty($updateSQL)) {
			// save user
			$sql = "UPDATE	wcf".WCF_N."_user
				SET	".$updateSQL."
				WHERE 	userID = ".$this->userID;
			WCF::getDB()->sendQuery($sql);
		}
		
		$this->resetSession();
	}
	
	/**
	 * Resets active sessions of this user.
	 */
	public function resetSession() {
		Session::resetSessions($this->userID);
	}
	
	/**
	 * Deletes users.
	 * Returns the number of deleted users.
	 *
	 * @param	array		$userIDs
	 * @return	integer
	 */
	public static function deleteUsers($userIDs) {
		if (count($userIDs) == 0) return 0;
		
		$userIDsStr = implode(',', $userIDs);
		
		// delete options from this user
		$sql = "DELETE 	FROM wcf".WCF_N."_user_option_value
			WHERE 	userID IN (".$userIDsStr.")";
		WCF::getDB()->sendQuery($sql);
		
		// delete user from user table
		$sql = "DELETE 	FROM wcf".WCF_N."_user
			WHERE 	userID IN (".$userIDsStr.")";
		WCF::getDB()->sendQuery($sql);
		
		// delete user to groups
		$sql = "DELETE 	FROM wcf".WCF_N."_user_to_groups
			WHERE 	userID IN (".$userIDsStr.")";
		WCF::getDB()->sendQuery($sql);
		
		// delete user to languages
		$sql = "DELETE 	FROM wcf".WCF_N."_user_to_languages
			WHERE 	userID IN (".$userIDsStr.")";
		WCF::getDB()->sendQuery($sql);
		
		// delete sessions
		Session::deleteSessions($userIDs);
		
		return count($userIDs);
	}
	
	/**
	 * Unmarks all marked users.
	 */
	public static function unmarkAll() {
		WCF::getSession()->unregister('markedUsers');
	}
	
	/**
	 * Returns true, if this user is marked.
	 * 
	 * @return 	boolean
	 */
	public function isMarked() {
		$sessionVars = WCF::getSession()->getVars();
		if (isset($sessionVars['markedUsers'])) {
			if (in_array($this->userID, $sessionVars['markedUsers'])) return 1;
		}
		
		return 0;
	}
}
?>