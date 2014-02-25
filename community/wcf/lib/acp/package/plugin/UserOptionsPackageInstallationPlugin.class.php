<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractOptionPackageInstallationPlugin.class.php');
require_once(WCF_DIR.'lib/data/user/option/UserOptionEditor.class.php');

/**
 * This PIP installs, updates or deletes user fields.
 * 
 * @author 	Benjamin Kunz
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class UserOptionsPackageInstallationPlugin extends AbstractOptionPackageInstallationPlugin {
	public $tagName = 'useroptions';
	public $tableName = 'user_option';
	public static $reservedTags = array('name', 'optiontype', 'defaultvalue', 'validationpattern', 'required', 'editable', 'visible', 'searchable', 'showorder', 'outputclass', 'selectoptions', 'enableoptions', 'disabled', 'categoryname', 'permissions', 'options', 'attrs', 'cdata');
	
	/**
	 * Installs user option categories.
	 * 
	 * @param 	array		$category
	 * @param	array		$categoryXML
	 */
	protected function saveCategory($category, $categoryXML = null) {
		$icon = $menuIcon = '';
		if (isset($categoryXML['icon'])) $icon = $categoryXML['icon'];
		if (isset($categoryXML['menuicon'])) $menuIcon = $categoryXML['menuicon'];
		
		$sql = "INSERT INTO			wcf".WCF_N."_".$this->tableName."_category
							(packageID, categoryName, parentCategoryName".($category['showOrder'] !== null ? ", showOrder" : "").", categoryIconS, categoryIconM, permissions, options)
			VALUES				(".$this->installation->getPackageID().", 
							'".escapeString($category['categoryName'])."', 
							'".$category['parentCategoryName']."', 
							".($category['showOrder'] !== null ? $category['showOrder']."," : "")." 
							'".escapeString($menuIcon)."',
							'".escapeString($icon)."',
							'".escapeString($category['permissions'])."',
							'".escapeString($category['options'])."')
			ON DUPLICATE KEY UPDATE 	parentCategoryName = VALUES(parentCategoryName),
							".($category['showOrder'] !== null ? "showOrder = VALUES(showOrder)," : "")."
							categoryIconS = VALUES(categoryIconS),
							categoryIconM = VALUES(categoryIconM),
							permissions = VALUES(permissions),
							options = VALUES(options)";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * @see	 AbstractOptionPackageInstallationPlugin::saveOption()
	 */
	protected function saveOption($option, $categoryName, $existingOptionID = 0) {
		// default values
		$optionName = $optionType = $defaultValue = $validationPattern = $outputClass = $selectOptions = $enableOptions = $permissions = $options = '';
		$required = $editable = $visible = $searchable = $disabled = $askDuringRegistration = 0;
		$showOrder = null;
		
		// make xml tags-names (keys in array) to lower case
		$this->keysToLowerCase($option);
								
		// get values
		if (isset($option['name'])) $optionName 			= $option['name'];
		if (isset($option['optiontype'])) $optionType 			= $option['optiontype'];
		if (isset($option['defaultvalue'])) $defaultValue 		= $option['defaultvalue'];
		if (isset($option['validationpattern'])) $validationPattern 	= $option['validationpattern'];
		if (isset($option['required'])) $required 			= intval($option['required']);
		if (isset($option['askduringregistration'])) $askDuringRegistration = intval($option['askduringregistration']);
		if (isset($option['editable'])) $editable		 	= intval($option['editable']);
		if (isset($option['visible'])) $visible 			= intval($option['visible']);
		if (isset($option['searchable'])) $searchable 			= intval($option['searchable']);
		if (isset($option['showorder'])) $showOrder	 		= intval($option['showorder']);
		if (isset($option['outputclass'])) $outputClass 		= $option['outputclass'];
		if (isset($option['selectoptions'])) $selectOptions 		= $option['selectoptions'];
		if (isset($option['enableoptions'])) $enableOptions	 	= $option['enableoptions'];
		if (isset($option['disabled'])) $disabled 			= intval($option['disabled']);
		$showOrder = $this->getShowOrder($showOrder, $categoryName, 'categoryName');
		if (isset($option['permissions'])) $permissions 		= $option['permissions'];
		if (isset($option['options'])) $options 			= $option['options'];
		
		// check if optionType exists
		$classFile = WCF_DIR.'lib/acp/option/OptionType'.ucfirst($optionType).'.class.php';
		if (!@file_exists($classFile)) {
			throw new SystemException('Unable to find file '.$classFile, 11002);
		}
		
		// collect additional tags and their values
		$additionalData = array();
		foreach ($option as $tag => $value) {
			if (!in_array($tag, self::$reservedTags)) $additionalData[$tag] = $value;
		}
		
		// get optionID if it was installed by this package already
		$sql = "SELECT	*
			FROM 	wcf".WCF_N."_".$this->tableName."
			WHERE 	optionName = '".escapeString($optionName)."'
			AND	packageID = ".$this->installation->getPackageID();
		$result = WCF::getDB()->getFirstRow($sql);
		
		// update option
		if (!empty($result['optionID']) && $this->installation->getAction() == 'update') {
			$userOption = new UserOptionEditor(null, $result);
			$userOption->update($optionName, $categoryName, $optionType, $defaultValue, $validationPattern, $selectOptions, $enableOptions, $required, $askDuringRegistration, $editable, $visible, $outputClass, $searchable, $showOrder, $disabled, $permissions, $options, $additionalData);
		}
		// insert new option
		else {
			UserOptionEditor::create($optionName, $categoryName, $optionType, $defaultValue, $validationPattern, $selectOptions, $enableOptions, $required, $askDuringRegistration, $editable, $visible, $outputClass, $searchable, $showOrder, $disabled, $this->installation->getPackageID(), $permissions, $options, $additionalData);
		}
        }
	
	/** 
	 * Drops the columns from user option value table from options
	 * installed by this package.
	 */
	public function uninstall() {
		// get optionsIDs from package
		$sql = "SELECT	optionID
			FROM 	wcf".WCF_N."_user_option
			WHERE	packageID = ".$this->installation->getPackageID();
		$result = WCF::getDB()->sendQuery($sql);
		$optionIDs = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$optionIDs[] = $row['optionID'];		
		}
		$this->dropColumns($optionIDs);
		
		// uninstall options and categories
		parent::uninstall();
	}	
	
	/** 
	 * Drops the columns from user option value table from options
	 * deleted by this package update.
	 * 
	 * @param 	string 		$optionNames 
	 */
	protected function deleteOptions($optionNames) {
		$sql = "SELECT	optionID
			FROM 	wcf".WCF_N."_user_option
			WHERE	optionName IN (".$optionNames.")
			AND 	packageID = ".$this->installation->getPackageID();
		$result = WCF::getDB()->sendQuery($sql);
		$optionIDs = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$optionIDs[] = $row['optionID'];
		}
		$this->dropColumns($optionIDs);
		parent::deleteOptions($optionNames);
	}
	
	/** 
	 * Drops the columns from user option value table from option categories
	 * deleted by this package update.
	 * 
	 * @param 	string 		$categoryNames  
	 */
	protected function deleteCategories($categoryNames) {
		$sql = "SELECT	optionID
			FROM 	wcf".WCF_N."_user_option
			WHERE	categoryName IN (".$categoryNames.")
			AND 	packageID = ".$this->installation->getPackageID();
		$result = WCF::getDB()->sendQuery($sql);
		$optionIDs = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$optionIDs[] = $row['optionID'];
		}
		$this->dropColumns($optionIDs);
		parent::deleteCategories($categoryNames);
	}
	
	/** 
	 * Drops columns from user-option-value table.
	 * 
	 * @param 	array 		$optionIDs  
	 */
	protected function dropColumns($optionIDs) {
		foreach ($optionIDs as $optionID) {
			$sql = "ALTER TABLE 	wcf".WCF_N."_user_option_value
				DROP COLUMN	userOption".$optionID;
			WCF::getDB()->sendQuery($sql);
		}
	}
}
?>