<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractOptionPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes user group permissions.
 * 
 * @author 	Benjamin Kunz
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class GroupOptionsPackageInstallationPlugin extends AbstractOptionPackageInstallationPlugin {
	public $tagName = 'groupoptions';
	public $tableName = 'group_option';
	public static $reservedTags = array('name', 'optiontype', 'defaultvalue', 'validationpattern', 'showorder', 'categoryname', 'selectoptions', 'enableoptions', 'permissions', 'options', 'attrs', 'cdata');
	
	/** 
	 * Deletes group-option-categories and/or group-options which where installed by the package.
	 */
	public function uninstall() {	 
		// Delete value-entries using categories or options
		// which will be deleted.
		$sql = "DELETE FROM 	wcf".WCF_N."_group_option_value
			WHERE		optionID IN (
						SELECT	optionID
						FROM 	wcf".WCF_N."_group_option
						WHERE	packageID = ".$this->installation->getPackageID()."
					)";
		WCF::getDB()->sendQuery($sql);
			
		parent::uninstall();
	}
	
	/**
	 * @see	 AbstractOptionPackageInstallationPlugin::saveOption()
	 */
	protected function saveOption($option, $categoryName, $existingOptionID = 0) {
		// default values
		$optionName = $optionType = $defaultValue = $validationPattern = $enableOptions = $permissions = $options = '';
		$showOrder = null;
		
		// make xml tags-names (keys in array) to lower case
		$this->keysToLowerCase($option);
		
		// get values
		if (isset($option['name'])) $optionName = $option['name'];
		if (isset($option['optiontype'])) $optionType = $option['optiontype'];
		if (isset($option['defaultvalue'])) $defaultValue = $option['defaultvalue'];
		if (isset($option['validationpattern'])) $validationPattern = $option['validationpattern'];
		if (!empty($option['showorder'])) $showOrder = intval($option['showorder']);
		$showOrder = $this->getShowOrder($showOrder, $categoryName, 'categoryName');
		if (isset($option['enableoptions'])) $enableOptions = $option['enableoptions'];
		if (isset($option['permissions'])) $permissions = $option['permissions'];
		if (isset($option['options'])) $options = $option['options'];

		// check if optionType exists
		$classFile = WCF_DIR.'lib/acp/group/GroupOptionType'.ucfirst($optionType).'.class.php';
		if (!@file_exists($classFile)) {
			throw new SystemException('Unable to find file '.$classFile, 11002);
		}
		
		// collect additional tags and their values
		$additionalData = array();
		foreach ($option as $tag => $value) {
			if (!in_array($tag, self::$reservedTags)) $additionalData[$tag] = $value;
		}
		
		// check if the otion exist already and was installed by this package
		$sql = "SELECT	optionID
			FROM 	wcf".WCF_N."_group_option
			WHERE 	optionName = '".escapeString($optionName)."'
			AND	packageID = ".$this->installation->getPackageID();
		$result = WCF::getDB()->getFirstRow($sql);

		$sql = "INSERT INTO	wcf".WCF_N."_group_option
					(packageID, optionName, 
		 			categoryName, optionType, 
					defaultValue, validationPattern, 
					showOrder, enableOptions,
					permissions, options,
					additionalData)
			VALUES		(".$this->installation->getPackageID().", '".escapeString($optionName)."', 
					'".escapeString($categoryName)."', '".escapeString($optionType)."',
					'".escapeString($defaultValue)."', '".escapeString($validationPattern)."', 
					 ".$showOrder.", '".escapeString($enableOptions)."',
					'".escapeString($permissions)."', '".escapeString($options)."',
					'".escapeString(serialize($additionalData))."')
			ON DUPLICATE KEY UPDATE 	categoryName = VALUES(categoryName),
							optionType = VALUES(optionType),
							defaultValue = VALUES(defaultValue),
							validationPattern = VALUES(validationPattern),
							showOrder = VALUES(showOrder),
							enableOptions = VALUES(enableOptions),
							permissions = VALUES(permissions),
							options = VALUES(options),
							additionalData = VALUES(additionalData)";
		WCF::getDB()->sendQuery($sql);
		if (isset($result['optionID']) && $this->installation->getAction() == 'update') {	
			$optionID = $result['optionID'];
		}
		else {
			$optionID = WCF::getDB()->getInsertID();
		}
		
		// insert new option and default value to each group
		// get all groupIDs
		// don't change values of existing options
		$sql = "SELECT	groupID
			FROM	wcf".WCF_N."_group";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$sql = "INSERT IGNORE INTO	wcf".WCF_N."_group_option_value
							(groupID, optionID, optionValue)
				VALUES			(".$row['groupID'].", 
							 ".$optionID.", 
							'".escapeString($defaultValue)."')";
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/** 
	 * Deletes the values from group option value table from options
	 * deleted by this package update.
	 * 
	 * @param 	string 	$optionNames 
	 */
	protected function deleteOptions($optionNames) {
		$sql = "SELECT	optionID
			FROM 	wcf".WCF_N."_group_option
			WHERE	optionName IN (".$optionNames.")
			AND 	packageID = ".$this->installation->getPackageID();
		$result = WCF::getDB()->sendQuery($sql);
		$optionIDs = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$optionIDs[] = $row['optionID'];
		}
		$this->deleteValues($optionIDs);
		parent::deleteOptions($optionNames);
	}
	
	/** 
	 * Deletes the values from group option value table from option categories
	 * deleted by this package update.
	 * 
	 * @param 	string 	$categoryNames  
	 */
	protected function deleteCategories($categoryNames) {
		$sql = "SELECT	optionID
			FROM 	wcf".WCF_N."_group_option
			WHERE	categoryName IN (".$categoryNames.")
			AND 	packageID = ".$this->installation->getPackageID();
		$result = WCF::getDB()->sendQuery($sql);
		$optionIDs = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$optionIDs[] = $row['optionID'];
		}
		$this->deleteValues($optionIDs);
		parent::deleteCategories($categoryNames);
	}
	
	/** 
	 * Deletes the values from user-option-value table.
	 * 
	 * @param 	array 	$optionIDs  
	 */
	protected function deleteValues($optionIDs) {
		foreach ($optionIDs as $optionID) {
			$sql = "DELETE FROM 	wcf".WCF_N."_group_option_value
				WHERE		optionID = ".$optionID;
			WCF::getDB()->sendQuery($sql);
		}
	}
}
?>