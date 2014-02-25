<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
require_once(WCF_DIR.'lib/system/session/Session.class.php');

/**
 * GroupEditor creates, edits or deletes groups.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group
 * @category 	Community Framework
 */
class GroupEditor extends Group {
	/**
	 * Creates a new GroupEditor object.
	 * 
	 * @param	integer		$groupID
	 * @param 	array		$row
	 */
	public function __construct($groupID, $row = null) {
		if ($row === null) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_group
				WHERE	groupID = ".$groupID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		DatabaseObject::__construct($row);
	}
	
	/**
	 * Creates a new user group with all required and filled out additional fields.
	 *
	 * @param 	string 			$groupName
	 * @param	array			$groupOptions
	 * @param	array			$additionalFields
	 * @return 	GroupEditor
	 */
	public static function create($groupName, $groupOptions = array(), $additionalFields = array()) {
		$groupID = self::insert($groupName, $additionalFields);
		self::insertGroupOptions($groupID, $groupOptions);
		self::updateAccessibleGroups($groupID);
		
		// clear cache
		self::clearCache();
		
		// return new group
		return new GroupEditor($groupID);
	}
	
	/**
	 * Inserts the main user group data into the 'group' table. 
	 *
	 * @param 	string 		$groupName
	 * @param	array		$additionalFields 
	 * @return 	integer		id of the new group
	 */
	public static function insert($groupName, $additionalFields = array()){ 
		$keys = $values = '';
		if (!isset($additionalFields['groupType'])) $additionalFields['groupType'] = self::OTHER;
		foreach ($additionalFields as $key => $value) {
			$keys .= ','.$key;
			$values .= ",'".escapeString($value)."'";
		}
		
		$sql = "INSERT INTO	wcf".WCF_N."_group
					(groupName
					".$keys.")
			VALUES		('".escapeString($groupName)."'
					".$values.")";
		WCF::getDB()->sendQuery($sql);
		return WCF::getDB()->getInsertID();
	}
	
