<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/option/UserOption.class.php');

/**
 * Provides functions to create and edit the data of a user option.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option
 * @category 	Community Framework
 */
class UserOptionEditor extends UserOption {
	/**
	 * Disables this option.
	 */
	public function disable() {
		$this->enable(false);
	}
	
	/**
	 * Enables this option.
	 * 
	 * @param 	boolean		$enable
	 */
	public function enable($enable = true) {
		$sql = "UPDATE	wcf".WCF_N."_user_option
			SET	disabled = ".intval(!$enable)."
			WHERE	optionID = ".$this->optionID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Deletes this user option.
	 */
	public function delete() {
		$sql = "DELETE FROM	wcf".WCF_N."_user_option
			WHERE		optionID = ".$this->optionID;
		WCF::getDB()->sendQuery($sql);
		
		$sql = "ALTER TABLE	wcf".WCF_N."_user_option_value
			DROP 		userOption".$this->optionID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Creates a new user option.
	 *
	 * @param 	string 		$optionName
	 * @param 	string 		$categoryName
	 * @param 	string 		$optionType
	 * @param 	mixed 		$defaultValue
	 * @param 	string 		$validationPattern
	 * @param 	string 		$selectOptions
	 * @param 	string 		$enableOptions
	 * @param 	boolean 	$required
	 * @param 	boolean 	$askDuringRegistration
	 * @param 	integer 	$editable
	 * @param 	integer 	$visible
	 * @param 	string 		$outputClass
	 * @param 	boolean 	$searchable
	 * @param 	integer 	$showOrder
	 * @param 	boolean 	$disabled
	 * @param 	integer 	$packageID
	 * @param	string		$permissions
	 * @param	string		$options
	 * @param	array		$addionalData
	 * @return 	integer
	 */
	public static function create($optionName, $categoryName, $optionType, $defaultValue, $validationPattern, $selectOptions, $enableOptions, $required, $askDuringRegistration, $editable, $visible, $outputClass, $searchable, $showOrder, $disabled = 0, $packageID = PACKAGE_ID, $permissions = '', $options = '', $additionalData = array()) {
		// insert new option
		$sql = "INSERT INTO	wcf".WCF_N."_user_option
					(packageID, optionName, categoryName, optionType, defaultValue,
					validationPattern, selectOptions, enableOptions, required, askDuringRegistration, editable,
					visible, outputClass, searchable, showOrder, disabled, permissions, options, additionalData)
			VALUES		(".$packageID.", '".escapeString($optionName)."', '".escapeString($categoryName)."', '".escapeString($optionType)."', '".escapeString($defaultValue)."',
					'".escapeString($validationPattern)."', '".escapeString($selectOptions)."', '".escapeString($enableOptions)."', ".$required.", ".$askDuringRegistration.", ".$editable.",
					".$visible.", '".escapeString($outputClass)."', ".$searchable.", ".$showOrder.", ".$disabled.", '".escapeString($permissions)."', '".escapeString($options)."', '".escapeString(serialize($additionalData))."')";
		WCF::getDB()->sendQuery($sql);
		$optionID = WCF::getDB()->getInsertID();
		
		// alter the table "wcf".WCF_N."_user_option_value" with this new option
		$sql = "ALTER TABLE 	wcf".WCF_N."_user_option_value
			ADD COLUMN	userOption".$optionID." ".self::getColumnType($optionType);  
    		WCF::getDB()->sendQuery($sql);
    		
    		// add the default value to this column
		if ($defaultValue) {
			$sql = "UPDATE	wcf".WCF_N."_user_option_value
					SET userOption".$optionID." = '".escapeString($defaultValue)."'";  
	    		WCF::getDB()->sendQuery($sql);
		}
		
		return $optionID;
	}
	
	/**
	 * Determines the needed sql column type for a user option.
	 * 
	 * @param	string		$optionType
	 * @return	string		column type
	 */
	public static function getColumnType($optionType) {
		switch ($optionType) {
			case 'boolean': return 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0';
			case 'integer': return 'INT(10) UNSIGNED NOT NULL DEFAULT 0';
			case 'float': return 'FLOAT NOT NULL DEFAULT 0.0';
			case 'textarea': return 'MEDIUMTEXT'; 
			case 'date': case 'birthday': return "CHAR(10) NOT NULL DEFAULT '0000-00-00'";
			default: return 'TEXT';
		}
	}
	
	/**
	 * Updates the data of an existing user option.
	 *
	 * @param 	string 		$optionName
	 * @param 	string 		$categoryName
	 * @param 	string 		$optionType
	 * @param 	mixed 		$defaultValue
	 * @param 	string 		$validationPattern
	 * @param 	string 		$selectOptions
	 * @param 	string 		$enableOptions
	 * @param 	boolean 	$required
	 * @param 	boolean 	$askDuringRegistration
	 * @param 	integer 	$editable
	 * @param 	integer 	$visible
	 * @param 	string 		$outputClass
	 * @param 	boolean 	$searchable
	 * @param 	integer 	$showOrder
	 * @param 	boolean 	$disabled
	 * @param	string		$permissions
	 * @param	string		$options
	 * @param	array		$addionalData
	 */
	public function update($optionName, $categoryName, $optionType, $defaultValue, $validationPattern, $selectOptions, $enableOptions, $required, $askDuringRegistration, $editable, $visible, $outputClass, $searchable, $showOrder, $disabled = 0, $permissions = '', $options = '', $additionalData = null) {
		$sql = "UPDATE 	wcf".WCF_N."_user_option
			SET	optionName = '".escapeString($optionName)."',
				categoryName = '".escapeString($categoryName)."',
				optionType = '".escapeString($optionType)."',
				defaultValue = '".escapeString($defaultValue)."',
				validationPattern = '".escapeString($validationPattern)."',
				selectOptions = '".escapeString($selectOptions)."',
				required = ".$required.",
				askDuringRegistration = ".$askDuringRegistration.",
				editable = ".$editable.",
				visible = ".$visible.",
				searchable = ".$searchable.",
				outputClass = '".escapeString($outputClass)."',
				showOrder = ".$showOrder.",
				enableOptions = '".escapeString($enableOptions)."',
				disabled = ".$disabled.",
				permissions = '".escapeString($permissions)."',
				options = '".escapeString($options)."'
				".($additionalData !== null ? ", additionalData = '".escapeString(serialize($additionalData))."'" : "")."
			WHERE 	optionID = ".$this->optionID;
		WCF::getDB()->sendQuery($sql);
		
		// alter the table "wcf".WCF_N."_user_option_value" with this new option
		$sql = "ALTER TABLE 	wcf".WCF_N."_user_option_value
			CHANGE		userOption".$this->optionID." 
					userOption".$this->optionID." ".$this->getColumnType($optionType);  
    		WCF::getDB()->sendQuery($sql);
    	}  
}
?>