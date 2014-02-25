<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractOptionPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes options.
 * 
 * @author 	Benjamin Kunz
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class OptionsPackageInstallationPlugin extends AbstractOptionPackageInstallationPlugin {
	public $tagName = 'options';
	public $tableName = 'option';
	public static $reservedTags = array('name', 'optiontype', 'defaultvalue', 'validationpattern', 'enableoptions', 'showorder', 'hidden', 'selectoptions', 'categoryname', 'permissions', 'options', 'attrs', 'cdata');
	
	/**
	 * @see	 AbstractOptionPackageInstallationPlugin::saveOption()
	 */
	protected function saveOption($option, $categoryName, $existingOptionID = 0) {
		// default values
		$optionName = $optionType = $defaultValue = $validationPattern = $selectOptions = $enableOptions = $permissions = $options = '';
		$showOrder = null;
		$hidden = 0; 
		
		// make xml tags-names (keys in array) to lower case
		$this->keysToLowerCase($option);
								
		// get values
		if (isset($option['name'])) $optionName = $option['name'];
		if (isset($option['optiontype'])) $optionType = $option['optiontype'];
		if (isset($option['defaultvalue'])) $defaultValue = WCF::getLanguage()->get($option['defaultvalue']);
		if (isset($option['validationpattern'])) $validationPattern = $option['validationpattern'];
		if (isset($option['enableoptions'])) $enableOptions = $option['enableoptions'];
		if (isset($option['showorder'])) $showOrder = intval($option['showorder']);
		if (isset($option['hidden'])) $hidden = intval($option['hidden']);
		$showOrder = $this->getShowOrder($showOrder, $categoryName, 'categoryName');
		if (isset($option['selectoptions'])) $selectOptions = $option['selectoptions'];
		if (isset($option['permissions'])) $permissions = $option['permissions'];
		if (isset($option['options'])) $options = $option['options'];
		
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
		
		// insert or update option
		$sql = "INSERT INTO 			wcf".WCF_N."_".$this->tableName."
							(packageID, optionName,
							categoryName, optionType, 
							optionValue, validationPattern, 
							selectOptions, showOrder,
							enableOptions, hidden,
							permissions, options,
							additionalData)
			VALUES				(".$this->installation->getPackageID().", 
							'".escapeString($optionName)."', 
							'".escapeString($categoryName)."', 
							'".escapeString($optionType)."', 
							'".escapeString($defaultValue)."', 
							'".escapeString($validationPattern)."',
							'".escapeString($selectOptions)."',		 
							".intval($showOrder).",
							'".escapeString($enableOptions)."',
							".intval($hidden).",
							'".escapeString($permissions)."',
							'".escapeString($options)."',
							'".escapeString(serialize($additionalData))."')
			ON DUPLICATE KEY UPDATE		categoryName = VALUES(categoryName), 
							optionType = VALUES(optionType),
							validationPattern = VALUES(validationPattern),
							selectoptions = VALUES(selectOptions),
							showOrder = VALUES(showOrder),
							enableOptions = VALUES(enableOptions),
							hidden = VALUES(hidden),
							permissions = VALUES(permissions),
							options = VALUES(options),
							additionalData = VALUES(additionalData)";
		WCF::getDB()->sendQuery($sql);
	}
}
?>