	/**
	 * Inserts the additional user group data into the 'group_option_value' table. 
	 *
	 * @param 	integer		$groupID
	 * @param 	array 		$groupOptions
	 * @param 	boolean		$update
	 */
	protected static function insertGroupOptions($groupID, $groupOptions = array(), $update = false) { 
		// get default values from options.
		$defaultValues = array();
		if (!$update) {
			$sql = "SELECT	optionID, defaultValue
				FROM	wcf".WCF_N."_group_option";
			$result = WCF::getDB()->sendQuery($sql);
			
			while ($row = WCF::getDB()->fetchArray($result)) {
				$defaultValues[$row['optionID']] = $row['defaultValue'];	
			}
		}
		
		// build the sql strings. 
		$inserts = '';
		foreach ($groupOptions as $option) {
			if (!empty($inserts)) $inserts .= ',';
			$inserts .= "(".$groupID.", ".$option['optionID'].", '".escapeString($option['optionValue'])."')";
			
			// the value of this option was send via "activeOptions".
			unset($defaultValues[$option['optionID']]);
		}
		
		// add default values from inactive options.
		foreach ($defaultValues as $optionID => $optionValue) {
			if (!empty($inserts)) $inserts .= ',';
			$inserts .= "(".$groupID.", ".$optionID.", '".escapeString($optionValue)."')";
		}
		
		if (!empty($inserts)) {
			$sql = "REPLACE INTO	wcf".WCF_N."_group_option_value
						(groupID, optionID, optionValue)
				VALUES 		".$inserts;
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * Updates this group. 
	 * 
	 * @param	string		$groupName
	 * @param	array		$groupOptions
	 * @param	array		$additionalFields
	 */
	public function update($groupName, $groupOptions, $additionalFields = array()) {
		$this->updateGroup($groupName, $additionalFields);
		self::insertGroupOptions($this->groupID, $groupOptions, true);
		
		// clear cache
		self::clearCache();
	}
	
	/**
	 * Updates the static data of this group.
	 *
	 * @param	string		$groupName
	 * @param	array		$additionalFields 
	 */
	protected function updateGroup($groupName, $additionalFields = array()) {
		$updates = '';
		foreach ($additionalFields as $key => $value) {
			$updates .= ",".$key."='".escapeString($value)."'";
		}
		
		// save group
		$sql = "UPDATE	wcf".WCF_N."_group
			SET	groupName = '".escapeString($groupName)."'
				".$updates."
			WHERE 	groupID = ".$this->groupID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Updates the value from the accessiblegroups option.
	 * 
	 * @param	integer		$groupID	this group is added or deleted in the value 	
	 * @param 	boolean		$delete		flag for group deletion
	 */
	protected static function updateAccessibleGroups($groupID, $delete = false) {
		$sql = "SELECT		groupID, optionValue, groupOption.optionID
			FROM		wcf".WCF_N."_group_option groupOption
			LEFT JOIN	wcf".WCF_N."_group_option_value optionValue
			ON		(groupOption.optionID = optionValue.optionID)
			WHERE		groupOption.optionname = 'admin.user.accessibleGroups'";
			
		if ($delete) {
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$valueIDs = explode(",", $row['optionValue']);
				if (in_array($groupID, $valueIDs)) {
					$key = array_keys($valueIDs, $groupID);
					if (!empty($key)) unset($valueIDs[$key[0]]);
					$updateIDs = implode(",", $valueIDs); 
					$sql = "UPDATE	wcf".WCF_N."_group_option_value
						SET	optionValue = '".escapeString($updateIDs)."'
						WHERE	groupID = ".$row['groupID']."
						AND	optionID = ".$row['optionID'];
					WCF::getDB()->sendQuery($sql);
				} 
			}
		}
		// new group added. add this groupID to alle accessiblegroups values 
		// from groups which got all groupIDs as value up to now  
		else {
			// get existing groups
			$groupSql = 'SELECT groupID FROM wcf'.WCF_N.'_group ORDER BY groupID';
			$result = WCF::getDB()->sendQuery($groupSql);
			$groupIDsStr = '';
			while ($row = WCF::getDB()->fetchArray($result)) {
				if ($row['groupID'] != $groupID) {
					if (!empty($groupIDsStr)) $groupIDsStr .= ',';
					$groupIDsStr .= $row['groupID'];
				}
			}
			
			$sql .= " AND groupID IN (".$groupIDsStr.")";
			$result = WCF::getDB()->sendQuery($sql);
			$updateGroupIDs = '';
			$optionID = 0;
			// get groups which got "accessibleGroups"-option with all groupIDs
			while ($row = WCF::getDB()->fetchArray($result)) {
				
				// check for differences in options-groups and existing-groups	
				$optionGroupIDs = explode(",", $row['optionValue']);
				$groupIDs = explode(",", $groupIDsStr);
				$differences = array_diff($optionGroupIDs, $groupIDs);
				
				// get groups which got the right to change all groups			
				if (empty($differences) && count($optionGroupIDs) == count($groupIDs)) {
					if (!empty($updateGroupIDs)) $updateGroupIDs .= ',';
					$updateGroupIDs .= $row['groupID'];
					$optionID = $row['optionID'];
				}		
			}
			
			// update optionValue from groups which got all existing groups as value
			if (!empty($updateGroupIDs)) {
				$sql = "UPDATE	wcf".WCF_N."_group_option_value
					SET	optionValue = '".escapeString($groupIDsStr).",".$groupID."'
					WHERE	groupID IN (".$updateGroupIDs.")
					AND 	optionID = ".$optionID;
				WCF::getDB()->sendQuery($sql);
			}
			
			// clear cache
			self::clearCache();
			
			// update sessions
			Session::resetSessions();
		}
	}
	
	/**
	 * Clears the cache of all groups.
	 */
	public static function clearCache() {
		self::$cache = null;
		WCF::getCache()->clear(WCF_DIR.'cache', 'cache.groups*.php', true);
	}
	
	/**
	 * Clears the cache of all groups.
	 * 
	 * @deprecated
	 */
	protected static function clearGroupCache() {
		self::clearCache();
	}
	
	/**
	 * Deletes groups.
	 * Returns the number of deleted groups.
	 *
	 * @param	array	$groupIDs
	 * @return	integer
	 */
	public static function deleteGroups($groupIDs) {
		// remove default groups
		$groupIDs = array_diff($groupIDs, self::getGroupIdsByType(array(self::EVERYONE, self::GUESTS, self::USERS)));
		
		if (count($groupIDs) > 0) {
			$groupIDsStr = implode(',', $groupIDs);
			
			// delete options from this group
			$sql = "DELETE 	FROM wcf".WCF_N."_group_option_value
				WHERE 	groupID IN (".$groupIDsStr.")";
			WCF::getDB()->sendQuery($sql);
			
			// delete user to groups
			$sql = "DELETE 	FROM wcf".WCF_N."_user_to_groups
				WHERE 	groupID IN (".$groupIDsStr.")";
			WCF::getDB()->sendQuery($sql);
			
			// delete group from accessiblegroup values
			foreach ($groupIDs as $groupID) {
				self::updateAccessibleGroups($groupID, true);
			}
			
			// delete groups
			$sql = "DELETE 	FROM wcf".WCF_N."_group
				WHERE 	groupID IN (".$groupIDsStr.")";
			WCF::getDB()->sendQuery($sql);
			
			// clear group cache
			self::clearCache();
		}
		
		return count($groupIDs);
	}
}
?>