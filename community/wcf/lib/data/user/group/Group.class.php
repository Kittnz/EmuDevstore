<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');
}

/**
 * Represents a user group in database.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group
 * @category 	Community Framework
 */
class Group extends DatabaseObject {
	protected $groupOptions = null;
	protected static $cache = null;
	protected static $accessibleGroups = null;
	
	// group types
	const EVERYONE = 1;
	const GUESTS = 2;
	const USERS = 3;
	const OTHER = 4;
	
	/**
	 * Creates a new Group object.
	 * 
	 * @param	integer		$groupID
	 * @param 	array		$row
	 */
	public function __construct($groupID, $row = null) {
		if ($row === null) {
			$this->getCache();
			if (isset(self::$cache['groups'][$groupID])) $row = self::$cache['groups'][$groupID];
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Returns the name of this group.
	 * 
	 *  @return 	string		name of this group
	 */
	public function __toString() {
		return $this->groupName;
	}
	
	/**
	 * Returns true, if this group is accessible for the active user.
	 * 
	 * @return 	boolean
	 */
	public function isAccessible() {
		return self::isAccessibleGroup($this->groupID);
	}
	
	/**
	 * Returns the value of the group option with the given name.
	 * 
	 * @param	string		$name
	 * @return	mixed
	 */
	public function getGroupOption($name) {
		if ($this->groupOptions === null) {
			// get all options and filter options with low priority
			$groupOptionIDs = array();
			$sql = "SELECT		optionName, optionID 
				FROM		wcf".WCF_N."_group_option option_table,
						wcf".WCF_N."_package_dependency package_dependency
				WHERE 		option_table.packageID = package_dependency.dependency
						AND package_dependency.packageID = ".PACKAGE_ID."
				ORDER BY	package_dependency.priority";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$groupOptionIDs[$row['optionName']] = $row['optionID'];
			}
			
			if (count($groupOptionIDs)) {
				$sql = "SELECT		group_option.optionName, option_value.optionValue
					FROM		wcf".WCF_N."_group_option_value option_value
					LEFT JOIN	wcf".WCF_N."_group_option group_option
					ON		(group_option.optionID = option_value.optionID)
					WHERE		option_value.groupID = ".$this->groupID."
							AND option_value.optionID IN (".implode(',', $groupOptionIDs).")";
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					$this->groupOptions[$row['optionName']] = $row['optionValue'];
				}
			}
		}
		
		if (isset($this->groupOptions[$name])) {
			return $this->groupOptions[$name];
		}
		
		return null;
	}
	
	/**
	 * Returns the id of the groups with the given types.
	 * Returns an empty array, if no group with the given types is found. 
	 * 
	 * @param	mixed		$types		an array of group types
	 * @return	mixed
	 */
	public static function getGroupIdsByType($types) {
		self::getCache();
		
		if (!is_array($types)) $types = array($types);
		
		$groupIDs = array();
		foreach ($types as $type) {
			if (isset(self::$cache['types'][$type])) {
				$groupIDs = array_merge($groupIDs, self::$cache['types'][$type]);
			}
		}
		
		return $groupIDs;
	}
	
	/**
	 * Returns a list (id => name) of the groups with the given types.
	 * @see Group::getGroupIdsByType()
	 */
	public static function getGroupsByType($types) {
		$groupIDs = self::getGroupIdsByType($types);
		$groups = array();
		
		foreach ($groupIDs as $groupID) {
			$groups[$groupID] = self::$cache['groups'][$groupID]['groupName'];
		}
		
		return $groups;
	}
	
	/**
	 * Returns a list of all existing user groups.
	 * 
	 * @return	array
	 */
	public static function getAllGroups() {
		self::getCache();
		$groups = array();
		
		foreach (self::$cache['groups'] as $groupID => $group) {
			$groups[$groupID] = $group['groupName'];
		}
		
		return $groups;
	}
		
	/**
	 * Returns the id of the group with the given type.
	 * Returns false, if no group with the given type is found. 
	 * 
	 * @param	integer		$type
	 * @return	mixed
	 */
	public static function getGroupIdByType($type) {
		$idArray = self::getGroupIdsByType($type);
		if (count($idArray) > 0) return $idArray[0];
		return false;
	}
	
	/**
	 * Loads the group cache.
	 */
	protected static function getCache() {
		if (self::$cache === null) {
			WCF::getCache()->addResource('groups', WCF_DIR.'cache/cache.groups.php', WCF_DIR.'lib/system/cache/CacheBuilderGroups.class.php');
			self::$cache = WCF::getCache()->get('groups');
		}
	}
	
	/**
	 * Returns true, if this given group is accessible for the active user.
	 * 
	 * @param	mixed		$groupIDs
	 * @return 	boolean
	 */
	public static function isAccessibleGroup($groupIDs) {
		if (self::$accessibleGroups === null) {
			self::$accessibleGroups = explode(',', WCF::getUser()->getPermission('admin.user.accessibleGroups'));
		}
		
		if (!is_array($groupIDs)) $groupIDs = array($groupIDs);
		if (count($groupIDs) == 0) return false;
		
		foreach ($groupIDs as $groupID) {
			if (!in_array($groupID, self::$accessibleGroups)) {
				return false;
			} 
		}
		
		return true;
	}
	
	/**
	 * Checks if the user who edits the group is a member of given group.
	 * 
	 * @param 	integer		$groupID
	 * @return	boolean		isMember		    
	 */
	public static function isMember($groupID) {
		// check membership
		if (in_array($groupID, WCF::getUser()->getGroupIDs())) return true;
		else return false;
	}
	
	/**
	 * Returns a list of accessible groups.
	 * 
	 * @param	array		$groupTypes
	 * @param	array		$invalidGroupTypes
	 * @return	array
	 */
	public static function getAccessibleGroups($groupTypes = array(), $invalidGroupTypes = array()) {
		$groups = (count($groupTypes) > 0 ? self::getGroupsByType($groupTypes) : self::getAllGroups());
		
		if (count($invalidGroupTypes) > 0) {
			$invalidGroups = self::getGroupsByType($invalidGroupTypes);
			foreach ($invalidGroups as $groupID => $name) {
				unset($groups[$groupID]);
			}
		}
		
		foreach ($groups as $key => $value) {
			if (!self::isAccessibleGroup($key)) {
				unset($groups[$key]);
			}
		}
		
		return $groups;
	}
}
?>