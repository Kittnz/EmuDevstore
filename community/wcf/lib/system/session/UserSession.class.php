<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/data/user/User.class.php');
	require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
}

/**
 * Class for user session handling. Fewer database queries through cached data in session table (user object).
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.session
 * @category 	Community Framework
 */
class UserSession extends User {
	protected $groupData = array();
	
	/**
	 * Creates a new UserSession object.
	 * 
	 * @see User::__construct()
	 */
	public function __construct($userID = null, $row = null, $username = null, $email = null) {
		$this->sqlSelects .= "	GROUP_CONCAT(DISTINCT groups.groupID ORDER BY groups.groupID ASC SEPARATOR ',') AS groupIDs,
					GROUP_CONCAT(DISTINCT languages.languageID ORDER BY languages.languageID ASC SEPARATOR ',') AS languageIDs,";
		$this->sqlJoins .= " 	LEFT JOIN wcf".WCF_N."_user_to_groups groups ON (groups.userID = user.userID)
					LEFT JOIN wcf".WCF_N."_user_to_languages languages ON (languages.userID = user.userID) ";
		$this->sqlGroupBy = " 	GROUP BY user.userID ";
		
		parent::__construct($userID, $row, $username, $email);
		
		// save memory
		$this->sqlJoins = $this->sqlSelects = $this->sqlGroupBy = null;
	}
	
	/**
	 * Updates the user session.
	 */
	public function update() {}
	
	/**
	 * Initialises the user session.
	 */
	public function init() {}
	
	/**
	 * This function is called by parent constructor. 
	 * The parent constructor reads user data from database.
	 *
	 * @param 	array 		$row
	 */
	protected function handleData($data) {
		// store user data in this object.
		parent::handleData($data);
		
		// get groups where user is in.
		if (isset($data['groupIDs'])) {
			$this->groupIDs = preg_split('/,/', $data['groupIDs'], -1, PREG_SPLIT_NO_EMPTY);
			if (count($this->groupIDs) > 0) $this->getGroupData();
		}
		else if (!$this->userID) {
			$this->groupIDs = Group::getGroupIdsByType(array(Group::EVERYONE, Group::GUESTS));
			if (count($this->groupIDs) > 0) $this->getGroupData();
		}
	}
	
	/**
	 * Gets the group data of this user from cache.
	 */
	protected function getGroupData() {
		$groups = implode(",", $this->groupIDs);
		$groupsFileName = StringUtil::getHash(implode("-", $this->groupIDs));
		
		// register cache resource
		WCF::getCache()->addResource('groups-'.PACKAGE_ID.'-'.$groups, WCF_DIR.'cache/cache.groups-'.PACKAGE_ID.'-'.$groupsFileName.'.php', WCF_DIR.'lib/system/cache/CacheBuilderGroupPermissions.class.php');
		
		// get group data from cache
		$this->groupData = WCF::getCache()->get('groups-'.PACKAGE_ID.'-'.$groups);
		if (isset($this->groupData['groupIDs']) && $this->groupData['groupIDs'] != $groups) {
			$this->groupData = array();
		}
	}
	
	/**
	 * Returns the value of the permission with the given name.
	 * 
	 * @param 	string		$permission
	 * @return	mixed		permission value
	 */
	public function getPermission($permission) {
		if (!isset($this->groupData[$permission])) return false;
		return $this->groupData[$permission];
	}
	
	/**
	 * Checks the requested permission.
	 * Throws a PermissionDeniedException if the permission is false.
	 * @see UserSession::getPermission()
	 */
	public function checkPermission($permissions) {
		if (!is_array($permissions)) $permissions = array($permissions);
		
		$result = false;
		foreach ($permissions as $permission) {
			$result = $result || $this->getPermission($permission);
		}
		
		if (!$result) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Saves the status of a specific page element. e.g. a closable list.
	 * 
	 * @param	string		$name
	 * @param	string		$status
	 * @return	boolean		false on failure
	 */
	public static function saveStatus($name, $status) {
		if (WCF::getUser()->userID) {
			// save as permanent user option
			WCF::getCache()->addResource('user-option-'.PACKAGE_ID, WCF_DIR.'cache/cache.user-option-'.PACKAGE_ID.'.php', WCF_DIR.'lib/system/cache/CacheBuilderOption.class.php');
			$validOptions = WCF::getCache()->get('user-option-'.PACKAGE_ID, 'options');
			if (!isset($validOptions[$name]) || $validOptions[$name]['visible'] != 4 || $validOptions[$name]['editable'] != 4) {
				return false;
			}
			
			$options = array();
			$options[$name] = $status;
			$editor = WCF::getUser()->getEditor();
			$editor->updateOptions($options);
			WCF::getSession()->resetUserData();
		}
		else {
			// save temporary as session variable
			WCF::getSession()->register($name, $status);
		}
		
		return true;
	}
}
?>